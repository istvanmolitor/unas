<?php

namespace Molitor\Unas\Filament\Resources\UnasProductResource\Pages;

use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Collection;
use Molitor\Unas\Filament\Resources\UnasProductResource;
use Molitor\Unas\Models\UnasShop;
use Molitor\Unas\Repositories\UnasProductRepositoryInterface;
use Molitor\Unas\Repositories\UnasShopRepositoryInterface;
use Molitor\Unas\Services\UnasProductService;

class ListUnasProducts extends ListRecords
{
    protected static string $resource = UnasProductResource::class;

    public int|null $unasShopId = null;

    public function mount(): void
    {
        parent::mount();
        $this->unasShopId = request()->integer('shop_id');
    }

    public function getBreadcrumb(): string
    {
        return 'Lista';
    }

    public function getTitle(): string
    {
        if ($this->unasShopId) {
            /** @var UnasShopRepositoryInterface $unasShopRepository */
            $unasShopRepository = app(UnasShopRepositoryInterface::class);
            $shop = $unasShopRepository->getById($this->unasShopId);
            return "Termékek – {$shop->name}";
        }
        return 'UNAS termékek';
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('copy_all_products')
                ->label('Összes termék átmásolása')
                ->icon('heroicon-o-document-duplicate')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Összes termék átmásolása')
                ->modalDescription('Biztosan át szeretnéd másolni az összes terméket a termék törzsbe?')
                ->modalSubmitActionLabel('Átmásolás')
                ->action(function () {
                    /** @var UnasProductService $service */
                    $service = app(UnasProductService::class);

                    /** @var UnasProductRepositoryInterface $unasProductRepository */
                    $count = $service->copyAllProduct(UnasShop::findOrFail($this->unasShopId));

                    Notification::make()
                        ->title(__('unas::product.copying_in_progress', ['count' => $count]))
                        ->success()
                        ->send();
                })
                ->visible(fn () => $this->unasShopId !== null),
            CreateAction::make()->label('Új UNAS termék')->url(fn () => UnasProductResource::getUrl(
                'create',
                ['shop_id' => $this->unasShopId]
            ))->icon('heroicon-o-plus'),
        ];
    }

    public function table(Table $table): Table
    {
        $table = UnasProductResource::table($table)
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('copy_to_product')
                        ->label(__('unas::common.copy_to_product'))
                        ->icon('heroicon-o-document-duplicate')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading(__('unas::common.copy_to_product'))
                        ->modalDescription(__('unas::common.copy_to_products_confirm'))
                        ->modalSubmitActionLabel(__('unas::common.copy'))
                        ->action(function (Collection $records) {

                            /** @var UnasProductService $service */
                            $service = app(UnasProductService::class);

                            $records->each(function ($record) use ($service) {
                                $service->copyToProduct($record);
                            });

                            Notification::make()
                                ->title(__('unas::product.copying_in_progress', ['count' => $records->count()]))
                                ->success()
                                ->send();
                        }),
                    DeleteBulkAction::make(),
                ]),
            ]);

        if ($this->unasShopId) {
            $table->modifyQueryUsing(function ($query) {
                $query->where('unas_shop_id', $this->unasShopId);
            });
        }

        return $table;
    }
}
