<?php

namespace Molitor\Unas\Services;

use Molitor\Product\Dto\ProductDto;
use Molitor\Product\Models\Product;
use Molitor\Product\Repositories\ProductUnitRepositoryInterface;
use Molitor\Product\Services\Dto\ProductDtoService;
use Molitor\Unas\Models\UnasProduct;
use Molitor\Unas\Models\UnasProductCategory;
use Molitor\Unas\Models\UnasShop;
use Molitor\Unas\Repositories\UnasProductCategoryRepositoryInterface;
use Molitor\Unas\Repositories\UnasProductImageRepositoryInterface;
use Molitor\Unas\Repositories\UnasProductRepositoryInterface;
use Molitor\Unas\Services\Dto\Api\UnasProductApiDtoService;
use Molitor\Unas\Services\Dto\UnasProductDtoService;

class UnasProductService extends UnasService
{
    public function __construct(
        private UnasProductCategoryService $unasProductCategoryService,
        private UnasProductRepositoryInterface $unasProductRepository,
        private UnasProductCategoryRepositoryInterface  $unasProductCategoryRepository,
        private UnasProductImageRepositoryInterface $unasProductImageRepository,
        private ProductUnitRepositoryInterface $productUnitRepository,

        private UnasProductApiDtoService $unasProductApiDtoService,
        private UnasProductDtoService $unasProductDtoService,
        private ProductDtoService $productDtoService,
    )
    {
    }

    public function syncOneUnasProduct(UnasProduct $unasProduct): void
    {
        if($unasProduct->remote_id) {
            $productDto = $this->unasProductApiDtoService->getProductDtoByRemoteId($unasProduct->shop, $unasProduct->remote_id);
        }
        else {
            $productDto = $this->unasProductApiDtoService->getProductDtoBySku($unasProduct->shop, $unasProduct->sku);
        }

        $this->unasProductDtoService->saveDto($unasProduct->shop, $productDto);
    }

    public function copyToProduct(UnasProduct $unasProduct): Product
    {
        $productDto = $this->unasProductDtoService->makeDto($unasProduct);
        $product = $this->productDtoService->saveDto($productDto);
        $unasProduct->product_id = $product->id;
        $unasProduct->save();
        return $product;
    }

    protected function getUnit(string $name): string
    {
        return $this->productUnitRepository->getByShortName($name);
    }

    public function copyAllProduct(UnasShop $shop): int
    {
        $products = $this->unasProductRepository->getShopProducts($shop);
        $products->each(function ($record) {
            $this->copyToProduct($record);
        });
        return $products->count();
    }

    private function findExistedProduct(UnasShop $unasShop, int $remoteId, ProductDto $productDto): UnasProduct|null
    {
        if($remoteId) {
            $unasProduct = $this->unasProductRepository->findByRemoteId($unasShop, $remoteId);
            if ($unasProduct) {
                return $unasProduct;
            }
        }

        if($productDto->sku) {
            $unasProduct = $this->unasProductRepository->getBySku($unasShop, $productDto->sku);
            if ($unasProduct && !$unasProduct->remote_id) {
                return $unasProduct;
            }
        }

        return null;
    }

    public function repairProducts(UnasShop $shop): int
    {
        $productUnit = $this->getUnit('db');

        $endpoint = $this->makeGetProductEndpoint($shop->api_key);

        $count = 0;

        for ($i = 1; $i <= 3; $i++) {
            $endpoint->setRequestData(
                [
                    'StatusBase' => $i,
                    'ContentType' => 'full',
                ]
            );
            $endpoint->execute();

            foreach ($endpoint->getResultProducts() as $resultProduct) {
                $product = $this->unasProductRepository->createProduct(
                    $shop,
                    (int)$resultProduct['Id'],
                    $resultProduct['Sku'],
                    $resultProduct['Name'],
                    $resultProduct['Description']['Long'] ?? null,
                    $endpoint->getPriceByProduct($resultProduct),
                    $productUnit
                );

                $count++;

                $resultCategories = $endpoint->getResultCategoriesByProduct($resultProduct);
                foreach ($resultCategories as $resultCategory) {
                    if (isset($resultCategory['Name'])) {
                        $shopCategory = $this->unasProductCategoryRepository->findByRemoteId(
                            $shop,
                            (int)$resultCategory['Id']
                        );
                        if (!$shopCategory) {
                            $path = explode('|', $resultCategory['Name']);
                            $shopCategory = $this->unasProductCategoryService->createByPath($shop, $path);
                        }
                        if ($shopCategory) {
                            $this->unasProductRepository->addShopProduct($product, $shopCategory);
                        }
                    }
                }

                $images = $endpoint->getResultImagesByProduct($resultProduct);
                foreach ($images as $image) {
                    $this->unasProductImageRepository->addUrl($product, $image['SefUrl'], $image['Alt']);;
                };
            }
        }

        return $count;
    }

    /**
     * Visszaadja az UNAS termékek számát amik még nem szerepelnek a saját törzsünkben
     * @param UnasShop $shop
     * @return int
     */
    public function getCountForeignByShop(UnasShop $shop): int
    {
        return $shop->shopProducts()->whereNull('product_id')->count();
    }

    public function clearShop(UnasShop $shop): void
    {

    }

    public function updateByCategory(UnasProductCategory $category): void
    {
        foreach ($category->shopProducts as $shopProduct) {
            $this->unasProductRepository->update($shopProduct);
        }
    }

    public function syncDeletes(UnasShop $shop): int
    {
        $shopProducts = $this->unasProductRepository->getDeletableByShop($shop);
        if ($shopProducts->count() == 0) {
            return 0;
        }

        $endpoint = $this->makeSetProductEndpoint($shop->api_key);

        $requestData = [];
        foreach ($shopProducts as $shopProduct) {
            $requestData['@Product'][] = [
                'Action' => self::ACTION_DELETE,
                'Id' => $shopProduct->remote_id,
            ];
        }

        $endpoint->setRequestData($requestData);
        $endpoint->execute();

        $i = 0;
        foreach ($endpoint->getResultProducts() as $resultProduct) {
            if ($resultProduct['Status'] == self::STATUS_OK) {
                $i++;
                $this->unasProductRepository->forceDeleteByRemoteId($resultProduct['Id']);
            }
        }
        return $i;
    }

    private function getParameters(UnasProduct $shopProduct): array
    {
        return [];
        $sql = "
        SELECT spp.remote_id AS id, pfo.name AS value
        FROM products p
        INNER JOIN unas_products sp ON sp.product_id = p.id
        INNER JOIN product_attributes pfv ON pfv.product_id = p.id
        INNER JOIN product_field_options pfo ON pfo.id = pfv.product_field_option_id
        INNER JOIN product_fields pf ON pf.id = pfo.product_field_id
        INNER JOIN unas_product_parameters spp ON spp.product_field_id = pf.id
        WHERE sp.id = ? AND sp.remote_id IS NOT NULL AND spp.remote_id IS NOT NULL
        ";

        $parameterValues = DB::select($sql, [$shopProduct->id]);

        $parameters = [];
        foreach ($parameterValues as $parameterValue) {
            $parameters[] = [
                'Id' => $parameterValue->id,
                'Type' => 'enum',
                'Value' => $parameterValue->value,
            ];
        }

        return $parameters;
    }

    public function syncChanges(UnasShop $shop): int
    {
        $shopProducts = $this->unasProductRepository->getChangedByShop($shop);

        if ($shopProducts->count() == 0) {
            return 0;
        }

        $validShopProducts = [];
        $requestProducts = [];

        foreach ($shopProducts as $product) {

            $categoriesData = [];
            foreach ($product->shopProductCategories as $productCategory) {
                if ($productCategory->remote_id) {
                    $categoriesData[] = [
                        'Type' => ($product->remote_id ? 'alt' : 'base'),
                        'Id' => $productCategory->remote_id,
                        'Name' => implode('|', $this->unasProductCategoryService->getPath($productCategory)),
                    ];
                }
            }

            if (count($categoriesData)) {
                $validShopProducts[] = $product;
                $requestProduct = [
                    'Action' => ($product->remote_id ? self::ACTION_UPDATE : self::ACTION_CREATE),
                    'Name' => $product->name,
                    'Unit' => 'db',
                    'Description' => [
                        'Short' => '',
                        'Long' => $product->description,
                    ],
                    'Prices' => [
                        '@Price' => [
                            [
                                'Type' => 'normal',
                                'Net' => (int)$product->price,
                                'Gross' => (int)($product->price * 1.27),
                            ],
                        ],
                    ],
                    'Categories' => [
                        '@Category' => $categoriesData,
                    ],
                ];
                if ($product->remote_id) {
                    $requestProduct['Id'] = $product->remote_id;
                } else {
                    $requestProduct['Sku'] = $product->sku;
                }

                $images = [];
                foreach ($product->productImages as $i => $productImage) {
                    $images[] = [
                        'Type' => ($i ? 'alt' : 'base'),
                        'SefUrl' => basename($productImage->url),
                        'Import' => [
                            'Url' => $productImage->url
                        ],
                    ];
                }
                if (count($images)) {
                    $requestProduct['Images']['@Image'] = $images;
                }


                $parameters = $this->getParameters($product);
                if (count($parameters)) {
                    $requestProduct['Params']['@Param'] = $parameters;
                }

                $requestProducts[] = $requestProduct;
            }
        }

        if (!count($validShopProducts)) {
            return 0;
        }

        $endpoint = $this->makeSetProductEndpoint($shop->api_key);

        $endpoint->setRequestData(
            [
                '@Product' => $requestProducts,
            ]
        );
        $endpoint->execute();
        $resultProducts = $endpoint->getResultProducts();

        $i = 0;
        foreach ($validShopProducts as $i => $shopProduct) {
            if (!isset($resultProducts[$i])) {
                return $i;
            }
            $resultProduct = $resultProducts[$i];
            if ($resultProduct['Status'] == self::STATUS_OK) {
                if ($resultProduct['Action'] == self::ACTION_CREATE) {
                    $shopProduct->remote_id = $resultProduct['Id'];
                }
                $shopProduct->changed = 0;
                $shopProduct->save();
                $i++;
            } else {
                return $i;
            }
        }
        return $i;
    }
}
