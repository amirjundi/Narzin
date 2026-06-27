<x-admin-layout>
    @section('title', 'Create Shipment Batch')

    {{-- Header --}}
    <div class="mb-6">
        <div class="flex items-center gap-2 text-sm text-gray-500 mb-2">
            <a href="{{ route('shipments.index') }}" class="hover:text-blue-600">Shipments</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
            <span class="text-gray-900">Create Batch</span>
        </div>
        <h1 class="text-2xl font-bold text-gray-900">Create Shipment Batch</h1>
        <p class="text-gray-500 text-sm mt-1">Select confirmed orders to include in this shipping run</p>
    </div>

    @if($orders->isEmpty())
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-12 text-center">
            <div class="flex flex-col items-center gap-4">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <h2 class="text-lg font-semibold text-gray-900">All caught up!</h2>
                <p class="text-gray-500 max-w-md">No confirmed orders with uncollected items. All items are either already in a batch or have been shipped.</p>
                <a href="{{ route('shipments.index') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">← Back to Batches</a>
            </div>
        </div>
    @else
        <form method="POST" action="{{ route('shipments.store') }}" id="batchForm">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Orders List --}}
                <div class="lg:col-span-2 space-y-4">
                    {{-- Select All Bar --}}
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <input type="checkbox" id="selectAll"
                                   class="w-5 h-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500 cursor-pointer">
                            <label for="selectAll" class="text-sm font-medium text-gray-700 cursor-pointer">
                                Select All Orders ({{ $orders->count() }})
                            </label>
                        </div>
                        <span class="text-sm text-gray-500" id="selectedCount">0 orders selected</span>
                    </div>

                    {{-- Vendor Groups --}}
                    @foreach($vendorGroups as $vendorId => $group)
                        @php $vendor = $group['vendor']; @endphp
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                            {{-- Vendor Header --}}
                            <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-white border-b border-gray-100">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <h3 class="font-semibold text-gray-900">
                                                {{ $vendor->store_name_in_arabic ?? $vendor->store_name_in_german ?? 'Unknown Vendor' }}
                                            </h3>
                                            <div class="flex items-center gap-3 text-xs text-gray-500 mt-0.5">
                                                @if($vendor->phone)
                                                    <span class="flex items-center gap-1">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                                                        {{ $vendor->phone }}
                                                    </span>
                                                @endif
                                                @if($vendor->address)
                                                    <span class="flex items-center gap-1">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                                        {{ $vendor->address }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-sm font-medium text-gray-900">{{ $group['items']->count() }} items</span>
                                        <p class="text-xs text-gray-500">{{ $group['order_count'] }} orders</p>
                                    </div>
                                </div>
                            </div>

                            {{-- Items --}}
                            <div class="divide-y divide-gray-50">
                                @foreach($group['items'] as $entry)
                                    @php
                                        $item = $entry['order_item'];
                                        $order = $entry['order'];
                                    @endphp
                                    <div class="px-6 py-3 flex items-center gap-4 hover:bg-gray-50 transition-colors">
                                        <input type="checkbox"
                                               name="order_ids[]"
                                               value="{{ $order->id }}"
                                               class="order-checkbox w-4 h-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500"
                                               data-order-id="{{ $order->id }}">

                                        {{-- Product Image --}}
                                        <div class="w-10 h-10 bg-gray-100 rounded-lg overflow-hidden flex-shrink-0">
                                            @if($item->product && $item->product->images && $item->product->images->count() > 0)
                                                <img src="{{ $item->product->images->first()->image }}"
                                                     alt="" class="w-full h-full object-cover">
                                            @else
                                                <div class="w-full h-full flex items-center justify-center text-gray-400">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                    </svg>
                                                </div>
                                            @endif
                                        </div>

                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-gray-900 truncate">
                                                {{ $item->product->name_arabic ?? 'Product' }}
                                            </p>
                                            <div class="flex items-center gap-2 text-xs text-gray-500">
                                                <span>× {{ $item->quantity }}</span>
                                                @if($item->productVariant && $item->productVariant->variantValues)
                                                    @foreach($item->productVariant->variantValues as $val)
                                                        <span class="bg-gray-100 px-1.5 py-0.5 rounded">
                                                            {{ $val->variantAttribute->name_arabic ?? '' }}: {{ $val->value }}
                                                        </span>
                                                    @endforeach
                                                @endif
                                            </div>
                                        </div>

                                        <div class="text-right flex-shrink-0">
                                            <span class="text-xs font-mono text-blue-600">{{ $order->order_number }}</span>
                                            <p class="text-xs text-gray-500">{{ $order->user->name ?? 'Customer' }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Sidebar --}}
                <div class="space-y-4">
                    {{-- Summary Card --}}
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 sticky top-6">
                        <h3 class="font-semibold text-gray-900 mb-4">Batch Summary</h3>

                        <div class="space-y-3 mb-6">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Orders</span>
                                <span class="font-medium text-gray-900" id="summaryOrders">0</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Vendors to visit</span>
                                <span class="font-medium text-gray-900" id="summaryVendors">{{ $vendorGroups->count() }}</span>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Notes (optional)</label>
                            <textarea name="notes" rows="3"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                      placeholder="Add notes for this batch..."></textarea>
                        </div>

                        <button type="submit" id="createBtn" disabled
                                class="w-full px-4 py-3 bg-gradient-to-r from-blue-600 to-blue-500 text-white rounded-lg font-medium text-sm
                                       shadow-lg shadow-blue-500/30 hover:from-blue-700 hover:to-blue-600 transition-all
                                       disabled:opacity-50 disabled:cursor-not-allowed disabled:shadow-none">
                            Create Batch
                        </button>
                    </div>
                </div>
            </div>
        </form>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const selectAll = document.getElementById('selectAll');
                const checkboxes = document.querySelectorAll('.order-checkbox');
                const createBtn = document.getElementById('createBtn');
                const selectedCount = document.getElementById('selectedCount');
                const summaryOrders = document.getElementById('summaryOrders');

                function updateCount() {
                    // Deduplicate by order ID
                    const selectedIds = new Set();
                    checkboxes.forEach(cb => {
                        if (cb.checked) selectedIds.add(cb.dataset.orderId);
                    });
                    const count = selectedIds.size;
                    selectedCount.textContent = count + ' orders selected';
                    summaryOrders.textContent = count;
                    createBtn.disabled = count === 0;
                }

                selectAll.addEventListener('change', function() {
                    checkboxes.forEach(cb => cb.checked = this.checked);
                    updateCount();
                });

                checkboxes.forEach(cb => {
                    cb.addEventListener('change', updateCount);
                });
            });
        </script>
    @endif
</x-admin-layout>
