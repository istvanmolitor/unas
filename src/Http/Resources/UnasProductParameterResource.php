<?php

declare(strict_types=1);

namespace Molitor\Unas\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UnasProductParameterResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'unas_shop_id' => $this->unas_shop_id,
            'shop_name' => $this->shop?->name,
            'name' => $this->name,
            'type' => $this->type,
            'language_id' => $this->language_id,
            'language_name' => $this->language?->name,
            'order' => $this->order,
            'remote_id' => $this->remote_id,
            'changed' => $this->changed,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
