@php $items = $c['items'] ?? [['icon' => 'truck'], ['icon' => 'shield']]; @endphp
<p class="text-sm text-gray-500">2–4 small items, e.g. "Free shipping over €49".</p>
<div id="info-items-rows" class="space-y-4">
    @foreach ($items as $i => $item)
        <div class="repeater-row border border-gray-200 rounded-lg p-4 space-y-3">
            <div class="flex items-start justify-between">
                <p class="text-xs font-semibold text-gray-400">Item {{ $i + 1 }}</p>
                <button type="button" onclick="this.closest('.repeater-row').remove()" class="text-red-500 text-sm">&times; Remove</button>
            </div>
            <div class="flex items-center gap-3">
                <label class="text-sm font-medium text-slate-700">Icon</label>
                <select name="content[items][{{ $i }}][icon]" class="border-gray-300 rounded-lg text-sm">
                    @foreach (\Modules\HomeContent\Support\BlockContentRules::ICONS as $icon)
                        <option value="{{ $icon }}" @selected(($item['icon'] ?? '') === $icon)>{{ $icon }}</option>
                    @endforeach
                </select>
            </div>
            @include('homecontent::partials.i18n-input', ['label' => 'Item text', 'name' => "content[items][$i][text]", 'values' => $item['text'] ?? []])
            @include('homecontent::partials.link-picker', ['name' => "content[items][$i][link]", 'value' => $item['link'] ?? null])
        </div>
    @endforeach
</div>
<template data-repeater-for="info-items">
    <div class="repeater-row border border-gray-200 rounded-lg p-4 space-y-3">
        <div class="flex items-start justify-between">
            <p class="text-xs font-semibold text-gray-400">New item</p>
            <button type="button" onclick="this.closest('.repeater-row').remove()" class="text-red-500 text-sm">&times; Remove</button>
        </div>
        <div class="flex items-center gap-3">
            <label class="text-sm font-medium text-slate-700">Icon</label>
            <select name="content[items][__IDX__][icon]" class="border-gray-300 rounded-lg text-sm">
                @foreach (\Modules\HomeContent\Support\BlockContentRules::ICONS as $icon)
                    <option value="{{ $icon }}">{{ $icon }}</option>
                @endforeach
            </select>
        </div>
        @include('homecontent::partials.i18n-input', ['label' => 'Item text', 'name' => 'content[items][__IDX__][text]', 'values' => []])
        @include('homecontent::partials.link-picker', ['name' => 'content[items][__IDX__][link]', 'value' => null])
    </div>
</template>
<button type="button" onclick="addRepeaterRow('info-items')" class="text-sm text-blue-600 hover:underline">+ Add item (max 4)</button>
