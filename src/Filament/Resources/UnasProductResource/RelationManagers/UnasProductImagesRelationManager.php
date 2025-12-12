<?php

declare(strict_types=1);

namespace Molitor\Unas\Filament\Resources\UnasProductResource\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class UnasProductImagesRelationManager extends RelationManager
{
    protected static string $relationship = 'images';

    public static function getTitle(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): string
    {
        return __('Képek');
    }

    public static function schema(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\TextInput::make('url')
                    ->label(__('Kép URL'))
                    ->required()
                    ->maxLength(1024)
                    ->url()
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('alt')
                    ->label(__('Alt'))
                    ->maxLength(255),

                Forms\Components\TextInput::make('sort')
                    ->label(__('Sorrend'))
                    ->numeric()
                    // Optional: if left empty, model will auto-assign next sort; DB has default as safety
                    ,
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('url')
            ->columns([
                Tables\Columns\TextColumn::make('sort')
                    ->label(__('Sorrend'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('url')
                    ->label(__('Kép URL'))
                    ->limit(80)
                    ->searchable(),
                Tables\Columns\TextColumn::make('alt')
                    ->label(__('Alt'))
                    ->searchable(),
            ])
            ->defaultSort('sort', 'asc')
            ->reorderable('sort')
            ->headerActions([
                CreateAction::make(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }
}
