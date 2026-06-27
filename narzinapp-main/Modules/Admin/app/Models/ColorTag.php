<?php

namespace Modules\Admin\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Admin\Database\Factories\BannerFactory;

class ColorTag extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'tag',
    ];

    protected $table = 'color_tags';

    // protected static function newFactory(): BannerFactory
    // {
    //     // return BannerFactory::new();
    // }
}
