@php $slides = $c['slides'] ?? [[]]; @endphp
<p class="text-sm text-gray-500">Recommended sizes — web ≈ 1600×530 (3:1), app ≈ 800×400 (2:1). Each slide needs at least one image.</p>
<div id="slides-rows" class="space-y-4">
    @foreach ($slides as $i => $slide)
        <div class="border border-gray-200 rounded-lg p-4 space-y-3">
            <p class="text-xs font-semibold text-gray-400">Slide {{ $i + 1 }}</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Web image</label>
                    @if (!empty($slide['image_web']))
                        <img src="{{ \Modules\HomeContent\Support\ImageUrl::make($slide['image_web']) }}" class="h-16 rounded mb-2" alt="">
                        <input type="hidden" name="content[slides][{{ $i }}][image_web]" value="{{ $slide['image_web'] }}">
                    @endif
                    <input type="file" name="slide_images_web[{{ $i }}]" accept="image/*" class="text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">App image</label>
                    @if (!empty($slide['image_app']))
                        <img src="{{ \Modules\HomeContent\Support\ImageUrl::make($slide['image_app']) }}" class="h-16 rounded mb-2" alt="">
                        <input type="hidden" name="content[slides][{{ $i }}][image_app]" value="{{ $slide['image_app'] }}">
                    @endif
                    <input type="file" name="slide_images_app[{{ $i }}]" accept="image/*" class="text-sm">
                </div>
            </div>
            @include('homecontent::partials.i18n-input', ['label' => 'Title (optional)', 'name' => "content[slides][$i][title]", 'values' => $slide['title'] ?? []])
            @include('homecontent::partials.i18n-input', ['label' => 'Subtitle (optional)', 'name' => "content[slides][$i][subtitle]", 'values' => $slide['subtitle'] ?? []])
            @include('homecontent::partials.link-picker', ['name' => "content[slides][$i][link]", 'value' => $slide['link'] ?? null])
        </div>
    @endforeach
</div>
<template data-repeater-for="slides">
    <div class="border border-gray-200 rounded-lg p-4 space-y-3">
        <p class="text-xs font-semibold text-gray-400">New slide</p>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Web image</label>
                <input type="file" name="slide_images_web[__IDX__]" accept="image/*" class="text-sm">
                <input type="hidden" name="content[slides][__IDX__][image_web]" value="">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">App image</label>
                <input type="file" name="slide_images_app[__IDX__]" accept="image/*" class="text-sm">
            </div>
        </div>
        @include('homecontent::partials.i18n-input', ['label' => 'Title (optional)', 'name' => 'content[slides][__IDX__][title]', 'values' => []])
        @include('homecontent::partials.i18n-input', ['label' => 'Subtitle (optional)', 'name' => 'content[slides][__IDX__][subtitle]', 'values' => []])
        @include('homecontent::partials.link-picker', ['name' => 'content[slides][__IDX__][link]', 'value' => null])
    </div>
</template>
<button type="button" onclick="addRepeaterRow('slides')" class="text-sm text-blue-600 hover:underline">+ Add slide (max 8)</button>

<script>
    // Spec §5: warn when an uploaded image is far from the recommended aspect ratio.
    // Delegated so it also covers rows added by the repeater.
    document.addEventListener('change', (event) => {
        const input = event.target;
        if (input.type !== 'file' || !input.files?.[0]) return;
        const isWeb = input.name.startsWith('slide_images_web');
        const isApp = input.name.startsWith('slide_images_app');
        if (!isWeb && !isApp) return;

        const expected = isWeb ? 3 : 2; // web ≈ 3:1, app ≈ 2:1
        const img = new Image();
        img.onload = () => {
            const ratio = img.width / img.height;
            input.parentElement.querySelector('.ratio-warning')?.remove();
            if (Math.abs(ratio - expected) / expected > 0.25) {
                input.insertAdjacentHTML('afterend',
                    `<p class="ratio-warning text-xs text-amber-600 mt-1">⚠ This image is ${img.width}×${img.height} (${ratio.toFixed(1)}:1); recommended is ${expected}:1 — it may look cropped or stretched.</p>`);
            }
            URL.revokeObjectURL(img.src);
        };
        img.src = URL.createObjectURL(input.files[0]);
    });
</script>
