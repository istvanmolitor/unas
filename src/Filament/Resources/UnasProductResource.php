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
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Gate;
use Molitor\Language\Filament\Components\TranslatableFields;
use Molitor\Language\Repositories\LanguageRepositoryInterface;
use Molitor\Product\Repositories\ProductFieldOptionRepositoryInterface;
use Molitor\Product\Repositories\ProductFieldRepositoryInterface;
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
        /** @var ProductFieldRepositoryInterface $productFieldRepository */
        $productFieldRepository = app(ProductFieldRepositoryInterface::class);
        /** @var ProductFieldOptionRepositoryInterface $productFieldOptionRepository */
        $productFieldOptionRepository = app(ProductFieldOptionRepositoryInterface::class);
        /** @var LanguageRepositoryInterface $languageRepository */
        $languageRepository = app(LanguageRepositoryInterface::class);

        return $schema->components([
            Tabs::make('Tabs')
                ->tabs([
                    Tabs\Tab::make('Alapadatok')
                        ->schema([
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
                            Forms\Components\TextInput::make('stock')
                                ->label(__('unas::common.stock'))
                                ->numeric(),
                            Forms\Components\Select::make('product_unit_id')
                                ->label(__('unas::common.product_unit'))
                                ->options($productUnitRepository->getOptions())
                                ->default($productUnitRepository->getDefaultId())
                                ->searchable()
                                ->preload()
                                ->required(),
                        ]),
                    Tabs\Tab::make('Tulajdonságok')
                        ->schema([
                            Forms\Components\Repeater::make('unas_product_attributes_form')
                                ->label(__('unas::common.product_attributes'))
                                ->dehydrated(false)
                                ->orderColumn('sort')
                                ->default([])
                                ->schema([
                                    Forms\Components\Select::make('product_field_id')
                                        ->label(__('unas::common.product_field'))
                                        ->options($productFieldRepository->getOptions())
                                        ->searchable()
                                        ->preload()
                                        ->reactive()
                                        ->required(),
                                    Forms\Components\Select::make('product_field_option_id')
                                        ->label(__('unas::common.product_field_option'))
                                        ->options(function ($get) use ($productFieldOptionRepository) {
                                            $fieldId = $get('product_field_id');
                                            if (!$fieldId) {
                                                return [];
                                            }
                                            return $productFieldOptionRepository->getOptionsByProductFieldId((int)$fieldId);
                                        })
                                        ->searchable()
                                        ->preload()
                                        ->required()
                                        ->disabled(fn($get) => empty($get('product_field_id'))),
                                ])->columns(2),
                        ]),
                    Tabs\Tab::make(__('unas::common.product_images'))->schema([
                        Forms\Components\Repeater::make('unasProductImages')
                            ->label(__('unas::common.image_data'))
                            ->relationship('unasProductImages')
                            ->orderColumn('sort')
                            ->reorderable()
                            ->schema([
                                Forms\Components\Toggle::make('is_main')
                                    ->label('Főkép')
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, $set, $get) {
                                        if ($state) {
                                            $unasProductImages = $get('../../unasProductImages') ?? [];
                                            foreach ($unasProductImages as $index => $image) {
                                                if (array_key_exists('is_main', $image) && $image['is_main'] && $index != array_search($get(), $unasProductImages)) {
                                                    $set("../../unasProductImages.{$index}.is_main", false);
                                                }
                                            }
                                        }
                                    }),
                                Grid::make(3)->schema([
                                    Group::make([
                                        Forms\Components\TextInput::make('image_url')
                                            ->label('Kép URL')
                                            ->url()
                                            ->required()
                                            ->maxLength(1024)
                                            ->columnSpanFull(),
                                    ])->columnSpan(1)->gap(1),
                                    Group::make([
                                        Forms\Components\Repeater::make('translations')
                                            ->default(fn () => [
                                                ['language_id' => $languageRepository->getDefaultId()],
                                            ])
                                            ->label(__('unas::common.translations'))
                                            ->relationship('translations')
                                            ->schema([
                                                Forms\Components\Select::make('language_id')
                                                    ->label(__('unas::common.language'))
                                                    ->relationship(name: 'language', titleAttribute: 'code')
                                                    ->default($languageRepository->getDefaultId())
                                                    ->searchable()
                                                    ->preload()
                                                    ->required(),
                                                Forms\Components\TextInput::make('title')
                                                    ->label('Cím')
                                                    ->maxLength(255),
                                                Forms\Components\TextInput::make('alt')
                                                    ->label('Alt szöveg')
                                                    ->maxLength(255),
                                            ])->columns(2),
                                    ])->columnSpan(2)->gap(2),
                                ]),
                            ])->columns(1),
                    ]),
                ])
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
                Tables\Columns\TextColumn::make('translation.name')
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
                Tables\Filters\SelectFilter::make('unas_shop_id')
                    ->relationship('shop', 'name')
                    ->label(__('unas::common.shop'))
                    ->searchable()
                    ->preload(),
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

    public static function getRelations(): array
    {
        return [
            // UnasProductImagesRelationManager::class, // Now using tab instead
        ];
    }
}
