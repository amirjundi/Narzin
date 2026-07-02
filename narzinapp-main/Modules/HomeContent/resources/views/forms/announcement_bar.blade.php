@include('homecontent::partials.i18n-input', ['label' => 'Announcement text', 'name' => 'content[text]', 'values' => $c['text'] ?? []])
@include('homecontent::partials.link-picker', ['name' => 'content[link]', 'value' => $c['link'] ?? null])
<div class="flex gap-8">
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Background</label>
        <input type="color" name="content[bg_color]" value="{{ $c['bg_color'] ?? '#141923' }}" class="h-9 w-16">
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Text color</label>
        <input type="color" name="content[text_color]" value="{{ $c['text_color'] ?? '#C5A880' }}" class="h-9 w-16">
    </div>
</div>
