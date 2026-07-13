<x-admin-layout>
    <div class="space-y-8 px-4">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h1 class="text-2xl font-bold mb-1">Conversion Funnel</h1>
            <p class="text-sm text-gray-500">
                Some stages (Sessions, Add to Cart) populate once the mobile/web
                cart &amp; session tracking hooks are live. Server-captured stages
                (Product View, Checkout, Order Placed) are live now.
            </p>

            <x-admin.date-range-filter :from="$from" :to="$to" />
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold">Funnel</h2>
                <span class="text-sm text-gray-600">
                    Overall conversion: {{ number_format(($funnel['overall_conversion'] ?? 0) * 100, 2) }}%
                </span>
            </div>
            <a href="{{ request()->fullUrlWithQuery(['export' => 'funnel']) }}"
               class="text-xs text-blue-600 hover:underline">Export CSV</a>

            @php $maxCount = max(1, collect($funnel['stages'])->max('count')); @endphp
            <div class="space-y-3">
                @foreach ($funnel['stages'] as $stage)
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="font-medium">{{ $stage['label'] }}</span>
                            <span class="text-gray-600">
                                {{ number_format($stage['count']) }}
                                @if (!is_null($stage['conversion_from_prev']))
                                    <span class="text-gray-400">({{ number_format($stage['conversion_from_prev'] * 100, 1) }}%)</span>
                                @endif
                            </span>
                        </div>
                        <div class="w-full bg-gray-100 rounded h-3">
                            <div class="bg-indigo-500 h-3 rounded" style="width: {{ ($stage['count'] / $maxCount) * 100 }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-lg font-semibold mb-4">Abandoned Carts</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500 border-b">
                            <th class="py-2 pr-4">Customer</th>
                            <th class="py-2 pr-4">Session</th>
                            <th class="py-2 pr-4">Cart value</th>
                            <th class="py-2 pr-4">Items</th>
                            <th class="py-2 pr-4">Age (h)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($abandoned as $row)
                            <tr class="border-b">
                                <td class="py-2 pr-4">{{ $row['user_name'] ?? 'Guest' }}</td>
                                <td class="py-2 pr-4 font-mono text-xs">{{ \Illuminate\Support\Str::limit($row['session_id'], 12) }}</td>
                                <td class="py-2 pr-4">{{ number_format($row['cart_value'], 2) }}</td>
                                <td class="py-2 pr-4">{{ $row['item_count'] }}</td>
                                <td class="py-2 pr-4">{{ $row['age_hours'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-4 text-center text-gray-400">
                                    No abandoned carts yet — cart tracking (/track/cart) is not wired into the apps yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-lg font-semibold mb-1">Attribution</h2>
            <p class="text-sm text-gray-500 mb-4">
                Placed-order value by traffic source (gross — counts all placed
                orders including unpaid/cancelled, same basis as the order stats,
                not settled revenue). Fills in as UTM-tagged visitors place
                orders; untagged/direct traffic shows as “(none)”.
            </p>

            <div class="grid gap-6 md:grid-cols-2">
                <div class="overflow-x-auto">
                    <h3 class="text-sm font-medium mb-2 text-gray-700">By Channel</h3>
                    <a href="{{ request()->fullUrlWithQuery(['export' => 'attribution_channel']) }}"
                       class="text-xs text-blue-600 hover:underline">Export CSV</a>
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="text-left text-gray-500 border-b">
                                <th class="py-2 pr-4">Source</th>
                                <th class="py-2 pr-4">Medium</th>
                                <th class="py-2 pr-4">Orders</th>
                                <th class="py-2 pr-4">Placed value</th>
                                <th class="py-2 pr-4">AOV</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($attribution['byChannel'] as $row)
                                <tr class="border-b">
                                    <td class="py-2 pr-4">{{ $row['source'] }}</td>
                                    <td class="py-2 pr-4">{{ $row['medium'] }}</td>
                                    <td class="py-2 pr-4">{{ number_format($row['orders']) }}</td>
                                    <td class="py-2 pr-4">{{ number_format($row['revenue'], 2) }}</td>
                                    <td class="py-2 pr-4">{{ number_format($row['aov'], 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="py-4 text-center text-gray-400">No attributed orders yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="overflow-x-auto">
                    <h3 class="text-sm font-medium mb-2 text-gray-700">By Campaign</h3>
                    <a href="{{ request()->fullUrlWithQuery(['export' => 'attribution_campaign']) }}"
                       class="text-xs text-blue-600 hover:underline">Export CSV</a>
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="text-left text-gray-500 border-b">
                                <th class="py-2 pr-4">Campaign</th>
                                <th class="py-2 pr-4">Orders</th>
                                <th class="py-2 pr-4">Placed value</th>
                                <th class="py-2 pr-4">AOV</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($attribution['byCampaign'] as $row)
                                <tr class="border-b">
                                    <td class="py-2 pr-4">{{ $row['campaign'] }}</td>
                                    <td class="py-2 pr-4">{{ number_format($row['orders']) }}</td>
                                    <td class="py-2 pr-4">{{ number_format($row['revenue'], 2) }}</td>
                                    <td class="py-2 pr-4">{{ number_format($row['aov'], 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="py-4 text-center text-gray-400">No attributed orders yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
