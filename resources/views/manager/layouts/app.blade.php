<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>DalyTrac Manager</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 flex h-screen overflow-hidden">
    {{-- Sidebar --}}
    @include('manager.sidebar')

    {{-- Main Wrapper (Navbar + Content) --}}
    <div class="flex flex-col flex-1 overflow-y-auto">
        {{-- Navbar --}}
        @include('manager.navbar')

        {{-- Main Content Area --}}
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-[#eaebef] p-8">
            @yield('content')
        </main>
    </div>
    @stack('scripts')
</body>
</html>


