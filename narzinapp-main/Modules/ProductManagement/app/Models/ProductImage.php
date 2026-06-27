<?php

namespace Modules\ProductManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\ProductManagement\Database\Factories\ProductImageFactory;

class ProductImage extends Model
{
    use HasFactory;

    protected $table = 'products_images';

    protected $fillable = [
        'product_id',
        'image',
        'color',
    ];


        protected static function booted()
    {
        static::addGlobalScope('image_url', function ($query) {
            $base = config('app.url');
            $query->select('*')
                ->selectRaw("CONCAT(?, image) as image", [$base . "/storage/" ]);
        });
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
