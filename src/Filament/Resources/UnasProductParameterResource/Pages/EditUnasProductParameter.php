<?php

namespace Molitor\Unas\Filament\Resources\UnasProductParameterResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Molitor\Unas\Filament\Resources\UnasProductParameterResource;

class EditUnasProductParameter extends EditRecord
{
    protected static string $resource = UnasProductParameterResource::class;

    public function getBreadcrumb(): string
    {
        return __('unas::common.edit');
    }

    public function getTitle(): string
    {
        return __('unas::parameter.edit_parameter');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}

