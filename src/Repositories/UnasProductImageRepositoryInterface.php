<?php

namespace Molitor\Unas\Repositories;

use Molitor\Unas\Models\UnasProduct;
use Molitor\Unas\Models\UnasProductImage;

interface UnasProductImageRepositoryInterface
{
    public function addUrl(UnasProduct $product, string $url, string $alt): UnasProductImage;
}