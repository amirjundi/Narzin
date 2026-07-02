@php $tiles = $c['tiles'] ?? [[]]; @endphp
<p class="text-sm text-gray-500">1–3 image tiles shown side by side. Each tile needs an image.</p>
<div id="tiles-rows" class="space-y-4">
    @foreach ($tiles as $i => $tile)
        <div class="repeater-row border border-gray-200 rounded-lg p-4 space-y-3">
            <div class="flex items-start justify-between">
                <p class="text-xs font-semibold text-gray-400">Tile {{ $i + 1 }}</p>
                <button type="button" onclick="this.closest('.repeater-row').remove()" class="text-red-500 text-sm">&times; Remove</button>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Tile image</label>
                @if (!empty($tile['image']))
                    <img src="{{ \Modules\HomeContent\Support\ImageUrl::make($tile['image']) }}" class="h-16 rounded mb-2" alt="">
                    <input type="hidden" name="content[tiles][{{ $i }}][image]" value="{{ $tile['image'] }}">
                @endif
                <input type="file" name="tile_images[{{ $i }}]" accept="image/*" class="text-sm">
            </div>
            @include('homecontent::partials.i18n-input', ['label' => 'Label (optional)', 'name' => "content[tiles][$i][label]", 'values' => $tile['label'] ?? []])
            @include('homecontent::partials.link-picker', ['name' => "content[tiles][$i][link]", 'value' => $tile['link'] ?? null])
        </div>
    @endforeach
</div>
<template data-repeater-for="tiles">
    <div class="repeater-row border border-gray-200 rounded-lg p-4 space-y-3">
        <div class="flex items-start justify-between">
            <p class="text-xs font-semibold text-gray-400">New tile</p>
            <button type="button" onclick="this.closest('.repeater-row').remove()" class="text-red-500 text-sm">&times; Remove</button>
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Tile image</label>
            <input type="file" name="tile_images[__IDX__]" accept="image/*" class="text-sm">
            <input type="hidden" name="content[tiles][__IDX__][image]" value="">
        </div>
        @include('homecontent::partials.i18n-input', ['label' => 'Label (optional)', 'name' => 'content[tiles][__IDX__][label]', 'values' => []])
        @include('homecontent::partials.link-picker', ['name' => 'content[tiles][__IDX__][link]', 'value' => null])
    </div>
</template>
<button type="button" onclick="addRepeaterRow('tiles')" class="text-sm text-blue-600 hover:underline">+ Add tile (max 3)</button>
