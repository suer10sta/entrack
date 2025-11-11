<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BizManager Dashboard</title>
    @vite('resources/css/app.css')
</head>
<body class="bg-gray-50 flex h-screen overflow-hidden">
    {{-- Sidebar --}}
    <x-dashboard.sidebar />

    <div class="flex flex-col flex-1 overflow-y-auto">
        {{-- Navbar --}}
        <x-dashboard.navbar />

        {{-- Main Content --}}
        <main class="flex-1 overflow-y-auto p-6">
            <div class="max-w-7xl mx-auto space-y-6">
                
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

                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4">
                        <h1 class="text-2xl font-bold text-gray-900 mb-2 sm:mb-0">{{ $project->name }}</h1>
                        <a href="{{ route($taskRoutePrefix . 'tasks.create', $project->id) }}"
                            class="inline-block bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors flex-shrink-0">
                            + Add Task
                        </a>
                    </div>

                    <div class="overflow-x-auto whitespace-nowrap py-2 border-t border-gray-200">
                        <div class="flex space-x-8">
                            
                            <div class="flex-shrink-0">
                                <p class="text-sm text-gray-500 mb-1">Budget</p>
                                <p class="text-lg font-semibold text-gray-900">€{{ number_format($project->budget, 2, ',', ' ') }}</p>
                            </div>
                            
                            <div class="flex-shrink-0">
                                <p class="text-sm text-gray-500 mb-1">Unbilled</p>
                                <p class="text-lg font-semibold text-gray-900">€{{ number_format($project->budget - $project->amount_billed, 2, ',', ' ') }}</p>
                            </div>

                            <div class="flex-shrink-0">
                                <p class="text-sm text-gray-500 mb-1">Cost/Hour</p>
                                <p class="text-lg font-semibold text-gray-900">€{{ number_format($project->cost_per_hour, 2, ',', ' ') }}</p>
                            </div>

                            <div class="flex-shrink-0">
                                <p class="text-sm text-gray-500 mb-1">Duration</p>
                                <p class="text-lg font-semibold text-gray-900">{{ $hours }}h:{{ $minutes }}m</p>
                            </div>

                            <div class="flex-shrink-0">
                                <p class="text-sm text-gray-500 mb-1">Est. Hours</p>
                                <p class="text-lg font-semibold text-gray-900">{{ $project->estimated_hours }}h</p>
                            </div>

                            <div class="flex-shrink-0">
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

                            <div class="flex-shrink-0">
                                <p class="text-sm text-gray-500 mb-1">TVA</p>
                                <p class="text-lg font-semibold text-gray-900">{{ $project->tva }}%</p>
                            </div>

                            <div class="flex-shrink-0">
                                <p class="text-sm text-gray-500 mb-1">Client</p>
                                <p class="text-lg font-semibold text-gray-900">{{ $project->client->name ?? '-' }}</p>
                            </div>

                            <div class="flex-shrink-0">
                                <p class="text-sm text-gray-500 mb-1">Teams</p>
                                <p class="text-lg font-semibold text-gray-900">
                                    @forelse($projectTeams as $project_team)
                                        {{ $project_team->team_name }}@if(!$loop->last), @endif
                                    @empty
                                        -
                                    @endforelse
                                </p>
                            </div>

                            <div class="flex-shrink-0">
                                <p class="text-sm text-gray-500 mb-1">Progress</p>
                                <p class="text-lg font-semibold text-gray-900">{{ $formattedProgressPercentage }}%</p>
                                <div class="mt-1 w-32 h-2 bg-gray-200 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full @if($progressPercentage < 50) bg-green-500 @elseif($progressPercentage < 75) bg-yellow-500 @else bg-red-500 @endif" 
                                         style="width: {{ min($formattedProgressPercentage, 100) }}%">
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>


                @php
                    // --- Task Section Logic (Performance) ---
                    $current_employee_id = \App\Models\Employees::where('user_id', Auth::user()->id)->first()?->id;
                    $taskIds = $tasks->pluck('id');
                    $allTaskEmployees = \App\Models\employees_tasks::whereIn('task_id', $taskIds)->get()->groupBy('task_id');
                    $allEmployeeDetails = \App\Models\Employees::whereIn('id', $allTaskEmployees->flatten()->pluck('employee_id'))->get()->keyBy('id');
                    $allTaskTimers = \App\Models\tasks_timer::whereIn('task_id', $taskIds)->get();
                    $allTaskTeams = \App\Models\team_tasks::whereIn('task_id', $taskIds)->get()->groupBy('task_id');
                    $allTeamDetails = \App\Models\Teams::whereIn('id', $allTaskTeams->flatten()->pluck('team_id'))->get()->keyBy('id');
                    $activeTimers = $allTaskTimers->where('employee_id', $current_employee_id)->whereNull('end_date')->keyBy('task_id');
                @endphp
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-center mb-4">
                        <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        <h2 class="text-lg font-semibold text-gray-900">Tasks</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-gray-200">
                                    <th class="py-3 px-4 text-xs font-medium text-gray-500 uppercase tracking-wider text-left">Task</th>
                                    <th class="py-3 px-4 text-xs font-medium text-gray-500 uppercase tracking-wider text-left">Description</th>
                                    <th class="py-3 px-4 text-xs font-medium text-gray-500 uppercase tracking-wider text-left">Employees</th>
                                    <th class="py-3 px-4 text-xs font-medium text-gray-500 uppercase tracking-wider text-left">Duration</th>
                                    <th class="py-3 px-4 text-xs font-medium text-gray-500 uppercase tracking-wider text-left">Total</th>
                                    <th class="py-3 px-4 text-xs font-medium text-gray-500 uppercase tracking-wider text-left">Estimated</th>
                                    <th class="py-3 px-4 text-xs font-medium text-gray-500 uppercase tracking-wider text-left">Difference</th>
                                    <th class="py-3 px-4 text-xs font-medium text-gray-500 uppercase tracking-wider text-left">Progress</th>
                                    <th class="py-3 px-4 text-xs font-medium text-gray-500 uppercase tracking-wider text-left">Teams</th>
                                    <th class="py-3 px-4 text-xs font-medium text-gray-500 uppercase tracking-wider text-left">Pointage</th>
                                    <th class="py-3 px-4 text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
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
                                <tr class="border-b border-gray-100 hover:bg-gray-50">
                                    <td class="py-4 px-4 text-sm text-gray-900">{{ $task->title }}</td>
                                    <td class="py-4 px-4 text-sm text-gray-600 max-w-xs truncate">{{ $task->description }}</td>
                                    <td class="py-4 px-4 text-sm text-gray-900">
                                        @foreach($employees as $employee)
                                            <span class="inline-block bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs mb-1">{{ $employee->name }}</span><br>
                                        @endforeach
                                    </td>
                                    <td class="py-4 px-4 text-sm text-gray-900">
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
                                    <td class="py-4 px-4 text-sm text-gray-900">{{ $taskHours }}h:{{ $taskMinutes }}m</td>
                                    <td class="py-4 px-4 text-sm text-gray-900">{{ $task->estimated_hours }}h</td>
                                    <td class="py-4 px-4 text-sm">
                                        @if($taskDifference > 0)
                                            <span class="text-green-600">+{{ $diffHours }}h:{{ $diffMinutes }}m</span>
                                        @elseif($taskDifference < 0)
                                            <span class="text-red-600">-{{ $diffHours }}h:{{ $diffMinutes }}m</span>
                                        @else
                                            <span class="text-gray-600">0h:0m</span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-4">
                                        <div class="w-24 h-2 bg-gray-200 rounded-full overflow-hidden">
                                            <div class="h-full rounded-full @if($taskProgress < 50) bg-green-500 @elseif($taskProgress < 75) bg-yellow-500 @else bg-red-500 @endif" style="width: {{ min($taskProgressFormatted, 100) }}%"></div>
                                        </div>
                                        <span class="text-xs text-gray-700 mt-1">{{ $taskProgressFormatted }}%</span>
                                    </td>
                                    <td class="py-4 px-4 text-sm text-gray-900">
                                        @foreach($teamTasks as $teamTask)
                                            {{ $allTeamDetails->get($teamTask->team_id)?->name }}<br>
                                        @endforeach
                                    </td>
                                    <td class="py-4 px-4 text-sm text-gray-900">
                                        @if($employee_task)
                                            @if(!$task_timer)
                                                <form class="flex flex-col gap-2" action="{{ route($taskRoutePrefix . 'tasks.start', ['tasks' => $employee_task->id, 'employee_id' => $current_employee_id]) }}" method="POST">
                                                    @csrf
                                                    <textarea name="comment" class="px-2 py-1 text-xs border border-gray-300 rounded resize-y" placeholder="Comment" style="min-height: 60px; width: 180px;"></textarea>
                                                    <button class="px-3 py-1.5 text-xs font-medium text-white bg-green-600 rounded hover:bg-green-700" type="submit">Start</button>
                                                </form>
                                            @else
                                                <form class="mt-2" action="{{ route($taskRoutePrefix . 'tasks.stop', ['tasks' => $employee_task->id, 'employee_id' => $current_employee_id]) }}" method="POST">
                                                    @csrf
                                                    <button class="px-3 py-1.5 text-xs font-medium text-white bg-red-600 rounded hover:bg-red-700" type="submit">Stop</button>
                                                </form>
                                            @endif
                                        @endif
                                    </td>
                                    <td class="py-4 px-4">
                                        <div class="flex items-center justify-center gap-3">
                                            @can('Update task')
                                            <a href="{{ route($taskRoutePrefix . 'tasks.edit', $task->id) }}" class="text-blue-600 hover:text-blue-800" title="Edit Task">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </a>
                                            @endcan
                                            @can('Delete task')
                                            <form method="post" action="{{ route($taskRoutePrefix . 'tasks.destroy', $task->id) }}" onsubmit="return confirm('Are you sure?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-800" title="Delete Task">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
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

                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-center mb-4">
                        <svg class="w-5 h-5 text-blue-500 mr-2" fill="currentColor" viewBox="0 0 20 20"><path d="M9 2a2 2 0 00-2 2v8a2 2 0 002 2h6a2 2 0 002-2V6.414A2 2 0 0016.414 5L14 2.586A2 2 0 0012.586 2H9z"/><path d="M3 8a2 2 0 012-2v10h8a2 2 0 01-2 2H5a2 2 0 01-2-2V8z"/></svg>
                        <h2 class="text-lg font-semibold text-gray-900">Invoices</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-gray-200">
                                    <th class="py-3 px-4 text-xs font-medium text-gray-500 uppercase tracking-wider text-left">Invoice N</th>
                                    <th class="py-3 px-4 text-xs font-medium text-gray-500 uppercase tracking-wider text-left">Project Name</th>
                                    <th class="py-3 px-4 text-xs font-medium text-gray-500 uppercase tracking-wider text-left">TVA</th>
                                    <th class="py-3 px-4 text-xs font-medium text-gray-500 uppercase tracking-wider text-left">Client Name</th>
                                    <th class="py-3 px-4 text-xs font-medium text-gray-500 uppercase tracking-wider text-left">Type</th>
                                    <th class="py-3 px-4 text-xs font-medium text-gray-500 uppercase tracking-wider text-left">Created At</th>
                                    <th class="py-3 px-4 text-xs font-medium text-gray-500 uppercase tracking-wider text-left">Cost Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($invoices as $invoice)
                                <tr class="border-b border-gray-100 hover:bg-gray-50">
                                    <td class="py-4 px-4 text-sm text-gray-900">{{ $invoice->number }}</td>
                                    <td class="py-4 px-4 text-sm text-gray-900">{{ $invoice->project_name }}</td>
                                    <td class="py-4 px-4 text-sm text-gray-900">{{ $invoice->tva }}%</td>
                                    <td class="py-4 px-4 text-sm text-gray-900">{{ $invoice->client_name }}</td>
                                    <td class="py-4 px-4 text-sm text-gray-600">{{ $invoice->type }}</td>
                                    <td class="py-4 px-4 text-sm text-gray-600">{{ $invoice->created_at->format('Y-m-d') }}</td>
                                    <td class="py-4 px-4 text-sm font-semibold text-gray-900">€{{ number_format($invoice->total, 2) }}</td>
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

                
                @can('view devis')
                @if($devis !== null)
                @php
                    // --- Devis Section Logic ---
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
                <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-lg font-semibold text-gray-900">Devis</h2>
                        <div class="text-right">
                            <label class="text-sm font-medium text-gray-700">Total Accepted: €{{ number_format($Total_Accpeted, 2, ',', ' ') }}</label>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-gray-200">
                                    <th class="py-3 px-4 text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Devis N</th>
                                    <th class="py-3 px-4 text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Client / Emails</th>
                                    <th class="py-3 px-4 text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Budget</th>
                                    <th class="py-3 px-4 text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Unbilled</th>
                                    <th class="py-3 px-4 text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Created at</th>
                                    <th class="py-3 px-4 text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Status</th>
                                    <th class="py-3 px-4 text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($devis as $d)
                                <tr class="border-b border-gray-100 hover:bg-gray-50">
                                    <td class="py-4 px-4 text-center">
                                        <p class="text-sm font-semibold text-blue-600">
                                            <a href="{{ route($devisRoutePrefix . 'devis.downloadDevis', $d->devis_number) }}" class="hover:underline">
                                                {{$entreprise_infos->devis_prefix}}-{{$d->devis_number}}
                                            </a>
                                        </p>
                                    </td>
                                    <td class="py-4 px-4 text-center">
                                        <p class="text-sm text-gray-900">
                                            @php $client = $devisClients->get($d->client_id); @endphp
                                            @if (!$client)
                                                @foreach (explode(',', $d->emails) as $email)
                                                    {{ $email }}<br />
                                                @endforeach
                                            @else
                                                {{$client->name}}
                                            @endif
                                        </p>
                                    </td>
                                    <td class="py-4 px-4 text-center">
                                        <p class="text-sm font-medium text-gray-900">€{{ number_format($d->budget, 2, ',', ' ') }}</p>
                                    </td>
                                    <td class="py-4 px-4 text-center">
                                        @php
                                            $totalBilled = $devisInvoicesTotal->get($d->id)?->total ?? 0;
                                            $unbilledBudget = ($d->budget ?? 0) - $totalBilled;
                                        @endphp
                                        <p class="text-sm font-medium text-gray-900">€{{ number_format(max($unbilledBudget, 0), 2, ',', ' ') }}</p>
                                    </td>
                                    <td class="py-4 px-4 text-center">
                                        <p class="text-sm text-gray-700">{{$d->created_at->format('Y-m-d')}}</p>
                                    </td>
                                    <td class="py-4 px-4 text-center">
                                        @if($d->acceptanceStatus == 1)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">Accepted</span>
                                        @elseif($d->acceptanceStatus == 2)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">Declined</span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">Pending</span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-4">
                                        <div class="flex flex-wrap items-center justify-center gap-3">
                                            <a href="{{ route($devisRoutePrefix . 'devis.downloadDevis', $d->devis_number) }}" class="text-blue-600 hover:text-blue-800" title="Download">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                                </svg>
                                            </a>
                                            @can('Send devis')
                                            <form action="{{ route($devisRoutePrefix . 'devis.sendDevis') }}" method="post" class="inline-block">
                                                @csrf
                                                <input type="hidden" name="devis_id" value="{{ $d->id }}">
                                                <button type="submit" class="text-green-600 hover:text-green-800" title="Send">
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
                                                <button type="submit" class="text-red-600 hover:text-red-800" title="Delete">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </form>
                                            @endcan
                                            <a href="{{route($devisRoutePrefix . 'devis.editDevis', $d->id)}}" class="text-cyan-600 hover:text-cyan-800" title="Edit">
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

                
                @php
                    $fileRoutePrefix = 'admin.'; // Default
                    if (Auth::user()->hasRole('Responsable de projet')) $fileRoutePrefix = 'Responsable_de_projet.';
                    elseif (Auth::user()->hasRole('Employee')) $fileRoutePrefix = 'employee.';
                @endphp
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-center mb-4">
                        <span class="text-lg mr-2">📁</span>
                        <h2 class="text-lg font-semibold text-gray-900">Project Files</h2> 
                    </div>
                    
                    <div class="mb-6">
                        <form action="{{ route($fileRoutePrefix . 'projects.uploadFile', $project->id) }}" method="POST" enctype="multipart/form-data" class="flex items-center gap-3">
                            @csrf
                            <input type="file" name="file" required class="text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-medium file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200">
                            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded text-sm font-medium transition-colors">Upload</button>
                        </form>
                    </div>
            
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-gray-200">
                                    <th class="py-3 px-4 text-xs font-medium text-gray-500 uppercase tracking-wider text-left">File Name</th>
                                    <th class="py-3 px-4 text-xs font-medium text-gray-500 uppercase tracking-wider text-left">Uploaded At</th>
                                    <th class="py-3 px-4 text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($Project_files as $file)
                                <tr class="border-b border-gray-100 hover:bg-gray-50">
                                    <td class="py-4 px-4 text-sm text-gray-900">
                                        <a href="{{ route($fileRoutePrefix . 'projects.downloadfiles', $file->file_name) }}" class="text-blue-600 hover:text-blue-800">{{ $file->file_name }}</a>
                                    </td>
                                    <td class="py-4 px-4 text-sm text-gray-600">
                                        {{ $file->created_at->format('Y-m-d H:i') }}
                                    </td>
                                    <td class="py-4 px-4">
                                        <div class="flex items-center justify-center gap-4">
                                            <a href="{{ route($fileRoutePrefix . 'projects.downloadfiles', $file->file_name) }}" class="text-blue-600 hover:text-blue-800" title="Download">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                                </svg>
                                            </a>
                                            <form action="{{ route($fileRoutePrefix . 'projects.deletefiles', $file->file_name) }}" method="POST" onsubmit="return confirm('Are you sure?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-800" title="Delete">
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

            </div>
        </main>
    </div>
    @livewireScripts
</body>
</html>