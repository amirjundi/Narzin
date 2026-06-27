<x-admin-layout>
    <div class="max-w-4xl mx-auto mt-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Exchange Rate & Markup</h1>
            <a href="{{ route('price-exchange.create') }}"
               class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
               Set New Rate
            </a>
        </div>

        @if (session('success'))
            <div class="mb-4 p-3 bg-green-100 text-green-700 rounded">
                {{ session('success') }}
            </div>
        @endif

        <!-- Current Active Rate -->
        @php
            $activeRate = $prices->first();
        @endphp
        @if($activeRate)
        <div class="mb-6 bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-lg p-5">
            <h3 class="text-sm font-semibold text-blue-600 uppercase tracking-wider mb-3">Currently Active</h3>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-500">Exchange Rate</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $activeRate->price_rate }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Set On</p>
                    <p class="text-lg font-semibold text-gray-700">{{ $activeRate->created_at->format('M d, Y H:i') }}</p>
                </div>
            </div>
        </div>
        @endif

        <div class="overflow-x-auto bg-white shadow rounded">
            <table class="min-w-full border-collapse">
                <thead>
                    <tr class="bg-gray-100 text-left">
                        <th class="px-4 py-2 border-b">ID</th>
                        <th class="px-4 py-2 border-b">Exchange Rate</th>
                        <th class="px-4 py-2 border-b">Created At</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($prices as $price)
                        <tr class="hover:bg-gray-50 {{ $loop->first ? 'bg-blue-50/50' : '' }}">
                            <td class="px-4 py-2 border-b">{{ $price->id }}</td>
                            <td class="px-4 py-2 border-b font-medium">{{ $price->price_rate }}</td>
                            <td class="px-4 py-2 border-b text-gray-500">{{ $price->created_at->format('Y-m-d H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-4 py-4 text-center text-gray-500">
                                No exchange rates set yet. Click "Set New Rate" to add one.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $prices->links() }}
        </div>
    </div>
</x-admin-layout>
