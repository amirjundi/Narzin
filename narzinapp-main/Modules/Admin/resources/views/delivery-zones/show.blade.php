<x-admin-layout>
    <div class="p-6">
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-semibold text-gray-800">Delivery Methods for {{ $deliveryZone->name_english }}</h2>
                <p class="text-sm text-gray-500 mt-1">Configure shipping options, speeds, and weight pricing for this zone.</p>
            </div>
            <a href="{{ route('delivery-zones.index') }}" class="text-indigo-600 hover:text-indigo-900">&larr; Back to Zones</a>
        </div>

        @if(session('success'))
            <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg">
                {{ session('success') }}
            </div>
        @endif
        @if($errors->any())
            <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left side: List of Methods -->
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                        <h3 class="font-semibold text-gray-800">Available Methods</h3>
                    </div>
                    <div class="divide-y divide-gray-100">
                        @forelse($deliveryZone->deliveryMethods as $method)
                            <div class="p-6 flex items-center justify-between hover:bg-gray-50">
                                <div>
                                    <div class="flex items-center gap-3">
                                        <h4 class="font-medium text-lg text-gray-900">{{ $method->name_english }}</h4>
                                        @if($method->is_active)
                                            <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-700 rounded-full">Active</span>
                                        @else
                                            <span class="px-2 py-1 text-xs font-medium bg-red-100 text-red-700 rounded-full">Inactive</span>
                                        @endif
                                    </div>
                                    <div class="mt-2 text-sm text-gray-500 flex items-center gap-4">
                                        <span><i class="fas fa-coins text-gray-400 mr-1"></i> Base: {{ number_format($method->base_price) }} IQD</span>
                                        <span><i class="fas fa-weight-hanging text-gray-400 mr-1"></i> +{{ number_format($method->price_per_kg) }} IQD / KG</span>
                                        @if($method->estimated_days)
                                            <span><i class="far fa-clock text-gray-400 mr-1"></i> {{ $method->estimated_days }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div>
                                    <form action="{{ route('delivery-methods.destroy', [$deliveryZone->id, $method->id]) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this delivery method?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition-colors">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <div class="p-8 text-center text-gray-500">
                                <i class="fas fa-truck-fast text-4xl mb-3 text-gray-300"></i>
                                <p>No delivery methods configured for this zone.</p>
                                <p class="text-sm mt-1">Add your first method using the form.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Right side: Add New Method Form -->
            <div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden sticky top-6">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                        <h3 class="font-semibold text-gray-800">Add New Method</h3>
                    </div>
                    <form action="{{ route('delivery-methods.store', $deliveryZone->id) }}" method="POST" class="p-6 space-y-4">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Method Name (English)</label>
                            <input type="text" name="name_english" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" placeholder="e.g. Standard" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Method Name (German)</label>
                            <input type="text" name="name_german" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" placeholder="e.g. Standard" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Method Name (Arabic)</label>
                            <input type="text" name="name_arabic" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" placeholder="e.g. عادي" required>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Base Price (IQD)</label>
                            <input type="number" name="base_price" step="0.01" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" placeholder="0" required>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Price Per KG (IQD)</label>
                            <input type="number" name="price_per_kg" step="0.01" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" placeholder="0" required>
                            <p class="text-xs text-gray-500 mt-1">Extra cost added for every KG of cart weight.</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Estimated Delivery Time</label>
                            <input type="text" name="estimated_days" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" placeholder="e.g. 3-5 Business Days">
                        </div>

                        <div class="flex items-center pt-2">
                            <input type="checkbox" name="is_active" id="is_active" class="h-4 w-4 text-indigo-600 border-gray-300 rounded" value="1" checked>
                            <label for="is_active" class="ml-2 block text-sm text-gray-900">Active</label>
                        </div>

                        <div class="pt-4">
                            <button type="submit" class="w-full px-4 py-2 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700">Add Method</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
