<x-admin-layout>
    <x-alerts />

    <form action="{{ route('promotions.update', $promotion->id) }}" method="POST" class="space-y-4">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-12 gap-4">

            <div class="col-span-6 px-4">
                <label class="block text-sm font-medium mb-2">Name</label>
                <input type="text" name="name" value="{{ old('name', $promotion->name) }}"
                    class="w-full rounded border px-3 py-2 focus:border-primary focus:ring-1 focus:ring-primary">
                @error('name') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="col-span-6 px-4">
                <label class="block text-sm font-medium mb-2">Type</label>
                <select name="type" class="w-full rounded border px-3 py-2 focus:border-primary focus:ring-1 focus:ring-primary">
                    @foreach (['free_shipping', 'percentage', 'fixed'] as $typeOption)
                        <option value="{{ $typeOption }}" {{ old('type', $promotion->type) === $typeOption ? 'selected' : '' }}>
                            {{ $typeOption }}
                        </option>
                    @endforeach
                </select>
                @error('type') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="col-span-6 px-4">
                <label class="block text-sm font-medium mb-2">Value (% or fixed amount)</label>
                <input type="number" name="value" value="{{ old('value', $promotion->value) }}"
                    class="w-full rounded border px-3 py-2 focus:border-primary focus:ring-1 focus:ring-primary">
                @error('value') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="col-span-6 px-4">
                <label class="block text-sm font-medium mb-2">Minimum Cart Amount</label>
                <input type="number" name="minimum_cart_amount" value="{{ old('minimum_cart_amount', $promotion->minimum_cart_amount) }}"
                    class="w-full rounded border px-3 py-2 focus:border-primary focus:ring-1 focus:ring-primary">
                @error('minimum_cart_amount') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="col-span-6 px-4">
                <label class="block text-sm font-medium mb-2">Vendor Absorption %</label>
                <input type="number" name="absorbed_by_vendor_percentage" value="{{ old('absorbed_by_vendor_percentage', $promotion->absorbed_by_vendor_percentage) }}"
                    class="w-full rounded border px-3 py-2 focus:border-primary focus:ring-1 focus:ring-primary">
                @error('absorbed_by_vendor_percentage') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="col-span-6 px-4">
                <label class="block text-sm font-medium mb-2">Start Date</label>
                <input type="date" name="start_date" value="{{ old('start_date', $promotion->start_date?->format('Y-m-d')) }}"
                    class="w-full rounded border px-3 py-2 focus:border-primary focus:ring-1 focus:ring-primary">
                @error('start_date') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="col-span-6 px-4">
                <label class="block text-sm font-medium mb-2">End Date</label>
                <input type="date" name="end_date" value="{{ old('end_date', $promotion->end_date?->format('Y-m-d')) }}"
                    class="w-full rounded border px-3 py-2 focus:border-primary focus:ring-1 focus:ring-primary">
                @error('end_date') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="col-span-6 px-4">
                <div class="flex items-center">
                    <input type="checkbox" name="is_active" value="1"
                        {{ old('is_active', $promotion->is_active) ? 'checked' : '' }}
                        class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
                    <label class="ml-2 block text-sm text-gray-900">Active</label>
                </div>
                @error('is_active') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

        </div>

        <div class="flex justify-end gap-2 px-4 mt-6">
            <button type="submit" class="bg-primary text-white px-4 py-2 rounded hover:bg-primary-dark transition">
                Submit
            </button>
            <a href="{{ route('promotions.index') }}"
                class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 transition">
                Cancel
            </a>
        </div>
    </form>

</x-admin-layout>
