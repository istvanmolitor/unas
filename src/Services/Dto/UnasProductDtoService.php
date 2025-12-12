<?php

namespace Molitor\Unas\Services\Dto;

use Molitor\Product\Dto\ProductDto;
use Molitor\Product\Services\Dto\ProductUnitDtoService;
use Molitor\Unas\Models\UnasProduct;
use Molitor\Unas\Models\UnasProductImage;
use Molitor\Unas\Models\UnasShop;
use Molitor\Unas\Repositories\UnasProductRepositoryInterface;

class UnasProductDtoService
{
    public function __construct(
        protected UnasProductRepositoryInterface $unasProductRepository,
        protected ProductUnitDtoService $productUnitDtoService,
    )
    {
    }

    public function makeDto(UnasProduct $unasProduct): ProductDto
    {
        $productDto = new ProductDto();
        $productDto->id = $unasProduct->id;
        $productDto->source = 'unas';
        $productDto->sku = $unasProduct->sku;
        $productDto->productUnit = $this->productUnitDtoService->makeDto($unasProduct->productUnit);
        $productDto->name = $unasProduct->getAttributeDto('name');
        $productDto->price = $unasProduct->price;
        $productDto->currency = 'HUF';
        return $productDto;
    }

    public function saveDto(UnasShop $unasShop, ProductDto $productDto): UnasProduct
    {
        $unasProduct = $this->makeModel($unasShop, $productDto);

        if($productDto->source === 'unas_api' and $productDto->id) {
            $unasProduct->remote_id = $productDto->id;
        }
        $this->fillModel($unasProduct, $productDto);
        $unasProduct->save();

        // Sync images after product has an ID
        $this->syncImages($unasProduct, $productDto);
        return $unasProduct;
    }

    public function makeModel(UnasShop $unasShop, ProductDto $productDto): UnasProduct
    {
        $unasProduct = $this->unasProductRepository->getBySku($unasShop, $productDto->sku);
        if($unasProduct) {
            return $unasProduct;
        }
        $unasProduct = new UnasProduct();
        $unasProduct->unas_shop_id = $unasShop->id;
        $unasProduct->sku = $productDto->sku;
        return $unasProduct;
    }

    public function fillModel(UnasProduct $unasProduct, ProductDto $productDto): void
    {
        $unasProduct->sku = $productDto->sku;
        $unasProduct->setAttributeDto('name', $productDto->name);
        $unasProduct->setAttributeDto('description', $productDto->description);
        $unasProduct->product_unit_id = $this->productUnitDtoService->saveDto($productDto->productUnit)->id;
        $unasProduct->price = $productDto->price;
        $unasProduct->stock = $productDto->stock;
        $unasProduct->changed = false;
    }

    protected function syncImages(UnasProduct $unasProduct, ProductDto $productDto): void
    {
        // Collect DTO image URLs preserving order
        $dtoImages = $productDto->getImages();
        $dtoUrls = [];

        // Index existing images by URL for quick lookup
        $existing = $unasProduct->images()->get()->keyBy('url');

        foreach ($dtoImages as $sort => $imageDto) {
            $url = trim($imageDto->url ?? '');
            if ($url === '') {
                continue;
            }
            $dtoUrls[] = $url;

            $alt = '';
            // Prefer Hungarian alt if available, otherwise first available translation
            if (method_exists($imageDto->alt, 'has') && $imageDto->alt->has('hu')) {
                $alt = $imageDto->alt->get('hu');
            } elseif (method_exists($imageDto->alt, 'getTranslations')) {
                $translations = $imageDto->alt->getTranslations();
                if (!empty($translations)) {
                    $alt = reset($translations) ?: '';
                }
            }

            /** @var UnasProductImage|null $existingImage */
            $existingImage = $existing->get($url);
            if ($existingImage) {
                // Update sort and alt if changed
                $existingImage->sort = $sort;
                $existingImage->alt = $alt ?: $existingImage->alt;
                $existingImage->save();
            } else {
                $unasProduct->images()->create([
                    'url' => $url,
                    'sort' => $sort,
                    'alt' => $alt,
                ]);
            }
        }

        // Remove images that are no longer present in DTO
        if (count($dtoUrls)) {
            $unasProduct->images()->whereNotIn('url', $dtoUrls)->delete();
        } else {
            // If no images provided, clear all
            $unasProduct->images()->delete();
        }
    }
}
