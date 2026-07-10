<?php

namespace Modules\Telemetry\Models;

use Illuminate\Database\Eloquent\Model;

class CheckoutEvent extends Model
{
    protected $table = 'checkout_events';

    protected $fillable = [
        'session_id', 'user_id', 'step', 'order_id', 'occurred_at',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
    ];
}
