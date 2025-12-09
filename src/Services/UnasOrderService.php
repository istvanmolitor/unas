<?php

namespace Molitor\Unas\Services;

use Molitor\Currency\Repositories\CurrencyRepositoryInterface;
use Molitor\Customer\Repositories\CustomerRepositoryInterface;
use Molitor\Order\Models\Order;
use Molitor\Order\Repositories\OrderRepositoryInterface;
use Molitor\Order\Repositories\OrderStatusRepositoryInterface;
use Molitor\Unas\Models\UnasShop;
use Molitor\Unas\Models\UnasOrder;
use Molitor\Unas\Repositories\UnasOrderRepositoryInterface;
use Molitor\Address\Repositories\AddressRepositoryInterface;
use Molitor\Address\Repositories\CountryRepositoryInterface;
use Molitor\Product\Repositories\ProductRepositoryInterface;
use Molitor\Order\Models\OrderItem;

class UnasOrderService extends UnasService
{
    public function __construct(
        private CustomerRepositoryInterface $customerRepository,
        private UnasOrderRepositoryInterface $unasOrderRepository,
        private OrderRepositoryInterface $orderRepository,
        private CurrencyRepositoryInterface $currencyRepository,
        private OrderStatusRepositoryInterface $orderStatusRepository,
        private ProductRepositoryInterface $productRepository
    )
    {
    }

    public function storeResultOrder(UnasShop $shop, array $resultOrder): UnasOrder
    {
        $remoteId = (int)$resultOrder['Id'];

        $unasOrder = $this->unasOrderRepository->getByRemoteId($remoteId);
        if ($unasOrder) {
            return $unasOrder;
        }

        $mail = $resultOrder['Customer']['Email'] ?? null;
        $internalName = $mail ?? ('unas-' . $remoteId);
        $customer = $this->customerRepository->findOrCrate($internalName);

        $order = $this->orderRepository->create(
            (string)$resultOrder['Key'],
            $customer,
            $this->currencyRepository->getByCode($resultOrder['Currency']),
            $this->orderStatusRepository->fundOrCreate($resultOrder['Status'], $resultOrder['Status'])
        );

        // Save invoice and shipping addresses if available
        /** @var AddressRepositoryInterface $addressRepository */
        $addressRepository = app(AddressRepositoryInterface::class);
        /** @var CountryRepositoryInterface $countryRepository */
        $countryRepository = app(CountryRepositoryInterface::class);

        $addresses = $resultOrder['Customer']['Addresses'] ?? [];
        $invoice = $addresses['Invoice'] ?? null;
        if ($invoice && $order->invoiceAddress) {
            $countryId = null;
            if (!empty($invoice['CountryCode'])) {
                $countryId = $countryRepository->findOrCreate(strtolower((string)$invoice['CountryCode']))->id;
            }
            $addressRepository->saveAddress($order->invoiceAddress, [
                'name' => (string)($invoice['Name'] ?? ''),
                'country_id' => $countryId,
                'zip_code' => (string)($invoice['ZIP'] ?? ''),
                'city' => (string)($invoice['City'] ?? ''),
                'address' => (string)($invoice['Street'] ?? ''),
            ]);
        }

        $shipping = $addresses['Shipping'] ?? null;
        if ($shipping && $order->shippingAddress) {
            $countryId = null;
            if (!empty($shipping['CountryCode'])) {
                $countryId = $countryRepository->findOrCreate(strtolower((string)$shipping['CountryCode']))->id;
            }
            $addressRepository->saveAddress($order->shippingAddress, [
                'name' => (string)($shipping['Name'] ?? ''),
                'country_id' => $countryId,
                'zip_code' => (string)($shipping['ZIP'] ?? ''),
                'city' => (string)($shipping['City'] ?? ''),
                'address' => (string)($shipping['Street'] ?? ''),
            ]);
        }

        $this->saveOrderItems($order, $resultOrder['Items']['Item'] ?? []);

        // Compose comments with UNAS metadata
        $contact = $resultOrder['Customer']['Contact'] ?? [];
        $payment = $resultOrder['Payment'] ?? [];
        $shippingInfo = $resultOrder['Shipping'] ?? [];
        $invoiceInfo = $resultOrder['Invoice'] ?? [];
        $metaLines = [];
        if (!empty($contact['Name'])) $metaLines[] = 'Customer: ' . $contact['Name'];
        if (!empty($mail)) $metaLines[] = 'Email: ' . $mail;
        if (!empty($contact['Phone'])) $metaLines[] = 'Phone: ' . $contact['Phone'];
        if (!empty($resultOrder['Referer'])) $metaLines[] = 'Referer: ' . $resultOrder['Referer'];
        if (!empty($payment)) $metaLines[] = 'Payment: ' . (($payment['Name'] ?? '') . (isset($payment['Type']) ? ' (' . $payment['Type'] . ')' : ''));
        if (!empty($shippingInfo)) $metaLines[] = 'Shipping: ' . (($shippingInfo['Name'] ?? '') . (isset($shippingInfo['PackageNumber']) ? ' [' . $shippingInfo['PackageNumber'] . ']' : ''));
        if (!empty($invoiceInfo)) $metaLines[] = 'Invoice: ' . (($invoiceInfo['Number'] ?? '') . (isset($invoiceInfo['Url']) ? ' ' . $invoiceInfo['Url'] : ''));
        if (!empty($resultOrder['SumPriceGross'])) $metaLines[] = 'Total gross: ' . $resultOrder['SumPriceGross'] . ' ' . ($resultOrder['Currency'] ?? '');
        if (!empty($resultOrder['Weight'])) $metaLines[] = 'Weight: ' . $resultOrder['Weight'] . ' kg';
        if (!empty($resultOrder['Date'])) $metaLines[] = 'Order date: ' . $resultOrder['Date'];
        if (!empty($resultOrder['DateMod'])) $metaLines[] = 'Modified: ' . $resultOrder['DateMod'];

        $order->comment = implode("\n", $metaLines);
        $order->internal_comment = 'UNAS Status: ' . ($resultOrder['Status'] ?? '') . ' / ' . ($resultOrder['StatusType'] ?? '') . ' (Seen: ' . ($resultOrder['Seen'] ?? '') . ', Auth: ' . ($resultOrder['Authenticated'] ?? '') . ')';
        $order->save();

        return UnasOrder::create([
            'unas_shop_id' => $shop->id,
            'order_id' => $order->id,
            'remote_id' => $remoteId,
            'changed' => false,
        ]);
    }

    public function downloadOrders(UnasShop $shop): void
    {
        $endpoint = $this->makeGetOrderEndpoint($shop->api_key);
        $endpoint->execute();

        foreach ($endpoint->getResultOrders() as $resultOrder) {
            $this->storeResultOrder($shop, $resultOrder);
        }
    }

    public function downloadOrderByCode(UnasShop $shop, string $code): UnasOrder|null
    {
        $endpoint = $this->makeGetOrderEndpoint($shop->api_key);
        $endpoint->setKeyRequestData($code);
        $endpoint->execute();

        $resultOrder = $endpoint->getResultOrder();
        return $this->storeResultOrder($shop, $resultOrder);
    }

    public function getProductIdByItem(array $item): int
    {
        $sku = (string)($item['Sku'] ?? '');
        $name = (string)($item['Name'] ?? $sku ?: 'UNAS item');
        if ($sku === '') {
            $sku = 'unas-' . ($item['Id'] ?? uniqid());
        }

        return $this->productRepository->findOrCreate($sku, $name)->id;
    }

    protected function saveOrderItems(Order $order, array $items): void
    {
        if (is_array($items)) {
            foreach ($items as $item) {
                $orderItem = new OrderItem();
                $orderItem->order_id = $order->id;
                $orderItem->product_id = $this->getProductIdByItem($item);
                $orderItem->quantity = (int)($item['Quantity'] ?? 1);
                $orderItem->price = (float)($item['PriceGross'] ?? 0);
                $orderItem->comment = $item['Unit'] ?? null;
                $orderItem->save();
            }
        }
    }
}
