<?php

namespace Modules\ProductManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\ProductManagement\Database\Factories\VariantValueFactory;

class VariantValue extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'product_variants_id',
        'variant_attribute_id',
        'value'
    ];



    public function getValueAttribute($value)
    {
        if (empty($value)) return $value;
        
        // Only transform paths to URLs if this is a pattern image attribute
        if ($this->variantAttribute?->type === 'pattern') {
            $raw = preg_replace('#^https?://[^/]+/storage/#', '', $value);
            return $raw ? \Modules\ProductManagement\Services\StorageService::url($raw) : '';
        }
        
        return $value;
    }

    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variants_id');
    }

    public function variantAttribute()
    {
        return $this->belongsTo(VariantAttribute::class);
    }
}
