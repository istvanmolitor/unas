<?php

namespace Molitor\Unas\Filament\Actions;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Molitor\Unas\Models\UnasProduct;
use Molitor\Unas\Services\UnasProductService;

class SyncUnasProductAction
{
    public static function make(): Action
    {
        return Action::make('sync')
            ->label(__('unas::common.sync_product'))
            ->icon('heroicon-o-arrow-path')
            ->action(function (UnasProduct $record) {

                /** @var UnasProductService $service */
                $service = app(UnasProductService::class);
                $service->syncOneUnasProduct($record);

                Notification::make()
                    ->title(__('unas::common.sync_product'))
                    ->body(__('unas::product.sync_successfully', ['sku' => $record->sku]))
                    ->success()
                    ->send();
            });
    }
}
