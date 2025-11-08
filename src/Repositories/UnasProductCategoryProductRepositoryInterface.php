<?php

namespace Molitor\Unas\Repositories;

use Molitor\Unas\Models\UnasShop;
use Molitor\Unas\Models\UnasProduct;
use Molitor\Unas\Models\UnasProductCategory;

interface UnasProductCategoryProductRepositoryInterface
{
    public function exists(UnasProductCategory $productCategory, UnasProduct $product):bool;

    public function delete(UnasProductCategory $productCategory, UnasProduct $product): bool;

    public function setValue(UnasProductCategory $productCategory, UnasProduct $product, $value): bool;

    public function getProductCategoryIdsByProduct(UnasProduct $resource): array;

    public function deleteByShop(UnasShop $shop): bool;

    public function deleteByCategory(UnasProductCategory $productCategory): bool;

    public function deleteByProduct(UnasProduct $product): bool;

    public function setProductCategories(UnasProduct $product, array $ids): void;
}