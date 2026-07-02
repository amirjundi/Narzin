@include('homecontent::partials.i18n-input', ['label' => 'Rail title', 'name' => 'content[title]', 'values' => $c['title'] ?? []])

<div x-data="{ rule: '{{ $c['rule'] ?? 'newest' }}' }" class="space-y-4">
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-2">Products come from</label>
        <div class="flex flex-wrap gap-4 text-sm">
            @foreach (['newest' => 'Newest products', 'best_sellers' => 'Best sellers', 'category' => 'A category', 'manual' => 'Hand-picked'] as $value => $label)
                <label class="inline-flex items-center gap-1.5">
                    <input type="radio" name="content[rule]" value="{{ $value }}" x-model="rule"> {{ $label }}
                </label>
            @endforeach
        </div>
    </div>

    <div x-show="rule === 'category'"
         x-data="{ q: '', results: [], picked: '{{ $c['category_id'] ?? '' }}', label: '', async search() {
            if (this.q.length < 2) { this.results = []; return; }
            const res = await fetch('{{ route('home-blocks.search.categories') }}?q=' + encodeURIComponent(this.q));
            this.results = (await res.json()).data;
         } }">
        <input type="hidden" name="content[category_id]" :value="picked">
        <input type="text" x-model="q" @input.debounce.300ms="search()" placeholder="Search categories…"
               class="w-full border-gray-300 rounded-lg">
        <div x-show="results.length" class="border border-gray-200 rounded-lg bg-white mt-1">
            <template x-for="result in results" :key="result.id">
                <button type="button" class="block w-full text-start px-3 py-1.5 text-sm hover:bg-gray-50"
                        @click="picked = result.id; label = result.name_german || result.name_arabic; results = []; q = '';"
                        x-text="(result.name_german || result.name_arabic) + ' (#' + result.id + ')'"></button>
            </template>
        </div>
        <p class="text-xs text-gray-500 mt-1" x-show="label" x-text="'Category: ' + label"></p>
    </div>

    <div x-show="rule === 'manual'">
        @php
            $pickedIds = array_map('intval', $c['product_ids'] ?? []);
            $pickedProducts = collect();
            if (!empty($pickedIds)) {
                $pickedProducts = \Modules\ProductManagement\Models\Product::whereIn('id', $pickedIds)->get()
                    ->sortBy(fn ($p) => array_search($p->id, $pickedIds))->values();
            }
        @endphp
        <ul id="picked-products" class="space-y-1">
            @foreach ($pickedProducts as $product)
                <li class="flex items-center gap-2 bg-gray-50 border border-gray-200 rounded px-3 py-1.5 text-sm" data-id="{{ $product->id }}">
                    <span class="drag-handle cursor-grab text-gray-400 select-none">&#8801;</span>
                    <span class="flex-1">{{ $product->name_german ?: $product->name_arabic }}</span>
                    <input type="hidden" name="content[product_ids][]" value="{{ $product->id }}">
                    <button type="button" onclick="this.closest('li').remove()" class="text-red-500">&times;</button>
                </li>
            @endforeach
        </ul>
        <div x-data="{ q: '', results: [], async search() {
                if (this.q.length < 2) { this.results = []; return; }
                const res = await fetch('{{ route('home-blocks.search.products') }}?q=' + encodeURIComponent(this.q));
                this.results = (await res.json()).data;
            } }">
            <input type="text" x-model="q" @input.debounce.300ms="search()" placeholder="Search products…"
                   class="w-full border-gray-300 rounded-lg mt-2">
            <div x-show="results.length" class="border border-gray-200 rounded-lg bg-white mt-1">
                <template x-for="result in results" :key="result.id">
                    <button type="button" class="block w-full text-start px-3 py-1.5 text-sm hover:bg-gray-50"
                            @click="addPickedProduct(result); results = []; q = '';"
                            x-text="(result.name_german || result.name_arabic) + ' (#' + result.id + ')'"></button>
                </template>
            </div>
        </div>
    </div>

    <div class="flex items-end gap-4">
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Max products</label>
            <input type="number" name="content[limit]" min="2" max="24" value="{{ $c['limit'] ?? 12 }}"
                   class="w-24 border-gray-300 rounded-lg">
        </div>
        <button type="button" onclick="railPreview()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg text-sm">
            Preview products
        </button>
    </div>
    <div id="rail-preview" class="flex gap-3 flex-wrap"></div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
    const pickedProductsList = document.getElementById('picked-products');
    if (pickedProductsList) new Sortable(pickedProductsList, { handle: '.drag-handle', animation: 150 });

    const escapeHtml = s => String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));

    function addPickedProduct(product) {
        if (document.querySelector(`#picked-products [data-id="${product.id}"]`)) return;
        const id = Number(product.id);
        pickedProductsList.insertAdjacentHTML('beforeend', `
            <li class="flex items-center gap-2 bg-gray-50 border border-gray-200 rounded px-3 py-1.5 text-sm" data-id="${id}">
                <span class="drag-handle cursor-grab text-gray-400 select-none">&#8801;</span>
                <span class="flex-1">${escapeHtml(product.name_german || product.name_arabic)}</span>
                <input type="hidden" name="content[product_ids][]" value="${id}">
                <button type="button" onclick="this.closest('li').remove()" class="text-red-500">&times;</button>
            </li>`);
    }

    async function railPreview() {
        const form = document.querySelector('form');
        const body = {
            rule: form.querySelector('input[name="content[rule]"]:checked').value,
            category_id: form.querySelector('input[name="content[category_id]"]')?.value || null,
            product_ids: [...form.querySelectorAll('input[name="content[product_ids][]"]')].map(el => Number(el.value)),
        };
        const res = await fetch('{{ route('home-blocks.rail-preview') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
            },
            body: JSON.stringify(body),
        });
        const json = await res.json();
        document.getElementById('rail-preview').innerHTML = json.data.length
            ? json.data.map(p => `
                <div class="w-28 text-xs text-center">
                    <div class="h-28 bg-gray-100 rounded overflow-hidden mb-1">
                        ${p.image ? `<img src="${escapeHtml(p.image)}" class="w-full h-full object-cover" alt="">` : ''}
                    </div>
                    <div class="truncate">${escapeHtml(p.name_german || p.name_arabic)}</div>
                    <div class="font-semibold">€${escapeHtml(p.min_price)}</div>
                </div>`).join('')
            : '<p class="text-sm text-red-600">No products match this rule yet — the rail would be hidden on the homepage.</p>';
    }
</script>
