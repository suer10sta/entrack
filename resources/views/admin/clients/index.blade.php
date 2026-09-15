<!DOCTYPE html>
<html lang="en"> 
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.Clients') }} - DalyTrac</title> 
    @vite('resources/css/app.css')
    @livewireStyles

    <style>
        /* Mobile: show table rows as stacked cards (no horizontal scroll) */
        @media (max-width: 1024px) {
            #clientsTable thead {
                display: none;
            }
            #clientsTable tbody {
                display: block;
            }
            #clientsTable tbody tr {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 10px;
                margin-bottom: 16px;
                border: 1px solid rgba(148, 163, 184, 0.55); /* slate-400/55 */
                border-radius: 16px;
                background: linear-gradient(180deg, rgba(255,255,255,0.98) 0%, rgba(248,250,252,0.98) 100%);
                overflow: hidden;
                box-shadow:
                    0 12px 30px rgba(15, 23, 42, 0.08),
                    0 2px 6px rgba(15, 23, 42, 0.06);
                transition: transform 160ms ease, box-shadow 160ms ease, border-color 160ms ease;
                padding: 12px;
            }
            #clientsTable tbody tr:hover {
                transform: translateY(-1px);
                border-color: rgba(59, 130, 246, 0.45); /* blue-500/45 */
                box-shadow:
                    0 16px 40px rgba(15, 23, 42, 0.10),
                    0 3px 10px rgba(15, 23, 42, 0.08);
            }
            #clientsTable tbody td {
                display: block;
                width: 100%;
                padding: 10px 10px;
                border: 1px solid rgba(226, 232, 240, 0.9); /* slate-200 */
                border-radius: 12px;
                background: rgba(255,255,255,0.75);
                text-align: left !important;
            }
            #clientsTable tbody td[data-span="2"] {
                grid-column: 1 / -1;
            }
            #clientsTable tbody td[data-label]::before {
                content: attr(data-label);
                display: block;
                font-size: 11px;
                font-weight: 700;
                letter-spacing: .04em;
                text-transform: uppercase;
                color: #64748b; /* slate-500 */
                margin-bottom: 4px;
            }
            /* Make values feel more “card-like” */
            #clientsTable tbody td[data-label] {
                color: #0f172a; /* slate-900 */
            }
            #clientsTable tbody td a {
                font-weight: 700;
            }

            /* Mobile accordion: collapsed row shows only client name */
            #clientsTable tbody tr.client-row.is-collapsed td:not(.client-toggle-cell) {
                display: none !important;
            }
            #clientsTable tbody tr.client-row td.client-toggle-cell {
                cursor: pointer;
                user-select: none;
            }
            /* We render the label inline in markup for this cell */
            #clientsTable tbody tr.client-row td.client-toggle-cell[data-label]::before {
                content: none !important;
            }
            #clientsTable tbody tr.client-row td.client-toggle-cell .client-chevron {
                transition: transform 180ms ease;
            }
            #clientsTable tbody tr.client-row[aria-expanded="true"] td.client-toggle-cell .client-chevron {
                transform: rotate(180deg);
            }
            /* When collapsed, hide the inline "Name" label (show value only) */
            #clientsTable tbody tr.client-row.is-collapsed td.client-toggle-cell .client-mobile-label {
                display: none !important;
            }
        }
    </style>
    
</head>
<body class="bg-gray-50 flex h-screen overflow-hidden">

    {{-- Sidebar Component --}}
    @if(Auth::user()->hasRole('Admin'))
        <x-dashboard.sidebar />
    @else
        <x-employees.sidebar />
    @endif

    {{-- Main Wrapper (Navbar + Content) --}}
    <div class="flex flex-col flex-1 overflow-y-auto">

        {{-- Navbar Component --}}
        <x-dashboard.navbar />

        {{-- Main Content Area --}}
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-[#eaebef] p-4 sm:p-6 lg:p-8">

            {{-- Container for the table --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">

                {{-- Header Section --}}
                <div class="p-4 sm:p-6 border-b border-gray-200 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <h6 class="text-lg font-semibold text-gray-900">{{ __('messages.Clients Table') }}</h6>

                    <div class="flex flex-col sm:flex-row sm:flex-wrap gap-2 w-full sm:w-auto">
                        {{-- Add Client Button --}}
                        @can('Add client')
                            @php
                                $createRoute = '#'; // Default fallback
                                if (Auth::user()->hasRole('Responsable de projet')) $createRoute = route('Responsable_de_projet.clients.create');
                                elseif (Auth::user()->hasRole('Responsable de projet et gestionnaire')) $createRoute = route('Responsable_gestionnaire.clients.create');
                                elseif (Auth::user()->hasRole('Admin')) $createRoute = route('admin.clients.create');
                            @endphp
                            <a href="{{ $createRoute }}" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg flex items-center justify-center gap-1.5 w-full sm:w-auto">
                                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                                {{ __('messages.Add Client') }}
                            </a>
                        @endcan

                        {{-- Report Button --}}
                        @php
                            $reportRoute = '#'; // Default fallback
                            if (Auth::user()->hasRole('Responsable de projet')) $reportRoute = route('Responsable_de_projet.Clientrepports.downloadRepports');
                            elseif (Auth::user()->hasRole('Responsable de projet et gestionnaire')) $reportRoute = route('Responsable_gestionnaire.Clientrepports.downloadRepports');
                            elseif (Auth::user()->hasRole('Admin')) $reportRoute = route('admin.Clientrepports.downloadRepports');
                        @endphp
                        <a href="{{ $reportRoute }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 border border-gray-300 text-sm font-medium px-4 py-2 rounded-lg flex items-center justify-center gap-1.5 w-full sm:w-auto">
                           <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                           {{ __('messages.Client Reports PDF') }}
                        </a>
                    </div>
                </div>

                {{-- Session Message Alert --}}
                @if(session('message'))
                    <div class="m-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded relative" role="alert">
                        <span class="block sm:inline">{{ session('message') }}</span>
                        <button type="button" onclick="this.parentElement.style.display='none'" class="absolute top-0 bottom-0 right-0 px-4 py-3 text-green-700 hover:text-green-900 font-bold text-xl" aria-label="Close">
                           &times; {{-- Better looking 'X' --}}
                        </button>
                    </div>
                @endif

                {{-- Table Wrapper --}}
                <div class="p-4 sm:p-6">
    <div>
        <table id="clientsTable" class="w-full text-left border-collapse table-auto">
            <thead class="border-b border-gray-200 bg-gray-50">
                <tr>
                    {{-- Optimized column widths to fit all columns on screen --}}
                    <th class="px-3 py-3 text-sm font-bold text-gray-600 uppercase tracking-wider text-center whitespace-nowrap">N°</th>
                    <th class="px-3 py-3 text-sm font-bold text-gray-600 uppercase tracking-wider text-left whitespace-nowrap">{{ __('messages.Name') }}</th>
                    <th class="px-3 py-3 text-sm font-bold text-gray-600 uppercase tracking-wider text-left whitespace-nowrap">{{ __('messages.Email') }}</th>
                    <th class="px-3 py-3 text-sm font-bold text-gray-600 uppercase tracking-wider text-left whitespace-nowrap">Phone</th>
                    <th class="px-3 py-3 text-sm font-bold text-gray-600 uppercase tracking-wider text-left whitespace-nowrap">{{ __('messages.Billing Address') }}</th>
                    <th class="px-3 py-3 text-sm font-bold text-gray-600 uppercase tracking-wider text-left whitespace-nowrap">{{ __('messages.VAT') }}</th>
                    <th class="px-3 py-3 text-sm font-bold text-gray-600 uppercase tracking-wider text-center whitespace-nowrap">{{ __('messages.Due Date') }}</th>
                    <th class="px-3 py-3 text-sm font-bold text-gray-600 uppercase tracking-wider text-left whitespace-nowrap">{{ __('messages.Address') }}</th>
                    <th class="px-3 py-3 text-sm font-bold text-gray-600 uppercase tracking-wider text-left whitespace-nowrap">{{ __('messages.City') }}</th>
                    <th class="px-3 py-3 text-sm font-bold text-gray-600 uppercase tracking-wider text-center whitespace-nowrap">{{ __('messages.Zip') }}</th>
                    <th class="px-3 py-3 text-sm font-bold text-gray-600 uppercase tracking-wider text-left whitespace-nowrap">{{ __('messages.Country') }}</th>
                    @can('Delete client')
                    <th class="px-3 py-3 text-sm font-bold text-gray-600 uppercase tracking-wider text-center whitespace-nowrap">{{ __('messages.Action') }}</th>
                    @endcan
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($clients as $client)
                <tr class="client-row hover:bg-gray-50" data-client-row aria-expanded="true">
                    
                    {{-- ID --}}
                    <td data-label="N°" class="px-3 py-5 text-sm text-gray-700 font-medium text-center">
                        {{$client->client_number}}
                    </td>
                    
                    {{-- Name --}}
                    <td data-label="{{ __('messages.Name') }}" data-span="2" class="px-3 py-5 text-sm text-gray-700 font-medium whitespace-normal break-words client-toggle-cell" data-client-toggle>
                         @php
                            // Prefer a concrete route per role; if none, fall back to admin route
                            $roleRouteMap = [
                                'Responsable de projet' => 'Responsable_de_projet.clients.show',
                                'Responsable de projet et gestionnaire' => 'Responsable_gestionnaire.clients.show',
                                'Admin' => 'admin.clients.show',
                            ];

                            $showRoute = null;
                            foreach ($roleRouteMap as $role => $routeName) {
                                if (Auth::user()->hasRole($role) && Route::has($routeName)) {
                                    $showRoute = route($routeName, $client->id);
                                    break;
                                }
                            }

                            if (!$showRoute && Route::has('admin.clients.show')) {
                                $showRoute = route('admin.clients.show', $client->id);
                            }
                         @endphp

                         {{-- Desktop: keep original simple link layout --}}
                         @if($showRoute)
                            <a href="{{ $showRoute }}" class="text-blue-600 hover:underline hidden lg:inline">
                                {{$client->name}}
                            </a>
                         @else
                            <span class="hidden lg:inline">{{$client->name}}</span>
                         @endif

                         {{-- Mobile accordion header --}}
                         <div class="flex items-start justify-between gap-3 lg:hidden">
                             <div class="flex items-baseline gap-2 min-w-0">
                                 <span class="client-mobile-label text-[11px] leading-none font-bold tracking-[.06em] uppercase text-slate-500 whitespace-nowrap">
                                     {{ __('messages.Name') }}
                                 </span>
                                 @if($showRoute)
                                     <a href="{{ $showRoute }}" class="text-blue-600 hover:underline truncate leading-none">
                                         {{$client->name}}
                                     </a>
                                 @else
                                     <span class="truncate leading-none">{{$client->name}}</span>
                                 @endif
                             </div>
                             <span class="shrink-0 mt-0.5 text-slate-400">
                                 <svg class="client-chevron w-5 h-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                     <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                                 </svg>
                             </span>
                         </div>
                    </td>

                    {{-- Email --}}
                    <td data-label="{{ __('messages.Email') }}" class="px-3 py-5 text-sm text-gray-700 whitespace-normal break-all">
                        {{$client->email}}
                    </td>

                    {{-- Phone --}}
                    <td data-label="Phone" class="px-2 py-2.5 text-sm text-gray-700 whitespace-nowrap">
                        {{$client->phone}}
                    </td>

                    {{-- Billing Address --}}
                    <td data-label="{{ __('messages.Billing Address') }}" data-span="2" class="px-2 py-2.5 text-sm text-gray-700 whitespace-normal break-words">
                        {{$client->Billing_address}}
                    </td>

                    {{-- VAT --}}
                    <td data-label="{{ __('messages.VAT') }}" class="px-2 py-2.5 text-sm text-gray-700 text-center whitespace-nowrap">
                        {{$client->TVA}}
                    </td>

                    {{-- Due Date --}}
                    <td data-label="{{ __('messages.Due Date') }}" class="px-2 py-2.5 text-sm text-gray-700 text-center whitespace-nowrap">
                        {{$client->due_date}}
                    </td>

                    {{-- Address --}}
                    <td data-label="{{ __('messages.Address') }}" data-span="2" class="px-2 py-2.5 text-sm text-gray-700 whitespace-normal break-words">
                        {{$client->address}}
                    </td>

                    {{-- City --}}
                    <td data-label="{{ __('messages.City') }}" class="px-2 py-2.5 text-sm text-gray-700 whitespace-normal">
                        {{$client->city}}
                    </td>

                    {{-- Zip --}}
                    <td data-label="{{ __('messages.Zip') }}" class="px-2 py-2.5 text-sm text-gray-700 text-center whitespace-nowrap">
                        {{$client->zip}}
                    </td>

                    {{-- Country --}}
                    <td data-label="{{ __('messages.Country') }}" class="px-2 py-2.5 text-sm text-gray-700 whitespace-normal">
                        {{$client->country}}
                    </td>

                    @can('Delete client')
                    <td data-label="{{ __('messages.Action') }}" data-span="2" class="px-2 py-2.5 text-sm text-gray-700 text-center">
                        @php
                            $client_has_invoice = \App\Models\invoices_history::where('client_id', $client->id)->exists();
                        @endphp
                        @if($client_has_invoice)
                           <div class="bg-red-50 border border-red-200 text-red-600 px-2 py-1 rounded text-[10px] font-medium leading-tight">
                                {{ __('messages.Cannot delete: Has invoices') }}
                           </div>
                        @else
                            @php
                                $deleteRoute = '#';
                                if (Auth::user()->hasRole('Responsable de projet')) $deleteRoute = route('Responsable_de_projet.clients.destroy', $client->id);
                                elseif (Auth::user()->hasRole('Responsable de projet et gestionnaire')) $deleteRoute = route('Responsable_gestionnaire.clients.destroy', $client->id);
                                elseif (Auth::user()->hasRole('Admin')) $deleteRoute = route('admin.clients.destroy', $client->id);
                            @endphp
                            <form action="{{ $deleteRoute }}" method="post" onsubmit="return confirm('{{ __('messages.Are you sure?') }}');" class="inline-block">
                                @csrf
                                @method('DELETE')
                                <button type="submit" title="{{ __('messages.Delete') }}"
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-white bg-red-600 hover:bg-red-700">
                                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </form>
                        @endif
                    </td>
                    @endcan
                </tr>
                @empty
                <tr>
                    <td colspan="12" class="px-6 py-4 text-center text-sm text-gray-500">
                         {{ __('messages.No clients found.') }}
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
            </div>

        </main>
    </div>

    @livewireScripts
    {{-- Include dashboard_layouts.scripts if needed --}}
    {{-- @include('dashboard_layouts.scripts') --}}

    {{-- Keep jQuery and inline editing script --}}
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script>
    $(document).ready(function(){
        $(".editable").dblclick(function(){
            if ($(this).find('input').length > 0) {
                return;
            }

            var originalContent = $(this).text().trim();
            var cellWidth = $(this).outerWidth();

            $(this).html("<input type='text' value='" + originalContent + "' class='w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-1' style='min-width: " + cellWidth + "px;' />");

            var input = $(this).children('input').first();
            input.focus();
            input[0].select();

            input.blur(function(){
                var newContent = $(this).val();
                var $parentCell = $(this).parent();
                var clientId = $parentCell.data('id');
                var columnName = $parentCell.data('column');

                $parentCell.text(originalContent);

                $.ajax({
                    url: '/admin/clients/updateClient', 
                    type: 'POST',
                    data: {
                        content: newContent,
                        id: clientId,
                        column: columnName,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        $parentCell.text(newContent);
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        $parentCell.text(originalContent);
                        alert('{{ __('messages.Error updating client') }}: ' + errorThrown);
                        console.error("AJAX Error:", textStatus, errorThrown, jqXHR.responseText);
                    }
                });
            });

            input.keypress(function(e){
                if(e.which == 13){ 
                    $(this).blur();
                    return false;
                }
            });
        });
    });
    </script>

    <script>
        // Mobile accordion per client row (show only Name when collapsed)
        document.addEventListener('DOMContentLoaded', function () {
            const mql = window.matchMedia('(max-width: 1024px)');

            function setRowCollapsed(row, collapsed) {
                if (!row) return;
                row.classList.toggle('is-collapsed', collapsed);
                row.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
            }

            function initAccordions() {
                const rows = document.querySelectorAll('#clientsTable tbody tr.client-row');
                const isMobile = mql.matches;
                rows.forEach(row => setRowCollapsed(row, isMobile));
            }

            document.addEventListener('click', function (e) {
                const toggleCell = e.target.closest('[data-client-toggle]');
                if (!toggleCell) return;

                // Let normal link clicks navigate without toggling.
                if (e.target.closest('a')) return;

                const row = toggleCell.closest('tr.client-row');
                if (!row) return;

                const collapsed = row.classList.contains('is-collapsed');
                setRowCollapsed(row, !collapsed);
            });

            if (mql.addEventListener) mql.addEventListener('change', initAccordions);
            else if (mql.addListener) mql.addListener(initAccordions);

            initAccordions();
        });
    </script>

</body>
</html>
