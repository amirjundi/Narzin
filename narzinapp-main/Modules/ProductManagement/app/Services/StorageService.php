<?php

namespace Modules\ProductManagement\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

/**
 * Central gateway for all file storage operations in the ProductManagement module.
 *
 * WHY THIS EXISTS:
 * By routing every upload and delete through this service instead of calling
 * Storage::disk('public') directly in controllers, we gain a single place to
 * change the active storage backend. Switching from local → Backblaze B2 is
 * a one-line change in .env (FILESYSTEM_DISK=b2) with zero controller edits.
 *
 * The database always stores relative paths (e.g. "products/images/foo.jpg"),
 * never full URLs. URLs are assembled at read time by ProductImage's GlobalScope,
 * so they automatically reflect the correct CDN / bucket URL after a migration.
 */
class StorageService
{
    /**
     * The active disk name, resolved from FILESYSTEM_DISK env var.
     * Locally: "public"   →  storage/app/public/
     * Production (B2): "b2"  →  Backblaze bucket (configured in filesystems.php)
     */
    public static function disk(): \Illuminate\Contracts\Filesystem\Filesystem
    {
        return Storage::disk(config('filesystems.default', 'public'));
    }

    /**
     * Store raw binary content and return the relative path.
     * Controllers should call this and persist the returned path string.
     */
    public static function put(string $path, string $contents): string
    {
        static::disk()->put($path, $contents);
        return $path;
    }

    /**
     * Store an uploaded file and return the relative path.
     */
    public static function store(\Illuminate\Http\UploadedFile $file, string $directory): string
    {
        return $file->store($directory, config('filesystems.default', 'public'));
    }

    /**
     * Delete one or more files by their relative paths.
     * Silently ignores paths that don't exist (idempotent).
     */
    public static function delete(string|array $paths): void
    {
        $paths = (array) $paths;
        foreach ($paths as $path) {
            if (empty($path)) {
                continue;
            }

            // Strip the full URL prefix if someone accidentally passed it
            // e.g. "https://cdn.example.com/storage/products/foo.jpg" → "products/foo.jpg"
            $path = preg_replace('#^https?://[^/]+/storage/#', '', $path);

            try {
                static::disk()->delete($path);
            } catch (\Throwable $e) {
                // Log but don't crash — a missing file should never block a product deletion.
                Log::warning("StorageService: could not delete [{$path}]: " . $e->getMessage());
            }
        }
    }

    /**
     * Return the public URL for a given relative path.
     * This is the single source of truth for URL generation — mirrors
     * the logic in ProductImage's GlobalScope but available anywhere.
     */
    public static function url(string $path): string
    {
        return static::disk()->url($path);
    }
}
