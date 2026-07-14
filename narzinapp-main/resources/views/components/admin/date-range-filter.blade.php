@props(['from', 'to'])
@php
    use Illuminate\Support\Carbon;
    $base = url()->current();
    $presets = [
        '7 days' => [Carbon::now()->subDays(7), Carbon::now()],
        '30 days' => [Carbon::now()->subDays(30), Carbon::now()],
        '90 days' => [Carbon::now()->subDays(90), Carbon::now()],
        'This month' => [Carbon::now()->startOfMonth(), Carbon::now()],
        'This year' => [Carbon::now()->startOfYear(), Carbon::now()],
    ];
@endphp
<div class="mt-4">
    <form method="GET" class="flex flex-wrap items-end gap-3">
        <label class="text-sm">From <input type="date" name="from" value="{{ $from }}" class="block border rounded px-2 py-1" /></label>
        <label class="text-sm">To <input type="date" name="to" value="{{ $to }}" class="block border rounded px-2 py-1" /></label>
        <button type="submit" class="bg-gray-800 text-white rounded px-4 py-1.5 text-sm">Apply</button>
    </form>
    <div class="mt-2 flex flex-wrap gap-2 text-xs">
        @foreach ($presets as $label => [$pFrom, $pTo])
            <a href="{{ $base }}?from={{ $pFrom->format('Y-m-d') }}&amp;to={{ $pTo->format('Y-m-d') }}"
               class="px-2 py-1 rounded border text-gray-600 hover:bg-gray-100">{{ $label }}</a>
        @endforeach
    </div>
</div>
