<div class="bg-white border border-gray-200 rounded-lg p-5 grid grid-cols-1 md:grid-cols-2 gap-4">
    <div class="md:col-span-2">
        <label class="block text-sm font-medium text-slate-700 mb-1">Internal name</label>
        <input type="text" name="name" value="{{ old('name', $block->name) }}" required
               class="w-full border-gray-300 rounded-lg" placeholder="e.g. Summer Sale Hero">
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Platform</label>
        <select name="platform" class="w-full border-gray-300 rounded-lg">
            @foreach (['both' => 'Web + App', 'web' => 'Web only', 'app' => 'App only'] as $value => $label)
                <option value="{{ $value }}" @selected(old('platform', $block->platform) === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="flex items-end pb-2">
        <label class="inline-flex items-center gap-2 text-sm text-slate-700">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" class="rounded"
                   @checked(old('is_active', $block->is_active))>
            Active
        </label>
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Starts at (optional)</label>
        <input type="datetime-local" name="starts_at" class="w-full border-gray-300 rounded-lg"
               value="{{ old('starts_at', optional($block->starts_at)->format('Y-m-d\TH:i')) }}">
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Ends at (optional)</label>
        <input type="datetime-local" name="ends_at" class="w-full border-gray-300 rounded-lg"
               value="{{ old('ends_at', optional($block->ends_at)->format('Y-m-d\TH:i')) }}">
    </div>
</div>
