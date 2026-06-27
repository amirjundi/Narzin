<?php

namespace Modules\Reviews\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\ProductManagement\Models\Product;

// use Modules\Reviews\Database\Factories\ReviewFactory;

class Review extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'product_id',
        'user_id',
        'review',
        'rating',
        'is_approved',
    ];


    public function user()
    {
        //return only username 
        return $this->belongsTo(User::class)->select('id', 'name');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // protected static function newFactory(): ReviewFactory
    // {
    //     // return ReviewFactory::new();
    // }
}
