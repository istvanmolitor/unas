<?php

namespace Molitor\Unas\Services\Dto\Api;

use Molitor\Product\Dto\ImageDto;
use Molitor\Product\Dto\ProductCategoryDto;
use Molitor\Unas\Models\UnasProductCategory;
use Molitor\Unas\Models\UnasShop;
use Molitor\Unas\Repositories\UnasProductCategoryRepositoryInterface;
use Molitor\Unas\Services\CategoryTreeBuilder;
use Molitor\Unas\Services\Dto\UnasProductCategoryDtoService;
use Molitor\Unas\Services\UnasService;

class UnasProductCategoryApiDtoService extends UnasService
{
    public function __construct(
        protected UnasProductCategoryRepositoryInterface $unasProductCategoryRepository,
        protected UnasProductCategoryDtoService $categoryDtoService,
    )
    {
    }

    public function fetchCategoriesFromApi(UnasShop $shop): array
    {
        $endpoint = $this->makeGetCategoryEndpoint($shop->api_key);
        $endpoint->execute();

        $categories = [];
        foreach ($endpoint->getResultCategories() as $resultCategory) {
            $categories[] = $this->makeProductCategoryDto($resultCategory);
        }

        return $categories;
    }

    public function getProductCategoryDtoByRemoteId(UnasShop $shop, int $remoteId): ?ProductCategoryDto
    {
        $endpoint = $this->makeGetCategoryEndpoint($shop->api_key);
        $endpoint->setIdRequestData($remoteId);
        $endpoint->execute();

        $categories = $endpoint->getResultCategories();
        if(!count($categories)) {
            return null;
        }

        return $this->makeProductCategoryDto($categories[0]);
    }

    /**
     * Create DTO from API response
     */
    public function makeProductCategoryDto(array $apiData): ProductCategoryDto
    {
        $dto = new ProductCategoryDto();
        $dto->id = (int)$apiData['Id'];
        $dto->source = 'unas_api';

        // Set the category name/path
        if (isset($apiData['Name'])) {
            $dto->path->addItem()->set('hu', $apiData['Name']);
        }

        // Set description from AutomaticMeta
        if (isset($apiData['AutomaticMeta']['Description'])) {
            $dto->description->set('hu', $apiData['AutomaticMeta']['Description']);
        }

        // Set image if available
        if (isset($apiData['Image']['Url'])) {
            $imageDto = new ImageDto();
            $imageDto->url = $apiData['Image']['Url'];
            $dto->image = $imageDto;
        }

        return $dto;
    }


    /**
     * Sync categories from API response using DTOs
     */
    public function syncFromApi(UnasShop $shop): void
    {
        $this->unasProductCategoryRepository->forceDeleteByShop($shop);

        $endpoint = $this->makeGetCategoryEndpoint($shop->api_key);
        $endpoint->execute();

        $treeBuilder = new CategoryTreeBuilder();
        foreach ($endpoint->getResultCategories() as $resultCategory) {
            $treeBuilder->add((int)$resultCategory['Id'], (int)$resultCategory['Parent']['Id'], $resultCategory);
        }

        foreach ($treeBuilder->getChildrenIds(0) as $id) {
            $this->createCategoryFromTree($shop, $treeBuilder, null, $id);
        }
    }

    /**
     * Recursively create categories from tree builder
     */
    protected function createCategoryFromTree(UnasShop $shop, CategoryTreeBuilder $treeBuilder, ?UnasProductCategory $parent, int $id): void
    {
        $item = $treeBuilder->getItem($id);
        $dto = $this->makeProductCategoryDto($item);

        // Create category
        if ($parent === null) {
            $category = $this->unasProductCategoryRepository->createRootCategory($shop, $item['Name']);
        } else {
            $category = $this->unasProductCategoryRepository->createSubCategory($parent, $item['Name']);
        }

        if (!$category) {
            return;
        }

        // Fill from DTO using the categoryDtoService
        $this->categoryDtoService->fillModel($category, $dto);

        // Also fill additional fields from API
        $category->title = $item['AutomaticMeta']['Title'] ?? '';
        $category->keywords = $item['AutomaticMeta']['Keywords'] ?? '';
        $category->display_page = ($item['Display']['Page'] ?? 'no') === 'yes';
        $category->display_menu = ($item['Display']['Menu'] ?? 'no') === 'yes';
        $category->remote_id = (int)$item['Id'];
        $category->save();

        // Process children
        foreach ($treeBuilder->getChildrenIds($id) as $childId) {
            $this->createCategoryFromTree($shop, $treeBuilder, $category, $childId);
        }
    }

    /**
     * Sync deletes to API
     */
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

    /**
     * Sync changes to API using DTOs
     */
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
            $dto = $this->categoryDtoService->makeDto($productCategory);
            $requestCategory = $this->dtoToApiRequest($dto, $productCategory);

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

    /**
     * Convert DTO to API request format
     */
    protected function dtoToApiRequest(ProductCategoryDto $dto, UnasProductCategory $category): array
    {
        $request = [
            'Name' => $category->name,
            'Display' => [
                'Page' => $this->getBooleanString($category->display_page),
                'Menu' => $this->getBooleanString($category->display_menu),
            ],
        ];

        if ($dto->image && $dto->image->url) {
            $request['Image']['Url'] = $dto->image->url;
            $request['Image']['OG'] = $dto->image->url;
        }

        return $request;
    }
}
