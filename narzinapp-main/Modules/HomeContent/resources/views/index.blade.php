<x-admin-layout>
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <h1 class="text-2xl font-semibold text-slate-800">Homepage Builder</h1>
        <div class="flex items-center gap-2">
            @if (config('homecontent.preview_token'))
                <a href="{{ rtrim(config('homecontent.storefront_url'), '/') }}/?preview=1&preview_token={{ config('homecontent.preview_token') }}"
                   target="_blank" rel="noopener"
                   class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-slate-700 rounded-lg text-sm">
                    Preview homepage
                </a>
            @endif
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open"
                        class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white rounded-lg text-sm">
                    + Add block
                </button>
                <div x-show="open" @click.outside="open = false" x-cloak
                     class="absolute right-0 mt-2 w-72 bg-white border border-gray-200 rounded-lg shadow-lg z-20 py-1">
                    @php
                        // FontAwesome is already loaded by the admin layout; icon per type so
                        // non-technical admins recognize blocks visually (spec §5).
                        $typeIcons = [
                            'announcement_bar' => 'fa-bullhorn', 'popup' => 'fa-window-restore',
                            'hero_slider' => 'fa-images', 'category_grid' => 'fa-circle-dot',
                            'product_rail' => 'fa-grip-lines', 'countdown_banner' => 'fa-stopwatch',
                            'info_strip' => 'fa-truck-fast', 'promo_tiles' => 'fa-table-cells-large',
                        ];
                    @endphp
                    @foreach (\Modules\HomeContent\Models\HomeBlock::TYPES as $blockType)
                        <a href="{{ route('home-blocks.create', ['type' => $blockType]) }}"
                           class="flex items-center gap-3 px-4 py-2 text-sm text-slate-700 hover:bg-gray-50 capitalize">
                            <i class="fa-solid {{ $typeIcons[$blockType] }} w-4 text-slate-400"></i>
                            {{ str_replace('_', ' ', $blockType) }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    @if ($blocks->isEmpty())
        <p class="text-gray-500">No blocks yet — add your first block to start composing the homepage.</p>
    @endif

    <ul id="blocks-list" class="space-y-2">
        @foreach ($blocks as $block)
            <li data-id="{{ $block->id }}"
                class="bg-white border border-gray-200 rounded-lg px-4 py-3 flex items-center gap-3">
                <span class="drag-handle cursor-grab text-gray-400 text-lg leading-none select-none">&#8801;</span>
                <span class="font-medium text-slate-800 flex-1 truncate">{{ $block->name }}</span>
                <span class="text-xs px-2 py-1 rounded bg-gray-100 text-slate-600 capitalize">{{ str_replace('_', ' ', $block->type) }}</span>
                <span class="text-xs px-2 py-1 rounded bg-gray-100 text-slate-600">{{ $block->platform }}</span>
                <span class="text-xs whitespace-nowrap {{ $block->ends_at && $block->ends_at->isPast() ? 'text-red-600 font-semibold' : 'text-gray-500' }}">
                    @if ($block->ends_at && $block->ends_at->isPast())
                        expired
                    @elseif ($block->starts_at && $block->starts_at->isFuture())
                        starts {{ $block->starts_at->format('M j') }}
                    @elseif ($block->ends_at)
                        ends {{ $block->ends_at->format('M j') }}
                    @endif
                </span>
                <button type="button" data-id="{{ $block->id }}"
                        class="toggle-btn text-xs px-3 py-1 rounded-full {{ $block->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-500' }}">
                    {{ $block->is_active ? 'ON' : 'OFF' }}
                </button>
                <a href="{{ route('home-blocks.edit', $block) }}" class="text-blue-600 hover:underline text-sm">Edit</a>
                <form method="POST" action="{{ route('home-blocks.destroy', $block) }}"
                      onsubmit="return confirm('Delete this block?')">
                    @csrf
                    @method('DELETE')
                    <button class="text-red-600 hover:underline text-sm">Delete</button>
                </form>
            </li>
        @endforeach
    </ul>

    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
    <script>
        const csrfToken = document.querySelector('meta[name=csrf-token]').content;

        new Sortable(document.getElementById('blocks-list'), {
            handle: '.drag-handle',
            animation: 150,
            onEnd() {
                const ids = [...document.querySelectorAll('#blocks-list [data-id]')].map(el => Number(el.dataset.id));
                fetch('{{ route('home-blocks.reorder') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({ ids }),
                });
            },
        });

        document.querySelectorAll('.toggle-btn').forEach((btn) => {
            btn.addEventListener('click', async () => {
                const res = await fetch(`/home-blocks/${btn.dataset.id}/toggle`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken },
                });
                const json = await res.json();
                btn.textContent = json.is_active ? 'ON' : 'OFF';
                btn.className = 'toggle-btn text-xs px-3 py-1 rounded-full ' +
                    (json.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-500');
            });
        });
    </script>
</x-admin-layout>
