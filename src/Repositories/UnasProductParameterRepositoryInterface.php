<?php

namespace Molitor\Unas\Repositories;

use Molitor\Product\Models\ProductField;
use Molitor\Unas\Models\UnasShop;

interface UnasProductParameterRepositoryInterface
{
    public function exists(UnasShop $shop, ProductField $productField): bool;

    public function addToShop(UnasShop $shop, ProductField $productField, $type = 'text'): bool;

    public function getRelevantProductFields(UnasShop $shop);

    public function createRelevantParameters(UnasShop $shop);

    public function deleteByShop(UnasShop $shop): void;

    public function getCountByShop(UnasShop $shop): int;

    public function forceDeleteByShop(UnasShop $shop): void;

    public function forceDeleteByRemoteId(int $id): bool;
}