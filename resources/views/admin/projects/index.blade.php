<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <x-favicon />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.projects_page_title') }} — DalyTrac</title>
    @vite('resources/css/app.css')
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
    <style>
        /* Searchable filter selects (Tom Select, dashboard look) */
        .ts-wrapper.single .ts-control {
            border: 1px solid #8292a6 !important;
            border-radius: 0.5rem !important;
            padding: 0.5rem 0.75rem !important;
            font-size: 12.5px !important;
            background-color: #f8fafc !important;
            min-height: 38px !important;
            color: #475569 !important;
            box-shadow: none !important;
        }
        .ts-wrapper.single.focus .ts-control {
            border-color: #2D7080 !important;
            background-color: #fff !important;
            box-shadow: 0 0 0 2px rgba(45, 112, 128, 0.20) !important;
        }
        .ts-dropdown {
            border: 1px solid #8292a6 !important;
            border-radius: 0.5rem !important;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,.1), 0 2px 4px -2px rgba(0,0,0,.1) !important;
        }
        .ts-dropdown .option {
            padding: 0.5rem 0.75rem !important;
            font-size: 12.5px !important;
        }
        .ts-dropdown .option.active {
            background-color: rgba(45, 112, 128, 0.10) !important;
            color: #0B2638 !important;
        }
    </style>
    @livewireStyles
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="//unpkg.com/alpinejs" defer></script>

    <style>
        /* ── Projects table ─────────────────────────────────────────── */
        #projectsTable th {
            font-size: 11px; font-weight: 700; letter-spacing: .07em;
            text-transform: uppercase; color: #334155;
            padding: 10px 10px; white-space: normal;
            background: #eaf0f4; border-bottom: 1px solid #b6c4d2;
            font-size: 12px;
        }
        #projectsTable td {
            padding: 10px 10px; font-size: 14px; color: #111827;
            vertical-align: middle; border-bottom: 1px solid #cbd5e1;
        }
        #projectsTable tbody tr:last-child td { border-bottom: none; }
        #projectsTable tbody tr:hover td     { background: #f8fafc; }
        #projectsTable :is(.text-slate-400, .text-slate-500, .text-gray-400, .text-gray-500) { color: #475569; }
        #projectsTable .project-name { font-size: 15.5px; color: #111827; line-height: 1.4; }
        #projectsTable .project-name:hover { color: #2D7080; }
        #clientFilterForm { width: clamp(230px, 22vw, 300px); max-width: 100%; }
        #clientFilterForm .ts-wrapper { width: 100%; min-width: 0; }
        #clientFilterForm .ts-control { padding-right: 2.25rem !important; }
        #clientFilterForm .ts-control > .item { max-width: 100%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        #clientFilterForm .ts-control > input { min-width: 0; }
        @media (min-width: 1024px) {
            #projectsTable.projects-table-admin { min-width: 1040px; }
            #projectsTable th { overflow-wrap: anywhere; }
            #projectsTable .act-row { justify-content: center; }
        }

        /* ── Progress bar ───────────────────────────────────────────── */
        .prog-bar-wrap { width: 70px; height: 6px; background: #e2e8f0; border-radius: 99px; overflow: hidden; flex-shrink: 0; }
        .prog-bar      { height: 100%; border-radius: 99px; transition: width .3s; }

        /* ── Action icon buttons ────────────────────────────────────── */
        .act-icon {
            display: inline-flex; align-items: center; justify-content: center;
            width: 30px; height: 30px; border-radius: 8px; border: 1px solid #e2e8f0;
            background: #fff; color: #475569; flex-shrink: 0; cursor: pointer;
            transition: background .15s, border-color .15s, color .15s, transform .1s;
            text-decoration: none;
        }
        .act-icon svg { width: 14px; height: 14px; flex-shrink: 0; }
        .act-icon:hover { transform: translateY(-1px); }
        .act-icon:focus-visible { outline: 2px solid #2D7080; outline-offset: 2px; }
        .act-icon-view   { color: #2D7080; border-color: #b2d8de; background: #f0fafb; }
        .act-icon-view:hover { background: #e0f2f1; border-color: #2D7080; }
        .act-icon-task   { color: #7c3aed; border-color: #ddd6fe; background: #faf5ff; }
        .act-icon-task:hover { background: #ede9fe; border-color: #7c3aed; }
        .act-icon-edit   { color: #2D7080; border-color: #b2d8de; background: #f0fafb; }
        .act-icon-edit:hover { background: #e0f2f1; border-color: #2D7080; }
        .act-icon-archive { color: #b45309; border-color: #fde68a; background: #fffbeb; }
        .act-icon-archive:hover { background: #fef3c7; border-color: #f59e0b; }
        .act-icon-delete { color: #dc2626; border-color: #fecaca; background: #fef2f2; }
        .act-icon-delete:hover { background: #fee2e2; border-color: #f87171; }


        /* ── Mobile card layout (< 1024px) ──────────────────────────── */
        @media (max-width: 1023px) {
            .table-wrap { overflow-x: visible !important; }
            #projectsTable thead { display: none; }
            #projectsTable tbody { display: block; width: 100%; }
            #projectsTable tbody tr.proj-row {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 8px;
                margin-bottom: 12px;
                padding: 12px;
                border: 1px solid rgba(148,163,184,.4);
                border-radius: 14px;
                background: #fff;
                box-shadow: 0 2px 10px -4px rgba(11,38,56,.09);
            }
            #projectsTable tbody td {
                display: block; width: 100%;
                padding: 8px 10px !important;
                border: 1px solid rgba(226,232,240,.8);
                border-radius: 10px; background: rgba(248,250,252,.7);
                text-align: left !important; white-space: normal !important; word-break: break-word;
            }
            #projectsTable tbody td[data-span="2"] { grid-column: span 2 / span 2; }
            #projectsTable tbody td[data-label]::before {
                content: attr(data-label);
                display: block; font-size: 10px; font-weight: 700;
                letter-spacing: .06em; text-transform: uppercase; color: #94a3b8; margin-bottom: 4px;
            }
            #projectsTable tbody td.no-label::before { content: none !important; }
            .act-row { justify-content: flex-start !important; }
        }
    </style>
</head>

@php $projectsIndexRoute = request()->route()?->getName() ?? 'admin.projects.index'; @endphp
<body class="dt-app-shell bg-[#f8fafc]">
<div class="flex h-screen overflow-hidden">

    {{-- Sidebar (same conditional shell as the other dashboard pages:
         Admin sees the admin sidebar, other roles see the shared
         role-based sidebar — the sidebar must not switch per page) --}}
    @if(Auth::user()->hasRole('Admin'))
        <x-dashboard.sidebar />
    @else
        <x-employees.sidebar />
    @endif

    {{-- Main panel --}}
    <div class="flex flex-col flex-1 overflow-y-auto">

        <x-dashboard.navbar />

        <main class="flex-1 overflow-x-hidden bg-[#f8fafc] px-3 py-4 sm:px-5 sm:py-5 lg:px-10 lg:py-7">

            {{-- ── Page header ─────────────────────────────────────── --}}
            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-2 mb-5">
                <div class="min-w-0">
                    <p class="text-[10px] font-semibold uppercase tracking-[.08em] text-slate-400 mb-0.5">
                        {{ __('messages.Project Management') }}
                    </p>
                    <h1 class="text-[20px] sm:text-[22px] font-bold tracking-tight text-[#0B2638] leading-tight">
                        {{ __('messages.projects_page_title') }}
                    </h1>
                    <p class="text-[12px] text-slate-500 mt-0.5 hidden sm:block">
                        {{ __('messages.projects_page_subtitle') }}
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2 flex-shrink-0">
                    <a href="{{ route('admin.projects.create') }}" class="dt-btn dt-btn-primary text-[12.5px]">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                        </svg>
                        {{ __('messages.Add Project') }}
                    </a>
                    <a href="{{ route('admin.projectsArchived.archiveIndex') }}" class="dt-btn dt-btn-ghost text-[12.5px]">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                        </svg>
                        {{ __('messages.View Archive') }}
                    </a>
                </div>
            </div>

            {{-- ── Flash messages ──────────────────────────────────── --}}
            @if(session('message'))
                <div class="flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl mb-4 relative" role="alert">
                    <svg class="w-4 h-4 flex-shrink-0 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    <span class="text-[13px] font-medium">{{ session('message') }}</span>
                    <button onclick="this.parentElement.style.display='none'" class="absolute top-2.5 right-3 text-emerald-500 hover:text-emerald-700 text-lg leading-none" aria-label="{{ __('messages.Close') }}">&times;</button>
                </div>
            @endif
            @if(session('error'))
                <div class="flex items-center gap-3 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl mb-4 relative" role="alert">
                    <svg class="w-4 h-4 flex-shrink-0 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    <span class="text-[13px] font-medium">{{ session('error') }}</span>
                    <button onclick="this.parentElement.style.display='none'" class="absolute top-2.5 right-3 text-red-500 hover:text-red-700 text-lg leading-none" aria-label="{{ __('messages.Close') }}">&times;</button>
                </div>
            @endif

            {{-- ── Table card ──────────────────────────────────────── --}}
            <div class="dt-card overflow-hidden">

                {{-- Card toolbar: search + count + per-page ─────────── --}}
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 px-4 py-3.5 border-b border-slate-100">
                    {{-- Left: title + total --}}
                    <div id="projectResultsSummary" class="flex items-center gap-3 min-w-0">
                        <p class="text-[13.5px] font-semibold text-[#0B2638]">{{ __('messages.projects_page_title') }}</p>
                        <span class="dt-badge dt-badge-navy">
                            <span class="dt-dot"></span>
                            {{ $projects->total() }} {{ __('messages.Projects') }}
                        </span>
                        @if($search)
                            <span class="text-[11.5px] text-[#2D7080] font-medium bg-[#2D7080]/[0.07] px-2 py-0.5 rounded-full">
                                "{{ $search }}"
                            </span>
                        @endif
                        @if($clientFilter && ($clientFilterName = optional($clients->firstWhere('id', (int) $clientFilter))->name))
                            <a href="{{ route($projectsIndexRoute, array_filter(['search' => $search ?: null, 'per_page' => $perPage])) }}"
                               class="text-[11.5px] text-[#0B2638] font-medium bg-[#E7B84B]/20 hover:bg-[#E7B84B]/30 px-2 py-0.5 rounded-full transition-colors"
                               title="{{ __('messages.All Clients') }}">
                                {{ $clientFilterName }} &times;
                            </a>
                        @endif
                    </div>

                    {{-- Right: search input + client filter + per-page ── --}}
                    <div class="flex flex-wrap items-center gap-2 flex-shrink-0">
                        {{-- Search ─────────────────────────────────── --}}
                        <form method="GET" action="{{ route($projectsIndexRoute) }}" class="flex items-center gap-2" id="projectSearchForm">
                            <div class="relative">
                                <span class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-slate-400">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
                                    </svg>
                                </span>
                                <input
                                    type="text"
                                    name="search"
                                    id="searchInput"
                                    value="{{ $search }}"
                                    placeholder="{{ __('messages.projects_search_placeholder') }}"
                                    class="pl-9 pr-8 py-2 text-[12.5px] border border-slate-200 rounded-lg bg-slate-50 text-slate-700 placeholder:text-slate-400 focus:bg-white focus:ring-2 focus:ring-[#2D7080]/20 focus:border-[#2D7080] outline-none transition w-56 sm:w-64"
                                    autocomplete="off"
                                />
                                <span id="projectSearchClear">
                                @if($search)
                                    <a href="{{ route($projectsIndexRoute, array_filter(['per_page' => $perPage, 'client' => $clientFilter ?: null])) }}"
                                       class="absolute inset-y-0 right-2.5 flex items-center text-slate-400 hover:text-slate-600 transition-colors"
                                       aria-label="Clear search">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </a>
                                @endif
                                </span>
                            </div>
                            <input type="hidden" name="per_page" value="{{ $perPage }}">
                            <input type="hidden" name="client" value="{{ $clientFilter }}">
                        </form>

                        {{-- Client filter ────────────────────────────── --}}
                        <form method="GET" action="{{ route($projectsIndexRoute) }}" id="clientFilterForm">
                            <input type="hidden" name="search" value="{{ $search }}">
                            <input type="hidden" name="per_page" value="{{ $perPage }}">
                            <select id="clientFilter" name="client" onchange="this.form.submit()" aria-label="{{ __('messages.Client') }}"
                                    class="text-[12.5px] border border-slate-400 rounded-lg bg-slate-50 text-slate-600 py-2 pl-2.5 pr-7 focus:ring-2 focus:ring-[#2D7080]/20 focus:border-[#2D7080] outline-none transition dt-filter-select w-full">
                                <option value="">{{ __('messages.All Clients') }}</option>
                                @foreach($clients as $client)
                                    <option value="{{ $client->id }}" {{ (string)$clientFilter === (string)$client->id ? 'selected' : '' }}>{{ $client->name }}</option>
                                @endforeach
                            </select>
                        </form>

                        {{-- Per-page ─────────────────────────────────── --}}
                        <form method="GET" action="{{ route($projectsIndexRoute) }}" id="perPageForm">
                            <input type="hidden" name="search" value="{{ $search }}">
                            <input type="hidden" name="client" value="{{ $clientFilter }}">
                            <select name="per_page" onchange="this.form.submit()"
                                    class="text-[12.5px] border border-slate-400 rounded-lg bg-slate-50 text-slate-600 py-2 pl-2.5 pr-7 focus:ring-2 focus:ring-[#2D7080]/20 focus:border-[#2D7080] outline-none transition dt-filter-select">
                                @foreach([10, 15, 25, 50, 100] as $pp)
                                    <option value="{{ $pp }}" {{ $perPage == $pp ? 'selected' : '' }}>{{ $pp }} {{ __('messages.projects_per_page') }}</option>
                                @endforeach
                            </select>
                        </form>
                    </div>
                </div>

                <p id="projectSearchError" role="status" class="hidden px-4 py-2 text-sm text-red-700">{{ __('messages.Project search could not be updated. Press Enter to retry.') }}</p>
                <div id="projectResults" aria-live="polite">
                {{-- Table ───────────────────────────────────────────── --}}
                <div class="table-wrap overflow-x-auto">
                    <table class="w-full table-fixed text-left border-collapse min-w-[700px] {{ Auth::user()->hasRole('Admin') ? 'projects-table-admin' : '' }}" id="projectsTable">
                        <colgroup>
                            <col style="width:{{ Auth::user()->hasRole('Admin') ? '30%' : '48%' }}">
                            @if(Auth::user()->hasRole('Admin'))
                                <col style="width:7%"><col style="width:7%"><col style="width:7%"><col style="width:7%"><col style="width:10%">
                            @endif
                            <col style="width:{{ Auth::user()->hasRole('Admin') ? '7%' : '18%' }}">
                            <col style="width:{{ Auth::user()->hasRole('Admin') ? '25%' : '34%' }}">
                        </colgroup>
                        <thead>
                            <tr class="dt-table-head">
                                <th class="px-3.5 py-2.5 text-left ">{{ __('messages.Project column') }}</th>
                                @if(Auth::user()->hasRole('Admin'))
                                    <th class="px-3.5 py-2.5 text-right  ">{{ __('messages.Budget column') }}</th>
                                    <th class="px-3.5 py-2.5 text-center  ">{{ __('messages.Duration column') }}</th>
                                    <th class="px-3.5 py-2.5 text-center ">{{ __('messages.Est. Hours column') }}</th>
                                    <th class="px-3.5 py-2.5 text-center ">{{ __('messages.Difference column') }}</th>
                                    <th class="px-3.5 py-2.5 text-center  ">{{ __('messages.Progress column') }}</th>
                                @endif
                                <th class="px-3.5 py-2.5 text-center">{{ __('messages.Teams column header') }}</th>
                                <th class="px-3.5 py-2.5 text-center ">{{ __('messages.Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @forelse($projects as $project)
                                @php
                                    /* ── Duration ─────────────────────────────────── */
                                    $taskIds        = App\Models\Tasks::where('project_id', $project->id)->pluck('id');
                                    $totalMinutes   = App\Models\tasks_timer::whereIn('task_id', $taskIds)->sum('duration');
                                    $durH           = floor($totalMinutes / 60);
                                    $durM           = $totalMinutes % 60;

                                    /* ── Progress / Difference ────────────────────── */
                                    $estimatedMin   = ($project->estimated_hours ?? 0) * 60;
                                    $diffMin        = $estimatedMin - $totalMinutes;
                                    $diffH          = floor(abs($diffMin) / 60);
                                    $diffM          = abs($diffMin) % 60;
                                    $progress       = $estimatedMin > 0
                                                        ? max(0, min(100, ($totalMinutes / $estimatedMin) * 100))
                                                        : 0;
                                    $progressFmt    = number_format($progress, 1);
                                    $barColor       = $progress >= 90 ? '#ef4444'
                                                    : ($progress >= 60 ? '#f59e0b' : '#22c55e');

                                    /* ── Routes ───────────────────────────────────── */
                                    $showRoute    = route('admin.projects.show',    $project->id);
                                    $editRoute    = route('admin.projects.edit',    $project->id);
                                    $archiveRoute = route('admin.projects.archive', $project->id);
                                    $deleteRoute  = route('admin.projects.destroy', $project->id);
                                    $taskRoute    = route('admin.tasks.create',     $project->id);
                                @endphp

                                <tr class="proj-row hover:bg-slate-50/70 transition-colors">
                                    {{-- Project (Client) ──────────────────────────── --}}
                                    <td data-label="{{ __('messages.Project column') }}" data-span="2">
                                        <div class="flex items-start gap-2.5 min-w-0">
                                            {{-- Color dot --}}
                                            <span class="mt-0.5 w-2.5 h-2.5 rounded-full flex-shrink-0 ring-1 ring-black/10"
                                                  style="background-color:{{ $project->color ?: '#FDE68A' }}"></span>
                                            <div class="min-w-0">
                                                <a href="{{ $showRoute }}"
                                                   title="{{ $project->name }}"
                                                   class="project-name font-semibold hover:underline block truncate">
                                                    {{ $project->name }}
                                                </a>
                                                @if($project->client)
                                                    <span class="text-[11.5px] text-slate-400 block truncate leading-tight mt-0.5">
                                                        {{ $project->client->name }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>

                                    @if(Auth::user()->hasRole('Admin'))
                                        {{-- Budget ──────────────────────────────────── --}}
                                        <td data-label="{{ __('messages.Budget column') }}" class="text-right">
                                            <span class="text-[13px] font-semibold text-emerald-700">
                                                €{{ number_format($project->budget ?? 0, 0, ',', ' ') }}
                                            </span>
                                        </td>

                                        {{-- Duration ────────────────────────────────── --}}
                                        <td data-label="{{ __('messages.Duration column') }}" class="text-center">
                                            <span class="text-[12.5px] font-medium text-slate-600 tabular-nums">
                                                {{ sprintf('%02d', $durH) }}h&nbsp;{{ sprintf('%02d', $durM) }}m
                                            </span>
                                        </td>

                                        {{-- Estimated hours ──────────────────────────── --}}
                                        <td data-label="{{ __('messages.Est. Hours column') }}" class="text-center">
                                            <span class="text-[12.5px] text-slate-600">
                                                {{ $project->estimated_hours ?? '—' }}h
                                            </span>
                                        </td>

                                        {{-- Difference ──────────────────────────────── --}}
                                        <td data-label="{{ __('messages.Difference column') }}" class="text-center">
                                            @if($project->estimated_hours !== null)
                                                @if($diffMin > 0)
                                                    <span class="dt-badge dt-badge-success">
                                                        +{{ sprintf('%02d', $diffH) }}h&nbsp;{{ sprintf('%02d', $diffM) }}m
                                                    </span>
                                                @elseif($diffMin < 0)
                                                    <span class="dt-badge dt-badge-danger">
                                                        −{{ sprintf('%02d', $diffH) }}h&nbsp;{{ sprintf('%02d', $diffM) }}m
                                                    </span>
                                                @else
                                                    <span class="dt-badge dt-badge-neutral">0h</span>
                                                @endif
                                            @else
                                                <span class="text-slate-300 text-[12px]">—</span>
                                            @endif
                                        </td>

                                        {{-- Progress ────────────────────────────────── --}}
                                        <td data-label="{{ __('messages.Progress column') }}">
                                            <div class="flex items-center gap-2 justify-center">
                                                <div class="prog-bar-wrap">
                                                    <div class="prog-bar" style="width:{{ $progressFmt }}%;background-color:{{ $barColor }}"></div>
                                                </div>
                                                <span class="text-[11.5px] font-semibold text-slate-500 tabular-nums w-10 text-right flex-shrink-0">
                                                    {{ $progressFmt }}%
                                                </span>
                                            </div>
                                        </td>
                                    @endif

                                    {{-- Teams ───────────────────────────────────────── --}}
                                    <td data-label="{{ __('messages.Teams column header') }}" class="text-center">
                                        <div class="flex flex-wrap justify-center gap-1.5">
                                            @php
                                                $projTeams = App\Models\project_teams::where('project_id', $project->id)
                                                    ->get()
                                                    ->unique('team_name');
                                            @endphp
                                            @forelse($projTeams as $pt)
                                                <span class="dt-badge dt-badge-navy">
                                                    <span class="dt-dot"></span>{{ $pt->team_name }}
                                                </span>
                                            @empty
                                                <span class="text-[11.5px] text-slate-400">{{ __('messages.No teams') }}</span>
                                            @endforelse
                                        </div>
                                    </td>

                                    {{-- Actions ─────────────────────────────────────── --}}
                                    <td class="no-label" data-span="2">
                                        <div class="act-row flex items-center justify-start gap-1.5 flex-wrap">

                                            {{-- Add Task — first, with label --}}
                                            <a href="{{ $taskRoute }}"
                                               class="act-icon-task inline-flex items-center gap-1.5 h-[30px] px-3 rounded-lg border border-[#ddd6fe] bg-[#faf5ff] text-[#7c3aed] hover:bg-[#ede9fe] hover:border-[#7c3aed] transition-colors no-underline"
                                               title="{{ __('messages.Add Task to Project') }}"
                                               style="font-size:12px;font-weight:600;white-space:nowrap;text-decoration:none;">
                                                <svg style="width:13px;height:13px;flex-shrink:0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                                </svg>
                                                {{ __('messages.Add Task') }}
                                            </a>

                                            {{-- View — icon only --}}
                                            <a href="{{ $showRoute }}"
                                               class="act-icon act-icon-view"
                                               title="{{ __('messages.View project') }}">
                                                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                    <circle cx="12" cy="12" r="3"/>
                                                </svg>
                                            </a>

                                            @can('Delete project')
                                                {{-- Edit — icon only --}}
                                                <a href="{{ $editRoute }}"
                                                   class="act-icon act-icon-edit"
                                                   title="{{ __('messages.Edit') }}">
                                                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536M4 20h4l9.5-9.5-4-4L4 16v4z"/>
                                                    </svg>
                                                </a>

                                                {{-- Archive — icon only --}}
                                                <form action="{{ $archiveRoute }}" method="POST" class="inline"
                                                      onsubmit="return confirm('{{ __('messages.Archive this project?') }}')">
                                                    @csrf
                                                    <button type="submit" class="act-icon act-icon-archive" title="{{ __('messages.Archive') }}">
                                                        <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z"/>
                                                        </svg>
                                                    </button>
                                                </form>

                                                {{-- Delete — icon only --}}
                                                <form action="{{ $deleteRoute }}" method="POST" class="inline"
                                                      onsubmit="return confirm('{{ __('messages.Are you sure you want to delete this project?') }}')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="act-icon act-icon-delete" title="{{ __('messages.Delete') }}">
                                                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862A2 2 0 015.995 19.142L5 7m5 4v6m4-6v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3M4 7h16"/>
                                                        </svg>
                                                    </button>
                                                </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ Auth::user()->hasRole('Admin') ? 8 : 3 }}" class="px-6 py-14 text-center">
                                        <div class="dt-empty-icon mx-auto mb-3">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"/>
                                            </svg>
                                        </div>
                                        <p class="text-[13.5px] font-semibold text-[#0B2638] mb-1">{{ __('messages.No projects found.') }}</p>
                                        @if($search)
                                            <p class="text-[12.5px] text-slate-400">
                                                {{ __('messages.No results found for') }} "{{ $search }}"
                                            </p>
                                            <a href="{{ route($projectsIndexRoute) }}" class="mt-3 inline-flex items-center text-[12.5px] font-semibold text-[#2D7080] hover:underline">
                                                ← {{ __('messages.All Projects') }}
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- ── Pagination ───────────────────────────────────── --}}
                @if($projects->hasPages())
                    <div class="px-4 py-3.5 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                        <p class="text-[12px] text-slate-400">
                            {{ __('messages.Showing') ?? 'Showing' }}
                            <span class="font-semibold text-slate-600">{{ $projects->firstItem() }}–{{ $projects->lastItem() }}</span>
                            {{ __('messages.of') ?? 'of' }}
                            <span class="font-semibold text-slate-600">{{ $projects->total() }}</span>
                        </p>
                        <div class="flex items-center gap-1 flex-wrap">
                            {{-- Previous --}}
                            @if($projects->onFirstPage())
                                <span class="dt-page-btn dt-page-btn-disabled">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                                </span>
                            @else
                                <a href="{{ $projects->previousPageUrl() }}" class="dt-page-btn">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                                </a>
                            @endif

                            {{-- Page numbers --}}
                            @foreach($projects->getUrlRange(max(1, $projects->currentPage() - 2), min($projects->lastPage(), $projects->currentPage() + 2)) as $page => $url)
                                @if($page == $projects->currentPage())
                                    <span class="dt-page-btn dt-page-btn-active">{{ $page }}</span>
                                @else
                                    <a href="{{ $url }}" class="dt-page-btn">{{ $page }}</a>
                                @endif
                            @endforeach

                            {{-- Next --}}
                            @if($projects->hasMorePages())
                                <a href="{{ $projects->nextPageUrl() }}" class="dt-page-btn">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            @else
                                <span class="dt-page-btn dt-page-btn-disabled">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                </span>
                            @endif
                        </div>
                    </div>
                @endif

                </div>{{-- end projectResults --}}
            </div>{{-- end dt-card --}}
        </main>
    </div>
</div>

@livewireScripts

{{-- Debounce the existing GET search and refresh only its server-rendered results. --}}
<script>
(function () {
    const form = document.getElementById('projectSearchForm');
    const input = form ? form.querySelector('input[name="search"]') : null;
    const results = document.getElementById('projectResults');
    const error = document.getElementById('projectSearchError');
    if (!form || !input || !results) return;

    let timer, controller, version = 0;
    function cancelPending() {
        clearTimeout(timer);
        version++;
        if (controller) controller.abort();
        results.removeAttribute('aria-busy');
    }
    function syncFilterSearch() {
        document.querySelectorAll('#clientFilterForm input[name="search"], #perPageForm input[name="search"]').forEach(function (field) {
            field.value = input.value;
        });
    }
    function searchUrl() {
        const url = new URL(form.action, window.location.href);
        url.search = new URLSearchParams(new FormData(form)).toString();
        return url;
    }
    async function updateResults(url) {
        cancelPending();
        const current = version;
        controller = new AbortController();
        results.setAttribute('aria-busy', 'true');
        error.classList.add('hidden');
        try {
            const response = await fetch(url.toString(), {
                signal: controller.signal,
                headers: { 'Accept': 'text/html' }
            });
            if (!response.ok || response.redirected) throw new Error('Search request failed');
            const html = await response.text();
            if (current !== version) return;
            const page = new DOMParser().parseFromString(html, 'text/html');
            const next = page.getElementById('projectResults');
            if (!next) throw new Error('Search results missing');
            results.innerHTML = next.innerHTML;
            ['projectResultsSummary', 'projectSearchClear'].forEach(function (id) {
                const target = document.getElementById(id);
                const replacement = page.getElementById(id);
                if (target && replacement) target.innerHTML = replacement.innerHTML;
            });
            window.history.replaceState(null, '', url.toString());
        } catch (err) {
            if (err.name !== 'AbortError' && current === version) error.classList.remove('hidden');
        } finally {
            if (current === version) results.removeAttribute('aria-busy');
        }
    }
    function scheduleSearch(event) {
        cancelPending();
        syncFilterSearch();
        if (event && event.isComposing) return;
        timer = setTimeout(function () { updateResults(searchUrl()); }, 700);
    }
    input.addEventListener('input', scheduleSearch);
    input.addEventListener('compositionend', scheduleSearch);
    form.addEventListener('submit', function (event) {
        event.preventDefault();
        syncFilterSearch();
        updateResults(searchUrl());
    });
    results.addEventListener('click', function (event) {
        const link = event.target.closest('a.dt-page-btn');
        if (!link || event.button !== 0 || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
        event.preventDefault();
        updateResults(new URL(link.href));
    });
    document.getElementById('projectSearchClear').addEventListener('click', function (event) {
        if (!event.target.closest('a') || event.button !== 0 || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
        event.preventDefault();
        input.value = '';
        syncFilterSearch();
        updateResults(searchUrl());
        input.focus();
    });
})();
</script>

<style>
    /* Pagination button tokens */
    .dt-page-btn {
        display: inline-flex; align-items: center; justify-content: center;
        min-width: 32px; height: 32px; padding: 0 8px;
        border-radius: 8px; border: 1px solid #e2e8f0;
        background: #fff; color: #475569;
        font-size: 12.5px; font-weight: 500;
        text-decoration: none; transition: background .12s, border-color .12s;
    }
    .dt-page-btn:hover:not(.dt-page-btn-active):not(.dt-page-btn-disabled) {
        background: #f1f5f9; border-color: #cbd5e1;
    }
    .dt-page-btn-active {
        background: #2D7080 !important; border-color: #2D7080 !important;
        color: #fff !important; font-weight: 700;
    }
    .dt-page-btn-disabled { opacity: .4; cursor: default; pointer-events: none; }
</style>
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
                        sortField: [{ field: '$order', direction: 'asc' }],
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
