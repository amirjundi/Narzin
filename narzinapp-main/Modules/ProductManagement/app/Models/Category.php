<?php

namespace Modules\ProductManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\URL;

// use Modules\ProductManagemnt\Database\Factories\CategoryFactory;

class Category extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name_arabic',
        'name_german',
        'slug_arabic',
        'slug_german',
        'image',
        'parent_id'
    ];


    public function getImageAttribute($value)
    {
        if (empty($value)) return $value;
        $raw = preg_replace('#^https?://[^/]+/storage/#', '', $value);
        return $raw ? \Modules\ProductManagement\Services\StorageService::url($raw) : '';
    }

    public function subcategories()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    /**
     * Products that belong directly to this category.
     */
    public function products()
    {
        return $this->hasMany(\Modules\ProductManagement\Models\Product::class, 'category_id');
    }



    /**
     * Get the parent category
     */
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * Get the children categories
     */
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    /**
     * Get all descendants
     */
    public function descendants()
    {
        return $this->children()->with('descendants');
    }

    /**
     * Get all ancestors
     */
    public function ancestors()
    {
        return $this->parent()->with('ancestors');
    }

    /**
     * Check if category has parent
     */
    public function hasParent(): bool
    {
        return !is_null($this->parent_id);
    }

    /**
     * Get category level in the tree
     */
    public function getLevel(): int
    {
        $level = 1;
        $parent = $this->parent;

        while ($parent) {
            $level++;
            $parent = $parent->parent;
        }

        return $level;
    }

    // protected static function newFactory(): CategoryFactory
    // {
    //     // return CategoryFactory::new();
    // }
}
