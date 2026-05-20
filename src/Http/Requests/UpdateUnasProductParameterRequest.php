<?php

declare(strict_types=1);

namespace Molitor\Unas\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUnasProductParameterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'unas_shop_id' => ['sometimes', 'required', 'integer', 'exists:unas_shops,id'],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'type' => ['sometimes', 'required', 'string', 'max:255'],
            'language_id' => ['nullable', 'integer', 'exists:languages,id'],
            'order' => ['sometimes', 'required', 'integer'],
            'remote_id' => ['nullable', 'string', 'max:255'],
            'changed' => ['sometimes', 'required', 'boolean'],
        ];
    }
}
