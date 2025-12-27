<?php

declare(strict_types=1);

namespace Molitor\Unas\Repositories;

use Molitor\Unas\Models\UnasProduct;
use Molitor\Unas\Models\UnasProductAttribute;
use Molitor\Product\Models\ProductFieldOption;
use Illuminate\Database\Eloquent\Collection;

interface UnasProductAttributeRepositoryInterface
{

    public function setAttribute(UnasProduct $unasProduct, string $name, string|array $value): self;

    public function getProductAttributesByProduct(UnasProduct $unasProduct): Collection;

    public function deleteAttributesByProduct(UnasProduct $unasProduct): bool;

    public function delete(UnasProduct $unasProduct, ProductFieldOption $productFieldOption): self;

    public function save(UnasProduct $unasProduct, ProductFieldOption $productFieldOption, int $sort): UnasProductAttribute;
}

