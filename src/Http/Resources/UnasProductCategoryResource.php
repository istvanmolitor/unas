<?php

declare(strict_types=1);

namespace Molitor\Unas\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UnasProductCategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'unas_shop_id' => $this->unas_shop_id,
            'shop_name' => $this->shop?->name,
            'parent_id' => $this->parent_id,
            'parent_name' => $this->parent?->name,
            'name' => $this->name,
            'title' => $this->title,
            'display_page' => $this->display_page,
            'display_menu' => $this->display_menu,
            'remote_id' => $this->remote_id,
            'changed' => $this->changed,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
