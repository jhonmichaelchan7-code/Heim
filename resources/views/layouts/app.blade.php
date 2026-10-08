<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Heim POS') }}</title>
        <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}" />

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @stack('styles')
    </head>
    <body class="font-sans antialiased bg-gray-50 text-gray-900">
        <div x-data="{ mobileOpen: false }" class="min-h-screen bg-gray-50 flex flex-col lg:flex-row">
            @include('layouts.navigation')

            <!-- Main Application Content Area -->
            <div class="flex-1 flex flex-col min-w-0 {{ request()->routeIs('pos.*') ? 'h-screen overflow-hidden' : '' }}">
                <!-- Flash Messages -->
                @if (session('success'))
                    <div id="flash-success" class="w-full px-4 sm:px-6 lg:px-8 xl:px-10 2xl:max-w-[1880px] 2xl:mx-auto mt-4">
                        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl relative shadow-sm" role="alert">
                            <span class="block sm:inline font-medium">{{ session('success') }}</span>
                            <button onclick="this.parentElement.parentElement.remove()" class="absolute top-0 bottom-0 right-0 px-4 py-3">
                                <svg class="fill-current h-5 w-5 text-green-500" viewBox="0 0 20 20"><path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/></svg>
                            </button>
                        </div>
                    </div>
                @endif

                @if (session('error'))
                    <div id="flash-error" class="w-full px-4 sm:px-6 lg:px-8 xl:px-10 2xl:max-w-[1880px] 2xl:mx-auto mt-4">
                        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl relative shadow-sm" role="alert">
                            <span class="block sm:inline font-medium">{{ session('error') }}</span>
                            <button onclick="this.parentElement.parentElement.remove()" class="absolute top-0 bottom-0 right-0 px-4 py-3">
                                <svg class="fill-current h-5 w-5 text-red-500" viewBox="0 0 20 20"><path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/></svg>
                            </button>
                        </div>
                    </div>
                @endif

                <!-- Page Heading (Clean Vertical Rhythm) -->
                @if (isset($header))
                    <header class="bg-white border-b border-gray-100 shadow-[0_1px_2px_rgba(0,0,0,0.02)]">
                        <div class="w-full px-4 sm:px-6 lg:px-8 xl:px-10 2xl:max-w-[1880px] 2xl:mx-auto py-3.5 sm:py-4">
                            {{ $header }}
                        </div>
                    </header>
                @endif

                <!-- Page Content -->
                <main class="flex-1 {{ request()->routeIs('pos.*') ? 'h-full overflow-hidden' : '' }}">
                    {{ $slot }}
                </main>
            </div>
        </div>

        @stack('scripts')

        <script>
            // Auto-hide flash messages after 5 seconds
            setTimeout(() => {
                document.querySelectorAll('#flash-success, #flash-error').forEach(el => {
                    el.style.transition = 'opacity 0.5s';
                    el.style.opacity = '0';
                    setTimeout(() => el.remove(), 500);
                });
            }, 5000);

            // Live Real-Time Clock for Desktop Sidebar & Mobile Top Bar
            function updateAppNavClock() {
                const now = new Date();
                const timeString = now.toLocaleString('en-US', {
                    hour: 'numeric',
                    minute: '2-digit',
                    second: '2-digit',
                    hour12: true
                });
                const shortTimeString = now.toLocaleString('en-US', {
                    hour: 'numeric',
                    minute: '2-digit',
                    hour12: true
                });

                const desktopClock = document.getElementById('nav-live-clock');
                if (desktopClock) {
                    desktopClock.innerText = timeString;
                }

                const mobileClock = document.getElementById('nav-live-clock-mobile');
                if (mobileClock) {
                    mobileClock.innerText = shortTimeString;
                }
            }
            setInterval(updateAppNavClock, 1000);
            updateAppNavClock();
        </script>
    </body>
</html>
