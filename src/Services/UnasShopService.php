<?php

namespace Molitor\Unas\Services;
use Molitor\Unas\Models\UnasShop;

class UnasShopService
{
    public function __construct(
        private UnasProductService          $productService,
        private UnasProductCategoryService  $productCategoryService,
        private UnasProductParameterService $productParameterService,
    )
    {
    }

    public function delete(UnasShop $shop): void
    {

    }

    public function clear(UnasShop $shop): void
    {
        $this->productService->clearShop($shop);
        $this->productCategoryService->clearShop($shop);
        $this->productParameterService->clearShop($shop);
    }
}