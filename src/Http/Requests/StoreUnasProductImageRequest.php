<?php

declare(strict_types=1);

namespace Molitor\Unas\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class StoreUnasProductImageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('acl', 'unas');
    }

    public function rules(): array
    {
        return [
            'image_url' => ['required', 'string', 'url', 'max:2048'],
            'is_main' => ['boolean'],
            'sort' => ['nullable', 'integer', 'min:0'],
        ];
    }
}

