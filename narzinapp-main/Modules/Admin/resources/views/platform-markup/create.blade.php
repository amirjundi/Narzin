<x-admin-layout>
    <div class="max-w-lg mx-auto mt-8 bg-white shadow p-6 rounded">
        <h1 class="text-2xl font-bold mb-6">Set New Global Markup</h1>

        @if ($errors->any())
            <div class="mb-4 bg-red-100 text-red-700 p-3 rounded">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('platform-markup.store') }}" method="POST">
            @csrf
            <div class="mb-6">
                <label class="block mb-1 font-semibold" for="percentage">Global Markup %</label>
                <input type="text" name="percentage" id="percentage"
                       value="{{ old('percentage', '0') }}"
                       class="w-full border-gray-300 rounded p-2 focus:ring focus:ring-blue-200"
                       placeholder="e.g. 10">
                <p class="mt-1 text-sm text-gray-500">
                    Platform markup added to all vendor prices. 
                    <span class="font-medium text-gray-700">Not visible to customers or vendors.</span>
                    Individual vendors can override this in their profile.
                </p>
            </div>

            <button type="submit"
                    class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                Save
            </button>

            <a href="{{ route('platform-markup.index') }}"
               class="ml-3 px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400">
               Cancel
            </a>
        </form>
    </div>
</x-admin-layout>
