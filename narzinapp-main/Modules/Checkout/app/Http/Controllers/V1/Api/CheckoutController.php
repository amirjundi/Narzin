<?php

namespace Modules\Checkout\Http\Controllers\V1\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use Modules\Admin\Models\DeliveryPrice;
use Modules\Checkout\Services\NassPaymentService;
use Modules\Checkout\Models\Cart;
use Modules\Checkout\Models\Coupon;
use Modules\Checkout\Models\Order;
use Modules\Checkout\Models\OrderAudit;
use Modules\Checkout\Models\OrderItem;
use Modules\Checkout\Models\UserWallet;
use Modules\Admin\Models\PriceExchange;
use Modules\Checkout\Models\WalletTransaction;
use Modules\ProductManagement\Models\Product;
use Modules\ProductManagement\Models\ProductVariant;
use Modules\Checkout\Services\PromotionEvaluator;
use Modules\Vendor\Services\VendorEarningCalculator;
use Modules\Vendor\Services\VendorRateResolver;

class CheckoutController extends Controller
{
    protected NassPaymentService $nassPaymentService;

    public function __construct(NassPaymentService $nassPaymentService)
    {
        $this->nassPaymentService = $nassPaymentService;
    }

    /**
     * PLACE ORDER
     * 
     * 1. Validates cart and stock
     * 2. DEDUCTS stock immediately (reserves items)
     * 3. Creates order with payment_status = "not_paid"
     * 4. Clears cart
     * 5. Calls Nass API for payment URL
     * 6. Returns payment URL to frontend
     */
    public function placeOrder(Request $request): JsonResponse
    {
        // Prevent double-click
        $lockKey = "checkout_lock_user_" . Auth::id();

        if (!Cache::add($lockKey, true, 30)) {
            return response()->json([
                'status' => false,
                'message' => 'Order is already being processed. Please wait.'
            ], 429);
        }

        try {
            \Modules\Telemetry\Services\CaptureService::recordCheckoutEvent(
                $request->input('session_id'),
                auth('sanctum')->id(),
                'checkout_start',
                null,
            );

            // Check for existing pending order
            $pendingOrder = Order::where('user_id', Auth::id())
                ->where('payment_status', 'not_paid')
                ->where('created_at', '>', now()->subMinutes(5))
                ->first();

            if ($pendingOrder) {
                Cache::forget($lockKey);
                return response()->json([
                    'status' => false,
                    'message' => 'You have a pending order. Complete payment or wait 5 minutes.',
                    'data' => [
                        'pending_order_id' => $pendingOrder->id,
                        'order_number' => $pendingOrder->order_number,
                        'payment_id' => $pendingOrder->payment_id
                    ]
                ], 400);
            }

            // Validate request
            $validator = Validator::make($request->all(), [
                'address_id' => [
                    'required',
                    'exists:user_address,id',
                    function ($attribute, $value, $fail) {
                        $address = DB::table('user_address')
                            ->where('id', $value)
                            ->where('user_id', Auth::id())
                            ->first();
                        if (!$address) {
                            $fail('The selected address is invalid.');
                        }
                    }
                ],
                'delivery_method_id' => [
                    'required',
                    'exists:delivery_methods,id',
                    function ($attribute, $value, $fail) use ($request) {
                        $method = DB::table('delivery_methods')->where('id', $value)->where('is_active', true)->first();
                        if (!$method) {
                            $fail('The selected delivery method is invalid or inactive.');
                        }
                    }
                ],
                'notes' => 'nullable|string|max:500',
                'coupon' => 'nullable|string|max:50',
                'wallet' => 'nullable|boolean'
            ]);

            if ($validator->fails()) {
                Cache::forget($lockKey);
                return response()->json([
                    'status' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Get cart items
            $cartItems = Cart::with(['product', 'product.vendor', 'productVariant'])
                ->where('user_id', Auth::id())
                ->get();

            if ($cartItems->isEmpty()) {
                Cache::forget($lockKey);
                return response()->json([
                    'status' => false,
                    'message' => 'Cart is empty'
                ], 400);
            }

            // Begin transaction
            DB::beginTransaction();

            try {
                // Track stock changes for audit
                $stockChanges = [];

                // Lock and deduct stock
                foreach ($cartItems as $item) {
                    $variant = ProductVariant::lockForUpdate()
                        ->find($item->product_variant_id);

                    if (!$variant) {
                        throw new \Exception("Product variant not found");
                    }

                    if ($variant->stock < $item->quantity) {
                        throw new \Exception(
                            "Insufficient stock for '{$item->product->name_arabic}'. " .
                            "Available: {$variant->stock}, Requested: {$item->quantity}"
                        );
                    }

                    // Track stock change
                    $stockChanges[] = [
                        'variant_id' => $variant->id,
                        'product_name' => $item->product->name_arabic,
                        'old_stock' => $variant->stock,
                        'quantity_reserved' => $item->quantity,
                        'new_stock' => $variant->stock - $item->quantity
                    ];

                    // DEDUCT STOCK NOW
                    $variant->decrement('stock', $item->quantity);
                }

                // Get exchange rate and global markup for price calculation
                $latestExchange = PriceExchange::latest('created_at')->first();
                $globalMarkup = \Modules\Admin\Models\PlatformMarkup::getLatest();

                // Calculate totals (with markup applied) and total weight
                $totalAmount = 0;
                $totalWeight = 0;
                foreach ($cartItems as $item) {
                    $vendor = $item->product->vendor ?? null;
                    $markup = ($vendor && $vendor->markup_percentage !== null)
                        ? (float) $vendor->markup_percentage
                        : (float) $globalMarkup;

                    $basePrice = $item->productVariant->price;
                    $markedUpPrice = $basePrice * (1 + $markup / 100);
                    $totalAmount += $markedUpPrice * $item->quantity;
                    
                    // Add to total weight
                    $itemWeight = $item->product->weight ?? 0;
                    $totalWeight += ($itemWeight * $item->quantity);
                }

                // Apply coupon
                $coupon = null;
                $discountAmount = 0;

                if ($request->coupon) {
                    $couponData = Coupon::where('code', $request->coupon)
                        ->where('is_active', true)
                        ->first();

                    if ($couponData) {
                        $alreadyUsed = Order::where('user_id', Auth::id())
                            ->where('coupon_id', $couponData->id)
                            ->whereNotIn('payment_status', ['failed', 'expired'])
                            ->exists();

                        if ($alreadyUsed) {
                            throw new \Exception('You have already used this coupon.');
                        }
                        
                        // Check coupon dates
                        if ($couponData->start_date && now()->lt($couponData->start_date)) {
                            throw new \Exception('This coupon is not yet active.');
                        }
                        if ($couponData->end_date && now()->gt($couponData->end_date)) {
                            throw new \Exception('This coupon has expired.');
                        }
                        // Check usage limit
                        if ($couponData->usage_limit && $couponData->used >= $couponData->usage_limit) {
                            throw new \Exception('This coupon has reached its usage limit.');
                        }
                        // Check minimum cart amount
                        if ($couponData->minimum_cart_amount && $totalAmount < $couponData->minimum_cart_amount) {
                            throw new \Exception("Minimum order amount for this coupon is {$couponData->minimum_cart_amount}.");
                        }

                        $coupon = $couponData;

                        if ($coupon->discount_type === 'percentage') {
                            $discountAmount = $totalAmount * ($coupon->discount_amount / 100);
                        } else {
                            $discountAmount = min($coupon->discount_amount, $totalAmount);
                        }
                    }
                }

                // Promotions: best-one-wins vs the coupon; free shipping rides on top.
                $promoResult = (new PromotionEvaluator())->evaluate((float) $totalAmount, (float) $discountAmount);
                $discountAmount = $promoResult->discountAmount;
                if ($promoResult->discountSource === 'promotion') {
                    $coupon = null; // promotion beat the coupon; the coupon is not consumed
                }

                // Get shipping cost based on delivery method and weight
                $deliveryMethod = \Modules\Admin\Models\DeliveryMethod::find($request->delivery_method_id);
                $calculatedPriceByKg = $totalWeight * $deliveryMethod->price_per_kg;
                $shippingCost = max($deliveryMethod->base_price, $calculatedPriceByKg);
                if ($promoResult->freeShipping) {
                    $shippingCost = 0;
                }

                // Calculate final amount
                $priceAfterDiscount = $totalAmount - $discountAmount;
                $finalAmount = $priceAfterDiscount + $shippingCost;

                // Apply wallet
                $walletUsage = 0;
                if ($request->wallet) {
                    $userWallet = UserWallet::where('user_id', Auth::id())->first();
                    if ($userWallet && $userWallet->balance > 0) {
                        $walletUsage = min($userWallet->balance, $finalAmount);
                        $finalAmount = $finalAmount - $walletUsage;
                    }
                }

                // Generate unique IDs
                $orderNumber = 'ORD-' . strtoupper(uniqid());

                do {
                    $paymentId = str_pad(random_int(0, 99999999), 8, '0', STR_PAD_LEFT);
                } while (Order::where('payment_id', $paymentId)->exists());

                // Create order
                $order = Order::create([
                    'user_id' => Auth::id(),
                    'address_id' => $request->address_id,
                    'order_number' => $orderNumber,
                    'payment_id' => $paymentId,
                    'total_amount' => $totalAmount,
                    'price_after_discount' => $priceAfterDiscount,
                    'shipping_type' => $deliveryMethod->name,
                    'shipping_cost' => $shippingCost,
                    'coupon_id' => $coupon?->id,
                    'promotion_id' => $promoResult->promotionId,
                    'free_shipping_promotion_id' => $promoResult->freeShippingPromotionId,
                    'wallet_usage' => $walletUsage,
                    'final_price' => $finalAmount,
                    'payment_status' => 'not_paid',
                    'order_status' => 'pending_payment',
                    'notes' => $request->notes,
                ]);

                // Create order items (with markup baked into the price)
                $resolver = new VendorRateResolver();
                $calc = new VendorEarningCalculator();
                foreach ($cartItems as $item) {
                    $vendor = $item->product->vendor ?? null;
                    $markup = ($vendor && $vendor->markup_percentage !== null)
                        ? (float) $vendor->markup_percentage
                        : (float) $globalMarkup;

                    $basePrice = $item->productVariant->price;
                    $markedUpUnitPrice = $basePrice * (1 + $markup / 100);
                    $itemSubtotal = $markedUpUnitPrice * $item->quantity;

                    $absorptionPct = $promoResult->discountSource === 'promotion'
                        ? (float) $promoResult->absorbedByVendorPercentage
                        : ($vendor ? $resolver->absorption($vendor) : 0.0);

                    $earning = $calc->compute(
                        (float) $basePrice,
                        (int) $item->quantity,
                        (float) $itemSubtotal,
                        (float) $discountAmount,   // winning discount amount (promo or coupon)
                        (float) $totalAmount,      // order pre-discount total
                        $vendor ? $resolver->commission($vendor) : 0.0,
                        $absorptionPct
                    );

                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $item->product_id,
                        'product_variant_id' => $item->product_variant_id,
                        'vendor_id' => $item->product->vendor_id,
                        'quantity' => $item->quantity,
                        'unit_price' => $markedUpUnitPrice,
                        'subtotal' => $itemSubtotal,
                        'final_price' => $itemSubtotal,
                        'vendor_base_subtotal' => $earning['vendor_base_subtotal'],
                        'vendor_commission_amount' => $earning['vendor_commission_amount'],
                        'vendor_discount_absorbed' => $earning['vendor_discount_absorbed'],
                        'vendor_earning' => $earning['vendor_earning'],
                    ]);
                }

                // Clear cart
                Cart::where('user_id', Auth::id())->delete();

                // AUDIT: Order created and stock reserved
                $this->logAudit($order, 'order_created', [
                    'old_payment_status' => null,
                    'new_payment_status' => 'not_paid',
                    'old_order_status' => null,
                    'new_order_status' => 'pending_payment',
                    'triggered_by' => 'user',
                    'data' => [
                        'stock_reserved' => $stockChanges,
                        'cart_items_count' => count($cartItems),
                        'total_amount' => $totalAmount,
                        'discount_amount' => $discountAmount,
                        'shipping_cost' => $shippingCost,
                        'wallet_usage' => $walletUsage,
                        'final_amount' => $finalAmount,
                        'coupon_code' => $coupon?->code
                    ],
                    'notes' => 'Order created, stock reserved, cart cleared'
                ]);

                DB::commit();

                \Modules\Telemetry\Services\CaptureService::recordCheckoutEvent(
                    $request->input('session_id'),
                    auth('sanctum')->id(),
                    'placed',
                    $order->id,
                );

                Log::info('Order created, stock reserved', [
                    'order_id' => $order->id,
                    'order_number' => $orderNumber,
                    'payment_id' => $paymentId
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                Cache::forget($lockKey);

                return response()->json([
                    'status' => false,
                    'message' => $e->getMessage()
                ], 400);
            }

            // Call Nass API (outside transaction)
            try {
                $nassResponse = $this->nassPaymentService->createTransaction([
                    'paymentId' => $paymentId,
                    'description' => "Order {$orderNumber} - " . count($cartItems) . " items",
                    'amount' => $finalAmount
                ]);

                if ($nassResponse['success'] ?? false) {
                    // AUDIT: Payment initiated
                    $this->logAudit($order, 'payment_initiated', [
                        'triggered_by' => 'system',
                        'data' => [
                            'payment_url' => $nassResponse['data']['url'] ?? null,
                            'nass_response' => $nassResponse
                        ],
                        'notes' => 'User redirected to Nass payment gateway'
                    ]);

                    Cache::forget($lockKey);

                    return response()->json([
                        'status' => true,
                        'message' => 'Redirecting to payment',
                        'data' => [
                            'order_id' => $order->id,
                            'order_number' => $orderNumber,
                            'payment_id' => $paymentId,
                            'final_amount' => $finalAmount,
                            'payment' => [
                                'type' => 'redirect',
                                'payment_url' => $nassResponse['data']['url'],
                                'transaction_params' => $nassResponse['data']['transactionParams']
                            ]
                        ]
                    ], 200);
                }

                throw new \Exception('Payment gateway returned unsuccessful response');

            } catch (\Exception $e) {
                // Nass failed - refill stock
                $stockRefilled = $this->refillOrderStock($order);

                $order->update(['payment_status' => 'failed']);

                // AUDIT: Nass failed, stock refilled
                $this->logAudit($order, 'payment_gateway_failed', [
                    'old_payment_status' => 'not_paid',
                    'new_payment_status' => 'failed',
                    'triggered_by' => 'system',
                    'data' => [
                        'error' => $e->getMessage(),
                        'stock_refilled' => $stockRefilled
                    ],
                    'notes' => 'Nass API failed, stock released back'
                ]);

                Cache::forget($lockKey);

                return response()->json([
                    'status' => false,
                    'message' => 'Payment gateway error. Please try again.'
                ], 500);
            }

        } catch (\Exception $e) {
            Cache::forget($lockKey);

            Log::error('Checkout error', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Checkout failed. Please try again.'
            ], 500);
        }
    }

    /**
     * VERIFY PAYMENT
     * 
     * Called by frontend after redirect from Nass.
     * Checks payment status with Nass API.
     */
    public function verifyPayment(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'orderId' => 'required|string|max:20',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Order ID is required'
                ], 422);
            }

            $paymentId = $request->orderId;

            // Find order
            $order = Order::with(['items.product', 'items.productVariant', 'address'])
                ->where('payment_id', $paymentId)
                ->where('user_id', Auth::id())
                ->first();

            if (!$order) {
                return response()->json([
                    'status' => false,
                    'message' => 'Order not found'
                ], 404);
            }

            // Already processed
            if (in_array($order->payment_status, ['processing', 'completed'])) {
                return response()->json([
                    'status' => true,
                    'message' => 'Order already confirmed',
                    'data' => $order
                ]);
            }

            // Check with Nass
            $nassStatus = $this->nassPaymentService->checkTransactionStatus($paymentId);

            // AUDIT: Payment verification attempted
            $this->logAudit($order, 'payment_verification_attempted', [
                'triggered_by' => 'user',
                'data' => [
                    'nass_response' => $nassStatus,
                    'current_payment_status' => $order->payment_status
                ],
                'notes' => 'User returned from payment gateway, verifying status'
            ]);

            if (!($nassStatus['success'] ?? false) || !isset($nassStatus['data'])) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unable to verify payment. If you paid, it will be confirmed shortly.',
                    'pending' => true,
                    'data' => [
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'payment_status' => $order->payment_status
                    ]
                ], 503);
            }

            // Payment successful
            if (($nassStatus['data']['responseCode'] ?? '') === '00') {

                // Handle expired order that was paid
                if ($order->payment_status === 'expired') {
                    return $this->handleExpiredButPaidOrder($order, $nassStatus['data'], 'user');
                }

                // Normal success
                $oldPaymentStatus = $order->payment_status;
                $oldOrderStatus = $order->order_status;

                DB::beginTransaction();

                try {
                    $order->update([
                        'payment_status' => 'processing',
                        'order_status' => 'confirmed',
                        'nass_rrn' => $nassStatus['data']['rrn'] ?? null,
                        'nass_int_ref' => $nassStatus['data']['intRef'] ?? null,
                        'paid_at' => now(),
                    ]);

                    $couponApplied = $this->applyCouponUsage($order);
                    $walletDeducted = $this->applyWalletDeduction($order);

                    // AUDIT: Payment verified successfully
                    $this->logAudit($order, 'payment_verified', [
                        'old_payment_status' => $oldPaymentStatus,
                        'new_payment_status' => 'processing',
                        'old_order_status' => $oldOrderStatus,
                        'new_order_status' => 'confirmed',
                        'triggered_by' => 'user',
                        'data' => [
                            'nass_rrn' => $nassStatus['data']['rrn'] ?? null,
                            'nass_int_ref' => $nassStatus['data']['intRef'] ?? null,
                            'nass_response_code' => $nassStatus['data']['responseCode'],
                            'coupon_applied' => $couponApplied,
                            'wallet_deducted' => $walletDeducted
                        ],
                        'notes' => 'Payment confirmed via Nass checkStatus API'
                    ]);

                    DB::commit();

                    // Send notification to customer
                    if ($order->user) {
                        $order->user->notify(new \App\Notifications\OrderConfirmedNotification($order));
                    }

                    // Send notification to vendors
                    $vendorIds = $order->items->pluck('vendor_id')->unique();
                    foreach ($vendorIds as $vendorId) {
                        $vendor = \Modules\Vendor\Models\Vendor::find($vendorId);
                        if ($vendor && $vendor->user) {
                            $vendorItems = $order->items->where('vendor_id', $vendorId);
                            $vendor->user->notify(new \App\Notifications\VendorOrderNotification($order, $vendorItems));
                        }
                    }

                    Log::info('Payment verified', [
                        'order_id' => $order->id,
                        'payment_id' => $paymentId
                    ]);

                    return response()->json([
                        'status' => true,
                        'message' => 'Payment confirmed',
                        'data' => $order->fresh(['items.product', 'items.productVariant', 'address'])
                    ]);

                } catch (\Exception $e) {
                    DB::rollBack();
                    throw $e;
                }
            }

            // Payment not successful
            $this->logAudit($order, 'payment_failed', [
                'triggered_by' => 'user',
                'data' => [
                    'nass_response_code' => $nassStatus['data']['responseCode'] ?? 'unknown',
                    'nass_status_msg' => $nassStatus['data']['statusMsg'] ?? null
                ],
                'notes' => 'Payment verification returned non-success response code'
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Payment not successful',
                'data' => [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'response_code' => $nassStatus['data']['responseCode'] ?? 'unknown'
                ]
            ], 400);

        } catch (\Exception $e) {
            Log::error('Payment verification error', [
                'orderId' => $request->orderId ?? 'unknown',
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Verification failed'
            ], 500);
        }
    }

    public function getInvoice(Request $request, $orderId)
    {
        try {
            $order = Order::where('id', $orderId)
                ->where('user_id', Auth::id())
                ->first();

            if (!$order) {
                return response()->json([
                    'status' => false,
                    'message' => 'Order not found'
                ], 404);
            }

            $invoiceService = new \Modules\Checkout\Services\InvoiceService();
            $invoiceData = $invoiceService->generateInvoice($order);

            return response()->json([
                'status' => true,
                'message' => 'Invoice generated successfully',
                'data' => $invoiceData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to generate invoice',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * WEBHOOK
     * 
     * Called by Nass server (backup, delayed 5-10 min).
     */
    public function nassWebhook(Request $request): JsonResponse
    {
        try {
            Log::info('Nass webhook received', $request->all());

            $data = $request->all();

            if (isset($data['test']) && $data['test'] === 'connectivity') {
                return response()->json(['status' => 'ok']);
            }

            if (empty($data) || !isset($data['orderId'])) {
                return response()->json(['status' => 'ok', 'message' => 'Invalid data']);
            }

            $paymentId = $data['orderId'];
            $order = Order::where('payment_id', $paymentId)->first();

            // Verify webhook authenticity by calling Nass checkStatus
            $nassStatus = $this->nassPaymentService->checkTransactionStatus($paymentId);
            if (!($nassStatus['success'] ?? false) || !isset($nassStatus['data'])) {
                Log::warning('Nass webhook verification failed', ['orderId' => $paymentId]);
                return response()->json(['status' => 'ok', 'message' => 'Verification failed']);
            }
            
            // Use the verified data from Nass to prevent spoofing
            $verifiedData = $nassStatus['data'];
            $data['responseCode'] = $verifiedData['responseCode'] ?? 'unknown';
            $data['rrn'] = $verifiedData['rrn'] ?? null;
            $data['intRef'] = $verifiedData['intRef'] ?? null;

            if (!$order) {
                return response()->json(['status' => 'ok', 'message' => 'Order not found']);
            }

            // AUDIT: Webhook received
            $this->logAudit($order, 'webhook_received', [
                'triggered_by' => 'webhook',
                'data' => [
                    'webhook_data' => $data,
                    'current_payment_status' => $order->payment_status
                ],
                'notes' => 'Received callback from Nass payment gateway'
            ]);

            // Already completed
            if ($order->payment_status === 'completed') {
                $order->update(['callback_data' => $data]);
                return response()->json(['status' => 'ok']);
            }

            // Processing -> completed
            if ($order->payment_status === 'processing') {
                if (isset($data['responseCode']) && $data['responseCode'] === '00') {
                    $order->update([
                        'payment_status' => 'completed',
                        'callback_data' => $data
                    ]);

                    // AUDIT: Upgraded to completed
                    $this->logAudit($order, 'payment_completed', [
                        'old_payment_status' => 'processing',
                        'new_payment_status' => 'completed',
                        'triggered_by' => 'webhook',
                        'data' => ['webhook_data' => $data],
                        'notes' => 'Payment status upgraded to completed via webhook'
                    ]);
                }
                return response()->json(['status' => 'ok']);
            }

            // Expired order received payment - refund
            if ($order->payment_status === 'expired') {
                if (isset($data['responseCode']) && $data['responseCode'] === '00') {
                    $this->handleExpiredButPaidOrder($order, $data, 'webhook');
                }
                return response()->json(['status' => 'ok']);
            }

            // not_paid -> process
            if ($order->payment_status === 'not_paid') {
                if (isset($data['responseCode']) && $data['responseCode'] === '00') {
                    DB::beginTransaction();

                    try {
                        $order->update([
                            'payment_status' => 'completed',
                            'order_status' => 'confirmed',
                            'nass_rrn' => $data['rrn'] ?? null,
                            'nass_int_ref' => $data['intRef'] ?? null,
                            'callback_data' => $data,
                            'paid_at' => now()
                        ]);

                        $couponApplied = $this->applyCouponUsage($order);
                        $walletDeducted = $this->applyWalletDeduction($order);

                        // AUDIT: Payment confirmed via webhook
                        $this->logAudit($order, 'payment_confirmed_webhook', [
                            'old_payment_status' => 'not_paid',
                            'new_payment_status' => 'completed',
                            'old_order_status' => 'pending_payment',
                            'new_order_status' => 'confirmed',
                            'triggered_by' => 'webhook',
                            'data' => [
                                'webhook_data' => $data,
                                'coupon_applied' => $couponApplied,
                                'wallet_deducted' => $walletDeducted
                            ],
                            'notes' => 'Payment confirmed directly via webhook (user did not return to site)'
                        ]);

                        DB::commit();

                        // Send notification to customer
                        if ($order->user) {
                            $order->user->notify(new \App\Notifications\OrderConfirmedNotification($order));
                        }

                        // Send notification to vendors
                        $vendorIds = $order->items->pluck('vendor_id')->unique();
                        foreach ($vendorIds as $vendorId) {
                            $vendor = \Modules\Vendor\Models\Vendor::find($vendorId);
                            if ($vendor && $vendor->user) {
                                $vendorItems = $order->items->where('vendor_id', $vendorId);
                                $vendor->user->notify(new \App\Notifications\VendorOrderNotification($order, $vendorItems));
                            }
                        }

                    } catch (\Exception $e) {
                        DB::rollBack();
                        Log::error('Webhook processing failed', ['error' => $e->getMessage()]);
                    }
                } else {
                    $order->update([
                        'payment_status' => 'failed',
                        'order_status' => 'canceled',
                        'callback_data' => json_encode($data)
                    ]);
                    $this->refillOrderStock($order);


                    // AUDIT: Payment failed via webhook
                    $this->logAudit($order, 'payment_failed_webhook', [
                        'old_payment_status' => 'not_paid',
                        'new_payment_status' => 'failed',
                        'triggered_by' => 'webhook',
                        'data' => ['webhook_data' => $data],
                        'notes' => 'Payment failed notification received via webhook'
                    ]);
                }
            }

            return response()->json(['status' => 'ok']);

        } catch (\Exception $e) {
            Log::error('Webhook error', ['error' => $e->getMessage()]);
            return response()->json(['status' => 'ok']);
        }
    }

    /**
     * Get user's pending orders
     */
    public function getPendingOrders(): JsonResponse
    {
        $pendingOrders = Order::where('user_id', Auth::id())
            ->where('payment_status', 'not_paid')
            ->where('created_at', '>', now()->subMinutes(15))
            ->orderBy('created_at', 'desc')
            ->get(['id', 'order_number', 'payment_id', 'final_price', 'created_at']);

        return response()->json([
            'status' => true,
            'data' => $pendingOrders
        ]);
    }

    /**
     * Get user's orders
     */
    public function getOrders(): JsonResponse
    {
        $orders = Order::with([
            'items.product',
            'items.product.vendor',
            'items.product.images',
            'items.productVariant',
            'items.productVariant.variantValues',
            'items.productVariant.variantValues.variantAttribute',
            'address'
        ])
            ->where('user_id', Auth::id())
            ->whereNotIn('payment_status', ['not_paid'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'status' => true,
            'data' => $orders
        ]);
    }

    /**
     * Get single order
     */
    public function getOrder($id): JsonResponse
    {
        $order = Order::with([
            'items.product',
            'items.product.images',
            'items.productVariant',
            'items.productVariant.variantValues',
            'items.productVariant.variantValues.variantAttribute',
            'address'
        ])
            ->where('user_id', Auth::id())
            ->find($id);

        if (!$order) {
            return response()->json([
                'status' => false,
                'message' => 'Order not found'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $order
        ]);
    }

    /**
     * Get order audit history
     */
    public function getOrderAudit($id): JsonResponse
    {
        $order = Order::where('user_id', Auth::id())->find($id);

        if (!$order) {
            return response()->json([
                'status' => false,
                'message' => 'Order not found'
            ], 404);
        }

        $audits = OrderAudit::where('order_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => true,
            'data' => $audits
        ]);
    }

    /**
     * Update order status (cancel/return)
     */
    public function updateOrderStatus(Request $request, $id): JsonResponse
    {
        $order = Order::where('user_id', Auth::id())->find($id);

        if (!$order) {
            return response()->json([
                'status' => false,
                'message' => 'Order not found'
            ], 404);
        }

        $oldOrderStatus = $order->order_status;

        if (in_array($order->order_status, ['processing', 'pending', 'confirmed', 'pending_payment'])) {
            $stockRefilled = $this->refillOrderStock($order);
            $order->order_status = 'cancelled';

            // AUDIT: Order cancelled
            $this->logAudit($order, 'order_cancelled', [
                'old_order_status' => $oldOrderStatus,
                'new_order_status' => 'cancelled',
                'triggered_by' => 'user',
                'data' => ['stock_refilled' => $stockRefilled],
                'notes' => 'Order cancelled by user, stock released'
            ]);

            $order->load('items');
            $ledger = new \Modules\Vendor\Services\VendorLedgerService();
            foreach ($order->items as $orderItem) {
                $ledger->reverseEarning($orderItem);
            }

        } elseif ($order->order_status === 'completed') {
            $order->order_status = 'returned';

            // AUDIT: Order returned
            $this->logAudit($order, 'order_returned', [
                'old_order_status' => $oldOrderStatus,
                'new_order_status' => 'returned',
                'triggered_by' => 'user',
                'notes' => 'Order marked as returned by user'
            ]);

        } else {
            return response()->json([
                'status' => false,
                'message' => 'Order cannot be updated'
            ], 400);
        }

        $order->save();

        return response()->json([
            'status' => true,
            'message' => 'Order status updated',
            'data' => $order
        ]);
    }

    // ─────────────────────────────────────────────────────────────────
    // HELPER METHODS
    // ─────────────────────────────────────────────────────────────────

    /**
     * Log audit entry for order
     */
    private function logAudit(Order $order, string $action, array $params = []): void
    {
        try {
            OrderAudit::create([
                'order_id' => $order->id,
                'action' => $action,
                'old_payment_status' => $params['old_payment_status'] ?? null,
                'new_payment_status' => $params['new_payment_status'] ?? null,
                'old_order_status' => $params['old_order_status'] ?? null,
                'new_order_status' => $params['new_order_status'] ?? null,
                'data' => $params['data'] ?? null,
                'triggered_by' => $params['triggered_by'] ?? 'system',
                'user_id' => Auth::id(),
                'ip_address' => request()->ip(),
                'notes' => $params['notes'] ?? null,
                'created_at' => now()
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log audit', [
                'order_id' => $order->id,
                'action' => $action,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Refill stock for all items in an order
     * Returns array of stock changes for audit
     */
    private function refillOrderStock(Order $order): array
    {
        $stockChanges = [];

        foreach ($order->items as $item) {
            $variant = ProductVariant::find($item->product_variant_id);
            
            if ($variant) {
                $oldStock = $variant->stock;
                $variant->increment('stock', $item->quantity);

                $stockChanges[] = [
                    'variant_id' => $variant->id,
                    'old_stock' => $oldStock,
                    'quantity_returned' => $item->quantity,
                    'new_stock' => $oldStock + $item->quantity
                ];
            }
        }

        Log::info('Stock refilled', ['order_id' => $order->id, 'changes' => $stockChanges]);

        return $stockChanges;
    }

    /**
     * Apply coupon usage
     * Returns true if coupon was applied
     */
    private function applyCouponUsage(Order $order): bool
    {
        if ($order->coupon_id && !$order->coupon_applied_at) {
            Coupon::where('id', $order->coupon_id)->increment('used');
            $order->update(['coupon_applied_at' => now()]);
            return true;
        }
        return false;
    }

    /**
     * Apply wallet deduction
     * Returns amount deducted or 0
     */
    private function applyWalletDeduction(Order $order): float
    {
        // Idempotency: never debit the same order's wallet usage twice.
        if ($order->wallet_usage <= 0 || $order->wallet_deducted_at) {
            return 0;
        }

        // Race-safe debit: a single conditional UPDATE that only deducts when the
        // balance actually covers it. The database row-locks the UPDATE, so two
        // concurrent confirmations can never both pass the `balance >= usage`
        // guard — the balance can never go negative and the same credit can never
        // be spent twice. (Previously this read the balance and decremented in two
        // steps without a lock, which allowed concurrent/duplicate over-spend.)
        $affected = UserWallet::where('user_id', $order->user_id)
            ->where('balance', '>=', $order->wallet_usage)
            ->decrement('balance', $order->wallet_usage);

        if ($affected > 0) {
            $wallet = UserWallet::where('user_id', $order->user_id)->first();

            WalletTransaction::create([
                'user_id' => $order->user_id,
                'wallet_id' => $wallet->id,
                'type' => 'order',
                'amount' => $order->wallet_usage,
                'order_id' => $order->id
            ]);

            $order->update(['wallet_deducted_at' => now()]);
            return (float) $order->wallet_usage;
        }

        // Insufficient balance at confirmation time. Previously the order was
        // silently confirmed as if the wallet discount had applied — letting a
        // user spend wallet credit they no longer held. Record the shortfall for
        // admin review instead of absorbing it silently.
        Log::warning('Wallet deduction shortfall: balance did not cover reserved wallet_usage', [
            'order_id' => $order->id,
            'user_id' => $order->user_id,
            'wallet_usage' => $order->wallet_usage,
        ]);
        $this->logAudit($order, 'wallet_deduction_shortfall', [
            'triggered_by' => 'system',
            'data' => ['wallet_usage' => $order->wallet_usage],
            'notes' => 'Reserved wallet credit exceeded the available balance at confirmation; flagged for review.',
        ]);
        return 0;
    }

    /**
     * Handle expired order that was actually paid
     * 
     * If stock available: Re-reserve and confirm order
     * If no stock: Refund to wallet
     */
    private function handleExpiredButPaidOrder(Order $order, array $nassData, string $triggeredBy): JsonResponse
    {
        Log::warning('Expired order was paid', ['order_id' => $order->id]);

        DB::beginTransaction();

        try {
            // Check if we can fulfill the order
            $canFulfill = true;
            $stockCheck = [];

            foreach ($order->items as $item) {
                $variant = ProductVariant::lockForUpdate()->find($item->product_variant_id);

                $available = $variant ? $variant->stock : 0;
                $stockCheck[] = [
                    'variant_id' => $item->product_variant_id,
                    'required' => $item->quantity,
                    'available' => $available,
                    'sufficient' => $available >= $item->quantity
                ];

                if (!$variant || $variant->stock < $item->quantity) {
                    $canFulfill = false;
                }
            }

            if ($canFulfill) {
                // Re-deduct stock
                $stockChanges = [];
                foreach ($order->items as $item) {
                    $variant = ProductVariant::find($item->product_variant_id);
                    $oldStock = $variant->stock;
                    
                    $variant->decrement('stock', $item->quantity);

                    $stockChanges[] = [
                        'variant_id' => $variant->id,
                        'old_stock' => $oldStock,
                        'quantity_reserved' => $item->quantity,
                        'new_stock' => $oldStock - $item->quantity
                    ];
                }

                $order->update([
                    'payment_status' => 'processing',
                    'order_status' => 'confirmed',
                    'nass_rrn' => $nassData['rrn'] ?? null,
                    'nass_int_ref' => $nassData['intRef'] ?? null,
                    'paid_at' => now(),
                ]);

                $couponApplied = $this->applyCouponUsage($order);
                $walletDeducted = $this->applyWalletDeduction($order);

                // AUDIT: Expired order recovered
                $this->logAudit($order, 'expired_order_recovered', [
                    'old_payment_status' => 'expired',
                    'new_payment_status' => 'processing',
                    'old_order_status' => 'cancelled',
                    'new_order_status' => 'confirmed',
                    'triggered_by' => $triggeredBy,
                    'data' => [
                        'stock_check' => $stockCheck,
                        'stock_re_reserved' => $stockChanges,
                        'coupon_applied' => $couponApplied,
                        'wallet_deducted' => $walletDeducted,
                        'nass_data' => $nassData
                    ],
                    'notes' => 'Order was expired but user paid - stock was available, order recovered'
                ]);

                DB::commit();

                Log::info('Expired order recovered', ['order_id' => $order->id]);

                return response()->json([
                    'status' => true,
                    'message' => 'Payment confirmed (recovered)',
                    'data' => $order->fresh(['items.product', 'items.productVariant', 'address'])
                ]);

            } else {
                // Can't fulfill - refund to wallet
                DB::rollBack();

                $refundAmount = $this->refundToWallet($order, 'Items no longer available after payment');

                // AUDIT: Expired order refunded
                $this->logAudit($order, 'expired_order_refunded', [
                    'triggered_by' => $triggeredBy,
                    'data' => [
                        'stock_check' => $stockCheck,
                        'refund_amount' => $refundAmount,
                        'refund_destination' => 'wallet',
                        'nass_data' => $nassData
                    ],
                    'notes' => 'Order was expired and user paid - stock NOT available, refunded to wallet'
                ]);

                return response()->json([
                    'status' => false,
                    'message' => 'Items no longer available. Refunded to wallet.',
                    'refunded' => true,
                    'refund_amount' => $refundAmount
                ], 400);
            }

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to handle expired but paid order', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Error processing order. Please contact support.'
            ], 500);
        }
    }

    /**
     * Refund order amount to user's wallet
     * Returns refunded amount
     */
    private function refundToWallet(Order $order, string $reason): float
    {
        $wallet = UserWallet::firstOrCreate(
            ['user_id' => $order->user_id],
            ['balance' => 0]
        );

        $refundAmount = $order->final_price;
        $wallet->increment('balance', $refundAmount);

        WalletTransaction::create([
            'user_id' => $order->user_id,
            'wallet_id' => $wallet->id,
            'type' => 'refund',
            'amount' => $refundAmount,
            'order_id' => $order->id
        ]);

        $order->update([
            'notes' => ($order->notes ?? '') . " | Refunded: {$reason}"
        ]);

        Log::info('Refunded to wallet', [
            'order_id' => $order->id,
            'amount' => $refundAmount
        ]);

        return $refundAmount;
    }
}