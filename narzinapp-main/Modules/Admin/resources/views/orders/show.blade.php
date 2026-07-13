<x-admin-layout>
    {{-- Header --}}
    <div class="mb-6">
        <div class="flex items-center gap-2 text-sm text-gray-500 mb-2">
            <a href="{{ route('orders.index') }}" class="hover:text-blue-600">Orders</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
            <span class="text-gray-900">{{ $order->order_number }}</span>
        </div>

        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Order {{ $order->order_number }}</h1>
                <p class="text-gray-500 text-sm mt-1">Payment ID: {{ $order->payment_id }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('orders.print', $order->id) }}" 
                   target="_blank"
                   class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 flex items-center gap-2 text-sm font-medium">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                    </svg>
                    Print
                </a>
                @if(in_array($order->payment_status, ['processing', 'completed']))
                    <button onclick="openRefundModal()" 
                            class="px-4 py-2 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 flex items-center gap-2 text-sm font-medium">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>
                        </svg>
                        Refund
                    </button>
                @endif
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Main Content --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Order Status Card --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Order Status</h2>
                
                <div class="flex flex-wrap gap-4 mb-6">
                    {{-- Payment Status --}}
                    <div class="flex-1 min-w-[150px]">
                        <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Payment</p>
                        @php
                            $paymentColors = [
                                'not_paid' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                                'processing' => 'bg-blue-100 text-blue-800 border-blue-200',
                                'completed' => 'bg-green-100 text-green-800 border-green-200',
                                'failed' => 'bg-red-100 text-red-800 border-red-200',
                                'expired' => 'bg-gray-100 text-gray-800 border-gray-200',
                                'refunded' => 'bg-purple-100 text-purple-800 border-purple-200',
                            ];
                            $paymentColor = $paymentColors[$order->payment_status] ?? 'bg-gray-100 text-gray-800 border-gray-200';
                        @endphp
                        <span class="inline-flex items-center px-3 py-1 rounded-lg text-sm font-medium border {{ $paymentColor }}">
                            {{ ucfirst(str_replace('_', ' ', $order->payment_status)) }}
                        </span>
                    </div>

                    {{-- Order Status --}}
                    <div class="flex-1 min-w-[150px]">
                        <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Order</p>
                        @php
                            $statusColors = [
                                'pending_payment' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                                'confirmed' => 'bg-blue-100 text-blue-800 border-blue-200',
                                'processing' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
                                'shipped' => 'bg-purple-100 text-purple-800 border-purple-200',
                                'delivered' => 'bg-green-100 text-green-800 border-green-200',
                                'cancelled' => 'bg-red-100 text-red-800 border-red-200',
                            ];
                            $statusColor = $statusColors[$order->order_status] ?? 'bg-gray-100 text-gray-800 border-gray-200';
                        @endphp
                        <span class="inline-flex items-center px-3 py-1 rounded-lg text-sm font-medium border {{ $statusColor }}">
                            {{ ucfirst(str_replace('_', ' ', $order->order_status)) }}
                        </span>
                    </div>
                </div>

                {{-- Update Status Form --}}
                @if(!in_array($order->order_status, ['delivered', 'cancelled']))
                    <form method="POST" action="{{ route('orders.update.status', $order->id) }}" class="border-t border-gray-100 pt-4">
                        @csrf
                        @method('PATCH')
                        
                        <div class="flex flex-wrap gap-3">
                            <select name="order_status" class="flex-1 min-w-[150px] px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                                <option value="pending_payment" {{ $order->order_status === 'pending_payment' ? 'selected' : '' }}>Pending Payment</option>
                                <option value="confirmed" {{ $order->order_status === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                <option value="processing" {{ $order->order_status === 'processing' ? 'selected' : '' }}>Processing</option>
                                <option value="shipped" {{ $order->order_status === 'shipped' ? 'selected' : '' }}>Shipped</option>
                                <option value="delivered" {{ $order->order_status === 'delivered' ? 'selected' : '' }}>Delivered</option>
                                <option value="cancelled" {{ $order->order_status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                            <select name="cancellation_reason" class="flex-1 min-w-[150px] px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                                <option value="">Cancellation reason (if cancelling)</option>
                                <option value="out_of_stock" {{ $order->cancellation_reason === 'out_of_stock' ? 'selected' : '' }}>Out of stock</option>
                                <option value="customer_request" {{ $order->cancellation_reason === 'customer_request' ? 'selected' : '' }}>Customer request</option>
                                <option value="fraud_suspected" {{ $order->cancellation_reason === 'fraud_suspected' ? 'selected' : '' }}>Fraud suspected</option>
                                <option value="pricing_error" {{ $order->cancellation_reason === 'pricing_error' ? 'selected' : '' }}>Pricing error</option>
                                <option value="other" {{ $order->cancellation_reason === 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                            <input type="text"
                                   name="notes" 
                                   placeholder="Add a note (optional)"
                                   class="flex-1 min-w-[200px] px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">
                                Update
                            </button>
                        </div>
                    </form>
                @endif
            </div>

            {{-- Order Items --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h2 class="text-lg font-semibold text-gray-900">Order Items</h2>
                </div>
                
                <div class="divide-y divide-gray-100">
                    @foreach($order->items as $item)
                        <div class="p-4 flex gap-4">
                            {{-- Product Image --}}
                            <div class="w-16 h-16 bg-gray-100 rounded-lg overflow-hidden flex-shrink-0">
                                @if($item->product && $item->product->images && $item->product->images->count() > 0)
                                    <img src="{{ $item->product->images->first()->image }}" 
                                         alt="{{ $item->product->name_arabic }}"
                                         class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-gray-400">
                                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                @endif
                            </div>

                            {{-- Product Details --}}
                            <div class="flex-1">
                                <h3 class="font-medium text-gray-900">{{ $item->product->name_arabic ?? 'Product' }}</h3>
                                <p class="text-sm text-gray-500">{{ $item->product->name_german ?? '' }}</p>
                                
                                {{-- Variant Info --}}
                                @if($item->productVariant && $item->productVariant->variantValues)
                                    <div class="flex flex-wrap gap-2 mt-1">
                                        @foreach($item->productVariant->variantValues as $value)
                                            <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded">
                                                {{ $value->variantAttribute->name_arabic ?? '' }}: 
                                                @if($value->variantAttribute->type === 'color')
                                                    <span class="inline-block w-3 h-3 rounded-full border" style="background-color: {{ $value->value }}"></span>
                                                @else
                                                    {{ $value->value }}
                                                @endif
                                            </span>
                                        @endforeach
                                    </div>
                                @endif

                                <div class="flex items-center gap-4 mt-2 text-sm">
                                    <span class="text-gray-600">Qty: {{ $item->quantity }}</span>
                                    <span class="text-gray-600">@ IQD{{ number_format($item->unit_price, 2) }}</span>
                                </div>
                            </div>

                            {{-- Item Total --}}
                            <div class="text-right">
                                <p class="font-semibold text-gray-900">IQD{{ number_format($item->subtotal, 2) }}</p>
                                @if($item->vendor)
                                    <p class="text-xs text-gray-500 mt-1">{{ $item->vendor->name ?? 'Vendor' }}</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Order Summary --}}
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
                    <div class="space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Subtotal</span>
                            <span class="text-gray-900">IQD{{ number_format($order->total_amount, 2) }}</span>
                        </div>
                        
                        @if($order->total_amount != $order->price_after_discount)
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Discount</span>
                                <span class="text-green-600">-IQD{{ number_format($order->total_amount - $order->price_after_discount, 2) }}</span>
                            </div>
                        @endif
                        
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Shipping ({{ ucfirst($order->shipping_type) }})</span>
                            <span class="text-gray-900">IQD{{ number_format($order->shipping_cost, 2) }}</span>
                        </div>
                        
                        @if($order->wallet_usage > 0)
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Wallet Credit</span>
                                <span class="text-green-600">-IQD{{ number_format($order->wallet_usage, 2) }}</span>
                            </div>
                        @endif
                        
                        <div class="flex justify-between text-lg font-semibold pt-2 border-t border-gray-200">
                            <span class="text-gray-900">Total</span>
                            <span class="text-gray-900">IQD{{ number_format($order->final_price, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Audit History --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h2 class="text-lg font-semibold text-gray-900">Activity Log</h2>
                </div>
                
                <div class="p-6">
                    @if($audits->count() > 0)
                        <div class="space-y-4">
                            @foreach($audits as $audit)
                                <div class="flex gap-4">
                                    <div class="flex-shrink-0">
                                        @php
                                            $iconColor = match($audit->triggered_by) {
                                                'user' => 'bg-blue-100 text-blue-600',
                                                'admin' => 'bg-purple-100 text-purple-600',
                                                'webhook' => 'bg-green-100 text-green-600',
                                                'cron' => 'bg-orange-100 text-orange-600',
                                                default => 'bg-gray-100 text-gray-600'
                                            };
                                        @endphp
                                        <div class="w-8 h-8 rounded-full {{ $iconColor }} flex items-center justify-center">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex items-center justify-between">
                                            <p class="font-medium text-gray-900">
                                                {{ ucfirst(str_replace('_', ' ', $audit->action)) }}
                                            </p>
                                            <span class="text-xs text-gray-500">
                                                {{ $audit->created_at}}
                                            </span>
                                        </div>
                                        
                                        @if($audit->notes)
                                            <p class="text-sm text-gray-600 mt-1">{{ $audit->notes }}</p>
                                        @endif
                                        
                                        <div class="flex flex-wrap gap-2 mt-2">
                                            @if($audit->old_payment_status && $audit->new_payment_status)
                                                <span class="text-xs bg-gray-100 px-2 py-0.5 rounded">
                                                    Payment: {{ $audit->old_payment_status }} → {{ $audit->new_payment_status }}
                                                </span>
                                            @endif
                                            @if($audit->old_order_status && $audit->new_order_status)
                                                <span class="text-xs bg-gray-100 px-2 py-0.5 rounded">
                                                    Status: {{ $audit->old_order_status }} → {{ $audit->new_order_status }}
                                                </span>
                                            @endif
                                            <span class="text-xs text-gray-500">
                                                by {{ ucfirst($audit->triggered_by) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500 text-center py-4">No activity recorded</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Customer Info --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Customer</h2>
                
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-12 h-12 bg-gray-200 rounded-full flex items-center justify-center text-lg font-medium text-gray-600">
                        {{ strtoupper(substr($order->user->name ?? 'G', 0, 1)) }}
                    </div>
                    <div>
                        <p class="font-medium text-gray-900">{{ $order->user->name ?? 'Guest' }}</p>
                        <p class="text-sm text-gray-500">{{ $order->user->email ?? '' }}</p>
                    </div>
                </div>
                
                @if($order->user)
                    <a href="#" class="text-sm text-blue-600 hover:text-blue-800">View Customer →</a>
                @endif
            </div>

            {{-- Shipping Address --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Shipping Address</h2>
                
                @if($order->address)
                    <div class="space-y-2 text-sm">
                        @if($order->address->title)
                            <p class="font-medium text-gray-900">{{ $order->address->title }}</p>
                        @endif
                        <p class="text-gray-600">{{ $order->address->address }}</p>
                        @if($order->address->city)
                            <p class="text-gray-600">{{ $order->address->city->name ?? '' }}</p>
                        @endif
                        @if($order->address->country)
                            <p class="text-gray-600">{{ $order->address->country ?? '' }}</p>
                        @endif
                        @if($order->address->postal_code)
                            <p class="text-gray-600">Postal: {{ $order->address->postal_code }}</p>
                        @endif
                        @if($order->address->phone_number)
                            <p class="text-gray-600 flex items-center gap-2 mt-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                </svg>
                                {{ $order->address->phone_number }}
                            </p>
                        @endif
                    </div>
                @else
                    <p class="text-gray-500">No address provided</p>
                @endif
            </div>

            {{-- Order Notes --}}
            @if($order->notes)
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Notes</h2>
                    <p class="text-sm text-gray-600">{{ $order->notes }}</p>
                </div>
            @endif

            {{-- Payment Info --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Payment Details</h2>
                
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Payment ID</span>
                        <span class="font-mono text-gray-900">{{ $order->payment_id }}</span>
                    </div>
                    @if($order->nass_rrn)
                        <div class="flex justify-between">
                            <span class="text-gray-600">Nass RRN</span>
                            <span class="font-mono text-gray-900">{{ $order->nass_rrn }}</span>
                        </div>
                    @endif
                    @if($order->nass_int_ref)
                        <div class="flex justify-between">
                            <span class="text-gray-600">Nass Ref</span>
                            <span class="font-mono text-gray-900 text-xs">{{ $order->nass_int_ref }}</span>
                        </div>
                    @endif
                    @if($order->paid_at)
                        <div class="flex justify-between">
                            <span class="text-gray-600">Paid At</span>
                            <span class="text-gray-900">{{ $order->paid_at }}</span>
                        </div>
                    @endif
                    @if($order->coupon)
                        <div class="flex justify-between">
                            <span class="text-gray-600">Coupon</span>
                            <span class="text-green-600 font-medium">{{ $order->coupon->code }}</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Timestamps --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Timeline</h2>
                
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Created</span>
                        <span class="text-gray-900">{{ $order->created_at }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Updated</span>
                        <span class="text-gray-900">{{ $order->updated_at }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Refund Modal --}}
    <div id="refundModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
        <div class="bg-white rounded-xl shadow-xl max-w-md w-full mx-4 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Refund Order</h3>
            
            <form method="POST" action="{{ route('orders.refund', $order->id) }}">
                @csrf
                
                <div class="mb-4">
                    <p class="text-gray-600 mb-2">Amount to refund:</p>
                    <p class="text-2xl font-bold text-gray-900">IQD{{ number_format($order->final_price, 2) }}</p>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Reason</label>
                    <textarea name="reason" 
                              rows="3" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                              placeholder="Enter reason for refund"></textarea>
                </div>
                
                <div class="flex gap-3">
                    <button type="button" 
                            onclick="closeRefundModal()"
                            class="flex-1 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 font-medium">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium">
                        Confirm Refund
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openRefundModal() {
            document.getElementById('refundModal').classList.remove('hidden');
        }
        
        function closeRefundModal() {
            document.getElementById('refundModal').classList.add('hidden');
        }
    </script>
</x-admin-layout>