<?php

namespace Molitor\Unas\Services;

use Illuminate\Support\Facades\Gate;
use Molitor\Setting\Services\SettingForm;

class UnasSettingForm extends SettingForm
{
    public function getSlug(): string
    {
        return 'unas';
    }

    public function getLabel(): string
    {
        return 'UNAS';
    }

    public function getFields(): array
    {
        return [
            'unas_enabled',
        ];
    }

    public function getDefaultValues(): array
    {
        return [
            'unas_enabled' => false,
        ];
    }

    public function canAccess(): bool
    {
        return parent::canAccess() && Gate::allows('acl', 'unas');
    }
}
