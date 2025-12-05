@php
    $user = Auth::user();
    $isSoloPlan = optional(optional($user)->entreprise)->type_compt === 'solo';
@endphp

{{-- We need to load the correct Tailwind-aware CSS and JS for DataTables --}}
<style>
    @keyframes fadeInSlideUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    .animate-fadeInSlideUp {
        opacity: 0;
        animation: fadeInSlideUp 0.5s ease-out forwards;
    }

    /* Override default DataTables styles to match Tailwind */
    #employeesTable_wrapper .dt-search input {
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
        line-height: 1.25rem;
        margin-left: 0.5rem;
    }
    #employeesTable_wrapper .dt-search input:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 2px #bfdbfe;
        outline: none;
    }
    
    /* FIXED: Better DataTables pagination styling */
    #employeesTable_wrapper .dt-paging {
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }
    
    /* DataTables pagination – light theme to match dashboard */
#employeesTable_wrapper .dataTables_paginate {
    display: flex;
    justify-content: center;
    margin-top: 1rem;
    gap: 0.25rem;
}

#employeesTable_wrapper .dataTables_paginate .paginate_button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 2.25rem;
    height: 2.25rem;
    padding: 0 0.75rem;
    border-radius: 0.5rem;
    font-size: 0.75rem;
    font-weight: 500;
    color: #4b5563;               /* gray-600 */
    background-color: #ffffff;    /* white */
    border: 1px solid #e5e7eb;    /* gray-200 */
    cursor: pointer;
    transition: all 0.15s ease-in-out;
}

#employeesTable_wrapper .dataTables_paginate .paginate_button:hover {
    background-color: #eff6ff;    /* blue-50 */
    border-color: #bfdbfe;        /* blue-200 */
    color: #1d4ed8;               /* blue-700 */
}

#employeesTable_wrapper .dataTables_paginate .paginate_button.current {
    background-color: #1d4ed8;    /* blue-700 */
    border-color: #1d4ed8;
    color: #ffffff;
}

#employeesTable_wrapper .dataTables_paginate .paginate_button.disabled {
    opacity: 0.5;
    cursor: not-allowed;
    background-color: #f9fafb;    /* gray-50 */
}

    #employeesTable_wrapper .dt-paging a,
    #employeesTable_wrapper .dt-paging button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 2rem;
        height: 2rem;
        padding: 0.5rem;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        color: #374151;
        background-color: #ffffff;
        border: 1px solid #e5e7eb;
        transition: all 0.2s;
    }
    
    #employeesTable_wrapper .dt-paging a:hover,
    #employeesTable_wrapper .dt-paging button:hover {
        background-color: #f3f4f6;
        border-color: #d1d5db;
    }
    
    #employeesTable_wrapper .dt-paging a.current,
    #employeesTable_wrapper .dt-paging button.current {
        background-color: #1E9FD8;
        color: #ffffff;
        border-color: #1E9FD8;
        font-weight: 600;
    }
    
    #employeesTable_wrapper .dt-paging a.disabled,
    #employeesTable_wrapper .dt-paging button.disabled {
        opacity: 0.5;
        cursor: not-allowed;
        pointer-events: none;
    }
    
    #employeesTable_wrapper .dt-info {
        font-size: 0.875rem;
        color: #6b7280;
    }

    /* Laravel paginator (Jetstream/Tailwind) override */
    .relative.z-0.inline-flex.rtl\:flex-row-reverse.shadow-sm.rounded-md {
        box-shadow: none !important;
        gap: 0.35rem;
    }

    .relative.z-0.inline-flex.rtl\:flex-row-reverse.shadow-sm.rounded-md > * {
        margin-left: 0 !important;
        border-radius: 0.5rem !important;
        border: 1px solid #e5e7eb !important;
        background-color: #ffffff !important;
        color: #374151 !important;
        transition: all 0.15s ease;
    }

    .relative.z-0.inline-flex.rtl\:flex-row-reverse.shadow-sm.rounded-md > *:hover {
        background-color: #eff6ff !important;
        border-color: #bfdbfe !important;
        color: #1d4ed8 !important;
    }

    .relative.z-0.inline-flex.rtl\:flex-row-reverse.shadow-sm.rounded-md > span[aria-current="page"] > span {
        background-color: #1d4ed8 !important;
        border-color: #1d4ed8 !important;
        color: #ffffff !important;
    }
</style>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.tailwindcss.min.css">
<script src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>

<div class="flex-1 overflow-auto bg-gray-50">
    {{-- Main content --}}
    <div class="p-8">

    {{-- Stats Cards --}}
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-6">

    {{-- Clients --}}
    <a href="{{ route('admin.clients.index') }}" 
       class="block bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-xl hover:scale-105 transition-all duration-300 group animate-fadeInSlideUp" 
       style="animation-delay: 100ms;">
        <div class="flex items-center justify-between mb-4">
            <div class="flex-1">
                <div class="flex items-center space-x-2 mb-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center group-hover:bg-blue-100 transition-colors">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5V10H2v10h5m10 0V8a5 5 0 00-10 0v12" />
                        </svg>
                    </div>
                    <h3 class="text-gray-500 text-sm font-medium">{{ __('messages.Clients') }}</h3>
                </div>
                <p class="text-3xl font-bold text-gray-800">{{ $data['clients']->count() }}</p>
            </div>
        </div>
        <div class="relative">
            <div class="h-2 bg-blue-100 rounded-full overflow-hidden">
                <div class="w-2/3 h-2 bg-gradient-to-r from-blue-500 to-blue-600 rounded-full animate-pulse"></div>
            </div>
        </div>
    </a>

    {{-- Employees --}}
    <a href="{{ route('admin.employees.index') }}" 
       class="block bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-xl hover:scale-105 transition-all duration-300 group animate-fadeInSlideUp" 
       style="animation-delay: 200ms;">
        <div class="flex items-center justify-between mb-4">
            <div class="flex-1">
                <div class="flex items-center space-x-2 mb-3">
                    <div class="w-10 h-10 rounded-xl bg-green-50 flex items-center justify-center group-hover:bg-green-100 transition-colors">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 21v-2a4 4 0 00-8 0v2m8 0H5a2 2 0 01-2-2V7a2 2 0 012-2h5l2 2h5a2 2 0 012 2v12a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <h3 class="text-gray-500 text-sm font-medium">{{ __('messages.Employees') }}</h3>
                </div>
                <p class="text-3xl font-bold text-gray-800">{{ $data['employees']->count()}}</p>
            </div>
        </div>
        <div class="relative">
            <div class="h-2 bg-green-100 rounded-full overflow-hidden">
                <div class="w-1/2 h-2 bg-gradient-to-r from-green-500 to-green-600 rounded-full animate-pulse"></div>
            </div>
        </div>
    </a>

    {{-- Projects --}}
    <a href="{{ route('admin.projects.index') }}" 
       class="block bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-xl hover:scale-105 transition-all duration-300 group animate-fadeInSlideUp" 
       style="animation-delay: 200ms;">
       <div class="flex items-center justify-between mb-4">
            <div class="flex-1">
                <div class="flex items-center space-x-2 mb-3">
                    <div class="w-10 h-10 rounded-xl bg-orange-50 flex items-center justify-center group-hover:bg-orange-100 transition-colors">
                        <svg class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0H4m16 0l-2 9H6l-2-9" />
                        </svg>
                    </div>
                    <h3 class="text-gray-500 text-sm font-medium">Projects</h3>
                </div>
                {{-- Show total number of projects for the enterprise, not only ongoing ones --}}
                <p class="text-3xl font-bold text-gray-800">
                    {{ $data['totalProjectsCount'] ?? ($data['projects']->total() ?? $data['projects']->count()) }}
                </p>
            </div>
        </div>
        <div class="relative">
            <div class="h-2 bg-orange-100 rounded-full overflow-hidden">
                <div class="w-3/4 h-2 bg-gradient-to-r from-orange-400 to-orange-500 rounded-full animate-pulse"></div>
            </div>
        </div>
    </a>

    {{-- Revenue --}}
    <a href="{{ route('admin.invoices.invoicehistory') }}" 
       class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-xl hover:scale-105 transition-all duration-300 group relative overflow-hidden animate-fadeInSlideUp" 
       style="animation-delay: 200ms;">
        <div class="absolute top-0 right-0 w-32 h-32 bg-green-50 rounded-full -mr-16 -mt-16 opacity-50"></div>
        <div class="relative z-10">
            <div class="flex items-center justify-between mb-4">
                <div class="flex-1">
                    <div class="flex items-center space-x-2 mb-3">
                        <div class="w-10 h-10 rounded-xl bg-green-50 flex items-center justify-center group-hover:bg-green-100 transition-colors">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c1.38 0 2.63.56 3.54 1.46M15.54 9.46A5 5 0 1112 8z" />
                            </svg>
                        </div>
                        <h3 class="text-gray-500 text-sm font-medium">{{ __('messages.Monthly Revenue') }}</h3>
                    </div>
                    <p class="text-3xl font-bold text-gray-800">€{{ number_format($data['monthlyRevenue'], 2, ',', ' ') }}</p>
                </div>
                <span class="px-3 py-1.5 bg-green-50 text-green-600 font-semibold text-sm rounded-full">+18%</span>
            </div>
            <div class="flex justify-center mt-4">
                <div class="relative w-14 h-14">
                    <svg class="w-14 h-14 transform -rotate-90">
                        <circle cx="28" cy="28" r="24" stroke="#e5e7eb" stroke-width="4" fill="none" />
                        <circle cx="28" cy="28" r="24" stroke="#10b981" stroke-width="4" fill="none" 
                                stroke-dasharray="150.8" stroke-dashoffset="37.7" 
                                class="transition-all duration-1000 ease-out" />
                    </svg>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <span class="text-xs font-bold text-green-600">75%</span>
                    </div>
                </div>
            </div>
        </div>
    </a>
</div>


{{-- Team employees Overview --}}
<div class="mt-8 border-0 shadow-sm bg-white rounded-lg overflow-hidden hover:shadow-xl animate-fadeInSlideUp" style="animation-delay: 500ms;">
    {{-- Header and action button --}}
    <div class="p-6 flex items-center justify-between border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-900">{{ __('messages.Team employees Overview') }}</h2>
        @unless($isSoloPlan)
            <a href="{{ route('admin.employees.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg">
                + {{__('messages.Add employee')}}
            </a>
        @endunless
    </div>
    
    {{-- Table --}}
    <div class="p-6 overflow-x-auto">
        <table id="employeesTable" class="w-full text-left" style="width:100%">
            <thead class="bg-gray-50">
                <tr class="border-b border-gray-200">
                    <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider">Employee</th>
                    <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider">Function</th>
                    <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-center">Current Status</th>
                    <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-center">Current Task</th>
                    <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-center">Current Project</th>
                    <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-center">Task Progress</th>
                    <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-center">Duration</th>
                    <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-center">Account Status</th>
                    <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-center">Chrono</th>
                    <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-center">Archive</th>
                    <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
            @php
                $avatarColors = ['bg-emerald-500','bg-gray-400','bg-amber-500','bg-red-500','bg-purple-500','bg-sky-500','bg-rose-800','bg-slate-700','bg-yellow-400','bg-indigo-500'];
            @endphp
            @foreach($data['employees'] as $employee)
                <tr class="hover:bg-gray-50">
                    {{-- Employee --}}
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full text-white font-semibold {{ $avatarColors[$loop->index % count($avatarColors)] }}">
                                {{ strtoupper(substr($employee->name, 0, 1)) }}
                            </div>
                            <div class="flex flex-col">
                                <a href="{{ route('admin.employees.show', $employee->id) }}" class="text-sm font-medium text-gray-900 hover:text-blue-600">
                                    {{ $employee->name }}
                                </a>
                                <p class="text-xs text-gray-500">{{ $employee->email }}</p>
                            </div>
                        </div>
                    </td>

                    {{-- Function (Roles) - previous badge style --}}
                    <td class="px-6 py-4 space-y-2 align-top">
                        <div class="flex flex-wrap gap-2">
                            @php
                                $employeeRoles = is_array($employee->roles ?? null) ? $employee->roles : (array)($employee->roles ?? []);
                            @endphp
                            @forelse($employeeRoles as $role)
                                <span class="inline-block px-2 py-1 text-xs font-medium rounded {{ $role=='SuperAdmin'?'bg-fuchsia-100 text-fuchsia-700':'bg-purple-100 text-purple-700'}}">
                                    {{ $role }}
                                </span>
                            @empty
                                <span class="text-xs text-gray-400">No Roles</span>
                            @endforelse
                        </div>
                        @if(!empty($employee['type']))
                            <p class="text-xs text-gray-600 pt-1">{{ $employee['type'] }}</p>
                        @endif
                    </td>

                    {{-- Current Status --}}
                    <td class="px-6 py-4 text-center align-middle text-sm">
                        @php
                            $employeeUser = \App\Models\User::find($employee->user_id);
                        @endphp
                        @if($employeeUser && $employeeUser->is_loggedIn == 0)
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">
                                Off Work
                            </span>
                        @elseif($employeeUser && $employeeUser->is_loggedIn == 1)
                            @php
                                $task_timer = \App\Models\tasks_timer::where('employee_id', $employee->id)
                                    ->whereNull('end_date')
                                    ->first();
                            @endphp
                            @if($task_timer)
                                @if(($task_timer->end_date === null && $task_timer->start_date !== null) && $task_timer->paused == 1)
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">
                                        On Pause
                                    </span>
                                @else
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">
                                        On Work
                                    </span>
                                @endif
                            @endif
                        @endif
                    </td>

                    {{-- Current Task --}}
                    <td class="px-6 py-4 text-center align-middle text-sm">
                        @php
                            $currentTask = \App\Models\tasks_timer::where('employee_id', $employee->id)
                                ->whereNull('end_date')
                                ->first();
                        @endphp
                        @if($currentTask && $currentTask->end_date === null)
                            @if(auth()->user()->hasRole('Admin'))
                                <a href="{{ route('admin.tasks.show', $currentTask->task_id) }}" class="text-xs font-semibold text-blue-600 hover:underline">
                            @elseif(auth()->user()->hasRole('Responsable de projet'))
                                <a href="{{ route('Responsable_de_projet.tasks.show', $currentTask->task_id) }}" class="text-xs font-semibold text-blue-600 hover:underline">
                            @else
                                <a href="#" class="text-xs font-semibold text-blue-600">
                            @endif
                                {{ $currentTask->task_name }}
                            </a>
                        @else
                            <span class="text-xs text-gray-400">No Task</span>
                        @endif
                    </td>

                    {{-- Current Project --}}
                    <td class="px-6 py-4 text-center align-middle text-sm">
                        @php
                            $currentProject = null;
                            if ($currentTask) {
                                $project_id = \App\Models\Tasks::where('id', $currentTask->task_id)->pluck('project_id')->first();
                                $currentProject = \App\Models\Project::where('id', $project_id)->first();
                            }
                        @endphp
                        @if($currentProject)
                            <a href="{{ route('admin.projects.show', $currentProject->id) }}" class="text-xs font-semibold text-emerald-600 hover:underline">
                                {{ $currentProject->name }}
                            </a>
                        @else
                            <span class="text-xs text-gray-400">No Project</span>
                        @endif
                    </td>

                    {{-- Task Progress --}}
                    <td class="px-6 py-4 text-center align-middle text-sm">
                        @if($currentTask && $currentTask->end_date === null)
                            @php
                                $task = \App\Models\Tasks::where('id', $currentTask->task_id)->first();
                            @endphp
                            @if($task && $task->estimated_hours !== null)
                                @php
                                    $tasktotalduration = \App\Models\tasks_timer::where('task_id', $currentTask->task_id)
                                        ->where('employee_id', $employee->id)
                                        ->sum('duration');
                                    $taskestimatedMinutes = $task->estimated_hours * 60;
                                    $taskprogressPercentage = $taskestimatedMinutes > 0 ? ($tasktotalduration / $taskestimatedMinutes) * 100 : 0;
                                    $taskformattedProgressPercentage = number_format($taskprogressPercentage, 2);
                                @endphp
                                <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
                                    <div
                                        class="h-2 rounded-full text-[10px] text-white flex items-center justify-center
                                            @if($taskprogressPercentage < 50) bg-emerald-500
                                            @elseif($taskprogressPercentage < 75) bg-amber-500
                                            @else bg-red-500 @endif"
                                        style="width: {{ $taskformattedProgressPercentage }}%;">
                                    </div>
                                </div>
                                <p class="mt-1 text-xs font-medium text-gray-700">{{ $taskformattedProgressPercentage }}%</p>
                            @else
                                <p class="text-xs font-medium text-gray-600">No Estimated Hours Inserted</p>
                            @endif
                        @else
                            <p class="text-xs font-medium text-gray-600">No Task</p>
                        @endif
                    </td>

                    {{-- Duration --}}
                    <td class="px-6 py-4 text-center align-middle text-sm">
                        @if($currentTask && $currentTask->end_date === null)
                            @php
                                $tasktotalduration = \App\Models\tasks_timer::where('task_id', $currentTask->task_id)
                                    ->where('employee_id', $employee->id)
                                    ->sum('duration');
                                $taskhours = floor($tasktotalduration / 60);
                                $taskminutes = $tasktotalduration % 60;
                            @endphp
                            <span class="text-xs font-semibold text-gray-800">
                                {{ $taskhours }}h:{{ $taskminutes }}m
                            </span>
                        @else
                            <span class="text-xs font-medium text-gray-600">No Task</span>
                        @endif
                    </td>

                    {{-- Account Status --}}
                    <td class="px-6 py-4 text-center align-middle text-sm">
                        @if($employeeUser && $employeeUser->is_activate == 1)
                            <form action="{{ route('admin.employee.deactivate', $employee->id) }}"
                                  method="post"
                                  onsubmit="return confirm('Are you sure?');">
                                @csrf
                                <button type="submit"
                                        class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700 hover:bg-emerald-200">
                                    Active
                                </button>
                            </form>
                        @else
                            <form action="{{ route('admin.employee.activate', $employee->id) }}"
                                  method="post"
                                  onsubmit="return confirm('Are you sure?');">
                                @csrf
                                <button type="submit"
                                        class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700 hover:bg-red-200">
                                    Inactive
                                </button>
                            </form>
                        @endif
                    </td>

                    {{-- Chrono (Stop button only, matching legacy behaviour) --}}
                    <td class="px-6 py-4 text-center align-middle text-sm">
                        @php
                            $task_timer = \App\Models\tasks_timer::where('employee_id', $employee->id)
                                ->whereNull('end_date')
                                ->first();
                        @endphp
                        @if($currentTask && $currentTask->end_date === null && $task_timer)
                            <form class="inline-block"
                                  action="{{ route('admin.tasks.stop', ['tasks' => $task_timer->task_employee_id, 'employee_id' => $employee->id]) }}"
                                  method="POST"
                                  onsubmit="return confirm('Stop timer for this task?');">
                                @csrf
                                <button type="submit"
                                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-red-500 text-white hover:bg-red-600">
                                    Stop
                                </button>
                            </form>
                        @else
                            <span class="text-xs text-gray-400">No Active Timer</span>
                        @endif
                    </td>

                    {{-- Archive --}}
                    <td class="px-6 py-4 text-center align-middle text-sm">
                        <form
                            action="{{ route('admin.employees.archive', $employee->id) }}"
                            method="post"
                            onsubmit="return confirm('Are you sure?');"
                            class="inline-block">
                            @csrf
                            <button type="submit"
                                    class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-slate-200 text-slate-700 hover:bg-slate-300">
                                Archive
                            </button>
                        </form>
                    </td>

                    {{-- Actions (view / edit / delete) --}}
                    <td class="px-6 py-4 whitespace-nowrap align-top">
                        <div class="flex items-center justify-start gap-2">
                            {{-- View details --}}
                            <a href="{{ route('admin.employees.show', $employee->id) }}"
                               title="View Details"
                               class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-white bg-purple-600 hover:bg-purple-700">
                                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </a>

                            {{-- Edit employee --}}
                            <a href="{{ route('admin.employees.edit', $employee->id) }}"
                               title="Edit Employee"
                               class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-white bg-blue-600 hover:bg-blue-700">
                                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                </svg>
                            </a>

                            {{-- Delete employee --}}
                            <form action="{{ route('admin.employees.destroy', $employee->id) }}"
                                  method="POST"
                                  onsubmit="return confirm('Are you sure you want to delete this employee?');"
                                  class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        title="Delete Employee"
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-white bg-red-600 hover:bg-red-700">
                                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
        </table>
    </div>
</div>
    
{{-- Project Overview + Events --}}
<div class="mt-8 grid grid-cols-1 lg:grid-cols-4 gap-8">
    
    {{-- Project Overview (col-span-3) --}}
    <div class="lg:col-span-3 border-0 shadow-sm bg-white rounded-lg hover:shadow-xl animate-fadeInSlideUp" style="animation-delay: 600ms;">
        <h2 class="p-6 text-lg font-semibold text-gray-900 border-b border-gray-200">{{__('messages.Project Overview')}}</h2>

        {{-- Column headers --}}
        <div class="px-6 py-3 grid grid-cols-12 gap-4 border-b border-gray-200">
            <span class="col-span-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">{{__('messages.PROJECT INFO')}}</span>
            <span class="col-span-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">{{__('messages.BUDGET & COSTS')}}</span>
            <span class="col-span-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">{{__('messages.TIME TRACKING')}}</span>
            <span class="col-span-1 text-xs font-semibold text-gray-500 uppercase tracking-wider">{{__('messages.PROGRESS')}}</span>
            <span class="col-span-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">{{__('messages.CLIENT & TEAMS')}}</span>
            <span class="col-span-1 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">{{__('messages.ACTIONS')}}</span>
        </div>
        
        {{-- Project list container --}}
        <div id="projects-container">
            <div class="divide-y divide-gray-200">
                @foreach($data['projects'] as $project)
                    @php
                        $projecttasksId = \App\Models\Tasks::where('project_id', $project->id)->pluck('id');
                        $totalduration = \App\Models\tasks_timer::whereIn('task_id', $projecttasksId)->sum('duration');
                        $difference = ($project->estimated_hours ?? 0) * 60 - $totalduration;
                        $hours = floor(abs($totalduration) / 60);
                        $minutes = abs($totalduration) % 60;
                        $diffhours = floor(abs($difference) / 60);
                        $diffminutes = abs($difference) % 60;
                        $estimatedMinutes = ($project->estimated_hours ?? 0) * 60;
                        $progressPercentage = $estimatedMinutes > 0 ? ($totalduration / $estimatedMinutes) * 100 : 0;
                        $formattedProgressPercentage = number_format($progressPercentage, 2);
                        $avatarColors = ['bg-emerald-500','bg-gray-400','bg-amber-500','bg-red-500','bg-purple-500','bg-sky-500','bg-rose-800','bg-slate-700','bg-yellow-400','bg-indigo-500'];
                    @endphp
                    <div class="px-6 py-4 grid grid-cols-12 gap-4 items-center hover:bg-gray-50">
                        
                        {{-- PROJECT INFO (col-span-3) --}}
                        <div class="col-span-3 flex items-center gap-3">
                            <div class="flex-shrink-0 h-10 w-10 flex items-center justify-center rounded-full text-white font-semibold {{ $avatarColors[$loop->index % count($avatarColors)] }}">
                                {{ strtoupper(substr($project->name, 0, 2)) }}
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $project->name }}</p>
                                <p class="text-xs text-gray-500">Est: {{ $project->estimated_hours ?? 'N/A' }}h</p>
                            </div>
                        </div>

                        {{-- BUDGET & COSTS (col-span-2) --}}
                        <div class="col-span-2 text-sm space-y-1">
                            <p class="text-gray-600"><span class="text-gray-500">{{__('messages.Budget')}}:</span> <span class="font-medium text-gray-800">€{{ number_format($project->budget ?? 0, 2, ',', ' ') }}</span></p>
                            <p class="text-gray-600"><span class="text-gray-500">{{__('messages.CPH')}}:</span> <span class="font-medium text-gray-800">€{{ number_format($project->cost_per_hour ?? 0, 2, ',', ' ') }}</span></p>
                            <p class="text-gray-600"><span class="text-gray-500">{{__('messages.VAT')}}:</span> <span class="font-medium text-gray-800">{{ $project->tva ?? 20 }}%</span></p>
                        </div>

                        {{-- TIME TRACKING (col-span-2) --}}
                        <div class="col-span-2 text-sm space-y-1">
                            <p class="text-gray-600"><span class="text-gray-500">{{__('messages.Duration')}}:</span> <span class="font-bold text-gray-800">{{ $hours }}h:{{ $minutes }}m</span></p>
                            <p class="font-medium 
                                @if($difference > 0) text-green-500 border-b-2 border-green-500
                                @elseif($difference < 0) text-red-500 border-b-2 border-red-500
                                @else text-blue-500 border-b-2 border-blue-500 @endif
                                border-dotted w-fit">
                                @if($difference > 0)+@elseif($difference < 0)-@endif{{ $diffhours }}h:{{ $diffminutes }}m
                            </p>
                        </div>

                        {{-- PROGRESS (col-span-1) --}}
<div class="col-span-1">
    @if($project->estimated_hours !== null)
        <div class="flex flex-col gap-1 w-full"> 
            <span class="text-xs font-medium text-gray-700">{{ $formattedProgressPercentage }}%</span>
            <div class="w-full bg-gray-200 rounded-full h-1.5">
                <div class="h-1.5 rounded-full
                    @if($progressPercentage < 50) bg-emerald-500
                    @elseif($progressPercentage < 75) bg-amber-500
                    @else bg-red-500 @endif"
                    style="width: {{ min($progressPercentage, 100) }}%;">
                </div>
            </div>
        </div>
    @else
        <span class="text-xs font-medium text-gray-600">No Estimated Hours</span>
    @endif
</div>
                        {{-- CLIENT & TEAMS (col-span-3) --}}
                        <div class="col-span-3 space-y-2">
                            <div>
                                <p class="text-xs text-gray-500">{{__('messages.Client')}}:</p>
                                @if($project->client)
                                    <p class="text-sm font-medium text-blue-600">{{ $project->client->name }}</p>
                                @else
                                    <p class="text-sm font-medium text-gray-500">N/A</p>
                                @endif
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Teams :</p>
                                <div class="flex flex-wrap gap-1 mt-1">
                                    @forelse($project->teams as $team)
                                        <span class="inline-block px-2 py-0.5 text-xs font-medium bg-blue-100 text-blue-700 rounded">{{ $team->name }}</span>
                                    @empty
                                        <span class="text-xs text-gray-500">No teams assigned</span>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        {{-- ACTIONS (col-span-1) --}}
                        <div class="col-span-1 flex justify-end gap-2">
                            <a href="{{ route('admin.projects.show', $project->id) }}" class="p-1 rounded-md bg-blue-600 text-white hover:bg-red-700">
                                <svg class="h-5 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-.07.207-.07.431 0 .639C20.268 16.057 16.477 19 12 19c-4.478 0-8.268-2.943-9.542-7a1.186 1.186 0 010-.639z" />
                                </svg>
                            </a>
                            <form action="{{ route('admin.projects.destroy', $project->id) }}"
                                  method="POST"
                                  onsubmit="return confirm('Are you sure?');"
                                  class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1 rounded-md bg-red-600 text-white hover:bg-red-700">
                                    <svg class="h-5 w-4" fill="none" stroke="currentColor" viewBox="0 0 23 23">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Pagination --}}
            <div class="mt-4 flex justify-center" id="pagination-container">
                {{ $data['projects']->appends(request()->except('page'))->links() }}
            </div>
        </div>
    </div>

    {{-- Today's Events --}}
    <div class="lg:col-span-1 border-0 shadow-sm bg-white rounded-2xl hover:shadow-xl p-6 h-fit animate-fadeInSlideUp" style="animation-delay: 700ms;">
        {{-- Header with "Add Event" button --}}
        <div class="flex items-center justify-between mb-1">
            <div class="flex items-center gap-2">
                <div class="w-1.5 h-5 bg-blue-600 rounded"></div>
                <h2 class="text-lg font-semibold text-gray-900">Today's Events</h2>
            </div>
            
            <button onclick="window.Livewire.dispatch('openCalendarForm')" class="flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-3 py-1.5 rounded-lg">
                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Add Event
            </button>
        </div>
            
        {{-- Date --}}
        <p class="text-sm text-gray-500 mb-6">{{ now()->format('d F Y') }}</p>
            
        {{-- Events list --}}
        <ul class="space-y-5">
            @forelse($data['dayEvents'] as $event)
            <li class="flex gap-3">
                <div class="w-1 flex-shrink-0 bg-blue-500 rounded-full"></div>
                
                <div>
                    <p class="text-sm font-medium text-gray-800">{{ $event->title }}</p>
                    <p class="text-sm text-gray-500 flex items-center gap-1.5 mt-1">
                        <svg class="w-3.5 h-3.5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                           <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                        <span>
                            {{ \Carbon\Carbon::parse($event->start_date)->format('H:i') }} - 
                            {{ \Carbon\Carbon::parse($event->end_date)->format('H:i') }}
                        </span>
                    </p>
                </div>
            </li>
        @empty
            <li class="text-sm text-gray-500">
                No events scheduled for today.
            </li>
        @endforelse
        </ul>

        {{-- Centered "View full calendar" link --}}
        <div class="text-center mt-6">
            <button onclick="window.Livewire.dispatch('openCalendarModal')" class="text-sm font-medium text-blue-600 hover:underline">
            See the full calendar
            </button>
        </div>
    </div>
</div>

{{-- AJAX Pagination Script --}}
<script>
$(document).ready(function() {
    // Handle pagination clicks
    $(document).on('click', '#pagination-container a', function(e) {
        e.preventDefault();
        
        var url = $(this).attr('href');
        if (!url || $(this).attr('aria-disabled') === 'true') return;
        
        // Add loading state
        $('#projects-container').css('opacity', '0.5').addClass('pointer-events-none');
        
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'html',
            success: function(response) {
                // Extract the projects and pagination content from response
                var newProjects = $(response).find('#projects-container').html();
                var newPagination = $(response).find('#pagination-container').html();
                
                // Update the containers
                $('#projects-container').html(newProjects);
                $('#pagination-container').html(newPagination);
                
                // Scroll to projects section smoothly
                $('html, body').animate({
                    scrollTop: $('#projects-container').offset().top - 100
                }, 400);
            },
            error: function(xhr, status, error) {
                console.error('Pagination error:', error);
                alert('Error loading projects. Please try again.');
            },
            complete: function() {
                $('#projects-container').css('opacity', '1').removeClass('pointer-events-none');
            }
        });
    });
});
</script>
