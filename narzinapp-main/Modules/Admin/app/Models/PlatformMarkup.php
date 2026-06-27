<?php

namespace Modules\Admin\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformMarkup extends Model
{
    protected $table = 'platform_markups';
    
    protected $fillable = [
        'percentage'
    ];

    /**
     * Helper to easily get the latest global markup percentage.
     */
    public static function getLatest(): float
    {
        return (float) static::latest('created_at')->value('percentage') ?? 0;
    }
}
