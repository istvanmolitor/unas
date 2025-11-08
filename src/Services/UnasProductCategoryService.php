<?php

namespace Molitor\Unas\Services;

use Molitor\Customer\Models\Customer;
use Molitor\Product\Models\ProductCategory;
use Molitor\Product\Repositories\ProductCategoryRepository;
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
        $this->unasProductCategoryRepository->forceDeleteByShop($shop);

        $endpoint = $this->makeGetCategoryEndpoint($shop->api_key);
        $endpoint->execute();

        $treeBuilder = new CategoryTreeBuilder();
        foreach ($endpoint->getResultCategories() as $resultCategory) {
            $treeBuilder->add((int)$resultCategory['Id'], (int)$resultCategory['Parent']['Id'], $resultCategory);
        }

        foreach ($treeBuilder->getChildrenIds(0) as $id) {
            $item = $treeBuilder->getItem($id);
            $rootCategory = $this->unasProductCategoryRepository->createRootCategory($shop, $item['Name']);
            if($rootCategory) {
                $this->fillUnasProductCategoryByItem($rootCategory, $item);
                foreach ($treeBuilder->getChildrenIds($id) as $childrenId) {
                    $this->createCategory($treeBuilder, $rootCategory, $childrenId);
                }
            }
        }
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

    private function createCategory(CategoryTreeBuilder $treeBuilder, UnasProductCategory $productCategory, int $id): void
    {
        $item = $treeBuilder->getItem($id);
        $subCategory = $this->unasProductCategoryRepository->createSubCategory($productCategory, $item['Name']);
        if($subCategory) {
            $this->fillUnasProductCategoryByItem($subCategory, $item);
            foreach ($treeBuilder->getChildrenIds($id) as $childrenId) {
                $this->createCategory($treeBuilder, $subCategory, $childrenId);
            }
        }
    }

    private function fillUnasProductCategoryByItem(UnasProductCategory $category, array $item): void
    {
        $category->title = $item['AutomaticMeta']['Title'];
        $category->keywords = $item['AutomaticMeta']['Keywords'];
        $category->description = $item['AutomaticMeta']['Description'];
        $category->display_page = $item['Display']['Page'] === 'yes';
        $category->display_menu = $item['Display']['Menu'] === 'yes';
        $category->remote_id = (int)$item['Id'];
        $category->save();
    }

    public function syncDeletes(UnasShop $shop): int
    {
        $productCategories = $this->unasProductCategoryRepository->getDeletedCategories($shop);
        if ($productCategories->count() == 0) {
            return 0;
        }

        $endpoint = $this->makeSetCategoryEndpoint($shop->api_key);

        $requestData = [];
        foreach ($productCategories as $productCategory) {
            $requestData['@Category'][] = [
                'Action' => self::ACTION_DELETE,
                'Id' => $productCategory->remote_id,
            ];
        }

        $endpoint->setRequestData($requestData);
        $endpoint->execute();

        $i = 0;
        foreach ($endpoint->getResultCategories() as $resultCategory) {
            if ($resultCategory['Status'] === self::STATUS_OK) {
                $this->unasProductCategoryRepository->forceDeleteByRemoteId($resultCategory['Id']);
                $i++;
            }
        }

        if ($i > 0) {
            $this->syncDeletes($shop);
        }

        return $i;
    }

    public function syncChanges(UnasShop $shop): bool
    {
        $productCategories = $this->unasProductCategoryRepository->getChangedByShop($shop);

        if ($productCategories->count() == 0) {
            return false;
        }

        $endpoint = $this->makeSetCategoryEndpoint($shop->api_key);

        $requestData = [];

        /** @var UnasProductCategory $productCategory */
        foreach ($productCategories as $productCategory) {
            $requestCategory = [
                'Name' => $productCategory->name,
                'Display' => [
                    'Page' => $this->getBooleanString($productCategory->display_page),
                    'Menu' => $this->getBooleanString($productCategory->display_menu),
                ],
            ];

            if ($productCategory->image_url) {
                $requestCategory['Image']['Url'] = $productCategory->image_url;
                $requestCategory['Image']['OG'] = $productCategory->image_url;
            }

            if ($productCategory->remote_id) {
                $requestCategory['Action'] = self::ACTION_UPDATE;
                $requestCategory['Id'] = $productCategory->remote_id;
            } else {
                $requestCategory['Action'] = self::ACTION_CREATE;
            }

            $parent = $productCategory->parent;
            if ($parent) {
                $requestCategory['Parent']['Id'] = $parent->remote_id;
            }

            $requestData['@Category'][] = $requestCategory;
        }

        $endpoint->setRequestData($requestData);
        $endpoint->execute();

        $resultCategories = $endpoint->getResultCategories();

        foreach ($productCategories as $i => $productCategory) {
            if (isset($resultCategories[$i])) {
                $resultCategory = $resultCategories[$i];
                if ($resultCategory['Status'] == self::STATUS_OK) {
                    if ($resultCategory['Action'] == self::ACTION_CREATE) {
                        $productCategory->remote_id = $resultCategory['Id'];
                    }
                    $productCategory->changed = 0;
                    $productCategory->save();
                }
            }
        }

        return true;
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
}
