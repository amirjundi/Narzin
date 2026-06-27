<?php

namespace Modules\Admin\Models;

use Illuminate\Database\Eloquent\Model;

class PriceExchange extends Model
{
    protected $table = 'price_exechange';
    protected $fillable = [
        'price_rate',
        'created_by'
    ];

    protected $casts = [
        'price_rate' => 'decimal:2',
        'markup_percentage' => 'decimal:2',
    ];

    /**
     * Get the latest exchange rate record with both rate and markup.
     */
    public static function getLatest(): ?self
    {
        return static::latest('created_at')->first();
    }
}
