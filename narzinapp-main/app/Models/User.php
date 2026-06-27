<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Notifications\CustomVerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Modules\Admin\Models\UserAdmin;
use Modules\Checkout\Models\Order;
use Modules\Checkout\Models\UserWallet;
use Modules\UserAddress\Models\UserAddress;
use Modules\Vendor\Models\Vendor;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'user_type_id',
        "email_verified_at",
        'email',
        'password',
        'fcm_token',
        'preferred_language',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public $appends = ['orders_count']; 

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function sendEmailVerificationNotification()
    {
        $this->notify(new CustomVerifyEmail());
    }

    public function admin(){
        return $this->hasOne(UserAdmin::class);
    }

    public function vendor(){
        return $this->hasOne(Vendor::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function getOrdersCountAttribute()
    {
        return $this->orders()->count();
    }

    public function wallet(){
        return $this->hasOne(UserWallet::class);
    }

    public function addresses(){
        return $this->hasMany(UserAddress::class);
    }

    // Keep old accessor for backwards compatibility
    public function address(){
        return $this->hasMany(UserAddress::class);
    }

    public function reviews(){
        return $this->hasMany(\Modules\Reviews\Models\Review::class);
    }
}
