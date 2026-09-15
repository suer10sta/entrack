<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.Projects - DalyTrac') }}</title> {{-- Updated title --}}
    @vite('resources/css/app.css')
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
    @livewireStyles
    {{-- Include dashboard_layouts.head if needed --}}
    {{-- @include('dashboard_layouts.head') --}}

    <style>
        /* Tom Select custom styling overrides (same as pointage) */
        .ts-wrapper.single .ts-control {
            border: 1px solid #e2e8f0 !important;
            border-radius: 0.5rem !important;
            padding: 0.5rem 0.75rem !important;
            font-size: 0.875rem !important;
            background-color: #fff !important;
            min-height: 38px !important;
            color: #475569 !important;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05) !important;
        }
        .ts-wrapper.single .ts-control:focus,
        .ts-wrapper.single.focus .ts-control {
            border-color: #3b82f6 !important;
            box-shadow: 0 0 0 1px #3b82f6 !important;
        }
        .ts-dropdown {
            border: 1px solid #e2e8f0 !important;
            border-radius: 0.5rem !important;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,.1), 0 2px 4px -2px rgba(0,0,0,.1) !important;
            z-index: 50 !important;
        }
        .ts-dropdown .option {
            padding: 0.5rem 0.75rem !important;
            font-size: 0.875rem !important;
            white-space: nowrap !important;
        }
        .ts-wrapper .ts-control {
            white-space: nowrap !important;
        }
        .ts-dropdown .option.active {
            background-color: #eff6ff !important;
            color: #1e40af !important;
        }
        .ts-dropdown .option:hover {
            background-color: #f8fafc !important;
        }
        .ts-control input {
            font-size: 0.875rem !important;
        }
        /* Mobile: projects table becomes modern 2-column cards (no horizontal scroll) */
        @media (max-width: 1024px) {
            .projects-table-wrapper {
                overflow-x: visible !important;
            }

            #projectsTable thead {
                display: none;
            }

            #projectsTable tbody {
                display: block;
            }

            /* Client header row acts like a section label */
            #projectsTable tbody tr.client-header {
                display: block;
                margin: 18px 0 10px;
                background: transparent !important;
            }
            #projectsTable tbody tr.client-header td {
                display: block;
                width: 100%;
                padding: 0 6px 8px;
                border: 0;
                background: transparent;
                text-align: left !important;
            }
            #projectsTable tbody tr.client-header .client-header-card {
                display: block;
                padding: 10px 12px;
                border-radius: 14px;
                background: linear-gradient(180deg, rgba(243,244,246,0.95) 0%, rgba(229,231,235,0.95) 100%);
                border: 1px solid rgba(203, 213, 225, 0.9); /* slate-300 */
                box-shadow: 0 1px 2px rgba(15, 23, 42, 0.06);
            }

            /* Actual project rows become cards */
            #projectsTable tbody tr.project-row {
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
            #projectsTable tbody tr.project-row:hover {
                transform: translateY(-1px);
                border-color: rgba(59, 130, 246, 0.45); /* blue-500/45 */
                box-shadow:
                    0 16px 40px rgba(15, 23, 42, 0.10),
                    0 3px 10px rgba(15, 23, 42, 0.08);
            }

            #projectsTable tbody tr.project-row td {
                display: block;
                width: 100%;
                padding: 10px 10px;
                border: 1px solid rgba(226, 232, 240, 0.9); /* slate-200 */
                border-radius: 12px;
                background: rgba(255,255,255,0.75);
                text-align: left !important;
                white-space: normal !important;
                color: #0f172a; /* slate-900 */
            }

            #projectsTable tbody tr.project-row td[data-span="2"] {
                grid-column: 1 / -1;
            }

            #projectsTable tbody tr.project-row td[data-label]::before {
                content: attr(data-label);
                display: block;
                font-size: 11px;
                font-weight: 700;
                letter-spacing: .04em;
                text-transform: uppercase;
                color: #64748b; /* slate-500 */
                margin-bottom: 4px;
            }

            /* Left-align action button row but keep icons centered inside buttons */
            #projectsTable tbody tr.project-row td[data-label] .project-actions {
                justify-content: flex-start !important;
            }

            /* Separator line after each client group */
            #projectsTable tbody tr.client-separator {
                display: block;
                height: 1px;
                margin: 10px 6px 18px;
                background: rgba(148, 163, 184, 0.45); /* slate-400/45 */
                border-radius: 9999px;
            }
            #projectsTable tbody tr.client-separator td {
                display: none;
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
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-[#eaebef] p-8">

            {{-- Container for the table --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">

                {{-- Header Section --}}
                <div class="p-6 border-b border-gray-200">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-4">
                        <h6 class="text-lg font-semibold text-gray-900">{{ __('messages.Projects Table') }}</h6>

                        {{-- Buttons --}}
                        <div class="flex flex-wrap gap-2">
                            @php 
                                $reportRoute = '#';
                                $archiveRoute = '#';
                                if (Auth::user()->hasRole('Admin')) {
                                    $reportRoute = route('admin.Projectsrepports.downloadRepports');
                                    $archiveRoute = route('admin.projectsArchived.archiveIndex');
                                } elseif (Auth::user()->hasRole('Responsable de projet')) {
                                    $reportRoute = route('Responsable_de_projet.Projectsrepports.downloadRepports');
                                    $archiveRoute = route('Responsable_de_projet.projectsArchived.archiveIndex');
                                } elseif (Auth::user()->hasRole('Employee')) {
                                    $reportRoute = route('employee.Projectsrepports.downloadRepports');
                                    $archiveRoute = route('employee.projectsArchived.archiveIndex');
                                } elseif (Auth::user()->hasRole('Responsable de projet et gestionnaire')) {
                                    $reportRoute = route('Responsable_gestionnaire.Projectsrepports.downloadRepports');
                                    $archiveRoute = route('Responsable_gestionnaire.projectsArchived.archiveIndex');
                                }
                            @endphp
                            <a href="{{ $reportRoute }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 border border-gray-300 text-sm font-medium px-4 py-2 rounded-lg flex items-center gap-1.5">
                               <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                                {{ __('messages.Projects Reports PDF') }}
                            </a>
                             <a href="{{ $archiveRoute }}" class="bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium px-4 py-2 rounded-lg flex items-center gap-1.5">
                                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" /></svg>
                                {{ __('messages.View Archive') }}
                            </a>
                        </div>
                    </div>

                     {{-- Filters --}}
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                        <div class="w-96">
                            <label for="projectFilter" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Filter by Project') }}</label>
                            <select id="projectFilter" class="mt-1 block w-full rounded-lg border-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500 bg-white text-slate-600 shadow-sm py-2 px-3">
                                <option value="">{{ __('messages.All Projects') }}</option>
                                {{-- Use distinct project names for filter options --}}
                                @foreach($projects->unique('name') as $project)
                                    <option value="{{ $project->name }}">{{ $project->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="w-full sm:w-auto">
                            <label for="nameFilter" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Filter by Name') }}</label>
                            <input type="text" id="nameFilter" class="block w-full sm:w-64 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" placeholder="{{ __('messages.Filter by Name') }}">
                        </div>
                    </div>
                </div>

                {{-- Session Message Alert --}}
                @if(session('message'))
                    <div class="m-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded relative" role="alert">
                        <span class="block sm:inline">{{ session('message') }}</span>
                        <button type="button" onclick="this.parentElement.style.display='none'" class="absolute top-0 bottom-0 right-0 px-4 py-3 text-green-700 hover:text-green-900 font-bold text-xl" aria-label="Close">
                           &times;
                        </button>
                    </div>
                @endif

                {{-- Mobile: Accordion list (clients -> projects) --}}
                <div class="p-0 sm:hidden">
                    <div class="projects-accordion">
                        @php $groupedProjects = $projects->groupBy('client_id'); @endphp
                        @forelse($groupedProjects as $clientId => $clientProjects)
                            @php $client = App\Models\Client::find($clientId); @endphp
                            @php
                                $clientShowRoute = '#';
                                $canViewClient = Auth::user()->can('View client');
                                if ($canViewClient && $client) {
                                    if (Auth::user()->hasRole('Admin')) $clientShowRoute = route('admin.clients.show', $clientId);
                                    elseif (Auth::user()->hasRole('Responsable de projet')) $clientShowRoute = route('Responsable_de_projet.clients.show', $clientId);
                                    elseif (Auth::user()->hasRole('Employee')) $clientShowRoute = route('employee.clients.show', $clientId);
                                    elseif (Auth::user()->hasRole('Responsable de projet et gestionnaire')) $clientShowRoute = route('Responsable_gestionnaire.clients.show', $clientId);
                                }
                            @endphp

                            <div class="client-group border-b border-gray-200">
                                <button type="button"
                                        class="client-toggle w-full text-left p-6 hover:bg-gray-50 transition-colors flex items-center justify-between gap-3"
                                        data-client-id="{{ $clientId }}"
                                        aria-expanded="false">
                                    <div class="min-w-0">
                                        <div class="text-sm font-semibold text-gray-900 truncate">
                                            @if($client)
                                                @if($canViewClient)
                                                    <a href="{{ $clientShowRoute }}" class="text-blue-600 hover:underline" onclick="event.stopPropagation()">
                                                        {{ $client->name }}
                                                    </a>
                                                @else
                                                    {{ $client->name }}
                                                @endif
                                            @else
                                                {{ __('messages.Unknown Client') }}
                                            @endif
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ $clientProjects->where('archived', '!=', 1)->count() }} {{ __('messages.Projects') }}
                                        </div>
                                    </div>

                                    <svg class="client-chevron w-5 h-5 text-gray-400 transition-transform duration-200" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 9l3.75 3.75L15.75 9" />
                                    </svg>
                                </button>

                                <div class="client-projects hidden px-6 pb-6" data-client-id="{{ $clientId }}">
                                    <div class="space-y-2">
                                        @foreach($clientProjects as $project)
                                            @continue($project->archived == 1)
                                            @php
                                                $roleRouteMap = [
                                                    'Admin' => 'admin.projects.show',
                                                    'Responsable de projet' => 'Responsable_de_projet.projects.show',
                                                    'Responsable de projet et gestionnaire' => 'Responsable_gestionnaire.projects.show',
                                                    'Employee' => 'employee.projects.show',
                                                ];
                                                $projectShowRoute = null;
                                                foreach ($roleRouteMap as $role => $routeName) {
                                                    if (Auth::user()->hasRole($role) && Route::has($routeName)) {
                                                        $projectShowRoute = route($routeName, $project->id);
                                                        break;
                                                    }
                                                }
                                                if (!$projectShowRoute && Route::has('admin.projects.show')) {
                                                    $projectShowRoute = route('admin.projects.show', $project->id);
                                                }
                                            @endphp

                                            <div class="project-item rounded-xl border border-slate-200 bg-white/90 hover:bg-white transition-colors shadow-sm">
                                                <a href="{{ $projectShowRoute ?: '#' }}"
                                                   class="block px-4 py-3 text-sm font-semibold text-gray-900 hover:text-blue-700">
                                                    {{ strtoupper($project->name) }}
                                                </a>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="p-6 text-center text-sm text-gray-500">
                                {{ __('messages.No projects found.') }}
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Desktop/Tablet: keep full table, add dropdown on client headers --}}
                <div class="hidden sm:block p-0">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse min-w-full" id="projectsTable">
                            <thead class="border-b border-gray-200 bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-left">{{ __('messages.Project column') }}</th>
                                    @if(Auth::user()->hasRole('Admin'))
                                        <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-left">{{ __('messages.Budget column') }}</th>
                                        <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-left">{{ __('messages.Duration column') }}</th>
                                        <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-left">{{ __('messages.Est. Hours column') }}</th>
                                        <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-left">{{ __('messages.Difference column') }}</th>
                                        <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-left">{{ __('messages.Progress column') }}</th>
                                    @endif
                                    <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-left">{{ __('messages.Teams column header') }}</th>
                                    @can('Delete project')
                                        <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-left">{{ __('messages.Actions') }}</th>
                                    @endcan
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @php $groupedProjects = $projects->groupBy('client_id'); @endphp
                                @forelse($groupedProjects as $clientId => $clientProjects)
                                    @php $client = App\Models\Client::find($clientId); @endphp
                                    @php
                                        $clientShowRoute = '#';
                                        $canViewClient = Auth::user()->can('View client');
                                        if ($canViewClient && $client) {
                                            if (Auth::user()->hasRole('Admin')) $clientShowRoute = route('admin.clients.show', $clientId);
                                            elseif (Auth::user()->hasRole('Responsable de projet')) $clientShowRoute = route('Responsable_de_projet.clients.show', $clientId);
                                            elseif (Auth::user()->hasRole('Employee')) $clientShowRoute = route('employee.clients.show', $clientId);
                                            elseif (Auth::user()->hasRole('Responsable de projet et gestionnaire')) $clientShowRoute = route('Responsable_gestionnaire.clients.show', $clientId);
                                        }
                                    @endphp

                                    {{-- Client Header Row (click to collapse/expand projects) --}}
                                    <tr class="bg-gray-100 client-header cursor-pointer" data-client-id="{{ $clientId }}" aria-expanded="false">
                                        <td colspan="{{ Auth::user()->hasRole('Admin') ? 10 : 3 }}" class="px-6 py-3 font-semibold text-gray-800 text-sm">
                                            <div class="flex items-center justify-between gap-3">
                                                <div class="flex items-center gap-3 min-w-0">
                                                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-md bg-white border border-gray-200 text-gray-500">
                                                        <svg class="client-chevron w-4 h-4 transition-transform duration-200" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 9l3.75 3.75L15.75 9" />
                                                        </svg>
                                                    </span>
                                                    <span class="truncate">
                                                        @if($client)
                                                            @if($canViewClient)
                                                                <a href="{{ $clientShowRoute }}" class="text-blue-600 hover:underline" onclick="event.stopPropagation()">
                                                                    {{$client->name}}
                                                                </a>
                                                            @else
                                                                {{$client->name}}
                                                            @endif
                                                        @else
                                                            {{ __('messages.Unknown Client') }}
                                                        @endif
                                                    </span>
                                                </div>
                                                <span class="text-xs font-medium text-gray-500 whitespace-nowrap">
                                                    {{ $clientProjects->where('archived', '!=', 1)->count() }} {{ __('messages.Projects') }}
                                                </span>
                                            </div>
                                        </td>
                                    </tr>

                                    {{-- Projects for this client --}}
                                    @foreach($clientProjects as $project)
                                        @continue($project->archived == 1)
                                        <tr class="hover:bg-gray-50 project-row" data-client-id="{{ $clientId }}">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 project-name">
                                                @php
                                                    $roleRouteMap = [
                                                        'Admin' => 'admin.projects.show',
                                                        'Responsable de projet' => 'Responsable_de_projet.projects.show',
                                                        'Responsable de projet et gestionnaire' => 'Responsable_gestionnaire.projects.show',
                                                        'Employee' => 'employee.projects.show',
                                                    ];
                                                    $projectShowRoute = null;
                                                    foreach ($roleRouteMap as $role => $routeName) {
                                                        if (Auth::user()->hasRole($role) && Route::has($routeName)) {
                                                            $projectShowRoute = route($routeName, $project->id);
                                                            break;
                                                        }
                                                    }
                                                    if (!$projectShowRoute && Route::has('admin.projects.show')) {
                                                        $projectShowRoute = route('admin.projects.show', $project->id);
                                                    }
                                                @endphp
                                                @if($projectShowRoute)
                                                    <a href="{{ $projectShowRoute }}" class="text-blue-600 hover:underline">
                                                        {{ strtoupper($project->name) }}
                                                    </a>
                                                @else
                                                    {{ strtoupper($project->name) }}
                                                @endif
                                            </td>

                                            @if(Auth::user()->hasRole('Admin'))
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600">€{{ number_format($project->budget, 2, ',', ' ') }}</td>
                                                @php
                                                    $projecttasksId = App\Models\Tasks::where('project_id', $project->id)->pluck('id');
                                                    $totalduration = App\Models\tasks_timer::whereIn('task_id', $projecttasksId)->sum('duration');
                                                    $hours = floor(abs($totalduration) / 60);
                                                    $minutes = abs($totalduration) % 60;

                                                    $estimatedMinutes = ($project->estimated_hours ?? 0) * 60;
                                                    $difference = $estimatedMinutes - $totalduration;
                                                    $diffhours = floor(abs($difference) / 60);
                                                    $diffminutes = abs($difference) % 60;

                                                    $progressPercentage = $estimatedMinutes > 0 ? max(0, min(100, ($totalduration / $estimatedMinutes) * 100)) : 0;
                                                    $formattedProgressPercentage = number_format($progressPercentage, 2);

                                                    $progressBarColor = 'bg-green-500';
                                                    if ($progressPercentage >= 75) $progressBarColor = 'bg-red-500';
                                                    elseif ($progressPercentage >= 50) $progressBarColor = 'bg-yellow-500';
                                                @endphp
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ sprintf('%02d', $hours) }}h {{ sprintf('%02d', $minutes) }}m</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-700">{{ $project->estimated_hours ?? 0 }}h</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                    @if($project->estimated_hours !== null)
                                                        @if($difference > 0)
                                                            <span class="text-green-600">+{{ sprintf('%02d', $diffhours) }}h {{ sprintf('%02d', $diffminutes) }}m</span>
                                                        @elseif($difference < 0)
                                                            <span class="text-red-600">-{{ sprintf('%02d', $diffhours) }}h {{ sprintf('%02d', $diffminutes) }}m</span>
                                                        @else
                                                            <span class="text-blue-600">{{ sprintf('%02d', $diffhours) }}h {{ sprintf('%02d', $diffminutes) }}m</span>
                                                        @endif
                                                    @else
                                                        <span class="text-gray-400">-</span>
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                                    <div class="flex items-center gap-2">
                                                        <div class="w-20 bg-gray-200 rounded-full h-1.5">
                                                            <div class="{{ $progressBarColor }} h-1.5 rounded-full" style="width: {{ $formattedProgressPercentage }}%"></div>
                                                        </div>
                                                        <span class="text-xs font-medium">{{ $formattedProgressPercentage }}%</span>
                                                    </div>
                                                </td>
                                            @endif

                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-500">
                                                <div class="flex flex-wrap gap-1">
                                                    @php
                                                        $teamIndexRoute = '#';
                                                        $canViewTeam = Auth::user()->can('View team');
                                                        if($canViewTeam) {
                                                            if (Auth::user()->hasRole('Admin')) $teamIndexRoute = route('admin.teams.index');
                                                            elseif (Auth::user()->hasRole('Responsable de projet')) $teamIndexRoute = route('Responsable_de_projet.teams.index');
                                                            elseif (Auth::user()->hasRole('Employee')) $teamIndexRoute = route('employee.teams.index');
                                                            elseif (Auth::user()->hasRole('Responsable de projet et gestionnaire')) $teamIndexRoute = route('Responsable_gestionnaire.teams.index');
                                                        }
                                                        $project_teams_list = App\Models\project_teams::where('project_id', $project->id)->get();
                                                    @endphp
                                                    @forelse($project_teams_list as $project_team)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                                            @if($canViewTeam)
                                                                <a href="{{ $teamIndexRoute }}" class="hover:underline">{{$project_team->team_name}}</a>
                                                            @else
                                                                {{$project_team->team_name}}
                                                            @endif
                                                        </span>
                                                    @empty
                                                        <span class="text-xs text-gray-400">{{ __('messages.No teams') }}</span>
                                                    @endforelse
                                                </div>
                                            </td>

                                            @can('Delete project')
                                                <td class="px-6 py-4 whitespace-nowrap text-sm space-x-1">
                                                    @php
                                                        $deleteRoute = '#'; $editRoute = '#'; $archiveRoute = '#';
                                                        if (Auth::user()->hasRole('Admin')) {
                                                            $deleteRoute = route('admin.projects.destroy', $project->id);
                                                            $editRoute = route('admin.projects.edit', $project->id);
                                                            $archiveRoute = route('admin.projects.archive', $project->id);
                                                        } elseif (Auth::user()->hasRole('Responsable de projet')) {
                                                            $deleteRoute = route('Responsable_de_projet.projects.destroy', $project->id);
                                                            $editRoute = route('Responsable_de_projet.projects.edit', $project->id);
                                                            $archiveRoute = route('Responsable_de_projet.projects.archive', $project->id);
                                                        } elseif (Auth::user()->hasRole('Employee')) {
                                                            $deleteRoute = route('employee.projects.destroy', $project->id);
                                                            $editRoute = route('employee.projects.edit', $project->id);
                                                            $archiveRoute = route('employee.projects.archive', $project->id);
                                                        } elseif (Auth::user()->hasRole('Responsable de projet et gestionnaire')) {
                                                            $deleteRoute = route('Responsable_gestionnaire.projects.destroy', $project->id);
                                                            $editRoute = route('Responsable_gestionnaire.projects.edit', $project->id);
                                                            $archiveRoute = route('Responsable_gestionnaire.projects.archive', $project->id);
                                                        }
                                                    @endphp
                                                    <a href="{{ $editRoute }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-white bg-blue-600 hover:bg-blue-700">
                                                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536M4 20h4l9.5-9.5-4-4L4 16v4z" />
                                                        </svg>
                                                    </a>
                                                    <form action="{{ $archiveRoute }}" method="post" class="inline" onsubmit="return confirm('{{ __('messages.Archive this project?') }}');">
                                                        @csrf
                                                        <button type="submit" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-white bg-gray-500 hover:bg-gray-600">
                                                            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
                                                            </svg>
                                                        </button>
                                                    </form>
                                                    <form action="{{ $deleteRoute }}" method="post" class="inline" onsubmit="return confirm('{{ __('messages.Are you sure you want to delete this project?') }}');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-white bg-red-600 hover:bg-red-700">
                                                            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862A2 2 0 015.995 19.142L5 7m5 4v6m4-6v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3M4 7h16" />
                                                            </svg>
                                                        </button>
                                                    </form>
                                                </td>
                                            @endcan
                                        </tr>
                                    @endforeach
                                @empty
                                    <tr>
                                        <td colspan="{{ Auth::user()->hasRole('Admin') ? 10 : 3 }}" class="px-6 py-4 text-center text-sm text-gray-500">
                                            {{ __('messages.No projects found.') }}
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
    {{-- @include('dashboard_layouts.scripts') --}}

    {{-- Keep jQuery and potentially filter scripts --}}
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    
    {{-- Inline Editing Script (Commented out as in original) --}}
    {{-- 
    @can('Update project')
    <script>
    $(document).ready(function(){
        // Use event delegation for dblclick
        $('body').on('dblclick', '.editable', function(){
             if ($(this).find('input').length > 0) return; // Prevent re-editing

            var originalContent = $(this).text().trim();
            var cellWidth = $(this).outerWidth();
            
            // Add Tailwind classes
            $(this).html("<input type='text' value='" + originalContent + "' class='w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-1' style='min-width: " + cellWidth + "px;' />");
            
            var input = $(this).children('input').first();
            input.focus();
            input[0].select(); 

            input.blur(function(){
                var newContent = $(this).val();
                var $parentCell = $(this).parent();
                var projectId = $parentCell.data('id'); // Changed from clientId
                var columnName = $parentCell.data('column');
                
                $parentCell.text(originalContent); // Revert temporarily

                $.ajax({
                    url: '/admin/projects/updateProject', // Example route - ADJUST THIS
                    type: 'POST', // Use POST
                    data: {
                        content: newContent,
                        id: projectId, // Use projectId
                        column: columnName,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        $parentCell.text(newContent); 
                        // Optional success message
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        $parentCell.text(originalContent); // Revert on error
                        alert('Error updating project: ' + errorThrown);
                        console.error("AJAX Error:", textStatus, errorThrown, jqXHR.responseText);
                    }
                });
            });

            input.keypress(function(e){
                if(e.which == 13){ // Enter key
                    $(this).blur(); 
                    return false; 
                }
            });
        });
    });
    </script>
    @endcan
    --}}

    {{-- Accordion + Filter Scripts --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const projectFilter = document.getElementById('projectFilter');
            const nameFilter = document.getElementById('nameFilter');

            const toggles = Array.from(document.querySelectorAll('.client-toggle'));
            const panels = Array.from(document.querySelectorAll('.client-projects'));
            const desktopClientHeaders = Array.from(document.querySelectorAll('tr.client-header[data-client-id]'));

            function setClientOpen(clientId, open) {
                const toggle = document.querySelector(`.client-toggle[data-client-id="${clientId}"]`);
                const panel = document.querySelector(`.client-projects[data-client-id="${clientId}"]`);
                if (!toggle || !panel) return;

                panel.classList.toggle('hidden', !open);
                toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
                const chevron = toggle.querySelector('.client-chevron');
                if (chevron) chevron.classList.toggle('rotate-180', open);
            }

            function closeAllClients() {
                toggles.forEach(t => setClientOpen(t.dataset.clientId, false));
            }

            function setDesktopClientOpen(clientId, open) {
                const header = document.querySelector(`tr.client-header[data-client-id="${clientId}"]`);
                if (!header) return;
                header.setAttribute('aria-expanded', open ? 'true' : 'false');
                const chevron = header.querySelector('.client-chevron');
                if (chevron) {
                    chevron.classList.toggle('rotate-180', open);
                }
                document.querySelectorAll(`tr.project-row[data-client-id="${clientId}"]`).forEach(row => {
                    row.style.display = open ? '' : 'none';
                });
            }

            desktopClientHeaders.forEach(header => {
                header.addEventListener('click', () => {
                    const clientId = header.dataset.clientId;
                    const isOpen = header.getAttribute('aria-expanded') === 'true';
                    setDesktopClientOpen(clientId, !isOpen);
                });
            });

            toggles.forEach(toggle => {
                toggle.addEventListener('click', () => {
                    const clientId = toggle.dataset.clientId;
                    const isOpen = toggle.getAttribute('aria-expanded') === 'true';
                    setClientOpen(clientId, !isOpen);
                });
            });

            function applyFilters() {
                const projectValue = (projectFilter?.value || '').toLowerCase().trim();
                const nameValue = (nameFilter?.value || '').toLowerCase().trim();
                const hasFilter = projectValue !== '' || nameValue !== '';

                // Default state: show only clients list (projects collapsed)
                if (!hasFilter) {
                    // Show all client groups
                    document.querySelectorAll('.client-group').forEach(g => g.style.display = '');
                    // Hide all project items and close all panels
                    document.querySelectorAll('.project-item').forEach(item => item.style.display = '');
                    closeAllClients();

                    // Desktop: show all client headers, keep groups collapsed
                    desktopClientHeaders.forEach(h => {
                        const clientId = h.dataset.clientId;
                        if (clientId) setDesktopClientOpen(clientId, false);
                        h.style.display = '';
                    });
                    return;
                }

                const visibleClients = new Set();

                // Filter project items and track which clients have matches
                document.querySelectorAll('.client-group').forEach(group => {
                    const toggle = group.querySelector('.client-toggle');
                    const clientId = toggle?.dataset.clientId;
                    const panel = group.querySelector('.client-projects');
                    const items = Array.from(group.querySelectorAll('.project-item'));

                    let hasAny = false;
                    items.forEach(item => {
                        const text = (item.textContent || '').toLowerCase();
                        const matchesProject = projectValue === '' || text.includes(projectValue);
                        const matchesName = nameValue === '' || text.includes(nameValue);
                        const ok = matchesProject && matchesName;
                        item.style.display = ok ? '' : 'none';
                        if (ok) hasAny = true;
                    });

                    if (hasAny && clientId) {
                        visibleClients.add(clientId);
                        group.style.display = '';
                        setClientOpen(clientId, true);
                    } else {
                        group.style.display = 'none';
                        if (clientId) setClientOpen(clientId, false);
                    }
                });

                // Desktop: filter table rows and auto-expand matching clients
                const visibleDesktopClients = new Set();
                document.querySelectorAll('tr.project-row[data-client-id]').forEach(row => {
                    const text = (row.querySelector('.project-name')?.textContent || row.textContent || '').toLowerCase();
                    const matchesProject = projectValue === '' || text.includes(projectValue);
                    const matchesName = nameValue === '' || text.includes(nameValue);
                    const ok = matchesProject && matchesName;
                    row.style.display = ok ? '' : 'none';
                    if (ok) visibleDesktopClients.add(row.dataset.clientId);
                });
                desktopClientHeaders.forEach(h => {
                    const clientId = h.dataset.clientId;
                    const show = visibleDesktopClients.has(clientId);
                    h.style.display = show ? '' : 'none';
                    if (show) setDesktopClientOpen(clientId, true);
                });
            }

            projectFilter?.addEventListener('change', applyFilters);
            nameFilter?.addEventListener('input', applyFilters);

            // Initial state:
            // - Mobile: collapsed clients list
            // - Desktop: collapsed table groups
            closeAllClients();
            desktopClientHeaders.forEach(h => {
                const clientId = h.dataset.clientId;
                if (clientId) setDesktopClientOpen(clientId, false);
            });
        });
    </script>

    {{-- Tom Select for searchable project dropdown --}}
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const el = document.getElementById('projectFilter');
            if (el) {
                new TomSelect(el, {
                    create: false,
                    sortField: { field: 'text', direction: 'asc' },
                    maxOptions: null,
                    allowEmptyOption: true,
                    placeholder: el.options[0] ? el.options[0].text : '{{ __("messages.Search for projects placeholder") }}'
                });
            }
        });
    </script>

</body>
</html>
