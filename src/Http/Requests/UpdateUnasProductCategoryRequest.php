<?php

declare(strict_types=1);

namespace Molitor\Unas\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class UpdateUnasProductCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('acl', 'unas');
    }

    public function rules(): array
    {
        return [
            'unas_shop_id' => ['sometimes', 'required', 'integer', 'exists:unas_shops,id'],
            'parent_id' => ['nullable', 'integer', 'exists:unas_product_categories,id'],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'keywords' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'display_page' => ['sometimes', 'required', 'boolean'],
            'display_menu' => ['sometimes', 'required', 'boolean'],
            'remote_id' => ['nullable', 'string', 'max:255'],
            'changed' => ['sometimes', 'required', 'boolean'],
        ];
    }
}
