<div>
    <div class="flex items-center justify-between mb-1">
        <label class="block text-sm font-medium text-slate-700">{{ $label }}</label>
        <div class="flex gap-1 text-xs">
            @foreach (['ar' => 'العربية', 'de' => 'Deutsch', 'en' => 'English'] as $code => $langLabel)
                <button type="button" @click="lang = '{{ $code }}'"
                        :class="lang === '{{ $code }}' ? 'bg-slate-800 text-white' : 'bg-gray-100 text-slate-600'"
                        class="px-2 py-0.5 rounded">
                    {{ $langLabel }}@if (!empty($values[$code])) &#8226;@endif
                </button>
            @endforeach
        </div>
    </div>
    @foreach (['ar', 'de', 'en'] as $code)
        <input x-show="lang === '{{ $code }}'" type="text"
               name="{{ $name }}[{{ $code }}]" value="{{ $values[$code] ?? '' }}"
               dir="{{ $code === 'ar' ? 'rtl' : 'ltr' }}"
               class="w-full border-gray-300 rounded-lg" placeholder="{{ $label }} ({{ strtoupper($code) }})">
    @endforeach
</div>
