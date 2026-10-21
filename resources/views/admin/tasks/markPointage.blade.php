<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <x-favicon />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.Employee Tasks - DalyTrac') }}</title>
    @vite('resources/css/app.css')
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
    <style>
        /* Searchable filter selects (Tom Select, dashboard look) */
        .ts-wrapper.single .ts-control {
            border: 1px solid #e2e8f0 !important;
            border-radius: 0.5rem !important;
            padding: 0.5rem 0.75rem !important;
            font-size: 13px !important;
            background-color: #fff !important;
            min-height: 38px !important;
            color: #475569 !important;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05) !important;
        }
        .ts-wrapper.single.focus .ts-control {
            border-color: #2D7080 !important;
            box-shadow: 0 0 0 2px rgba(45, 112, 128, 0.25) !important;
        }
        .ts-dropdown {
            border: 1px solid #e2e8f0 !important;
            border-radius: 0.5rem !important;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,.1), 0 2px 4px -2px rgba(0,0,0,.1) !important;
        }
        .ts-dropdown .option {
            padding: 0.5rem 0.75rem !important;
            font-size: 13px !important;
        }
        .ts-dropdown .option.active {
            background-color: rgba(45, 112, 128, 0.10) !important;
            color: #0B2638 !important;
        }
    </style>
    @livewireStyles
    <meta name="csrf-token" content="{{ csrf_token() }}">
<style>
        select.form-select-custom {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 0.5rem center;
            background-repeat: no-repeat;
            background-size: 1.5em 1.5em;
            padding-right: 2.5rem;
        }
        select:invalid {
            color: #9ca3af;
        }
        option {
            color: #111827;
        }
        .project-link {
             color: #2563eb; /* blue-600 */
             font-weight: 500; /* medium */
        }
         .project-link:hover {
             color: #1d4ed8; /* blue-800 */
             text-decoration: underline;
         }
        .client-header-row {
            background-color: #f3f4f6; /* bg-gray-100 */
            border-top: 1px solid #e5e7eb; /* border-gray-200 */
            border-bottom: 1px solid #e5e7eb; /* border-gray-200 */
        }
        .client-header-row td {
            font-weight: 600; /* font-semibold */
            color: #1f2937; /* text-gray-800 */
        }
        /* Ensure hidden rows are actually hidden */
        #pointageTable tbody tr.hidden {
            display: none !important;
        }

        /* Mobile: convert rows to modern cards and add client collapse */
        @media (max-width: 1024px) {
            .pointage-table-wrapper {
                overflow-x: visible !important;
            }

            #pointageTable thead {
                display: none;
            }

            #pointageTable tbody {
                display: block;
                width: 100%;
            }

            /* Client header as accordion card */
            #pointageTable tbody tr.client-header-row {
                display: block;
                margin: 12px 12px 6px 12px;
                border-radius: 14px;
                border: 1px solid rgba(148, 163, 184, 0.55);
                background: linear-gradient(180deg, rgba(248,250,252,0.98), rgba(226,232,240,0.96));
                box-shadow: 0 18px 45px rgba(15,23,42,0.08), 0 3px 10px rgba(15,23,42,0.06);
            }

            #pointageTable tbody tr.client-header-row td {
                display: block;
                padding: 10px 14px !important;
                border: none;
            }

            /* Project rows as 2-col cards */
            #pointageTable tbody tr.project-row {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 10px;
                margin: 0 12px 16px 12px;
                padding: 12px;
                border: 1px solid rgba(148, 163, 184, 0.55);
                border-radius: 16px;
                background: linear-gradient(180deg, rgba(255,255,255,0.95), rgba(248,250,252,0.92));
                box-shadow: 0 18px 45px rgba(15,23,42,0.10), 0 3px 10px rgba(15,23,42,0.08);
            }

            #pointageTable tbody tr.project-row td {
                display: block;
                width: 100%;
                padding: 10px 12px !important;
                border: 1px solid rgba(226, 232, 240, 0.9);
                border-radius: 12px;
                background: rgba(255,255,255,0.78);
                text-align: left !important;
                white-space: normal !important;
                word-break: break-word;
            }

            #pointageTable tbody tr.project-row td:first-child {
                display: none !important;
            }

            #pointageTable tbody tr.project-row td[data-span="2"] {
                grid-column: span 2 / span 2;
            }

            #pointageTable tbody tr.project-row td[data-label]::before {
                content: attr(data-label);
                display: block;
                font-size: 11px;
                font-weight: 700;
                letter-spacing: .06em;
                text-transform: uppercase;
                color: #64748b; /* slate-500 */
                margin-bottom: 6px;
            }
        }
</style>
</head>
<body class="bg-gray-100 flex h-screen overflow-hidden font-sans">
    @if(Auth::user()->hasRole('Admin'))
        <x-dashboard.sidebar />
    @else
        <x-employees.sidebar />
    @endif
    
    <div class="flex flex-col flex-1 overflow-y-auto">
        <x-dashboard.navbar />
        
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-8 space-y-8">

            {{-- 
              MODIFICATION: 
              - Header is now a flex container to hold the title and the small duration card.
              - `items-start` is used to align the top of the title with the top of the card.
            --}}
            <div class="flex flex-wrap justify-between items-start gap-4">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">{{ __('messages.Employee Tasks') }}</h1>
                </div>
                
                {{-- 
                  MODIFICATION: 
                  - "Todays Duration" is now a small card placed on the right.
                  - Padding reduced to `p-4` (from p-5).
                  - Icon size reduced to `h-5 w-5` (from h-6 w-6).
                  - Text size for value reduced to `text-lg` (from text-xl).
                --}}
                <div class="bg-white rounded-lg shadow border border-gray-200 p-4 flex items-center space-x-3">
                    <div class="flex-shrink-0 bg-blue-100 rounded-full p-2"> {{-- Reduced padding --}}
                        {{-- Clock Icon --}}
                        <svg class="h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"> {{-- Reduced size --}}
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase font-medium">{{ __('messages.Todays Duration') }}</p>
                        @php
                            $today = \Carbon\Carbon::today()->toDateString();
                            $totalDurationAllTasks = App\Models\tasks_timer::where('employee_id', $employee_id)
                                                                ->whereDate('start_date', '=', $today)
                                                                ->whereNotNull('duration')
                                                                ->sum('duration');
                            $totalHours = intdiv($totalDurationAllTasks, 60);
                            $totalMinutes = $totalDurationAllTasks % 60;
                        @endphp
                        <p class="text-lg font-semibold text-gray-900"> {{-- Reduced text size --}}
                            {{ (isset($totalHours) && $totalHours > 0) || (isset($totalMinutes) && $totalMinutes > 0) ? $totalHours . 'h:' . sprintf('%02d', $totalMinutes) . 'm' : '00h:00m' }}
                        </p> 
                    </div>
                </div>
            </div>

            {{-- Session Alerts (Placed after the header section) --}}
            @if(session('alertmessage'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative flex justify-between items-center" role="alert">
                <span class="block sm:inline">{{ session('alertmessage') }}</span>
                <button type="button" onclick="this.parentElement.style.display='none';" class="p-1 -mr-1" aria-label="{{ __('messages.Close') }}">
                    <svg class="w-5 h-5 text-red-700" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            @elseif(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative flex justify-between items-center" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
                <button type="button" onclick="this.parentElement.style.display='none';" class="p-1 -mr-1" aria-label="{{ __('messages.Close') }}">
                    <svg class="w-5 h-5 text-green-700" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            @endif

            {{-- 2. Filters Section Card --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h6 class="text-base font-semibold text-gray-800 mb-4">{{ __('messages.Filters') }}</h6>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="clientFilter" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Select Client') }}</label>
                        <select id="clientFilter" class="form-select-custom block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3">
                            <option value="">{{ __('messages.All Clients') }}</option>
                            @php
                                $sortedClients = $clients->sortBy('name');
                            @endphp
                            @foreach($sortedClients as $client)
                            <option value="{{ strtoupper($client->name) }}">{{strtoupper($client->name)}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="taskSearchInput" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Search for projects') }}</label>
                        <input type="text" id="taskSearchInput" placeholder="{{ __('messages.Search for projects placeholder') }}" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3">
                    </div>
                </div>
            </div>

            {{-- 3. Table Section Card --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="pointage-table-wrapper overflow-x-auto">
                    <table id="pointageTable" class="w-full min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50"> 
                            <tr class="text-left">
                                <th scope="col" class="px-4 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider">{{ __('messages.Client Name header') }}</th>
                                <th scope="col" class="px-4 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider">{{ __('messages.Project Name header') }}</th>
                                <th scope="col" class="px-4 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider">{{ __('messages.Total Duration header') }}</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($clients as $client)
                                {{-- Ligne de l'en-tête du client --}}
                                <tr class="client-header-row text-left cursor-pointer select-none" data-client-name="{{ strtoupper($client->name) }}" aria-expanded="true"> 
                                    <td colspan="3" class="px-4 py-3"> 
                                        <div class="flex items-center justify-between gap-2">
                                            <span>{{strtoupper($client->name)}}</span>
                                            <svg class="client-chevron w-5 h-5 text-slate-500 transition-transform" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6" />
                                            </svg>
                                        </div>
                                    </td>
                                </tr>
                                
                                @php
                                    $clients_projects = App\Models\Project::where('entreprise_id', Auth::user()->entreprise_id)
                                        ->where(function($query) {
                                            $query->whereNull('AcceptedDevisProject')
                                                  ->orWhere('AcceptedDevisProject', '=', 1);
                                        })
                                        ->where('client_id', $client->id)
                                        ->where('archived', 0)
                                        ->get();
                                @endphp

                                @foreach($clients_projects as $project)
                                    @php
                                        $today = \Carbon\Carbon::today()->toDateString();
                                        $projectTasks = App\Models\Tasks::where('project_id', $project->id)->pluck('id');
                                        $totalProjectDuration = App\Models\tasks_timer::whereIn('task_id', $projectTasks)
                                            ->where('employee_id', $employee_id)
                                            ->whereDate('start_date', '=', $today)
                                            ->whereNotNull('duration')
                                            ->sum('duration');
                                        
                                        $hours = intdiv($totalProjectDuration, 60);
                                        $minutes = $totalProjectDuration % 60;
                                    @endphp
                                    
                                    {{-- Ligne du projet --}}
                                    <tr class="project-row text-left hover:bg-gray-50" 
                                        data-client-name="{{ strtoupper($client->name) }}" 
                                        data-project-name="{{ strtoupper($project->name) }}">
                                        
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500"></td> 
                                        
                                        <td data-label="{{ __('messages.Project Name header') }}" data-span="2" class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-800">
                                            @if(Auth::user()->hasRole('Admin') && Auth::user()->can('View project'))
                                                <a href="{{route('admin.projects.show', $project->id)}}" class="project-link">
                                                    {{strtoupper($project->name)}}
                                                </a>
                                            @elseif(Auth::user()->hasRole('Responsable de projet') && Auth::user()->can('View project'))
                                                <a href="{{route('Responsable_de_projet.projects.show', $project->id)}}" class="project-link">
                                                    {{strtoupper($project->name)}}
                                                </a>
                                            @elseif(Auth::user()->hasRole('Employee') && Auth::user()->can('View project'))
                                                <a href="{{route('employee.projects.show', $project->id)}}" class="project-link">
                                                    {{strtoupper($project->name)}}
                                                </a>
                                            @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire') && Auth::user()->can('View project'))
                                                <a href="{{route('Responsable_gestionnaire.projects.show', $project->id)}}" class="project-link">
                                                    {{strtoupper($project->name)}}
                                                </a>
                                            @else
                                                {{strtoupper($project->name)}}
                                            @endif
                                        </td>
                                        <td data-label="{{ __('messages.Total Duration header') }}" class="px-4 py-3 whitespace-nowrap text-sm text-gray-600 text-left">
                                           {{ $hours }}h:{{ sprintf('%02d', $minutes) }}m
                                        </td>
                                    </tr>
                                @endforeach
                            @empty
                                {{-- Empty state row (Inspired by Devis History) --}}
                                <tr>
                                    <td colspan="3" class="text-center px-4 py-6 text-gray-500">
                                        {{ __('messages.No clients or projects found') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            
        </main>
        
    </div>

    @livewireScripts

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const clientFilter = document.getElementById("clientFilter");
    const searchInput = document.getElementById("taskSearchInput");
    const tableBody = document.getElementById("pointageTable").getElementsByTagName("tbody")[0];
    const projectRows = tableBody.querySelectorAll(".project-row");
    const clientHeaderRows = tableBody.querySelectorAll(".client-header-row");

    function applyFilters() {
        const clientQuery = clientFilter.value.toLowerCase();
        const searchQuery = searchInput.value.toLowerCase();

        const visibleClients = new Set();

        // 1. Filter project rows
        projectRows.forEach(row => {
            const clientName = row.dataset.clientName.toLowerCase();
            const projectName = row.dataset.projectName.toLowerCase(); 

            const clientMatch = clientQuery === "" || clientName === clientQuery;
            const searchMatch = searchQuery === "" || projectName.includes(searchQuery);

            if (clientMatch && searchMatch) {
                row.style.display = "";
                visibleClients.add(row.dataset.clientName);
            } else {
                row.style.display = "none";
            }
        });

        // 2. Filter client header rows
        clientHeaderRows.forEach(row => {
            const clientName = row.dataset.clientName;
            if (visibleClients.has(clientName)) {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }
        });
    }

    // Add event listeners to both filters
    clientFilter.addEventListener("change", applyFilters);
    searchInput.addEventListener("keyup", applyFilters);

    // --- Client accordion (collapse by client name) ---
    function setClientExpanded(headerRow, expanded) {
        const clientName = headerRow.dataset.clientName;
        headerRow.setAttribute('aria-expanded', expanded ? 'true' : 'false');
        const chevron = headerRow.querySelector('.client-chevron');
        if (chevron) {
            chevron.classList.toggle('rotate-180', expanded);
        }
        projectRows.forEach(row => {
            if (row.dataset.clientName === clientName) {
                row.classList.toggle('hidden', !expanded);
            }
        });
    }

    function initClientAccordionForViewport() {
        const isMobile = window.matchMedia('(max-width: 1024px)').matches;
        clientHeaderRows.forEach(headerRow => {
            setClientExpanded(headerRow, !isMobile);
        });
    }

    clientHeaderRows.forEach(headerRow => {
        headerRow.addEventListener('click', () => {
            const expanded = headerRow.getAttribute('aria-expanded') === 'true';
            setClientExpanded(headerRow, !expanded);
        });
    });

    initClientAccordionForViewport();
    const mq = window.matchMedia('(max-width: 1024px)');
    if (mq.addEventListener) mq.addEventListener('change', initClientAccordionForViewport);
    else mq.addListener(initClientAccordionForViewport);
});
</script>
    {{-- Searchable filter selects (Tom Select). Syncs the original selects and
         fires their change events, so existing filter logic keeps working. --}}
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            ['clientFilter'].forEach(function (id) {
                var el = document.getElementById(id);
                if (el && window.TomSelect && !el.tomselect) {
                    new TomSelect(el, {
                        create: false,
                        sortField: null,
                        maxOptions: null,
                        allowEmptyOption: true,
                        placeholder: el.options[0] ? el.options[0].text : ''
                    });
                }
            });
        });
    </script>

</body> 
</html>
