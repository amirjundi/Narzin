<x-admin-layout>
    <div class="space-y-8 px-4">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h1 class="text-2xl font-bold mb-1">Inventory Analytics</h1>
            <p class="text-sm text-gray-500">
                Stock valuation, reorder worklist, dead stock and expiring stock across active variants.
            </p>
        </div>

        <div class="grid gap-6 md:grid-cols-4">
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="text-gray-500 text-sm">Total units</div>
                <div class="text-3xl font-bold">{{ number_format($valuation['total_units']) }}</div>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="text-gray-500 text-sm">Value @ cost</div>
                <div class="text-3xl font-bold">{{ number_format($valuation['value_at_cost'], 2) }}</div>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="text-gray-500 text-sm">Value @ retail</div>
                <div class="text-3xl font-bold">{{ number_format($valuation['value_at_retail'], 2) }}</div>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="text-gray-500 text-sm">Potential margin</div>
                <div class="text-3xl font-bold">{{ number_format($valuation['potential_margin'], 2) }}</div>
            </div>
        </div>

        <div class="grid gap-6 md:grid-cols-2">
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-lg font-semibold mb-4">Valuation by category</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="text-left text-gray-500 border-b">
                                <th class="py-2 pr-4">Category</th>
                                <th class="py-2 pr-4">Units</th>
                                <th class="py-2 pr-4">Value @ cost</th>
                                <th class="py-2 pr-4">Value @ retail</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($valuation['by_category'] as $row)
                                <tr class="border-b">
                                    <td class="py-2 pr-4">{{ $row['name'] }}</td>
                                    <td class="py-2 pr-4">{{ number_format($row['units']) }}</td>
                                    <td class="py-2 pr-4">{{ number_format($row['value_at_cost'], 2) }}</td>
                                    <td class="py-2 pr-4">{{ number_format($row['value_at_retail'], 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="py-4 text-center text-gray-400">No active stock found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-lg font-semibold mb-4">Valuation by vendor</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="text-left text-gray-500 border-b">
                                <th class="py-2 pr-4">Vendor</th>
                                <th class="py-2 pr-4">Units</th>
                                <th class="py-2 pr-4">Value @ cost</th>
                                <th class="py-2 pr-4">Value @ retail</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($valuation['by_vendor'] as $row)
                                <tr class="border-b">
                                    <td class="py-2 pr-4">{{ $row['name'] }}</td>
                                    <td class="py-2 pr-4">{{ number_format($row['units']) }}</td>
                                    <td class="py-2 pr-4">{{ number_format($row['value_at_cost'], 2) }}</td>
                                    <td class="py-2 pr-4">{{ number_format($row['value_at_retail'], 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="py-4 text-center text-gray-400">No active stock found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-lg font-semibold mb-1">Reorder worklist</h2>
            <p class="text-xs text-gray-400 mb-4">threshold = {{ $lowStockThreshold }}</p>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500 border-b">
                            <th class="py-2 pr-4">SKU</th>
                            <th class="py-2 pr-4">Product</th>
                            <th class="py-2 pr-4">Stock</th>
                            <th class="py-2 pr-4">Vendor</th>
                            <th class="py-2 pr-4">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($reorder as $row)
                            <tr class="border-b">
                                <td class="py-2 pr-4">{{ $row['sku'] }}</td>
                                <td class="py-2 pr-4">{{ $row['product_name_german'] ?: $row['product_name_arabic'] }}</td>
                                <td class="py-2 pr-4">{{ number_format($row['stock']) }}</td>
                                <td class="py-2 pr-4">{{ $row['vendor_name'] }}</td>
                                <td class="py-2 pr-4">
                                    @if ($row['is_out'])
                                        <span class="inline-block px-2 py-0.5 rounded text-xs font-semibold bg-red-100 text-red-700">OUT</span>
                                    @else
                                        <span class="inline-block px-2 py-0.5 rounded text-xs font-semibold bg-amber-100 text-amber-700">LOW</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="py-4 text-center text-gray-400">Nothing to reorder right now.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-lg font-semibold mb-1">Dead stock</h2>
            <p class="text-xs text-gray-400 mb-4">
                Dead stock reflects the selected date range; the other three views are point-in-time (current stock).
            </p>
            <form method="GET" class="mb-4 flex flex-wrap items-end gap-3">
                <label class="text-sm">From <input type="date" name="from" value="{{ $from }}" class="block border rounded px-2 py-1" /></label>
                <label class="text-sm">To <input type="date" name="to" value="{{ $to }}" class="block border rounded px-2 py-1" /></label>
                <button type="submit" class="bg-gray-800 text-white rounded px-4 py-1.5 text-sm">Apply</button>
            </form>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500 border-b">
                            <th class="py-2 pr-4">SKU</th>
                            <th class="py-2 pr-4">Product</th>
                            <th class="py-2 pr-4">Stock</th>
                            <th class="py-2 pr-4">Value @ cost</th>
                            <th class="py-2 pr-4">Vendor</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($deadStock as $row)
                            <tr class="border-b">
                                <td class="py-2 pr-4">{{ $row['sku'] }}</td>
                                <td class="py-2 pr-4">{{ $row['product_name_german'] ?: $row['product_name_arabic'] }}</td>
                                <td class="py-2 pr-4">{{ number_format($row['stock']) }}</td>
                                <td class="py-2 pr-4">{{ number_format($row['value_at_cost'], 2) }}</td>
                                <td class="py-2 pr-4">{{ $row['vendor_name'] }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="py-4 text-center text-gray-400">No dead stock in this range.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-lg font-semibold mb-1">Expiring stock</h2>
            <p class="text-xs text-gray-400 mb-4">within {{ $expiryDaysAhead }} days</p>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500 border-b">
                            <th class="py-2 pr-4">SKU</th>
                            <th class="py-2 pr-4">Product</th>
                            <th class="py-2 pr-4">Expiry date</th>
                            <th class="py-2 pr-4">Stock</th>
                            <th class="py-2 pr-4">Value @ cost</th>
                            <th class="py-2 pr-4">Vendor</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($expiring as $row)
                            <tr class="border-b">
                                <td class="py-2 pr-4">{{ $row['sku'] }}</td>
                                <td class="py-2 pr-4">{{ $row['product_name_german'] ?: $row['product_name_arabic'] }}</td>
                                <td class="py-2 pr-4">{{ $row['expiry_date'] }}</td>
                                <td class="py-2 pr-4">{{ number_format($row['stock']) }}</td>
                                <td class="py-2 pr-4">{{ number_format($row['value_at_cost'], 2) }}</td>
                                <td class="py-2 pr-4">{{ $row['vendor_name'] }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="py-4 text-center text-gray-400">Nothing expiring in this window.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <p class="text-xs text-gray-400 px-1">
            All figures cover active variants only.
        </p>
    </div>
</x-admin-layout>
