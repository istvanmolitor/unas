<?php

namespace Molitor\Unas\Filament\Resources\UnasProductCategoryResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Molitor\Unas\Filament\Resources\UnasProductCategoryResource;

class CreateUnasProductCategory extends CreateRecord
{
    protected static string $resource = UnasProductCategoryResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // If shop_id is provided in URL and not already set in form, use it
        if (!isset($data['unas_shop_id']) && request()->has('shop_id')) {
            $data['unas_shop_id'] = request()->integer('shop_id');
        }

        return $data;
    }
}
