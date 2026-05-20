<?php

declare(strict_types=1);

namespace Molitor\Unas\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUnasProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sku' => ['sometimes', 'required', 'string', 'max:255'],
            'unas_shop_id' => ['sometimes', 'required', 'integer', 'exists:unas_shops,id'],
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
            'product_unit_id' => ['nullable', 'integer', 'exists:product_units,id'],
            'price' => ['sometimes', 'required', 'numeric'],
            'stock' => ['sometimes', 'required', 'numeric'],
            'remote_id' => ['nullable', 'string', 'max:255'],
            'changed' => ['sometimes', 'required', 'boolean'],
        ];
    }
}
