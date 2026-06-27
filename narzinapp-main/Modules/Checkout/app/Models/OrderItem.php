<?php

namespace Modules\Checkout\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Admin\Models\ShipmentBatchItem;
use Modules\ProductManagement\Models\Product;
use Modules\ProductManagement\Models\ProductVariant;
use Modules\Vendor\Models\Vendor;

// use Modules\Checkout\Database\Factories\OrderItemFactory;

class OrderItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'order_id',
        'product_id',
        'product_variant_id',
        'quantity',
        'vendor_id',
        'unit_price',
        'subtotal',
        'status',
        'discount',
        'final_price',
        'collection_status',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function shipmentBatchItems()
    {
        return $this->hasMany(ShipmentBatchItem::class, 'order_item_id');
    }

    // protected static function newFactory(): OrderItemFactory
    // {
    //     // return OrderItemFactory::new();
    // }
}
