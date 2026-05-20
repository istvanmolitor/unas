<?php

declare(strict_types=1);

namespace Molitor\Unas\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUnasProductParameterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'unas_shop_id' => ['required', 'integer', 'exists:unas_shops,id'],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:255'],
            'language_id' => ['nullable', 'integer', 'exists:languages,id'],
            'order' => ['required', 'integer'],
            'remote_id' => ['nullable', 'string', 'max:255'],
            'changed' => ['required', 'boolean'],
        ];
    }
}
