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

    public static function getNavigationGroup(): string
    {
        return 'UNAS';
    }

    public static function getNavigationLabel(): string
    {
        return 'UNAS kategóriák';
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
                    ->label('Bolt')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\TextInput::make('parent_id')
                    ->numeric()
                    ->default(0)
                    ->minValue(0)
                    ->label('Szülő kategória ID (0 = Főkategória)'),
            ]),
            Forms\Components\TextInput::make('name')
                ->label('Név')
                ->required()
                ->maxLength(255),
            Grid::make(2)->schema([
                Forms\Components\Toggle::make('display_page')
                    ->label('Megjelenítés oldalon')
                    ->default(true),
                Forms\Components\Toggle::make('display_menu')
                    ->label('Megjelenítés menüben')
                    ->default(true),
            ]),
            Forms\Components\TextInput::make('title')
                ->label('Cím')
                ->maxLength(255),
            Forms\Components\TextInput::make('keywords')
                ->label('Kulcsszavak')
                ->maxLength(255),
            Forms\Components\Textarea::make('description')
                ->label('Leírás')
                ->rows(3)
                ->columnSpanFull(),
        ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('shop.name')
                    ->label('Bolt')
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Név')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('display_page')
                    ->boolean()
                    ->label('Oldalon'),
                Tables\Columns\IconColumn::make('display_menu')
                    ->boolean()
                    ->label('Menüben'),
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
