<?php

namespace Modules\Checkout\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\ProductManagement\Models\Product;
use Modules\ProductManagement\Models\ProductVariant;

// use Modules\Checkout\Database\Factories\CartFactory;

class Cart extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $table = 'cart';
    
    protected $fillable = [
        'user_id',
        'product_id',
        'product_variant_id',
        'quantity'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }

    // protected static function newFactory(): CartFactory
    // {
    //     // return CartFactory::new();
    // }
}
