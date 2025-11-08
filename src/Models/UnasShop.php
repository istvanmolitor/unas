<?php

declare(strict_types=1);

namespace Molitor\Unas\Models;

use Illuminate\Database\Eloquent\Model;

class UnasShop extends Model
{
    protected $fillable = [
        'enabled',
        'domain',
        'name',
        'api_key',
    ];

    public function shopProductCategories()
    {
        return $this->hasMany(UnasProductCategory::class, 'unas_shop_id');
    }

    public function shopProducts()
    {
        return $this->hasMany(UnasProduct::class, 'unas_shop_id');
    }

    public function shopProductParameters()
    {
        return $this->hasMany(UnasProductParameter::class, 'unas_shop_id');
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
