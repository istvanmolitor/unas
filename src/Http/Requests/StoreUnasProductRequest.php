<?php

declare(strict_types=1);

namespace Molitor\Unas\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class StoreUnasProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('acl', 'unas');
    }

    public function rules(): array
    {
        return [
            'sku' => ['required', 'string', 'max:255'],
            'unas_shop_id' => ['required', 'integer', 'exists:unas_shops,id'],
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
            'product_unit_id' => ['nullable', 'integer', 'exists:product_units,id'],
            'price' => ['required', 'numeric'],
            'stock' => ['required', 'numeric'],
            'remote_id' => ['nullable', 'string', 'max:255'],
            'changed' => ['required', 'boolean'],
        ];
    }
}
