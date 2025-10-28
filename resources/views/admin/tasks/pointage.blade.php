<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tasks Timer | BizManager</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    @vite('resources/css/app.css')
</head>
<body class="bg-gray-50 flex h-screen overflow-hidden">
    {{-- Sidebar --}}
    <x-dashboard.sidebar />

    <div class="flex flex-col flex-1 overflow-y-auto">
        {{-- Navbar --}}
        <x-dashboard.navbar />

        {{-- Main Content Area --}}
        <div class="flex-1 overflow-auto bg-gray-50">
            
            {{-- ANIMATION: Style block from the target example --}}
            <style>
                @keyframes fadeInSlideUp {
                    from { opacity: 0; transform: translateY(20px); }
                    to { opacity: 1; transform: translateY(0); }
                }
                .animate-fadeInSlideUp {
                    opacity: 0;
                    animation: fadeInSlideUp 0.5s ease-out forwards;
                }
                /* Custom styles for highlighted table rows */
                .highlighted-task {
                    background-color: #fef9c3; /* yellow-100 */
                }
                .regie-task {
                    background-color: #eff6ff; /* blue-50 */
                }
                /* Ensure modals are on top */
                .modal {
                    display: none; /* Hidden by default, JS controls this */
                }
                .modal.show {
                    display: block;
                }
            </style>

            {{-- Main content --}}
            <div class="p-8">

                {{-- The main card, restyled and animated --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 hover:shadow-xl transition-all duration-300 animate-fadeInSlideUp" style="animation-delay: 100ms;">
                    
                    {{-- Card Header: Title and Export Buttons --}}
                    <div class="flex flex-wrap items-center justify-between p-6 border-b border-gray-200 gap-4">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900">Tasks Timer Table</h2>
                            <p class="text-sm text-gray-500">Pointage Reports</p>
                        </div>
                        <div class="flex items-center space-x-2">
                            {{-- NEW: Buttons with Icons --}}
                            <button id="exportBtn" class="flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4"><path d="M10.75 2.75a.75.75 0 0 0-1.5 0v8.614L6.295 8.235a.75.75 0 1 0-1.09 1.03l4.25 4.5a.75.75 0 0 0 1.09 0l4.25-4.5a.75.75 0 0 0-1.09-1.03l-2.955 3.129V2.75Z" /><path d="M3.5 12.75a.75.75 0 0 0-1.5 0v2.5A2.75 2.75 0 0 0 4.75 18h10.5A2.75 2.75 0 0 0 18 15.25v-2.5a.75.75 0 0 0-1.5 0v2.5c0 .69-.56 1.25-1.25 1.25H4.75c-.69 0-1.25-.56-1.25-1.25v-2.5Z" /></svg>
                                Excel
                            </button>
                            <button id="exportPDFBtn" class="flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4"><path d="M10.75 2.75a.75.75 0 0 0-1.5 0v8.614L6.295 8.235a.75.75 0 1 0-1.09 1.03l4.25 4.5a.75.75 0 0 0 1.09 0l4.25-4.5a.75.75 0 0 0-1.09-1.03l-2.955 3.129V2.75Z" /><path d="M3.5 12.75a.75.75 0 0 0-1.5 0v2.5A2.75 2.75 0 0 0 4.75 18h10.5A2.75 2.75 0 0 0 18 15.25v-2.5a.75.75 0 0 0-1.5 0v2.5c0 .69-.56 1.25-1.25 1.25H4.75c-.69 0-1.25-.56-1.25-1.25v-2.5Z" /></svg>
                                PDF
                            </button>
                            <button id="exportPDFBtnCustomer" class="flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4"><path d="M10.75 2.75a.75.75 0 0 0-1.5 0v8.614L6.295 8.235a.75.75 0 1 0-1.09 1.03l4.25 4.5a.75.75 0 0 0 1.09 0l4.25-4.5a.75.75 0 0 0-1.09-1.03l-2.955 3.129V2.75Z" /><path d="M3.5 12.75a.75.75 0 0 0-1.5 0v2.5A2.75 2.75 0 0 0 4.75 18h10.5A2.75 2.75 0 0 0 18 15.25v-2.5a.75.75 0 0 0-1.5 0v2.5c0 .69-.56 1.25-1.25 1.25H4.75c-.69 0-1.25-.56-1.25-1.25v-2.5Z" /></svg>
                                PDF Customer
                            </button>
                        </div>
                        
                        @if(session('alertmessage'))
                        <div class="w-full bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg relative" role="alert">
                            <strong class="font-bold">Error!</strong>
                            <span class="block sm:inline">{{ session('alertmessage') }}</span>
                            <button type="button" class="absolute top-0 bottom-0 right-0 px-4 py-3" data-bs-dismiss="alert" aria-label="Close">
                                <svg class="fill-current h-6 w-6 text-red-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>Close</title><path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 2.03a1.2 1.2 0 1 1-1.697-1.697l2.03-2.651-2.03-2.651a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-2.03a1.2 1.2 0 1 1 1.697 1.697l-2.03 2.651 2.03 2.651a1.2 1.2 0 0 1 0 1.697z"/></svg>
                            </button>
                        </div>
                        @endif
                    </div>

                    {{-- NEW: Totals Bar (Restyled) --}}
                    <div class="grid grid-cols-1 sm:grid-cols-3 divide-y sm:divide-y-0 sm:divide-x border-b border-gray-200">
                        <div class="p-6">
                            <label class="block text-sm font-medium text-gray-500">Total Duration</label>
                            <p class="text-2xl font-bold text-gray-900">0h:00m</p>
                        </div>
                        <div class="p-6">
                            <label class="block text-sm font-medium text-gray-500">Total Operating Cost</label>
                            <p class="text-2xl font-bold text-green-600">0.00€</p>
                        </div>
                        <div class="p-6">
                            <label class="block text-sm font-medium text-gray-500">Total Sales</label>
                            <p class="text-2xl font-bold text-gray-900">0.00€</p>
                        </div>
                    </div>


                    {{-- Date Filter Form --}}
                    <div class="p-6 border-b border-gray-200">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Fetch Data:</label>
                        
                        @if(Auth::user()->hasRole('Admin'))
                        <form method="GET" action="{{ route('admin.pointage.filterPointageByDate') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end" id="dateForm" >
                        @elseif(Auth::user()->hasRole('Responsable de projet'))
                        <form method="GET" action="{{ route('Responsable_de_projet.pointage.filterPointageByDate') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end" id="dateForm" >
                        @elseif(Auth::user()->hasRole('Employee'))
                        <form method="GET" action="{{ route('employee.pointage.filterPointageByDate') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end" id="dateForm" >
                        @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                        <form method="GET" action="{{ route('Responsable_gestionnaire.pointage.filterPointageByDate') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end" id="dateForm" >
                        @endif
                            @csrf
                            <div class="md:col-span-1">
                                <label for="monthFilter" class="block text-xs font-medium text-gray-600 mb-1">Month</label>
                                <select id="monthFilter" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm text-gray-700 focus:border-blue-500 focus:outline-none" name="month">
                                    <option value="">Select Month</option>
                                    @for($i = 1; $i <= 12; $i++)
                                        <option value="{{ $i }}">{{ date('F', mktime(0, 0, 0, $i, 1)) }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="md:col-span-1">
                                <label for="startDateInput" class="block text-xs font-medium text-gray-600 mb-1">Start Date</label>
                                <input type="date" id="startDateInput" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm text-gray-700 focus:border-blue-500 focus:outline-none" name="start_date">
                            </div>
                            <div class="md:col-span-1">
                                <label for="endDateInput" class="block text-xs font-medium text-gray-600 mb-1">End Date</label>
                                <input type="date" id="endDateInput" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm text-gray-700 focus:border-blue-500 focus:outline-none" name="end_date">
                            </div>
                            <div class="md:col-span-2 flex items-center space-x-2">
                                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg">Get</button>
                                @if(Auth::user()->hasRole('Admin'))
                                <a id="employeesDailyTotalsBtn" class="w-full text-center bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 text-sm font-medium px-4 py-2 rounded-lg" target="_blank">Employees PDF</a>
                                @endif
                            </div>
                        </form>
                    </div>

                    {{-- Content Filter Form --}}
                    <div class="p-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Filter by:</label>
                        
                        {{-- NEW: Grid is md:grid-cols-5 --}}
                        @if(Auth::user()->hasRole('Admin'))
                        <form method="GET" action="{{ route('admin.pointage.filterPointageTable') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end" id="filterForm">
                        @elseif(Auth::user()->hasRole('Responsable de projet'))
                        <form method="GET" action="{{ route('Responsable_de_projet.pointage.filterPointageTable') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end" id="filterForm">
                        @elseif(Auth::user()->hasRole('Employee'))
                        <form method="GET" action="{{ route('employee.pointage.filterPointageTable') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end" id="filterForm">
                        @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                        <form method="GET" action="{{ route('Responsable_gestionnaire.pointage.filterPointageTable') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end" id="filterForm">
                        @endif
                            @csrf
                            <input type="hidden" id="hiddenStartDateInput" name="start_date">
                            <input type="hidden" id="hiddenEndDateInput" name="end_date">
                            
                            <div class="md:col-span-1">
                                <label for="clientFilter" class="block text-xs font-medium text-gray-600 mb-1">Client</label>
                                <select id="clientFilter" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm text-gray-700 focus:border-blue-500 focus:outline-none" name="client">
                                    <option value="">All Clients</option>
                                    @foreach($clients as $client)
                                    <option value="{{ $client->id }}" {{ request('client') == $client->id ? 'selected' : '' }}>
                                        {{ $client->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="md:col-span-1">
                                <label for="employeeFilter" class="block text-xs font-medium text-gray-600 mb-1">Employee</label>
                                <select id="employeeFilter" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm text-gray-700 focus:border-blue-500 focus:outline-none" name="employee">
                                    <option value="">All Employees</option>
                                    @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}" {{ request('employee') == $employee->id ? 'selected' : '' }}>
                                        {{ $employee->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="md:col-span-1">
                                <label for="projectFilter" class="block text-xs font-medium text-gray-600 mb-1">Project</label>
                                <select id="projectFilter" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm text-gray-700 focus:border-blue-500 focus:outline-none" name="project">
                                    <option value="">All Projects</option>
                                    @foreach($projects as $project)
                                    <option value="{{ $project->id }}" 
                                            {{ request('project') == $project->id ? 'selected' : '' }}
                                            data-tva="{{ $project->tva ?? 0 }}">
                                        {{ strtoupper($project->name) }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- NEW: Task filter is no longer 'multiple' --}}
                            <div class="md:col-span-1">
                                <label for="taskFilter" class="block text-xs font-medium text-gray-600 mb-1">Task</label>
                                <select id="taskFilter" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm text-gray-700 focus:border-blue-500 focus:outline-none" name="task">
                                    <option value="">All Tasks</option>
                                    @foreach($allTasks as $task)
                                    <option value="{{ $task->id }}" {{ $task->id == request('task') ? 'selected' : '' }}>
                                        {{ $task->title }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- REMOVED: Project Type filter removed to match image --}}

                            <div class="md:col-span-1">
                                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg">Filter</button>
                            </div>
                        </form>
                        
                        {{-- NEW: Invoice Comment (Restructured) --}}
                        <div class="mt-6">
                            <label for="periodInvoiceComment" class="block text-sm font-medium text-gray-700 mb-2">Invoice Comment</label>
                            <textarea class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm text-gray-700 focus:border-blue-500 focus:outline-none" 
                                      id="periodInvoiceComment" 
                                      placeholder="Add any additional notes or comments here..." 
                                      style="resize: vertical; min-height: 90px;"></textarea>
                            <button id="generatePeriodInvoiceBtn" class="mt-4 bg-green-600 hover:bg-green-700 text-white text-sm font-medium px-4 py-2 rounded-lg">
                                Generate Period Invoice
                            </button>
                        </div>
                    </div>

                    {{-- Table --}}
                    <div class="overflow-x-auto">
                        <table id="taskTable" class="w-full text-left border-collapse">
                            <thead class="border-b border-t border-gray-200">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Project name</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">task name</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Employee name</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">start date</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">end date</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">duration</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">operating cost</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Sales</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Comment</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="taskTableBody" class="divide-y divide-gray-200">
                                @php
                                function formatDuration($minutes) {
                                    $hours = floor($minutes / 60);
                                    $minutes = $minutes % 60;
                                    $seconds = 0; // Assuming no seconds are provided
                                    return sprintf('%02dh:%02dm:%02ds', $hours, $minutes, $seconds);
                                }
                                @endphp
                        
                                {{-- NEW: Check if tasks_timer is empty --}}
                                @if(count($tasks_timer) > 0)
                                    @foreach($tasks_timer as $start_date => $tasks)
                                        @php
                                        try {
                                            if (is_string($start_date) && strpos($start_date, ' - ') !== false) {
                                                list($dayName, $datePart) = explode(' - ', $start_date);
                                                $dateObject = \Carbon\Carbon::createFromFormat('Y-m-d', $datePart);
                                                $displayDate = $dayName . ' - ' . $dateObject->format('d-m-Y');
                                            } else {
                                                $dateObject = is_string($start_date) 
                                                    ? \Carbon\Carbon::createFromFormat('Y-m-d', $start_date)
                                                    : $start_date;
                                                $displayDate = $dateObject->format('l - d-m-Y');
                                            }
                                            $groupTotal = 0;
                                            $groupTotalRecette = 0;
                                            $groupTotalDuration = 0;
                                        } catch (\Exception $e) {
                                            $dateObject = null;
                                            Log::error("Invalid date format: " . $start_date . ". Error: " . $e->getMessage());
                                            continue;
                                        }
                                        @endphp

                                        @if($dateObject)
                                        {{-- Date Group Header Row --}}
                                        <tr class="bg-gray-100">
                                            <td colspan="10" class="px-6 py-3 text-left text-sm font-bold text-gray-800">
                                                {{ $displayDate }}
                                            </td>
                                        </tr>
                                        @endif
                                
                                        @foreach($tasks as $task)
                                            @php 
                                                $task_project_id = App\Models\Tasks::where('id', $task->task_id)->first()->project_id;
                                                $project = App\Models\Project::where('id', $task_project_id)->first();
                                                $employee = App\Models\Employees::where('id', $task->employee_id)->first();
                                                $groupTotalDuration += $task->duration;
                                                $groupTotal += $task->end_date ? $task->cost : 0;
                                                $groupTotalRecette += $task->end_date ? (($task->duration / 60) * $project->cost_per_hour) : 0;
                                                $hasInvoice = in_array($task_project_id, $periodInvoices);
                                                $isRegie = $project->regie === 1;
                                            @endphp
                                    
                                            {{-- Task Data Row --}}
                                            <tr class="text-left hover:bg-gray-50 {{ $hasInvoice ? 'highlighted-task' : '' }} {{ $isRegie ? 'regie-task' : '' }}" 
                                                id="{{ $task->id }}" 
                                                data-employee-id="{{ $task->employee_id }}"
                                                data-project-id="{{ $task_project_id}}" 
                                                data-task-id="{{ $task->task_id }}">
                                                
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                    @if(Auth::user()->hasRole('Responsable de projet'))
                                                        @if(Auth::user()->can('View project'))
                                                        <a href="{{route('Responsable_de_projet.projects.show',$task_project_id)}}" class="text-blue-600 hover:underline">{{ $project->name }}</a>
                                                        @else
                                                        {{ $project->name }}
                                                        @endif
                                                    @elseif(Auth::user()->hasRole('Admin'))
                                                        @if(Auth::user()->can('View project'))
                                                        <a href="{{route('admin.projects.show',$task_project_id)}}" class="text-blue-600 hover:underline">{{ $project->name }}</a>
                                                        @else
                                                        {{ $project->name }}
                                                        @endif
                                                    @elseif(Auth::user()->hasRole('Employee'))
                                                        @if(Auth::user()->can('View project'))
                                                        <a href="{{route('employee.projects.show',$task_project_id)}}" class="text-blue-600 hover:underline">{{ $project->name }}</a>
                                                        @else
                                                        {{ $project->name }}
                                                        @endif
                                                    @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                                        <a href="{{route('Responsable_gestionnaire.projects.show',$task_project_id)}}" class="text-blue-600 hover:underline">{{ $project->name }}</a>
                                                    @endif
                                                </td>
                                                
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                                    @if(auth()->user()->hasRole('Admin'))
                                                    <a href="{{route('admin.tasks.show', $task->task_id)}}" class="text-blue-600 hover:underline">
                                                    @elseif(auth()->user()->hasRole('Responsable de projet'))
                                                    <a href="{{route('Responsable_de_projet.tasks.show', $task->task_id)}}" class="text-blue-600 hover:underline">
                                                    @elseif(auth()->user()->hasRole('Employee'))
                                                    <a href="{{route('employee.tasks.show', $task->task_id)}}" class="text-blue-600 hover:underline">
                                                    @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                                    <a href="{{route('Responsable_gestionnaire.tasks.show', $task->task_id)}}" class="text-blue-600 hover:underline">
                                                    @endif
                                                        {{ $task->task_name }}
                                                    </a>
                                                </td>
                                                
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                                    @if(Auth::user()->hasRole('Responsable de projet'))
                                                        @if(Auth::user()->can('View employee'))
                                                        <a href="{{route('Responsable_de_projet.employees.show',$employee->id )}}" class="text-blue-600 hover:underline">{{ $employee->name }}</a>
                                                        @else
                                                        {{ $employee->name }}
                                                        @endif
                                                    @elseif(Auth::user()->hasRole('Admin'))
                                                        @if(Auth::user()->can('View employee'))
                                                        <a href="{{route('admin.employees.show',$employee->id )}}" class="text-blue-600 hover:underline">{{ $employee->name }}</a>
                                                        @else
                                                        {{ $employee->name }}
                                                        @endif
                                                    @elseif(Auth::user()->hasRole('Employee'))
                                                        @if(Auth::user()->can('View employee'))
                                                        <a href="{{route('employee.employees.show',$employee->id )}}" class="text-blue-600 hover:underline">{{ $employee->name }}</a>
                                                        @else
                                                        {{ $employee->name }}
                                                        @endif
                                                    @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                                        <a href="{{route('Responsable_gestionnaire.employees.show',$employee->id )}}" class="text-blue-600 hover:underline">{{ $employee->name }}</a>
                                                    @endif
                                                </td>
                                                
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 cursor-pointer clickable" data-field="start_date" data-task-id="{{ $task->id }}">
                                                    {{ \Carbon\Carbon::parse($task->start_date)->format('d-m-Y H:i:s') }}
                                                </td>
                                                
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 cursor-pointer clickable" data-field="end_date" data-task-id="{{ $task->id }}">
                                                    {{ $task->end_date ? \Carbon\Carbon::parse($task->end_date)->format('d-m-Y H:i:s') : 'Still Working' }}
                                                </td>
                                                
                                                <td id="duration-{{ $task->id }}" class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 duration" data-task-id="{{ $task->id }}">
                                                    @if (is_null($task->end_date))
                                                        {{-- This script is preserved from your original file --}}
                                                        <script>
                                                        document.addEventListener('DOMContentLoaded', function () {
                                                            function startTimer(taskId, startDate) {
                                                                const durationCellId = 'duration-' + taskId;
                                                                let retryCount = 0;
                                                                const maxRetries = 10;
                                                                var intervalId = setInterval(function() {
                                                                    var durationInMilliseconds = new Date() - new Date(startDate);
                                                                    var hours = Math.floor(durationInMilliseconds / 3600000);
                                                                    var minutes = Math.floor((durationInMilliseconds % 3600000) / 60000);
                                                                    var seconds = Math.floor((durationInMilliseconds % 60000) / 1000);
                                                                    var durationFormatted = (hours < 10 ? '0' : '') + hours + 'h:' + (minutes < 10 ? '0' : '') + minutes + 'm:' + (seconds < 10 ? '0' : '') + seconds + 's';
                                                                    var durationCell = document.getElementById(durationCellId);
                                                                    if (durationCell) {
                                                                        durationCell.textContent = durationFormatted;
                                                                    } else {
                                                                        retryCount++;
                                                                        if (retryCount >= maxRetries) {
                                                                            console.error('Element not found: #' + durationCellId);
                                                                            clearInterval(intervalId);
                                                                        }
                                                                    }
                                                                }, 1000);
                                                            }
                                                            startTimer('{{ $task->id }}', '{{ $task->start_date }}');
                                                        });
                                                        </script>
                                                    @else
                                                        {{ formatDuration($task->duration) }}
                                                    @endif
                                                </td>
                                                
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                                    {{ $task->end_date ? number_format((float)$task->cost, 3, '.', '') : '0.00' }}€
                                                </td>
                                                
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                                    {{ $task->end_date ? number_format((($task->duration / 60) * $project->cost_per_hour), 3, '.', '') : '0.00' }}€
                                                </td>
                                                
                                                <td class="px-6 py-4 text-sm text-gray-600 min-w-[200px] editable-comment" data-id="{{ $task->id }}" data-field="comment" data-update-url="{{ route('admin.tasks.updateField', ['field' => 'comment', 'id' => $task->id]) }}">
                                                    <span class="comment-text">{{ $task->comment }}</span>
                                                </td>

                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                    @if($task->end_date)
                                                    <button class="bg-blue-100 text-blue-700 hover:bg-blue-200 text-xs font-medium px-3 py-1 rounded-lg move-duration-btn" 
                                                            data-task-id="{{$task->id}}" 
                                                            data-project-id="{{$task_project_id}}" 
                                                            data-employee-id="{{$task->employee_id}}"
                                                            data-start-date="{{\Carbon\Carbon::parse($task->start_date)->format('Y-m-d\TH:i')}}"
                                                            data-end-date="{{\Carbon\Carbon::parse($task->end_date)->format('Y-m-d\TH:i')}}"
                                                            data-duration="{{$task->duration}}">
                                                        Move Duration 
                                                    </button>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                
                                        {{-- Date Group Total Row --}}
                                        <tr class="text-left bg-gray-50 border-t-2 border-gray-200">
                                            <td colspan="5" class="px-6 py-3 text-right font-bold text-gray-800">Day Total:</td>
                                            <td class="px-6 py-3 font-bold text-gray-800">{{ formatDuration($groupTotalDuration) }}</td>
                                            <td class="px-6 py-3 font-bold text-gray-800">{{ number_format($groupTotal, 3, ',', '') }}€</td>
                                            <td class="px-6 py-3 font-bold text-gray-800">{{ number_format($groupTotalRecette, 3, ',', '') }}€</td>
                                            <td colspan="2"></td>
                                        </tr>
                                    @endforeach
                                @else
                                    {{-- NEW: Empty state row --}}
                                    <tr>
                                        <td colspan="10" class="text-center px-6 py-10 text-gray-500">
                                            No tasks found for this period.
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="editDateModal" tabindex="-1" aria-labelledby="editDateModalLabel" aria-hidden="true">
        {{-- Restyled with Tailwind --}}
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4 text-center">
                <div class="relative bg-white rounded-lg shadow-xl transform transition-all sm:my-8 sm:w-full sm:max-w-lg text-left">
                    {{-- modal-content --}}
                    <div class="flex items-center justify-between p-4 border-b rounded-t">
                        {{-- modal-header --}}
                        <h5 class="text-lg font-semibold text-gray-900" id="editDateModalLabel">Edit Date and Time</h5>
                        <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center" data-bs-dismiss="modal" aria-label="Close">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                        </button>
                    </div>
                    <div class="p-6 space-y-4">
                        {{-- modal-body --}}
                        <form id="editDateForm">
                            <div class="mb-3">
                                <label for="dateInput" class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                                <input type="date" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm text-gray-700 focus:border-blue-500 focus:outline-none" id="dateInput" required>
                            </div>
                            <div class="mb-3">
                                <label for="timeInput" class="block text-sm font-medium text-gray-700 mb-1">Time</label>
                                <input type="time" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm text-gray-700 focus:border-blue-500 focus:outline-none" id="timeInput" required>
                            </div>
                            <input type="hidden" id="taskId" value="">
                            <input type="hidden" id="fieldName" value="">
                        </form>
                    </div>
                    <div class="flex items-center justify-end p-4 border-t border-gray-200 rounded-b space-x-2">
                        {{-- modal-footer --}}
                        <button type="button" class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 text-sm font-medium px-4 py-2 rounded-lg" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg" id="saveChangesBtn">Save changes</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="moveDurationModal" tabindex="-1" aria-labelledby="moveDurationModalLabel" aria-hidden="true">
        {{-- Restyled with Tailwind --}}
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4 text-center">
                <div class="relative bg-white rounded-lg shadow-xl transform transition-all sm:my-8 sm:w-full sm:max-w-lg text-left">
                    {{-- modal-content --}}
                    <div class="flex items-center justify-between p-4 border-b rounded-t">
                        {{-- modal-header --}}
                        <h5 class="text-lg font-semibold text-gray-900" id="moveDurationModalLabel">Move Duration</h5>
                        <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center" data-bs-dismiss="modal" aria-label="Close">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                        </button>
                    </div>
                    <div class="p-6 space-y-4">
                        {{-- modal-body --}}
                        <form id="moveDurationForm">
                            <div class="mb-3">
                                <label for="projectSelect" class="block text-sm font-medium text-gray-700 mb-1">Project</label>
                                <select class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm text-gray-700 focus:border-blue-500 focus:outline-none" id="projectSelect" required>
                                    @foreach($projects as $project)
                                        <option value="{{ $project->id }}">{{ $project->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="taskSelect" class="block text-sm font-medium text-gray-700 mb-1">Task</label>
                                <select class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm text-gray-700 focus:border-blue-500 focus:outline-none" id="taskSelect" required>
                                    </select>
                            </div>
                            <div class="mb-3">
                                <label for="employeeSelect" class="block text-sm font-medium text-gray-700 mb-1">Employee</label>
                                <select class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm text-gray-700 focus:border-blue-500 focus:outline-none" id="employeeSelect" required>
                                    @foreach($employees as $employee)
                                        <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="moveStartDate" class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                                <input type="datetime-local" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm text-gray-700 focus:border-blue-500 focus:outline-none" id="moveStartDate" required>
                            </div>
                            <div class="mb-3">
                                <label for="moveEndDate" class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                                <input type="datetime-local" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm text-gray-700 focus:border-blue-500 focus:outline-none" id="moveEndDate" required>
                            </div>
                            <div class="mb-3">
                                <label for="moveDuration" class="block text-sm font-medium text-gray-700 mb-1">Duration (HH:MM)</label>
                                <input type="text" 
                                       class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm text-gray-700 focus:border-blue-500 focus:outline-none" 
                                       id="moveDuration" 
                                       required 
                                       pattern="^([0-9]{2}h:[0-9]{2}m)$"
                                       placeholder="00h:00m">
                            </div>
                            <input type="hidden" id="moveDurationMinutes">
                            <input type="hidden" id="originalTaskId" value="">
                        </form>
                    </div>
                    <div class="flex items-center justify-end p-4 border-t border-gray-200 rounded-b space-x-2">
                        {{-- modal-footer --}}
                        <button type="button" class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 text-sm font-medium px-4 py-2 rounded-lg" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg" id="saveMoveChangesBtn">Save changes</button>
                    </div>
                </div>
            </div>
        </div>
    </div>


    {{-- 
        Original scripts from your file.
        This @include is necessary for your Bootstrap Modals and jQuery AJAX calls.
    --}}
    @include('dashboard_layouts.scripts')
    
    {{-- Livewire scripts from the new layout --}}
    @livewireScripts

    {{-- All the custom scripts from your original file are preserved below --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.3.1/jspdf.umd.min.js"></script>
    <script src="https://unpkg.com/jspdf-autotable@3.5.17/dist/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.4/xlsx.full.min.js"></script>
    
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inline edit for comments on double click
        document.querySelectorAll('.editable-comment').forEach(function(cell) {
            cell.addEventListener('dblclick', function() {
                var span = cell.querySelector('.comment-text');
                if (!span) return;
                var originalText = span.textContent || '';
                if (cell.getAttribute('data-editing') === '1') return;
                cell.setAttribute('data-editing', '1');

                // Restyled textarea
                var input = document.createElement('textarea');
                input.className = 'w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm text-gray-700 focus:border-blue-500 focus:outline-none';
                input.value = originalText;
                input.rows = 2;
                input.style.minWidth = '220px';
                input.style.width = '100%';
                input.style.resize = 'vertical';
                span.style.display = 'none';
                cell.appendChild(input);
                input.focus();
                input.select();

                var save = function(newValue) {
                    var trimmed = (newValue || '').trim();
                    if (trimmed === (originalText || '').trim()) {
                        cancel();
                        return;
                    }
                    var url = cell.getAttribute('data-update-url');
                    var field = cell.getAttribute('data-field') || 'comment';
                    var formData = new FormData();
                    formData.append(field, trimmed);
                    var token = document.querySelector('meta[name="csrf-token"]');
                    var headers = {};
                    if (token) { headers['X-CSRF-TOKEN'] = token.getAttribute('content'); }

                    fetch(url, {
                        method: 'POST',
                        headers: headers,
                        body: formData,
                        credentials: 'same-origin'
                    }).then(function(resp) { return resp.json(); })
                    .then(function(json) {
                        if (json && json.status === 'success') {
                            span.textContent = trimmed;
                        } else {
                            span.textContent = originalText;
                            console.error('Failed to update comment', json);
                            alert((json && json.message) ? json.message : 'Failed to update comment');
                        }
                    }).catch(function(err) {
                        span.textContent = originalText;
                        console.error(err);
                        alert('Network error while updating comment');
                    }).finally(function() {
                        cleanup();
                    });
                };
                var cancel = function() { cleanup(); };
                var cleanup = function() {
                    if (input && input.parentNode) {
                        input.parentNode.removeChild(input);
                    }
                    span.style.display = '';
                    cell.removeAttribute('data-editing');
                };

                input.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' && (e.ctrlKey || e.metaKey)) {
                        e.preventDefault();
                        save(input.value);
                    } else if (e.key === 'Escape') {
                        e.preventDefault();
                        cancel();
                    }
                });
                input.addEventListener('blur', function() {
                    save(input.value);
                });
            });
        });
    });

    document.addEventListener('DOMContentLoaded', function() {
        var btn = document.getElementById('employeesDailyTotalsBtn');
        if (btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                var startDate = document.getElementById('startDateInput').value;
                var endDate = document.getElementById('endDateInput').value;
                if(!startDate || !endDate){
                    alert('Please pick start and end dates first.');
                    return;
                }
                var url = "{{ route('admin.Pointagerepports.employeesDailyTotals', ['startDate' => 'START', 'endDate' => 'END']) }}";
                url = url.replace('START', startDate).replace('END', endDate);
                window.open(url, '_blank');
            });
        }
    });
    
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

    {{-- All other filter/PDF/date scripts from original file --}}
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var startDateInput = document.getElementById('startDateInput');
        document.getElementById('clientFilter').addEventListener('change', handleClientChange);
        document.getElementById('employeeFilter').addEventListener('change', handleEmployeeChange);
        document.getElementById('projectFilter').addEventListener('change', handleProjectChange);
        document.getElementById('taskFilter').addEventListener('change', handleTaskChange);

        async function handleClientChange() {
            const clientId = document.getElementById('clientFilter').value;
            if (clientId === '') {
                await loadAllProjects();
            } else {
                await updateProjectFilterByClient(clientId);
            }
            document.getElementById('employeeFilter').value = '';
            document.getElementById('taskFilter').value = '';
        }
        async function updateProjectFilterByClient(clientId) {
            const projectFilter = document.getElementById('projectFilter');
            projectFilter.innerHTML = '<option value="">All Projects</option>';
            let url;
            @if(Auth::user()->hasRole('Admin'))
                url = `/admin/filter/clients/${clientId}/projects`;
            @elseif(Auth::user()->hasRole('Responsable de projet'))
                url = `/Responsable_de_projet/filter/clients/${clientId}/projects`;
            @elseif(Auth::user()->hasRole('Employee'))
                url = `/employee/filter/clients/${clientId}/projects`;
            @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                url = `/Responsable_gestionnaire/filter/clients/${clientId}/projects`;
            @endif
            try {
                const response = await fetch(url);
                const projects = await response.json();
                projects.forEach(project => {
                    const option = document.createElement('option');
                    option.value = project.id;
                    option.textContent = project.name.toUpperCase();
                    projectFilter.appendChild(option);
                });
            } catch (error) {
                console.error('Error fetching projects:', error);
            }
        }
        async function handleEmployeeChange() {
            const employeeId = document.getElementById('employeeFilter').value;
            if (employeeId === '') {
                await loadAllProjects();
                await loadAllTasks();
            } else {
                await updateProjectFilter(employeeId);
                await updateTaskFilter(employeeId, null);
            }
        }
        async function handleProjectChange() {
            const projectId = document.getElementById('projectFilter').value;
            const employeeId = document.getElementById('employeeFilter').value;
            if (projectId === '') {
                if (employeeId === '') {
                    await loadAllTasks();
                } else {
                    await updateTaskFilter(employeeId, null);
                }
            } else {
                await updateTaskFilter(null, projectId);
            }
        }
        async function handleTaskChange() {
            const taskId = document.getElementById('taskFilter').value;
            if (taskId) {
                const taskInfo = await fetchTaskInfo(taskId);
                if (!document.getElementById('projectFilter').value) {
                    document.getElementById('projectFilter').value = taskInfo.projectId;
                }
                if (!document.getElementById('employeeFilter').value) {
                    document.getElementById('employeeFilter').value = taskInfo.employeeId;
                }
            }
        }
        async function loadAllProjects() {
            const projectFilter = document.getElementById('projectFilter');
            projectFilter.innerHTML = '<option value="">All Projects</option>';
            const allProjects = await fetchAllProjects();
            allProjects.forEach(project => {
                const option = document.createElement('option');
                option.value = project.id;
                option.textContent = project.name.toUpperCase();
                projectFilter.appendChild(option);
            });
        }
        async function loadAllTasks() {
            const taskFilter = document.getElementById('taskFilter');
            taskFilter.innerHTML = '<option value="">All Tasks</option>';
            const allTasks = await fetchAllTasks();
            allTasks.forEach(task => {
                const option = document.createElement('option');
                option.value = task.id;
                option.textContent = task.title;
                taskFilter.appendChild(option);
            });
        }
        async function updateProjectFilter(employeeId) {
            const projectFilter = document.getElementById('projectFilter');
            projectFilter.innerHTML = '<option value="">All Projects</option>';
            let projects;
            if (employeeId) {
                projects = await fetchEmployeeProjects(employeeId);
            } else {
                projects = await fetchAllProjects();
            }
            projects.forEach(project => {
                const option = document.createElement('option');
                option.value = project.id;
                option.textContent = project.name.toUpperCase();
                option.setAttribute('data-tva', project.tva || '');
                projectFilter.appendChild(option);
            });
        }
        async function updateTaskFilter(employeeId, projectId) {
            const taskFilter = document.getElementById('taskFilter');
            taskFilter.innerHTML = '<option value="">All Tasks</option>';
            let tasks;
            if (employeeId) {
                tasks = await fetchEmployeeTasks(employeeId);
            } else if (projectId) {
                tasks = await fetchProjectTasks(projectId);
            } else {
                tasks = await fetchAllTasks();
            }
            tasks.forEach(task => {
                const option = document.createElement('option');
                option.value = task.id;
                option.textContent = task.title;
                taskFilter.appendChild(option);
            });
        }
        async function fetchEmployeeProjects(employeeId) {
            if (!employeeId) return fetchAllProjects();
            let url;
            @if(Auth::user()->hasRole('Admin'))
                url = `/admin/filter/employees/${employeeId}/projects?include_tva=true`;
            @elseif(Auth::user()->hasRole('Responsable de projet'))
                url = `/Responsable_de_projet/filter/employees/${employeeId}/projects?include_tva=true`;
            @elseif(Auth::user()->hasRole('Employee'))
                url = `/employee/filter/employees/${employeeId}/projects?include_tva=true`;
            @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                url = `/Responsable_gestionnaire/filter/employees/${employeeId}/projects?include_tva=true`;
            @endif
            const response = await fetch(url);
            return await response.json();
        }
        async function fetchEmployeeTasks(employeeId) {
            if (!employeeId) return fetchAllTasks();
            let url;
            @if(Auth::user()->hasRole('Admin'))
                url = `/admin/filter/employees/${employeeId}/tasks`;
            @elseif(Auth::user()->hasRole('Responsable de projet'))
                url = `/Responsable_de_projet/filter/employees/${employeeId}/tasks`;
            @elseif(Auth::user()->hasRole('Employee'))
                url = `/employee/filter/employees/${employeeId}/tasks`;
            @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                url = `/Responsable_gestionnaire/filter/employees/${employeeId}/tasks`;
            @endif
            const response = await fetch(url);
            return await response.json();
        }
        async function fetchProjectTasks(projectId) {
            let url;
            @if(Auth::user()->hasRole('Admin'))
                url = `/admin/filter/projects/${projectId}/tasks`;
            @elseif(Auth::user()->hasRole('Responsable de projet'))
                url = `/Responsable_de_projet/filter/projects/${projectId}/tasks`;
            @elseif(Auth::user()->hasRole('Employee'))
                url = `/employee/filter/projects/${projectId}/tasks`;
            @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                url = `/Responsable_gestionnaire/filter/projects/${projectId}/tasks`;
            @endif
            const response = await fetch(url);
            return await response.json();
        }
        async function fetchTaskInfo(taskId) {
            let url;
            @if(Auth::user()->hasRole('Admin'))
                url = `/admin/filter/tasks/${taskId}`;
            @elseif(Auth::user()->hasRole('Responsable de projet'))
                url = `/Responsable_de_projet/filter/tasks/${taskId}`;
            @elseif(Auth::user()->hasRole('Employee'))
                url = `/employee/filter/tasks/${taskId}`;
            @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                url = `/Responsable_gestionnaire/filter/tasks/${taskId}`;
            @endif
            const response = await fetch(url);
            return await response.json();
        }
        async function fetchAllProjects() {
            let url;
            @if(Auth::user()->hasRole('Admin'))
                url = '/admin/filter/all-projects?include_tva=true';
            @elseif(Auth::user()->hasRole('Responsable de projet'))
                url = '/Responsable_de_projet/filter/all-projects?include_tva=true';
            @elseif(Auth::user()->hasRole('Employee'))
                url = '/employee/filter/all-projects?include_tva=true';
            @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                url = '/Responsable_gestionnaire/filter/all-projects?include_tva=true';
            @endif
            const response = await fetch(url);
            return await response.json();
        }
        async function fetchAllTasks() {
            let url;
            @if(Auth::user()->hasRole('Admin'))
                url = '/admin/filter/all-tasks';
            @elseif(Auth::user()->hasRole('Responsable de projet'))
                url = '/Responsable_de_projet/filter/all-tasks';
            @elseif(Auth::user()->hasRole('Employee'))
                url = '/employee/filter/all-tasks';
            @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                url = '/Responsable_gestionnaire/filter/all-tasks';
            @endif
            const response = await fetch(url);
            return await response.json();
        }
    });
    </script>
    
    <script>
    document.getElementById('exportPDFBtn').addEventListener('click', function() {
        var startDate = document.getElementById('startDateInput').value;
        var endDate = document.getElementById('endDateInput').value;
        var employeeId = document.getElementById('employeeFilter').value;
        var projectId = document.getElementById('projectFilter').value;
        var selectedTask = document.getElementById('taskFilter').value; // NEW: Changed to single value
        var clientId = document.getElementById('clientFilter').value;
        var projectName = '';
        var projectSelect = document.getElementById('projectFilter');
        if (projectSelect.selectedIndex > 0) {
            projectName = projectSelect.options[projectSelect.selectedIndex].text;
        }
        var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        if (startDate) {
            var url;
            if (startDate && endDate) {
                @if(Auth::user()->hasRole('Admin'))
                    var url = '/admin/Pointagerepports/downloadRepports/' + startDate + '/' + endDate;
                @elseif(Auth::user()->hasRole('Responsable de projet'))
                    var url = '/Responsable_de_projet/Pointagerepports/downloadRepports/' + startDate + '/' + endDate;
                @elseif(Auth::user()->hasRole('Employee'))
                    var url = '/employee/Pointagerepports/downloadRepports/' + startDate + '/' + endDate;
                @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                    var url = '/Responsable_gestionnaire/Pointagerepports/downloadRepports/' + startDate + '/' + endDate;
                @endif
            } else {
                @if(Auth::user()->hasRole('Admin'))
                    var url = '/admin/Pointagerepports/downloadOneDayRepports/' + startDate;
                @elseif(Auth::user()->hasRole('Responsable de projet'))
                    var url = '/Responsable_de_projet/Pointagerepports/downloadOneDayRepports/' + startDate;
                @elseif(Auth::user()->hasRole('Employee'))
                    var url = '/employee/Pointagerepports/downloadOneDayRepports/' + startDate;
                @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                    var url = '/Responsable_gestionnaire/Pointagerepports/downloadOneDayRepports/' + startDate;
                @endif
            }
            var queryParams = [];
            if (employeeId) queryParams.push('employeeId=' + encodeURIComponent(employeeId));
            if (projectId) queryParams.push('projectId=' + encodeURIComponent(projectId));
            if (selectedTask) { queryParams.push('task=' + encodeURIComponent(selectedTask)); } // NEW: Changed to single param
            if (clientId) queryParams.push('clientId=' + encodeURIComponent(clientId));
            var fileName = 'Report_';
            if (projectName) fileName += projectName.replace(/[^a-z0-9]/gi, '_') + '_';
            if (startDate) fileName += startDate;
            if (endDate) fileName += '_to_' + endDate;
            fileName += '.pdf';
            queryParams.push('fileName=' + encodeURIComponent(fileName));
            if (queryParams.length > 0) url += '?' + queryParams.join('&');
            fetch(url, {
                method: 'GET',
                headers: { 'X-CSRF-TOKEN': csrfToken }
            })
            .then(response => response.blob())
            .then(blob => {
                var url = window.URL.createObjectURL(blob);
                var a = document.createElement('a');
                a.href = url;
                a.download = fileName;
                document.body.appendChild(a);
                a.click();
                a.remove();
                window.URL.revokeObjectURL(url);
            })
            .catch((error) => {
                console.error('Error:', error);
                alert('An error occurred while generating the PDF. Please try again.');
            });
        } else {
            alert('Please select a start date first.');
        }
    });

    document.getElementById('exportPDFBtnCustomer').addEventListener('click', function() {
        var startDate = document.getElementById('startDateInput').value;
        var endDate = document.getElementById('endDateInput').value;
        var employeeId = document.getElementById('employeeFilter').value;
        var projectId = document.getElementById('projectFilter').value;
        var selectedTask = document.getElementById('taskFilter').value; // NEW: Changed to single value
        var clientId = document.getElementById('clientFilter').value;
        if (startDate) {
            var url;
            if (startDate && endDate) {
                @if(Auth::user()->hasRole('Admin'))
                    var url = '/admin/CustomerPointagerepports/preview/' + startDate + '/' + endDate;
                @elseif(Auth::user()->hasRole('Responsable de projet'))
                    var url = '/Responsable_de_projet/CustomerPointagerepports/preview/' + startDate + '/' + endDate;
                @elseif(Auth::user()->hasRole('Employee'))
                    var url = '/employee/CustomerPointagerepports/preview/' + startDate + '/' + endDate;
                @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                    var url = '/Responsable_gestionnaire/CustomerPointagerepports/preview/' + startDate + '/' + endDate;
                @endif
            } else {
                @if(Auth::user()->hasRole('Admin'))
                    var url = '/admin/CustomerPointagerepports/previewOneDay/' + startDate;
                @elseif(Auth::user()->hasRole('Responsable de projet'))
                    var url = '/Responsable_de_projet/CustomerPointagerepports/previewOneDay/' + startDate;
                @elseif(Auth::user()->hasRole('Employee'))
                    var url = '/employee/CustomerPointagerepports/previewOneDay/' + startDate;
                @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                    var url = '/Responsable_gestionnaire/CustomerPointagerepports/previewOneDay/' + startDate;
                @endif
            }
            var queryParams = [];
            if (employeeId) queryParams.push('employeeId=' + encodeURIComponent(employeeId));
            if (projectId) queryParams.push('projectId=' + encodeURIComponent(projectId));
            if (selectedTask) { queryParams.push('task=' + encodeURIComponent(selectedTask)); } // NEW: Changed to single param
            if (clientId) queryParams.push('clientId=' + encodeURIComponent(clientId));
            if (queryParams.length > 0) url += '?' + queryParams.join('&');
            window.location.href = url;
        }
    });
    </script>

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        var startDate = localStorage.getItem('startDate');
        var endDate = localStorage.getItem('endDate');
        var selectedMonth = localStorage.getItem('selectedMonth');
        if (startDate) document.getElementById('startDateInput').value = startDate;
        if (endDate) document.getElementById('endDateInput').value = endDate;
        if (selectedMonth) document.getElementById('monthFilter').value = selectedMonth;

        document.getElementById('monthFilter').addEventListener('change', function() {
            const selectedMonth = this.value;
            if (selectedMonth) {
                const currentYear = new Date().getFullYear();
                const firstDay = new Date(currentYear, selectedMonth - 1, 1);
                const lastDay = new Date(currentYear, selectedMonth, 0);
                const formatDate = (date) => {
                    const year = date.getFullYear();
                    const month = String(date.getMonth() + 1).padStart(2, '0');
                    const day = String(date.getDate()).padStart(2, '0');
                    return `${year}-${month}-${day}`;
                };
                document.getElementById('startDateInput').value = formatDate(firstDay);
                document.getElementById('endDateInput').value = formatDate(lastDay);
            }
        });

        document.getElementById('dateForm').addEventListener('submit', function() {
            localStorage.setItem('startDate', document.getElementById('startDateInput').value);
            localStorage.setItem('endDate', document.getElementById('endDateInput').value);
            localStorage.setItem('selectedMonth', document.getElementById('monthFilter').value);
        });
    });
    </script>

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        var startDate = localStorage.getItem('startDate');
        var endDate = localStorage.getItem('endDate');
        document.getElementById('hiddenStartDateInput').value = startDate;
        document.getElementById('hiddenEndDateInput').value = endDate;

        document.getElementById('filterForm').addEventListener('submit', function(event) {
            event.preventDefault();
            localStorage.setItem('employeeFilter', document.getElementById('employeeFilter').value);
            localStorage.setItem('projectFilter', document.getElementById('projectFilter').value);
            localStorage.setItem('taskFilter', document.getElementById('taskFilter').value); // NEW: Storing single value
            
            var startDateValue = startDate;
            var endDateValue = endDate;
            
            @if(Auth::user()->hasRole('Admin'))
            var baseUrl = "{{ route('admin.pointage.filterPointageTable') }}";
            @elseif(Auth::user()->hasRole('Responsable de projet'))
            var baseUrl = "{{ route('Responsable_de_projet.pointage.filterPointageTable') }}";
            @elseif(Auth::user()->hasRole('Employee'))
            var baseUrl = "{{ route('employee.pointage.filterPointageTable') }}";
            @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
            var baseUrl = "{{ route('Responsable_gestionnaire.pointage.filterPointageTable') }}";
            @endif
            
            var url = new URL(baseUrl, window.location.origin);
            if (startDateValue) url.searchParams.append('start_date', startDateValue);
            if (endDateValue) url.searchParams.append('end_date', endDateValue);
            
            var employeeFilter = document.getElementById('employeeFilter').value;
            var projectFilter = document.getElementById('projectFilter').value;
            var taskFilter = document.getElementById('taskFilter').value; // NEW: Changed to single value
            
            if (employeeFilter) url.searchParams.append('employee', employeeFilter);
            if (projectFilter) url.searchParams.append('project', projectFilter);
            if (taskFilter) url.searchParams.append('task', taskFilter); // NEW: Changed to single param
            
            this.action = url.toString();
            this.submit();
        });

        function restoreFilterValues() {
            const urlParams = new URLSearchParams(window.location.search);
            const employeeValue = urlParams.get('employee') || localStorage.getItem('employeeFilter');
            const projectValue = urlParams.get('project') || localStorage.getItem('projectFilter');
            const taskValue = urlParams.get('task') || localStorage.getItem('taskFilter'); // NEW: Changed to single value
            
            if (employeeValue) document.getElementById('employeeFilter').value = employeeValue;
            if (projectValue) document.getElementById('projectFilter').value = projectValue;
            if (taskValue) document.getElementById('taskFilter').value = taskValue; // NEW: Changed to single value
        }
        restoreFilterValues();
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const cells = document.querySelectorAll('.clickable');
        cells.forEach(cell => {
            cell.addEventListener('click', function() {
                const field = this.getAttribute('data-field');
                const taskId = this.getAttribute('data-task-id');
                const currentValue = this.textContent.trim();
                const [datePart, timePart] = currentValue.split(' ');
                const [day, month, year] = datePart.split('-');
                const formattedDate = `${year}-${month}-${day}`;
                const formattedTime = timePart.substring(0, 5);
                
                document.getElementById('dateInput').value = formattedDate;
                document.getElementById('timeInput').value = formattedTime;
                document.getElementById('taskId').value = taskId;
                document.getElementById('fieldName').value = field;
                
                // This line assumes Bootstrap's JS is loaded.
                $('#editDateModal').modal('show');
            });
        });

        document.getElementById('saveChangesBtn').addEventListener('click', function() {
            const taskId = document.getElementById('taskId').value;
            const field = document.getElementById('fieldName').value;
            const date = document.getElementById('dateInput').value;
            const time = document.getElementById('timeInput').value;
            const newValue = `${date} ${time}`;
            
            // This line assumes jQuery is loaded.
            $.ajax({
                url: `/admin/tasks/update-${field}/${taskId}`,
                type: 'POST',
                data: {
                    [field]: newValue,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.status === 'success') {
                        $('#editDateModal').modal('hide');
                        location.reload();
                    } else {
                        alert(response.message || 'Failed to update the value.');
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'Failed to update the value.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    alert(errorMessage);
                }
            });
        });
    });
</script>

</body>
</html>