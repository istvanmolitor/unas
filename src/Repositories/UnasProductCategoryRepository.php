<?php

declare(strict_types=1);

namespace Molitor\Unas\Repositories;

use App\Services\TreeBuilder;
use Molitor\Unas\Services\Endpoint;
use Molitor\Unas\Models\UnasShop;
use Molitor\Unas\Models\UnasProductCategory as Category;
use Molitor\Unas\Models\UnasProductCategory;
use Illuminate\Database\Eloquent\Collection;

class UnasProductCategoryRepository implements UnasProductCategoryRepositoryInterface
{
    private Category $category;

    public function __construct()
    {
        $this->category = new Category();
    }

    public function getByShop(UnasShop $shop): Collection
    {
        return $this->category->where('unas_shop_id', $shop->id)->get();
    }

    public function getByShopWithRoot(UnasShop $shop): Collection
    {
        $categories = $this->getByShop($shop);
        $categories->prepend((object)[
            'id' => 0,
            'name' => 'Főkategória',
            'parent_id' => null,
        ]);
        return $categories;
    }

    public function getRootCategories(UnasShop $shop): Collection
    {
        return $shop->shopProductCategories()->where('parent_id', 0)->get();
    }

    private array $rootCategoryCache = [];

    public function getRootCategoryByName(UnasShop $shop, string $name): ?Category
    {
        if(!isset($this->rootCategoryCache[$shop->id][$name])) {
            $this->rootCategoryCache[$shop->id][$name] = $this->category
                ->where('unas_shop_id', $shop->id)
                ->where('parent_id', 0)
                ->where('name', $name)
                ->first();
        }
        return $this->rootCategoryCache[$shop->id][$name];
    }

    public function createRootCategory(UnasShop $shop, string $name): ?Category
    {
        $category = $this->getRootCategoryByName($shop, $name);
        if (!$category) {
            $category = $this->category->create(
                [
                    'unas_shop_id' => $shop->id,
                    'parent_id' => 0,
                    'name' => $name,
                    'changed' => 0,
                ]
            );
            $this->rootCategoryCache[$shop->id][$name] = $category;
        }
        if (!$category) {
            throw new \Exception('A gyökér kategóriát nem lehet létrehozni.');
        }

        return $category;
    }

    private array $subCategoryCache = [];

    public function getSubCategoryByName(Category $parent, string $name): ?Category
    {
        if(!isset($this->subCategoryCache[$parent->id][$name])) {
            $this->subCategoryCache[$parent->id][$name] = $this->category
                ->where('unas_shop_id', $parent->unas_shop_id)
                ->where('parent_id', $parent->id)
                ->where('name', $name)
                ->first();
        }
        return $this->subCategoryCache[$parent->id][$name];
    }

    public function createSubCategory(Category $parent, string $name): ?Category
    {
        $category = $this->getSubCategoryByName($parent, $name);
        if (!$category) {
            $category = $this->category->create(
                [
                    'unas_shop_id' => $parent->unas_shop_id,
                    'parent_id' => $parent->id,
                    'name' => $name,
                    'changed' => 0,
                ]
            );
            $this->subCategoryCache[$parent->id][$name] = $category;
        }
        if (!$category) {
            throw new \Exception('A kategóriát nem lehet létrehozni.');
        }

        return $category;
    }

    public function getPathCategories(Category $category): array
    {
        $path = $category->parent ? $this->getPathCategories($category->parent) : [];
        $path[] = $category;
        return $path;
    }

    public function categoryNameExists(UnasShop $shop, string $name): bool
    {
        return $this->category->where('unas_shop_id', $shop->id)->where('name', $name)->count() > 0;
    }

    public function delete(Category $category): void
    {
        $category->delete();
    }

    public function deleteByShop(UnasShop $shop): void
    {
        foreach ($this->getRootCategories($shop) as $category) {
            $this->delete($category);
        }
    }

    public function getDeletedCategories(UnasShop $shop): Collection
    {
        return $this->category->onlyTrashed()
            ->where('unas_shop_id', $shop->id)
            ->whereDoesntHave('shopProducts')
            ->whereNotNull('remote_id')
            ->get();
    }

    public function getAll()
    {
    }

    private function insertRepairCategories(TreeBuilder $treeBuilder, UnasShop $shop, int $parentId, $remoteId)
    {
        $resultCategory = $treeBuilder->get($remoteId);
        if ($resultCategory) {
            $category = $this->category->create(
                [
                    'unas_shop_id' => $shop->id,
                    'parent_id' => $parentId,
                    'name' => $resultCategory['Name'],
                    'remote_id' => $remoteId,
                ]
            );
            if ($category) {
                foreach ($treeBuilder->getChildIds($remoteId) as $subRemoteId) {
                    $this->insertRepairCategories($treeBuilder, $shop, $category->id, $subRemoteId);
                }
            }
        }
    }

    public function findByRemoteId(UnasShop $shop, int $remoteId): ?UnasProductCategory
    {
        return $this->category->where('unas_shop_id', $shop->id)->where('remote_id', $remoteId)->first();
    }

    public function downloadImage(UnasProductCategory $shopProductCategory)
    {
        $url = $shopProductCategory->image_url;
        if (!empty($url) && $shopProductCategory->file_id === null) {
            $file = (new FileRepository())->storeUrl($url);
            if ($file) {
                $shopProductCategory->file_id = $file->id;
                $shopProductCategory->save();
            }
        }
    }

    public function getCountByShop(UnasShop $shop): int
    {
        return $shop->shopProductCategories()->count();
    }

    public function forceDeleteByShop(UnasShop $shop): void
    {
        $this->category->where('unas_shop_id', $shop->id)->forceDelete();
    }

    public function isChildOf(UnasProductCategory $parent, UnasProductCategory $category): bool
    {
        if($parent->id === $category->id) {
            return false;
        }

        if($parent->id === $category->parent_id) {
            return true;
        }

        $category = $category->parent;
        if(!$category) {
            return false;
        }

        return $this->isChildOf($parent, $category);
    }

    public function getChangedByShop(UnasShop $shop): Collection
    {
        return $this->category->where('changed', 1)->orWhereNull('remote_id')->get();
    }

    public function forceDeleteByRemoteId(int $id): bool
    {
        return $this->category->withTrashed()
            ->where('remote_id', $id)
            ->forceDelete();
    }
}
