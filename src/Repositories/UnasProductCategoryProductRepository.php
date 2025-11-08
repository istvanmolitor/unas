<?php

namespace Molitor\Unas\Repositories;

use Molitor\Unas\Models\UnasShop;
use Molitor\Unas\Models\UnasProduct;
use Molitor\Unas\Models\UnasProductCategory;
use Molitor\Unas\Models\UnasProductCategoryProduct;

class UnasProductCategoryProductRepository implements UnasProductCategoryProductRepositoryInterface
{
    private UnasProductCategoryProduct $unasProductCategoryProduct;

    public function __construct()
    {
        $this->unasProductCategoryProduct = new UnasProductCategoryProduct();
    }

    public function exists(UnasProductCategory $productCategory, UnasProduct $product):bool
    {
        return $this->unasProductCategoryProduct
            ->where('unas_product_category_id', $productCategory->id)
            ->where('unas_product_id', $product->id)
            ->exists();
    }

    public function delete(UnasProductCategory $productCategory, UnasProduct $product): bool
    {
        return $this->unasProductCategoryProduct
                ->where('unas_product_category_id', $productCategory->id)
                ->where('unas_product_id', $product->id)
                ->delete() > 0;
    }

    public function setValue(UnasProductCategory $productCategory, UnasProduct $product, $value): bool
    {
        if ($value) {
            return $this->unasProductCategoryProduct->firstOrCreate(
                        [
                            'unas_product_category_id' => $productCategory->id,
                            'unas_product_id' => $product->id,
                        ]
                    ) instanceof UnasProductCategoryProduct;
        } else {
            return $this->delete($productCategory, $product);
        }
    }

    public function deleteByShop(UnasShop $shop): bool
    {
        return $this->unasProductCategoryProduct->join(
            'unas_products',
            'unas_products.id',
            '=',
            'unas_product_category_products.unas_product_id'
        )
            ->where('unas_products.unas_shop_id', $shop->id)
            ->delete();
    }

    public function getProductCategoryIdsByProduct(UnasProduct $product): array
    {
        return $this->unasProductCategoryProduct->where('unas_product_id', $product->id)->pluck('unas_product_category_id')->toArray();
    }

    public function deleteByCategory(UnasProductCategory $productCategory): bool
    {
        return $this->unasProductCategoryProduct->where('unas_product_category_id', $productCategory->id)->delete();
    }

    public function deleteByProduct(UnasProduct $product): bool
    {
        return $this->unasProductCategoryProduct->where('unas_product_id', $product->id)->delete();
    }

    public function setProductCategories(UnasProduct $product, array $productCategoryIds): void
    {
        foreach ($product->shop->shopProductCategories as $productCategory) {
            $this->setValue($productCategory, $product, in_array($productCategory->id, $productCategoryIds));
        }
    }
}