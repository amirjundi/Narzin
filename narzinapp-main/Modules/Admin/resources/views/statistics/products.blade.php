<x-admin-layout>
    <div class="space-y-6">
        <!-- Overview Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @foreach($stockStatus as $status)
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="rounded-md bg-{{ $status->stock_status === 'In Stock' ? 'green' : ($status->stock_status === 'Out of Stock' ? 'red' : 'yellow') }}-100 p-3">
                                <svg class="h-6 w-6 text-{{ $status->stock_status === 'In Stock' ? 'green' : ($status->stock_status === 'Out of Stock' ? 'red' : 'yellow') }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">
                                    {{ $status->stock_status }}
                                </dt>
                                <dd class="flex items-baseline">
                                    <div class="text-2xl font-semibold text-gray-900">
                                        {{ $status->count }}
                                    </div>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    
        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Products by Category -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Products by Category</h3>
                <canvas id="categoryChart" class="w-full" height="300"></canvas>
            </div>
    
            <!-- Top Selling Products -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Top Selling Products</h3>
                <div class="overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product Name</th>
                                <th class="px-6 py-3 bg-gray-50 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Units Sold</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($topProducts as $product)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ app()->getLocale() === 'ar' ? $product->name_arabic : $product->name_german }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">
                                    {{ $product->total_sold }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    
        <!-- Product Price Range Distribution -->
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Price Range Distribution</h3>
            <canvas id="priceRangeChart" class="w-full" height="200"></canvas>
        </div>
    
        <!-- Product Variants Overview -->
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Product Variants Overview</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Color Distribution -->
                <div>
                    <h4 class="text-md font-medium text-gray-700 mb-2">Color Distribution</h4>
                    <canvas id="colorChart" class="w-full" height="200"></canvas>
                </div>
                <!-- Size Distribution -->
                <div>
                    <h4 class="text-md font-medium text-gray-700 mb-2">Size Distribution</h4>
                    <canvas id="sizeChart" class="w-full" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Categories Chart
        const categoryCtx = document.getElementById('categoryChart').getContext('2d');
        new Chart(categoryCtx, {
            type: 'bar',
            data: {
                labels: @json($productsByCategory->pluck(app()->getLocale() === 'ar' ? 'name_arabic' : 'name_german')),
                datasets: [{
                    label: 'Number of Products',
                    data: @json($productsByCategory->pluck('count')),
                    backgroundColor: 'rgba(59, 130, 246, 0.5)',
                    borderColor: 'rgb(59, 130, 246)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    
        // Price Range Chart (You'll need to modify the controller to provide this data)
        const priceRangeCtx = document.getElementById('priceRangeChart').getContext('2d');
        new Chart(priceRangeCtx, {
            type: 'bar',
            data: {
                labels: ['$0-50', '$51-100', '$101-200', '$201-500', '$500+'],
                datasets: [{
                    label: 'Number of Products',
                    data: [15, 25, 30, 20, 10], // Replace with actual data
                    backgroundColor: 'rgba(16, 185, 129, 0.5)',
                    borderColor: 'rgb(16, 185, 129)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    
        // Color Distribution Chart
        const colorCtx = document.getElementById('colorChart').getContext('2d');
        new Chart(colorCtx, {
            type: 'doughnut',
            data: {
                labels: ['Red', 'Blue', 'Green', 'Black', 'White'], // Replace with actual data
                datasets: [{
                    data: [12, 19, 3, 5, 2], // Replace with actual data
                    backgroundColor: [
                        'rgb(239, 68, 68)',
                        'rgb(59, 130, 246)',
                        'rgb(16, 185, 129)',
                        'rgb(75, 85, 99)',
                        'rgb(229, 231, 235)'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    
        // Size Distribution Chart
        const sizeCtx = document.getElementById('sizeChart').getContext('2d');
        new Chart(sizeCtx, {
            type: 'doughnut',
            data: {
                labels: ['S', 'M', 'L', 'XL', 'XXL'], // Replace with actual data
                datasets: [{
                    data: [15, 25, 20, 15, 10], // Replace with actual data
                    backgroundColor: [
                        'rgb(239, 68, 68)',
                        'rgb(59, 130, 246)',
                        'rgb(16, 185, 129)',
                        'rgb(245, 158, 11)',
                        'rgb(139, 92, 246)'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    </script>
</x-admin-layout>