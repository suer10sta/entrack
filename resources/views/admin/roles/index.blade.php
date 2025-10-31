<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BizManager Dashboard - Roles</title>
    @vite('resources/css/app.css')
    @livewireStyles
</head>

<body class="bg-gradient-to-br from-slate-50 to-slate-100 flex h-screen overflow-hidden">

    {{-- Sidebar --}}
    <x-dashboard.sidebar />

    <div class="flex flex-col flex-1 overflow-y-auto">
        {{-- Navbar --}}
        <x-dashboard.navbar />

        {{-- Main Content --}}
        <main class="p-8 min-h-screen ml-0 lg:ml-64 transition-all duration-300">

            <div class="max-w-7xl mx-auto">

                {{-- Header --}}
                <div class="mb-8">
                    <h1 class="text-4xl font-bold text-gray-900 mb-2">Roles Management</h1>
                    <p class="text-gray-600">Manage user roles and their permissions</p>
                </div>

                {{-- Controls --}}
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">

                        {{-- Entries per page (placeholder UI, optional Livewire binding) --}}
                        <div class="flex items-center gap-2">
                            <label class="text-sm font-medium text-gray-700">Entries per page:</label>
                            <select
                                class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="5">5</option>
                                <option value="10" selected>10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                            </select>
                        </div>

                        {{-- Add Role --}}
                        <a href="{{ route('admin.roles.create') }}"
                           class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                            </svg>
                            Add Role
                        </a>
                    </div>

                    {{-- Search Bar --}}
                    <div class="relative">
                        <svg xmlns="http://www.w3.org/2000/svg"
                             class="absolute left-3 top-3 h-5 w-5 text-gray-400" fill="none"
                             viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 1010.5 18.5a7.5 7.5 0 006.15-3.85z"/>
                        </svg>
                        <input
                            id="searchInput"
                            type="text"
                            placeholder="Search roles..."
                            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            wire:model.live="search"
                        />
                    </div>
                </div>

                {{-- Table --}}
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full" id="rolesTable">
                            <thead>
                                <tr class="bg-gradient-to-r from-slate-50 to-slate-100 border-b border-gray-200">
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900">ROLE</th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900">PERMISSIONS</th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900">ACTIONS</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($roles as $role)
                                    <tr class="{{ $loop->even ? 'bg-gray-50' : 'bg-white' }} border-b border-gray-200 hover:bg-gray-50 transition-colors">
                                        {{-- Role name --}}
                                        <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $role->name }}</td>

                                        {{-- Permissions --}}
                                        <td class="px-6 py-4">
                                            @php
                                                $enterprisePermissions = \App\Models\EnterpriseRolePermission::where('entreprise_id', Auth::user()->entreprise_id)
                                                    ->where('role_id', $role->id)
                                                    ->with('permission')
                                                    ->get()
                                                    ->pluck('permission');
                                            @endphp
                                            <div class="flex flex-wrap gap-2">
                                                @if ($enterprisePermissions->count() > 0)
                                                    @foreach ($enterprisePermissions->take(5) as $per)
                                                        <span class="inline-block px-3 py-1 bg-cyan-100 text-cyan-700 text-xs font-semibold rounded-full">
                                                            {{ $per->name }}
                                                        </span>
                                                    @endforeach
                                                    @if ($enterprisePermissions->count() > 5)
                                                        <span class="inline-block px-3 py-1 bg-gray-100 text-gray-700 text-xs font-semibold rounded-full">
                                                            +{{ $enterprisePermissions->count() - 5 }} more
                                                        </span>
                                                    @endif
                                                @else
                                                    <span class="inline-block px-3 py-1 bg-red-100 text-red-700 text-xs font-semibold rounded-full">
                                                        No permissions
                                                    </span>
                                                @endif
                                            </div>
                                        </td>

                                        {{-- Actions --}}
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-2">
                                                {{-- Edit --}}
                                                @if (Auth::user()->hasRole('Admin'))
                                                    <a href="{{ route('admin.roles.edit', $role->id) }}"
                                                       class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                                                       title="Edit">
                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                             class="h-4 w-4" fill="none"
                                                             viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                  d="M16.862 4.487a2.25 2.25 0 113.182 3.182L7.5 20.212 3 21l.788-4.5L16.862 4.487z"/>
                                                        </svg>
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="px-6 py-4 border-t border-gray-200 flex flex-col sm:flex-row items-center justify-between bg-gray-50">
                      <span class="text-sm text-gray-600 mb-2 sm:mb-0">
                        
                      </span>
                      
                      <div class="flex items-center gap-2">
                        <button class="px-3 py-1 border border-gray-300 rounded-lg text-sm hover:bg-gray-100 transition-colors">
                          Previous
                        </button>
                        <button class="px-3 py-1 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700 transition-colors">1</button>
                        <button class="px-3 py-1 border border-gray-300 rounded-lg text-sm hover:bg-gray-100 transition-colors">
                          Next
                        </button>
                      </div>
                    </div>
                </div>
            </div>

        </main>
    </div>

    @livewireScripts
</body>

<script>
document.getElementById('searchInput').addEventListener('keyup', function() {
  const search = this.value.toLowerCase();
  document.querySelectorAll('#rolesTable tbody tr').forEach(row => {
    const roleName = row.querySelector('td:first-child').textContent.toLowerCase();
    row.style.display = roleName.includes(search) ? '' : 'none';
  });
});
</script>
</html>
