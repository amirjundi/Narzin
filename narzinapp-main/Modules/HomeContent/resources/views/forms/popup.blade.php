<div>
    <label class="block text-sm font-medium text-slate-700 mb-1">Popup image (optional)</label>
    @if (!empty($c['image']))
        <img src="{{ \Modules\HomeContent\Support\ImageUrl::make($c['image']) }}" class="h-24 rounded mb-2" alt="">
        <input type="hidden" name="content[image]" value="{{ $c['image'] }}">
    @endif
    <input type="file" name="popup_image" accept="image/*" class="text-sm">
</div>
@include('homecontent::partials.i18n-input', ['label' => 'Title', 'name' => 'content[title]', 'values' => $c['title'] ?? []])
@include('homecontent::partials.i18n-input', ['label' => 'Text (optional)', 'name' => 'content[text]', 'values' => $c['text'] ?? []])
@include('homecontent::partials.i18n-input', ['label' => 'Button label (optional)', 'name' => 'content[button_label]', 'values' => $c['button_label'] ?? []])
@include('homecontent::partials.link-picker', ['name' => 'content[link]', 'value' => $c['link'] ?? null])
<div x-data="{ mode: '{{ $c['frequency']['mode'] ?? 'once_per_session' }}' }" class="flex flex-wrap items-end gap-4">
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Show again</label>
        <select name="content[frequency][mode]" x-model="mode" class="border-gray-300 rounded-lg">
            <option value="once_per_session">Once per session</option>
            <option value="once_per_days">Once every N days</option>
        </select>
    </div>
    <div x-show="mode === 'once_per_days'">
        <label class="block text-sm font-medium text-slate-700 mb-1">Days</label>
        <input type="number" name="content[frequency][days]" min="1" max="90"
               value="{{ $c['frequency']['days'] ?? 7 }}" class="w-24 border-gray-300 rounded-lg">
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Delay (seconds)</label>
        <input type="number" name="content[delay_seconds]" min="0" max="60"
               value="{{ $c['delay_seconds'] ?? 3 }}" class="w-24 border-gray-300 rounded-lg">
    </div>
</div>
