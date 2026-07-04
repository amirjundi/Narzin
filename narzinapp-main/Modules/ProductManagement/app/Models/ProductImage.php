<?php

namespace Modules\ProductManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\ProductManagement\Services\StorageService;
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

    /**
     * Append 'url' as a virtual attribute so existing API consumers
     * continue to receive the full image URL without schema changes.
     */
    protected $appends = ['url'];

    /**
     * Build the full URL for this image using the active storage disk.
     * Locally this resolves to /storage/..., on Backblaze B2 it resolves
     * to the bucket CDN URL — with zero code changes required.
     */
    public function getUrlAttribute(): string
    {
        $raw = $this->getRawOriginal('image') ?? $this->attributes['image'] ?? '';
        // Strip any prepended URL if the value was already transformed
        $raw = preg_replace('#^https?://[^/]+/storage/#', '', $raw);
        return $raw ? StorageService::url($raw) : '';
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}

