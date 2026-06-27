<header class="bg-white shadow-sm z-10">
    <div class="flex items-center justify-between h-16 px-4 sm:px-6 lg:px-8">
        <!-- Mobile Menu Button -->
        <button @click="sidebarOpen = !sidebarOpen" type="button" class="lg:hidden text-gray-500 hover:text-gray-600 focus:outline-none">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>

        <!-- Search -->
        <div class="flex-1 px-4 lg:px-8">
            <x-admin.search />
        </div>

        <!-- Right side buttons -->
        <div class="flex items-center space-x-4">
            <x-admin.notifications />
            <x-admin.account-dropdown />
        </div>
    </div>
</header>