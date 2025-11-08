<?php

namespace Molitor\Unas\Repositories;

use Molitor\Unas\Models\UnasShop;
use Molitor\Unas\Models\UnasProductCategory;
use Illuminate\Database\Eloquent\Collection;

interface UnasProductCategoryRepositoryInterface
{
    public function getByShop(UnasShop $shop): Collection;

    public function getRootCategories(UnasShop $shop): Collection;

    public function getRootCategoryByName(UnasShop $shop, string $name): ?UnasProductCategory;

    public function createRootCategory(UnasShop $shop, string $name): ?UnasProductCategory;

    public function getSubCategoryByName(UnasProductCategory $parent, string $name): ?UnasProductCategory;

    public function createSubCategory(UnasProductCategory $parent, string $name): ?UnasProductCategory;

    public function categoryNameExists(UnasShop $shop, string $name): bool;

    public function delete(UnasProductCategory $category): void;

    public function deleteByShop(UnasShop $shop): void;

    public function getDeletedCategories(UnasShop $shop): Collection;

    public function findByRemoteId(UnasShop $shop, int $remoteId): ?UnasProductCategory;

    public function getCountByShop(UnasShop $shop): int;

    public function forceDeleteByShop(UnasShop $shop): void;

    public function isChildOf(UnasProductCategory $parent, UnasProductCategory $category): bool;

    public function getChangedByShop(UnasShop $shop): Collection;

    public function forceDeleteByRemoteId(int $id): bool;
}