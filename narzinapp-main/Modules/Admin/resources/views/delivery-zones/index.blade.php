<x-admin-layout>
    <div class="p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-semibold text-gray-800">Delivery Zones</h2>
            <a href="{{ route('delivery-zones.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">Add New Zone</a>
        </div>

        @if(session('success'))
            <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="px-6 py-4 text-sm font-medium text-gray-500">ID</th>
                        <th class="px-6 py-4 text-sm font-medium text-gray-500">Zone Name</th>
                        <th class="px-6 py-4 text-sm font-medium text-gray-500">Status</th>
                        <th class="px-6 py-4 text-sm font-medium text-gray-500">Methods Configured</th>
                        <th class="px-6 py-4 text-sm font-medium text-gray-500 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($zones as $zone)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $zone->id }}</td>
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-900">{{ $zone->name_english }}</div>
                            </td>
                            <td class="px-6 py-4">
                                @if($zone->is_active)
                                    <span class="inline-flex px-2 py-1 text-xs font-medium bg-green-100 text-green-700 rounded-full">Active</span>
                                @else
                                    <span class="inline-flex px-2 py-1 text-xs font-medium bg-red-100 text-red-700 rounded-full">Inactive</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ $zone->deliveryMethods->count() }} methods
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('delivery-zones.show', $zone->id) }}" class="text-indigo-600 hover:text-indigo-900 mx-2">Manage Methods</a>
                                <a href="{{ route('delivery-zones.edit', $zone->id) }}" class="text-blue-600 hover:text-blue-900 mx-2">Edit</a>
                                <form action="{{ route('delivery-zones.destroy', $zone->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this zone?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                No delivery zones found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $zones->links() }}
        </div>
    </div>
</x-admin-layout>
