<?php

namespace Molitor\Unas\Repositories;

use Molitor\Unas\Models\UnasProduct;
use Molitor\Unas\Models\UnasProductImage;
use Illuminate\Database\Eloquent\Collection;

class UnasProductImageRepository implements UnasProductImageRepositoryInterface
{
    private UnasProductImage $productImage;

    public function __construct()
    {
        $this->productImage = new UnasProductImage();
    }

    public function getByProduct(UnasProduct $product): Collection
    {
        return $this->productImage->where('unas_product_id', $product->id)->orderBy('short')->get();
    }

    private function getNextShort(UnasProduct $product): int
    {
        return $this->productImage->where('unas_product_id', $product->id)->max('sort') + 1;
    }

    public function addUrl(UnasProduct $product, string $url, string $alt): UnasProductImage {
        return $this->productImage->create([
            'unas_product_id' => $product->id,
            'url' => $url,
            'sort' => $this->getNextShort($product),
            'alt' => $alt,
        ]);
    }
}