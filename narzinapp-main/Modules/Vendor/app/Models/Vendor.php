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
        'commission_percentage',
        'discount_absorption_percentage',
        'exchange_rate',
    ];

    /**
     * Separation of duties: a user who is an admin cannot also be a vendor.
     * Enforced centrally so no controller, future flow, or manual insert can bypass it.
     */
    protected static function booted(): void
    {
        static::creating(function (Vendor $vendor) {
            if ($vendor->user_id && \Illuminate\Support\Facades\DB::table('users_admins')
                    ->where('user_id', $vendor->user_id)->exists()) {
                throw new \DomainException('This user is an admin; admins cannot also be vendors (separation of duties).');
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // protected static function newFactory(): VendorFactory
    // {
    //     // return VendorFactory::new();
    // }
}
