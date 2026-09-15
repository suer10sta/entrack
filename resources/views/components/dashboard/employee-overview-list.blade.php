@php
    $avatarColors = ['bg-emerald-500', 'bg-gray-400', 'bg-amber-500', 'bg-red-500', 'bg-purple-500', 'bg-sky-500', 'bg-rose-800', 'bg-slate-700', 'bg-yellow-400', 'bg-indigo-500'];
    $hasWorkedToday = $employeesToday->isNotEmpty();
    $todayStart = \Carbon\Carbon::today();
    $todayEnd = \Carbon\Carbon::today()->endOfDay();
    $today = \Carbon\Carbon::today();
@endphp

<div class="dashboard-employee-overview-list divide-y divide-gray-200">
    @foreach($employeesToday as $employeeIndex => $employee)
        @php
            // Get all timers for this employee today
            $employeeTimers = $timersToday->where('employee_id', $employee->id);
            
            // Group timers by project
            $employeeProjects = collect();
            
            foreach ($employeeTimers as $timer) {
                $projectId = null;
                
                // Try to get project_id directly from timer
                if ($timer->project_id) {
                    $projectId = $timer->project_id;
                } else {
                    // Fallback: get project_id from task
                    $task = \App\Models\Tasks::find($timer->task_id);
                    if ($task && $task->project_id) {
                        $projectId = $task->project_id;
                    }
                }
                
                if ($projectId) {
                    $project = \App\Models\Project::find($projectId);
                    if ($project && !$employeeProjects->contains('id', $project->id)) {
                        // Calculate hours for this project today for this employee
                        $projectTimers = $employeeTimers->filter(function($t) use ($projectId, $project) {
                            if ($t->project_id == $projectId) return true;
                            $task = \App\Models\Tasks::find($t->task_id);
                            return $task && $task->project_id == $projectId;
                        });
                        
                        // Sum completed timers that started today (those with duration and start_date today)
                        $todayDuration = $projectTimers->filter(function($t) use ($today) {
                            return $t->duration !== null && \Carbon\Carbon::parse($t->start_date)->isSameDay($today);
                        })->sum('duration');
                        
                        // Also include active timers (those without end_date) - only count time from today
                        $activeTimers = $projectTimers->whereNull('end_date');
                        $now = \Carbon\Carbon::now();
                        foreach ($activeTimers as $activeTimer) {
                            $startTime = \Carbon\Carbon::parse($activeTimer->start_date);
                            $isPaused = $activeTimer->paused == 1;
                            
                            if (!$isPaused) {
                                // For running timers, only count time from today's start
                                $effectiveStartTime = $startTime->lt($todayStart) ? $todayStart : $startTime;
                                $elapsedSeconds = $effectiveStartTime->diffInSeconds($now);
                                $todayDuration += floor($elapsedSeconds / 60);
                            } else {
                                // For paused timers, only count if they started today
                                // (stored duration for timers started before today would include pre-today time)
                                if ($startTime->isSameDay($today)) {
                                    $todayDuration += ($activeTimer->duration ?? 0);
                                }
                            }
                        }
                        
                        $project->employee_today_duration = $todayDuration;
                        
                        // Get tasks for this project that the employee worked on today
                        $projectTasks = collect();
                        $taskIds = $projectTimers->pluck('task_id')->filter()->unique();
                        foreach ($taskIds as $taskId) {
                            $task = \App\Models\Tasks::find($taskId);
                            if ($task) {
                                // Calculate duration for this task today
                                $taskTimers = $projectTimers->where('task_id', $taskId);
                                $taskTodayDuration = $taskTimers->filter(function($t) use ($today) {
                                    return $t->duration !== null && \Carbon\Carbon::parse($t->start_date)->isSameDay($today);
                                })->sum('duration');
                                
                                // Include active timers for this task
                                $taskActiveTimers = $taskTimers->whereNull('end_date');
                                foreach ($taskActiveTimers as $taskActiveTimer) {
                                    $taskStartTime = \Carbon\Carbon::parse($taskActiveTimer->start_date);
                                    $taskIsPaused = $taskActiveTimer->paused == 1;
                                    
                                    if (!$taskIsPaused) {
                                        $taskEffectiveStartTime = $taskStartTime->lt($todayStart) ? $todayStart : $taskStartTime;
                                        $taskElapsedSeconds = $taskEffectiveStartTime->diffInSeconds($now);
                                        $taskTodayDuration += floor($taskElapsedSeconds / 60);
                                    } else {
                                        if ($taskStartTime->isSameDay($today)) {
                                            $taskTodayDuration += ($taskActiveTimer->duration ?? 0);
                                        }
                                    }
                                }
                                
                                $task->employee_today_duration = $taskTodayDuration;
                                $projectTasks->push($task);
                            }
                        }
                        
                        $project->employee_tasks = $projectTasks;
                        $employeeProjects->push($project);
                    }
                }
            }
        @endphp

        @if($employeeProjects->isNotEmpty())
            <div class="dashboard-emp-row px-6 py-4 hover:bg-gray-50">
                @foreach($employeeProjects as $projectIndex => $project)
                    <div class="dashboard-emp-project grid grid-cols-12 gap-4 items-start">
                        {{-- EMPLOYEE (col-span-2) - Only show on first project --}}
                        <div class="dashboard-emp-employee col-span-2 flex items-center gap-3 pr-4" data-label="{{ __('messages.EMPLOYEES') }}">
                            @if($projectIndex === 0)
                                <div class="flex items-center justify-between gap-3 w-full">
                                    <div class="dashboard-emp-employee-inner flex items-center gap-3 min-w-0">
                                    <div
                                        class="flex-shrink-0 h-10 w-10 flex items-center justify-center rounded-full text-white font-semibold {{ $avatarColors[$employeeIndex % count($avatarColors)] }}">
                                        {{ strtoupper(substr($employee->name, 0, 2)) }}
                                    </div>
                                    <div class="min-w-0 flex flex-col gap-0.5">
                                        <p class="text-sm font-medium text-gray-900 truncate">{{ $employee->name }}</p>
                                        <p class="text-xs text-gray-500 truncate">{{ $employee->email }}</p>
                                        @php
                                            $employeeRoles = is_array($employee->roles ?? null) ? $employee->roles : (array) ($employee->roles ?? []);
                                        @endphp
                                        @if(!empty($employeeRoles))
                                            <div class="flex flex-wrap gap-1 pt-1">
                                                @foreach($employeeRoles as $role)
                                                    <span class="inline-block px-2 py-0.5 text-[10.5px] font-medium rounded {{ $role == 'SuperAdmin' ? 'bg-fuchsia-100 text-fuchsia-700' : 'bg-purple-100 text-purple-700'}}">
                                                        {{ $role }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                    </div>
                                    <button
                                        type="button"
                                        class="shrink-0 text-slate-400 hover:text-slate-600 lg:hidden"
                                        data-emp-overview-toggle
                                        aria-label="Toggle employee overview details"
                                    >
                                        <svg class="emp-overview-chevron w-5 h-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                            <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </button>
                                </div>
                            @endif
                        </div>

                        {{-- PROJECTS & CLIENT onwards - with border --}}
                        <div class="dashboard-emp-details col-span-10 grid grid-cols-10 gap-4 items-start {{ !$loop->last ? 'pb-3 border-b border-gray-200 mb-3' : '' }}">
                            {{-- PROJECTS & CLIENT (col-span-3) --}}
                            <div class="col-span-3" data-label="{{ __('messages.PROJECTS & CLIENT') }}" data-span="2">
                                <a href="{{ route('admin.projects.show', $project->id) }}"
                                    class="text-sm font-medium text-emerald-600 hover:underline">
                                    {{ $project->name }}
                                </a>
                                <p class="text-xs text-gray-500 mt-0.5">
                                    Client: 
                                    @if($project->client)
                                        <a href="{{ route('admin.clients.show', $project->client->id) }}" 
                                           class="text-blue-600 hover:underline">
                                            {{ $project->client->name }}
                                        </a>
                                    @else
                                        <span class="text-gray-500">N/A</span>
                                    @endif
                                </p>
                            </div>

                            {{-- TIME TRACKING (col-span-2) --}}
                            <div class="col-span-2" data-label="{{ __('messages.TIME TRACKING') }}">
                            @php
                                // Today's duration
                                $todayHours = floor($project->employee_today_duration / 60);
                                $todayMinutes = $project->employee_today_duration % 60;
                                
                                // Total duration for the project (all time, not just today)
                                $projecttasksId = \App\Models\Tasks::where('project_id', $project->id)->pluck('id');
                                $totalDurationMinutes = \App\Models\tasks_timer::where(function ($q) use ($projecttasksId, $project) {
                                    $q->whereIn('task_id', $projecttasksId)
                                        ->orWhere('project_id', $project->id);
                                })->sum('duration');
                                $totalHours = floor($totalDurationMinutes / 60);
                                $totalMinutes = $totalDurationMinutes % 60;
                            @endphp
                            <div class="text-sm">
                                <div class="font-medium text-gray-800">
                                    {{ $todayHours }}h:{{ sprintf('%02d', $todayMinutes) }}m
                                </div>
                                <div class="text-xs text-gray-500 mt-0.5">
                                    Total: {{ $totalHours }}h:{{ sprintf('%02d', $totalMinutes) }}m
                                </div>
                            </div>
                        </div>

                            {{-- TASKS (col-span-3) --}}
                            <div class="col-span-3" data-label="{{ __('messages.TASKS') }}" data-span="2">
                                @if($project->employee_tasks && $project->employee_tasks->isNotEmpty())
                                    @foreach($project->employee_tasks as $task)
                                        <div class="mb-2 last:mb-0">
                                            <a href="{{ route('admin.tasks.show', $task->id) }}"
                                                class="text-xs font-medium text-indigo-600 hover:underline">
                                                {{ $task->title }}
                                            </a>
                                        </div>
                                    @endforeach
                                @else
                                    <p class="text-xs text-gray-400">No tasks</p>
                                @endif
                            </div>

                            {{-- TASK DURATION (col-span-1) --}}
                            <div class="col-span-1" data-label="{{ __('messages.TASK DURATION') }}">
                                @if($project->employee_tasks && $project->employee_tasks->isNotEmpty())
                                    @foreach($project->employee_tasks as $task)
                                        @php
                                            // Today's duration for this task
                                            $taskTodayHours = floor($task->employee_today_duration / 60);
                                            $taskTodayMinutes = $task->employee_today_duration % 60;
                                            
                                            // Total duration for this task (all time, not just today)
                                            $taskTotalDurationMinutes = \App\Models\tasks_timer::where('task_id', $task->id)->sum('duration');
                                            $taskTotalHours = floor($taskTotalDurationMinutes / 60);
                                            $taskTotalMinutes = $taskTotalDurationMinutes % 60;
                                        @endphp
                                        <div class="mb-2 last:mb-0 text-xs">
                                            <div class="font-medium text-gray-800">
                                                {{ $taskTodayHours }}h:{{ sprintf('%02d', $taskTodayMinutes) }}m
                                            </div>
                                            <div class="text-gray-500 mt-0.5">
                                                Total: {{ $taskTotalHours }}h:{{ sprintf('%02d', $taskTotalMinutes) }}m
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <p class="text-xs text-gray-400">-</p>
                                @endif
                            </div>

                            {{-- ACTIONS (col-span-1) - Only show on first project --}}
                            <div class="dashboard-emp-actions col-span-1 flex justify-end items-start pt-1" data-label="{{ __('messages.Employee Actions') }}" data-span="2">
                                @if($projectIndex === 0)
                                    <a href="{{ route('admin.employees.show', $employee->id) }}"
                                        class="p-1 rounded-md bg-purple-600 text-white hover:bg-purple-700"
                                        title="View Employee">
                                        <svg class="h-5 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-.07.207-.07.431 0 .639C20.268 16.057 16.477 19 12 19c-4.478 0-8.268-2.943-9.542-7a1.186 1.186 0 010-.639z" />
                                        </svg>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    @endforeach
</div>

{{-- No employees worked today message --}}
@if(!$hasWorkedToday)
    <div class="px-6 py-8 text-center">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-4">
            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17 21v-2a4 4 0 00-8 0v2m8 0H5a2 2 0 01-2-2V7a2 2 0 012-2h5l2 2h5a2 2 0 012 2v12a2 2 0 01-2 2z" />
            </svg>
        </div>
        <p class="text-sm font-medium text-gray-900 mb-1">No Employees Worked Today</p>
        <p class="text-sm text-gray-500">No employees have logged work today. Start tracking time to see it here.</p>
    </div>
@endif

