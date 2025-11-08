<?php

namespace Molitor\Unas\Models;

use Molitor\Language\Models\TranslationModel;

class UnasProductTranslation extends TranslationModel
{

    public function getTranslatableModelClass(): string
    {
        return UnasProduct::class;
    }

    public function getTranslationForeignKey(): string
    {
        return 'unas_product_id';
    }

    public function getTranslatableFields(): array
    {
        return [
            'name',
            'description',
        ];
    }
}
