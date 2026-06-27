<?php

namespace Modules\Admin\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Admin\Database\Factories\DeliveryPriceFactory;

class DeliveryPrice extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'price',
        'fast_price',
        'from_days',
        'to_days',
    ];

    protected $table = 'delivery_prices';
    // protected static function newFactory(): DeliveryPriceFactory
    // {
    //     // return DeliveryPriceFactory::new();
    // }
}
