<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Narzin') }} - @yield('title')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/ag-grid-community/dist/ag-grid-community.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css" integrity="sha512-5Hs3dF2AEPkpNAR7UiOHba+lRSJNeM2ECkwxUIxC1Q/FLycGTbNapWXB4tP889k5T5Ju8fs4b1P5z/iB4nMfSQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @yield('styles') 

</head>
<body x-data="{ sidebarOpen: true }" class="bg-gray-50">
    <div class="min-h-screen flex">
        <x-admin.sidebar />
        <div :class="{'lg:pl-64': sidebarOpen, 'lg:pl-20': !sidebarOpen}" class="flex flex-col flex-1 transition-all duration-300">
            <x-admin.header />
            <main class="flex-1 overflow-y-auto bg-gray-50">
                <div class="py-6">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <x-alerts />
                        {{ $slot }}
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="../node_modules/flyonui/flyonui.js"></script>
    <script src="../node_modules/flyonui/dist/js/accordion.js"></script> 
</body>
</html>
