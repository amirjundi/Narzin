@props(['route' => null])

@if($route && Route::has($route))
    <a 
        href="{{ route($route) }}"
        @class([
            'relative flex items-center pl-8 pr-3 py-2 text-sm rounded-lg transition-colors duration-200',
            'text-blue-600 bg-blue-50 hover:bg-blue-50' => request()->routeIs($route),
            'text-gray-600 hover:bg-gray-50' => !request()->routeIs($route)
        ])
    >
        <div class="absolute left-[0.5rem] w-2 h-2 rounded-full bg-gray-300"></div>
        {{ $slot }}
    </a>
@else
    <span class="relative flex items-center pl-8 pr-3 py-2 text-sm text-gray-400 cursor-not-allowed">
        <div class="absolute left-[0.5rem] w-2 h-2 rounded-full bg-gray-200"></div>
        {{ $slot }}
        <span class="ml-auto text-xs">(Soon)</span>
    </span>
@endif