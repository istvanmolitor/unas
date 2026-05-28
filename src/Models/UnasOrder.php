<?php

declare(strict_types=1);

namespace Molitor\Unas\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Molitor\Order\Models\Order;

class UnasOrder extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'unas_shop_id',
        'order_id',
        'remote_id',
        'changed',
    ];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(UnasShop::class, 'unas_shop_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}
