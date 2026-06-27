<x-admin-layout>
    <div class="min-h-screen bg-gray-50 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Header -->
            <div class="mb-8">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Products</h1>
                        <p class="mt-1 text-gray-500">Manage your product catalog</p>
                    </div>
                    <a href="{{ route('products.create') }}" 
                       class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-blue-600 to-blue-500 text-white rounded-xl hover:from-blue-700 hover:to-blue-600 transition-all shadow-lg shadow-blue-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Add New Product
                    </a>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                @php
                    $totalProducts = \Modules\ProductManagement\Models\Product::count();
                    $activeProducts = \Modules\ProductManagement\Models\Product::where('is_active', true)->count();
                    $outOfStock = \Modules\ProductManagement\Models\ProductVariant::where('stock', '<=', 0)->distinct('product_id')->count();
                    $totalVariants = \Modules\ProductManagement\Models\ProductVariant::count();
                @endphp

                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Total Products</p>
                            <p class="text-3xl font-bold text-gray-900 mt-1">{{ number_format($totalProducts) }}</p>
                        </div>
                        <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Active Products</p>
                            <p class="text-3xl font-bold text-green-600 mt-1">{{ number_format($activeProducts) }}</p>
                        </div>
                        <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Out of Stock</p>
                            <p class="text-3xl font-bold text-red-600 mt-1">{{ number_format($outOfStock) }}</p>
                        </div>
                        <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Total Variants</p>
                            <p class="text-3xl font-bold text-purple-600 mt-1">{{ number_format($totalVariants) }}</p>
                        </div>
                        <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters & Search -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
                <form method="GET" action="{{ route('products.index') }}" class="flex flex-col lg:flex-row gap-4">
                    <!-- Search -->
                    <div class="flex-1">
                        <div class="relative">
                            <input type="text" 
                                   name="search" 
                                   value="{{ request('search') }}"
                                   placeholder="Search products by name, SKU..."
                                   class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all">
                            <svg class="w-5 h-5 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                    </div>

                    <!-- Category Filter -->
                    <div class="w-full lg:w-48">
                        <select name="category" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all">
                            <option value="">All Categories</option>
                            @foreach(\Modules\ProductManagement\Models\Category::whereNull('parent_id')->get() as $category)
                                <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name_arabic }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Vendor Filter -->
                    <div class="w-full lg:w-48">
                        <select name="vendor" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all">
                            <option value="">All Vendors</option>
                            @foreach(\Modules\Vendor\Models\Vendor::all() as $vendor)
                                <option value="{{ $vendor->id }}" {{ request('vendor') == $vendor->id ? 'selected' : '' }}>
                                    {{ $vendor->store_name_in_arabic }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Status Filter -->
                    <div class="w-full lg:w-40">
                        <select name="status" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all">
                            <option value="">All Status</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>

                    <!-- Filter Buttons -->
                    <div class="flex gap-2">
                        <button type="submit" class="px-5 py-2.5 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors">
                            Filter
                        </button>
                        <a href="{{ route('products.index') }}" class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition-colors">
                            Clear
                        </a>
                    </div>
                </form>
            </div>

            <!-- Products Table -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-100">
                                <th class="text-left py-4 px-6 text-xs font-semibold text-gray-500 uppercase tracking-wider">Product</th>
                                <th class="text-left py-4 px-6 text-xs font-semibold text-gray-500 uppercase tracking-wider">Category</th>
                                <th class="text-left py-4 px-6 text-xs font-semibold text-gray-500 uppercase tracking-wider">Vendor</th>
                                <th class="text-left py-4 px-6 text-xs font-semibold text-gray-500 uppercase tracking-wider">Variants</th>
                                <th class="text-left py-4 px-6 text-xs font-semibold text-gray-500 uppercase tracking-wider">Price Range</th>
                                <th class="text-left py-4 px-6 text-xs font-semibold text-gray-500 uppercase tracking-wider">Stock</th>
                                <th class="text-left py-4 px-6 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="text-right py-4 px-6 text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($products as $product)
                                @php
                                    $minPrice = $product->variants->min('price') ?? 0;
                                    $maxPrice = $product->variants->max('price') ?? 0;
                                    $totalStock = $product->variants->sum('stock') ?? 0;
                                    $variantCount = $product->variants->count();
                                @endphp
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <!-- Product -->
                                    <td class="py-4 px-6">
                                        <div class="flex items-center gap-4">
                                            <div class="w-14 h-14 rounded-xl overflow-hidden bg-gray-100 flex-shrink-0">
                                                @if($product->images->first())
                                                    <img src="{{ $product->images->first()->image }}" 
                                                         alt="{{ $product->name_arabic }}"
                                                         class="w-full h-full object-cover">
                                                @else
                                                    <div class="w-full h-full flex items-center justify-center">
                                                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                        </svg>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="min-w-0">
                                                <p class="font-semibold text-gray-900 truncate max-w-[200px]">{{ $product->name_arabic }}</p>
                                                <p class="text-sm text-gray-500 truncate max-w-[200px]">{{ $product->name_german }}</p>
                                                <p class="text-xs text-gray-400 mt-0.5">ID: {{ $product->id }}</p>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Category -->
                                    <td class="py-4 px-6">
                                        <span class="px-3 py-1 bg-blue-50 text-blue-700 rounded-lg text-sm font-medium">
                                            {{ $product->category->name_arabic ?? 'N/A' }}
                                        </span>
                                    </td>

                                    <!-- Vendor -->
                                    <td class="py-4 px-6">
                                        <div class="flex items-center gap-2">
                                            <div class="w-8 h-8 bg-gradient-to-br from-purple-500 to-pink-500 rounded-lg flex items-center justify-center text-white text-xs font-bold">
                                                {{ strtoupper(substr($product->vendor->store_name_in_arabic ?? 'V', 0, 1)) }}
                                            </div>
                                            <span class="text-sm text-gray-700">{{ $product->vendor->store_name_in_arabic ?? 'N/A' }}</span>
                                        </div>
                                    </td>

                                    <!-- Variants -->
                                    <td class="py-4 px-6">
                                        <span class="px-3 py-1 bg-purple-50 text-purple-700 rounded-lg text-sm font-medium">
                                            {{ $variantCount }} variant{{ $variantCount != 1 ? 's' : '' }}
                                        </span>
                                    </td>

                                    <!-- Price Range -->
                                    <td class="py-4 px-6">
                                        <div class="text-sm">
                                            @if($minPrice == $maxPrice)
                                                <span class="font-semibold text-gray-900">IQD{{ number_format($minPrice, 2) }}</span>
                                            @else
                                                <span class="font-semibold text-gray-900">IQD{{ number_format($minPrice, 2) }}</span>
                                                <span class="text-gray-400">-</span>
                                                <span class="font-semibold text-gray-900">IQD{{ number_format($maxPrice, 2) }}</span>
                                            @endif
                                        </div>
                                    </td>

                                    <!-- Stock -->
                                    <td class="py-4 px-6">
                                        @if($totalStock > 10)
                                            <span class="px-3 py-1 bg-green-50 text-green-700 rounded-lg text-sm font-medium">
                                                {{ $totalStock }} in stock
                                            </span>
                                        @elseif($totalStock > 0)
                                            <span class="px-3 py-1 bg-yellow-50 text-yellow-700 rounded-lg text-sm font-medium">
                                                {{ $totalStock }} low stock
                                            </span>
                                        @else
                                            <span class="px-3 py-1 bg-red-50 text-red-700 rounded-lg text-sm font-medium">
                                                Out of stock
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Status -->
                                    <td class="py-4 px-6">
                                        @if($product->is_active)
                                            <span class="inline-flex items-center gap-1 px-3 py-1 bg-green-50 text-green-700 rounded-lg text-sm font-medium">
                                                <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>
                                                Active
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-3 py-1 bg-gray-100 text-gray-600 rounded-lg text-sm font-medium">
                                                <span class="w-1.5 h-1.5 bg-gray-400 rounded-full"></span>
                                                Inactive
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Actions -->
                                    <td class="py-4 px-6">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('products.show', $product->id) }}" 
                                               class="p-2 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                                               title="View">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                            </a>
                                            <a href="{{ route('products.edit', $product->id) }}" 
                                               class="p-2 text-gray-500 hover:text-green-600 hover:bg-green-50 rounded-lg transition-colors"
                                               title="Edit">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </a>
                                            <form action="{{ route('products.destroy', $product->id) }}" 
                                                  method="POST" 
                                                  class="inline"
                                                  onsubmit="return confirm('Are you sure you want to delete this product? This action cannot be undone.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="p-2 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                                        title="Delete">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="py-12 text-center">
                                        <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                        </svg>
                                        <p class="text-gray-500 text-lg">No products found</p>
                                        <p class="text-gray-400 mt-1">Try adjusting your search or filters</p>
                                        <a href="{{ route('products.create') }}" 
                                           class="inline-flex items-center gap-2 mt-4 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                            </svg>
                                            Add First Product
                                        </a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($products instanceof \Illuminate\Pagination\LengthAwarePaginator && $products->hasPages())
                    <div class="px-6 py-4 border-t border-gray-100">
                        {{ $products->withQueryString()->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-admin-layout>