<?php

namespace Modules\Admin\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryMethod extends Model
{
    protected $table = 'delivery_methods';
    
    protected $fillable = [
        'delivery_zone_id',
        'name_english',
        'name_german',
        'name_arabic',
        'base_price',
        'price_per_kg',
        'estimated_days',
        'is_active'
    ];

    public function zone(): BelongsTo
    {
        return $this->belongsTo(DeliveryZone::class, 'delivery_zone_id');
    }
}
