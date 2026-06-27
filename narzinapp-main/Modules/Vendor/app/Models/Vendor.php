<?php

namespace Modules\Vendor\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Vendor\Database\Factories\VendorFactory;

use Illuminate\Database\Eloquent\SoftDeletes;

class Vendor extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'store_name_in_arabic' ,
        'store_name_in_german' ,
        'latitude' ,
        'longitude' ,
        'store_logo',
        'address',
        'phone',
        'store_type',
        'store_id',
        'user_id',
        'is_active',
        'status',
        'markup_percentage',
        'exchange_rate',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // protected static function newFactory(): VendorFactory
    // {
    //     // return VendorFactory::new();
    // }
}
