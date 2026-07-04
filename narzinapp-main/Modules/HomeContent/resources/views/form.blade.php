<x-admin-layout>
    @php $c = old('content', $block->content ?? []); @endphp

    <h1 class="text-2xl font-semibold text-slate-800 mb-6 capitalize">
        {{ $block->exists ? 'Edit' : 'New' }} block — {{ str_replace('_', ' ', $type) }}
    </h1>

    @if ($errors->any())
        <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
            <ul class="list-disc ps-4">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" enctype="multipart/form-data"
          action="{{ $block->exists ? route('home-blocks.update', $block) : route('home-blocks.store') }}"
          x-data="{ lang: 'ar' }" class="space-y-6 max-w-3xl">
        @csrf
        @if ($block->exists)
            @method('PUT')
        @endif
        <input type="hidden" name="type" value="{{ $type }}">

        @include('homecontent::partials.shared')

        <div class="bg-white border border-gray-200 rounded-lg p-5 space-y-5">
            @include('homecontent::forms.' . $type)
        </div>

        <div class="flex items-center gap-3 pt-2">
            <x-admin.button-primary type="submit">Save Block</x-admin.button-primary>
            <x-admin.button-secondary href="{{ route('home-blocks.index') }}">Cancel</x-admin.button-secondary>
        </div>
    </form>

    <script>
        function linkPicker(initial) {
            return {
                type: (initial && initial.type) || 'none',
                value: (initial && initial.value) || null,
                label: (initial && initial.value) ? ('#' + initial.value) : '',
                q: '',
                results: [],
                async search() {
                    if (this.q.length < 2) { this.results = []; return; }
                    const base = this.type === 'product'
                        ? '{{ route('home-blocks.search.products') }}'
                        : '{{ route('home-blocks.search.categories') }}';
                    const res = await fetch(base + '?q=' + encodeURIComponent(this.q));
                    this.results = (await res.json()).data;
                },
                pick(item) {
                    this.value = item.id;
                    this.label = item.name_german || item.name_arabic;
                    this.results = [];
                    this.q = '';
                },
            };
        }

        // Generic repeater: clones <template data-repeater-for="X"> into #X-rows replacing __IDX__.
        function addRepeaterRow(key) {
            const template = document.querySelector(`template[data-repeater-for="${key}"]`);
            const container = document.getElementById(`${key}-rows`);
            if (container.dataset.nextIdx === undefined) {
                container.dataset.nextIdx = String(container.children.length);
            }
            const index = Number(container.dataset.nextIdx);
            container.dataset.nextIdx = String(index + 1);
            container.insertAdjacentHTML('beforeend', template.innerHTML.replaceAll('__IDX__', String(index)));
        }

        // Eagerly initialize nextIdx at page load to prevent index collisions
        document.querySelectorAll('template[data-repeater-for]').forEach((t) => {
            const c = document.getElementById(`${t.dataset.repeaterFor}-rows`);
            if (c) c.dataset.nextIdx = String(c.children.length);
        });
    </script>
</x-admin-layout>
