<?php

declare(strict_types=1);

namespace Molitor\Unas\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Molitor\Language\Models\TranslatableModel;
use Molitor\Product\Models\Product;
use Illuminate\Database\Eloquent\SoftDeletes;
use Molitor\Product\Models\ProductUnit;

class UnasProduct extends TranslatableModel
{
    use SoftDeletes;

    protected $fillable = [
        'sku',
        'unas_shop_id',
        'product_id',
        'sku',
        'product_unit_id',
        'price',
        'stock',
        'remote_id',
        'changed',
    ];

    public function getTranslationModelClass(): string
    {
        return UnasProductTranslation::class;
    }

    public function __toString(): string
    {
        return (string) $this->sku . ' - ' . $this->name;
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(UnasShop::class, 'unas_shop_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function shopProductCategories(): BelongsToMany
    {
        return $this->belongsToMany(UnasProductCategory::class, 'unas_product_category_products', 'unas_product_id', 'unas_product_category_id');
    }

    public function productUnit(): BelongsTo
    {
        return $this->belongsTo(ProductUnit::class, 'product_unit_id');
    }
}
