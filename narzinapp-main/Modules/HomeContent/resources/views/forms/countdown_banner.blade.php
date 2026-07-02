@include('homecontent::partials.i18n-input', ['label' => 'Banner text', 'name' => 'content[text]', 'values' => $c['text'] ?? []])
<div>
    <label class="block text-sm font-medium text-slate-700 mb-1">Countdown ends at</label>
    <input type="datetime-local" name="content[ends_at_display]" required class="border-gray-300 rounded-lg"
           value="{{ isset($c['ends_at_display']) ? \Carbon\Carbon::parse($c['ends_at_display'])->format('Y-m-d\TH:i') : '' }}">
</div>
@include('homecontent::partials.link-picker', ['name' => 'content[link]', 'value' => $c['link'] ?? null])
<div class="flex gap-8">
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Background</label>
        <input type="color" name="content[bg_color]" value="{{ $c['bg_color'] ?? '#141923' }}" class="h-9 w-16">
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Text color</label>
        <input type="color" name="content[text_color]" value="{{ $c['text_color'] ?? '#D4AF37' }}" class="h-9 w-16">
    </div>
</div>
<div>
    <label class="block text-sm font-medium text-slate-700 mb-1">Background image (optional)</label>
    @if (!empty($c['image']))
        <img src="{{ \Modules\HomeContent\Support\ImageUrl::make($c['image']) }}" class="h-16 rounded mb-2" alt="">
        <input type="hidden" name="content[image]" value="{{ $c['image'] }}">
    @endif
    <input type="file" name="countdown_image" accept="image/*" class="text-sm">
</div>
