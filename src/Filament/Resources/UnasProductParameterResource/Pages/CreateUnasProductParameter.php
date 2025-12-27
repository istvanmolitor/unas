<?php

namespace Molitor\Unas\Filament\Resources\UnasProductParameterResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Molitor\Unas\Filament\Resources\UnasProductParameterResource;

class CreateUnasProductParameter extends CreateRecord
{
    protected static string $resource = UnasProductParameterResource::class;

    public function getBreadcrumb(): string
    {
        return 'Új';
    }

    public function getTitle(): string
    {
        return __('unas::parameter.create_parameter');
    }
}

