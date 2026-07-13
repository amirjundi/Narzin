<x-admin-layout>
    <div class="space-y-8 px-4">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h1 class="text-2xl font-bold mb-1">Fulfillment Analytics</h1>
            <p class="text-sm text-gray-500">
                SLA timing across order stages and cancellation breakdown for orders placed in the selected window.
            </p>

            <x-admin.date-range-filter :from="$from" :to="$to" />
        </div>

        <div class="flex justify-end">
            <a href="{{ request()->fullUrlWithQuery(['export' => 'sla']) }}"
               class="text-xs text-blue-600 hover:underline">Export SLA CSV</a>
        </div>

        <div class="grid gap-6 md:grid-cols-3">
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="text-gray-500 text-sm">Confirm &rarr; Ship</div>
                <div class="text-3xl font-bold">{{ number_format($sla['stages']['confirm_to_ship']['avg_hours'], 1) }}h</div>
                <div class="text-xs text-gray-400 mt-1">
                    {{ number_format($sla['stages']['confirm_to_ship']['count']) }} orders ·
                    median {{ number_format($sla['stages']['confirm_to_ship']['median_hours'], 1) }}h ·
                    p90 {{ number_format($sla['stages']['confirm_to_ship']['p90_hours'], 1) }}h
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="text-gray-500 text-sm">Ship &rarr; Deliver</div>
                <div class="text-3xl font-bold">{{ number_format($sla['stages']['ship_to_deliver']['avg_hours'], 1) }}h</div>
                <div class="text-xs text-gray-400 mt-1">
                    {{ number_format($sla['stages']['ship_to_deliver']['count']) }} orders ·
                    median {{ number_format($sla['stages']['ship_to_deliver']['median_hours'], 1) }}h ·
                    p90 {{ number_format($sla['stages']['ship_to_deliver']['p90_hours'], 1) }}h
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="text-gray-500 text-sm">Placed &rarr; Ship</div>
                <div class="text-3xl font-bold">{{ number_format($sla['stages']['placed_to_ship']['avg_hours'], 1) }}h</div>
                <div class="text-xs text-gray-400 mt-1">
                    {{ number_format($sla['stages']['placed_to_ship']['count']) }} orders ·
                    median {{ number_format($sla['stages']['placed_to_ship']['median_hours'], 1) }}h ·
                    p90 {{ number_format($sla['stages']['placed_to_ship']['p90_hours'], 1) }}h
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="text-gray-500 text-sm">SLA breach rate</div>
            <div class="text-3xl font-bold">{{ round($sla['breach_rate'] * 100, 1) }}%</div>
            <div class="text-xs text-gray-400 mt-1">
                Share of orders whose placed-to-ship time exceeded the {{ $sla['sla_hours'] }}h SLA threshold.
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold">Cancellations</h2>
                <a href="{{ request()->fullUrlWithQuery(['export' => 'cancellations']) }}"
                   class="text-xs text-blue-600 hover:underline">Export CSV</a>
            </div>
            <div class="mb-4">
                <div class="text-3xl font-bold">{{ round($cancellations['cancellation_rate'] * 100, 1) }}%</div>
                <div class="text-xs text-gray-400 mt-1">
                    {{ number_format($cancellations['total_cancelled']) }} cancelled of {{ number_format($cancellations['total_orders']) }} orders
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500 border-b">
                            <th class="py-2 pr-4">Reason</th>
                            <th class="py-2 pr-4">Count</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($cancellations['by_reason'] as $row)
                            <tr class="border-b">
                                <td class="py-2 pr-4">{{ ucfirst(str_replace('_', ' ', $row['reason'])) }}</td>
                                <td class="py-2 pr-4">{{ number_format($row['count']) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="py-4 text-center text-gray-400">No cancellations recorded in this range.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <p class="text-xs text-gray-400 px-1">
            SLA measured over orders placed in the window; orders not yet shipped don't contribute a shipping time. Historical cancellations before this release show as (unspecified).
        </p>
    </div>
</x-admin-layout>
