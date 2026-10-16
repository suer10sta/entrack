<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <x-favicon />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $task->title }} — DalyTrac</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite('resources/css/app.css')
</head>
<body class="dt-app-shell bg-[#f8fafc] text-slate-900 antialiased flex h-screen overflow-hidden">
    {{-- Sidebar --}}
    @if(Auth::user()->hasRole('Admin'))
        <x-dashboard.sidebar />
    @else
        <x-employees.sidebar />
    @endif
    <div class="flex flex-col flex-1 overflow-y-auto">
        {{-- Navbar --}}
        <x-dashboard.navbar />

        <main class="flex-1 bg-[#f8fafc] px-3 py-4 sm:px-5 sm:py-5 lg:px-8">
            <div class="max-w-5xl mx-auto w-full">
                @php
                    $projectName = $task->project_name ?? null;
                    $project = null;
                    if (!empty($task->project_id)) {
                        $project = App\Models\Project::find($task->project_id);
                        if (!$projectName && $project) $projectName = $project->name;
                    }
                    $projectShowRoute = null;
                    if (Auth::user()->hasRole('Admin') && $project) $projectShowRoute = route('admin.projects.show', $project->id);

                    $teams_id = App\Models\team_tasks::where('task_id', $task->id)->pluck('team_id');
                    $teams = App\Models\Teams::whereIn('id', $teams_id)->get();

                    $employee_id = App\Models\employees_tasks::where('task_id', $task->id)->pluck('employee_id');
                    $employees = App\Models\Employees::whereIn('id', $employee_id)->get();

                    $overallTaskDuration = App\Models\tasks_timer::where('task_id', $task->id)->sum('duration');
                    $estimatedMinutes = ($task->estimated_hours ?? 0) * 60;
                    $consumedMinutes = ($task->consumed_hours ?? 0) * 60;
                    $taskdifference = $estimatedMinutes - $consumedMinutes - $overallTaskDuration;
                    $hours = floor(abs($taskdifference) / 60);
                    $minutes = abs($taskdifference) % 60;

                    $taskestimatedMinutes = max(0, (($task->estimated_hours ?? 0) - ($task->consumed_hours ?? 0)) * 60);
                    $taskprogressPercentage = $taskestimatedMinutes > 0 ? ($overallTaskDuration / $taskestimatedMinutes) * 100 : 0;
                    $taskformattedProgressPercentage = number_format($taskprogressPercentage, 2);
                    $progressColor = 'bg-green-500';
                    if ($taskprogressPercentage >= 75) $progressColor = 'bg-red-500';
                    elseif ($taskprogressPercentage >= 50) $progressColor = 'bg-yellow-500';

                    $taskEndDate = App\Models\tasks_timer::where('task_id', $task->id)->max('end_date');

                    $editRoute = '#';
                    $deleteRoute = '#';
                    if (Auth::user()->hasRole('Admin')) {
                        $editRoute = route('admin.tasks.edit', $task->id);
                        $deleteRoute = route('admin.tasks.destroy', $task->id);
                    } elseif (Auth::user()->hasRole('Responsable de projet')) {
                        $editRoute = route('Responsable_de_projet.tasks.edit', $task->id);
                        $deleteRoute = route('Responsable_de_projet.tasks.destroy', $task->id);
                    } elseif (Auth::user()->hasRole('Employee')) {
                        $editRoute = route('employee.tasks.edit', $task->id);
                        $deleteRoute = route('employee.tasks.destroy', $task->id);
                    } elseif (Auth::user()->hasRole('Responsable de projet et gestionnaire')) {
                        $editRoute = route('Responsable_gestionnaire.tasks.edit', $task->id);
                    }

                    /* Viewer chrono context (same rules as navbar/board) */
                    $viewerEmployee = App\Models\Employees::where('user_id', Auth::id())->first();
                    $viewerLink = $viewerEmployee
                        ? App\Models\employees_tasks::where('employee_id', $viewerEmployee->id)->where('task_id', $task->id)->first()
                        : null;
                    $viewerTimer = ($viewerEmployee && $viewerLink)
                        ? App\Models\tasks_timer::where('task_employee_id', $viewerLink->id)->whereNull('end_date')->first()
                        : null;
                    $viewerPrefix = 'admin';
                    if (Auth::user()->hasRole('Employee')) $viewerPrefix = 'employee';
                    elseif (Auth::user()->hasRole('Responsable de projet')) $viewerPrefix = 'Responsable_de_projet';
                    elseif (Auth::user()->hasRole('Responsable de projet et gestionnaire')) $viewerPrefix = 'Responsable_gestionnaire';
                    elseif (Auth::user()->hasRole('super_admin') || Auth::user()->hasRole('SuperAdmin')) $viewerPrefix = 'SuperAdmin';
                @endphp

                {{-- Back + title --}}
                <div class="flex items-center gap-3 mb-4">
                    <a href="javascript:history.back()" title="{{ __('messages.Cancel') }}" aria-label="{{ __('messages.Cancel') }}"
                        class="inline-flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white text-[#0B2638] hover:bg-slate-50 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
                    </a>
                    <div class="min-w-0 flex-1">
                        <p class="text-[10px] font-bold uppercase tracking-[0.08em] text-slate-400">{{ __('messages.Task Details') }}</p>
                        <h1 class="text-[19px] sm:text-[22px] font-bold tracking-tight text-[#0B2638] leading-tight truncate" title="{{ $task->title }}">
                            {{ $task->title }}
                        </h1>
                    </div>
                    <span class="dt-badge {{ $taskprogressPercentage >= 75 ? 'dt-badge-danger' : ($taskprogressPercentage >= 50 ? 'dt-badge-warning' : 'dt-badge-success') }} flex-shrink-0">
                        {{ $taskformattedProgressPercentage }}%
                    </span>
                </div>

                {{-- Alert --}}
                @if(session('alertmessage'))
                <div class="mb-3 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl relative" role="alert">
                    <strong class="font-bold text-[13px]">{{ __('messages.Error!') }}</strong>
                    <span class="block sm:inline text-[13px]">{{ session('alertmessage') }}</span>
                    <button type="button" onclick="this.parentElement.style.display='none';" class="absolute top-2 right-3 font-bold text-lg leading-none" aria-label="{{ __('messages.Close') }}">&times;</button>
                </div>
                @endif

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-3 items-start">

                    {{-- LEFT: overview + description + employees ── --}}
                    <div class="lg:col-span-2 space-y-3 min-w-0">

                        {{-- Overview --}}
                        <section class="bg-white rounded-xl shadow-sm border border-slate-100 p-4 sm:p-5">
                            <div class="flex flex-wrap items-center gap-2 mb-3">
                                @if($projectShowRoute)
                                    <a href="{{ $projectShowRoute }}" class="inline-flex items-center gap-1.5 rounded-full bg-[#2D7080]/[0.08] text-[#2D7080] px-3 py-1 text-[12px] font-semibold hover:bg-[#2D7080]/[0.15] transition-colors max-w-full">
                                        <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                        <span class="truncate">{{ $projectName ?? '-' }}</span>
                                    </a>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-slate-100 text-slate-500 px-3 py-1 text-[12px] font-semibold max-w-full"><span class="truncate">{{ $projectName ?? '-' }}</span></span>
                                @endif
                                @foreach($teams as $team)
                                    <span class="inline-flex items-center rounded-full bg-slate-100 text-slate-600 px-3 py-1 text-[12px] font-medium">{{ $team->name }}</span>
                                @endforeach
                            </div>
                            @if($task->description)
                                <p class="text-[13px] text-slate-600 leading-relaxed break-words">{{ $task->description }}</p>
                            @endif
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 mt-4">
                                <div class="rounded-lg bg-slate-50 border border-slate-100 px-3 py-2">
                                    <p class="text-[10px] font-bold uppercase tracking-[0.07em] text-slate-400">{{ __('messages.Estimated Hours') }}</p>
                                    <p class="text-[15px] font-bold text-[#0B2638] tabular-nums mt-0.5">{{ $task->estimated_hours ?? '—' }}h</p>
                                </div>
                                <div class="rounded-lg bg-slate-50 border border-slate-100 px-3 py-2">
                                    <p class="text-[10px] font-bold uppercase tracking-[0.07em] text-slate-400">{{ __('messages.Consumed Hours') }}</p>
                                    <p class="text-[15px] font-bold text-[#0B2638] tabular-nums mt-0.5">{{ $task->consumed_hours ?? 0 }}h</p>
                                </div>
                                <div class="rounded-lg bg-slate-50 border border-slate-100 px-3 py-2">
                                    <p class="text-[10px] font-bold uppercase tracking-[0.07em] text-slate-400">{{ __('messages.Difference') }}</p>
                                    <p class="text-[15px] font-bold tabular-nums mt-0.5">
                                        @if($task->estimated_hours !== null)
                                            @if($taskdifference > 0)
                                                <span class="text-green-600">+{{$hours}}h:{{ str_pad($minutes, 2, '0', STR_PAD_LEFT) }}m</span>
                                            @elseif($taskdifference < 0)
                                                <span class="text-red-600">-{{$hours}}h:{{ str_pad($minutes, 2, '0', STR_PAD_LEFT) }}m</span>
                                            @else
                                                <span class="text-blue-600">{{$hours}}h:{{ str_pad($minutes, 2, '0', STR_PAD_LEFT) }}m</span>
                                            @endif
                                        @else
                                            <span class="text-slate-300">—</span>
                                        @endif
                                    </p>
                                </div>
                                <div class="rounded-lg bg-slate-50 border border-slate-100 px-3 py-2">
                                    <p class="text-[10px] font-bold uppercase tracking-[0.07em] text-slate-400">{{ __('messages.End Date') }}</p>
                                    <p class="text-[15px] font-bold text-[#0B2638] tabular-nums mt-0.5">
                                        @if($taskEndDate)
                                            {{ \Carbon\Carbon::parse($taskEndDate)->format('d-m-Y H:i') }}
                                        @else
                                            <span class="text-slate-300">—</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <div class="mt-4">
                                <div class="flex items-center justify-between text-[11.5px] font-semibold text-slate-500 mb-1.5">
                                    <span>{{ __('messages.Progress') }}</span>
                                    <span class="tabular-nums">{{ $taskformattedProgressPercentage }}%</span>
                                </div>
                                <div class="w-full bg-slate-100 rounded-full h-2.5 overflow-hidden">
                                    <div class="{{ $progressColor }} h-2.5 rounded-full transition-all" style="width: {{ min($taskprogressPercentage, 100) }}%"></div>
                                </div>
                            </div>
                        </section>

                        {{-- Employees & durations --}}
                        <section class="bg-white rounded-xl shadow-sm border border-slate-100 p-4 sm:p-5">
                            <h2 class="text-[13px] font-bold text-[#0B2638] mb-2">{{ __('messages.Employees') }}</h2>
                            <div class="divide-y divide-slate-50">
                                @forelse($employees as $employee)
                                    @php
                                        $employeeTaskDuration = App\Models\tasks_timer::where('task_id', $task->id)
                                            ->where('employee_id', $employee->id)
                                            ->sum('duration');
                                        $taskhours = floor($employeeTaskDuration / 60);
                                        $taskminutes = $employeeTaskDuration % 60;

                                        $employeeShowRoute = '#';
                                        if (Auth::user()->hasRole('Admin') && Auth::user()->can('View employee')) $employeeShowRoute = route('admin.employees.show', $employee->id);
                                        elseif (Auth::user()->hasRole('Responsable de projet') && Auth::user()->can('View employee')) $employeeShowRoute = route('Responsable_de_projet.employees.show', $employee->id);
                                        elseif (Auth::user()->hasRole('Employee') && Auth::user()->can('View employee')) $employeeShowRoute = route('employee.employees.show', $employee->id);
                                        elseif (Auth::user()->hasRole('Responsable de projet et gestionnaire') && Auth::user()->can('View employee')) $employeeShowRoute = route('Responsable_gestionnaire.employees.show', $employee->id);

                                        $durationInfoRoute = '#';
                                        if (Auth::user()->hasRole('Admin')) $durationInfoRoute = route('admin.tasks.durationInfos', ['employee' => $employee->id, 'task' => $task->id]);
                                        elseif (Auth::user()->hasRole('Responsable de projet')) $durationInfoRoute = route('Responsable_de_projet.tasks.durationInfos', ['employee' => $employee->id, 'task' => $task->id]);
                                        elseif (Auth::user()->hasRole('Employee')) $durationInfoRoute = route('employee.tasks.durationInfos', ['employee' => $employee->id, 'task' => $task->id]);
                                        elseif (Auth::user()->hasRole('Responsable de projet et gestionnaire')) $durationInfoRoute = route('Responsable_gestionnaire.tasks.durationInfos', ['employee' => $employee->id, 'task' => $task->id]);
                                    @endphp
                                    <div class="flex items-center gap-2 py-2">
                                        <a href="{{ $employeeShowRoute }}" class="flex-1 min-w-0 truncate text-[13px] font-medium text-[#2D7080] hover:underline">{{ $employee->name }}</a>
                                        <a href="{{ $durationInfoRoute }}" class="flex-shrink-0 font-mono font-semibold text-[12px] tabular-nums text-slate-500 hover:text-[#0B2638] transition-colors">
                                            {{ $taskhours }}h:{{ str_pad($taskminutes, 2, '0', STR_PAD_LEFT) }}m
                                        </a>
                                    </div>
                                @empty
                                    <p class="text-[12.5px] text-slate-400 py-1">—</p>
                                @endforelse
                            </div>
                        </section>
                    </div>

                    {{-- RIGHT: chrono + actions ── --}}
                    <div class="space-y-3 min-w-0">
                        <section class="bg-white rounded-xl shadow-sm border border-slate-100 p-4">
                            <h2 class="text-[13px] font-bold text-[#0B2638] mb-2.5">{{ __('messages.Duration') }}</h2>
                            @if($viewerTimer)
                                @php
                                    $vtBase = (float) ($viewerTimer->duration ?? 0) * 60;
                                @endphp
                                <p id="details-live" class="font-mono font-bold text-[26px] tabular-nums text-[#16a34a] tracking-tight"
                                   data-base="{{ $vtBase }}"
                                   data-start="{{ $viewerTimer->start_date }}"
                                   data-resume="{{ $viewerTimer->resume_date ?? '' }}"
                                   data-paused="{{ $viewerTimer->paused ? '1' : '0' }}">00:00:00</p>
                                <button type="button" data-stop-url="{{ route($viewerPrefix . '.tasks.stop', ['tasks' => $viewerTimer->task_employee_id, 'employee_id' => $viewerTimer->employee_id]) }}"
                                        onclick="if(window.ChronoStopModal){window.ChronoStopModal.open(this.getAttribute('data-stop-url'))}"
                                        class="mt-2.5 w-full inline-flex items-center justify-center gap-1.5 h-9 rounded-lg bg-red-500 hover:bg-red-600 text-white text-[13px] font-semibold transition-colors">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24"><rect x="4" y="4" width="16" height="16" rx="2"/></svg>
                                    {{ __('messages.Stop') }}
                                </button>
                            @elseif($viewerLink)
                                <p class="font-mono font-bold text-[15px] tabular-nums text-slate-400">00:00:00</p>
                                <form action="{{ route($viewerPrefix . '.tasks.start', ['tasks' => $viewerLink->id, 'employee_id' => $viewerEmployee->id]) }}" method="POST" class="mt-2.5"
                                    data-chrono-start data-chrono-employee="{{ $viewerEmployee->id }}"
                                    data-chrono-emp-task="{{ $viewerLink->id }}" data-chrono-task="{{ $task->id }}">
                                    @csrf
                                    <button type="submit"
                                            class="w-full inline-flex items-center justify-center gap-1.5 h-9 rounded-lg bg-[#0B2638] hover:bg-[#14364d] text-white text-[13px] font-semibold transition-colors">
                                        <svg class="w-3 h-3" viewBox="0 0 24 24" fill="currentColor"><polygon points="5,3 19,12 5,21"/></svg>
                                        {{ __('messages.Start') }}
                                    </button>
                                </form>
                            @else
                                <p class="text-[12.5px] text-slate-400">{{ __('messages.Task') }}</p>
                            @endif
                        </section>

                        <section class="bg-white rounded-xl shadow-sm border border-slate-100 p-4">
                            <h2 class="text-[13px] font-bold text-[#0B2638] mb-2.5">{{ __('messages.Actions') }}</h2>
                            <div class="flex flex-col gap-2">
                                @can('Update task')
                                <a href="{{ $editRoute }}" class="w-full inline-flex items-center justify-center gap-1.5 h-9 rounded-lg bg-[#2D7080] hover:bg-[#245f6d] text-white text-[13px] font-semibold transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536M4 20h4l9.5-9.5-4-4L4 16v4z"/></svg>
                                    {{ __('messages.Edit') }}
                                </a>
                                @endcan
                                @can('Delete task')
                                <form method="post" action="{{ $deleteRoute }}" onsubmit="return confirm('{{ __('messages.Are you sure?') }}');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-full inline-flex items-center justify-center gap-1.5 h-9 rounded-lg bg-red-50 hover:bg-red-100 border border-red-200 text-red-600 text-[13px] font-semibold transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862A2 2 0 015.995 19.142L5 7m5 4v6m4-6v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3M4 7h16"/></svg>
                                        {{ __('messages.Delete') }}
                                    </button>
                                </form>
                                @endcan
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </main>
    </div>

    {{-- Shared Stop-Task popup (same flow as everywhere else) --}}
    <x-chrono-stop-modal />
    <script>
        /* After a modal stop on this page, reload so all states reflect server truth. */
        function refreshEmployeeData() { window.location.reload(); }
        /* Live elapsed for the running timer (display only; server stays the source of truth) */
        function detailsTick() {
            var el = document.getElementById('details-live');
            if (!el) return;
            var base = parseFloat(el.getAttribute('data-base') || '0');
            var paused = el.getAttribute('data-paused') === '1';
            var startMs = new Date(String(el.getAttribute('data-start') || '').replace(' ', 'T')).getTime();
            var resumeRaw = el.getAttribute('data-resume') || '';
            var resumeMs = resumeRaw ? new Date(resumeRaw.replace(' ', 'T')).getTime() : null;
            var live = paused ? 0 : Math.max(0, Math.round(((resumeMs || startMs) ? (Date.now() - (resumeMs || startMs)) / 1000 : 0)));
            var s = Math.max(0, Math.round(base + live));
            var h = Math.floor(s / 3600), m = Math.floor((s % 3600) / 60), sec = s % 60;
            el.textContent = String(h).padStart(2, '0') + ':' + String(m).padStart(2, '0') + ':' + String(sec).padStart(2, '0');
        }
        document.addEventListener('DOMContentLoaded', function () {
            detailsTick();
            setInterval(detailsTick, 1000);
        });
    </script>
</body>
</html>
