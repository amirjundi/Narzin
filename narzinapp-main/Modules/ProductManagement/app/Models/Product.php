<?php

namespace Modules\ProductManagement\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Reviews\Models\Review;
use Modules\Vendor\Models\Vendor;
use Modules\Wishlist\Models\Wishlist;

use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'name_arabic',
        'name_german',
        'slug_arabic',
        'slug_german',
        'description_arabic',
        'description_german',
        'category_id',
        'child_category_id',
        'is_active',
        'vendor_id',
        'weight',
        'size_chart',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'size_chart' => 'array',
    ];

    protected $appends = ['average_rating'];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function child_category(): BelongsTo
    {
        return $this->belongsTo(Category::class , 'child_category_id');
    }

    /**
     * Keyword product search.
     *
     * Splits the query into whitespace-separated keywords and requires EVERY
     * keyword to appear (in any order) in the product name, description, or its
     * category name — in Arabic or German. Matching is case-insensitive and
     * portable: LOWER(col) LIKE lower(term) behaves the same on Postgres (whose
     * plain LIKE is case-sensitive) and on the SQLite test database.
     */
    public function scopeSearch($query, ?string $term)
    {
        $term = trim((string) $term);
        if ($term === '') {
            return $query;
        }

        $keywords = preg_split('/\s+/', $term, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        foreach ($keywords as $keyword) {
            $like = '%' . mb_strtolower($keyword) . '%';

            $query->where(function ($q) use ($like) {
                $q->whereRaw('LOWER(name_arabic) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(name_german) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(description_arabic) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(description_german) LIKE ?', [$like])
                    ->orWhereHas('category', function ($c) use ($like) {
                        $c->whereRaw('LOWER(name_arabic) LIKE ?', [$like])
                            ->orWhereRaw('LOWER(name_german) LIKE ?', [$like]);
                    });
            });
        }

        return $query;
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    protected function averageRating(): Attribute
    {
        return Attribute::make(
            get: function () {
                return $this->reviews()->avg('rating') ?? 0;
            }
        );
    }

    public function isWishlistedByUser($userId): bool
    {
        return $this->wishlists()->where('user_id', $userId)->exists();
    }

    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }
}
