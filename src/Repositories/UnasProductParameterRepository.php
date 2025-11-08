<?php

declare(strict_types=1);

namespace Molitor\Unas\Repositories;

use Molitor\Unas\Services\Endpoint;
use Molitor\Unas\Services\UnasService;
use Molitor\Product\Models\ProductField;
use Molitor\Unas\Models\UnasShop;
use Molitor\Unas\Models\UnasProductParameter;

class UnasProductParameterRepository implements UnasProductParameterRepositoryInterface
{
    private UnasProductParameter $shopProductParameter;

    public function __construct()
    {
        $this->shopProductParameter = new UnasProductParameter();
    }

    public function exists(UnasShop $shop, ProductField $productField): bool
    {
        return $this->shopProductParameter
                ->where('unas_shop_id', $shop->id)
                ->where('product_field_id', $productField->id)
                ->count() > 0;
    }

    public function addToShop(UnasShop $shop, ProductField $productField, $type = 'text'): bool
    {
        if (!$this->exists($shop, $productField)) {
            $this->shopProductParameter->create(
                [
                    'unas_shop_id' => $shop->id,
                    'product_field_id' => $productField->id,
                    'type' => $type,
                    'changed' => 1,
                ]
            );
            return true;
        }
        return false;
    }

    public function getRelevantProductFields(UnasShop $shop)
    {
        $subselect = \DB::table('product_fields AS pf')->join(
            'product_field_options',
            'product_field_options.product_field_id',
            '=',
            'pf.id'
        )
            ->join(
                'product_attributes',
                'product_attributes.product_field_option_id',
                '=',
                'product_field_options.id'
            )
            ->join('products', 'products.id', '=', 'product_attributes.product_id')
            ->join('unas_products', 'unas_products.product_id', '=', 'products.id')
            ->where('unas_products.unas_shop_id', $shop->id)
            ->groupBy('pf.id')
            ->select('pf.id');

        return ProductField::joinSub($subselect, 's', 's.id', '=', 'product_fields.id')->get();
    }

    public function createRelevantParameters(UnasShop $shop)
    {
        foreach ($this->getRelevantProductFields($shop) as $productField) {
            $this->addToShop($shop, $productField);
        }
    }

    public function deleteAll(UnasShop $shop): void
    {
        foreach($shop->shopProductParameters()->get() as $shopProductParameter)
        {
            $shopProductParameter->delete();
        }
    }

    public function getCountByShop(UnasShop $shop): int
    {
        return $shop->shopProductParameters()->count();
    }

    public function deleteByShop(UnasShop $shop): void
    {
        $this->shopProductParameter->where('unas_shop_id', $shop->id)->delete();
    }

    public function forceDeleteByShop(UnasShop $shop): void
    {
        $this->shopProductParameter->where('unas_shop_id', $shop->id)->forceDelete();
    }

    public function forceDeleteByRemoteId(int $id): bool
    {
        return $this->shopProductParameter->withTrashed()
            ->where('remote_id', $id)
            ->forceDelete();
    }
}
