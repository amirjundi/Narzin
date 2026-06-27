<?php

namespace Modules\Checkout\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Vendor\Models\Vendor;


// use Modules\Checkout\Database\Factories\CartFactory;

class Coupon extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $table = 'coupons';
    
    protected $fillable = [
        'code',
        'discount_amount',
        'discount_type',
        'start_date',
        'end_date',
        'usage_limit',
        'used',
        'minimum_cart_amount',
        'is_active',
        'vendor_id'
    ];


    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
  
}
