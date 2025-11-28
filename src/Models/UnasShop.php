<?php

declare(strict_types=1);

namespace Molitor\Unas\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Molitor\Stock\Models\Warehouse;

class UnasShop extends Model
{
    protected $fillable = [
        'enabled',
        'domain',
        'name',
        'api_key',
        'warehouse_id',
    ];

    public function shopProductCategories(): HasMany
    {
        return $this->hasMany(UnasProductCategory::class, 'unas_shop_id');
    }

    public function shopProducts(): HasMany
    {
        return $this->hasMany(UnasProduct::class, 'unas_shop_id');
    }

    public function shopProductParameters(): HasMany
    {
        return $this->hasMany(UnasProductParameter::class, 'unas_shop_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
