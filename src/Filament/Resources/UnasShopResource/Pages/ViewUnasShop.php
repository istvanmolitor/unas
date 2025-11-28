<?php

namespace Molitor\Unas\Filament\Resources\UnasShopResource\Pages;

use Filament\Actions\EditAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\IconEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Schema;
use Molitor\Unas\Filament\Resources\UnasProductResource;
use Molitor\Unas\Filament\Resources\UnasShopResource;

class ViewUnasShop extends ViewRecord
{
    protected static string $resource = UnasShopResource::class;

    public function getTitle(): string
    {
        return 'UNAS bolt megtekintése';
    }

    public function getBreadcrumb(): string
    {
        return 'Megtekintés';
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->label('Szerkesztés'),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Fieldset::make('Alapadatok')
                    ->schema([
                        IconEntry::make('enabled')
                            ->label('Engedélyezve')
                            ->boolean(),
                        TextEntry::make('name')
                            ->label('Név'),
                        TextEntry::make('domain')
                            ->label('Domain')
                            ->copyable(),
                        TextEntry::make('api_key')
                            ->label('API kulcs')
                            ->copyable()
                            ->columnSpanFull(),
                        TextEntry::make('warehouse.name')
                            ->label(__('unas::common.warehouse')),
                    ])
                    ->columns(2),
                Fieldset::make('Statisztikák')
                    ->schema([
                        TextEntry::make('shopProductCategories_count')
                            ->label('Termékkategóriák száma')
                            ->state(fn ($record) => $record->shopProductCategories()->count()),
                        TextEntry::make('shopProducts_count')
                            ->label('Termékek száma')
                            ->state(fn ($record) => $record->shopProducts()->count())
                            ->url(fn ($record) => UnasProductResource::getUrl('index', ['shop_id' => $record->getKey()]))
                            ->color('primary'),
                        TextEntry::make('shopProductParameters_count')
                            ->label('Termék paraméterek száma')
                            ->state(fn ($record) => $record->shopProductParameters()->count()),
                    ])
                    ->columns(3),
                Fieldset::make('Időbélyegek')
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Létrehozva')
                            ->dateTime(),
                        TextEntry::make('updated_at')
                            ->label('Frissítve')
                            ->dateTime(),
                    ])
                    ->columns(2),
            ]);
    }
}

