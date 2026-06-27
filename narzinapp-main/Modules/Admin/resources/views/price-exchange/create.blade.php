<x-admin-layout>
    <div class="max-w-lg mx-auto mt-8 bg-white shadow p-6 rounded">
        <h1 class="text-2xl font-bold mb-6">Set New Exchange Rate & Markup</h1>

        @if ($errors->any())
            <div class="mb-4 bg-red-100 text-red-700 p-3 rounded">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('price-exchange.store') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block mb-1 font-semibold" for="price_rate">Exchange Rate (IQD)</label>
                <input type="text" name="price_rate" id="price_rate"
                       value="{{ old('price_rate') }}"
                       class="w-full border-gray-300 rounded p-2 focus:ring focus:ring-blue-200"
                       placeholder="e.g. 1500">
                <p class="mt-1 text-sm text-gray-500">How many IQD = 1 USD (or your base currency)</p>
            </div>

            <button type="submit"
                    class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                Save
            </button>

            <a href="{{ route('price-exchange.index') }}"
               class="ml-3 px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400">
               Cancel
            </a>
        </form>
    </div>
</x-admin-layout>
