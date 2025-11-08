<?php

namespace Molitor\Unas\Filament\Resources\UnasProductResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Molitor\Unas\Filament\Resources\UnasProductResource;

class CreateUnasProduct extends CreateRecord
{
    protected static string $resource = UnasProductResource::class;

    public function getBreadcrumb(): string
    {
        return 'Új';
    }

    public function getTitle(): string
    {
        return 'UNAS termék létrehozása';
    }
}
