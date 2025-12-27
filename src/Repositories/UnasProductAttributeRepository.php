<?php

declare(strict_types=1);

namespace Molitor\Unas\Repositories;

use Molitor\Unas\Models\UnasProduct;
use Molitor\Unas\Models\UnasProductAttribute;
use Molitor\Product\Models\ProductField;
use Molitor\Product\Models\ProductFieldOption;
use Molitor\Product\Repositories\ProductFieldRepositoryInterface;
use Molitor\Product\Repositories\ProductFieldOptionRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class UnasProductAttributeRepository implements UnasProductAttributeRepositoryInterface
{
    private UnasProductAttribute $unasProductAttribute;

    public function __construct(
        private ProductFieldRepositoryInterface $productFieldRepository,
        private ProductFieldOptionRepositoryInterface $productFieldOptionRepository
    )
    {
        $this->unasProductAttribute = new UnasProductAttribute();
    }

    public function setAttribute(UnasProduct $unasProduct, string $name, array|string $value, int|string|null $language = null): self
    {
        $field = $this->productFieldRepository->create($name, $language);

        if ($field->multiple && is_array($value)) {
            $this->deleteAttributesByProduct($unasProduct);
            foreach ($value as $valueElement) {
                $this->add($unasProduct, $this->productFieldOptionRepository->create($field, $valueElement, $language));
            }
        } else {
            $this->deleteAttributes($field)
                ->add($unasProduct, $this->productFieldOptionRepository->create($field, $value, $language));
        }
        return $this;
    }

    public function getProductAttributesByProduct(UnasProduct $unasProduct): Collection
    {
        return $this->unasProductAttribute->with(['productField', 'productFieldOption'])->where('unas_product_id', $unasProduct->id)->get();
    }

    public function deleteAttributesByProduct(UnasProduct $unasProduct): bool
    {
        $this->unasProductAttribute
            ->where('unas_product_id', $unasProduct->id)
            ->delete();
        return true;
    }

    public function delete(UnasProduct $unasProduct, ProductFieldOption $productFieldOption): self
    {
        $this->unasProductAttribute
            ->where('unas_product_id', $unasProduct->id)
            ->where('product_field_option_id', $productFieldOption->id)
            ->delete();

        return $this;
    }

    protected function exists(UnasProduct $unasProduct, ProductFieldOption $productFieldOption): bool
    {
        return $this->unasProductAttribute
                ->where('unas_product_id', $unasProduct->id)
                ->where('product_field_option_id', $productFieldOption->id)
                ->count() > 0;
    }

    protected function deleteAttributes(ProductField $productField): self
    {
        $this->unasProductAttribute
            ->join(
                'product_field_options',
                'product_field_options.id',
                '=',
                'unas_product_attributes.product_field_option_id'
            )
            ->where('unas_product_id', $productField->id)
            ->delete();

        return $this;
    }

    private function add(UnasProduct $unasProduct, ProductFieldOption $productFieldOption): self
    {
        if (!$this->exists($unasProduct, $productFieldOption)) {
            $this->unasProductAttribute->create(
                [
                    'unas_product_id' => $unasProduct->id,
                    'product_field_option_id' => $productFieldOption->id,
                    'sort' => 0,
                ]
            );
        }
        return $this;
    }

    public function save(UnasProduct $unasProduct, ProductFieldOption $productFieldOption, int $sort): UnasProductAttribute
    {
        if (!$this->exists($unasProduct, $productFieldOption)) {
            return $this->unasProductAttribute->create(
                [
                    'unas_product_id' => $unasProduct->id,
                    'product_field_option_id' => $productFieldOption->id,
                    'sort' => $sort,
                ]
            );
        }
        else {
            $attribute = $this->unasProductAttribute
                ->where('unas_product_id', $unasProduct->id)
                ->where('product_field_option_id', $productFieldOption->id)
                ->first();

            $attribute->update(['sort' => $sort]);

            return $attribute;
        }
    }
}

