<?php

namespace Molitor\Unas\Services;

use Molitor\Currency\Repositories\CurrencyRepositoryInterface;
use Molitor\Customer\Repositories\CustomerRepositoryInterface;
use Molitor\Order\Repositories\OrderRepositoryInterface;
use Molitor\Order\Repositories\OrderStatusRepositoryInterface;
use Molitor\Unas\Models\UnasShop;
use Molitor\Unas\Models\UnasOrder;
use Molitor\Unas\Repositories\UnasOrderRepositoryInterface;

class UnasOrderService extends UnasService
{
    public function __construct(
        private CustomerRepositoryInterface $customerRepository,
        private UnasOrderRepositoryInterface $unasOrderRepository,
        private OrderRepositoryInterface $orderRepository,
        private CurrencyRepositoryInterface $currencyRepository,
        private OrderStatusRepositoryInterface $orderStatusRepository,
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

        $mail = $resultOrder['Email'];

        $customer = $this->customerRepository->findOrCrate('aaaa');

        $order = $this->orderRepository->create(
            (string)$resultOrder['Key'],
            $customer,
            $this->currencyRepository->getByCode($resultOrder['Currency']),
            $this->orderStatusRepository->fundOrCreate($resultOrder['Status'])
        );

        return $this->unasOrder->create([
            'unas_shop_id' => $shop->id,
            'order_id' => $order->id,
            'remote_id' => $remoteId,
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
}
