<?php

namespace Molitor\Unas\Services;

use Molitor\Product\Models\Product;
use Molitor\Product\Services\Dto\ProductDtoService;
use Molitor\Unas\Models\UnasProduct;
use Molitor\Unas\Models\UnasShop;
use Molitor\Unas\Services\Dto\UnasProductDtoService;

class ProductCopyService
{
    public function __construct(
        private ProductDtoService  $productDtoService,
        private UnasProductDtoService $unasProductDtoService,
    )
    {
    }

    public function copyProduct(Product $product, UnasShop $unasShop): UnasProduct
    {
        $productDto = $this->productDtoService->makeDto($product);
        return $this->unasProductDtoService->saveDto($unasShop, $productDto);
    }
}
