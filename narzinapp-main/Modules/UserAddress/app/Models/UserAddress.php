<?php

namespace Modules\UserAddress\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Admin\Models\City;
use Modules\Admin\Models\Country;

// use Modules\UserAddress\Database\Factories\UserAddressFactory;

class UserAddress extends Model
{
    use HasFactory;

    protected $table = 'user_address';
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'title',
        'phone_number',
        'country_id',
        'city',
        'address',
        'postal_code',
        'latitude',
        'longitude',
        'is_default',
        'delivery_zone_id',
    ];

    public function deliveryZone()
    {
        return $this->belongsTo(\Modules\Admin\Models\DeliveryZone::class, 'delivery_zone_id');
    }

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }





    
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }


    // protected static function newFactory(): UserAddressFactory
    // {
    //     // return UserAddressFactory::new();
    // }
}
