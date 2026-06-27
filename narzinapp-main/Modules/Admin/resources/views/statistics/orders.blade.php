<x-admin-layout>
    <div class="space-y-8 px-4">
        <!-- Page Header -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Order Analytics</h1>
                    <p class="mt-1 text-sm text-gray-500">Comprehensive overview of order metrics and performance</p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <select class="rounded-lg border-gray-300 text-sm focus:ring-primary-500">
                        <option>Last 7 days</option>
                        <option>Last 30 days</option>
                        <option>Last 90 days</option>
                        <option>Last year</option>
                    </select>
                    <button class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-primary-600 rounded-lg hover:bg-primary-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Export Report
                    </button>
                </div>
            </div>
        </div>
    
        <!-- Key Metrics Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Total Orders -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 hover:border-primary-500 transition-colors">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-primary-50">
                        <svg class="h-8 w-8 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h2 class="text-sm font-medium text-gray-600">Total Orders</h2>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($totalOrders) }}</p>
                        <p class="text-sm text-green-600 mt-1">
                            <span class="font-medium">↑ {{ $orderGrowthRate }}%</span> vs last month
                        </p>
                    </div>
                </div>
            </div>
    
            <!-- Revenue -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 hover:border-blue-500 transition-colors">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-50">
                        <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h2 class="text-sm font-medium text-gray-600">Total Revenue</h2>
                        <p class="text-2xl font-bold text-gray-900">${{ number_format($totalRevenue, 2) }}</p>
                        <p class="text-sm text-green-600 mt-1">
                            <span class="font-medium">↑ {{ $revenueGrowthRate }}%</span> vs last month
                        </p>
                    </div>
                </div>
            </div>
    
            <!-- Average Order Value -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 hover:border-green-500 transition-colors">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-50">
                        <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h2 class="text-sm font-medium text-gray-600">Avg Order Value</h2>
                        <p class="text-2xl font-bold text-gray-900">${{ number_format($avgOrderValue, 2) }}</p>
                        <p class="text-sm text-green-600 mt-1">
                            <span class="font-medium">↑ {{ $avgOrderGrowthRate }}%</span> vs last month
                        </p>
                    </div>
                </div>
            </div>
    
            <!-- Fulfillment Rate -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 hover:border-purple-500 transition-colors">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-purple-50">
                        <svg class="h-8 w-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h2 class="text-sm font-medium text-gray-600">Fulfillment Rate</h2>
                        <p class="text-2xl font-bold text-gray-900">{{ $fulfillmentRate }}%</p>
                        <p class="text-sm text-green-600 mt-1">
                            <span class="font-medium">↑ 2.1%</span> vs last month
                        </p>
                    </div>
                </div>
            </div>
        </div>
    
        <!-- Order Status Overview -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Status Distribution -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-6">Order Status Distribution</h3>
                <div class="space-y-4">
                    @foreach($ordersByStatus as $status)
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-sm font-medium text-gray-600">{{ ucfirst($status->order_status) }}</span>
                            <span class="text-sm font-medium text-gray-900">{{ $status->count }}</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="h-2 rounded-full {{ 
                                $status->order_status === 'completed' ? 'bg-green-500' : 
                                ($status->order_status === 'cancelled' ? 'bg-red-500' : 
                                ($status->order_status === 'processing' ? 'bg-blue-500' : 'bg-yellow-500'))
                            }}" style="width: {{ ($status->count / $totalOrders) * 100 }}%"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
    
            <!-- Shipping Methods -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-6">Shipping Methods</h3>
                <canvas id="shippingTypesChart" height="250"></canvas>
            </div>
        </div>
    
        <!-- Revenue & Orders Timeline -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-900">Revenue & Orders Timeline</h3>
                <div class="flex gap-2">
                    <button class="px-3 py-1 text-sm font-medium text-gray-600 rounded-lg bg-gray-100 hover:bg-gray-200">
                        Daily
                    </button>
                    <button class="px-3 py-1 text-sm font-medium text-gray-600 rounded-lg bg-gray-100 hover:bg-gray-200">
                        Weekly
                    </button>
                    <button class="px-3 py-1 text-sm font-medium text-gray-600 rounded-lg bg-gray-100 hover:bg-gray-200">
                        Monthly
                    </button>
                </div>
            </div>
            <canvas id="revenueOrdersChart" height="300"></canvas>
        </div>
    
        <!-- Recent Orders Table -->
        <div class="bg-white rounded-xl shadow-sm">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Recent Orders</h3>
                    <button class="px-4 py-2 text-sm font-medium text-gray-600 rounded-lg bg-gray-100 hover:bg-gray-200">
                        View All Orders
                    </button>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($recentOrders as $order)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-primary-600">
                                #{{ $order->order_number }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center">
                                        <span class="text-sm font-medium text-gray-600">
                                            {{ substr($order->user->name, 0, 1) }}
                                        </span>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-gray-900">{{ $order->user->name }}</p>
                                        <p class="text-sm text-gray-500">{{ $order->user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium {{ 
                                    $order->order_status === 'completed' ? 'bg-green-100 text-green-800' : 
                                    ($order->order_status === 'cancelled' ? 'bg-red-100 text-red-800' : 
                                    ($order->order_status === 'processing' ? 'bg-blue-100 text-blue-800' : 
                                    'bg-yellow-100 text-yellow-800'))
                                }}">
                                    {{ ucfirst($order->order_status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                <span class="font-medium text-gray-900">${{ number_format($order->total_amount, 2) }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-500">
                                {{ $order->created_at->format('M d, Y') }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    
        <!-- Additional Analytics -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Popular Products -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Popular Products</h3>
                <div class="space-y-4">
                    @foreach($popularProducts as $product)
                    <div class="flex items-center">
                        <div class="h-12 w-12 rounded-lg bg-gray-100 flex items-center justify-center">
                            @if($product->image)
                                <img src="{{ $product->image }}" alt="{{ $product->name }}" class="h-10 w-10 rounded">
                            @else
                                <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            @endif
                        </div>
                        <div class="ml-4 flex-1">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-medium text-gray-900">
                                    {{ app()->getLocale() === 'ar' ? $product->name_arabic : $product->name_german }}
                                </p>
                                <span class="text-sm font-medium text-gray-900">{{ $product->total_sold }} sold</span>
                            </div>
                            <div class="mt-1 flex items-center justify-between">
                                <p class="text-sm text-gray-500">Revenue: ${{ number_format($product->total_revenue, 2) }}</p>
                                <span class="text-sm text-green-600">↑ {{ $product->growth_rate }}%</span>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
    
        
        </div>
    </div>
    
    <script>
        // Shipping Types Chart
        const shippingCtx = document.getElementById('shippingTypesChart').getContext('2d');
        new Chart(shippingCtx, {
            type: 'doughnut',
            data: {
                labels: @json($shippingTypes->pluck('shipping_type')),
                datasets: [{
                    data: @json($shippingTypes->pluck('count')),
                    backgroundColor: [
                        'rgb(79, 70, 229)',
                        'rgb(16, 185, 129)',
                        'rgb(239, 68, 68)'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                },
                cutout: '65%'
            }
        });
    
        // Revenue & Orders Timeline Chart
        const revenueOrdersCtx = document.getElementById('revenueOrdersChart').getContext('2d');
        new Chart(revenueOrdersCtx, {
            type: 'line',
            data: {
                labels: @json($orderTrends->pluck('date')),
                datasets: [
                    {
                        label: 'Revenue',
                        data: @json($orderTrends->pluck('revenue')),
                        borderColor: 'rgb(79, 70, 229)',
                        backgroundColor: 'rgba(79, 70, 229, 0.1)',
                        yAxisID: 'y-revenue',
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: 'Orders',
                        data: @json($orderTrends->pluck('count')),
                        borderColor: 'rgb(16, 185, 129)',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        yAxisID: 'y-orders',
                        tension: 0.4,
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        position: 'top'
                    }
                },
                scales: {
                    'y-revenue': {
                        type: 'linear',
                        position: 'left',
                        grid: {
                            drawOnChartArea: false
                        },
                        ticks: {
                            callback: value => '$' + value.toLocaleString()
                        }
                    },
                    'y-orders': {
                        type: 'linear',
                        position: 'right',
                        grid: {
                            drawOnChartArea: false
                        }
                    }
                }
            }
        });
    

         
    </script>
</x-admin-layout>