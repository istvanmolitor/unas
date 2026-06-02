<?php

declare(strict_types=1);

namespace Molitor\Unas\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UnasProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $mainImageUrl = $this->mainImage?->image_url;
        if (! $mainImageUrl && $this->relationLoaded('images')) {
            $mainImageUrl = $this->images->first()?->image_url;
        }

        return [
            'id' => $this->id,
            'sku' => $this->sku,
            'unas_shop_id' => $this->unas_shop_id,
            'shop_name' => $this->shop?->name,
            'product_id' => $this->product_id,
            'product_title' => $this->product?->title,
            'main_image_url' => $mainImageUrl,
            'product_unit_id' => $this->product_unit_id,
            'price' => $this->price,
            'stock' => $this->stock,
            'remote_id' => $this->remote_id,
            'changed' => $this->changed,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
