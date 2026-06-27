<?php 


namespace Modules\Checkout\Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Checkout\Models\Order;
use Modules\Checkout\Models\OrderItem;
use Modules\ProductManagement\Models\Product;
use Modules\ProductManagement\Models\ProductVariant;
use Modules\UserAddress\Models\UserAddress;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        // First ensure we have addresses

        $users = User::all();
        $orderStatuses = ['pending', 'processing', 'completed', 'cancelled'];
        $paymentStatuses = ['pending', 'paid', 'failed'];
        $itemStatuses = ['pending', 'completed', 'rejected'];

        foreach ($users as $user) {
            // Create 1-3 orders per user
            $numOrders = rand(1, 3);
            
            for ($i = 1; $i <= $numOrders; $i++) {
                $address = UserAddress::where('user_id', $user->id)->inRandomOrder()->first();
                
                // Create order
                $order = Order::create([
                    'user_id' => $user->id,
                    'address_id' => $address->id,
                    'order_number' => 'ORD-' . date('Y') . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT),
                    'order_status' => $orderStatuses[array_rand($orderStatuses)],
                    'payment_status' => $paymentStatuses[array_rand($paymentStatuses)],
                    'notes' => rand(0, 1) ? 'Please deliver in the morning' : null,
                    'total_amount' => 0, // Will be updated after adding items
                ]);

                // Add 1-5 items to each order
                $numItems = rand(1, 5);
                $totalAmount = 0;

                for ($j = 1; $j <= $numItems; $j++) {
                    // Get random product and its variant
                    $product = Product::inRandomOrder()->first();
                    $variant = ProductVariant::where('product_id', $product->id)
                        ->inRandomOrder()
                        ->first();

                    $quantity = rand(1, 5);
                    $unitPrice = $variant->price;
                    $subtotal = $quantity * $unitPrice;
                    $totalAmount += $subtotal;

                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'product_variant_id' => $variant->id,
                        'vendor_id' => $product->vendor_id,
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'subtotal' => $subtotal,
                        'status' => $itemStatuses[array_rand($itemStatuses)],
                    ]);
                }

                // Update order total
                $order->update(['total_amount' => $totalAmount]);
            }
        }
    }
}