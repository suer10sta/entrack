<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Details - DailyTrac</title>
    @vite('resources/css/app.css')
    @livewireStyles
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="//unpkg.com/alpinejs" defer></script>
    <style>
        /* Table Highlight Styles */
        .highlighted-task td { background-color: #eff6ff !important; } /* blue-50 */
        .regie-task td { background-color: #fefce8 !important; } /* yellow-50 */

        /* Clickable Date Styles */
        td[data-field="start_date"],
        td[data-field="end_date"] {
            color: #2563eb !important; /* blue-600 */
            cursor: pointer !important;
            font-weight: 500;
        }
        td[data-field="start_date"]:hover,
        td[data-field="end_date"]:hover {
            color: #1d4ed8 !important; /* blue-800 */
            text-decoration: underline;
        }
        
        /* Table styles */
        .table {
            width: 100%;
        }
        .table td, .table th {
            padding: 0.75rem; /* 12px */
            vertical-align: top; /* Align content to the top */
        }

        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-50 flex h-screen overflow-hidden font-sans" x-data>

    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-NKDMSK6" height="0" width="0"
            style="display: none; visibility: hidden"></iframe></noscript>

    {{-- Sidebar --}}
    <x-dashboard.sidebar />

    {{-- Main Wrapper (Navbar + Content) --}}
    <div class="flex flex-col flex-1 overflow-y-auto">
        {{-- Navbar --}}
        <x-dashboard.navbar />

        {{-- Main Content --}}
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-[#eaebef] p-4 sm:p-6 lg:p-8">
            {{-- [FIX] Using max-w-10xl for "larger" cards and space-y-6 for spacing --}}
            <div class="max-w-10xl mx-auto space-y-6"> 
                
                @php
                    // --- Project Header Logic ---
                    $projecttasksId = \App\Models\Tasks::where('project_id', $project->id)->pluck('id');
                    $totalduration = \App\Models\tasks_timer::whereIn('task_id', $projecttasksId)->sum('duration');
                    $hours = floor($totalduration / 60);
                    $minutes = $totalduration % 60;
                    $estimatedMinutes = $project->estimated_hours * 60;
                    $difference = $estimatedMinutes - $totalduration;
                    $diffhours = floor(abs($difference) / 60);
                    $diffminutes = abs($difference) % 60;
                    $progressPercentage = $estimatedMinutes > 0 ? ($totalduration / $estimatedMinutes) * 100 : 0;
                    $formattedProgressPercentage = number_format($progressPercentage, 2);
                    $projectTeams = \App\Models\project_teams::where('project_id', $project->id)->get();

                    // --- Determine Route Prefixes Once ---
                    $taskRoutePrefix = 'admin.'; // Default
                    if (Auth::user()->hasRole('Responsable de projet')) $taskRoutePrefix = 'Responsable_de_projet.';
                    elseif (Auth::user()->hasRole('Responsable de projet et gestionnaire')) $taskRoutePrefix = 'Responsable_gestionnaire.';
                    elseif (Auth::user()->hasRole('Employee')) $taskRoutePrefix = 'employee.';
                @endphp

                {{-- 1. Project Header Card --}}
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4">
                        <h1 class="text-2xl font-bold text-gray-900 mb-2 sm:mb-0">{{ $project->name }}</h1>
                        @can('Add task')
                        <a href="{{ route($taskRoutePrefix . 'tasks.create', $project->id) }}"
                            class="inline-flex items-center gap-2 bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors flex-shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                            Add Task
                        </a>
                        @endcan
                    </div>

                    <div class="border-t border-gray-200 pt-4">
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 lg:grid-cols-9 gap-x-6 gap-y-4">
                            <div>
                                <p class="text-sm text-gray-500 mb-1">Budget</p>
                                <p class="text-lg font-semibold text-gray-900 editable" data-id="{{$project->id}}" data-column="budget">€{{ number_format($project->budget, 2, ',', ' ') }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 mb-1">Unbilled</p>
                                <p class="text-lg font-semibold text-gray-900">€{{ number_format($project->budget - $project->amount_billed, 2, ',', ' ') }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 mb-1">Cost/Hour</p>
                                <p class="text-lg font-semibold text-gray-900 editable" data-id="{{$project->id}}" data-column="cost_per_hour">€{{ number_format($project->cost_per_hour, 2, ',', ' ') }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 mb-1">Duration</p>
                                <p class="text-lg font-semibold text-gray-900">{{ $hours }}h:{{ $minutes }}m</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 mb-1">Est. Hours</p>
                                <p class="text-lg font-semibold text-gray-900 editable" data-id="{{$project->id}}" data-column="estimated_hours">{{ $project->estimated_hours }}h</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 mb-1">Difference</p>
                                <p class="text-lg font-semibold">
                                    @if($difference > 0)
                                        <span class="text-green-600">+{{ $diffhours }}h:{{ $diffminutes }}m</span>
                                    @elseif($difference < 0)
                                        <span class="text-red-600">-{{ $diffhours }}h:{{ $diffminutes }}m</span>
                                    @else
                                        <span class="text-gray-600">{{ $diffhours }}h:{{ $diffminutes }}m</span>
                                    @endif
                                </p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 mb-1">TVA</p>
                                <p class="text-lg font-semibold text-gray-900 editable" data-id="{{$project->id}}" data-column="tva">{{ $project->tva }}%</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 mb-1">Client</p>
                                <p class="text-lg font-semibold text-gray-900">{{ $project->client->name ?? '-' }}</p>
                            </div>
                            <div class="col-span-2 sm:col-span-1 lg:col-span-1">
                                <p class="text-sm text-gray-500 mb-1">Progress</p>
                                <p class="text-lg font-semibold text-gray-900">{{ $formattedProgressPercentage }}%</p>
                                <div class="mt-1 w-full h-2 bg-gray-200 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full @if($progressPercentage < 50) bg-green-500 @elseif($progressPercentage < 75) bg-yellow-500 @else bg-red-500 @endif" 
                                         style="width: {{ min($formattedProgressPercentage, 100) }}%">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @if(Auth::user()->hasRole('Admin') || Auth::user()->hasRole('Responsable de projet'))
                        @can('Update project')
                            <div class="pt-4 mt-4 border-t border-gray-200 text-right">
                                <a href="{{ route(Auth::user()->hasRole('Admin') ? 'admin.projects.edit' : 'Responsable_de_projet.projects.edit', $project->id) }}" class="inline-flex justify-center bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-lg text-sm shadow-sm">
                                    Edit Project
                                </a>
                            </div>
                        @endcan
                    @endif
                </div>


                {{-- 2. Tasks Table --}}
                @php
                    $current_employee_id = \App\Models\Employees::where('user_id', Auth::user()->id)->first()?->id;
                    $taskIds = $tasks->pluck('id');
                    $allTaskEmployees = \App\Models\employees_tasks::whereIn('task_id', $taskIds)->get()->groupBy('task_id');
                    $allEmployeeDetails = \App\Models\Employees::whereIn('id', $allTaskEmployees->flatten()->pluck('employee_id'))->get()->keyBy('id');
                    $allTaskTimers = \App\Models\tasks_timer::whereIn('task_id', $taskIds)->get();
                    $allTaskTeams = \App\Models\team_tasks::whereIn('task_id', $taskIds)->get()->groupBy('task_id');
                    $allTeamDetails = \App\Models\Teams::whereIn('id', $allTaskTeams->flatten()->pluck('team_id'))->get()->keyBy('id');
                    $activeTimers = $allTaskTimers->where('employee_id', $current_employee_id)->whereNull('end_date')->keyBy('task_id');
                @endphp
                {{-- Card still has overflow-hidden, which is fine as it has no dropdowns --}}
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                        <h2 class="text-lg font-semibold text-gray-900">Tasks</h2>
                        <div class="relative">
                            <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-3 top-2.5 h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 1010.5 18.5a7.5 7.5 0 006.15-3.85z"/></svg>
                            <input type="text" id="taskSearchInput" placeholder="Search tasks..." class="w-full sm:w-64 pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                        </div>
                    </div>
                    
                    {{-- [FIX] Removed overflow-x-auto, table-fixed, and all inline widths --}}
                    <div>
                        <table class="w-full text-left border-collapse" id="taskTable">
                            <thead class="border-b border-gray-200 bg-gray-50">
                                <tr>
                                    {{-- Removed all style="width: ...%" attributes --}}
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600 uppercase tracking-wider text-center">Task</th>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600 uppercase tracking-wider text-center">Description</th>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600 uppercase tracking-wider text-center">Employees</th>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600 uppercase tracking-wider text-center">Duration</th>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600 uppercase tracking-wider text-center">Total</th>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600 uppercase tracking-wider text-center">Estimated</th>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600 uppercase tracking-wider text-center">Difference</th>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600 uppercase tracking-wider text-center">Progress</th>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600 uppercase tracking-wider text-center">Teams</th>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600 uppercase tracking-wider text-center">Pointage</th>
                                    <th class="px-4 py-3 text-center text-sm font-semibold text-gray-600 uppercase tracking-wider text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse($tasks as $task)
                                @php
                                    $taskEmployees = $allTaskEmployees->get($task->id, collect());
                                    $employeeIds = $taskEmployees->pluck('employee_id');
                                    $employees = $allEmployeeDetails->whereIn('id', $employeeIds);
                                    $taskTotalDuration = $allTaskTimers->where('task_id', $task->id)->sum('duration');
                                    $taskHours = floor($taskTotalDuration / 60);     
                                    $taskMinutes = $taskTotalDuration % 60;
                                    $estimatedMinutes = $task->estimated_hours * 60;
                                    $taskDifference = $estimatedMinutes - $taskTotalDuration;
                                    $diffHours = floor(abs($taskDifference) / 60);
                                    $diffMinutes = abs($taskDifference) % 60;
                                    $taskProgress = $estimatedMinutes > 0 ? ($taskTotalDuration / $estimatedMinutes) * 100 : 0;
                                    $taskProgressFormatted = number_format($taskProgress, 2);
                                    $teamTasks = $allTaskTeams->get($task->id, collect());
                                    $task_timer = $activeTimers->get($task->id);
                                    $employee_task = $taskEmployees->where('employee_id', $current_employee_id)->first();
                                @endphp
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-sm text-gray-900 whitespace-normal break-words">
                                        <a href="{{ route('admin.tasks.show', $task->id) }}" class="text-blue-600 hover:underline font-semibold">
                                            {{ $task->title }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600 max-w-xs truncate whitespace-normal break-words">{{ $task->description }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900 whitespace-normal break-words">
                                        @foreach($employees as $employee)
                                            <span class="inline-block bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs mb-1">{{ $employee->name }}</span><br>
                                        @endforeach
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900 whitespace-normal break-words">
                                        @foreach($employees as $employee)
                                            @php
                                                $duration = $allTaskTimers->where('task_id', $task->id)->where('employee_id', $employee->id)->sum('duration');
                                                $h = floor($duration / 60);
                                                $m = $duration % 60;
                                            @endphp
                                            <a href="{{ route($taskRoutePrefix . 'tasks.durationInfos', ['employee' => $employee->id, 'task' => $task->id]) }}" class="text-blue-600 hover:text-blue-800">
                                                {{ $h }}h:{{ $m }}m
                                            </a><br>
                                        @endforeach
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900 whitespace-nowrap">{{ $taskHours }}h:{{ $taskMinutes }}m</td>
                                    <td class="px-4 py-3 text-sm text-gray-900 whitespace-nowrap">{{ $task->estimated_hours }}h</td>
                                    <td class="px-4 py-3 text-sm whitespace-nowrap">
                                        @if($taskDifference > 0)
                                            <span class="text-green-600">+{{ $diffHours }}h:{{ $diffMinutes }}m</span>
                                        @elseif($taskDifference < 0)
                                            <span class="text-red-600">-{{ $diffHours }}h:{{ $diffMinutes }}m</span>
                                        @else
                                            <span class="text-gray-600">0h:0m</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="w-24 h-2 bg-gray-200 rounded-full overflow-hidden">
                                            <div class="h-full rounded-full @if($taskProgress < 50) bg-green-500 @elseif($taskProgress < 75) bg-yellow-500 @else bg-red-500 @endif" style="width: {{ min($taskProgressFormatted, 100) }}%"></div>
                                        </div>
                                        <span class="text-xs text-gray-700 mt-1">{{ $taskProgressFormatted }}%</span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900 whitespace-normal break-words">
                                        @foreach($teamTasks as $teamTask)
                                            {{ $allTeamDetails->get($teamTask->team_id)?->name }}<br>
                                        @endforeach
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900">
                                        @if($employee_task)
                                            @if(!$task_timer)
                                                <form class="flex flex-col gap-2" action="{{ route($taskRoutePrefix . 'tasks.start', ['tasks' => $employee_task->id, 'employee_id' => $current_employee_id]) }}" method="POST">
                                                    @csrf
                                                    {{-- [FIX] rows="1" and new fixed width --}}
                                                    <textarea name="comment" class="block w-full rounded-lg border-gray-300 shadow-sm sm:text-sm py-1 px-2" placeholder="Comment" rows="1" style="width: 130px;"></textarea>
                                                    <button class="px-3 py-1.5 text-xs font-medium text-white bg-green-600 rounded-lg hover:bg-green-700" type="submit">Start</button>
                                                </form>
                                            @else
                                                <form class="mt-2" action="{{ route($taskRoutePrefix . 'tasks.stop', ['tasks' => $employee_task->id, 'employee_id' => $current_employee_id]) }}" method="POST">
                                                    @csrf
                                                    <button class="px-3 py-1.5 text-xs font-medium text-white bg-red-600 rounded-lg hover:bg-red-700" type="submit">Stop</button>
                                                </form>
                                            @endif
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-center gap-3">
                                            @can('Update task')
                                            <a href="{{ route($taskRoutePrefix . 'tasks.edit', $task->id) }}" class="p-1.5 rounded-md bg-blue-600 text-white hover:bg-blue-700" title="Edit Task">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                            </a>
                                            @endcan
                                            @can('Delete task')
                                            <form method="post" action="{{ route($taskRoutePrefix . 'tasks.destroy', $task->id) }}" onsubmit="return confirm('Are you sure?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-1.5 rounded-md bg-red-600 text-white hover:bg-red-700" title="Delete Task">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                </button>
                                            </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="11" class="py-8 text-center text-gray-500">No tasks found</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- 3. Invoices Table --}}
                @can('view invoices')
                @if($invoices !== null)
                {{-- [FIX] Removed overflow-hidden from card to prevent dropdown clipping --}}
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="p-6 border-b border-gray-200 flex flex-col sm:flex-row justify-between items-center">
                        <h2 class="text-lg font-semibold text-gray-900">Invoices</h2>
                        @php
                            $totalPaid = App\Models\invoices_history::where('entreprise_id', Auth::user()->entreprise_id)->where('project_id',$project->id)->where('Payment_status', 1)->sum('total_ttc');
                            $totalUnpaid = App\Models\invoices_history::where('entreprise_id', Auth::user()->entreprise_id)->where('project_id',$project->id)
                                            ->where(function($query) {
                                                $query->where('Payment_status', 0)->orWhereNull('Payment_status');
                                            })->sum('total_ttc');
                            $totaltva = App\Models\invoices_history::where('entreprise_id', Auth::user()->entreprise_id)->where('project_id',$project->id)->sum('total_tva');
                        @endphp
                        <div class="flex flex-wrap gap-x-4 gap-y-1 text-sm mt-2 sm:mt-0">
                            <span>Total Paid: <strong class="text-green-600">€{{ number_format($totalPaid, 2, ',', ' ') }}</strong></span>
                            <span>Total Unpaid: <strong class="text-red-600">€{{ number_format($totalUnpaid, 2, ',', ' ') }}</strong></span>
                            <span>Total TVA: <strong class="text-gray-800">€{{ number_format($totaltva, 2, ',', ' ') }}</strong></span>
                        </div>
                    </div>
                    {{-- [FIX] Removed table-responsive wrapper. Table will squish. --}}
                    <div>
                        <table class="w-full table" id="invoicesTable">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600 uppercase">Invoice N</th>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600 uppercase">TVA</th>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600 uppercase">Client</th>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600 uppercase">Type</th>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600 uppercase">Created At</th>
                                    <th class="px-4 py-3 text-right text-sm font-semibold text-gray-600 uppercase">Cost Total</th>
                                    <th class="px-4 py-3 text-center text-sm font-semibold text-gray-600 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse($invoices as $invoice)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-sm font-medium whitespace-nowrap">
                                        @php $pdfRoute = '#'; @endphp
                                        @if(Auth::user()->hasRole('Responsable de projet')) @php $pdfRoute = route('Responsable_de_projet.invoices.servePdf', ['invoiceName' => $invoice->id]); @endphp @endif
                                        @if(Auth::user()->hasRole('Admin')) @php $pdfRoute = route('admin.invoices.servePdf', ['invoiceName' => $invoice->id]); @endphp @endif
                                        @if(Auth::user()->hasRole('Employee')) @php $pdfRoute = route('employee.invoices.servePdf', ['invoiceName' => $invoice->id]); @endphp @endif
                                        @if(Auth::user()->hasRole('Responsable de projet et gestionnaire')) @php $pdfRoute = route('Responsable_gestionnaire.invoices.servePdf', ['invoiceName' => $invoice->id]); @endphp @endif
                                        
                                        <a href="{{ $pdfRoute }}" target="_blank" class="text-blue-600 hover:text-blue-800">
                                            {{$entreprise_infos->invoice_prefix}}-{{date('ym', strtotime($invoice->created_at))}}{{$invoice->number}}
                                        </a>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900 whitespace-nowrap">
                                        @php $project = App\Models\Project::find($invoice->project_id); @endphp
                                        {{$project->tva ?? 'N/A'}}%
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900 whitespace-normal break-words">{{ $invoice->client_name }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600 whitespace-nowrap">{{ $invoice->type }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600 whitespace-nowrap">{{ $invoice->created_at->format('Y-m-d') }}</td>
                                    <td class="py-4 px-4 text-sm font-semibold text-gray-900 text-right">€{{ number_format($invoice->total_ttc, 2, ',', ' ') }}</td>
                                    <td class="px-7 py-3">
                                        <div class="flex flex-row items-center gap-2">
                                            @php
                                                $isPaid = $invoice->Payment_status == 1;
                                                $toggleRoute = '#';
                                                if(Auth::user()->hasRole('Admin')) $toggleRoute = $isPaid ? route('admin.invoice.deactivate', $invoice->id) : route('admin.invoice.activate', $invoice->id);
                                                elseif(Auth::user()->hasRole('Responsable de projet')) $toggleRoute = $isPaid ? route('Responsable_de_projet.invoice.deactivate', $invoice->id) : route('Responsable_de_projet.invoice.activate', $invoice->id);
                                                elseif(Auth::user()->hasRole('Employee')) $toggleRoute = $isPaid ? route('employee.invoice.deactivate', $invoice->id) : route('employee.invoice.activate', $invoice->id);
                                                elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire')) $toggleRoute = $isPaid ? route('Responsable_gestionnaire.invoice.deactivate', $invoice->id) : route('Responsable_gestionnaire.invoice.activate', $invoice->id);
                                            @endphp
                                            <form action="{{ $toggleRoute }}" method="post" onsubmit="return confirm('Change payment status?');" class="w-full">
                                                @csrf
                                                <button type="submit" title="{{ $isPaid ? 'Mark as Unpaid' : 'Mark as Paid' }}"
                                                        class="payment-button w-full px-2 py-1 rounded-md text-xs font-medium {{ $isPaid ? 'bg-green-600 hover:bg-green-700 text-white' : 'bg-red-600 hover:bg-red-700 text-white' }} whitespace-nowrap">
                                                    {{ $isPaid ? 'Paid' : 'Unpaid' }}
                                                </button>
                                            </form>
                                            
                                            <button type="button" title="{{ $invoice->sentInvoice == 0 ? 'Send Invoice' : 'Resend Invoice' }}"
                                                    class="w-full px-2 py-1 rounded-md bg-gray-600 text-white hover:bg-gray-700 text-xs font-medium whitespace-nowrap"
                                                    @click="$dispatch('open-send-invoice-modal', { invoiceId: {{ $invoice->id }} })">
                                                Send
                                            </button>
                                            
                                            <div x-data="{ open: false }" class="relative w-full">
                                                <button @click="open = !open" type="button" title="Create Credit Note" class="w-full px-2 py-1 rounded-md bg-purple-600 text-white hover:bg-purple-700 text-xs font-medium whitespace-nowrap">
                                                    Credit Note
                                                </button>
                                                <ul x-show="open" @click.outside="open = false" x-transition style="display: none;"
                                                    class="absolute right-0 bottom-full mb-2 w-40 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none z-50">
                                                    @php 
                                                    $creditNoteRoute = ''; $detailedCreditNoteRoute = '';
                                                    if(Auth::user()->hasRole('Admin')) { $creditNoteRoute = 'admin.invoice.creditNote'; $detailedCreditNoteRoute = 'admin.invoice.detailedCreditNote'; }
                                                    elseif(Auth::user()->hasRole('Responsable de projet')) { $creditNoteRoute = 'Responsable_de_projet.invoice.creditNote'; $detailedCreditNoteRoute = 'Responsable_de_projet.invoice.detailedCreditNote'; }
                                                    elseif(Auth::user()->hasRole('Employee')) { $creditNoteRoute = 'employee.invoice.creditNote'; $detailedCreditNoteRoute = 'employee.invoice.detailedCreditNote'; }
                                                    elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire')) { $creditNoteRoute = 'Responsable_gestionnaire.invoice.creditNote'; $detailedCreditNoteRoute = 'Responsable_gestionnaire.invoice.detailedCreditNote'; }
                                                    @endphp
                                                    
                                                    @if($invoice->type == 'Detailed' && $detailedCreditNoteRoute)
                                                        <li><a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" @click.prevent="$dispatch('open-partial-credit-modal', { invoiceId: {{ $invoice->id }}, totalCost: {{ $invoice->total_ttc }} }); open = false;">Partial</a></li>
                                                        <li><a href="{{ route($detailedCreditNoteRoute, $invoice->id) }}?type=full" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Full</a></li>
                                                    @elseif($creditNoteRoute)
                                                        <li><a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" @click.prevent="$dispatch('open-partial-credit-modal', { invoiceId: {{ $invoice->id }}, totalCost: {{ $invoice->total_ttc }} }); open = false;">Partial</a></li>
                                                        <li><a href="{{ route($creditNoteRoute, $invoice->id) }}?type=full" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Full</a></li>
                                                    @endif
                                                </ul>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="py-8 text-center text-gray-500">No invoices found</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif
                @endcan

                
                {{-- 4. Devis Table --}}
                @can('view devis')
                @if($devis !== null)
                @php
                    $Total_Accpeted = \App\Models\devis_history::where('entreprise_id', Auth::user()->entreprise_id)->where('project_id',$project->id)->where('acceptanceStatus', 1)->sum('budget');
                    $devisRoutePrefix = 'admin.';
                    if (Auth::user()->hasRole('Responsable de projet')) $devisRoutePrefix = 'Responsable_de_projet.';
                    elseif (Auth::user()->hasRole('Responsable de projet et gestionnaire')) $devisRoutePrefix = 'Responsable_gestionnaire.';
                    elseif (Auth::user()->hasRole('Employee')) $devisRoutePrefix = 'employee.';
                    $devisClientIds = $devis->pluck('client_id')->filter();
                    $devisClients = \App\Models\Client::whereIn('id', $devisClientIds)->get()->keyBy('id');
                    $devisIds = $devis->pluck('id');
                    $devisInvoicesTotal = \App\Models\invoices_history::whereIn('devis_id', $devisIds)->select('devis_id', \DB::raw('SUM(total_htva) as total'))->groupBy('devis_id')->get()->keyBy('devis_id');
                @endphp
                {{-- [FIX] Removed overflow-hidden from card to prevent dropdown clipping --}}
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="p-6 border-b border-gray-200 flex flex-col sm:flex-row justify-between items-center">
                        <h2 class="text-lg font-semibold text-gray-900">Devis</h2>
                        <div class="text-right mt-2 sm:mt-0">
                            <span class="text-sm font-medium text-gray-700">Total Accepted: </span>
                            <strong class="text-green-600">€{{ number_format($Total_Accpeted, 2, ',', ' ') }}</strong>
                        </div>
                    </div>
                    {{-- [FIX] Removed table-responsive wrapper. Table will squish. --}}
                    <div>
                        <table class="w-full table">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600 uppercase">Devis N</th>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600 uppercase">Client / Emails</th>
                                    <th class="px-4 py-3 text-right text-sm font-semibold text-gray-600 uppercase">Budget</th>
                                    <th class="px-4 py-3 text-right text-sm font-semibold text-gray-600 uppercase">Unbilled</th>
                                    <th class="px-4 py-3 text-center text-sm font-semibold text-gray-600 uppercase">Created at</th>
                                    <th class="px-4 py-3 text-center text-sm font-semibold text-gray-600 uppercase">Status</th>
                                    <th class="px-4 py-3 text-center text-sm font-semibold text-gray-600 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse($devis as $d)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-sm font-semibold whitespace-nowrap">
                                        <a href="{{ route($devisRoutePrefix . 'devis.downloadDevis', $d->devis_number) }}" class="text-blue-600 hover:text-blue-800">
                                            {{$entreprise_infos->devis_prefix}}-{{$d->devis_number}}
                                        </a>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900 whitespace-normal break-words">
                                        @php $client = $devisClients->get($d->client_id); @endphp
                                        @if (!$client)
                                            @foreach (explode(',', $d->emails) as $email)
                                                <span class="block">{{ $email }}</span>
                                            @endforeach
                                        @else
                                            {{$client->name}}
                                        @endif
                                    </td>
                                    <td class="py-4 px-4 text-sm font-medium text-gray-900 text-right">€{{ number_format($d->budget, 2, ',', ' ') }}</td>
                                    <td class="py-4 px-4 text-sm font-medium text-gray-900 text-right">
                                        @php
                                            $totalBilled = $devisInvoicesTotal->get($d->id)?->total ?? 0;
                                            $unbilledBudget = ($d->budget ?? 0) - $totalBilled;
                                        @endphp
                                        €{{ number_format(max($unbilledBudget, 0), 2, ',', ' ') }}
                                    </td>
                                    <td class="py-4 px-4 text-sm text-gray-700 text-center whitespace-nowrap">{{$d->created_at->format('Y-m-d')}}</td>
                                    <td class="py-4 px-4 text-center whitespace-nowrap">
                                        @if($d->acceptanceStatus == 1)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">Accepted</span>
                                        @elseif($d->acceptanceStatus == 2)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">Declined</span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">Pending</span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-4">
                                        <div class="flex items-center justify-center gap-3">
                                            <a href="{{ route($devisRoutePrefix . 'devis.downloadDevis', $d->devis_number) }}" class="p-1 text-blue-600 hover:text-blue-800 rounded-md hover:bg-gray-100" title="Download">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                                </svg>
                                            </a>
                                            @can('Send devis')
                                            <form action="{{ route($devisRoutePrefix . 'devis.sendDevis') }}" method="post" class="inline-block">
                                                @csrf
                                                <input type="hidden" name="devis_id" value="{{ $d->id }}">
                                                <button type="submit" class="p-1 text-green-600 hover:text-green-800 rounded-md hover:bg-gray-100" title="Send">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                                    </svg>
                                                </button>
                                            </form>
                                            @endcan
                                            @can('Delete devis')
                                            <form action="{{route($devisRoutePrefix . 'devis.destroy', $d->id)}}" method="post" onsubmit="return confirm('Are you sure?');" class="inline-block">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-1 text-red-600 hover:text-red-800 rounded-md hover:bg-gray-100" title="Delete">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </form>
                                            @endcan
                                            <a href="{{route($devisRoutePrefix . 'devis.editDevis', $d->id)}}" class="p-1 text-cyan-600 hover:text-cyan-800 rounded-md hover:bg-gray-100" title="Edit">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="py-8 text-center text-gray-500">No devis found</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif
                @endcan
                
                {{-- 5. Project Files Table (Original Logic: Admin/Super Admin only) --}}
                @if(Auth::user()->hasRole('Admin') || Auth::user()->hasRole('Super Admin'))
                @php
                    $fileRoutePrefix = 'admin.';
                @endphp
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Project Files</h2>
                    </div>
                    
                    <div class="p-6">
                        <form action="{{ route($fileRoutePrefix . 'projects.uploadFile', $project->id) }}" method="POST" enctype="multipart/form-data" class="flex items-center gap-3">
                            @csrf
                            <input type="file" name="file" required class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            <button type="submit" class="inline-flex justify-center bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-lg text-sm shadow-sm">Upload</button>
                        </form>
                    </div>
                
                    {{-- [FIX] Removed table-responsive wrapper. Table will squish. --}}
                    <div>
                        <table class="w-full table">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600 uppercase">File Name</th>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600 uppercase">Uploaded At</th>
                                    <th class="px-4 py-3 text-center text-sm font-semibold text-gray-600 uppercase">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse($Project_files as $file)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-sm text-gray-900 whitespace-normal break-words">
                                        <a href="{{ route($fileRoutePrefix . 'projects.downloadfiles', $file->file_name) }}" class="text-blue-600 hover:text-blue-800">{{ $file->file_name }}</a>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600 whitespace-nowrap">
                                        {{ $file->created_at->format('Y-m-d H:i') }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-center gap-4">
                                            <a href="{{ route($fileRoutePrefix . 'projects.downloadfiles', $file->file_name) }}" class="p-1 text-blue-600 hover:text-blue-800 rounded-md hover:bg-gray-100" title="Download">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                                </svg>
                                            </a>
                                            <form action="{{ route($fileRoutePrefix . 'projects.deletefiles', $file->file_name) }}" method="POST" onsubmit="return confirm('Are you sure?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-1 text-red-600 hover:text-red-800 rounded-md hover:bg-gray-100" title="Delete">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="py-8 text-center text-gray-500">No files uploaded</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif

                {{-- 6. Contacts Section (Converted to Alpine.js) --}}
                @can('view contacts')
                @php
                    $project_contacts=App\Models\client_contacts_projects::where('project_id',$project->id)->get();
                    $contactIds = $project_contacts->pluck('contact_id');
                    $contacts = App\Models\client_contacts::whereIn('id', $contactIds)->get();
                @endphp
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Contacts Infos</h2>
                        <p class="text-sm text-gray-600 mt-1">Mandatory informations</p>
                    </div>
                    <div class="divide-y divide-gray-200">
                        @foreach ($contacts as $contact)
                        <div x-data="{ open: false }">
                            {{-- Accordion Header --}}
                            <button @click="open = !open" class="w-full flex justify-between items-center p-6 text-left hover:bg-gray-50">
                                <div class="flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-600 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v11.494m-5.747-8.606l-.001 5.72m11.495-5.72l-.001 5.72M3 10.044v5.72c0 1.25.803 2.335 1.95 2.684A19.46 19.46 0 0012 21c2.148 0 4.217-.36 6.05-1.002 1.147-.35 1.95-1.435 1.95-2.685v-5.72c0-1.25-.803-2.335-1.95-2.684A19.46 19.46 0 0012 6.309a19.463 19.463 0 00-6.05 1.051C4.803 7.71 4 8.794 4 10.044z" /></svg>
                                    <span class="text-base font-semibold text-gray-900">{{ ucfirst($contact->name) }}</span>
                                </div>
                                <svg x-bind:class="{ 'rotate-180': open }" class="h-5 w-5 text-gray-500 transform transition-transform" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                            </button>
                            {{-- Accordion Content --}}
                            <div x-show="open" x-transition class="p-6 bg-gray-50 border-t border-gray-200">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Full Name:</label>
                                        <input class="block w-full rounded-lg border-gray-300 shadow-sm sm:text-sm py-2 px-3 bg-gray-100" type="text" value="{{$contact->name}}" readonly />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Functionality:</label>
                                        <input class="block w-full rounded-lg border-gray-300 shadow-sm sm:text-sm py-2 px-3 bg-gray-100" type="text" value="{{$contact->functionality}}" readonly />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Mobile/Fix:</label>
                                        <input class="block w-full rounded-lg border-gray-300 shadow-sm sm:text-sm py-2 px-3 bg-gray-100" type="text" value="{{$contact->mobile_fix}}" readonly />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Email:</label>
                                        <input class="block w-full rounded-lg border-gray-300 shadow-sm sm:text-sm py-2 px-3 bg-gray-100" type="text" value="{{$contact->email}}" readonly />
                                    </div>
                                    <div class="sm:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Address:</label>
                                        <input class="block w-full rounded-lg border-gray-300 shadow-sm sm:text-sm py-2 px-3 bg-gray-100" type="text" value="{{$contact->address}}" readonly />
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endcan

                {{-- 7. Teams Section --}}
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($teams as $team)
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 flex flex-col">
                        <div class="p-6 border-b border-gray-200 flex justify-between items-start">
                            <div>
                                <h5 class="text-lg font-semibold text-gray-900 capitalize">{{$team->team_name}}</h5>
                                <p class="text-sm font-medium text-gray-500">Team Members:</p>
                            </div>
                            @php $teamRoute = '#'; @endphp
                            @if(Auth::user()->hasRole('Admin')) @php $teamRoute = route('admin.teams.index'); @endphp @endif
                            @if(Auth::user()->hasRole('Responsable de projet')) @php $teamRoute = route('Responsable_de_projet.teams.index'); @endphp @endif
                            @if(Auth::user()->hasRole('Employee')) @php $teamRoute = route('employee.teams.index'); @endphp @endif
                            @if(Auth::user()->hasRole('Responsable de projet et gestionnaire')) @php $teamRoute = route('Responsable_gestionnaire.teams.index'); @endphp @endif
                            <a href="{{ $teamRoute }}" class="inline-flex justify-center bg-blue-600 hover:bg-blue-700 text-white font-medium px-3 py-1 rounded-lg text-xs shadow-sm">View</a>
                        </div>
                        
                        <div class="p-6 space-y-4">
                            @foreach(App\Models\team_members::where('team_id', $team->team_id)->get() as $team_member)
                            @php
                                $employee = App\Models\Employees::where('id', $team_member->employee_id)->first();
                                $employee_user = App\Models\User::where('id', $employee->user_id)->first();
                                $colors = ['#2ECC71', '#BDC3C7', '#F39C12', '#E74C3C', '#9B59B6', '#2E86C1','#641E16','#2C3E50','#F4D03F','#F4D03F'];
                                $color = $colors[$loop->index % count($colors)];
                                $initials = strtoupper(substr($team_member->employee_name, 0, 1));
                                $employeeShowRoute = '#';
                                if(Auth::user()->hasRole('Admin')) $employeeShowRoute = route('admin.employees.show', $team_member->employee_id);
                                elseif(Auth::user()->hasRole('Responsable de projet')) $employeeShowRoute = route('Responsable_de_projet.employees.show', $team_member->employee_id);
                                elseif(Auth::user()->hasRole('Employee')) $employeeShowRoute = route('employee.employees.show', $team_member->employee_id);
                                elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire')) $employeeShowRoute = route('Responsable_gestionnaire.employees.show', $team_member->employee_id);
                            @endphp
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    @if($employee->image)
                                        <img class="h-10 w-10 rounded-full mr-3 object-cover" src="{{ Storage::disk('public')->url('employee_images/' . $employee->image) }}" alt="employee Image">
                                    @else
                                        <div class="h-10 w-10 rounded-full mr-3 flex items-center justify-center text-white font-semibold" style="background-color: {{ $color }};">
                                            <span>{{ $initials }}</span>
                                        </div>
                                    @endif
                                    <div>
                                        <a href="{{ $employeeShowRoute }}" class="text-sm font-semibold text-gray-900 hover:text-blue-600">{{$team_member->employee_name}}</a>
                                        @if($employee_user->is_loggedIn == 0)
                                            <span class="block text-xs text-red-600">Off Work</span>
                                        @elseif($employee_user->is_loggedIn == 1)
                                            <span class="block text-xs text-green-600">On Work</span>
                                        @else
                                            <span class="block text-xs text-yellow-600">On Pause</span>
                                        @endif
                                    </div>
                                </div>
                                @can('View employee')
                                <a href="{{ $employeeShowRoute }}" class="inline-flex justify-center bg-white hover:bg-gray-100 text-gray-700 font-medium px-3 py-1 rounded-lg text-xs border border-gray-300 shadow-sm">View</a>
                                @endcan
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>

            </div>
        </main>
    </div>

    {{-- 1. Edit Date/Time Modal --}}
    <div x-data="editDateModal()" x-cloak x-show="show" 
         @open-edit-date-modal.window="open($event.detail)"
         class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50">
        <div @click.outside="show = false" class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
            <div class="flex items-center justify-between p-4 border-b">
                <h5 class="text-lg font-semibold">Edit Date and Time</h5>
                <button @click="show = false" type="button" class="text-gray-400 hover:text-gray-600">&times;</button>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label for="dateInput" class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                    <input type="date" x-model="date" id="dateInput" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3">
                </div>
                <div>
                    <label for="timeInput" class="block text-sm font-medium text-gray-700 mb-1">Time (HH:MM)</label>
                    <input type="time" x-model="time" id="timeInput" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3">
                </div>
            </div>
            <div class="flex items-center justify-end p-4 bg-gray-50 border-t rounded-b-lg space-x-2">
                <button @click="show = false" type="button" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-100">Close</button>
                <button @click="save()" type="button" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">Save changes</button>
            </div>
        </div>
    </div>

    {{-- 2. Move Duration Modal --}}
    <div x-data="moveDurationModal()" x-cloak x-show="show"
         @open-move-modal.window="open($event.detail)"
         class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50">
        <div @click.outside="show = false" class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
            <div class="flex items-center justify-between p-4 border-b">
                <h5 class="text-lg font-semibold">Move Duration</h5>
                <button @click="show = false" type="button" class="text-gray-400 hover:text-gray-600">&times;</button>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label for="projectSelect" class="block text-sm font-medium text-gray-700 mb-1">Project</label>
                    <select x-model="projectId" @change="loadTasks()" id="projectSelect" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3 form-select-custom">
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}">{{ $project->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="taskSelect" class="block text-sm font-medium text-gray-700 mb-1">Task</label>
                    <select x-model="taskId" id="taskSelect" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3 form-select-custom">
                    </select>
                </div>
                <div>
                    <label for="employeeSelect" class="block text-sm font-medium text-gray-700 mb-1">Employee</label>
                    <select x-model="employeeId" id="employeeSelect" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3 form-select-custom">
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="moveStartDate" class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                    <input type="datetime-local" x-model="startDate" @input="calculateDuration()" id="moveStartDate" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3">
                </div>
                <div>
                    <label for="moveEndDate" class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                    <input type="datetime-local" x-model="endDate" @input="calculateDuration()" id="moveEndDate" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3">
                </div>
                <div>
                    <label for="moveDuration" class="block text-sm font-medium text-gray-700 mb-1">Duration (HH:MM)</label>
                    <input type="text" x-model="duration" id="moveDuration" @input="validateDurationFormat"
                           class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3"
                           placeholder="00h:00m" pattern="^(\d+)h:(\d{2})m$">
                </div>
            </div>
            <div class="flex items-center justify-end p-4 bg-gray-50 border-t rounded-b-lg space-x-2">
                <button @click="show = false" type="button" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-100">Close</button>
                <button @click="saveMove()" type="button" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">Save changes</button>
            </div>
        </div>
    </div>
    
    {{-- 3. Export Report Modal --}}
    <div x-data="exportReportModal()" x-cloak x-show="show"
         @open-export-modal.window="open($event.detail.isCustomer)"
         class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50">
        <div @click.outside="show = false" class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
            <div class="flex items-center justify-between p-4 border-b">
                <h5 class="text-lg font-semibold" x-text="isCustomerReport ? 'Export for Customer' : 'Export Report'"></h5>
                <button @click="show = false" type="button" class="text-gray-400 hover:text-gray-600">&times;</button>
            </div>
            <div class="p-6">
                <p class="text-sm text-gray-700">The report will use the Start Date and End Date you selected. If no dates are selected, all records will be downloaded.</p>
            </div>
            <div class="flex items-center justify-end p-4 bg-gray-50 border-t rounded-b-lg space-x-2">
                <button @click="show = false" type="button" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-100">Cancel</button>
                <button @click="download()" type="button" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">Download PDF</button>
            </div>
        </div>
    </div>
    
    {{-- 4. Send Invoice Modal (for Invoices Table) --}}
    <div x-data="sendInvoiceModal()" x-cloak x-show="show" 
         @open-send-invoice-modal.window="open($event.detail.invoiceId)"
         class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50">
        <div x-show="show" x-transition @click.outside="show = false" class="bg-white rounded-lg shadow-xl w-full max-w-lg mx-4">
            <div class="flex items-center justify-between p-4 border-b">
                <h5 class="text-lg font-semibold">Send Invoice</h5>
                <button @click="show = false" type="button" class="text-gray-400 hover:text-gray-600">&times;</button>
            </div>
            <form id="sendInvoiceForm" @submit.prevent="submitForm">
                @csrf
                <div class="p-6 space-y-4">
                    <div>
                        <h6 class="text-sm font-medium text-gray-700 mb-2">Select Recipients:</h6>
                        <div id="recipientsList" class="space-y-2 p-3 border border-gray-200 rounded-lg bg-gray-50 h-40 overflow-y-auto"></div>
                    </div>
                    <div>
                        <label for="fileUpload" class="block text-sm font-medium text-gray-700 mb-2">Upload Additional Files (Optional):</label>
                        <input type="file" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" id="fileUpload" name="file[]" multiple>
                    </div>
                </div>
                <div class="flex items-center justify-end p-4 bg-gray-50 border-t rounded-b-lg space-x-2">
                    <button @click="show = false" type="button" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-100">Close</button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">Send Invoice</button>
                </div>
            </form>
        </div>
    </div>
    
    {{-- 5. Partial Credit Note Modal (for Invoices Table) --}}
    <div x-data="partialCreditNoteModal()" x-cloak x-show="show" 
         @open-partial-credit-modal.window="open($event.detail.invoiceId, $event.detail.totalCost)"
         class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50">
        <div x-show="show" x-transition @click.outside="show = false" class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
            <div class="flex items-center justify-between p-4 border-b">
                <h5 class="text-lg font-semibold">Partial Credit Note</h5>
                <button @click="show = false" type="button" class="text-gray-400 hover:text-gray-600">&times;</button>
            </div>
            <form @submit.prevent="submitForm">
                <div class="p-6">
                    <label for="partialAmount" class="block text-sm font-medium text-gray-700 mb-2">Enter Amount:</label>
                    <input type="number" x-model="amount" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" id="partialAmount" step="0.0001" required>
                    <span x-show="error" x-text="errorMessage" class="text-red-600 text-sm mt-1"></span>
                </div>
                <div class="flex items-center justify-end p-4 bg-gray-50 border-t rounded-b-lg space-x-2">
                    <button @click="show = false" type="button" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-100">Close</button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">Submit</button>
                </div>
            </form>
        </div>
    </div>

    {{-- SCRIPTS --}}
    @include('dashboard_layouts.scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.3.1/jspdf.umd.min.js"></script>
    <script src="https://unpkg.com/jspdf-autotable@3.5.17/dist/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.4/xlsx.full.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    {{-- Main Page Logic --}}
    <script>
        function editDateModal() {
            return {
                show: false,
                taskId: null,
                field: '',
                date: '',
                time: '',
                open(detail) {
                    this.taskId = detail.taskId;
                    this.field = detail.field;
                    if (detail.datetime) {
                        this.date = detail.datetime.substring(0, 10);
                        this.time = detail.datetime.substring(11, 16);
                    } else {
                        const now = new Date();
                        now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
                        const isoNow = now.toISOString().substring(0, 16);
                        this.date = isoNow.substring(0, 10);
                        this.time = isoNow.substring(11, 16);
                    }
                    this.show = true;
                },
                save() {
                    const newValue = `${this.date} ${this.time}`;
                    let url = '';
                    @if(Auth::user()->hasRole('Admin')) url = `/admin/tasks/update-${this.field}/${this.taskId}`; @endif
                    @if(Auth::user()->hasRole('Responsable de projet')) url = `/Responsable_de_projet/tasks/update-${this.field}/${this.taskId}`; @endif
                    @if(Auth::user()->hasRole('Employee')) url = `/employee/tasks/update-${this.field}/${this.taskId}`; @endif
                    @if(Auth::user()->hasRole('Responsable de projet et gestionnaire')) url = `/Responsable_gestionnaire/tasks/update-${this.field}/${this.taskId}`; @endif

                    $.ajax({
                        url: url, type: 'POST',
                        data: { [this.field]: newValue, _token: '{{ csrf_token() }}' },
                        success: (response) => {
                            if (response.status === 'success') location.reload();
                            else alert(response.message || 'Failed to update.');
                        },
                        error: (xhr) => {
                            let msg = 'Failed to update.';
                            if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                            alert(msg);
                        }
                    });
                    this.show = false;
                }
            }
        }
        
        function moveDurationModal() {
            return {
                show: false,
                originalTaskId: null,
                projectId: '',
                taskId: '',
                employeeId: '',
                startDate: '',
                endDate: '',
                duration: '',
                durationMinutes: 0,
                open(detail) {
                    this.originalTaskId = detail.taskId;
                    this.projectId = detail.projectId;
                    this.employeeId = detail.employeeId;
                    this.startDate = detail.startDate;
                    this.endDate = detail.endDate;
                    this.durationMinutes = detail.duration;
                    this.duration = this.minutesToHoursMinutes(detail.duration);
                    this.loadTasks(detail.taskId);
                    this.show = true;
                },
                loadTasks(selectedTaskId = null) {
                    let url = '';
                    @if(Auth::user()->hasRole('Admin')) url = `/admin/tasks/get-project-tasks/${this.projectId}`; @endif
                    @if(Auth::user()->hasRole('Responsable de projet')) url = `/Responsable_de_projet/tasks/get-project-tasks/${this.projectId}`; @endif
                    @if(Auth::user()->hasRole('Employee')) url = `/employee/tasks/get-project-tasks/${this.projectId}`; @endif
                    @if(Auth::user()->hasRole('Responsable de projet et gestionnaire')) url = `/Responsable_gestionnaire/tasks/get-project-tasks/${this.projectId}`; @endif
                    
                    fetch(url)
                        .then(response => response.json())
                        .then(tasks => {
                            const taskSelect = document.getElementById('taskSelect');
                            taskSelect.innerHTML = '';
                            tasks.forEach(task => {
                                const option = new Option(task.name, task.id);
                                taskSelect.add(option);
                            });
                            this.taskId = selectedTaskId || (tasks.length > 0 ? tasks[0].id : '');
                        });
                },
                calculateDuration() {
                    if (!this.startDate || !this.endDate) return;
                    const start = new Date(this.startDate);
                    const end = new Date(this.endDate);
                    const diffMs = end - start;
                    if (diffMs < 0) {
                        this.duration = '00h:00m'; this.durationMinutes = 0; return;
                    }
                    const minutes = Math.floor(diffMs / 60000);
                    this.durationMinutes = minutes;
                    this.duration = this.minutesToHoursMinutes(minutes);
                },
                validateDurationFormat() {
                    if (/^(\d+)h:(\d{2})m$/.test(this.duration)) {
                        this.durationMinutes = this.hoursMinutesToMinutes(this.duration);
                    }
                },
                saveMove() {
                    if (!this.duration.match(/^(\d+)h:(\d{2})m$/)) {
                        alert('Please enter duration in format (e.g., 01h:30m or 10h:00m)');
                        return;
                    }
                    const formData = {
                        original_task_id: this.originalTaskId, new_task_id: this.taskId,
                        employee_id: this.employeeId, start_date: this.startDate,
                        end_date: this.endDate, duration: this.durationMinutes,
                        _token: '{{ csrf_token() }}'
                    };
                    let url = '';
                    @if(Auth::user()->hasRole('Admin')) url = '/admin/tasks/move-duration'; @endif
                    @if(Auth::user()->hasRole('Responsable de projet')) url = '/Responsable_de_projet/tasks/move-duration'; @endif
                    @if(Auth::user()->hasRole('Employee')) url = '/employee/tasks/move-duration'; @endif
                    @if(Auth::user()->hasRole('Responsable de projet et gestionnaire')) url = '/Responsable_gestionnaire/tasks/move-duration'; @endif

                    $.ajax({
                        url: url, type: 'POST', data: formData,
                        success: (response) => {
                            if (response.status === 'success') location.reload();
                            else alert('Failed: ' + (response.message || 'Unknown error'));
                        },
                        error: (xhr) => alert('Error: ' + xhr.responseText)
                    });
                    this.show = false;
                },
                minutesToHoursMinutes(minutes) {
                    const hours = Math.floor(minutes / 60);
                    const remainingMinutes = minutes % 60;
                    return `${hours.toString().padStart(2, '0')}h:${remainingMinutes.toString().padStart(2, '0')}m`;
                },
                hoursMinutesToMinutes(durationString) {
                    const matches = durationString.match(/^(\d+)h:(\d{2})m$/);
                    if (!matches) return 0;
                    return (parseInt(matches[1]) * 60) + parseInt(matches[2]);
                }
            }
        }

        function exportReportModal() {
            return {
                show: false,
                isCustomerReport: false,
                open(isCustomer) {
                    this.isCustomerReport = isCustomer;
                    this.show = true;
                },
                download() {
                    // This function should be populated based on your export logic
                    alert('Download logic not implemented in Alpine.js modal yet.');
                    this.show = false;
                }
            }
        }
        
        function sendInvoiceModal() {
            return {
                show: false,
                invoiceId: null,
                form: document.getElementById('sendInvoiceForm'),
                open(invoiceId) {
                    this.invoiceId = invoiceId;
                    this.show = true;
                    this.updateFormAction();
                    this.fetchRecipients();
                },
                updateFormAction() {
                    let actionUrl = '';
                    @if(Auth::user()->hasRole('Admin')) actionUrl = '{{ route("admin.invoice.sendInvoice", ":id") }}'; @endif
                    @if(Auth::user()->hasRole('Responsable de projet')) actionUrl = '{{ route("Responsable_de_projet.invoice.sendInvoice", ":id") }}'; @endif
                    @if(Auth::user()->hasRole('Employee')) actionUrl = '{{ route("employee.invoice.sendInvoice", ":id") }}'; @endif
                    @if(Auth::user()->hasRole('Responsable de projet et gestionnaire')) actionUrl = '{{ route("Responsable_gestionnaire.invoice.sendInvoice", ":id") }}'; @endif
                    this.form.action = actionUrl.replace(':id', this.invoiceId);
                },
                fetchRecipients() {
                    let fetchUrl = '';
                    @if(Auth::user()->hasRole('Admin')) fetchUrl = `/admin/invoice/${this.invoiceId}/recipients`; @endif
                    @if(Auth::user()->hasRole('Responsable de projet')) fetchUrl = `/Responsable_de_projet/invoice/${this.invoiceId}/recipients`; @endif
                    @if(Auth::user()->hasRole('Employee')) fetchUrl = `/employee/invoice/${this.invoiceId}/recipients`; @endif
                    @if(Auth::user()->hasRole('Responsable de projet et gestionnaire')) fetchUrl = `/Responsable_gestionnaire/invoice/${this.invoiceId}/recipients`; @endif
                    
                    fetch(fetchUrl)
                        .then(response => response.json())
                        .then(data => {
                            const recipientsList = document.getElementById('recipientsList');
                            recipientsList.innerHTML = '';
                            if (data.recipients && data.recipients.length > 0) {
                                recipientsList.innerHTML += `
                                    <div class="flex items-center">
                                        <input class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" type="checkbox" id="selectAllRecipients">
                                        <label class="ml-2 text-sm font-bold text-gray-900" for="selectAllRecipients">Select All</label>
                                    </div>`;
                                data.recipients.forEach(recipient => {
                                    recipientsList.innerHTML += `
                                        <div class="flex items-center">
                                            <input class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 recipient-checkbox" type="checkbox" 
                                                   name="selected_recipients[]" value="${recipient.email}" id="recipient-${recipient.email}" 
                                                   ${recipient.is_comptable === 1 ? 'checked' : ''}>
                                            <label class="ml-2 text-sm text-gray-700" for="recipient-${recipient.email}">
                                                <strong>${recipient.name}</strong> (${recipient.email})
                                            </label>
                                        </div>`;
                                });
                                document.getElementById('selectAllRecipients').addEventListener('change', function() {
                                    document.querySelectorAll('.recipient-checkbox').forEach(checkbox => {
                                        checkbox.checked = this.checked;
                                    });
                                });
                            } else {
                                recipientsList.innerHTML = '<p class="text-sm text-gray-500">No recipients found</p>';
                            }
                        });
                },
                submitForm() {
                    const selectedRecipients = Array.from(document.querySelectorAll('input[name="selected_recipients[]"]:checked')).map(cb => cb.value);
                    if (selectedRecipients.length === 0) {
                        alert('Please select at least one recipient'); return;
                    }
                    const formData = new FormData(this.form);
                    formData.delete('selected_recipients[]');
                    formData.append('selected_recipients', JSON.stringify(selectedRecipients));

                    fetch(this.form.action, {
                        method: 'POST',
                        body: formData,
                        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }
                    }).then(response => response.json())
                    .then(data => {
                        if (data.success) location.reload();
                        else alert(data.message || 'Error sending invoice');
                    }).catch(error => alert('Error sending invoice.'));
                    this.show = false;
                }
            }
        }
        
        function partialCreditNoteModal() {
            return {
                show: false,
                invoiceId: null,
                totalCost: 0,
                amount: null,
                error: false,
                errorMessage: '',
                open(invoiceId, totalCost) {
                    this.invoiceId = invoiceId;
                    this.totalCost = parseFloat(totalCost);
                    this.amount = null;
                    this.error = false;
                    this.show = true;
                },
                submitForm() {
                    this.error = false;
                    const amount = parseFloat(this.amount);
                    if (isNaN(amount) || amount <= 0) {
                        this.error = true; this.errorMessage = 'Please enter a valid amount > 0.'; return;
                    }
                    if (amount > this.totalCost) {
                        this.error = true; this.errorMessage = 'Amount exceeds total cost.'; return;
                    }
                    let url = '';
                    @if(Auth::user()->hasRole('Admin')) url = `{{ route('admin.invoice.creditNote', '') }}/${this.invoiceId}?type=partial&amount=${amount}`; @endif
                    @if(Auth::user()->hasRole('Responsable de projet')) url = `{{ route('Responsable_de_projet.invoice.creditNote', '') }}/${this.invoiceId}?type=partial&amount=${amount}`; @endif
                    @if(Auth::user()->hasRole('Employee')) url = `{{ route('employee.invoice.creditNote', '') }}/${this.invoiceId}?type=partial&amount=${amount}`; @endif
                    @if(Auth::user()->hasRole('Responsable de projet et gestionnaire')) url = `{{ route('Responsable_gestionnaire.invoice.creditNote', '') }}/${this.invoiceId}?type=partial&amount=${amount}`; @endif
                    
                    fetch(url, {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                    .then(response => response.text())
                    .then(html => {
                        document.open(); document.write(html); document.close();
                    })
                    .catch(error => alert('An error occurred: ' + error.message));
                }
            }
        }

        // --- PAGE SCRIPT LOGIC ---
        document.addEventListener('DOMContentLoaded', function() {
            
            // --- Comment editing (in-place) ---
            document.querySelectorAll('.editable-comment').forEach(function(cell) {
                cell.addEventListener('dblclick', function() {
                    var span = cell.querySelector('.comment-text');
                    if (!span) return;
                    var originalText = span.textContent || '';
                    if (cell.getAttribute('data-editing') === '1') return;
                    cell.setAttribute('data-editing', '1');
                    var input = document.createElement('textarea');
                    input.className = 'block w-full rounded-lg border-gray-300 shadow-sm sm:text-sm py-1 px-2';
                    input.value = originalText;
                    input.rows = 2;
                    span.style.display = 'none';
                    cell.appendChild(input);
                    input.focus();
                    input.select();
                    var save = function(newValue) {
                        var trimmed = (newValue || '').trim();
                        if (trimmed === (originalText || '').trim()) { cancel(); return; }
                        var url = cell.getAttribute('data-update-url');
                        var field = cell.getAttribute('data-field') || 'comment';
                        var token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                        fetch(url, {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': token, 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: `${field}=${encodeURIComponent(trimmed)}`
                        }).then(resp => resp.json())
                        .then(json => {
                            if (json.status === 'success') span.textContent = trimmed;
                            else { span.textContent = originalText; alert(json.message || 'Failed to update'); }
                        }).catch(err => { span.textContent = originalText; alert('Network error'); })
                        .finally(() => cleanup());
                    };
                    var cancel = () => cleanup();
                    var cleanup = () => {
                        if (input && input.parentNode) input.parentNode.removeChild(input);
                        span.style.display = '';
                        cell.removeAttribute('data-editing');
                    };
                    input.addEventListener('keydown', (e) => {
                        if (e.key === 'Enter' && (e.ctrlKey || e.metaKey)) { e.preventDefault(); save(input.value); }
                        else if (e.key === 'Escape') { e.preventDefault(); cancel(); }
                    });
                    input.addEventListener('blur', () => save(input.value));
                });
            });

            // Task search filter
            document.getElementById('taskSearchInput').addEventListener('keyup', function() {
                const searchTerm = this.value.toLowerCase();
                const tableRows = document.querySelectorAll('#taskTable tbody tr');
                
                tableRows.forEach(row => {
                    // [FIX] Changed this condition. The 'data-task-id' attribute didn't exist.
                    // We only need to skip the 'No tasks found' row, which has 1 cell (due to colspan).
                    // All valid task rows have 11 cells.
                    if (row.cells.length < 11) { 
                        return; // Skip 'No tasks found' row
                    }
                    const taskTitle = row.cells[0]?.textContent || '';
                    const taskDescription = row.cells[1]?.textContent || '';
                    
                    if (taskTitle.toLowerCase().includes(searchTerm) || taskDescription.toLowerCase().includes(searchTerm)) {
                        row.style.display = ""; // Show row
                    } else {
                        row.style.display = "none"; // Hide row
                    }
                });
            });

            // [FIX] Removed broken Excel export button code
            
            @can('Update project')
            // In-place editing for project details
            $('#editableTable .editable').dblclick(function(){
                var originalContent = $(this).text().trim();
                var width = $(this).width();
                $(this).html("<input type='text' value='" + originalContent + "' style='width: 100%; min-width: " + width + "px;' class='block rounded-lg border-gray-300 shadow-sm sm:text-sm py-1 px-2' />");
                var input = $(this).children().first();
                input.focus();
                input[0].setSelectionRange(0, 0);

                input.blur(function(){
                    var newContent = $(this).val();
                    var clientId = $(this).parent().data('id');
                    var columnName = $(this).parent().data('column');
                    $(this).parent().text(newContent); 

                    var updatedUrl = '';
                    @if(Auth::user()->hasRole('Responsable de projet'))
                        updatedUrl = '/Responsable_de_projet/projects/updateProject';
                    @elseif(Auth::user()->hasRole('Admin'))
                        updatedUrl = '/admin/projects/updateProject';
                    @endif 

                    $.ajax({
                        url: updatedUrl,
                        type: 'post',
                        data: {
                            content: newContent,
                            id: clientId,
                            column: columnName,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            location.reload();
                        }
                    });
                });
            });
            @endcan

        });
    </script>
</body>
</html>
