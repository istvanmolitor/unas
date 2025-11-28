<?php

namespace Molitor\Unas\Filament\Resources\UnasShopResource\Pages;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;
use Molitor\Unas\Filament\Resources\UnasProductResource;
use Molitor\Unas\Filament\Resources\UnasShopResource;

class ListUnasShops extends ListRecords
{
    protected static string $resource = UnasShopResource::class;

    public function getBreadcrumb(): string
    {
        return __('unas::common.list');
    }

    public function getTitle(): string
    {
        return __('unas::common.unas_shops');
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label(__('unas::common.new_unas_shop'))
                ->icon('heroicon-o-plus'),
        ];
    }

    public function table(Table $table): Table
    {
        return UnasShopResource::table($table)
            ->actions([
                Action::make('products')
                    ->label(__('unas::common.products'))
                    ->icon('heroicon-o-cube')
                    ->url(function ($record) {
                        return 'unas-products?shop_id=' . $record->getKey();
                    }),
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
