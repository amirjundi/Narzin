<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Modules\ProductManagement\Models\ProductVariant;
use App\Models\User;
use Illuminate\Support\Facades\Notification;

class LowStockAlertCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventory:low-stock-alert';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for products with low stock and alert admins';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $lowStockThreshold = config('shop.low_stock_threshold', 5);
        
        $lowStockVariants = ProductVariant::with('product')
            ->where('stock', '<=', $lowStockThreshold)
            ->where('is_active', true)
            ->get();

        if ($lowStockVariants->isEmpty()) {
            $this->info('No low stock items found.');
            return;
        }

        $this->info('Found ' . $lowStockVariants->count() . ' items with low stock.');
        
        // Log it for monitoring
        foreach ($lowStockVariants as $variant) {
            Log::warning("Low stock alert: Product '{$variant->product->name_arabic}' (Variant ID: {$variant->id}) has only {$variant->stock} left.");
        }

        // Ideally here we would notify the admin user(s)
        // $admins = User::where('user_type_id', 3)->get();
        // Notification::send($admins, new \App\Notifications\LowStockNotification($lowStockVariants));
    }
}
