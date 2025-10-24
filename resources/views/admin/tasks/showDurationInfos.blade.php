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

                <div class="col-12 col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                @if(session('alertmessage'))
                                <div class="alert alert-danger alert-dismissible fade show" role="alert"
                                    style="color: white;">
                                    {{ session('alertmessage') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"
                                        aria-label="Close">X</button>
                                </div>
                                @endif
                                <table class="table align-items-center mb-0">
                                    <thead>
                                        <tr>
                                            <th class="text-uppercase text-dark text-x font-weight-bolder opacity-10">
                                                Task
                                            </th>
                                            <th
                                                class="text-uppercase text-dark text-x font-weight-bolder opacity-10 ps-2">
                                                Employee Name
                                            </th>
                                            <th
                                                class="text-uppercase text-dark text-x font-weight-bolder opacity-10 ps-2">
                                                Start Date
                                            </th>
                                            <th
                                                class="text-uppercase text-dark text-x font-weight-bolder opacity-10 ps-2">
                                                End Date
                                            </th>
                                            <th
                                                class="text-uppercase text-dark text-x font-weight-bolder opacity-10 ps-2">
                                                Duration
                                            </th>
                                            <th
                                                class="text-uppercase text-dark text-x font-weight-bolder opacity-10 ps-2">
                                                Move Duration
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php 
                                            $total_duration = 0;
                                            $current_date = null;
                                            $daily_duration = 0;
                                            
                                            // Group tasks by date
                                            $grouped_tasks = $tasks->groupBy(function($task) {
                                                return \Carbon\Carbon::parse($task->start_date)->format('Y-m-d');
                                            })->sortKeys();
                                        @endphp
                                        
                                        @foreach($grouped_tasks as $date => $day_tasks)
                                            @php
                                                $daily_duration = 0;
                                                $formatted_date = \Carbon\Carbon::parse($date)->format('d-m-Y');
                                            @endphp
                                            
                                            <tr>
                                                <td colspan="6" class="bg-light">
                                                    <h6 class="mb-0 text-s"><b>{{ \Carbon\Carbon::parse($date)->format('l') }} : {{ $formatted_date }}</b></h6>
                                                </td>
                                            </tr>
                                            
                                            @foreach($day_tasks as $task)
                                                <tr>
                                                    <td>
                                                        <div class="d-flex px-2">
                                                            <div class="my-auto">
                                                                <h6 class="mb-0 text-s"><b>{{$task->task_name}}</b></h6>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <p class="text-s font-weight-bold mb-0">
                                                            @php
                                                                $employee_name = App\Models\Employees::where('id', $task->employee_id)->pluck('name');
                                                            @endphp
                                                            {{$employee_name[0]}}
                                                        </p>
                                                    </td>
                                                    <td class="clickable" data-field="start_date" data-task-id="{{ $task->id }}" style="cursor: pointer; color: blue;">
                                                        {{ \Carbon\Carbon::parse($task->start_date)->format('d-m-Y H:i:s') }}
                                                    </td>
                                                    <td class="clickable" data-field="end_date" data-task-id="{{ $task->id }}" style="cursor: pointer; color: blue;">
                                                        {{ $task->end_date ? \Carbon\Carbon::parse($task->end_date)->format('d-m-Y H:i:s') : 'Still Working' }}
                                                    </td>
                                                    <td id="duration-{{ $task->id }}" class="duration clickable" data-task-id="{{ $task->id }}">
                                                        
                                                            @php
                                                                $tasktotalduration = $task->duration;
                                                                $taskhours = floor($tasktotalduration / 60);
                                                                $taskminutes = $tasktotalduration % 60;
                                                                $total_duration += $tasktotalduration;
                                                                $daily_duration += $tasktotalduration;
                                                            @endphp
                                                            <span class="badge badge-dot me-4">
                                                                <i class="bg-info"></i>
                                                                <span class="duration text-dark text-sm" style="cursor: pointer; color: blue !important;font-size: 15px !important; font-weight: bold !important;">
                                                                    {{$taskhours}}h:{{$taskminutes}}m      
                                                                </span>
                                                            </span>
                                                        
                                                    </td>
                                                    <td>
                                                        <button class="btn btn-sm btn-info ms-2 move-duration-btn p-2 m-2" 
                                                            data-task-id="{{$task->id}}" 
                                                            data-project-id="{{$task->project_id}}" 
                                                            data-employee-id="{{$task->employee_id}}"
                                                            data-start-date="{{$task->start_date}}"
                                                            data-end-date="{{$task->end_date}}"
                                                            data-duration="{{$task->duration}}">
                                                            Move Duration
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                            
                                            <tr>
                                                <td colspan="4" class="text-end">
                                                    <p class="text-sm font-weight-bold mb-0">
                                                        <b>Daily Total</b>
                                                    </p>
                                                </td>
                                                <td>
                                                    @php
                                                        $dailyhours = floor($daily_duration / 60);
                                                        $dailyminutes = $daily_duration % 60;
                                                    @endphp
                                                    <span class="duration text-dark" style="margin-left: 15px;">
                                                        <strong>{{$dailyhours}}h:{{$dailyminutes}}m</strong>      
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                        
                                        <tr>
                                            <td colspan="4" class="text-end">
                                                <p class="text-s font-weight-bold mb-0">
                                                    <b>Grand Total</b>
                                                </p>
                                            </td>
                                            <td>
                                                @php
                                                    $totalhours = floor($total_duration / 60);
                                                    $totalminutes = $total_duration % 60;
                                                @endphp
                                                <span class="duration text-dark text-s">
                                                    <strong>{{$totalhours}}h:{{$totalminutes}}m</strong>      
                                                </span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    

                </div>

            </div>
            
            






    </main>


    @include('dashboard_layouts.scripts')

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.0/xlsx.full.min.js"></script>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>


    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

    


    <script type="text/javascript">
    $(document).ready(function() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        }); 
    });
    </script>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

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

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle clicks on date/time cells
        const cells = document.querySelectorAll('.clickable');
        cells.forEach(cell => {
            cell.addEventListener('click', function() {
                const field = this.getAttribute('data-field');
                const taskId = this.getAttribute('data-task-id');
                const currentValue = this.textContent.trim();
                
                // Check if this is a duration cell
                if (this.classList.contains('duration')) {
                    // Handle duration editing
                    var newValue = prompt("Enter new duration for task");
                    if (newValue) {
                        var updatedurl;
                        @if(Auth::user()->hasRole('Admin'))
                            updatedurl = '/admin/tasks/update-duration/' + taskId;
                        @endif
                        @if(Auth::user()->hasRole('Responsable de projet'))
                            updatedurl = '/Responsable_de_projet/tasks/update-duration/' + taskId;
                        @endif
                        @if(Auth::user()->hasRole('Employee'))
                            updatedurl = '/employee/tasks/update-duration/' + taskId;
                        @endif
                        @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                            updatedurl = '/Responsable_gestionnaire/tasks/update-duration/' + taskId;
                        @endif

                        $.ajax({
                            url: updatedurl,
                            type: 'POST',
                            data: {
                                duration: newValue,
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                if (response.status == 'success') {
                                    location.reload();
                                } else {
                                    location.reload();
                                }
                            }
                        });
                    }
                } else {
                    // Handle date/time editing
                    if (currentValue !== 'Still Working') {
                        const [datePart, timePart] = currentValue.split(' ');
                        const [day, month, year] = datePart.split('-');
                        const formattedDate = `${year}-${month}-${day}`;
                        const formattedTime = timePart.substring(0, 5);

                        document.getElementById('dateInput').value = formattedDate;
                        document.getElementById('timeInput').value = formattedTime;
                        document.getElementById('taskId').value = taskId;
                        document.getElementById('fieldName').value = field;

                        $('#editDateModal').modal('show');
                    }
                }
            });
        });

        // Handle save changes button in modal
        document.getElementById('saveChangesBtn').addEventListener('click', function() {
            const taskId = document.getElementById('taskId').value;
            const field = document.getElementById('fieldName').value;
            const date = document.getElementById('dateInput').value;
            const time = document.getElementById('timeInput').value;
            const newValue = `${date} ${time}`;

            var updateUrl;
            @if(Auth::user()->hasRole('Admin'))
                updateUrl = `/admin/tasks/update-${field}/${taskId}`;
            @endif
            @if(Auth::user()->hasRole('Responsable de projet'))
                updateUrl = `/Responsable_de_projet/tasks/update-${field}/${taskId}`;
            @endif
            @if(Auth::user()->hasRole('Employee'))
                updateUrl = `/employee/tasks/update-${field}/${taskId}`;
            @endif
            @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                updateUrl = `/Responsable_gestionnaire/tasks/update-${field}/${taskId}`;
            @endif

            $.ajax({
                url: updateUrl,
                type: 'POST',
                data: {
                    [field]: newValue,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.status === 'success') {
                        location.reload();
                    } else {
                        alert('Failed to update the value.');
                    }
                }
            });

            $('#editDateModal').modal('hide');
        });
    });
    </script>

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