<?php

namespace Molitor\Unas\Services;

use Molitor\Unas\Models\UnasShop;
use Molitor\Unas\Models\UnasOrder;

class UnasOrderService extends UnasService
{
    public function storeResultOrder(UnasShop $shop, array $resultOrder): UnasOrder
    {
        $remoteId = (int)$resultOrder['Id'];

        $unasOrder = $this->getByRemoteId($remoteId);
        if ($unasOrder) {
            return $unasOrder;
        }

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

    public function downloadOrders(UnasShop $shop)
    {
        $endpoint = (new UnasService($shop->api_key))->makeGetOrderEndpoint();
        $endpoint->execute();

        foreach ($endpoint->getResultOrders() as $resultOrder) {
            $this->storeResultOrder($shop, $resultOrder);
        }
    }
}