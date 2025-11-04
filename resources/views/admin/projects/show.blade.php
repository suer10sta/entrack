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

        <div class="min-h-screen bg-gray-50 p-6">
    <div class="max-w-82xl mx-auto space-y-6">
        
        <!-- Project Header Card -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <h1 class="text-2xl font-bold text-gray-900 mb-4">{{ $project->name }}</h1>
                    <div class="grid grid-cols-9 gap-9">
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Budget</p>
                            <p class="text-lg font-semibold text-gray-900">€{{ number_format($project->budget, 2, ',', ' ') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Unbilled budget</p>
                            <p class="text-lg font-semibold text-gray-900">
                                @if($project->budget) 
                                €{{ number_format($project->budget - $project->amount_billed, 2, ',', ' ') }}
                                @else
                                @endif
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Cost Per Hour</p>
                            <p class="text-lg font-semibold text-gray-900">€{{ number_format($project->cost_per_hour, 2, ',', ' ') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Duration</p>
                            @php
                            $projecttasksId = App\Models\Tasks::where('project_id', $project->id)->pluck('id');
                            $totalduration = App\Models\tasks_timer::whereIn('task_id', $projecttasksId)->sum('duration');
                            $hours = floor($totalduration / 60);
                            $minutes = $totalduration % 60;
                            @endphp
                            <p class="text-lg font-semibold text-gray-900">{{ $hours }}h:{{ $minutes }}m</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Estimated Work Hours</p>
                            <p class="text-lg font-semibold text-gray-900">{{ $project->estimated_hours }}h</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Difference</p>
                            @php
                            $difference = $project->estimated_hours * 60 - $totalduration;
                            $diffhours = floor(abs($difference) / 60);
                            $diffminutes = abs($difference) % 60;
                            @endphp
                            <p class="text-lg font-semibold text-gray-900">
                                @if($difference > 0)
                                <span class="text-green-600">+{{ $diffhours }}h:{{ $diffminutes }}m</span>
                                @elseif($difference < 0)
                                <span class="text-red-600">-{{ $diffhours }}h:{{ $diffminutes }}m</span>
                                @else
                                <span class="text-blue-600">{{ $diffhours }}h:{{ $diffminutes }}m</span>
                                @endif
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 mb-1">TVA</p>
                            <p class="text-lg font-semibold text-gray-900">{{ $project->tva }}%</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Client</p>
                            <p class="text-lg font-semibold text-gray-900">
                                {{ $project->client->name ?? '-' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Teams</p>
                            <p class="text-lg font-semibold text-gray-900">
                                @foreach(App\Models\project_teams::where('project_id', $project->id)->get() as $project_team)
                                {{ $project_team->team_name }}<br>
                                @endforeach
                            </p>
                        </div>
                    </div>
                </div>
                
                @php
                $projecttasksId = App\Models\Tasks::where('project_id', $project->id)->pluck('id');
                $totalduration = App\Models\tasks_timer::whereIn('task_id', $projecttasksId)->sum('duration');
                $estimatedMinutes = $project->estimated_hours * 60;
                $progressPercentage = $estimatedMinutes > 0 ? ($totalduration / $estimatedMinutes) * 100 : 0;
                $formattedProgressPercentage = number_format($progressPercentage, 2);
                $hours = floor($totalduration / 60);
                $minutes = $totalduration % 60;
                $difference = $estimatedMinutes - $totalduration;
                $diffhours = floor(abs($difference) / 60);
                $diffminutes = abs($difference) % 60;
                @endphp

                <div class="ml-8 text-right">
                    <p class="text-sm text-gray-500 mb-2">Progress</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $formattedProgressPercentage }}%</p>
                    <div class="mt-3 w-32 h-2 bg-gray-200 rounded-full overflow-hidden">
                        <div 
                        class="h-full rounded-full @if($progressPercentage < 50) bg-green-500 @elseif($progressPercentage < 75) bg-yellow-500 @else bg-red-500 @endif" 
                        style="width: {{ min($formattedProgressPercentage, 100) }}%">
                    </div>
                </div>
            </div>
        </div>
            <button onclick="
                @if(Auth::user()->hasRole('Responsable de projet'))
                window.location='{{ route('Responsable_de_projet.tasks.create', $project->id) }}';
                @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                window.location='{{ route('Responsable_gestionnaire.tasks.create', $project->id) }}';
                @elseif(Auth::user()->hasRole('Admin'))
                window.location='{{ route('admin.tasks.create', $project->id) }}';
                @elseif(Auth::user()->hasRole('Employee'))
                window.location='{{ route('employee.tasks.create', $project->id) }}';
                @endif "
                class="mt-6 bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors">
                + Add Task
            </button>
        </div>

        <!-- Tasks Section -->
         <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center mb-4">
                <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <h2 class="text-lg font-semibold text-gray-900">Tasks</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-500">Task</th>
                    
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-500">Description</th>
                    
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-500">Employees</th>
                    
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-500">Duration</th>
                    
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-500">Total Duration</th>
                    
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-500">Estimated Hours</th>
                    
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-500">Difference</th>
                    
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-500">Progress</th>
                    
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-500">Teams</th>
                    
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-500">Pointage</th>
                    
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-500">Action</th>
                
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tasks as $task)
                        @php
                            $employeeIds = App\Models\employees_tasks::where('task_id', $task->id)->pluck('employee_id');
    
                            $employees = App\Models\Employees::whereIn('id', $employeeIds)->get();
                            $taskTotalDuration = App\Models\tasks_timer::where('task_id', $task->id)->sum('duration');
                            $taskHours = floor($taskTotalDuration / 60);    
                            $taskMinutes = $taskTotalDuration % 60;
                            $estimatedMinutes = $task->estimated_hours * 60;
                            $taskDifference = $estimatedMinutes - $taskTotalDuration;
                            $diffHours = floor(abs($taskDifference) / 60);
                            $diffMinutes = abs($taskDifference) % 60;
                            $taskProgress = $estimatedMinutes > 0 ? ($taskTotalDuration / $estimatedMinutes) * 100 : 0;
                            $taskProgressFormatted = number_format($taskProgress, 2);
                            $teamTasks = App\Models\team_tasks::where('task_id', $task->id)->get();
                        @endphp
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <!-- 1. Task -->
                            <td class="py-4 px-4 text-sm text-gray-900">{{ $task->title }}</td>
                            <!-- 2. Description -->
                             <td class="py-4 px-4 text-sm text-gray-600">{{ $task->description }}</td>
                             <!-- 3. Employees -->
                              <td class="py-4 px-4 text-sm text-gray-900">
                                @foreach($employees as $employee)
                                <span class="inline-block bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs mb-1">{{ $employee->name }}</span><br>
                                @endforeach
                            </td>
                            <!-- 4. Duration (per Employee with clickable links) -->
                             <td class="py-4 px-4 text-sm text-gray-900">
                                @foreach($employees as $employee)
                                @php
                                $duration = App\Models\tasks_timer::where('task_id', $task->id)
                                ->where('employee_id', $employee->id)
                                ->sum('duration');
                                $h = floor($duration / 60);
                                $m = $duration % 60;
                                @endphp
                                @if(Auth::user()->hasRole('Admin'))
                                <a href="{{ route('admin.tasks.durationInfos', ['employee' => $employee->id, 'task' => $task->id]) }}" class="text-blue-600 hover:text-blue-800">
                                    {{ $h }}h:{{ $m }}m
                                </a>
                                @elseif(Auth::user()->hasRole('Responsable de projet'))
                                <a href="{{ route('Responsable_de_projet.tasks.durationInfos', ['employee' => $employee->id, 'task' => $task->id]) }}" class="text-blue-600 hover:text-blue-800">
                                    {{ $h }}h:{{ $m }}m
                                </a>
                                @elseif(Auth::user()->hasRole('Employee'))
                                <a href="{{ route('employee.tasks.durationInfos', ['employee' => $employee->id, 'task' => $task->id]) }}" class="text-blue-600 hover:text-blue-800">
                                    {{ $h }}h:{{ $m }}m
                                </a>
                                @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                <a href="{{ route('Responsable_gestionnaire.tasks.durationInfos', ['employee' => $employee->id, 'task' => $task->id]) }}" class="text-blue-600 hover:text-blue-800">
                                    {{ $h }}h:{{ $m }}m
                                </a>
                                @endif
                                <br>
                                @endforeach
                            </td>
                            <!-- 5. Total Duration -->
                            <td class="py-4 px-4 text-sm text-gray-900">{{ $taskHours }}h:{{ $taskMinutes }}m</td>
                             <!-- 6. Estimated Hours -->
                            <td class="py-4 px-4 text-sm text-gray-900">{{ $task->estimated_hours }}h</td>
                              <!-- 7. Difference -->
                            <td class="py-4 px-4 text-sm text-gray-900">
                                @if($taskDifference > 0)
                                <span class="text-green-600">+{{ $diffHours }}h:{{ $diffMinutes }}m</span>
                                @elseif($taskDifference < 0)
                                <span class="text-red-600">-{{ $diffHours }}h:{{ $diffMinutes }}m</span>
                                @else
                                <span class="text-blue-600">0h:0m</span>
                                @endif
                            </td>
                            <!-- 8. Progress -->
                             <td class="py-4 px-4">
                                <div class="w-32 h-2 bg-gray-200 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full 
                                    @if($taskProgress < 50) bg-green-500 
                                    @elseif($taskProgress < 75) bg-yellow-500 
                                    @else bg-red-500 
                                    @endif" 
                                    style="width: {{ min($taskProgressFormatted, 100) }}%">
                                </div>
                            </div>
                            <span class="text-xs text-gray-700 mt-1">{{ $taskProgressFormatted }}%</span>
                        </td>
                        <!-- 9. Teams -->
                         <td class="py-4 px-4 text-sm text-gray-900">
                            @foreach($teamTasks as $teamTask)
                            {{ optional(App\Models\Teams::find($teamTask->team_id))->name }}<br>
                            @endforeach
                        </td>
                        <!-- 10. Pointage (Start/Stop buttons) -->
                        <td class="py-4 px-4 text-sm text-gray-900">
                            @php
                            $current_employee_id = App\Models\Employees::where('user_id', Auth::user()->id)->first()?->id;
                            $task_timer = App\Models\tasks_timer::where('task_id', $task->id)
                            ->where('employee_id', $current_employee_id)
                            ->whereNull('end_date')
                            ->first();
                            $employee_tasks = App\Models\employees_tasks::where('employee_id', $current_employee_id)
                            ->where('task_id', $task->id)
                            ->first();
                            @endphp

                            @if($employee_tasks)
                            @if(!$task_timer)
                            {{-- Start Form with Comment --}}
                
                            @if(Auth::user()->hasRole('Admin'))
                            <form class="flex flex-col gap-2" action="{{ route('admin.tasks.start', ['tasks' => $employee_tasks->id, 'employee_id' => $current_employee_id]) }}" method="POST">
                                @elseif(Auth::user()->hasRole('Responsable de projet'))
                                <form class="flex flex-col gap-2" action="{{ route('Responsable_de_projet.tasks.start', ['tasks' => $employee_tasks->id, 'employee_id' => $current_employee_id]) }}" method="POST">
                                    @elseif(Auth::user()->hasRole('Employee'))
                                    <form class="flex flex-col gap-2" action="{{ route('employee.tasks.start', ['tasks' => $employee_tasks->id, 'employee_id' => $current_employee_id]) }}" method="POST">
                                        @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                        <form class="flex flex-col gap-2" action="{{ route('Responsable_gestionnaire.tasks.start', ['tasks' => $employee_tasks->id, 'employee_id' => $current_employee_id]) }}" method="POST">
                                            @endif
                                            @csrf
                                            <textarea name="comment" class="px-2 py-1 text-xs border border-gray-300 rounded resize-y" placeholder="Comment" style="min-height: 60px; width: 200px;"></textarea>
                                            <button class="px-3 py-1.5 text-xs font-medium text-white bg-green-600 rounded hover:bg-green-700" type="submit">
                                                Start
                                            </button>
                                        </form>
                                        @endif
                                        {{-- Stop Form --}}
                                        @if(Auth::user()->hasRole('Admin'))
                                        <form class="mt-2" action="{{ route('admin.tasks.stop', ['tasks' => $employee_tasks->id, 'employee_id' => $current_employee_id]) }}" method="POST">
                                        @elseif(Auth::user()->hasRole('Responsable de projet'))
                                        <form class="mt-2" action="{{ route('Responsable_de_projet.tasks.stop', ['tasks' => $employee_tasks->id, 'employee_id' => $current_employee_id]) }}" method="POST">
                                        @elseif(Auth::user()->hasRole('Employee'))
                                        <form class="mt-2" action="{{ route('employee.tasks.stop', ['tasks' => $employee_tasks->id, 'employee_id' => $current_employee_id]) }}" method="POST">
                                        @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                        <form class="mt-2" action="{{ route('Responsable_gestionnaire.tasks.stop', ['tasks' => $employee_tasks->id, 'employee_id' => $current_employee_id]) }}" method="POST">
                                        @endif
                                        @csrf
                                    <button class="px-3 py-1.5 text-xs font-medium text-white bg-red-600 rounded hover:bg-red-700" type="submit">
                                        Stop
                                    </button>
                                </form>
                                @endif
                            </td>

    
                            <!-- 11. Action (Edit/Delete buttons) -->
    
                            <td class="py-4 px-4">
                                <div class="flex flex-col gap-2">
                                    @if(Auth::user()->hasRole('Admin'))
                                    @can('Update task')
                                    <a href="{{ route('admin.tasks.edit', $task->id) }}" class="px-3 py-1 text-xs font-medium text-blue-600 hover:text-blue-800">Edit</a>
                                    @endcan
                                    @can('Delete task')
                                    <form method="post" action="{{ route('admin.tasks.destroy', $task->id) }}" onsubmit="return confirm('Are you sure?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-3 py-1 text-xs font-medium text-red-600 hover:text-red-800">Delete</button>
                                    </form>
                                    @endcan
                                    @elseif(Auth::user()->hasRole('Responsable de projet'))
                                    @can('Update task')
                                    <a href="{{ route('Responsable_de_projet.tasks.edit', $task->id) }}" class="px-3 py-1 text-xs font-medium text-blue-600 hover:text-blue-800">Edit</a>
                                    @endcan
                                    @can('Delete task')
                                    <form method="post" action="{{ route('Responsable_de_projet.tasks.destroy', $task->id) }}" onsubmit="return confirm('Are you sure?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-3 py-1 text-xs font-medium text-red-600 hover:text-red-800">Delete</button>
                                    </form>
                                    @endcan
                                    @elseif(Auth::user()->hasRole('Employee'))
                                    @can('Update task')
                                    <a href="{{ route('employee.tasks.edit', $task->id) }}" class="px-3 py-1 text-xs font-medium text-blue-600 hover:text-blue-800">Edit</a>
                                    @endcan
                                    @can('Delete task')
                                    <form method="post" action="{{ route('employee.tasks.destroy', $task->id) }}" onsubmit="return confirm('Are you sure?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-3 py-1 text-xs font-medium text-red-600 hover:text-red-800">Delete</button>
                                    </form>
                                    @endcan
                                    @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                    @can('Update task')
                                    <a href="{{ route('Responsable_gestionnaire.tasks.edit', $task->id) }}" class="px-3 py-1 text-xs font-medium text-blue-600 hover:text-blue-800">Edit</a>
                                    @endcan
                                    @can('Delete task')
                                    <form method="post" action="{{ route('Responsable_gestionnaire.tasks.destroy', $task->id) }}" onsubmit="return confirm('Are you sure?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-3 py-1 text-xs font-medium text-red-600 hover:text-red-800">Delete</button>
                                    </form>
                                    @endcan
                                    @endif
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

        <!-- Invoices Section -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center mb-4">
                <svg class="w-5 h-5 text-blue-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9 2a2 2 0 00-2 2v8a2 2 0 002 2h6a2 2 0 002-2V6.414A2 2 0 0016.414 5L14 2.586A2 2 0 0012.586 2H9z"/>
                    <path d="M3 8a2 2 0 012-2v10h8a2 2 0 01-2 2H5a2 2 0 01-2-2V8z"/>
                </svg>
                <h2 class="text-lg font-semibold text-gray-900">Invoices</h2>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-500">Invoice N</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-500">Project Name</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-500">TVA</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-500">Client Name</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-500">Type</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-500">Created At</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-500">Cost Total</th>
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
        <!-- Devis Section -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-semibold text-gray-900">Devis</h2>
                @php
                    $Total_Accpeted = App\Models\devis_history::where('entreprise_id', Auth::user()->entreprise_id)->where('project_id',$project->id)->where('acceptanceStatus', 1)->sum('budget');
                @endphp
                <div class="text-right">
                    <label class="text-sm font-medium text-gray-700">Total: €{{ number_format($Total_Accpeted, 2, ',', ' ') }}</label>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="text-center py-3 px-4 text-xs font-medium text-gray-500 uppercase">Devis N</th>
                            <th class="text-center py-3 px-4 text-xs font-medium text-gray-500 uppercase">Client Name / Emails</th>
                            <th class="text-center py-3 px-4 text-xs font-medium text-gray-500 uppercase">Budget</th>
                            <th class="text-center py-3 px-4 text-xs font-medium text-gray-500 uppercase">Unbilled Budget</th>
                            <th class="text-center py-3 px-4 text-xs font-medium text-gray-500 uppercase">Created at</th>
                            <th class="text-center py-3 px-4 text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="text-center py-3 px-4 text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($devis as $d)
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="py-4 px-4 text-center">
                                <p class="text-sm font-semibold text-blue-600">
                                    @if(Auth::user()->hasRole('Responsable de projet'))
                                        <a href="{{ route('Responsable_de_projet.devis.downloadDevis', $d->devis_number) }}" class="hover:underline">
                                            {{$entreprise_infos->devis_prefix}}-{{$d->devis_number}}
                                        </a>
                                    @endif
                                    @if(Auth::user()->hasRole('Admin'))
                                        <a href="{{ route('admin.devis.downloadDevis', $d->devis_number) }}" class="hover:underline">
                                            {{$entreprise_infos->devis_prefix}}-{{$d->devis_number}}
                                        </a>
                                    @endif
                                    @if(Auth::user()->hasRole('Employee'))
                                        <a href="{{ route('employee.devis.downloadDevis', $d->devis_number) }}" class="hover:underline">
                                            {{$entreprise_infos->devis_prefix}}-{{$d->devis_number}}
                                        </a>
                                    @endif
                                    @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                        <a href="{{ route('Responsable_gestionnaire.devis.downloadDevis', $d->devis_number) }}" class="hover:underline">
                                            {{$entreprise_infos->devis_prefix}}-{{$d->devis_number}}
                                        </a>
                                    @endif
                                </p>
                            </td>

                            <td class="py-4 px-4 text-center">
                                <p class="text-sm text-gray-900">
                                    @php
                                        $client = \App\Models\Client::where('id', $d->client_id)->first();
                                        $emails = explode(',', $d->emails);
                                    @endphp
                                    @if (empty($client))
                                        @foreach ($emails as $email)
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
                                    $devisInvoicesTotal = \App\Models\invoices_history::where('devis_id', $d->id)
                                        ->sum('total_htva');
                                    $unbilledBudget = ($d->budget ?? 0) - ($devisInvoicesTotal ?? 0);
                                @endphp
                                <p class="text-sm font-medium text-gray-900">€{{ number_format(max($unbilledBudget, 0), 2, ',', ' ') }}</p>
                            </td>

                            <td class="py-4 px-4 text-center">
                                <p class="text-sm text-gray-700">{{$d->created_at}}</p>
                            </td>

                            <td class="py-4 px-4 text-center">
                                @if($d->acceptanceStatus == 1)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                        Accepted
                                    </span>
                                @elseif($d->acceptanceStatus == 2)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                                        Declined
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                                        Pending
                                    </span>
                                @endif
                            </td>

                            <td class="py-4 px-4">
                                <div class="flex flex-wrap justify-center gap-2">
                                    {{-- Admin Actions --}}
                                    @if(Auth::user()->hasRole('Admin'))
                                        <a class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-xs font-medium transition-colors" 
                                           href="{{ route('admin.devis.downloadDevis', $d->devis_number) }}">
                                            Download
                                        </a>
                                        
                                        @can('Send devis')
                                        <form action="{{ route('admin.devis.sendDevis') }}" method="post" class="inline-block">
                                            @csrf
                                            <input type="hidden" name="devis_id" value="{{ $d->id }}">
                                            <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-xs font-medium transition-colors">
                                                Send
                                            </button>
                                        </form>
                                        @endcan
                                        
                                        @can('Delete devis')
                                        <form action="{{route('admin.devis.destroy', $d->id)}}" method="post" 
                                              onsubmit="return confirm('Are you sure?');" class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-xs font-medium transition-colors">
                                                Delete
                                            </button>
                                        </form>
                                        @endcan
                                        
                                        <a href="{{route('admin.devis.editDevis', $d->id)}}" 
                                           class="bg-cyan-500 hover:bg-cyan-600 text-white px-3 py-1 rounded text-xs font-medium transition-colors">
                                            Edit
                                        </a>
                                    @endif

                                    {{-- Responsable de projet Actions --}}
                                    @if(Auth::user()->hasRole('Responsable de projet'))
                                        <a class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-xs font-medium transition-colors" 
                                           href="{{ route('Responsable_de_projet.devis.downloadDevis', $d->devis_number) }}">
                                            Download
                                        </a>
                                        
                                        @can('Send devis')
                                        <form action="{{ route('Responsable_de_projet.devis.sendDevis') }}" method="post" class="inline-block">
                                            @csrf
                                            <input type="hidden" name="devis_id" value="{{ $d->id }}">
                                            <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-xs font-medium transition-colors">
                                                Send
                                            </button>
                                        </form>
                                        @endcan
                                        
                                        @can('Delete devis')
                                        <form action="{{route('Responsable_de_projet.devis.destroy', $d->id)}}" method="post" 
                                              onsubmit="return confirm('Are you sure?');" class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-xs font-medium transition-colors">
                                                Delete
                                            </button>
                                        </form>
                                        @endcan
                                        
                                        <a href="{{route('Responsable_de_projet.devis.editDevis', $d->id)}}" 
                                           class="bg-cyan-500 hover:bg-cyan-600 text-white px-3 py-1 rounded text-xs font-medium transition-colors">
                                            Edit
                                        </a>
                                    @endif

                                    {{-- Employee Actions --}}
                                    @if(Auth::user()->hasRole('Employee'))
                                        <a class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-xs font-medium transition-colors" 
                                           href="{{ route('employee.devis.downloadDevis', $d->devis_number) }}">
                                            Download
                                        </a>
                                        
                                        @can('Send devis')
                                        <form action="{{ route('employee.devis.sendDevis') }}" method="post" class="inline-block">
                                            @csrf
                                            <input type="hidden" name="devis_id" value="{{ $d->id }}">
                                            <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-xs font-medium transition-colors">
                                                Send
                                            </button>
                                        </form>
                                        @endcan
                                        
                                        @can('Delete devis')
                                        <form action="{{route('employee.devis.destroy', $d->id)}}" method="post" 
                                              onsubmit="return confirm('Are you sure?');" class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-xs font-medium transition-colors">
                                                Delete
                                            </button>
                                        </form>
                                        @endcan
                                        
                                        <a href="{{route('employee.devis.editDevis', $d->id)}}" 
                                           class="bg-cyan-500 hover:bg-cyan-600 text-white px-3 py-1 rounded text-xs font-medium transition-colors">
                                            Edit
                                        </a>
                                    @endif

                                    {{-- Responsable de projet et gestionnaire Actions --}}
                                    @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                        <a class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-xs font-medium transition-colors" 
                                           href="{{ route('Responsable_gestionnaire.devis.downloadDevis', $d->devis_number) }}">
                                            Download
                                        </a>
                                        
                                        @can('Send devis')
                                        <form action="{{ route('Responsable_gestionnaire.devis.sendDevis') }}" method="post" class="inline-block">
                                            @csrf
                                            <input type="hidden" name="devis_id" value="{{ $d->id }}">
                                            <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-xs font-medium transition-colors">
                                                Send
                                            </button>
                                        </form>
                                        @endcan
                                        
                                        @can('Delete devis')
                                        <form action="{{route('Responsable_gestionnaire.devis.destroy', $d->id)}}" method="post" 
                                              onsubmit="return confirm('Are you sure?');" class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-xs font-medium transition-colors">
                                                Delete
                                            </button>
                                        </form>
                                        @endcan
                                        
                                        <a href="{{route('Responsable_gestionnaire.devis.editDevis', $d->id)}}" 
                                           class="bg-cyan-500 hover:bg-cyan-600 text-white px-3 py-1 rounded text-xs font-medium transition-colors">
                                            Edit
                                        </a>
                                    @endif
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

        
         <!-- project file Section -->
        <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center mb-4">
                <span class="text-lg mr-2">📁</span>
                <h2 class="text-lg font-semibold text-gray-900">Project Files</h2> 
            </div>

        
            <div class="mb-6">
                <div class="flex flex-wrap gap-3 items-center">
                    @if(Auth::user()->hasRole('Admin'))
                    <form action="{{ route('admin.projects.uploadFile', $project->id) }}" method="POST" enctype="multipart/form-data" class="flex items-center gap-3">
                        @csrf
                    <input type="file" name="file" required
                        class="text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-medium file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200">
                        <button type="submit"
                            class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded text-sm font-medium transition-colors">Upload
                        </button>
                    </form>
                    @endif
                    
                    @if(Auth::user()->hasRole('Responsable de projet'))
                    <form action="{{ route('Responsable_de_projet.projects.uploadFile', $project->id) }}" method="POST" enctype="multipart/form-data" class="flex items-center gap-3">
                        @csrf
                        <input type="file" name="file" required
                        class="text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-medium file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200">
                        <button type="submit"
                            class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded text-sm font-medium transition-colors">Upload
                        </button>
                    </form>
                    @endif
                    
                    @if(Auth::user()->hasRole('Employee'))
                    <form action="{{ route('employee.projects.uploadFile', $project->id) }}" method="POST" enctype="multipart/form-data" class="flex items-center gap-3">
                        @csrf
                        <input type="file" name="file" required
                            class="text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-medium file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200">
                            <button type="submit"
                                class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded text-sm font-medium transition-colors">Upload
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
    
                <!-- Files Table -->
                 <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-200">
                                <th class="text-left py-3 px-4 text-sm font-medium text-gray-500">File Name</th>
                                <th class="text-left py-3 px-4 text-sm font-medium text-gray-500">Uploaded At</th>
                                <th class="text-left py-3 px-4 text-sm font-medium text-gray-500">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($Project_files as $file)
                            <tr class="border-b border-gray-100 hover:bg-gray-50">
                                <td class="py-4 px-4 text-sm text-gray-900">
                           
                                @if(Auth::user()->hasRole('Admin'))
                                    <a href="{{ route('admin.projects.downloadfiles', $file->file_name) }}" class="text-blue-600 hover:text-blue-800">{{ $file->file_name }}</a>
                                    @elseif(Auth::user()->hasRole('Responsable de projet'))
                                    <a href="{{ route('Responsable_de_projet.projects.downloadfiles', $file->file_name) }}" class="text-blue-600 hover:text-blue-800">{{ $file->file_name }}</a>
                                    @elseif(Auth::user()->hasRole('Employee'))
                                    <a href="{{ route('employee.projects.downloadfiles', $file->file_name) }}" class="text-blue-600 hover:text-blue-800">{{ $file->file_name }}</a>
                                    @endif
                                </td>
                                <td class="py-4 px-4 text-sm text-gray-600">
                                    {{ $file->created_at->format('Y-m-d H:i') }}
                                </td>
                                <td class="py-4 px-4 flex flex-wrap gap-2">
                                    
                                <!-- Download -->
                                 @if(Auth::user()->hasRole('Admin'))
                                <a href="{{ route('admin.projects.downloadfiles', $file->file_name) }}"
                                    class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-xs font-medium">Download</a>
                            
                                 @elseif(Auth::user()->hasRole('Responsable de projet'))
                                <a href="{{ route('Responsable_de_projet.projects.downloadfiles', $file->file_name) }}"
                                    class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-xs font-medium">Download</a>
                            
                                   @elseif(Auth::user()->hasRole('Employee'))
                                <a href="{{ route('employee.projects.downloadfiles', $file->file_name) }}"
                                    class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-xs font-medium">Download</a>
                                    @endif
                            
                                <!-- Delete -->
                                @if(Auth::user()->hasRole('Admin'))
                                <form action="{{ route('admin.projects.deletefiles', $file->file_name) }}" method="POST" onsubmit="return confirm('Are you sure?');">
                                    @csrf
                                    @method('DELETE')
                                        <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-xs font-medium">Delete</button>
                                </form>
                            
                                @elseif(Auth::user()->hasRole('Responsable de projet'))
                                <form action="{{ route('Responsable_de_projet.projects.deletefiles', $file->file_name) }}" method="POST" onsubmit="return confirm('Are you sure?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-xs font-medium">Delete</button>
                                </form>
                            
                                @elseif(Auth::user()->hasRole('Employee'))
                                <form action="{{ route('employee.projects.deletefiles', $file->file_name) }}" method="POST" onsubmit="return confirm('Are you sure?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-xs font-medium">Delete</button>
                                </form>
                                @endif
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



        

        {{--
        <!-- Team Sections -->
        <div class="grid grid-cols-2 gap-6">
            <!-- Frontend Team -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center mb-4">
                    <span class="text-lg mr-2">👨‍💻</span>
                    <h2 class="text-lg font-semibold text-gray-900">Frontend Team</h2>
                </div>
                
                <p class="text-sm text-gray-600 mb-6">
                    Team Members: {{ implode(', ', $frontendTeam) }}
                </p>

                <button class="w-full bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded font-medium transition-colors">
                    View
                </button>
            </div>

            <!-- Backend Team -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center mb-4">
                    <span class="text-lg mr-2">🖥️</span>
                    <h2 class="text-lg font-semibold text-gray-900">Backend Team</h2>
                </div>
                
                <p class="text-sm text-gray-600 mb-6">
                    Team Members: {{ implode(', ', $backendTeam) }}
                </p>

                <button class="w-full bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded font-medium transition-colors">
                    View
                </button>
            </div>
        </div>--}}

    </div>
</div>
        
    </div>
    @livewireScripts
</body>
</html>