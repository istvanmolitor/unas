<?php

namespace Molitor\Unas\Filament\Actions;

use Filament\Actions\ActionGroup;

class UnasProductActions
{
    public static function make(): ActionGroup
    {
        return ActionGroup::make([
            SyncUnasProductAction::make(),
            CopyUnasProductToProductAction::make(),
        ])->label(__('unas::common.actions'))
        ->icon('heroicon-m-ellipsis-vertical')
        ->size('md')
        ->color('gray')
        ->button();
    }
}
