<?php

declare(strict_types=1);

namespace Molitor\Unas\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Molitor\Product\Models\ProductFieldOption;

class UnasProductAttribute extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'unas_product_id',
        'product_field_option_id',
        'sort',
    ];

    public function unasProduct(): BelongsTo
    {
        return $this->belongsTo(UnasProduct::class, 'unas_product_id');
    }

    public function productFieldOption(): BelongsTo
    {
        return $this->belongsTo(ProductFieldOption::class, 'product_field_option_id');
    }
}

