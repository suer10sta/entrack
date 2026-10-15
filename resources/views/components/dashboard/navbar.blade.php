@php
    use App\Models\Alert;
    use Illuminate\Support\Facades\Auth;
    use Carbon\Carbon;

    /* ── Alerts ──────────────────────────────────────────────────────── */
    $navbarAlerts     = collect();
    $navbarAlertCount = 0;

    if (Auth::check() && Auth::user()->entreprise_id) {
        Alert::syncUpcomingEventAlerts(Auth::user()->entreprise_id, Auth::id());
        $navbarAlerts = Alert::query()
            ->where('entreprise_id', Auth::user()->entreprise_id)
            ->where('type', 'event')
            ->whereNull('read_at')
            ->where('trigger_time', '>=', Carbon::now()->subHour())
            ->orderBy('trigger_time')
            ->limit(5)
            ->get();
        $navbarAlertCount = $navbarAlerts->count();
    }

    /* ── Role-aware URL resolution for chrono (reuses existing routes) ── */
    $chronoRoutePrefix = 'admin';
    if (Auth::check()) {
        $u = Auth::user();
        if ($u->hasRole('Employee'))                                  $chronoRoutePrefix = 'employee';
        elseif ($u->hasRole('Responsable de projet'))                 $chronoRoutePrefix = 'Responsable_de_projet';
        elseif ($u->hasRole('Responsable de projet et gestionnaire')) $chronoRoutePrefix = 'Responsable_gestionnaire';
        elseif ($u->hasRole('super_admin') || $u->hasRole('SuperAdmin')) $chronoRoutePrefix = 'SuperAdmin';
    }

    $chronoTimerStateUrl    = '';
    $chronoAllProjectsUrl   = '';
    $chronoTaskInfoBase     = '';
    $chronoProjectTasksBase = '';
    $chronoTaskShowBase     = '';
    $chronoTaskStartBase    = '';
    $chronoTaskStopBase     = '';
    $chronoAddProjectUrl    = '';
    $chronoAddTaskTemplate  = '';
    $chronoCreateTaskUrl    = '';
    $chronoInitialProjects  = [];
    $chronoUserTimezone     = '';
    $chronoViewerEmployeeId = null;
    $chronoTzUpdateUrl      = '';
    try {
        if (Auth::check()) {
            $chronoUserTimezone = Auth::user()->timezone ?? '';
            $ve = \App\Models\Employees::where('user_id', Auth::id())->first();
            if ($ve) {
                $chronoViewerEmployeeId = $ve->id;
                $chronoTzUpdateUrl = route($chronoRoutePrefix . '.employees.updateEmployee');
            }
        }
    } catch (\Exception $e) {}

    /* Relative paths only: fetch() stays same-origin so session cookies are
       always sent, no matter which host the app is opened with (APP_URL
       may differ from the actual host, which silently breaks absolute-URL
       fetches: no cookies -> auth redirect -> no JSON -> empty lists). */
    try { $chronoTimerStateUrl  = parse_url(route($chronoRoutePrefix . '.pointage.get_timer_state'), PHP_URL_PATH); } catch (\Exception $e) {}
    try { $chronoAllProjectsUrl = parse_url(route($chronoRoutePrefix . '.filter.getAllProjects'), PHP_URL_PATH);     } catch (\Exception $e) {}
    try { $chronoAddProjectUrl  = route($chronoRoutePrefix . '.projects.create');           } catch (\Exception $e) {}
    try { $chronoAddTaskTemplate = route($chronoRoutePrefix . '.tasks.create', ['project_id' => '__PID__']); } catch (\Exception $e) {}
    try { $chronoCreateTaskUrl = route($chronoRoutePrefix . '.tasks.create'); } catch (\Exception $e) {}
    try {
        $base = str_replace('/filter/projects', '', parse_url(
            route($chronoRoutePrefix . '.filter.getAllProjects'), PHP_URL_PATH
        ));
        $chronoTaskInfoBase     = $base . '/filter/tasks';
        $chronoProjectTasksBase = $base . '/filter/projects';
        $chronoTaskStartBase    = $base . '/tasks';
        $chronoTaskStopBase     = $base . '/tasks';
    } catch (\Exception $e) {}
    try {
        $chronoTaskShowBase = ($chronoRoutePrefix === 'admin')
            ? '/admin/tasks/show'
            : preg_replace('/\/filter\/projects$/', '',
                parse_url(route($chronoRoutePrefix . '.filter.getAllProjects'), PHP_URL_PATH)
              ) . '/tasks/show';
    } catch (\Exception $e) { $chronoTaskShowBase = '/admin/tasks/show'; }

    if (Auth::check() && Auth::user()->entreprise_id) {
        $chronoProjectQuery = \App\Models\Project::query()
            ->where('entreprise_id', Auth::user()->entreprise_id)
            ->where(function ($q) {
                $q->whereNull('archived')->orWhere('archived', 0);
            })
            ->orderBy('name');

        $u = Auth::user();
        if ($u->hasRole('Responsable de projet') || $u->hasRole('Responsable de projet et gestionnaire')) {
            $employee = $u->employee;
            if ($employee) {
                $myTeamIds = $employee->teams()->pluck('teams.id');
                $chronoProjectQuery->whereHas('teams', function ($subQ) use ($myTeamIds) {
                    $subQ->whereIn('teams.id', $myTeamIds);
                });
            }
        }

        $chronoInitialProjects = $chronoProjectQuery
            ->get(['id', 'name'])
            ->map(fn ($p) => ['id' => $p->id, 'name' => $p->name ?: ('#'.$p->id)])
            ->values();
    }

    $isSoloPlanNavbar = optional(optional(Auth::user())->entreprise)->type_compt === 'solo';
    $chronoMultiAllowed = Auth::check() && method_exists(Auth::user(), 'hasRole')
        && (Auth::user()->hasRole('SuperAdmin') || Auth::user()->hasRole('super_admin'));

    /* ── Shared-navbar visibility ────────────────────────────────────
       Admin (+ SuperAdmin) keep the full navbar. Employee / Responsable
       de projet / Gestionnaire roles get a reduced navbar: sidebar
       toggles + language selector stay visible, the rest (chrono,
       search, quick-add, alerts) is hidden. Single implementation,
       role-based rendering only. */
    $isReducedNavbar = Auth::check() && method_exists(Auth::user(), 'hasAnyRole')
        && Auth::user()->hasAnyRole(['Employee', 'Responsable de projet', 'Responsable de projet et gestionnaire', 'Gestionnaire']);
@endphp

{{-- ── Trial / expiry banner ──────────────────────────────────────── --}}
@if(Auth::check() && Auth::user()->entreprise_id && Auth::user()->entreprise && Auth::user()->entreprise->trial_ends_at)
    @php
        $ent       = Auth::user()->entreprise;
        $isOnTrial = $ent->isOnTrial();
        $isExpired = $ent->trial_ends_at->isPast();
        $daysLeft  = $isOnTrial ? max(0, (int) ceil(now()->floatDiffInDays($ent->trial_ends_at))) : null;
    @endphp
    @if($isOnTrial && $daysLeft > 0)
        <div class="bg-[#E7B84B]/15 border-b border-[#E7B84B]/40 px-4 py-1.5 text-center">
            <p class="text-[12.5px] font-semibold text-[#7a5a12]">
                {{ __('messages.days_left_trial', ['days' => $daysLeft]) }}
                <a href="{{ route('profile.upgrade') }}" class="ml-2 inline-flex items-center rounded-lg bg-[#0B2638] px-2.5 py-0.5 text-[11.5px] font-semibold text-white hover:bg-[#14364d] no-underline">{{ __('messages.upgrade') }}</a>
            </p>
        </div>
    @elseif($isExpired)
        <div class="bg-red-50 border-b border-red-200 px-4 py-1.5 text-center">
            <p class="text-[12.5px] font-semibold text-red-800">
                {{ __('messages.trial_expired') }}
                <a href="{{ route('profile.upgrade') }}" class="ml-2 inline-flex items-center rounded-lg bg-red-600 px-2.5 py-0.5 text-[11.5px] font-semibold text-white hover:bg-red-700 no-underline">{{ __('messages.upgrade_plan') }}</a>
            </p>
        </div>
    @endif
@endif

<header class="sticky top-0 z-30 flex items-center justify-between gap-3 px-4 sm:px-6 py-3 bg-white/90 backdrop-blur border-b border-slate-200">
    <div class="flex-1 flex items-center gap-2.5 min-w-0">
        {{-- Desktop collapse toggle --}}
        <button
            type="button"
            class="hidden lg:inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-[#0B2638] hover:bg-slate-50 hover:border-slate-300 transition-colors"
            onclick="toggleDesktopSidebar()"
            aria-label="Toggle sidebar"
            title="Collapse / Expand sidebar"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h10" />
            </svg>
        </button>
        {{-- Mobile hamburger (shows sidebar) --}}
        <button
            type="button"
            class="lg:hidden inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-[#0B2638] hover:bg-slate-50 transition-colors"
            onclick="toggleAppSidebar(true)"
            aria-label="{{ __('messages.Open menu') }}"
        >
            <span class="sr-only">{{ __('messages.Open menu') }}</span>
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h10" />
            </svg>
        </button>

        @unless($isReducedNavbar)
        {{-- ── CHRONO ─────────────────────────────────────────────────── --}}
        <div x-data="navbarChrono()" x-init="init()" class="relative flex items-center gap-1.5 flex-shrink-0" @click.away="openPanel = false"
             @chrono-revalidate.window="checkRunningTimer($event.detail && $event.detail.force === true)">

            {{-- IDLE: Start Chrono button (stopwatch icon) — always rendered,
                 never gated on the timer-state request (no delayed appearance) --}}
            <button x-cloak type="button"
                    @click="togglePanel()"
                    class="chrono-idle-btn" title="{{ __('messages.start_chrono') }}">
                {{-- Stopwatch: circle + hand + crown + left-lug --}}
                <svg class="w-[15px] h-[15px] flex-shrink-0" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="13" r="7"/>
                    <path d="M12 10v3.5l2.2 2.2"/>
                    <path d="M9.5 2.5h5"/>
                    <path d="M12 2.5v3"/>
                    <path d="M5.5 6.5 7 8"/>
                </svg>
                <span class="hidden sm:inline text-[12.5px] font-semibold">{{ __('messages.start_chrono') }}</span>
            </button>

            {{-- RUNNING: green pill with stopwatch + task name + timer + stop --}}
            <div x-show="isRunning && stateChecked" x-cloak class="chrono-running-pill">
                <svg class="w-[14px] h-[14px] flex-shrink-0" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="13" r="7"/>
                    <path d="M12 10v3.5l2.2 2.2"/>
                    <path d="M9.5 2.5h5"/>
                    <path d="M12 2.5v3"/>
                    <path d="M5.5 6.5 7 8"/>
                </svg>
                {{-- Task name (desktop only, truncated) --}}
                <span class="hidden md:block text-[12px] font-semibold text-white/90 max-w-[100px] truncate"
                      :title="taskName" x-text="taskName"></span>
                {{-- HH:MM:SS --}}
                <span class="font-mono font-bold text-[13px] tabular-nums text-white min-w-[58px] text-center"
                      x-text="elapsed"></span>
                {{-- Stop (primary task): opens the shared stop-comment popup --}}
                <button @click="askStopChrono()" type="button"
                        class="inline-flex items-center gap-1 h-6 px-2 rounded-md bg-white/25 hover:bg-white/40
                               text-white text-[11px] font-bold transition-colors">
                    <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 24 24">
                        <rect x="4" y="4" width="16" height="16" rx="2"/>
                    </svg>
                    <span class="hidden sm:inline">{{ __('messages.chrono_stop') }}</span>
                </button>
                {{-- Expand: SuperAdmin with several running tasks --}}
                <button x-show="multiAllowed && runningTimers.length > 1" @click="timersOpen = !timersOpen" type="button"
                        class="inline-flex items-center gap-0.5 h-6 px-1.5 rounded-md bg-white/25 hover:bg-white/40
                               text-white text-[11px] font-bold transition-colors flex-shrink-0"
                        :title="'{{ __('messages.chrono_running') }} (' + runningTimers.length + ')'">
                    <span x-text="runningTimers.length"></span>
                    <svg class="w-3 h-3 transition-transform" :class="timersOpen && 'rotate-180'" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
                    </svg>
                </button>
            </div>

            {{-- RUNNING LIST (SuperAdmin): floating popover, one row per active timer --}}
            <div x-show="timersOpen && isRunning && runningTimers.length > 1"
                 @click.away="timersOpen = false"
                 x-cloak
                 class="chrono-timers-panel">
                <p class="px-3.5 pt-3 pb-2 text-[10.5px] font-bold uppercase tracking-[0.08em] text-slate-400 border-b border-slate-100">
                    {{ __('messages.chrono_running') }} (<span x-text="runningTimers.length"></span>)
                </p>
                <div class="max-h-56 overflow-y-auto divide-y divide-slate-100">
                    <template x-for="rt in runningTimers" :key="rt.etId">
                        <div class="flex items-center gap-2 px-3.5 py-2">
                            <svg class="w-3.5 h-3.5 flex-shrink-0 text-[#2D7080]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="13" r="7"/>
                                <path d="M12 10v3.5l2.2 2.2"/>
                                <path d="M9.5 2.5h5"/>
                                <path d="M12 2.5v3"/>
                            </svg>
                            <span class="flex-1 min-w-0 truncate text-[12.5px] font-semibold text-[#0B2638]" :title="rt.name" x-text="rt.name"></span>
                            <span class="font-mono font-bold text-[12px] tabular-nums text-slate-600 flex-shrink-0" x-text="rt.elapsed"></span>
                            <button @click="askStopRowTimer(rt)" type="button"
                                    class="inline-flex items-center h-6 px-2 rounded-md bg-red-50 hover:bg-red-100 border border-red-200
                                           text-red-600 text-[11px] font-bold transition-colors flex-shrink-0">
                                {{ __('messages.chrono_stop') }}
                            </button>
                        </div>
                    </template>
                </div>
            </div>

            {{-- PICKER POPOVER ──────────────────────────────────────── --}}
            <div x-show="openPanel"
                 x-cloak
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-1"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-95 translate-y-1"
                 class="chrono-picker-panel ">

                {{-- Header --}}
                <div class="flex items-center justify-between px-4 pt-3 pb-2 border-b border-slate-100">
                    <p class="text-[10.5px] font-bold uppercase tracking-[.09em] text-slate-400">{{ __('messages.start_chrono') }}</p>
                    <button @click="openPanel = false" type="button" class="text-slate-400 hover:text-slate-600 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="p-3 space-y-3">

                    {{-- ── PROJECT SELECT ──────────────────────────────── --}}
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-[.07em] text-slate-400 mb-1.5">
                            {{ __('messages.Project') }}
                        </label>

                        <div class="relative">
                            <button type="button" @click="toggleProjectSelect()" class="chrono-select-btn">
                                <span class="truncate" :class="selectedProject ? 'text-[#0B2638]' : 'text-slate-400'"
                                      x-text="selectedProject ? selectedProject.name : '{{ __('messages.chrono_select_project') }}'"></span>
                                <svg class="w-3.5 h-3.5 text-slate-400 flex-shrink-0 transition-transform" :class="projectSelectOpen && 'rotate-180'" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
                                </svg>
                            </button>

                            <div x-show="projectSelectOpen" x-cloak
                                 class="absolute left-0 right-0 top-full mt-1 z-10 bg-white rounded-lg border border-slate-200 shadow-lg overflow-hidden">
                                <div class="relative border-b border-slate-100">
                                    <span class="absolute inset-y-0 left-2.5 flex items-center pointer-events-none text-slate-400">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.2-5.2m0 0A7.5 7.5 0 1 0 5.2 5.2a7.5 7.5 0 0 0 10.6 10.6Z"/>
                                        </svg>
                                    </span>
                                    <input type="text" x-model="projectSearch"
                                           @input="filterProjects()"
                                           @keydown.escape.stop="projectSelectOpen = false"
                                           @click.stop
                                           placeholder="{{ __('messages.chrono_search_project') }}"
                                           autocomplete="off" x-ref="projectInput"
                                           class="w-full pl-8 pr-3 py-2 text-[13px] border-0 bg-white outline-none"/>
                                </div>
                                <div class="max-h-44 overflow-y-auto">
                                    <div x-show="projectsLoading" class="px-3 py-3 text-center">
                                        <svg class="w-4 h-4 text-slate-300 animate-spin mx-auto" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                                        </svg>
                                    </div>
                                    <p x-show="!projectsLoading && filteredProjects.length === 0"
                                       class="px-3 py-2.5 text-[12px] text-slate-400 text-center">
                                       {{ __('messages.chrono_no_projects') }}
                                    </p>
                                    <template x-for="p in filteredProjects" :key="'p-'+p.id">
                                        <button type="button" @click="selectProject(p)"
                                                :class="selectedProject && selectedProject.id === p.id
                                                    ? 'bg-[#2D7080]/[0.09] font-semibold text-[#2D7080]'
                                                    : 'text-[#0B2638] hover:bg-[#2D7080]/[0.05]'"
                                                class="w-full px-3 py-2 text-left text-[13px] transition-colors truncate">
                                            <span class="truncate" x-text="p.name"></span>
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ── TASK SELECT (after a project is chosen) ─────── --}}
                    <div x-show="selectedProject">
                        <label class="block text-[11px] font-bold uppercase tracking-[.07em] text-slate-400 mb-1.5">
                            {{ __('messages.Task') }}
                        </label>

                        <div class="relative">
                            <button type="button" @click="toggleTaskSelect()" class="chrono-select-btn">
                                <span class="truncate" :class="selectedTask ? 'text-[#0B2638]' : 'text-slate-400'"
                                      x-text="selectedTask ? (selectedTask.title || selectedTask.task_name || '') : '{{ __('messages.chrono_select_task') }}'"></span>
                                <svg class="w-3.5 h-3.5 text-slate-400 flex-shrink-0 transition-transform" :class="taskSelectOpen && 'rotate-180'" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
                                </svg>
                            </button>

                            <div x-show="taskSelectOpen" x-cloak
                                 class="absolute left-0 right-0 top-full mt-1 z-10 bg-white rounded-lg border border-slate-200 shadow-lg overflow-hidden">
                                <div class="max-h-44 overflow-y-auto">
                                    <div x-show="tasksLoading" class="px-3 py-3 text-center">
                                        <svg class="w-4 h-4 text-slate-300 animate-spin mx-auto" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                                        </svg>
                                    </div>
                                    <p x-show="!tasksLoading && filteredTasks.length === 0"
                                       class="px-3 py-2.5 text-[12px] text-slate-400 text-center">
                                       {{ __('messages.chrono_no_tasks') }}
                                    </p>
                                    <template x-for="t in filteredTasks" :key="'t-'+t.id">
                                        <button type="button" @click="selectTask(t)"
                                                :class="selectedTask && selectedTask.id === t.id
                                                    ? 'bg-[#2D7080]/[0.09] font-semibold text-[#2D7080]'
                                                    : 'text-[#0B2638] hover:bg-slate-50'"
                                                class="w-full px-3 py-2 text-left text-[12.5px] transition-colors truncate">
                                            <span class="truncate" x-text="t.title || t.task_name || ''"></span>
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="button"
                            @click="startChrono()"
                            :disabled="!selectedProject || !selectedTask || starting"
                            class="w-full inline-flex items-center justify-center gap-1.5 h-8 rounded-lg bg-[#0B2638] text-white
                                   text-[12.5px] font-semibold hover:bg-[#14364d] transition-colors
                                   disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:bg-[#0B2638]">
                        <svg class="w-3 h-3" viewBox="0 0 24 24" fill="currentColor">
                            <polygon points="5,3 19,12 5,21"/>
                        </svg>
                        {{ __('messages.chrono_start') }}
                    </button>

                    <p x-show="chronoError" x-text="chronoError"
                       class="text-[11.5px] text-red-500 text-center"></p>
                </div>
            </div>

            {{-- TIMEZONE POPOVER: shown before the first chrono start when unset --}}
            <div x-show="tzOpen"
                 @click.away="tzOpen = false"
                 x-cloak
                 class="chrono-picker-panel p-4">
                <p class="text-[10.5px] font-bold uppercase tracking-[0.08em] text-slate-400 mb-1">{{ __('messages.Timezone') }}</p>
                <p class="text-[12px] text-slate-600 mb-3">{{ __('messages.chrono_tz_required') }}</p>
                <select x-model="tzValue"
                        class="w-full px-2.5 py-2 text-[13px] border border-slate-200 rounded-lg bg-white text-[#0B2638] focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none transition mb-2">
                    <option value="">{{ __('messages.Select your timezone') }}</option>
                    @foreach(timezone_identifiers_list() as $tzName)
                        <option value="{{ $tzName }}">{{ $tzName }}</option>
                    @endforeach
                </select>
                <p x-show="tzError" x-text="tzError" class="text-[11.5px] text-red-500 mb-2"></p>
                <div class="flex justify-end gap-2">
                    <button type="button" @click="tzOpen = false"
                            class="h-8 px-3 rounded-lg bg-white border border-slate-300 text-slate-600 text-[12.5px] font-semibold hover:bg-slate-50 transition-colors">
                        {{ __('messages.Cancel') }}
                    </button>
                    <button type="button" @click="saveTimezoneAndStart()" :disabled="tzSaving"
                            class="h-8 px-3 rounded-lg bg-[#0B2638] text-white text-[12.5px] font-semibold hover:bg-[#14364d] transition-colors disabled:opacity-40">
                        {{ __('messages.Save') }}
                    </button>
                </div>
            </div>
        </div>
        @endunless
    </div>

    {{-- ══════════════════════ CENTER: search + quick add ══════════════ --}}
    @unless($isReducedNavbar)
    <div class="flex-1 flex items-center justify-center gap-2 px-3 sm:px-4 min-w-0">

        {{-- Global search --}}
        <div class="relative w-full max-w-xs sm:max-w-sm lg:max-w-md min-w-0" x-data="searchDropdown()" x-init="init()">            
             <span class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-slate-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.2-5.2m0 0A7.5 7.5 0 1 0 5.2 5.2a7.5 7.5 0 0 0 10.6 10.6Z"/>
                </svg>
            </span>
            <input type="text"
                   placeholder="{{ __('messages.search_placeholder') }}"
                   x-model="query"
                   @input.debounce.300ms="performSearch()"
                   @focus="showResults = true"
                   @keydown.escape="showResults = false"
                   @keydown.arrow-down.prevent="navigateResults(1)"
                   @keydown.arrow-up.prevent="navigateResults(-1)"
                   @keydown.enter.prevent="selectResult()"
                   class="w-full pl-9 pr-4 py-2 rounded-lg bg-slate-100 border border-transparent text-[13px]
                          text-slate-700 placeholder:text-slate-400 focus:bg-white focus:border-[#2D7080]
                          focus:ring-2 focus:ring-[#2D7080]/20 outline-none transition"/>

            {{-- Results dropdown --}}
            <div x-show="showResults && (results.length > 0 || (query.length >= 2 && !loading))"
                 @click.away="showResults = false"
                 x-cloak
                 x-transition:enter="transition ease-out duration-100"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-75"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="absolute z-50 w-full mt-2 bg-white rounded-xl shadow-lg border border-slate-200 max-h-96 overflow-y-auto">

                <div x-show="loading" class="px-4 py-3 text-sm text-slate-500 text-center">{{ __('messages.Searching...') }}</div>
                <div x-show="!loading && results.length === 0 && query.length >= 2"
                     class="px-4 py-8 text-sm text-slate-500 text-center">
                    {{ __('messages.No results found for') }} "<span x-text="query"></span>"
                </div>
                <div x-show="!loading && results.length > 0" class="py-1">
                    <template x-for="(result, index) in results" :key="result.id + '-' + result.type">
                        <a :href="result.url" @click="showResults = false"
                           :class="{'bg-[#2D7080]/10': selectedIndex === index, 'hover:bg-slate-50': selectedIndex !== index}"
                           class="flex items-center gap-3 px-4 py-2.5 border-b border-slate-50 last:border-b-0 transition-colors cursor-pointer">
                            <div class="w-7 h-7 rounded-lg flex items-center justify-center flex-shrink-0"
                                 :class="{'bg-blue-100 text-blue-600':result.type==='client','bg-green-100 text-green-600':result.type==='project','bg-purple-100 text-purple-600':result.type==='task','bg-orange-100 text-orange-600':result.type==='employee','bg-indigo-100 text-indigo-600':result.type==='team','bg-red-100 text-red-600':result.type==='invoice','bg-yellow-100 text-yellow-600':result.type==='devis'}">
                                <svg x-show="result.type==='client'" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                <svg x-show="result.type==='project'" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                                <svg x-show="result.type==='task'" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <svg x-show="result.type==='employee'" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                <svg x-show="result.type==='team'" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                <svg x-show="result.type==='invoice'" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <svg x-show="result.type==='devis'" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"/></svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <p class="text-[13px] font-semibold text-slate-800 truncate" x-text="result.title"></p>
                                    <span x-show="result.archived" class="px-1.5 py-0.5 rounded text-[10px] font-semibold bg-red-100 text-red-700">
                                        {{ __('messages.Archived') }}
                                    </span>
                                </div>
                                <p class="text-[11.5px] text-slate-400 truncate" x-text="result.subtitle"></p>
                            </div>
                            <span class="text-[10.5px] font-semibold px-1.5 py-0.5 rounded flex-shrink-0"
                                  :class="{'bg-blue-100 text-blue-700':result.type==='client','bg-green-100 text-green-700':result.type==='project','bg-purple-100 text-purple-700':result.type==='task','bg-orange-100 text-orange-700':result.type==='employee','bg-indigo-100 text-indigo-700':result.type==='team','bg-red-100 text-red-700':result.type==='invoice','bg-yellow-100 text-yellow-700':result.type==='devis'}"
                                  x-text="result.type.charAt(0).toUpperCase()+result.type.slice(1)"></span>
                        </a>
                    </template>
                </div>
            </div>
        </div>

        {{-- Quick Add (+) — matches dt-stat-plus style from dashboard cards --}}
        <div class="relative flex-shrink-0" x-data="{ open: false }">
            <button @click="open = !open" @click.away="open = false" type="button"
                    class="dt-stat-plus"
                    title="{{ __('messages.quick_add') }}" aria-label="{{ __('messages.quick_add') }}">
                <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
            </button>

            <div x-show="open" x-cloak
                 x-transition:enter="transition ease-out duration-100"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-75"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="absolute left-1/2 -translate-x-1/2 top-full mt-2 w-52 bg-white rounded-xl shadow-lg border border-slate-200 z-50 overflow-hidden">
                <div class="px-3 pt-2.5 pb-1">
                    <p class="text-[10px] font-bold uppercase tracking-[.1em] text-slate-400">{{ __('messages.quick_add') }}</p>
                </div>
                <div class="py-1">
                    <a href="{{ route('admin.clients.create') }}" @click="open=false"
                       class="flex items-center gap-2.5 px-3 py-2 text-[13px] font-medium text-[#0B2638] hover:bg-slate-50 transition-colors">
                        <span class="w-6 h-6 flex items-center justify-center rounded-md bg-blue-50 text-blue-600 flex-shrink-0">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><rect x="4" y="3" width="16" height="18" rx="2"/><path d="M9 21v-4h6v4M8 7h2M8 11h2M14 7h2M14 11h2"/></svg>
                        </span>{{ __('messages.quick_add_client') }}
                    </a>
                    <a href="{{ route('admin.projects.create') }}" @click="open=false"
                       class="flex items-center gap-2.5 px-3 py-2 text-[13px] font-medium text-[#0B2638] hover:bg-slate-50 transition-colors">
                        <span class="w-6 h-6 flex items-center justify-center rounded-md bg-green-50 text-green-600 flex-shrink-0">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V7z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </span>{{ __('messages.quick_add_project') }}
                    </a>
                    <a href="{{ route('admin.tasks.create') }}" @click="open=false"
                       class="flex items-center gap-2.5 px-3 py-2 text-[13px] font-medium text-[#0B2638] hover:bg-slate-50 transition-colors">
                        <span class="w-6 h-6 flex items-center justify-center rounded-md bg-purple-50 text-purple-600 flex-shrink-0">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </span>{{ __('messages.quick_add_task') }}
                    </a>
                    @unless($isSoloPlanNavbar)
                    <a href="{{ route('admin.employees.create') }}" @click="open=false"
                       class="flex items-center gap-2.5 px-3 py-2 text-[13px] font-medium text-[#0B2638] hover:bg-slate-50 transition-colors">
                        <span class="w-6 h-6 flex items-center justify-center rounded-md bg-orange-50 text-orange-600 flex-shrink-0">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><circle cx="9" cy="7" r="3"/><path d="M3.5 19c0-3 2.3-4.5 5.5-4.5s5.5 1.5 5.5 4.5M16 5.5a3 3 0 010 5M17.5 14.5c1.9.6 2.5 1.8 2.5 3.5" stroke-linecap="round"/></svg>
                        </span>{{ __('messages.quick_add_employee') }}
                    </a>
                    @endunless
                    <div class="my-1 border-t border-slate-100"></div>
                    <a href="{{ route('admin.invoices.fullinvoice') }}" @click="open=false"
                       class="flex items-center gap-2.5 px-3 py-2 text-[13px] font-medium text-[#0B2638] hover:bg-[#E7B84B]/10 transition-colors">
                        <span class="w-6 h-6 flex items-center justify-center rounded-md bg-[#E7B84B]/20 text-[#b8860b] flex-shrink-0">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </span>{{ __('messages.quick_add_invoice') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endunless

    {{-- ══════════════════════ RIGHT: language ═════════════════════════ --}}
    <div class="flex-1 flex items-end justify-end px-3 sm:px-4 flex-shrink-0">
        <div class="relative" x-data="{ open: false }">
            <button @click="open = !open" @click.away="open = false" type="button"
                    class="flex items-center gap-1.5 h-8 px-2.5 rounded-lg border border-slate-200 bg-white
                           hover:bg-slate-50 hover:border-slate-300 transition-colors text-[12.5px] font-semibold text-slate-700"
                    title="{{ __('messages.Language') }}">
                @switch(app()->getLocale())
                    @case('fr') <img src="https://flagcdn.com/fr.svg" alt="Français" class="w-4 h-3 rounded-sm object-cover"> <span>FR</span> @break
                    @case('es') <img src="https://flagcdn.com/es.svg" alt="Español"  class="w-4 h-3 rounded-sm object-cover"> <span>ES</span> @break
                    @case('nl') <img src="https://flagcdn.com/be.svg" alt="NL"       class="w-4 h-3 rounded-sm object-cover"> <span>NL</span> @break
                    @default    <img src="https://flagcdn.com/gb.svg" alt="English"  class="w-4 h-3 rounded-sm object-cover"> <span>EN</span>
                @endswitch
                <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
                </svg>
            </button>
            <div x-show="open" x-cloak
                 x-transition:enter="transition ease-out duration-100"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-75"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="absolute right-0 top-full mt-2 w-44 bg-white rounded-xl shadow-lg border border-slate-200 z-50 py-1.5">
                @foreach([['en','gb','English'],['fr','fr','Français'],['es','es','Español'],['nl','be','']] as [$lc,$flag,$label])
                    <a href="{{ route('changeLang', ['lang' => $lc]) }}"
                       class="flex items-center gap-2.5 px-3 py-2 text-[13px] text-slate-700 hover:bg-slate-50 transition-colors
                              {{ app()->getLocale() === $lc ? 'bg-[#2D7080]/[0.07] font-semibold text-[#2D7080]' : '' }}">
                        <img src="https://flagcdn.com/{{ $flag }}.svg" alt="{{ $label }}" class="w-4 h-3 rounded-sm object-cover flex-shrink-0">
                        <span>{{ $lc === 'nl' ? __('messages.Nederlands') : $label }}</span>
                        @if(app()->getLocale() === $lc)
                            <svg class="w-3.5 h-3.5 ml-auto text-[#2D7080]" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>

        @unless($isReducedNavbar)
        {{-- Alerts dropdown (hidden trigger kept for JS compat) --}}
        <div class="relative" x-data="alertDropdown({ initialCount: {{ $navbarAlertCount }} })" x-init="init()">
            <div x-show="open" @click.away="open = false" x-cloak
                 x-transition:enter="transition ease-out duration-100"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-75"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="absolute right-0 top-full mt-2 w-80 bg-white rounded-xl shadow-lg border border-slate-200 z-50 max-h-96 overflow-y-auto">
                <div class="py-2" x-ref="alertList">
                    @foreach($navbarAlerts as $alert)
                        <div id="alert-card-{{ $alert->id }}" data-alert-card
                             class="px-4 py-3 hover:bg-slate-50 border-b border-slate-100 last:border-b-0 transition-colors">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-start gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-[#2D7080]/10 text-[#2D7080] flex items-center justify-center flex-shrink-0">
                                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h3m6 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/>
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-[13px] font-semibold text-slate-800">{{ __('messages.Event Reminder') }}</p>
                                        <p class="text-[12.5px] text-slate-600">{{ $alert->message }}</p>
                                        <p class="text-[11.5px] text-slate-400 mt-0.5">{{ optional($alert->trigger_time)->format('H:i') ?? '—' }} · {{ $alert->title }}</p>
                                    </div>
                                </div>
                                <button type="button" class="text-slate-400 hover:text-slate-600"
                                        @click.stop="deleteAlert({{ $alert->id }}, '{{ route('alerts.destroy', $alert) }}')">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                            <div class="mt-2 flex items-center justify-between text-[11.5px] text-slate-400">
                                <span>{{ optional($alert->created_at)->diffForHumans() ?? __('messages.Just now') }}</span>
                                @if(is_null($alert->read_at))
                                    <button type="button" class="text-[#2D7080] font-semibold hover:underline"
                                            @click.stop.prevent="markAsRead({{ $alert->id }}, '{{ route('alerts.read', $alert) }}')">
                                        {{ __('messages.Mark as read') }}
                                    </button>
                                @else
                                    <span class="text-green-600 font-medium">{{ __('messages.Read') }}</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endunless
    </div>

</header>

{{-- Global toast notifications (flash messages + AJAX flows) --}}
<x-app-toast />

{{-- Shared Stop-Task comment popup (same flow as the Tasks pages). Rendered once
     per page by the navbar; navbar Stop buttons open it instead of stopping
     directly, so the stop comment is always collected first. --}}
<x-chrono-stop-modal />

{{-- ── Navbar-specific CSS ──────────────────────────────────────────── --}}
<style>
    [x-cloak] { display: none !important; }

    /* Shared stop-comment modal (same popup as the Tasks pages) */
    .chrono-modal {
        display: none; position: fixed; z-index: 60; left: 0; top: 0;
        width: 100%; height: 100%; overflow: auto;
        background-color: rgba(0,0,0,0.5);
    }
    .chrono-modal-card {
        background-color: #fefefe; margin: 12% auto; padding: 20px;
        border: 1px solid #888; width: 80%; max-width: 600px; text-align: center;
        position: relative; border-radius: 0.75rem;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        animation: chronoModalIn 0.25s ease-out;
    }
    @keyframes chronoModalIn { from { transform: translateY(-40px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
    .chrono-close-button {
        color: #94a3b8; position: absolute; top: 10px; right: 15px;
        font-size: 28px; font-weight: bold; cursor: pointer; line-height: 1;
    }
    .chrono-close-button:hover { color: #475569; }

    /* Shared icon button */
    .nb-icon-btn {
        display: inline-flex; align-items: center; justify-content: center;
        width: 32px; height: 32px; border-radius: 8px;
        border: 1px solid #e2e8f0; background: #fff; color: #0B2638;
        flex-shrink: 0; cursor: pointer;
        transition: background-color .15s, border-color .15s, color .15s;
    }
    .nb-icon-btn:hover  { background: #f1f5f9; border-color: #cbd5e1; }
    .nb-icon-btn:focus-visible { outline: 2px solid #2D7080; outline-offset: 2px; }

    /* Quick-add (+) — identical to dt-stat-plus in dashboard cards */
    .dt-stat-plus {
        display: inline-flex; align-items: center; justify-content: center;
        width: 36px; height: 36px; border-radius: 12px;
        background: rgba(11,38,56,.06); color: #0B2638;
        box-shadow: inset 0 0 0 1px rgba(11,38,56,.10);
        flex-shrink: 0; cursor: pointer;
        transition: background-color .15s ease, box-shadow .15s ease, color .15s ease, transform .15s ease;
        border: none;
    }
    .dt-stat-plus svg { width: 16px; height: 16px; }
    .dt-stat-plus:hover {
        background: rgba(45,112,128,.12);
        box-shadow: inset 0 0 0 1px rgba(45,112,128,.35);
        color: #2D7080; transform: translateY(-1px);
    }
    .dt-stat-plus:focus-visible { outline: 2px solid #2D7080; outline-offset: 2px; }

    /* Chrono idle */
    .chrono-idle-btn {
        display: inline-flex; align-items: center; gap: 6px;
        height: 32px; padding: 0 10px; border-radius: 8px;
        border: 1px solid #b91c1c; background: #dc2626; color: #fff;
        cursor: pointer; white-space: nowrap; flex-shrink: 0;
        transition: background-color .15s, border-color .15s;
    }
    .chrono-idle-btn:hover { background: #b91c1c; border-color: #991b1b; }
    .chrono-idle-btn:focus-visible { outline: 2px solid #fca5a5; outline-offset: 2px; }

    .chrono-plus-btn {
        display: inline-flex; align-items: center; justify-content: center;
        width: 22px; height: 22px; border-radius: 6px;
        border: 1px solid #e2e8f0; background: #fff; color: #0B2638;
        text-decoration: none; flex-shrink: 0;
        transition: background-color .15s, border-color .15s, color .15s;
    }
    .chrono-plus-btn:hover { background: #f1f5f9; border-color: #cbd5e1; color: #2D7080; }

    .chrono-select-btn {
        display: flex; align-items: center; justify-content: space-between; gap: 8px;
        width: 100%; height: 34px; padding: 0 10px; border-radius: 8px;
        border: 1px solid #e2e8f0; background: #fff;
        font-size: 13px; font-weight: 500; text-align: left; cursor: pointer;
        color: #0B2638;
    }
    .chrono-select-btn:hover { border-color: #cbd5e1; background: #f8fafc; }

    /* Neutral slot while the global chrono state is being verified (no Start flash) */
    .chrono-skeleton {
        display: none;
    }

    /* Chrono running-timers list — floating popover, NEVER in-flow */
    .chrono-timers-panel {
        position: absolute; top: calc(100% + 8px); left: 0;
        width: 300px; max-width: calc(100vw - 24px);
        background: #fff; border: 1px solid #e2e8f0; border-radius: 12px;
        box-shadow: 0 20px 25px -5px rgba(11,38,56,.15), 0 8px 10px -6px rgba(11,38,56,.10);
        z-index: 50; overflow: hidden;
    }

    /* Chrono picker — floating popover, NEVER in-flow (navbar height stays fixed) */    .chrono-picker-panel {
        position: absolute; top: calc(100% + 8px); left: 0;
        width: 288px; max-width: calc(100vw - 24px);
        background: #fff; border: 1px solid #e2e8f0; border-radius: 12px;
        box-shadow: 0 20px 25px -5px rgba(11,38,56,.15), 0 8px 10px -6px rgba(11,38,56,.10);
        z-index: 50;
    }

    /* Chrono running — green pill */
    .chrono-running-pill {
        display: inline-flex; align-items: center; gap: 6px;
        height: 32px; padding: 0 8px 0 10px; border-radius: 8px;
        background: #16a34a; color: #fff; border: 1px solid #15803d;
        flex-shrink: 0;
        animation: chrono-pulse 2s ease-in-out infinite;
    }
    @keyframes chrono-pulse {
        0%, 100% { box-shadow: 0 0 0 0 rgba(22,163,74,.45); }
        50%       { box-shadow: 0 0 0 4px rgba(22,163,74,0); }
    }

    /* Responsive: hide chrono text on very small screens */
    @media (max-width: 400px) {
        .chrono-idle-btn span { display: none; }
        .chrono-skeleton { width: 32px; }
    }
</style>

@php
    $hideTrialModalRoutes = ['profile.*','subscription.*','profile.index'];
@endphp
@unless(request()->routeIs($hideTrialModalRoutes))
    <x-modals.trial-expired :show="$showTrialExpiredModal ?? false" />
@endunless

@once
<script>
    /* ── Ensure Alpine is loaded ──────────────────────────────────────── */
    if (!window.Alpine) {
        const s = document.createElement('script');
        s.src   = 'https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js';
        s.defer = true;
        document.head.appendChild(s);
    }

    /* ════════════════════════════════════════════════════════════════════
       Global chrono state — single source of truth on the client.
       Sole live writer: navbarChrono(), fed ONLY by the existing
       get_timer_state / start / stop endpoints (no second timer engine).
       Other components only READ (Start/Stop controls) or TRIGGER a
       revalidation via `chrono-revalidate` (same page) — never fetch/write.
    ════════════════════════════════════════════════════════════════════ */
    (function () {
        if (window.ChronoSync) return;
        const PING_KEY = 'dalytrac-chrono-ping';
        const sync = {
            snapshot: { checked: false, running: false, taskEmployeeId: null, taskId: null,
                        employeeId: null, taskName: '', elapsed: '00:00:00', multiAllowed: false },
            ensureStore() {
                try {
                    if (!window.Alpine || !Alpine.store) return null;
                    if (!Alpine.store('chrono')) Alpine.store('chrono', { ...this.snapshot });
                    return Alpine.store('chrono');
                } catch { return null; }
            },
            publish(patch, source) {
                var wasRunning = !!this.snapshot.running;
                Object.assign(this.snapshot, patch);
                const store = this.ensureStore();
                if (store) Object.assign(store, patch);
                this.syncRowControls();
                try { window.dispatchEvent(new CustomEvent('chrono:changed', { detail: { source: source || 'unknown' } })); } catch {}
                /* Cross-tab ping ONLY on running/idle flips: steady-state re-checks must
                   not ping, otherwise two open tabs revalidate each other forever
                   (request storm → socket exhaustion). */
                if (source === 'navbar' && !!this.snapshot.running !== wasRunning) {
                    try { localStorage.setItem(PING_KEY, String(Date.now())); } catch {}
                }
            },
            patch(patch) {
                Object.assign(this.snapshot, patch);
                try {
                    const s = window.Alpine && Alpine.store && Alpine.store('chrono');
                    if (s) Object.assign(s, patch);
                } catch {}
            },
            sameTask(form) {
                const s = this.snapshot;
                const empTask = form.getAttribute('data-chrono-emp-task');
                const taskId  = form.getAttribute('data-chrono-task');
                if (empTask && s.taskEmployeeId != null && String(empTask) === String(s.taskEmployeeId)) return true;
                if (taskId && s.taskId != null && String(taskId) === String(s.taskId)) return true;
                return false;
            },
            sameEmployee(form) {
                const s = this.snapshot;
                const emp = form.getAttribute('data-chrono-employee');
                return emp != null && emp !== '' && s.employeeId != null && String(emp) === String(s.employeeId);
            },
            lockButton(btn) {
                if (btn.disabled && !btn.dataset.chronoLocked) return; // pre-existing state: respect it
                if (!btn.dataset.chronoLocked) {
                    btn.dataset.chronoLocked = '1';
                    btn.dataset.chronoOpacity = btn.style.opacity || '';
                    btn.dataset.chronoCursor  = btn.style.cursor || '';
                }
                btn.disabled = true;
                btn.style.opacity = '.45';
                btn.style.cursor = 'not-allowed';
            },
            unlockButton(btn) {
                if (!btn.dataset.chronoLocked) return;
                delete btn.dataset.chronoLocked;
                btn.disabled = false;
                btn.style.opacity = btn.dataset.chronoOpacity || '';
                btn.style.cursor  = btn.dataset.chronoCursor || '';
                delete btn.dataset.chronoOpacity; delete btn.dataset.chronoCursor;
            },
            /* Reflect global state on per-task Start/Stop controls (display only).
               Single-timer rule is enforced server-side; concurrent timers are only
               possible for SuperAdmin employees (multiAllowed) — then leave all alone. */
            syncRowControls() {
                const s = this.snapshot;
                if (!s.checked || document.querySelectorAll('[data-chrono-start],[data-chrono-stop]').length === 0) return;
                const lockOthers = s.running && !s.multiAllowed;
                document.querySelectorAll('[data-chrono-start]').forEach((form) => {
                    if (!this.sameEmployee(form)) return; // other employees: server rules apply
                    const btn = form.querySelector('button[type="submit"],button:not([type])');
                    if (!btn) return;
                    if (lockOthers) this.lockButton(btn);
                    else this.unlockButton(btn);
                    form.hidden = false; // start controls always stay visible; only disabled
                });
                document.querySelectorAll('[data-chrono-stop]').forEach((form) => {
                    if (!this.sameEmployee(form)) return;
                    const stale = !s.running || !this.sameTask(form);
                    if (stale && !form.dataset.chronoHidden) { form.dataset.chronoHidden = '1'; form.hidden = true; }
                    else if (!stale && form.dataset.chronoHidden) { delete form.dataset.chronoHidden; form.hidden = false; }
                });
            }
        };
        window.ChronoSync = sync;
        document.addEventListener('alpine:init', () => sync.ensureStore(), { once: true });
        document.addEventListener('DOMContentLoaded', () => sync.syncRowControls());
        /* Multi-tab + return-to-tab: ask the navbar (if present) to revalidate. */
        window.addEventListener('storage', (e) => {
            if (e.key === PING_KEY) { try { window.dispatchEvent(new CustomEvent('chrono-revalidate', { detail: { force: true } })); } catch {} }
        });
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) { try { window.dispatchEvent(new CustomEvent('chrono-revalidate')); } catch {} }
        });
        window.addEventListener('focus', () => {
            try { window.dispatchEvent(new CustomEvent('chrono-revalidate')); } catch {}
        });
    })();

    /* ════════════════════════════════════════════════════════════════════
       Global search
    ════════════════════════════════════════════════════════════════════ */
    function searchDropdown() {
        return {
            query: '', results: [], loading: false, showResults: false, selectedIndex: -1,
            init() { this.query = ''; this.results = []; this.loading = false; this.showResults = false; this.selectedIndex = -1; },
            async performSearch() {
                if (this.query.length < 2) { this.results = []; this.showResults = false; return; }
                this.loading = true; this.selectedIndex = -1;
                try {
                    const tok = document.querySelector('meta[name="csrf-token"]')?.content;
                    const r   = await fetch('{{ route("search") }}?q=' + encodeURIComponent(this.query), {
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', ...(tok ? { 'X-CSRF-TOKEN': tok } : {}) }
                    });
                    if (!r.ok) throw new Error();
                    const d = await r.json();
                    this.results = d.results || [];
                    this.showResults = this.results.length > 0 || this.query.length >= 2;
                } catch { this.results = []; }
                finally { this.loading = false; }
            },
            navigateResults(dir) {
                if (!this.results.length) return;
                this.selectedIndex += dir;
                if (this.selectedIndex < 0) this.selectedIndex = this.results.length - 1;
                else if (this.selectedIndex >= this.results.length) this.selectedIndex = 0;
            },
            selectResult() {
                const i = this.selectedIndex >= 0 ? this.selectedIndex : 0;
                if (this.results[i]) { window.location.href = this.results[i].url; this.showResults = false; }
            }
        };
    }

    /* ════════════════════════════════════════════════════════════════════
       Alert dropdown
    ════════════════════════════════════════════════════════════════════ */
    function alertDropdown(cfg = {}) {
        return {
            open: false, count: Number(cfg.initialCount ?? 0), hasAlerts: (cfg.initialCount ?? 0) > 0,
            init() { this.open = false; this.updateAlertState(); },
            async _req(url, method) {
                const tok = document.querySelector('meta[name="csrf-token"]')?.content;
                if (!tok) throw new Error('No CSRF');
                const r = await fetch(url, { method, headers: { 'X-CSRF-TOKEN': tok, 'Accept': 'application/json', 'Content-Type': 'application/json' }, body: JSON.stringify({}) });
                if (!r.ok) throw new Error('Failed');
            },
            async markAsRead(id, url)  { try { await this._req(url,'POST');   this._remove(id); } catch {} },
            async deleteAlert(id, url) { try { await this._req(url,'DELETE'); this._remove(id); } catch {} },
            _remove(id) {
                document.getElementById('alert-card-' + id)?.remove();
                if (this.count > 0) this.count--;
                this.updateAlertState();
            },
            updateAlertState() { this.hasAlerts = (this.$refs?.alertList?.querySelectorAll('[data-alert-card]') ?? []).length > 0; },
            get showEmptyState() { return this.count <= 0 || !this.hasAlerts; }
        };
    }

    /* ════════════════════════════════════════════════════════════════════
       Navbar Chrono
       Reuses existing routes:
         GET  …/pointage/get-timer-state       → check running timer
         GET  …/filter/projects               → all projects
         GET  …/filter/projects/{id}/tasks    → tasks for project
         GET  …/filter/tasks/{id}             → employee_task_id + employee_id
         POST …/tasks/{etId}/start/{empId}    → start
         POST …/tasks/{etId}/stop/{empId}     → stop
    ════════════════════════════════════════════════════════════════════ */
    function navbarChrono() {
        return {
            /* state — stateChecked gates all visuals: no Start flash while verifying */
            isRunning: false, openPanel: false, stateChecked: false,
            starting: false, chronoError: '',
            lastStateCheck: 0,
            /* timer */
            elapsed: '00:00:00', taskName: '',
            timerInterval: null, timerStartMs: 0, timerOffsetSec: 0,
            activeEmployeeTaskId: null, activeEmployeeId: null, activeTaskId: null,
            multiAllowed: @json($chronoMultiAllowed ?? false),
            /* SuperAdmin running-timers list (primary = earliest started) */
            runningTimers: [], timersOpen: false,
            /* timezone gate: users without a timezone pick one before starting */
            userTimezone: @json($chronoUserTimezone ?? ''),
            tzViewerEmployeeId: @json($chronoViewerEmployeeId),
            tzUpdateUrl: @json($chronoTzUpdateUrl),
            tzOpen: false, tzValue: '', tzSaving: false, tzError: '',
            /* project picker */
            allProjects: @json($chronoInitialProjects),
            filteredProjects: @json($chronoInitialProjects),
            projectSearch: '',
            selectedProject: null, projectsLoading: false,
            projectSelectOpen: false,
            /* task picker */
            allTasks: [], filteredTasks: [],
            selectedTask: null, tasksLoading: false,
            taskSelectOpen: false,
            async init() {
                await this.checkRunningTimer();
                /* Safety net: converge even if every event mechanism fails (no refresh needed) */
                try { setInterval(() => { this.checkRunningTimer(); }, 30000); } catch {}
            },

            /* ── helpers ─────────────────────────────────────────────── */
            norm(s) {
                return (s ?? '').toString().toLowerCase()
                    .normalize('NFD').replace(/[\u0300-\u036f]/g, '').trim();
            },

            /* ── check the single source of truth (server) for a running timer ── */
            async checkRunningTimer(force = false) {
                /* Single-flight: never overlap a pending check (event + direct
                   triggers can fire back-to-back for the same action). */
                if (this._checking) return;
                if (!force && Date.now() - this.lastStateCheck < 20000) return;
                this._checking = true;
                this.lastStateCheck = Date.now();
                @if($chronoTimerStateUrl)
                try {
                    const url = '{{ $chronoTimerStateUrl }}';
                    const r = await fetch(url,
                        { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                    if (!r.ok) return;
                    const d = await r.json();
                    this.applyTimers(
                        (d.allTimers && d.allTimers.length)
                            ? d.allTimers
                            : (d.tasksTimer && !d.tasksTimer.end_date ? [d.tasksTimer] : [])
                    );
                    this.publishState();
                } catch (e) { console.error('Chrono init:', e); }
                finally { this.stateChecked = true; this.publishState(); this._checking = false; }
                @else
                this.stateChecked = true;
                this._checking = false;
                @endif
            },

            /* ── publish to the global store so every component reflects this state ── */
            publishState() {
                if (window.ChronoSync) window.ChronoSync.publish({
                    checked: this.stateChecked,
                    running: this.isRunning,
                    taskEmployeeId: this.activeEmployeeTaskId,
                    taskId: this.activeTaskId || null,
                    employeeId: this.activeEmployeeId,
                    taskName: this.taskName,
                    elapsed: this.elapsed,
                    multiAllowed: this.multiAllowed,
                    runningCount: this.runningTimers.length,
                }, 'navbar');
            },

            resumeFromExistingTimer(t) {
                this.applyTimers([t]);
            },

            /* Elapsed math shared by the pill and every list row (single ticker below) */
            timerBase(t) {
                const startMs  = new Date(String(t.start_date).replace(' ', 'T')).getTime();
                const resumeMs = t.resume_date
                    ? new Date(String(t.resume_date).replace(' ', 'T')).getTime() : null;
                const base = t.duration ? parseFloat(t.duration) * 60 : 0;
                return {
                    startMs: Number.isFinite(startMs) ? startMs : Date.now(),
                    offsetSec: t.paused
                        ? Math.round(base)
                        : Math.round(resumeMs ? base + (Date.now() - resumeMs) / 1000
                                              : (Date.now() - startMs) / 1000),
                };
            },

            /* Rebuild the whole list from server truth; primary = earliest started.
               Normal users always receive 0–1 timers, so behavior is unchanged. */
            applyTimers(list) {
                const now = Date.now();
                this.runningTimers = (Array.isArray(list) ? list : [])
                    .filter(t => t && !t.end_date && t.task_employee_id != null)
                    .map(t => {
                        const b = this.timerBase(t);
                        return { etId: t.task_employee_id, empId: t.employee_id,
                                 taskId: t.task_id ?? null, name: t.task_name || '',
                                 paused: t.paused == 1, startMs: b.startMs,
                                 offsetSec: b.offsetSec, appliedAt: now,
                                 elapsed: this.fmt(b.offsetSec) };
                    })
                    .sort((a, b) => a.startMs - b.startMs);
                if (this.runningTimers.length) {
                    const p = this.runningTimers[0];
                    this.isRunning = true;
                    this.activeEmployeeTaskId = p.etId;
                    this.activeEmployeeId     = p.empId;
                    this.activeTaskId         = p.taskId;
                    this.taskName = p.name;
                    this.timerOffsetSec = p.offsetSec;
                    this.timerStartMs = now;
                    this.elapsed = p.elapsed;
                    this.startTicking();
                } else {
                    this.isRunning = false;
                    if (this.timerInterval) { clearInterval(this.timerInterval); this.timerInterval = null; }
                    this.elapsed = '00:00:00'; this.taskName = '';
                    this.activeEmployeeTaskId = this.activeEmployeeId = this.activeTaskId = null;
                    this.timersOpen = false;
                }
            },

            startTicking() {
                if (this.timerInterval) clearInterval(this.timerInterval);
                this.elapsed = this.fmt(this.timerOffsetSec);
                if (window.ChronoSync) window.ChronoSync.patch({ elapsed: this.elapsed });
                this.timerInterval = setInterval(() => {
                    /* The ONE ticker: pill first, then every list row from its own base */
                    const now = Date.now();
                    this.elapsed = this.fmt(
                        this.timerOffsetSec + Math.round((now - this.timerStartMs) / 1000)
                    );
                    this.runningTimers.forEach(rt => {
                        rt.elapsed = rt.paused
                            ? this.fmt(rt.offsetSec)
                            : this.fmt(rt.offsetSec + Math.round((now - rt.appliedAt) / 1000));
                    });
                    if (window.ChronoSync) window.ChronoSync.patch({ elapsed: this.elapsed });
                }, 1000);
            },

            fmt(total) {
                total = Math.max(0, Math.round(total));
                const h = Math.floor(total / 3600);
                const m = Math.floor((total % 3600) / 60);
                const s = total % 60;
                return String(h).padStart(2,'0') + ':' + String(m).padStart(2,'0') + ':' + String(s).padStart(2,'0');
            },

            /* ── panel ─────────────────────────────────────────────────── */
            togglePanel() {
                this.openPanel = !this.openPanel;
                if (this.openPanel) {
                    this.projectSelectOpen = false;
                    this.taskSelectOpen = false;
                    this.loadProjects();
                }
            },

            /* ── projects ──────────────────────────────────────────────── */
            async toggleProjectSelect() {
                this.projectSelectOpen = !this.projectSelectOpen;
                this.taskSelectOpen = false;
                if (this.projectSelectOpen) {
                    this.projectSearch = '';
                    this.filterProjects();
                    this.loadProjects();
                    this.$nextTick(() => this.$refs.projectInput?.focus());
                }
            },

            async loadProjects() {
                @if($chronoAllProjectsUrl)
                const hadProjects = this.allProjects.length > 0;
                if (!hadProjects) this.projectsLoading = true;
                try {
                    const r = await fetch('{{ $chronoAllProjectsUrl }}', {
                        credentials: 'same-origin',
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    if (!r.ok) return;
                    const d = await r.json();
                    let raw = [];
                    if (Array.isArray(d)) raw = d;
                    else if (Array.isArray(d.projects)) raw = d.projects;
                    else if (Array.isArray(d.data)) raw = d.data;
                    const list = raw.map(p => ({
                        id: p.id,
                        name: (p.name ?? p.title ?? '').toString().trim() || ('#' + p.id)
                    })).filter(p => p.id != null);
                    if (list.length) {
                        this.allProjects = list;
                        this.filterProjects();
                    }
                } catch (e) {
                    console.error('Load projects:', e);
                }
                finally { this.projectsLoading = false; }
                @endif
            },

            filterProjects() {
                const q = this.norm(this.projectSearch);
                this.filteredProjects = q
                    ? this.allProjects.filter(p => this.norm(p.name).includes(q))
                    : this.allProjects.slice();
            },

            async selectProject(p) {
                this.selectedProject = p;
                this.openPanel = true; // stay open: task selection + Start still pending
                this.chronoError = '';
                this.projectSelectOpen = false;
                this.projectSearch = '';
                this.filterProjects();
                this.selectedTask  = null;
                this.taskSelectOpen = false;
                this.allTasks      = [];
                this.filteredTasks = [];
                await this.loadTasksForProject(p.id);
                this.taskSelectOpen = true; // continue the flow: show the task list
            },

            /* ── tasks ─────────────────────────────────────────────────── */
            async loadTasksForProject(pid) {
                @if($chronoProjectTasksBase)
                this.tasksLoading = true;
                try {
                    const r = await fetch('{{ $chronoProjectTasksBase }}/' + pid + '/tasks', {
                        credentials: 'same-origin',
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    if (!r.ok) { this.chronoError = @json(__('messages.chrono_no_tasks')); return; }
                    const d   = await r.json();
                    const raw = d.tasks ?? d.data ?? d ?? [];
                    this.allTasks = (Array.isArray(raw) ? raw : [])
                        .map(t => ({ ...t, title: t.title ?? t.name ?? t.task_name ?? '' }));
                    this.filteredTasks = [...this.allTasks];
                } catch (e) { console.error('Load tasks:', e); }
                finally { this.tasksLoading = false; }
                @endif
            },

            toggleTaskSelect() {
                if (!this.selectedProject) return;
                this.taskSelectOpen = !this.taskSelectOpen;
                this.projectSelectOpen = false;
            },

            selectTask(t) {
                this.selectedTask = t;
                this.taskSelectOpen = false;
                this.openPanel = true; // stay open: user still has to click Start
            },

            /* ── start ─────────────────────────────────────────────────── */
            async startChrono() {
                if (!this.selectedTask || !this.selectedProject || this.starting) return;
                /* No timezone yet → collect it first (existing save endpoint), then continue */
                if (!this.userTimezone && this.tzViewerEmployeeId && this.tzUpdateUrl) {
                    this.tzValue = '';
                    this.tzError = '';
                    this.tzOpen = true;
                    return;
                }
                this.starting = true; this.chronoError = '';
                try {
                    const tok = document.querySelector('meta[name="csrf-token"]')?.content;
                    @if($chronoTaskInfoBase)
                    /* get employees_tasks.id + employee.id for current user + task */
                    const ir = await fetch('{{ $chronoTaskInfoBase }}/' + this.selectedTask.id, {
                        credentials: 'same-origin',
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    if (!ir.ok) { this.chronoError = @json(__('messages.chrono_no_tasks')); return; }
                    const info = await ir.json();
                    const etId  = info.employee_task_id || info.id;
                    const empId = info.employee_id;
                    if (!etId || !empId) {
                        this.chronoError = @json(__('messages.chrono_select_project_first'));
                        return;
                    }
                    const sr = await fetch('{{ $chronoTaskStartBase }}/' + etId + '/start/' + empId, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': tok, 'Accept': 'application/json', 'Content-Type': 'application/json' },
                        body: JSON.stringify({})
                    });
                    if (!sr.ok) {
                        const e = await sr.json().catch(() => ({}));
                        this.chronoError = e.message || e.alertmessage || @json(__('messages.chrono_no_tasks'));
                        return;
                    }
                    this.openPanel          = false;
                    /* Rebuild from server truth so pill + list reflect all running timers */
                    await this.checkRunningTimer(true);
                    @endif
                } catch (e) {
                    console.error('Start chrono:', e);
                    this.chronoError = @json(__('messages.chrono_no_tasks'));
                } finally { this.starting = false; }
            },

            /* ── timezone gate: save via the existing employee update endpoint,
               then continue the pending chrono start ── */
            async saveTimezoneAndStart() {
                if (!this.tzValue) {
                    this.tzError = @json(__('messages.Select your timezone'));
                    return;
                }
                if (this.tzSaving) return;
                this.tzSaving = true; this.tzError = '';
                try {
                    const tok = document.querySelector('meta[name="csrf-token"]')?.content;
                    const r = await fetch(this.tzUpdateUrl, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': tok, 'Accept': 'application/json', 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id: this.tzViewerEmployeeId, column: 'timezone', content: this.tzValue })
                    });
                    if (!r.ok) throw new Error('Request failed: ' + r.status);
                    this.userTimezone = this.tzValue;
                    this.tzOpen = false;
                    this.startChrono();
                } catch (e) {
                    console.error('Save timezone:', e);
                    this.tzError = @json(__('messages.chrono_tz_save_failed'));
                } finally { this.tzSaving = false; }
            },

            /* ── stop (primary): open the shared stop-comment popup ──────────
               Nothing is finalized here: the existing stop endpoint runs only
               after the popup is confirmed (with the stop comment). */
            askStopChrono() {
                if (!this.activeEmployeeTaskId || !this.activeEmployeeId) return;
                @if($chronoTaskStopBase)
                if (window.ChronoStopModal) {
                    window.ChronoStopModal.open(
                        '{{ $chronoTaskStopBase }}/' + this.activeEmployeeTaskId + '/stop/' + this.activeEmployeeId
                    );
                }
                @endif
            },

            /* ── stop one specific row (SuperAdmin list): popup for that task only ── */
            askStopRowTimer(rt) {
                if (!rt) return;
                @if($chronoTaskStopBase)
                if (window.ChronoStopModal) {
                    window.ChronoStopModal.open(
                        '{{ $chronoTaskStopBase }}/' + rt.etId + '/stop/' + rt.empId
                    );
                }
                @endif
            }
        };
    }
</script>
@endonce

@once
<script>
    /* ── Desktop sidebar collapse ──────────────────────────────────────── */
    if (typeof toggleDesktopSidebar === 'undefined') {
        function toggleDesktopSidebar(force) {
            const c = typeof force === 'boolean' ? force : !document.body.classList.contains('dt-sidebar-collapsed');
            document.body.classList.toggle('dt-sidebar-collapsed', c);
            try { localStorage.setItem('dalytrac-sidebar-collapsed', c ? '1' : '0'); } catch {}
        }
        try {
            if (localStorage.getItem('dalytrac-sidebar-collapsed') === '1' && window.innerWidth >= 1024) {
                document.body.classList.add('dt-sidebar-collapsed');
            }
        } catch {}
    }

    /* ── Mobile sidebar ────────────────────────────────────────────────── */
    function toggleAppSidebar(forceOpen = null) {
        const sb = document.getElementById('appSidebar');
        const bd = document.getElementById('appSidebarBackdrop');
        if (!sb || !bd) return;
        const was    = !sb.classList.contains('hidden') && !sb.classList.contains('-translate-x-full');
        const open   = forceOpen === null ? !was : Boolean(forceOpen);
        if (open) {
            sb.classList.remove('hidden'); sb.classList.add('flex');
            sb.classList.remove('-translate-x-full'); sb.classList.add('translate-x-0');
            bd.classList.remove('hidden');
        } else {
            sb.classList.add('-translate-x-full'); sb.classList.remove('translate-x-0');
            bd.classList.add('hidden');
            setTimeout(() => { sb.classList.add('hidden'); sb.classList.remove('flex'); }, 200);
        }
    }
    document.addEventListener('keydown', e => { if (e.key === 'Escape') toggleAppSidebar(false); });
    document.addEventListener('DOMContentLoaded', () => toggleAppSidebar(false));
</script>
@endonce
