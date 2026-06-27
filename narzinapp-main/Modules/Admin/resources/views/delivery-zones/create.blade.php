<x-admin-layout>
    <div class="p-6 max-w-4xl mx-auto">
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-semibold text-gray-800">Add New Delivery Zone</h2>
                <p class="text-sm text-gray-500 mt-1">Create a new country or region for shipping.</p>
            </div>
            <a href="{{ route('delivery-zones.index') }}" class="text-indigo-600 hover:text-indigo-900">&larr; Back to Zones</a>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <form action="{{ route('delivery-zones.store') }}" method="POST">
                @csrf
                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Zone / Country Name (English)</label>
                        <input type="text" name="name_english" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" placeholder="e.g. Germany" required value="{{ old('name_english') }}">
                        @error('name_english')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Zone / Country Name (German)</label>
                        <input type="text" name="name_german" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" placeholder="e.g. Deutschland" required value="{{ old('name_german') }}">
                        @error('name_german')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Zone / Country Name (Arabic)</label>
                        <input type="text" name="name_arabic" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" placeholder="e.g. ألمانيا" required value="{{ old('name_arabic') }}">
                        @error('name_arabic')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" name="is_active" id="is_active" class="h-4 w-4 text-indigo-600 border-gray-300 rounded" value="1" checked>
                        <label for="is_active" class="ml-2 block text-sm text-gray-900">
                            Active (Available for customers during checkout)
                        </label>
                    </div>

                    <div class="pt-4 flex justify-end">
                        <button type="submit" class="px-6 py-2 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700">Save Zone</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
