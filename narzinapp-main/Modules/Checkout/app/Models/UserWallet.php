<?php

namespace Modules\Checkout\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\ProductManagement\Models\Product;
use Modules\ProductManagement\Models\ProductVariant;

// use Modules\Checkout\Database\Factories\CartFactory;

class UserWallet extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $table = 'user_wallet';

    protected $fillable = [
        'user_id',
        'balance',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }


    // protected static function newFactory(): CartFactory
    // {
    //     // return CartFactory::new();
    // }
}
