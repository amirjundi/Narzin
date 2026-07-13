<x-admin-layout>
    <div class="space-y-8 px-4">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h1 class="text-2xl font-bold mb-1">Returns Analytics</h1>
            <p class="text-sm text-gray-500">
                Return rate, refund volume, and reason breakdown for returns requested in the selected window.
            </p>

            <x-admin.date-range-filter :from="$from" :to="$to" />
        </div>

        <div class="flex justify-end">
            <a href="{{ request()->fullUrlWithQuery(['export' => 'summary']) }}"
               class="text-xs text-blue-600 hover:underline">Export summary CSV</a>
        </div>

        <div class="grid gap-6 md:grid-cols-3">
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="text-gray-500 text-sm">Return rate</div>
                <div class="text-3xl font-bold">{{ number_format($summary['return_rate'] * 100, 1) }}%</div>
                <div class="text-xs text-gray-400 mt-1">{{ number_format($summary['total_returns']) }} returns</div>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="text-gray-500 text-sm">Total refunded</div>
                <div class="text-3xl font-bold">{{ number_format($summary['total_refunded'], 2) }}</div>
                <div class="text-xs text-gray-400 mt-1">{{ number_format($summary['refunded']) }} refunded returns</div>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="text-gray-500 text-sm">Status counts</div>
                <div class="text-lg font-semibold">
                    {{ number_format($summary['requested']) }} requested ·
                    {{ number_format($summary['approved']) }} approved ·
                    {{ number_format($summary['rejected']) }} rejected
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold">Returns by reason</h2>
                <a href="{{ request()->fullUrlWithQuery(['export' => 'by_reason']) }}"
                   class="text-xs text-blue-600 hover:underline">Export CSV</a>
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
                        @forelse ($byReason as $row)
                            <tr class="border-b">
                                <td class="py-2 pr-4">{{ $row['reason'] }}</td>
                                <td class="py-2 pr-4">{{ number_format($row['count']) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="py-4 text-center text-gray-400">No returns recorded in this range.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-admin-layout>
