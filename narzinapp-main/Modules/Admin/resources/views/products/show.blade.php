<x-admin-layout>
    <div class="min-h-screen bg-gray-50 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Header -->
            <div class="mb-8">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <nav class="flex items-center gap-2 text-sm text-gray-500 mb-2">
                            <a href="{{ route('products.index') }}" class="hover:text-gray-700">Products</a>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                            <span class="text-gray-900">{{ $product->name_german }}</span>
                        </nav>
                        <h1 class="text-3xl font-bold text-gray-900">{{ $product->name_arabic }}</h1>
                        <p class="mt-1 text-gray-500">{{ $product->name_german }}</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <a href="{{ route('products.edit', $product->id) }}" 
                           class="inline-flex items-center gap-2 px-5 py-2.5 bg-white border border-gray-200 text-gray-700 rounded-xl hover:bg-gray-50 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Edit Product
                        </a>
                        <a href="{{ route('products.index') }}" 
                           class="inline-flex items-center gap-2 px-5 py-2.5 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                            Back
                        </a>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-6">
                    
                    <!-- Product Images -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="bg-gradient-to-r from-purple-600 to-purple-500 px-6 py-4">
                            <h2 class="text-lg font-semibold text-white flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                Product Images ({{ $product->images->count() }})
                            </h2>
                        </div>
                        <div class="p-6">
                            @if($product->images->count() > 0)
                                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                                    @foreach($product->images as $image)
                                        <div class="relative group">
                                            <div class="aspect-square rounded-xl overflow-hidden bg-gray-100 border-2 border-gray-200">
                                                <img src="{{ $image->image }}" 
                                                     alt="Product Image"
                                                     class="w-full h-full object-cover">
                                            </div>
                                            @if($image->color)
                                                <div class="absolute bottom-2 left-2 flex items-center gap-1 px-2 py-1 bg-white/90 backdrop-blur rounded-lg shadow-sm">
                                                    @php
                                                        $hexColor = str_replace(['0xFF', '0x'], '#', $image->color);
                                                    @endphp
                                                    <div class="w-4 h-4 rounded-full border border-gray-300" 
                                                         style="background-color: {{ $hexColor }}"></div>
                                                    <span class="text-xs font-mono text-gray-600">{{ $image->color }}</span>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-8">
                                    <svg class="w-12 h-12 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    <p class="text-gray-500">No images uploaded</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Product Variants -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="bg-gradient-to-r from-green-600 to-green-500 px-6 py-4">
                            <h2 class="text-lg font-semibold text-white flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                </svg>
                                Product Variants ({{ $product->variants->count() }})
                            </h2>
                        </div>
                        <div class="p-6">
                            @if($product->variants->count() > 0)
                                <div class="space-y-4">
                                    @foreach($product->variants as $index => $variant)
                                        <div class="bg-gray-50 rounded-xl p-4 border border-gray-200">
                                            <!-- Variant Header -->
                                            <div class="flex items-center justify-between mb-4">
                                                <div class="flex items-center gap-3">
                                                    <span class="w-8 h-8 bg-green-600 text-white rounded-lg flex items-center justify-center text-sm font-semibold">
                                                        {{ $index + 1 }}
                                                    </span>
                                                    <div>
                                                        <p class="font-medium text-gray-900">SKU: {{ $variant->sku }}</p>
                                                        <div class="flex flex-wrap gap-2 mt-1">
                                                            @foreach($variant->variantValues as $value)
                                                                @php
                                                                    $attrType = $value->variantAttribute->type ?? 'text';
                                                                @endphp
                                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-white text-xs rounded-lg border border-gray-200">
                                                                    <span class="text-gray-500">{{ $value->variantAttribute->name_german ?? '' }}:</span>
                                                                    @if($attrType === 'color')
                                                                        @php
                                                                            $hexColor = str_replace(['0xFF', '0x'], '#', $value->value);
                                                                        @endphp
                                                                        <span class="w-3 h-3 rounded-full border border-gray-300" 
                                                                              style="background-color: {{ $hexColor }}"></span>
                                                                        <span class="font-mono">{{ $value->value }}</span>
                                                                    @elseif($attrType === 'pattern')
                                                                        <img src="{{ Storage::url($value->value) }}" 
                                                                             class="w-6 h-6 rounded object-cover">
                                                                    @else
                                                                        <span class="font-medium">{{ $value->value }}</span>
                                                                    @endif
                                                                </span>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    @if($variant->is_active)
                                                        <span class="px-2 py-1 bg-green-100 text-green-700 rounded-lg text-xs font-medium">Active</span>
                                                    @else
                                                        <span class="px-2 py-1 bg-gray-100 text-gray-600 rounded-lg text-xs font-medium">Inactive</span>
                                                    @endif
                                                </div>
                                            </div>

                                            <!-- Variant Details -->
                                            <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-4">
                                                <div class="bg-white rounded-lg p-3 border border-gray-100">
                                                    <p class="text-xs text-gray-500 mb-1">Price</p>
                                                    <p class="text-lg font-bold text-gray-900">IQD{{ number_format($variant->price, 2) }}</p>
                                                </div>
                                                <div class="bg-white rounded-lg p-3 border border-gray-100">
                                                    <p class="text-xs text-gray-500 mb-1">Cost</p>
                                                    <p class="text-lg font-bold text-gray-700">IQD{{ number_format($variant->cost ?? 0, 2) }}</p>
                                                </div>
                                                <div class="bg-white rounded-lg p-3 border border-gray-100">
                                                    <p class="text-xs text-gray-500 mb-1">Stock</p>
                                                    <p class="text-lg font-bold {{ $variant->stock > 10 ? 'text-green-600' : ($variant->stock > 0 ? 'text-yellow-600' : 'text-red-600') }}">
                                                        {{ $variant->stock }}
                                                    </p>
                                                </div>
                                                <div class="bg-white rounded-lg p-3 border border-gray-100">
                                                    <p class="text-xs text-gray-500 mb-1">Tax</p>
                                                    <p class="text-lg font-bold text-gray-700">{{ $variant->tax ?? 0 }}%</p>
                                                </div>
                                                <div class="bg-white rounded-lg p-3 border border-gray-100">
                                                    <p class="text-xs text-gray-500 mb-1">Profit</p>
                                                    @php
                                                        $profit = ($variant->price ?? 0) - ($variant->cost ?? 0);
                                                        $profitPercent = $variant->cost > 0 ? ($profit / $variant->cost * 100) : 0;
                                                    @endphp
                                                    <p class="text-lg font-bold {{ $profit > 0 ? 'text-green-600' : 'text-red-600' }}">
                                                        IQD{{ number_format($profit, 2) }}
                                                    </p>
                                                </div>
                                                <div class="bg-white rounded-lg p-3 border border-gray-100">
                                                    <p class="text-xs text-gray-500 mb-1">Margin</p>
                                                    <p class="text-lg font-bold text-purple-600">{{ number_format($profitPercent, 1) }}%</p>
                                                </div>
                                            </div>

                                            <!-- Expiry Info -->
                                            @if($variant->expiry_date || $variant->expiry_days)
                                                <div class="mt-3 pt-3 border-t border-gray-200">
                                                    <div class="flex items-center gap-4 text-sm">
                                                        @if($variant->expiry_date)
                                                            <span class="text-gray-600">
                                                                <span class="font-medium">Expiry Date:</span> 
                                                                {{ \Carbon\Carbon::parse($variant->expiry_date)->format('M d, Y') }}
                                                            </span>
                                                        @endif
                                                        @if($variant->expiry_days)
                                                            <span class="text-gray-600">
                                                                <span class="font-medium">Expiry Days:</span> 
                                                                {{ $variant->expiry_days }} days
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-8">
                                    <svg class="w-12 h-12 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                    </svg>
                                    <p class="text-gray-500">No variants created</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="bg-gradient-to-r from-indigo-600 to-indigo-500 px-6 py-4">
                            <h2 class="text-lg font-semibold text-white flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                Descriptions
                            </h2>
                        </div>
                        <div class="p-6 space-y-6">
                            <div>
                                <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-2">Arabic Description</h3>
                                <div class="bg-gray-50 rounded-xl p-4 text-gray-700" dir="rtl">
                                    {{ $product->description_arabic ?: 'No description available' }}
                                </div>
                            </div>
                            <div>
                                <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-2">German Description</h3>
                                <div class="bg-gray-50 rounded-xl p-4 text-gray-700">
                                    {{ $product->description_german ?: 'No description available' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    
                    <!-- Product Info Card -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="bg-gradient-to-r from-blue-600 to-blue-500 px-6 py-4">
                            <h2 class="text-lg font-semibold text-white flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Product Info
                            </h2>
                        </div>
                        <div class="p-6 space-y-4">
                            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                <span class="text-gray-500">Product ID</span>
                                <span class="font-mono text-gray-900">{{ $product->id }}</span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                <span class="text-gray-500">Status</span>
                                @if($product->is_active)
                                    <span class="px-3 py-1 bg-green-100 text-green-700 rounded-lg text-sm font-medium">Active</span>
                                @else
                                    <span class="px-3 py-1 bg-gray-100 text-gray-600 rounded-lg text-sm font-medium">Inactive</span>
                                @endif
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                <span class="text-gray-500">Created</span>
                                <span class="text-gray-900">{{ $product->created_at->format('M d, Y') }}</span>
                            </div>
                            <div class="flex justify-between items-center py-2">
                                <span class="text-gray-500">Updated</span>
                                <span class="text-gray-900">{{ $product->updated_at->format('M d, Y') }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Category Card -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100">
                            <h3 class="font-semibold text-gray-900">Category</h3>
                        </div>
                        <div class="p-6">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">{{ $product->category->name_arabic ?? 'N/A' }}</p>
                                    <p class="text-sm text-gray-500">{{ $product->category->name_german ?? '' }}</p>
                                </div>
                            </div>
                            @if($product->childCategory)
                                <div class="mt-3 pl-4 border-l-2 border-blue-200">
                                    <p class="text-sm font-medium text-gray-700">{{ $product->childCategory->name_arabic }}</p>
                                    <p class="text-xs text-gray-500">{{ $product->childCategory->name_german }}</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Vendor Card -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100">
                            <h3 class="font-semibold text-gray-900">Vendor</h3>
                        </div>
                        <div class="p-6">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-pink-500 rounded-xl flex items-center justify-center text-white font-bold">
                                    {{ strtoupper(substr($product->vendor->store_name_in_arabic ?? 'V', 0, 1)) }}
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">{{ $product->vendor->store_name_in_arabic ?? 'N/A' }}</p>
                                    <p class="text-sm text-gray-500">{{ $product->vendor->store_name_in_german ?? '' }}</p>
                                </div>
                            </div>
                            @if($product->vendor)
                                <div class="mt-4 pt-4 border-t border-gray-100">
                                    <a href="#" class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                                        View Vendor Profile →
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Quick Stats -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100">
                            <h3 class="font-semibold text-gray-900">Quick Stats</h3>
                        </div>
                        <div class="p-6 grid grid-cols-2 gap-4">
                            @php
                                $totalStock = $product->variants->sum('stock');
                                $minPrice = $product->variants->min('price') ?? 0;
                                $maxPrice = $product->variants->max('price') ?? 0;
                                $avgPrice = $product->variants->avg('price') ?? 0;
                            @endphp
                            <div class="bg-gray-50 rounded-xl p-3 text-center">
                                <p class="text-2xl font-bold text-gray-900">{{ $product->variants->count() }}</p>
                                <p class="text-xs text-gray-500">Variants</p>
                            </div>
                            <div class="bg-gray-50 rounded-xl p-3 text-center">
                                <p class="text-2xl font-bold {{ $totalStock > 10 ? 'text-green-600' : ($totalStock > 0 ? 'text-yellow-600' : 'text-red-600') }}">{{ $totalStock }}</p>
                                <p class="text-xs text-gray-500">Total Stock</p>
                            </div>
                            <div class="bg-gray-50 rounded-xl p-3 text-center">
                                <p class="text-2xl font-bold text-gray-900">IQD{{ number_format($minPrice, 0) }}</p>
                                <p class="text-xs text-gray-500">Min Price</p>
                            </div>
                            <div class="bg-gray-50 rounded-xl p-3 text-center">
                                <p class="text-2xl font-bold text-gray-900">IQD{{ number_format($maxPrice, 0) }}</p>
                                <p class="text-xs text-gray-500">Max Price</p>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100">
                            <h3 class="font-semibold text-gray-900">Actions</h3>
                        </div>
                        <div class="p-6 space-y-3">
                            <a href="{{ route('products.edit', $product->id) }}" 
                               class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                Edit Product
                            </a>
                            <button type="button"
                                    onclick="toggleStatus()"
                                    class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition-colors">
                                @if($product->is_active)
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                    </svg>
                                    Deactivate Product
                                @else
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Activate Product
                                @endif
                            </button>
                            <form action="{{ route('products.destroy', $product->id) }}" 
                                  method="POST"
                                  onsubmit="return confirm('Are you sure you want to delete this product? This action cannot be undone.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-red-50 text-red-600 rounded-xl hover:bg-red-100 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    Delete Product
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>