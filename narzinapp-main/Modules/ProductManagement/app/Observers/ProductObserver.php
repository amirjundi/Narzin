<?php

namespace Modules\ProductManagement\Observers;

use Modules\ProductManagement\Models\Product;

/**
 * Listens to Product lifecycle events and cascades cleanup to child images.
 *
 * SOFT DELETE behaviour:
 *   - When a product is soft-deleted (admin clicks "Delete"), images are KEPT.
 *     The product can be restored with all its images intact.
 *
 * FORCE DELETE behaviour:
 *   - When a product is force-deleted (permanent removal), all associated
 *     ProductImage records are hard-deleted, which triggers ProductImageObserver
 *     to remove each physical file from storage.
 *
 * EXPIRED PRODUCTS (is_active = false):
 *   - Expiration does not delete images. The product is just hidden from the
 *     storefront. Images remain until the admin explicitly force-deletes.
 *   - If you want auto-purge on expiry, add a scheduled command later.
 */
class ProductObserver
{
    /**
     * Before a hard delete, cascade to images so the observer fires per image.
     * Using forceDelete() on the relationship triggers ProductImageObserver::deleted().
     */
    public function forceDeleting(Product $product): void
    {
        // withoutGlobalScope bypasses the URL-prepending scope so we get raw paths.
        $product->images()->withoutGlobalScope('image_url')->get()->each->forceDelete();
    }
}
