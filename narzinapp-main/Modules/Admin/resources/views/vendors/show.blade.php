<x-admin-layout>
    <div class="min-h-screen bg-gray-50/50 p-6">
        <div class="max-w-7xl mx-auto space-y-8">
            <!-- Vendor Profile Card -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="relative h-48 bg-gradient-to-r from-purple-500 to-indigo-600">
                    <div class="absolute -bottom-20 left-8">
                        <div class="w-32 h-32 rounded-full border-4 border-white overflow-hidden">
                            @if ($vendor->store_logo)
                                <img src="{{ asset('storage/' . $vendor->store_logo) }}"
                                    alt="{{ $vendor->store_name_in_german }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full bg-gray-200 flex items-center justify-center">
                                    <span
                                        class="text-4xl text-gray-600">{{ strtoupper(substr($vendor->store_name_in_german, 0, 1)) }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="pt-24 pb-6 px-8">
                    <div class="flex justify-between items-start">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">{{ $vendor->store_name_in_german }}</h1>
                            <p class="text-gray-500">{{ $vendor->store_name_in_arabic }}</p>
                            <div class="mt-2 space-y-1 text-sm text-gray-600">
                                @if ($vendor->address)
                                    <p class="flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        {{ $vendor->address }}
                                    </p>
                                @endif
                                @if ($vendor->phone)
                                    <p class="flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                        </svg>
                                        {{ $vendor->phone }}
                                    </p>
                                @endif
                                @if ($vendor->store_type)
                                    <p class="flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                        </svg>
                                        {{ $vendor->store_type }}
                                    </p>
                                @endif
                            </div>
                        </div>

                        <form action="{{ route('vendors.change-status', $vendor->id) }}" method="POST" class="mt-4">
                            @csrf
                            <div class="flex gap-2 items-center">
                                <select name="status" class="">
                                    <option value="Active" {{ $vendor->status === 'Active' ? 'selected' : '' }}>Active
                                    </option>
                                    <option value="Waiting Approve"
                                        {{ $vendor->status === 'Waiting Approve' ? 'selected' : '' }}>Waiting Approve
                                    </option>
                                    <option value="Rejected" {{ $vendor->status === 'Rejected' ? 'selected' : '' }}>
                                        Rejected</option>
                                </select>
                                <button type="submit" class="btn btn-sm btn-primary">Update Status</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Orders Overview -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                    <h2 class="text-xl font-semibold text-gray-900">Orders</h2>
                    <div class="tabs tabs-boxed">
                        <a class="tab {{ request()->get('status') == 'all' || !request()->has('status') ? 'tab-active' : '' }}"
                            href="{{ route('orders.index', ['status' => 'all']) }}">All</a>
                        <a class="tab {{ request()->get('status') == 'pending' ? 'tab-active' : '' }}"
                            href="{{ route('orders.index', ['status' => 'pending']) }}">Pending</a>
                        <a class="tab {{ request()->get('status') == 'completed' ? 'tab-active' : '' }}"
                            href="{{ route('orders.index', ['status' => 'completed']) }}">Completed</a>
                        <a class="tab {{ request()->get('status') == 'rejected' ? 'tab-active' : '' }}"
                            href="{{ route('orders.index', ['status' => 'rejected']) }}">Rejected</a>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="table table-zebra w-full">
                        <thead>
                            <tr>
                                <th class="bg-gray-50">Order #</th>
                                <th class="bg-gray-50">Product</th>
                                <th class="bg-gray-50">Date</th>
                                <th class="bg-gray-50">Quantity</th>
                                <th class="bg-gray-50">Price</th>
                                <th class="bg-gray-50">Subtotal</th>
                                <th class="bg-gray-50">Status</th>
                                <th class="bg-gray-50">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($orderItems as $item)
                                <tr>
                                    <td class="font-medium">#{{ $item->order->order_number }}</td>
                                    <td>
                                        <div class="flex items-center space-x-3">
                                            @php
                                                $productImage = $item->product->images->first()
                                                    ?  $item->product->images->first()->image
                                                    : null;
                                            @endphp
                                            @if ($productImage)
                                                <div class="avatar">
                                                    <div class="mask mask-squircle w-12 h-12">
                                                        <img src="{{ $productImage }}"
                                                            alt="{{ $item->product->name_in_german }}" />
                                                    </div>
                                                </div>
                                            @endif
                                            <div>
                                                <div class="font-bold">{{ $item->product->name_in_german }}</div>
                                                <div class="text-sm opacity-50">
                                                    @php
                                                        $variantInfo = [];
                                                        foreach ($item->productVariant->variantValues as $value) {
                                                            $variantInfo[] =
                                                                $value->attribute?->name . ': ' . $value->value;
                                                        }
                                                    @endphp
                                                    {{ implode(', ', $variantInfo) }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $item->created_at->format('M d, Y') }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>IQD{{ number_format($item->unit_price, 2) }}</td>
                                    <td>IQD{{ number_format($item->subtotal, 2) }}</td>
                                    <td>
                                        <div
                                            class="badge badge-{{ $item->status === 'completed' ? 'success' : ($item->status === 'rejected' ? 'error' : 'warning') }} gap-2">
                                            {{ ucfirst($item->status) }}
                                        </div>
                                    </td>
                                    <td>
                                        <a href="{{ route('vendors.index', $item->order->id) }}"
                                            class="btn btn-sm btn-ghost">
                                            View Details
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-gray-500">
                                        No orders found
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-4">
                    {{ $orderItems->links() }}
                </div>
            </div>
            <!-- Products Overview -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                    <h2 class="text-xl font-semibold text-gray-900">Products</h2>
                    <div class="tabs tabs-boxed">
                        <a class="tab {{ request()->get('product_status') == 'all' || !request()->has('product_status') ? 'tab-active' : '' }}"
                            href="{{ route('vendors.index', ['product_status' => 'all']) }}">All</a>
                        <a class="tab {{ request()->get('product_status') == 'active' ? 'tab-active' : '' }}"
                            href="{{ route('vendors.index', ['product_status' => 'active']) }}">Active</a>
                        <a class="tab {{ request()->get('product_status') == 'inactive' ? 'tab-active' : '' }}"
                            href="{{ route('vendors.index', ['product_status' => 'inactive']) }}">Inactive</a>
                    </div>
                </div>

                <!-- Product Search and Filter -->
                <div class="p-4 bg-gray-50 border-b border-gray-200">
                    <form action="{{ route('vendors.index') }}" method="GET" class="flex flex-wrap gap-4">
                        <!-- Preserve other query parameters -->
                        @if (request()->has('status'))
                            <input type="hidden" name="status" value="{{ request()->get('status') }}">
                        @endif
                        @if (request()->has('product_status'))
                            <input type="hidden" name="product_status"
                                value="{{ request()->get('product_status') }}">
                        @endif

                        <div class="form-control w-full max-w-xs">
                            <div class="input-group">
                                <input type="text" name="search" placeholder="Search products..."
                                    class="input input-bordered w-full" value="{{ request()->get('search') }}">
                                <button class="btn btn-square">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div class="form-control max-w-xs">
                            <select name="category" class="select select-bordered">
                                <option value="">All Categories</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}"
                                        {{ request()->get('category') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name_in_german }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-control max-w-xs">
                            <select name="stock_status" class="select select-bordered">
                                <option value="">All Stock Status</option>
                                <option value="in_stock"
                                    {{ request()->get('stock_status') === 'in_stock' ? 'selected' : '' }}>In Stock
                                </option>
                                <option value="out_of_stock"
                                    {{ request()->get('stock_status') === 'out_of_stock' ? 'selected' : '' }}>Out of
                                    Stock</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary">Filter</button>
                        <a href="{{ route('vendors.index') }}" class="btn btn-ghost">Reset</a>
                    </form>
                </div>

                <!-- Products Grid -->
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @forelse($products as $product)
                            <div class="card bg-base-100 shadow-sm border hover:shadow-md transition">
                                <figure class="h-48 bg-gray-100">
                                    @if ($product->images->isNotEmpty())
                                        <img src="{{ $product->images->first()->image }}"
                                            alt="{{ $product->name_in_german }}" class="h-full w-full object-cover">
                                    @else
                                        <div class="h-full w-full flex items-center justify-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-300"
                                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                    @endif
                                </figure>
                                <div class="card-body">
                                    <h3 class="card-title font-medium">
                                        {{ $product->name_in_german }}
                                        @if (!$product->is_active)
                                            <div class="badge badge-warning">Inactive</div>
                                        @endif
                                    </h3>
                                    <p class="text-sm text-gray-500">{{ $product->name_arabic }}</p>
                                    <div class="flex items-center gap-2 text-xs text-gray-600 mt-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                        </svg>
                                        <span>{{ $product->category->name_in_german }}</span>
                                        @if ($product->child_category)
                                            <span>/ {{ $product->child_category->name_in_german }}</span>
                                        @endif
                                    </div>

                                    <!-- Product Variant Summary -->
                                    <div class="mt-3 grid grid-cols-2 gap-2 text-sm">
                                        <div>
                                            <span class="text-gray-500">Variants:</span>
                                            <span class="font-medium">{{ $product->variants->count() }}</span>
                                        </div>
                                        <div>
                                            <span class="text-gray-500">Total Stock:</span>
                                            <span class="font-medium">{{ $product->variants->sum('stock') }}</span>
                                        </div>
                                        <div class="col-span-2">
                                            <span class="text-gray-500">Price Range:</span>
                                            <span class="font-medium">
                                                @if ($product->variants->count() > 0)
                                                    IQD{{ number_format($product->variants->min('price'), 2) }}
                                                    @if ($product->variants->max('price') > $product->variants->min('price'))
                                                        - IQD{{ number_format($product->variants->max('price'), 2) }}
                                                    @endif
                                                @else
                                                    N/A
                                                @endif
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Product Variants Listing -->
                                    <div class="mt-4 pt-4 border-t border-gray-100">
                                        <div class="text-sm font-medium text-gray-700 mb-2">Variants:</div>
                                        <div class="space-y-2 max-h-40 overflow-y-auto">
                                            @forelse($product->variants->take(3) as $variant)
                                                <div class="text-xs p-2 bg-gray-50 rounded-md">
                                                    <div class="flex justify-between">
                                                        <div>
                                                            @php
                                                                $variantInfo = [];
                                                                foreach ($variant->variantValues as $value) {
                                                                    $variantInfo[] = $value->value;
                                                                }
                                                            @endphp
                                                            <span
                                                                class="font-medium">{{ implode(' / ', $variantInfo) }}</span>
                                                        </div>
                                                        <div>
                                                            <span
                                                                class="font-medium">IQD{{ number_format($variant->price, 2) }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="flex justify-between mt-1 text-gray-500">
                                                        <div>Stock: {{ $variant->stock }}</div>
                                                        <div>SKU: {{ $variant->sku }}</div>
                                                    </div>
                                                </div>
                                            @empty
                                                <p class="text-xs text-gray-500">No variants available</p>
                                            @endforelse

                                            @if ($product->variants->count() > 3)
                                                <div class="text-center text-xs text-blue-600">
                                                    + {{ $product->variants->count() - 3 }} more variants
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="card-actions justify-end mt-4">
                                        <a href="{{ route('vendors.index', $product->id) }}"
                                            class="btn btn-sm btn-primary">View Details</a>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-span-full p-8">
                                <div class="flex flex-col items-center justify-center text-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-400 mb-4"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                    </svg>
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">No products found</h3>
                                    <p class="text-gray-500">No products match your filter criteria.</p>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Pagination -->
                <div class="p-4 border-t border-gray-200">
                    {{ $products->links() }}
                </div>
            </div>
            <!-- Statistics Overview -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Total Revenue Card -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Total Revenue</p>
                            <h3 class="text-2xl font-bold text-gray-900 mt-1">
                                IQD{{ number_format($stats['total_revenue'], 2) }}</h3>
                            <p
                                class="text-xs text-{{ $stats['revenue_trend'] >= 0 ? 'green' : 'red' }}-500 mt-2 flex items-center">
                                @if ($stats['revenue_trend'] >= 0)
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20"
                                        fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z"
                                            clip-rule="evenodd" />
                                    </svg>
                                @else
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20"
                                        fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M12 13a1 1 0 100 2h5a1 1 0 001-1v-5a1 1 0 10-2 0v2.586l-4.293-4.293a1 1 0 00-1.414 0L8 9.586l-4.293-4.293a1 1 0 00-1.414 1.414l5 5a1 1 0 001.414 0L11 9.414 14.586 13H12z"
                                            clip-rule="evenodd" />
                                    </svg>
                                @endif
                                {{ abs($stats['revenue_trend']) }}% from last period
                            </p>
                        </div>
                        <div class="bg-blue-100 p-3 rounded-full">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Total Orders Card -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Total Orders</p>
                            <h3 class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['total_orders'] }}</h3>
                            <p
                                class="text-xs text-{{ $stats['orders_trend'] >= 0 ? 'green' : 'red' }}-500 mt-2 flex items-center">
                                @if ($stats['orders_trend'] >= 0)
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20"
                                        fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z"
                                            clip-rule="evenodd" />
                                    </svg>
                                @else
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20"
                                        fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M12 13a1 1 0 100 2h5a1 1 0 001-1v-5a1 1 0 10-2 0v2.586l-4.293-4.293a1 1 0 00-1.414 0L8 9.586l-4.293-4.293a1 1 0 00-1.414 1.414l5 5a1 1 0 001.414 0L11 9.414 14.586 13H12z"
                                            clip-rule="evenodd" />
                                    </svg>
                                @endif
                                {{ abs($stats['orders_trend']) }}% from last period
                            </p>
                        </div>
                        <div class="bg-green-100 p-3 rounded-full">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Total Cost Card -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Total Cost</p>
                            <h3 class="text-2xl font-bold text-gray-900 mt-1">
                                IQD{{ number_format($stats['total_cost'], 2) }}</h3>
                            <p
                                class="text-xs text-{{ $stats['cost_trend'] <= 0 ? 'green' : 'red' }}-500 mt-2 flex items-center">
                                @if ($stats['cost_trend'] <= 0)
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20"
                                        fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z"
                                            clip-rule="evenodd" />
                                    </svg>
                                @else
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20"
                                        fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M12 13a1 1 0 100 2h5a1 1 0 001-1v-5a1 1 0 10-2 0v2.586l-4.293-4.293a1 1 0 00-1.414 0L8 9.586l-4.293-4.293a1 1 0 00-1.414 1.414l5 5a1 1 0 001.414 0L11 9.414 14.586 13H12z"
                                            clip-rule="evenodd" />
                                    </svg>
                                @endif
                                {{ abs($stats['cost_trend']) }}% from last period
                            </p>
                        </div>
                        <div class="bg-red-100 p-3 rounded-full">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-600" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Net Profit Card -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Net Profit</p>
                            <h3 class="text-2xl font-bold text-gray-900 mt-1">
                                IQD{{ number_format($stats['net_profit'], 2) }}</h3>
                            <p
                                class="text-xs text-{{ $stats['profit_trend'] >= 0 ? 'green' : 'red' }}-500 mt-2 flex items-center">
                                @if ($stats['profit_trend'] >= 0)
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20"
                                        fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z"
                                            clip-rule="evenodd" />
                                    </svg>
                                @else
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20"
                                        fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M12 13a1 1 0 100 2h5a1 1 0 001-1v-5a1 1 0 10-2 0v2.586l-4.293-4.293a1 1 0 00-1.414 0L8 9.586l-4.293-4.293a1 1 0 00-1.414 1.414l5 5a1 1 0 001.414 0L11 9.414 14.586 13H12z"
                                            clip-rule="evenodd" />
                                    </svg>
                                @endif
                                {{ abs($stats['profit_trend']) }}% from last period
                            </p>
                        </div>
                        <div class="bg-purple-100 p-3 rounded-full">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-purple-600" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Revenue Analytics -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                <!-- Revenue Chart -->
                <div class="bg-white rounded-lg shadow-md lg:col-span-2">
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex justify-between items-center">
                            <h2 class="text-xl font-semibold text-gray-900">Revenue Overview</h2>
                            <div class="flex gap-2">
                                <select id="revenue-period" class="select select-bordered select-sm">
                                    <option value="week">This Week</option>
                                    <option value="month" selected>This Month</option>
                                    <option value="quarter">This Quarter</option>
                                    <option value="year">This Year</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="p-6">
                        <!-- Revenue Chart Canvas -->
                        <div class="w-full h-80">
                            <canvas id="revenueChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Top Products -->
                <div class="bg-white rounded-lg shadow-md">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-900">Top Products</h2>
                    </div>
                    <div class="p-4">
                        <ul class="divide-y divide-gray-200">
                            @forelse($topProducts as $product)
                                <li class="py-3 flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10 mr-3">
                                        @if ($product->product->images->isNotEmpty())
                                            <img class="h-10 w-10 rounded-md object-cover"
                                                src="{{ $product->product->images->first()->image }}"
                                                alt="{{ $product->product->name_in_german }}">
                                        @else
                                            <div
                                                class="h-10 w-10 rounded-md bg-gray-200 flex items-center justify-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-400"
                                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate">
                                            {{ $product->product->name_in_german }}
                                        </p>
                                        <p class="text-xs text-gray-500 truncate">
                                            {{ $product->total_sold }} units sold
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-medium text-gray-900">
                                            IQD{{ number_format($product->total_revenue, 2) }}
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            {{ number_format($product->percentage, 1) }}% of revenue
                                        </p>
                                    </div>
                                </li>
                            @empty
                                <li class="py-8 text-center">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="h-12 w-12 text-gray-400 mx-auto mb-4" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <p class="text-gray-500">No sales data available yet</p>
                                </li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Revenue by Category and Monthly Reports -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Revenue By Category -->
                <div class="bg-white rounded-lg shadow-md">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-900">Revenue By Category</h2>
                    </div>
                    <div class="p-6">
                        <!-- Category Revenue Canvas -->
                        <div class="h-72">
                            <canvas id="categoryRevenueChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Monthly Reports -->
                <div class="bg-white rounded-lg shadow-md">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-900">Monthly Reports</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="table table-zebra w-full">
                            <thead>
                                <tr>
                                    <th class="bg-gray-50">Month</th>
                                    <th class="bg-gray-50">Orders</th>
                                    <th class="bg-gray-50">Revenue</th>
                                    <th class="bg-gray-50">Cost</th>
                                    <th class="bg-gray-50">Profit</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($monthlyReports as $report)
                                    <tr>
                                        <td>{{ $report->month }}</td>
                                        <td>{{ $report->orders_count }}</td>
                                        <td>IQD{{ number_format($report->revenue, 2) }}</td>
                                        <td>IQD{{ number_format($report->cost, 2) }}</td>
                                        <td
                                            class="{{ $report->profit >= 0 ? 'text-green-600' : 'text-red-600' }} font-medium">
                                            IQD{{ number_format($report->profit, 2) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-gray-500">
                                            No monthly reports available
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart.js Script -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Revenue Chart
            const revenueCtx = document.getElementById('revenueChart').getContext('2d');
            const revenueChart = new Chart(revenueCtx, {
                type: 'line',
                data: {
                    labels: @json($revenueChart['labels']),
                    datasets: [{
                            label: 'Revenue',
                            data: @json($revenueChart['revenue']),
                            borderColor: 'rgb(59, 130, 246)',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            tension: 0.3,
                            fill: true
                        },
                        {
                            label: 'Cost',
                            data: @json($revenueChart['cost']),
                            borderColor: 'rgb(239, 68, 68)',
                            backgroundColor: 'rgba(239, 68, 68, 0.1)',
                            tension: 0.3,
                            fill: true
                        },
                        {
                            label: 'Profit',
                            data: @json($revenueChart['profit']),
                            borderColor: 'rgb(16, 185, 129)',
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            tension: 0.3,
                            fill: true
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.parsed.y !== null) {
                                        label += new Intl.NumberFormat('de-DE', {
                                            style: 'currency',
                                            currency: 'EUR'
                                        }).format(context.parsed.y);
                                    }
                                    return label;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            }
                        },
                        y: {
                            grid: {
                                borderDash: [2, 4],
                                color: 'rgba(0, 0, 0, 0.05)'
                            },
                            ticks: {
                                callback: function(value) {
                                    return 'IQD' + value;
                                }
                            }
                        }
                    }
                }
            });

            // Category Revenue Chart
            const categoryCtx = document.getElementById('categoryRevenueChart').getContext('2d');
            const categoryChart = new Chart(categoryCtx, {
                type: 'doughnut',
                data: {
                    labels: @json($categoryChart['labels']),
                    datasets: [{
                        data: @json($categoryChart['data']),
                        backgroundColor: [
                            'rgba(59, 130, 246, 0.8)',
                            'rgba(16, 185, 129, 0.8)',
                            'rgba(245, 158, 11, 0.8)',
                            'rgba(239, 68, 68, 0.8)',
                            'rgba(139, 92, 246, 0.8)',
                            'rgba(236, 72, 153, 0.8)',
                            'rgba(20, 184, 166, 0.8)',
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.parsed || 0;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = Math.round((value / total) * 100);
                                    return label + ': IQD' + value + ' (' + percentage + '%)';
                                }
                            }
                        }
                    },
                    cutout: '60%'
                }
            });

            // Period selector for revenue chart
            document.getElementById('revenue-period').addEventListener('change', function() {
                const period = this.value;
                fetch(`/vendor/dashboard/revenue-data?period=${period}`)
                    .then(response => response.json())
                    .then(data => {
                        revenueChart.data.labels = data.labels;
                        revenueChart.data.datasets[0].data = data.revenue;
                        revenueChart.data.datasets[1].data = data.cost;
                        revenueChart.data.datasets[2].data = data.profit;
                        revenueChart.update();
                    })
                    .catch(error => console.error('Error fetching revenue data:', error));
            });
        });
    </script>
</x-admin-layout>
