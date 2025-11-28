<?php

namespace Molitor\Unas\Filament\Resources\UnasProductCategoryResource\Pages;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;
use Molitor\Unas\Filament\Resources\UnasProductCategoryResource;

class ListUnasProductCategories extends ListRecords
{
    protected static string $resource = UnasProductCategoryResource::class;

    public function getBreadcrumb(): string
    {
        return __('unas::common.list');
    }

    public function getTitle(): string
    {
        return __('unas::common.unas_categories');
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label(__('unas::common.new_category'))
                ->icon('heroicon-o-plus'),
        ];
    }

    public function table(Table $table): Table
    {
        return UnasProductCategoryResource::table($table)
            ->actions([
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
