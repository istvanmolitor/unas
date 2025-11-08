<?php

namespace Molitor\Unas\Filament\Resources\UnasProductResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Molitor\Unas\Filament\Actions\UnasProductActions;
use Molitor\Unas\Filament\Resources\UnasProductResource;

class ViewUnasProduct extends ViewRecord
{
    protected static string $resource = UnasProductResource::class;

    public function getBreadcrumb(): string
    {
        return 'Megtekintés';
    }

    public function getTitle(): string
    {
        return 'UNAS termék megtekintése';
    }

    protected function getHeaderActions(): array
    {
        return [
            UnasProductActions::make(),
            Actions\EditAction::make(),
        ];
    }
}
