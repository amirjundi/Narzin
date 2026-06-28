<x-admin-layout>
    <div class="max-w-5xl mx-auto mt-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">
                Statement: {{ $vendor->store_name_in_german ?? $vendor->store_name_in_arabic }}
            </h1>
            <a href="{{ route('vendor-payouts.index') }}"
               class="px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400">
               Back to Payouts
            </a>
        </div>

        <div class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded">
            <p class="text-sm text-gray-500">Payable Balance</p>
            <p class="text-3xl font-bold text-blue-700">{{ number_format($payable, 2) }}</p>
        </div>

        @if (session('success'))
            <div class="mb-4 p-3 bg-green-100 text-green-700 rounded">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 bg-red-100 text-red-700 p-3 rounded">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Record Payout Form -->
        <div class="mb-6 bg-white shadow rounded p-5">
            <h2 class="text-lg font-semibold mb-4">Record Payout</h2>
            <form method="POST" action="{{ route('vendor-payouts.payout', $vendor->id) }}">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block mb-1 font-medium text-sm" for="amount">Amount</label>
                        <input type="number" step="0.01" name="amount" id="amount"
                               value="{{ old('amount', $payable) }}"
                               class="w-full border border-gray-300 rounded p-2 focus:ring focus:ring-blue-200"
                               min="0.01" required>
                    </div>
                    <div>
                        <label class="block mb-1 font-medium text-sm" for="method">Method</label>
                        <input type="text" name="method" id="method"
                               value="{{ old('method') }}"
                               class="w-full border border-gray-300 rounded p-2 focus:ring focus:ring-blue-200"
                               placeholder="e.g. bank, cash">
                    </div>
                    <div>
                        <label class="block mb-1 font-medium text-sm" for="reference">Reference</label>
                        <input type="text" name="reference" id="reference"
                               value="{{ old('reference') }}"
                               class="w-full border border-gray-300 rounded p-2 focus:ring focus:ring-blue-200"
                               placeholder="Transaction reference">
                    </div>
                    <div>
                        <label class="block mb-1 font-medium text-sm" for="notes">Notes</label>
                        <input type="text" name="notes" id="notes"
                               value="{{ old('notes') }}"
                               class="w-full border border-gray-300 rounded p-2 focus:ring focus:ring-blue-200">
                    </div>
                </div>
                <button type="submit"
                        class="mt-4 px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                    Record Payout
                </button>
            </form>
        </div>

        <!-- Adjustment Form -->
        <div class="mb-6 bg-white shadow rounded p-5">
            <h2 class="text-lg font-semibold mb-4">Manual Adjustment</h2>
            <form method="POST" action="{{ route('vendor-payouts.adjust', $vendor->id) }}">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block mb-1 font-medium text-sm" for="adj_amount">Amount (positive or negative)</label>
                        <input type="number" step="0.01" name="amount" id="adj_amount"
                               value="{{ old('amount') }}"
                               class="w-full border border-gray-300 rounded p-2 focus:ring focus:ring-blue-200"
                               required>
                    </div>
                    <div>
                        <label class="block mb-1 font-medium text-sm" for="description">Description</label>
                        <input type="text" name="description" id="description"
                               value="{{ old('description') }}"
                               class="w-full border border-gray-300 rounded p-2 focus:ring focus:ring-blue-200"
                               required>
                    </div>
                </div>
                <button type="submit"
                        class="mt-4 px-4 py-2 bg-yellow-600 text-white rounded hover:bg-yellow-700">
                    Apply Adjustment
                </button>
            </form>
        </div>

        <!-- Transaction History -->
        <div class="bg-white shadow rounded">
            <div class="p-4 border-b">
                <h2 class="text-lg font-semibold">Transaction History</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full border-collapse">
                    <thead>
                        <tr class="bg-gray-100 text-left">
                            <th class="px-4 py-2 border-b">Type</th>
                            <th class="px-4 py-2 border-b">Amount</th>
                            <th class="px-4 py-2 border-b">Description</th>
                            <th class="px-4 py-2 border-b">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($entries as $entry)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 border-b">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                        {{ ($entry->type === 'earning' || ($entry->type === 'adjustment' && $entry->amount >= 0)) ? 'bg-green-100 text-green-800' : (($entry->type === 'reversal' || $entry->type === 'payout') ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-700') }}">
                                        {{ ucfirst($entry->type) }}
                                    </span>
                                </td>
                                <td class="px-4 py-2 border-b {{ $entry->amount >= 0 ? 'text-green-700' : 'text-red-700' }}">
                                    {{ number_format($entry->amount, 2) }}
                                </td>
                                <td class="px-4 py-2 border-b text-gray-600">{{ $entry->description }}</td>
                                <td class="px-4 py-2 border-b text-gray-500">{{ $entry->created_at->format('Y-m-d H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-4 text-center text-gray-500">No transactions yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-admin-layout>
