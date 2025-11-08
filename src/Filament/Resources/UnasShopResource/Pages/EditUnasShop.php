<?php

namespace Molitor\Unas\Filament\Resources\UnasShopResource\Pages;

use Filament\Resources\Pages\EditRecord;
use Molitor\Unas\Filament\Resources\UnasShopResource;

class EditUnasShop extends EditRecord
{
    protected static string $resource = UnasShopResource::class;

    public function getTitle(): string
    {
        return 'UNAS bolt szerkesztése';
    }

    public function getBreadcrumb(): string
    {
        return 'Szerkesztés';
    }
}
