<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.Tasks') }} — DalyTrac</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite('resources/css/app.css')
    <style>
        /* Compact icon buttons (same look as the projects table actions) */
        .b-icon {
            display: inline-flex; align-items: center; justify-content: center;
            width: 28px; height: 28px; border-radius: 8px; border: 1px solid #e2e8f0;
            background: #fff; color: #475569; flex-shrink: 0; cursor: pointer;
            transition: background .15s, border-color .15s, color .15s;
            text-decoration: none;
        }
        .b-icon svg { width: 13px; height: 13px; flex-shrink: 0; }
        .b-icon-archive { color: #b45309; border-color: #fde68a; background: #fffbeb; }
        .b-icon-archive:hover { background: #fef3c7; border-color: #f59e0b; }
        .b-icon-delete { color: #dc2626; border-color: #fecaca; background: #fef2f2; }
        .b-icon-delete:hover { background: #fee2e2; border-color: #f87171; }
        /* Employee quick-filter chips */
        .emp-chip {
            display: inline-flex; align-items: center; gap: 6px; flex-shrink: 0;
            padding: 5px 12px; border-radius: 9999px; font-size: 12.5px; font-weight: 600;
            border: 1px solid #e2e8f0; background: #fff; color: #475569;
            transition: background-color .15s, border-color .15s, color .15s;
            text-decoration: none; white-space: nowrap;
        }
        .emp-chip:hover { border-color: #2D7080; color: #0B2638; }
        .emp-chip.is-active { background: #0B2638; border-color: #0B2638; color: #fff; }
        .emp-chip.is-active .emp-dot { background: #E7B84B; }
        .emp-dot { width: 7px; height: 7px; border-radius: 9999px; background: #cbd5e1; flex-shrink: 0; }
    </style>
</head>
<body class="dt-app-shell bg-[#f8fafc] text-slate-900 antialiased flex h-screen overflow-hidden">

    @if(Auth::user()->hasRole('Admin'))
        <x-dashboard.sidebar />
    @else
        <x-employees.sidebar />
    @endif

    <div class="flex flex-col flex-1 overflow-y-auto">
        <x-dashboard.navbar />

        <main class="flex-1 bg-[#f8fafc] px-3 py-4 sm:px-5 sm:py-5 lg:px-8">
            <div class="w-full">

                {{-- Header --}}
                <div class="flex items-center gap-3 mb-4">
                    <div class="min-w-0">
                        <h1 class="text-[20px] sm:text-[22px] font-bold tracking-tight text-[#0B2638] leading-tight">
                            {{ __('messages.Tasks') }}
                        </h1>
                    </div>
                    <span class="dt-badge dt-badge-navy flex-shrink-0">
                        <span class="dt-dot"></span>
                        {{ $visibleProjects->count() }}&nbsp;{{ $visibleProjects->count() > 1 ? __('messages.Projects') : __('messages.Project') }}
                    </span>
                </div>

                {{-- Filters --}}
                <form method="GET" action="{{ route($routePrefix . '.tasks.board') }}"
                      class="bg-white rounded-xl shadow-sm border border-slate-100 px-4 py-3 mb-3">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                        <div class="min-w-0">
                            <label for="f-project" class="block text-[11px] font-bold uppercase tracking-[0.07em] text-slate-400 mb-1">{{ __('messages.Project') }}</label>
                            <select id="f-project" name="project" onchange="this.form.submit()"
                                    class="w-full rounded-lg border-slate-200 bg-slate-50 text-[13px] text-slate-700 py-2 focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/20 outline-none transition">
                                <option value="">{{ __('messages.All Projects') }}</option>
                                @foreach($projects as $p)
                                    <option value="{{ $p->id }}" {{ (string)$fProject === (string)$p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="min-w-0">
                            <label for="f-team" class="block text-[11px] font-bold uppercase tracking-[0.07em] text-slate-400 mb-1">{{ __('messages.Teams label') }}</label>
                            <select id="f-team" name="team" onchange="this.form.submit()"
                                    class="w-full rounded-lg border-slate-200 bg-slate-50 text-[13px] text-slate-700 py-2 focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/20 outline-none transition">
                                <option value="">{{ __('messages.All') }}</option>
                                @foreach($teams as $t)
                                    <option value="{{ $t->id }}" {{ (string)$fTeam === (string)$t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="min-w-0">
                            <label for="f-employee" class="block text-[11px] font-bold uppercase tracking-[0.07em] text-slate-400 mb-1">{{ __('messages.Employees') }}</label>
                            <select id="f-employee" name="employee" onchange="this.form.submit()"
                                    class="w-full rounded-lg border-slate-200 bg-slate-50 text-[13px] text-slate-700 py-2 focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/20 outline-none transition">
                                <option value="">{{ __('messages.All employees') }}</option>
                                @foreach($employees as $e)
                                    <option value="{{ $e->id }}" {{ (string)$fEmployee === (string)$e->id ? 'selected' : '' }}>{{ $e->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </form>

                {{-- Employee quick-filter chips (wrap, no horizontal scroll) --}}
                <div class="flex flex-wrap gap-1.5 mb-4" role="group" aria-label="{{ __('messages.Employees') }}">
                    <a href="{{ route($routePrefix . '.tasks.board', array_filter(['project' => $fProject ?: null, 'team' => $fTeam ?: null])) }}"
                       class="emp-chip {{ $fEmployee === '' || $fEmployee === null ? 'is-active' : '' }}">
                        <span class="emp-dot"></span>{{ __('messages.All') }}
                    </a>
                    @foreach($employees as $e)
                        <a href="{{ route($routePrefix . '.tasks.board', array_filter(['project' => $fProject ?: null, 'team' => $fTeam ?: null, 'employee' => $e->id])) }}"
                           class="emp-chip {{ (string)$fEmployee === (string)$e->id ? 'is-active' : '' }}">
                            <span class="emp-dot"></span>{{ $e->name }}
                        </a>
                    @endforeach
                </div>

                <div class="columns-1 sm:columns-2 lg:columns-3 xl:columns-4 gap-3">
                @foreach($visibleProjects as $project)
                    @php $projectTasks = $grouped[$project->id]; @endphp
                    <section class="bg-white rounded-xl shadow-lg border border-slate-100 overflow-hidden break-inside-avoid mb-3 hover:shadow-xl transition-shadow">
                        {{-- Card header: name + delete/archive icons --}}
                        <div class="flex items-center gap-2 px-3 py-2 border-b border-slate-100">
                            <span class="w-2.5 h-2.5 rounded-full flex-shrink-0 ring-1 ring-black/10"
                                  style="background-color:{{ $project->color ?: '#FDE68A' }}"></span>
                            <h2 class="flex-1 min-w-0 truncate text-[14px] font-bold text-[#0B2638]" title="{{ $project->name }}">
                                {{ $project->name }}
                            </h2>
                            <span class="flex-shrink-0 text-[11px] font-semibold text-slate-400 tabular-nums">{{ $projectTasks->count() }}</span>
                            @can('Delete project')
                                <form action="{{ route($routePrefix . '.projects.archive', $project->id) }}" method="POST" class="inline flex-shrink-0"
                                      onsubmit="return confirm('{{ __('messages.Archive this project?') }}')">
                                    @csrf
                                    <button type="submit" class="b-icon b-icon-archive" title="{{ __('messages.Archive') }}" aria-label="{{ __('messages.Archive') }}">
                                        <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z"/></svg>
                                    </button>
                                </form>
                                <form action="{{ route($routePrefix . '.projects.destroy', $project->id) }}" method="POST" class="inline flex-shrink-0"
                                      onsubmit="return confirm('{{ __('messages.Are you sure you want to delete this project?') }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="b-icon b-icon-delete" title="{{ __('messages.Delete') }}" aria-label="{{ __('messages.Delete') }}">
                                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862A2 2 0 015.995 19.142L5 7m5 4v6m4-6v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            @endcan
                        </div>
                        {{-- Task rows: name | duration | start/stop --}}
                        <div class="divide-y divide-slate-50">
                            @foreach($projectTasks as $task)
                                @php
                                    $etId = $viewerLinks[$task->id] ?? null;
                                    $openTimer = $etId && isset($viewerOpen[$etId]) ? $viewerOpen[$etId] : null;
                                    $totalMin = (int) ($durations[$task->id] ?? 0);
                                    $stopUrl = $openTimer ? route($routePrefix . '.tasks.stop', ['tasks' => $openTimer->task_employee_id, 'employee_id' => $openTimer->employee_id]) : '';
                                @endphp
                                <div class="flex items-center gap-1.5 px-3 py-1.5 hover:bg-slate-50/70 transition-colors">
                                    <a href="{{ route($routePrefix . '.tasks.show', $task->id) }}"
                                       class="flex-1 min-w-0 truncate text-[13px] font-medium text-slate-800 hover:text-[#2D7080] hover:underline transition-colors"
                                       title="{{ $task->title }}">
                                        {{ $task->title }}
                                    </a>
                                    <a href="{{ route($routePrefix . '.tasks.show', $task->id) }}"
                                       class="flex-shrink-0 font-mono font-semibold text-[12px] tabular-nums text-slate-500 hover:text-[#2D7080] hover:underline cursor-pointer transition-colors min-w-[76px] text-right"
                                       title="{{ $task->title }}"
                                        @if($openTimer)
                                            data-tick="1"
                                            data-total-min="{{ $totalMin }}"
                                            data-start="{{ $openTimer->start_date }}"
                                            data-resume="{{ $openTimer->resume_date ?? '' }}"
                                            data-duration="{{ $openTimer->duration ?? 0 }}"
                                            data-paused="{{ $openTimer->paused ? '1' : '0' }}"
                                        @endif
                                    >{{ sprintf('%02dh %02dm', intdiv($totalMin, 60), $totalMin % 60) }}</a>
                                    @if($openTimer)
                                        <button type="button" data-stop-url="{{ $stopUrl }}"
                                                onclick="if(window.ChronoStopModal){window.ChronoStopModal.open(this.getAttribute('data-stop-url'))}"
                                                class="flex-shrink-0 inline-flex items-center gap-0.5 h-5 px-1.5 rounded-md bg-red-500 hover:bg-red-600 text-white text-[10px] font-semibold transition-colors">
                                            <svg class="w-2 h-2" fill="currentColor" viewBox="0 0 24 24"><rect x="4" y="4" width="16" height="16" rx="2"/></svg>
                                            {{ __('messages.Stop') }}
                                        </button>
                                    @elseif($etId)
                                        <form action="{{ route($routePrefix . '.tasks.start', ['tasks' => $etId, 'employee_id' => $viewerEmployeeId]) }}" method="POST" class="flex-shrink-0"
                                            data-chrono-start data-chrono-employee="{{ $viewerEmployeeId }}"
                                            data-chrono-emp-task="{{ $etId }}" data-chrono-task="{{ $task->id }}">
                                            @csrf
                                            <button type="submit"
                                                    class="inline-flex items-center gap-0.5 h-5 px-1.5 rounded-md text-[10.5px] font-semibold bg-emerald-500 text-white hover:bg-emerald-600 transition-colors">
                                                <svg class="w-2 h-2" viewBox="0 0 24 24" fill="currentColor"><polygon points="5,3 19,12 5,21"/></svg>
                                                {{ __('messages.Start') }}
                                            </button>
                                        </form>
                                    @else
                                        <span class="flex-shrink-0 inline-flex items-center h-7 px-2.5 rounded-lg bg-slate-100 text-slate-400 text-[11.5px] font-semibold"
                                              title="{{ __('messages.Task') }}">—</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endforeach
                </div>
                @if($visibleProjects->isEmpty())
                    <div class="bg-white rounded-xl border border-slate-100 px-6 py-14 text-center">
                        <div class="dt-empty-icon mx-auto mb-3">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"/></svg>
                        </div>
                        <p class="text-[13.5px] font-semibold text-[#0B2638]">{{ __('messages.No projects found.') }}</p>
                    </div>
                @endif
                </div>
            </div>
        </main>
    </div>

    {{-- Shared Stop-Task popup (same flow as everywhere else) --}}
    <x-chrono-stop-modal />
    <script>
        /* After a modal stop on this page, reload so rows reflect server truth
           (same UX as the project pages, which submit stop forms natively). */
        function refreshEmployeeData() { window.location.reload(); }
        /* Live elapsed for running rows (display only; server stays the source of truth) */
        function boardTick() {
            document.querySelectorAll('[data-tick="1"]').forEach(function (el) {
                var total = parseInt(el.getAttribute('data-total-min') || '0', 10) * 60;
                var paused = el.getAttribute('data-paused') === '1';
                var base = parseFloat(el.getAttribute('data-duration') || '0') * 60;
                var startMs = new Date(String(el.getAttribute('data-start') || '').replace(' ', 'T')).getTime();
                var resumeRaw = el.getAttribute('data-resume') || '';
                var resumeMs = resumeRaw ? new Date(resumeRaw.replace(' ', 'T')).getTime() : null;
                var live = 0;
                if (!paused) {
                    live = Math.max(0, Math.round(((resumeMs || startMs) ? (Date.now() - (resumeMs || startMs)) / 1000 : 0)));
                }
                var s = Math.max(0, Math.round(total + base + live));
                var h = Math.floor(s / 3600), m = Math.floor((s % 3600) / 60);
                el.textContent = String(h).padStart(2, '0') + 'h ' + String(m).padStart(2, '0') + 'm';
            });
        }
        document.addEventListener('DOMContentLoaded', function () {
            boardTick();
            setInterval(boardTick, 1000);
        });
    </script>
</body>
</html>
