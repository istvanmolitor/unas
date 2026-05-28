<?php

declare(strict_types=1);

namespace Molitor\Unas\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUnasProductImageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'image_url' => ['sometimes', 'required', 'string', 'url', 'max:2048'],
            'is_main' => ['sometimes', 'boolean'],
            'sort' => ['sometimes', 'nullable', 'integer', 'min:0'],
        ];
    }
}

