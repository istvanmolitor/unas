<?php

declare(strict_types=1);

namespace Molitor\Unas\Models;

use Molitor\Product\Models\ProductField;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UnasProductParameter extends Model
{
    use SoftDeletes;


    protected $fillable = [
        'type',
        'unas_shop_id',
        'name',
        'type',
        'language_id',
        'order',
        'remote_id',
        'changed',
    ];

    public $timestamps = true;

    public function productField()
    {
        return $this->belongsTo(ProductField::class, 'product_field_id');
    }

    public function shop()
    {
        return $this->belongsTo(UnasShop::class, 'unas_shop_id');
    }

    public function language()
    {
        return $this->belongsTo(\Molitor\Language\Models\Language::class, 'language_id');
    }
}
