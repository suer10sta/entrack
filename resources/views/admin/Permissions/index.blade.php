<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <x-favicon />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.Permissions Page Title') }}</title>
    @vite('resources/css/app.css')
</head>
<body class="bg-gradient-to-br from-slate-50 to-slate-100 flex h-screen overflow-hidden">

    {{-- Sidebar --}}
    @if(Auth::user()->hasRole('Admin'))
        <x-dashboard.sidebar />
    @else
        <x-employees.sidebar />
    @endif

    <div class="flex flex-col flex-1 overflow-y-auto">
        {{-- Navbar --}}
        <x-dashboard.navbar />

        {{-- Main Content --}}
        <main class="flex-1 p-8">
            <div class="max-w-6xl mx-auto">
                {{-- Header --}}
                <div class="mb-8">
                    <h1 class="text-4xl font-bold text-gray-900 mb-2">{{ __('messages.Permissions Management') }}</h1>
                    <p class="text-gray-600">{{ __('messages.Permissions Management subtitle') }}</p>
                </div>

                {{-- Flash message --}}
                @if(session('message'))
                    <div class="mb-6 bg-green-100 border border-green-200 text-green-800 text-sm rounded-lg px-4 py-3 flex items-center justify-between">
                        <span>{{ session('message') }}</span>
                        <button type="button" class="text-green-800 font-semibold" data-bs-dismiss="alert" aria-label="Close">×</button>
                    </div>
                @endif

                {{-- Add Permission --}}
                <div class="mb-6 flex justify-end">
                    <a href="{{ route('admin.permissions.create') }}"
                        class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        {{ __('messages.Add Permission') }}
                    </a>
                </div>

                {{-- Search Bar --}}
                <div class="mb-6">
                    <input
                        type="text"
                        id="permissionSearch"
                        placeholder="{{ __('messages.Search permissions placeholder') }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                </div>

                {{-- Permissions List --}}
                <div class="space-y-6" id="permissionsList">
                    @php
                        $groupedPermissions = [];
                        $categoryLabels = [
                            'employee' => __('messages.Employees Management'),
                            'client' => __('messages.Client Management'),
                            'project' => __('messages.Project Management'),
                            'invoice' => __('messages.Invoice Management'),
                            'devis' => __('messages.Devis Management'),
                            'role' => __('messages.Role Management'),
                            'permission' => __('messages.Permission Management'),
                            'user' => __('messages.User Management'),
                        ];

                        foreach($permissions as $permission) {
                            $parts = explode(' ', strtolower($permission->name));
                            $entity = $parts[1] ?? 'other';
                            $groupedPermissions[$entity][] = $permission;
                        }
                    @endphp

                    @foreach($groupedPermissions as $entity => $perms)
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden category-group">
                            <div class="bg-gradient-to-r from-slate-50 to-slate-100 px-6 py-4 border-b border-gray-200">
                                <h2 class="text-lg font-semibold text-gray-900">
                                    {{ $categoryLabels[$entity] ?? ucfirst($entity) . ' Management' }}
                                </h2>
                            </div>

                            <div class="divide-y divide-gray-200">
                                @foreach($perms as $permission)
                                    <div class="px-6 py-4 hover:bg-gray-50 transition-colors flex items-center justify-between permission-item">
                                        <div class="flex-1">
                                            @php
                                                $permissionRawName = trim($permission->name);
                                                $permissionCandidates = array_values(array_unique([
                                                    $permissionRawName,
                                                    strtolower($permissionRawName),
                                                    strtoupper($permissionRawName),
                                                    str_replace(['_', '-'], ' ', $permissionRawName),
                                                    ucwords(strtolower(str_replace(['_', '-'], ' ', $permissionRawName))),
                                                    str_replace(' ', '_', $permissionRawName),
                                                    strtolower(str_replace(' ', '_', $permissionRawName)),
                                                    strtoupper(str_replace(' ', '_', $permissionRawName)),
                                                ]));
                                                $permissionLabel = $permissionRawName;
                                                foreach ($permissionCandidates as $candidate) {
                                                    $candidateKey = 'messages.' . $candidate;
                                                    $translated = __($candidateKey);
                                                    if ($translated !== $candidateKey) {
                                                        $permissionLabel = $translated;
                                                        break;
                                                    }
                                                }
                                            @endphp
                                            <h3 class="text-sm font-semibold text-gray-900 mb-1 permission-name">
                                                {{ strtoupper($permissionLabel) }}
                                            </h3>
                                            <p class="text-xs text-gray-500">
                                                @php
                                                    $action = explode(' ', strtolower($permission->name))[0];
                                                    $entityName = explode(' ', strtolower($permission->name))[1] ?? '';
                                                    $desc = [
                                                        'add' => __('messages.Permission add description', ['entity' => $entityName . 's']),
                                                        'delete' => __('messages.Permission delete description', ['entity' => $entityName . 's']),
                                                        'update' => __('messages.Permission update description', ['entity' => $entityName]),
                                                        'create' => __('messages.Permission create description', ['entity' => $entityName . 's']),
                                                        'view' => __('messages.Permission view description', ['entity' => $entityName]),
                                                        'manage' => __('messages.Permission manage description', ['entity' => $entityName . 's']),
                                                    ];
                                                    echo $desc[$action] ?? __('messages.Permission generic description', ['name' => $permission->name]);
                                                @endphp
                                            </p>
                                        </div>

                                        <div class="flex items-center gap-2 ml-4">
                                            <a href="{{ route('admin.permissions.edit', $permission->id) }}" 
                                               class="p-1.5 rounded-md bg-blue-600 text-white hover:bg-blue-700"
                                               title="{{ __('messages.Edit') }}">
                                               <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                            </a>
                                            <form action="{{ route('admin.permissions.destroy', $permission->id) }}" 
                                                  method="POST" 
                                                  onsubmit="return confirm('{{ __('messages.Delete permission confirm') }}');" 
                                                  class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="p-1.5 rounded-md bg-red-600 text-white hover:bg-red-700"
                                                        title="{{ __('messages.Delete') }}">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Dynamic Pagination Info + Controls --}}
                <div id="paginationInfo" class="text-sm text-gray-600 text-center mt-6"></div>
                <div id="paginationContainer" class="flex justify-center mt-4"></div>
            </div>
        </main>
    </div>

    <script>
    const searchInput = document.getElementById('permissionSearch');
    const permissionItems = Array.from(document.querySelectorAll('.permission-item'));
    const categoryGroups = document.querySelectorAll('.category-group');
    const info = document.getElementById('paginationInfo');
    const paginationContainer = document.getElementById('paginationContainer');

    let currentPage = 1;
    const itemsPerPage = 10;
    let filteredItems = [];

    function filterItems() {
        const term = searchInput.value.toLowerCase();
        
        // Reset all items to be visible first
        permissionItems.forEach(item => {
            const name = item.querySelector('.permission-name').textContent.toLowerCase();
            if (name.includes(term)) {
                item.classList.remove('filtered-out');
            } else {
                item.classList.add('filtered-out');
            }
        });

        // Update filtered items array
        filteredItems = permissionItems.filter(item => !item.classList.contains('filtered-out'));

        // Hide empty category groups
        categoryGroups.forEach(group => {
            const visibleItems = group.querySelectorAll('.permission-item:not(.filtered-out)');
            group.style.display = visibleItems.length > 0 ? '' : 'none';
        });

        // Reset to page 1 and show results
        showPage(1);
    }

    function showPage(page) {
        const totalPages = Math.ceil(filteredItems.length / itemsPerPage);
        
        // Validate page number
        if (page < 1) page = 1;
        if (page > totalPages && totalPages > 0) page = totalPages;
        if (totalPages === 0) page = 1;
        
        currentPage = page;

        // Hide all items first
        permissionItems.forEach(item => {
            item.style.display = 'none';
        });

        // Show only items for current page
        const start = (page - 1) * itemsPerPage;
        const end = start + itemsPerPage;
        
        filteredItems.slice(start, end).forEach(item => {
            item.style.display = '';
        });

        // Update category group visibility after pagination
        categoryGroups.forEach(group => {
            const visibleItems = group.querySelectorAll('.permission-item:not([style*="display: none"]):not(.filtered-out)');
            group.style.display = visibleItems.length > 0 ? '' : 'none';
        });

        // Update info text
        const displayStart = filteredItems.length ? start + 1 : 0;
        const displayEnd = Math.min(end, filteredItems.length);
        info.textContent = `Showing ${displayStart}–${displayEnd} of ${filteredItems.length} permissions`;

        // Build pagination buttons
        renderPagination(totalPages);
    }

    function renderPagination(totalPages) {
        if (totalPages <= 1) {
            paginationContainer.innerHTML = '';
            return;
        }

        let html = '<div class="flex gap-1">';
        
        // Previous button
        html += `<button ${currentPage === 1 ? 'disabled' : ''} onclick="showPage(${currentPage - 1})" 
                 class="px-3 py-1 border rounded transition ${currentPage === 1 ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : 'hover:bg-gray-100'}">
                 &lt;
                 </button>`;
        
        // Page numbers
        for (let i = 1; i <= totalPages; i++) {
            // Show first, last, current, and adjacent pages
            if (i === 1 || i === totalPages || (i >= currentPage - 1 && i <= currentPage + 1)) {
                html += `<button onclick="showPage(${i})" 
                         class="px-3 py-1 border rounded transition ${i === currentPage ? 'bg-blue-600 text-white' : 'hover:bg-gray-100'}">
                         ${i}
                         </button>`;
            } else if (i === currentPage - 2 || i === currentPage + 2) {
                html += `<span class="px-2 py-1">...</span>`;
            }
        }
        
        // Next button
        html += `<button ${currentPage === totalPages ? 'disabled' : ''} onclick="showPage(${currentPage + 1})" 
                 class="px-3 py-1 border rounded transition ${currentPage === totalPages ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : 'hover:bg-gray-100'}">
                 &gt;
                 </button>`;
        
        html += '</div>';
        paginationContainer.innerHTML = html;
    }

    // Event listeners
    searchInput.addEventListener('input', filterItems);
    
    // Initialize on page load
    document.addEventListener('DOMContentLoaded', () => {
        filterItems();
    });
</script>

</body>
</html>
