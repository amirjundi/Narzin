<?php

namespace Modules\ProductManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Admin\Models\ColorTag;

// use Modules\ProductManagement\Database\Factories\ProductVariantFactory;

class ProductVariant extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'product_id',
        'price',
        'cost',
        'tax',
        'stock',
        'expiry_date',
        'expiry_days',
        'sku',
        'is_active',
        "color_tag_id",
        'is_out_of_stock'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'is_out_of_stock' => 'boolean',
        'expiry_date' => 'date'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variantValues()
    {
        return $this->hasMany(VariantValue::class, 'product_variants_id');
    }

    public function colorTag()
    {
        return $this->belongsTo(ColorTag::class);
    }

    
}
