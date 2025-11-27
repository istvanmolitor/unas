<?php

namespace Molitor\Unas\Filament\Resources;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Gate;
use Molitor\Language\Filament\Components\TranslatableFields;
use Molitor\Product\Repositories\ProductUnitRepositoryInterface;
use Molitor\Unas\Filament\Resources\UnasProductResource\Pages;
use Molitor\Unas\Models\UnasProduct;

class UnasProductResource extends Resource
{
    protected static ?string $model = UnasProduct::class;

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function canAccess(): bool
    {
        return Gate::allows('acl', 'unas');
    }

    public static function form(Schema $schema): Schema
    {
        /** @var ProductUnitRepositoryInterface $productUnitRepository */
        $productUnitRepository = app(ProductUnitRepositoryInterface::class);

        return $schema->components([
            Forms\Components\Select::make('unas_shop_id')
                ->relationship('shop', 'name')
                ->label(__('unas::common.shop'))
                ->searchable()
                ->preload()
                ->required()
                ->disabled(fn (string $operation) => $operation === 'edit')
                ->dehydrated(fn (string $operation) => $operation !== 'edit'),
            Forms\Components\TextInput::make('sku')
                ->label(__('unas::common.sku'))
                ->required()
                ->maxLength(255),
            Forms\Components\Select::make('product_id')
                ->label(__('unas::common.product'))
                ->relationship('product', 'sku')
                ->searchable()
                ->preload(),
            TranslatableFields::schema([
                Forms\Components\TextInput::make('name')
                    ->label(__('unas::common.name'))
                    ->required()
                    ->maxLength(255),
                Forms\Components\RichEditor::make('description')
                    ->label(__('unas::common.description'))
                    ->columnSpanFull(),
            ]),
            Forms\Components\TextInput::make('price')
                ->label(__('unas::common.price'))
                ->numeric(),
            Forms\Components\Select::make('product_unit_id')
                ->label(__('unas::common.product_unit'))
                ->options($productUnitRepository->getOptions())
                ->default($productUnitRepository->getDefaultId())
                ->searchable()
                ->preload()
                ->required(),
        ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sku')
                    ->label(__('unas::common.sku'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label(__('unas::common.name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('shop.name')
                    ->label(__('unas::common.shop'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('price')
                    ->label(__('unas::common.price'))
                    ->sortable(),
            ])
            ->filters([
            ])
            ->actions([
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUnasProducts::route('/'),
            'create' => Pages\CreateUnasProduct::route('/create'),
            'view' => Pages\ViewUnasProduct::route('/{record}'),
            'edit' => Pages\EditUnasProduct::route('/{record}/edit'),
        ];
    }
}
