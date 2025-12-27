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
use Molitor\Language\Repositories\LanguageRepositoryInterface;
use Molitor\Unas\Filament\Resources\UnasProductParameterResource\Pages;
use Molitor\Unas\Models\UnasProductParameter;

class UnasProductParameterResource extends Resource
{
    protected static ?string $model = UnasProductParameter::class;

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
        /** @var LanguageRepositoryInterface $languageRepository */
        $languageRepository = app(LanguageRepositoryInterface::class);

        return $schema->components([
            Forms\Components\Select::make('unas_shop_id')
                ->relationship('shop', 'name')
                ->label(__('unas::common.shop'))
                ->searchable()
                ->preload()
                ->required()
                ->disabled(fn (string $operation) => $operation === 'edit')
                ->dehydrated(fn (string $operation) => $operation !== 'edit'),
            Forms\Components\TextInput::make('name')
                ->label(__('unas::common.name'))
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('type')
                ->label(__('unas::parameter.type'))
                ->required()
                ->maxLength(255),
            Forms\Components\Select::make('language_id')
                ->label(__('unas::common.language'))
                ->relationship(name: 'language', titleAttribute: 'code')
                ->default($languageRepository->getDefaultId())
                ->searchable()
                ->preload()
                ->required(),
            Forms\Components\TextInput::make('order')
                ->label(__('unas::parameter.order'))
                ->numeric()
                ->default(0)
                ->required(),
            Forms\Components\TextInput::make('remote_id')
                ->label(__('unas::common.remote_id'))
                ->numeric()
                ->disabled(),
            Forms\Components\Toggle::make('changed')
                ->label(__('unas::common.changed'))
                ->disabled(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('unas::common.name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label(__('unas::parameter.type'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('shop.name')
                    ->label(__('unas::common.shop'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('language.code')
                    ->label(__('unas::common.language'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('order')
                    ->label(__('unas::parameter.order'))
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('unas_shop_id')
                    ->relationship('shop', 'name')
                    ->label(__('unas::common.shop'))
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('language_id')
                    ->relationship('language', 'code')
                    ->label(__('unas::common.language'))
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
            ])
            ->defaultSort('order', 'asc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUnasProductParameters::route('/'),
            'create' => Pages\CreateUnasProductParameter::route('/create'),
            'view' => Pages\ViewUnasProductParameter::route('/{record}'),
            'edit' => Pages\EditUnasProductParameter::route('/{record}/edit'),
        ];
    }

    public static function getRelations(): array
    {
        return [];
    }
}

