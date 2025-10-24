<!DOCTYPE html>
<html lang="en">

@include('dashboard_layouts.head')
<style>
  a {
    color: blue ;
}
th {
    font-size: 14px !important; 
}
.text-sm{
    color: #332f2f;
}
</style>
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

                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <h6>Tasks Table</h6>
                            <div>
                            @if(Auth::user()->hasRole('Responsable de projet'))
                                <a type="button" class="btn btn-primary"
                                href="{{route('Responsable_de_projet.Tasksrepports.downloadRepports')}}">Tasks Reports PDF</a>
                            @endif
                            @if(Auth::user()->hasRole('Admin'))
                                <a type="button" class="btn btn-primary"
                                href="{{route('admin.Tasksrepports.downloadRepports')}}">Tasks Reports PDF</a>
                            @endif
                            @if(Auth::user()->hasRole('Employee'))
                                <a type="button" class="btn btn-primary"
                                href="{{route('employee.Tasksrepports.downloadRepports')}}">Tasks Reports PDF</a>
                            @endif
                            @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                <a type="button" class="btn btn-primary"
                                href="{{route('Responsable_gestionnaire.Tasksrepports.downloadRepports')}}">Tasks Reports PDF</a>
                            @endif
                               
                            </div>
                        </div>
                        <div class="d-flex justify-content-between mt-3 mb-4">
                            <div>
                                <label for="">
                                    Filter by Name
                                </label>
                                <select id="taskFilter" class="form-select">
                                    <option value="">All Tasks</option>
                                    @foreach($tasks as $task)
                                        <option value="{{ $task->title }}">{{ $task->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="taskSearch">
                                    Search Task
                                </label>
                                <input type="text" id="taskSearch" class="form-control" placeholder="Type to search...">
                            </div>
                        </div>

                        @if(session('message'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('message') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"
                                aria-label="Close">X</button>
                        </div>
                        @endif
                        @if(session('alertmessage'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert" style="color: white;">
                            {{ session('alertmessage') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"
                                aria-label="Close">X</button>
                        </div>
                        @endif

                        <div class="table-responsive">
                            <table class="table align-items-center mb-0" id="tasksTable">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-dark text-x font-weight-bolder opacity-10">
                                            Project / Task
                                        </th>
                                        <th class="text-uppercase text-dark text-x font-weight-bolder opacity-10 ps-2">
                                            Teams
                                        </th>
                                        <th class="text-uppercase text-dark text-x font-weight-bolder opacity-10 ps-2">
                                            Employees
                                        </th>
                                        <th class="text-uppercase text-dark text-x font-weight-bolder opacity-10 ps-2">
                                            Duration
                                        </th>
                                        <th class="text-uppercase text-dark text-x font-weight-bolder opacity-10 ps-2">
                                            Estimated Hours
                                        </th>
                                        <th class="text-uppercase text-dark text-x font-weight-bolder opacity-10 ps-2">
                                            Difference
                                        </th>
                                        <th class="text-uppercase text-dark text-x font-weight-bolder opacity-10 ps-2">
                                            Progress
                                        </th>
                                        <th class="text-uppercase text-dark text-x font-weight-bolder opacity-10 ps-2">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $groupedTasks = $tasks->groupBy('project_id');
                                    @endphp
                                    
                                    @foreach($groupedTasks as $projectId => $projectTasks)
                                        @php
                                            $firstTask = $projectTasks->first();
                                        @endphp
                                        <tr class="table-secondary">
                                            <td colspan="8" style="background-color: lightgray !important;">
                                                <h6 class="mb-0">
                                                    @if(Auth::user()->hasRole('Admin'))
                                                    <a href="{{route('admin.projects.show', $projectId)}}" class="text-primary">
                                                        <strong style="color: black !important;">{{$firstTask->project_name}}</strong>
                                                    </a>
                                                    @endif
                                                    @if(Auth::user()->hasRole('Responsable de projet'))
                                                    <a href="{{route('Responsable_de_projet.projects.show', $projectId)}}" class="text-primary">
                                                        <strong style="color: black !important;">{{$firstTask->project_name}}</strong>
                                                    </a>
                                                    @endif
                                                    @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                                    <a href="{{route('Responsable_gestionnaire.projects.show', $projectId)}}" class="text-primary">
                                                        <strong style="color: black !important;">{{$firstTask->project_name}}</strong>
                                                    </a>
                                                    @endif
                                                    @if(Auth::user()->hasRole('Employee'))
                                                    <a href="{{route('employee.projects.show', $projectId)}}" class="text-primary">
                                                        <strong style="color: black !important;">{{$firstTask->project_name}}</strong>
                                                    </a>
                                                    @endif
                                                </h6>
                                            </td>
                                        </tr>
                                        @foreach($projectTasks as $task)
                                            <tr>
                                                <td class="ps-4">
                                                    <div class="d-flex px-2">
                                                        @if(auth()->user()->hasRole('Admin'))
                                                        <a href="{{route('admin.tasks.show', $task->id)}}">
                                                        @endif
                                                        @if(auth()->user()->hasRole('Responsable de projet'))
                                                        <a href="{{route('Responsable_de_projet.tasks.show', $task->id)}}">
                                                        @endif
                                                            <div class="my-auto">
                                                                <h6 class="mb-0 text-sm">
                                                                    <b style="color: blue;">{{$task->title}}</b>                                                    
                                                                </h6>
                                                            </div>
                                                        </a>
                                                    </div>
                                                </td>
                                                
                                                <td>
                                                    @foreach(App\Models\team_tasks::where('task_id', $task->id)->get() as $team_task)
                                                        @php
                                                            $team = App\Models\Teams::find($team_task->team_id);
                                                        @endphp
                                                        @if(count(App\Models\team_tasks::where('task_id', $task->id)->get()) > 0)
                                                            <span class="badge badge-dot me-4">
                                                                <i class="bg-info"></i>
                                                                @if(Auth::user()->hasRole('Admin'))
                                                                    @if(Auth::user()->can('View team'))
                                                                    <a href="{{route('admin.teams.index')}}">
                                                                        <span class="text-dark text-sm">{{$team->name}}</span>
                                                                    </a>
                                                                    @else
                                                                        <span class="text-dark text-sm">{{$team->name}}</span>
                                                                    @endif
                                                                @endif
                                                                @if(Auth::user()->hasRole('Responsable de projet'))
                                                                    @if(Auth::user()->can('View team'))
                                                                    <a href="{{route('Responsable_de_projet.teams.index')}}">
                                                                        <span class="text-dark text-sm">{{$team->name}}</span>
                                                                    </a>
                                                                    @else
                                                                        <span class="text-dark text-sm">{{$team->name}}</span>
                                                                    @endif
                                                                @endif
                                                                @if(Auth::user()->hasRole('Employee'))
                                                                    @if(Auth::user()->can('View team'))
                                                                    <a href="{{route('employee.teams.index')}}">
                                                                        <span class="text-dark text-sm">{{$team->name}}</span>
                                                                    </a>
                                                                    @else
                                                                        <span class="text-dark text-sm">{{$team->name}}</span>
                                                                    @endif
                                                                @endif
                                                                @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                                                    @if(Auth::user()->can('View team'))
                                                                    <a href="{{route('Responsable_gestionnaire.teams.index')}}">
                                                                        <span class="text-dark text-sm">{{$team->name}}</span>
                                                                    </a>
                                                                    @else
                                                                        <span class="text-dark text-sm">{{$team->name}}</span>
                                                                    @endif
                                                                @endif
                                                            </span>
                                                            <br>
                                                        @else
                                                            <span class="badge badge-dot me-4">
                                                                <i class="bg-info"></i>
                                                            <span class="text-dark text-sm">No team</span>
                                                        @endif
                                                    @endforeach
                                                </td>
                                                <td>
                                                    @php
                                                        $employee_id = App\Models\employees_tasks::where('task_id',$task->id)->pluck('employee_id');
                                                    @endphp
                                                    @foreach(App\Models\Employees::whereIn('id', $employee_id)->get() as $employee)
                                                        <span class="badge badge-dot me-4">
                                                            <i class="bg-info"></i>
                                                            @if(Auth::user()->hasRole('Admin'))
                                                                @if(Auth::user()->can('View employee'))
                                                                <a href="{{route('admin.employees.show',$employee->id )}}">
                                                                    <span class="text-dark text-sm">{{$employee->name}}</span>
                                                                </a>
                                                                @else
                                                                    <span class="text-dark text-sm">{{$employee->name}}</span>
                                                                @endif
                                                            @endif
                                                            @if(Auth::user()->hasRole('Responsable de projet'))
                                                                @if(Auth::user()->can('View employee'))
                                                                <a href="{{route('Responsable_de_projet.employees.show',$employee->id )}}">
                                                                    <span class="text-dark text-sm">{{$employee->name}}</span>
                                                                </a>
                                                                @else
                                                                    <span class="text-dark text-sm">{{$employee->name}}</span>
                                                                @endif
                                                            @endif
                                                            @if(Auth::user()->hasRole('Employee'))
                                                                @if(Auth::user()->can('View employee'))
                                                                <a href="{{route('employee.employees.show',$employee->id )}}">
                                                                    <span class="text-dark text-sm">{{$employee->name}}</span>
                                                                </a>
                                                                @else
                                                                    <span class="text-dark text-sm">{{$employee->name}}</span>
                                                                @endif
                                                            @endif
                                                            @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                                                @if(Auth::user()->can('View employee'))
                                                                <a href="{{route('Responsable_gestionnaire.employees.show',$employee->id )}}">
                                                                    <span class="text-dark text-sm">{{$employee->name}}</span>
                                                                </a>
                                                                @else
                                                                    <span class="text-dark text-sm">{{$employee->name}}</span>
                                                                @endif
                                                            @endif
                                                        </span>
                                                        <br>
                                                    @endforeach
                                                </td>

                                                <td>
                                                    <p class="text-sm font-weight-bold mb-0">
                                                    @php 
                                                        $tasktotalduration = App\Models\tasks_timer::where('task_id', $task->id)->sum('duration');
                                                        $taskhours = floor($tasktotalduration / 60);
                                                        $taskminutes = $tasktotalduration % 60;
                                                    @endphp
                                                    {{$taskhours}}h:{{$taskminutes}}m
                                                    </p>
                                                </td>
                                                <td>
                                                    <p class="text-sm font-weight-bold mb-0">
                                                        {{$task->estimated_hours}}h
                                                    </p>
                                                </td>
                                                <td>
                                                    <p class="text-sm font-weight-bold ">
                                                    @php
                                                        $taskdifference = $task->estimated_hours * 60 - $tasktotalduration; // Convert estimated hours to minutes and subtract total duration
                                                        $hours = floor(abs($taskdifference) / 60);
                                                        $minutes = abs($taskdifference) % 60;

                                                        $taskestimatedMinutes = $task->estimated_hours * 60;
                                                        $taskprogressPercentage = $taskestimatedMinutes > 0 ? ($tasktotalduration / $taskestimatedMinutes) * 100 : 0;
                                                        $taskformattedProgressPercentage = number_format($taskprogressPercentage, 2);
                                                    @endphp
                                                    @if($task->estimated_hours !== null)
                                                       

                                                        @if($taskdifference > 0)
                                                        <p class="text-sm font-weight-bold mb-0 text-success">
                                                        +{{$hours}}h:{{$minutes}}m</p>
                                                        @elseif($taskdifference < 0)
                                                            @php
                                                                $hours = floor(abs($taskdifference) / 60);
                                                                $minutes = abs($taskdifference) % 60;
                                                            @endphp
                                                            <p class="text-sm font-weight-bold mb-0 text-danger">
                                                            -{{$hours}}h:{{$minutes}}m</p>
                                                        @else
                                                            <span style="color: blue;">{{$hours}}h:{{$minutes}}m</span>
                                                        @endif
                                                    @endif
                                                    </p>
                                                </td>
                                                <td class="" data-id="" data-column="">
                                                    <div class="progress" style="height: 12px; background-color: #e9ecef; border-radius: 0.25rem;">
                                                        <div class="progress-bar 
                                                            @if($taskprogressPercentage < 50) bg-success 
                                                            @elseif($taskprogressPercentage < 75) bg-warning 
                                                            @else bg-danger 
                                                            @endif" 
                                                            role="progressbar" 
                                                            style="width: {{$taskformattedProgressPercentage}}%; font-size: 10px; display: flex; align-items: center; justify-content: center; overflow: hidden; color: #fff; text-align: center; white-space: nowrap; transition: width 0.6s ease;" 
                                                            aria-valuenow="{{$taskformattedProgressPercentage}}" 
                                                            aria-valuemin="0" 
                                                            aria-valuemax="100">
                                                            {{$taskformattedProgressPercentage}}%
                                                        </div>
                                                    </div>
                                                </td>

                                                <td>
                                                @if(Auth::user()->hasRole('Responsable de projet'))
                                                    @can('Update task')
                                                    <a href="{{ route('Responsable_de_projet.tasks.edit', $task->id) }}" class="btn btn-sm btn-primary p-2 m-2">Edit</a>
                                                    @endcan
                                                @endif
                                                @if(Auth::user()->hasRole('Employee'))
                                                    @can('Update task')
                                                    <a href="{{ route('employee.tasks.edit', $task->id) }}" class="btn btn-sm btn-primary p-2 m-2">Edit</a>
                                                    @endcan
                                                @endif
                                                @if(Auth::user()->hasRole('Admin'))
                                                    @can('Update task')
                                                    <a href="{{ route('admin.tasks.edit', $task->id) }}" class="btn btn-sm btn-primary p-2 m-2">Edit</a>
                                                    @endcan
                                                    @can('Delete task')
                                                    <form method="post" action="{{ route('admin.tasks.destroy', $task->id) }}" onsubmit="return confirm('Are you sure?');" style="display: inline;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger p-2 m-2">X</button>
                                                    </form>
                                                    @endcan 
                                                @endif
                                                @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                                    @can('Update task')
                                                    <a href="{{ route('Responsable_gestionnaire.tasks.edit', $task->id) }}" class="btn btn-sm btn-primary p-2 m-2">Edit</a>
                                                    @endcan
                                                @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <br>







    </main>



    @include('dashboard_layouts.scripts')

    <script>
    document.getElementById('taskFilter').addEventListener('change', function() {
        var filter = this.value.toLowerCase();
        var rows = document.querySelectorAll('#tasksTable tbody tr');

        rows.forEach(function(row) {
            var taskName = row.cells[0].textContent.toLowerCase();
            if (filter === "" || taskName.includes(filter)) {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }
        });
    });
</script>
<script>
    document.getElementById('taskSearch').addEventListener('input', function() {
        let filter = this.value.toLowerCase();
        let rows = document.querySelectorAll('#tasksTable tbody tr:not(.bg-light)'); // Exclude project headers

        rows.forEach(row => {
            let taskTitle = row.querySelector('td:first-child').textContent.toLowerCase();
            let projectHeader = row.previousElementSibling;
            
            if (taskTitle.includes(filter)) {
                row.style.display = '';
                if (projectHeader && projectHeader.classList.contains('bg-light')) {
                    projectHeader.style.display = '';
                }
            } else {
                row.style.display = 'none';
            }
        });
        
        // Hide project headers with no visible tasks
        document.querySelectorAll('#tasksTable tbody tr.bg-light').forEach(header => {
            let nextRow = header.nextElementSibling;
            let hasVisibleTasks = false;
            
            while (nextRow && !nextRow.classList.contains('bg-light')) {
                if (nextRow.style.display !== 'none') {
                    hasVisibleTasks = true;
                    break;
                }
                nextRow = nextRow.nextElementSibling;
            }
            
            header.style.display = hasVisibleTasks ? '' : 'none';
        });
    });
</script>

<!-- Check if the timer is running and show an alert if it's not -->
<script>
     document.addEventListener("DOMContentLoaded", function() {
    var modal = document.getElementById("customAlertModal");
    var span = document.getElementsByClassName("close-button")[0];
    var alertSound = document.getElementById("alertSound");
    var alertInterval;
    var checkStateInterval;

    span.onclick = function() {
        modal.style.display = "none";
    }

    function showAlert() {
        modal.style.display = "block";
        alertSound.play();
    }

    function startAlertInterval() {
        clearInterval(alertInterval);
        alertInterval = setInterval(showAlert, 60000); // Show alert every 1 minute
    }

    function clearAlertInterval() {
        clearInterval(alertInterval);
    }

    function checkTimerState(tasksTimer) {
        if (!tasksTimer) {
            // If $tasks_timer doesn't exist, start the interval 
            startAlertInterval();
        } else {
            if (tasksTimer.paused === null || tasksTimer.paused === 0) {
                // If $tasks_timer exists and paused is null or 0, clear the interval
                clearAlertInterval();
            } else if (tasksTimer.paused === 1) {
                // If $tasks_timer exists and paused is 1, start the interval
                startAlertInterval();
            }

            if (tasksTimer.paused_date !== null && tasksTimer.paused === 0) {
                // If paused_date is not null and paused is 0, clear the interval
                clearAlertInterval();
            }
        }
    }

    function fetchTimerState() {
        @if(Auth::user()->hasRole('Responsable de projet'))
            fetch('/Responsable_de_projet/pointage/get-timer-state') // Replace with your actual endpoint
        @endif
        @if(Auth::user()->hasRole('Admin'))
            fetch('/admin/pointage/get-timer-state') // Replace with your actual endpoint 
        @endif
        @if(Auth::user()->hasRole('Employee'))
            fetch('/employee/pointage/get-timer-state') // Replace with your actual endpoint 
        @endif
            .then(response => response.json())
            .then(data => {
                checkTimerState(data.tasksTimer);
            })
            .catch(error => {
                console.error('Error fetching timer state:', error);
            });
    }

    // Initial check
    fetchTimerState();

    // Set up periodic checks every 1 minutes
    checkStateInterval = setInterval(fetchTimerState, 1 * 60 * 1000);
    console.log("timer started");

   
});
    </script>

</body>

</html>