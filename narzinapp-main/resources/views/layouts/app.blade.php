@php
    use Modules\Vendor\Models\Vendor;
    $vendor = Vendor::where('user_id', Auth::id())->first();
@endphp

<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Fredoka:wght@300..700&family=Tajawal:wght@200;300;400;500;700;800;900&display=swap"
        rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])


</head>

<body class="bg-gray-100 flex h-screen">
    <!-- Sidebar -->
    <div id="sidebar"
        class="fixed inset-y-0 transform transition-transform duration-300 bg-[#246392] text-white w-64 min-h-screen md:relative z-50"
        :class="document.dir === 'rtl' ? 'right-0 translate-x-full md:translate-x-0' :
            'left-0 -translate-x-full md:translate-x-0'">
        <div class="flex items-center justify-center px-4 py-3 border-b border-[#919191]">
            <h1 class="text-[31px] font-bold">NARZIN</h1>
            <img class="mx-2" src="{{ asset('storage/images/vendor/logoVendor.svg') }}" alt="">
            <button id="closeSidebar" class="text-white focus:outline-none md:hidden">
                ✖
            </button>
        </div>
        <nav class="mt-4 flex flex-col space-y-2 px-4">
            <a href="#"
                class="flex items-center space-x-2 rtl:space-x-reverse text-white hover:bg-blue-700 p-2 rounded">
                <span>🏠</span>
                <span class="sidebar-text">{{ trans('vendor.layout.home') }}</span>
            </a>
            <a href="#"
                class="flex items-center space-x-2 rtl:space-x-reverse text-white hover:bg-blue-700 p-2 rounded">
                <span>📄</span>
                <span class="sidebar-text">{{ __('vendor.layout.reports') }}</span>
            </a>
            <a href="#"
                class="flex items-center space-x-2 rtl:space-x-reverse text-white hover:bg-blue-700 p-2 rounded">
                <span>⚙️</span>
                <span class="sidebar-text">{{ __('vendor.layout.settings') }}</span>
            </a>
            <button onclick="setLanguage('ar')">{{ __('vendor.layout.arabic') }}</button>
            <button onclick="setLanguage('nl')">{{ __('vendor.layout.dutch') }}</button>
        </nav>

    </div>

    <!-- Main Content -->

    <div class="w-full">
        {{-- NAVBAR --}}

        <div class="navbar bg-base-100">
            <div class="flex-1">
                <button id="openSidebar" class=" text-black px-4 py-2 rounded ">
                    ☰
                </button>
            </div>
            <div class="flex-none">

                <div class="dropdown dropdown-end">
                    <div tabindex="0" role="button" class="btn btn-ghost btn-circle avatar">
                        <div class="w-10 rounded-full">
                            <img alt="Tailwind CSS Navbar component"
                                src="http://localhost/Narzin/public/storage/{{ $vendor->store_logo }}" />
                        </div>
                    </div>
                    <ul tabindex="0"
                        class="menu menu-sm dropdown-content bg-base-100 rounded-box z-[1] mt-3 w-52 p-2 shadow">
                        <li>
                            <a class="justify-between">
                                {{ __('vendor.layout.my-account') }}
                            </a>
                        </li>
                        <li> <button onclick="setLanguage('ar')">{{ __('vendor.layout.arabic') }}</button></li>
                        <li> <button onclick="setLanguage('nl')">{{ __('vendor.layout.dutch') }}</button></li>

                        <li><a>{{ __('vendor.layout.logout') }}</a></li>
                    </ul>
                </div>
            </div>
        </div>


        {{-- NAVBAR --}}
        <div class="flex-1 p-6 ">
            <div class="flex justify-between items-center">





                {{ $slot }}
            </div>
        </div>
    </div>

    <script>
        const sidebar = document.getElementById("sidebar");
        const openSidebar = document.getElementById("openSidebar");
        const closeSidebar = document.getElementById("closeSidebar");
        const html = document.documentElement;

        function toggleSidebar(show) {
            const isRTL = html.getAttribute("dir") === "rtl";
            if (show) {
                sidebar.style.transform = "translateX(0)";
            } else {
                sidebar.style.transform = isRTL ? "translateX(100%)" : "translateX(-100%)";
            }
        }

        // Open Sidebar
        openSidebar.addEventListener("click", () => {
            toggleSidebar(true);
        });

        // Close Sidebar
        closeSidebar.addEventListener("click", () => {
            toggleSidebar(false);
        });

        function switchDirection(locale) {
            const html = document.documentElement;
            const rtlStylesheet = '/css/style-rtl.css';
            const ltrStylesheet = '/css/style.css';

            if (locale === 'ar') {
                html.setAttribute('dir', 'rtl');
                document.getElementById('stylesheet').href = rtlStylesheet;
                sidebar.classList.remove('left-0', '-translate-x-full');
                sidebar.classList.add('right-0', 'translate-x-full');
            } else {
                html.setAttribute('dir', 'ltr');
                document.getElementById('stylesheet').href = ltrStylesheet;
                sidebar.classList.remove('right-0', 'translate-x-full');
                sidebar.classList.add('left-0', '-translate-x-full');
            }
        }

        function setLanguage(locale) {
            const baseUrl = '{{ config('app.url') }}';

            localStorage.setItem('locale', locale);
            fetch(`${baseUrl}/language/${locale}`, {
                    headers: {
                        'X-Locale': locale,
                        'Accept': 'application/json'
                    }
                }).then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.reload();
                    }
                })
                .catch(error => {
                    console.error('Error switching language:', error);
                });
        }



        document.addEventListener('DOMContentLoaded', () => {
            const locale = localStorage.getItem('locale');
            if (locale) {
                document.cookie = `locale=${locale};path=/`;
                switchDirection(locale);
            }
        });
    </script>
</body>

</html>
