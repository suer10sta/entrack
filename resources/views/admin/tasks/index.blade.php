<!DOCTYPE html>
<html lang="en">
<head>
    <x-favicon />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.Tasks - DalyTrac') }}</title> {{-- Updated title --}}
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
    {{-- Include dashboard_layouts.head if needed --}}
    {{-- @include('dashboard_layouts.head') --}}

    <style>
        /* Mobile-only: accordion by project + show only task name */
        @media (max-width: 1024px) {
            #tasksTable thead {
                display: none;
            }

            /* Remove horizontal scrolling on mobile */
            .tasks-table-responsive {
                overflow-x: visible !important;
            }

            #tasksTable tbody {
                display: block;
                width: 100%;
            }

            /* Project header as a modern clickable card */
            #tasksTable tbody tr.project-header {
                display: block;
                margin: 12px 12px 6px 12px;
                border-radius: 14px;
                border: 1px solid rgba(11, 38, 56, 0.15);
                background: linear-gradient(180deg, rgba(11,38,56,0.05), rgba(45,112,128,0.07));
                box-shadow: 0 18px 45px rgba(15,23,42,0.10), 0 3px 10px rgba(15,23,42,0.08);
            }

            #tasksTable tbody tr.project-header td {
                display: block;
                padding: 12px 14px !important;
                text-align: left !important;
            }

            /* Task rows become simple list items showing only name */
            #tasksTable tbody tr.task-row {
                display: block;
                margin: 0 12px;
                border-left: 1px solid rgba(226, 232, 240, 0.9);
                border-right: 1px solid rgba(226, 232, 240, 0.9);
                border-bottom: 1px solid rgba(226, 232, 240, 0.9);
                background: rgba(255,255,255,0.92);
            }

            #tasksTable tbody tr.task-row:first-of-type {
                border-top: 1px solid rgba(226, 232, 240, 0.9);
            }

            /* Only keep first column visible (task title) */
            #tasksTable tbody tr.task-row td {
                display: none;
            }
            #tasksTable tbody tr.task-row td:first-child {
                display: block;
                padding: 12px 14px !important;
                white-space: normal !important;
                word-break: break-word;
            }

            /* Make sure hidden rows actually disappear */
            #tasksTable tbody tr.hidden {
                display: none !important;
            }
        }

        /* Ensure hidden wins on all breakpoints */
        #tasksTable tbody tr.hidden {
            display: none !important;
        }
    </style>
</head>
<body class="dt-app-shell bg-[#f8fafc] text-slate-900 antialiased flex h-screen overflow-hidden">

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
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-[#f8fafc] p-4 sm:p-6 lg:px-20 lg:py-8">

            {{-- Page heading (same design as other Reports pages) --}}
            <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-5">
                <div class="min-w-0">
                    <p class="dt-overline mb-1">{{ __('messages.Reports') }}</p>
                    <h1 class="text-[22px] sm:text-[24px] font-bold tracking-tight text-[#0B2638] leading-tight">
                        {{ __('messages.Tasks Table') }}
                    </h1>
                </div>

                {{-- Buttons --}}
                <div class="flex flex-col sm:flex-row sm:flex-wrap gap-2 w-full sm:w-auto">
                    @php
                        $reportRoute = '#';
                        if (Auth::user()->hasRole('Responsable de projet')) $reportRoute = route('Responsable_de_projet.Tasksrepports.downloadRepports');
                        elseif (Auth::user()->hasRole('Admin')) $reportRoute = route('admin.Tasksrepports.downloadRepports');
                        elseif (Auth::user()->hasRole('Employee')) $reportRoute = route('employee.Tasksrepports.downloadRepports');
                        elseif (Auth::user()->hasRole('Responsable de projet et gestionnaire')) $reportRoute = route('Responsable_gestionnaire.Tasksrepports.downloadRepports');
                    @endphp
                    <a href="{{ $reportRoute }}" class="dt-btn dt-btn-ghost w-full sm:w-auto">
                       <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                        {{ __('messages.Tasks Reports PDF') }}
                    </a>
                </div>
            </div>

            {{-- Container for the table --}}
            <div class="dt-card overflow-hidden">

                {{-- Filters --}}
                <div class="px-5 sm:px-6 py-4 border-b border-slate-100 flex flex-col sm:flex-row justify-between items-start sm:items-end gap-3">
                    <div class="w-full sm:max-w-md">
                        <label for="taskFilter" class="block text-[13px] font-medium text-slate-700 mb-1">{{ __('messages.Filter by Task Name') }}</label>
                        <select id="taskFilter" class="mt-1 block w-full rounded-xl border-slate-200 bg-white px-3 py-2 text-sm text-slate-600 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25">
                            <option value="">{{ __('messages.All Tasks') }}</option>
                            {{-- Use distinct task titles --}}
                            @foreach($tasks->unique('title') as $task)
                                <option value="{{ $task->title }}">{{ $task->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="w-full sm:w-auto flex flex-col sm:flex-row gap-3 sm:items-end">
                        <div class="w-full sm:w-64">
                            <label for="taskSearch" class="block text-[13px] font-medium text-slate-700 mb-1">{{ __('messages.Search Task') }}</label>
                            <input type="text" id="taskSearch" class="block w-full rounded-xl border-slate-200 bg-white text-slate-700 px-3 py-2 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm" placeholder="{{ __('messages.Type to search...') }}">
                        </div>
                        <form method="GET" action="{{ request()->url() }}" class="w-full sm:w-[7rem]">
                            <label for="projectsPerPage" class="block text-[13px] font-medium text-slate-700 mb-1">{{ __('messages.Projects per page') }}</label>
                            <select id="projectsPerPage" name="per_page" onchange="this.form.submit()" class="block w-full rounded-xl border-slate-200 bg-white px-3 py-2 text-sm text-slate-600 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25">
                                @foreach($projectsPerPageOptions as $option)
                                    <option value="{{ $option }}" @selected($projectsPerPage === $option)>{{ $option }}</option>
                                @endforeach
                            </select>
                        </form>
                    </div>
                </div>

                {{-- Session Message Alerts --}}
                @if(session('message'))
                    <div class="m-4 sm:m-5 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl relative flex items-center gap-2" role="alert">
                        <span class="block sm:inline text-[13px] font-medium">{{ session('message') }}</span>
                        <button type="button" onclick="this.parentElement.style.display='none'" class="absolute top-0 bottom-0 right-0 px-4 py-3 text-emerald-600 hover:text-emerald-800 font-bold text-xl" aria-label="Close">
                           &times;
                        </button>
                    </div>
                @endif
                @if(session('alertmessage'))
                    <div class="m-4 sm:m-5 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl relative flex items-center gap-2" role="alert">
                        <span class="block sm:inline text-[13px] font-medium">{{ session('alertmessage') }}</span>
                         <button type="button" onclick="this.parentElement.style.display='none'" class="absolute top-0 bottom-0 right-0 px-4 py-3 text-red-600 hover:text-red-800 font-bold text-xl" aria-label="Close">
                           &times;
                        </button>
                    </div>
                @endif


                {{-- Table Wrapper --}}
                <div class="p-0"> {{-- No padding for edge-to-edge table --}}
                    <div class="overflow-x-auto tasks-table-responsive custom-scrollbar">
                        <table class="w-full text-left border-collapse" id="tasksTable">
                            <thead class="border-b border-slate-100 bg-slate-50/80">
                                <tr class="dt-table-head">
                                    <th class="px-4 py-3 text-left whitespace-nowrap min-w-[220px]">{{ __('messages.Project / Task') }}</th>
                                    <th class="px-4 py-3 text-left whitespace-nowrap min-w-[160px]">{{ __('messages.Teams column') }}</th>
                                    <th class="px-4 py-3 text-left whitespace-nowrap min-w-[180px]" >{{ __('messages.Employees column') }}</th>
                                    <th class="px-4 py-3 text-left whitespace-nowrap">{{ __('messages.Duration') }}</th>
                                    <th class="px-4 py-3 text-left whitespace-nowrap">{{ __('messages.Est. Hours') }}</th>
                                    <th class="px-4 py-3 text-left whitespace-nowrap">{{ __('messages.Difference') }}</th>
                                    <th class="px-4 py-3 text-left whitespace-nowrap min-w-[150px]">{{ __('messages.Progress') }}</th>
                                    <th class="px-4 py-3 text-center whitespace-nowrap min-w-[110px]">{{ __('messages.Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($groupedTasks as $projectId => $projectTasks)
                                    @php
                                        // Eager load project or fetch once
                                        $project = \App\Models\Project::find($projectId);
                                        $firstTask = $projectTasks->first(); // Use first task to get project name if project not found
                                    @endphp
                                    {{-- Project Header Row --}}
                                    <tr class="bg-[#0B2638]/[0.04] hover:bg-[#0B2638]/[0.07] transition-colors project-header cursor-pointer select-none"
                                        data-project-group="{{ $projectId }}" aria-expanded="true"> {{-- Class for filtering --}}
                                        <td colspan="8" class="px-4 py-2.5 font-semibold text-[#0B2638] text-[13px] break-words border-y border-[#0B2638]/10">
                                            @php
                                                $projectName = $project ? $project->name : ($firstTask ? $firstTask->project_name : 'Unknown Project');
                                            @endphp
                                            <div class="flex items-center justify-between gap-3">
                                                <a href="{{ route('admin.projects.show', $projectId) }}" class="text-[#2D7080] hover:text-[#0B2638] hover:underline truncate">
                                                    {{ $projectName }}
                                                </a>
                                                <svg class="task-project-chevron w-5 h-5 text-slate-400 transition-transform flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6" />
                                                </svg>
                                            </div>
                                        </td>
                                    </tr>

                                    {{-- Tasks for this project --}}
                                    @foreach($projectTasks as $task)
                                        <tr class="hover:bg-slate-50/70 transition-colors task-row" data-project-parent="{{ $projectId }}"> {{-- Class for filtering --}}
                                            {{-- Task Title --}}
                                            <td class="px-4 py-3 whitespace-normal break-words text-[13px] font-medium text-[#0B2638] task-title">
                                                <a href="{{ route('admin.tasks.show', $task->id) }}" class="text-[#2D7080] hover:text-[#0B2638] hover:underline">
                                                    {{ $task->title }}
                                                </a>
                                            </td>
                                            {{-- Teams --}}
                                            <td class="px-4 py-3 text-[13px] text-slate-600">
                                                 <div class="flex flex-wrap gap-1.5">
                                                     @php
                                                         // Eager load teams for performance
                                                         $task_teams = App\Models\team_tasks::with('team')->where('task_id', $task->id)->get();
                                                         $teamIndexRoute = '#';
                                                         $canViewTeam = Auth::user()->can('View team');
                                                         if($canViewTeam) {
                                                            if (Auth::user()->hasRole('Admin')) $teamIndexRoute = route('admin.teams.index');
                                                            elseif (Auth::user()->hasRole('Responsable de projet')) $teamIndexRoute = route('Responsable_de_projet.teams.index');
                                                            elseif (Auth::user()->hasRole('Employee')) $teamIndexRoute = route('employee.teams.index');
                                                            elseif (Auth::user()->hasRole('Responsable de projet et gestionnaire')) $teamIndexRoute = route('Responsable_gestionnaire.teams.index');
                                                         }
                                                     @endphp
                                                     @forelse($task_teams as $team_task)
                                                        @if($team_task->team) {{-- Check if team exists --}}
                                                        <span class="dt-badge dt-badge-navy">
                                                            <span class="dt-dot"></span>
                                                             @if($canViewTeam)
                                                                 <a href="{{ $teamIndexRoute }}" class="hover:underline">{{ $team_task->team->name }}</a>
                                                             @else
                                                                 {{ $team_task->team->name }}
                                                             @endif
                                                        </span>
                                                        @endif
                                                     @empty
                                                        <span class="text-xs text-slate-400">{{ __('messages.No team') }}</span>
                                                     @endforelse
                                                 </div>
                                            </td>
                                            {{-- Employees --}}
                                            <td class="px-4 py-3 whitespace-normal break-words text-[13px] text-slate-600">
                                                <div class="flex flex-wrap gap-1.5">
                                                     @php
                                                         // Eager load employees for performance
                                                         $task_employees = App\Models\employees_tasks::with('employee')->where('task_id', $task->id)->get();
                                                         $canViewEmployee = Auth::user()->can('View employee');
                                                     @endphp
                                                     @forelse($task_employees as $emp_task)
                                                        @if($emp_task->employee) {{-- Check if employee exists --}}
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800">
                                                             @if($canViewEmployee)
                                                                 @php
                                                                     // Build the correct employee show route based on role and current employee id
                                                                     $employeeShowRoute = '#';
                                                                     if (Auth::user()->hasRole('Admin')) {
                                                                         $employeeShowRoute = route('admin.employees.show', $emp_task->employee_id);
                                                                     } elseif (Auth::user()->hasRole('Responsable de projet')) {
                                                                         $employeeShowRoute = route('Responsable_de_projet.employees.show', $emp_task->employee_id);
                                                                     } elseif (Auth::user()->hasRole('Employee')) {
                                                                         $employeeShowRoute = route('employee.employees.show', $emp_task->employee_id);
                                                                     } elseif (Auth::user()->hasRole('Responsable de projet et gestionnaire')) {
                                                                         $employeeShowRoute = route('Responsable_gestionnaire.employees.show', $emp_task->employee_id);
                                                                     }
                                                                 @endphp
                                                                 <a href="{{ $employeeShowRoute }}" class="hover:underline">{{ $emp_task->employee->name }}</a>
                                                             @else
                                                                 {{ $emp_task->employee->name }}
                                                             @endif
                                                        </span>
                                                        @endif
                                                     @empty
                                                        <span class="text-xs text-gray-400">{{ __('messages.No employees') }}</span>
                                                     @endforelse
                                                 </div>
                                            </td>
                                            {{-- Duration, Est. Hours, Difference, Progress --}}
                                            @php
                                                // Calculations (same as project table logic, applied to task)
                                                $tasktotalduration = App\Models\tasks_timer::where('task_id', $task->id)->sum('duration'); // in minutes
                                                $taskhours = floor(abs($tasktotalduration) / 60);
                                                $taskminutes = abs($tasktotalduration) % 60;

                                                $taskestimatedMinutes = max(0, (($task->estimated_hours ?? 0) - ($task->consumed_hours ?? 0)) * 60);
                                                $taskdifference = $taskestimatedMinutes - $tasktotalduration;
                                                $diffhours = floor(abs($taskdifference) / 60);
                                                $diffminutes = abs($taskdifference) % 60;

                                                $taskprogressPercentage = $taskestimatedMinutes > 0 ? max(0, min(100, ($tasktotalduration / $taskestimatedMinutes) * 100)) : 0;
                                                $taskformattedProgressPercentage = number_format($taskprogressPercentage, 2);

                                                $taskProgressBarColor = 'bg-emerald-500';
                                                if ($taskprogressPercentage >= 75) $taskProgressBarColor = 'bg-red-500';
                                                elseif ($taskprogressPercentage >= 50) $taskProgressBarColor = 'bg-amber-500';
                                            @endphp
                                            <td class="px-4 py-3 whitespace-nowrap text-[13px] text-slate-600">{{ sprintf('%02d', $taskhours) }}h {{ sprintf('%02d', $taskminutes) }}m</td>
                                            <td class="px-4 py-3 whitespace-nowrap text-[13px] text-slate-600">{{ $task->estimated_hours ?? 0 }}h</td>
                                            <td class="px-4 py-3 whitespace-nowrap text-[13px] font-medium">
                                                @if($task->estimated_hours !== null)
                                                    @if($taskdifference > 0)
                                                        <span class="dt-badge dt-badge-success">+{{ sprintf('%02d', $diffhours) }}h {{ sprintf('%02d', $diffminutes) }}m</span>
                                                    @elseif($taskdifference < 0)
                                                        <span class="dt-badge dt-badge-danger">-{{ sprintf('%02d', $diffhours) }}h {{ sprintf('%02d', $diffminutes) }}m</span>
                                                    @else
                                                        <span class="dt-badge dt-badge-navy">{{ sprintf('%02d', $diffhours) }}h {{ sprintf('%02d', $diffminutes) }}m</span>
                                                    @endif
                                                 @else
                                                    <span class="text-slate-300">-</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-[13px] text-slate-600">
                                                <div class="flex items-center gap-2">
                                                    <div class="w-full max-w-[110px] bg-slate-200 rounded-full h-1.5 overflow-hidden">
                                                        <div class="{{ $taskProgressBarColor }} h-1.5 rounded-full" style="width: {{ $taskformattedProgressPercentage }}%"></div>
                                                    </div>
                                                    <span class="text-[11px] font-semibold text-slate-500 whitespace-nowrap">{{ $taskformattedProgressPercentage }}%</span>
                                                </div>
                                            </td>
                                            {{-- Actions --}}
                                            <td class="px-4 py-3 whitespace-nowrap text-[13px]">
                                                <div class="flex items-center justify-center gap-2">
                                                @php
                                                    $canEditTask = Auth::user()->can('Update task');
                                                    $canDeleteTask = Auth::user()->can('Delete task');
                                                    $editRoute = '#'; $deleteRoute = '#';

                                                    if (Auth::user()->hasRole('Admin')) {
                                                        $editRoute = route('admin.tasks.edit', $task->id);
                                                        $deleteRoute = route('admin.tasks.destroy', $task->id);
                                                    } elseif (Auth::user()->hasRole('Responsable de projet')) {
                                                        $editRoute = route('Responsable_de_projet.tasks.edit', $task->id);
                                                        // $deleteRoute = ? // Add if applicable
                                                    } elseif (Auth::user()->hasRole('Employee')) {
                                                        $editRoute = route('employee.tasks.edit', $task->id);
                                                        // $deleteRoute = ? // Add if applicable
                                                    } elseif (Auth::user()->hasRole('Responsable de projet et gestionnaire')) {
                                                         $editRoute = route('Responsable_gestionnaire.tasks.edit', $task->id);
                                                         // $deleteRoute = ? // Add if applicable
                                                    }
                                                @endphp

                                                @if($canEditTask)
                                                    <a href="{{ $editRoute }}" title="{{ __('messages.Update task') }}"
                                                       class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-slate-200 bg-white text-[#2D7080] hover:bg-[#2D7080]/10 hover:border-[#2D7080]/30 transition-colors">
                                                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                  d="M15.232 5.232l3.536 3.536M4 20h4l9.5-9.5-4-4L4 16v4z" />
                                                        </svg>
                                                    </a>
                                                @endif

                                                @if($canDeleteTask && Auth::user()->hasRole('Admin')) {{-- Only show delete for Admin based on original --}}
                                                    <form method="post" action="{{ $deleteRoute }}" onsubmit="return confirm('{{ __('messages.Are you sure?') }}');" class="inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" title="{{ __('messages.Delete') }}"
                                                                class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-slate-200 bg-white text-red-600 hover:bg-red-50 hover:border-red-200 transition-colors">
                                                            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862A2 2 0 015.995 19.142L5 7m5 4v6m4-6v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3M4 7h16" />
                                                            </svg>
                                                        </button>
                                                    </form>
                                                @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                @empty
                                    <tr>
                                         <td colspan="8" class="px-6 py-10 text-center">
                                             <div class="dt-empty-icon mx-auto mb-3">
                                                 <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                             </div>
                                             <p class="text-[13.5px] font-semibold text-[#0B2638]">{{ __('messages.No tasks found index') }}</p>
                                         </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($groupedTasks->hasPages())
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 px-5 sm:px-6 py-3 border-t border-slate-100 bg-[#f8fafc]">
                        <p class="text-[12px] text-slate-500 text-center sm:text-left">
                            {{ $groupedTasks->firstItem() }}–{{ $groupedTasks->lastItem() }} / {{ $groupedTasks->total() }} {{ __('messages.Projects') }}
                        </p>
                        <nav class="flex flex-wrap items-center justify-center gap-1" aria-label="{{ __('messages.Pagination Navigation') }}">
                            @php($pageLinks = $groupedTasks->onEachSide(1)->linkCollection()->slice(1, -1))
                            <a href="{{ $groupedTasks->previousPageUrl() ?? '#' }}" aria-label="{{ __('messages.Previous') }}" @if(!$groupedTasks->previousPageUrl()) aria-disabled="true" @endif
                               class="inline-flex h-8 items-center justify-center gap-1 rounded-lg border px-2 text-[12px] font-medium transition-colors {{ $groupedTasks->previousPageUrl() ? 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50' : 'pointer-events-none border-slate-100 bg-slate-50 text-slate-300' }}">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
                                <span class="hidden sm:inline">{{ __('messages.Previous') }}</span>
                            </a>
                            @foreach($pageLinks as $pageLink)
                                @if(!$pageLink['url'])
                                    <span class="inline-flex h-8 min-w-[28px] items-center justify-center px-1 text-[12px] text-slate-400" aria-hidden="true">…</span>
                                @else
                                    <a href="{{ $pageLink['url'] }}" aria-label="{{ __('messages.Page') }} {{ $pageLink['label'] }}" @if($pageLink['active']) aria-current="page" @endif
                                       class="inline-flex h-8 min-w-[28px] items-center justify-center rounded-lg border px-1.5 text-[12px] font-medium transition-colors {{ $pageLink['active'] ? 'border-[#2D7080] bg-[#2D7080] text-white' : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50' }}">
                                        {{ $pageLink['label'] }}
                                    </a>
                                @endif
                            @endforeach
                            <a href="{{ $groupedTasks->nextPageUrl() ?? '#' }}" aria-label="{{ __('messages.Next') }}" @if(!$groupedTasks->nextPageUrl()) aria-disabled="true" @endif
                               class="inline-flex h-8 items-center justify-center gap-1 rounded-lg border px-2 text-[12px] font-medium transition-colors {{ $groupedTasks->nextPageUrl() ? 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50' : 'pointer-events-none border-slate-100 bg-slate-50 text-slate-300' }}">
                                <span class="hidden sm:inline">{{ __('messages.Next') }}</span>
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                            </a>
                        </nav>
                    </div>
                @endif
            </div>

        </main>
    </div>

    @livewireScripts
    {{-- @include('dashboard_layouts.scripts') --}}

    {{-- Filter Scripts --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const taskFilter = document.getElementById('taskFilter');
            const taskSearch = document.getElementById('taskSearch');
            const tableBody = document.querySelector('#tasksTable tbody');
            const allRows = Array.from(tableBody.querySelectorAll('tr')); // All rows including headers

            // Mobile-only: collapse tasks by project, showing only task names
            const setProjectExpanded = (headerRow, expanded) => {
                headerRow.setAttribute('aria-expanded', expanded ? 'true' : 'false');
                const chevron = headerRow.querySelector('.task-project-chevron');
                if (chevron) chevron.classList.toggle('rotate-180', expanded);

                const projectId = headerRow.getAttribute('data-project-group');
                if (!projectId) return;
                document
                    .querySelectorAll(`#tasksTable tbody tr.task-row[data-project-parent="${projectId}"]`)
                    .forEach((row) => row.classList.toggle('hidden', !expanded));
            };

            const initProjectAccordionForViewport = () => {
                const isMobile = window.matchMedia('(max-width: 1024px)').matches;
                document.querySelectorAll('#tasksTable tbody tr.project-header[data-project-group]').forEach((headerRow) => {
                    // Default: collapsed on mobile, expanded on desktop
                    setProjectExpanded(headerRow, !isMobile);

                    // Bind once
                    if (headerRow.dataset.boundToggle === '1') return;
                    headerRow.dataset.boundToggle = '1';

                    headerRow.addEventListener('click', (e) => {
                        if (e.target && e.target.closest('a')) return; // keep navigation
                        const expanded = headerRow.getAttribute('aria-expanded') === 'true';
                        setProjectExpanded(headerRow, !expanded);
                    });
                });
            };

            function applyFilters() {
                const filterValue = taskFilter.value.toLowerCase();
                const searchValue = taskSearch.value.toLowerCase();

                let visibleProjectHeaders = new Set();

                // Filter task rows
                allRows.forEach(row => {
                    if (row.classList.contains('task-row')) {
                        const taskTitleElement = row.querySelector('.task-title'); // Use the specific class
                        const taskTitle = taskTitleElement ? taskTitleElement.textContent.toLowerCase() : '';

                        const matchesFilter = filterValue === "" || taskTitle.includes(filterValue);
                        const matchesSearch = searchValue === "" || taskTitle.includes(searchValue);

                        if (matchesFilter && matchesSearch) {
                            row.style.display = "";
                            // Find preceding project header and mark for visibility
                            let header = row.previousElementSibling;
                            while(header && !header.classList.contains('project-header')) {
                                header = header.previousElementSibling;
                            }
                            if(header) {
                                visibleProjectHeaders.add(header);
                            }
                        } else {
                            row.style.display = "none";
                        }
                    }
                });

                // Show/hide project headers
                allRows.forEach(row => {
                     if (row.classList.contains('project-header')) {
                         row.style.display = visibleProjectHeaders.has(row) ? "" : "none";
                     }
                });

                // After filtering, re-apply accordion state (mobile should remain collapsed unless user opened)
                // We keep current header aria-expanded where possible.
                const isMobile = window.matchMedia('(max-width: 1024px)').matches;
                if (isMobile) {
                    document.querySelectorAll('#tasksTable tbody tr.project-header[data-project-group]').forEach((headerRow) => {
                        const expanded = headerRow.getAttribute('aria-expanded') === 'true';
                        setProjectExpanded(headerRow, expanded);
                    });
                }
            }

            taskFilter.addEventListener('change', applyFilters);
            taskSearch.addEventListener('input', applyFilters);

            initProjectAccordionForViewport();
            const mq = window.matchMedia('(max-width: 1024px)');
            if (mq.addEventListener) mq.addEventListener('change', initProjectAccordionForViewport);
            else mq.addListener(initProjectAccordionForViewport);
        });
    </script>
    
    {{-- Timer Alert Script (kept as is) --}}
    {{-- Consider moving this to a central JS file if used on multiple pages --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var modal = document.getElementById("customAlertModal"); // Ensure this modal exists in your included head/scripts
            var span = document.getElementsByClassName("close-button")[0]; // Ensure this exists
            var alertSound = document.getElementById("alertSound"); // Ensure this exists
            var alertInterval;
            var checkStateInterval;
            var previousTimerId = null;

            if (span) {
                span.onclick = function() {
                    if(modal) modal.style.display = "none";
                }
            }

            function showAlert() {
                if(modal) modal.style.display = "block";
                if(alertSound) alertSound.play();
            }

            function startAlertInterval() {
                clearInterval(alertInterval);
                alertInterval = setInterval(showAlert, 60000); // Show alert every 1 minute
            }

            function clearAlertInterval() {
                clearInterval(alertInterval);
            }

            function checkTimerState(tasksTimer) {
                // ... (rest of your checkTimerState logic - kept as is) ...
                 if (!tasksTimer) {
                    startAlertInterval();
                } else {
                    if (tasksTimer.paused === null || tasksTimer.paused === 0) {
                        clearAlertInterval();
                    } else if (tasksTimer.paused === 1) {
                        startAlertInterval();
                    }
                    if (tasksTimer.paused_date !== null && tasksTimer.paused === 0) {
                       clearAlertInterval();
                   }
                }
            }

            function fetchTimerState() {
                let url = '';
                @if(Auth::user()->hasRole('Responsable de projet'))
                    url = '/Responsable_de_projet/pointage/get-timer-state';
                @elseif(Auth::user()->hasRole('Admin'))
                    url = '/admin/pointage/get-timer-state';
                @elseif(Auth::user()->hasRole('Employee'))
                    url = '/employee/pointage/get-timer-state';
                @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                     url = '/Responsable_gestionnaire/pointage/get-timer-state'; // Added this role
                @endif

                if (url) { 
                    // JSON headers: an expired session must yield 401 (no login
                    // redirect), otherwise this background poll would store the
                    // JSON endpoint as the post-login "intended" URL.
                    fetch(url, {
                            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                        })
                        .then(response => { if (!response.ok) return null; return response.json(); })
                        .then(data => {
                            if (!data) return;
                            var currentTimerId = data.tasksTimer ? data.tasksTimer.id : null;
                            if (previousTimerId !== null && currentTimerId === null) {
                                location.reload();
                                return;
                            }
                            previousTimerId = currentTimerId;
                            checkTimerState(data.tasksTimer);
                        })
                        .catch(error => {
                            console.error('Error fetching timer state:', error);
                            
                        });
                } else {
                     console.log("No timer state URL defined for this user role.");
                     
                }
            }

            // Initial check
            fetchTimerState();

            // Set up periodic checks every 1 minute
            checkStateInterval = setInterval(fetchTimerState, 1 * 60 * 1000);
            console.log("Timer state checker started.");
        });
    </script>
    {{-- Searchable filter selects (Tom Select). Syncs the original selects and
         fires their change events, so existing filter logic keeps working. --}}
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            ['taskFilter'].forEach(function (id) {
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
