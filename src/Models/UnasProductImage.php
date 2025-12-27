<?php

declare(strict_types=1);

namespace Molitor\Unas\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Molitor\Language\Models\TranslatableModel;

class UnasProductImage extends TranslatableModel
{
    protected $fillable = [
        'unas_product_id',
        'image_url',
        'is_main',
        'sort',
    ];

    public function getTranslationModelClass(): string
    {
        return UnasProductImageTranslation::class;
    }

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

    public function unasProduct(): BelongsTo
    {
        return $this->belongsTo(UnasProduct::class, 'unas_product_id');
    }

    public function getSrc(): string|null
    {
        return $this->image_url;
    }

    public function __toString(): string
    {
        return $this->alt ?? $this->image_url ?? '';
    }
}
