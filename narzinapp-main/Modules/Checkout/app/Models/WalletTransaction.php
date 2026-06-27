<?php

namespace Modules\Checkout\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\ProductManagement\Models\Product;
use Modules\ProductManagement\Models\ProductVariant;

// use Modules\Checkout\Database\Factories\CartFactory;

class WalletTransaction extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $table = 'wallet_transactions';

    protected $fillable = [
        'user_id',
        'wallet_id',
        'type',
        'amount',
        'order_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function wallet()
    {
        return $this->belongsTo(UserWallet::class);
    }


    // protected static function newFactory(): CartFactory
    // {
    //     // return CartFactory::new();
    // }
}
