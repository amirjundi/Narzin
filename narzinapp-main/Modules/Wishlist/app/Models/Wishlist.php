<?php

namespace Modules\Wishlist\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\ProductManagement\Models\Product;

// use Modules\Wishlist\Database\Factories\WishlistFactory;

class Wishlist extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'product_id',
    ];

    protected $table = 'wishlist';



    // protected static function newFactory(): WishlistFactory
    // {
    //     // return WishlistFactory::new();
    // }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
