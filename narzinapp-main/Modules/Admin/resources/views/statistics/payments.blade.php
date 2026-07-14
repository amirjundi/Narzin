<x-admin-layout>
    <div class="space-y-8 px-4">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h1 class="text-2xl font-bold mb-1">Payment Analytics</h1>
            <p class="text-sm text-gray-500">
                Order-level metrics cover all orders now. Attempt-level metrics
                (retries, failure reasons) fill in as new gateway payments flow.
            </p>
            <x-admin.date-range-filter :from="$from" :to="$to" />
        </div>

        <div class="grid gap-6 md:grid-cols-3">
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="text-gray-500 text-sm">Order success rate</div>
                <div class="text-3xl font-bold">{{ number_format($orderSummary['success_rate'] * 100, 1) }}%</div>
                <div class="text-xs text-gray-400 mt-1">{{ number_format($orderSummary['completed']) }} completed · {{ number_format($orderSummary['failed'] + $orderSummary['expired']) }} failed</div>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="text-gray-500 text-sm">Gateway success rate</div>
                <div class="text-3xl font-bold">{{ number_format($attempts['gateway_success_rate'] * 100, 1) }}%</div>
                <div class="text-xs text-gray-400 mt-1">{{ number_format($attempts['total']) }} attempts</div>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="text-gray-500 text-sm">Method mix (inferred)</div>
                <div class="text-lg font-semibold">{{ number_format($methodMix['wallet_involved']) }} wallet · {{ number_format($methodMix['gateway_only']) }} gateway</div>
                <a href="{{ request()->fullUrlWithQuery(['export' => 'methods']) }}"
                   class="text-xs text-blue-600 hover:underline">Export CSV</a>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold">Order payment status</h2>
                <a href="{{ request()->fullUrlWithQuery(['export' => 'status_breakdown']) }}"
                   class="text-xs text-blue-600 hover:underline">Export CSV</a>
            </div>
            <div class="flex flex-wrap gap-6 text-sm">
                @foreach (['completed'=>'Completed','failed'=>'Failed','expired'=>'Expired','refunded'=>'Refunded','processing'=>'Processing','not_paid'=>'Not paid'] as $k => $label)
                    <div><span class="text-gray-500">{{ $label }}:</span> <span class="font-semibold">{{ number_format($orderSummary[$k]) }}</span></div>
                @endforeach
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold">Gateway failure reasons</h2>
                <a href="{{ request()->fullUrlWithQuery(['export' => 'failure_reasons']) }}"
                   class="text-xs text-blue-600 hover:underline">Export CSV</a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500 border-b">
                            <th class="py-2 pr-4">Response code</th>
                            <th class="py-2 pr-4">Failed attempts</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($failureReasons as $row)
                            <tr class="border-b">
                                <td class="py-2 pr-4 font-mono">{{ $row['response_code'] }}</td>
                                <td class="py-2 pr-4">{{ number_format($row['count']) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="py-4 text-center text-gray-400">No failed gateway attempts recorded yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-admin-layout>
