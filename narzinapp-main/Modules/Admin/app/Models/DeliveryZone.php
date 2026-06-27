<?php

namespace Modules\Admin\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeliveryZone extends Model
{
    protected $table = 'delivery_zones';
    
    protected $fillable = [
        'name_english',
        'name_german',
        'name_arabic',
        'is_active'
    ];

    public function deliveryMethods(): HasMany
    {
        return $this->hasMany(DeliveryMethod::class);
    }
}
