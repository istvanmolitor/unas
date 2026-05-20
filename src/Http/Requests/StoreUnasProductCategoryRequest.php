<?php

declare(strict_types=1);

namespace Molitor\Unas\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUnasProductCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'unas_shop_id' => ['required', 'integer', 'exists:unas_shops,id'],
            'parent_id' => ['nullable', 'integer', 'exists:unas_product_categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'title' => ['required', 'string', 'max:255'],
            'keywords' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'display_page' => ['required', 'boolean'],
            'display_menu' => ['required', 'boolean'],
            'remote_id' => ['nullable', 'string', 'max:255'],
            'changed' => ['required', 'boolean'],
        ];
    }
}
