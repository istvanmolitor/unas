<?php

declare(strict_types=1);

namespace Molitor\Unas\Models;

use Illuminate\Database\Eloquent\Model;

class UnasOrder extends Model
{
    protected $fillable = [
        'unas_shop_id',
        'order_id',
        'remote_id',
        'changed',
    ];
}
