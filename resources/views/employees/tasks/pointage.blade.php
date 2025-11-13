<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>BizManager Dashboard</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        [x-cloak] { display: none !important; }
        
        /* Custom Scrollbar */
        .custom-scrollbar::-webkit-scrollbar { height: 6px; width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        /* Modal Styles */
        .modal { display: none; position: fixed; z-index: 50; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.5); backdrop-filter: blur(4px); }
        .modal-content { animation: slideDown 0.3s ease-out; }
        @keyframes slideDown { from { transform: translateY(-50px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
    </style>
</head>
<body class="bg-gray-50 flex h-screen overflow-hidden font-sans antialiased text-slate-600">
    
    <audio id="alertSound" src="{{ asset('audio/alert_sound.mp3') }}" preload="auto"></audio>

    {{-- Sidebar --}}
    <x-employees.sidebar />

    <div class="flex flex-col flex-1 overflow-y-auto">

        {{-- Main Content --}}
        <main class="container mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">

            <div class="mb-6">
                @if(session('alertmessage'))
                    <div class="rounded-xl bg-red-50 p-4 border border-red-100 flex items-center gap-3">
                        <i class="fas fa-exclamation-circle text-red-500"></i>
                        <p class="text-sm text-red-700">{{ session('alertmessage') }}</p>
                        <button type="button" class="ml-auto text-red-400 hover:text-red-600" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>
                    </div>
                @elseif(session('message'))
                    <div class="rounded-xl bg-green-50 p-4 border border-green-100 flex items-center gap-3">
                        <i class="fas fa-check-circle text-green-500"></i>
                        <p class="text-sm text-green-700">{{ session('message') }}</p>
                        <button type="button" class="ml-auto text-green-400 hover:text-green-600" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>
                    </div>
                @endif
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex justify-between items-start">
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Active Projects</p>
                        <h2 class="text-3xl font-bold text-slate-800">{{ $projects->count() }}</h2>
                    </div>
                    <div class="bg-red-500 text-white w-12 h-12 rounded-full flex items-center justify-center shadow-md shadow-red-200">
                        <i class="fas fa-suitcase text-lg"></i>
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex justify-between items-start">
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Tasks</p>
                        <h2 class="text-3xl font-bold text-slate-800">{{ $employees_tasks->count() }}</h2>
                    </div>
                    <div class="bg-teal-500 text-white w-12 h-12 rounded-full flex items-center justify-center shadow-md shadow-teal-200">
                        <i class="fas fa-check-square text-lg"></i>
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex justify-between items-start">
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Teams</p>
                        <h2 class="text-3xl font-bold text-slate-800">0</h2>
                    </div>
                    <div class="bg-orange-500 text-white w-12 h-12 rounded-full flex items-center justify-center shadow-md shadow-orange-200">
                        <i class="fas fa-users text-lg"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 mb-8">
                <div class="p-6 pb-0">
                    @php
                        $employee_id = App\Models\employees::where('user_id', Auth::user()->id)->first()->id;
                        $today = \Carbon\Carbon::today()->toDateString();
                        $totalDurationAllTasks = App\Models\tasks_timer::where('employee_id', $employee_id)
                            ->whereDate('start_date', '=', $today)
                            ->whereNotNull('duration')
                            ->sum('duration');
                        $totalHours = intdiv($totalDurationAllTasks, 60);
                        $totalMinutes = $totalDurationAllTasks % 60;
                        
                        $tasksByProject = $employees_tasks->groupBy(function($task) {
                            return App\Models\Tasks::where('id', $task->task_id)->first()->project_name;
                        });
                    @endphp

                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-base font-bold text-slate-800">Employee Tasks Table</h3>
                        <div class="text-xs font-bold text-slate-500">
                            Today's Duration: <span class="text-slate-800">{{ (isset($totalHours) && $totalHours > 0) || (isset($totalMinutes) && $totalMinutes > 0) ? $totalHours . 'h' . sprintf('%02d', $totalMinutes) . 'm' : '00h00m' }}</span>
                        </div>
                    </div>

                    <div class="mb-4">
                        <select id="projectFilter" class="block w-48 rounded-lg border-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500 bg-white text-slate-600 shadow-sm py-2 px-3">
                            <option value="">All Projects</option>
                            @foreach($projects as $project)
                                <option value="{{ $project->name }}">{{ $project->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="overflow-x-auto custom-scrollbar">
                    <table id="tasksTable" class="w-full text-left border-collapse">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-6 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Task Name</th>
                                <th class="px-6 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Total Duration</th>
                                <th class="px-6 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Estimated Hours</th>
                                <th class="px-6 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Difference</th>
                                <th class="px-6 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider w-32">Progress</th>
                                <th class="px-6 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Timing</th>
                                <th class="px-6 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Today's Duration</th>
                                <th class="px-6 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($tasksByProject as $projectName => $projectTasks)
                                <tr>
                                    <td colspan="8" class="px-6 py-3 bg-white">
                                        <span class="text-xs font-bold text-slate-800 uppercase tracking-wide">{{ strtoupper($projectName) }}</span>
                                    </td>
                                </tr>
                                @foreach($projectTasks as $task)
                                    @php
                                        $task_id = $task->task_id;
                                        $task_name = App\Models\Tasks::where('id', $task_id)->first();
                                        $task_timer = App\Models\tasks_timer::where('task_employee_id', $task->id)->whereNull('end_date')->first();
                                        $employee_id = App\Models\employees::where('user_id', Auth::user()->id)->first()->id;
                                    @endphp
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="px-6 py-4">
                                            <a href="#" class="text-sm font-medium text-blue-500 hover:text-blue-700">{{ $task->task_name }}</a>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-slate-600">
                                            @php 
                                                $tasktotalduration = App\Models\tasks_timer::where('task_id', $task_id)->where('employee_id', $employee_id)->sum('duration');
                                                $taskhours = floor($tasktotalduration / 60);
                                                $taskminutes = $tasktotalduration % 60;
                                            @endphp
                                            {{$taskhours}}h{{$taskminutes}}m
                                        </td>
                                        <td class="px-6 py-4 text-sm text-slate-600">{{$task_name->estimated_hours ?? '-'}} h</td>
                                        <td class="px-6 py-4 text-sm text-slate-500">
                                            @php
                                                $taskdifference = $task_name->estimated_hours * 60 - $tasktotalduration;
                                                $hours = floor(abs($taskdifference) / 60);
                                                $minutes = abs($taskdifference) % 60;
                                                $taskestimatedMinutes = $task_name->estimated_hours * 60;
                                                $taskprogressPercentage = $taskestimatedMinutes > 0 ? ($tasktotalduration / $taskestimatedMinutes) * 100 : 0;
                                                $taskformattedProgressPercentage = number_format($taskprogressPercentage, 2);
                                            @endphp
                                            @if($task_name->estimated_hours !== null)
                                                @if($taskdifference > 0)
                                                    <span class="text-green-600 font-medium text-xs">+{{$hours}}h{{$minutes}}m</span>
                                                @elseif($taskdifference < 0)
                                                    <span class="text-red-600 font-medium text-xs">-{{$hours}}h{{$minutes}}m</span>
                                                @else
                                                    <span class="text-blue-600 font-medium text-xs">{{$hours}}h{{$minutes}}m</span>
                                                @endif
                                            @else
                                                <span class="text-xs text-slate-400">No estimated hours inserted</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 align-middle">
                                            <div class="w-full bg-slate-200 rounded-full h-1.5 flex items-center">
                                                <div class="h-1.5 rounded-full bg-blue-500" style="width: {{$taskformattedProgressPercentage}}%"></div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-slate-600">
                                            On track
                                        </td>
                                        <td class="px-6 py-4 text-sm font-bold text-slate-800">
                                            @php
                                                $today = \Carbon\Carbon::today()->toDateString();
                                                $totalDurationToday = App\Models\tasks_timer::where('task_employee_id', $task->id)->whereNotNull('duration')->whereDate('start_date', '=', $today)->sum('duration');
                                                $hours = intdiv($totalDurationToday, 60);
                                                $minutes = $totalDurationToday % 60;
                                            @endphp
                                            <span id="duration-{{$task->id}}">{{ $totalDurationToday ? $hours . 'h' . sprintf('%02d', $minutes).'m' : '00h00m' }}</span>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <div class="flex justify-end gap-2">
                                                @php $routePrefix = Auth::user()->hasRole('Employee') ? 'employee' : (Auth::user()->hasRole('Responsable de projet') ? 'Responsable_de_projet' : 'Responsable_gestionnaire'); @endphp
                                                
                                                @if($task_timer)
                                                    @if(($task_timer->end_date == null && $task_timer->start_date != null ) && $task_timer->paused == 1)
                                                        <form class="inline-block" action="{{ route($routePrefix.'.tasks.resume', ['tasks' => $task->id, 'employee_id' => $employee_id]) }}" method="POST">
                                                            @csrf
                                                            <button type="submit" class="resume-button bg-blue-500 hover:bg-blue-600 text-white text-xs font-bold py-1.5 px-3 rounded shadow-sm" id="continue-btn" data-task-id="{{$task->id}}">
                                                                Resume
                                                            </button>
                                                        </form>
                                                    @else
                                                        <form class="inline-block" action="{{ route($routePrefix.'.tasks.pause', ['tasks' => $task->id, 'employee_id' => $employee_id]) }}" method="POST">
                                                            @csrf
                                                            <button type="submit" class="pause-button bg-amber-400 hover:bg-amber-500 text-white text-xs font-bold py-1.5 px-3 rounded shadow-sm" id="pause-btn" data-task-id="{{$task->id}}">
                                                                Pause
                                                            </button>
                                                        </form>
                                                    @endif
                                                @else
                                                    <form class="inline-block" action="{{ route($routePrefix.'.tasks.start', ['tasks' => $task->id, 'employee_id' => $employee_id]) }}" method="POST">
                                                        @csrf
                                                        <button type="submit" class="start-button bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-bold py-1.5 px-3 rounded shadow-sm" id="start-btn" data-task-id="{{$task->id}}">
                                                            Start
                                                        </button>
                                                    </form>
                                                @endif

                                                <form class="inline-block stop-form" action="{{ route($routePrefix.'.tasks.stop', ['tasks' => $task->id, 'employee_id' => $employee_id]) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="stop-button bg-red-500 hover:bg-red-600 text-white text-xs font-bold py-1.5 px-3 rounded shadow-sm" data-task-id="{{$task->id}}">
                                                        Stop
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 mb-8">
                <div class="p-6">
                    <div class="flex flex-col md:flex-row md:items-center justify-between mb-6">
                        <h3 class="text-base font-bold text-slate-800">Tasks Timer Table</h3>
                        <div class="flex space-x-2 mt-4 md:mt-0">
                            <button id="exportBtn" class="bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 px-4 py-2 rounded-lg text-sm font-medium shadow-sm transition-colors">Export to Excel</button>
                            <button id="exportPDFBtn" class="bg-blue-600 text-white hover:bg-blue-700 px-4 py-2 rounded-lg text-sm font-medium shadow-sm transition-colors">Export to PDF</button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="relative">
                            <input type="date" id="startDateInput" class="block w-full rounded-lg border-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500 bg-white text-slate-600 shadow-sm py-2.5 pl-3">
                        </div>
                        <div class="relative">
                            <input type="date" id="endDateInput" class="block w-full rounded-lg border-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500 bg-white text-slate-600 shadow-sm py-2.5 pl-3" placeholder="--:--">
                        </div>
                        <div class="relative">
                            <select id="projectFilterTimer" class="block w-full rounded-lg border-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500 bg-white text-slate-600 shadow-sm py-2.5 pl-3">
                                <option value="">All Projects</option>
                                @foreach($projects as $project)
                                    <option value="{{ $project->id }}">{{ $project->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto custom-scrollbar border-t border-slate-100">
                    <table id="taskTable" class="w-full text-left border-collapse">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-6 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Project Name</th>
                                <th class="px-6 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Task Name</th>
                                <th class="px-6 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Start Date</th>
                                <th class="px-6 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">End Date</th>
                                <th class="px-6 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Comment</th>
                                <th class="px-6 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Duration</th>
                            </tr>
                        </thead>
                        <tbody id="taskTableBody" class="divide-y divide-slate-100">
                            </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-100">
                <div class="p-6 border-b border-slate-100">
                    <h3 class="text-base font-bold text-slate-800">Work Days Reports</h3>
                </div>
                <div class="overflow-x-auto custom-scrollbar">
                    <table id="editableTable" class="w-full text-left border-collapse">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider leading-tight">Number of<br>Working Days</th>
                                <th class="px-4 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider leading-tight">Number of<br>Sick Days</th>
                                <th class="px-4 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider leading-tight">Number of<br>Public Holidays</th>
                                <th class="px-4 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider leading-tight">Remaining<br>Paid Leave Days</th>
                                <th class="px-4 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider leading-tight">Paid Leave<br>Days Accrued</th>
                                <th class="px-4 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider leading-tight">Paid Leave<br>Days Taken</th>
                                <th class="px-4 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider leading-tight">Leave<br>Balance</th>
                                <th class="px-4 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider leading-tight">Days of<br>Absence</th>
                                <th class="px-4 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider leading-tight">Paid Leave<br>Dates</th>
                                <th class="px-4 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider leading-tight">Month</th>
                                <th class="px-4 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider leading-tight">Year</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-600">
                            @if($work_days_repports)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-4 text-sm editable" data-id="{{$work_days_repports->id}}" data-column="days_worked">
                                    @php
                                        $firstDay = Carbon\Carbon::create($work_days_repports->year, $work_days_repports->month, 1);
                                        $lastDay = $firstDay->copy()->endOfMonth();
                                        $saturdays = 0;
                                        for($date = $firstDay; $date->lte($lastDay); $date->addDay()) { if($date->isSaturday()) { $saturdays++; } }
                                        $totalDays = $work_days_repports->days_worked + $saturdays + $work_days_repports->days_to_recover;
                                    @endphp
                                    {{$totalDays}}
                                </td>
                                <td class="px-4 py-4 text-sm editable" data-id="{{$work_days_repports->id}}" data-column="sick_days">{{$work_days_repports->sick_days}}</td>
                                <td class="px-4 py-4 text-sm editable" data-id="{{$work_days_repports->id}}" data-column="holidays">{{$work_days_repports->holidays}}</td>
                                <td class="px-4 py-4 text-sm editable" data-id="{{$work_days_repports->id}}" data-column="remaining_days_of_paid_leave">{{$work_days_repports->remaining_days_of_paid_leave}}</td>
                                <td class="px-4 py-4 text-sm editable" data-id="{{$work_days_repports->id}}" data-column="number_of_days_of_paid_leave_acquired">{{$work_days_repports->number_of_days_of_paid_leave_acquired}}</td>
                                <td class="px-4 py-4 text-sm editable" data-id="{{$work_days_repports->id}}" data-column="number_of_days_of_paid_leave_taken">{{$work_days_repports->number_of_days_of_paid_leave_taken}}</td>
                                <td class="px-4 py-4 text-sm editable" data-id="{{$work_days_repports->id}}" data-column="holiday_balance">{{$work_days_repports->holiday_balance}}</td>
                                <td class="px-4 py-4 text-sm editable" data-id="{{$work_days_repports->id}}" data-column="days_of_unpaid_absence">{{$work_days_repports->days_of_unpaid_absence}}</td>
                                <td class="px-4 py-4 text-sm editable" data-id="{{$work_days_repports->id}}" data-column="dates_of_holidays">{{$work_days_repports->dates_of_holidays}}</td>
                                <td class="px-4 py-4 text-sm text-blue-500 editable" data-id="{{$work_days_repports->id}}" data-column="month">{{$work_days_repports->month}}</td>
                                <td class="px-4 py-4 text-sm text-blue-500 editable" data-id="{{$work_days_repports->id}}" data-column="year">{{$work_days_repports->year}}</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="customAlertModal" class="modal">
                <div class="modal-content relative w-full max-w-md mx-auto mt-20 bg-white rounded-xl shadow-2xl p-6 text-center">
                    <span class="close-button absolute top-3 right-4 text-slate-400 hover:text-slate-600 cursor-pointer text-2xl font-bold">&times;</span>
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                        <i class="fas fa-bell text-red-500 text-xl"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 mb-2">Timer Alert</h3>
                    <p class="text-sm text-slate-600">Did you forget to launch your timer?</p>
                </div>
            </div>

        </main>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.3.1/jspdf.umd.min.js"></script>
    <script src="https://unpkg.com/jspdf-autotable@3.5.17/dist/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.4/xlsx.full.min.js"></script>

    <script>
        var taskIntervals = {};
        var taskPaused = {};
        
        document.addEventListener('DOMContentLoaded', function() {
            @foreach($employees_tasks as $task)
                @php
                    $task_timer = App\Models\tasks_timer::where('task_employee_id', $task->id)->whereNull('end_date')->first();
                @endphp
                @if($task_timer && $task_timer->paused === 1)
                    taskPaused["{{$task->id}}"] = true;
                @else
                    taskPaused["{{$task->id}}"] = false;
                @endif
            @endforeach
            
            document.querySelectorAll('.pause-button').forEach(function(button) {
                button.addEventListener('click', function() {
                    var taskId = this.getAttribute('data-task-id');
                    taskPaused[taskId] = true;
                });
            });
            
            document.querySelectorAll('.resume-button').forEach(function(button) {
                button.addEventListener('click', function() {
                    var taskId = this.getAttribute('data-task-id');
                    taskPaused[taskId] = false;
                });
            });
            
            document.querySelectorAll('.stop-button').forEach(function(button) {
                button.addEventListener('click', function() {
                    var taskId = this.getAttribute('data-task-id');
                    if(taskIntervals[taskId]) {
                        clearInterval(taskIntervals[taskId]);
                        delete taskIntervals[taskId];
                    }
                });
            });
        });
    </script>

    <script>
    document.getElementById('exportBtn').addEventListener('click', function() {
        var table = document.getElementById('taskTable');
        var wb = XLSX.utils.table_to_book(table, { sheet: 'Sheet1' });
        var startDate = document.getElementById('startDateInput').value;
        var endDate = document.getElementById('endDateInput').value;
        var taskInfo = 'TasksInfo'; 
        var fileName = startDate + '__' + endDate + '_' + taskInfo + '.xlsx';
        XLSX.writeFile(wb, fileName);
    });
    </script>

    <script>
        document.getElementById('exportPDFBtn').addEventListener('click', function() {
            var startDate = document.getElementById('startDateInput').value;
            var endDate = document.getElementById('endDateInput').value;
            var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            var employeeId = {{ $employee_id }};
            
            if(startDate && endDate) {
                @if(Auth::user()->hasRole('Employee'))
                var url = '/employee/Pointagerepports/downloadRepports/' + startDate + '/' + endDate + '/' + employeeId ;
                @endif
                @if(Auth::user()->hasRole('Responsable de projet'))
                var url = '/Responsable_de_projet/Pointagerepports/downloadRepports/' + startDate + '/' + endDate + '/' + employeeId ;
                @endif
                @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                var url = '/Responsable_gestionnaire/Pointagerepports/downloadRepports/' + startDate + '/' + endDate + '/' + employeeId ;
                @endif
                fetch(url, { method: 'GET', headers: { 'X-CSRF-TOKEN': csrfToken } })
                .then(response => response.blob())
                .then(blob => {
                    var url = window.URL.createObjectURL(blob);
                    var a = document.createElement('a');
                    a.href = url; a.download = 'pointageRepports.pdf';
                    document.body.appendChild(a); a.click(); a.remove();          
                })
                .catch((error) => { console.error('Error:', error); });
            }
            if(startDate && !endDate) {
                @if(Auth::user()->hasRole('Employee'))
                var url = '/employee/Pointagerepports/downloadOneDayRepports/' + startDate + '/' + employeeId;
                @endif
                @if(Auth::user()->hasRole('Responsable de projet'))
                var url = '/Responsable_de_projet/Pointagerepports/downloadOneDayRepports/' + startDate + '/' + employeeId;
                @endif
                @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                var url = '/Responsable_gestionnaire/Pointagerepports/downloadOneDayRepports/' + startDate + '/' + employeeId;
                @endif
                fetch(url, { method: 'GET', headers: { 'X-CSRF-TOKEN': csrfToken } })
                .then(response => response.blob())
                .then(blob => {
                    var url = window.URL.createObjectURL(blob);
                    var a = document.createElement('a');
                    a.href = url; a.download = 'pointageRepports.pdf';
                    document.body.appendChild(a); a.click(); a.remove();          
                })
                .catch((error) => { console.error('Error:', error); });
            }
        });
    </script>

    @php 
        $user = App\Models\User::where('id', Auth::user()->id)->first();
        $employee_id = App\Models\employees::where('user_id', Auth::user()->id)->first()->id;
        $tasks_timer = App\Models\tasks_timer::where('employee_id', $employee_id)->whereDate('start_date', '=', $today)->whereNull('end_date')->first();
    @endphp

    <script>
        document.addEventListener("DOMContentLoaded", function() {
        var modal = document.getElementById("customAlertModal");
        var span = document.getElementsByClassName("close-button")[0];
        var alertSound = document.getElementById("alertSound");
        var alertInterval;

        span.onclick = function() { modal.style.display = "none"; }

        function showAlert() {
            modal.style.display = "block";
            alertSound.play();
        }

        function startAlertInterval() {
            clearInterval(alertInterval);
            alertInterval = setInterval(showAlert, 300000); 
        }

        function clearAlertInterval() { clearInterval(alertInterval); }

        var tasksTimer = @json($tasks_timer);

        function checkTimerState() {
            if (!tasksTimer) {
                startAlertInterval();
            } else {
                if (tasksTimer.paused === null || tasksTimer.paused === 0) {
                    clearAlertInterval();
                } else if (tasksTimer.paused === 1) {
                    startAlertInterval();
                }
                if (tasksTimer.pause_date !== null && tasksTimer.paused === 0) {
                    clearAlertInterval();
                }
            }
        }

        checkTimerState();

        var startBtn = document.getElementById('start-btn');
        if(startBtn) { startBtn.addEventListener('click', function() { checkTimerState(); }); }

        var pauseBtn = document.getElementById('pause-btn');
        if(pauseBtn) { pauseBtn.addEventListener('click', function() { checkTimerState(); }); }

        var continueBtn = document.getElementById('continue-btn');
        if(continueBtn) { continueBtn.addEventListener('click', function() { checkTimerState(); }); }

        var stopBtn = document.getElementById('stop-btn');
        if(stopBtn) { stopBtn.addEventListener('click', function() { checkTimerState(); }); }
    });
    </script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var today = new Date();
        var dd = String(today.getDate()).padStart(2, '0');
        var mm = String(today.getMonth() + 1).padStart(2, '0'); 
        var yyyy = today.getFullYear();
        today = yyyy + '-' + mm + '-' + dd;
        document.getElementById('startDateInput').value = today;
    });
    </script>

    <script type="text/javascript">
        var employeeId = @json($employee_id); 
    </script>

    <script>
    // UPDATED JAVASCRIPT TO GENERATE TAILWIND TABLE ROWS
    document.addEventListener('DOMContentLoaded', function() {
        var startDateInput = document.getElementById('startDateInput');

        document.getElementById('projectFilter').addEventListener('change', function() {
            var selectedProjectName = this.options[this.selectedIndex].text;
            var tableRows = document.querySelectorAll('#taskTableBody tr');
            var total = 0; 
            var totalRecette = 0;
            var totalDuration = 0; 

            var totalRow = document.querySelector('.total-row');
            if (totalRow) { totalRow.remove(); }
            var existingInvoiceButton = document.querySelector('.invoice-button');
            if (existingInvoiceButton) { existingInvoiceButton.remove(); }

            tableRows.forEach(row => {
                var projectCell = row.cells[0]; 
                if (selectedProjectName === "All Projects" || projectCell.textContent === selectedProjectName) {
                    row.style.display = ''; 
                    var DurationValue = row.cells[5].textContent.trim(); 
                    var durationInSeconds = parseDuration(DurationValue);
                    totalDuration += durationInSeconds / 3600;
                } else {
                    row.style.display = 'none'; 
                }
            });

            var tableBody = document.getElementById('taskTableBody');
            var hours = Math.floor(totalDuration);
            var minutes = Math.round((totalDuration - hours) * 60);
            var formattedDuration = `${hours.toString().padStart(2, '0')}h:${minutes.toString().padStart(2, '0')}m`;
            
            // Updated HTML string with Tailwind classes
            var totalRow = `
                <tr class="text-center total-row bg-slate-50 font-bold">
                <td colspan="5" class="px-6 py-4 text-right">Total:</td>
                <td class="px-6 py-4"><span class="text-blue-600">${formattedDuration}</span></td>
                </tr>`;
            tableBody.insertAdjacentHTML('beforeend', totalRow);
        });

        window.onload = function() {
            var currentDate = new Date().toISOString().split('T')[0]; 
            startDateInput.value = currentDate;
            var event = new Event('change');
            startDateInput.dispatchEvent(event);
        }

        var endDateInput = document.getElementById('endDateInput');
        endDateInput.addEventListener('change', function() {
            var startDate = startDateInput.value;
            var endDate = this.value;
            if (startDate && endDate) { fetchTasksForInterval(startDate, endDate, employeeId); } 
            else if (endDate) { fetchTasksForDate(startDate, employeeId); }
        });

        startDateInput.addEventListener('change', function() {
            var endDate = endDateInput.value;
            var startDate = this.value;
            if (startDate && endDate) { fetchTasksForInterval(startDate, endDate, employeeId); } 
            else if (startDate) { fetchTasksForDate(startDate, employeeId); }
        });

        function fetchTasksForInterval(startDate, endDate, employeeId) {
            @if(Auth::user()->hasRole('Employee'))
            var url = '/employee/tasks/tasks-info-interval-employee/' + startDate + '/' + endDate + '/' + employeeId;
            @endif
            @if(Auth::user()->hasRole('Responsable de projet'))
            var url = '/Responsable_de_projet/tasks/tasks-info-interval-employee/' + startDate + '/' + endDate + '/' + employeeId;
            @endif
            @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
            var url = '/Responsable_gestionnaire/tasks/tasks-info-interval-employee/' + startDate + '/' + endDate + '/' + employeeId;
            @endif
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    var tableBody = document.getElementById('taskTableBody');
                    if (tableBody !== null) {
                        tableBody.innerHTML = ''; 
                        var totalDuration = 0;

                        Promise.all(data.map(task => {
                            var startTime = new Date(task.start_date);
                            var endTime = task.end_date ? new Date(task.end_date) : new Date(); 
                            var duration = endTime - startTime; 

                            var hours = Math.floor(duration / (1000 * 60 * 60));
                            var minutes = Math.floor((duration % (1000 * 60 * 60)) / (1000 * 60));
                            var seconds = Math.floor((duration % (1000 * 60)) / 1000);
                            var formattedDuration = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
                            var endDateText = task.end_date ? task.end_date : '<span class="text-green-600 font-bold text-xs">Still working</span>';

                            @if(Auth::user()->hasRole('Employee'))
                            var fetchurl = '/employee/tasks/get-project-name/'
                            @endif
                            @if(Auth::user()->hasRole('Responsable de projet'))
                            var fetchurl = '/Responsable_de_projet/tasks/get-project-name/'
                            @endif
                            @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                            var fetchurl = '/Responsable_gestionnaire/tasks/get-project-name/'
                            @endif
                            return fetch(fetchurl + encodeURIComponent(task.task_id))
                                .then(response => response.json())
                                .then(projectData => {
                                    return fetch('/employee/tasks/get-employee-name/' + encodeURIComponent(task.employee_id))
                                        .then(response => response.json())
                                        .then(employeeData => {
                                            var rowId = 'row-' + task.id;
                                            let duration_hours = Math.floor(task.duration / 60);
                                            let duration_minutes = task.duration % 60;
                                            let duration_seconds = 0; 
                                            let formattedDuration = `${duration_hours.toString().padStart(2, '0')}h:${duration_minutes.toString().padStart(2, '0')}m:${duration_seconds.toString().padStart(2, '0')}s`;

                                            // Updated Row with Tailwind classes
                                            var row = `
                                      <tr id="${rowId}" class="hover:bg-slate-50 transition-colors">
                                          <td class="px-6 py-4 text-sm text-slate-600">${projectData.name.toUpperCase()}</td>                          
                                          <td class="px-6 py-4 text-sm text-blue-500 font-medium">${task.task_name}</td>
                                          <td class="px-6 py-4 text-sm text-slate-500">${task.start_date}</td>
                                          <td class="px-6 py-4 text-sm text-slate-500">${endDateText}</td>
                                          <td class="px-6 py-4 text-sm text-slate-500">${task.comment ? task.comment : "No Comment"}</td>
                                          <td class="task_duration px-6 py-4 text-sm font-bold text-slate-800">${task.end_date ? formattedDuration : ""}</td>
                                      </tr>`;

                                            tableBody.insertAdjacentHTML('beforeend', row);

                                            if (task.end_date === null) {
                                                var startDate = new Date(task.start_date);
                                                var taskId = task.id;
                                                var rowId = 'row-' + taskId;
                                                taskPaused[taskId] = task.paused === 1;
                                                var startTimestamp = startDate.getTime();
                                                var elapsedTime = 0;
                                                
                                                if(taskIntervals[taskId]) { clearInterval(taskIntervals[taskId]); }
                                                
                                                taskIntervals[taskId] = setInterval(function() {
                                                    if(!taskPaused[taskId]) {
                                                        var now = new Date();
                                                        var durationInMilliseconds = now - startDate + elapsedTime;
                                                        var hours = Math.floor(durationInMilliseconds / 3600000);
                                                        var minutes = Math.floor((durationInMilliseconds % 3600000) / 60000);
                                                        var seconds = Math.floor((durationInMilliseconds % 60000) / 1000);
                                                        var formattedDuration = (hours < 10 ? '0' : '') + hours + 'h:' + (minutes < 10 ? '0' : '') + minutes + 'm:' + (seconds < 10 ? '0' : '') + seconds + 's';
                                                        var durationCell = document.querySelector('#' + rowId + ' .task_duration');
                                                        if (durationCell) { durationCell.textContent = formattedDuration; } 
                                                        else { clearInterval(taskIntervals[taskId]); }
                                                    }
                                                }, 1000); 
                                            }
                                            var rowElement = tableBody.lastElementChild;
                                            var DurationValue = rowElement.cells[5].textContent.trim();
                                            var durationInSeconds = parseDuration(DurationValue);
                                            totalDuration += durationInSeconds / 3600;
                                        });
                                });
                        })).then(() => {
                            var hours = Math.floor(totalDuration);
                            var minutes = Math.round((totalDuration - hours) * 60);
                            var formattedDuration = `${hours.toString().padStart(2, '0')}h:${minutes.toString().padStart(2, '0')}m`;
                            var totalRow = `
                            <tr class="bg-slate-50 font-bold">
                                <td colspan="5" class="px-6 py-4 text-right">Total:</td>
                                <td class="px-6 py-4 text-blue-600">${formattedDuration}</td>
                            </tr>`;
                            tableBody.insertAdjacentHTML('beforeend', totalRow);
                        });
                    }
                })
                .catch(error => { console.error('Error fetching data:', error); });
        }

        function fetchTasksForDate(selectedDate, employeeId) {
            let totalDuration = 0;
            @if(Auth::user()->hasRole('Employee'))
            var url = '/employee/tasks/tasks-info/' + selectedDate + '/' + employeeId;
            @endif
            @if(Auth::user()->hasRole('Responsable de projet'))
            var url = '/Responsable_de_projet/tasks/tasks-info/' + selectedDate + '/' + employeeId;
            @endif
            @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
            var url = '/Responsable_gestionnaire/tasks/tasks-info/' + selectedDate + '/' + employeeId;
            @endif
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    var tableBody = document.getElementById('taskTableBody');
                    if (tableBody !== null) {
                        tableBody.innerHTML = ''; 
                        Promise.all(data.map(task => {
                            var startTime = new Date(task.start_date);
                            var endTime = task.end_date ? new Date(task.end_date) : new Date(); 
                            var duration = endTime - startTime; 
                            var hours = Math.floor(duration / (1000 * 60 * 60));
                            var minutes = Math.floor((duration % (1000 * 60 * 60)) / (1000 * 60));
                            var seconds = Math.floor((duration % (1000 * 60)) / 1000);
                            var formattedDuration = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
                            var endDateText = task.end_date ? task.end_date : '<span class="text-green-600 font-bold text-xs">Still working</span>';

                            @if(Auth::user()->hasRole('Employee'))
                            var fetchurl = '/employee/tasks/get-project-name/'
                            @endif
                            @if(Auth::user()->hasRole('Responsable de projet'))
                            var fetchurl = '/Responsable_de_projet/tasks/get-project-name/'
                            @endif
                            @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                            var fetchurl = '/Responsable_gestionnaire/tasks/get-project-name/'
                            @endif
                            return fetch(fetchurl + encodeURIComponent(task.task_id))
                                .then(response => response.json())
                                .then(projectData => {
                                    return fetch('/employee/tasks/get-employee-name/' + encodeURIComponent(task.employee_id))
                                        .then(response => response.json())
                                        .then(employeeData => {
                                            var rowId = 'row-' + task.id; 
                                            let duration_hours = Math.floor(task.duration / 60);
                                            let duration_minutes = task.duration % 60;
                                            let duration_seconds = 0; 
                                            let formattedDuration = `${duration_hours.toString().padStart(2, '0')}h:${duration_minutes.toString().padStart(2, '0')}m:${duration_seconds.toString().padStart(2, '0')}s`;

                                            // Updated Row with Tailwind classes
                                            var row = `
                                      <tr id="${rowId}" class="hover:bg-slate-50 transition-colors">
                                          <td class="px-6 py-4 text-sm text-slate-600">${projectData.name.toUpperCase()}</td>                          
                                          <td class="px-6 py-4 text-sm text-blue-500 font-medium">${task.task_name}</td>
                                          <td class="px-6 py-4 text-sm text-slate-500">${task.start_date}</td>
                                          <td class="px-6 py-4 text-sm text-slate-500">${endDateText}</td>
                                          <td class="px-6 py-4 text-sm text-slate-500">${task.comment ? task.comment : "No Comment"}</td>
                                          <td class="task_duration px-6 py-4 text-sm font-bold text-slate-800">${task.end_date ? formattedDuration : ""}</td>
                                      </tr>`;

                                            tableBody.insertAdjacentHTML('beforeend', row);

                                            if (task.end_date === null) {
                                                var startDate = new Date(task.start_date);
                                                var taskId = task.id;
                                                var rowId = 'row-' + taskId;
                                                taskPaused[taskId] = task.paused === 1;
                                                var startTimestamp = startDate.getTime();
                                                var elapsedTime = 0;
                                                
                                                if(taskIntervals[taskId]) { clearInterval(taskIntervals[taskId]); }
                                                
                                                taskIntervals[taskId] = setInterval(function() {
                                                    if(!taskPaused[taskId]) {
                                                        var now = new Date();
                                                        var durationInMilliseconds = now - startDate + elapsedTime;
                                                        var hours = Math.floor(durationInMilliseconds / 3600000);
                                                        var minutes = Math.floor((durationInMilliseconds % 3600000) / 60000);
                                                        var seconds = Math.floor((durationInMilliseconds % 60000) / 1000);
                                                        var formattedDuration = (hours < 10 ? '0' : '') + hours + 'h:' + (minutes < 10 ? '0' : '') + minutes + 'm:' + (seconds < 10 ? '0' : '') + seconds + 's';
                                                        var durationCell = document.querySelector('#' + rowId + ' .task_duration');
                                                        if (durationCell) { durationCell.textContent = formattedDuration; } 
                                                        else { clearInterval(taskIntervals[taskId]); }
                                                    }
                                                }, 1000); 
                                            }
                                            var rowElement = tableBody.lastElementChild;
                                            var DurationValue = rowElement.cells[5].textContent.trim();
                                            var durationInSeconds = parseDuration(DurationValue);
                                            totalDuration += durationInSeconds / 3600;
                                        });
                                });
                        })).then(() => {
                            var hours = Math.floor(totalDuration);
                            var minutes = Math.round((totalDuration - hours) * 60);
                            var formattedDuration = `${hours.toString().padStart(2, '0')}h:${minutes.toString().padStart(2, '0')}m`;
                            var totalRow = `
                            <tr class="bg-slate-50 font-bold">
                                <td colspan="5" class="px-6 py-4 text-right">Total:</td>
                                <td class="px-6 py-4 text-blue-600">${formattedDuration}</td>
                            </tr>`;
                            tableBody.insertAdjacentHTML('beforeend', totalRow);
                        });
                    }
                })
                .catch(error => { console.error('Error fetching data:', error); });
        }

        function parseDuration(durationString) {
            var parts = durationString.split(':');
            var hours = parseInt(parts[0].replace('h', ''), 10);
            var minutes = parseInt(parts[1].replace('m', ''), 10);
            var seconds = parseInt(parts[2].replace('s', ''), 10);
            return hours * 3600 + minutes * 60 + seconds;
        }
    });
    </script>

    <script>
    $(document).ready(function() {
        $('.start-button').click(function() {
            var taskId = $(this).data('task-id');
            var employeeId = $(this).data('employee-id');
            $.ajax({
                url: '/tasks/' + taskId + '/start/' + employeeId,
                type: 'POST',
                data: { task_id: taskId, employee_id: employeeId },
                success: function(response) {},
                error: function(response) {}
            });
        });

        $('.stop-button').click(function() {
            var taskId = $(this).data('task-id');
            var employeeId = $(this).data('employee-id');
            $.ajax({
                url: '/tasks/' + taskId + '/stop/' + employeeId,
                type: 'POST',
                data: { task_id: taskId, employee_id: employeeId },
                success: function(response) {},
                error: function(response) {}
            });
        });
    });
    </script>
    <script>
        document.getElementById('projectFilter').addEventListener('change', function() {
            var filter = this.value.toLowerCase();
            var rows = document.querySelectorAll('#tasksTable tbody tr');
            var currentProject = null;
            var shouldShowCurrentProject = false;

            rows.forEach(function(row) {
                // Check if this is a project banner row (has colspan="8")
                if (row.cells.length === 1 && row.cells[0].colSpan === 8) {
                    currentProject = row.cells[0].textContent.trim().toLowerCase();
                    shouldShowCurrentProject = filter === "" || currentProject.includes(filter);
                    row.style.display = shouldShowCurrentProject ? "" : "none";
                } else {
                    // This is a task row - show it if its project is being shown
                    row.style.display = shouldShowCurrentProject ? "" : "none";
                }
            });
        });
    </script>

</body>
</html>