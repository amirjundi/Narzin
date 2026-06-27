<x-admin-layout>
    @section('title', "Today's Pickups")

    {{-- Header --}}
    <div class="mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">📅 Today's Pickups</h1>
                <p class="text-gray-500 text-sm mt-1">{{ now()->format('l, F j, Y') }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('shipments.index') }}"
                   class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 flex items-center gap-2 text-sm font-medium transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                    </svg>
                    All Batches
                </a>
                @if($unbatchedOrderCount > 0)
                    <a href="{{ route('shipments.create') }}"
                       class="px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-500 text-white rounded-lg hover:from-blue-700 hover:to-blue-600 flex items-center gap-2 text-sm font-medium shadow-lg shadow-blue-500/30 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Create Batch ({{ $unbatchedOrderCount }} orders)
                    </a>
                @endif
            </div>
        </div>
    </div>

    {{-- Quick Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center text-lg">📦</div>
                <div>
                    <p class="text-2xl font-bold text-gray-900">{{ $todayBatches->count() }}</p>
                    <p class="text-xs text-gray-500">Today's Batches</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center text-lg">🏪</div>
                <div>
                    <p class="text-2xl font-bold text-gray-900">{{ $vendorSummary->count() }}</p>
                    <p class="text-xs text-gray-500">Vendors to Visit</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center text-lg">📋</div>
                <div>
                    <p class="text-2xl font-bold text-gray-900">{{ $unbatchedOrderCount }}</p>
                    <p class="text-xs text-gray-500">Unbatched Orders</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Vendor Visit List --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-orange-50 to-yellow-50">
                <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                    <span>📍</span> Vendors to Visit Today
                </h2>
            </div>

            @if($vendorSummary->isEmpty())
                <div class="p-8 text-center">
                    <p class="text-gray-500">No vendors to visit today</p>
                </div>
            @else
                <div class="divide-y divide-gray-100">
                    @foreach($vendorSummary as $summary)
                        @php
                            $vendor = $summary['vendor'];
                            $allDone = $summary['pending'] === 0;
                            $progress = $summary['total_items'] > 0 ? round((($summary['collected'] + $summary['unavailable']) / $summary['total_items']) * 100) : 0;
                        @endphp
                        <div class="p-4 {{ $allDone ? 'bg-green-50/50' : '' }}">
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center gap-3">
                                    <span class="text-lg">{{ $allDone ? '✅' : '🏪' }}</span>
                                    <div>
                                        <p class="font-medium text-gray-900">
                                            {{ $vendor->store_name_in_arabic ?? $vendor->store_name_in_german ?? 'Vendor' }}
                                        </p>
                                        <div class="flex items-center gap-3 text-xs text-gray-500">
                                            @if($vendor->phone)
                                                <span>📞 {{ $vendor->phone }}</span>
                                            @endif
                                            @if($vendor->address)
                                                <span>📍 {{ \Illuminate\Support\Str::limit($vendor->address, 30) }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="text-sm font-medium {{ $allDone ? 'text-green-600' : 'text-gray-900' }}">
                                        {{ $summary['collected'] + $summary['unavailable'] }}/{{ $summary['total_items'] }}
                                    </span>
                                </div>
                            </div>
                            <div class="h-1.5 bg-gray-200 rounded-full overflow-hidden">
                                <div class="h-full rounded-full {{ $allDone ? 'bg-green-500' : 'bg-blue-500' }}" style="width: {{ $progress }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Today's Batches --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-blue-50 to-indigo-50">
                <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                    <span>📦</span> Today's Batches
                </h2>
            </div>

            @if($todayBatches->isEmpty())
                <div class="p-8 text-center">
                    <p class="text-gray-500 mb-3">No batches created today</p>
                    @if($unbatchedOrderCount > 0)
                        <a href="{{ route('shipments.create') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">Create your first batch →</a>
                    @endif
                </div>
            @else
                <div class="divide-y divide-gray-100">
                    @foreach($todayBatches as $batch)
                        <a href="{{ route('shipments.show', $batch->id) }}" class="block p-4 hover:bg-gray-50 transition-colors">
                            <div class="flex items-center justify-between mb-2">
                                <div>
                                    <p class="font-medium text-blue-600">{{ $batch->batch_number }}</p>
                                    <p class="text-xs text-gray-500">{{ $batch->created_at->format('H:i') }} • {{ $batch->admin->name ?? 'Admin' }}</p>
                                </div>
                                @php
                                    $statusColors = [
                                        'pending' => 'bg-yellow-100 text-yellow-800',
                                        'collecting' => 'bg-blue-100 text-blue-800',
                                        'collected' => 'bg-indigo-100 text-indigo-800',
                                        'shipped' => 'bg-purple-100 text-purple-800',
                                        'delivered' => 'bg-green-100 text-green-800',
                                    ];
                                @endphp
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$batch->status] ?? '' }}">
                                    {{ ucfirst($batch->status) }}
                                </span>
                            </div>
                            <div class="h-1.5 bg-gray-200 rounded-full overflow-hidden">
                                <div class="h-full rounded-full bg-blue-500" style="width: {{ $batch->progress_percentage }}%"></div>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">{{ $batch->resolved_items }}/{{ $batch->total_items }} items</p>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-admin-layout>
