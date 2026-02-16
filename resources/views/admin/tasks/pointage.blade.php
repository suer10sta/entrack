<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tasks Timer - DalyTrac</title>
    
    @vite('resources/css/app.css')
    @livewireStyles
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    {{-- Alpine.js Modal Functions - Must be defined before Alpine.js loads --}}
    <script>
        // --- ALPINE MODAL LOGIC ---
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
                        // Adjust for timezone offset
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
                        url: url,
                        type: 'POST',
                        data: {
                            [this.field]: newValue,
                            _token: '{{ csrf_token() }}'
                        },
                        success: (response) => {
                            if (response.status === 'success') {
                                location.reload();
                            } else {
                                alert(response.message || 'Failed to update.');
                            }
                        },
                        error: (xhr) => {
                            let msg = 'Failed to update.';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                msg = xhr.responseJSON.message;
                            }
                            alert(msg);
                        }
                    });
                    this.show = false;
                }
            }
        }
        
        @php
            $rolePrefix = 'admin';
            if (auth()->user()->hasRole('Responsable de projet')) {
                $rolePrefix = 'Responsable_de_projet';
            } elseif (auth()->user()->hasRole('Employee')) {
                $rolePrefix = 'employee';
            } elseif (auth()->user()->hasRole('Responsable de projet et gestionnaire')) {
                $rolePrefix = 'Responsable_gestionnaire';
            }
        @endphp
        const rolePrefix = @json($rolePrefix);

        function moveDurationModal() {
            return {
                show: false,
                originalTimerId: null,
                currentTaskId: null,
                projectId: '',
                taskId: '',
                employeeId: '',
                startDate: '',
                endDate: '',
                duration: '',
                durationMinutes: 0,
                originalDuration: 0,
                open(detail) {
                    this.originalTimerId = detail.timerId;
                    this.currentTaskId = detail.taskId;
                    this.projectId = detail.projectId;
                    this.employeeId = detail.employeeId;
                    this.startDate = detail.startDate;
                    this.endDate = detail.endDate;
                    this.originalDuration = detail.duration;
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
                        // keep string normalized (e.g. 1h:5m -> 01h:05m)
                        this.duration = this.minutesToHoursMinutes(this.durationMinutes);
                    }
                },
                saveMove() {
                    if (!this.duration.match(/^(\d+)h:(\d{2})m$/)) {
                        alert('Please enter duration in format (e.g., 01h:30m or 10h:00m)');
                        return;
                    }
                    if (this.durationMinutes <= 0) {
                        alert('Duration must be greater than 0 minutes.');
                        return;
                    }
                    
                    const formData = {
                        original_task_id: this.originalTimerId, 
                        new_task_id: this.taskId || this.currentTaskId,
                        employee_id: this.employeeId, start_date: this.startDate,
                        end_date: this.endDate,
                        duration: this.durationMinutes,
                        _token: '{{ csrf_token() }}'
                    };

                    let url = '';
                    @if(Auth::user()->hasRole('Admin')) url = '/admin/tasks/move-duration'; @endif
                    @if(Auth::user()->hasRole('Responsable de projet')) url = '/Responsable_de_projet/tasks/move-duration'; @endif
                    @if(Auth::user()->hasRole('Employee')) url = '/employee/tasks/move-duration'; @endif
                    @if(Auth::user()->hasRole('Responsable de projet et gestionnaire')) url = '/Responsable_gestionnaire/tasks/move-duration'; @endif

                    $.ajax({
                        url: url,
                        type: 'POST',
                        data: formData,
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
    </script>
    
    <script src="//unpkg.com/alpinejs" defer></script> 

    <style>
        /* Custom Select Styling */
        select.form-select-custom {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 0.5rem center;
            background-repeat: no-repeat;
            background-size: 1.5em 1.5em;
            padding-right: 2.5rem;
        }
        /* Remove dropdown arrow for multiple selects */
        select.form-select-custom[multiple] {
            background-image: none;
            padding-right: 0.75rem;
        }
        select:invalid { color: #9ca3af; }
        option { color: #111827; }
        /* Multiple select styling */
        select[multiple] {
            overflow-y: auto;
        }
        select[multiple] option {
            padding: 0.5rem;
            margin: 0.125rem 0;
        }
        select[multiple] option:checked {
            background-color: #3b82f6;
            color: white;
        }
        /* Green text for projects with tasks in date range */
        #projectFilter option.has-tasks {
            color: #16a34a !important; /* green-600 */
            font-weight: 500;
        }
        /* Task filter specific styling to show full task names */
        #taskFilter {
            min-width: 100%;
            width: 100%;
        }
        #taskFilter option {
            white-space: normal;
            word-wrap: break-word;
            overflow-wrap: break-word;
            padding: 0.5rem;
            margin: 0.125rem 0;
        }

        /* Table Highlight Styles */
        .highlighted-task td { background-color: #e6f3ff !important; }
        .regie-task td { background-color: #fff3e6 !important; }
        
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

        /* Editable Comment Styles */
        td.editable-comment {
            cursor: pointer;
            background-color: #f9fafb; /* subtle background */
        }
        td.editable-comment:hover {
            background-color: #eff6ff; /* light blue on hover */
        }
        td.editable-comment .comment-text {
            border-bottom: 1px dashed #9ca3af; /* dashed underline to indicate editability */
        }
        td.editable-comment .comment-text:empty::before {
            content: 'Double-click to add comment';
            font-size: 0.75rem; /* text-xs */
            color: #9ca3af;     /* gray-400 */
            font-style: italic;
        }

        /* Table styles from Invoice page */
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        .table {
            width: 100%;
        }
        .table td, .table th {
            padding: 0.75rem;
        }
        
        [x-cloak] { display: none !important; }
    </style>
</head>

<body class="bg-gray-50 flex h-screen overflow-hidden font-sans" x-data>

    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-NKDMSK6" height="0" width="0"
            style="display: none; visibility: hidden"></iframe></noscript>

    {{-- Sidebar Component --}}
    @if(Auth::user()->hasRole('Admin'))
        <x-dashboard.sidebar />
    @else
        <x-employees.sidebar />
    @endif

    {{-- Main Wrapper (Navbar + Content) --}}
    <div class="flex flex-col flex-1 overflow-y-auto">

        {{-- Navbar Component --}}
        <x-dashboard.navbar />

        {{-- Main Content Area --}}
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-[#eaebef] p-8 space-y-8">

            {{-- 1. Header Section --}}
            <div class="flex flex-wrap justify-between items-center gap-4">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">Tasks Timer</h1>
                </div>
                <div class="flex items-center gap-3">
                    <button type="button" id="exportBtn" class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg shadow-sm">
                        Export to Excel
                    </button>
                    <button type="button" id="exportPDFBtn" class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg shadow-sm">
                        Export to PDF
                    </button>
                    <button type="button" id="exportPDFBtnCustomer" class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg shadow-sm">
                        Export to PDF for Customer
                    </button>
                </div>
            </div>
            
            {{-- Session Message --}}
            @if(session('alertmessage'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <strong class="font-bold">Error!</strong>
                <span class="block sm:inline">{{ session('alertmessage') }}</span>
                <button type="button" onclick="this.parentElement.style.display='none';" class="absolute top-0 bottom-0 right-0 px-4 py-3 font-bold text-xl" aria-label="Close">&times;</button>
            </div>
            @endif

            {{-- 2. Totals Section --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <div class="flex flex-wrap items-center justify-center sm:justify-end gap-x-6 gap-y-2">
                    <div class="text-sm">
                        <span class="font-medium text-gray-700">Total Duration:</span>
                        <strong class="text-gray-900" id="totalDurationLabel">0h:00m</strong>
                    </div>
                    <div class="text-sm">
                        <span class="font-medium text-gray-700">Total Operating Cost:</span>
                        <strong class="text-green-600" id="totalOperatingCostLabel">0.00€</strong>
                    </div>
                    <div class="text-sm">
                        <span class="font-medium text-gray-700">Total Sales:</span>
                        <strong class="text-gray-900" id="totalSalesLabel">0.00€</strong>
                    </div>
                </div>
            </div>

            {{-- 3. Date Fetch Form --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h6 class="text-base font-semibold text-gray-800 mb-4">Fetch Data</h6>
                @php $dateFormAction = ''; @endphp
                @if(Auth::user()->hasRole('Admin')) @php $dateFormAction = route('admin.pointage.filterPointageByDate'); @endphp @endif
                @if(Auth::user()->hasRole('Responsable de projet')) @php $dateFormAction = route('Responsable_de_projet.pointage.filterPointageByDate'); @endphp @endif
                @if(Auth::user()->hasRole('Employee')) @php $dateFormAction = route('employee.pointage.filterPointageByDate'); @endphp @endif
                @if(Auth::user()->hasRole('Responsable de projet et gestionnaire')) @php $dateFormAction = route('Responsable_gestionnaire.pointage.filterPointageByDate'); @endphp @endif
                
                <form method="GET" action="{{ $dateFormAction }}" id="dateForm" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 items-end">
                    @csrf
                    <div>
                        <label for="monthFilter" class="block text-sm font-medium text-gray-700 mb-1">Month</label>
                        <select id="monthFilter" name="month" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3 form-select-custom">
                            <option value="">Select Month</option>
                            @for($i = 1; $i <= 12; $i++)
                                <option value="{{ $i }}">{{ date('F', mktime(0, 0, 0, $i, 1)) }}</option>
                            @endfor
                        </select>
                    </div>
                    <div>
                        <label for="startDateInput" class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                        <input type="date" id="startDateInput" name="start_date" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3">
                    </div>
                    <div>
                        <label for="endDateInput" class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                        <input type="date" id="endDateInput" name="end_date" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3">
                    </div>
                    <div class="flex items-center gap-3">
                        <button type="submit" class="inline-flex justify-center bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-lg text-sm shadow-sm w-full">Get</button>
                        @if(Auth::user()->hasRole('Admin'))
                        <a id="employeesDailyTotalsBtn" class="inline-flex justify-center bg-white hover:bg-gray-50 text-gray-700 font-medium px-4 py-2 rounded-lg text-sm border border-gray-300 shadow-sm w-full" target="_blank">Employees PDF</a>
                        @endif
                    </div>
                </form>
            </div>

            {{-- 4. Filter Form --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h6 class="text-base font-semibold text-gray-800 mb-4">Filters</h6>
                @php $filterFormAction = ''; @endphp
                @if(Auth::user()->hasRole('Admin')) @php $filterFormAction = route('admin.pointage.filterPointageTable'); @endphp @endif
                @if(Auth::user()->hasRole('Responsable de projet')) @php $filterFormAction = route('Responsable_de_projet.pointage.filterPointageTable'); @endphp @endif
                @if(Auth::user()->hasRole('Employee')) @php $filterFormAction = route('employee.pointage.filterPointageTable'); @endphp @endif
                @if(Auth::user()->hasRole('Responsable de projet et gestionnaire')) @php $filterFormAction = route('Responsable_gestionnaire.pointage.filterPointageTable'); @endphp @endif
                
                <form method="GET" action="{{ $filterFormAction }}" id="filterForm" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-7 gap-4 items-end">
                    @csrf
                       <input type="hidden" id="hiddenStartDateInput" name="start_date">
                       <input type="hidden" id="hiddenEndDateInput" name="end_date">

                       {{-- Filters --}}
                        <div class="lg:col-span-1">
                            <label for="clientFilter" class="block text-sm font-medium text-gray-700 mb-1">Client</label>
                            <select id="clientFilter" name="client" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3 form-select-custom">
                            <option value="">All Clients</option>
                                @foreach($clients as $client)
                            <option value="{{ $client->id }}" {{ request('client') == $client->id ? 'selected' : '' }}>{{ $client->name }}</option>
                                @endforeach
                            </select>
                        </div>
    
                        <div class="lg:col-span-1">
                            <label for="employeeFilter" class="block text-sm font-medium text-gray-700 mb-1">Employee</label>
                                <select id="employeeFilter" name="employee" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3 form-select-custom">
                                <option value="">All Employees</option>
                                    @foreach($employees as $employee)
                                <option value="{{ $employee->id }}" {{ request('employee') == $employee->id ? 'selected' : '' }}>{{ $employee->name }}</option>
                                    @endforeach
                                </select>
                        </div>
    
                        <div class="lg:col-span-1">
                            <label for="projectFilter" class="block text-sm font-medium text-gray-700 mb-1">Project</label>
                                <select id="projectFilter" name="project" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3 form-select-custom">
                                    <option value="">All Projects</option>
                                        @foreach($projects as $project)
                                        @php
                                            $hasTasksInRange = isset($projectsWithTasks) && in_array($project->id, $projectsWithTasks);
                                            $hasExactInvoice = isset($projectsWithExactInvoices) && in_array($project->id, $projectsWithExactInvoices);
                                        @endphp
                                        <option
                                            value="{{ $project->id }}"
                                            {{ request('project') == $project->id ? 'selected' : '' }}
                                            data-tva="{{ $project->tva ?? 0 }}"
                                            @if($hasTasksInRange && !$hasExactInvoice)
                                                class="has-tasks" style="color: #16a34a; font-weight: 500;"
                                            @endif
                                        >
                                            {{ strtoupper($project->name) }}
                                            @if($hasExactInvoice)
                                                (INVOICED)
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>
    
                            <div class="lg:col-span-2">
                                <label for="taskFilter" class="block text-sm font-medium text-gray-700 mb-1">Task</label>
                                <select id="taskFilter" name="task[]" multiple class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3 form-select-custom" style="min-height: 100px; min-width: 100%;">
                                    <option value="all" {{ in_array('all', (array)request('task', [])) || empty(request('task')) ? 'selected' : '' }}>All Tasks</option>
                                    @foreach($allTasks as $task)
                                    <option value="{{ $task->id }}" {{ in_array($task->id, (array)request('task', [])) ? 'selected' : '' }}>
                                        {{ $task->title }}
                                    </option>
                                    @endforeach
                                </select>
                                <p class="mt-1 text-xs text-gray-500">Hold Ctrl/Cmd to select multiple tasks</p>
                            </div>
    
                            <div class="lg:col-span-1">
                                <label for="projectTypeFilter" class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                                    <select id="projectTypeFilter" name="project_type" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3 form-select-custom">
                                    <option value="">All Types</option>
                                    <option value="regie" {{ request('project_type') == 'regie' ? 'selected' : '' }}>Regie</option>
                                    <option value="devis" {{ request('project_type') == 'devis' ? 'selected' : '' }}>Devis</option>
                                </select>
                            </div>
                            
                            {{-- Filter Button --}}
                            <div class="lg:col-span-1 flex items-end">
                                <button type="submit" id="applyFiltersBtn" class="inline-flex justify-center bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-lg text-sm shadow-sm w-full">Apply Filters</button>
                            </div>
    
                            {{-- Comment and Button Row --}}
                            <div class="lg:col-span-2 mt-5">
                                <label for="periodInvoiceComment" class="block text-sm font-medium text-gray-700 mb-1">Invoice Comment</label>
                                <textarea rows="3" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" id="periodInvoiceComment" placeholder="Optional comment for the period invoice..."></textarea>
                            </div>
                            
                            <div class="lg:col-span-1 mt-4 flex items-end">
                                <button type="button" id="generatePeriodInvoiceBtn" class="inline-flex justify-center bg-green-600 hover:bg-green-700 text-white font-medium px-4 py-2 rounded-lg text-sm shadow-sm w-full">Generate Period Invoice</button>
                        </div>
                </form>
            </div>

            {{-- 5. Table Section --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="table-responsive">
                    <table id="taskTable" class="w-full divide-y divide-gray-200 table table-fixed">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600 uppercase">Project</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600 uppercase">Task</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600 uppercase">Employee</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600 uppercase">Start Date</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600 uppercase">End Date</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600 uppercase">Duration</th>
                                <th class="px-4 py-3 text-right text-sm font-semibold text-gray-600 uppercase">Op. Cost</th>
                                <th class="px-4 py-3 text-right text-sm font-semibold text-gray-600 uppercase">Sales</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600 uppercase">
                                    Comment
                                    <span class="block text-[10px] font-normal text-gray-400 normal-case">Double-click to edit</span>
                                </th>
                                <th class="px-4 py-3 text-center text-sm font-semibold text-gray-600 uppercase">Actions</th>
                            </tr>
                        </thead>

                        <tbody id="taskTableBody" class="bg-white divide-y divide-gray-200">
                            @php
                                function formatDuration($minutes) {
                                    $hours = floor($minutes / 60);
                                    $minutes = $minutes % 60;
                                    $seconds = 0; 
                                    return sprintf('%02dh:%02dm:%02ds', $hours, $minutes, $seconds);
                                }
                            @endphp
                            
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
                                        Log::error("Invalid date format: " . $start_date . ". Error: " . $e->getMessage());
                                        continue;
                                    }
                                @endphp

                                @if($dateObject)
                                <tr>
                                    <td colspan="10" class="px-4 py-2 bg-gray-100 text-gray-800 font-bold">
                                        <strong>{{ $displayDate }}</strong>
                                    </td>
                                </tr>
                                @endif
                            
                                @foreach($tasks as $task)
                                    @php 
                                        // Use cached relationships if present; fall back to DB lookups safely
                                        $taskModel = App\Models\Tasks::find($task->task_id);
                                        $task_project_id = $task->project_id ?? optional($taskModel)->project_id;
                                        $project = $task_project_id ? App\Models\Project::find($task_project_id) : null;
                                        $employee = App\Models\Employees::find($task->employee_id);

                                        // Skip rendering if any critical relation is missing to avoid runtime errors
                                        if (!$taskModel || !$project || !$employee) {
                                            continue;
                                        }
                                        $groupTotalDuration += $task->duration;
                                        $groupTotal += $task->end_date ? $task->cost : 0;
                                        $groupTotalRecette += $task->end_date ? (($task->duration / 60) * $project->cost_per_hour) : 0;
                                        
                                        $hasInvoice = in_array($task_project_id, $periodInvoices);
                                        $isRegie = $project->regie === 1;
                                    @endphp
                                
                                    <tr class="{{ $hasInvoice ? 'highlighted-task' : '' }} {{ $isRegie ? 'regie-task' : '' }} hover:bg-gray-50" 
                                        id="{{ $task->id }}" 
                                        data-employee-id="{{ $task->employee_id }}"
                                        data-project-id="{{ $task_project_id}}" 
                                        data-task-id="{{ $task->task_id }}">
                                        
                                        <td class="px-4 py-3 text-sm text-gray-800 whitespace-normal break-words">
                                            @if(Auth::user()->can('View project'))
                                                @if(Auth::user()->hasRole('Admin')) <a href="{{route('admin.projects.show',$task_project_id)}}" class="text-blue-600 hover:text-blue-800" @click.stop>{{ $project->name }}</a> @endif
                                                @if(Auth::user()->hasRole('Responsable de projet')) <a href="{{route('Responsable_de_projet.projects.show',$task_project_id)}}" class="text-blue-600 hover:text-blue-800" @click.stop>{{ $project->name }}</a> @endif
                                                @if(Auth::user()->hasRole('Employee')) <a href="{{route('employee.projects.show',$task_project_id)}}" class="text-blue-600 hover:text-blue-800" @click.stop>{{ $project->name }}</a> @endif
                                                @if(Auth::user()->hasRole('Responsable de projet et gestionnaire')) <a href="{{route('Responsable_gestionnaire.projects.show',$task_project_id)}}" class="text-blue-600 hover:text-blue-800" @click.stop>{{ $project->name }}</a> @endif
                                            @else
                                                {{ $project->name }}
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-800 whitespace-normal break-words">
                                            @if(auth()->user()->hasRole('Admin')) <a href="{{route('admin.tasks.show', $task->task_id)}}" class="text-blue-600 hover:text-blue-800" @click.stop>{{ $task->task_name }}</a> @endif
                                            @if(auth()->user()->hasRole('Responsable de projet')) <a href="{{route('Responsable_de_projet.tasks.show', $task->task_id)}}" class="text-blue-600 hover:text-blue-800" @click.stop>{{ $task->task_name }}</a> @endif
                                            @if(auth()->user()->hasRole('Employee')) <a href="{{route('employee.tasks.show', $task->task_id)}}" class="text-blue-600 hover:text-blue-800" @click.stop>{{ $task->task_name }}</a> @endif
                                            @if(Auth::user()->hasRole('Responsable de projet et gestionnaire')) <a href="{{route('Responsable_gestionnaire.tasks.show', $task->task_id)}}" class="text-blue-600 hover:text-blue-800" @click.stop>{{ $task->task_name }}</a> @endif
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-800 whitespace-normal break-words">
                                            @if(Auth::user()->can('View employee'))
                                                @if(Auth::user()->hasRole('Admin')) <a href="{{route('admin.employees.show',$employee->id )}}" class="text-blue-600 hover:text-blue-800" @click.stop>{{ $employee->name }}</a> @endif
                                                @if(Auth::user()->hasRole('Responsable de projet')) <a href="{{route('Responsable_de_projet.employees.show',$employee->id )}}" class="text-blue-600 hover:text-blue-800" @click.stop>{{ $employee->name }}</a> @endif
                                                @if(Auth::user()->hasRole('Employee')) <a href="{{route('employee.employees.show',$employee->id )}}" class="text-blue-600 hover:text-blue-800" @click.stop>{{ $employee->name }}</a> @endif
                                                @if(Auth::user()->hasRole('Responsable de projet et gestionnaire')) <a href="{{route('Responsable_gestionnaire.employees.show',$employee->id )}}" class="text-blue-600 hover:text-blue-800" @click.stop>{{ $employee->name }}</a> @endif
                                            @else
                                                {{ $employee->name }}
                                            @endif
                                        </td>
                                        
                                        <td class="px-4 py-3 whitespace-nowrap text-sm" data-field="start_date" 
                                            @click.prevent.stop="$dispatch('open-edit-date-modal', { 
                                                taskId: {{ $task->id }}, 
                                                field: 'start_date', 
                                                datetime: '{{ \Carbon\Carbon::parse($task->start_date)->format('Y-m-d\TH:i') }}' 
                                            })">
                                            {{ \Carbon\Carbon::parse($task->start_date)->format('d-m-Y H:i:s') }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm" data-field="end_date"
                                            @click.prevent.stop="$dispatch('open-edit-date-modal', { 
                                                taskId: {{ $task->id }}, 
                                                field: 'end_date', 
                                                datetime: '{{ $task->end_date ? \Carbon\Carbon::parse($task->end_date)->format('Y-m-d\TH:i') : '' }}' 
                                            })">
                                            {{ $task->end_date ? \Carbon\Carbon::parse($task->end_date)->format('d-m-Y H:i:s') : 'Still Working' }}
                                        </td>
                                        
                                        <td id="duration-{{ $task->id }}" class="px-4 py-3 whitespace-nowrap text-sm text-gray-800" data-task-id="{{ $task->id }}">
                                            @if (is_null($task->end_date))
                                                <script>
                                                    document.addEventListener('DOMContentLoaded', function () {
                                                        function startTimer(taskId, startDate) {
                                                            const durationCellId = 'duration-' + taskId;
                                                            var intervalId = setInterval(function() {
                                                                var durationCell = document.getElementById(durationCellId);
                                                                if (durationCell) {
                                                                    var durationInMilliseconds = new Date() - new Date(startDate);
                                                                    var hours = Math.floor(durationInMilliseconds / 3600000);
                                                                    var minutes = Math.floor((durationInMilliseconds % 3600000) / 60000);
                                                                    var seconds = Math.floor((durationInMilliseconds % 60000) / 1000);
                                                                    var durationFormatted = (hours < 10 ? '0' : '') + hours + 'h:' +
                                                                        (minutes < 10 ? '0' : '') + minutes + 'm:' +
                                                                        (seconds < 10 ? '0' : '') + seconds + 's';
                                                                    durationCell.textContent = durationFormatted;
                                                                } else {
                                                                    clearInterval(intervalId);
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
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-800 text-right">{{ $task->end_date ? number_format((float)$task->cost, 3, '.', '') : '0.00' }}€</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-800 text-right">{{ $task->end_date ? number_format((($task->duration / 60) * $project->cost_per_hour), 3, '.', '') : '0.00' }}€</td>
                                        
                                        <td class="px-4 py-3 text-sm text-gray-800 whitespace-normal break-words editable-comment" 
                                            data-id="{{ $task->id }}" 
                                            data-field="comment" 
                                            data-update-url="{{ route('admin.tasks.updateField', ['field' => 'comment', 'id' => $task->id]) }}">
                                            <span class="comment-text">{{ $task->comment }}</span>
                                        </td>
                                        
                                        <td class="px-2 py-3">
                                            <div class="flex flex-col items-center gap-2">
                                                @if($task->end_date)
                                                <button type="button" class="inline-flex justify-center w-full text-white bg-blue-600 hover:bg-blue-700 font-medium rounded text-xs px-2 py-1 text-center" 
                                                    @click.prevent.stop="$dispatch('open-move-modal', {
                                                        timerId: {{$task->id}}, 
                                                        taskId: {{$task->task_id}}, 
                                                        projectId: {{$task_project_id}}, 
                                                        employeeId: {{$task->employee_id}}, 
                                                        startDate: '{{\Carbon\Carbon::parse($task->start_date)->format('Y-m-d\TH:i')}}', 
                                                        endDate: '{{\Carbon\Carbon::parse($task->end_date)->format('Y-m-d\TH:i')}}', 
                                                        duration: {{$task->duration}}
                                                    })">
                                                    Move Duration
                                                </button>
                                                @endif
                                            </div>
                                            <div class="flex flex-col items-center gap-2">
                                                @if(Auth::user()->hasRole('Admin'))
                                                    <form action="{{ route('admin.tasks.timer.destroy', $task->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this timer entry?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="inline-flex justify-center w-full text-white bg-red-600 hover:bg-red-700 font-medium rounded text-xs px-2 py-1 text-center">
                                                            Delete
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                                
                                <tr class="bg-gray-50 font-bold">
                                    <td colspan="5" class="px-4 py-3 text-sm text-gray-900 text-right">Date Total:</td>
                                    <td class="px-4 py-3 text-sm text-gray-900 text-left">{{ formatDuration($groupTotalDuration) }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($groupTotal, 3, ',', '') }}€</td>
                                    <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($groupTotalRecette, 3, ',', '') }}€</td>
                                    <td colspan="2"></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            
        </main>
    </div>

    {{-- 
    =============================================
    ALPINE.JS MODALS (Replacing Bootstrap)
    =============================================
    --}}

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
                    <input type="text" x-model="duration" id="moveDuration" @input="validateDurationFormat()"
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
    



    {{-- SCRIPTS --}}
    @include('dashboard_layouts.scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.3.1/jspdf.umd.min.js"></script>
    <script src="https://unpkg.com/jspdf-autotable@3.5.17/dist/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.4/xlsx.full.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    {{-- Main Page Logic --}}
    <script>
        // --- PAGE SCRIPT LOGIC ---
        document.addEventListener('DOMContentLoaded', function() {
            
            // --- Ensure all links in the table work correctly ---
            // This is a safety net to catch any invalid navigation attempts
            document.addEventListener('click', function(e) {
                const link = e.target.closest('a[href]');
                if (link && link.closest('#taskTable')) {
                    const href = link.getAttribute('href');
                    // If href is invalid or contains [object, prevent navigation
                    if (!href || href === '#' || href.includes('[object')) {
                        e.preventDefault();
                        e.stopImmediatePropagation();
                        console.error('Blocked invalid navigation attempt. Link:', link, 'href:', href);
                        // Try to get a valid href from the link's text or data attributes
                        const validHref = link.href || link.getAttribute('data-href');
                        if (validHref && !validHref.includes('[object')) {
                            window.location.href = validHref;
                        }
                        return false;
                    }
                }
            }, true); // Use capture phase to catch early
            
            // --- NO AUTO-SUBMIT - Users must click "Apply Filters" button to submit ---
            // Handle "All Tasks" option - if "all" is selected, deselect all other tasks
            const taskFilter = document.getElementById('taskFilter');
            if (taskFilter) {
                taskFilter.addEventListener('change', function(e) {
                    const selectedOptions = Array.from(this.selectedOptions);
                    const hasAllOption = selectedOptions.some(opt => opt.value === 'all');
                    
                    if (hasAllOption) {
                        // If "All Tasks" is selected, deselect everything else
                        Array.from(this.options).forEach(option => {
                            if (option.value !== 'all') {
                                option.selected = false;
                            }
                        });
                    } else {
                        // If a specific task is selected, deselect "All Tasks"
                        const allOption = Array.from(this.options).find(opt => opt.value === 'all');
                        if (allOption) {
                            allOption.selected = false;
                        }
                    }
                });
            }

            // --- Comment editing ---
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

            // Employees Daily Totals PDF Button
            var btn = document.getElementById('employeesDailyTotalsBtn');
            if (btn) {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    var startDate = document.getElementById('startDateInput').value;
                    var endDate = document.getElementById('endDateInput').value;
                    if(!startDate || !endDate){ alert('Please pick start and end dates first.'); return; }
                    var url = "{{ route('admin.Pointagerepports.employeesDailyTotals', ['startDate' => 'START', 'endDate' => 'END']) }}";
                    url = url.replace('START', startDate).replace('END', endDate);
                    window.open(url, '_blank');
                });
            }
            
            // Excel Export Button
            document.getElementById('exportBtn').addEventListener('click', function() {
                var table = document.getElementById('taskTable');
                var wb = XLSX.utils.table_to_book(table, { sheet: 'Sheet1' });
                var startDate = document.getElementById('startDateInput').value;
                var endDate = document.getElementById('endDateInput').value;
                var fileName = `${startDate || 'Report'}_to_${endDate || 'Today'}_TasksInfo.xlsx`;
                XLSX.writeFile(wb, fileName);
            });

            // Date Form Local Storage
            // Check URL parameters first, then localStorage
            const urlParams = new URLSearchParams(window.location.search);
            var startDate = urlParams.get('start_date') || localStorage.getItem('startDate');
            var endDate = urlParams.get('end_date') || localStorage.getItem('endDate');
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
            
            // Filter Form Local Storage
            document.getElementById('hiddenStartDateInput').value = startDate;
            document.getElementById('hiddenEndDateInput').value = endDate;

            document.getElementById('filterForm').addEventListener('submit', function(event) {
                localStorage.setItem('employeeFilter', document.getElementById('employeeFilter').value);
                localStorage.setItem('projectFilter', document.getElementById('projectFilter').value);
                // Store multiple selected tasks as JSON array
                const selectedTasks = Array.from(document.getElementById('taskFilter').selectedOptions).map(opt => opt.value);
                localStorage.setItem('taskFilter', JSON.stringify(selectedTasks));
                localStorage.setItem('projectTypeFilter', document.getElementById('projectTypeFilter').value);
            });
            
            function restoreFilterValues() {
                const urlParams = new URLSearchParams(window.location.search);
                const employeeValue = urlParams.get('employee') || localStorage.getItem('employeeFilter');
                const projectValue = urlParams.get('project') || localStorage.getItem('projectFilter');
                
                // Handle multiple task selections from URL params
                const taskParams = urlParams.getAll('task[]');
                let taskValues = taskParams.length > 0 ? taskParams : null;
                if (!taskValues) {
                    const storedTasks = localStorage.getItem('taskFilter');
                    if (storedTasks) {
                        try {
                            taskValues = JSON.parse(storedTasks);
                        } catch (e) {
                            // Fallback for old single value format
                            taskValues = storedTasks ? [storedTasks] : null;
                        }
                    }
                }
                
                const projectTypeValue = urlParams.get('project_type') || localStorage.getItem('projectTypeFilter');

                if (employeeValue) document.getElementById('employeeFilter').value = employeeValue;
                if (projectValue) document.getElementById('projectFilter').value = projectValue;
                
                // Restore multiple task selections
                const taskFilter = document.getElementById('taskFilter');
                if (taskValues && taskValues.length > 0) {
                    Array.from(taskFilter.options).forEach(option => {
                        option.selected = taskValues.includes(option.value);
                    });
                } else {
                    // If no tasks selected, default to "All Tasks"
                    const allOption = Array.from(taskFilter.options).find(opt => opt.value === 'all');
                    if (allOption) {
                        allOption.selected = true;
                    }
                }
                
                if (projectTypeValue) document.getElementById('projectTypeFilter').value = projectTypeValue;
            }
            restoreFilterValues();

            // Total Calculator
            function updateTotals() {
                let totalDuration = 0, totalOperatingCost = 0, totalSales = 0;
                const rows = document.querySelectorAll('#taskTableBody tr[data-task-id]');
                
                rows.forEach(row => {
                    if (row.style.display === 'none') return; 
                    
                    const durationCell = row.cells[5];
                    if (durationCell) {
                        const durationText = durationCell.textContent.trim();
                        if (durationText !== 'Still Working') {
                            const matches = durationText.match(/(\d+)h:(\d+)m:(\d+)s/);
                            if (matches) {
                                totalDuration += (parseInt(matches[1]) * 60) + parseInt(matches[2]);
                            }
                        }
                    }
                    const operatingCostCell = row.cells[6];
                    if (operatingCostCell) {
                        totalOperatingCost += parseFloat(operatingCostCell.textContent.trim().replace('€', '').replace(',', '.')) || 0;
                    }
                    const salesCell = row.cells[7];
                    if (salesCell) {
                        totalSales += parseFloat(salesCell.textContent.trim().replace('€', '').replace(',', '.')) || 0;
                    }
                });
                const hours = Math.floor(totalDuration / 60);
                const minutes = totalDuration % 60;
                document.getElementById('totalDurationLabel').textContent = `${hours}h:${minutes.toString().padStart(2, '0')}m`;
                document.getElementById('totalOperatingCostLabel').textContent = totalOperatingCost.toFixed(2) + '€';
                document.getElementById('totalSalesLabel').textContent = totalSales.toFixed(2) + '€';
            }
            updateTotals();
            
            // Re-calculate totals after filters are applied
            document.getElementById('filterForm').addEventListener('submit', () => setTimeout(updateTotals, 500));
            document.getElementById('dateForm').addEventListener('submit', () => setTimeout(updateTotals, 500));

            // Dynamic Filter Logic
            async function fetchAndPopulate(url, selectElementId, defaultOptionText, preserveSelections = false) {
                const selectElement = document.getElementById(selectElementId);
                
                // Preserve selected values if needed (for multiple select like taskFilter)
                let selectedValues = [];
                let hadAllOption = false;
                if (preserveSelections && selectElement.multiple) {
                    selectedValues = Array.from(selectElement.selectedOptions).map(opt => opt.value);
                    hadAllOption = selectedValues.includes('all');
                }
                
                // Only add default option if it's not a multiple select
                if (!selectElement.multiple) {
                    selectElement.innerHTML = `<option value="">${defaultOptionText}</option>`;
                } else {
                    selectElement.innerHTML = '';
                    // For taskFilter (multiple select), add "All Tasks" option
                    if (selectElementId === 'taskFilter') {
                        const allOption = new Option('All Tasks', 'all');
                        if (hadAllOption || selectedValues.length === 0) {
                            allOption.selected = true;
                        }
                        selectElement.appendChild(allOption);
                    }
                }
                
                try {
                    const response = await fetch(url);
                    if (!response.ok) {
                        console.error('Failed to fetch data:', response.status, response.statusText);
                        return;
                    }
                    const data = await response.json();
                    if (!Array.isArray(data)) {
                        console.error('Expected array but got:', typeof data, data);
                        return;
                    }
                    data.forEach(item => {
                        if (!item || !item.id) {
                            console.warn('Invalid item in response:', item);
                            return;
                        }
                        const option = new Option(item.name || item.title, item.id);
                        if (item.tva) option.setAttribute('data-tva', item.tva);
                        // Restore selection if it was previously selected
                        if (preserveSelections && selectedValues.includes(item.id.toString())) {
                            option.selected = true;
                        }
                        // Apply green styling for projects with tasks in date range
                        if (selectElementId === 'projectFilter') {
                            applyProjectStyling(option, item.id);
                        }
                        selectElement.appendChild(option);
                    });
                } catch (error) {
                    console.error('Error fetching data:', error);
                }
            }

            const roleRoutes = {
                'Admin': '/admin/filter',
                'admin': '/admin/filter',
                'Responsable de projet': '/Responsable_de_projet/filter',
                'Employee': '/employee/filter',
                'employee': '/employee/filter',
                'Responsable de projet et gestionnaire': '/Responsable_gestionnaire/filter'
            };
            const userRole = "{{ Auth::user()->roles->first()->name }}";
            let baseUrl = roleRoutes[userRole] || roleRoutes[userRole.trim()] || '';
            
            // Fallback: Try to determine baseUrl from current path if not set
            if (!baseUrl) {
                const currentPath = window.location.pathname;
                if (currentPath.includes('/admin/')) {
                    baseUrl = '/admin/filter';
                } else if (currentPath.includes('/Responsable_de_projet/')) {
                    baseUrl = '/Responsable_de_projet/filter';
                } else if (currentPath.includes('/employee/')) {
                    baseUrl = '/employee/filter';
                } else if (currentPath.includes('/Responsable_gestionnaire/')) {
                    baseUrl = '/Responsable_gestionnaire/filter';
                } else {
                    // Default to admin if we can't determine
                    baseUrl = '/admin/filter';
                }
            }
            
            // Debug: Log baseUrl to ensure it's set correctly
            console.log('baseUrl set to:', baseUrl, 'for role:', userRole, 'trimmed:', userRole.trim());
            
            // Store projects with tasks in the selected date range
            const projectsWithTasks = @json(isset($projectsWithTasks) ? $projectsWithTasks : []);

            // Store projects that have an exact period invoice for the selected date range
            const projectsWithExactInvoices = @json(isset($projectsWithExactInvoices) ? $projectsWithExactInvoices : []);
            
            // Function to check if a project has tasks and/or an exact invoice and apply styling / label
            function applyProjectStyling(option, projectId) {
                const id = parseInt(projectId);
                const hasTasks = projectsWithTasks.includes(id);
                const hasExactInvoice = projectsWithExactInvoices.includes(id);

                // Reset any previous inline styles / classes we may have applied
                option.classList.remove('has-tasks');
                option.style.color = '';
                option.style.fontWeight = '';

                // If there is an exact invoice for this project and range, append label and do NOT make it green
                if (hasExactInvoice) {
                    if (!option.text.includes('(INVOICED)')) {
                        option.text = option.text + ' (INVOICED)';
                    }
                    return;
                }

                // If project has tasks in range but no exact invoice yet, mark it in green
                if (hasTasks) {
                    option.classList.add('has-tasks');
                    option.style.color = '#16a34a'; // green-600
                    option.style.fontWeight = '500';
                }
            }
            
            // Apply styling to existing project options on page load
            (function() {
                const projectFilter = document.getElementById('projectFilter');
                if (projectFilter) {
                    Array.from(projectFilter.options).forEach(option => {
                        if (option.value) {
                            applyProjectStyling(option, option.value);
                        }
                    });
                }
            })();

            document.getElementById('clientFilter').addEventListener('change', async () => {
                const clientId = document.getElementById('clientFilter').value;
                const url = clientId ? `${baseUrl}/clients/${clientId}/projects` : `${baseUrl}/all-projects?include_tva=true`;
                await fetchAndPopulate(url, 'projectFilter', 'All Projects');
                // Reset task filter when client changes
                const taskFilter = document.getElementById('taskFilter');
                taskFilter.innerHTML = '<option value="all" selected>All Tasks</option>';
            });

            document.getElementById('employeeFilter').addEventListener('change', async () => {
                const employeeId = document.getElementById('employeeFilter').value;
                if (employeeId) {
                    await fetchAndPopulate(`${baseUrl}/employees/${employeeId}/projects?include_tva=true`, 'projectFilter', 'All Projects');
                    await fetchAndPopulate(`${baseUrl}/employees/${employeeId}/tasks`, 'taskFilter', 'All Tasks', true);
                } else {
                    await fetchAndPopulate(`${baseUrl}/all-projects?include_tva=true`, 'projectFilter', 'All Projects');
                    await fetchAndPopulate(`${baseUrl}/all-tasks`, 'taskFilter', 'All Tasks', true);
                }
            });
            
            // Update tasks when project is selected (without submitting form)
            document.getElementById('projectFilter').addEventListener('change', async () => {
                const projectId = document.getElementById('projectFilter').value;
                if (!projectId) {
                    // If no project selected, load all tasks
                    const url = baseUrl ? `${baseUrl}/all-tasks` : '/admin/filter/all-tasks';
                    await fetchAndPopulate(url, 'taskFilter', 'All Tasks', false);
                    return;
                }
                
                // Construct the correct URL with baseUrl
                const url = baseUrl ? `${baseUrl}/projects/${projectId}/tasks` : `/admin/filter/projects/${projectId}/tasks`;
                console.log('Fetching tasks from URL:', url, 'baseUrl:', baseUrl); // Debug log
                
                if (!baseUrl) {
                    console.error('baseUrl is empty! Using fallback URL.');
                }
                
                // Don't preserve selections when project changes - reset to show all tasks for the new project
                await fetchAndPopulate(url, 'taskFilter', 'All Tasks', false);
            });
            
            // Filter tasks on page load if a project is selected
            // This ensures tasks are filtered by project even on initial page load
            (async () => {
                // Wait a bit to ensure DOM is fully rendered
                await new Promise(resolve => setTimeout(resolve, 50));
                
                const initialProjectId = document.getElementById('projectFilter')?.value;
                const taskFilter = document.getElementById('taskFilter');
                
                if (!taskFilter) return;
                
                // Count actual task options (excluding "all" option)
                const taskOptionsCount = Array.from(taskFilter.options).filter(opt => opt.value !== '' && opt.value !== 'all').length;
                
                // If a project is selected, always fetch tasks for that project to ensure they're filtered
                // This handles both initial page load and after form submission
                if (initialProjectId) {
                    const url = baseUrl ? `${baseUrl}/projects/${initialProjectId}/tasks` : `/admin/filter/projects/${initialProjectId}/tasks`;
                    console.log('Initial load - fetching tasks from URL:', url, 'baseUrl:', baseUrl); // Debug log
                    await fetchAndPopulate(url, 'taskFilter', 'All Tasks', true);
                    
                    // Restore selected tasks from URL params
                    restoreFilterValues();
                } else if (taskOptionsCount === 0) {
                    // If no project is selected and no tasks are shown, load all tasks
                    const url = `${baseUrl}/all-tasks`;
                    await fetchAndPopulate(url, 'taskFilter', 'All Tasks', true);
                    restoreFilterValues();
                }
            })();
            
            // Logic for Generate Period Invoice Button
            document.getElementById('generatePeriodInvoiceBtn').addEventListener('click', function(e) {
                e.preventDefault();
                var projectId = $('#projectFilter').val();
                var startDate = $('#startDateInput').val();
                var endDate = $('#endDateInput').val();
                var comment = encodeURIComponent($('#periodInvoiceComment').val());
                // Get multiple selected tasks (excluding "all" option)
                var selectedTasks = Array.from(document.getElementById('taskFilter').selectedOptions)
                    .map(option => option.value)
                    .filter(taskId => taskId !== 'all');

                if (!projectId) { alert('Please select a project from the filter dropdown.'); return; }
                if (!startDate || !endDate) { alert('Please enter both start and end dates in the date filter section.'); return; }

                var downloadUrl = '';
                @if(Auth::user()->hasRole('Responsable de projet')) downloadUrl = "{{ route('Responsable_de_projet.projects.invoicebyduration2', ['id' => ':projectId', 'startdate' => ':startDate', 'enddate' => ':endDate']) }}"; @endif
                @if(Auth::user()->hasRole('Admin')) downloadUrl = "{{ route('admin.projects.invoicebyduration2', ['id' => ':projectId', 'startdate' => ':startDate', 'enddate' => ':endDate']) }}"; @endif
                @if(Auth::user()->hasRole('Employee')) downloadUrl = "{{ route('employee.projects.invoicebyduration2', ['id' => ':projectId', 'startdate' => ':startDate', 'enddate' => ':endDate']) }}"; @endif
                @if(Auth::user()->hasRole('Responsable de projet et gestionnaire')) downloadUrl = "{{ route('Responsable_gestionnaire.projects.invoicebyduration2', ['id' => ':projectId', 'startdate' => ':startDate', 'enddate' => ':endDate']) }}"; @endif

                downloadUrl = downloadUrl.replace(':projectId', projectId).replace(':startDate', startDate).replace(':endDate', endDate);
                
                var queryParams = [];
                if (comment) {
                    queryParams.push('comment=' + comment);
                }
                // Add multiple tasks to query params (only if specific tasks are selected, not "all")
                if (selectedTasks.length > 0) {
                    selectedTasks.forEach(taskId => {
                        queryParams.push('task[]=' + encodeURIComponent(taskId));
                    });
                }
                
                if (queryParams.length > 0) {
                    downloadUrl += '?' + queryParams.join('&');
                }

                window.location.href = downloadUrl;
            });


            // --- PDF Export Logic (Vanilla JS) ---
    document.getElementById('exportPDFBtn').addEventListener('click', function() {
                var startDate = document.getElementById('startDateInput').value;
                var endDate = document.getElementById('endDateInput').value;
                var employeeId = document.getElementById('employeeFilter').value;
                var projectId = document.getElementById('projectFilter').value;
                var selectedTasks = Array.from(document.getElementById('taskFilter').selectedOptions)
                    .map(option => option.value)
                    .filter(taskId => taskId !== 'all');
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
                url = '/' + rolePrefix + '/Pointagerepports/downloadRepports/' + startDate + '/' + endDate;
            } else {
                url = '/' + rolePrefix + '/Pointagerepports/downloadOneDayRepports/' + startDate;
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
                    .then(response => {
                        const skippedHeader = response.headers.get('X-Skipped-Tasks');

                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }

                        return response.blob().then(blob => ({
                            blob: blob,
                            skippedHeader: skippedHeader
                        }));
                    })
                    .then(({ blob, skippedHeader }) => {
                        var url = window.URL.createObjectURL(blob);
                        var a = document.createElement('a');
                        a.href = url;
                        a.download = fileName;
                        document.body.appendChild(a);
                        a.click();
                        a.remove();
                        window.URL.revokeObjectURL(url);

                        if (skippedHeader) {
                            try {
                                var skippedTasks = JSON.parse(atob(skippedHeader));
                                if (Array.isArray(skippedTasks) && skippedTasks.length > 0) {
                                    showSkippedTasksModal(skippedTasks);
                                }
                            } catch (e) {
                                console.error('Failed to parse skipped tasks header', e);
                            }
                        }
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
                var selectedTasks = Array.from(document.getElementById('taskFilter').selectedOptions)
                    .map(option => option.value)
                    .filter(taskId => taskId !== 'all');
                var clientId = document.getElementById('clientFilter').value;
                var projectName = '';
                var projectSelect = document.getElementById('projectFilter');
                if (projectSelect.selectedIndex > 0) {
                    projectName = projectSelect.options[projectSelect.selectedIndex].text;
                }

                if (startDate) {
            var url;
            if (startDate && endDate) {
                url = '/' + rolePrefix + '/CustomerPointagerepports/preview/' + startDate + '/' + endDate;
            } else {
                url = '/' + rolePrefix + '/CustomerPointagerepports/previewOneDay/' + startDate;
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

            // --- Skipped tasks modal helpers ---
            window.showSkippedTasksModal = function(skippedTasks) {
                var modal = document.getElementById('skippedTasksModal');
                var tbody = document.getElementById('skippedTasksTableBody');

                if (!modal || !tbody) {
                    console.error('Skipped tasks modal elements not found');
                    return;
                }

                // Clear previous rows
                tbody.innerHTML = '';

                skippedTasks.forEach(function(task) {
                    var tr = document.createElement('tr');

                    function createCell(text) {
                        var td = document.createElement('td');
                        td.className = 'px-3 py-2 text-sm text-gray-700 border-b border-gray-200';
                        td.textContent = text || '';
                        return td;
                    }

                    // Format duration (minutes -> HH:MM)
                    var durationText = '';
                    var durationMinutes = null;

                    if (typeof task.duration === 'number') {
                        durationMinutes = task.duration;
                    } else if (typeof task.duration === 'string' && task.duration.trim() !== '') {
                        var parsed = parseInt(task.duration, 10);
                        if (!isNaN(parsed)) {
                            durationMinutes = parsed;
                        }
                    }

                    if (durationMinutes !== null) {
                        var hours = Math.floor(durationMinutes / 60);
                        var minutes = durationMinutes % 60;
                        durationText = (hours < 10 ? '0' + hours : hours) + 'h:' +
                                       (minutes < 10 ? '0' + minutes : minutes) + 'm';
                    }

                    tr.appendChild(createCell(task.task_name));
                    tr.appendChild(createCell(task.project_name));
                    tr.appendChild(createCell(task.employee_name));
                    tr.appendChild(createCell(task.start_date));
                    tr.appendChild(createCell(task.end_date));
                    tr.appendChild(createCell(durationText));

                    tbody.appendChild(tr);
                });

                modal.classList.remove('hidden');
            };

            window.closeSkippedTasksModal = function() {
                var modal = document.getElementById('skippedTasksModal');
                if (modal) {
                    modal.classList.add('hidden');
                }
            };

        });
    </script>

    <!-- Skipped tasks modal -->
    <div id="skippedTasksModal" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full mx-4">
            <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">
                    Tasks not included in the PDF
                </h3>
                <button type="button"
                        onclick="closeSkippedTasksModal()"
                        class="text-gray-400 hover:text-gray-600 focus:outline-none">
                    <span class="sr-only">Close</span>
                    &times;
                </button>
            </div>
            <div class="px-4 py-3 text-sm text-gray-700 border-b border-gray-100">
                Some tasks were skipped because their data is incomplete or inconsistent
                (for example: missing project, missing hourly rate, or missing links).
                You can review them below and fix them later; the rest of the PDF has been generated correctly.
            </div>
            <div class="px-4 py-3 max-h-96 overflow-y-auto">
                <table class="min-w-full border border-gray-200 rounded-lg overflow-hidden">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider border-b border-gray-200">
                                Task
                            </th>
                            <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider border-b border-gray-200">
                                Project
                            </th>
                            <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider border-b border-gray-200">
                                Employee
                            </th>
                            <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider border-b border-gray-200">
                                Start date
                            </th>
                            <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider border-b border-gray-200">
                                End date
                            </th>
                            <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider border-b border-gray-200">
                                Duration
                            </th>
                        </tr>
                    </thead>
                    <tbody id="skippedTasksTableBody" class="bg-white">
                        <!-- Rows injected dynamically -->
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 flex justify-end border-t border-gray-200">
                <button type="button"
                        onclick="closeSkippedTasksModal()"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    Close
                </button>
            </div>
        </div>
    </div>
</body>
</html>
