<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BizManager Dashboard - Add Role</title>
    
    {{-- Using Vite and Livewire styles from your target file --}}
    @vite('resources/css/app.css')
    @livewireStyles
</head>

<body class="bg-gradient-to-br from-slate-50 to-slate-100 flex h-screen overflow-hidden">

    {{-- 1. Using Blade Component for Sidebar --}}
    <x-dashboard.sidebar />

    <div class="flex flex-col flex-1 overflow-y-auto">
        {{-- 2. Using Blade Component for Navbar --}}
        <x-dashboard.navbar />

        {{-- 3. Main Content Area (Styled like your target file) --}}
        <main class="p-8 min-h-screen ml-0 lg:ml-64 transition-all duration-300">

            {{-- We use max-w-3xl for a form page, 7xl is too wide --}}
            <div class="max-w-3xl mx-auto">

                {{-- Header (Styled like your target file) --}}
                <div class="mb-8">
                    <h1 class="text-4xl font-bold text-gray-900 mb-2">Add New Role</h1>
                    <p class="text-gray-600">Create a new user role for your enterprise.</p>
                </div>

                

                @php
                    $storeRoute = '#'; // Default fallback
                    $cancelRoute = '#'; // Default fallback

                    if (Auth::user()->hasRole('Admin')) {
                        $storeRoute = route('admin.roles.store');
                        $cancelRoute = route('admin.index'); // Use your admin dashboard route
                    } elseif (Auth::user()->hasRole('Responsable de projet')) {
                        $storeRoute = route('Responsable_de_projet.roles.store');
                        $cancelRoute = route('Responsable_de_projet.index');
                    } elseif (Auth::user()->hasRole('Employee')) {
                        $storeRoute = route('employee.roles.store');
                        $cancelRoute = route('employee.index');
                    } elseif (Auth::user()->hasRole('Responsable de projet et gestionnaire')) {
                        $storeRoute = route('Responsable_gestionnaire.roles.store');
                        $cancelRoute = route('Responsable_gestionnaire.index');
                    }
                @endphp

                {{-- 5. Form Card (Styled like your target file's cards) --}}
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    
                    {{-- The form now uses the $storeRoute variable --}}
                    <form method="POST" action="{{ $storeRoute }}" autocomplete="off">
                        @csrf
                        
                        <div class="p-6">
                            {{-- Role Name Field (Styled with Tailwind) --}}
                            <div class="mb-6">
                                <label for="role_name" class="block text-sm font-medium text-gray-700 mb-2">Role Name</label>
                                <input 
                                    type="text" 
                                    name="name" 
                                    id="role_name"
                                    placeholder="e.g., Project Manager"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    autocomplete="off"
                                />
                                @error('name')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        {{-- 6. Form Actions (Buttons) --}}
                        <div class="flex items-center justify-end gap-4 border-t border-gray-200 bg-gray-50 p-6 rounded-b-lg">
                            
                            {{-- Cancel Button (Styled with Tailwind) --}}
                            <a href="{{ $cancelRoute }}"
                               class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors text-sm font-medium">
                                Cancel
                            </a>
                            
                            {{-- Create Button (Styled like your target file's "Add" button) --}}
                            <button 
                                type="submit"
                                class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                </svg>
                                Create Role
                            </button>
                        </div>

                    </form>
                </div> {{-- End Form Card --}}

            </div> {{-- End max-w-3xl --}}
        </main>
    </div> {{-- End flex-col --}}

    {{-- Using Livewire scripts from your target file --}}
    @livewireScripts
    {{-- The old autocomplete JS is no longer needed, as we use the 'autocomplete="off"' HTML attribute --}}
</body>
</html>