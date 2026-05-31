<?php

declare(strict_types=1);

namespace Molitor\Unas\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class UpdateUnasProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('acl', 'unas');
    }

    public function rules(): array
    {
        return [
            'sku' => ['sometimes', 'required', 'string', 'max:255'],
            'unas_shop_id' => ['prohibited'],
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
            'product_unit_id' => ['nullable', 'integer', 'exists:product_units,id'],
            'price' => ['sometimes', 'required', 'numeric'],
            'stock' => ['sometimes', 'required', 'numeric'],
            'remote_id' => ['prohibited'],
            'changed' => ['sometimes', 'required', 'boolean'],
        ];
    }
}
