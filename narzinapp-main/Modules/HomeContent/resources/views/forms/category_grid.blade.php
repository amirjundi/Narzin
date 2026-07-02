@php
    $selectedIds = array_map('intval', $c['category_ids'] ?? []);
    $selected = collect();
    if (!empty($selectedIds)) {
        $selected = \Modules\ProductManagement\Models\Category::whereIn('id', $selectedIds)->get()
            ->sortBy(fn ($cat) => array_search($cat->id, $selectedIds))->values();
    }
@endphp
<p class="text-sm text-gray-500">Pick at least 2 categories; drag to set their order on the homepage.</p>
<ul id="picked-categories" class="space-y-1">
    @foreach ($selected as $category)
        <li class="flex items-center gap-2 bg-gray-50 border border-gray-200 rounded px-3 py-1.5 text-sm" data-id="{{ $category->id }}">
            <span class="drag-handle cursor-grab text-gray-400 select-none">&#8801;</span>
            <span class="flex-1">{{ $category->name_german ?: $category->name_arabic }}</span>
            <input type="hidden" name="content[category_ids][]" value="{{ $category->id }}">
            <button type="button" onclick="this.closest('li').remove()" class="text-red-500">&times;</button>
        </li>
    @endforeach
</ul>
<div x-data="{ q: '', results: [], async search() {
        if (this.q.length < 2) { this.results = []; return; }
        const res = await fetch('{{ route('home-blocks.search.categories') }}?q=' + encodeURIComponent(this.q));
        this.results = (await res.json()).data;
    } }">
    <input type="text" x-model="q" @input.debounce.300ms="search()" placeholder="Search categories…"
           class="w-full border-gray-300 rounded-lg mt-2">
    <div x-show="results.length" class="border border-gray-200 rounded-lg bg-white mt-1">
        <template x-for="result in results" :key="result.id">
            <button type="button" class="block w-full text-start px-3 py-1.5 text-sm hover:bg-gray-50"
                    @click="addPickedCategory(result); results = []; q = '';"
                    x-text="(result.name_german || result.name_arabic) + ' (#' + result.id + ')'"></button>
        </template>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
    new Sortable(document.getElementById('picked-categories'), { handle: '.drag-handle', animation: 150 });

    const escapeHtml = s => String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));

    function addPickedCategory(category) {
        if (document.querySelector(`#picked-categories [data-id="${category.id}"]`)) return;
        const id = Number(category.id);
        document.getElementById('picked-categories').insertAdjacentHTML('beforeend', `
            <li class="flex items-center gap-2 bg-gray-50 border border-gray-200 rounded px-3 py-1.5 text-sm" data-id="${id}">
                <span class="drag-handle cursor-grab text-gray-400 select-none">&#8801;</span>
                <span class="flex-1">${escapeHtml(category.name_german || category.name_arabic)}</span>
                <input type="hidden" name="content[category_ids][]" value="${id}">
                <button type="button" onclick="this.closest('li').remove()" class="text-red-500">&times;</button>
            </li>`);
    }
</script>
