<?php

namespace Molitor\Unas\Repositories;

use Molitor\Currency\Repositories\CurrencyRepositoryInterface;
use Molitor\Customer\Repositories\CustomerRepositoryInterface;
use Molitor\Order\Repositories\OrderRepository;
use Molitor\Order\Repositories\OrderStatusRepository;
use Molitor\Unas\Services\UnasService;
use Molitor\Unas\Models\UnasShop;
use Molitor\Unas\Models\UnasOrder;

class UnasOrderRepository implements UnasOrderRepositoryInterface
{
    private UnasOrder $unasOrder;
    private OrderStatusRepository $orderStatusRepository;

    public function __construct(
        private CurrencyRepositoryInterface $currencyRepository,
        private CustomerRepositoryInterface $customerRepository
    )
    {
        $this->unasOrder = new UnasOrder();
        $this->orderStatusRepository = new OrderStatusRepository();
    }

    public function getByRemoteId(int $remoteId): ?UnasOrder
    {
        return $this->unasOrder->where('remote_id', $remoteId)->first();
    }

    public function deleteByShop(UnasShop $shop): void
    {
        $this->unasOrder->where('unas_shop_id', $shop->id)->delete();
    }

    public function getCountByShop(UnasShop $shop): int
    {
        return $this->unasOrder->where('unas_shop_id', $shop->id)->count();
    }
}
