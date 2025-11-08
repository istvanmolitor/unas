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

    public function shopProducts()
    {
        return $this->hasMany(UnasProduct::class, 'unas_shop_id');
    }

    public function __toString(): string
    {
        return $this->alt;
    }
}
