<?php

namespace Modules\Checkout\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderReturn extends Model
{
    protected $table = 'order_returns';

    protected $fillable = [
        'order_id', 'order_item_id', 'user_id', 'reason', 'status',
        'refund_amount', 'admin_note', 'customer_note', 'requested_at', 'resolved_at',
    ];

    protected $casts = [
        'refund_amount' => 'decimal:2',
        'requested_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
