<?php

namespace Molitor\Unas\Services;

use Molitor\Customer\Models\Customer;
use Molitor\Product\Models\ProductCategory;
use Molitor\Product\Repositories\ProductCategoryRepository;
use Molitor\Tree\IdTreeBuilder;
use Molitor\Unas\Models\UnasProductCategory;
use Molitor\Unas\Models\UnasShop;
use Molitor\Unas\Repositories\UnasProductCategoryProductRepositoryInterface;
use Molitor\Unas\Repositories\UnasProductCategoryRepositoryInterface;
use Molitor\Unas\Repositories\UnasProductRepositoryInterface;

class UnasProductCategoryService extends UnasService
{
    public function __construct(
        private UnasProductCategoryRepositoryInterface        $unasProductCategoryRepository,
        private UnasProductCategoryProductRepositoryInterface $unasProductCategoryProductRepository,
        private UnasProductRepositoryInterface                $unasProductRepository
    )
    {
    }

    public function createByPath(UnasShop $shop, array $path): ?UnasProductCategory
    {
        $parent = null;
        foreach ($path as $name) {
            if ($parent === null) {
                $parent = $this->unasProductCategoryRepository->createRootCategory($shop, (string)$name);
            } else {
                $parent = $this->unasProductCategoryRepository->createSubCategory($parent, (string)$name);
            }
            if (!$parent) {
                return null;
            }
        }
        return $parent;
    }

    public function getByPath(UnasShop $shop, array $path): ?UnasProductCategory
    {
        $count = count($path);
        if ($count == 0) {
            return null;
        } elseif ($count == 1) {
            return $this->unasProductCategoryRepository->getRootCategoryByName($shop, $path[0]);
        } else {
            $parent = $this->getByPath($shop, array_slice($path, 0, $count - 1));
            return $this->unasProductCategoryRepository->getSubCategoryByName($parent, $path[$count - 1]);
        }
    }

    public function repairCategories(UnasShop $shop): void
    {
        // Delegated to UnasProductCategoryApiDtoService
        $apiService = app(\Molitor\Unas\Services\Dto\Api\UnasProductCategoryApiDtoService::class);
        $apiService->syncFromApi($shop);
    }

    public function deleteCategory(UnasProductCategory $category)
    {
        foreach ($category->childCategories as $subCategory) {
            $this->deleteCategory($subCategory);
        }

        foreach ($category->shopProducts as $shopProduct) {
            $this->unasProductRepository->update($shopProduct);
        }

        $this->unasProductCategoryProductRepository->deleteByCategory($category);

        $this->unasProductCategoryRepository->delete($category);
    }

    public function isEmptyCategory(UnasProductCategory $shopCategory): bool
    {
        if (count($shopCategory->shopProducts)) {
            return false;
        }
        return true;
    }

    public function clearShop(UnasShop $shop): void
    {
        $shopCategories = $this->unasProductCategoryRepository->getByShop($shop);

        /** @var UnasProductCategory $shopCategory */
        foreach ($shopCategories as $shopCategory) {
            if ($this->isEmptyCategory($shopCategory)) {
                $this->deleteCategory($shopCategory);
            }
        }
    }

    public function syncDeletes(UnasShop $shop): int
    {
        // Delegated to UnasProductCategoryApiDtoService
        $apiService = app(\Molitor\Unas\Services\Dto\Api\UnasProductCategoryApiDtoService::class);
        return $apiService->syncDeletes($shop);
    }

    public function syncChanges(UnasShop $shop): bool
    {
        // Delegated to UnasProductCategoryApiDtoService
        $apiService = app(\Molitor\Unas\Services\Dto\Api\UnasProductCategoryApiDtoService::class);
        return $apiService->syncChanges($shop);
    }

    public function getPath(UnasProductCategory $category): array
    {
        $path = [];
        foreach ($this->unasProductCategoryRepository->getPathCategories($category) as $pathCategory) {
            $path[] = (string)$pathCategory;
        }
        return $path;
    }

    public function copyProductCategory(ProductCategory $productCategory, UnasShop $shop): ?UnasProductCategory
    {
        $productCategoryRepository = new ProductCategoryRepository();
        $path = $productCategoryRepository->getPathCategories($productCategory);
        return $this->createShopCategory($shop, $path);
    }

    public function copyAllProductCategory(UnasShop $shop): void
    {
        $productCategoryRepository = new ProductCategoryRepository();
        /** @var ProductCategory $productCategory */
        foreach ($productCategoryRepository->getAll() as $productCategory) {
            $this->copyProductCategory($productCategory, $shop);
        }
    }

    public function update(UnasProductCategory $productCategory): void
    {
        foreach ($productCategory->shopProducts as $shopProduct) {
            $this->unasProductRepository->update($shopProduct);
        }

        foreach ($productCategory->childCategories as $subCategory) {
            $this->update($subCategory);
        }

        $productCategory->changed = true;
        $productCategory->save();
    }

    private function createShopCategory(UnasShop $shop, array $path)
    {
    }
}
