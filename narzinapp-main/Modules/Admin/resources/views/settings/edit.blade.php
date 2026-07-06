<x-admin-layout>
    <div class="max-w-lg mx-auto mt-8 bg-white shadow p-6 rounded">
        <h1 class="text-2xl font-bold mb-6">Site Settings</h1>

        @if (session('success'))
            <div class="mb-4 bg-green-100 text-green-700 p-3 rounded">{{ session('success') }}</div>
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

        <form action="{{ route('settings.update') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block mb-1 font-semibold" for="whatsapp_number">WhatsApp Number</label>
                <input type="text" name="whatsapp_number" id="whatsapp_number"
                       value="{{ old('whatsapp_number', $whatsapp_number) }}"
                       class="w-full border-gray-300 rounded p-2 focus:ring focus:ring-blue-200"
                       placeholder="e.g. +964 770 123 4567">
                <p class="mt-1 text-sm text-gray-500">Shown behind the storefront support icon. Leave empty to hide it.</p>
            </div>

            <div class="mb-4">
                <label class="block mb-1 font-semibold" for="support_hours">Support Hours (optional)</label>
                <input type="text" name="support_hours" id="support_hours"
                       value="{{ old('support_hours', $support_hours) }}"
                       class="w-full border-gray-300 rounded p-2 focus:ring focus:ring-blue-200"
                       placeholder="e.g. Sun–Thu, 9:00–18:00">
            </div>

            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Save</button>
        </form>
    </div>
</x-admin-layout>
