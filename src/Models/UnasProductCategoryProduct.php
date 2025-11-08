<?php

declare(strict_types=1);

namespace Molitor\Unas\Models;

use Illuminate\Database\Eloquent\Model;

class UnasProductCategoryProduct extends Model
{
    protected $fillable = [
        'unas_product_id',
        'unas_product_category_id',
    ];

    public $timestamps = false;
}
