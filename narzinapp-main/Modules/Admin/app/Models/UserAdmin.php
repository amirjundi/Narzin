<?php

namespace Modules\Admin\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Admin\Database\Factories\UserAdminFactory;

class UserAdmin extends Model
{
    use HasFactory;
    protected $table = 'users_admins';
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'is_active',
    ];

    /**
     * Separation of duties: a user who is a vendor cannot be granted admin.
     * Enforced centrally so no controller, future flow, or manual insert can bypass it.
     */
    protected static function booted(): void
    {
        static::creating(function (UserAdmin $admin) {
            if ($admin->user_id && \Illuminate\Support\Facades\DB::table('vendors')
                    ->where('user_id', $admin->user_id)->whereNull('deleted_at')->exists()) {
                throw new \DomainException('This user is a vendor; vendors cannot be granted admin (separation of duties).');
            }
        });
    }

    // protected static function newFactory(): UserAdminFactory
    // {
    //     // return UserAdminFactory::new();
    // }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
