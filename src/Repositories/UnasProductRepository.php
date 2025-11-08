<?php

declare(strict_types=1);

namespace Molitor\Unas\Repositories;

use Molitor\Currency\Repositories\CurrencyRepositoryInterface;
use Molitor\Product\Models\ProductUnit;
use Molitor\Product\Repositories\ProductRepository;
use Molitor\Unas\Services\Endpoint;
use Molitor\Product\Models\Product;
use Molitor\Unas\Models\UnasShop;
use Molitor\Unas\Models\UnasProduct;
use Molitor\Unas\Models\UnasProductCategory;
use Molitor\Unas\Models\UnasProductCategoryProduct;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Collection;

class UnasProductRepository implements UnasProductRepositoryInterface
{
    private UnasProduct $shopProduct;

    public function __construct(
        private CurrencyRepositoryInterface $currencyRepository
    )
    {
        $this->shopProduct = new UnasProduct();
    }

    public function getShopProducts(UnasShop $shop): Collection
    {
        return $this->shopProduct
            ->where('unas_shop_id', $shop->id)
            ->get();
    }

    public function skuExists(UnasShop $shop, string $sku): bool
    {
        return $this->shopProduct
                ->where('unas_shop_id', $shop->id)
                ->where('sku', $sku)
                ->count() > 0;
    }

    public function createSku(UnasShop $shop, string $sku): string
    {
        $baseSku = Str::slug($sku);
        $newSku = $baseSku;

        $i = 2;
        while ($this->skuExists($shop, $newSku)) {
            $newSku = $baseSku . '-' . $i;
            $i++;
        }
        return $newSku;
    }

    public function findByRemoteId(UnasShop $unasShop, ?int $remoteId): ?UnasProduct
    {
        if ($remoteId === null) {
            return null;
        }
        return $this->shopProduct->where('unas_shop_id', $unasShop->id)->where('remote_id', $remoteId)->first();
    }

    public function createProduct(
        UnasShop    $shop,
        int         $remoteId,
        string      $sku,
        string      $name,
        ?string     $description,
        ?float      $price,
        ProductUnit $productUnit
    ): UnasProduct
    {
        $product = $this->findByRemoteId($shop, $remoteId);
        if ($product) {
            $product->sku = $sku;
            $product->name = $name;
            $product->description = $description;
            $product->product_unit_id = $productUnit->id;
            $product->price = $price;
            $product->save();
        } else {
            $product = $this->shopProduct->create(
                [
                    'unas_shop_id' => $shop->id,
                    'product_id' => null,
                    'sku' => $this->createSku($shop, $sku),
                    'name' => $name,
                    'description' => $description,
                    'product_unit_id' => $productUnit->id,
                    'price' => $price,
                    'remote_id' => $remoteId,
                    'changed' => false,
                ]
            );
        }
        return $product;
    }

    public function addShopProduct(UnasProduct $shopProduct, UnasProductCategory $shopProductCategory): UnasProduct
    {
        $shopProduct->changed = true;
        $shopProduct->save();

        $exists = UnasProductCategoryProduct::where('unas_product_id', $shopProduct->id)
            ->where('unas_product_category_id', $shopProductCategory->id)
            ->count();

        if (!$exists) {
            UnasProductCategoryProduct::create(
                [
                    'unas_product_id' => $shopProduct->id,
                    'unas_product_category_id' => $shopProductCategory->id,
                ]
            );
        }

        return $shopProduct;
    }

    public function update(UnasProduct $product): void
    {
        $product->changed = true;
        $product->save();
    }

    public function deleteShopProductFromTheShop(UnasProduct $shopProduct): void
    {
        UnasProductCategoryProduct::where('unas_product_id', $shopProduct->id)->delete();
        $shopProduct->delete();
    }

    public function deleteProduct(Product $product, UnasProductCategory $shopProductCategory): bool
    {
        $shopProduct = $this->getShopProduct($shopProductCategory->shop, $product);

        UnasProductCategoryProduct::where('unas_product_id', $shopProduct->id)
            ->where('unas_product_category_id', $shopProductCategory->id)
            ->delete();

        $count = UnasProductCategoryProduct::where('unas_product_id', $shopProduct->id)->count();
        if (!$count) {
            $shopProduct->delete();
            return true;
        }
        return false;
    }

    public function deleteByShop(UnasShop $shop): self
    {
        UnasProductCategoryProduct::join(
            'unas_products',
            'unas_products.id',
            '=',
            'unas_product_category_products.unas_product_id'
        )
            ->where('unas_products.unas_shop_id', $shop->id)
            ->delete();
        $shop->shopProducts()->delete();
        return $this;
    }

    public function getCountByShop(UnasShop $shop): int
    {
        return $shop->shopProducts()->count();
    }

    public function copyProduct(UnasProduct $shopProduct): ?Product
    {
        if ($shopProduct->product_id) {
            return $shopProduct->product;
        }

        $currency = $this->currencyRepository->getByCode('HUF');

        $productRepository = new ProductRepository();
        $product = $productRepository->save(
            (string)$shopProduct->sku,
            (string)$shopProduct->name,
            $shopProduct->description,
            $shopProduct->price,
            $currency,
            $shopProduct->productUnit
        );

        return $product;
    }

    /**
     * Törli a termékeket amik nincsenek a törzsben.
     * @param UnasShop $shop
     * @return void
     */
    public function clearShop(UnasShop $shop): void
    {
        $shopProducts = $this->shopProduct
            ->where('unas_shop_id', $shop->id)
            ->whereNull('product_id')
            ->get();

        /** @var UnasProduct $shopProduct */
        foreach ($shopProducts as $shopProduct) {
            $this->deleteShopProductFromTheShop($shopProduct);
        }
    }

    public function forceDeleteByShop(UnasShop $shop): void
    {
        $this->shopProduct->where('unas_shop_id', $shop->id)->forceDelete();
    }

    public function getChangedByShop(UnasShop $shop): Collection
    {
        return $this->shopProduct
            ->where('unas_shop_id', $shop->id)
            ->where('changed', 1)
            ->with('product')
            ->get();
    }

    public function getDeletableByShop(UnasShop $shop): Collection
    {
        return $this->shopProduct->where('unas_shop_id', $shop->id)->onlyTrashed()->get();
    }

    public function forceDeleteByRemoteId(int $id): bool
    {
        return $this->shopProduct->withTrashed()
            ->where('remote_id', $id)
            ->forceDelete();
    }

    public function getBySku(UnasShop $unasShop, string $sku): UnasProduct|null
    {
        return $this->shopProduct
            ->where('unas_shop_id', $unasShop->id)
            ->where('sku', $sku)
            ->first();
    }
}
