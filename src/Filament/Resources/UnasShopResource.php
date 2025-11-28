<?php

namespace Molitor\Unas\Filament\Resources;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Gate;
use Molitor\Unas\Filament\Resources\UnasShopResource\Pages;
use Molitor\Unas\Models\UnasShop;

class UnasShopResource extends Resource
{
    protected static ?string $model = UnasShop::class;

    protected static \BackedEnum|null|string $navigationIcon = 'heroicon-o-building-storefront';

    public static function getNavigationGroup(): string
    {
        return __('unas::common.unas');
    }

    public static function getNavigationLabel(): string
    {
        return __('unas::common.unas_shops');
    }

    public static function canAccess(): bool
    {
        return Gate::allows('acl', 'unas');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Forms\Components\Toggle::make('enabled')
                ->label(__('unas::common.enabled'))
                ->default(true),
            Forms\Components\TextInput::make('name')
                ->label(__('unas::common.name'))
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('domain')
                ->label(__('unas::common.domain'))
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('api_key')
                ->label(__('unas::common.api_key'))
                ->required()
                ->maxLength(255),
            Forms\Components\Select::make('warehouse_id')
                ->label(__('unas::common.warehouse'))
                ->relationship('warehouse', 'name')
                ->required()
                ->searchable()
                ->preload(),
        ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\IconColumn::make('enabled')
                    ->boolean()
                    ->label(__('unas::common.active')),
                Tables\Columns\TextColumn::make('name')
                    ->label(__('unas::common.name'))
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('domain')
                    ->label(__('unas::common.domain'))
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUnasShops::route('/'),
            'create' => Pages\CreateUnasShop::route('/create'),
            'view' => Pages\ViewUnasShop::route('/{record}'),
            'edit' => Pages\EditUnasShop::route('/{record}/edit'),
        ];
    }
}
