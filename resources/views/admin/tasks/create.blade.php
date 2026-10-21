<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <x-favicon />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.DalyTrac Dashboard - New Task') }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite('resources/css/app.css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css">
    <style>
        /* Compact workflow selectors; retain the existing native change handlers. */
        #client-select, #project-select {
            width: 100%;
            max-width: 32rem;
            min-height: 44px;
            padding: 10px 36px 10px 12px;
            border: 1px solid #8292a6;
            border-radius: 10px;
            background-color: #f8fafc;
            color: #0B2638;
            font-size: 14px;
            line-height: 1.5;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .dt-task-selector-row {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            align-items: center;
            gap: 12px;
        }
        .dt-task-selector-row :is(#client-select, #project-select) {
            flex: 0 1 24rem;
            width: 24rem;
            max-width: 100%;
            margin-top: 0;
        }
        .dt-task-selector-row > button { min-height: 44px; }
        #client-select:focus, #project-select:focus {
            border-color: #2D7080;
            background-color: #fff;
            box-shadow: 0 0 0 3px rgb(45 112 128 / 12%);
        }
        /* Bound both OPEN selector lists independently of their closed controls. */
        .dt-task-selector-row .ts-wrapper {
            flex: 0 1 24rem;
            width: 24rem;
            max-width: 100%;
            min-width: 0;
        }
        .dt-task-selector-row .ts-control {
            min-height: 44px;
            padding: 10px 36px 10px 12px;
            border: 1px solid #8292a6;
            border-radius: 10px;
            background: #f8fafc;
            color: #0B2638;
            font-size: 14px;
            line-height: 1.5;
            box-shadow: none;
        }
        .dt-task-selector-row .ts-control .item {
            max-width: 100%;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
        }
        .dt-task-selector-row .ts-dropdown {
            border: 1px solid #8292a6;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 8px 24px rgb(11 38 56 / 12%);
        }
        .dt-task-selector-row .ts-dropdown-content {
            max-height: min(224px, 40vh);
            overflow-y: auto;
            overscroll-behavior: contain;
        }
        .dt-task-selector-row .ts-dropdown .option {
            padding: 9px 12px;
            font-size: 14px;
            overflow-wrap: anywhere;
        }
        /* ── smooth inline expand/collapse ── */
        .expander { display: grid; grid-template-rows: 0fr; opacity: 0; transition: grid-template-rows .32s ease, opacity .25s ease, margin-top .32s ease; margin-top: 0; }
        .expander.open { grid-template-rows: 1fr; opacity: 1; margin-top: 12px; }
        .expander > div { overflow: hidden; min-height: 0; }
        .toggle-chev { transition: transform .25s ease; }
        .is-open .toggle-chev { transform: rotate(180deg); }
        /* ── tag pills (employees / teams) ── */
        .tag-pick { position: relative; display: inline-flex; }
        .tag-pick input { position: absolute; opacity: 0; pointer-events: none; }
        .tag-pick span {
            display: inline-flex; align-items: center; gap: 7px;
            padding: 5px 12px 5px 10px; border-radius: 9999px;
            border: 1px solid #e2e8f0; background: #f8fafc;
            font-size: 12.5px; font-weight: 500; color: #475569;
            cursor: pointer; transition: background-color .15s, border-color .15s, color .15s;
            max-width: 100%;
        }
        .tag-pick span::before {
            content: ''; width: 7px; height: 7px; border-radius: 9999px;
            background: #cbd5e1; flex-shrink: 0; transition: background-color .15s;
        }
        .tag-pick:hover span { border-color: #2D7080; color: #0B2638; }
        .tag-pick input:checked + span { background: #2D7080; border-color: #2D7080; color: #fff; }
        .tag-pick input:checked + span::before { background: #fff; }
        .tag-pick input:focus-visible + span { outline: 2px solid #2D7080; outline-offset: 2px; }
    </style>
</head>
<body class="bg-[#f8fafc] flex h-screen overflow-hidden">

    {{-- Sidebar --}}
    @if(Auth::user()->hasRole('Admin'))
        <x-dashboard.sidebar />
    @else
        <x-employees.sidebar />
    @endif

    {{-- Main content area --}}
    <div class="flex flex-col flex-1 overflow-y-auto">

        {{-- Navbar --}}
        <x-dashboard.navbar />

        {{-- Page Content --}}
        <main class="dt-create-page p-4 sm:p-6 lg:p-8 bg-[#f8fafc] min-h-screen">

            {{-- Role-aware routes + workflow endpoints, resolved ONCE --}}
            @php
                $user = Auth::user();
                $routePrefix = 'admin';
                $cancelRoute = '#';
                if ($user->hasRole('Employee')) {
                    $routePrefix = 'employee';
                    $cancelRoute = route('employee.index');
                } elseif ($user->hasRole('Responsable de projet')) {
                    $routePrefix = 'Responsable_de_projet';
                    $cancelRoute = route('Responsable_de_projet.index');
                } elseif ($user->hasRole('Responsable de projet et gestionnaire')) {
                    $routePrefix = 'Responsable_gestionnaire';
                    $cancelRoute = route('Responsable_gestionnaire.index');
                } elseif ($user->hasRole('super_admin') || $user->hasRole('SuperAdmin')) {
                    $routePrefix = 'SuperAdmin';
                    $cancelRoute = route('SuperAdmin.index');
                } elseif ($user->hasRole('Admin')) {
                    $cancelRoute = route('admin.index');
                }

                $clientsStoreUrl = $projectsStoreTemplate = $clientProjectsTemplate = '';
                $allProjectsUrl = $taskStoreTemplate = $searchUrl = '';
                /* Relative paths: same-origin requests work regardless of host/APP_URL */
                $rel = function ($url) { $p = parse_url($url, PHP_URL_PATH); return $p ?: $url; };
                try { $clientsStoreUrl = $rel(route($routePrefix . '.clients.store')); } catch (\Exception $e) {}
                try { $projectsStoreTemplate = $rel(route($routePrefix . '.projects.store', ['client_id' => '__CID__'])); } catch (\Exception $e) {}
                try { $clientProjectsTemplate = $rel(route($routePrefix . '.filter.client.projects', ['client' => '__CID__'])); } catch (\Exception $e) {}
                try { $allProjectsUrl = $rel(route($routePrefix . '.filter.getAllProjects')); } catch (\Exception $e) {}
                try { $taskStoreTemplate = $rel(route($routePrefix . '.tasks.store', ['project_id' => '__PID__'])); } catch (\Exception $e) {}
                try { $searchUrl = $rel(route('search')); } catch (\Exception $e) {}
                $contactsTemplate = '/' . $routePrefix . '/get-contacts/__CID__';

                $preselectedProjectId = $project->id ?? null;
                $preselectedClientId = $project->client_id ?? null;
            @endphp

            <div class="dt-create-container mx-auto w-full">

                {{-- Header --}}
                <div class="flex items-center gap-3 mb-4">

                    <div class="min-w-0">
                        <h1 class="text-[19px] sm:text-[21px] font-bold tracking-tight text-[#0B2638] leading-tight truncate">{{ __('messages.New Task') }}</h1>
                        <p class="text-[12px] text-slate-500 truncate">{{ __('messages.Create a new task for the project') }}: {{ $project->name ?? '' }}</p>
                    </div>
                </div>

                {{-- Server-side validation errors (existing behavior) --}}
                @if ($errors->any())
                    <div class="mb-3 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl">
                        <div class="text-[13px] font-semibold mb-1">{{ __('messages.Mandatory informations') }}</div>
                        <ul class="list-disc list-inside space-y-0.5 text-[12.5px]">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- ── 1 · Client (optional) ─────────────────────────── --}}
                <section class="dt-card dt-create-panel p-4 sm:p-5 mb-3">
                    <h2 class="mb-3 text-[13px] font-bold text-[#0B2638]">1 · {{ __('messages.Client') }}</h2>
                    <div class="dt-task-selector-row">
                        <select id="client-select"
                            class="w-full min-w-0 rounded-lg border-slate-300 shadow-sm focus:border-[#2D7080] focus:ring-[#2D7080] text-sm py-2">
                        <option value="">{{ __('messages.No client') }}</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}" {{ (string)$preselectedClientId === (string)$client->id ? 'selected' : '' }}>
                                {{ $client->name }}
                            </option>
                        @endforeach
                    </select>
                        @if($clientsStoreUrl)
                        <button type="button" id="toggle-client-form" aria-expanded="false"
                                class="inline-flex flex-shrink-0 items-center gap-1.5 transition-colors dt-btn bg-[#0B2638]/[0.06] text-[#0B2638] ring-1 ring-[#0B2638]/10 hover:bg-[#0B2638]/[0.12] hover:ring-[#0B2638]/20">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                            {{ __('messages.Add New Client') }}
                            <svg class="toggle-chev w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
                        </button>
                        @endif
                    </div>

                    {{-- Inline client form: every field of the original Add Client page --}}
                    @if($clientsStoreUrl)
                    <div id="client-inline" class="expander" aria-hidden="true"><div>
                        <div class="rounded-xl border border-slate-200 bg-slate-50/60 p-4">
                            <div id="client-form-errors" class="hidden mb-3 bg-red-50 border border-red-200 text-red-700 px-3 py-2 rounded-lg text-[12.5px]"></div>
                            <form id="create-client-form" autocomplete="off">
                                @include('partials.inline-client-fields')
                            </form>
                            <div class="mt-3 flex justify-end gap-2">
                                <button type="button" id="cancel-client-inline" class="rounded-lg bg-white px-3.5 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">{{ __('messages.Cancel') }}</button>
                                <button type="button" id="submit-client-inline" class="rounded-lg bg-[#0B2638] px-3.5 py-2 text-sm font-semibold text-white hover:bg-[#14364d] disabled:opacity-40">{{ __('messages.Create') }}</button>
                            </div>
                        </div>
                    </div></div>
                    @endif
                </section>

                {{-- ── 2 · Project (required) ────────────────────────── --}}
                <section class="dt-card dt-create-panel p-4 sm:p-5 mb-3">
                    <h2 class="mb-3 text-[13px] font-bold text-[#0B2638]">2 · {{ __('messages.Project') }} <span class="text-red-500">*</span></h2>
                    <div class="dt-task-selector-row">
                        <select id="project-select" required
                            class="w-full min-w-0 rounded-lg border-slate-300 shadow-sm focus:border-[#2D7080] focus:ring-[#2D7080] text-sm py-2">
                        @if($preselectedProjectId)
                            <option value="{{ $preselectedProjectId }}" selected>{{ $project->name }}</option>
                        @else
                            <option value="" disabled selected>{{ __('messages.Select a project') }}</option>
                        @endif
                    </select>
                        @if($projectsStoreTemplate)
                        <button type="button" id="toggle-project-form" aria-expanded="false"
                                class="inline-flex flex-shrink-0 items-center gap-1.5 transition-colors dt-btn bg-[#0B2638]/[0.06] text-[#0B2638] ring-1 ring-[#0B2638]/10 hover:bg-[#0B2638]/[0.12] hover:ring-[#0B2638]/20">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                            {{ __('messages.New Project') }}
                            <svg class="toggle-chev w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
                        </button>
                        @endif
                    </div>
                    <p id="project-hint" class="mt-1.5 text-[12px] text-slate-400">{{ __('messages.Select a project') }}</p>

                    {{-- Inline project form: every field of the original Add Project page --}}
                    @if($projectsStoreTemplate)
                    <div id="project-inline" class="expander" aria-hidden="true"><div>
                        <div class="rounded-xl border border-slate-200 bg-slate-50/60 p-4">
                            <div id="project-form-errors" class="hidden mb-3 bg-red-50 border border-red-200 text-red-700 px-3 py-2 rounded-lg text-[12.5px]"></div>
                            <form id="create-project-form" autocomplete="off">
                                <p id="project-inline-client" class="text-[12px] text-slate-500 mb-3"></p>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div class="sm:col-span-2">
                                        <label class="block text-[12.5px] font-medium text-slate-700 mb-1">{{ __('messages.Project Name') }} <span class="text-red-500">*</span></label>
                                        <input type="text" id="ip-name" name="name" required class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none" />
                                    </div>
                                    <div>
                                        <label class="block text-[12.5px] font-medium text-slate-700 mb-1">{{ __('messages.Budget label') }}</label>
                                        <input type="number" step="0.01" min="0" name="budget" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none" />
                                    </div>
                                    <div>
                                        <label class="block text-[12.5px] font-medium text-slate-700 mb-1">{{ __('messages.CPH') }} <span class="text-red-500">*</span></label>
                                        <input type="number" step="0.01" min="0" name="cost_per_hour" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none" />
                                    </div>
                                    <div>
                                        <label class="block text-[12.5px] font-medium text-slate-700 mb-1">{{ __('messages.Est. Hours') }}</label>
                                        <input type="number" step="0.01" min="0" name="estimated_hours" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none" />
                                    </div>
                                    <div>
                                        <label class="block text-[12.5px] font-medium text-slate-700 mb-1">{{ __('messages.TVA %') }} <span class="text-red-500">*</span></label>
                                        <input type="number" step="0.01" min="0" name="tva" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none" />
                                    </div>
                                    <div>
                                        <label class="block text-[12.5px] font-medium text-slate-700 mb-1">{{ __('messages.Tag Color') }}</label>
                                        <input type="color" name="color" value="#FDE68A" class="w-full h-10 border border-slate-300 rounded-lg p-1 bg-white" />
                                        <p class="mt-1 text-[11px] text-slate-500">{{ __('messages.Tag Color helper') }}</p>
                                    </div>
                                    <div class="flex items-end pb-1">
                                        <label class="inline-flex items-center gap-2 text-[12.5px] font-medium text-slate-700">
                                            <input id="ip-regie" name="regie" type="checkbox" value="1" class="h-4 w-4 rounded border-slate-300 text-[#2D7080] focus:ring-[#2D7080]">
                                            {{ __('messages.Regie') }}
                                        </label>
                                    </div>
                                    <div class="sm:col-span-2">
                                        <label class="block text-[12.5px] font-medium text-slate-700 mb-1">{{ __('messages.Informations') }}</label>
                                        <textarea name="description" rows="2" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none"></textarea>
                                    </div>
                                </div>
                                <p class="text-[11px] font-bold uppercase tracking-[0.07em] text-slate-400 mt-4 mb-2">{{ __('messages.Teams label') }}</p>
                                <div class="flex flex-wrap gap-1.5 max-h-32 overflow-y-auto rounded-lg border border-slate-200 bg-white p-2">
                                    @foreach($teams as $team)
                                        <label class="tag-pick">
                                            <input type="checkbox" name="teams[]" value="{{ $team->id }}">
                                            <span>{{ $team->name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                                <p class="text-[11px] font-bold uppercase tracking-[0.07em] text-slate-400 mt-4 mb-2">{{ __('messages.Contacts label') }}</p>
                                <div id="ip-contacts" class="flex flex-wrap gap-1.5 max-h-32 overflow-y-auto rounded-lg border border-slate-200 bg-white p-2">
                                    <span class="text-[12px] text-slate-400" id="ip-contacts-empty">{{ __('messages.No client') }}</span>
                                </div>
                            </form>
                            <div class="mt-3 flex justify-end gap-2">
                                <button type="button" id="cancel-project-inline" class="rounded-lg bg-white px-3.5 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">{{ __('messages.Cancel') }}</button>
                                <button type="button" id="submit-project-inline" class="rounded-lg bg-[#0B2638] px-3.5 py-2 text-sm font-semibold text-white hover:bg-[#14364d] disabled:opacity-40">{{ __('messages.Create') }}</button>
                            </div>
                        </div>
                    </div></div>
                    @endif
                </section>

                {{-- ── 3 · Task (existing form, project comes from §2) ── --}}
                <section class="dt-card dt-create-panel p-4 sm:p-5">
                    <h2 class="text-[13px] font-bold text-[#0B2638] mb-3">3 · {{ __('messages.Task') }}</h2>

                    <form id="task-form" method="POST"
                        action="{{ $preselectedProjectId && $taskStoreTemplate ? str_replace('__PID__', $preselectedProjectId, $taskStoreTemplate) : '#' }}"
                        autocomplete="off">
                        @csrf
                        <input type="hidden" name="from" value="{{ old('from', request()->query('from')) }}" />

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="sm:col-span-2">
                                <label for="TaskName" class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('messages.Task Name') }} <span class="text-red-500">*</span>
                                </label>
                                <input type="text" id="TaskName" name="title" value="{{ old('title') }}" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#2D7080] focus:ring-[#2D7080] sm:text-sm" />
                            </div>
                            <div>
                                <label for="estimated_hours" class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('messages.Estimated Hours') }}
                                </label>
                                <input type="number" step="0.01" min="0" id="estimated_hours" name="estimated_hours" value="{{ old('estimated_hours') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#2D7080] focus:ring-[#2D7080] sm:text-sm" />
                            </div>
                            <div>
                                <label for="consumed_hours" class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('messages.Consumed Hours') }}
                                </label>
                                <input type="number" step="0.01" min="0" id="consumed_hours" name="consumed_hours" value="{{ old('consumed_hours', 0) }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#2D7080] focus:ring-[#2D7080] sm:text-sm" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                            <div>
                                <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('messages.Start Date') }}
                                </label>
                                <input type="date" id="start_date" name="start" value="{{ old('start') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#2D7080] focus:ring-[#2D7080] sm:text-sm" />
                            </div>
                            <div>
                                <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('messages.End Date') }}
                                </label>
                                <input type="date" id="end_date" name="end" value="{{ old('end') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#2D7080] focus:ring-[#2D7080] sm:text-sm" />
                            </div>
                        </div>

                        <div class="mt-4">
                            <label for="TaskDescription" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ __('messages.Task Description') }}
                            </label>
                            <textarea id="TaskDescription" name="description" rows="3"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#2D7080] focus:ring-[#2D7080] sm:text-sm">{{ old('description') }}</textarea>
                        </div>

                        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="block text-sm font-medium text-gray-700 mb-1.5">
                                    {{ __('messages.Assign Employees') }} <span class="text-red-500">*</span>
                                </p>
                                <div class="flex flex-wrap gap-1.5 max-h-40 overflow-y-auto rounded-lg border border-gray-100 p-2">
                                    @foreach($employee_users as $employee)
                                        <label class="tag-pick" title="{{$employee->name}}">
                                            <input type="checkbox" name="employees[]" value="{{$employee->id}}">
                                            <span class="truncate max-w-[160px]">{{$employee->name}}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                            <div>
                                <p class="block text-sm font-medium text-gray-700 mb-1.5">
                                    {{ __('messages.Assign Teams') }}
                                </p>
                                <div class="flex flex-wrap gap-1.5 max-h-40 overflow-y-auto rounded-lg border border-gray-100 p-2">
                                    @foreach($teams as $team)
                                        <label class="tag-pick" title="{{$team->name}}">
                                            <input type="checkbox" name="teams[]" value="{{$team->id}}">
                                            <span class="truncate max-w-[160px]">{{$team->name}}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="dt-create-actions mt-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-t border-gray-200 pt-4">
                            <div class="flex items-center">
                                <input id="add_to_planning" name="add_to_planning" type="checkbox" value="1"
                                    class="h-4 w-4 rounded border-gray-300 text-[#2D7080] focus:ring-[#2D7080]">
                                <label for="add_to_planning" class="ml-2 block text-sm text-gray-700">
                                    {{ __('messages.Add this task to planning') }}
                                </label>
                            </div>
                            <div class="flex justify-end items-center gap-3">
                                <a href="{{ $cancelRoute }}"
                                    class="rounded-lg bg-white px-3.5 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                                    {{ __('messages.Cancel') }}
                                </a>
                                <button type="submit" id="task-submit"
                                        title="{{ __('messages.Select a project') }}"
                                        class="rounded-lg bg-[#0B2638] px-3.5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#14364d] disabled:opacity-40 disabled:cursor-not-allowed">
                                    {{ __('messages.Create Task') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </section>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const clientsStoreUrl = @json($clientsStoreUrl);
        const projectsStoreTemplate = @json($projectsStoreTemplate);
        const clientProjectsTemplate = @json($clientProjectsTemplate);
        const allProjectsUrl = @json($allProjectsUrl);
        const taskStoreTemplate = @json($taskStoreTemplate);
        const searchUrl = @json($searchUrl);
        const contactsTemplate = @json($contactsTemplate);
        const toastClientOk = @json(__('messages.toast_created_client'));
        const toastClientFail = @json(__('messages.toast_failed_client'));
        const toastProjectOk = @json(__('messages.toast_created_project'));
        const toastProjectFail = @json(__('messages.toast_failed_project'));

        function appToast(type, msg) {
            try { if (window.AppToast) window.AppToast[type === 'error' ? 'error' : 'success'](msg); } catch {}
        }
        function firstServerMessage(d, fallback) {
            const errs = d && d.errors ? d.errors : null;
            if (errs) {
                for (const k of Object.keys(errs)) {
                    const v = Array.isArray(errs[k]) ? errs[k][0] : errs[k];
                    if (v) return v;
                }
            }
            if (d && d.message) return d.message;
            return fallback;
        }

        const clientSelect = document.getElementById('client-select');
        const projectSelect = document.getElementById('project-select');
        let projectPicker = null;
        let clientPicker = null;
        const projectHint = document.getElementById('project-hint');
        const taskForm = document.getElementById('task-form');
        const taskSubmit = document.getElementById('task-submit');

        const norm = (s) => (s ?? '').toString().toLowerCase().trim();
        const preselectedProject = { id: @json($preselectedProjectId), name: @json($project->name ?? '') };

        /* ── smooth expand/collapse ── */
        function wireExpander(btnId, panelId) {
            const btn = document.getElementById(btnId);
            const panel = document.getElementById(panelId);
            if (!btn || !panel) return null;
            const toggle = (force) => {
                const open = force !== undefined ? force : !panel.classList.contains('open');
                panel.classList.toggle('open', open);
                panel.setAttribute('aria-hidden', String(!open));
                btn.classList.toggle('is-open', open);
                btn.setAttribute('aria-expanded', String(open));
                return open;
            };
            btn.addEventListener('click', () => toggle());
            return { panel, toggle };
        }
        const clientExp = wireExpander('toggle-client-form', 'client-inline');
        const projectExp = wireExpander('toggle-project-form', 'project-inline');
        document.getElementById('cancel-client-inline')?.addEventListener('click', () => clientExp && clientExp.toggle(false));
        document.getElementById('cancel-project-inline')?.addEventListener('click', () => projectExp && projectExp.toggle(false));

        /* ── project list ── */
        function setProjectOptions(projects, keepId) {
            const prev = keepId ?? projectSelect.value;
            projectSelect.innerHTML = '';
            const empty = document.createElement('option');
            empty.value = '';
            empty.disabled = true;
            empty.textContent = @json(__('messages.Select a project'));
            projectSelect.appendChild(empty);
            (projects || []).forEach((p) => {
                const o = document.createElement('option');
                o.value = p.id;
                o.textContent = p.name;
                projectSelect.appendChild(o);
            });
            if (prev && (projects || []).some((p) => String(p.id) === String(prev))) {
                projectSelect.value = prev;
            } else if (prev && preselectedProject.id != null && String(prev) === String(preselectedProject.id)) {
                /* entry project (e.g. archived) not in the API list: keep it selectable */
                const o = document.createElement('option');
                o.value = preselectedProject.id;
                o.textContent = preselectedProject.name;
                projectSelect.appendChild(o);
                projectSelect.value = prev;
            } else {
                projectSelect.value = '';
                empty.selected = true;
            }
            if (projectPicker) {
                const selected = projectSelect.value;
                const options = Array.from(projectSelect.options).filter(o => o.value).map(o => ({ value: o.value, text: o.textContent }));
                projectPicker.clear(true);
                projectPicker.clearOptions();
                projectPicker.addOptions(options);
                projectPicker.setValue(selected, true);
            }
            syncTaskForm();
        }

        async function fetchJson(url) {
            const r = await fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
            if (!r.ok) throw new Error('Request failed: ' + r.status);
            const d = await r.json();
            return d.projects ?? d.data ?? d ?? [];
        }

        async function loadProjects() {
            const cid = clientSelect.value;
            projectHint.textContent = '…';
            try {
                let list = [];
                if (cid && clientProjectsTemplate) {
                    list = await fetchJson(clientProjectsTemplate.replace('__CID__', encodeURIComponent(cid)));
                } else if (!cid && allProjectsUrl) {
                    list = await fetchJson(allProjectsUrl);
                }
                setProjectOptions(Array.isArray(list) ? list : []);
            } catch (e) {
                console.error('Load projects:', e);
                setProjectOptions([]);
            }
            projectHint.textContent = @json(__('messages.Select a project'));
        }

        /* ── task form follows the selected project ── */
        function syncTaskForm() {
            const pid = projectSelect.value;
            if (pid && taskStoreTemplate) {
                taskForm.setAttribute('action', taskStoreTemplate.replace('__PID__', encodeURIComponent(pid)));
                taskSubmit.disabled = false;
            } else {
                taskForm.setAttribute('action', '#');
                taskSubmit.disabled = true;
            }
        }

        taskForm.addEventListener('submit', function (e) {
            if (!projectSelect.value) {
                e.preventDefault();
                projectSelect.focus();
            }
        });

        clientSelect.addEventListener('change', function () {
            /* changing client refreshes projects; stale selection is cleared */
            projectSelect.value = '';
            loadProjects();
        });
        projectSelect.addEventListener('change', syncTaskForm);

        function showErrors(boxId, errors) {
            const box = document.getElementById(boxId);
            box.innerHTML = '';
            const msgs = [];
            Object.values(errors || {}).forEach((v) => (Array.isArray(v) ? v : [v]).forEach((m) => { if (m) msgs.push(m); }));
            msgs.forEach((m) => {
                const div = document.createElement('div');
                div.textContent = m;
                box.appendChild(div);
            });
            box.classList.toggle('hidden', !msgs.length);
        }

        /* discover the id of a just-created client via the existing global search
           (client_number is unique per entreprise, name match as fallback) */
        async function discoverClientId(name, number) {
            const queries = [number, name].filter((q) => (q || '').trim().length >= 2);
            for (const q of queries) {
                try {
                    const r = await fetch(searchUrl + '?q=' + encodeURIComponent(q.trim()), { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                    if (!r.ok) continue;
                    const d = await r.json();
                    const clients = (d.results || []).filter((x) => x.type === 'client');
                    const exact = clients.find((x) => norm(x.title) === norm(name));
                    if (exact) return exact.id;
                    if (q === number && clients.length) return clients[0].id;
                } catch (e) { console.error('Discover client:', e); }
            }
            return null;
        }

        /* ── inline client creation → existing store route ── */
        const submitClientBtn = document.getElementById('submit-client-inline');
        if (submitClientBtn) {
            /* billing address mirrors the client address (same as the original page) */
            document.getElementById('ic-billing-same')?.addEventListener('change', function () {
                const on = this.checked;
                const map = [['address', 'billing_street'], ['city', 'billing_city'], ['zip', 'billing_zip'], ['country', 'billing_country']];
                const form = document.getElementById('create-client-form');
                map.forEach(([from, to]) => {
                    const src = form.querySelector('[name="' + from + '"]');
                    const dst = form.querySelector('[name="' + to + '"]');
                    if (!src || !dst) return;
                    dst.value = on ? src.value : '';
                    dst.disabled = on;
                });
            });
            /* extra contact entries, like the original page */
            document.getElementById('ic-add-contact')?.addEventListener('click', function () {
                const container = document.getElementById('ic-contacts');
                const first = container.querySelector('.contact-entry');
                if (!first) return;
                const entry = first.cloneNode(true);
                entry.querySelectorAll('input').forEach((i) => { i.value = ''; });
                const hr = document.createElement('hr');
                hr.className = 'border-t border-slate-200';
                const del = document.createElement('button');
                del.type = 'button';
                del.className = 'w-full px-3 py-2 bg-red-50 hover:bg-red-100 text-red-700 border border-red-200 rounded-lg text-[12.5px] font-semibold transition-colors';
                del.textContent = @json(__('messages.Delete Contact'));
                del.addEventListener('click', () => entry.remove());
                entry.appendChild(hr);
                entry.appendChild(del);
                container.appendChild(entry);
            });
            submitClientBtn.addEventListener('click', async () => {
                if (!clientsStoreUrl) return;
                const form = document.getElementById('create-client-form');
                showErrors('client-form-errors', null);
                submitClientBtn.disabled = true;
                try {
                    const r = await fetch(clientsStoreUrl, {
                        method: 'POST',
                        body: new FormData(form),
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf }
                    });
                    if (r.status === 422) {
                        const d = await r.json().catch(() => ({}));
                        showErrors('client-form-errors', d.errors);
                        appToast('error', firstServerMessage(d, toastClientFail));
                        return;
                    }
                    if (!r.ok) throw new Error('Request failed: ' + r.status);
                    const name = form.querySelector('[name="name"]').value.trim();
                    const number = form.querySelector('[name="client_number"]').value.trim();
                    const id = await discoverClientId(name, number);
                    appToast('success', toastClientOk);
                    if (id) {
                        const o = document.createElement('option');
                        o.value = id;
                        o.textContent = name;
                        if (clientPicker) {
                            clientPicker.addOption({ value: String(id), text: name });
                            clientPicker.setValue(String(id), true);
                        } else {
                            clientSelect.appendChild(o);
                            clientSelect.value = String(id);
                        }
                    }
                    if (clientExp) clientExp.toggle(false);
                    form.reset();
                    projectSelect.value = '';
                    await loadProjects();
                } catch (e) {
                    console.error('Create client:', e);
                    showErrors('client-form-errors', { error: [e.message] });
                    appToast('error', toastClientFail);
                } finally {
                    submitClientBtn.disabled = false;
                }
            });
        }

        /* ── inline project creation → existing store route (client via URL) ── */
        const submitProjectBtn = document.getElementById('submit-project-inline');
        if (submitProjectBtn) {
            /* regie suffix, like the original page */
            document.getElementById('ip-regie')?.addEventListener('change', function () {
                const nameInput = document.getElementById('ip-name');
                if (this.checked && !nameInput.value.includes(' - REGIE')) nameInput.value += ' - REGIE';
                else if (!this.checked) nameInput.value = nameInput.value.replace(' - REGIE', '');
            });
            /* client contacts of the selected client, like the original page */
            async function loadProjectContacts() {
                const box = document.getElementById('ip-contacts');
                const cid = clientSelect.value;
                box.innerHTML = '';
                if (!cid) {
                    const s = document.createElement('span');
                    s.className = 'text-[12px] text-slate-400';
                    s.textContent = @json(__('messages.No client'));
                    box.appendChild(s);
                    return;
                }
                try {
                    const r = await fetch(contactsTemplate.replace('__CID__', encodeURIComponent(cid)), { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                    if (!r.ok) throw new Error('Request failed: ' + r.status);
                    const list = await r.json();
                    (Array.isArray(list) ? list : []).filter((c) => c.name).forEach((c) => {
                        const label = document.createElement('label');
                        label.className = 'tag-pick';
                        label.title = c.name;
                        const input = document.createElement('input');
                        input.type = 'checkbox';
                        input.name = 'contacts[]';
                        input.value = c.id;
                        const span = document.createElement('span');
                        span.textContent = c.name;
                        label.appendChild(input);
                        label.appendChild(span);
                        box.appendChild(label);
                    });
                    if (!box.children.length) {
                        const s = document.createElement('span');
                        s.className = 'text-[12px] text-slate-400';
                        s.textContent = '—';
                        box.appendChild(s);
                    }
                } catch (e) { console.error('Load contacts:', e); }
            }
            document.getElementById('toggle-project-form')?.addEventListener('click', () => {
                const cname = clientSelect.selectedOptions[0]?.textContent?.trim() || '';
                document.getElementById('project-inline-client').textContent =
                    (clientSelect.value ? cname : @json(__('messages.No client')));
                loadProjectContacts();
            });
            clientSelect.addEventListener('change', () => {
                if (document.getElementById('project-inline')?.classList.contains('open')) loadProjectContacts();
            });
            submitProjectBtn.addEventListener('click', async () => {
                if (!projectsStoreTemplate) return;
                const form = document.getElementById('create-project-form');
                showErrors('project-form-errors', null);
                submitProjectBtn.disabled = true;
                try {
                    const cid = clientSelect.value;
                    const url = projectsStoreTemplate.replace('__CID__', encodeURIComponent(cid || ''));
                    const r = await fetch(url.replace(/\/$/, ''), {
                        method: 'POST',
                        body: new FormData(form),
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf }
                    });
                    if (r.status === 422) {
                        const d = await r.json().catch(() => ({}));
                        showErrors('project-form-errors', d.errors);
                        appToast('error', firstServerMessage(d, toastProjectFail));
                        return;
                    }
                    if (!r.ok) throw new Error('Request failed: ' + r.status);
                    const name = form.querySelector('[name="name"]').value.trim();
                    await loadProjects();
                    const opts = projectPicker
                        ? Object.values(projectPicker.options).map(o => ({ value: o.value, textContent: o.text }))
                        : Array.from(projectSelect.options);
                    const exact = opts.find((o) => o.value && norm(o.textContent) === norm(name));
                    const newest = opts.filter((o) => o.value).reduce((m, o) => (!m || Number(o.value) > Number(m.value) ? o : m), null);
                    const pick = exact || newest;
                    if (pick) {
                        if (projectPicker) projectPicker.setValue(pick.value, true);
                        else projectSelect.value = pick.value;
                        syncTaskForm();
                    }
                    appToast('success', toastProjectOk);
                    if (projectExp) projectExp.toggle(false);
                    form.reset();
                } catch (e) {
                    console.error('Create project:', e);
                    showErrors('project-form-errors', { error: [e.message] });
                    appToast('error', toastProjectFail);
                } finally {
                    submitProjectBtn.disabled = false;
                }
            });
        }

        /* ── init: project arrives preselected via the entry URL ── */
        if (window.TomSelect) {
            clientPicker = new TomSelect(clientSelect, {
                create: false,
                maxOptions: null,
                allowEmptyOption: true,
                sortField: [{ field: '$order', direction: 'asc' }],
                placeholder: @json(__('messages.No client')),
            });
            projectPicker = new TomSelect(projectSelect, {
                create: false,
                maxOptions: null,
                sortField: [{ field: '$order', direction: 'asc' }],
                placeholder: @json(__('messages.Select a project')),
            });
        }
        syncTaskForm();
        loadProjects();
    });
    </script>
</body>
</html>
