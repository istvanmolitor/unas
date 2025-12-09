<?php

namespace Molitor\Unas\Repositories;

use Molitor\Unas\Models\UnasShop;
use Molitor\Unas\Models\UnasOrder;

class UnasOrderRepository implements UnasOrderRepositoryInterface
{
    private UnasOrder $unasOrder;

    public function __construct()
    {
        $this->unasOrder = new UnasOrder();
    }

    public function getByRemoteId(int $remoteId): UnasOrder|null
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
