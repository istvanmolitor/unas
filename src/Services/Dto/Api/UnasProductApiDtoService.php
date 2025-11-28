<?php

namespace Molitor\Unas\Services\Dto\Api;

use Closure;
use Molitor\Product\Dto\ImageDto;
use Molitor\Product\Dto\ProductAttributeDto;
use Molitor\Product\Dto\ProductCategoryDto;
use Molitor\Product\Dto\ProductDto;
use Molitor\Product\Dto\ProductFieldDto;
use Molitor\Product\Dto\ProductFieldOptionDto;
use Molitor\Unas\Models\UnasShop;
use Molitor\Unas\Services\UnasService;

class UnasProductApiDtoService extends UnasService
{
    public function getProductDtoByRemoteId(UnasShop $shop, int $remoteId): ProductDto|null
    {
        $endpoint = $this->makeGetProductEndpoint($shop->api_key);
        $endpoint->setIdRequestData($remoteId);
        $endpoint->execute();
        $products = $endpoint->getResultProducts();
        if (!count($products)) {
            return null;
        }
        return $this->makeProductDto($products[0]);
    }

    public function getProductDtoBySku(UnasShop $shop, string $sku): ProductDto|null
    {
        $endpoint = $this->makeGetProductEndpoint($shop->api_key);
        $endpoint->setSkuRequestData($sku);
        $endpoint->execute();
        $products = $endpoint->getResultProducts();
        if (!count($products)) {
            return null;
        }
        return $this->makeProductDto($products[0]);
    }

    public function eachProducts(UnasShop $shop, Closure $product): array
    {
        $endpoint = $this->makeGetProductEndpoint($shop->api_key);

        $products = [];

        for ($i = 1; $i <= 3; $i++) {
            $endpoint->setRequestData(
                [
                    'StatusBase' => $i,
                    'ContentType' => 'full',
                ]
            );
            $endpoint->execute();
            foreach ($endpoint->getResultProducts() as $resultProduct) {
                $productDto = $this->makeProductDto($resultProduct);
                $product((int)$resultProduct['Id'], $productDto);
            }
        }

        return $products;
    }

    protected function makeProductDto(array $result): ProductDto
    {
        $defaultLanguage = 'hu';

        $name = $result['Name'] ?? null;

        $productDto = new ProductDto();
        $productDto->id = (int)$result['Id'];
        $productDto->source = 'unas_api';
        $productDto->active = ($result['State'] == 'live');
        $productDto->sku = $result['Sku'];
        $productDto->slug = $result['SefUrl'] ?? null;
        $productDto->url = $result['Url'] ?? null;
        $productDto->name->set($defaultLanguage, $name);
        $productDto->description->set($defaultLanguage, $result['AutomaticMeta']['Description'] ?? null);
        $productDto->currency = 'HUF';
        $productDto->weight = $result['Weight'] ?? null;
        $productDto->productUnit->name->set($defaultLanguage, $result['Unit'] ?? null);
        $productDto->price = $this->getPriceByProduct($result);
        $productDto->stock = $result['Stocks']['Stock']['Qty'] ?? null;

        foreach($this->getResultImagesByProduct($result) as $image)
        {
            $imageDto = new ImageDto();
            $imageDto->url = $image['SefUrl'];
            $imageDto->alt->set($defaultLanguage, $image['Alt'] ?? null);
            $imageDto->title->set($defaultLanguage, $name);
            $productDto->addImage($imageDto);
        }

        foreach ($this->getResultCategoriesByProduct($result) as $category)
        {
            $productCategoryDto = new ProductCategoryDto();
            $productCategoryDto->id = $category['Id'];
            $productCategoryDto->path->separator = '|';
            $productCategoryDto->path->setPath($defaultLanguage, $category['Name'] ?? null);
            $productCategoryDto->path->separator = '/';
            $productDto->addCategory($productCategoryDto);
        }

        $sort = 0;
        foreach ($this->getResultParametersByProduct($result) as $parameter)
        {
            if($parameter['Name'] && $parameter['Value']) {
                $productFieldDto = new ProductFieldDto();
                $productFieldDto->name->set($defaultLanguage, $parameter['Name']);

                $productFieldOptionDto = new ProductFieldOptionDto();
                $productFieldOptionDto->name->set($defaultLanguage, $parameter['Value']);

                $attribute = new ProductAttributeDto($productFieldDto, $productFieldOptionDto);
                $attribute->sort = $sort++;

                $productDto->addAttribute($attribute);
            }
        }

        return $productDto;
    }

    protected function getResultCategoriesByProduct(array $resultProduct): array
    {
        if (isset($resultProduct['Categories'])) {
            if (isset($resultProduct['Categories']['Category'][0])) {
                return $resultProduct['Categories']['Category'];
            } else {
                return [$resultProduct['Categories']['Category']];
            }
        }
        return [];
    }

    protected function getResultParametersByProduct(array $resultProduct): array
    {
        if (isset($resultProduct['Params'])) {
            if (isset($resultProduct['Params']['Param'][0])) {
                return $resultProduct['Params']['Param'];
            } else {
                return [$resultProduct['Params']['Param']];
            }
        }
        return [];
    }

    protected function getPriceByProduct(array $resultProduct): ?float
    {
        if (isset($resultProduct['Prices'])) {
            if (isset($resultProduct['Prices']['Price']['Net'])) {
                return (float)$resultProduct['Prices']['Price']['Net'];
            } else {
                foreach ($resultProduct['Prices']['Price'] as $price) {
                    if ($price['Type'] === 'normal') {
                        return (float)$price['Net'];
                    }
                }
            }
        }
        return null;
    }

    protected function getResultImagesByProduct(array $resultProduct): array
    {
        if (isset($resultProduct['Images'])) {
            if (isset($resultProduct['Images']['Image'][0])) {
                return $resultProduct['Images']['Image'];
            } else {
                return [$resultProduct['Images']['Image']];
            }
        }
        return [];
    }
}
