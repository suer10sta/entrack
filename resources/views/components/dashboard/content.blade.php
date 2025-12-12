@php
    $user = Auth::user();
    $isSoloPlan = optional(optional($user)->entreprise)->type_compt === 'solo';
@endphp

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
        color: #4b5563;
        /* gray-600 */
        background-color: #ffffff;
        /* white */
        border: 1px solid #e5e7eb;
        /* gray-200 */
        cursor: pointer;
        transition: all 0.15s ease-in-out;
    }

    #employeesTable_wrapper .dataTables_paginate .paginate_button:hover {
        background-color: #eff6ff;
        /* blue-50 */
        border-color: #bfdbfe;
        /* blue-200 */
        color: #1d4ed8;
        /* blue-700 */
    }

    #employeesTable_wrapper .dataTables_paginate .paginate_button.current {
        background-color: #1d4ed8;
        /* blue-700 */
        border-color: #1d4ed8;
        color: #ffffff;
    }

    #employeesTable_wrapper .dataTables_paginate .paginate_button.disabled {
        opacity: 0.5;
        cursor: not-allowed;
        background-color: #f9fafb;
        /* gray-50 */
    }

    /* Keep prev/next consistent with light theme */
    #employeesTable_wrapper .dataTables_paginate .paginate_button.previous,
    #employeesTable_wrapper .dataTables_paginate .paginate_button.next {
        background-color: #ffffff;
        border: 1px solid #e5e7eb;
        color: #4b5563;
    }

    #employeesTable_wrapper .dataTables_paginate .paginate_button.previous:not(.disabled):hover,
    #employeesTable_wrapper .dataTables_paginate .paginate_button.next:not(.disabled):hover {
        background-color: #eff6ff;
        border-color: #bfdbfe;
        color: #1d4ed8;
    }

    #employeesTable_wrapper .dataTables_paginate .paginate_button.previous.disabled,
    #employeesTable_wrapper .dataTables_paginate .paginate_button.next.disabled {
        background-color: #f9fafb;
        color: #9ca3af;
        border-color: #e5e7eb;
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

    .relative.z-0.inline-flex.rtl\:flex-row-reverse.shadow-sm.rounded-md>* {
        margin-left: 0 !important;
        border-radius: 0.5rem !important;
        border: 1px solid #e5e7eb !important;
        background-color: #ffffff !important;
        color: #374151 !important;
        transition: all 0.15s ease;
    }

    .relative.z-0.inline-flex.rtl\:flex-row-reverse.shadow-sm.rounded-md>*:hover {
        background-color: #eff6ff !important;
        border-color: #bfdbfe !important;
        color: #1d4ed8 !important;
    }

    .relative.z-0.inline-flex.rtl\:flex-row-reverse.shadow-sm.rounded-md>span[aria-current="page"]>span {
        background-color: #1d4ed8 !important;
        border-color: #1d4ed8 !important;
        color: #ffffff !important;
    }
</style>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.tailwindcss.min.css">
<script src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>
@livewireStyles


<div class="flex-1 overflow-auto bg-[#eaebef]">
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
                            <div
                                class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center group-hover:bg-blue-100 transition-colors">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M17 20h5V10H2v10h5m10 0V8a5 5 0 00-10 0v12" />
                                </svg>
                            </div>
                            <h3 class="text-gray-500 text-sm font-medium">{{ __('messages.Clients') }}</h3>
                        </div>
                        <p class="text-3xl font-bold text-gray-800">{{ $data['clients']->count() }}</p>
                    </div>
                </div>
                <div class="relative">
                    <div class="h-2 bg-blue-100 rounded-full overflow-hidden">
                        <div class="w-2/3 h-2 bg-gradient-to-r from-blue-500 to-blue-600 rounded-full animate-pulse">
                        </div>
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
                            <div
                                class="w-10 h-10 rounded-xl bg-green-50 flex items-center justify-center group-hover:bg-green-100 transition-colors">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M17 21v-2a4 4 0 00-8 0v2m8 0H5a2 2 0 01-2-2V7a2 2 0 012-2h5l2 2h5a2 2 0 012 2v12a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <h3 class="text-gray-500 text-sm font-medium">{{ __('messages.Employees') }}</h3>
                        </div>
                        <p class="text-3xl font-bold text-gray-800">{{ $data['employees']->count()}}</p>
                    </div>
                </div>
                <div class="relative">
                    <div class="h-2 bg-green-100 rounded-full overflow-hidden">
                        <div class="w-1/2 h-2 bg-gradient-to-r from-green-500 to-green-600 rounded-full animate-pulse">
                        </div>
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
                            <div
                                class="w-10 h-10 rounded-xl bg-orange-50 flex items-center justify-center group-hover:bg-orange-100 transition-colors">
                                <svg class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0H4m16 0l-2 9H6l-2-9" />
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
                        <div
                            class="w-3/4 h-2 bg-gradient-to-r from-orange-400 to-orange-500 rounded-full animate-pulse">
                        </div>
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
                                <div
                                    class="w-10 h-10 rounded-xl bg-green-50 flex items-center justify-center group-hover:bg-green-100 transition-colors">
                                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor"
                                        stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 8c1.38 0 2.63.56 3.54 1.46M15.54 9.46A5 5 0 1112 8z" />
                                    </svg>
                                </div>
                                <h3 class="text-gray-500 text-sm font-medium">{{ __('messages.Monthly Revenue') }}</h3>
                            </div>
                            <p class="text-3xl font-bold text-gray-800">
                                €{{ number_format($data['monthlyRevenue'], 2, ',', ' ') }}</p>
                        </div>
                        <span
                            class="px-3 py-1.5 bg-green-50 text-green-600 font-semibold text-sm rounded-full">+18%</span>
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
        <div class="mt-8 border-0 shadow-sm bg-white rounded-lg overflow-hidden hover:shadow-xl animate-fadeInSlideUp"
            style="animation-delay: 500ms;">
            {{-- Header and action button --}}
            <div class="p-6 flex items-center justify-between border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">{{ __('messages.Team employees Overview') }}</h2>
                @unless($isSoloPlan)
                    <a href="{{ route('admin.employees.create') }}"
                        class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg">
                        + {{__('messages.Add employee')}}
                    </a>
                @endunless
            </div>

            {{-- Table --}}
            <div class="p-6 overflow-x-auto">
                <table id="employeesTable" class="w-full text-left" style="width:100%">
                    <thead class="bg-gray-50">
                        <tr class="border-b border-gray-200">
                            <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider">Employee
                            </th>
                            <th
                                class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-center">
                                Current Status</th>
                            <th
                                class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-center">
                                Current Task</th>
                            <th
                                class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-center">
                                Current Project</th>
                            <th
                                class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-center">
                                Task Progress</th>
                            <th
                                class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-center">
                                Duration</th>
                            <th
                                class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-center">
                                Account Status</th>
                            <th
                                class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-center">
                                Chrono</th>
                            <th
                                class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-center">
                                Archive</th>
                            <th
                                class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-center">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @php
                            $avatarColors = ['bg-emerald-500', 'bg-gray-400', 'bg-amber-500', 'bg-red-500', 'bg-purple-500', 'bg-sky-500', 'bg-rose-800', 'bg-slate-700', 'bg-yellow-400', 'bg-indigo-500'];
                        @endphp
                        @foreach($data['employees'] as $employee)
                            <tr class="hover:bg-gray-50" data-employee-id="{{ $employee->id }}">
                                {{-- Employee --}}
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex h-10 w-10 items-center justify-center rounded-full text-white font-semibold {{ $avatarColors[$loop->index % count($avatarColors)] }}">
                                            {{ strtoupper(substr($employee->name, 0, 1)) }}
                                        </div>
                                        <div class="flex flex-col">
                                            <a href="{{ route('admin.employees.show', $employee->id) }}"
                                                class="text-sm font-medium text-gray-900 hover:text-blue-600">
                                                {{ $employee->name }}
                                            </a>
                                            <p class="text-xs text-gray-500">{{ $employee->email }}</p>
                                            {{-- Roles under email to minimize width --}}
                                            @php
                                                $employeeRoles = is_array($employee->roles ?? null) ? $employee->roles : (array) ($employee->roles ?? []);
                                            @endphp
                                            <div class="flex flex-wrap gap-1 pt-1">
                                                @forelse($employeeRoles as $role)
                                                    <span
                                                        class="inline-block px-2 py-0.5 text-[10.5px] font-medium rounded {{ $role == 'SuperAdmin' ? 'bg-fuchsia-100 text-fuchsia-700' : 'bg-purple-100 text-purple-700'}}">
                                                        {{ $role }}
                                                    </span>
                                                @empty
                                                    <span class="text-[10px] text-gray-400">No Roles</span>
                                                @endforelse
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                {{-- Current Status --}}
                                <td class="px-6 py-4 text-center align-middle text-sm cell-current-status">
                                    @php
                                        $employeeUser = \App\Models\User::find($employee->user_id);
                                    @endphp
                                    @if($employeeUser && $employeeUser->is_loggedIn == 0)
                                        <span
                                            class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">
                                            Off Work
                                        </span>
                                    @elseif($employeeUser && $employeeUser->is_loggedIn == 1)
                                        @php
                                            // Align status with other columns: only consider timers started today and still open
                                            $task_timer = \App\Models\tasks_timer::where('employee_id', $employee->id)
                                                ->whereDate('start_date', today())
                                                ->whereNull('end_date')
                                                ->first();
                                        @endphp
                                        @if($task_timer)
                                            @if(($task_timer->end_date === null && $task_timer->start_date !== null) && $task_timer->paused == 1)
                                                <span
                                                    class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">
                                                    On Pause
                                                </span>
                                            @else
                                                <span
                                                    class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">
                                                    On Work
                                                </span>
                                            @endif
                                        @else
                                            <span
                                                class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">
                                                Off Work
                                            </span>
                                        @endif
                                    @endif
                                </td>

                                {{-- Current Task --}}
                                <td class="px-6 py-4 text-center align-middle text-sm cell-current-task">
                                    @php
                                        $isSuperAdmin = auth()->user()->hasRole('SuperAdmin') || auth()->user()->hasRole('super_admin');
                                        if ($isSuperAdmin) {
                                            // For superadmin, get all active tasks
                                            $currentTasks = \App\Models\tasks_timer::where('employee_id', $employee->id)
                                                ->whereDate('start_date', today())
                                                ->whereNull('end_date')
                                                ->orderBy('start_date', 'desc')
                                                ->get();
                                        } else {
                                            // For others, get only the latest active task
                                            $currentTasks = \App\Models\tasks_timer::where('employee_id', $employee->id)
                                                ->whereDate('start_date', today())
                                                ->whereNull('end_date')
                                                ->latest()
                                                ->take(1)
                                                ->get();
                                        }
                                    @endphp
                                    @if($currentTasks->isNotEmpty())
                                        @php
                                            $taskListClasses = 'space-y-1';
                                            if ($isSuperAdmin && $currentTasks->count() > 1) {
                                                $taskListClasses .= ' divide-y divide-gray-500';
                                            }
                                        @endphp
                                        <div class="{{ $taskListClasses }}">
                                            @foreach($currentTasks as $currentTask)
                                                @if(auth()->user()->hasRole('Admin') || auth()->user()->hasRole('SuperAdmin') || auth()->user()->hasRole('super_admin'))
                                                    <a href="{{ route('admin.tasks.show', $currentTask->task_id) }}"
                                                        class="text-xs font-semibold text-blue-600 hover:underline block">
                                                @elseif(auth()->user()->hasRole('Responsable de projet'))
                                                        <a href="{{ route('Responsable_de_projet.tasks.show', $currentTask->task_id) }}"
                                                            class="text-xs font-semibold text-blue-600 hover:underline block">
                                                    @else
                                                            <a href="#" class="text-xs font-semibold text-blue-600 block">
                                                        @endif
                                                            {{ $currentTask->task_name }}
                                                        </a>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-xs text-gray-400">No Task</span>
                                    @endif
                                </td>

                                {{-- Current Project --}}
                                <td class="px-6 py-4 text-center align-middle text-sm cell-current-project">
                                    @php
                                        $isSuperAdmin = auth()->user()->hasRole('SuperAdmin') || auth()->user()->hasRole('super_admin');
                                        if ($isSuperAdmin) {
                                            // For superadmin, get all active tasks
                                            $activeTasks = \App\Models\tasks_timer::where('employee_id', $employee->id)
                                                ->whereDate('start_date', today())
                                                ->whereNull('end_date')
                                                ->orderBy('start_date', 'desc')
                                                ->get();
                                        } else {
                                            // For others, get only the latest active task
                                            $activeTasks = \App\Models\tasks_timer::where('employee_id', $employee->id)
                                                ->whereDate('start_date', today())
                                                ->whereNull('end_date')
                                                ->latest()
                                                ->take(1)
                                                ->get();
                                        }
                                        $currentProjects = collect();
                                        foreach ($activeTasks as $task) {
                                            $project_id = \App\Models\Tasks::where('id', $task->task_id)->pluck('project_id')->first();
                                            if ($project_id) {
                                                $project = \App\Models\Project::find($project_id);
                                                if ($project && !$currentProjects->contains('id', $project->id)) {
                                                    $currentProjects->push($project);
                                                }
                                            }
                                        }
                                    @endphp
                                    @if($currentProjects->isNotEmpty())
                                        @php
                                            $projectListClasses = 'space-y-1';
                                            if ($isSuperAdmin && $currentProjects->count() > 1) {
                                                $projectListClasses .= ' divide-y divide-gray-300';
                                            }
                                        @endphp
                                        <div class="{{ $projectListClasses }}">
                                            @foreach($currentProjects as $currentProject)
                                                <a href="{{ route('admin.projects.show', $currentProject->id) }}"
                                                    class="text-xs font-semibold text-emerald-600 hover:underline block">
                                                    {{ $currentProject->name }}
                                                </a>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-xs text-gray-400">No Project</span>
                                    @endif
                                </td>

                                {{-- Task Progress --}}
                                <td class="px-6 py-4 text-center align-middle text-sm cell-task-progress">
                                    @php
                                        $isSuperAdmin = auth()->user()->hasRole('SuperAdmin') || auth()->user()->hasRole('super_admin');
                                        if ($isSuperAdmin) {
                                            $activeTasks = \App\Models\tasks_timer::where('employee_id', $employee->id)
                                                ->whereDate('start_date', today())
                                                ->whereNull('end_date')
                                                ->orderBy('start_date', 'desc')
                                                ->get();
                                        } else {
                                            $activeTasks = \App\Models\tasks_timer::where('employee_id', $employee->id)
                                                ->whereDate('start_date', today())
                                                ->whereNull('end_date')
                                                ->latest()
                                                ->take(1)
                                                ->get();
                                        }
                                        $firstActiveTask = $activeTasks->first();
                                    @endphp
                                    @if($firstActiveTask)
                                        @php
                                            $task = \App\Models\Tasks::where('id', $firstActiveTask->task_id)->first();
                                        @endphp
                                        @if($task && $task->estimated_hours !== null)
                                            @php
                                                $tasktotalduration = \App\Models\tasks_timer::where('task_id', $firstActiveTask->task_id)
                                                    ->where('employee_id', $employee->id)
                                                    ->sum('duration');
                                                $taskestimatedMinutes = $task->estimated_hours * 60;
                                                $taskprogressPercentage = $taskestimatedMinutes > 0 ? ($tasktotalduration / $taskestimatedMinutes) * 100 : 0;
                                                $taskformattedProgressPercentage = number_format($taskprogressPercentage, 2);
                                            @endphp
                                            <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
                                                <div class="h-2 rounded-full text-[10px] text-white flex items-center justify-center
                                                                                                                                                                                                                                @if($taskprogressPercentage < 50) bg-emerald-500
                                                                                                                                                                                                                                @elseif($taskprogressPercentage < 75) bg-amber-500
                                                                                                                                                                                                                                @else bg-red-500 @endif"
                                                    style="width: {{ $taskformattedProgressPercentage }}%;">
                                                </div>
                                            </div>
                                            <p class="mt-1 text-xs font-medium text-gray-700">
                                                {{ $taskformattedProgressPercentage }}%
                                            </p>
                                        @else
                                            <p class="text-xs font-medium text-gray-600">No Estimated Hours Inserted</p>
                                        @endif
                                    @else
                                        <p class="text-xs font-medium text-gray-600">No Task</p>
                                    @endif
                                </td>

                                {{-- Duration --}}
                                <td class="px-6 py-4 text-center align-middle text-sm cell-duration">
                                    @php
                                        $isSuperAdmin = auth()->user()->hasRole('SuperAdmin') || auth()->user()->hasRole('super_admin');
                                        if ($isSuperAdmin) {
                                            $activeTasks = \App\Models\tasks_timer::where('employee_id', $employee->id)
                                                ->whereDate('start_date', today())
                                                ->whereNull('end_date')
                                                ->orderBy('start_date', 'desc')
                                                ->get();
                                        } else {
                                            $activeTasks = \App\Models\tasks_timer::where('employee_id', $employee->id)
                                                ->whereDate('start_date', today())
                                                ->whereNull('end_date')
                                                ->latest()
                                                ->take(1)
                                                ->get();
                                        }
                                        $firstActiveTask = $activeTasks->first();
                                    @endphp
                                    @if($activeTasks->isNotEmpty())
                                        @php $now = \Carbon\Carbon::now(); @endphp
                                        <div
                                            class="space-y-1 {{ ($isSuperAdmin && $activeTasks->count() > 1) ? 'divide-y divide-gray-500' : '' }}">
                                            @foreach($activeTasks as $activeTask)
                                                @php
                                                    $startTime = \Carbon\Carbon::parse($activeTask->start_date);
                                                    $isPaused = $activeTask->paused == 1;
                                                    $baseSeconds = ($activeTask->duration ?? 0) * 60;
                                                    $currentDurationSeconds = $isPaused ? $baseSeconds : ($startTime->diffInSeconds($now) + $baseSeconds);
                                                    $taskhours = floor($currentDurationSeconds / 3600);
                                                    $taskminutes = floor(($currentDurationSeconds % 3600) / 60);
                                                    $taskseconds = $currentDurationSeconds % 60;
                                                    $taskName = \App\Models\employees_tasks::where('id', $activeTask->task_employee_id)->value('task_name');
                                                    $shouldShowName = $activeTasks->count() > 1;
                                                @endphp
                                                <div class="text-xs font-semibold text-gray-800">
                                                    @if($shouldShowName)
                                                        <span
                                                            class="text-gray-600">{{ $taskName ?? ('Task ' . $activeTask->task_employee_id) }}:</span>
                                                    @endif
                                                    <span class="live-duration" data-start="{{ $activeTask->start_date }}"
                                                        data-extra-seconds="{{ $baseSeconds }}"
                                                        data-paused="{{ $isPaused ? 1 : 0 }}">
                                                        {{ $taskhours }}h:{{ sprintf('%02d', $taskminutes) }}m:{{ sprintf('%02d', $taskseconds) }}s
                                                    </span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        @php
                                            $today = \Carbon\Carbon::today();
                                            $totalDurationMinutes = \App\Models\tasks_timer::where('employee_id', $employee->id)
                                                ->whereDate('start_date', $today)
                                                ->whereNotNull('duration')
                                                ->sum('duration');
                                            $h = floor($totalDurationMinutes / 60);
                                            $m = $totalDurationMinutes % 60;
                                        @endphp
                                        <span class="text-xs font-medium text-gray-700">Total today:
                                            {{ $h }}h:{{ sprintf('%02d', $m) }}m</span>
                                    @endif
                                </td>

                                {{-- Account Status --}}
                                <td class="px-6 py-4 text-center align-middle text-sm cell-account-status">
                                    @if($employeeUser && $employeeUser->is_activate == 1)
                                        <form action="{{ route('admin.employee.deactivate', $employee->id) }}" method="post"
                                            onsubmit="return confirm('Are you sure?');">
                                            @csrf
                                            <button type="submit"
                                                class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700 hover:bg-emerald-200">
                                                Active
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('admin.employee.activate', $employee->id) }}" method="post"
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
                                <td class="px-6 py-4 text-center align-middle text-sm cell-chrono">
                                    @php
                                        $isSuperAdmin = auth()->user()->hasRole('SuperAdmin') || auth()->user()->hasRole('super_admin');
                                        if ($isSuperAdmin) {
                                            $activeTasks = \App\Models\tasks_timer::where('employee_id', $employee->id)
                                                ->whereDate('start_date', today())
                                                ->whereNull('end_date')
                                                ->orderBy('start_date', 'desc')
                                                ->get();
                                        } else {
                                            $activeTasks = \App\Models\tasks_timer::where('employee_id', $employee->id)
                                                ->whereDate('start_date', today())
                                                ->whereNull('end_date')
                                                ->latest()
                                                ->take(1)
                                                ->get();
                                        }
                                        $firstActiveTask = $activeTasks->first();
                                    @endphp
                                    @if($activeTasks->isNotEmpty())
                                        @if($isSuperAdmin && $activeTasks->count() > 1)
                                            <button type="button"
                                                class="inline-flex items-center px-3 py-1 mb-2 rounded-full text-xs font-semibold bg-red-100 text-red-700 hover:bg-red-200"
                                                onclick="stopAllTimersForEmployee({{ $employee->id }})">
                                                Stop all
                                            </button>
                                        @endif
                                        <div class="space-y-1">
                                            @foreach($activeTasks as $activeTask)
                                                @php
                                                    $taskName = \App\Models\employees_tasks::where('id', $activeTask->task_employee_id)->value('task_name');
                                                    $shouldShowName = $activeTasks->count() > 1;
                                                @endphp
                                                <form class="inline-flex items-center gap-2 stop-form-{{ $employee->id }}"
                                                    action="{{ route('admin.tasks.stop', ['tasks' => $activeTask->task_employee_id, 'employee_id' => $employee->id]) }}"
                                                    method="POST" data-confirm="Stop timer for this task?">
                                                    @csrf
                                                    @if($shouldShowName)
                                                        <span
                                                            class="text-xs text-gray-700">{{ $taskName ?? ('Task ' . $activeTask->task_employee_id) }}</span>
                                                    @endif
                                                    <button type="submit"
                                                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-red-500 text-white hover:bg-red-600">
                                                        Stop
                                                    </button>
                                                </form>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-xs text-gray-400">No Active Timer</span>
                                    @endif
                                </td>

                                {{-- Archive --}}
                                <td class="px-6 py-4 text-center align-middle text-sm">
                                    <form action="{{ route('admin.employees.archive', $employee->id) }}" method="post"
                                        onsubmit="return confirm('Are you sure?');" class="inline-block">
                                        @csrf
                                        <button type="submit"
                                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-slate-200 text-slate-700 hover:bg-slate-300">
                                            Archive
                                        </button>
                                    </form>
                                </td>

                                {{-- Actions (view / edit / delete / tasks done today) --}}
                                <td class="px-6 py-4 whitespace-nowrap align-top">
                                    <div class="relative flex items-center justify-start gap-2">
                                        @php
                                            $isSuperAdmin = auth()->user()->hasRole('SuperAdmin') || auth()->user()->hasRole('super_admin');
                                            // Active timers for the employee today
                                            if ($isSuperAdmin) {
                                                $activeTimers = \App\Models\tasks_timer::where('employee_id', $employee->id)
                                                    ->whereDate('start_date', \Carbon\Carbon::today())
                                                    ->whereNull('end_date')
                                                    ->get();
                                            } else {
                                                $activeTimers = \App\Models\tasks_timer::where('employee_id', $employee->id)
                                                    ->whereDate('start_date', \Carbon\Carbon::today())
                                                    ->whereNull('end_date')
                                                    ->latest()
                                                    ->take(1)
                                                    ->get();
                                            }
                                            $activeTaskIds = $activeTimers->pluck('task_id')->filter()->unique();

                                            $completedTasksToday = \App\Models\tasks_timer::where('employee_id', $employee->id)
                                                ->whereDate('start_date', \Carbon\Carbon::today())
                                                ->whereNotNull('end_date')
                                                // exclude any task currently active (so history shows only stopped tasks)
                                                ->when($activeTaskIds->isNotEmpty(), fn($q) => $q->whereNotIn('task_id', $activeTaskIds))
                                                ->get()
                                                // Group by task (prefer task_id, otherwise fallback to task name)
                                                ->groupBy(fn($t) => $t->task_id ?? 'name:' . ($t->task_name ?? 'unknown'))
                                                ->map(function ($group) {
                                                    // keep latest session info, but sum durations across the day
                                                    $latest = $group->sortByDesc('end_date')->first();
                                                    $latest->total_duration = $group->sum('duration');
                                                    return $latest;
                                                })
                                                ->sortByDesc('end_date');
                                        @endphp

                                        {{-- Tasks history toggle (shows tasks done today, excluding current) --}}
                                        <button type="button" onclick="toggleTasksTodayRow('tasks-row-{{ $employee->id }}')"
                                            title="Show today's completed tasks"
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-white bg-indigo-500 hover:bg-indigo-600">
                                            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M4 6h16M4 12h16M4 18h16" />
                                            </svg>
                                        </button>

                                        {{-- View details --}}
                                        <a href="{{ route('admin.employees.show', $employee->id) }}"
                                            title="Details Employee"
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-white bg-purple-600 hover:bg-purple-700">
                                            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                        </a>

                                        {{-- Edit employee --}}
                                        <a href="{{ route('admin.employees.edit', $employee->id) }}" title="Edit Employee"
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-white bg-blue-600 hover:bg-blue-700">
                                            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                            </svg>
                                        </a>

                                        {{-- Delete employee --}}
                                        <form action="{{ route('admin.employees.destroy', $employee->id) }}" method="POST"
                                            onsubmit="return confirm('Are you sure you want to delete this employee?');"
                                            class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="Delete Employee"
                                                class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-white bg-red-600 hover:bg-red-700">
                                                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            {{-- Task history row (hidden by default) --}}
                            <tr id="tasks-row-{{ $employee->id }}" class="hidden bg-gray-50">
                                <td colspan="10" class="px-6 py-4">
                                    <div class="space-y-3">
                                        <div class="flex items-center justify-between">
                                            <h4 class="text-sm font-semibold text-gray-800">Task History (Today)</h4>
                                            <button type="button"
                                                onclick="toggleTasksTodayRow('tasks-row-{{ $employee->id }}')"
                                                class="text-xs text-gray-500 hover:text-gray-700">Close</button>
                                        </div>
                                        @forelse($completedTasksToday as $doneTask)
                                            @php
                                                $durationMinutes = (int) ($doneTask->total_duration ?? $doneTask->duration);
                                                $h = floor($durationMinutes / 60);
                                                $m = $durationMinutes % 60;
                                                $taskModel = \App\Models\Tasks::find($doneTask->task_id);
                                                $projectName = null;
                                                // Prefer direct project_id on timer if present
                                                if (!empty($doneTask->project_id)) {
                                                    $projectName = optional(\App\Models\Project::find($doneTask->project_id))->name;
                                                }
                                                // Fallback to task's project
                                                if (!$projectName && $taskModel) {
                                                    $projectName = optional($taskModel->project)->name ?? null;
                                                }
                                            @endphp
                                            <div
                                                class="flex items-center justify-between rounded-lg border border-gray-200 bg-white px-3 py-2">
                                                <div class="flex flex-col">
                                                    <span
                                                        class="text-sm font-medium text-gray-900">{{ $doneTask->task_name ?? ($taskModel->title ?? 'Task') }}</span>
                                                    <span
                                                        class="text-xs text-gray-500">{{ $projectName ?? 'No project linked' }}</span>
                                                </div>
                                                <div class="flex items-center gap-3">
                                                    <span
                                                        class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-700">Completed</span>
                                                    <span class="text-xs text-gray-700 whitespace-nowrap">{{ $h }}h
                                                        {{ $m }}m</span>
                                                    <form
                                                        action="{{ route('admin.tasks.start', ['tasks' => $doneTask->task_employee_id ?? $doneTask->task_id, 'employee_id' => $doneTask->employee_id]) }}"
                                                        method="POST" class="history-start-form"
                                                        data-task-id="{{ $doneTask->task_employee_id ?? $doneTask->task_id }}">
                                                        @csrf
                                                        <textarea name="comment" placeholder="Enter comment" rows="1"
                                                            class="border border-slate-300 rounded px-2 py-1 text-xs text-slate-700 focus:outline-none focus:ring focus:ring-emerald-200 resize-y min-h-[32px]"></textarea>
                                                        <button type="submit"
                                                            class="inline-flex items-center px-3 py-1 rounded-full text-[11px] font-semibold bg-emerald-500 text-white hover:bg-emerald-600">
                                                            Start
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        @empty
                                            <p class="text-xs text-gray-500">No completed tasks today.</p>
                                        @endforelse
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
            <div class="lg:col-span-3 border-0 shadow-sm bg-white rounded-lg hover:shadow-xl animate-fadeInSlideUp"
                style="animation-delay: 600ms;">
                <h2 class="p-6 text-lg font-semibold text-gray-900 border-b border-gray-200">
                    {{__('messages.Project Overview')}} - Today's Work
                </h2>

                {{-- Column headers --}}
                <div class="px-6 py-3 grid grid-cols-12 gap-4 border-b border-gray-200">
                    <span
                        class="col-span-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">{{__('messages.PROJECT INFO')}}</span>
                    <span
                        class="col-span-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">{{__('messages.BUDGET & COSTS')}}</span>
                    <span
                        class="col-span-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">{{__('messages.TIME TRACKING')}}</span>
                    <span
                        class="col-span-1 text-xs font-semibold text-gray-500 uppercase tracking-wider">{{__('messages.PROGRESS')}}</span>
                    <span
                        class="col-span-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">{{__('messages.CLIENT & EMPLOYEES')}}</span>
                    <span
                        class="col-span-1 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">{{__('messages.ACTIONS')}}</span>
                </div>

                {{-- Project list container --}}
                <div id="projects-container">
                    @php
                        $hasWorkedToday = false;
                        $todayStart = \Carbon\Carbon::today();
                        $todayEnd = \Carbon\Carbon::today()->endOfDay();
                        $avatarColors = ['bg-emerald-500', 'bg-gray-400', 'bg-amber-500', 'bg-red-500', 'bg-purple-500', 'bg-sky-500', 'bg-rose-800', 'bg-slate-700', 'bg-yellow-400', 'bg-indigo-500'];

                        // Collect timers that overlap today
                        $entrepriseId = optional(Auth::user())->entreprise_id;
                        $timersToday = \App\Models\tasks_timer::where(function ($q) use ($todayStart, $todayEnd) {
                            $q->where('start_date', '<=', $todayEnd)
                                ->where(function ($qq) use ($todayStart) {
                                    $qq->whereNull('end_date')
                                        ->orWhere('end_date', '>=', $todayStart);
                                });
                        })
                            ->when($entrepriseId, fn($q) => $q->where('entreprise_id', $entrepriseId))
                            ->get();

                        // Gather project IDs from timers directly and via tasks
                        $projectIdsDirect = $timersToday->pluck('project_id')->filter();
                        $taskIds = $timersToday->pluck('task_id')->filter();
                        $projectIdsFromTasks = $taskIds->isNotEmpty()
                            ? \App\Models\Tasks::whereIn('id', $taskIds)->pluck('project_id')
                            : collect();
                        $projectIdsToday = $projectIdsDirect->merge($projectIdsFromTasks)->unique()->filter();

                        $projectsToday = $projectIdsToday->isNotEmpty()
                            ? \App\Models\Project::whereIn('id', $projectIdsToday)->get()
                            : collect();
                    @endphp

                    @include('components.dashboard.project-overview-list', ['projectsToday' => $projectsToday])

                </div>
            </div>

            {{-- Today's Events section remains the same --}}
            <div class="lg:col-span-1 border-0 shadow-sm bg-white rounded-2xl hover:shadow-xl p-6 h-fit animate-fadeInSlideUp"
                style="animation-delay: 700ms;">
                {{-- Header with "Add Event" button --}}
                <div class="flex items-center justify-between mb-1">
                    <div class="flex items-center gap-2">
                        <div class="w-1.5 h-5 bg-blue-600 rounded"></div>
                        <h2 class="text-lg font-semibold text-gray-900">Today's Events</h2>
                    </div>

                    <button type="button" x-data
                        @click="if(window.Livewire) { window.Livewire.dispatch('openCalendarForm'); } else { setTimeout(() => { if(window.Livewire) window.Livewire.dispatch('openCalendarForm'); }, 100); }"
                        class="flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-3 py-1.5 rounded-lg">
                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="2.5" stroke="currentColor">
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
                                    <svg class="w-3.5 h-3.5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none"
                                        viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
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
                    <button type="button" x-data
                        @click="if(window.Livewire) { window.Livewire.dispatch('openCalendarModal'); } else { setTimeout(() => { if(window.Livewire) window.Livewire.dispatch('openCalendarModal'); }, 100); }"
                        class="text-sm font-medium text-blue-600 hover:underline">
                        See the full calendar
                    </button>
                </div>
            </div>
        </div>

        @livewireStyles
        @livewireScripts

        <script>
            function toggleTasksTodayRow(id) {
                const row = document.getElementById(id);
                if (!row) return;
                row.classList.toggle('hidden');
            }

            function stopAllTimersForEmployee(employeeId) {
                const forms = document.querySelectorAll('.stop-form-' + employeeId);
                if (!forms.length) return;
                if (!confirm('Stop all timers for this employee?')) return;

                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

                const requests = Array.from(forms).map(form => {
                    const action = form.getAttribute('action');
                    const formData = new FormData(form);
                    return fetch(action, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: formData,
                    });
                });

                Promise.all(requests)
                    .then(() => window.location.reload())
                    .catch(() => window.location.reload());
            }

            // Live-updating durations
            function formatDurationSeconds(totalSeconds) {
                const hours = Math.floor(totalSeconds / 3600);
                const minutes = Math.floor((totalSeconds % 3600) / 60);
                const seconds = totalSeconds % 60;
                return `${String(hours).padStart(2, '0')}h:${String(minutes).padStart(2, '0')}m:${String(seconds).padStart(2, '0')}s`;
            }

            function refreshLiveDurations() {
                const nowMs = Date.now();
                document.querySelectorAll('.live-duration').forEach(node => {
                    const paused = node.dataset.paused === '1';
                    const startMs = node.dataset.start ? new Date(node.dataset.start).getTime() : null;
                    const extraSeconds = parseInt(node.dataset['extraSeconds'] || '0', 10);
                    if (!startMs) return;
                    const elapsedSeconds = paused ? 0 : Math.floor((nowMs - startMs) / 1000);
                    const totalSeconds = extraSeconds + elapsedSeconds;
                    node.textContent = formatDurationSeconds(totalSeconds);
                });
            }

            // ===== AUTO-REFRESH FUNCTIONALITY =====
            const AUTO_REFRESH_INTERVAL = 5000; // 5 seconds
            let refreshTimer = null;

            // Function to refresh employee data via AJAX
            async function refreshEmployeeData() {
                console.log('[AUTO-REFRESH] Starting refresh at', new Date().toLocaleTimeString());
                try {
                    const response = await fetch('{{ route("admin.refresh-employees") }}', {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        }
                    });

                    if (!response.ok) throw new Error('Failed to fetch data');

                    const data = await response.json();
                    console.log('[AUTO-REFRESH] Received data for', data.employees.length, 'employees');

                    // Update each employee row with fresh data
                    data.employees.forEach(employee => {
                        updateEmployeeRow(employee);
                    });

                    // Update live durations after data refresh
                    refreshLiveDurations();

                    console.log('[AUTO-REFRESH] Refresh completed successfully');

                } catch (error) {
                    console.error('[AUTO-REFRESH] Error:', error);
                }
            }

            // Function to update a single employee row
            function updateEmployeeRow(employee) {
                const row = document.querySelector(`tr[data-employee-id="${employee.id}"]`);
                if (!row) return;

                // Update only the specific cells
                const cells = {
                    '.cell-current-status': employee.currentStatus,
                    '.cell-current-task': employee.currentTask,
                    '.cell-current-project': employee.currentProject,
                    '.cell-task-progress': employee.taskProgress,
                    '.cell-duration': employee.duration,
                    '.cell-account-status': employee.accountStatus,
                    '.cell-chrono': employee.chrono
                };

                Object.entries(cells).forEach(([selector, html]) => {
                    const cell = row.querySelector(selector);
                    if (cell) {
                        cell.innerHTML = html;
                    }
                });

                // Update task history section (if it exists)
                const historyRow = document.getElementById(`tasks-row-${employee.id}`);
                if (historyRow && employee.taskHistory) {
                    // Check if user is typing in this row (prevent overwriting input)
                    if (historyRow.contains(document.activeElement)) {
                        // console.log(`[AUTO-REFRESH] Skipping history update for employee ${employee.id} due to user focus`);
                        return;
                    }

                    const historyContent = historyRow.querySelector('.space-y-3');
                    if (historyContent) {
                        // Keep the header, update only the task list
                        // The structure is: <div><h4>Header</h4><button>Close</button></div> ... tasks ...
                        // We want to keep the first div (header) and replace the rest

                        // Simple way: Find the header div
                        const headerDiv = historyContent.querySelector('.flex.items-center.justify-between');
                        if (headerDiv) {
                            // Rebuild content: Header + New History HTML
                            historyContent.innerHTML = '';
                            historyContent.appendChild(headerDiv);

                            // Insert the new history HTML after header
                            // Since employee.taskHistory is a string of HTML, we need to parse it or insert adjacent
                            headerDiv.insertAdjacentHTML('afterend', employee.taskHistory);
                        }
                    }
                }
            }

            // Function to refresh projects data
            async function refreshProjectsData() {
                try {
                    const response = await fetch('{{ route("admin.refresh-projects") }}', {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        }
                    });

                    if (!response.ok) throw new Error('Failed to fetch project data');

                    const data = await response.json();
                    if (data.success) {
                        const container = document.getElementById('projects-container');
                        if (container) {
                            container.innerHTML = data.html;
                        }
                    }

                } catch (error) {
                    console.error('[AUTO-REFRESH] Projects Error:', error);
                }
            }

            // Start auto-refresh
            function startAutoRefresh() {
                refreshEmployeeData();
                refreshProjectsData();
                refreshTimer = setInterval(() => {
                    refreshEmployeeData();
                    refreshProjectsData();
                }, AUTO_REFRESH_INTERVAL);
            }

            // Stop auto-refresh
            function stopAutoRefresh() {
                if (refreshTimer) {
                    clearInterval(refreshTimer);
                    refreshTimer = null;
                }
            }

            document.addEventListener('DOMContentLoaded', () => {
                refreshLiveDurations();
                setInterval(refreshLiveDurations, 1000);

                // Start auto-refresh
                startAutoRefresh();

                // Universal AJAX handler for ALL dashboard forms (start, stop, activate, deactivate, archive, etc.)
                document.addEventListener('submit', async (e) => {
                    const form = e.target;

                    // Check if this form is inside the employees table or task history
                    const isEmployeeTableForm = form.closest('#employeesTable') || form.classList.contains('history-start-form');

                    if (!isEmployeeTableForm) {
                        return; // Not a dashboard form, let it submit normally
                    }

                    e.preventDefault(); // Prevent page reload
                    e.stopPropagation(); // Stop event from bubbling

                    const submitButton = form.querySelector('button[type="submit"]');
                    if (!submitButton) return;

                    const originalText = submitButton.textContent.trim();
                    const originalClasses = submitButton.className;

                    // Prevent double submission
                    if (submitButton.disabled) return;

                    // Handle confirmation dialogs
                    const confirmMessage = form.getAttribute('data-confirm');
                    if (confirmMessage) {
                        if (!confirm(confirmMessage)) {
                            return; // User cancelled
                        }
                    }
                    // Fallback for forms still using onsubmit (if any)
                    else if (form.getAttribute('onsubmit') && form.getAttribute('onsubmit').includes('confirm')) {
                        const onsubmit = form.getAttribute('onsubmit');
                        const confirmMatch = onsubmit.match(/confirm\(['"](.+?)['"]\)/);
                        if (confirmMatch) {
                            // This path shouldn't be taken for forms where we removed onsubmit
                            // But keeping it for other forms
                            if (!confirm(confirmMatch[1])) {
                                return; // User cancelled
                            }
                        }
                    }

                    try {
                        // Disable button and show loading state
                        submitButton.disabled = true;
                        const loadingText = originalText === 'Start' ? 'Starting...' :
                            originalText === 'Stop' ? 'Stopping...' :
                                originalText === 'Active' ? 'Updating...' :
                                    originalText === 'Inactive' ? 'Updating...' :
                                        originalText === 'Archive' ? 'Archiving...' : 'Processing...';
                        submitButton.textContent = loadingText;

                        const formData = new FormData(form);
                        const response = await fetch(form.action, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                            }
                        });

                        if (response.ok) {
                            // Clear comment field if it exists
                            const commentField = form.querySelector('[name="comment"]');
                            if (commentField) commentField.value = '';

                            // Trigger immediate refresh to show updated data
                            await refreshEmployeeData();

                            // Show success feedback
                            const successText = originalText === 'Start' ? 'Started!' :
                                originalText === 'Stop' ? 'Stopped!' :
                                    originalText === 'Active' ? 'Deactivated!' :
                                        originalText === 'Inactive' ? 'Activated!' :
                                            originalText === 'Archive' ? 'Archived!' : 'Done!';

                            submitButton.textContent = successText;
                            submitButton.classList.add('bg-green-600');

                            setTimeout(() => {
                                submitButton.textContent = originalText;
                                submitButton.className = originalClasses;
                                submitButton.disabled = false;
                            }, 1500);
                        } else {
                            throw new Error('Request failed');
                        }
                    } catch (error) {
                        console.error('AJAX form submission error:', error);
                        submitButton.textContent = 'Error';
                        submitButton.classList.add('bg-red-600');

                        setTimeout(() => {
                            submitButton.textContent = originalText;
                            submitButton.className = originalClasses;
                            submitButton.disabled = false;
                        }, 2000);
                    }
                });




                // Pause refresh when page is not visible
                document.addEventListener('visibilitychange', () => {
                    if (document.hidden) {
                        stopAutoRefresh();
                    } else {
                        startAutoRefresh();
                    }
                });

                // Debug: Detect if something is trying to navigate away
                window.addEventListener('beforeunload', (e) => {
                    console.warn('[DEBUG] Page is about to unload/reload!');
                    console.trace('[DEBUG] Unload stack trace');
                });

            });

            // Clean up on page unload
            window.addEventListener('beforeunload', () => {
                stopAutoRefresh();
            });
        </script>