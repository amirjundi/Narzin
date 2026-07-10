<?php

namespace Modules\Telemetry\Models;

use Illuminate\Database\Eloquent\Model;

class CartEvent extends Model
{
    protected $table = 'cart_events';

    protected $fillable = [
        'session_id', 'user_id', 'product_id', 'variant_id',
        'action', 'quantity', 'unit_price', 'occurred_at',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'occurred_at' => 'datetime',
    ];
}
