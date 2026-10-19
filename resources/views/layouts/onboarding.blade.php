<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <x-favicon />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'DalyTrac')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite('resources/css/app.css')
    @stack('styles')
</head>
<body class="bg-[#f8fafc] text-slate-900 antialiased min-h-screen">
    <div class="min-h-screen flex flex-col">
        <header class="w-full">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 pt-6 flex items-center gap-2.5">
                <x-application-mark />
                <x-application-logo class="text-[1.15rem]" />
            </div>
        </header>

        <main class="flex-1 w-full max-w-6xl mx-auto px-4 sm:px-6 pb-10 flex flex-col justify-center">
            @yield('content')
        </main>

        <footer class="pb-6 text-center text-[11.5px] text-slate-400">
            DalyTrac
        </footer>
    </div>

    {{-- Shared toast notifications (same system as the app, without the dashboard layout) --}}
    <x-app-toast />
    @stack('scripts')
</body>
</html>
