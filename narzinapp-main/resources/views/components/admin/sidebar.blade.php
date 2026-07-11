<aside x-cloak x-bind:class="{ 'w-64': sidebarOpen, 'w-20': !sidebarOpen }"
    class="fixed inset-y-0 left-0 bg-gradient-to-b from-slate-900 to-slate-800 shadow-2xl hidden lg:block transition-all duration-300 z-50">
    <div class="flex flex-col h-full">
        <!-- Logo -->
        <div class="flex items-center justify-between h-16 border-b border-slate-700/50 px-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-cyan-400 rounded-xl flex items-center justify-center shadow-lg shadow-blue-500/20">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
                <span x-show="sidebarOpen" x-transition:enter="transition ease-out duration-200" 
                      x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                      class="text-xl font-bold bg-gradient-to-r from-blue-400 to-cyan-300 bg-clip-text text-transparent">
                    Narzin
                </span>
            </div>
            <button @click="sidebarOpen = !sidebarOpen" 
                    class="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-700/50 transition-colors">
                <svg x-bind:class="{ 'rotate-180': !sidebarOpen }" class="w-5 h-5 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
                </svg>
            </button>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 overflow-y-auto py-6 px-3">
            <!-- Main Section -->
            <div class="mb-6">
                <p x-show="sidebarOpen" class="px-3 mb-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                    Main
                </p>
                <ul class="space-y-1">
                    <!-- Dashboard -->
                    <li>
                        <a href="{{ route('dashboard') }}" 
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200
                                  {{ request()->routeIs('dashboard') 
                                     ? 'bg-gradient-to-r from-blue-600 to-blue-500 text-white shadow-lg shadow-blue-500/30' 
                                     : 'text-slate-400 hover:text-white hover:bg-slate-700/50' }}">
                            <div class="flex-shrink-0 w-9 h-9 flex items-center justify-center rounded-lg 
                                        {{ request()->routeIs('dashboard') ? 'bg-white/20' : 'bg-slate-700/50' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                </svg>
                            </div>
                            <span x-show="sidebarOpen" class="font-medium">Dashboard</span>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Users Section -->
            <div class="mb-6">
                <p x-show="sidebarOpen" class="px-3 mb-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                    Users
                </p>
                <ul class="space-y-1">
                    <!-- Admins -->
                    <li>
                        <a href="{{ route('admins.index') }}" 
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200
                                  {{ request()->routeIs('admins.*') 
                                     ? 'bg-gradient-to-r from-purple-600 to-purple-500 text-white shadow-lg shadow-purple-500/30' 
                                     : 'text-slate-400 hover:text-white hover:bg-slate-700/50' }}">
                            <div class="flex-shrink-0 w-9 h-9 flex items-center justify-center rounded-lg 
                                        {{ request()->routeIs('admins.*') ? 'bg-white/20' : 'bg-slate-700/50' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </div>
                            <span x-show="sidebarOpen" class="font-medium">Admins</span>
                        </a>
                    </li>

                    <!-- Active Vendors -->
                    <li>
                        <a href="{{ route('vendors.index') }}" 
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200
                                  {{ request()->routeIs('vendors.index') 
                                     ? 'bg-gradient-to-r from-green-600 to-green-500 text-white shadow-lg shadow-green-500/30' 
                                     : 'text-slate-400 hover:text-white hover:bg-slate-700/50' }}">
                            <div class="flex-shrink-0 w-9 h-9 flex items-center justify-center rounded-lg 
                                        {{ request()->routeIs('vendors.index') ? 'bg-white/20' : 'bg-slate-700/50' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                            </div>
                            <span x-show="sidebarOpen" class="font-medium">Active Vendors</span>
                        </a>
                    </li>

                    <!-- Pending Vendors -->
                    <li>
                        <a href="{{ route('vendors.waiting-action') }}" 
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200
                                  {{ request()->routeIs('vendors.waiting-action') 
                                     ? 'bg-gradient-to-r from-yellow-600 to-yellow-500 text-white shadow-lg shadow-yellow-500/30' 
                                     : 'text-slate-400 hover:text-white hover:bg-slate-700/50' }}">
                            <div class="flex-shrink-0 w-9 h-9 flex items-center justify-center rounded-lg 
                                        {{ request()->routeIs('vendors.waiting-action') ? 'bg-white/20' : 'bg-slate-700/50' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <span x-show="sidebarOpen" class="font-medium">Pending Vendors</span>
                        </a>
                    </li>

                    <!-- Customers -->
                    <li>
                        <a href="{{ route('users.index') }}" 
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200
                                  {{ request()->routeIs('users.*') 
                                     ? 'bg-gradient-to-r from-cyan-600 to-cyan-500 text-white shadow-lg shadow-cyan-500/30' 
                                     : 'text-slate-400 hover:text-white hover:bg-slate-700/50' }}">
                            <div class="flex-shrink-0 w-9 h-9 flex items-center justify-center rounded-lg 
                                        {{ request()->routeIs('users.*') ? 'bg-white/20' : 'bg-slate-700/50' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                            </div>
                            <span x-show="sidebarOpen" class="font-medium">Customers</span>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Products Section -->
            <div class="mb-6">
                <p x-show="sidebarOpen" class="px-3 mb-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                    Products
                </p>
                <ul class="space-y-1">
                    <!-- Categories -->
                    <li>
                        <a href="{{ route('categories.index') }}" 
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200
                                  {{ request()->routeIs('categories.*') 
                                     ? 'bg-gradient-to-r from-indigo-600 to-indigo-500 text-white shadow-lg shadow-indigo-500/30' 
                                     : 'text-slate-400 hover:text-white hover:bg-slate-700/50' }}">
                            <div class="flex-shrink-0 w-9 h-9 flex items-center justify-center rounded-lg 
                                        {{ request()->routeIs('categories.*') ? 'bg-white/20' : 'bg-slate-700/50' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                                </svg>
                            </div>
                            <span x-show="sidebarOpen" class="font-medium">Categories</span>
                        </a>
                    </li>

                    <!-- Sub Categories -->
                    <li>
                        <a href="{{ route('sub-categories.index') }}" 
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200
                                  {{ request()->routeIs('sub-categories.*') 
                                     ? 'bg-gradient-to-r from-violet-600 to-violet-500 text-white shadow-lg shadow-violet-500/30' 
                                     : 'text-slate-400 hover:text-white hover:bg-slate-700/50' }}">
                            <div class="flex-shrink-0 w-9 h-9 flex items-center justify-center rounded-lg 
                                        {{ request()->routeIs('sub-categories.*') ? 'bg-white/20' : 'bg-slate-700/50' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                </svg>
                            </div>
                            <span x-show="sidebarOpen" class="font-medium">Sub Categories</span>
                        </a>
                    </li>

                    <!-- Products -->
                    <li>
                        <a href="{{ route('products.index') }}" 
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200
                                  {{ request()->routeIs('products.*') 
                                     ? 'bg-gradient-to-r from-pink-600 to-pink-500 text-white shadow-lg shadow-pink-500/30' 
                                     : 'text-slate-400 hover:text-white hover:bg-slate-700/50' }}">
                            <div class="flex-shrink-0 w-9 h-9 flex items-center justify-center rounded-lg 
                                        {{ request()->routeIs('products.*') ? 'bg-white/20' : 'bg-slate-700/50' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                            </div>
                            <span x-show="sidebarOpen" class="font-medium">Products</span>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Orders Section -->
            <div class="mb-6">
                <p x-show="sidebarOpen" class="px-3 mb-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                    Orders
                </p>
                <ul class="space-y-1">
                    <!-- All Orders -->
                    <li>
                        <a href="{{ route('orders.index') }}" 
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200
                                  {{ request()->routeIs('orders.*') 
                                     ? 'bg-gradient-to-r from-orange-600 to-orange-500 text-white shadow-lg shadow-orange-500/30' 
                                     : 'text-slate-400 hover:text-white hover:bg-slate-700/50' }}">
                            <div class="flex-shrink-0 w-9 h-9 flex items-center justify-center rounded-lg 
                                        {{ request()->routeIs('orders.*') ? 'bg-white/20' : 'bg-slate-700/50' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                </svg>
                            </div>
                            <span x-show="sidebarOpen" class="font-medium">All Orders</span>
                            @php
                                $pendingOrders = \Modules\Checkout\Models\Order::whereIn('payment_status', ['processing', 'completed'])
                                    ->where('order_status', 'confirmed')->count();
                            @endphp
                            @if($pendingOrders > 0)
                                <span x-show="sidebarOpen" class="ml-auto px-2 py-0.5 text-xs font-semibold bg-orange-500 text-white rounded-full">
                                    {{ $pendingOrders }}
                                </span>
                            @endif
                        </a>
                    </li>

                    <!-- Shipping Prices -->
                    <li>
                        <a href="{{ route('delivery-zones.index') }}" 
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200
                                  {{ request()->routeIs('delivery-zones.*') 
                                     ? 'bg-gradient-to-r from-teal-600 to-teal-500 text-white shadow-lg shadow-teal-500/30' 
                                     : 'text-slate-400 hover:text-white hover:bg-slate-700/50' }}">
                            <div class="flex-shrink-0 w-9 h-9 flex items-center justify-center rounded-lg 
                                        {{ request()->routeIs('delivery-zones.*') ? 'bg-white/20' : 'bg-slate-700/50' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                                </svg>
                            </div>
                            <span x-show="sidebarOpen" class="font-medium">Delivery Zones</span>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Shipments Section -->
            <div class="mb-6">
                <p x-show="sidebarOpen" class="px-3 mb-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                    Shipments
                </p>
                <ul class="space-y-1">
                    <!-- All Batches -->
                    <li>
                        <a href="{{ route('shipments.index') }}" 
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200
                                  {{ request()->routeIs('shipments.index') 
                                     ? 'bg-gradient-to-r from-violet-600 to-violet-500 text-white shadow-lg shadow-violet-500/30' 
                                     : 'text-slate-400 hover:text-white hover:bg-slate-700/50' }}">
                            <div class="flex-shrink-0 w-9 h-9 flex items-center justify-center rounded-lg 
                                        {{ request()->routeIs('shipments.index') ? 'bg-white/20' : 'bg-slate-700/50' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                            </div>
                            <span x-show="sidebarOpen" class="font-medium">All Batches</span>
                            @php
                                $activeBatches = \Modules\Admin\Models\ShipmentBatch::whereIn('status', ['pending', 'collecting'])->count();
                            @endphp
                            @if($activeBatches > 0)
                                <span x-show="sidebarOpen" class="ml-auto px-2 py-0.5 text-xs font-semibold bg-violet-500 text-white rounded-full">
                                    {{ $activeBatches }}
                                </span>
                            @endif
                        </a>
                    </li>

                    <!-- Today's Pickups -->
                    <li>
                        <a href="{{ route('shipments.daily') }}" 
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200
                                  {{ request()->routeIs('shipments.daily') 
                                     ? 'bg-gradient-to-r from-amber-600 to-amber-500 text-white shadow-lg shadow-amber-500/30' 
                                     : 'text-slate-400 hover:text-white hover:bg-slate-700/50' }}">
                            <div class="flex-shrink-0 w-9 h-9 flex items-center justify-center rounded-lg 
                                        {{ request()->routeIs('shipments.daily') ? 'bg-white/20' : 'bg-slate-700/50' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <span x-show="sidebarOpen" class="font-medium">Today's Pickups</span>
                        </a>
                    </li>

                    <!-- Create Batch -->
                    <li>
                        <a href="{{ route('shipments.create') }}" 
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200
                                  {{ request()->routeIs('shipments.create') 
                                     ? 'bg-gradient-to-r from-cyan-600 to-cyan-500 text-white shadow-lg shadow-cyan-500/30' 
                                     : 'text-slate-400 hover:text-white hover:bg-slate-700/50' }}">
                            <div class="flex-shrink-0 w-9 h-9 flex items-center justify-center rounded-lg 
                                        {{ request()->routeIs('shipments.create') ? 'bg-white/20' : 'bg-slate-700/50' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                            </div>
                            <span x-show="sidebarOpen" class="font-medium">Create Batch</span>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Reports Section -->
            <div class="mb-6">
                <p x-show="sidebarOpen" class="px-3 mb-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                    Reports
                </p>
                <ul class="space-y-1">
                    <!-- Users Statistics -->
                    <li>
                        <a href="{{ route('statistics.users') }}" 
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200
                                  {{ request()->routeIs('statistics.users') 
                                     ? 'bg-gradient-to-r from-blue-600 to-blue-500 text-white shadow-lg shadow-blue-500/30' 
                                     : 'text-slate-400 hover:text-white hover:bg-slate-700/50' }}">
                            <div class="flex-shrink-0 w-9 h-9 flex items-center justify-center rounded-lg 
                                        {{ request()->routeIs('statistics.users') ? 'bg-white/20' : 'bg-slate-700/50' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                            <span x-show="sidebarOpen" class="font-medium">Users Stats</span>
                        </a>
                    </li>

                    <!-- Vendors Statistics -->
                    <li>
                        <a href="{{ route('statistics.vendors') }}" 
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200
                                  {{ request()->routeIs('statistics.vendors') 
                                     ? 'bg-gradient-to-r from-green-600 to-green-500 text-white shadow-lg shadow-green-500/30' 
                                     : 'text-slate-400 hover:text-white hover:bg-slate-700/50' }}">
                            <div class="flex-shrink-0 w-9 h-9 flex items-center justify-center rounded-lg 
                                        {{ request()->routeIs('statistics.vendors') ? 'bg-white/20' : 'bg-slate-700/50' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </div>
                            <span x-show="sidebarOpen" class="font-medium">Vendors Stats</span>
                        </a>
                    </li>

                    <!-- Orders Statistics -->
                    <li>
                        <a href="{{ route('statistics.orders') }}" 
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200
                                  {{ request()->routeIs('statistics.orders') 
                                     ? 'bg-gradient-to-r from-purple-600 to-purple-500 text-white shadow-lg shadow-purple-500/30' 
                                     : 'text-slate-400 hover:text-white hover:bg-slate-700/50' }}">
                            <div class="flex-shrink-0 w-9 h-9 flex items-center justify-center rounded-lg 
                                        {{ request()->routeIs('statistics.orders') ? 'bg-white/20' : 'bg-slate-700/50' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <span x-show="sidebarOpen" class="font-medium">Orders Stats</span>
                        </a>
                    </li>

                    <!-- Products Statistics -->
                    <li>
                        <a href="{{ route('statistics.products') }}" 
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200
                                  {{ request()->routeIs('statistics.products') 
                                     ? 'bg-gradient-to-r from-rose-600 to-rose-500 text-white shadow-lg shadow-rose-500/30' 
                                     : 'text-slate-400 hover:text-white hover:bg-slate-700/50' }}">
                            <div class="flex-shrink-0 w-9 h-9 flex items-center justify-center rounded-lg 
                                        {{ request()->routeIs('statistics.products') ? 'bg-white/20' : 'bg-slate-700/50' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
                                </svg>
                            </div>
                            <span x-show="sidebarOpen" class="font-medium">Products Stats</span>
                        </a>
                    </li>

                    <!-- Conversion Funnel -->
                    <li>
                        <a href="{{ route('statistics.funnel') }}"
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200
                                  {{ request()->routeIs('statistics.funnel')
                                     ? 'bg-gradient-to-r from-rose-600 to-rose-500 text-white shadow-lg shadow-rose-500/30'
                                     : 'text-slate-400 hover:text-white hover:bg-slate-700/50' }}">
                            <div class="flex-shrink-0 w-9 h-9 flex items-center justify-center rounded-lg
                                        {{ request()->routeIs('statistics.funnel') ? 'bg-white/20' : 'bg-slate-700/50' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L15 12.414V19a1 1 0 01-.553.894l-4 2A1 1 0 019 21v-8.586L3.293 6.707A1 1 0 013 6V4z" />
                                </svg>
                            </div>
                            <span x-show="sidebarOpen" class="font-medium">Funnel</span>
                        </a>
                    </li>

                    <!-- Coupons & Promotions Stats -->
                    <li>
                        <a href="{{ route('statistics.promotions') }}"
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200
                                  {{ request()->routeIs('statistics.promotions')
                                     ? 'bg-gradient-to-r from-rose-600 to-rose-500 text-white shadow-lg shadow-rose-500/30'
                                     : 'text-slate-400 hover:text-white hover:bg-slate-700/50' }}">
                            <div class="flex-shrink-0 w-9 h-9 flex items-center justify-center rounded-lg
                                        {{ request()->routeIs('statistics.promotions') ? 'bg-white/20' : 'bg-slate-700/50' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5a1.99 1.99 0 011.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.99 1.99 0 013 12V7a4 4 0 014-4z" />
                                </svg>
                            </div>
                            <span x-show="sidebarOpen" class="font-medium">Promotions Stats</span>
                        </a>
                    </li>

                    <!-- Best Sellers -->
                    <li>
                        <a href="#" 
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200
                                  text-slate-400 hover:text-white hover:bg-slate-700/50">
                            <div class="flex-shrink-0 w-9 h-9 flex items-center justify-center rounded-lg 
                                        bg-slate-700/50">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                                </svg>
                            </div>
                            <span x-show="sidebarOpen" class="font-medium">Best Sellers</span>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Marketing Section -->
            <div class="mb-6">
                <p x-show="sidebarOpen" class="px-3 mb-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                    Marketing
                </p>
                <ul class="space-y-1">
                    <!-- Homepage -->
                    @if(Route::has('home-blocks.index'))
                    <li>
                        <a href="{{ route('home-blocks.index') }}"
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200
                                  {{ request()->routeIs('home-blocks.*')
                                     ? 'bg-gradient-to-r from-fuchsia-600 to-fuchsia-500 text-white shadow-lg shadow-fuchsia-500/30'
                                     : 'text-slate-400 hover:text-white hover:bg-slate-700/50' }}">
                            <div class="flex-shrink-0 w-9 h-9 flex items-center justify-center rounded-lg
                                        {{ request()->routeIs('home-blocks.*') ? 'bg-white/20' : 'bg-slate-700/50' }}">
                                <span class="inline-flex items-center justify-center w-5 h-5"><i class="fa-solid fa-house"></i></span>
                            </div>
                            <span x-show="sidebarOpen" class="font-medium">Homepage</span>
                        </a>
                    </li>
                    @endif

                    <!-- Coupons -->
                    <li>
                        <a href="{{ route('coupons.index') }}"
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200
                                  {{ request()->routeIs('coupons.*')
                                     ? 'bg-gradient-to-r from-emerald-600 to-emerald-500 text-white shadow-lg shadow-emerald-500/30'
                                     : 'text-slate-400 hover:text-white hover:bg-slate-700/50' }}">
                            <div class="flex-shrink-0 w-9 h-9 flex items-center justify-center rounded-lg
                                        {{ request()->routeIs('coupons.*') ? 'bg-white/20' : 'bg-slate-700/50' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                </svg>
                            </div>
                            <span x-show="sidebarOpen" class="font-medium">Coupons</span>
                        </a>
                    </li>

                    <!-- Promotions -->
                    <li>
                        <a href="{{ route('promotions.index') }}"
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200
                                  {{ request()->routeIs('promotions.*')
                                     ? 'bg-gradient-to-r from-emerald-600 to-emerald-500 text-white shadow-lg shadow-emerald-500/30'
                                     : 'text-slate-400 hover:text-white hover:bg-slate-700/50' }}">
                            <div class="flex-shrink-0 w-9 h-9 flex items-center justify-center rounded-lg
                                        {{ request()->routeIs('promotions.*') ? 'bg-white/20' : 'bg-slate-700/50' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                </svg>
                            </div>
                            <span x-show="sidebarOpen" class="font-medium">Promotions</span>
                        </a>
                    </li>

                </ul>
            </div>

            <!-- Settings Section -->
            <div class="mb-6">
                <p x-show="sidebarOpen" class="px-3 mb-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                    Settings
                </p>
                <ul class="space-y-1">
                    <!-- Exchange Rates -->
                    <li>
                        <a href="{{ route('price-exchange.index') }}" 
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200
                                  {{ request()->routeIs('price-exchange.*') 
                                     ? 'bg-gradient-to-r from-lime-600 to-lime-500 text-white shadow-lg shadow-lime-500/30' 
                                     : 'text-slate-400 hover:text-white hover:bg-slate-700/50' }}">
                            <div class="flex-shrink-0 w-9 h-9 flex items-center justify-center rounded-lg 
                                        {{ request()->routeIs('price-exchange.*') ? 'bg-white/20' : 'bg-slate-700/50' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <span x-show="sidebarOpen" class="font-medium">Exchange Rates</span>
                        </a>
                    </li>
                    <!-- Global Markup -->
                    <li>
                        <a href="{{ route('platform-markup.index') }}" 
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200
                                  {{ request()->routeIs('platform-markup.*') 
                                     ? 'bg-gradient-to-r from-lime-600 to-lime-500 text-white shadow-lg shadow-lime-500/30' 
                                     : 'text-slate-400 hover:text-white hover:bg-slate-700/50' }}">
                            <div class="flex-shrink-0 w-9 h-9 flex items-center justify-center rounded-lg 
                                        {{ request()->routeIs('platform-markup.*') ? 'bg-white/20' : 'bg-slate-700/50' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                </svg>
                            </div>
                            <span x-show="sidebarOpen" class="font-medium">Global Markup</span>
                        </a>
                    </li>

                    <!-- Site Settings -->
                    <li>
                        <a href="{{ route('settings.edit') }}"
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200
                                  {{ request()->routeIs('settings.*')
                                     ? 'bg-gradient-to-r from-slate-600 to-slate-500 text-white shadow-lg shadow-slate-500/30'
                                     : 'text-slate-400 hover:text-white hover:bg-slate-700/50' }}">
                            <div class="flex-shrink-0 w-9 h-9 flex items-center justify-center rounded-lg
                                        {{ request()->routeIs('settings.*') ? 'bg-white/20' : 'bg-slate-700/50' }}">
                                <span class="inline-flex items-center justify-center w-5 h-5"><i class="fa-solid fa-gear"></i></span>
                            </div>
                            <span x-show="sidebarOpen" class="font-medium">Settings</span>
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- User Profile / Footer -->
        <div class="border-t border-slate-700/50 p-4">
            <a href="#" 
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 text-slate-400 hover:text-white hover:bg-slate-700/50">
                <div class="flex-shrink-0 w-9 h-9 bg-gradient-to-br from-blue-500 to-purple-500 rounded-lg flex items-center justify-center text-white font-semibold text-sm">
                    {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
                </div>
                <div x-show="sidebarOpen" class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-white truncate">{{ auth()->user()->name ?? 'Admin' }}</p>
                    <p class="text-xs text-slate-500 truncate">{{ auth()->user()->email ?? '' }}</p>
                </div>
                <svg x-show="sidebarOpen" class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </a>

            <!-- Logout -->
            <form method="POST" action="{{ route('logout') }}" class="mt-2">
                @csrf
                <button type="submit" 
                        class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 text-red-400 hover:text-white hover:bg-red-500/20">
                    <div class="flex-shrink-0 w-9 h-9 flex items-center justify-center rounded-lg bg-red-500/10">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </div>
                    <span x-show="sidebarOpen" class="font-medium">Logout</span>
                </button>
            </form>
        </div>
    </div>
</aside>