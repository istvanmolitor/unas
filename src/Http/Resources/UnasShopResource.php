<?php

declare(strict_types=1);

namespace Molitor\Unas\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UnasShopResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'enabled' => $this->enabled,
            'domain' => $this->domain,
            'name' => $this->name,
            'api_key' => $this->api_key,
            'warehouse_id' => $this->warehouse_id,
            'warehouse_name' => $this->warehouse?->name,
            'shop_products_count' => $this->whenCounted('shopProducts'),
            'shop_product_categories_count' => $this->whenCounted('shopProductCategories'),
            'shop_product_parameters_count' => $this->whenCounted('shopProductParameters'),
            'shop_orders_count' => $this->whenCounted('shopOrders'),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
