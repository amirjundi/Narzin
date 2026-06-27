<x-admin-layout>
    <div class="max-w-lg mx-auto mt-8 bg-white shadow p-6 rounded">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Payout Default Settings</h1>
            <a href="{{ route('vendor-payouts.index') }}"
               class="px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400">
               Back
            </a>
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

        <form action="{{ route('vendor-payouts.settings.save') }}" method="POST">
            @csrf
            <div class="mb-5">
                <label class="block mb-1 font-semibold" for="default_commission_percentage">
                    Default Commission %
                </label>
                <input type="number" step="0.01" name="default_commission_percentage"
                       id="default_commission_percentage"
                       value="{{ old('default_commission_percentage', $settings->default_commission_percentage) }}"
                       class="w-full border border-gray-300 rounded p-2 focus:ring focus:ring-blue-200"
                       min="0" max="100" required>
                <p class="mt-1 text-sm text-gray-500">
                    The default commission percentage deducted from vendor earnings. Can be overridden per vendor.
                </p>
            </div>

            <div class="mb-6">
                <label class="block mb-1 font-semibold" for="default_discount_absorption_percentage">
                    Default Discount Absorption %
                </label>
                <input type="number" step="0.01" name="default_discount_absorption_percentage"
                       id="default_discount_absorption_percentage"
                       value="{{ old('default_discount_absorption_percentage', $settings->default_discount_absorption_percentage) }}"
                       class="w-full border border-gray-300 rounded p-2 focus:ring focus:ring-blue-200"
                       min="0" max="100" required>
                <p class="mt-1 text-sm text-gray-500">
                    The percentage of coupon discounts absorbed by the vendor. Can be overridden per vendor.
                </p>
            </div>

            <button type="submit"
                    class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                Save Defaults
            </button>
        </form>
    </div>
</x-admin-layout>
