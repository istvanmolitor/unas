<?php

declare(strict_types=1);

namespace Molitor\Unas\Models;

use Illuminate\Database\Eloquent\Model;

class UnasProductImage extends Model
{
    protected $fillable = [
        'unas_product_id',
        'url',
        'sort',
        'alt',
    ];

    protected static function booted(): void
    {
        static::creating(function (UnasProductImage $image) {
            // If sort is not provided, assign the next index within the same product
            if ($image->sort === null && $image->unas_product_id) {
                $max = self::where('unas_product_id', $image->unas_product_id)->max('sort');
                $image->sort = ($max === null) ? 0 : ($max + 1);
            }
        });
    }

    public function shopProducts()
    {
        return $this->hasMany(UnasProduct::class, 'unas_shop_id');
    }

    public function __toString(): string
    {
        return $this->alt;
    }
}
