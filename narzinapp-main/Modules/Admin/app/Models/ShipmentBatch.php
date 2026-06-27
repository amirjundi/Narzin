<?php

namespace Modules\Admin\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShipmentBatch extends Model
{
    protected $table = 'shipment_batches';

    protected $fillable = [
        'batch_number',
        'status',
        'admin_id',
        'notes',
        'total_items',
        'collected_items',
        'started_at',
        'completed_at',
        'shipped_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'shipped_at' => 'datetime',
    ];

    // ─── Relationships ───────────────────────────────────────────

    public function items(): HasMany
    {
        return $this->hasMany(ShipmentBatchItem::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    // ─── Scopes ──────────────────────────────────────────────────

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['pending', 'collecting']);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    // ─── Computed Attributes ─────────────────────────────────────

    public function getProgressPercentageAttribute(): int
    {
        if ($this->total_items === 0) {
            return 0;
        }
        // Count both collected and unavailable as "resolved"
        $resolved = $this->items()->whereIn('collection_status', ['collected', 'unavailable'])->count();
        return (int) round(($resolved / $this->total_items) * 100);
    }

    public function getResolvedItemsAttribute(): int
    {
        return $this->items()->whereIn('collection_status', ['collected', 'unavailable'])->count();
    }

    public function getIsCompleteAttribute(): bool
    {
        if ($this->total_items === 0) {
            return false;
        }
        return $this->items()->where('collection_status', 'pending')->count() === 0;
    }

    /**
     * Get items grouped by vendor with vendor info
     */
    public function getVendorGroupsAttribute()
    {
        return $this->items()
            ->with([
                'vendor',
                'orderItem.product',
                'orderItem.product.images',
                'orderItem.productVariant',
                'orderItem.productVariant.variantValues',
                'orderItem.productVariant.variantValues.variantAttribute',
                'order.user',
                'order.address',
                'order.address.city',
                'order.address.country',
            ])
            ->get()
            ->groupBy('vendor_id');
    }

    /**
     * Get items grouped by customer/order for packing
     */
    public function getCustomerGroupsAttribute()
    {
        return $this->items()
            ->with([
                'vendor',
                'orderItem.product',
                'orderItem.product.images',
                'orderItem.productVariant',
                'orderItem.productVariant.variantValues',
                'orderItem.productVariant.variantValues.variantAttribute',
                'order.user',
                'order.address',
                'order.address.city',
                'order.address.country',
            ])
            ->get()
            ->groupBy('order_id');
    }

    /**
     * Generate the next batch number
     */
    public static function generateBatchNumber(): string
    {
        $today = now()->format('Ymd');
        $todayCount = static::whereDate('created_at', today())->count() + 1;
        return 'BATCH-' . $today . '-' . str_pad($todayCount, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Recalculate cached counters
     */
    public function recalculateCounters(): void
    {
        $this->update([
            'total_items' => $this->items()->count(),
            'collected_items' => $this->items()->where('collection_status', 'collected')->count(),
        ]);
    }
}
