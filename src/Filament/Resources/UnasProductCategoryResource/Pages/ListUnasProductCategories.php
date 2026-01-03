<?php

namespace Molitor\Unas\Filament\Resources\UnasProductCategoryResource\Pages;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;
use Molitor\Unas\Filament\Resources\UnasProductCategoryResource;
use Molitor\Unas\Repositories\UnasShopRepositoryInterface;

class ListUnasProductCategories extends ListRecords
{
    protected static string $resource = UnasProductCategoryResource::class;

    public int|null $unasShopId = null;

    public function mount(): void
    {
        parent::mount();
        $this->unasShopId = request()->integer('shop_id');
    }

    public function getBreadcrumb(): string
    {
        return __('unas::common.list');
    }

    public function getTitle(): string
    {
        if ($this->unasShopId) {
            /** @var UnasShopRepositoryInterface $unasShopRepository */
            $unasShopRepository = app(UnasShopRepositoryInterface::class);
            $shop = $unasShopRepository->getById($this->unasShopId);
            return __('unas::common.categories_shop', ['shop' => $shop->name]);
        }
        return __('unas::common.unas_categories');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('tree_view')
                ->label('Fa nézet')
                ->icon('heroicon-o-rectangle-group')
                ->url(route('filament.admin.pages.unas-product-categories-page', ['shop_id' => $this->unasShopId]))
                ->color('gray'),
            CreateAction::make()
                ->label(__('unas::common.new_category'))
                ->icon('heroicon-o-plus')
                ->url(fn () => UnasProductCategoryResource::getUrl(
                    'create',
                    ['shop_id' => $this->unasShopId]
                )),
        ];
    }

    public function table(Table $table): Table
    {
        $table = UnasProductCategoryResource::table($table)
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
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
