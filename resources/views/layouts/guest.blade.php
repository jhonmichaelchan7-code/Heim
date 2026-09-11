<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Heim POS') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased bg-[#f0f8f5]">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 p-4">
            <div class="flex flex-col items-center gap-2 mb-2">
                <a href="/" class="flex flex-col items-center group">
                    <div class="w-24 h-24 rounded-full overflow-hidden shadow-lg border-4 border-white bg-[#155d49] transition-transform group-hover:scale-105 duration-200">
                        <img src="{{ asset('images/logo.png') }}" alt="Heim Logo" class="w-full h-full object-cover rounded-full" />
                    </div>
                    <h1 class="mt-3 font-extrabold text-2xl text-[#155d49] tracking-tight">Heim</h1>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-widest">Coffee POS & Inventory</p>
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-4 px-8 py-8 bg-white shadow-xl shadow-[#155d49]/5 rounded-2xl border border-gray-100">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
