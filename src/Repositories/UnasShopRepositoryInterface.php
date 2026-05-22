<?php

namespace Molitor\Unas\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Molitor\Unas\Models\UnasShop;

interface UnasShopRepositoryInterface
{
    public function getByName($name): ?UnasShop;

    public function getAll(): Collection;

    public function delete(UnasShop $shop): void;

    public function getById(int $shopId): ?UnasShop;
}
