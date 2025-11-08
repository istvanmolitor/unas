<?php

namespace Molitor\Unas\Filament\Resources\UnasProductCategoryResource\Pages;

use Filament\Resources\Pages\EditRecord;
use Molitor\Unas\Filament\Resources\UnasProductCategoryResource;

class EditUnasProductCategory extends EditRecord
{
    protected static string $resource = UnasProductCategoryResource::class;

    public function getTitle(): string
    {
        return 'Kategória szerkesztése';
    }

    public function getBreadcrumb(): string
    {
        return 'Szerkesztés';
    }
}
