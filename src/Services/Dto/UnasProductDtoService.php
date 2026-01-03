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

        // Sync parameters after product has an ID
        $this->syncParameters($unasProduct, $productDto);

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
        $dtoImages = $productDto->getImages();

        $existingImages = $unasProduct->images()->get();
        $existingUrls = $existingImages->pluck('image_url')->toArray();
        $dtoUrls = array_map(fn($image) => $image->url, $dtoImages);

        $urlsToDelete = array_diff($existingUrls, $dtoUrls);
        if (!empty($urlsToDelete)) {
            $unasProduct->images()->whereIn('image_url', $urlsToDelete)->delete();
        }

        foreach ($dtoImages as $index => $imageDto) {
            $unasProductImage = $existingImages->firstWhere('image_url', $imageDto->url);
            if(!$unasProductImage) {
                $unasProductImage = new UnasProductImage();
                $unasProductImage->unas_product_id = $unasProduct->id;
                $unasProductImage->image_url = $imageDto->url;
            }

            $unasProductImage->sort = $index;
            $unasProductImage->alt = $imageDto->alt;
            $unasProductImage->save();
        }
    }

    protected function syncParameters(UnasProduct $unasProduct, ProductDto $productDto): void
    {

    }
}
