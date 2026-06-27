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



    protected static function booted()
    {
        static::addGlobalScope('image_url', function ($query) {
            $base = config('app.url');
            $query->select('variant_values.*')
                ->leftJoin('variant_attributes', 'variant_values.variant_attribute_id', '=', 'variant_attributes.id')
                ->selectRaw("
                    CASE 
                        WHEN variant_attributes.type = 'pattern' THEN CONCAT(?, variant_values.value)
                        ELSE variant_values.value
                    END as value
                ", [$base . "/storage/"]);
        });
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
