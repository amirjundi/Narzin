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

    // protected static function newFactory(): UserAdminFactory
    // {
    //     // return UserAdminFactory::new();
    // }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
