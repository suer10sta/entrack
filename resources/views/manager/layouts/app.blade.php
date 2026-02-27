<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>DalyTrac Manager</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <div class="flex min-h-screen">
        {{-- Sidebar (sticky within full-page scroll) --}}
        @include('manager.sidebar')

        {{-- Main Wrapper (Navbar + Content) --}}
        <div class="flex flex-col flex-1">
            {{-- Navbar --}}
            @include('manager.navbar')

            {{-- Main Content Area --}}
            <main class="flex-1 overflow-x-hidden bg-[#eaebef] p-8">
                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>


