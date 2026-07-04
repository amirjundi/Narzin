@props(['action', 'method' => 'POST', 'columns', 'routePrefix' => '', 'data' => null])


<x-alerts />

<form action="{{ $action }}" method="{{ $method === 'GET' ? 'GET' : 'POST' }}" class="space-y-4"
    enctype="multipart/form-data">
    @csrf
    @if ($method !== 'GET' && $method !== 'POST')
        @method($method)
    @endif



    <div class="grid grid-cols-12 gap-4">
        @foreach ($columns as $column)
            @php
                if (isset($column['value'])) {
                    if (isset($column['relation'])) {
                        $column['value'] = $data->{$column['relation']}->{$column['name']};
                    } else {
                        $column['value'] = $data->{$column['name']};
                    }
                }

                $spanClass = match ($column['width'] ?? 'full') {
                    'half' => 'col-span-6',
                    'third' => 'col-span-4',
                    'quarter' => 'col-span-3',
                    default => 'col-span-12',
                };
            @endphp

            <div class="{{ $spanClass }} px-4">
                @switch($column['type'])
                    @case('file')
                        <div class="flex items-center justify-center w-full">
                            <label
                                class="flex flex-col w-full h-32 border-4 border-dashed hover:bg-gray-100 hover:border-primary">
                                <div class="flex flex-col items-center justify-center pt-7">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="w-12 h-12 text-gray-400 group-hover:text-gray-600" viewBox="0 0 20 20"
                                        fill="currentColor">
                                        <path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" />
                                    </svg>
                                    <p class="pt-1 text-sm tracking-wider text-gray-400 group-hover:text-gray-600">
                                        {{ $column['label'] }}
                                    </p>
                                </div>
                                <input type="file" name="{{ $column['name'] }}" class="opacity-0" />
                            </label>
                        </div>
                        @if ($data && $data->{$column['name']})
                            @if (in_array(pathinfo($column['value'], PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png', 'gif']))
                                <button type="button" onclick="openModal('{{ asset('storage/' . $column['value']) }}')"
                                    class="text-primary hover:underline">
                                    Preview
                                </button>
                                <!-- Modal -->
                                <div id="previewModal" class="fixed z-10 inset-0 overflow-y-auto hidden">
                                    <div class="flex items-center justify-center min-h-screen">
                                        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
                                        <div
                                            class="bg-white rounded-lg overflow-hidden shadow-xl transform transition-all max-w-lg w-full">
                                            <div class="bg-white p-4">
                                                <img id="modalImage" src="" alt="Preview" class="w-full h-auto">
                                            </div>
                                            <div class="bg-gray-50 px-4 py-3 text-right">
                                                <button type="button" onclick="closeModal()"
                                                    class="bg-primary text-white px-4 py-2 rounded hover:bg-primary-dark transition">
                                                    Close
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <script>
                                    function openModal(imageSrc) {
                                        document.getElementById('modalImage').src = imageSrc;
                                        document.getElementById('previewModal').classList.remove('hidden');
                                    }

                                    function closeModal() {
                                        document.getElementById('previewModal').classList.add('hidden');
                                    }
                                </script>
                            @else
                                <a href="{{ asset('storage/' . $column['value']) }}" download
                                    class="text-primary hover:underline">
                                    Download
                                </a>
                            @endif
                        @endif
                    @break

                    @case('checkbox')
                        <div class="flex items-center">
                            <input type="checkbox" name="{{ $column['name'] }}" value="1"
                                @if (isset($column['value'])) {{ old($column['name'], $column['value']) ? 'checked' : '' }} @endif
                                class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
                            <label class="ml-2 block text-sm text-gray-900">{{ $column['label'] }}</label>
                        </div>
                    @break

                    @case('radio')
                        <label class="block text-sm font-medium mb-2">{{ $column['label'] }}</label>
                        @foreach ($column['options'] as $value => $label)
                            <div class="flex items-center mt-2">
                                <input type="radio" name="{{ $column['name'] }}" value="{{ $value }}"
                                    @if (isset($column['value'])) {{ old($column['name'], $column['value']) == $value ? 'checked' : '' }} @endif
                                    class="w-4 h-4 text-primary border-gray-300 focus:ring-primary">
                                <label class="ml-2 block text-sm text-gray-900">{{ $label }}</label>
                            </div>
                        @endforeach
                    @break

                    @case('date')
                        <label class="block text-sm font-medium mb-2">{{ $column['label'] }}</label>
                        <input type="date" name="{{ $column['name'] }}"
                            @if (isset($column['value'])) value="{{ old($column['name'], $column['value'] ?? '') }}" @endif
                            class="w-full rounded border px-3 py-2 focus:border-primary focus:ring-1 focus:ring-primary">
                    @break

                    @case('textarea')
                        <label class="block text-sm font-medium mb-2">{{ $column['label'] }}</label>
                        <textarea name="{{ $column['name'] }}" rows="4"
                            class="w-full rounded border px-3 py-2 focus:border-primary focus:ring-1 focus:ring-primary">
@if (isset($column['value']))
{{ old($column['name'], $column['value'] ?? '') }}
@endif
</textarea>
                    @break

                    @default
                        <label class="block text-sm font-medium mb-2">{{ $column['label'] }}</label>
                        @switch($column['type'])
                            @case('text')
                            @case('email')
                            @case('number')
                            @case('password')
                                <input type="{{ $column['type'] }}" @if (isset($column['disabled']) && $column['disabled']) disabled @endif
                                    name="{{ $column['name'] }}"
                                    @if (isset($column['value'])) value="{{ old($column['name'], $column['value'] ?? '') }}" @endif
                                    class="w-full rounded border px-3 py-2 focus:border-primary focus:ring-1 focus:ring-primary">
                            @break

                            @case('select')
                                <select name="{{ $column['name'] }}" @if (isset($column['disabled']) && $column['disabled']) disabled @endif
                                    class="w-full rounded border px-3 py-2 focus:border-primary focus:ring-1 focus:ring-primary">
                                    <option value="">leave empty?</option>
                                    @foreach ($column['options'] as $value => $label)
                                        <option value="{{ $label->id ?? $label }}"
                                            @if (isset($column['value'])) {{ old($column['name'], $column['value']) == (is_object($label) ? $label->id : $label) ? 'selected' : '' }} @endif>
                                            @if (isset($column['optionLabel']))
                                                {{ $label->{$column['optionLabel']} }}

                                                @else{{ $label }}
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                            @break
                        @endswitch
                    @endswitch

                    @error($column['name'])
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            @endforeach

        </div>
        {{ $slot ?? '' }}

        <div class="flex justify-end gap-3 px-4 mt-6">
            <x-admin.button-secondary href="{{ route($routePrefix . '.index') }}">
                Cancel
            </x-admin.button-secondary>
            <x-admin.button-primary type="submit">
                Submit
            </x-admin.button-primary>
        </div>
    </form>
