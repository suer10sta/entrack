<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tasks - DailyTrac</title> {{-- Updated title --}}
    @vite('resources/css/app.css')
    @livewireStyles
    {{-- Include dashboard_layouts.head if needed --}}
    {{-- @include('dashboard_layouts.head') --}}
</head>
<body class="bg-gray-50 flex h-screen overflow-hidden">

    {{-- Sidebar Component --}}
    <x-dashboard.sidebar />

    {{-- Main Wrapper (Navbar + Content) --}}
    <div class="flex flex-col flex-1 overflow-y-auto">

        {{-- Navbar Component --}}
        <x-dashboard.navbar />

        {{-- Main Content Area --}}
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-8">

            {{-- Container for the table --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">

                {{-- Header Section --}}
                <div class="p-6 border-b border-gray-200">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-4">
                        <h6 class="text-lg font-semibold text-gray-900">Tasks Table</h6>

                        {{-- Buttons --}}
                        <div class="flex flex-wrap gap-2">
                            @php
                                $reportRoute = '#';
                                if (Auth::user()->hasRole('Responsable de projet')) $reportRoute = route('Responsable_de_projet.Tasksrepports.downloadRepports');
                                elseif (Auth::user()->hasRole('Admin')) $reportRoute = route('admin.Tasksrepports.downloadRepports');
                                elseif (Auth::user()->hasRole('Employee')) $reportRoute = route('employee.Tasksrepports.downloadRepports');
                                elseif (Auth::user()->hasRole('Responsable de projet et gestionnaire')) $reportRoute = route('Responsable_gestionnaire.Tasksrepports.downloadRepports');
                            @endphp
                            <a href="{{ $reportRoute }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 border border-gray-300 text-sm font-medium px-4 py-2 rounded-lg flex items-center gap-1.5">
                               <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                                Tasks Reports PDF
                            </a>
                        </div>
                    </div>

                    {{-- Filters --}}
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                        <div class="w-full sm:w-auto">
                            <label for="taskFilter" class="block text-sm font-medium text-gray-700 mb-1">Filter by Task Name</label>
                            <select id="taskFilter" class="mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                                <option value="">All Tasks</option>
                                {{-- Use distinct task titles --}}
                                @foreach($tasks->unique('title') as $task)
                                    <option value="{{ $task->title }}">{{ $task->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="w-full sm:w-auto">
                            <label for="taskSearch" class="block text-sm font-medium text-gray-700 mb-1">Search Task</label>
                            <input type="text" id="taskSearch" class="block w-full sm:w-64 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" placeholder="Type to search...">
                        </div>
                    </div>
                </div>

                {{-- Session Message Alerts --}}
                @if(session('message'))
                    <div class="m-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded relative" role="alert">
                        <span class="block sm:inline">{{ session('message') }}</span>
                        <button type="button" onclick="this.parentElement.style.display='none'" class="absolute top-0 bottom-0 right-0 px-4 py-3 text-green-700 hover:text-green-900 font-bold text-xl" aria-label="Close">
                           &times;
                        </button>
                    </div>
                @endif
                @if(session('alertmessage'))
                    <div class="m-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded relative" role="alert">
                        <span class="block sm:inline">{{ session('alertmessage') }}</span>
                         <button type="button" onclick="this.parentElement.style.display='none'" class="absolute top-0 bottom-0 right-0 px-4 py-3 text-red-700 hover:red-900 font-bold text-xl" aria-label="Close">
                           &times;
                        </button>
                    </div>
                @endif


                {{-- Table Wrapper --}}
                <div class="p-0"> {{-- No padding for edge-to-edge table --}}
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse table-fixed" id="tasksTable">
                            <thead class="border-b border-gray-200 bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-left">Project / Task</th>
                                    <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-left">Teams</th>
                                    <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-left" >Employees</th>
                                    <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-left">Duration</th>
                                    <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-left">Est. Hours</th>
                                    <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-left">Difference</th>
                                    <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-left">Progress</th>
                                    <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-left">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @php $groupedTasks = $tasks->groupBy('project_id'); @endphp

                                @forelse($groupedTasks as $projectId => $projectTasks)
                                    @php
                                        // Eager load project or fetch once
                                        $project = \App\Models\Project::find($projectId);
                                        $firstTask = $projectTasks->first(); // Use first task to get project name if project not found
                                    @endphp
                                    {{-- Project Header Row --}}
                                    <tr class="bg-gray-100 project-header"> {{-- Class for filtering --}}
                                        <td colspan="8" class="px-2 py-2 font-semibold text-gray-800 text-sm break-words">
                                            @php
                                                $projectShowRoute = '#';
                                                $canViewProject = $project && Auth::user()->can('View project'); // Check if project exists
                                                if ($canViewProject) {
                                                    if (Auth::user()->hasRole('Admin')) $projectShowRoute = route('admin.projects.show', $projectId);
                                                    elseif (Auth::user()->hasRole('Responsable de projet')) $projectShowRoute = route('Responsable_de_projet.projects.show', $projectId);
                                                    elseif (Auth::user()->hasRole('Responsable de projet et gestionnaire')) $projectShowRoute = route('Responsable_gestionnaire.projects.show', $projectId);
                                                    elseif (Auth::user()->hasRole('Employee')) $projectShowRoute = route('employee.projects.show', $projectId);
                                                }
                                                $projectName = $project ? $project->name : ($firstTask ? $firstTask->project_name : 'Unknown Project');
                                            @endphp
                                            @if($canViewProject)
                                                <a href="{{ $projectShowRoute }}" class="text-blue-600 hover:underline">
                                                    {{ $projectName }}
                                                </a>
                                            @else
                                                {{ $projectName }}
                                            @endif
                                        </td>
                                    </tr>

                                    {{-- Tasks for this project --}}
                                    @foreach($projectTasks as $task)
                                        <tr class="hover:bg-gray-50 task-row"> {{-- Class for filtering --}}
                                            {{-- Task Title --}}
                                            <td class="px-6 py-4 whitespace-normal break-words text-sm font-medium text-gray-900 task-title"> 
                                                @php
                                                    $taskShowRoute = '#';
                                                    $canViewTask = Auth::user()->can('View task');
                                                     if ($canViewTask) {
                                                        if(auth()->user()->hasRole('Admin')) $taskShowRoute = route('admin.tasks.show', $task->id);
                                                        elseif(auth()->user()->hasRole('Responsable de projet')) $taskShowRoute = route('Responsable_de_projet.tasks.show', $task->id);
                                                        // Add Employee/Resp Gestionnaire if needed
                                                     }
                                                @endphp
                                                 @if($canViewTask)
                                                     <a href="{{ $taskShowRoute }}" class="text-blue-600 hover:underline">
                                                         {{ $task->title }}
                                                     </a>
                                                 @else
                                                      {{ $task->title }}
                                                 @endif
                                            </td>
                                            {{-- Teams --}}
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                 <div class="flex flex-wrap gap-1">
                                                     @php
                                                         // Eager load teams for performance
                                                         $task_teams = App\Models\team_tasks::with('team')->where('task_id', $task->id)->get();
                                                         $teamIndexRoute = '#';
                                                         $canViewTeam = Auth::user()->can('View team');
                                                         if($canViewTeam) {
                                                            if (Auth::user()->hasRole('Admin')) $teamIndexRoute = route('admin.teams.index');
                                                            elseif (Auth::user()->hasRole('Responsable de projet')) $teamIndexRoute = route('Responsable_de_projet.teams.index');
                                                            elseif (Auth::user()->hasRole('Employee')) $teamIndexRoute = route('employee.teams.index');
                                                            elseif (Auth::user()->hasRole('Responsable de projet et gestionnaire')) $teamIndexRoute = route('Responsable_gestionnaire.teams.index');
                                                         }
                                                     @endphp
                                                     @forelse($task_teams as $team_task)
                                                        @if($team_task->team) {{-- Check if team exists --}}
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                                             @if($canViewTeam)
                                                                 <a href="{{ $teamIndexRoute }}" class="hover:underline">{{ $team_task->team->name }}</a>
                                                             @else
                                                                 {{ $team_task->team->name }}
                                                             @endif
                                                        </span>
                                                        @endif
                                                     @empty
                                                        <span class="text-xs text-gray-400">No team</span>
                                                     @endforelse
                                                 </div>
                                            </td>
                                            {{-- Employees --}}
                                            <td class="px-6 py-4 whitespace-normal break-words text-sm text-gray-500">
                                                <div class="flex flex-wrap gap-1.5">
                                                     @php
                                                         // Eager load employees for performance
                                                         $task_employees = App\Models\employees_tasks::with('employee')->where('task_id', $task->id)->get();
                                                         $employeeShowRoute = '#';
                                                         $canViewEmployee = Auth::user()->can('View employee');
                                                          if ($canViewEmployee) {
                                                            if (Auth::user()->hasRole('Admin')) $employeeShowRoute = route('admin.employees.show', '[ID]'); // Need ID placeholder
                                                            elseif (Auth::user()->hasRole('Responsable de projet')) $employeeShowRoute = route('Responsable_de_projet.employees.show', '[ID]');
                                                            elseif (Auth::user()->hasRole('Employee')) $employeeShowRoute = route('employee.employees.show', '[ID]');
                                                            elseif (Auth::user()->hasRole('Responsable de projet et gestionnaire')) $employeeShowRoute = route('Responsable_gestionnaire.employees.show', '[ID]');
                                                          }
                                                     @endphp
                                                     @forelse($task_employees as $emp_task)
                                                        @if($emp_task->employee) {{-- Check if employee exists --}}
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800">
                                                             @if($canViewEmployee)
                                                                 <a href="{{ str_replace('[ID]', $emp_task->employee->id, $employeeShowRoute) }}" class="hover:underline">{{ $emp_task->employee->name }}</a>
                                                             @else
                                                                 {{ $emp_task->employee->name }}
                                                             @endif
                                                        </span>
                                                        @endif
                                                     @empty
                                                        <span class="text-xs text-gray-400">No employees</span>
                                                     @endforelse
                                                 </div>
                                            </td>
                                            {{-- Duration, Est. Hours, Difference, Progress --}}
                                            @php
                                                // Calculations (same as project table logic, applied to task)
                                                $tasktotalduration = App\Models\tasks_timer::where('task_id', $task->id)->sum('duration'); // in minutes
                                                $taskhours = floor(abs($tasktotalduration) / 60);
                                                $taskminutes = abs($tasktotalduration) % 60;

                                                $taskestimatedMinutes = ($task->estimated_hours ?? 0) * 60;
                                                $taskdifference = $taskestimatedMinutes - $tasktotalduration;
                                                $diffhours = floor(abs($taskdifference) / 60);
                                                $diffminutes = abs($taskdifference) % 60;

                                                $taskprogressPercentage = $taskestimatedMinutes > 0 ? max(0, min(100, ($tasktotalduration / $taskestimatedMinutes) * 100)) : 0;
                                                $taskformattedProgressPercentage = number_format($taskprogressPercentage, 2);

                                                $taskProgressBarColor = 'bg-green-500';
                                                if ($taskprogressPercentage >= 75) $taskProgressBarColor = 'bg-red-500';
                                                elseif ($taskprogressPercentage >= 50) $taskProgressBarColor = 'bg-yellow-500';
                                            @endphp
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ sprintf('%02d', $taskhours) }}h {{ sprintf('%02d', $taskminutes) }}m</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $task->estimated_hours ?? 0 }}h</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                @if($task->estimated_hours !== null)
                                                    @if($taskdifference > 0)
                                                        <span class="text-green-600">+{{ sprintf('%02d', $diffhours) }}h {{ sprintf('%02d', $diffminutes) }}m</span>
                                                    @elseif($taskdifference < 0)
                                                        <span class="text-red-600">-{{ sprintf('%02d', $diffhours) }}h {{ sprintf('%02d', $diffminutes) }}m</span>
                                                    @else
                                                        <span class="text-blue-600">{{ sprintf('%02d', $diffhours) }}h {{ sprintf('%02d', $diffminutes) }}m</span>
                                                    @endif
                                                 @else
                                                    <span class="text-gray-400">-</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                                <div class="flex items-center gap-2">
                                                    <div class="w-20 bg-gray-200 rounded-full h-1.5">
                                                        <div class="{{ $taskProgressBarColor }} h-1.5 rounded-full" style="width: {{ $taskformattedProgressPercentage }}%"></div>
                                                    </div>
                                                    <span class="text-xs font-medium">{{ $taskformattedProgressPercentage }}%</span>
                                                </div>
                                            </td>
                                            {{-- Actions --}}
                                            <td class="px-6 py-4 whitespace-nowrap text-sm space-x-1">
                                                @php
                                                    $canEditTask = Auth::user()->can('Update task');
                                                    $canDeleteTask = Auth::user()->can('Delete task');
                                                    $editRoute = '#'; $deleteRoute = '#';

                                                    if (Auth::user()->hasRole('Admin')) {
                                                        $editRoute = route('admin.tasks.edit', $task->id);
                                                        $deleteRoute = route('admin.tasks.destroy', $task->id);
                                                    } elseif (Auth::user()->hasRole('Responsable de projet')) {
                                                        $editRoute = route('Responsable_de_projet.tasks.edit', $task->id);
                                                        // $deleteRoute = ? // Add if applicable
                                                    } elseif (Auth::user()->hasRole('Employee')) {
                                                        $editRoute = route('employee.tasks.edit', $task->id);
                                                        // $deleteRoute = ? // Add if applicable
                                                    } elseif (Auth::user()->hasRole('Responsable de projet et gestionnaire')) {
                                                         $editRoute = route('Responsable_gestionnaire.tasks.edit', $task->id);
                                                         // $deleteRoute = ? // Add if applicable
                                                    }
                                                @endphp

                                                @if($canEditTask)
                                                    <a href="{{ $editRoute }}" class="inline-flex items-center px-2.5 py-1 border border-transparent text-xs font-medium rounded shadow-sm text-white bg-sky-600 hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500">
                                                        Edit
                                                    </a>
                                                @endif

                                                @if($canDeleteTask && Auth::user()->hasRole('Admin')) {{-- Only show delete for Admin based on original --}}
                                                    <form method="post" action="{{ $deleteRoute }}" onsubmit="return confirm('Are you sure?');" class="inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="inline-flex items-center px-2.5 py-1 border border-transparent text-xs font-medium rounded shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                                            Delete
                                                        </button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                @empty
                                    <tr>
                                         <td colspan="8" class="px-6 py-4 text-center text-sm text-gray-500">
                                             No tasks found.
                                         </td>
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
    {{-- @include('dashboard_layouts.scripts') --}}

    {{-- Filter Scripts --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const taskFilter = document.getElementById('taskFilter');
            const taskSearch = document.getElementById('taskSearch');
            const tableBody = document.querySelector('#tasksTable tbody');
            const allRows = Array.from(tableBody.querySelectorAll('tr')); // All rows including headers

            function applyFilters() {
                const filterValue = taskFilter.value.toLowerCase();
                const searchValue = taskSearch.value.toLowerCase();

                let visibleProjectHeaders = new Set();

                // Filter task rows
                allRows.forEach(row => {
                    if (row.classList.contains('task-row')) {
                        const taskTitleElement = row.querySelector('.task-title'); // Use the specific class
                        const taskTitle = taskTitleElement ? taskTitleElement.textContent.toLowerCase() : '';

                        const matchesFilter = filterValue === "" || taskTitle.includes(filterValue);
                        const matchesSearch = searchValue === "" || taskTitle.includes(searchValue);

                        if (matchesFilter && matchesSearch) {
                            row.style.display = "";
                            // Find preceding project header and mark for visibility
                            let header = row.previousElementSibling;
                            while(header && !header.classList.contains('project-header')) {
                                header = header.previousElementSibling;
                            }
                            if(header) {
                                visibleProjectHeaders.add(header);
                            }
                        } else {
                            row.style.display = "none";
                        }
                    }
                });

                // Show/hide project headers
                allRows.forEach(row => {
                     if (row.classList.contains('project-header')) {
                         row.style.display = visibleProjectHeaders.has(row) ? "" : "none";
                     }
                });
            }

            taskFilter.addEventListener('change', applyFilters);
            taskSearch.addEventListener('input', applyFilters);
        });
    </script>
    
    {{-- Timer Alert Script (kept as is) --}}
    {{-- Consider moving this to a central JS file if used on multiple pages --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var modal = document.getElementById("customAlertModal"); // Ensure this modal exists in your included head/scripts
            var span = document.getElementsByClassName("close-button")[0]; // Ensure this exists
            var alertSound = document.getElementById("alertSound"); // Ensure this exists
            var alertInterval;
            var checkStateInterval;

            if (span) {
                span.onclick = function() {
                    if(modal) modal.style.display = "none";
                }
            }

            function showAlert() {
                if(modal) modal.style.display = "block";
                if(alertSound) alertSound.play();
            }

            function startAlertInterval() {
                clearInterval(alertInterval);
                alertInterval = setInterval(showAlert, 60000); // Show alert every 1 minute
            }

            function clearAlertInterval() {
                clearInterval(alertInterval);
            }

            function checkTimerState(tasksTimer) {
                // ... (rest of your checkTimerState logic - kept as is) ...
                 if (!tasksTimer) {
                    startAlertInterval();
                } else {
                    if (tasksTimer.paused === null || tasksTimer.paused === 0) {
                        clearAlertInterval();
                    } else if (tasksTimer.paused === 1) {
                        startAlertInterval();
                    }
                    if (tasksTimer.paused_date !== null && tasksTimer.paused === 0) {
                       clearAlertInterval();
                   }
                }
            }

            function fetchTimerState() {
                let url = '';
                @if(Auth::user()->hasRole('Responsable de projet'))
                    url = '/Responsable_de_projet/pointage/get-timer-state';
                @elseif(Auth::user()->hasRole('Admin'))
                    url = '/admin/pointage/get-timer-state';
                @elseif(Auth::user()->hasRole('Employee'))
                    url = '/employee/pointage/get-timer-state';
                @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                     url = '/Responsable_gestionnaire/pointage/get-timer-state'; // Added this role
                @endif

                if (url) { 
                    fetch(url)
                        .then(response => response.json())
                        .then(data => {
                            checkTimerState(data.tasksTimer);
                        })
                        .catch(error => {
                            console.error('Error fetching timer state:', error);
                            
                        });
                } else {
                     console.log("No timer state URL defined for this user role.");
                     
                }
            }

            // Initial check
            fetchTimerState();

            // Set up periodic checks every 1 minute
            checkStateInterval = setInterval(fetchTimerState, 1 * 60 * 1000);
            console.log("Timer state checker started.");
        });
    </script>

</body>
</html>