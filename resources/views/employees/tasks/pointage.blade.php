<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('messages.Pointage Dashboard') }}</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        [x-cloak] { display: none !important; }
        
        /* Custom Scrollbar */
        .custom-scrollbar::-webkit-scrollbar { height: 6px; width: 7px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        /* Modal Styles */
        .modal { display: none; position: fixed; z-index: 50; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.5); backdrop-filter: blur(4px); }
        .modal-content { animation: slideDown 0.3s ease-out; }
        @keyframes slideDown { from { transform: translateY(-50px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }

        /* Updated Custom Alert Modal to have stronger shadow */
        .modal-content-custom {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 600px;
            text-align: center;
            position: relative;
            border-radius: 0.75rem; /* rounded-xl */
            /* Stronger shadow for the modal */
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        .close-button {
            color: #94a3b8;
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .close-button:hover { color: #475569; }
    </style>
</head>
<body class="bg-gray-100 flex h-screen overflow-hidden font-sans antialiased text-slate-600">
    
    <audio id="alertSound" src="{{ asset('audio/alert_sound.mp3') }}" preload="auto"></audio>

    <div class="flex flex-1 h-full overflow-hidden">
        {{-- Sidebar --}}
        <x-employees.sidebar />


        <div class="flex flex-col flex-1 overflow-y-auto w-full">
        {{-- Main Content --}}
        <main class="flex flex-col flex-1 overflow-y-auto w-full px-4 md:px-8">

            <div class="mb-6">
                @if(session('alertmessage'))
                    <div class="rounded-xl bg-red-50 p-4 border border-red-200 shadow-md flex items-center gap-3">
                        <i class="fas fa-exclamation-circle text-red-500"></i>
                        <p class="text-sm text-red-700">{{ session('alertmessage') }}</p>
                        <button type="button" class="ml-auto text-red-400 hover:text-red-600" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>
                    </div>
                @elseif(session('message'))
                    <div class="rounded-xl bg-green-50 p-4 border border-green-200 shadow-md flex items-center gap-3">
                        <i class="fas fa-check-circle text-green-500"></i>
                        <p class="text-sm text-green-700">{{ session('message') }}</p>
                        <button type="button" class="ml-auto text-green-400 hover:text-green-600" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>
                    </div>
                @endif
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white rounded-2xl p-6 shadow-lg border border-slate-200 flex justify-between items-start">
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">{{ __('messages.Active Projects') }}</p>
                        <h2 class="text-3xl font-bold text-slate-800">{{ $projects->count() }}</h2>
                    </div>
                    <div class="bg-red-500 text-white w-12 h-12 rounded-full flex items-center justify-center shadow-md shadow-red-200">
                        <i class="fas fa-suitcase text-lg"></i>
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-6 shadow-lg border border-slate-200 flex justify-between items-start">
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">{{ __('messages.Tasks') }}</p>
                        <h2 class="text-3xl font-bold text-slate-800">{{ $employees_tasks->count() }}</h2>
                    </div>
                    <div class="bg-teal-500 text-white w-12 h-12 rounded-full flex items-center justify-center shadow-md shadow-teal-200">
                        <i class="fas fa-check-square text-lg"></i>
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-6 shadow-lg border border-slate-200 flex justify-between items-start">
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">{{ __('messages.Teams') }}</p>
                        <h2 class="text-3xl font-bold text-slate-800">0</h2>
                    </div>
                    <div class="bg-orange-500 text-white w-12 h-12 rounded-full flex items-center justify-center shadow-md shadow-orange-200">
                        <i class="fas fa-users text-lg"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-lg border border-slate-200 mb-8">
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
                        <h3 class="text-base font-bold text-slate-800">{{ __('messages.Employee Tasks Table') }}</h3>
                        <div class="text-xs font-bold text-slate-500">
                            {{ __('messages.Today\'s Duration') }}: <span class="text-slate-800">{{ (isset($totalHours) && $totalHours > 0) || (isset($totalMinutes) && $totalMinutes > 0) ? $totalHours . 'h' . sprintf('%02d', $totalMinutes) . 'm' : '00h00m' }}</span>
                        </div>
                    </div>

                    <div class="mb-4">
                        <select id="projectFilter" class="block w-48 rounded-lg border-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500 bg-white text-slate-600 shadow-sm py-2 px-3">
                            <option value="">{{ __('messages.All Projects') }}</option>
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
                                <th class="px-6 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">{{ __('messages.Task Name') }}</th>
                                <th class="px-6 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">{{ __('messages.Total Duration') }}</th>
                                <th class="px-6 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">{{ __('messages.Estimated Hours') }}</th>
                                <th class="px-6 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">{{ __('messages.Difference') }}</th>
                                <th class="px-6 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider w-32">{{ __('messages.Progress') }}</th>
                                <th class="px-6 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">{{ __('messages.Comments') }}</th>
                                <th class="px-6 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">{{ __('messages.Today\'s Duration') }}</th>
                                <th class="px-6 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">{{ __('messages.Action') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($tasksByProject as $projectName => $projectTasks)
                                <tr>
                                    <td colspan="8" class="px-6 py-3 bg-slate-200 text-center">
                                        <span class="text-xs font-bold text-slate-800 uppercase tracking-wide">{{ strtoupper($projectName) }}</span>
                                    </td>
                                </tr>
                                @foreach($projectTasks as $task)
                                    @php
                                        $task_id = $task->task_id;
                                        $task_name = App\Models\Tasks::where('id', $task_id)->first();
                                        $task_timer = App\Models\tasks_timer::where('task_employee_id', $task->id)->whereNull('end_date')->first();
                                        $employee_id = App\Models\employees::where('user_id', Auth::user()->id)->first()->id;
                                        $routePrefix = Auth::user()->hasRole('Employee')
                                            ? 'employee'
                                            : (Auth::user()->hasRole('Responsable de projet')
                                                ? 'Responsable_de_projet'
                                                : (Auth::user()->hasRole('Responsable de projet et gestionnaire')
                                                    ? 'Responsable_gestionnaire'
                                                    : 'employee'));
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
                                                $taskEstimatedMinutes = ($task_name->estimated_hours ?? 0) * 60;
                                                $taskConsumedMinutes = ($task_name->consumed_hours ?? 0) * 60;
                                                $taskdifference = $taskEstimatedMinutes - $taskConsumedMinutes - $tasktotalduration;
                                                $hours = floor(abs($taskdifference) / 60);
                                                $minutes = abs($taskdifference) % 60;
                                                $taskestimatedMinutes = max(0, (($task_name->estimated_hours ?? 0) - ($task_name->consumed_hours ?? 0)) * 60);
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
                                                <span class="text-xs text-slate-400">{{ __('messages.No estimated hours inserted') }}</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 align-middle">
                                            <div class="w-full bg-slate-200 rounded-full h-1.5 flex items-center">
                                                <div class="h-1.5 rounded-full bg-blue-500" style="width: {{$taskformattedProgressPercentage}}%"></div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-slate-600">
                                            {{ $task->comment ?? __('messages.No Comment') }}
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
                                            
                                            @unless($task_timer)
                                                <form class="inline-flex items-center gap-2" action="{{ route($routePrefix.'.tasks.start', ['tasks' => $task->id, 'employee_id' => $employee_id]) }}" method="POST">
                                                    @csrf
                                                    <input type="text"
                                                           name="comment"
                                                           placeholder="{{ __('messages.Enter comment') }}"
                                                           class="border border-slate-300 rounded px-2 py-1 text-xs text-slate-700 focus:outline-none focus:ring focus:ring-emerald-200">
                                                    <button type="submit" class="start-button bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-bold py-1.5 px-3 rounded shadow-sm" id="start-btn" data-task-id="{{$task->id}}">
                                                        {{ __('messages.Start') }}
                                                    </button>
                                                </form>
                                            @endunless

                                            @if($task_timer)
                                                <form class="inline-block stop-form" action="{{ route($routePrefix.'.tasks.stop', ['tasks' => $task->id, 'employee_id' => $employee_id]) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="stop-button bg-red-500 hover:bg-red-600 text-white text-xs font-bold py-1.5 px-3 rounded shadow-sm" data-task-id="{{$task->id}}">
                                                        {{ __('messages.Stop') }}
                                                    </button>
                                                </form>
                                            @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-lg border border-slate-200 mb-8">
                <div class="p-6">
                    <div class="flex flex-col md:flex-row md:items-center justify-between mb-6">
                        <h3 class="text-base font-bold text-slate-800">{{ __('messages.Tasks Timer Table') }}</h3>
                        <div class="flex space-x-2 mt-4 md:mt-0">
                            <button id="exportBtn" class="bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 px-4 py-2 rounded-lg text-sm font-medium shadow-sm transition-colors">{{ __('messages.Export to Excel') }}</button>
                            <button id="exportPDFBtn" class="bg-blue-600 text-white hover:bg-blue-700 px-4 py-2 rounded-lg text-sm font-medium shadow-sm transition-colors">{{ __('messages.Export to PDF') }}</button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <input type="date" id="startDateInput" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                        <input type="date" id="endDateInput" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                        <select id="projectFilterExport" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                            <option value="">{{ __('messages.All Projects') }}</option>
                            @foreach($projects as $project)
                                <option value="{{ $project->id }}">{{ $project->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="overflow-x-auto custom-scrollbar border-t border-slate-100">
                    <table id="taskTable" class="w-full text-left border-collapse">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-6 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">{{ __('messages.Project Name') }}</th>
                                <th class="px-6 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">{{ __('messages.Task Name') }}</th>
                                <th class="px-6 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">{{ __('messages.Start Date') }}</th>
                                <th class="px-6 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">{{ __('messages.End Date') }}</th>
                                <th class="px-6 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">{{ __('messages.Comment') }}</th>
                                <th class="px-6 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">{{ __('messages.Duration') }}</th>
                            </tr>
                        </thead>
                        <tbody id="taskTableBody" class="divide-y divide-slate-100">
                            </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-lg border border-slate-200" style="margin-bottom: 25px;">
                <div class="p-6 border-b border-slate-100">
                    <h3 class="text-base font-bold text-slate-800">{{ __('messages.Work Days Reports') }}</h3>
                </div>
                <div class="overflow-x-auto custom-scrollbar">
                    <table id="editableTable" class="w-full text-left border-collapse">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider leading-tight">{{ __('messages.Number of') }}<br>{{ __('messages.Working Days') }}</th>
                                <th class="px-4 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider leading-tight">{{ __('messages.Number of') }}<br>{{ __('messages.Sick Days') }}</th>
                                <th class="px-4 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider leading-tight">{{ __('messages.Number of') }}<br>{{ __('messages.Public Holidays') }}</th>
                                <th class="px-4 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider leading-tight">{{ __('messages.Remaining') }}<br>{{ __('messages.Paid Leave Days') }}</th>
                                <th class="px-4 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider leading-tight">{{ __('messages.Paid Leave') }}<br>{{ __('messages.Days Accrued') }}</th>
                                <th class="px-4 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider leading-tight">{{ __('messages.Paid Leave') }}<br>{{ __('messages.Days Taken') }}</th>
                                <th class="px-4 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider leading-tight">{{ __('messages.Leave') }}<br>{{ __('messages.Balance') }}</th>
                                <th class="px-4 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider leading-tight">{{ __('messages.Days of') }}<br>{{ __('messages.Absence') }}</th>
                                <th class="px-4 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider leading-tight">{{ __('messages.Paid Leave') }}<br>{{ __('messages.Dates') }}</th>
                                <th class="px-4 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider leading-tight">{{ __('messages.Month') }}</th>
                                <th class="px-4 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider leading-tight">{{ __('messages.Year') }}</th>
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
                <div class="modal-content-custom relative w-full max-w-md mx-auto mt-20 bg-white rounded-xl shadow-2xl p-6 text-center">
                    <span class="close-button absolute top-3 right-4 text-slate-400 hover:text-slate-600 cursor-pointer text-2xl font-bold">&times;</span>
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                        <i class="fas fa-bell text-red-500 text-xl"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 mb-2">{{ __('messages.Timer Alert') }}</h3>
                    <p class="text-sm text-slate-600">{{ __('messages.Did you forget to launch your timer?') }}</p>
                </div>
            </div>

        </main>
        </div>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.3.1/jspdf.umd.min.js"></script>
    <script src="https://unpkg.com/jspdf-autotable@3.5.17/dist/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.4/xlsx.full.min.js"></script>
    
    {{-- Alpine.js for Language Switcher --}}
    <script>
        if (!window.Alpine) {
            const alpineScript = document.createElement('script');
            alpineScript.src = 'https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js';
            alpineScript.defer = true;
            document.head.appendChild(alpineScript);
        }
    </script>

    
    <script>
        var taskIntervals = {};
        
        document.addEventListener('DOMContentLoaded', function() {
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

    {{-- 2. Excel Export --}}
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

    {{-- 3. PDF Export --}}
    <script>
        document.getElementById('exportPDFBtn').addEventListener('click', function() {
            var startDate = document.getElementById('startDateInput').value;
            var endDate = document.getElementById('endDateInput').value;
            var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            var employeeId = {{ $employee_id }};
            var url = '';
            
            @if(Auth::user()->hasRole('Employee'))
                var baseUrlInterval = '/employee/Pointagerepports/downloadRepports/';
                var baseUrlDay = '/employee/Pointagerepports/downloadOneDayRepports/';
            @elseif(Auth::user()->hasRole('Responsable de projet'))
                var baseUrlInterval = '/Responsable_de_projet/Pointagerepports/downloadRepports/';
                var baseUrlDay = '/Responsable_de_projet/Pointagerepports/downloadOneDayRepports/';
            @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                var baseUrlInterval = '/Responsable_gestionnaire/Pointagerepports/downloadRepports/';
                var baseUrlDay = '/Responsable_gestionnaire/Pointagerepports/downloadOneDayRepports/';
            @endif

            if(startDate && endDate) {
                url = baseUrlInterval + startDate + '/' + endDate + '/' + employeeId;
            } else if (startDate && !endDate) {
                url = baseUrlDay + startDate + '/' + employeeId;
            }

            if (url) {
                fetch(url, {
                    method: 'GET',
                    headers: { 'X-CSRF-TOKEN': csrfToken }
                })
                .then(response => response.blob())
                .then(blob => {
                    var url = window.URL.createObjectURL(blob);
                    var a = document.createElement('a');
                    a.href = url;
                    a.download = 'pointageRepports.pdf';
                    document.body.appendChild(a);
                    a.click();      
                    a.remove();           
                })
                .catch((error) => { console.error('Error:', error); });
            }
        });
    </script>

    {{-- 4. Inactivity Alert Modal Logic --}}
    @php 
        $tasks_timer = App\Models\tasks_timer::where('employee_id', $employee_id)->whereDate('start_date', '=', $today)->whereNull('end_date')->first();
    @endphp
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var modal = document.getElementById("customAlertModal");
            var span = document.getElementsByClassName("close-button")[0];
            var alertSound = document.getElementById("alertSound");
            var alertInterval;

            span.onclick = function() { modal.style.display = "none"; }
            function showAlert() { modal.style.display = "block"; alertSound.play(); }
            function startAlertInterval() { clearInterval(alertInterval); alertInterval = setInterval(showAlert, 300000); }
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

            var btns = document.querySelectorAll('#start-btn, #pause-btn, #continue-btn, .stop-button');
            btns.forEach(btn => {
                btn.addEventListener('click', function() { checkTimerState(); });
            });
        });
    </script>

    {{-- 5. Initialize Start Date --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var today = new Date().toISOString().split('T')[0];
            document.getElementById('startDateInput').value = today;
        });
    </script>
    
    <script type="text/javascript">
        var employeeId = @json($employee_id); 
    </script>

    {{-- 6. Fetch Tasks Logic (AJAX Table Population) --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var startDateInput = document.getElementById('startDateInput');
            var endDateInput = document.getElementById('endDateInput');

            // Filter logic for dynamic table (Updated ID to match the export section select)
            document.getElementById('projectFilterExport').addEventListener('change', function() {
                // Use the selected VALUE to check for "All Projects" (empty string)
                var selectedValue = this.value; 
                
                // Use the selected TEXT to compare against table content
                var selectedText = this.options[this.selectedIndex].text.trim().toLowerCase();
                
                var tableRows = document.querySelectorAll('#taskTableBody tr');
                
                var totalDuration = 0;

                // Remove totals
                var totalRow = document.querySelector('.total-row');
                if (totalRow) totalRow.remove();

                tableRows.forEach(row => {
                    var projectCell = row.cells[0];
                    var cellText = projectCell.textContent.trim().toLowerCase();

                    // Logic: If value is empty (All Projects) OR the text matches (Case Insensitive)
                    if (selectedValue === "" || cellText === selectedText) {
                        row.style.display = '';
                        var DurationValue = row.cells[5].textContent.trim();
                        var durationInSeconds = parseDuration(DurationValue);
                        totalDuration += durationInSeconds / 3600;
                    } else {
                        row.style.display = 'none';
                    }
                });

                // Add total row
                var tableBody = document.getElementById('taskTableBody');
                var hours = Math.floor(totalDuration);
                var minutes = Math.round((totalDuration - hours) * 60);
                var formattedDuration = `${hours.toString().padStart(2, '0')}h:${minutes.toString().padStart(2, '0')}m`;
                var totalRowHTML = `
                  <tr class="text-center total-row bg-gray-100 font-bold">
                    <td colspan="5"></td>
                    <td class="px-4 py-3 text-sm text-gray-900">${formattedDuration}</td>
                  </tr>`;
                tableBody.insertAdjacentHTML('beforeend', totalRowHTML);
            });

            window.onload = function() {
                var currentDate = new Date().toISOString().split('T')[0];
                startDateInput.value = currentDate;
                var event = new Event('change');
                startDateInput.dispatchEvent(event);
            }

            endDateInput.addEventListener('change', function() { handleDateChange(); });
            startDateInput.addEventListener('change', function() { handleDateChange(); });

            function handleDateChange() {
                var start = startDateInput.value;
                var end = endDateInput.value;
                if (start && end) fetchTasksForInterval(start, end, employeeId);
                else if (start) fetchTasksForDate(start, employeeId);
            }

            function fetchTasksForInterval(startDate, endDate, employeeId) {
                var url = '';
                @if(Auth::user()->hasRole('Employee')) url = '/employee/tasks/tasks-info-interval-employee/';
                @elseif(Auth::user()->hasRole('Responsable de projet')) url = '/Responsable_de_projet/tasks/tasks-info-interval-employee/';
                @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire')) url = '/Responsable_gestionnaire/tasks/tasks-info-interval-employee/';
                @endif
                
                url += startDate + '/' + endDate + '/' + employeeId;

                fetchAndPopulateTable(url);
            }

            function fetchTasksForDate(selectedDate, employeeId) {
                var url = '';
                @if(Auth::user()->hasRole('Employee')) url = '/employee/tasks/tasks-info/';
                @elseif(Auth::user()->hasRole('Responsable de projet')) url = '/Responsable_de_projet/tasks/tasks-info/';
                @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire')) url = '/Responsable_gestionnaire/tasks/tasks-info/';
                @endif

                url += selectedDate + '/' + employeeId;
                fetchAndPopulateTable(url);
            }

            function fetchAndPopulateTable(url) {
                fetch(url)
                    .then(response => response.json())
                    .then(data => {
                        var tableBody = document.getElementById('taskTableBody');
                        if (tableBody) {
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
                                var endDateText = task.end_date ? task.end_date : {!! json_encode(__('messages.Still working')) !!};

                                var fetchurl = '';
                                @if(Auth::user()->hasRole('Employee')) fetchurl = '/employee/tasks/get-project-name/';
                                @elseif(Auth::user()->hasRole('Responsable de projet')) fetchurl = '/Responsable_de_projet/tasks/get-project-name/';
                                @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire')) fetchurl = '/Responsable_gestionnaire/tasks/get-project-name/';
                                @endif

                                return fetch(fetchurl + encodeURIComponent(task.task_id))
                                    .then(response => response.json())
                                    .then(projectData => {
                                        var fetchEmpUrl = '';
                                        @if(Auth::user()->hasRole('Employee')) fetchEmpUrl = '/employee/tasks/get-employee-name/';
                                        @elseif(Auth::user()->hasRole('Responsable de projet')) fetchEmpUrl = '/Responsable_de_projet/tasks/get-employee-name/';
                                        @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire')) fetchEmpUrl = '/Responsable_gestionnaire/tasks/get-employee-name/';
                                        @endif

                                        return fetch(fetchEmpUrl + encodeURIComponent(task.employee_id))
                                            .then(response => response.json())
                                            .then(employeeData => {
                                                var rowId = 'row-' + task.id;
                                                let d_h = Math.floor(task.duration / 60);
                                                let d_m = task.duration % 60;
                                                let formattedD = `${d_h.toString().padStart(2, '0')}h:${d_m.toString().padStart(2, '0')}m:00s`;

                                var row = `
                                                  <tr id="${rowId}" class="text-center hover:bg-gray-50">
                                                      <td class="px-4 py-3 text-sm text-gray-700">${projectData.name.toUpperCase()}</td>                      
                                                      <td class="px-4 py-3 text-sm text-gray-700 font-medium">${task.task_name}</td>
                                                      <td class="px-4 py-3 text-sm text-gray-700">${task.start_date}</td>
                                                      <td class="px-4 py-3 text-sm text-gray-700">${endDateText}</td>
                                                      <td class="px-4 py-3 text-sm text-gray-700">
                                                          <span class="comment-display cursor-pointer hover:text-blue-600" style="display: block;">${task.comment ? task.comment : {!! json_encode(__('messages.No Comment')) !!}}</span>
                                                          <input type="text" 
                                                                 class="comment-input border border-gray-300 rounded px-2 py-1 text-sm text-gray-700 focus:outline-none focus:ring focus:ring-blue-200 w-full" 
                                                                 value="${task.comment ? task.comment.replace(/"/g, '&quot;') : ''}" 
                                                                 data-timer-id="${task.id}"
                                                                 style="display: none;">
                                                      </td>
                                                      <td class="px-4 py-3 text-sm text-gray-700 task_duration">${task.end_date ? formattedD : ""}</td>
                                                  </tr>`;
                                                
                                                tableBody.insertAdjacentHTML('beforeend', row);

                                                if (task.end_date === null) {
                                                    // Live timer logic for active tasks in this table
                                                    var startDate = new Date(task.start_date.replace(' ', 'T'));
                                                    var taskId = task.id;
                                                    var rowId = 'row-' + taskId;
                                                    var elapsedTime = 0;
                                                    
                                                    if(taskIntervals[taskId]) clearInterval(taskIntervals[taskId]);
                                                    
                                                    taskIntervals[taskId] = setInterval(function() {
                                                        var now = new Date();
                                                        var durationInMilliseconds = Math.max(0, now - startDate + elapsedTime);
                                                        var h = Math.floor(durationInMilliseconds / 3600000);
                                                        var m = Math.floor((durationInMilliseconds % 3600000) / 60000);
                                                        var s = Math.floor((durationInMilliseconds % 60000) / 1000);
                                                        var durFmt = (h < 10 ? '0' : '') + h + 'h:' + (m < 10 ? '0' : '') + m + 'm:' + (s < 10 ? '0' : '') + s + 's';
                                                        
                                                        var durationCell = document.querySelector('#' + rowId + ' .task_duration');
                                                        if (durationCell) durationCell.textContent = durFmt;
                                                        else {
                                                            clearInterval(taskIntervals[taskId]);
                                                            delete taskIntervals[taskId];
                                                        }
                                                    }, 1000);
                                                }
                                                
                                                var rowElement = document.getElementById(rowId);
                                                // Use the formatted duration we just calculated/fetched, NOT textContent which might be empty initially
                                                var dString = rowElement.querySelector('.task_duration').textContent.trim();
                                                if(dString) {
                                                    var dSec = parseDuration(dString);
                                                    totalDuration += dSec / 3600;
                                                }
                                            });
                                    });
                            })).then(() => {
                                // Trigger the filter logic after data is loaded to respect any pre-selected value
                                var event = new Event('change');
                                document.getElementById('projectFilterExport').dispatchEvent(event);
                            });
                        }
                    });
            }

            function parseDuration(durationString) {
                if(!durationString) return 0;
                var parts = durationString.split(':');
                if(parts.length < 3) return 0; 
                var hours = parseInt(parts[0].replace('h', ''), 10);
                var minutes = parseInt(parts[1].replace('m', ''), 10);
                var seconds = parseInt(parts[2].replace('s', ''), 10);
                return hours * 3600 + minutes * 60 + seconds;
            }
        });
    </script>

    {{-- 7. JQuery AJAX for Start/Stop --}}
    <script>
        $(document).ready(function() {
            $('.start-button').click(function() {
                var taskId = $(this).data('task-id');
                var employeeId = @json($employee_id); // Fixed usage
                $.ajax({
                    url: '/tasks/' + taskId + '/start/' + employeeId,
                    type: 'POST',
                    data: { task_id: taskId, employee_id: employeeId },
                    success: function(response) { /* handle success */ },
                    error: function(response) { /* handle error */ }
                });
            });

            $('.stop-button').click(function() {
                var taskId = $(this).data('task-id');
                var employeeId = @json($employee_id); // Fixed usage
                $.ajax({
                    url: '/tasks/' + taskId + '/stop/' + employeeId,
                    type: 'POST',
                    data: { task_id: taskId, employee_id: employeeId },
                    success: function(response) { /* handle success */ },
                    error: function(response) { /* handle error */ }
                });
            });
        });
    </script>

    {{-- 8. Close Banner Logic --}}
    <script>
        function closeBanner() {
            var closeButton = document.querySelector('#close-bar');
            if (closeButton) closeButton.click();
        }
        window.onload = closeBanner;
    </script>

    {{-- 9. Client-side Filtering for Main Task Table --}}
    <script>
        document.getElementById('projectFilter').addEventListener('change', function() {
            var filter = this.value.toLowerCase();
            var rows = document.querySelectorAll('#tasksTable tbody tr');
            var currentProject = null;
            var shouldShowCurrentProject = false;

            rows.forEach(function(row) {
                // Check for project header row (colspan matches the header divider row)
                if (row.cells.length === 1 && row.cells[0].colSpan === 8) {
                    currentProject = row.cells[0].textContent.trim().toLowerCase();
                    shouldShowCurrentProject = filter === "" || currentProject.includes(filter);
                    row.style.display = shouldShowCurrentProject ? "" : "none";
                } else {
                    row.style.display = shouldShowCurrentProject ? "" : "none";
                }
            });
        });
    </script>

    {{-- 10. Inline Comment Editing --}}
    <script>
        // Inline comment editing handlers
        document.addEventListener('click', function(e) {
            const cell = e.target.closest('td');
            if (!cell) return;
            const display = cell.querySelector('.comment-display');
            const input = cell.querySelector('.comment-input');
            if (display && input && (e.target === display)) {
                display.style.display = 'none';
                input.style.display = 'block';
                input.focus();
                input.setSelectionRange(input.value.length, input.value.length);
            }
        });

        async function saveComment(inputEl) {
            const timerId = inputEl.getAttribute('data-timer-id');
            const newComment = inputEl.value.trim();
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            @if(Auth::user()->hasRole('Employee'))
            var url = `/employee/tasks/timers/${timerId}/comment`;
            @endif
            @if(Auth::user()->hasRole('Responsable de projet'))
            var url = `/Responsable_de_projet/tasks/timers/${timerId}/comment`;
            @endif
            @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
            var url = `/Responsable_gestionnaire/tasks/timers/${timerId}/comment`;
            @endif
            try {
                const res = await fetch(url, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ comment: newComment })
                });
                if (!res.ok) throw new Error('Failed to save comment');
                const data = await res.json();
                const cell = inputEl.closest('td');
                const display = cell.querySelector('.comment-display');
                display.textContent = data.comment && data.comment.length ? data.comment : {!! json_encode(__('messages.No Comment')) !!};
                inputEl.style.display = 'none';
                display.style.display = '';
                window.location.reload();
            } catch (err) {
                console.error(err);
                // Revert UI on error
                const cell = inputEl.closest('td');
                const display = cell.querySelector('.comment-display');
                inputEl.style.display = 'none';
                display.style.display = '';
            }
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                const inputEl = e.target.closest('.comment-input');
                if (inputEl) {
                    e.preventDefault();
                    saveComment(inputEl);
                }
            }
            if (e.key === 'Escape') {
                const inputEl = e.target.closest('.comment-input');
                if (inputEl) {
                    const cell = inputEl.closest('td');
                    const display = cell.querySelector('.comment-display');
                    inputEl.style.display = 'none';
                    display.style.display = '';
                }
            }
        });

        document.addEventListener('blur', function(e) {
            const inputEl = e.target.closest('.comment-input');
            if (inputEl) {
                // Save on blur
                saveComment(inputEl);
            }
        }, true);
    </script>

</body>
</html>

