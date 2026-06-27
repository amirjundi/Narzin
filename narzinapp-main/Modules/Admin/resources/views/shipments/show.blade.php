<x-admin-layout>
    @section('title', $batch->batch_number)

    {{-- Header --}}
    <div class="mb-6">
        <div class="flex items-center gap-2 text-sm text-gray-500 mb-2">
            <a href="{{ route('shipments.index') }}" class="hover:text-blue-600">Shipments</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
            <span class="text-gray-900">{{ $batch->batch_number }}</span>
        </div>

        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $batch->batch_number }}</h1>
                <p class="text-gray-500 text-sm mt-1">Created by {{ $batch->admin->name ?? 'Admin' }} • {{ $batch->created_at->format('M d, Y H:i') }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('shipments.print', $batch->id) }}"
                   target="_blank"
                   class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 flex items-center gap-2 text-sm font-medium transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                    </svg>
                    Print Pickup List
                </a>
            </div>
        </div>
    </div>

    {{-- Status & Progress Bar --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4">
            <div class="flex items-center gap-4">
                @php
                    $statusConfig = [
                        'pending' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-800', 'border' => 'border-yellow-200', 'icon' => '⏳'],
                        'collecting' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-800', 'border' => 'border-blue-200', 'icon' => '🔄'],
                        'collected' => ['bg' => 'bg-indigo-100', 'text' => 'text-indigo-800', 'border' => 'border-indigo-200', 'icon' => '✅'],
                        'shipped' => ['bg' => 'bg-purple-100', 'text' => 'text-purple-800', 'border' => 'border-purple-200', 'icon' => '✈️'],
                        'delivered' => ['bg' => 'bg-green-100', 'text' => 'text-green-800', 'border' => 'border-green-200', 'icon' => '📦'],
                    ];
                    $sc = $statusConfig[$batch->status] ?? $statusConfig['pending'];
                @endphp
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium border {{ $sc['bg'] }} {{ $sc['text'] }} {{ $sc['border'] }}">
                    <span>{{ $sc['icon'] }}</span> {{ ucfirst($batch->status) }}
                </span>
                <span class="text-sm text-gray-500">{{ $vendorGroups->count() }} vendors to visit</span>
            </div>

            {{-- Status Actions --}}
            @if(!in_array($batch->status, ['shipped', 'delivered']))
                <form method="POST" action="{{ route('shipments.update-status', $batch->id) }}" class="flex items-center gap-2"
                      onsubmit="return confirm('Are you sure you want to update the batch status?')">
                    @csrf
                    @method('PATCH')
                    @if($batch->status === 'collected' || $batch->is_complete)
                        <input type="hidden" name="status" value="shipped">
                        <button type="submit"
                                class="px-4 py-2 bg-gradient-to-r from-purple-600 to-purple-500 text-white rounded-lg text-sm font-medium
                                       shadow-lg shadow-purple-500/30 hover:from-purple-700 hover:to-purple-600 transition-all flex items-center gap-2">
                            <span>✈️</span> Mark Batch as Shipped
                        </button>
                    @endif
                </form>
            @endif
        </div>

        {{-- Progress Bar --}}
        <div>
            <div class="flex items-center justify-between text-sm mb-2">
                <span class="text-gray-600">Collection Progress</span>
                <span class="font-semibold text-gray-900" id="progressText">
                    {{ $batch->resolved_items }}/{{ $batch->total_items }} items ({{ $batch->progress_percentage }}%)
                </span>
            </div>
            <div class="h-3 bg-gray-200 rounded-full overflow-hidden">
                <div id="progressBar"
                     class="h-full rounded-full transition-all duration-700 ease-out
                            {{ $batch->progress_percentage === 100 ? 'bg-gradient-to-r from-green-500 to-emerald-400' : 'bg-gradient-to-r from-blue-500 to-cyan-400' }}"
                     style="width: {{ $batch->progress_percentage }}%"></div>
            </div>
        </div>
    </div>

    {{-- Two tabs: Vendor Collection / Customer Packing --}}
    <div x-data="{ activeTab: '{{ $batch->is_complete ? 'packing' : 'vendor' }}' }" class="mb-6">
        {{-- Tab Headers --}}
        <div class="bg-white rounded-t-xl border border-gray-100 border-b-0">
            <nav class="flex">
                <button @click="activeTab = 'vendor'"
                        :class="activeTab === 'vendor' ? 'border-blue-500 text-blue-600 bg-blue-50/50' : 'border-transparent text-gray-500 hover:text-gray-700'"
                        class="flex-1 px-6 py-4 text-sm font-medium border-b-2 transition-all flex items-center justify-center gap-2">
                    <span>📍</span> Vendor Collection
                </button>
                <button @click="activeTab = 'packing'"
                        :class="activeTab === 'packing' ? 'border-blue-500 text-blue-600 bg-blue-50/50' : 'border-transparent text-gray-500 hover:text-gray-700'"
                        class="flex-1 px-6 py-4 text-sm font-medium border-b-2 transition-all flex items-center justify-center gap-2">
                    <span>📦</span> Customer Packing
                </button>
            </nav>
        </div>

        {{-- VENDOR COLLECTION TAB --}}
        <div x-show="activeTab === 'vendor'" class="space-y-4">
            @foreach($vendorGroups as $vendorId => $items)
                @php
                    $vendor = $items->first()->vendor;
                    $stats = $vendorStats[$vendorId];
                    $allCollected = $stats['pending'] === 0;
                    $vendorProgress = $stats['total'] > 0 ? round((($stats['collected'] + $stats['unavailable']) / $stats['total']) * 100) : 0;
                @endphp
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden" id="vendor-{{ $vendorId }}">
                    {{-- Vendor Header --}}
                    <div class="px-6 py-4 border-b border-gray-100
                                {{ $allCollected ? 'bg-gradient-to-r from-green-50 to-emerald-50' : 'bg-gradient-to-r from-gray-50 to-white' }}">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-xl flex items-center justify-center text-lg
                                            {{ $allCollected ? 'bg-green-100' : ($stats['collected'] > 0 ? 'bg-blue-100' : 'bg-gray-100') }}">
                                    @if($allCollected)
                                        ✅
                                    @elseif($stats['collected'] > 0)
                                        🔄
                                    @else
                                        🔴
                                    @endif
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900 text-lg">
                                        {{ $vendor->store_name_in_arabic ?? $vendor->store_name_in_german ?? 'Vendor' }}
                                    </h3>
                                    <div class="flex items-center gap-4 text-sm text-gray-500 mt-0.5">
                                        @if($vendor->phone)
                                            <span class="flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                                                {{ $vendor->phone }}
                                            </span>
                                        @endif
                                        @if($vendor->address)
                                            <span class="flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                                {{ $vendor->address }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="text-right">
                                <div class="flex items-center gap-2">
                                    <div class="w-24 h-2 bg-gray-200 rounded-full overflow-hidden">
                                        <div class="h-full rounded-full transition-all {{ $allCollected ? 'bg-green-500' : 'bg-blue-500' }}"
                                             style="width: {{ $vendorProgress }}%" id="vendorProgress-{{ $vendorId }}"></div>
                                    </div>
                                    <span class="text-sm font-medium text-gray-600" id="vendorCount-{{ $vendorId }}">
                                        {{ $stats['collected'] + $stats['unavailable'] }}/{{ $stats['total'] }}
                                    </span>
                                </div>
                                @if($allCollected)
                                    <span class="text-xs text-green-600 font-medium">All Collected</span>
                                @elseif($stats['pending'] > 0)
                                    <span class="text-xs text-gray-500">{{ $stats['pending'] }} remaining</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Items Checklist --}}
                    <div class="divide-y divide-gray-50">
                        @foreach($items as $batchItem)
                            @php
                                $orderItem = $batchItem->orderItem;
                                $order = $batchItem->order;
                                $isCollected = $batchItem->collection_status === 'collected';
                                $isUnavailable = $batchItem->collection_status === 'unavailable';
                            @endphp
                            <div class="px-6 py-3 flex items-center gap-4 transition-colors
                                        {{ $isCollected ? 'bg-green-50/50' : ($isUnavailable ? 'bg-red-50/50' : 'hover:bg-gray-50') }}"
                                 id="batchItem-{{ $batchItem->id }}">

                                {{-- Checkbox --}}
                                @if(!$isUnavailable && in_array($batch->status, ['pending', 'collecting']))
                                    <button onclick="toggleCollect({{ $batch->id }}, {{ $batchItem->id }}, this)"
                                            class="flex-shrink-0 w-6 h-6 rounded-md border-2 flex items-center justify-center transition-all cursor-pointer
                                                   {{ $isCollected
                                                      ? 'bg-green-500 border-green-500 text-white'
                                                      : 'border-gray-300 hover:border-blue-400' }}">
                                        @if($isCollected)
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                        @endif
                                    </button>
                                @elseif($isUnavailable)
                                    <div class="flex-shrink-0 w-6 h-6 rounded-md bg-red-100 border-2 border-red-200 flex items-center justify-center">
                                        <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </div>
                                @else
                                    <div class="flex-shrink-0 w-6 h-6 rounded-md border-2 border-gray-200 bg-gray-50"></div>
                                @endif

                                {{-- Product Image --}}
                                <div class="w-10 h-10 bg-gray-100 rounded-lg overflow-hidden flex-shrink-0">
                                    @if($orderItem->product && $orderItem->product->images && $orderItem->product->images->count() > 0)
                                        <img src="{{ $orderItem->product->images->first()->image }}" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-gray-400">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                        </div>
                                    @endif
                                </div>

                                {{-- Product Info --}}
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 {{ $isCollected ? 'line-through text-gray-500' : '' }} {{ $isUnavailable ? 'line-through text-red-400' : '' }}">
                                        {{ $orderItem->product->name_arabic ?? 'Product' }}
                                    </p>
                                    <div class="flex items-center gap-2 text-xs text-gray-500 mt-0.5">
                                        <span>× {{ $orderItem->quantity }}</span>
                                        @if($orderItem->productVariant && $orderItem->productVariant->variantValues)
                                            @foreach($orderItem->productVariant->variantValues as $val)
                                                <span class="bg-gray-100 px-1.5 py-0.5 rounded">
                                                    @if($val->variantAttribute->type === 'color')
                                                        <span class="inline-block w-2.5 h-2.5 rounded-full border" style="background-color: {{ $val->value }}"></span>
                                                    @else
                                                        {{ $val->value }}
                                                    @endif
                                                </span>
                                            @endforeach
                                        @endif
                                    </div>
                                </div>

                                {{-- Order & Customer Info --}}
                                <div class="text-right flex-shrink-0">
                                    <span class="text-xs font-mono text-blue-600">{{ $order->order_number }}</span>
                                    <p class="text-xs text-gray-500">{{ $order->user->name ?? 'Customer' }}</p>
                                </div>

                                {{-- Unavailable Button --}}
                                @if(!$isCollected && !$isUnavailable && in_array($batch->status, ['pending', 'collecting']))
                                    <button onclick="markUnavailable({{ $batch->id }}, {{ $batchItem->id }}, '{{ $orderItem->product->name_arabic ?? 'Product' }}', {{ $orderItem->final_price }})"
                                            class="flex-shrink-0 p-1.5 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors"
                                            title="Mark Unavailable & Refund">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                                        </svg>
                                    </button>
                                @endif

                                {{-- Refund Badge --}}
                                @if($isUnavailable && $batchItem->refund_amount)
                                    <span class="flex-shrink-0 text-xs bg-red-100 text-red-700 px-2 py-1 rounded-lg font-medium">
                                        Refunded IQD{{ number_format($batchItem->refund_amount, 2) }}
                                    </span>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    {{-- Vendor Actions --}}
                    @if(!$allCollected && in_array($batch->status, ['pending', 'collecting']) && $stats['pending'] > 0)
                        <div class="px-6 py-3 bg-gray-50 border-t border-gray-100 flex justify-end">
                            <button onclick="collectAllVendor({{ $batch->id }}, {{ $vendorId }})"
                                    class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700 transition-colors flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Mark All Collected ({{ $stats['pending'] }})
                            </button>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- CUSTOMER PACKING TAB --}}
        <div x-show="activeTab === 'packing'" class="space-y-4">
            @foreach($customerGroups as $orderId => $items)
                @php
                    $order = $items->first()->order;
                    $user = $order->user;
                    $address = $order->address;
                    $collectedItems = $items->where('collection_status', 'collected');
                    $unavailableItems = $items->where('collection_status', 'unavailable');
                @endphp
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    {{-- Customer Header --}}
                    <div class="px-6 py-4 bg-gradient-to-r from-indigo-50 to-purple-50 border-b border-gray-100">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center text-lg">📦</div>
                                <div>
                                    <h3 class="font-semibold text-gray-900">{{ $user->name ?? 'Customer' }}</h3>
                                    <span class="text-xs font-mono text-indigo-600">{{ $order->order_number }}</span>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-medium text-gray-900">{{ $collectedItems->count() }} items to pack</p>
                                @if($unavailableItems->count() > 0)
                                    <p class="text-xs text-red-500">{{ $unavailableItems->count() }} unavailable</p>
                                @endif
                            </div>
                        </div>

                        {{-- Shipping Address --}}
                        @if($address)
                            <div class="mt-3 flex items-start gap-2 text-sm text-gray-600 bg-white/60 rounded-lg p-3">
                                <svg class="w-4 h-4 text-gray-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                <div>
                                    <p>{{ $address->address }}</p>
                                    @if($address->city)
                                        <p>{{ $address->city->name ?? '' }}{{ $address->country ? ', ' . ($address->country->name ?? '') : '' }}</p>
                                    @endif
                                    @if($address->phone_number)
                                        <p class="flex items-center gap-1 mt-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                                            {{ $address->phone_number }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Items grouped by vendor --}}
                    @php $itemsByVendor = $items->groupBy('vendor_id'); @endphp
                    @foreach($itemsByVendor as $vId => $vendorItems)
                        @php $v = $vendorItems->first()->vendor; @endphp
                        <div class="px-6 py-2 bg-gray-50 border-b border-gray-100">
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">
                                From {{ $v->store_name_in_arabic ?? $v->store_name_in_german ?? 'Vendor' }}
                            </p>
                        </div>
                        <div class="divide-y divide-gray-50">
                            @foreach($vendorItems as $batchItem)
                                @php $oi = $batchItem->orderItem; @endphp
                                <div class="px-6 py-3 flex items-center gap-3
                                            {{ $batchItem->collection_status === 'unavailable' ? 'bg-red-50/50' : '' }}">
                                    @if($batchItem->collection_status === 'collected')
                                        <span class="text-green-500">✓</span>
                                    @elseif($batchItem->collection_status === 'unavailable')
                                        <span class="text-red-500">✗</span>
                                    @else
                                        <span class="text-gray-300">○</span>
                                    @endif

                                    <div class="w-8 h-8 bg-gray-100 rounded overflow-hidden flex-shrink-0">
                                        @if($oi->product && $oi->product->images && $oi->product->images->count() > 0)
                                            <img src="{{ $oi->product->images->first()->image }}" class="w-full h-full object-cover">
                                        @endif
                                    </div>

                                    <div class="flex-1">
                                        <span class="text-sm {{ $batchItem->collection_status === 'unavailable' ? 'line-through text-red-400' : 'text-gray-900' }}">
                                            {{ $oi->product->name_arabic ?? 'Product' }}
                                        </span>
                                        <span class="text-xs text-gray-500 ml-2">× {{ $oi->quantity }}</span>
                                    </div>

                                    @if($batchItem->collection_status === 'unavailable')
                                        <span class="text-xs bg-red-100 text-red-700 px-2 py-0.5 rounded font-medium">
                                            ⚠️ Unavailable — IQD{{ number_format($batchItem->refund_amount, 2) }} refunded
                                        </span>
                                    @else
                                        <span class="text-xs text-gray-500">IQD{{ number_format($oi->final_price, 2) }}</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endforeach

                    {{-- Order Total --}}
                    <div class="px-6 py-3 bg-gray-50 border-t border-gray-100 flex justify-between items-center">
                        <span class="text-sm text-gray-600">Items: {{ $collectedItems->count() }}</span>
                        <span class="text-sm font-semibold text-gray-900">
                            Order Total: IQD{{ number_format($order->final_price, 2) }}
                        </span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Unavailable Confirmation Modal --}}
    <div id="unavailableModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
        <div class="bg-white rounded-xl shadow-xl max-w-md w-full mx-4 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Mark Item Unavailable</h3>
            <p class="text-sm text-gray-600 mb-4">
                <span id="unavailableProductName" class="font-medium"></span> will be marked as unavailable and
                <span id="unavailableRefundAmount" class="font-semibold text-red-600"></span> will be refunded to the customer's wallet.
            </p>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Reason (optional)</label>
                <textarea id="unavailableNotes" rows="2"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-red-500"
                          placeholder="e.g., Out of stock at vendor"></textarea>
            </div>

            <div class="flex gap-3">
                <button onclick="closeUnavailableModal()"
                        class="flex-1 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 font-medium text-sm">
                    Cancel
                </button>
                <button onclick="confirmUnavailable()"
                        class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium text-sm">
                    Confirm & Refund
                </button>
            </div>
        </div>
    </div>

    {{-- Toast Notification --}}
    <div id="toast" class="hidden fixed bottom-6 right-6 z-50 px-6 py-3 rounded-xl shadow-2xl text-white text-sm font-medium transition-all transform translate-y-4 opacity-0"></div>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        let pendingUnavailable = null;

        function showToast(message, type = 'success') {
            const toast = document.getElementById('toast');
            toast.className = 'fixed bottom-6 right-6 z-50 px-6 py-3 rounded-xl shadow-2xl text-white text-sm font-medium transition-all transform';
            toast.classList.add(type === 'success' ? 'bg-green-600' : 'bg-red-600');
            toast.textContent = message;
            toast.classList.remove('hidden', 'translate-y-4', 'opacity-0');

            setTimeout(() => {
                toast.classList.add('translate-y-4', 'opacity-0');
                setTimeout(() => toast.classList.add('hidden'), 300);
            }, 3000);
        }

        function updateProgress(data) {
            const progressBar = document.getElementById('progressBar');
            const progressText = document.getElementById('progressText');
            const resolved = data.batch_collected + (data.batch_total - data.batch_collected - document.querySelectorAll('[data-status="pending"]').length);

            progressBar.style.width = data.batch_progress + '%';
            progressText.textContent = Math.round(data.batch_progress / 100 * data.batch_total) + '/' + data.batch_total + ' items (' + data.batch_progress + '%)';

            if (data.batch_progress === 100) {
                progressBar.className = 'h-full rounded-full transition-all duration-700 ease-out bg-gradient-to-r from-green-500 to-emerald-400';
            }

            if (data.is_complete) {
                setTimeout(() => location.reload(), 500);
            }
        }

        function toggleCollect(batchId, itemId, btn) {
            fetch(`/shipments/${batchId}/collect`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ item_id: itemId })
            })
            .then(r => r.json())
            .then(data => {
                if (data.status) {
                    const row = document.getElementById('batchItem-' + itemId);
                    if (data.item_status === 'collected') {
                        btn.className = 'flex-shrink-0 w-6 h-6 rounded-md border-2 flex items-center justify-center transition-all cursor-pointer bg-green-500 border-green-500 text-white';
                        btn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>';
                        row.classList.add('bg-green-50/50');
                        showToast('Item collected!');
                    } else {
                        btn.className = 'flex-shrink-0 w-6 h-6 rounded-md border-2 flex items-center justify-center transition-all cursor-pointer border-gray-300 hover:border-blue-400';
                        btn.innerHTML = '';
                        row.classList.remove('bg-green-50/50');
                        showToast('Item unchecked', 'error');
                    }
                    updateProgress(data);
                }
            })
            .catch(() => showToast('Error updating item', 'error'));
        }

        function collectAllVendor(batchId, vendorId) {
            if (!confirm('Mark all pending items from this vendor as collected?')) return;

            fetch(`/shipments/${batchId}/collect-vendor`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ vendor_id: vendorId })
            })
            .then(r => r.json())
            .then(data => {
                if (data.status) {
                    showToast(`${data.collected_count} items collected!`);
                    setTimeout(() => location.reload(), 500);
                }
            })
            .catch(() => showToast('Error collecting vendor items', 'error'));
        }

        function markUnavailable(batchId, itemId, productName, price) {
            pendingUnavailable = { batchId, itemId };
            document.getElementById('unavailableProductName').textContent = productName;
            document.getElementById('unavailableRefundAmount').textContent = 'IQD' + price.toLocaleString(undefined, {minimumFractionDigits: 2});
            document.getElementById('unavailableModal').classList.remove('hidden');
        }

        function closeUnavailableModal() {
            document.getElementById('unavailableModal').classList.add('hidden');
            pendingUnavailable = null;
        }

        function confirmUnavailable() {
            if (!pendingUnavailable) return;

            const notes = document.getElementById('unavailableNotes').value;

            fetch(`/shipments/${pendingUnavailable.batchId}/unavailable`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    item_id: pendingUnavailable.itemId,
                    notes: notes
                })
            })
            .then(r => r.json())
            .then(data => {
                closeUnavailableModal();
                if (data.status) {
                    showToast(data.message);
                    setTimeout(() => location.reload(), 800);
                } else {
                    showToast(data.message || 'Error', 'error');
                }
            })
            .catch(() => {
                closeUnavailableModal();
                showToast('Error marking item unavailable', 'error');
            });
        }
    </script>
</x-admin-layout>
