<?php

namespace Molitor\Unas\Services;

use Illuminate\Support\Facades\Gate;
use Molitor\Setting\Services\SettingForm;
use Filament\Forms\Components\Toggle;

class UnasSettingForm extends SettingForm
{

    public function getSlug(): string
    {
        return 'unas';
    }

    public function getForm(): array
    {
        return [
            Toggle::make('unas_enabled')->label('Engedélyezve')->default(true),
        ];
    }

    public function getLabel(): string
    {
        return 'UNAS';
    }

    public function canAccess(): bool
    {
        return parent::canAccess() && Gate::allows('acl', 'unas');
    }

    public function getFormFields(): array
    {
        return [
            'unas_enabled',
        ];
    }

    public function getDefaults(): array
    {
        return [
            'unas_enabled' => false,
        ];
    }
}
