<x-admin-layout>
    <div class="max-w-6xl mx-auto mt-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Vendor Payouts</h1>
            <a href="{{ route('vendor-payouts.settings') }}"
               class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">
               Payout Settings
            </a>
        </div>

        @if (session('success'))
            <div class="mb-4 p-3 bg-green-100 text-green-700 rounded">
                {{ session('success') }}
            </div>
        @endif

        <div class="overflow-x-auto bg-white shadow rounded">
            <table class="min-w-full border-collapse">
                <thead>
                    <tr class="bg-gray-100 text-left">
                        <th class="px-4 py-2 border-b">Vendor</th>
                        <th class="px-4 py-2 border-b">Pending Earnings</th>
                        <th class="px-4 py-2 border-b">Payable Balance</th>
                        <th class="px-4 py-2 border-b">Total Paid</th>
                        <th class="px-4 py-2 border-b">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($vendors as $row)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 border-b font-medium">
                                {{ $row['vendor']->store_name_in_german ?? $row['vendor']->store_name_in_arabic }}
                            </td>
                            <td class="px-4 py-2 border-b">{{ number_format($row['pending'], 2) }}</td>
                            <td class="px-4 py-2 border-b">
                                <span class="font-semibold {{ $row['payable'] > 0 ? 'text-green-700' : 'text-gray-500' }}">
                                    {{ number_format($row['payable'], 2) }}
                                </span>
                            </td>
                            <td class="px-4 py-2 border-b">{{ number_format($row['paid'], 2) }}</td>
                            <td class="px-4 py-2 border-b">
                                <a href="{{ route('vendor-payouts.show', $row['vendor']->id) }}"
                                   class="px-3 py-1 bg-blue-600 text-white rounded text-sm hover:bg-blue-700">
                                   View Statement
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-4 text-center text-gray-500">No vendors found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-admin-layout>
