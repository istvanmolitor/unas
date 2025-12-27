<?php

namespace Molitor\Unas\Filament\Resources\UnasProductParameterResource\Pages;

use Filament\Actions;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Schema;
use Molitor\Unas\Filament\Resources\UnasProductParameterResource;
use Molitor\Unas\Filament\Resources\UnasShopResource;

class ViewUnasProductParameter extends ViewRecord
{
    protected static string $resource = UnasProductParameterResource::class;

    public function getBreadcrumb(): string
    {
        return __('unas::common.view');
    }

    public function getTitle(): string
    {
        return __('unas::parameter.view_parameter');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Fieldset::make(__('unas::common.basic_info'))
                    ->schema([
                        TextEntry::make('name')
                            ->label(__('unas::common.name')),
                        TextEntry::make('type')
                            ->label(__('unas::parameter.type')),
                        TextEntry::make('shop.name')
                            ->label(__('unas::common.shop'))
                            ->url(fn ($record) => $record->shop ? UnasShopResource::getUrl('view', ['record' => $record->shop]) : null)
                            ->color('primary'),
                        TextEntry::make('language.code')
                            ->label(__('unas::common.language')),
                        TextEntry::make('order')
                            ->label(__('unas::parameter.order')),
                    ]),
                Fieldset::make(__('unas::common.technical_info'))
                    ->schema([
                        TextEntry::make('remote_id')
                            ->label(__('unas::common.remote_id'))
                            ->placeholder(__('unas::common.not_synced')),
                        TextEntry::make('changed')
                            ->label(__('unas::common.changed'))
                            ->formatStateUsing(fn ($state) => $state ? __('unas::common.yes') : __('unas::common.no')),
                    ]),
                Fieldset::make(__('unas::common.timestamps'))
                    ->schema([
                        TextEntry::make('created_at')
                            ->label(__('unas::common.created'))
                            ->dateTime(),
                        TextEntry::make('updated_at')
                            ->label(__('unas::common.updated'))
                            ->dateTime(),
                    ]),
            ]);
    }
}

