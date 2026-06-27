<x-admin-layout>
    <div class="space-y-8 px-4">
        <!-- Page Header -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">User Analytics</h1>
                    <p class="mt-1 text-sm text-gray-500">Comprehensive overview of user statistics and behavior</p>
                </div>
                <div class="flex space-x-3">
                    <select class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option>Last 7 days</option>
                        <option>Last 30 days</option>
                        <option>Last 90 days</option>
                        <option>Last year</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Key Metrics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Total Users -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 hover:border-indigo-500 transition-colors">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-indigo-50">
                        <svg class="h-8 w-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h2 class="text-sm font-medium text-gray-600">Total Users</h2>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($totalUsers) }}</p>
                        <p class="text-sm text-green-600 mt-1">
                            <span class="font-medium">↑ 12%</span> vs last month
                        </p>
                    </div>
                </div>
            </div>

            <!-- Active Users -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 hover:border-indigo-500 transition-colors">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-50">
                        <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h2 class="text-sm font-medium text-gray-600">Active Users (30d)</h2>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($activeUsers) }}</p>
                        <p class="text-sm text-green-600 mt-1">
                            <span class="font-medium">↑ 8%</span> vs last month
                        </p>
                    </div>
                </div>
            </div>

            <!-- Average Order Value -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 hover:border-indigo-500 transition-colors">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-50">
                        <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h2 class="text-sm font-medium text-gray-600">Avg Order Value</h2>
                        <p class="text-2xl font-bold text-gray-900">${{ number_format($avgOrderValue, 2) }}</p>
                        <p class="text-sm text-green-600 mt-1">
                            <span class="font-medium">↑ 5%</span> vs last month
                        </p>
                    </div>
                </div>
            </div>

            <!-- Customer Lifetime Value -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 hover:border-indigo-500 transition-colors">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-purple-50">
                        <svg class="h-8 w-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h2 class="text-sm font-medium text-gray-600">Avg Customer LTV</h2>
                        <p class="text-2xl font-bold text-gray-900">${{ number_format($avgLifetimeValue, 2) }}</p>
                        <p class="text-sm text-green-600 mt-1">
                            <span class="font-medium">↑ 15%</span> vs last month
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- User Growth Trend -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-6">User Growth Trend</h3>
                <canvas id="registrationChart" class="w-full" height="300"></canvas>
            </div>

            <!-- Language Distribution -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-6">Language Preferences</h3>
                <canvas id="languageChart" class="w-full" height="300"></canvas>
            </div>
        </div>

        <!-- User Engagement and Activity -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Top Customers -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Top Customers</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Customer</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Orders</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Total Spent</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach ($topCustomers as $customer)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center">
                                                <span class="text-sm font-medium text-gray-600">
                                                    {{ substr($customer->name, 0, 1) }}
                                                </span>
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-sm font-medium text-gray-900">{{ $customer->name }}</p>
                                                <p class="text-sm text-gray-500">{{ $customer->email }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $customer->orders_count }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                                        ${{ number_format($customer->total_spent, 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Purchase Categories -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Popular Categories</h3>
                <canvas id="categoryChart" class="w-full" height="300"></canvas>
            </div>
        </div>

        <!-- User Retention Matrix -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">User Retention Matrix</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Cohort</th>
                            @foreach (range(0, 5) as $month)
                                <th
                                    class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Month {{ $month }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach ($retentionMatrix as $cohort)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $cohort['month'] }}
                                </td>
                                @foreach ($cohort['retention'] as $retention)
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                {{ $retention >= 75
                                    ? 'bg-green-100 text-green-800'
                                    : ($retention >= 50
                                        ? 'bg-blue-100 text-blue-800'
                                        : ($retention >= 25
                                            ? 'bg-yellow-100 text-yellow-800'
                                            : 'bg-red-100 text-red-800')) }}">
                                            {{ $retention }}%
                                        </span>
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Registration Trend Chart
        const registrationCtx = document.getElementById('registrationChart').getContext('2d');
        new Chart(registrationCtx, {
            type: 'line',
            data: {
                labels: @json($userRegistrationTrend->pluck('date')),
                datasets: [{
                    label: 'New Users',
                    data: @json($userRegistrationTrend->pluck('count')),
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
                            stepSize: 1
                        },
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

        // Language Distribution Chart
        const languageCtx = document.getElementById('languageChart').getContext('2d');
        new Chart(languageCtx, {
            type: 'doughnut',
            data: {
                labels: @json($usersByLanguage->pluck('preferred_language')),
                datasets: [{
                    data: @json($usersByLanguage->pluck('count')),
                    backgroundColor: [
                        'rgb(79, 70, 229)',
                        'rgb(16, 185, 129)',
                        'rgb(239, 68, 68)',
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
                cutout: '60%'
            }
        });

        // Category Distribution Chart
        const categoryCtx = document.getElementById('categoryChart').getContext('2d');
        new Chart(categoryCtx, {
            type: 'bar',
            data: {
                labels: @json($popularCategories->pluck('name')),
                datasets: [{
                    label: 'Purchases',
                    data: @json($popularCategories->pluck('purchase_count')),
                    backgroundColor: 'rgba(79, 70, 229, 0.8)',
                    borderRadius: 6,
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
    </script>
</x-admin-layout>
