<?php

namespace Molitor\Unas\Filament\Resources\UnasShopResource\Pages;

use Filament\Actions\EditAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\IconEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Schema;
use Molitor\Unas\Filament\Resources\UnasProductCategoryResource;
use Molitor\Unas\Filament\Resources\UnasProductResource;
use Molitor\Unas\Filament\Resources\UnasProductParameterResource;
use Molitor\Unas\Filament\Resources\UnasShopResource;

class ViewUnasShop extends ViewRecord
{
    protected static string $resource = UnasShopResource::class;

    public function getTitle(): string
    {
        return __('unas::common.view_unas_shop');
    }

    public function getBreadcrumb(): string
    {
        return __('unas::common.view_action');
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->label(__('unas::common.edit')),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Fieldset::make(__('unas::common.basic_data'))
                    ->schema([
                        IconEntry::make('enabled')
                            ->label(__('unas::common.enabled'))
                            ->boolean(),
                        TextEntry::make('name')
                            ->label(__('unas::common.name')),
                        TextEntry::make('domain')
                            ->label(__('unas::common.domain'))
                            ->copyable(),
                        TextEntry::make('api_key')
                            ->label(__('unas::common.api_key'))
                            ->copyable()
                            ->columnSpanFull(),
                        TextEntry::make('warehouse.name')
                            ->label(__('unas::common.warehouse')),
                    ])
                    ->columns(2),
                Fieldset::make(__('unas::common.statistics'))
                    ->schema([
                        TextEntry::make('shopProductCategories_count')
                            ->label(__('unas::common.product_categories_count'))
                            ->state(fn ($record) => $record->shopProductCategories()->count())
                            ->url(fn ($record) => UnasProductCategoryResource::getUrl('index', ['shop_id' => $record->getKey()]))
                            ->color('primary'),
                        TextEntry::make('shopProducts_count')
                            ->label(__('unas::common.products_count'))
                            ->state(fn ($record) => $record->shopProducts()->count())
                            ->url(fn ($record) => UnasProductResource::getUrl('index', ['shop_id' => $record->getKey()]))
                            ->color('primary'),
                        TextEntry::make('shopProductParameters_count')
                            ->label(__('unas::common.product_parameters_count'))
                            ->state(fn ($record) => $record->shopProductParameters()->count())
                            ->url(fn ($record) => UnasProductParameterResource::getUrl('index', ['shop_id' => $record->getKey()]))
                            ->color('primary'),
                    ])
                    ->columns(3),
                Fieldset::make(__('unas::common.timestamps'))
                    ->schema([
                        TextEntry::make('created_at')
                            ->label(__('unas::common.created'))
                            ->dateTime(),
                        TextEntry::make('updated_at')
                            ->label(__('unas::common.updated'))
                            ->dateTime(),
                    ])
                    ->columns(2),
            ]);
    }
}

