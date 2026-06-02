<?php

namespace Molitor\Unas\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Molitor\Unas\Models\UnasProduct;
use Molitor\Unas\Models\UnasProductImage;

class UnasProductImageRepository implements UnasProductImageRepositoryInterface
{
    private UnasProductImage $productImage;

    public function __construct()
    {
        $this->productImage = new UnasProductImage;
    }

    public function getByProduct(UnasProduct $product): Collection
    {
        return $this->productImage->where('unas_product_id', $product->id)->orderBy('sort')->get();
    }

    private function getNextShort(UnasProduct $product): int
    {
        return $this->productImage->where('unas_product_id', $product->id)->max('sort') + 1;
    }

    public function addUrl(UnasProduct $product, string $url, string $alt): UnasProductImage
    {
        $isMain = ! $this->productImage->where('unas_product_id', $product->id)->exists();

        return $this->productImage->create([
            'unas_product_id' => $product->id,
            'image_url' => $url,
            'is_main' => $isMain,
            'sort' => $this->getNextShort($product),
            'alt' => $alt,
        ]);
    }

    public function create(UnasProduct $unasProduct, string $imageUrl, bool $isMain, ?int $sort): UnasProductImage
    {
        if ($isMain) {
            $unasProduct->images()->update(['is_main' => false]);
        }

        return $unasProduct->images()->create([
            'image_url' => $imageUrl,
            'is_main' => $isMain,
            'sort' => $sort,
        ]);
    }
}
