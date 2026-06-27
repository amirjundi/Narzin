<?php

namespace Modules\ProductManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\ProductManagement\Database\Factories\VariantAttributeFactory;

class VariantAttribute extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['name_arabic', 'name_german'];

    public function variantValues()
    {
        return $this->hasMany(VariantValue::class);
    }
}
