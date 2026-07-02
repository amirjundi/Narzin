@php $initialLink = $value ?? ['type' => 'none', 'value' => null]; @endphp
<div x-data='linkPicker(@json($initialLink))' class="space-y-2">
    <label class="block text-sm font-medium text-slate-700">Link</label>
    <select x-model="type" class="border-gray-300 rounded-lg">
        <option value="none">No link</option>
        <option value="category">Category</option>
        <option value="product">Product</option>
        <option value="url">Custom URL</option>
    </select>
    <input type="hidden" name="{{ $name }}[type]" :value="type">

    <template x-if="type === 'url'">
        <input type="text" name="{{ $name }}[value]" x-model="value"
               placeholder="https://..." class="w-full border-gray-300 rounded-lg">
    </template>

    <template x-if="type === 'category' || type === 'product'">
        <div>
            <input type="hidden" name="{{ $name }}[value]" :value="value">
            <input type="text" x-model="q" @input.debounce.300ms="search()"
                   :placeholder="'Search ' + type + 's…'" class="w-full border-gray-300 rounded-lg">
            <div x-show="results.length" class="border border-gray-200 rounded-lg bg-white mt-1 max-h-48 overflow-y-auto">
                <template x-for="result in results" :key="result.id">
                    <button type="button" @click="pick(result)"
                            class="block w-full text-start px-3 py-1.5 text-sm hover:bg-gray-50"
                            x-text="(result.name_german || result.name_arabic) + ' (#' + result.id + ')'"></button>
                </template>
            </div>
            <p class="text-xs text-gray-500 mt-1" x-show="label" x-text="'Selected: ' + label"></p>
        </div>
    </template>
</div>
