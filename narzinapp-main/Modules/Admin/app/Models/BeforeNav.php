<?php

namespace Modules\Admin\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Admin\Database\Factories\BannerFactory;

class BeforeNav extends Model
{
    use HasFactory;

    protected $table = 'before_nav';
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'text',
        'start_date',
        'end_date',
    ];

    // protected static function newFactory(): BannerFactory
    // {
    //     // return BannerFactory::new();
    // }
}
