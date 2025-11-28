<?php

namespace Molitor\Unas\Filament\Resources\UnasProductResource\Pages;

use Filament\Actions;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;
use Molitor\Unas\Filament\Actions\UnasProductActions;
use Molitor\Unas\Filament\Resources\UnasProductResource;
use Molitor\Unas\Filament\Resources\UnasShopResource;

class ViewUnasProduct extends ViewRecord
{
    protected static string $resource = UnasProductResource::class;

    public function getBreadcrumb(): string
    {
        return __('unas::common.view');
    }

    public function getTitle(): string
    {
        return __('unas::common.view_unas_product');
    }

    protected function getHeaderActions(): array
    {
        return [
            UnasProductActions::make(),
            Actions\EditAction::make(),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Fieldset::make(__('unas::common.basic_info'))
                    ->schema([
                        TextEntry::make('sku')
                            ->label(__('unas::common.sku')),
                        TextEntry::make('shop.name')
                            ->label(__('unas::common.shop'))
                            ->url(fn ($record) => $record->shop ? UnasShopResource::getUrl('view', ['record' => $record->shop]) : null)
                            ->color('primary'),
                        TextEntry::make('product.sku')
                            ->label(__('unas::common.product'))
                            ->placeholder(__('unas::common.no_product_linked'))
                            ->url(fn ($record) => $record->product ? route('filament.admin.resources.products.view', ['record' => $record->product]) : null)
                            ->color('primary'),
                        TextEntry::make('productUnit')
                            ->label(__('unas::common.product_unit'))
                            ->state(fn ($record) => (string) $record->productUnit),
                    ])
                    ->columns(2),
                Fieldset::make(__('unas::common.pricing_and_stock'))
                    ->schema([
                        TextEntry::make('price')
                            ->label(__('unas::common.price'))
                            ->money(fn ($record) => $record->shop?->currency?->code ?? 'HUF'),
                        TextEntry::make('stock')
                            ->label(__('unas::common.stock'))
                            ->numeric()
                            ->suffix(fn ($record) => $record->productUnit ? ' ' . (string) $record->productUnit : ''),
                    ])
                    ->columns(2),
                Fieldset::make(__('unas::common.translations'))
                    ->schema([
                        TextEntry::make('translations')
                            ->label('')
                            ->state(function ($record) {
                                $translations = $record->translations ?? [];
                                if (empty($translations)) {
                                    return __('unas::common.no_translations');
                                }

                                $html = '<div class="space-y-4">';
                                foreach ($translations as $translation) {
                                    $languageCode = $translation->language?->code ?? 'N/A';
                                    $name = $translation->name ?? __('unas::common.no_name');
                                    $description = $translation->description ?? __('unas::common.no_description');

                                    $html .= '<div class="border rounded-lg p-4 dark:border-gray-700">';
                                    $html .= '<div class="font-bold text-lg mb-2">' . e($languageCode) . '</div>';
                                    $html .= '<div class="mb-2"><span class="font-semibold">' . __('unas::common.name') . ':</span> ' . e($name) . '</div>';
                                    $html .= '<div><span class="font-semibold">' . __('unas::common.description') . ':</span><div class="mt-1 prose dark:prose-invert max-w-none">' . $description . '</div></div>';
                                    $html .= '</div>';
                                }
                                $html .= '</div>';

                                return new HtmlString($html);
                            })
                            ->columnSpanFull(),
                    ]),
                Fieldset::make(__('unas::common.technical_info'))
                    ->schema([
                        TextEntry::make('remote_id')
                            ->label(__('unas::common.remote_id'))
                            ->placeholder(__('unas::common.not_synced')),
                        IconEntry::make('changed')
                            ->label(__('unas::common.changed'))
                            ->boolean(),
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
