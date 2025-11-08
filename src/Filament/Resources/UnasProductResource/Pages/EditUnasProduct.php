<?php

namespace Molitor\Unas\Filament\Resources\UnasProductResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Molitor\Unas\Filament\Actions\UnasProductActions;
use Molitor\Unas\Filament\Resources\UnasProductResource;

class EditUnasProduct extends EditRecord
{
    protected static string $resource = UnasProductResource::class;

    public function getBreadcrumb(): string
    {
        return 'Szerkesztés';
    }

    public function getTitle(): string
    {
        return 'UNAS termék szerkesztése';
    }

    protected function getHeaderActions(): array
    {
        return [
            UnasProductActions::make(),
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
