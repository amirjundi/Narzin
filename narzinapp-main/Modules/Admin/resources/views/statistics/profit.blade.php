<x-admin-layout>
    <div class="space-y-8 px-4">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h1 class="text-2xl font-bold mb-1">Platform Profit</h1>
            <p class="text-sm text-gray-500">
                Platform profit = product revenue (after discount) minus what we
                owe vendors. Orders before the vendor-earning system (Jun 2026)
                lack recorded vendor earnings and will overstate profit for
                historical ranges.
            </p>

            <form method="GET" class="mt-4 flex flex-wrap items-end gap-3">
                <label class="text-sm">From
                    <input type="date" name="from" value="{{ $from }}" class="block border rounded px-2 py-1" />
                </label>
                <label class="text-sm">To
                    <input type="date" name="to" value="{{ $to }}" class="block border rounded px-2 py-1" />
                </label>
                <button type="submit" class="bg-gray-800 text-white rounded px-4 py-1.5 text-sm">Apply</button>
            </form>
        </div>

        <div class="grid gap-6 md:grid-cols-3">
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="text-gray-500 text-sm">Platform profit (paid)</div>
                <div class="text-3xl font-bold">{{ number_format($profit['paid']['platform_profit'], 2) }}</div>
                <div class="text-xs text-gray-400 mt-1">{{ number_format($profit['paid']['margin'] * 100, 1) }}% margin</div>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="text-gray-500 text-sm">Commission collected (paid)</div>
                <div class="text-3xl font-bold">{{ number_format($profit['commission_collected'], 2) }}</div>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="text-gray-500 text-sm">Total owed to vendors</div>
                <div class="text-3xl font-bold">{{ number_format($profit['total_owed_to_vendors'], 2) }}</div>
                <a href="{{ route('vendor-payouts.index') }}" class="text-xs text-indigo-600 hover:underline mt-1 inline-block">View vendor payouts →</a>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-lg font-semibold mb-4">Placed vs Paid</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500 border-b">
                            <th class="py-2 pr-4">Basis</th>
                            <th class="py-2 pr-4">Orders</th>
                            <th class="py-2 pr-4">Revenue</th>
                            <th class="py-2 pr-4">Vendor earnings</th>
                            <th class="py-2 pr-4">Platform profit</th>
                            <th class="py-2 pr-4">Margin</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach (['placed' => 'Placed (all orders)', 'paid' => 'Paid (completed)'] as $key => $label)
                            <tr class="border-b">
                                <td class="py-2 pr-4 font-medium">{{ $label }}</td>
                                <td class="py-2 pr-4">{{ number_format($profit[$key]['orders']) }}</td>
                                <td class="py-2 pr-4">{{ number_format($profit[$key]['revenue'], 2) }}</td>
                                <td class="py-2 pr-4">{{ number_format($profit[$key]['vendor_earnings'], 2) }}</td>
                                <td class="py-2 pr-4 font-semibold">{{ number_format($profit[$key]['platform_profit'], 2) }}</td>
                                <td class="py-2 pr-4">{{ number_format($profit[$key]['margin'] * 100, 1) }}%</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-admin-layout>
