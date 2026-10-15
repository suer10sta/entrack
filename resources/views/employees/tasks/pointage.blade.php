<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('messages.Pointage Dashboard') }}</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Tom Select CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">

    <style>
        [x-cloak] { display: none !important; }

        /* Tom Select custom styling overrides */
        .ts-wrapper.single .ts-control {
            border: 1px solid #e2e8f0 !important; /* border-slate-200 */
            border-radius: 0.5rem !important; /* rounded-lg */
            padding: 0.5rem 0.75rem !important;
            font-size: 0.875rem !important; /* text-sm */
            background-color: #fff !important;
            min-height: 38px !important;
            color: #475569 !important; /* text-slate-600 */
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05) !important; /* shadow-sm */
        }
        .ts-wrapper.single .ts-control:focus,
        .ts-wrapper.single.focus .ts-control {
            border-color: #2D7080 !important;
            box-shadow: 0 0 0 2px rgba(45, 112, 128, 0.25) !important;
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
            background-color: rgba(45, 112, 128, 0.10) !important;
            color: #0B2638 !important;
        }
        .ts-dropdown .option:hover {
            background-color: #f8fafc !important;
        }
        .ts-control input {
            font-size: 0.875rem !important;
        }
        
        /* Custom Scrollbar */
        .custom-scrollbar::-webkit-scrollbar { height: 6px; width: 7px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        /* Pin button */
        .pin-btn.is-pinned {
            fill: #eab308 !important;
            color: #eab308 !important;
        }
        .pin-btn.is-pinned svg {
            fill: #eab308;
        }

        /* Collapse toggle */
        .collapse-toggle.is-collapsed .chevron-icon {
            transform: rotate(-90deg);
        }
        .project-tasks-collapsed {
            display: none !important;
        }

        /* Modal Styles */
        .modal { display: none; position: fixed; z-index: 50; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.5); backdrop-filter: blur(4px); }
        .modal-content { animation: slideDown 0.3s ease-out; }
        @keyframes slideDown { from { transform: translateY(-50px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }

        /* Updated Custom Alert Modal to have stronger shadow */
        .modal-content-custom {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 600px;
            text-align: center;
            position: relative;
            border-radius: 0.75rem; /* rounded-xl */
            /* Stronger shadow for the modal */
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        .close-button {
            color: #94a3b8;
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .close-button:hover { color: #475569; }

        /* Mobile: tables -> modern 2-col cards (no horizontal scroll) */
        @media (max-width: 1024px) {
            .custom-scrollbar {
                overflow-x: visible !important;
            }

            /* Shared card table pattern */
            #tasksTable thead,
            #taskTable thead,
            #editableTable thead {
                display: none;
            }

            #tasksTable tbody,
            #taskTable tbody,
            #editableTable tbody {
                display: block;
                width: 100%;
            }

            #tasksTable tbody tr.task-row,
            #taskTable tbody tr.task-timer-row,
            #editableTable tbody tr {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 10px;
                margin-bottom: 16px;
                padding: 12px;
                border: 1px solid rgba(148, 163, 184, 0.55);
                border-radius: 16px;
                background: linear-gradient(180deg, rgba(255,255,255,0.95), rgba(248,250,252,0.92));
                box-shadow: 0 18px 45px rgba(15,23,42,0.10), 0 3px 10px rgba(15,23,42,0.08);
            }

            /* Group header rows (project name) should stay as simple headers */
            #tasksTable tbody tr.project-group-row {
                display: block !important;
                margin: 14px 0 10px;
                padding: 0;
                border: 0;
                border-radius: 0;
                background: transparent;
                box-shadow: none;
            }
            #tasksTable tbody tr.project-group-row td {
                display: block;
                width: 100%;
                padding: 6px 2px !important;
                border: 0;
                background: transparent;
                text-align: left !important;
                white-space: nowrap !important;
                overflow-x: auto !important;
                -webkit-overflow-scrolling: touch;
            }

            #tasksTable tbody tr.task-row td,
            #taskTable tbody tr.task-timer-row td,
            #editableTable tbody td {
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

            #tasksTable tbody td[data-span="2"],
            #taskTable tbody td[data-span="2"],
            #editableTable tbody td[data-span="2"] {
                grid-column: span 2 / span 2;
            }

            #tasksTable tbody td[data-label]::before,
            #taskTable tbody td[data-label]::before,
            #editableTable tbody td[data-label]::before {
                content: attr(data-label);
                display: block;
                font-size: 11px;
                font-weight: 700;
                letter-spacing: .06em;
                text-transform: uppercase;
                color: #64748b;
                margin-bottom: 6px;
            }

            /* Totals row in Task Timer export table */
            #taskTable tbody tr.total-row {
                display: block !important;
                margin: 10px 0 18px;
                padding: 12px;
                border: 1px solid rgba(148, 163, 184, 0.55);
                border-radius: 16px;
                background: #ffffff;
                box-shadow: 0 12px 30px rgba(15,23,42,0.10), 0 2px 8px rgba(15,23,42,0.08);
            }
            #taskTable tbody tr.total-row td {
                display: block;
                width: 100%;
                padding: 8px 10px !important;
                border: 0;
                background: transparent;
                text-align: left !important;
            }

            /* Actions in employee tasks table: keep buttons readable */
            #tasksTable tbody td[data-label="{{ __('messages.Action') }}"] .flex,
            #tasksTable tbody td[data-label="Action"] .flex {
                justify-content: center !important;
                flex-wrap: wrap !important;
                gap: 8px !important;
            }

            /* Tasks accordion: collapsed shows only name + action (two columns) */
            #tasksTable tbody tr.task-row.is-collapsed td:not(.task-name-cell):not(.task-action-cell) {
                display: none !important;
            }
            #tasksTable tbody tr.task-row td.task-name-cell,
            #tasksTable tbody tr.task-row td.task-action-cell {
                align-self: start;
                grid-column: span 2 / span 2;
            }
            /* Remove pseudo labels on the header row cells for a cleaner compact line */
            #tasksTable tbody tr.task-row td.task-name-cell[data-label]::before,
            #tasksTable tbody tr.task-row td.task-action-cell[data-label]::before {
                content: none !important;
            }
            #tasksTable tbody tr.task-row td.task-name-cell .task-chevron {
                transition: transform 180ms ease;
            }
            #tasksTable tbody tr.task-row[aria-expanded="true"] td.task-name-cell .task-chevron {
                transform: rotate(180deg);
            }

            /* Make Start/Stop buttons more readable on mobile */
            #tasksTable tbody tr.task-row td.task-action-cell .start-button,
            #tasksTable tbody tr.task-row td.task-action-cell .stop-button {
                display: inline-flex !important;
                align-items: center;
                justify-content: center;
                gap: 8px;
                min-width: 110px;
                min-height: 38px;
                padding: 8px 14px !important;
                font-size: 13px !important;
                line-height: 1 !important;
                border-radius: 12px !important;
                white-space: nowrap !important;
                box-shadow: 0 10px 22px rgba(15, 23, 42, 0.10), 0 2px 6px rgba(15, 23, 42, 0.08);
            }
            #tasksTable tbody tr.task-row td.task-action-cell .flex {
                justify-content: center !important;
            }
            #tasksTable tbody tr.task-row td.task-action-cell input[name="comment"] {
                min-width: 0;
                width: 100%;
            }
        }
    </style>
</head>
<body class="bg-[#f8fafc] flex h-screen overflow-hidden font-sans antialiased text-slate-600">
    
    <audio id="alertSound" src="{{ asset('audio/alert_sound.mp3') }}" preload="auto"></audio>

    <div class="flex flex-1 h-full overflow-hidden">
        {{-- Sidebar --}}
        <x-employees.sidebar />


        <div class="flex flex-col flex-1 overflow-y-auto w-full">
        {{-- Navbar (same design as Admin Dashboard) --}}
        <x-employees.navbar />

        {{-- Main Content --}}
        <main class="flex flex-col flex-1 overflow-y-auto w-full">
            <div class="p-4 sm:p-6 lg:px-20 lg:py-8 mx-auto w-full flex flex-col flex-1">

            {{-- Page heading (same design as Admin Dashboard) --}}
            <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-5">
                <div class="min-w-0">
                    <!-- <p class="dt-overline mb-1">{{ now()->format('l, d F Y') }}</p> -->
                    <h1 class="text-[22px] sm:text-[24px] font-bold tracking-tight text-[#0B2638] leading-tight">
                        {{ __('messages.Pointage Dashboard') }}
                    </h1>
                    <p class="text-[13.5px] text-slate-500 mt-1">{{ __('messages.Track time per task and project with real-time timers and logs.') }}</p>
                </div>
            </div>

            <div class="mb-6">
                @if(session('alertmessage'))
                    <div class="rounded-xl bg-red-50 p-4 border border-red-200 shadow-md flex items-center gap-3">
                        <i class="fas fa-exclamation-circle text-red-500"></i>
                        <p class="text-sm text-red-700">{{ session('alertmessage') }}</p>
                        <button type="button" class="ml-auto text-red-400 hover:text-red-600" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>
                    </div>
                @elseif(session('message'))
                    <div class="rounded-xl bg-green-50 p-4 border border-green-200 shadow-md flex items-center gap-3">
                        <i class="fas fa-check-circle text-green-500"></i>
                        <p class="text-sm text-green-700">{{ session('message') }}</p>
                        <button type="button" class="ml-auto text-green-400 hover:text-green-600" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>
                    </div>
                @endif
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                <div class="dt-card dt-card-hover p-4 sm:p-5 flex items-center gap-3.5">
                    <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-[#0B2638]/[0.06] text-[#0B2638] ring-1 ring-[#0B2638]/10 flex-shrink-0">
                        <i class="fas fa-suitcase text-base"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-[0.07em] truncate">{{ __('messages.Active Projects') }}</p>
                        <h2 class="text-[26px] leading-none font-bold text-[#0B2638] tracking-tight mt-1">{{ $projects->count() }}</h2>
                    </div>
                </div>

                <div class="dt-card dt-card-hover p-4 sm:p-5 flex items-center gap-3.5">
                    <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-[#0B2638]/[0.06] text-[#0B2638] ring-1 ring-[#0B2638]/10 flex-shrink-0">
                        <i class="fas fa-check-square text-base"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-[0.07em] truncate">{{ __('messages.Tasks') }}</p>
                        <h2 class="text-[26px] leading-none font-bold text-[#0B2638] tracking-tight mt-1">{{ $employees_tasks->count() }}</h2>
                    </div>
                </div>

                <div class="dt-card dt-card-hover p-4 sm:p-5 flex items-center gap-3.5 relative overflow-hidden">
                    <span class="absolute left-0 top-0 h-full w-1 bg-[#E7B84B]"></span>
                    <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-[#E7B84B]/20 text-[#0B2638] ring-1 ring-[#E7B84B]/40 flex-shrink-0">
                        <i class="fas fa-users text-base"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-[0.07em] truncate">{{ __('messages.Teams') }}</p>
                        <h2 class="text-[26px] leading-none font-bold text-[#0B2638] tracking-tight mt-1">0</h2>
                    </div>
                </div>
            </div>

            <div class="dt-card mb-6 overflow-hidden">
                <div class="px-5 sm:px-6 pt-5 pb-1">
                    @php
                        $employee_id = App\Models\Employees::where('user_id', Auth::user()->id)->first()->id;
                        $today = \Carbon\Carbon::today()->toDateString();
                        $totalDurationAllTasks = App\Models\tasks_timer::where('employee_id', $employee_id)
                            ->whereDate('start_date', '=', $today)
                            ->whereNotNull('duration')
                            ->sum('duration');
                        $totalHours = intdiv($totalDurationAllTasks, 60);
                        $totalMinutes = $totalDurationAllTasks % 60;
                        
                        $tasksByProject = $employees_tasks->groupBy(function($task) {
                            return App\Models\Tasks::where('id', $task->task_id)->first()->project_name;
                        });
                    @endphp

                    <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:justify-between mb-4">
                        <div class="min-w-0">
                            <h3 class="dt-section-title">{{ __('messages.Employee Tasks Table') }}</h3>
                        </div>
                        <div class="dt-badge dt-badge-neutral flex-shrink-0">
                            <span class="dt-dot"></span>
                            {{ __('messages.Today\'s Duration') }}:&nbsp;<span class="text-[#0B2638]">{{ (isset($totalHours) && $totalHours > 0) || (isset($totalMinutes) && $totalMinutes > 0) ? $totalHours . 'h' . sprintf('%02d', $totalMinutes) . 'm' : '00h00m' }}</span>
                        </div>
                    </div>

                    <div class="mb-4">
                        <select id="projectFilter" class="block w-full sm:max-w-md rounded-xl border-slate-200 text-sm focus:border-[#2D7080] focus:ring-[#2D7080]/25 bg-white text-slate-600 shadow-sm py-2 px-3 outline-none">
                            <option value="">{{ __('messages.All Projects') }}</option>
                            @foreach($projects as $project)
                            <option value="{{ $project->name }}">{{ $project->name }}</option>
                             @endforeach
                            </select>
                        </div>
                    </div>

                <div class="overflow-x-auto custom-scrollbar border-t border-slate-100">
                    <table id="tasksTable" class="w-full text-left border-collapse">
                        <thead class="bg-slate-50/80">
                            <tr class="dt-table-head">
                                <th class="px-4 py-3 text-left min-w-[220px]">{{ __('messages.Task Name') }}</th>
                                <th class="px-4 py-3 text-left whitespace-nowrap">{{ __('messages.Total Duration') }}</th>
                                <th class="px-4 py-3 text-left whitespace-nowrap">{{ __('messages.Estimated Hours') }}</th>
                                <th class="px-4 py-3 text-left whitespace-nowrap">{{ __('messages.Difference') }}</th>
                                <th class="px-4 py-3 text-left min-w-[130px]">{{ __('messages.Progress') }}</th>
                                <th class="px-4 py-3 text-left min-w-[180px]">{{ __('messages.Comments') }}</th>
                                <th class="px-4 py-3 text-left whitespace-nowrap">{{ __('messages.Today\'s Duration') }}</th>
                                <th class="px-4 py-3 text-right min-w-[210px]">{{ __('messages.Action') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($tasksByProject as $projectName => $projectTasks)
                                <tr class="project-group-row" data-project-name="{{ $projectName }}">
                                    <td colspan="8" class="px-4 py-2 bg-[#0B2638]/[0.05] border-y border-[#0B2638]/10">
                                        <div class="flex items-center justify-between gap-2">
                                            <div class="flex items-center gap-2 min-w-0 overflow-hidden">
                                                <button type="button" class="pin-btn shrink-0 text-slate-400 hover:text-yellow-400 transition-colors" data-project="{{ $projectName }}" title="{{ __('messages.Pin project') }}">
                                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                                                    </svg>
                                                </button>
                                                <span class="text-xs font-bold text-[#0B2638] uppercase tracking-[0.06em] truncate">{{ strtoupper($projectName) }}</span>
                                            </div>
                                            <button type="button" class="collapse-toggle shrink-0 text-slate-400 hover:text-slate-600 transition-transform duration-200" data-project="{{ $projectName }}" title="{{ __('messages.Toggle tasks') }}">
                                                <svg class="w-4 h-4 chevron-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                                    <path d="M6 9l6 6 6-6" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @foreach($projectTasks as $task)
                                    @php
                                        $task_id = $task->task_id;
                                        $task_name = App\Models\Tasks::where('id', $task_id)->first();
                                        $task_timer = App\Models\tasks_timer::where('task_employee_id', $task->id)->whereNull('end_date')->first();
                                        $employee_id = App\Models\Employees::where('user_id', Auth::user()->id)->first()->id;
                                        $routePrefix = Auth::user()->hasRole('Employee')
                                            ? 'employee'
                                            : (Auth::user()->hasRole('Responsable de projet')
                                                ? 'Responsable_de_projet'
                                                : (Auth::user()->hasRole('Responsable de projet et gestionnaire')
                                                    ? 'Responsable_gestionnaire'
                                                    : 'employee'));
                                    @endphp
                                    <tr class="task-row hover:bg-slate-50/70 transition-colors" data-task-row aria-expanded="true" data-project-name="{{ $projectName }}" data-active-timer="{{ $task_timer ? 'true' : 'false' }}">
                                        <td data-label="{{ __('messages.Task Name') }}" class="px-4 py-3 task-name-cell">
                                            <div class="flex items-center justify-between gap-3">
                                                <a href="#" class="min-w-0 truncate text-sm font-medium text-[#2D7080] hover:text-[#0B2638] hover:underline" title="{{ $task->task_name }}">{{ $task->task_name }}</a>
                                                <button type="button" class="shrink-0 text-slate-400 hover:text-slate-600 sm:hidden" data-task-collapse-toggle aria-label="Toggle task details">
                                                    <svg class="task-chevron w-5 h-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                        <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>
                                        <td data-label="{{ __('messages.Total Duration') }}" class="px-4 py-3 text-sm text-slate-600 whitespace-nowrap">
                                            @php 
                                                $tasktotalduration = App\Models\tasks_timer::where('task_id', $task_id)->where('employee_id', $employee_id)->sum('duration');
                                                $taskhours = floor($tasktotalduration / 60);
                                                $taskminutes = $tasktotalduration % 60;
                                            @endphp
                                            {{$taskhours}}h{{$taskminutes}}m
                                        </td>
                                        <td data-label="{{ __('messages.Estimated Hours') }}" class="px-4 py-3 text-sm text-slate-600 whitespace-nowrap">{{$task_name->estimated_hours ?? '-'}} h</td>
                                        <td data-label="{{ __('messages.Difference') }}" class="px-4 py-3 text-sm text-slate-500 whitespace-nowrap">
                                            @php
                                                $taskEstimatedMinutes = ($task_name->estimated_hours ?? 0) * 60;
                                                $taskConsumedMinutes = ($task_name->consumed_hours ?? 0) * 60;
                                                $taskdifference = $taskEstimatedMinutes - $taskConsumedMinutes - $tasktotalduration;
                                                $hours = floor(abs($taskdifference) / 60);
                                                $minutes = abs($taskdifference) % 60;
                                                $taskestimatedMinutes = max(0, (($task_name->estimated_hours ?? 0) - ($task_name->consumed_hours ?? 0)) * 60);
                                                $taskprogressPercentage = $taskestimatedMinutes > 0 ? ($tasktotalduration / $taskestimatedMinutes) * 100 : 0;
                                                $taskformattedProgressPercentage = number_format($taskprogressPercentage, 2);
                                            @endphp
                                            @if($task_name->estimated_hours !== null)
                                                @if($taskdifference > 0)
                                                    <span class="dt-badge dt-badge-success">+{{$hours}}h{{$minutes}}m</span>
                                                @elseif($taskdifference < 0)
                                                    <span class="dt-badge dt-badge-danger">-{{$hours}}h{{$minutes}}m</span>
                                                @else
                                                    <span class="dt-badge dt-badge-navy">{{$hours}}h{{$minutes}}m</span>
                                                @endif
                                            @else
                                                <span class="text-xs text-slate-400">{{ __('messages.No estimated hours inserted') }}</span>
                                            @endif
                                        </td>
                                        <td data-label="{{ __('messages.Progress') }}" class="px-4 py-3 align-middle">
                                            <div class="flex items-center gap-2">
                                                <div class="w-full max-w-[140px] bg-slate-200 rounded-full h-1.5 overflow-hidden">
                                                    <div class="h-1.5 rounded-full bg-[#2D7080]" style="width: {{ min($taskprogressPercentage, 100) }}%"></div>
                                                </div>
                                                <span class="text-[11px] font-semibold text-slate-500 whitespace-nowrap">{{ $taskformattedProgressPercentage }}%</span>
                                            </div>
                                        </td>
                                        <td data-label="{{ __('messages.Comments') }}" data-span="2" class="px-4 py-3 text-sm text-slate-600">
                                            <span class="block min-w-0 max-w-[280px] break-words leading-snug">{{ $task->comment ?? __('messages.No Comment') }}</span>
                                        </td>
                                        <td data-label="{{ __('messages.Today\'s Duration') }}" class="px-4 py-3 text-sm font-bold text-[#0B2638] whitespace-nowrap">
                                            @php
                                                $today = \Carbon\Carbon::today()->toDateString();
                                                $totalDurationToday = App\Models\tasks_timer::where('task_employee_id', $task->id)->whereNotNull('duration')->whereDate('start_date', '=', $today)->sum('duration');
                                                $hours = intdiv($totalDurationToday, 60);
                                                $minutes = $totalDurationToday % 60;
                                            @endphp
                                            <span id="duration-{{$task->id}}">{{ $totalDurationToday ? $hours . 'h' . sprintf('%02d', $minutes).'m' : '00h00m' }}</span>
                                        </td>
                                        <td data-label="{{ __('messages.Action') }}" class="px-4 py-3 text-right task-action-cell">
                                            <div class="flex justify-end gap-2 flex-wrap">
                                                @php $routePrefix = Auth::user()->hasRole('Employee') ? 'employee' : (Auth::user()->hasRole('Responsable de projet') ? 'Responsable_de_projet' : 'Responsable_gestionnaire'); @endphp

                                                @can('Add manual timer')
                                                    <button type="button"
                                                            class="manual-timer-btn bg-[#2D7080] hover:bg-[#245f6d] text-white text-xs font-semibold py-1.5 px-3 rounded-lg shadow-sm flex items-center justify-center transition-colors"
                                                            title="{{ __('messages.Add Manual Time') }}"
                                                            data-task-id="{{ $task_id }}"
                                                            data-employee-id="{{ $employee_id }}">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M12 6a6 6 0 016 6 6 6 0 11-6-6z" />
                                                        </svg>
                                                    </button>
                                                @endcan

                                                @unless($task_timer)
                                                    <form class="inline-flex items-center gap-2 flex-wrap" action="{{ route($routePrefix.'.tasks.start', ['tasks' => $task->id, 'employee_id' => $employee_id]) }}" method="POST"
                                                        data-chrono-start data-chrono-employee="{{ $employee_id }}"
                                                        data-chrono-emp-task="{{ $task->id }}" data-chrono-task="{{ $task_id }}">
                                                        @csrf
                                                        <input type="text"
                                                               name="comment"
                                                               placeholder="{{ __('messages.Enter comment') }}"
                                                               class="border border-slate-300 rounded-lg px-2 py-1 text-xs text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] w-40 max-w-full min-w-0">
                                                        <button type="submit" class="start-button bg-[#2D7080] hover:bg-[#245f6d] text-white text-xs font-semibold py-1.5 px-3 rounded-lg shadow-sm whitespace-nowrap transition-colors" id="start-btn" data-task-id="{{$task->id}}">
                                                            {{ __('messages.Start') }}
                                                        </button>
                                                    </form>
                                                @endunless

                                                @if($task_timer)
                                                    <form class="inline-block stop-form" action="{{ route($routePrefix.'.tasks.stop', ['tasks' => $task->id, 'employee_id' => $employee_id]) }}" method="POST"
                                                        data-chrono-stop data-chrono-employee="{{ $employee_id }}"
                                                        data-chrono-emp-task="{{ $task->id }}" data-chrono-task="{{ $task_id }}">
                                                        @csrf
                                                        <input type="hidden" name="stop_comment" class="stop-comment-field">
                                                        <button type="submit" class="stop-button bg-red-500 hover:bg-red-600 text-white text-xs font-semibold py-1.5 px-3 rounded-lg shadow-sm whitespace-nowrap transition-colors" data-task-id="{{$task->id}}">
                                                            {{ __('messages.Stop') }}
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Manual Timer Modal --}}
            <div id="manualTimerModal" class="modal">
                <div class="modal-content-custom">
                    <span id="manualTimerClose" class="close-button">&times;</span>
                    <h2 class="text-lg font-semibold text-slate-800 mb-4">{{ __('messages.Add Manual Time') }}</h2>
                    <form id="manualTimerForm" class="space-y-4">
                        @csrf
                        <input type="hidden" id="manualTimerTaskId" name="task_id">
                        <input type="hidden" id="manualTimerEmployeeId" name="employee_id">

                        <div class="text-left">
                            <label for="manualStartDate" class="block text-sm font-medium text-slate-700 mb-1">{{ __('messages.Start Date and Time') }}</label>
                            <input type="datetime-local" id="manualStartDate" name="start_date" class="border border-slate-300 rounded-lg px-3 py-2 w-full text-sm focus:outline-none focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080]" required>
                        </div>
                        <div class="text-left">
                            <label for="manualEndDate" class="block text-sm font-medium text-slate-700 mb-1">{{ __('messages.End Date and Time') }}</label>
                            <input type="datetime-local" id="manualEndDate" name="end_date" class="border border-slate-300 rounded-lg px-3 py-2 w-full text-sm focus:outline-none focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080]" required>
                        </div>

                        <div class="flex justify-end gap-2 pt-2">
                            <button type="button" id="manualTimerCancelBtn" class="px-4 py-2 text-sm font-medium text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50">{{ __('messages.Close button') }}</button>
                            <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-[#2D7080] rounded-lg hover:bg-[#245f6d]">{{ __('messages.Save Timer') }}</button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Stop Comment Modal --}}
            <div id="stopCommentModal" class="modal">
                <div class="modal-content-custom">
                    <span id="stopCommentClose" class="close-button">&times;</span>
                    <div class="flex items-center justify-center mb-3">
                        <div class="bg-red-100 rounded-full p-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                            </svg>
                        </div>
                    </div>
                    <h2 class="text-lg font-semibold text-slate-800 mb-2">{{ __('messages.Stop Task') }}</h2>
                    <p class="text-sm text-slate-500 mb-4">{{ __('messages.Please leave a comment describing the work done before stopping the task.') }}</p>
                    <div class="text-left mb-4">
                        <div class="flex justify-between items-center mb-1">
                            <label for="stopCommentText" class="block text-sm font-medium text-slate-700">{{ __('messages.Comment') }} <span class="text-red-500">*</span></label>
                            <div class="flex gap-3 items-center">
                                <button type="button" id="stopCommentAiBtn" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 flex items-center gap-1 transition-colors opacity-50 cursor-not-allowed" title="{{ __('messages.Correct with AI') }}" disabled>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                                    </svg>
                                    <span id="stopCommentAiBtnText">{{ __('messages.Correct with AI') }}</span>
                                </button>
                                <button type="button" id="stopCommentTranslateBtn" class="text-xs font-semibold text-[#2D7080] hover:text-[#0B2638] flex items-center gap-1 transition-colors opacity-50 cursor-not-allowed" title="{{ __('messages.Translate to Dutch') }}" disabled>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5c-.006 3.125-1.026 6.042-2.751 8.498m-1.782-2.5A18.022 18.022 0 014.888 5h7.863z" />
                                    </svg>
                                    <span id="stopCommentTranslateBtnText">{{ __('messages.Translate to Dutch') }}</span>
                                </button>
                            </div>
                        </div>
                        <textarea id="stopCommentText" rows="3" maxlength="500" placeholder="{{ __('messages.Describe what you worked on...') }}" spellcheck="true" class="border border-slate-300 rounded-lg px-3 py-2 w-full text-sm focus:outline-none focus:ring-2 focus:ring-red-400 resize-none transition-colors"></textarea>
                        <p id="stopCommentError" class="text-xs text-red-500 mt-1 hidden">{{ __('messages.Comment is required to stop the task.') }}</p>
                        <p id="stopCommentAiFeedback" class="text-xs mt-1 hidden"></p>
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" id="stopCommentCancelBtn" class="px-4 py-2 text-sm font-medium text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors">{{ __('messages.Cancel') }}</button>
                        <button type="button" id="stopCommentConfirmBtn" class="px-4 py-2 text-sm font-medium text-white bg-red-500 rounded-lg hover:bg-red-600 transition-colors opacity-50 cursor-not-allowed" disabled>{{ __('messages.Confirm Stop') }}</button>
                    </div>
                </div>
            </div>

            {{-- Spell-Check Confirmation Modal --}}
            <div id="spellCheckModal" class="modal" style="z-index: 60;">
                <div class="modal-content-custom" style="max-width: 420px;">
                    <div class="flex items-center justify-center mb-3">
                        <div class="bg-amber-100 rounded-full p-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <h2 class="text-lg font-semibold text-slate-800 mb-2">{{ __('messages.Check Spelling?') }}</h2>
                    <p class="text-sm text-slate-500 mb-5">{{ __('messages.Would you like to verify the spelling of your comment before submitting?') }}</p>
                    <div class="flex flex-col gap-2">
                        <button type="button" id="spellCheckVerifyBtn" class="w-full px-4 py-2.5 text-sm font-medium text-white bg-[#2D7080] rounded-lg hover:bg-[#245f6d] transition-colors flex items-center justify-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            {{ __('messages.Yes, verify spelling') }}
                        </button>
                        <button type="button" id="spellCheckSkipBtn" class="w-full px-4 py-2.5 text-sm font-medium text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors">
                            {{ __('messages.No, submit as is') }}
                        </button>
                    </div>
                </div>
            </div>

            {{-- Translate Confirmation Modal --}}
            <div id="translateCheckModal" class="modal" style="z-index: 60;">
                <div class="modal-content-custom" style="max-width: 420px;">
                    <div class="flex items-center justify-center mb-3">
                        <div class="bg-[#2D7080]/10 rounded-full p-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-[#2D7080]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5c-.006 3.125-1.026 6.042-2.751 8.498m-1.782-2.5A18.022 18.022 0 014.888 5h7.863z" />
                            </svg>
                        </div>
                    </div>
                    <h2 class="text-lg font-semibold text-slate-800 mb-2">{{ __('messages.Translate to Dutch?') }}</h2>
                    <p class="text-sm text-slate-500 mb-5">{{ __('messages.Would you like to translate your comment into Dutch before submitting?') }}</p>
                    <div class="flex flex-col gap-2">
                        <button type="button" id="translateCheckVerifyBtn" class="w-full px-4 py-2.5 text-sm font-medium text-white bg-[#0B2638] rounded-lg hover:bg-[#14364d] transition-colors flex items-center justify-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            {{ __('messages.Yes, translate to Dutch') }}
                        </button>
                        <button type="button" id="translateCheckSkipBtn" class="w-full px-4 py-2.5 text-sm font-medium text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors">
                            {{ __('messages.No, submit as is') }}
                        </button>
                    </div>
                </div>
            </div>

            <div class="dt-card mb-6 overflow-hidden">
                <div class="px-5 sm:px-6 pt-5 pb-1">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 mb-5">
                        <h3 class="dt-section-title">{{ __('messages.Tasks Timer Table') }}</h3>
                        <div class="flex flex-wrap gap-2 mt-1 md:mt-0">
                            <button id="exportBtn" class="dt-btn dt-btn-ghost">{{ __('messages.Export to Excel') }}</button>
                            <button id="exportPDFBtn" class="dt-btn dt-btn-primary">{{ __('messages.Export to PDF') }}</button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-5">
                        <input type="date" id="startDateInput" class="bg-white border border-slate-200 text-slate-700 text-sm rounded-xl focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none block w-full p-2.5 shadow-sm">
                        <input type="date" id="endDateInput" class="bg-white border border-slate-200 text-slate-700 text-sm rounded-xl focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none block w-full p-2.5 shadow-sm">
                        <select id="projectFilterExport" class="bg-white border border-slate-200 text-slate-700 text-sm rounded-xl focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none block w-full p-2.5 shadow-sm">
                            <option value="">{{ __('messages.All Projects') }}</option>
                            @foreach($projects as $project)
                                <option value="{{ $project->id }}">{{ $project->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="overflow-x-auto custom-scrollbar border-t border-slate-100">
                    <table id="taskTable" class="w-full text-left border-collapse">
                        <thead class="bg-slate-50/80">
                            <tr class="dt-table-head">
                                <th class="px-4 py-3 text-left whitespace-nowrap">{{ __('messages.Project Name') }}</th>
                                <th class="px-4 py-3 text-left min-w-[200px]">{{ __('messages.Task Name') }}</th>
                                <th class="px-4 py-3 text-left whitespace-nowrap">{{ __('messages.Start Date') }}</th>
                                <th class="px-4 py-3 text-left whitespace-nowrap">{{ __('messages.End Date') }}</th>
                                <th class="px-4 py-3 text-left min-w-[180px]">{{ __('messages.Comment') }}</th>
                                <th class="px-4 py-3 text-left whitespace-nowrap">{{ __('messages.Duration') }}</th>
                            </tr>
                        </thead>
                        <tbody id="taskTableBody" class="divide-y divide-slate-100">
                            </tbody>
                    </table>
                </div>
            </div>

            <div class="dt-card mb-6 overflow-hidden">
                <div class="px-5 sm:px-6 py-4 border-b border-slate-100">
                    <h3 class="dt-section-title">{{ __('messages.Work Days Reports') }}</h3>
                </div>
                <div class="overflow-x-auto custom-scrollbar">
                    <table id="editableTable" class="w-full text-left border-collapse">
                        <thead class="bg-slate-50/80">
                            <tr class="dt-table-head">
                                <th class="px-4 py-3 text-left leading-snug min-w-[110px]">{{ __('messages.Number of') }}<br>{{ __('messages.Working Days') }}</th>
                                <th class="px-4 py-3 text-left leading-snug min-w-[95px]">{{ __('messages.Number of') }}<br>{{ __('messages.Sick Days') }}</th>
                                <th class="px-4 py-3 text-left leading-snug min-w-[110px]">{{ __('messages.Number of') }}<br>{{ __('messages.Public Holidays') }}</th>
                                <th class="px-4 py-3 text-left leading-snug min-w-[110px]">{{ __('messages.Remaining') }}<br>{{ __('messages.Paid Leave Days') }}</th>
                                <th class="px-4 py-3 text-left leading-snug min-w-[110px]">{{ __('messages.Paid Leave') }}<br>{{ __('messages.Days Accrued') }}</th>
                                <th class="px-4 py-3 text-left leading-snug min-w-[110px]">{{ __('messages.Paid Leave') }}<br>{{ __('messages.Days Taken') }}</th>
                                <th class="px-4 py-3 text-left leading-snug min-w-[90px]">{{ __('messages.Leave') }}<br>{{ __('messages.Balance') }}</th>
                                <th class="px-4 py-3 text-left leading-snug min-w-[90px]">{{ __('messages.Days of') }}<br>{{ __('messages.Absence') }}</th>
                                <th class="px-4 py-3 text-left leading-snug min-w-[110px]">{{ __('messages.Paid Leave') }}<br>{{ __('messages.Dates') }}</th>
                                <th class="px-4 py-3 text-left leading-snug">{{ __('messages.Month') }}</th>
                                <th class="px-4 py-3 text-left leading-snug">{{ __('messages.Year') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-600">
                            @isset($work_days_repports)
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td data-label="{{ __('messages.Number of') }} {{ __('messages.Working Days') }}" class="px-4 py-4 text-sm editable" data-id="{{$work_days_repports->id}}" data-column="days_worked">
                                    @php
                                        $firstDay = Carbon\Carbon::create($work_days_repports->year, $work_days_repports->month, 1);
                                        $lastDay = $firstDay->copy()->endOfMonth();
                                        $saturdays = 0;
                                        for($date = $firstDay; $date->lte($lastDay); $date->addDay()) { if($date->isSaturday()) { $saturdays++; } }
                                        $totalDays = $work_days_repports->days_worked + $saturdays + $work_days_repports->days_to_recover;
                                    @endphp
                                    {{$totalDays}}
                                </td>
                                <td data-label="{{ __('messages.Number of') }} {{ __('messages.Sick Days') }}" class="px-4 py-4 text-sm editable" data-id="{{$work_days_repports->id}}" data-column="sick_days">{{$work_days_repports->sick_days}}</td>
                                <td data-label="{{ __('messages.Number of') }} {{ __('messages.Public Holidays') }}" class="px-4 py-4 text-sm editable" data-id="{{$work_days_repports->id}}" data-column="holidays">{{$work_days_repports->holidays}}</td>
                                <td data-label="{{ __('messages.Remaining') }} {{ __('messages.Paid Leave Days') }}" class="px-4 py-4 text-sm editable" data-id="{{$work_days_repports->id}}" data-column="remaining_days_of_paid_leave">{{$work_days_repports->remaining_days_of_paid_leave}}</td>
                                <td data-label="{{ __('messages.Paid Leave') }} {{ __('messages.Days Accrued') }}" class="px-4 py-4 text-sm editable" data-id="{{$work_days_repports->id}}" data-column="number_of_days_of_paid_leave_acquired">{{$work_days_repports->number_of_days_of_paid_leave_acquired}}</td>
                                <td data-label="{{ __('messages.Paid Leave') }} {{ __('messages.Days Taken') }}" class="px-4 py-4 text-sm editable" data-id="{{$work_days_repports->id}}" data-column="number_of_days_of_paid_leave_taken">{{$work_days_repports->number_of_days_of_paid_leave_taken}}</td>
                                <td data-label="{{ __('messages.Leave') }} {{ __('messages.Balance') }}" class="px-4 py-4 text-sm editable" data-id="{{$work_days_repports->id}}" data-column="holiday_balance">{{$work_days_repports->holiday_balance}}</td>
                                <td data-label="{{ __('messages.Days of') }} {{ __('messages.Absence') }}" class="px-4 py-4 text-sm editable" data-id="{{$work_days_repports->id}}" data-column="days_of_unpaid_absence">{{$work_days_repports->days_of_unpaid_absence}}</td>
                                <td data-label="{{ __('messages.Paid Leave') }} {{ __('messages.Dates') }}" data-span="2" class="px-4 py-4 text-sm editable" data-id="{{$work_days_repports->id}}" data-column="dates_of_holidays">{{$work_days_repports->dates_of_holidays}}</td>
                                <td data-label="{{ __('messages.Month') }}" class="px-4 py-4 text-sm font-medium text-[#2D7080] editable" data-id="{{$work_days_repports->id}}" data-column="month">{{$work_days_repports->month}}</td>
                                <td data-label="{{ __('messages.Year') }}" class="px-4 py-4 text-sm font-medium text-[#2D7080] editable" data-id="{{$work_days_repports->id}}" data-column="year">{{$work_days_repports->year}}</td>
                            </tr>
                            @endisset
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="customAlertModal" class="modal">
                <div class="modal-content-custom relative w-full max-w-md mx-auto mt-20 bg-white rounded-xl shadow-2xl p-6 text-center">
                    <span class="close-button absolute top-3 right-4 text-slate-400 hover:text-slate-600 cursor-pointer text-2xl font-bold">&times;</span>
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                        <i class="fas fa-bell text-red-500 text-xl"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 mb-2">{{ __('messages.Timer Alert') }}</h3>
                    <p class="text-sm text-slate-600">{{ __('messages.Did you forget to launch your timer?') }}</p>
                </div>
            </div>

            </div>
        </main>
        </div>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.3.1/jspdf.umd.min.js"></script>
    <script src="https://unpkg.com/jspdf-autotable@3.5.17/dist/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.4/xlsx.full.min.js"></script>

    {{-- Sidebar toggles + Alpine.js are provided by x-employees.navbar (same as Admin Dashboard) --}}


    
    <script>
        var taskIntervals = {};
        
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.stop-button').forEach(function(button) {
                button.addEventListener('click', function() {
                    var taskId = this.getAttribute('data-task-id');
                    if(taskIntervals[taskId]) {
                        clearInterval(taskIntervals[taskId]);
                        delete taskIntervals[taskId];
                    }
                });
            });
        });
    </script>

    {{-- 2. Excel Export --}}
    <script>
        document.getElementById('exportBtn').addEventListener('click', function() {
            var table = document.getElementById('taskTable'); 
            var wb = XLSX.utils.table_to_book(table, { sheet: 'Sheet1' });
            var startDate = document.getElementById('startDateInput').value;
            var endDate = document.getElementById('endDateInput').value;
            var taskInfo = 'TasksInfo';
            var fileName = startDate + '__' + endDate + '_' + taskInfo + '.xlsx';
            XLSX.writeFile(wb, fileName);
        });
    </script>

    {{-- 3. PDF Export --}}
    <script>
        document.getElementById('exportPDFBtn').addEventListener('click', function() {
            var startDate = document.getElementById('startDateInput').value;
            var endDate = document.getElementById('endDateInput').value;
            var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            var employeeId = {{ $employee_id }};
            var url = '';
            
            @if(Auth::user()->hasRole('Employee'))
                var baseUrlInterval = '/employee/Pointagerepports/downloadRepports/';
                var baseUrlDay = '/employee/Pointagerepports/downloadOneDayRepports/';
            @elseif(Auth::user()->hasRole('Responsable de projet'))
                var baseUrlInterval = '/Responsable_de_projet/Pointagerepports/downloadRepports/';
                var baseUrlDay = '/Responsable_de_projet/Pointagerepports/downloadOneDayRepports/';
            @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                var baseUrlInterval = '/Responsable_gestionnaire/Pointagerepports/downloadRepports/';
                var baseUrlDay = '/Responsable_gestionnaire/Pointagerepports/downloadOneDayRepports/';
            @endif

            if(startDate && endDate) {
                url = baseUrlInterval + startDate + '/' + endDate + '/' + employeeId;
            } else if (startDate && !endDate) {
                url = baseUrlDay + startDate + '/' + employeeId;
            }

            if (url) {
                fetch(url, {
                    method: 'GET',
                    headers: { 'X-CSRF-TOKEN': csrfToken }
                })
                .then(response => response.blob())
                .then(blob => {
                    var url = window.URL.createObjectURL(blob);
                    var a = document.createElement('a');
                    a.href = url;
                    a.download = 'pointageRepports.pdf';
                    document.body.appendChild(a);
                    a.click();      
                    a.remove();           
                })
                .catch((error) => { console.error('Error:', error); });
            }
        });
    </script>

    {{-- 4. Inactivity Alert Modal Logic --}}
    @php 
        $tasks_timer = App\Models\tasks_timer::where('employee_id', $employee_id)->whereDate('start_date', '=', $today)->whereNull('end_date')->first();
    @endphp
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var modal = document.getElementById("customAlertModal");
            var alertSound = document.getElementById("alertSound");
            var alertInterval;

            modal.querySelector('.close-button').onclick = function() { modal.style.display = "none"; }
            function showAlert() { modal.style.display = "block"; alertSound.play(); }
            function hideAlert() { modal.style.display = "none"; }
            function startAlertInterval() { clearInterval(alertInterval); alertInterval = setInterval(showAlert, 300000); }
            function clearAlertInterval() { clearInterval(alertInterval); }
            function handleTimerStarted() { tasksTimer = { paused: 0, pause_date: null }; hideAlert(); clearAlertInterval(); try { window.dispatchEvent(new CustomEvent('chrono-revalidate', { detail: { force: true } })); } catch {} }
            function handleTimerStopped() { tasksTimer = null; startAlertInterval(); }

            var tasksTimer = @json($tasks_timer);

            function checkTimerState() {
                if (!tasksTimer) {
                    startAlertInterval();
                } else {
                    if (tasksTimer.paused === null || tasksTimer.paused === 0) {
                        clearAlertInterval();
                    } else if (tasksTimer.paused === 1) {
                        startAlertInterval();
                    }
                    if (tasksTimer.pause_date !== null && tasksTimer.paused === 0) {
                        clearAlertInterval();
                    }
                }
            }

            checkTimerState();

            var btns = document.querySelectorAll('#start-btn, #pause-btn, #continue-btn, .stop-button');
            btns.forEach(btn => {
                btn.addEventListener('click', function() { checkTimerState(); });
            });

            var previousTimerId = tasksTimer ? tasksTimer.id : null;
            setInterval(function() {
                fetch('/employee/pointage/get-timer-state')
                    .then(function(r) { return r.json(); })
                    .then(function(data) {
                        var currentTimerId = data.tasksTimer ? data.tasksTimer.id : null;
                        if (previousTimerId !== null && currentTimerId === null) {
                            location.reload();
                        }
                    });
            }, 60000);
        });
    </script>

    {{-- 5. Initialize Start Date --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var today = new Date().toISOString().split('T')[0];
            document.getElementById('startDateInput').value = today;
        });
    </script>
    
    <script type="text/javascript">
        var employeeId = @json($employee_id); 
    </script>

    {{-- 6. Fetch Tasks Logic (AJAX Table Population) --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var startDateInput = document.getElementById('startDateInput');
            var endDateInput = document.getElementById('endDateInput');

            // Filter logic for dynamic table (Updated ID to match the export section select)
            document.getElementById('projectFilterExport').addEventListener('change', function() {
                // Use the selected VALUE to check for "All Projects" (empty string)
                var selectedValue = this.value; 
                
                // Use the selected TEXT to compare against table content
                var selectedText = this.options[this.selectedIndex].text.trim().toLowerCase();
                
                var tableRows = document.querySelectorAll('#taskTableBody tr');
                
                var totalDuration = 0;

                // Remove totals
                var totalRow = document.querySelector('.total-row');
                if (totalRow) totalRow.remove();

                tableRows.forEach(row => {
                    var projectCell = row.cells[0];
                    var cellText = projectCell.textContent.trim().toLowerCase();

                    // Logic: If value is empty (All Projects) OR the text matches (Case Insensitive)
                    if (selectedValue === "" || cellText === selectedText) {
                        row.style.display = '';
                        var DurationValue = row.cells[5].textContent.trim();
                        var durationInSeconds = parseDuration(DurationValue);
                        totalDuration += durationInSeconds / 3600;
                    } else {
                        row.style.display = 'none';
                    }
                });

                // Add total row
                var tableBody = document.getElementById('taskTableBody');
                var hours = Math.floor(totalDuration);
                var minutes = Math.round((totalDuration - hours) * 60);
                var formattedDuration = `${hours.toString().padStart(2, '0')}h:${minutes.toString().padStart(2, '0')}m`;
                var totalRowHTML = `
                  <tr class="text-center total-row bg-slate-50 font-bold border-t border-slate-200">
                    <td colspan="5"></td>
                    <td class="px-4 py-3 text-sm text-[#0B2638]">${formattedDuration}</td>
                  </tr>`;
                tableBody.insertAdjacentHTML('beforeend', totalRowHTML);
            });

            window.onload = function() {
                var currentDate = new Date().toISOString().split('T')[0];
                startDateInput.value = currentDate;
                var event = new Event('change');
                startDateInput.dispatchEvent(event);
            }

            endDateInput.addEventListener('change', function() { handleDateChange(); });
            startDateInput.addEventListener('change', function() { handleDateChange(); });

            function handleDateChange() {
                var start = startDateInput.value;
                var end = endDateInput.value;
                if (start && end) fetchTasksForInterval(start, end, employeeId);
                else if (start) fetchTasksForDate(start, employeeId);
            }

            function fetchTasksForInterval(startDate, endDate, employeeId) {
                var url = '';
                @if(Auth::user()->hasRole('Employee')) url = '/employee/tasks/tasks-info-interval-employee/';
                @elseif(Auth::user()->hasRole('Responsable de projet')) url = '/Responsable_de_projet/tasks/tasks-info-interval-employee/';
                @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire')) url = '/Responsable_gestionnaire/tasks/tasks-info-interval-employee/';
                @endif
                
                url += startDate + '/' + endDate + '/' + employeeId;

                fetchAndPopulateTable(url);
            }

            function fetchTasksForDate(selectedDate, employeeId) {
                var url = '';
                @if(Auth::user()->hasRole('Employee')) url = '/employee/tasks/tasks-info/';
                @elseif(Auth::user()->hasRole('Responsable de projet')) url = '/Responsable_de_projet/tasks/tasks-info/';
                @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire')) url = '/Responsable_gestionnaire/tasks/tasks-info/';
                @endif

                url += selectedDate + '/' + employeeId;
                fetchAndPopulateTable(url);
            }

            function fetchAndPopulateTable(url) {
                const lblProjectName = @json(__('messages.Project Name'));
                const lblTaskName = @json(__('messages.Task Name'));
                const lblStartDate = @json(__('messages.Start Date'));
                const lblEndDate = @json(__('messages.End Date'));
                const lblComment = @json(__('messages.Comment'));
                const lblDuration = @json(__('messages.Duration'));

                fetch(url)
                    .then(response => response.json())
                    .then(data => {
                        var tableBody = document.getElementById('taskTableBody');
                        if (tableBody) {
                            tableBody.innerHTML = '';
                            var totalDuration = 0;

                            Promise.all(data.map(task => {
                                var startTime = new Date(task.start_date);
                                var endTime = task.end_date ? new Date(task.end_date) : new Date();
                                var duration = endTime - startTime;
                                var hours = Math.floor(duration / (1000 * 60 * 60));
                                var minutes = Math.floor((duration % (1000 * 60 * 60)) / (1000 * 60));
                                var seconds = Math.floor((duration % (1000 * 60)) / 1000);
                                var formattedDuration = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
                                var endDateText = task.end_date ? task.end_date : {!! json_encode(__('messages.Still working')) !!};

                                var fetchurl = '';
                                @if(Auth::user()->hasRole('Employee')) fetchurl = '/employee/tasks/get-project-name/';
                                @elseif(Auth::user()->hasRole('Responsable de projet')) fetchurl = '/Responsable_de_projet/tasks/get-project-name/';
                                @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire')) fetchurl = '/Responsable_gestionnaire/tasks/get-project-name/';
                                @endif

                                return fetch(fetchurl + encodeURIComponent(task.task_id))
                                    .then(response => response.json())
                                    .then(projectData => {
                                        var fetchEmpUrl = '';
                                        @if(Auth::user()->hasRole('Employee')) fetchEmpUrl = '/employee/tasks/get-employee-name/';
                                        @elseif(Auth::user()->hasRole('Responsable de projet')) fetchEmpUrl = '/Responsable_de_projet/tasks/get-employee-name/';
                                        @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire')) fetchEmpUrl = '/Responsable_gestionnaire/tasks/get-employee-name/';
                                        @endif

                                        return fetch(fetchEmpUrl + encodeURIComponent(task.employee_id))
                                            .then(response => response.json())
                                            .then(employeeData => {
                                                var rowId = 'row-' + task.id;
                                                let d_h = Math.floor(task.duration / 60);
                                                let d_m = task.duration % 60;
                                                let formattedD = `${d_h.toString().padStart(2, '0')}h:${d_m.toString().padStart(2, '0')}m:00s`;

                                var row = `
                                                  <tr id="${rowId}" class="task-timer-row text-left hover:bg-slate-50/70 transition-colors">
                                                      <td data-label="${lblProjectName}" data-span="2" class="px-4 py-3 text-sm font-semibold text-[#0B2638] whitespace-nowrap">${projectData.name.toUpperCase()}</td>
                                                      <td data-label="${lblTaskName}" data-span="2" class="px-4 py-3 text-sm text-slate-700 font-medium">${task.task_name}</td>
                                                      <td data-label="${lblStartDate}" class="px-4 py-3 text-sm text-slate-600 whitespace-nowrap">${task.start_date}</td>
                                                      <td data-label="${lblEndDate}" class="px-4 py-3 text-sm text-slate-600 whitespace-nowrap">${endDateText}</td>
                                                      <td data-label="${lblComment}" data-span="2" class="px-4 py-3 text-sm text-slate-600">
                                                          <span class="comment-display cursor-pointer hover:text-[#2D7080] break-words" style="display: block;">${task.comment ? task.comment : {!! json_encode(__('messages.No Comment')) !!}}</span>
                                                          <input type="text"
                                                                 class="comment-input border border-slate-300 rounded-lg px-2 py-1 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] w-full"
                                                                 value="${task.comment ? task.comment.replace(/"/g, '&quot;') : ''}"
                                                                 data-timer-id="${task.id}"
                                                                 style="display: none;">
                                                      </td>
                                                      <td data-label="${lblDuration}" class="px-4 py-3 text-sm font-semibold text-[#0B2638] whitespace-nowrap task_duration">${task.end_date ? formattedD : ""}</td>
                                                  </tr>`;
                                                
                                                tableBody.insertAdjacentHTML('beforeend', row);

                                                if (task.end_date === null) {
                                                    // Live timer logic for active tasks in this table
                                                    var startDate = new Date(task.start_date.replace(' ', 'T'));
                                                    var taskId = task.id;
                                                    var rowId = 'row-' + taskId;
                                                    var elapsedTime = 0;
                                                    
                                                    if(taskIntervals[taskId]) clearInterval(taskIntervals[taskId]);
                                                    
                                                    taskIntervals[taskId] = setInterval(function() {
                                                        var now = new Date();
                                                        var durationInMilliseconds = Math.max(0, now - startDate + elapsedTime);
                                                        var h = Math.floor(durationInMilliseconds / 3600000);
                                                        var m = Math.floor((durationInMilliseconds % 3600000) / 60000);
                                                        var s = Math.floor((durationInMilliseconds % 60000) / 1000);
                                                        var durFmt = (h < 10 ? '0' : '') + h + 'h:' + (m < 10 ? '0' : '') + m + 'm:' + (s < 10 ? '0' : '') + s + 's';
                                                        
                                                        var durationCell = document.querySelector('#' + rowId + ' .task_duration');
                                                        if (durationCell) durationCell.textContent = durFmt;
                                                        else {
                                                            clearInterval(taskIntervals[taskId]);
                                                            delete taskIntervals[taskId];
                                                        }
                                                    }, 1000);
                                                }
                                                
                                                var rowElement = document.getElementById(rowId);
                                                // Use the formatted duration we just calculated/fetched, NOT textContent which might be empty initially
                                                var dString = rowElement.querySelector('.task_duration').textContent.trim();
                                                if(dString) {
                                                    var dSec = parseDuration(dString);
                                                    totalDuration += dSec / 3600;
                                                }
                                            });
                                    });
                            })).then(() => {
                                // Trigger the filter logic after data is loaded to respect any pre-selected value
                                var event = new Event('change');
                                document.getElementById('projectFilterExport').dispatchEvent(event);
                            });
                        }
                    });
            }

            function parseDuration(durationString) {
                if(!durationString) return 0;
                var parts = durationString.split(':');
                if(parts.length < 3) return 0; 
                var hours = parseInt(parts[0].replace('h', ''), 10);
                var minutes = parseInt(parts[1].replace('m', ''), 10);
                var seconds = parseInt(parts[2].replace('s', ''), 10);
                return hours * 3600 + minutes * 60 + seconds;
            }
        });
    </script>

    {{-- 7. Manual Timer Modal Logic --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modal = document.getElementById('manualTimerModal');
            const form = document.getElementById('manualTimerForm');
            const taskIdInput = document.getElementById('manualTimerTaskId');
            const employeeIdInput = document.getElementById('manualTimerEmployeeId');
            const startInput = document.getElementById('manualStartDate');
            const endInput = document.getElementById('manualEndDate');
            const closeBtn = document.getElementById('manualTimerClose');
            const cancelBtn = document.getElementById('manualTimerCancelBtn');

            function openModal(taskId, employeeId) {
                taskIdInput.value = taskId;
                employeeIdInput.value = employeeId;
                startInput.value = '';
                endInput.value = '';
                modal.style.display = 'block';
            }

            function closeModal() {
                modal.style.display = 'none';
            }

            document.querySelectorAll('.manual-timer-btn').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const taskId = this.getAttribute('data-task-id');
                    const employeeId = this.getAttribute('data-employee-id');
                    openModal(taskId, employeeId);
                });
            });

            if (closeBtn) {
                closeBtn.addEventListener('click', closeModal);
            }
            if (cancelBtn) {
                cancelBtn.addEventListener('click', closeModal);
            }

            window.addEventListener('click', function (event) {
                if (event.target === modal) {
                    closeModal();
                }
            });

            if (form) {
                form.addEventListener('submit', function (e) {
                    e.preventDefault();
                    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                    let manualTimerUrl = '';
                    @if(Auth::user()->hasRole('Employee'))
                        manualTimerUrl = '{{ url('/employee/tasks/manual-timer') }}';
                    @endif
                    @if(Auth::user()->hasRole('Responsable de projet'))
                        manualTimerUrl = '{{ url('/Responsable_de_projet/tasks/manual-timer') }}';
                    @endif
                    @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                        manualTimerUrl = '{{ url('/Responsable_gestionnaire/tasks/manual-timer') }}';
                    @endif
                    @if(Auth::user()->hasRole('Admin') || Auth::user()->hasRole('SuperAdmin') || Auth::user()->hasRole('super_admin'))
                        manualTimerUrl = '{{ url('/admin/tasks/manual-timer') }}';
                    @endif

                    fetch(manualTimerUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({
                            task_id: taskIdInput.value,
                            employee_id: employeeIdInput.value,
                            start_date: startInput.value,
                            end_date: endInput.value
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            closeModal();
                            window.location.reload();
                        } else {
                            alert(data.message || 'Failed to create timer.');
                        }
                    })
                    .catch(() => {
                        alert('Failed to create timer.');
                    });
                });
            }
        });
    </script>

    {{-- 8. Mobile accordion for Employee Tasks table --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const mql = window.matchMedia('(max-width: 1024px)');

            function setTaskRowCollapsed(row, collapsed) {
                if (!row) return;
                row.classList.toggle('is-collapsed', collapsed);
                row.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
            }

            function initTaskAccordions() {
                const rows = document.querySelectorAll('#tasksTable tbody tr.task-row');
                const isMobile = mql.matches;
                rows.forEach(row => setTaskRowCollapsed(row, isMobile));
            }

            document.addEventListener('click', function (e) {
                const toggleBtn = e.target.closest('[data-task-collapse-toggle]');
                if (!toggleBtn) return;

                const row = toggleBtn.closest('tr.task-row');
                if (!row) return;

                const collapsed = row.classList.contains('is-collapsed');
                setTaskRowCollapsed(row, !collapsed);
            });

            if (mql.addEventListener) mql.addEventListener('change', initTaskAccordions);
            else if (mql.addListener) mql.addListener(initTaskAccordions);

            initTaskAccordions();
        });
    </script>

    {{-- 8. JQuery AJAX for Start/Stop --}}
    <script>
        $(document).ready(function() {
            $('.start-button').click(function() {
                var taskId = $(this).data('task-id');
                var employeeId = @json($employee_id); // Fixed usage
                $.ajax({
                    url: '/tasks/' + taskId + '/start/' + employeeId,
                    type: 'POST',
                    data: { task_id: taskId, employee_id: employeeId },
                    success: function(response) { handleTimerStarted(); },
                    error: function(response) { console.error('Error starting timer:', response); }
                });
            });

            // Stop Comment Modal logic
            var activeStopForm = null;
            var stopModal = document.getElementById('stopCommentModal');
            var stopCommentText = document.getElementById('stopCommentText');
            var stopCommentError = document.getElementById('stopCommentError');
            var stopConfirmBtn = document.getElementById('stopCommentConfirmBtn');
            var stopCancelBtn = document.getElementById('stopCommentCancelBtn');
            var stopCloseBtn = document.getElementById('stopCommentClose');
            var stopCommentAiBtn = document.getElementById('stopCommentAiBtn');
            var stopCommentAiBtnText = document.getElementById('stopCommentAiBtnText');
            var stopCommentTranslateBtn = document.getElementById('stopCommentTranslateBtn');
            var stopCommentTranslateBtnText = document.getElementById('stopCommentTranslateBtnText');
            var stopCommentAiFeedback = document.getElementById('stopCommentAiFeedback');
            var spellCheckModal = document.getElementById('spellCheckModal');
            var spellCheckVerifyBtn = document.getElementById('spellCheckVerifyBtn');
            var spellCheckSkipBtn = document.getElementById('spellCheckSkipBtn');
            var translateCheckModal = document.getElementById('translateCheckModal');
            var translateCheckVerifyBtn = document.getElementById('translateCheckVerifyBtn');
            var translateCheckSkipBtn = document.getElementById('translateCheckSkipBtn');

            // State flags to avoid repetitive popups
            var hasSpelledChecked = false;
            var hasTranslated = false;

            // Intercept stop button clicks to show modal
            $(document).on('click', '.stop-button', function(e) {
                e.preventDefault();
                e.stopPropagation();
                activeStopForm = $(this).closest('.stop-form')[0];
                // Reset modal state
                stopCommentText.value = '';
                stopCommentError.classList.add('hidden');
                stopCommentAiFeedback.classList.add('hidden');
                stopCommentAiFeedback.textContent = '';
                stopConfirmBtn.disabled = true;
                stopConfirmBtn.classList.add('opacity-50', 'cursor-not-allowed');
                stopCommentAiBtn.disabled = true;
                stopCommentAiBtn.classList.add('opacity-50', 'cursor-not-allowed');
                stopCommentTranslateBtn.disabled = true;
                stopCommentTranslateBtn.classList.add('opacity-50', 'cursor-not-allowed');
                hasSpelledChecked = false;
                hasTranslated = false;
                stopModal.style.display = 'block';
                stopCommentText.focus();
            });

            // Enable/disable confirm, AI and Translate button based on comment content
            stopCommentText.addEventListener('input', function() {
                var hasContent = this.value.trim().length > 0;
                stopConfirmBtn.disabled = !hasContent;
                stopCommentAiBtn.disabled = !hasContent;
                stopCommentTranslateBtn.disabled = !hasContent;
                if (hasContent) {
                    stopConfirmBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                    stopCommentAiBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                    stopCommentTranslateBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                    stopCommentError.classList.add('hidden');
                } else {
                    stopConfirmBtn.classList.add('opacity-50', 'cursor-not-allowed');
                    stopCommentAiBtn.classList.add('opacity-50', 'cursor-not-allowed');
                    stopCommentTranslateBtn.classList.add('opacity-50', 'cursor-not-allowed');
                }
            });

            // AI correction request
            stopCommentAiBtn.addEventListener('click', function() {
                var comment = stopCommentText.value.trim();
                if (!comment) return;

                // Set loading state
                stopCommentAiBtn.disabled = true;
                stopCommentAiBtn.classList.add('opacity-50', 'cursor-not-allowed');
                stopCommentText.disabled = true;
                var oldBtnText = stopCommentAiBtnText.textContent;
                stopCommentAiBtnText.textContent = "{{ __('messages.Correcting...') }}";
                stopCommentAiFeedback.classList.add('hidden');

                var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                fetch('/tasks/correct-comment', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ comment: comment })
                })
                .then(function(res) {
                    if (!res.ok) {
                        return res.json().then(function(err) {
                            throw new Error(err.error || 'Failed to correct comment.');
                        });
                    }
                    return res.json();
                })
                .then(function(data) {
                    if (data.corrected) {
                        stopCommentText.value = data.corrected;
                        stopCommentAiFeedback.textContent = "{{ __('messages.Spelling and grammar corrected!') }}";
                        stopCommentAiFeedback.className = "text-xs mt-1 text-emerald-600 font-medium";
                        stopCommentAiFeedback.classList.remove('hidden');
                        hasSpelledChecked = true;
                    }
                })
                .catch(function(err) {
                    console.error(err);
                    stopCommentAiFeedback.textContent = "{{ __('messages.Failed to correct comment. Please check API configuration.') }}";
                    stopCommentAiFeedback.className = "text-xs mt-1 text-red-500 font-medium";
                    stopCommentAiFeedback.classList.remove('hidden');
                })
                .finally(function() {
                    stopCommentAiBtn.disabled = false;
                    stopCommentAiBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                    stopCommentText.disabled = false;
                    stopCommentAiBtnText.textContent = oldBtnText;
                });
            });

            // Translate request
            stopCommentTranslateBtn.addEventListener('click', function() {
                var comment = stopCommentText.value.trim();
                if (!comment) return;

                stopCommentTranslateBtn.disabled = true;
                stopCommentTranslateBtn.classList.add('opacity-50', 'cursor-not-allowed');
                stopCommentAiBtn.disabled = true;
                stopCommentAiBtn.classList.add('opacity-50', 'cursor-not-allowed');
                stopCommentText.disabled = true;
                var oldBtnText = stopCommentTranslateBtnText.textContent;
                stopCommentTranslateBtnText.textContent = "{{ __('messages.Translating...') }}";
                stopCommentAiFeedback.classList.add('hidden');

                var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                fetch('/tasks/translate-comment', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ comment: comment })
                })
                .then(function(res) {
                    if (!res.ok) {
                        return res.json().then(function(err) {
                            throw new Error(err.error || 'Failed to translate comment.');
                        });
                    }
                    return res.json();
                })
                .then(function(data) {
                    if (data.translated) {
                        stopCommentText.value = data.translated;
                        stopCommentAiFeedback.textContent = "{{ __('messages.Translated to Dutch!') }}";
                        stopCommentAiFeedback.className = "text-xs mt-1 text-emerald-600 font-medium";
                        stopCommentAiFeedback.classList.remove('hidden');
                        hasTranslated = true;
                    }
                })
                .catch(function(err) {
                    console.error(err);
                    stopCommentAiFeedback.textContent = "{{ __('messages.Failed to translate comment. Please check API configuration.') }}";
                    stopCommentAiFeedback.className = "text-xs mt-1 text-red-500 font-medium";
                    stopCommentAiFeedback.classList.remove('hidden');
                })
                .finally(function() {
                    stopCommentTranslateBtn.disabled = false;
                    stopCommentTranslateBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                    stopCommentAiBtn.disabled = false;
                    stopCommentAiBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                    stopCommentText.disabled = false;
                    stopCommentTranslateBtnText.textContent = oldBtnText;
                });
            });

            // Function to actually submit the stop task
            function doSubmitStop() {
                var comment = stopCommentText.value.trim();
                if (activeStopForm) {
                    // Set the hidden field value
                    activeStopForm.querySelector('.stop-comment-field').value = comment;
                    // Clear the interval for this task
                    var taskId = activeStopForm.querySelector('.stop-button').getAttribute('data-task-id');
                    if (window.taskIntervals && window.taskIntervals[taskId]) {
                        clearInterval(window.taskIntervals[taskId]);
                        delete window.taskIntervals[taskId];
                    }
                    stopModal.style.display = 'none';
                    activeStopForm.submit();
                }
            }

            // Confirm stop: show spell-check or translate popups if they haven't been completed yet
            stopConfirmBtn.addEventListener('click', function() {
                var comment = stopCommentText.value.trim();
                if (!comment) {
                    stopCommentError.classList.remove('hidden');
                    stopCommentText.focus();
                    return;
                }
                
                if (!hasSpelledChecked) {
                    spellCheckModal.style.display = 'block';
                } else if (!hasTranslated) {
                    translateCheckModal.style.display = 'block';
                } else {
                    doSubmitStop();
                }
            });

            // Spell-check popup: verify spelling (trigger AI correction then return to comment modal)
            spellCheckVerifyBtn.addEventListener('click', function() {
                spellCheckModal.style.display = 'none';
                hasSpelledChecked = true;
                if (!stopCommentAiBtn.disabled) {
                    stopCommentAiBtn.click();
                }
            });

            // Spell-check popup: skip and open translate confirmation popup
            spellCheckSkipBtn.addEventListener('click', function() {
                spellCheckModal.style.display = 'none';
                hasSpelledChecked = true;
                if (!hasTranslated) {
                    translateCheckModal.style.display = 'block';
                } else {
                    doSubmitStop();
                }
            });

            // Close spell-check modal on outside click
            spellCheckModal.addEventListener('click', function(e) {
                if (e.target === spellCheckModal) {
                    spellCheckModal.style.display = 'none';
                }
            });

            // Translate popup: verify spelling (trigger translation then return to comment modal)
            translateCheckVerifyBtn.addEventListener('click', function() {
                translateCheckModal.style.display = 'none';
                hasTranslated = true;
                if (!stopCommentTranslateBtn.disabled) {
                    stopCommentTranslateBtn.click();
                }
            });

            // Translate popup: skip and submit directly
            translateCheckSkipBtn.addEventListener('click', function() {
                translateCheckModal.style.display = 'none';
                hasTranslated = true;
                doSubmitStop();
            });

            // Close translate modal on outside click
            translateCheckModal.addEventListener('click', function(e) {
                if (e.target === translateCheckModal) {
                    translateCheckModal.style.display = 'none';
                }
            });

            // Cancel / Close modal
            function closeStopModal() {
                stopModal.style.display = 'none';
                activeStopForm = null;
            }
            stopCancelBtn.addEventListener('click', closeStopModal);
            stopCloseBtn.addEventListener('click', closeStopModal);
            stopModal.addEventListener('click', function(e) {
                if (e.target === stopModal) closeStopModal();
            });

            // Allow Enter to confirm if comment is filled
            stopCommentText.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    if (!stopConfirmBtn.disabled) {
                        stopConfirmBtn.click();
                    }
                }
            });
        });
    </script>
    
    {{-- 9. Close Banner Logic --}}
    <script>
        function closeBanner() {
            var closeButton = document.querySelector('#close-bar');
            if (closeButton) closeButton.click();
        }
        window.onload = closeBanner;
    </script>

    {{-- 10. Client-side Filtering for Main Task Table --}}
    <script>
        document.getElementById('projectFilter').addEventListener('change', function() {
            var filter = this.value.toLowerCase();
            var rows = document.querySelectorAll('#tasksTable tbody tr');
            var currentProject = null;
            var shouldShowCurrentProject = false;

            rows.forEach(function(row) {
                // Check for project header row (colspan matches the header divider row)
                if (row.cells.length === 1 && row.cells[0].colSpan === 8) {
                    currentProject = row.cells[0].textContent.trim().toLowerCase();
                    shouldShowCurrentProject = filter === "" || currentProject.includes(filter);
                    row.style.display = shouldShowCurrentProject ? "" : "none";
                } else {
                    row.style.display = shouldShowCurrentProject ? "" : "none";
                }
            });
        });
    </script>

    {{-- 11. Inline Comment Editing --}}
    <script>
        // Inline comment editing handlers
        document.addEventListener('click', function(e) {
            const cell = e.target.closest('td');
            if (!cell) return;
            const display = cell.querySelector('.comment-display');
            const input = cell.querySelector('.comment-input');
            if (display && input && (e.target === display)) {
                display.style.display = 'none';
                input.style.display = 'block';
                input.focus();
                input.setSelectionRange(input.value.length, input.value.length);
            }
        });

        async function saveComment(inputEl) {
            const timerId = inputEl.getAttribute('data-timer-id');
            const newComment = inputEl.value.trim();
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            @if(Auth::user()->hasRole('Employee'))
            var url = `/employee/tasks/timers/${timerId}/comment`;
            @endif
            @if(Auth::user()->hasRole('Responsable de projet'))
            var url = `/Responsable_de_projet/tasks/timers/${timerId}/comment`;
            @endif
            @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
            var url = `/Responsable_gestionnaire/tasks/timers/${timerId}/comment`;
            @endif
            try {
                const res = await fetch(url, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ comment: newComment })
                });
                if (!res.ok) throw new Error('Failed to save comment');
                const data = await res.json();
                const cell = inputEl.closest('td');
                const display = cell.querySelector('.comment-display');
                display.textContent = data.comment && data.comment.length ? data.comment : {!! json_encode(__('messages.No Comment')) !!};
                inputEl.style.display = 'none';
                display.style.display = '';
                window.location.reload();
            } catch (err) {
                console.error(err);
                // Revert UI on error
                const cell = inputEl.closest('td');
                const display = cell.querySelector('.comment-display');
                inputEl.style.display = 'none';
                display.style.display = '';
            }
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                const inputEl = e.target.closest('.comment-input');
                if (inputEl) {
                    e.preventDefault();
                    saveComment(inputEl);
                }
            }
            if (e.key === 'Escape') {
                const inputEl = e.target.closest('.comment-input');
                if (inputEl) {
                    const cell = inputEl.closest('td');
                    const display = cell.querySelector('.comment-display');
                    inputEl.style.display = 'none';
                    display.style.display = '';
                }
            }
        });

        document.addEventListener('blur', function(e) {
            const inputEl = e.target.closest('.comment-input');
            if (inputEl) {
                // Save on blur
                saveComment(inputEl);
            }
        }, true);
    </script>

    <!-- Tom Select JS Complete CDN -->
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Tom Select for both project filters
            ['projectFilter', 'projectFilterExport'].forEach(function(id) {
                const el = document.getElementById(id);
                if (el) {
                    new TomSelect(el, {
                        create: false,
                        sortField: { field: 'text', direction: 'asc' },
                        maxOptions: null,
                        allowEmptyOption: true,
                        placeholder: el.options[0] ? el.options[0].text : 'Search...'
                    });
                }
            });
        });
    </script>

    {{-- 12. Project pinning and collapse --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const STORAGE_KEY = 'entrack_pinned';

            function getPinned() {
                try { return JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]'); }
                catch { return []; }
            }

            function setPinned(names) {
                localStorage.setItem(STORAGE_KEY, JSON.stringify(names));
            }

            function updatePinButton($btn) {
                const pinned = getPinned();
                const project = $btn.dataset.project;
                const isPinned = pinned.includes(project);
                $btn.classList.toggle('is-pinned', isPinned);
                const svg = $btn.querySelector('svg');
                if (svg) {
                    svg.setAttribute('fill', isPinned ? '#eab308' : 'none');
                }
                $btn.title = isPinned ? '{{ __('messages.Unpin project') }}' : '{{ __('messages.Pin project') }}';
            }

            function updateAllPinButtons() {
                document.querySelectorAll('.pin-btn').forEach(updatePinButton);
            }

            function togglePin(e) {
                const btn = e.currentTarget;
                const project = btn.dataset.project;
                let pinned = getPinned();
                const idx = pinned.indexOf(project);
                if (idx > -1) {
                    pinned.splice(idx, 1);
                } else {
                    pinned.push(project);
                }
                setPinned(pinned);
                updatePinButton(btn);
                reorderProjects();
            }

            function hasActiveTimer(projectName) {
                const rows = document.querySelectorAll(
                    '#tasksTable tbody tr.task-row[data-project-name="' + projectName.replace(/"/g, '\\"') + '"]'
                );
                for (let r of rows) {
                    if (r.dataset.activeTimer === 'true') return true;
                }
                return false;
            }

            function collapseAllProjects() {
                const pinned = getPinned();
                const toggles = document.querySelectorAll('.collapse-toggle');
                toggles.forEach(function(t) {
                    const project = t.dataset.project;
                    if (pinned.includes(project) || hasActiveTimer(project)) {
                        t.classList.remove('is-collapsed');
                    } else {
                        t.classList.add('is-collapsed');
                    }
                });
                const rows = document.querySelectorAll('#tasksTable tbody tr.task-row');
                rows.forEach(function(r) {
                    const project = r.dataset.projectName;
                    const keepOpen = pinned.includes(project) || hasActiveTimer(project);
                    r.classList.toggle('project-tasks-collapsed', !keepOpen);
                });
            }

            function reorderProjects() {
                const pinned = getPinned();
                if (pinned.length === 0) return;

                const tbody = document.querySelector('#tasksTable tbody');
                const groups = [];
                let currentGroup = null;

                Array.from(tbody.children).forEach(function(child) {
                    if (child.classList.contains('project-group-row')) {
                        currentGroup = {
                            name: child.dataset.projectName,
                            header: child,
                            tasks: []
                        };
                        groups.push(currentGroup);
                    } else if (child.classList.contains('task-row') && currentGroup) {
                        currentGroup.tasks.push(child);
                    }
                });

                groups.sort(function(a, b) {
                    const aP = pinned.includes(a.name);
                    const bP = pinned.includes(b.name);
                    if (aP && !bP) return -1;
                    if (!aP && bP) return 1;
                    return 0;
                });

                groups.forEach(function(g) {
                    tbody.appendChild(g.header);
                    g.tasks.forEach(function(t) {
                        tbody.appendChild(t);
                    });
                });
            }

            // Init: pin buttons
            document.querySelectorAll('.pin-btn').forEach(function(btn) {
                btn.addEventListener('click', togglePin);
                updatePinButton(btn);
            });

            // Init: collapse toggles — click anywhere on the project header row
            document.querySelectorAll('.project-group-row td').forEach(function(td) {
                td.addEventListener('click', function(e) {
                    if (e.target.closest('.pin-btn')) return;
                    const btn = this.querySelector('.collapse-toggle');
                    if (!btn) return;
                    btn.classList.toggle('is-collapsed');
                    const isCollapsed = btn.classList.contains('is-collapsed');
                    const project = btn.dataset.project;
                    const rows = document.querySelectorAll(
                        '#tasksTable tbody tr.task-row[data-project-name="' + project.replace(/"/g, '\\"') + '"]'
                    );
                    rows.forEach(function(r) {
                        r.classList.toggle('project-tasks-collapsed', isCollapsed);
                    });
                });
            });

            // Default: collapse all projects
            collapseAllProjects();

            // Reorder pinned projects to top
            reorderProjects();
        });
    </script>
</body>
</html>


