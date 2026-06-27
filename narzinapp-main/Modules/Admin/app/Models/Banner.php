<?php

namespace Modules\Admin\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Admin\Database\Factories\BannerFactory;

class Banner extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'image',
        'is_mobile',
        'title',
        'description',
    ];

    // protected static function newFactory(): BannerFactory
    // {
    //     // return BannerFactory::new();
    // }
}
