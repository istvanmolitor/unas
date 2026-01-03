<?php

namespace Molitor\Unas\Filament\Resources;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Gate;
use Molitor\Unas\Filament\Resources\UnasProductCategoryResource\Pages;
use Molitor\Unas\Models\UnasProductCategory;

class UnasProductCategoryResource extends Resource
{
    protected static ?string $model = UnasProductCategory::class;

    protected static \BackedEnum|null|string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static bool $shouldRegisterNavigation = false;

    public static function getNavigationGroup(): string
    {
        return __('unas::common.unas');
    }

    public static function getNavigationLabel(): string
    {
        return __('unas::common.unas_categories');
    }

    public static function canAccess(): bool
    {
        return Gate::allows('acl', 'unas');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(2)->schema([
                Forms\Components\Select::make('unas_shop_id')
                    ->relationship('shop', 'name')
                    ->label(__('unas::common.shop'))
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('parent_id')
                    ->relationship('parent', 'name')
                    ->label(__('unas::common.parent_category'))
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->default(0),
            ]),
            Forms\Components\TextInput::make('name')
                ->label(__('unas::common.name'))
                ->required()
                ->maxLength(255),
            Grid::make(2)->schema([
                Forms\Components\Toggle::make('display_page')
                    ->label(__('unas::common.display_page'))
                    ->default(true),
                Forms\Components\Toggle::make('display_menu')
                    ->label(__('unas::common.display_menu'))
                    ->default(true),
            ]),
            Forms\Components\TextInput::make('title')
                ->label(__('unas::common.title'))
                ->maxLength(255),
            Forms\Components\TextInput::make('keywords')
                ->label(__('unas::common.keywords'))
                ->maxLength(255),
            Forms\Components\Textarea::make('description')
                ->label(__('unas::common.description'))
                ->rows(3)
                ->columnSpanFull(),
        ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('shop.name')
                    ->label(__('unas::common.shop'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label(__('unas::common.name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('parent.name')
                    ->label(__('unas::common.parent_category'))
                    ->default('-')
                    ->sortable(),
                Tables\Columns\IconColumn::make('display_page')
                    ->boolean()
                    ->label(__('unas::common.on_page')),
                Tables\Columns\IconColumn::make('display_menu')
                    ->boolean()
                    ->label(__('unas::common.in_menu')),
            ])
            ->filters([
            ])
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUnasProductCategories::route('/'),
            'create' => Pages\CreateUnasProductCategory::route('/create'),
            'edit' => Pages\EditUnasProductCategory::route('/{record}/edit'),
        ];
    }
}
