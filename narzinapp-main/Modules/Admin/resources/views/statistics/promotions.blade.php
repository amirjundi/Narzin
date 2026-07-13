<x-admin-layout>
    <div class="space-y-8 px-4">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h1 class="text-2xl font-bold mb-1">Coupons &amp; Promotions</h1>
            <p class="text-sm text-gray-500">
                “Placed value” is gross placed-order value (incl. unpaid/cancelled,
                same basis as the order stats), not settled revenue.
            </p>

            <x-admin.date-range-filter :from="$from" :to="$to" />
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-lg font-semibold mb-4">Discount Penetration</h2>
            <div class="flex flex-wrap gap-8 text-sm">
                <div>
                    <div class="text-gray-500">Discounted orders</div>
                    <div class="text-2xl font-bold">{{ number_format($summary['discounted_orders']) }} / {{ number_format($summary['total_orders']) }}</div>
                </div>
                <div>
                    <div class="text-gray-500">Discount rate</div>
                    <div class="text-2xl font-bold">{{ number_format($summary['discount_rate'] * 100, 1) }}%</div>
                </div>
                <div>
                    <div class="text-gray-500">Total discount given</div>
                    <div class="text-2xl font-bold">{{ number_format($summary['total_discount'], 2) }}</div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold">Coupons</h2>
                <a href="{{ request()->fullUrlWithQuery(['export' => 'coupons']) }}"
                   class="text-xs text-blue-600 hover:underline">Export CSV</a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500 border-b">
                            <th class="py-2 pr-4">Code</th>
                            <th class="py-2 pr-4">Redemptions</th>
                            <th class="py-2 pr-4">Discount given</th>
                            <th class="py-2 pr-4">Placed value</th>
                            <th class="py-2 pr-4">AOV</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($coupons as $row)
                            <tr class="border-b">
                                <td class="py-2 pr-4">{{ $row['code'] }}</td>
                                <td class="py-2 pr-4">{{ number_format($row['redemptions']) }}</td>
                                <td class="py-2 pr-4">{{ number_format($row['discount_given'], 2) }}</td>
                                <td class="py-2 pr-4">{{ number_format($row['placed_value'], 2) }}</td>
                                <td class="py-2 pr-4">{{ number_format($row['aov'], 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="py-4 text-center text-gray-400">No coupon redemptions in this range.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold">Promotions</h2>
                <a href="{{ request()->fullUrlWithQuery(['export' => 'promotions']) }}"
                   class="text-xs text-blue-600 hover:underline">Export CSV</a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500 border-b">
                            <th class="py-2 pr-4">Name</th>
                            <th class="py-2 pr-4">Redemptions</th>
                            <th class="py-2 pr-4">Discount given</th>
                            <th class="py-2 pr-4">Placed value</th>
                            <th class="py-2 pr-4">AOV</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($promotions as $row)
                            <tr class="border-b">
                                <td class="py-2 pr-4">{{ $row['name'] }}</td>
                                <td class="py-2 pr-4">{{ number_format($row['redemptions']) }}</td>
                                <td class="py-2 pr-4">{{ number_format($row['discount_given'], 2) }}</td>
                                <td class="py-2 pr-4">{{ number_format($row['placed_value'], 2) }}</td>
                                <td class="py-2 pr-4">{{ number_format($row['aov'], 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="py-4 text-center text-gray-400">No promotion redemptions in this range.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-admin-layout>
