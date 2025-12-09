<?php

namespace Molitor\Unas\Repositories;

use Molitor\Unas\Models\UnasShop;
use Molitor\Unas\Models\UnasOrder;

interface UnasOrderRepositoryInterface
{
    public function getByRemoteId(int $remoteId): UnasOrder|null;

    public function deleteByShop(UnasShop $shop): void;

    public function getCountByShop(UnasShop $shop): int;
}
