<x-admin-layout>
    <div class="space-y-8 px-4">
        <!-- Page Header -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Vendor Analytics</h1>
                    <p class="mt-1 text-sm text-gray-500">Comprehensive overview of vendor performance and metrics</p>
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
    
        <!-- Key Metrics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Total Vendors -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 hover:border-primary-500 transition-colors">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-primary-50">
                        <svg class="h-8 w-8 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h2 class="text-sm font-medium text-gray-600">Total Vendors</h2>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($totalVendors) }}</p>
                        <p class="text-sm text-green-600 mt-1">
                            <span class="font-medium">↑ {{ $vendorGrowthRate }}%</span> vs last month
                        </p>
                    </div>
                </div>
            </div>
    
            <!-- Active Vendors -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 hover:border-green-500 transition-colors">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-50">
                        <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h2 class="text-sm font-medium text-gray-600">Active Vendors</h2>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($activeVendors) }}</p>
                        <p class="text-sm text-green-600 mt-1">
                            <span class="font-medium">{{ $activeVendorRate }}%</span> activity rate
                        </p>
                    </div>
                </div>
            </div>
    
            <!-- Total Revenue -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 hover:border-blue-500 transition-colors">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-50">
                        <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h2 class="text-sm font-medium text-gray-600">Total Revenue</h2>
                        <p class="text-2xl font-bold text-gray-900">${{ number_format($totalRevenue) }}</p>
                        <p class="text-sm text-green-600 mt-1">
                            <span class="font-medium">↑ {{ $revenueGrowthRate }}%</span> vs last month
                        </p>
                    </div>
                </div>
            </div>
    
            <!-- Average Order Value -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 hover:border-purple-500 transition-colors">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-purple-50">
                        <svg class="h-8 w-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h2 class="text-sm font-medium text-gray-600">Avg Order Value</h2>
                        <p class="text-2xl font-bold text-gray-900">${{ number_format($avgOrderValue) }}</p>
                        <p class="text-sm text-green-600 mt-1">
                            <span class="font-medium">↑ {{ $avgOrderGrowthRate }}%</span> vs last month
                        </p>
                    </div>
                </div>
            </div>
        </div>
    
        <!-- Charts Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Store Types Distribution -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Store Types Distribution</h3>
                    <div class="flex gap-2">
                        <button class="px-3 py-1 text-sm font-medium text-gray-600 rounded-lg bg-gray-100 hover:bg-gray-200">
                            By Count
                        </button>
                        <button class="px-3 py-1 text-sm font-medium text-gray-600 rounded-lg bg-gray-100 hover:bg-gray-200">
                            By Revenue
                        </button>
                    </div>
                </div>
                <canvas id="storeTypeChart" class="w-full" height="300"></canvas>
            </div>
    
            <!-- Revenue Trend -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Revenue Trend</h3>
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
                <canvas id="revenueTrendChart" class="w-full" height="300"></canvas>
            </div>
        </div>
    
        <!-- Top Performing Vendors Table -->
        <div class="bg-white rounded-xl shadow-sm">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Top Performing Vendors</h3>
                    <div class="flex gap-2">
                        <select class="rounded-lg border-gray-300 text-sm focus:ring-primary-500">
                            <option>By Revenue</option>
                            <option>By Orders</option>
                            <option>By Growth</option>
                        </select>
                        <button class="px-3 py-2 text-sm font-medium text-gray-600 rounded-lg bg-gray-100 hover:bg-gray-200">
                            View All
                        </button>
                    </div>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vendor</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Store Type</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Orders</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Revenue</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Growth</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($topVendors as $vendor)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <img class="h-10 w-10 rounded-full" src="{{ $vendor->store_logo ?? 'https://via.placeholder.com/40' }}" alt="">
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ app()->getLocale() === 'ar' ? $vendor->store_name_in_arabic : $vendor->store_name_in_german }}
                                        </div>
                                        <div class="text-sm text-gray-500">{{ $vendor->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $vendor->store_type === 'Retail' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                                    {{ $vendor->store_type }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-500">
                                {{ number_format($vendor->order_count) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900">
                                ${{ number_format($vendor->revenue, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <div class="flex items-center justify-end">
                                    <div class="text-sm {{ $vendor->growth_rate >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $vendor->growth_rate >= 0 ? '↑' : '↓' }} {{ abs($vendor->growth_rate) }}%
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    
        <!-- Vendor Locations Map -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Vendor Locations</h3>
                <div class="flex gap-2">
                    <button class=class="px-3 py-1 text-sm font-medium text-gray-600 rounded-lg bg-gray-100 hover:bg-gray-200">
                        Cluster View
                    </button>
                    <button class="px-3 py-1 text-sm font-medium text-gray-600 rounded-lg bg-gray-100 hover:bg-gray-200">
                        Heat Map
                    </button>
                </div>
            </div>
            <div id="vendorMap" class="w-full h-[400px] rounded-lg border border-gray-200"></div>
        </div>
    
        <!-- Performance Metrics -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Category Performance -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Top Categories by Vendor</h3>
                <div class="space-y-4">
                    @foreach($topCategories as $category)
                    <div class="flex items-center">
                        <div class="w-full">
                            <div class="flex justify-between mb-1">
                                <span class="text-sm font-medium text-gray-700">
                                    {{ app()->getLocale() === 'ar' ? $category->name_arabic : $category->name_german }}
                                </span>
                                <span class="text-sm font-medium text-gray-700">{{ $category->vendor_count }} vendors</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-primary-600 h-2 rounded-full" style="width: {{ $category->percentage }}%"></div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
    
            <!-- Review Metrics -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Vendor Ratings Distribution</h3>
                <canvas id="ratingsChart" class="w-full" height="300"></canvas>
            </div>
        </div>
    </div>
    
    <script>
        // Store Types Chart
        const storeTypeCtx = document.getElementById('storeTypeChart').getContext('2d');
        new Chart(storeTypeCtx, {
            type: 'doughnut',
            data: {
                labels: @json($vendorsByType->pluck('store_type')),
                datasets: [{
                    data: @json($vendorsByType->pluck('count')),
                    backgroundColor: [
                        'rgb(79, 70, 229)',
                        'rgb(16, 185, 129)',
                        'rgb(239, 68, 68)',
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
                },
                cutout: '65%'
            }
        });
    
        // Revenue Trend Chart
        const revenueCtx = document.getElementById('revenueTrendChart').getContext('2d');
        new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: @json($revenueTrend->pluck('date')),
                datasets: [{
                    label: 'Revenue',
                    data: @json($revenueTrend->pluck('amount')),
                    borderColor: 'rgb(79, 70, 229)',
                    backgroundColor: 'rgba(79, 70, 229, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: value => '$' + value.toLocaleString()
                        }
                    }
                }
            }
        });
    
        // Ratings Distribution Chart
        const ratingsCtx = document.getElementById('ratingsChart').getContext('2d');
        new Chart(ratingsCtx, {
            type: 'bar',
            data: {
                labels: ['5★', '4★', '3★', '2★', '1★'],
                datasets: [{
                    data: @json($ratingDistribution),
                    backgroundColor: [
                        'rgb(16, 185, 129)',
                        'rgb(79, 70, 229)',
                        'rgb(245, 158, 11)',
                        'rgb(239, 68, 68)',
                        'rgb(190, 18, 60)'
                    ],
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            display: true,
                            drawBorder: false
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    
        // Initialize Map
        function initMap() {
            const map = new google.maps.Map(document.getElementById('vendorMap'), {
                zoom: 12,
                center: { lat: {{ $mapCenter['lat'] }}, lng: {{ $mapCenter['lng'] }} },
                styles: [
                    {
                        featureType: 'administrative',
                        elementType: 'geometry',
                        stylers: [{ visibility: 'off' }]
                    },
                    {
                        featureType: 'poi',
                        stylers: [{ visibility: 'off' }]
                    },
                    {
                        featureType: 'transit',
                        elementType: 'labels.icon',
                        stylers: [{ visibility: 'off' }]
                    }
                ]
            });
    
            const vendors = @json($vendorLocations);
            const infoWindow = new google.maps.InfoWindow();
    
            vendors.forEach(vendor => {
                const marker = new google.maps.Marker({
                    position: { lat: parseFloat(vendor.latitude), lng: parseFloat(vendor.longitude) },
                    map: map,
                    title: vendor.store_name,
                    icon: {
                        path: google.maps.SymbolPath.CIRCLE,
                        fillColor: vendor.status === 'Active' ? '#10B981' : '#EF4444',
                        fillOpacity: 0.9,
                        strokeWeight: 0,
                        scale: 10
                    }
                });
    
                marker.addListener('click', () => {
                    const content = `
                        <div class="p-2">
                            <h3 class="font-semibold">${vendor.store_name}</h3>
                            <p class="text-sm text-gray-600">${vendor.address}</p>
                            <p class="text-sm text-gray-600">${vendor.store_type}</p>
                            <div class="mt-2">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full ${
                                    vendor.status === 'Active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                                }">${vendor.status}</span>
                            </div>
                        </div>
                    `;
                    infoWindow.setContent(content);
                    infoWindow.open(map, marker);
                });
            });
        }
    
        // Load map when the page is ready
        window.addEventListener('load', initMap);
    </script>
</x-admin-layout>