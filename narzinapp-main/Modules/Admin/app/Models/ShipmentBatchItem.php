<?php

namespace Modules\Admin\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Checkout\Models\Order;
use Modules\Checkout\Models\OrderItem;
use Modules\Vendor\Models\Vendor;

class ShipmentBatchItem extends Model
{
    protected $table = 'shipment_batch_items';

    protected $fillable = [
        'shipment_batch_id',
        'order_item_id',
        'order_id',
        'vendor_id',
        'collection_status',
        'collected_at',
        'collected_by',
        'refund_amount',
        'notes',
    ];

    protected $casts = [
        'collected_at' => 'datetime',
        'refund_amount' => 'decimal:2',
    ];

    // ─── Relationships ───────────────────────────────────────────

    public function batch(): BelongsTo
    {
        return $this->belongsTo(ShipmentBatch::class, 'shipment_batch_id');
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class, 'order_item_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    public function collectedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'collected_by');
    }
}
