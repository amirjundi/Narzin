<x-admin-layout>
    {{-- Page Header --}}
    <div class="mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Orders Management</h1>
                <p class="text-gray-500 text-sm mt-1">Manage and track all customer orders</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('orders.export.csv') }}"
                    class="btn btn-sm bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded-lg flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                        </path>
                    </svg>
                    Export CSV
                </a>
                <a href="{{ route('orders.export.pdf') }}"
                    class="btn btn-sm bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded-lg flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z">
                        </path>
                    </svg>
                    Export PDF
                </a>
            </div>
        </div>
    </div>

    {{-- Statistics Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4 mb-6">
        {{-- Total Orders --}}
        <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Total Orders</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($stats['total_orders']) }}</p>
                </div>
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Pending Payment --}}
        <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Pending</p>
                    <p class="text-2xl font-bold text-yellow-600 mt-1">{{ number_format($stats['pending_payment']) }}
                    </p>
                </div>
                <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Processing --}}
        <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Processing</p>
                    <p class="text-2xl font-bold text-blue-600 mt-1">{{ number_format($stats['processing']) }}</p>
                </div>
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                        </path>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Completed --}}
        <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Completed</p>
                    <p class="text-2xl font-bold text-green-600 mt-1">{{ number_format($stats['completed']) }}</p>
                </div>
                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Today's Orders --}}
        <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Today</p>
                    <p class="text-2xl font-bold text-purple-600 mt-1">{{ number_format($stats['today_orders']) }}</p>
                </div>
                <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                        </path>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Total Revenue --}}
        <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Revenue</p>
                    <p class="text-xl font-bold text-green-600 mt-1">IQD{{ number_format($stats['total_revenue'], 2) }}
                    </p>
                </div>
                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                        </path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Filter Tabs --}}
    <div class="bg-white rounded-xl shadow-sm mb-6 border border-gray-100">
        <div class="flex flex-wrap border-b border-gray-200">
            <a href="{{ route('orders.index') }}"
                class="px-6 py-3 text-sm font-medium {{ !request()->filled('payment_status') ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-500 hover:text-gray-700' }}">
                All Orders
            </a>
            <a href="{{ route('orders.index', ['payment_status' => 'not_paid']) }}"
                class="px-6 py-3 text-sm font-medium {{ request('payment_status') === 'not_paid' ? 'text-yellow-600 border-b-2 border-yellow-600' : 'text-gray-500 hover:text-gray-700' }}">
                Pending Payment
                @if ($stats['pending_payment'] > 0)
                    <span
                        class="ml-1 px-2 py-0.5 text-xs bg-yellow-100 text-yellow-800 rounded-full">{{ $stats['pending_payment'] }}</span>
                @endif
            </a>
            <a href="{{ route('orders.index', ['payment_status' => 'processing']) }}"
                class="px-6 py-3 text-sm font-medium {{ request('payment_status') === 'processing' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-500 hover:text-gray-700' }}">
                Processing
            </a>
            <a href="{{ route('orders.index', ['payment_status' => 'completed']) }}"
                class="px-6 py-3 text-sm font-medium {{ request('payment_status') === 'completed' ? 'text-green-600 border-b-2 border-green-600' : 'text-gray-500 hover:text-gray-700' }}">
                Completed
            </a>
            <a href="{{ route('orders.index', ['payment_status' => 'expired']) }}"
                class="px-6 py-3 text-sm font-medium {{ request('payment_status') === 'expired' ? 'text-red-600 border-b-2 border-red-600' : 'text-gray-500 hover:text-gray-700' }}">
                Expired
                @if ($stats['expired'] > 0)
                    <span
                        class="ml-1 px-2 py-0.5 text-xs bg-red-100 text-red-800 rounded-full">{{ $stats['expired'] }}</span>
                @endif
            </a>
            <a href="{{ route('orders.index', ['payment_status' => 'failed']) }}"
                class="px-6 py-3 text-sm font-medium {{ request('payment_status') === 'failed' ? 'text-gray-600 border-b-2 border-gray-600' : 'text-gray-500 hover:text-gray-700' }}">
                Failed
            </a>

            <a href="{{ route('orders.index', ['payment_status' => 'refunded']) }}"
                class="px-6 py-3 text-sm font-medium {{ request('payment_status') === 'refunded' ? 'text-gray-600 border-b-2 border-gray-600' : 'text-gray-500 hover:text-gray-700' }}">
                Refunded
            </a>
        </div>

        {{-- Search & Filters --}}
        <div class="p-4">
            <form method="GET" action="{{ route('orders.index') }}" class="flex flex-wrap gap-3">
                {{-- Keep current filters --}}
                @if (request('payment_status'))
                    <input type="hidden" name="payment_status" value="{{ request('payment_status') }}">
                @endif

                {{-- Search --}}
                <div class="flex-1 min-w-[200px]">
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Search by order #, payment ID, customer..."
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                </div>

                {{-- Order Status --}}
                <select name="order_status"
                    class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm">
                    <option value="">All Order Status</option>
                    <option value="pending_payment"
                        {{ request('order_status') === 'pending_payment' ? 'selected' : '' }}>Pending Payment</option>
                    <option value="confirmed" {{ request('order_status') === 'confirmed' ? 'selected' : '' }}>
                        Confirmed</option>
                    <option value="processing" {{ request('order_status') === 'processing' ? 'selected' : '' }}>
                        Processing</option>
                    <option value="shipped" {{ request('order_status') === 'shipped' ? 'selected' : '' }}>Shipped
                    </option>
                    <option value="delivered" {{ request('order_status') === 'delivered' ? 'selected' : '' }}>
                        Delivered</option>
                    <option value="cancelled" {{ request('order_status') === 'cancelled' ? 'selected' : '' }}>
                        Cancelled</option>
                </select>

                {{-- Date From --}}
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                    class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm">

                {{-- Date To --}}
                <input type="date" name="date_to" value="{{ request('date_to') }}"
                    class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm">

                {{-- Buttons --}}
                <button type="submit"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">
                    Filter
                </button>
                <a href="{{ route('orders.index') }}"
                    class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 text-sm font-medium">
                    Clear
                </a>
            </form>
        </div>
    </div>

    {{-- Bulk Actions --}}
    <div id="bulkActions" class="hidden bg-blue-50 rounded-xl p-4 mb-4 border border-blue-200">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="text-sm text-blue-800">
                    <span id="selectedCount">0</span> orders selected
                </span>
            </div>
            <div class="flex gap-2">
                <button type="button" onclick="bulkAction('processing')"
                    class="px-3 py-1.5 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700">
                    Set Processing
                </button>
                <button type="button" onclick="bulkAction('shipped')"
                    class="px-3 py-1.5 bg-green-600 text-white rounded-lg text-sm hover:bg-green-700">
                    Set Shipped
                </button>
                <button type="button" onclick="clearSelection()"
                    class="px-3 py-1.5 bg-gray-200 text-gray-700 rounded-lg text-sm hover:bg-gray-300">
                    Clear Selection
                </button>
            </div>
        </div>
    </div>

    {{-- Orders Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3 text-left">
                            <input type="checkbox" id="selectAll"
                                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Order</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Customer</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Items</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Total</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Payment</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Status</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Date</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($orders as $order)
                        <tr class="hover:bg-gray-50 transition-colors">
                            {{-- Checkbox --}}
                            <td class="px-4 py-3">
                                <input type="checkbox"
                                    class="order-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                    value="{{ $order->id }}" data-order-id="{{ $order->id }}">
                            </td>

                            {{-- Order Info --}}
                            <td class="px-4 py-3">
                                <div>
                                    <a href="{{ route('orders.show', $order->id) }}"
                                        class="font-medium text-blue-600 hover:text-blue-800">
                                        {{ $order->order_number }}
                                    </a>
                                    <p class="text-xs text-gray-500 mt-0.5">
                                        ID: {{ $order->payment_id }}
                                    </p>
                                </div>
                            </td>

                            {{-- Customer --}}
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <div
                                        class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center text-xs font-medium text-gray-600">
                                        {{ strtoupper(substr($order->user->name ?? 'G', 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">
                                            {{ $order->user->name ?? 'Guest' }}</p>
                                        <p class="text-xs text-gray-500">{{ $order->user->email ?? '' }}</p>
                                    </div>
                                </div>
                            </td>

                            {{-- Items --}}
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-1">
                                    <span
                                        class="text-sm font-medium text-gray-900">{{ $order->items->count() }}</span>
                                    <span class="text-xs text-gray-500">items</span>
                                </div>
                                <p class="text-xs text-gray-500">
                                    {{ $order->items->sum('quantity') }} units
                                </p>
                            </td>

                            {{-- Total --}}
                            <td class="px-4 py-3">
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">
                                        IQD{{ number_format($order->final_price, 2) }}</p>
                                    @if ($order->wallet_usage > 0)
                                        <p class="text-xs text-green-600">Wallet:
                                            IQD{{ number_format($order->wallet_usage, 2) }}</p>
                                    @endif
                                    @if ($order->total_amount != $order->price_after_discount)
                                        <p class="text-xs text-red-500">
                                            -IQD{{ number_format($order->total_amount - $order->price_after_discount, 2) }}
                                        </p>
                                    @endif
                                </div>
                            </td>

                            {{-- Payment Status --}}
                            <td class="px-4 py-3">
                                @php
                                    $paymentColors = [
                                        'not_paid' => 'bg-yellow-100 text-yellow-800',
                                        'processing' => 'bg-blue-100 text-blue-800',
                                        'completed' => 'bg-green-100 text-green-800',
                                        'failed' => 'bg-red-100 text-red-800',
                                        'expired' => 'bg-gray-100 text-gray-800',
                                        'refunded' => 'bg-purple-100 text-purple-800',
                                    ];
                                    $paymentColor =
                                        $paymentColors[$order->payment_status] ?? 'bg-gray-100 text-gray-800';
                                @endphp
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $paymentColor }}">
                                    {{ ucfirst(str_replace('_', ' ', $order->payment_status)) }}
                                </span>
                                @if ($order->paid_at)
                                    <p class="text-xs text-gray-500 mt-0.5">{{ $order->paid_at }}</p>
                                @endif
                            </td>

                            {{-- Order Status --}}
                            <td class="px-4 py-3">
                                @php
                                    $statusColors = [
                                        'pending_payment' => 'bg-yellow-100 text-yellow-800',
                                        'confirmed' => 'bg-blue-100 text-blue-800',
                                        'processing' => 'bg-indigo-100 text-indigo-800',
                                        'shipped' => 'bg-purple-100 text-purple-800',
                                        'delivered' => 'bg-green-100 text-green-800',
                                        'cancelled' => 'bg-red-100 text-red-800',
                                    ];
                                    $statusColor = $statusColors[$order->order_status] ?? 'bg-gray-100 text-gray-800';
                                @endphp
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColor }}">
                                    {{ ucfirst(str_replace('_', ' ', $order->order_status)) }}
                                </span>
                            </td>

                            {{-- Date --}}
                            <td class="px-4 py-3">
                                <div>
                                    <p class="text-sm text-gray-900">{{ $order->created_at->format('M d, Y') }}</p>
                                    <p class="text-xs text-gray-500">{{ $order->created_at->format('H:i') }}</p>
                                </div>
                            </td>

                            {{-- Actions --}}
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-1">
                                    <a href="{{ route('orders.show', $order->id) }}"
                                        class="p-1.5 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                                        title="View">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                            </path>
                                        </svg>
                                    </a>
                                    <a href="{{ route('orders.print', $order->id) }}" target="_blank"
                                        class="p-1.5 text-gray-500 hover:text-green-600 hover:bg-green-50 rounded-lg transition-colors"
                                        title="Print">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z">
                                            </path>
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <svg class="w-12 h-12 text-gray-300 mb-4" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                    </svg>
                                    <p class="text-gray-500 text-lg font-medium">No orders found</p>
                                    <p class="text-gray-400 text-sm mt-1">Try adjusting your filters</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($orders->hasPages())
            <div class="px-4 py-3 border-t border-gray-200">
                {{ $orders->withQueryString()->links() }}
            </div>
        @endif
    </div>

    {{-- Hidden forms for bulk actions --}}
    <form id="bulkProcessingForm" method="POST" action="{{ route('orders.bulk.processing') }}" class="hidden">
        @csrf
        <input type="hidden" name="order_ids" id="processingOrderIds">
    </form>

    <form id="bulkShippedForm" method="POST" action="{{ route('orders.bulk.shipped') }}" class="hidden">
        @csrf
        <input type="hidden" name="order_ids" id="shippedOrderIds">
    </form>

    {{-- JavaScript --}}
    <script>
        let selectedOrders = [];

        // Select All checkbox
        document.getElementById('selectAll').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.order-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
                updateSelection(checkbox);
            });
        });

        // Individual checkboxes
        document.querySelectorAll('.order-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                updateSelection(this);
            });
        });

        function updateSelection(checkbox) {
            const orderId = parseInt(checkbox.value);

            if (checkbox.checked) {
                if (!selectedOrders.includes(orderId)) {
                    selectedOrders.push(orderId);
                }
            } else {
                selectedOrders = selectedOrders.filter(id => id !== orderId);
            }

            // Update UI
            const bulkActions = document.getElementById('bulkActions');
            const selectedCount = document.getElementById('selectedCount');

            if (selectedOrders.length > 0) {
                bulkActions.classList.remove('hidden');
                selectedCount.textContent = selectedOrders.length;
            } else {
                bulkActions.classList.add('hidden');
            }
        }

        function bulkAction(action) {
            if (selectedOrders.length === 0) {
                alert('Please select at least one order');
                return;
            }

            if (!confirm(`Are you sure you want to mark ${selectedOrders.length} orders as ${action}?`)) {
                return;
            }

            if (action === 'processing') {
                document.getElementById('processingOrderIds').value = JSON.stringify(selectedOrders);
                document.getElementById('bulkProcessingForm').submit();
            } else if (action === 'shipped') {
                document.getElementById('shippedOrderIds').value = JSON.stringify(selectedOrders);
                document.getElementById('bulkShippedForm').submit();
            }
        }

        function clearSelection() {
            selectedOrders = [];
            document.querySelectorAll('.order-checkbox').forEach(cb => cb.checked = false);
            document.getElementById('selectAll').checked = false;
            document.getElementById('bulkActions').classList.add('hidden');
        }
    </script>
</x-admin-layout>
