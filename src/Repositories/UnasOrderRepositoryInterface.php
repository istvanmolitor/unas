<?php

namespace Molitor\Unas\Repositories;

use Molitor\Unas\Models\UnasOrder;
use Molitor\Unas\Models\UnasShop;

interface UnasOrderRepositoryInterface
{
    public function getByRemoteId(int $remoteId): ?UnasOrder;

    public function deleteByShop(UnasShop $shop): void;

    public function getCountByShop(UnasShop $shop): int;
}
