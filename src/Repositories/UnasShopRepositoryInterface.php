<?php

namespace Molitor\Unas\Repositories;

use Molitor\Unas\Models\UnasShop;
use Illuminate\Database\Eloquent\Collection;

interface UnasShopRepositoryInterface
{
    public function getByName($name): ?UnasShop;

    public function getAll(): Collection;

    public function delete(UnasShop $shop): void;

    public function getById(int $shopId): UnasShop|null;
}
