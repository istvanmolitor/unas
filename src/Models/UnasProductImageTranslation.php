<?php

declare(strict_types=1);

namespace Molitor\Unas\Models;

use Molitor\Language\Models\TranslationModel;

class UnasProductImageTranslation extends TranslationModel
{
    public function getTranslatableModelClass(): string
    {
        return UnasProductImage::class;
    }

    public function getTranslationForeignKey(): string
    {
        return 'unas_product_image_id';
    }

    public function getTranslatableFields(): array
    {
        return [
            'title',
            'alt',
        ];
    }
}

