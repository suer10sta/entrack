<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DalyTrac Dashboard</title>
    @vite('resources/css/app.css')
</head>
<body class="bg-gray-50 flex h-screen overflow-hidden">
    {{-- Sidebar --}}
    @if(Auth::user()->hasRole('Admin'))
        <x-dashboard.sidebar />
    @else
        <x-employees.sidebar />
    @endif
    <div class="flex flex-col flex-1 overflow-y-auto">
        {{-- Navbar --}}
        <x-dashboard.navbar />

        {{-- Contenu Principal --}}
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-[#eaebef] p-4 sm:p-6 lg:p-8">

            <div class="w-full max-w-8xl mx-auto"> 

                {{-- Message d'alerte  --}}
                @if(session('alertmessage'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <strong class="font-bold">{{ __('messages.Error!') }}</strong>
                    <span class="block sm:inline">{{ session('alertmessage') }}</span>
                    <button type="button" onclick="this.parentElement.style.display='none';" class="absolute top-0 bottom-0 right-0 px-4 py-3 font-bold text-xl" aria-label="{{ __('messages.Close') }}">&times;</button>
                </div>
                @endif

                {{-- Carte de la Table --}}
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">{{ __('messages.Task Details') }}</h2>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.Task') }}</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.Teams') }}</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.Description') }}</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.Employees') }}</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.Duration') }}</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.Estimated Hours') }}</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.Difference') }}</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.Progress') }}</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr>
                                    {{-- Tâche --}}
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                        {{$task->title}}
                                    </td>
                                    
                                    {{-- Équipes --}}
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        @php
                                            $teams_id = App\Models\team_tasks::where('task_id', $task->id)->pluck('team_id');
                                            $teams = App\Models\Teams::whereIn('id', $teams_id)->get();
                                        @endphp
                                        @foreach($teams as $team)
                                            <span class="block">{{ $team->name }}</span>
                                        @endforeach
                                    </td>
                                    
                                    {{-- Description --}}
                                    <td class="px-6 py-4 text-sm text-gray-700 max-w-xs truncate">
                                        {{$task->description}}
                                    </td>
                                    
                                    {{-- Employés --}}
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        @php
                                            $employee_id = App\Models\employees_tasks::where('task_id', $task->id)->pluck('employee_id');
                                            // Show all employees assigned to the task (including the current user),
                                            // to be consistent with the project show view.
                                            $employees = App\Models\Employees::whereIn('id', $employee_id)->get();
                                        @endphp
                                        @foreach($employees as $employee)
                                            @php
                                                $employeeShowRoute = '#';
                                                if(Auth::user()->hasRole('Admin') && Auth::user()->can('View employee')) $employeeShowRoute = route('admin.employees.show',$employee->id );
                                                elseif(Auth::user()->hasRole('Responsable de projet') && Auth::user()->can('View employee')) $employeeShowRoute = route('Responsable_de_projet.employees.show',$employee->id );
                                                elseif(Auth::user()->hasRole('Employee') && Auth::user()->can('View employee')) $employeeShowRoute = route('employee.employees.show',$employee->id );
                                                elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire') && Auth::user()->can('View employee')) $employeeShowRoute = route('Responsable_gestionnaire.employees.show',$employee->id );
                                            @endphp
                                            <a href="{{ $employeeShowRoute }}" class="text-blue-600 hover:text-blue-800 block">{{$employee->name}}</a>
                                        @endforeach
                                    </td>
                                    
                                    {{-- Durée par Employé --}}
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-600 font-medium">
                                        @foreach($employees as $employee)
                                            @php
                                                $employeeTaskDuration = App\Models\tasks_timer::where('task_id', $task->id)
                                                    ->where('employee_id', $employee->id)
                                                    ->sum('duration');
                                                $taskhours = floor($employeeTaskDuration / 60);
                                                $taskminutes = $employeeTaskDuration % 60;
                                                
                                                $durationInfoRoute = '#';
                                                if(Auth::user()->hasRole('Admin')) $durationInfoRoute = route('admin.tasks.durationInfos', ['employee' => $employee->id, 'task' => $task->id]);
                                                elseif(Auth::user()->hasRole('Responsable de projet')) $durationInfoRoute = route('Responsable_de_projet.tasks.durationInfos', ['employee' => $employee->id, 'task' => $task->id]);
                                                elseif(Auth::user()->hasRole('Employee')) $durationInfoRoute = route('employee.tasks.durationInfos', ['employee' => $employee->id, 'task' => $task->id]);
                                                elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire')) $durationInfoRoute = route('Responsable_gestionnaire.tasks.durationInfos', ['employee' => $employee->id, 'task' => $task->id]);
                                            @endphp
                                            <a href="{{ $durationInfoRoute }}" class="block hover:underline">
                                                {{$taskhours}}h:{{ str_pad($taskminutes, 2, '0', STR_PAD_LEFT) }}m
                                            </a>
                                        @endforeach
                                    </td>
                                    
                                    {{-- Heures Estimées --}}
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 font-medium">
                                        {{$task->estimated_hours}}h
                                    </td>
                                    
                                    {{-- Différence --}}
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold">
                                        @php
                                            $overallTaskDuration = App\Models\tasks_timer::where('task_id', $task->id)->sum('duration');
                                            $estimatedMinutes = ($task->estimated_hours ?? 0) * 60;
                                            $consumedMinutes = ($task->consumed_hours ?? 0) * 60;
                                            $taskdifference = $estimatedMinutes - $consumedMinutes - $overallTaskDuration;
                                            $hours = floor(abs($taskdifference) / 60);
                                            $minutes = abs($taskdifference) % 60;
                                        @endphp
                                        @if($task->estimated_hours !== null)
                                            @if($taskdifference > 0)
                                                <span class="text-green-600">+{{$hours}}h:{{ str_pad($minutes, 2, '0', STR_PAD_LEFT) }}m</span>
                                            @elseif($taskdifference < 0)
                                                <span class="text-red-600">-{{$hours}}h:{{ str_pad($minutes, 2, '0', STR_PAD_LEFT) }}m</span>
                                            @else
                                                <span class="text-blue-600">{{$hours}}h:{{ str_pad($minutes, 2, '0', STR_PAD_LEFT) }}m</span>
                                            @endif
                                        @endif
                                    </td>
                                    
                                    {{-- Progrès --}}
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 min-w-[150px]">
                                        @php
                                            $taskestimatedMinutes = max(0, (($task->estimated_hours ?? 0) - ($task->consumed_hours ?? 0)) * 60);
                                            $taskprogressPercentage = $taskestimatedMinutes > 0 ? ($overallTaskDuration / $taskestimatedMinutes) * 100 : 0;
                                            $taskformattedProgressPercentage = number_format($taskprogressPercentage, 2);
                                            
                                            $progressColor = 'bg-green-500'; // < 50%
                                            if ($taskprogressPercentage >= 75) $progressColor = 'bg-red-500';
                                            elseif ($taskprogressPercentage >= 50) $progressColor = 'bg-yellow-500';
                                        @endphp
                                        <div class="w-full bg-gray-200 rounded-full h-3 relative">
                                            <div class="absolute inset-0 flex items-center justify-center">
                                                <span class="text-xs font-semibold {{ $taskprogressPercentage > 40 ? 'text-white' : 'text-gray-700' }}">{{$taskformattedProgressPercentage}}%</span>
                                            </div>
                                            <div class="{{ $progressColor }} h-3 rounded-full" style="width: {{ min($taskprogressPercentage, 100) }}%"></div>
                                        </div>
                                    </td>
                                    
                                    {{-- Actions --}}
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                        @php
                                            $editRoute = '#';
                                            $deleteRoute = '#';
                                            if(Auth::user()->hasRole('Admin')) {
                                                $editRoute = route('admin.tasks.edit', $task->id);
                                                $deleteRoute = route('admin.tasks.destroy', $task->id);
                                            } elseif(Auth::user()->hasRole('Responsable de projet')) {
                                                $editRoute = route('Responsable_de_projet.tasks.edit', $task->id);
                                                $deleteRoute = route('Responsable_de_projet.tasks.destroy', $task->id);
                                            } elseif(Auth::user()->hasRole('Employee')) {
                                                $editRoute = route('employee.tasks.edit', $task->id);
                                                $deleteRoute = route('employee.tasks.destroy', $task->id);
                                            } elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire')) {
                                                $editRoute = route('Responsable_gestionnaire.tasks.edit', $task->id);
                                            }
                                        @endphp

                                        @can('Update task')
                                        <a href="{{ $editRoute }}" class="inline-block px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-medium">{{ __('messages.Edit') }}</a>
                                        @endcan
                                        
                                        @can('Delete task')
                                        <form method="post" action="{{ $deleteRoute }}" onsubmit="return confirm('{{ __('messages.Are you sure?') }}');" class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-block px-3 py-1 bg-red-600 hover:bg-red-700 text-white rounded-lg text-xs font-medium">X</button>
                                        </form>
                                        @endcan
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
