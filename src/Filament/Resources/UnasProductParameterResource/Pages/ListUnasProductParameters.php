<?php

namespace Molitor\Unas\Filament\Resources\UnasProductParameterResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Molitor\Unas\Filament\Resources\UnasProductParameterResource;
use Molitor\Unas\Repositories\UnasShopRepositoryInterface;

class ListUnasProductParameters extends ListRecords
{
    protected static string $resource = UnasProductParameterResource::class;

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
            return __('unas::parameter.parameters_shop', ['shop' => $shop->name]);
        }
        return __('unas::parameter.unas_product_parameters');
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getModelQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getModelQuery();

        if ($this->unasShopId) {
            $query->where('unas_shop_id', $this->unasShopId);
        }

        return $query;
    }
}

