@props(['route' => null, 'icon', 'hasChildren' => false])

<li x-data="{ isOpen: false }" class="!my-3">
    @if($hasChildren)
        <div class="relative">
            <button 
                @click="isOpen = !isOpen" 
                type="button"
                class="flex items-center w-full px-3 py-2.5 text-gray-700 hover:bg-gray-50 rounded-lg transition-colors duration-200"
                :class="{ 'bg-gray-50': isOpen }"
            >
                <div class="flex items-center flex-1">
                    <x-heroicon-o-{{ $icon }} 
                        class="w-5 h-5 transition-colors duration-200" 
                        :class="isOpen ? 'text-blue-600' : 'text-gray-500'"
                    />
                    <div 
                        x-bind:class="{ 'hidden': !sidebarOpen }" 
                        class="ml-3 text-sm font-medium"
                        :class="isOpen ? 'text-blue-600' : 'text-gray-700'"
                    >
                        {{ $slot }}
                    </div>
                </div>
                <div x-show="sidebarOpen" class="flex items-center">
                    <x-heroicon-o-chevron-right
                        class="w-4 h-4 ml-1 text-gray-400 transition-transform duration-200"
                        x-bind:class="{ 'transform rotate-90': isOpen }"
                    />
                </div>
            </button>
        </div>
        
        <!-- Submenu -->
        <div 
            x-show="isOpen && sidebarOpen" 
            x-collapse
            class="mt-1"
        >
            <div class="relative ml-3 before:absolute before:left-3.5 before:top-0 before:h-full before:w-[1.5px] before:bg-gray-200">
                {{ $children }}
            </div>
        </div>
    @else
        <a 
            href="{{ $route && Route::has($route) ? route($route) : '#' }}" 
            @class([
                'flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors duration-200',
                'text-blue-600 bg-blue-50 hover:bg-blue-50' => $route && Route::has($route) && request()->routeIs($route),
                'text-gray-700 hover:bg-gray-50' => !($route && Route::has($route) && request()->routeIs($route))
            ])
        >
            <x-heroicon-o-{{ $icon }} @class([
                'w-5 h-5',
                'text-blue-600' => $route && Route::has($route) && request()->routeIs($route),
                'text-gray-500' => !($route && Route::has($route) && request()->routeIs($route))
            ])/>
            <span x-bind:class="{ 'hidden': !sidebarOpen }" class="ml-3">{{ $slot }}</span>
        </a>
    @endif
</li>