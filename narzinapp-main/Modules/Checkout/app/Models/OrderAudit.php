<?php

namespace Modules\Checkout\Models;

use Illuminate\Database\Eloquent\Model;

class OrderAudit extends Model
{
    public $timestamps = false;
    
    protected $fillable = [
        'order_id',
        'action',
        'old_payment_status',
        'new_payment_status',
        'old_order_status',
        'new_order_status',
        'data',
        'triggered_by',
        'user_id',
        'ip_address',
        'notes',
        'created_at'
    ];

    protected $casts = [
        'data' => 'array',
        'created_at' => 'datetime'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}