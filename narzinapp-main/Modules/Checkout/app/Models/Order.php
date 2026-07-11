<?php

namespace Modules\Checkout\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Admin\Models\Status;
use Modules\UserAddress\Models\UserAddress;

// use Modules\Checkout\Database\Factories\OrderFactory;

use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'address_id',
        'order_number',
        'total_amount',
        'payment_status',
        'order_status',
        'status_id',
        'shipping_type',
        'shipping_cost',
        'notes',
        'coupon_id',
        'price_after_discount',
        'wallet_usage',
        'final_price',
        'discount_breakdown',
        'nass_rrn',
        'nass_int_ref',
        'callback_data',
        'paid_at',
        'payment_id',
        'promotion_id',
        'free_shipping_promotion_id',
        // Idempotency markers — must be mass-assignable so the "already applied"
        // guards in applyWalletDeduction()/applyCouponUsage() actually persist.
        'coupon_applied_at',
        'wallet_deducted_at',
        'attributed_session_id',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_term',
        'utm_content',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function address()
    {
        return $this->belongsTo(UserAddress::class, 'address_id');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function status()
    {
        return $this->belongsTo(Status::class, 'status_id');
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class, 'coupon_id');
    }

    // protected static function newFactory(): OrderFactory
    // {
    //     // return OrderFactory::new();
    // }
}
