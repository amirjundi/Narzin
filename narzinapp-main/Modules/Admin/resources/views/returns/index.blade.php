<x-admin-layout>
    @section('title', 'Returns')

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Returns</h1>
        <p class="text-gray-500 text-sm mt-1">Review return requests, approve/reject, and issue refunds.</p>
    </div>

    @if (session('success'))
        <div class="mb-4 px-4 py-3 bg-green-50 text-green-700 rounded-lg text-sm">{{ session('success') }}</div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Order</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Customer</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Reason</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Requested</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($returns as $return)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 text-sm font-semibold text-gray-900">
                                {{ $return->order->order_number ?? ('#' . $return->order_id) }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                {{ $return->user->name ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                {{ $return->reason }}
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $statusColors = [
                                        'requested' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                                        'approved' => 'bg-blue-100 text-blue-800 border-blue-200',
                                        'rejected' => 'bg-red-100 text-red-800 border-red-200',
                                        'refunded' => 'bg-green-100 text-green-800 border-green-200',
                                    ];
                                    $color = $statusColors[$return->status] ?? 'bg-gray-100 text-gray-800 border-gray-200';
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border {{ $color }}">
                                    {{ ucfirst($return->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ optional($return->requested_at)->format('M d, Y H:i') }}
                            </td>
                            <td class="px-6 py-4">
                                @if ($return->status === 'requested')
                                    <div class="flex items-center gap-2">
                                        <form method="POST" action="{{ route('returns.approve', $return->id) }}">
                                            @csrf
                                            <button type="submit" class="px-3 py-1.5 bg-green-50 text-green-700 rounded-lg hover:bg-green-100 text-xs font-medium transition-colors">
                                                Approve
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('returns.reject', $return->id) }}" class="flex items-center gap-1">
                                            @csrf
                                            <input type="text" name="admin_note" placeholder="Reason (optional)"
                                                   class="border rounded px-2 py-1 text-xs w-32" />
                                            <button type="submit" class="px-3 py-1.5 bg-red-50 text-red-700 rounded-lg hover:bg-red-100 text-xs font-medium transition-colors">
                                                Reject
                                            </button>
                                        </form>
                                    </div>
                                @elseif ($return->status === 'approved')
                                    <form method="POST" action="{{ route('returns.refund', $return->id) }}">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100 text-xs font-medium transition-colors">
                                            Refund
                                        </button>
                                    </form>
                                @else
                                    <span class="text-xs text-gray-400">
                                        @if ($return->admin_note)
                                            {{ $return->admin_note }}
                                        @else
                                            &mdash;
                                        @endif
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                No returns found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($returns->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $returns->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</x-admin-layout>
