<?php

namespace Molitor\Unas\Filament\Actions;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Molitor\Unas\Models\UnasProduct;
use Molitor\Unas\Services\UnasProductService;

class CopyUnasProductToProductAction
{
    public static function make(): Action
    {
        return Action::make('copy_to_product')
            ->label(__('unas::common.copy_to_product'))
            ->icon('heroicon-o-document-duplicate')
            ->action(function (UnasProduct $record) {

                /** @var UnasProductService $service */
                $service = app(UnasProductService::class);
                $service->copyToProduct($record);

                Notification::make()
                    ->title(__('unas::common.sync_product'))
                    ->body(__('unas::product.sync_successfully', ['sku' => $record->sku]))
                    ->warning()
                    ->send();
            });
    }
}
