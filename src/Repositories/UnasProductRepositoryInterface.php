<?php

namespace Molitor\Unas\Repositories;

use Molitor\Product\Models\Product;
use Molitor\Product\Models\ProductUnit;
use Molitor\Unas\Models\UnasShop;
use Molitor\Unas\Models\UnasProduct;
use Molitor\Unas\Models\UnasProductCategory;
use Illuminate\Database\Eloquent\Collection;

interface UnasProductRepositoryInterface
{
    public function getShopProducts(UnasShop $shop): Collection;

    public function skuExists(UnasShop $shop, string $sku): bool;

    public function createSku(UnasShop $shop, string $sku): string;

    public function findByRemoteId(UnasShop $unasShop, ?int $remoteId): ?UnasProduct;

    public function createProduct(
        UnasShop    $shop,
        int         $remoteId,
        string      $sku,
        string      $name,
        ?string     $description,
        ?float      $price,
        ProductUnit $productUnit
    ): UnasProduct;

    public function addShopProduct(UnasProduct $shopProduct, UnasProductCategory $shopProductCategory): UnasProduct;

    public function getBySku(UnasShop $unasShop, string $sku): UnasProduct|null;

    public function update(UnasProduct $product): void;

    public function deleteShopProductFromTheShop(UnasProduct $shopProduct): void;

    public function deleteProduct(Product $product, UnasProductCategory $shopProductCategory): bool;

    public function deleteByShop(UnasShop $shop): self;

    public function getCountByShop(UnasShop $shop): int;

    public function forceDeleteByShop(UnasShop $shop): void;

    public function getChangedByShop(UnasShop $shop): Collection;

    public function getDeletableByShop(UnasShop $shop): Collection;

    public function forceDeleteByRemoteId(int $Id): bool;
}
