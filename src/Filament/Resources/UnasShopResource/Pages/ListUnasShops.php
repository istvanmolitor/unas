<?php

namespace Molitor\Unas\Filament\Resources\UnasShopResource\Pages;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;
use Molitor\Unas\Filament\Resources\UnasProductResource;
use Molitor\Unas\Filament\Resources\UnasShopResource;

class ListUnasShops extends ListRecords
{
    protected static string $resource = UnasShopResource::class;

    public function getBreadcrumb(): string
    {
        return 'Lista';
    }

    public function getTitle(): string
    {
        return 'UNAS boltok';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Új UNAS bolt')
                ->icon('heroicon-o-plus'),
        ];
    }

    public function table(Table $table): Table
    {
        return UnasShopResource::table($table)
            ->actions([
                Action::make('products')
                    ->label('Termékek')
                    ->icon('heroicon-o-cube')
                    ->url(function ($record) {
                        return 'unas-products?shop_id=' . $record->getKey();
                    }),
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
