<x-admin-layout>
    <div class="space-y-8 px-4">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h1 class="text-2xl font-bold mb-1">Payment Analytics</h1>
            <p class="text-sm text-gray-500">
                Order-level metrics cover all orders now. Attempt-level metrics
                (retries, failure reasons) fill in as new gateway payments flow.
            </p>
            <form method="GET" class="mt-4 flex flex-wrap items-end gap-3">
                <label class="text-sm">From <input type="date" name="from" value="{{ $from }}" class="block border rounded px-2 py-1" /></label>
                <label class="text-sm">To <input type="date" name="to" value="{{ $to }}" class="block border rounded px-2 py-1" /></label>
                <button type="submit" class="bg-gray-800 text-white rounded px-4 py-1.5 text-sm">Apply</button>
            </form>
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
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-lg font-semibold mb-4">Order payment status</h2>
            <div class="flex flex-wrap gap-6 text-sm">
                @foreach (['completed'=>'Completed','failed'=>'Failed','expired'=>'Expired','processing'=>'Processing','not_paid'=>'Not paid'] as $k => $label)
                    <div><span class="text-gray-500">{{ $label }}:</span> <span class="font-semibold">{{ number_format($orderSummary[$k]) }}</span></div>
                @endforeach
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-lg font-semibold mb-4">Gateway failure reasons</h2>
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
