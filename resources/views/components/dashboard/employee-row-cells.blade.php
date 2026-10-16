                                {{-- STATUS --}}
                                <td data-label="{{ __('messages.Statut') }}"
                                    class="px-3 py-2.5 text-center align-middle cell-current-status">
                                    @if(!$employeeUser || $employeeUser->is_loggedIn == 0)
                                        <span class="dt-badge dt-badge-neutral"><span class="dt-dot"></span>{{ __('messages.Off Work') }}</span>
                                    @else
                                        @php
                                            $openTimer = \App\Models\tasks_timer::where('employee_id', $employee->id)->whereNull('end_date')->first();
                                        @endphp
                                        @if($openTimer && $openTimer->paused == 1)
                                            <span class="dt-badge dt-badge-warning"><span class="dt-dot"></span>{{ __('messages.On Pause') }}</span>
                                        @elseif($openTimer)
                                            <span class="dt-badge dt-badge-success"><span class="dt-dot"></span>{{ __('messages.On Work') }}</span>
                                        @else
                                            <span class="dt-badge dt-badge-neutral"><span class="dt-dot"></span>{{ __('messages.Off Work') }}</span>
                                        @endif
                                    @endif
                                </td>

                                {{-- PROJECT --}}
                                <td data-label="{{ __('messages.Current Project') }}"
                                    class="px-3 py-2.5 align-middle text-left cell-current-project">
                                    @if($rowActiveTasks->isNotEmpty())
                                        @php
                                            $projectGroups = $rowActiveTasks
                                                ->map(function($t){ $t->_pid = \App\Models\Tasks::where('id',$t->task_id)->value('project_id') ?? 0; return $t; })
                                                ->groupBy('_pid')
                                                ->unique(fn($g)=>$g->first()->_pid);
                                        @endphp
                                        <div class="flex flex-col gap-1.5">
                                            @foreach($projectGroups as $pid => $pg)
                                                @php
                                                    $proj = $pid ? \App\Models\Project::find($pid) : null;
                                                    $projActive = $pg->contains(fn($t)=>$t->end_date===null && (int)($t->paused??0)===0);
                                                @endphp
                                                <div class="flex items-center gap-2 min-w-0">
                                                    <span class="flex-shrink-0 inline-flex items-center justify-center w-5 h-5 rounded-md
                                                        {{ $projActive ? 'bg-emerald-100 text-emerald-600' : 'bg-slate-100 text-slate-400' }}">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7z" />
                                                        </svg>
                                                    </span>
                                                    @if($proj)
                                                        <a href="{{ route('admin.projects.show', $proj->id) }}"
                                                            class="flex-1 min-w-0 text-[12px] font-semibold leading-tight truncate
                                                                {{ $projActive ? 'text-emerald-600 hover:underline' : 'text-slate-500 hover:text-slate-700' }}">
                                                            {{ $proj->name }}
                                                        </a>
                                                    @else
                                                        <span class="text-[12px] text-slate-400">—</span>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="flex items-center gap-2">
                                            <span class="flex-shrink-0 inline-flex items-center justify-center w-5 h-5 rounded-md bg-slate-100 text-slate-300">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7z" />
                                                </svg>
                                            </span>
                                            <span class="text-[12px] text-slate-400">—</span>
                                        </div>
                                    @endif
                                </td>

                                {{-- TÂCHE EN COURS — unified: every task worked on today (running + completed), one separated card per task --}}
                                <td data-label="{{ __('messages.Tâche en cours') }}" data-span="2"
                                    class="px-3 py-2 align-middle cell-current-task cell-duration cell-chrono">
                                    @if($rowTasksToday->isNotEmpty())
                                        <div class="flex flex-col gap-1.5">
                                            @foreach($rowTasksToday as $tid => $tGroup)
                                                @php
                                                    // Prefer the still-open timer for live display + stop action
                                                    $tOpen     = $tGroup->first(fn($t) => $t->end_date === null);
                                                    $tRow      = $tOpen ?? $tGroup->first();
                                                    $tActive   = $tOpen !== null && (int)($tOpen->paused ?? 0) === 0;
                                                    $tPaused   = $tOpen !== null && (int)($tOpen->paused ?? 0) === 1;
                                                    $tDone     = $tOpen === null;
                                                    $tLinkable = !empty($tRow->task_id);
                                                    // Compute initial chrono value
                                                    $tExtraS   = ((int)($tRow->duration ?? 0)) * 60;
                                                    $tElapsed  = 0;
                                                    if (!$tPaused && !$tDone && !empty($tRow->start_date)) {
                                                        $tElapsed = max(0, \Carbon\Carbon::parse($tRow->start_date)->diffInSeconds($nowRow, false));
                                                    }
                                                    $tTotal    = $tExtraS + $tElapsed;
                                                    $tDisplay  = sprintf('%02dh:%02dm:%02ds', intdiv($tTotal,3600), intdiv($tTotal%3600,60), $tTotal%60);
                                                    $tStart    = !$tDone && !empty($tRow->start_date) ? date('c', strtotime($tRow->start_date)) : '';
                                                    // Total worked time today for finished tasks
                                                    $tDoneMin  = (int) $tGroup->sum('duration');
                                                    $tDoneTxt  = intdiv($tDoneMin, 60) . 'h ' . ($tDoneMin % 60) . 'm';
                                                    // Assignment id used to restart a finished task (latest timer first)
                                                    $tRestartSrc = $tGroup->sortByDesc('end_date')->first();
                                                    $tRestartId  = $tRestartSrc ? ($tRestartSrc->task_employee_id ?? $tRestartSrc->task_id) : null;
                                                @endphp
                                                <div class="flex items-center gap-2 rounded-lg border
                                                    {{ $tActive ? 'border-emerald-200 bg-emerald-50/60' : ($tPaused ? 'border-amber-200 bg-amber-50/50' : 'border-slate-200 bg-slate-50/70') }}
                                                    px-2.5 py-1.5">
                                                    {{-- Task icon --}}
                                                    <span class="flex-shrink-0 inline-flex items-center justify-center w-5 h-5 rounded-md
                                                        {{ $tActive ? 'bg-emerald-100 text-emerald-600' : ($tPaused ? 'bg-amber-100 text-amber-600' : 'bg-slate-200 text-slate-500') }}">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 002-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                                                        </svg>
                                                    </span>
                                                    {{-- Task name --}}
                                                    <span class="flex-1 min-w-0">
                                                        @if($tLinkable && (auth()->user()->hasRole('Admin') || auth()->user()->hasRole('SuperAdmin') || auth()->user()->hasRole('super_admin')))
                                                            <a href="{{ route('admin.tasks.show', $tRow->task_id) }}"
                                                                class="text-[12px] font-semibold leading-tight truncate block
                                                                    {{ $tActive ? 'text-[#0B2638] hover:text-brand-secondary hover:underline' : 'text-slate-500' }}">
                                                                {{ $tRow->task_name }}
                                                            </a>
                                                        @elseif($tLinkable && auth()->user()->hasRole('Responsable de projet'))
                                                            <a href="{{ route('Responsable_de_projet.tasks.show', $tRow->task_id) }}"
                                                                class="text-[12px] font-semibold leading-tight truncate block
                                                                    {{ $tActive ? 'text-[#0B2638] hover:text-brand-secondary hover:underline' : 'text-slate-500' }}">
                                                                {{ $tRow->task_name }}
                                                            </a>
                                                        @else
                                                            <span class="text-[12px] font-semibold text-slate-500 leading-tight block truncate">{{ $tRow->task_name }}</span>
                                                        @endif
                                                    </span>
                                                    @if($tDone)
                                                        {{-- Finished today: static total + badge + restart --}}
                                                        <span class="flex-shrink-0 text-[11.5px] font-bold tabular-nums text-slate-500">{{ $tDoneTxt }}</span>
                                                        <span class="flex-shrink-0 inline-flex px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-200 text-slate-600">{{ __('messages.Completed') }}</span>
                                                        @if($tRestartId)
                                                            <form class="history-start-form flex-shrink-0"
                                                                action="{{ route('admin.tasks.start', ['tasks' => $tRestartId, 'employee_id' => $employee->id]) }}"
                                                                method="POST" data-task-id="{{ $tRestartId }}"
                                                                data-chrono-start data-chrono-employee="{{ $employee->id }}"
                                                                data-chrono-emp-task="{{ $tRestartId }}" data-chrono-task="{{ $tRow->task_id ?? '' }}">
                                                                @csrf
                                                                <button type="submit"
                                                                    class="inline-flex items-center gap-1 rounded-md bg-emerald-500 hover:bg-emerald-600 text-white text-[10.5px] font-semibold px-2 py-1 transition-colors leading-none">
                                                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                                                    {{ __('messages.Start') }}
                                                                </button>
                                                            </form>
                                                        @endif
                                                    @else
                                                        {{-- Live timer --}}
                                                        <span class="live-duration flex-shrink-0 text-[11.5px] font-bold tabular-nums
                                                            {{ $tActive ? 'text-[#0B2638]' : 'text-slate-400' }}"
                                                            data-start="{{ $tStart }}"
                                                            data-paused="{{ $tPaused ? '1' : '0' }}"
                                                            data-extra-seconds="{{ $tExtraS }}">{{ $tDisplay }}</span>
                                                        {{-- Stop button --}}
                                                        @if($tRow->end_date === null)
                                                            <form class="stop-form-{{ $employee->id }} stop-form flex-shrink-0"
                                                                action="{{ route('admin.tasks.stop', ['tasks' => $tRow->task_employee_id, 'employee_id' => $employee->id]) }}"
                                                                method="POST"
                                                                data-confirm="{{ __('messages.Stop timer for this task?') }}"
                                                                data-confirm-kind="stop"
                                                                data-chrono-stop data-chrono-employee="{{ $employee->id }}"
                                                                data-chrono-emp-task="{{ $tRow->task_employee_id }}" data-chrono-task="{{ $tRow->task_id ?? '' }}">
                                                                @csrf
                                                                <button type="submit"
                                                                    class="stop-button inline-flex items-center gap-1 rounded-md bg-red-500 hover:bg-red-600 text-white text-[10.5px] font-semibold px-2 py-1 transition-colors leading-none">
                                                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24"><rect x="6" y="6" width="12" height="12" rx="2"/></svg>
                                                                    Arrêt
                                                                </button>
                                                            </form>
                                                        @endif
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="flex items-center gap-2">
                                            <span class="flex-shrink-0 inline-flex items-center justify-center w-5 h-5 rounded-md bg-slate-100 text-slate-300">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 002-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                                                </svg>
                                            </span>
                                            <span class="text-[12px] text-slate-400 italic">{{ __('messages.No Task') }}</span>
                                        </div>
                                    @endif
                                </td>
