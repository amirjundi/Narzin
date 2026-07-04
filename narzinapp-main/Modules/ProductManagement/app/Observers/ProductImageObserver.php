<?php

namespace Modules\ProductManagement\Observers;

use Illuminate\Support\Facades\Log;
use Modules\ProductManagement\Models\ProductImage;
use Modules\ProductManagement\Services\StorageService;

/**
 * Listens to ProductImage lifecycle events and cleans up storage automatically.
 *
 * This observer is registered in ProductManagementServiceProvider.
 * It fires on HARD delete only (forceDelete). Because products use SoftDeletes,
 * a soft-deleted product's images are preserved so an admin can restore the product.
 * Images are only permanently removed when a product is force-deleted.
 */
class ProductImageObserver
{
    /**
     * Called when a ProductImage record is hard-deleted.
     * Removes the physical file from whatever disk is active (local or B2).
     */
    public function deleted(ProductImage $image): void
    {
        // The 'image' column stores the raw relative path, e.g. "products/images/foo.jpg".
        // The GlobalScope prepends the full URL, but here we access the raw attribute
        // before the scope transforms it by using withoutGlobalScope.
        $rawPath = $image->getRawOriginal('image');

        if ($rawPath) {
            StorageService::delete($rawPath);
        }
    }
}
