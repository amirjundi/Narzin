@props(['type' => 'button', 'href' => null])

@php
    // We use a dark slate color that perfectly matches the sidebar's premium dark theme.
    // It features a subtle shadow, smooth transitions, and a scale effect on click.
    $classes = 'inline-flex justify-center items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-slate-900 rounded-lg shadow-md hover:bg-slate-800 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 transition-all duration-200 ease-in-out active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed';
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif
