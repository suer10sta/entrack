<!DOCTYPE html>
<html lang="en">

@include('dashboard_layouts.head')
<style>
a {
    color: blue;
}

.font-weight-bolder {
    color: black !important;
}

th {
    font-size: 14px !important;
}
.text-sm{
    color: #332f2f;
}

.highlighted-task {
    background-color: #e6f3ff !important;
}

.highlighted-task td {
    background-color: #e6f3ff !important;
}

.regie-task {
    background-color: #fff3e6 !important;
}

.regie-task td {
    background-color: #fff3e6 !important;
}

/* Add new styles for start date and duration */
td[data-field="start_date"] {
    color: blue !important;
    cursor: pointer !important;
}

td.duration {
    color: #332f2f !important;
    cursor: default !important;
    pointer-events: none;
}

/* Style for end date - make it clickable like start date */
td[data-field="end_date"] {
    color: blue !important;
    cursor: pointer !important;
}
</style>



<meta name="csrf-token" content="{{ csrf_token() }}">

<body class="g-sidenav-show bg-gray-100">
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-NKDMSK6" height="0" width="0"
            style="display: none; visibility: hidden"></iframe></noscript>
    <div class="min-height-300 bg-primary position-absolute w-100"></div>

    @if(Auth::user()->hasRole('Responsable de projet') || Auth::user()->hasRole('Employee') || Auth::user()->hasRole('Responsable de projet et gestionnaire'))
    @include('employees.sidebar')
    @endif
    @if(Auth::user()->hasRole('Admin'))
    @include('admin.sidebar')
    @endif

    <main class="main-content position-relative border-radius-lg">
        <nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl z-index-sticky"
            id="navbarBlur" data-scroll="false">
            <div class="container-fluid py-1 px-3">

                @include('dashboard_layouts.navbar')

            </div>

            <div class="container-fluid py-4">


                <div class="row mt-3">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center p-3 pb-0">
                                <h6>Tasks Timer Table</h6>
                                <div>
                                    <button id="exportBtn" class="btn btn-primary">Export to Excel</button>
                                    <button id="exportPDFBtn" class="btn btn-primary">Export to PDF</button>
                                    <button id="exportPDFBtnCustomer" class="btn btn-primary">Export to PDF for Customer</button>
                                </div>

                                @if(session('alertmessage'))
                                <div class="alert alert-danger alert-dismissible fade show" role="alert" style="color: white; font-weight: bold;">
                                    {{ session('alertmessage') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">X</button>
                                </div>
                                @endif
                            </div>
                            <div class="card-header d-flex justify-content-end align-items-center p-2 pb-0">
                                <div class="d-flex align-items-center">
                                    <div>
                                        <label for="totalDuration" class="form-label" style="font-size: 16px !important;">Total Duration: <label style="font-size: 16px !important;">0h:00m</label> | </label>
                                    </div>
                                    <div>
                                        <label for="totalOperatingCost" class="form-label" style="font-size: 16px !important;">Total Operating Cost: <label style="color: #2dce89 !important;font-size: 16px !important;">0.00€</label> | </label>
                                    </div>
                                    <div>
                                        <label for="totalSales" class="form-label" style="font-size: 16px !important;">Total Sales: <label style="font-size: 16px !important;padding-right: 12px;">0.00€</label></label>
                                    </div>
                                </div>
                            </div>
                            <div class="row p-2 pb-0">
                                <label class="mb-0">
                                    Fetch Data:
                                </label>
                                @if(Auth::user()->hasRole('Admin'))
                                <form method="GET" action="{{ route('admin.pointage.filterPointageByDate') }}" class="d-flex align-items-end mb-3" id="dateForm" >
                                @endif
                                @if(Auth::user()->hasRole('Responsable de projet'))
                                <form method="GET" action="{{ route('Responsable_de_projet.pointage.filterPointageByDate') }}" class="d-flex align-items-end mb-3" id="dateForm" >
                                @endif
                                @if(Auth::user()->hasRole('Employee'))
                                <form method="GET" action="{{ route('employee.pointage.filterPointageByDate') }}" class="d-flex align-items-end mb-3" id="dateForm" >
                                @endif
                                @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                <form method="GET" action="{{ route('Responsable_gestionnaire.pointage.filterPointageByDate') }}" class="d-flex align-items-end mb-3" id="dateForm" >
                                @endif
                                    @csrf
                                    <div class="col-md-2 m-3">
                                        <select id="monthFilter" class="form-control" name="month">
                                            <option value="">Select Month</option>
                                            @for($i = 1; $i <= 12; $i++)
                                                <option value="{{ $i }}">{{ date('F', mktime(0, 0, 0, $i, 1)) }}</option>
                                            @endfor
                                        </select>
                                    </div>
                                    <div class="col-md-3 m-3">
                                        <input type="date" id="startDateInput" class="form-control" name="start_date">
                                    </div>
                                    <div class="col-md-3 m-3 ms-2">
                                        <input type="date" id="endDateInput" class="form-control" name="end_date">
                                    </div>
                                    <div class="col-md-3 ms-2">
                                        <button type="submit" class="btn btn-primary">Get</button>
                                        @if(Auth::user()->hasRole('Admin'))
                                        <a id="employeesDailyTotalsBtn" class="btn btn-outline-secondary ms-2" target="_blank">Employees PDF</a>
                                        @endif
                                    </div>
                                </form>
                            </div>

                            <div class="row p-4 pt-0">
                                <label for="">
                                    Filter by:
                                </label>
                                @if(Auth::user()->hasRole('Admin'))
                                <form method="GET" action="{{ route('admin.pointage.filterPointageTable') }}" class="d-flex align-items-end mb-3" id="filterForm" style="margin-left:-15px;">
                                @endif
                                @if(Auth::user()->hasRole('Responsable de projet'))
                                <form method="GET" action="{{ route('Responsable_de_projet.pointage.filterPointageTable') }}" class="d-flex align-items-end mb-3" id="filterForm" style="margin-left:-15px;">
                                @endif
                                @if(Auth::user()->hasRole('Employee'))
                                <form method="GET" action="{{ route('employee.pointage.filterPointageTable') }}" class="d-flex align-items-end mb-3" id="filterForm" style="margin-left:-15px;">
                                @endif
                                @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                <form method="GET" action="{{ route('Responsable_gestionnaire.pointage.filterPointageTable') }}" class="d-flex align-items-end mb-3" id="filterForm" style="margin-left:-15px;">
                                @endif
                                    @csrf
                                    <input type="hidden" id="hiddenStartDateInput" name="start_date">
                                    <input type="hidden" id="hiddenEndDateInput" name="end_date">
                                    <div class="col-md-1 m-3">
                                        <select id="clientFilter" class="form-control" name="client">
                                            <option value="">All Clients</option>
                                            @foreach($clients as $client)
                                            <option value="{{ $client->id }}" {{ request('client') == $client->id ? 'selected' : '' }}>
                                                {{ $client->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2 m-3">
                                        <select id="employeeFilter" class="form-control" name="employee">
                                            <option value="">All Employees</option>
                                            @foreach($employees as $employee)
                                            <option value="{{ $employee->id }}" {{ request('employee') == $employee->id ? 'selected' : '' }}>
                                                {{ $employee->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3 ms-2 m-3">
                                        <select id="projectFilter" class="form-control" name="project">
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
                                    <div class="col-md-3 m-3">
                                        <select id="taskFilter" class="form-control" name="task[]" multiple>
                                            <option value="">All Tasks</option>
                                            @foreach($allTasks as $task)
                                            <option value="{{ $task->id }}" {{ in_array($task->id, (array) request('task', [])) ? 'selected' : '' }}>
                                                {{ $task->title }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-1 m-3">
                                        <select id="projectTypeFilter" class="form-control" name="project_type">
                                            <option value="">All Types</option>
                                            <option value="regie" {{ request('project_type') == 'regie' ? 'selected' : '' }}>Regie</option>
                                            <option value="devis" {{ request('project_type') == 'devis' ? 'selected' : '' }}>Devis</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3 ms-2 ms-2">
                                        <button type="submit" class="btn btn-primary">Filter</button>
                                    </div>
                                </form>
                                <div class="row mt-3">
                                    <div class="col-md-6">
                                        <textarea class="form-control" id="periodInvoiceComment" placeholder="Invoice Comment" style="resize: vertical; min-height: 60px;"></textarea>
                                    </div>
                                    <div class="col-md-3">
                                        <button id="generatePeriodInvoiceBtn" class="btn btn-primary">Generate Period Invoice</button>
                                    </div>
                                </div>
                            </div>


                            <div class="table-responsive p-4 pt-1">
                                <table id="taskTable" class="table table-flush" style="width:100%">
                                    <thead class="thead-light">
                                        <tr class="text-center">
                                            <th
                                                class="text-center text-uppercase text-secondary text-x font-weight-bolder opacity-10">
                                                Project name</th>
                                            <th
                                                class="text-center text-uppercase text-secondary text-x font-weight-bolder opacity-10">
                                                task name</th>
                                            <th
                                                class="text-center text-uppercase text-secondary text-x font-weight-bolder opacity-10">
                                                Employee name</th>
                                            <th
                                                class="text-center text-uppercase text-secondary text-x font-weight-bolder opacity-10">
                                                start date</th>
                                            <th
                                                class="text-center text-uppercase text-secondary text-x font-weight-bolder opacity-10">
                                                end date</th>
                                           
                                            <th
                                                class="text-center text-uppercase text-secondary text-x font-weight-bolder opacity-10">
                                                duration </th>
                                            <th
                                                class="text-center text-uppercase text-secondary text-x font-weight-bolder opacity-10">
                                                operating cost </th>
                                            <th
                                                class="text-center text-uppercase text-secondary text-x font-weight-bolder opacity-10">
                                                Sales </th>
                                            <th
                                                class="text-center text-uppercase text-secondary text-x font-weight-bolder opacity-10">
                                                Comment</th>
                                            <th
                                                class="text-center text-uppercase text-secondary text-x font-weight-bolder opacity-10">
                                                Actions</th>
                                        </tr>
                                    </thead>

                                        <tbody id="taskTableBody">
                                        @php
                                            function formatDuration($minutes) {
                                                $hours = floor($minutes / 60);
                                                $minutes = $minutes % 60;
                                                $seconds = 0; // Assuming no seconds are provided
                                    
                                                return sprintf('%02dh:%02dm:%02ds', $hours, $minutes, $seconds);
                                            }
                                        @endphp
                                    
                                        @foreach($tasks_timer as $start_date => $tasks)
                                        @php
                                            try {
                                                // Check if the date string contains a day name
                                                if (is_string($start_date) && strpos($start_date, ' - ') !== false) {
                                                    // Split into day name and date
                                                    list($dayName, $datePart) = explode(' - ', $start_date);
                                                    $dateObject = \Carbon\Carbon::createFromFormat('Y-m-d', $datePart);
                                                    // Keep the original format with day name
                                                    $displayDate = $dayName . ' - ' . $dateObject->format('d-m-Y');
                                                } else {
                                                    $dateObject = is_string($start_date) 
                                                        ? \Carbon\Carbon::createFromFormat('Y-m-d', $start_date)
                                                        : $start_date;
                                                    $displayDate = $dateObject->format('l - d-m-Y'); // 'l' gives full day name
                                                }
                                                
                                                $groupTotal = 0;
                                                $groupTotalRecette = 0;
                                                $groupTotalDuration = 0;
                                            } catch (\Exception $e) {
                                                // Handle invalid date format
                                                $dateObject = null;
                                                Log::error("Invalid date format: " . $start_date . ". Error: " . $e->getMessage());
                                                continue;
                                            }
                                        @endphp

                                        @if($dateObject)
                                            <tr>
                                                <td colspan="9" style="background-color: lightgray; color: #000000c7;"><strong>{{ $displayDate }}</strong></td>
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
                                                    
                                                    // Debug information
                                                    $hasInvoice = in_array($task_project_id, $periodInvoices);
                                                    $isRegie = $project->regie === 1;
                                                @endphp
                                    
                                                <tr class="text-center {{ $hasInvoice ? 'highlighted-task' : '' }} {{ $isRegie ? 'regie-task' : '' }}" id="{{ $task->id }}" data-employee-id="{{ $task->employee_id }}"
                                                    data-project-id="{{ $task_project_id}}" data-task-id="{{ $task->task_id }}">
                                                    <td>
                                                        @if(Auth::user()->hasRole('Responsable de projet'))
                                                            @if(Auth::user()->can('View project'))
                                                            <a href="{{route('Responsable_de_projet.projects.show',$task_project_id)}}">
                                                                {{ $project->name }}
                                                            </a>
                                                            @else
                                                                {{ $project->name }}
                                                            @endif
                                                        @endif
                                                        @if(Auth::user()->hasRole('Admin'))
                                                            @if(Auth::user()->can('View project'))
                                                            <a href="{{route('admin.projects.show',$task_project_id)}}">
                                                                {{ $project->name }}
                                                            </a>
                                                            @else
                                                                {{ $project->name }}
                                                            @endif
                                                        @endif
                                                        @if(Auth::user()->hasRole('Employee'))
                                                            @if(Auth::user()->can('View project'))
                                                            <a href="{{route('employee.projects.show',$task_project_id)}}">
                                                                {{ $project->name }}
                                                            </a>
                                                            @else
                                                                {{ $project->name }}
                                                            @endif
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if(auth()->user()->hasRole('Admin'))
                                                        <a href="{{route('admin.tasks.show', $task->task_id)}}">
                                                        @endif
                                                        @if(auth()->user()->hasRole('Responsable de projet'))
                                                        <a href="{{route('Responsable_de_projet.tasks.show', $task->task_id)}}">
                                                        @endif
                                                        @if(auth()->user()->hasRole('Employee'))
                                                        <a href="{{route('employee.tasks.show', $task->task_id)}}">
                                                        @endif
                                                        @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                                        <a href="{{route('Responsable_gestionnaire.tasks.show', $task->task_id)}}">
                                                        @endif
                                                            {{ $task->task_name }}
                                                        </a>
                                                    </td>
                                                    <td>
                                                        @if(Auth::user()->hasRole('Responsable de projet'))
                                                            @if(Auth::user()->can('View employee'))
                                                                <a href="{{route('Responsable_de_projet.employees.show',$employee->id )}}">
                                                                    {{ $employee->name }}
                                                                </a>
                                                            @else
                                                                {{ $employee->name }}
                                                            @endif
                                                        @endif
                                                        @if(Auth::user()->hasRole('Admin'))
                                                            @if(Auth::user()->can('View employee'))
                                                                <a href="{{route('admin.employees.show',$employee->id )}}">
                                                                    {{ $employee->name }}
                                                                </a>
                                                            @else
                                                                {{ $employee->name }}
                                                            @endif
                                                        @endif 
                                                        @if(Auth::user()->hasRole('Employee'))
                                                            @if(Auth::user()->can('View employee'))
                                                                <a href="{{route('employee.employees.show',$employee->id )}}">
                                                                    {{ $employee->name }}
                                                                </a>
                                                            @else
                                                                {{ $employee->name }}
                                                            @endif
                                                        @endif 
                                                        @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                                            @if(Auth::user()->can('View employee'))
                                                                <a href="{{route('Responsable_gestionnaire.employees.show',$employee->id )}}">
                                                                    {{ $employee->name }}
                                                                </a>
                                                            @else
                                                                {{ $employee->name }}
                                                            @endif
                                                        @endif 
                                                    </td>
                                                    <td class="clickable" data-field="start_date" data-task-id="{{ $task->id }}">
                                                        {{ \Carbon\Carbon::parse($task->start_date)->format('d-m-Y H:i:s') }}
                                                    </td>
                                                    <td class="clickable" data-field="end_date" data-task-id="{{ $task->id }}">
                                                        {{ $task->end_date ? \Carbon\Carbon::parse($task->end_date)->format('d-m-Y H:i:s') : 'Still Working' }}
                                                    </td>
                                                    <td id="duration-{{ $task->id }}" class="duration" data-task-id="{{ $task->id }}">
                                                        @if (is_null($task->end_date))
                                                            <script>
                                                                document.addEventListener('DOMContentLoaded', function () {
                                                                    function startTimer(taskId, startDate) {
                                                                        const durationCellId = 'duration-' + taskId;
                                                                        let retryCount = 0;
                                                                        const maxRetries = 10;
                                    
                                                                        // Start an interval that runs every second
                                                                        var intervalId = setInterval(function() {
                                                                            // Calculate the duration of the task in hours
                                                                            var durationInMilliseconds = new Date() - new Date(startDate);
                                                                            var hours = Math.floor(durationInMilliseconds / 3600000);
                                                                            var minutes = Math.floor((durationInMilliseconds % 3600000) / 60000);
                                                                            var seconds = Math.floor((durationInMilliseconds % 60000) / 1000);
                                    
                                                                            var durationFormatted = (hours < 10 ? '0' : '') + hours + 'h:' +
                                                                                (minutes < 10 ? '0' : '') + minutes + 'm:' +
                                                                                (seconds < 10 ? '0' : '') + seconds + 's';
                                    
                                                                            // Find the duration cell
                                                                            var durationCell = document.getElementById(durationCellId);
                                    
                                                                            // Check if the duration cell exists
                                                                            if (durationCell) {
                                                                                // Update the duration cell
                                                                                durationCell.textContent = durationFormatted;
                                                                            } else {
                                                                                retryCount++;
                                                                                if (retryCount >= maxRetries) {
                                                                                    console.error('Element not found: #' + durationCellId);
                                                                                    clearInterval(intervalId); // Stop the interval
                                                                                }
                                                                            }
                                                                        }, 1000); // 1000 milliseconds = 1 second
                                                                    }
                                    
                                                                    // Call the startTimer function for this task
                                                                    startTimer('{{ $task->id }}', '{{ $task->start_date }}');
                                                                });
                                                            </script>
                                                        @else
                                                            {{ formatDuration($task->duration) }}
                                                        @endif
                                                    </td>
                                                    <td>
                                                        {{ $task->end_date ? number_format((float)$task->cost, 3, '.', '') : '0.00' }}€
                                                    </td>
                                                    <td>
                                                        {{ $task->end_date ? number_format((($task->duration / 60) * $project->cost_per_hour), 3, '.', '') : '0.00' }}€
                                                    </td>
                                                    <td class="editable-comment" data-id="{{ $task->id }}" data-field="comment" data-update-url="{{ route('admin.tasks.updateField', ['field' => 'comment', 'id' => $task->id]) }}">
                                                        <span class="comment-text">{{ $task->comment }}</span>
                                                    </td>
                                                    <td>
                                                        @if($task->end_date)
                                                        <button class="btn btn-sm btn-info ms-2 move-duration-btn" 
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
                                    
                                            <tr class="text-center">
                                                <td colspan="5"></td>
                                                <td><strong>{{ formatDuration($groupTotalDuration) }}</strong></td>
                                                <td><strong>{{ number_format($groupTotal, 3, ',', '') }}€</strong></td>
                                                <td><strong>{{ number_format($groupTotalRecette, 3, ',', '') }}€</strong></td>
                                            </tr>
                                        @endforeach
                                    </tbody>

                                </table>
                            </div>

                        </div>
                    </div>
                </div>
                
    </main>



    @include('dashboard_layouts.scripts')
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

                // Prevent multiple editors
                if (cell.getAttribute('data-editing') === '1') return;
                cell.setAttribute('data-editing', '1');

                // Create resizable textarea
                var input = document.createElement('textarea');
                input.className = 'form-control form-control-sm';
                input.value = originalText;
                input.rows = 2;
                input.style.minWidth = '220px';
                input.style.width = '100%';
                input.style.resize = 'vertical'; // allow resizing

                // Replace span with textarea
                span.style.display = 'none';
                cell.appendChild(input);
                input.focus();
                input.select();

                var save = function(newValue) {
                    var trimmed = (newValue || '').trim();
                    // If unchanged, just cancel
                    if (trimmed === (originalText || '').trim()) {
                        cancel();
                        return;
                    }
                    var url = cell.getAttribute('data-update-url');
                    var field = cell.getAttribute('data-field') || 'comment';

                    // Build form data
                    var formData = new FormData();
                    formData.append(field, trimmed);

                    // CSRF token from meta if present
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
                            // Revert on failure
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

                var cancel = function() {
                    cleanup();
                };

                var cleanup = function() {
                    if (input && input.parentNode) {
                        input.parentNode.removeChild(input);
                    }
                    span.style.display = '';
                    cell.removeAttribute('data-editing');
                };

                // Save on Ctrl+Enter, cancel on Escape, save on blur
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
                    // Save on blur
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
        var table = document.getElementById(
            'taskTable'); // Replace 'yourTableId' with the actual ID of your table
        var wb = XLSX.utils.table_to_book(table, {
            sheet: 'Sheet1'
        });
        // Get the start date, end date, and task information
        var startDate = document.getElementById('startDateInput').value;
        var endDate = document.getElementById('endDateInput').value;
        var taskInfo = 'TasksInfo'; // Example task information

        // Format the file name
        var fileName = startDate + '__' + endDate + '_' + taskInfo + '.xlsx';

        // Trigger the file download with the custom file name
        XLSX.writeFile(wb, fileName);
    });
    </script>




    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var startDateInput = document.getElementById('startDateInput');

        // Add event listeners
        document.getElementById('clientFilter').addEventListener('change', handleClientChange);
        document.getElementById('employeeFilter').addEventListener('change', handleEmployeeChange);
        document.getElementById('projectFilter').addEventListener('change', handleProjectChange);
        document.getElementById('taskFilter').addEventListener('change', handleTaskChange);

        async function handleClientChange() {
            const clientId = document.getElementById('clientFilter').value;
            if (clientId === '') {
                // If "All Clients" is selected, load all projects
                await loadAllProjects();
            } else {
                // If specific client is selected, load their projects
                await updateProjectFilterByClient(clientId);
            }
            // Reset employee and task filters
            document.getElementById('employeeFilter').value = '';
            document.getElementById('taskFilter').value = '';
        }

        async function updateProjectFilterByClient(clientId) {
            const projectFilter = document.getElementById('projectFilter');
            projectFilter.innerHTML = '<option value="">All Projects</option>';
            
            let url;
            @if(Auth::user()->hasRole('Admin'))
                url = `/admin/filter/clients/${clientId}/projects`;
            @endif
            @if(Auth::user()->hasRole('Responsable de projet'))
                url = `/Responsable_de_projet/filter/clients/${clientId}/projects`;
            @endif
            @if(Auth::user()->hasRole('Employee'))
                url = `/employee/filter/clients/${clientId}/projects`;
            @endif
            @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
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
                // If "All Employees" is selected, load all projects and tasks
                await loadAllProjects();
                await loadAllTasks();
            } else {
                // If specific employee is selected, load their projects and tasks
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
                // Only update project and employee if they're not already set
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
                option.setAttribute('data-tva', project.tva || ''); // Set the TVA data attribute
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

        // Functions to fetch data from the server
        async function fetchEmployeeProjects(employeeId) {
            if (!employeeId) return fetchAllProjects();
            
            let url;
            @if(Auth::user()->hasRole('Admin'))
                url = `/admin/filter/employees/${employeeId}/projects?include_tva=true`;
            @endif
            @if(Auth::user()->hasRole('Responsable de projet'))
                url = `/Responsable_de_projet/filter/employees/${employeeId}/projects?include_tva=true`;
            @endif
            @if(Auth::user()->hasRole('Employee'))
                url = `/employee/filter/employees/${employeeId}/projects?include_tva=true`;
            @endif
            @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
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
            @endif
            @if(Auth::user()->hasRole('Responsable de projet'))
                url = `/Responsable_de_projet/filter/employees/${employeeId}/tasks`;
            @endif
            @if(Auth::user()->hasRole('Employee'))
                url = `/employee/filter/employees/${employeeId}/tasks`;
            @endif
            @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                url = `/Responsable_gestionnaire/filter/employees/${employeeId}/tasks`;
            @endif
            const response = await fetch(url);
            return await response.json();
        }

        async function fetchProjectTasks(projectId) {
            let url;
            @if(Auth::user()->hasRole('Admin'))
                url = `/admin/filter/projects/${projectId}/tasks`;
            @endif
            @if(Auth::user()->hasRole('Responsable de projet'))
                url = `/Responsable_de_projet/filter/projects/${projectId}/tasks`;
            @endif
            @if(Auth::user()->hasRole('Employee'))
                url = `/employee/filter/projects/${projectId}/tasks`;
            @endif
            @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                url = `/Responsable_gestionnaire/filter/projects/${projectId}/tasks`;
            @endif
            const response = await fetch(url);
            return await response.json();
        }

        async function fetchTaskInfo(taskId) {
            let url;
            @if(Auth::user()->hasRole('Admin'))
                url = `/admin/filter/tasks/${taskId}`;
            @endif
            @if(Auth::user()->hasRole('Responsable de projet'))
                url = `/Responsable_de_projet/filter/tasks/${taskId}`;
            @endif
            @if(Auth::user()->hasRole('Employee'))
                url = `/employee/filter/tasks/${taskId}`;
            @endif
            @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                url = `/Responsable_gestionnaire/filter/tasks/${taskId}`;
            @endif
            const response = await fetch(url);
            return await response.json();
        }

        async function fetchAllProjects() {
            let url;
            @if(Auth::user()->hasRole('Admin'))
                url = '/admin/filter/all-projects?include_tva=true';  // Added include_tva parameter
            @endif
            @if(Auth::user()->hasRole('Responsable de projet'))
                url = '/Responsable_de_projet/filter/all-projects?include_tva=true';
            @endif
            @if(Auth::user()->hasRole('Employee'))
                url = '/employee/filter/all-projects?include_tva=true';
            @endif
            @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                url = '/Responsable_gestionnaire/filter/all-projects?include_tva=true';
            @endif
            const response = await fetch(url);
            return await response.json();
        }

        async function fetchAllTasks() {
            let url;
            @if(Auth::user()->hasRole('Admin'))
                url = '/admin/filter/all-tasks';  // Changed endpoint
            @endif
            @if(Auth::user()->hasRole('Responsable de projet'))
                url = '/Responsable_de_projet/filter/all-tasks';  // Changed endpoint
            @endif
            @if(Auth::user()->hasRole('Employee'))
                url = '/employee/filter/all-tasks';  // Changed endpoint
            @endif
            @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                url = '/Responsable_gestionnaire/filter/all-tasks';  // Changed endpoint
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
        var selectedTasks = Array.from(document.getElementById('taskFilter').selectedOptions).map(option => option.value);
        var clientId = document.getElementById('clientFilter').value;
        var projectName = '';
        var projectSelect = document.getElementById('projectFilter');
        if (projectSelect.selectedIndex > 0) {
            projectName = projectSelect.options[projectSelect.selectedIndex].text;
        }

        // Get CSRF token from meta tag
        var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        if (startDate) {
            var url;
            if (startDate && endDate) {
                @if(Auth::user()->hasRole('Admin'))
                var url = '/admin/Pointagerepports/downloadRepports/' + startDate + '/' + endDate;
                @endif
                @if(Auth::user()->hasRole('Responsable de projet'))
                var url = '/Responsable_de_projet/Pointagerepports/downloadRepports/' + startDate + '/' + endDate;
                @endif
                @if(Auth::user()->hasRole('Employee'))
                var url = '/employee/Pointagerepports/downloadRepports/' + startDate + '/' + endDate;
                @endif
                @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                var url = '/Responsable_gestionnaire/Pointagerepports/downloadRepports/' + startDate + '/' + endDate;
                @endif
            } else {
                @if(Auth::user()->hasRole('Admin'))
                var url = '/admin/Pointagerepports/downloadOneDayRepports/' + startDate;
                @endif
                @if(Auth::user()->hasRole('Responsable de projet'))
                var url = '/Responsable_de_projet/Pointagerepports/downloadOneDayRepports/' + startDate;
                @endif
                @if(Auth::user()->hasRole('Employee'))
                var url = '/employee/Pointagerepports/downloadOneDayRepports/' + startDate;
                @endif
                @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                var url = '/Responsable_gestionnaire/Pointagerepports/downloadOneDayRepports/' + startDate;
                @endif
            }

            var queryParams = [];
            if (employeeId) {
                queryParams.push('employeeId=' + encodeURIComponent(employeeId));
            }
            if (projectId) {
                queryParams.push('projectId=' + encodeURIComponent(projectId));
            }
            if (selectedTasks.length > 0) {
                queryParams.push('taskIds=' + encodeURIComponent(JSON.stringify(selectedTasks)));
            }
            if (clientId) {
                queryParams.push('clientId=' + encodeURIComponent(clientId));
            }
            
            // Generate a meaningful filename
            var fileName = 'Report_';
            if (projectName) {
                fileName += projectName.replace(/[^a-z0-9]/gi, '_') + '_';
            }
            if (startDate) {
                fileName += startDate;
            }
            if (endDate) {
                fileName += '_to_' + endDate;
            }
            fileName += '.pdf';
            
            // Add filename to query params
            queryParams.push('fileName=' + encodeURIComponent(fileName));
            
            if (queryParams.length > 0) {
                url += '?' + queryParams.join('&');
            }

            fetch(url, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                }
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
        var selectedTasks = Array.from(document.getElementById('taskFilter').selectedOptions).map(option => option.value);
        var clientId = document.getElementById('clientFilter').value;
        var projectName = '';
        var projectSelect = document.getElementById('projectFilter');
        if (projectSelect.selectedIndex > 0) {
            projectName = projectSelect.options[projectSelect.selectedIndex].text;
        }

        if (startDate) {
            var url;
            if (startDate && endDate) {
                @if(Auth::user()->hasRole('Admin'))
                var url = '/admin/CustomerPointagerepports/preview/' + startDate + '/' + endDate;
                @endif
                @if(Auth::user()->hasRole('Responsable de projet'))
                var url = '/Responsable_de_projet/CustomerPointagerepports/preview/' + startDate + '/' + endDate;
                @endif
                @if(Auth::user()->hasRole('Employee'))
                var url = '/employee/CustomerPointagerepports/preview/' + startDate + '/' + endDate;
                @endif
                @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                var url = '/Responsable_gestionnaire/CustomerPointagerepports/preview/' + startDate + '/' + endDate;
                @endif
            } else {
                @if(Auth::user()->hasRole('Admin'))
                var url = '/admin/CustomerPointagerepports/previewOneDay/' + startDate;
                @endif
                @if(Auth::user()->hasRole('Responsable de projet'))
                var url = '/Responsable_de_projet/CustomerPointagerepports/previewOneDay/' + startDate;
                @endif
                @if(Auth::user()->hasRole('Employee'))
                var url = '/employee/CustomerPointagerepports/previewOneDay/' + startDate;
                @endif
                @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                var url = '/Responsable_gestionnaire/CustomerPointagerepports/previewOneDay/' + startDate;
                @endif
            }

            var queryParams = [];
            if (employeeId) {
                queryParams.push('employeeId=' + encodeURIComponent(employeeId));
            }
            if (projectId) {
                queryParams.push('projectId=' + encodeURIComponent(projectId));
            }
            if (selectedTasks.length > 0) {
                queryParams.push('taskIds=' + encodeURIComponent(JSON.stringify(selectedTasks)));
            }
            if (clientId) {
                queryParams.push('clientId=' + encodeURIComponent(clientId));
            }
            
            if (queryParams.length > 0) {
                url += '?' + queryParams.join('&');
            }

            window.location.href = url;
        }
    });
    </script>

<!-- Date form script -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Retrieve date values from local storage and set them to the inputs
        var startDate = localStorage.getItem('startDate');
        var endDate = localStorage.getItem('endDate');
        var selectedMonth = localStorage.getItem('selectedMonth');

        if (startDate) {
            document.getElementById('startDateInput').value = startDate;
        }
        if (endDate) {
            document.getElementById('endDateInput').value = endDate;
        }
        if (selectedMonth) {
            document.getElementById('monthFilter').value = selectedMonth;
        }

        // Handle month selection
        document.getElementById('monthFilter').addEventListener('change', function() {
            const selectedMonth = this.value;
            if (selectedMonth) {
                const currentYear = new Date().getFullYear();
                // Create date for the first day of the selected month
                const firstDay = new Date(currentYear, selectedMonth - 1, 1);
                // Create date for the last day of the selected month
                const lastDay = new Date(currentYear, selectedMonth, 0);
                
                // Format dates as YYYY-MM-DD
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

        // Save date values to local storage when the form is submitted
        document.getElementById('dateForm').addEventListener('submit', function() {
            var startDateValue = document.getElementById('startDateInput').value;
            var endDateValue = document.getElementById('endDateInput').value;
            var selectedMonth = document.getElementById('monthFilter').value;

            localStorage.setItem('startDate', startDateValue);
            localStorage.setItem('endDate', endDateValue);
            localStorage.setItem('selectedMonth', selectedMonth);
        });
    });
</script>

<!-- Filter form script -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Retrieve date values from local storage
        var startDate = localStorage.getItem('startDate');
        var endDate = localStorage.getItem('endDate');

        // Set the hidden input values
        document.getElementById('hiddenStartDateInput').value = startDate;
        document.getElementById('hiddenEndDateInput').value = endDate;

        // Store filter values in localStorage when form is submitted
        document.getElementById('filterForm').addEventListener('submit', function(event) {
            event.preventDefault();

            // Save filter values to localStorage
            localStorage.setItem('employeeFilter', document.getElementById('employeeFilter').value);
            localStorage.setItem('projectFilter', document.getElementById('projectFilter').value);
            localStorage.setItem('taskFilter', document.getElementById('taskFilter').value);
            localStorage.setItem('projectTypeFilter', document.getElementById('projectTypeFilter').value);

            var startDateValue = startDate;
            var endDateValue = endDate;

            // Construct the URL
            @if(Auth::user()->hasRole('Admin'))
            var baseUrl = "{{ route('admin.pointage.filterPointageTable') }}";
            @endif
            @if(Auth::user()->hasRole('Responsable de projet'))
            var baseUrl = "{{ route('Responsable_de_projet.pointage.filterPointageTable') }}";
            @endif
            @if(Auth::user()->hasRole('Employee'))
            var baseUrl = "{{ route('employee.pointage.filterPointageTable') }}";
            @endif
            @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
            var baseUrl = "{{ route('Responsable_gestionnaire.pointage.filterPointageTable') }}";
            @endif
            var url = new URL(baseUrl, window.location.origin);

            // Add parameters to URL
            if (startDateValue) url.searchParams.append('start_date', startDateValue);
            if (endDateValue) url.searchParams.append('end_date', endDateValue);

            // Add filter values to URL
            var employeeFilter = document.getElementById('employeeFilter').value;
            var projectFilter = document.getElementById('projectFilter').value;
            var taskFilterEl = document.getElementById('taskFilter');
            var selectedTaskValues = Array.from(taskFilterEl.selectedOptions).map(o => o.value).filter(Boolean);
            var projectTypeFilter = document.getElementById('projectTypeFilter').value;

            if (employeeFilter) url.searchParams.append('employee', employeeFilter);
            if (projectFilter) url.searchParams.append('project', projectFilter);
            if (selectedTaskValues.length > 0) {
                selectedTaskValues.forEach(v => url.searchParams.append('task[]', v));
            }
            if (projectTypeFilter) url.searchParams.append('project_type', projectTypeFilter);

            // Set form action and submit
            this.action = url.toString();
            this.submit();
        });

        // Restore filter values from URL parameters or localStorage
        function restoreFilterValues() {
            const urlParams = new URLSearchParams(window.location.search);
            
            const employeeValue = urlParams.get('employee') || localStorage.getItem('employeeFilter');
            const projectValue = urlParams.get('project') || localStorage.getItem('projectFilter');
            const taskValues = urlParams.getAll('task[]');
            const projectTypeValue = urlParams.get('project_type') || localStorage.getItem('projectTypeFilter');

            if (employeeValue) document.getElementById('employeeFilter').value = employeeValue;
            if (projectValue) document.getElementById('projectFilter').value = projectValue;
            if (taskValues && taskValues.length > 0) {
                const taskSelect = document.getElementById('taskFilter');
                Array.from(taskSelect.options).forEach(opt => {
                    opt.selected = taskValues.includes(opt.value);
                });
            }
            if (projectTypeValue) document.getElementById('projectTypeFilter').value = projectTypeValue;
        }

        // Call restore function when page loads
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
                
                // Split the current value into date and time
                const [datePart, timePart] = currentValue.split(' '); // Split into date and time
                const [day, month, year] = datePart.split('-'); // Split date into day, month, year

                // Format date for input[type="date"] (YYYY-MM-DD)
                const formattedDate = `${year}-${month}-${day}`; // Convert to YYYY-MM-DD
                const formattedTime = timePart.substring(0, 5); // Get HH:MM from timePart

                // Set the values in the modal
                document.getElementById('dateInput').value = formattedDate; // Set the date input
                document.getElementById('timeInput').value = formattedTime; // Set the time input
                document.getElementById('taskId').value = taskId; // Set the task ID
                document.getElementById('fieldName').value = field; // Set the field name

                // Show the modal
                $('#editDateModal').modal('show');
            });
        });

        document.getElementById('saveChangesBtn').addEventListener('click', function() {
            const taskId = document.getElementById('taskId').value;
            const field = document.getElementById('fieldName').value;
            const date = document.getElementById('dateInput').value;
            const time = document.getElementById('timeInput').value;

            // Combine date and time into a single string
            const newValue = `${date} ${time}`;

            // Make an AJAX request to update the value
            $.ajax({
                url: `/admin/tasks/update-${field}/${taskId}`,
                type: 'POST',
                data: {
                    [field]: newValue,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.status === 'success') {
                        // Close the modal
                        $('#editDateModal').modal('hide');
                        // Reload the page
                        location.reload();
                    } else {
                        // Show error message from the response
                        alert(response.message || 'Failed to update the value.');
                    }
                },
                error: function(xhr) {
                    // Handle validation errors and other errors
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

<!-- Modal for editing date and time -->
<div class="modal fade" id="editDateModal" tabindex="-1" aria-labelledby="editDateModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editDateModalLabel">Edit Date and Time</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editDateForm">
                    <div class="mb-3">
                        <label for="dateInput" class="form-label">Date</label>
                        <input type="date" class="form-control" id="dateInput" required>
                    </div>
                    <div class="mb-3">
                        <label for="timeInput" class="form-label">Time</label>
                        <input type="time" class="form-control" id="timeInput" required>
                    </div>
                    <input type="hidden" id="taskId" value="">
                    <input type="hidden" id="fieldName" value="">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveChangesBtn">Save changes</button>
            </div>
        </div>
    </div>
</div>

<!-- Move Duration Modal -->
<div class="modal fade" id="moveDurationModal" tabindex="-1" aria-labelledby="moveDurationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="moveDurationModalLabel">Move Duration</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="moveDurationForm">
                    <div class="mb-3">
                        <label for="projectSelect" class="form-label">Project</label>
                        <select class="form-control" id="projectSelect" required>
                            @foreach($projects as $project)
                                <option value="{{ $project->id }}">{{ $project->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="taskSelect" class="form-label">Task</label>
                        <select class="form-control" id="taskSelect" required>
                            <!-- Tasks will be populated dynamically --> 
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="employeeSelect" class="form-label">Employee</label>
                        <select class="form-control" id="employeeSelect" required>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="moveStartDate" class="form-label">Start Date</label>
                        <input type="datetime-local" class="form-control" id="moveStartDate" required>
                    </div>
                    <div class="mb-3">
                        <label for="moveEndDate" class="form-label">End Date</label>
                        <input type="datetime-local" class="form-control" id="moveEndDate" required>
                    </div>
                    <div class="mb-3">
                        <label for="moveDuration" class="form-label">Duration (HH:MM)</label>
                        <input type="text" 
                               class="form-control" 
                               id="moveDuration" 
                               required 
                               pattern="^([0-9]{2}h:[0-9]{2}m)$"
                               placeholder="00h:00m">
                        <div class="invalid-feedback">
                            Please enter duration in format: 00h:00m
                        </div>
                    </div>
                    <input type="hidden" id="moveDurationMinutes">
                    <input type="hidden" id="originalTaskId" value="">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveMoveChangesBtn">Save changes</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Store original table data
    let originalTableData = [];
    
    // Function to store original table data
    function storeOriginalTableData() {
        const rows = document.querySelectorAll('#taskTableBody tr');
        originalTableData = Array.from(rows).map(row => ({
            projectId: row.getAttribute('data-project-id'),
            employeeId: row.getAttribute('data-employee-id')
        })).filter(data => data.projectId); // Only store rows with project IDs
    }

    // Function to highlight projects with tasks in date range
    function highlightProjectsWithTasks() {
        const projectSelect = document.getElementById('projectFilter');
        const startDate = document.getElementById('startDateInput').value;
        const endDate = document.getElementById('endDateInput').value;
        const clientId = document.getElementById('clientFilter').value;
        const employeeId = document.getElementById('employeeFilter').value;
        
        if (!startDate) return; // Exit if no date range selected
        
        // Get all project IDs that have tasks in the table
        const projectIds = new Set();
        
        // Use original table data if available, otherwise use current table data
        const dataToUse = originalTableData.length > 0 ? originalTableData : 
            Array.from(document.querySelectorAll('#taskTableBody tr')).map(row => ({
                projectId: row.getAttribute('data-project-id'),
                employeeId: row.getAttribute('data-employee-id')
            })).filter(data => data.projectId);
        
        dataToUse.forEach(data => {
            // Only add project if it matches the employee filter (if one is selected)
            if (!employeeId || data.employeeId === employeeId) {
                projectIds.add(data.projectId);
            }
        });
        
        // Update project options styling
        Array.from(projectSelect.options).forEach(option => {
            if (option.value && projectIds.has(option.value)) {
                option.style.fontWeight = 'bold';
                option.style.color = '#2dce89'; // Green color for highlighted projects
            } else {
                option.style.fontWeight = 'normal';
                option.style.color = ''; // Reset color
            }
        });

        // Store the highlighted project IDs in localStorage
        localStorage.setItem('highlightedProjects', JSON.stringify(Array.from(projectIds)));
    }

    // Function to restore project highlighting from localStorage
    function restoreProjectHighlighting() {
        const projectSelect = document.getElementById('projectFilter');
        const highlightedProjects = JSON.parse(localStorage.getItem('highlightedProjects') || '[]');
        
        Array.from(projectSelect.options).forEach(option => {
            if (option.value && highlightedProjects.includes(option.value)) {
                option.style.fontWeight = 'bold';
                option.style.color = '#2dce89';
            } else {
                option.style.fontWeight = 'normal';
                option.style.color = '';
            }
        });
    }

    // Function to store project tasks in localStorage
    function storeProjectTasks(projectId, tasks) {
        if (projectId) {
            localStorage.setItem('projectTasks_' + projectId, JSON.stringify(tasks));
        }
    }

    // Function to restore project tasks from localStorage
    function restoreProjectTasks(projectId) {
        const taskFilter = document.getElementById('taskFilter');
        const storedTasks = localStorage.getItem('projectTasks_' + projectId);
        
        if (storedTasks) {
            const tasks = JSON.parse(storedTasks);
            taskFilter.innerHTML = '<option value="">All Tasks</option>';
            tasks.forEach(task => {
                const option = new Option(task.name, task.id);
                taskFilter.add(option);
            });
        }
    }

    // Function to clear stored project tasks
    function clearStoredProjectTasks() {
        const keys = Object.keys(localStorage);
        keys.forEach(key => {
            if (key.startsWith('projectTasks_')) {
                localStorage.removeItem(key);
            }
        });
    }

    // Store original table data when page loads
    storeOriginalTableData();

    // Call the function when date range changes
    document.getElementById('startDateInput').addEventListener('change', function() {
        storeOriginalTableData();
        highlightProjectsWithTasks();
    });
    
    document.getElementById('endDateInput').addEventListener('change', function() {
        storeOriginalTableData();
        highlightProjectsWithTasks();
    });
    
    // Call the function when client or employee filter changes
    document.getElementById('clientFilter').addEventListener('change', function() {
        // Wait for the table to update after client change
        setTimeout(highlightProjectsWithTasks, 100);
    });
    
    document.getElementById('employeeFilter').addEventListener('change', function() {
        // Wait for the table to update after employee change
        setTimeout(highlightProjectsWithTasks, 100);
    });

    // Store highlighted projects before form submission
    document.getElementById('filterForm').addEventListener('submit', function() {
        highlightProjectsWithTasks();
    });
    
    // Call the function when the page loads
    highlightProjectsWithTasks();
    // Also restore highlighting from localStorage
    restoreProjectHighlighting();

    // Handle project selection change
    document.getElementById('projectFilter').addEventListener('change', function() {
        const projectId = this.value;
        if (projectId) {
            // Load tasks for selected project
            loadTasksForProject(projectId);
        } else {
            // If "All Projects" is selected, clear stored tasks and load all tasks
            clearStoredProjectTasks();
            loadAllTasks();
        }
    });

    // Function to load tasks for a project
    function loadTasksForProject(projectId, selectedTaskId = null) {
        let url;
        @if(Auth::user()->hasRole('Admin'))
            url = `/admin/tasks/get-project-tasks/${projectId}`;
        @endif
        @if(Auth::user()->hasRole('Responsable de projet'))
            url = `/Responsable_de_projet/tasks/get-project-tasks/${projectId}`;
        @endif
        @if(Auth::user()->hasRole('Employee'))
            url = `/employee/tasks/get-project-tasks/${projectId}`;
        @endif
        @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
            url = `/Responsable_gestionnaire/tasks/get-project-tasks/${projectId}`;
        @endif

        fetch(url)
            .then(response => response.json())
            .then(tasks => {
                const taskSelect = document.getElementById('taskSelect');
                taskSelect.innerHTML = '';
                
                // Add the current task first if it exists
                if (selectedTaskId) {
                    const currentTask = tasks.find(task => task.id == selectedTaskId);
                    if (currentTask) {
                        const option = new Option(currentTask.name, currentTask.id, true, true);
                        taskSelect.add(option);
                    }
                }
                
                // Add all other tasks
                tasks.forEach(task => {
                    if (!selectedTaskId || task.id != selectedTaskId) {
                        const option = new Option(task.name, task.id);
                        taskSelect.add(option);
                    }
                });

                // Store the tasks in localStorage
                storeProjectTasks(projectId, tasks);
            });
    }

    // Function to load all tasks
    async function loadAllTasks() {
        const taskFilter = document.getElementById('taskFilter');
        taskFilter.innerHTML = '<option value="">All Tasks</option>';
        const allTasks = await fetchAllTasks();
        allTasks.forEach(task => {
            const option = new Option(task.title, task.id);
            taskFilter.add(option);
        });
    }

    // Restore project tasks when page loads if a project is selected
    const selectedProjectId = document.getElementById('projectFilter').value;
    if (selectedProjectId) {
        restoreProjectTasks(selectedProjectId);
    }

    // ... rest of your existing code ...
});
</script>

    <script>
    $(document).ready(function() {
        $('#generatePeriodInvoiceBtn').click(function(e) {
            e.preventDefault();

            var projectId = $('#projectFilter').val();
            var startDate = $('#startDateInput').val();
            var endDate = $('#endDateInput').val();
            var comment = encodeURIComponent($('#periodInvoiceComment').val());
            var selectedTasks = Array.from(document.getElementById('taskFilter').selectedOptions).map(function(option){return option.value;}).filter(Boolean);

            if (!projectId) {
                alert('Please select a project from the filter dropdown.');
                return;
            }

            if (!startDate || !endDate) {
                alert('Please enter both start and end dates in the date filter section.');
                return;
            }

            var downloadUrl = '';
            @if(Auth::user()->hasRole('Responsable de projet'))
                downloadUrl = "{{ route('Responsable_de_projet.projects.invoicebyduration2', ['id' => ':projectId', 'startdate' => ':startDate', 'enddate' => ':endDate']) }}";
            @endif
            @if(Auth::user()->hasRole('Admin'))
                downloadUrl = "{{ route('admin.projects.invoicebyduration2', ['id' => ':projectId', 'startdate' => ':startDate', 'enddate' => ':endDate']) }}";
            @endif
            @if(Auth::user()->hasRole('Employee'))
                downloadUrl = "{{ route('employee.projects.invoicebyduration2', ['id' => ':projectId', 'startdate' => ':startDate', 'enddate' => ':endDate']) }}";
            @endif
            @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                downloadUrl = "{{ route('Responsable_gestionnaire.projects.invoicebyduration2', ['id' => ':projectId', 'startdate' => ':startDate', 'enddate' => ':endDate']) }}";
            @endif

            downloadUrl = downloadUrl
                .replace(':projectId', projectId)
                .replace(':startDate', startDate)
                .replace(':endDate', endDate);

            if (comment) {
                downloadUrl += '?comment=' + comment;
            }

            if (selectedTasks.length > 0) {
                // Append with proper separator if comment already added
                downloadUrl += (downloadUrl.indexOf('?') === -1 ? '?' : '&') + 'taskIds=' + encodeURIComponent(JSON.stringify(selectedTasks));
            }

            window.location.href = downloadUrl;
        });
    });
    </script>

    <script>
    $(document).ready(function() {
        function updateTotals() {
            let totalDuration = 0;
            let totalOperatingCost = 0;
            let totalSales = 0;

            // Get all rows from the table body
            const rows = document.querySelectorAll('#taskTableBody tr');
            
            rows.forEach(row => {
                // Skip the date header rows
                if (row.cells.length === 1) return;
                
                // Skip the total rows for each date group
                if (row.cells.length === 4) return;

                // Get the duration cell (6th column)
                const durationCell = row.cells[5];
                if (durationCell) {
                    const durationText = durationCell.textContent.trim();
                    if (durationText !== 'Still Working') {
                        // Parse duration (format: XXh:YYm:ZZs)
                        const matches = durationText.match(/(\d+)h:(\d+)m:(\d+)s/);
                        if (matches) {
                            const hours = parseInt(matches[1]);
                            const minutes = parseInt(matches[2]);
                            totalDuration += (hours * 3600) + (minutes * 60);
                        }
                    }
                }

                // Get operating cost (7th column)
                const operatingCostCell = row.cells[6];
                if (operatingCostCell) {
                    const costText = operatingCostCell.textContent.trim();
                    if (costText !== '0.00€') {
                        totalOperatingCost += parseFloat(costText.replace('€', ''));
                    }
                }

                // Get sales (8th column)
                const salesCell = row.cells[7];
                if (salesCell) {
                    const salesText = salesCell.textContent.trim();
                    if (salesText !== '0.00€') {
                        totalSales += parseFloat(salesText.replace('€', ''));
                    }
                }
            });

            // Format duration
            const hours = Math.floor(totalDuration / 3600);
            const minutes = Math.floor((totalDuration % 3600) / 60);
            const formattedDuration = `${hours}h:${minutes.toString().padStart(2, '0')}m`;

            // Update the display
            document.querySelector('label[for="totalDuration"] label').textContent = formattedDuration;
            document.querySelector('label[for="totalOperatingCost"] label').textContent = totalOperatingCost.toFixed(2) + '€';
            document.querySelector('label[for="totalSales"] label').textContent = totalSales.toFixed(2) + '€';
        }

        // Update totals when the page loads
        updateTotals();

        // Update totals when the filter form is submitted
        document.getElementById('filterForm').addEventListener('submit', function() {
            // Use setTimeout to ensure the table is updated before calculating totals
            setTimeout(updateTotals, 100);
        });

        // Update totals when the date form is submitted
        document.getElementById('dateForm').addEventListener('submit', function() {
            // Use setTimeout to ensure the table is updated before calculating totals
            setTimeout(updateTotals, 100);
        });
    });
    </script>

    <script>
    document.getElementById('exportMultipleTasksBtn').addEventListener('click', function() {
        var startDate = document.getElementById('startDateInput').value;
        var endDate = document.getElementById('endDateInput').value;
        var selectedTasks = Array.from(document.getElementById('taskFilter').selectedOptions).map(option => option.value);
        var projectId = document.getElementById('projectFilter').value;
        var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        if (!startDate || !endDate) {
            alert('Please select both start and end dates');
            return;
        }

        if (selectedTasks.length === 0) {
            alert('Please select at least one task');
            return;
        }

        if (!projectId) {
            alert('Please select a project');
            return;
        }

        var url;
        if (startDate && endDate) {
            @if(Auth::user()->hasRole('Admin'))
            var url = '/admin/Pointagerepports/downloadMultipleTasksRepports/' + startDate + '/' + endDate;
            @endif
            @if(Auth::user()->hasRole('Responsable de projet'))
            var url = '/Responsable_de_projet/Pointagerepports/downloadMultipleTasksRepports/' + startDate + '/' + endDate;
            @endif
            @if(Auth::user()->hasRole('Employee'))
            var url = '/employee/Pointagerepports/downloadMultipleTasksRepports/' + startDate + '/' + endDate;
            @endif
            @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
            var url = '/Responsable_gestionnaire/Pointagerepports/downloadMultipleTasksRepports/' + startDate + '/' + endDate;
            @endif
        }

        var queryParams = [];
        queryParams.push('projectId=' + encodeURIComponent(projectId));
        queryParams.push('taskIds=' + encodeURIComponent(JSON.stringify(selectedTasks)));
        
        if (queryParams.length > 0) {
            url += '?' + queryParams.join('&');
        }

        fetch(url, {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': csrfToken
            }
        })
        .then(response => response.blob())
        .then(blob => {
            var url = window.URL.createObjectURL(blob);
            var a = document.createElement('a');
            a.href = url;
            a.download = 'multiple_tasks_report.pdf';
            document.body.appendChild(a);
            a.click();
            a.remove();
            window.URL.revokeObjectURL(url);
        })
        .catch((error) => {
            console.error('Error:', error);
        });
    });
    </script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle Move Duration button clicks
        document.querySelectorAll('.move-duration-btn').forEach(button => {
            button.addEventListener('click', function() {
                const taskId = this.getAttribute('data-task-id');
                const projectId = this.getAttribute('data-project-id');
                const employeeId = this.getAttribute('data-employee-id');
                const startDate = this.getAttribute('data-start-date');
                const endDate = this.getAttribute('data-end-date');
                const duration = this.getAttribute('data-duration');

                // Set initial values in the modal
                document.getElementById('projectSelect').value = projectId;
                document.getElementById('employeeSelect').value = employeeId;
                document.getElementById('moveStartDate').value = startDate;
                document.getElementById('moveEndDate').value = endDate;
                document.getElementById('moveDuration').value = minutesToHoursMinutes(parseInt(duration));
                document.getElementById('moveDurationMinutes').value = duration;
                document.getElementById('originalTaskId').value = taskId;

                // Load tasks for selected project
                loadTasksForProject(projectId, taskId);

                $('#moveDurationModal').modal('show');
            });
        });

        // Handle project selection change
        document.getElementById('projectSelect').addEventListener('change', function() {
            loadTasksForProject(this.value);
        });

        // Function to format datetime for input with timezone adjustment
        function formatDateTimeForInput(dateString) {
            const date = new Date(dateString);
            // Add one hour to compensate for timezone difference
            date.setHours(date.getHours() + 1);
            return date.toISOString().slice(0, 16);
        }

        // Function to load tasks for a project
        function loadTasksForProject(projectId, selectedTaskId = null) {
            let url;
            @if(Auth::user()->hasRole('Admin'))
                url = `/admin/tasks/get-project-tasks/${projectId}`;
            @endif
            @if(Auth::user()->hasRole('Responsable de projet'))
                url = `/Responsable_de_projet/tasks/get-project-tasks/${projectId}`;
            @endif
            @if(Auth::user()->hasRole('Employee'))
                url = `/employee/tasks/get-project-tasks/${projectId}`;
            @endif
            @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                url = `/Responsable_gestionnaire/tasks/get-project-tasks/${projectId}`;
            @endif

            fetch(url)
                .then(response => response.json())
                .then(tasks => {
                    const taskSelect = document.getElementById('taskSelect');
                    taskSelect.innerHTML = '';
                    
                    // Add the current task first if it exists
                    if (selectedTaskId) {
                        const currentTask = tasks.find(task => task.id == selectedTaskId);
                        if (currentTask) {
                            const option = new Option(currentTask.name, currentTask.id, true, true);
                            taskSelect.add(option);
                        }
                    }
                    
                    // Add all other tasks
                    tasks.forEach(task => {
                        if (!selectedTaskId || task.id != selectedTaskId) {
                            const option = new Option(task.name, task.id);
                            taskSelect.add(option);
                        }
                    });
                });
        }

        // Function to convert duration from minutes to HH:MM format
        function minutesToHoursMinutes(minutes) {
            const hours = Math.floor(minutes / 60);
            const remainingMinutes = minutes % 60;
            return `${hours.toString().padStart(2, '0')}h:${remainingMinutes.toString().padStart(2, '0')}m`;
        }

        // Function to convert HH:MM format to minutes
        function hoursMinutesToMinutes(durationString) {
            const matches = durationString.match(/^(\d{2})h:(\d{2})m$/);
            if (!matches) return null;
            
            const hours = parseInt(matches[1]);
            const minutes = parseInt(matches[2]);
            return (hours * 60) + minutes;
        }

        // Auto-calculate duration when start/end dates change
        function calculateAndSetDuration() {
            const startValue = document.getElementById('moveStartDate')?.value;
            const endValue = document.getElementById('moveEndDate')?.value;
            const durationInput = document.getElementById('moveDuration');
            const durationMinutesInput = document.getElementById('moveDurationMinutes');
            const endInput = document.getElementById('moveEndDate');

            if (!startValue || !endValue) {
                return;
            }

            const start = new Date(startValue);
            const end = new Date(endValue);

            const diffMs = end - start;
            if (Number.isNaN(diffMs)) {
                return;
            }

            if (diffMs < 0) {
                // Mark end date invalid if it is before start date
                endInput.classList.add('is-invalid');
                endInput.classList.remove('is-valid');
                return;
            } else {
                endInput.classList.remove('is-invalid');
                endInput.classList.add('is-valid');
            }

            const minutes = Math.floor(diffMs / 60000);
            durationInput.value = minutesToHoursMinutes(minutes);
            durationMinutesInput.value = String(minutes);
            durationInput.classList.remove('is-invalid');
            durationInput.classList.add('is-valid');
        }

        // Listen to changes on start and end datetime inputs
        const moveStartDateEl = document.getElementById('moveStartDate');
        const moveEndDateEl = document.getElementById('moveEndDate');
        if (moveStartDateEl) {
            moveStartDateEl.addEventListener('change', calculateAndSetDuration);
            moveStartDateEl.addEventListener('input', calculateAndSetDuration);
        }
        if (moveEndDateEl) {
            moveEndDateEl.addEventListener('change', calculateAndSetDuration);
            moveEndDateEl.addEventListener('input', calculateAndSetDuration);
        }

        // Validate duration input format
        document.getElementById('moveDuration').addEventListener('input', function(e) {
            const input = e.target;
            const durationPattern = /^[0-9]{2}h:[0-9]{2}m$/;
            
            if (durationPattern.test(input.value)) {
                input.classList.remove('is-invalid');
                input.classList.add('is-valid');
                const minutes = hoursMinutesToMinutes(input.value);
                document.getElementById('moveDurationMinutes').value = minutes;
            } else {
                input.classList.remove('is-valid');
                input.classList.add('is-invalid');
            }
        });

        // Update the save changes handler
        document.getElementById('saveMoveChangesBtn').addEventListener('click', function() {
            const durationInput = document.getElementById('moveDuration');
            const durationMinutes = document.getElementById('moveDurationMinutes').value;

            if (!durationInput.value.match(/^[0-9]{2}h:[0-9]{2}m$/)) {
                alert('Please enter duration in correct format (00h:00m)');
                return;
            }

            const formData = {
                original_task_id: document.getElementById('originalTaskId').value,
                new_task_id: document.getElementById('taskSelect').value,
                employee_id: document.getElementById('employeeSelect').value,
                start_date: document.getElementById('moveStartDate').value,
                end_date: document.getElementById('moveEndDate').value,
                duration: durationMinutes, // Send the converted minutes value
                _token: '{{ csrf_token() }}'
            };

            let url;
            @if(Auth::user()->hasRole('Admin'))
                url = '/admin/tasks/move-duration';
            @endif
            @if(Auth::user()->hasRole('Responsable de projet'))
                url = '/Responsable_de_projet/tasks/move-duration';
            @endif
            @if(Auth::user()->hasRole('Employee'))
                url = '/employee/tasks/move-duration';
            @endif
            @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                url = '/Responsable_gestionnaire/tasks/move-duration';
            @endif

            $.ajax({
                url: url,
                type: 'POST',
                data: formData,
                success: function(response) {
                    if (response.status === 'success') {
                        location.reload();
                    } else {
                        alert('Failed to move duration: ' + (response.message || 'Unknown error'));
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error details:', {
                        status: status,
                        error: error,
                        response: xhr.responseText
                    });
                    alert('Error moving duration: ' + error);
                }
            });

            $('#moveDurationModal').modal('hide');
        });
    });
    </script>
</body>

</html>