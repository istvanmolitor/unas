<?php

declare(strict_types=1);

namespace Molitor\Unas\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Molitor\Unas\Models\UnasShop;

class UnasShopRepository implements UnasShopRepositoryInterface
{
    private UnasShop $shop;

    public function __construct(
        private UnasProductRepositoryInterface          $UnasProductRepository,
        private UnasProductCategoryRepositoryInterface  $UnasProductCategoryRepository,
        private UnasProductParameterRepositoryInterface $UnasProductParameterRepository,
        private UnasOrderRepositoryInterface            $unasOrderRepository,
    )
    {
        $this->shop = new UnasShop();
    }

    public function getByName($name): ?UnasShop
    {
        return $this->shop->where('name', $name)->first();
    }

    public function getAll(): Collection
    {
        return $this->shop->orderBy('name')->get();
    }

    public function delete(UnasShop $shop): void
    {
        $this->UnasProductRepository->deleteByShop($shop);
        $this->UnasProductCategoryRepository->deleteByShop($shop);
        $this->UnasProductParameterRepository->deleteByShop($shop);
        $this->unasOrderRepository->deleteByShop($shop);
        $shop->delete();
    }

    public function getEnableShops(): Collection
    {
        return $this->shop->where('enabled', 1)->get();
    }

    public function getById(int $shopId): UnasShop|null
    {
        return $this->shop->find($shopId);
    }
}
