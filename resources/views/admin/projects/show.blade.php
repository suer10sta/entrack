<!DOCTYPE html>
<html lang="en">

@include('dashboard_layouts.head')
<style>
  a {
    color: blue;
}
.font-weight-bolder{
    color : black !important;
}
th {
    font-size: 14px !important; 
}
.text-sm{
    color: #332f2f;
}
/* Add these new styles */
.description-cell {
    max-width: 180px; /* Adjust this value as needed */
    min-width: 180px; /* Added to maintain consistent width */
    white-space: normal !important;
    word-wrap: break-word;
    overflow: hidden; /* Prevent content from spilling */
    padding: 8px; /* Add some padding */
}

.description-cell p {
    margin: 0;
    overflow-wrap: break-word;
    word-break: break-word; /* Allow words to break if needed */
    white-space: pre-wrap !important;
    display: block;
}

/* Task name cell styles */
.task-name-cell {
    max-width: 180px;
    min-width: 180px;
    white-space: normal !important;
    word-wrap: break-word;
    overflow: hidden;
    padding: 8px;
}

.task-name-cell h6 {
    margin: 0;
    overflow-wrap: break-word;
    word-break: break-word;
    white-space: pre-wrap !important;
    display: block;
}

.stop-button{
    margin-left: -8px !important;
}

.edit-btn{
    margin-right: -4px !important;
    margin-left: 45px !important;
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
                                    <table id="editableTable" class="table align-items-center mb-0">
                                        <thead>
                                            <tr>
                                                <th
                                                    class="text-uppercase text-dark text-x font-weight-bolder opacity-10">
                                                    Project
                                                </th>
                                                @if(Auth::user()->hasRole('Admin'))
                                                    <th
                                                        class="text-uppercase text-dark text-x font-weight-bolder opacity-10 ps-2">
                                                        Budget
                                                    </th>
                                                    <th
                                                        class="text-uppercase text-dark text-x font-weight-bolder opacity-10 ps-2">
                                                        Unbilled budget
                                                    </th>
                                                    <th
                                                        class="text-uppercase text-dark text-x font-weight-bolder opacity-10 ps-2">
                                                        Cost Per Hour
                                                    </th>
                                                    <th
                                                        class="text-uppercase text-dark text-x font-weight-bolder opacity-10 ps-2">
                                                        Duration
                                                    </th>
                                                    <th
                                                        class="text-uppercase text-dark text-x font-weight-bolder opacity-10 ps-2">
                                                        estimated work hours
                                                    </th>
                                                    <th
                                                        class="text-uppercase text-dark text-x font-weight-bolder opacity-10 ps-2">
                                                        Difference
                                                    </th>
                                                    <th
                                                        class="text-uppercase text-dark text-x font-weight-bolder opacity-10 ps-2">
                                                        Progress
                                                    </th>
                                                @endif
                                                <th
                                                    class="text-uppercase text-dark text-x font-weight-bolder opacity-10 ps-2">
                                                    TVA
                                                </th>
                                                <th
                                                    class="text-uppercase text-dark text-x font-weight-bolder opacity-10 ps-2">
                                                    Client
                                                </th>
                                                <th
                                                    class="text-uppercase text-dark text-x font-weight-bolder opacity-10 ps-2">
                                                    Teams
                                                </th>
                                                <th
                                                    class="text-uppercase text-dark text-x font-weight-bolder opacity-10 ps-2">
                                                    Action
                                                </th>
                                               
                                            </tr>
                                        </thead>
                                        <tbody>
                                            
                                            <tr >
                                            <td class="editable" data-id="{{$project->id}}" data-column="name">
                                                <div class="d-flex px-2">
                                                        <div class="my-auto">
                                                            <h6 class="mb-0 text-sm"><b>{{strtoupper($project->name)}}</b></h6>
                                                        </div>
                                                    </div> 
                                                </td>
                                                @if(Auth::user()->hasRole('Admin'))
                                                    <td class="editable" data-id="{{$project->id}}" data-column="budget">
                                                        <p class="text-sm font-weight-bold mb-0">€{{ number_format($project->budget, 2, ',', ' ') }}</p>
                                                    </td>
                                                    <td class="editable" data-id="{{$project->id}}" data-column="unbilled_budget">
                                                        <p class="text-sm font-weight-bold mb-0">
                                                            @if($project->budget) 
                                                                €{{ number_format($project->budget - $project->amount_billed, 2, ',', ' ') }} 
                                                            @else 
                                                                no project budget inserted  
                                                            @endif
                                                        </p>
                                                    </td>
                                                    <td class="editable" data-id="{{$project->id}}" data-column="cost_per_hour">
                                                        <p class="text-sm font-weight-bold mb-0">€{{ number_format($project->cost_per_hour, 2, ',', ' ') }}</p>
                                                    </td>
                                                    @php 
                                                    $projecttasksId = App\Models\Tasks::where('project_id', $project->id)->pluck('id');
                                                    $totalduration = App\Models\tasks_timer::whereIn('task_id', $projecttasksId)->sum('duration');
                                                    $difference = $project->estimated_hours * 60 - $totalduration; // Convert estimated hours to minutes and subtract total duration
                                                    
                                                    $hours = floor(abs($totalduration) / 60);
                                                    $minutes = abs($totalduration) % 60;
                                                    
                                                    $diffhours = floor(abs($difference) / 60);
                                                    $diffminutes = abs($difference) % 60;

                                                    $estimatedMinutes = $project->estimated_hours * 60;
                                                    $progressPercentage = $estimatedMinutes > 0 ? ($totalduration / $estimatedMinutes) * 100 : 0;
                                                    $formattedProgressPercentage = number_format($progressPercentage, 2);
                                                @endphp
                                                <td class="" data-id="{{$project->id}}" data-column="">
                                                    <p class="text-sm font-weight-bold mb-0">{{$hours}}h:{{$minutes}}m</p>
                                                </td>
                                                <td class="editable" data-id="{{$project->id}}" data-column="estimated_hours">
                                                    <p class="text-sm font-weight-bold mb-0">{{$project->estimated_hours}}h</p>
                                                </td>
                                                <td class="" data-id="{{$project->id}}" data-column="">
                                                    <p class="text-sm font-weight-bold "> 
                                                        @if($project->estimated_hours !== null)
                                                            @if($difference > 0)
                                                            <p class="text-sm font-weight-bold mb-0 text-success">
                                                            +{{$diffhours}}h:{{$diffminutes}}m</p>
                                                            @elseif($difference < 0)
                                                            <p class="text-sm font-weight-bold mb-0 text-danger">
                                                            -{{$diffhours}}h:{{$diffminutes}}m</p>
                                                            @else
                                                                <span style="color: blue;">{{$diffhours}}h:{{$diffminutes}}m</span>
                                                            @endif
                                                        @endif
                                                    </p>
                                                </td>
                                                <td class="" data-id="{{$project->id}}" data-column="">
                                                    <div class="progress" style="height: 12px; background-color: #e9ecef; border-radius: 0.25rem;">
                                                        <div class="progress-bar 
                                                            @if($progressPercentage < 50) bg-success 
                                                            @elseif($progressPercentage < 75) bg-warning 
                                                            @else bg-danger 
                                                            @endif" 
                                                            role="progressbar" 
                                                            style="width: {{$formattedProgressPercentage}}%; font-size: 10px; display: flex; align-items: center; justify-content: center; overflow: hidden; color: #fff; text-align: center; white-space: nowrap; transition: width 0.6s ease;" 
                                                            aria-valuenow="{{$formattedProgressPercentage}}" 
                                                            aria-valuemin="0" 
                                                            aria-valuemax="100">
                                                            {{$formattedProgressPercentage}}%
                                                        </div>
                                                    </div>
                                                </td>
                                                @endif
                                                <td class="editable" data-id="{{$project->id}}" data-column="tva">
                                                    <p class="text-sm font-weight-bold mb-0">%{{$project->tva}}</p>
                                                </td>
                                                <td>
                                                @if(Auth::user()->hasRole('Responsable de projet'))
                                                    @if(Auth::user()->can('View client'))
                                                        <p class="text-sm font-weight-bold mb-0">
                                                            <a href="{{route('Responsable_de_projet.clients.show', $client->id)}}">
                                                                {{App\Models\Client::where('id', $project->client_id)->first()->name}}
                                                            </a>
                                                        </p>
                                                    @else
                                                        <p class="text-sm font-weight-bold mb-0">
                                                            {{App\Models\Client::where('id', $project->client_id)->first()->name}}
                                                        </p>
                                                    @endif
                                                @endif
                                                @if(Auth::user()->hasRole('Admin'))
                                                    @if(Auth::user()->can('View client'))
                                                        <p class="text-sm font-weight-bold mb-0">
                                                            <a href="{{route('admin.clients.show', $client->id)}}">
                                                                {{App\Models\Client::where('id', $project->client_id)->first()->name}}
                                                            </a>
                                                        </p>
                                                    @else
                                                        <p class="text-sm font-weight-bold mb-0">
                                                            {{App\Models\Client::where('id', $project->client_id)->first()->name}}
                                                        </p>
                                                    @endif
                                                @endif
                                                @if(Auth::user()->hasRole('Employee'))
                                                    @if(Auth::user()->can('View client'))
                                                        <p class="text-sm font-weight-bold mb-0">
                                                            <a href="{{route('employee.clients.show', $client->id)}}">
                                                                {{App\Models\Client::where('id', $project->client_id)->first()->name}}
                                                            </a>
                                                        </p>
                                                    @else
                                                        <p class="text-sm font-weight-bold mb-0">
                                                            {{App\Models\Client::where('id', $project->client_id)->first()->name}}
                                                        </p>
                                                    @endif
                                                @endif
                                                @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                                    @if(Auth::user()->can('View client'))
                                                        <p class="text-sm font-weight-bold mb-0">
                                                            <a href="{{route('Responsable_gestionnaire.clients.show', $client->id)}}">
                                                                {{App\Models\Client::where('id', $project->client_id)->first()->name}}
                                                            </a>
                                                        </p>
                                                    @else
                                                        <p class="text-sm font-weight-bold mb-0">
                                                            {{App\Models\Client::where('id', $project->client_id)->first()->name}}
                                                        </p>
                                                    @endif
                                                @endif
                                                </td>
                                                <td>
                                                    <p class="text-sm font-weight-bold mb-0">
                                                        @forEach(App\Models\project_teams::where('project_id', $project->id)->get() as $project_team)
                                                            {{$project_team->team_name}}
                                                         <br>
                                                        @endforEach
                                                    </p>
                                                </td>
                                                <td>
                                                @if(Auth::user()->hasRole('Admin'))
                                                    @can('Update project')
                                                    <a href="{{route('admin.projects.edit', $project->id)}}" class="btn btn-info btn-xs p-2 mb-0">
                                                        edit
                                                    </a>
                                                    @endcan
                                                @endif
                                                @if(Auth::user()->hasRole('Responsable de projet'))
                                                    @can('Update project')
                                                    <a href="{{route('Responsable_de_projet.projects.edit', $project->id)}}" class="btn btn-info btn-xs p-2 mb-0">
                                                        edit
                                                    </a>
                                                    @endcan
                                                @endif
                                                @if(Auth::user()->hasRole('Employee'))
                                                    @can('Update project')
                                                    <a href="{{route('employee.projects.edit', $project->id)}}" class="btn btn-info btn-xs p-2 mb-0">
                                                        edit
                                                    </a>
                                                    @endcan
                                                @endif
                                                @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                                    @can('Update project')
                                                    <a href="{{route('Responsable_gestionnaire.projects.edit', $project->id)}}" class="btn btn-info btn-xs p-2 mb-0">
                                                        edit
                                                    </a>
                                                    @endcan
                                                @endif
                                                </td>
                                            </tr>
                                           
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <br>

                    <div class="col-12 col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <!-- Add Task Button -->
                                    <div>
                                        @if(Auth::user()->hasRole('Responsable de projet'))
                                            @can('Add task')
                                                <a href="{{route('Responsable_de_projet.tasks.create', $project->id)}}" class="btn btn-sm btn-success btn-sm">Add Task</a>
                                            @endcan
                                        @endif
                                        @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                            @can('Add task')
                                                <a href="{{route('Responsable_gestionnaire.tasks.create', $project->id)}}" class="btn btn-sm btn-success btn-sm">Add Task</a>
                                            @endcan
                                        @endif
                                        @if(Auth::user()->hasRole('Admin'))
                                            @can('Add task')
                                                <a href="{{route('admin.tasks.create', $project->id)}}" class="btn btn-sm btn-success btn-sm">Add Task</a>
                                            @endcan
                                        @endif
                                        @if(Auth::user()->hasRole('Employee'))
                                            @can('Add task')
                                                <a href="{{route('employee.tasks.create', $project->id)}}" class="btn btn-sm btn-success btn-sm">Add Task</a>
                                            @endcan
                                        @endif
                                    </div>

                                    <!-- Search Input -->
                                    <div class="d-flex flex-column align-items-start">
                                        <label for="taskSearchInput" class="form-label mb-1">Search tasks:</label>
                                        <input type="text" id="taskSearchInput" class="form-control form-control-sm" placeholder="Search tasks..." style="width: 200px;">
                                    </div>
                                </div>

                                <div class="table-responsive">
                                @if(session('alertmessage'))
                                <div class="alert alert-danger alert-dismissible fade show" role="alert" style="color: white;">
                                    {{ session('alertmessage') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"
                                        aria-label="Close">X</button>
                                </div>
                                @endif
                                    <table class="table align-items-center mb-0">
                                        <thead>
                                            <tr>
                                                <th
                                                    class="text-uppercase text-dark text-x font-weight-bolder opacity-10">
                                                    Task
                                                </th>
                                                <th
                                                    class="text-uppercase text-dark text-x font-weight-bolder opacity-10 ps-2">
                                                    description
                                                </th>
                                                <th
                                                    class="text-uppercase text-dark text-x font-weight-bolder opacity-10 ps-2">
                                                    Employees
                                                </th>
                                                <th
                                                    class="text-uppercase text-dark text-x font-weight-bolder opacity-10 ps-2">
                                                    Duration
                                                </th>
                                                <th
                                                    class="text-uppercase text-dark text-x font-weight-bolder opacity-10 ps-2">
                                                    Total <br> Duration
                                                </th>
                                                <th
                                                    class="text-uppercase text-dark text-x font-weight-bolder opacity-10 ps-2">
                                                    Estimated <br> Hours
                                                </th>
                                                <th
                                                    class="text-uppercase text-dark text-x font-weight-bolder opacity-10 ps-2">
                                                    Difference
                                                </th>
                                                <th
                                                    class="text-uppercase text-dark text-x font-weight-bolder opacity-10 ps-2">
                                                    Progress
                                                </th>
                                                <th
                                                    class="text-uppercase text-dark text-x font-weight-bolder opacity-10 ps-2">
                                                    Pointage
                                                </th>
                                                <th
                                                    class="text-uppercase text-dark text-x font-weight-bolder opacity-10 ps-2">
                                                    Action
                                                </th>
                                                <th
                                                    class="text-uppercase text-dark text-x font-weight-bolder opacity-10 ps-2">
                                                    teams
                                                </th>
                                                
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $tasks=App\Models\Tasks::where('project_id', $project->id)->get();
                                            @endphp
                                            @foreach($tasks as $task)
                                            <tr >
                                                <td class="task-name-cell">
                                                    <div class="d-flex px-2">
                                                        <div class="my-auto">
                                                            <h6 class="mb-0 text-sm"><b>{{$task->title}}</b></h6>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="description-cell">
                                                    <p class="text-sm font-weight-bold mb-0">
                                                        {{$task->description}}
                                                    </p>
                                                </td>
                                                <td>
                                                    @php
                                                        $employee_id = App\Models\employees_tasks::where('task_id', $task->id)->pluck('employee_id');
                                                    @endphp
                                                    @foreach(App\Models\Employees::whereIn('id', $employee_id)->get() as $employee)
                                                    @if(Auth::user()->hasRole('Admin'))
                                                        @if(Auth::user()->can('View employee'))
                                                        <span class="badge badge-dot me-4">
                                                            <i class="bg-info"></i>
                                                            <a href="{{route('admin.employees.show',$employee->id )}}">
                                                                <span class="text-dark text-sm">{{$employee->name}}</span>
                                                            </a>
                                                        </span>
                                                        @else
                                                        <span class="badge badge-dot me-4">
                                                            <i class="bg-info"></i>
                                                            <span class="text-dark text-sm">{{$employee->name}}</span>
                                                        </span>
                                                        @endif
                                                    @endif
                                                    @if(Auth::user()->hasRole('Responsable de projet'))
                                                        @if(Auth::user()->can('View employee'))
                                                        <span class="badge badge-dot me-4">
                                                            <i class="bg-info"></i>
                                                            <a href="{{route('Responsable_de_projet.employees.show',$employee->id )}}">
                                                                <span class="text-dark text-sm">{{$employee->name}}</span>
                                                            </a>
                                                        </span>
                                                        @else
                                                        <span class="badge badge-dot me-4">
                                                            <i class="bg-info"></i>
                                                            <span class="text-dark text-sm">{{$employee->name}}</span>
                                                        </span>
                                                        @endif
                                                    @endif
                                                    @if(Auth::user()->hasRole('Employee'))
                                                        @if(Auth::user()->can('View employee'))
                                                        <span class="badge badge-dot me-4">
                                                            <i class="bg-info"></i>
                                                            <a href="{{route('employee.employees.show',$employee->id )}}">
                                                                <span class="text-dark text-sm">{{$employee->name}}</span>
                                                            </a>
                                                        </span>
                                                        @else
                                                        <span class="badge badge-dot me-4">
                                                            <i class="bg-info"></i>
                                                            <span class="text-dark text-sm">{{$employee->name}}</span>
                                                        </span>
                                                        @endif
                                                    @endif
                                                    @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                                        @if(Auth::user()->can('View employee'))
                                                        <span class="badge badge-dot me-4">
                                                            <i class="bg-info"></i>
                                                            <a href="{{route('Responsable_gestionnaire.employees.show',$employee->id )}}">
                                                                <span class="text-dark text-sm">{{$employee->name}}</span>
                                                            </a>
                                                        </span>
                                                        @else
                                                        <span class="badge badge-dot me-4">
                                                            <i class="bg-info"></i>
                                                            <span class="text-dark text-sm">{{$employee->name}}</span>
                                                        </span>
                                                        @endif
                                                    @endif
                                                    <br>
                                                    @endforeach
                                                </td>
                                                <td>
                                                    @php
                                                        $employee_id = App\Models\employees_tasks::where('task_id',$task->id)->pluck('employee_id');
                                                        $employees = App\Models\Employees::whereIn('id', $employee_id)->get();
                                                    @endphp
                                                    @foreach($employees as $employee)
                                                        @php
                                                            $tasktotalduration = App\Models\tasks_timer::where('task_id',
                                                            $task->id)->where('employee_id', $employee->id)->sum('duration');
                                                            $taskhours = floor($tasktotalduration / 60);
                                                            $taskminutes = $tasktotalduration % 60;
                                                        @endphp
                                                        <span class="badge badge-dot me-4">
                                                            @if(Auth::user()->hasRole('Admin'))
                                                                <a href="{{ route('admin.tasks.durationInfos', ['employee' => $employee->id, 'task' => $task->id]) }}">
                                                            @endif
                                                            @if(Auth::user()->hasRole('Responsable de projet'))
                                                                <a href="{{route('Responsable_de_projet.tasks.durationInfos', ['employee' => $employee->id, 'task' => $task->id])}}">
                                                            @endif
                                                            @if(Auth::user()->hasRole('Employee'))
                                                                <a href="{{route('employee.tasks.durationInfos', ['employee' => $employee->id, 'task' => $task->id])}}">
                                                            @endif
                                                            @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                                                <a href="{{route('Responsable_gestionnaire.tasks.durationInfos', ['employee' => $employee->id, 'task' => $task->id])}}">
                                                            @endif
                                                                <i class="bg-info"></i>
                                                                    <span class="duration text-dark text-sm" style="color: blue !important;">
                                                                                {{$taskhours}}h:{{$taskminutes}}m
                                                                    </span>
                                                            </a>
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
                                                    @php
                                                        $taskdifference = $task->estimated_hours * 60 - $tasktotalduration; // Convert estimated hours to minutes and subtract total duration
                                                        $hours = floor(abs($taskdifference) / 60);
                                                        $minutes = abs($taskdifference) % 60;

                                                        $taskestimatedMinutes = $task->estimated_hours * 60;
                                                        $taskprogressPercentage = $taskestimatedMinutes > 0 ? ($tasktotalduration / $taskestimatedMinutes) * 100 : 0;
                                                        $taskformattedProgressPercentage = number_format($taskprogressPercentage, 2);
                                                    @endphp
                                                    <p class="text-sm font-weight-bold">
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
                                                    @php
                                                        $employee_id = App\Models\Employees::where('user_id', Auth::user()->id)->first()->id;
                                                        $task_timer = App\Models\tasks_timer::where('task_id', $task->id)->where('employee_id', $employee_id)->whereNull('end_date')->first();
                                                        $employee_tasks = App\Models\employees_tasks::where('employee_id', $employee_id)->where('task_id', $task->id)->first();
                                                    @endphp
                                                    @if($employee_tasks)
                                                        @if($task_timer)
                                                        <!--
                                                            @if(($task_timer->start_date != null && $task_timer->end_date == null)
                                                            && $task_timer->paused == 0 )
                                                                @if(Auth::user()->hasRole('Admin'))
                                                                <form class="d-inline"
                                                                    action="{{ route('admin.tasks.pause', ['tasks' => $employee_tasks->id, 'employee_id' => $employee_id]) }}"
                                                                    method="POST">
                                                                @endif
                                                                @if(Auth::user()->hasRole('Responsable de projet'))
                                                                <form class="d-inline"
                                                                    action="{{ route('Responsable_de_projet.tasks.pause', ['tasks' => $employee_tasks->id, 'employee_id' => $employee_id]) }}"
                                                                    method="POST">
                                                                @endif
                                                                @if(Auth::user()->hasRole('Employee'))
                                                                <form class="d-inline"
                                                                    action="{{ route('employee.tasks.pause', ['tasks' => $employee_tasks->id, 'employee_id' => $employee_id]) }}"
                                                                    method="POST">
                                                                @endif
                                                                @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                                                <form class="d-inline"
                                                                    action="{{ route('Responsable_gestionnaire.tasks.pause', ['tasks' => $employee_tasks->id, 'employee_id' => $employee_id]) }}"
                                                                    method="POST">
                                                                @endif
                                                                    @csrf
                                                                    <button class="btn btn-sm btn-secondary p-2 m-2 pause-button" type="submit"
                                                                        data-task-id="{{$task->id}}">
                                                                        Pause
                                                                    </button>
                                                                </form>
                                                            @endif
                                                            @if(($task_timer->end_date == null && $task_timer->start_date != null )
                                                            && $task_timer->paused == 1)
                                                                @if(Auth::user()->hasRole('Admin'))
                                                                <form class="d-inline"
                                                                    action="{{ route('admin.tasks.resume', ['tasks' => $employee_tasks->id, 'employee_id' => $employee_id]) }}"
                                                                    method="POST">
                                                                @endif
                                                                @if(Auth::user()->hasRole('Responsable de projet'))
                                                                <form class="d-inline"
                                                                    action="{{ route('Responsable_de_projet.tasks.resume', ['tasks' => $employee_tasks->id, 'employee_id' => $employee_id]) }}"
                                                                    method="POST">
                                                                @endif
                                                                @if(Auth::user()->hasRole('Employee'))
                                                                <form class="d-inline"
                                                                    action="{{ route('employee.tasks.resume', ['tasks' => $employee_tasks->id, 'employee_id' => $employee_id]) }}"
                                                                    method="POST">
                                                                @endif
                                                                @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                                                <form class="d-inline"
                                                                    action="{{ route('Responsable_gestionnaire.tasks.resume', ['tasks' => $employee_tasks->id, 'employee_id' => $employee_id]) }}"
                                                                    method="POST">
                                                                @endif
                                                                    @csrf
                                                                    <button class="btn btn-sm btn-primary p-2 m-2 resume-button" type="submit"
                                                                        data-task-id="{{$task->id}}">
                                                                        Continue
                                                                    </button>
                                                                </form>
                                                            @endif
                                                        -->
                                                        @else
                                                            @if(Auth::user()->hasRole('Admin'))
                                                            <form class="d-inline-flex align-items-center"
                                                            action="{{ route('admin.tasks.start', ['tasks' => $employee_tasks->id, 'employee_id' => $employee_id]) }}"
                                                            method="POST">
                                                            @endif
                                                            @if(Auth::user()->hasRole('Responsable de projet'))
                                                            <form class="d-inline-flex align-items-center"
                                                                action="{{ route('Responsable_de_projet.tasks.start', ['tasks' => $employee_tasks->id, 'employee_id' => $employee_id]) }}"
                                                                method="POST">
                                                            @endif
                                                            @if(Auth::user()->hasRole('Employee'))
                                                            <form class="d-inline-flex align-items-center"
                                                                action="{{ route('employee.tasks.start', ['tasks' => $employee_tasks->id, 'employee_id' => $employee_id]) }}"
                                                                method="POST">
                                                            @endif
                                                            @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                                            <form class="d-inline-flex align-items-center"
                                                                action="{{ route('Responsable_gestionnaire.tasks.start', ['tasks' => $employee_tasks->id, 'employee_id' => $employee_id]) }}"
                                                                method="POST">
                                                            @endif
                                                            @csrf
                                                            <textarea name="comment" id="comment" class="form-control mr-3" placeholder="comment" style="margin-right: 10px; min-height: 38px; resize: vertical;"></textarea>
                                                            <button class="btn btn-sm btn-success p-2 m-2 start-button" id="start-btn" type="submit" data-task-id="{{$task->id}}" style="height: 38px;">
                                                                Start
                                                            </button>
                                                        </form>

                                                        @endif

                                                        @if(Auth::user()->hasRole('Admin'))
                                                        <form class="d-inline stop-form"
                                                            action="{{ route('admin.tasks.stop', ['tasks' => $employee_tasks->id, 'employee_id' => $employee_id]) }}"
                                                            method="POST">
                                                        @endif
                                                        @if(Auth::user()->hasRole('Responsable de projet'))
                                                        <form class="d-inline stop-form"
                                                            action="{{ route('Responsable_de_projet.tasks.stop', ['tasks' => $employee_tasks->id, 'employee_id' => $employee_id]) }}"
                                                            method="POST">
                                                        @endif
                                                        @if(Auth::user()->hasRole('Employee'))
                                                        <form class="d-inline stop-form"
                                                            action="{{ route('employee.tasks.stop', ['tasks' => $employee_tasks->id, 'employee_id' => $employee_id]) }}"
                                                            method="POST">
                                                        @endif
                                                        @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                                        <form class="d-inline stop-form"
                                                            action="{{ route('Responsable_gestionnaire.tasks.stop', ['tasks' => $employee_tasks->id, 'employee_id' => $employee_id]) }}"
                                                            method="POST">
                                                        @endif
                                                            @csrf
                                                            <button class="btn btn-sm btn-danger p-2 m-2 stop-button" type="submit"
                                                                data-task-id="{{$task->id}}">
                                                                Stop
                                                            </button>
                                                        </form>
                                                    @endif
                                                </td>
                                                @if(Auth::user()->hasRole('Admin'))
                                                    <td>
                                                        @can('Update task')
                                                        <a href="{{ route('admin.tasks.edit', $task->id) }}" class="btn btn-sm btn-primary p-2 m-2 edit-btn">Edit</a>
                                                        @endcan
                                                        @can('Delete task')
                                                        <form method="post" action="{{ route('admin.tasks.destroy', $task->id) }}" onsubmit="return confirm('Are you sure?');" style="display: inline;">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-danger p-2 m-2">X</button>
                                                        </form>
                                                        @endcan
                                                    </td>
                                                @endif
                                                @if(Auth::user()->hasRole('Responsable de projet'))
                                                    @can('Update task')
                                                        <td>
                                                            <a href="{{ route('Responsable_de_projet.tasks.edit', $task->id) }}" class="btn btn-sm btn-primary p-2 m-2 edit-btn">Edit</a>
                                                        </td>
                                                    @endcan
                                                    @can('Delete task')
                                                        <form method="post" action="{{ route('Responsable_de_projet.tasks.destroy', $task->id) }}" onsubmit="return confirm('Are you sure?');" style="display: inline;">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-danger p-2 m-2">X</button>
                                                        </form>
                                                    @endcan
                                                @endif
                                                @if(Auth::user()->hasRole('Employee'))
                                                    @can('Update task')
                                                        <td>
                                                            <a href="{{ route('employee.tasks.edit', $task->id) }}" class="btn btn-sm btn-primary p-2 m-2 edit-btn">Edit</a>
                                                        </td>
                                                    @endcan
                                                    @can('Delete task')
                                                        <form method="post" action="{{ route('employee.tasks.destroy', $task->id) }}" onsubmit="return confirm('Are you sure?');" style="display: inline;">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-danger p-2 m-2">X</button>
                                                        </form>
                                                    @endcan
                                                @endif
                                                @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                                    @can('Update task')
                                                        <td>
                                                            <a href="{{ route('Responsable_gestionnaire.tasks.edit', $task->id) }}" class="btn btn-sm btn-primary p-2 m-2 edit-btn">Edit</a>
                                                        </td>
                                                    @endcan
                                                    @can('Delete task')
                                                        <form method="post" action="{{ route('Responsable_gestionnaire.tasks.destroy', $task->id) }}" onsubmit="return confirm('Are you sure?');" style="display: inline;">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-danger p-2 m-2">X</button>
                                                        </form>
                                                    @endcan
                                                @endif
                                                <td>
                                                    <p class="text-sm font-weight-bold mb-0">
                                                        @php
                                                            $teams_id=App\Models\team_tasks::where('task_id', $task->id)->get();
                                                            
                                                        @endphp
                                                        @foreach($teams_id as $id)
                                                            @php
                                                                $team_id = $id->team_id;
                                                            @endphp
                                                            @forEach(App\Models\Teams::where('id', $team_id)->get() as $team)
                                                            {{$team->name}}
                                                         <br>
                                                        @endforEach
                                                        @endforeach
                                                        
                                                    </p>
                                                </td>
                                            </tr>
                                           @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
            
                    <br>
                    @can('view invoices')
                    @if($invoices !== null)
                    <div class="col-12 col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                @php
                                $totalPaid = App\Models\invoices_history::where('entreprise_id', Auth::user()->entreprise_id)->where('project_id',$project->id)->where('Payment_status', 1)->sum('total_ttc');
                                $totalUnpaid = App\Models\invoices_history::where('entreprise_id', Auth::user()->entreprise_id)->where('project_id',$project->id)
                                                ->where(function($query) {
                                                    $query->where('Payment_status', 0)
                                                        ->orWhereNull('Payment_status');
                                                })
                                                ->sum('total_ttc');
                                $totaltva = App\Models\invoices_history::where('entreprise_id', Auth::user()->entreprise_id)->where('project_id',$project->id)->sum('total_tva');                           
                                @endphp
                                <div class="d-flex flex-column align-items-end ms-3"> <!-- Adjusted for alignment and spacing -->
                                    <div class="d-flex align-items-center">
                                        <div>
                                            <label for="totalPaid" class="form-label">Total Paid: €{{ number_format($totalPaid, 2, ',', ' ') }} | </label>
                                        </div>
                                        <div>
                                            <label for="totalUnpaid" class="form-label">Total Unpaid: €{{ number_format($totalUnpaid, 2, ',', ' ') }} | </label>
                                        </div>
                                        <div>
                                            <label for="totalUnpaid" class="form-label">Total TVA: €{{ number_format($totaltva, 2, ',', ' ') }}</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table align-items-center mb-0" id="invoicesTable">
                                        <thead>
                                            <tr>
                                                <th
                                                    class="text-center text-uppercase text-secondary text-x font-weight-bolder opacity-10">
                                                    Invoice N
                                                </th>
                                                <th
                                                    class="text-center text-uppercase text-secondary text-x font-weight-bolder opacity-10">
                                                    Project Name
                                                </th>
                                                <th
                                                    class="text-center text-uppercase text-secondary text-x font-weight-bolder opacity-10">
                                                    TVA
                                                </th>
                                                <th
                                                    class="text-center text-uppercase text-secondary text-x font-weight-bolder opacity-10">
                                                    Client Name
                                                </th>
                                                <th
                                                    class="text-center text-uppercase text-secondary text-x font-weight-bolder opacity-10">
                                                    Invoice Type
                                                </th>
                                                <th
                                                    class="text-center text-uppercase text-secondary text-x font-weight-bolder opacity-10">
                                                    Created at
                                                </th>
                                                <th
                                                    class="text-center text-uppercase text-secondary text-x font-weight-bolder opacity-10">
                                                    Cost Total
                                                </th>
                                                <th
                                                    class="text-center text-uppercase text-secondary text-x font-weight-bolder opacity-10">
                                                    Actions
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($invoices as $invoice)
                                            <tr>
                                                <td>
                                                    <p class="align-middle text-center text-sm font-weight-bold mb-0">
                                                    @if(Auth::user()->hasRole('Responsable de projet'))
                                                        <a href="{{ route('Responsable_de_projet.invoices.servePdf', ['invoiceName' => $invoice->id]) }}" target="_blank">
                                                            {{$entreprise_infos->invoice_prefix}}-{{date('ym', strtotime($invoice->created_at))}}{{$invoice->number}}
                                                        </a>
                                                    @endif
                                                    @if(Auth::user()->hasRole('Admin'))
                                                        <a href="{{ route('admin.invoices.servePdf', ['invoiceName' => $invoice->id]) }}" target="_blank">
                                                            {{$entreprise_infos->invoice_prefix}}-{{date('ym', strtotime($invoice->created_at))}}{{$invoice->number}}
                                                        </a>
                                                    @endif
                                                    @if(Auth::user()->hasRole('Employee'))
                                                        <a href="{{ route('employee.invoices.servePdf', ['invoiceName' => $invoice->id]) }}" target="_blank">
                                                            {{$entreprise_infos->invoice_prefix}}-{{date('ym', strtotime($invoice->created_at))}}{{$invoice->number}}
                                                        </a>
                                                    @endif
                                                    @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                                        <a href="{{ route('Responsable_gestionnaire.invoices.servePdf', ['invoiceName' => $invoice->id]) }}" target="_blank">
                                                            {{$entreprise_infos->invoice_prefix}}-{{date('ym', strtotime($invoice->created_at))}}{{$invoice->number}}
                                                        </a>
                                                    @endif
                                                    </p>
                                                </td>
                                                <td>
                                                    <p class="align-middle text-center text-sm font-weight-bold mb-0">
                                                        {{strtoupper($invoice->project_name)}}  
                                                    </p>
                                                </td>
                                                <td>
                                                    <p class="align-middle text-center text-sm font-weight-bold mb-0">
                                                        @php 
                                                            $project = App\Models\Project::find($invoice->project_id);
                                                        @endphp
                                                        {{$project->tva}}%
                                                    </p>
                                                </td>
                                                <td>
                                                    <p class="align-middle text-center text-sm font-weight-bold mb-0">
                                                        @php
                                                        $client = \App\Models\Client::where('id', $invoice->client_id)->first();
                                                        @endphp
                                                        {{$client->name}}
                                                    </p>
                                                </td>
                                                <td>
                                                    <p class="text-center text-sm font-weight-bold mb-0">
                                                    @if($invoice->type == 'Partial')
                                                        {{$invoice->type}}-{{$invoice->partial}}
                                                    @elseif($invoice->type == 'Percentage')
                                                        {{$invoice->type}}-{{$invoice->percentage}}
                                                    @else
                                                        {{$invoice->type}}
                                                    @endif
                                                    </p>
                                                </td>
                                                <td>
                                                    <p class="text-center text-sm font-weight-bold mb-0">{{$invoice->created_at}}
                                                    </p>
                                                </td>
                                                <td id="totalcost">
                                                    <p class="text-center text-sm font-weight-bold mb-0">€<span id="totalCostValue">{{ number_format($invoice->total_ttc, 2, ',', ' ') }}</span>
                                                    </p>
                                                </td>
                                                <td>
                                                    <div class="card-footer d-flex justify-content-end">

                                                        
                                                            @if(Auth::user()->hasRole('Admin'))
                                                                @if(\App\Models\invoices_history::where('id',
                                                                $invoice->id)->first()->Payment_status==1)
                                                                <form action="{{route('admin.invoice.deactivate', $invoice->id) }}"
                                                                    method="post" onsubmit="return confirm('Are you sure?');">
                                                                    @csrf
                                                                    <button type="submit" class="btn btn-success m-2 payment-button p-2" style="height: 30px; width: 78px; text-align: center; line-height: 0px;">
                                                                        Paid
                                                                    </button>
                                                                </form>
                                                                @else
                                                                <form action="{{route('admin.invoice.activate', $invoice->id) }}"
                                                                    method="post" onsubmit="return confirm('Are you sure?');">
                                                                    @csrf
                                                                    <button type="submit" class="btn btn-danger m-2 payment-button p-2" style="height: 30px; width: 78px; text-align: center; line-height: 0px;">
                                                                        Unpaid
                                                                    </button>
                                                                </form>
                                                                @endif
                                                            @endif
                                                            @if(Auth::user()->hasRole('Responsable de projet'))
                                                                @if(\App\Models\invoices_history::where('id',
                                                                $invoice->id)->first()->Payment_status==1)
                                                                <form action="{{route('Responsable_de_projet.invoice.deactivate', $invoice->id) }}"
                                                                    method="post" onsubmit="return confirm('Are you sure?');">
                                                                    @csrf
                                                                    <button type="submit" class="btn btn-success btn-xs m-2 payment-button" style="height: 30px; width: 78px; text-align: center; line-height: 0px;">
                                                                        Paid
                                                                    </button>
                                                                </form>
                                                                @else
                                                                <form action="{{route('Responsable_de_projet.invoice.activate', $invoice->id) }}"
                                                                    method="post" onsubmit="return confirm('Are you sure?');">
                                                                    @csrf
                                                                    <button type="submit" class="btn btn-danger btn-xs m-2 payment-button" style="height: 30px; width: 78px; text-align: center; line-height: 0px;"> 
                                                                        Unpaid
                                                                    </button>
                                                                </form>
                                                                @endif
                                                            @endif
                                                            @if(Auth::user()->hasRole('Employee'))
                                                                @if(\App\Models\invoices_history::where('id',
                                                                $invoice->id)->first()->Payment_status==1)
                                                                <form action="{{route('employee.invoice.deactivate', $invoice->id) }}"
                                                                    method="post" onsubmit="return confirm('Are you sure?');">
                                                                    @csrf
                                                                    <button type="submit" class="btn btn-success btn-xs m-2 payment-button" style="height: 30px; width: 78px; text-align: center; line-height: 0px;">
                                                                        Paid
                                                                    </button>
                                                                </form>
                                                                @else
                                                                <form action="{{route('employee.invoice.activate', $invoice->id) }}"
                                                                    method="post" onsubmit="return confirm('Are you sure?');">
                                                                    @csrf
                                                                    <button type="submit" class="btn btn-danger btn-xs m-2 payment-button" style="height: 30px; width: 78px; text-align: center; line-height: 0px;"> 
                                                                        Unpaid
                                                                    </button>
                                                                </form>
                                                                @endif
                                                            @endif
                                                            @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                                                @if(\App\Models\invoices_history::where('id',
                                                                $invoice->id)->first()->Payment_status==1)
                                                                <form action="{{route('Responsable_gestionnaire.invoice.deactivate', $invoice->id) }}"
                                                                    method="post" onsubmit="return confirm('Are you sure?');">
                                                                    @csrf
                                                                    <button type="submit" class="btn btn-success btn-xs m-2 payment-button" style="height: 30px; width: 78px; text-align: center; line-height: 0px;">
                                                                        Paid
                                                                    </button>
                                                                </form>
                                                                @else
                                                                <form action="{{route('Responsable_gestionnaire.invoice.activate', $invoice->id) }}"
                                                                    method="post" onsubmit="return confirm('Are you sure?');">
                                                                    @csrf
                                                                    <button type="submit" class="btn btn-danger btn-xs m-2 payment-button" style="height: 30px; width: 78px; text-align: center; line-height: 0px;"> 
                                                                        Unpaid
                                                                    </button>
                                                                </form>
                                                                @endif
                                                            @endif
                                                            @if ($invoice->sentInvoice == 0)
                                                            <!-- Send Button Triggering Modal --> 
                                                            <button type="button" class="btn btn-dark btn-xs p-2 m-2" style="height: 30px; width: 70px;"
                                                                data-bs-toggle="modal" data-bs-target="#sendInvoiceModal"
                                                                data-invoice-id="{{ $invoice->id }}">
                                                                Send
                                                            </button>
                                                            @else
                                                            <button type="button" class="btn btn-dark btn-xs p-2 m-2" style="height: 30px; width: 70px;"
                                                                data-bs-toggle="modal" data-bs-target="#sendInvoiceModal"
                                                                data-invoice-id="{{ $invoice->id }}">
                                                                Resend
                                                            </button>
                                                            @endif
                                                        

                                                        @if(!$invoice->Payment_status == 1)
                                                    
                                                            @if(Auth::user()->hasRole('Admin') || Auth::user()->hasRole('Responsable de projet') || Auth::user()->hasRole('Employee') || Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                                                @can('Send invoice')
                                                                    <div class="dropdown m-2">
                                                                        <button class="btn btn-secondary btn-xs dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                                                            Send Reminder
                                                                        </button>
                                                                        <ul class="dropdown-menu">
                                                                        @if(!$invoice->first_reminder_date)
                                                                        <li><a class="dropdown-item" href="{{ route(Auth::user()->hasRole('Admin') ? 'admin.invoice.sendManualremind' : (Auth::user()->hasRole('Responsable de projet') ? 'Responsable_de_projet.invoice.sendManualremind' : (Auth::user()->hasRole('Employee') ? 'employee.invoice.sendManualremind' : (Auth::user()->hasRole('Responsable de projet et gestionnaire') ? 'Responsable_gestionnaire.invoice.sendManualremind' : ''))), ['id' => $invoice->id, 'reminder' => 1]) }}">Send Reminder 1</a></li>
                                                                        @endif
                                                                        @if(!$invoice->second_reminder_date)
                                                                        <li><a class="dropdown-item" href="{{ route(Auth::user()->hasRole('Admin') ? 'admin.invoice.sendManualremind' : (Auth::user()->hasRole('Responsable de projet') ? 'Responsable_de_projet.invoice.sendManualremind' : (Auth::user()->hasRole('Employee') ? 'employee.invoice.sendManualremind' : (Auth::user()->hasRole('Responsable de projet et gestionnaire') ? 'Responsable_gestionnaire.invoice.sendManualremind' : ''))), ['id' => $invoice->id, 'reminder' => 2]) }}">Send Reminder 2</a></li>
                                                                        @endif
                                                                        @if(!$invoice->third_reminder_date)
                                                                        <li><a class="dropdown-item" href="{{ route(Auth::user()->hasRole('Admin') ? 'admin.invoice.sendManualremind' : (Auth::user()->hasRole('Responsable de projet') ? 'Responsable_de_projet.invoice.sendManualremind' : (Auth::user()->hasRole('Employee') ? 'employee.invoice.sendManualremind' : (Auth::user()->hasRole('Responsable de projet et gestionnaire') ? 'Responsable_gestionnaire.invoice.sendManualremind' : ''))), ['id' => $invoice->id, 'reminder' => 3]) }}">Send Reminder 3</a></li>
                                                                        @endif
                                                                        </ul>
                                                                    </div>
                                                                @endcan
                                                            @endif
                                                        @endif

                                                        <div class="dropdown m-2">
                                                            <button type="button" class="btn btn-dark btn-xs dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                                                Credit Note
                                                            </button>
                                                            <ul class="dropdown-menu">
                                                                @if(Auth::user()->hasRole('Admin'))
                                                                <li><a class="dropdown-item" href="{{ route('admin.invoice.creditNote', $invoice->id) }}?type=full">Full</a></li>
                                                                @endif
                                                                @if(Auth::user()->hasRole('Responsable de projet'))
                                                                <li><a class="dropdown-item" href="{{ route('Responsable_de_projet.invoice.creditNote', $invoice->id) }}?type=full">Full</a></li>
                                                                @endif
                                                                @if(Auth::user()->hasRole('Employee'))
                                                                <li><a class="dropdown-item" href="{{ route('employee.invoice.creditNote', $invoice->id) }}?type=full">Full</a></li>
                                                                @endif
                                                                @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                                                <li><a class="dropdown-item" href="{{ route('Responsable_gestionnaire.invoice.creditNote', $invoice->id) }}?type=full">Full</a></li>
                                                                @endif
                                                                <li><a class="dropdown-item" href="#" onclick="showPartialPopup({{ $invoice->id }}, {{ number_format($invoice->total_ttc, 2, '.', '') }})">Partial</a></li>
                                                            </ul>
                                                        </div>

                                                        <!-- Partial Credit Note Modal -->
                                                        <div class="modal fade" id="partialCreditNoteModal" tabindex="-1" aria-labelledby="partialCreditNoteModalLabel" aria-hidden="true">
                                                            <div class="modal-dialog">
                                                                <div class="modal-content">
                                                                    <div class="modal-header">
                                                                        <h5 class="modal-title" id="partialCreditNoteModalLabel">Partial Credit Note</h5>
                                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                    </div>
                                                                    <div class="modal-body">
                                                                        <form id="partialCreditNoteForm">
                                                                            <div class="mb-3">
                                                                                <label for="partialAmount" class="form-label">Enter Amount:</label>
                                                                                <input type="number" class="form-control" id="partialAmount" name="partialAmount" step="0.0001" required>
                                                                                <span id="error-message" style="color: red; display: none;">Amount exceeds total cost.</span>
                                                                            </div>
                                                                            <input type="hidden" id="invoiceId" name="invoiceId">
                                                                            <input type="hidden" id="totalCost" name="totalCost">
                                                                        </form>
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                                        <button type="button" class="btn btn-primary" onclick="submitPartialCreditNote()">Submit</button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        @if($invoice->id === $invoices->max('id') || $invoice->sentInvoice == 0)
                                                            @if(Auth::user()->hasRole('Admin'))
                                                                @if($invoice->type == 'Partial')
                                                                    <a href="{{ route('admin.invoices.editPartialInvoice', $invoice->id) }}" class="btn btn-primary btn-xs m-2" style="height: 30px;">Edit</a>
                                                                @elseif($invoice->type == 'Percentage')
                                                                    <a href="{{ route('admin.invoices.editPercentageInvoice', $invoice->id) }}" class="btn btn-primary btn-xs m-2" style="height: 30px;">Edit</a>
                                                                @elseif($invoice->type == 'Period')
                                                                    <a href="{{ route('admin.invoices.editPeriodInvoice', $invoice->id) }}" class="btn btn-primary btn-xs m-2" style="height: 30px;">Edit</a>
                                                                @elseif($invoice->type == 'Detailed')
                                                                    <a href="{{ route('admin.invoices.editDetailedInvoice', $invoice->id) }}" class="btn btn-primary btn-xs m-2" style="height: 30px;">Edit</a>
                                                                @else
                                                                    <a href="{{ route('admin.invoices.editFullInvoice', $invoice->id) }}" class="btn btn-primary btn-xs m-2" style="height: 30px;">Edit</a>
                                                                @endif
                                                            @endif
                                                            @if(Auth::user()->hasRole('Responsable de projet'))
                                                                @if($invoice->type == 'Partial')
                                                                    <a href="{{ route('Responsable_de_projet.invoices.editPartialInvoice', $invoice->id) }}" class="btn btn-primary btn-xs m-2" style="height: 30px;">Edit</a>
                                                                @elseif($invoice->type == 'Percentage')
                                                                    <a href="{{ route('Responsable_de_projet.invoices.editPercentageInvoice', $invoice->id) }}" class="btn btn-primary btn-xs m-2" style="height: 30px;">Edit</a>
                                                                @elseif($invoice->type == 'Period')
                                                                    <a href="{{ route('Responsable_de_projet.invoices.editPeriodInvoice', $invoice->id) }}" class="btn btn-primary btn-xs m-2" style="height: 30px;">Edit</a>
                                                                @elseif($invoice->type == 'Detailed')
                                                                    <a href="{{ route('Responsable_de_projet.invoices.editDetailedInvoice', $invoice->id) }}" class="btn btn-primary btn-xs m-2" style="height: 30px;">Edit</a>
                                                                @else
                                                                    <a href="{{ route('Responsable_de_projet.invoices.editFullInvoice', $invoice->id) }}" class="btn btn-primary btn-xs m-2" style="height: 30px;">Edit</a>
                                                                @endif
                                                            @endif
                                                            @if(Auth::user()->hasRole('Employee'))
                                                                @if($invoice->type == 'Partial')
                                                                    <a href="{{ route('employee.invoices.editPartialInvoice', $invoice->id) }}" class="btn btn-primary btn-xs m-2" style="height: 30px;">Edit</a>
                                                                @elseif($invoice->type == 'Percentage')
                                                                    <a href="{{ route('employee.invoices.editPercentageInvoice', $invoice->id) }}" class="btn btn-primary btn-xs m-2" style="height: 30px;">Edit</a>
                                                                @elseif($invoice->type == 'Period')
                                                                    <a href="{{ route('employee.invoices.editPeriodInvoice', $invoice->id) }}" class="btn btn-primary btn-xs m-2" style="height: 30px;">Edit</a>
                                                                @elseif($invoice->type == 'Detailed')
                                                                    <a href="{{ route('employee.invoices.editDetailedInvoice', $invoice->id) }}" class="btn btn-primary btn-xs m-2" style="height: 30px;">Edit</a>
                                                                @else
                                                                    <a href="{{ route('employee.invoices.editFullInvoice', $invoice->id) }}" class="btn btn-primary btn-xs m-2" style="height: 30px;">Edit</a>
                                                                @endif
                                                            @endif
                                                            @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                                                @if($invoice->type == 'Partial')
                                                                    <a href="{{ route('Responsable_gestionnaire.invoices.editPartialInvoice', $invoice->id) }}" class="btn btn-primary btn-xs m-2" style="height: 30px;">Edit</a>
                                                                @elseif($invoice->type == 'Percentage')
                                                                    <a href="{{ route('Responsable_gestionnaire.invoices.editPercentageInvoice', $invoice->id) }}" class="btn btn-primary btn-xs m-2" style="height: 30px;">Edit</a>
                                                                @elseif($invoice->type == 'Period')
                                                                    <a href="{{ route('Responsable_gestionnaire.invoices.editPeriodInvoice', $invoice->id) }}" class="btn btn-primary btn-xs m-2" style="height: 30px;">Edit</a>
                                                                @elseif($invoice->type == 'Detailed')
                                                                    <a href="{{ route('Responsable_gestionnaire.invoices.editDetailedInvoice', $invoice->id) }}" class="btn btn-primary btn-xs m-2" style="height: 30px;">Edit</a>
                                                                @else
                                                                    <a href="{{ route('Responsable_gestionnaire.invoices.editFullInvoice', $invoice->id) }}" class="btn btn-primary btn-xs m-2" style="height: 30px;">Edit</a>
                                                                @endif
                                                            @endif
                                                        
                                                            @if(Auth::user()->hasRole('Admin'))
                                                            @can('Delete invoice')
                                                            @if($invoice->id === $invoices->max('id'))
                                                            <form action="{{route('admin.invoice.destroy',$invoice->id) }}"
                                                                method="post" onsubmit="return confirm('Are you sure?');"
                                                                class="d-inline">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-danger btn-xs m-2">X</button>
                                                            </form>
                                                            @endif
                                                            @endcan
                                                            @endif
                                                            @if(Auth::user()->hasRole('Responsable de projet'))
                                                            @can('Delete invoice')
                                                            @if($invoice->id === $invoices->max('id'))
                                                            <form action="{{route('Responsable_de_projet.invoice.destroy',$invoice->id) }}"
                                                                method="post" onsubmit="return confirm('Are you sure?');"
                                                                class="d-inline">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-danger btn-xs m-2">X</button>
                                                            </form>
                                                            @endif
                                                            @endcan
                                                            @endif
                                                            @if(Auth::user()->hasRole('Employee'))
                                                            @can('Delete invoice')
                                                            @if($invoice->id === $invoices->max('id'))
                                                            <form action="{{route('employee.invoice.destroy',$invoice->id) }}"
                                                                method="post" onsubmit="return confirm('Are you sure?');"
                                                                class="d-inline">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-danger btn-xs m-2">X</button>
                                                            </form>
                                                            @endif
                                                            @endcan
                                                            @endif
                                                            @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                                            @can('Delete invoice')
                                                            @if($invoice->id === $invoices->max('id'))
                                                            <form action="{{route('Responsable_gestionnaire.invoice.destroy',$invoice->id) }}"
                                                                method="post" onsubmit="return confirm('Are you sure?');"
                                                                class="d-inline">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-danger btn-xs m-2">X</button>
                                                            </form>
                                                            @endif
                                                            @endcan
                                                            @endif

                                                        @endif

                                                    </div>
                                                </td>
                                            </tr>
                                            @endforeach

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Attachement Modal -->
                        <div class="modal fade" id="sendInvoiceModal" tabindex="-1"
                            aria-labelledby="sendInvoiceModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="sendInvoiceModalLabel">Send Invoice</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <form id="sendInvoiceForm" method="post" enctype="multipart/form-data">
                                        @csrf
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <h6>Recipients:</h6>
                                                <div id="recipientsList" class="mb-3 p-2 border rounded">
                                                    <!-- Recipients will be populated here -->
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label for="fileUpload" class="form-label">Upload Additional Files (Optional):</label>
                                                <input type="file" class="form-control" id="fileUpload" name="file[]" multiple>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                            <button type="submit" class="btn btn-primary">Send Invoice</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>  
                    @endif
                    @endcan

                    <br>
                    @can('view devis')
                    @if($devis !== null)
                    <div class="col-12 col-lg-12">
                        <div class="card">
                            <div class="card-body">
                            @php
                                $Total_Accpeted = App\Models\devis_history::where('entreprise_id', Auth::user()->entreprise_id)->where('project_id',$project->id)->where('acceptanceStatus', 1)->sum('budget');
                                
                            @endphp
                            <div class="d-flex flex-column align-items-end ms-3"> <!-- Adjusted for alignment and spacing -->
                                <div class="d-flex align-items-center">
                                    <div>
                                        <label for="totalPaid" class="form-label">Total : €{{ number_format($Total_Accpeted, 2, ',', ' ') }} </label>
                                    </div>
                                    
                                </div>
                            </div>
                                <div class="table-responsive">
                                    <table class="table align-items-center mb-0">
                                        <thead>
                                            <tr>
                                                <th
                                                    class="text-center text-uppercase text-secondary text-x font-weight-bolder opacity-10">
                                                    devis N
                                                </th>
                                                <th
                                                    class="text-center text-uppercase text-secondary text-x font-weight-bolder opacity-10">
                                                    Client Name / Emails
                                                </th>
                                                <th
                                                    class="text-center text-uppercase text-secondary text-x font-weight-bolder opacity-10 ps-2">
                                                    Budget
                                                </th>
                                                <th
                                                    class="text-center text-uppercase text-secondary text-x font-weight-bolder opacity-10 ps-2">
                                                    Unbilled Budget
                                                </th>
                                                <th
                                                    class="text-center text-uppercase text-secondary text-x font-weight-bolder opacity-10">
                                                    Created at
                                                </th>
                                                <th
                                                    class="text-center text-uppercase text-secondary text-x font-weight-bolder opacity-10">
                                                    Status 
                                                </th>
                                                <th
                                                    class="text-center text-uppercase text-secondary text-x font-weight-bolder opacity-10">
                                                    Actions
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($devis as $d)
                                            <tr>
                                                <td>
                                                    <p class="align-middle text-center text-sm font-weight-bold mb-0">
                                                        @if(Auth::user()->hasRole('Responsable de projet'))
                                                            <a href="{{ route('Responsable_de_projet.devis.downloadDevis', $d->devis_number) }}">{{$entreprise_infos->devis_prefix}}-{{$d->devis_number}}
                                                            </a>
                                                        @endif
                                                        @if(Auth::user()->hasRole('Admin'))
                                                            <a href="{{ route('admin.devis.downloadDevis', $d->devis_number) }}">{{$entreprise_infos->devis_prefix}}-{{$d->devis_number}}
                                                            </a>
                                                        @endif
                                                        @if(Auth::user()->hasRole('Employee'))
                                                            <a href="{{ route('employee.devis.downloadDevis', $d->devis_number) }}">{{$entreprise_infos->devis_prefix}}-{{$d->devis_number}}
                                                            </a>
                                                        @endif
                                                        @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                                            <a href="{{ route('Responsable_gestionnaire.devis.downloadDevis', $d->devis_number) }}">{{$entreprise_infos->devis_prefix}}-{{$d->devis_number}}
                                                            </a>
                                                        @endif
                                                    </p>
                                                </td>

                                                <td>
                                                    <p class="align-middle text-center text-sm font-weight-bold mb-0">
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
                                                <td>
                                                    <p class="text-center text-sm font-weight-bold mb-0">€{{ number_format($d->budget, 2, ',', ' ') }}</p>
                                                </td>
                                                <td>
                                                    @php
                                                        $devisInvoicesTotal = \App\Models\invoices_history::where('devis_id', $d->id)
                                                            ->sum('total_htva');
                                                        $unbilledBudget = ($d->budget ?? 0) - ($devisInvoicesTotal ?? 0);
                                                    @endphp
                                                    <p class="text-center text-sm font-weight-bold mb-0">€{{ number_format(max($unbilledBudget, 0), 2, ',', ' ') }}</p>
                                                </td>
                                                <td>
                                                    <p class="text-center text-sm font-weight-bold mb-0">{{$d->created_at}}
                                                    </p>
                                                </td>
                                                <td>
                                                    <p class="text-center text-sm font-weight-bold mb-0">
                                                        @if($d->acceptanceStatus == 1)
                                                        <span class="badge  badge-success"> Accepted </span> 
                                                        @elseif($d->acceptanceStatus == 2 )
                                                        <span class="badge  badge-danger"> Declined </span> 
                                                        @else
                                                        <span class="badge  badge-warning"> Pending </span> 
                                                        @endif
                                                    </p>
                                                </td>
                                                <td>
                                                    <div class="">
                                                        @if(Auth::user()->hasRole('Admin'))
                                                            <a class="btn btn-sm btn-primary m-2" href="{{ route('admin.devis.downloadDevis', $d->devis_number) }}">
                                                                download
                                                            </a>
                                                            @can('Send devis')
                                                            <form action="{{ route('admin.devis.sendDevis') }}" method="post" class="d-inline ">
                                                                @csrf
                                                                <input type="hidden" name="devis_id" value="{{ $d->id }}">
                                                                <button type="submit" class="btn btn-sm btn-success m-2">Send</button>
                                                            </form>
                                                            @endcan 
                                                            @can('Delete devis')
                                                            <form action="{{route('admin.devis.destroy', $d->id)}}" method="post" onsubmit="return confirm('Are you sure?');"
                                                                class="d-inline ">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-sm btn-danger m-2">Delete</button>
                                                            </form>
                                                            @endcan
                                                            <a href="{{route('admin.devis.editDevis', $d->id)}}" class="btn btn-info btn-xs p-2 mb-0">Edit</a>
                                                        @endif
                                                        @if(Auth::user()->hasRole('Responsable de projet'))
                                                            <a class="btn btn-sm btn-primary m-2" href="{{ route('Responsable_de_projet.devis.downloadDevis', $d->devis_number) }}">
                                                                download
                                                            </a>
                                                            @can('Send devis')
                                                            <form action="{{ route('Responsable_de_projet.devis.sendDevis') }}" method="post" class="d-inline ">
                                                                @csrf
                                                                <input type="hidden" name="devis_id" value="{{ $d->id }}">
                                                                <button type="submit" class="btn btn-sm btn-success m-2">Send</button>
                                                            </form>
                                                            @endcan
                                                            @can('Delete devis')
                                                            <form action="{{route('Responsable_de_projet.devis.destroy', $d->id)}}" method="post" onsubmit="return confirm('Are you sure?');"
                                                                class="d-inline ">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-sm btn-danger m-2">Delete</button>
                                                            </form>
                                                            @endcan
                                                            <a href="{{route('Responsable_de_projet.devis.editDevis', $d->id)}}" class="btn btn-info btn-xs p-2 mb-0">Edit</a>
                                                        @endif
                                                        @if(Auth::user()->hasRole('Employee'))
                                                            <a class="btn btn-sm btn-primary m-2" href="{{ route('employee.devis.downloadDevis', $d->devis_number) }}">
                                                                download
                                                            </a>
                                                            @can('Send devis')
                                                            <form action="{{ route('employee.devis.sendDevis') }}" method="post" class="d-inline ">
                                                                @csrf
                                                                <input type="hidden" name="devis_id" value="{{ $d->id }}">
                                                                <button type="submit" class="btn btn-sm btn-success m-2">Send</button>
                                                            </form>
                                                            @endcan
                                                            @can('Delete devis')
                                                            <form action="{{route('employee.devis.destroy', $d->id)}}" method="post" onsubmit="return confirm('Are you sure?');"
                                                                class="d-inline ">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-sm btn-danger m-2">Delete</button>
                                                            </form>
                                                            @endcan
                                                            <a href="{{route('employee.devis.editDevis', $d->id)}}" class="btn btn-info btn-xs p-2 mb-0">Edit</a>
                                                        @endif
                                                        @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                                            <a class="btn btn-sm btn-primary m-2" href="{{ route('Responsable_gestionnaire.devis.downloadDevis', $d->devis_number) }}">
                                                                download
                                                            </a>
                                                            @can('Send devis')
                                                            <form action="{{ route('Responsable_gestionnaire.devis.sendDevis') }}" method="post" class="d-inline ">
                                                                @csrf
                                                                <input type="hidden" name="devis_id" value="{{ $d->id }}">
                                                                <button type="submit" class="btn btn-sm btn-success m-2">Send</button>
                                                            </form>
                                                            @endcan
                                                            @can('Delete devis')
                                                            <form action="{{route('Responsable_gestionnaire.devis.destroy', $d->id)}}" method="post" onsubmit="return confirm('Are you sure?');"
                                                                class="d-inline ">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-sm btn-danger m-2">Delete</button>
                                                            </form>
                                                            @endcan
                                                            <a href="{{route('Responsable_gestionnaire.devis.editDevis', $d->id)}}" class="btn btn-info btn-xs p-2 mb-0">Edit</a>
                                                        @endif
                                                    </div>
                                                </td>
                                                        
                                                    </div>
                                                </td>
                                            </tr>
                                            @endforeach



                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    @endcan
                <br>
                    @if(Auth::user()->hasRole('Admin') || Auth::user()->hasRole('Super Admin'))
                    <div class="col-12 col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex flex-column align-items-end ms-3"> <!-- Adjusted for alignment and spacing -->
                                    <div class="d-flex align-items-center mb-2"> <!-- Added margin-bottom for spacing -->
                                        <div class="input-group">
                                            @if(Auth::user()->hasRole('Admin'))
                                                <form action="{{ route('admin.projects.uploadFile', $project->id) }}" method="POST" enctype="multipart/form-data" class="d-flex">
                                                    @csrf
                                                    <input type="file" name="file" required class="form-control" style="width: 268px; height: 40px; border-block-color: black;">
                                                    <button type="submit" class="btn btn-primary btn-sm ms-2">Upload</button> <!-- Made button smaller -->
                                                </form>
                                            @endif
                                            <div class="ms-3"> <!-- Added margin-left for spacing -->
                                            @if(Auth::user()->hasRole('Responsable de projet'))
                                                <form action="{{ route('Responsable_de_projet.projects.uploadFile', $project->id) }}" method="POST" enctype="multipart/form-data" class="d-flex">
                                                    @csrf
                                                    <input type="file" name="file" required class="form-control" style="width: 268px; height: 40px; border-block-color: black;">
                                                    <button type="submit" class="btn btn-primary btn-sm ms-2">Upload</button> <!-- Made button smaller -->
                                                </form>
                                            @endif
                                            </div>
                                            <div class="ms-3"> <!-- Added margin-left for spacing -->
                                            @if(Auth::user()->hasRole('Employee'))
                                                <form action="{{ route('employee.projects.uploadFile', $project->id) }}" method="POST" enctype="multipart/form-data" class="d-flex">
                                                    @csrf
                                                    <input type="file" name="file" required class="form-control" style="width: 268px; height: 40px; border-block-color: black;">
                                                    <button type="submit" class="btn btn-primary btn-sm ms-2">Upload</button> <!-- Made button smaller -->
                                                </form>
                                            @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table align-items-center mb-0">
                                        <thead>
                                            <tr>
                                                <th
                                                    class="text-center text-uppercase text-secondary text-x font-weight-bolder opacity-10">
                                                    File Name
                                                </th>
                                                <th
                                                    class="text-center text-uppercase text-secondary text-x font-weight-bolder opacity-10">
                                                    Uploaded at
                                                </th>
                                                <th
                                                    class="text-center text-uppercase text-secondary text-x font-weight-bolder opacity-10 ps-2">
                                                    Action
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($Project_files as $file)
                                            <tr>
                                                <td>
                                                    <p class="align-middle text-center text-sm font-weight-bold mb-0">
                                                        @if(Auth::user()->hasRole('Responsable de projet'))
                                                            <a href="{{ route('Responsable_de_projet.projects.downloadfiles', $file->file_name) }}">{{$file->file_name}}
                                                            </a>
                                                        @endif
                                                        @if(Auth::user()->hasRole('Admin'))
                                                            <a href="{{ route('admin.projects.downloadfiles', $file->file_name) }}">{{$file->file_name}}
                                                            </a>
                                                        @endif
                                                        @if(Auth::user()->hasRole('Employee'))
                                                            <a href="{{ route('employee.projects.downloadfiles', $file->file_name) }}">{{$file->file_name}}
                                                            </a>
                                                        @endif
                                                    </p>
                                                </td>
                                                <td>
                                                    <p class="align-middle text-center text-sm font-weight-bold mb-0">
                                                        {{$file->created_at}}
                                                    </p>
                                                </td>
                                                <td>
                                                    <div class="">
                                                        @if(Auth::user()->hasRole('Admin'))
                                                            <a href="{{ route('admin.projects.downloadfiles', $file->file_name) }}" class="btn btn-primary btn-xs p-2 mb-0" >
                                                                Download
                                                            </a>
                                                        @endif
                                                        @if(Auth::user()->hasRole('Responsable de projet'))
                                                            <a href="{{ route('Responsable_de_projet.projects.downloadfiles', $file->file_name) }}" class="btn btn-primary btn-xs p-2 mb-0" >
                                                                Download
                                                            </a>
                                                        @endif
                                                        @if(Auth::user()->hasRole('Employee'))
                                                            <a href="{{ route('employee.projects.downloadfiles', $file->file_name) }}" class="btn btn-primary btn-xs p-2 mb-0" >
                                                                Download
                                                            </a>
                                                        @endif
                                                    
                                                    <!-- Delete -->
                                                    @if(Auth::user()->hasRole('Admin'))
                                                        <form action="{{ route('admin.projects.deletefiles', $file->file_name) }}" method="post" class="d-inline" onsubmit="return confirm('Are you sure?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-danger btn-xs p-2 mb-0">Delete</button>
                                                        </form>
                                                    @endif
                                                    @if(Auth::user()->hasRole('Responsable de projet'))
                                                        <form action="{{ route('Responsable_de_projet.projects.deletefiles', $file->file_name) }}" method="post" class="d-inline" onsubmit="return confirm('Are you sure?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-danger btn-xs p-2 mb-0">Delete</button>
                                                        </form>
                                                    @endif
                                                    @if(Auth::user()->hasRole('Employee'))
                                                        <form action="{{ route('employee.projects.deletefiles', $file->file_name) }}" method="post" class="d-inline" onsubmit="return confirm('Are you sure?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-danger btn-xs p-2 mb-0">Delete</button>
                                                        </form>
                                                    @endif
                                                    </div>
                                                </td>

                                            </tr>
                                            @endforeach



                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                <br>

                @php
                    $project_contacts=App\Models\client_contacts_projects::where('project_id',$project->id)->get();
                    $contactIds = $project_contacts->pluck('contact_id');
                    $contacts = App\Models\client_contacts::whereIn('id', $contactIds)->get();
                    $i=1;
                @endphp

               @can('view contacts')
                <div class="row">
                    <div class="card p-3 border-radius-xl bg-white mb-3 ">
                        <h5 class="font-weight-bolder mb-0">Contacts Infos</h5>
                        <p class="mb-0 text-sm">Mandatory informations</p>
                        <br>
                        
                            <div id="accordion">
                                @foreach ($contacts as $contact)
                                
                                    <div class="card">
                                        <h5 class="">
                                            <button class="btn btn-link" style='color:black;' type="button" data-toggle="collapse" data-target="#collapse{{$i}}" aria-expanded="true" aria-controls="collapse{{$i}}">
                                            <i class="fa-solid fa-address-book fa-lg p-4"></i>
                                            {{ ucfirst($contact->name) }} <span style=" font-family: 'Courier New', Courier, monospace;">Contact  Infos</span>
                                            </button>
                                            <button  class="btn btn-link ml-auto arrow-button" type="button" data-toggle="collapse" data-target="#collapse{{$i}}" aria-expanded="true" aria-controls="collapse{{$i}}">
                                                <i class="fas fa-chevron-down arrow"></i>
                                            </button>
                                        </h5>

                                        <div id="collapse{{$i}}" class="collapse" aria-labelledby="heading{{$i}}" data-parent="#accordion">
                                            <div class="card-body">
                                                <div class="row mt-3">
                                                    <div class="col-12 col-sm-6">
                                                        <label>Full Name : </label>
                                                        <input class="multisteps-form__input form-control editable-contact"  data-id="{{$contact->id}}" data-column="name" type="text" value="{{$contact->name}}" name="name" readonly  />
                                                    </div>
                                                    <div class="col-12 col-sm-6">
                                                        <label>Functionality : </label>
                                                        <input class="multisteps-form__input form-control editable-contact"  data-id="{{$contact->id}}" data-column="functionality" type="text" value="{{$contact->functionality}}" name="functionality" readonly  />
                                                    </div>
                                                    <div class="col-12 col-sm-6">
                                                        <label>Mobile Fix : </label>
                                                        <input class="multisteps-form__input form-control editable-contact"  data-id="{{$contact->id}}" data-column="mobile_fix" type="number" value="{{$contact->mobile_fix}}" name="mobile_fix" readonly  />
                                                    </div>
                                                    <div class="col-12 col-sm-6">
                                                        <label>Email : </label>
                                                        <input class="multisteps-form__input form-control editable-contact"  data-id="{{$contact->id}}" data-column="email" type="text" value="{{$contact->email}}" name="email" readonly  />
                                                    </div>
                                                    <div class="col-12 col-sm-6">
                                                        <label>Address : </label>
                                                        <input class="multisteps-form__input form-control editable-contact"  data-id="{{$contact->id}}" data-column="address" type="text" value="{{$contact->address}}" name="address" readonly />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <br>
                                    </div>
                                
                                    @php
                                        $i++;
                                    @endphp
                                @endforeach
                            </div>
                    </div>
                </div>
                @endcan
                <br>
                <div class="col-12 col-lg-12">
                    <div class="row mb-3">
                        @php
                            $teams=App\Models\project_teams::where('project_id', $project->id)->get();
                        @endphp
                    @foreach($teams as $team)
                    <div class="col-lg-4 col-md-6 mb-3 mb-lg-0">
                        <div class="card h-100">
                            <div class="card-header d-flex flex-column align-items-start">
                                <div class="d-flex justify-content-between w-100">
                                    <h5 class="mb-0 text-capitalize">{{$team->team_name}}</h5>
                                    @if(Auth::user()->hasRole('Admin'))
                                    <a href="{{route('admin.teams.index')}}" class='btn btn-sm btn-primary btn-sm'>
                                        View
                                    </a>
                                    @endif
                                    @if(Auth::user()->hasRole('Responsable de projet'))
                                    <a href="{{route('Responsable_de_projet.teams.index')}}" class='btn btn-sm btn-primary btn-sm'>
                                        View
                                    </a>
                                    @endif
                                    @if(Auth::user()->hasRole('Employee'))
                                    <a href="{{route('employee.teams.index')}}" class='btn btn-sm btn-primary btn-sm'>
                                        View
                                    </a>
                                    @endif
                                    @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                    <a href="{{route('Responsable_gestionnaire.teams.index')}}" class='btn btn-sm btn-primary btn-sm'>
                                        View
                                    </a>
                                    @endif
                                </div>
                                <span class="font-weight-bold" style="font-size: 0.8em; padding-left: 10px;">Team Members :</span>
                            </div>

                            <div class="card-body pt-0">
                                <ul class="list-group list-group-flush">
                                    @foreach(App\Models\team_members::where('team_id', $team->team_id)->get() as $team_member)
                                    <li class="list-group-item px-0">
                                        <div class="row align-items-center">
                                            <div class="col-auto d-flex align-items-center">
                                                @php
                                                    $colors = ['#2ECC71', '#BDC3C7', '#F39C12', '#E74C3C', '#9B59B6',
                                                    '#2E86C1','#641E16','#2C3E50','#F4D03F','#F4D03F'];
                                                @endphp
                                                @if(App\Models\Employees::where('id', $team_member->employee_id)->first()->image)
                                                <img class="avatar avatar-sm rounded-circle me-2" 
                                                    src="{{ Storage::disk('do_spaces')->url('employee_images/' . App\Models\Employees::where('id', $team_member->employee_id)->first()->image) }}" 
                                                    alt="employee Image">                                                
                                                    @else
                                                <div class="avatar avatar-sm rounded-circle me-2"
                                                    style="background-color: {{ $colors[$loop->index % count($colors)] }};">
                                                    <span class="initial-letter">{{ strtoupper(substr($team_member->employee_name,
                                                        0, 1)) }}</span>
                                                </div>
                                                @endif   
                                            </div>
                                            <div class="col ml-2">
                                                <h6 class="mb-0">
                                                @if(Auth::user()->hasRole('Admin'))
                                                    @if(Auth::user()->can('View employee'))
                                                        <a href="{{route('admin.employees.show', $team_member->employee_id)}}">{{$team_member->employee_name}}</a>
                                                    @else
                                                        {{$team_member->employee_name}}
                                                    @endif
                                                @endif
                                                @if(Auth::user()->hasRole('Responsable de projet'))
                                                    @if(Auth::user()->can('View employee'))
                                                        <a href="{{route('Responsable_de_projet.employees.show', $team_member->employee_id)}}">{{$team_member->employee_name}}</a>
                                                    @else
                                                        {{$team_member->employee_name}}
                                                    @endif
                                                @endif
                                                @if(Auth::user()->hasRole('Employee'))
                                                    @if(Auth::user()->can('View employee'))
                                                        <a href="{{route('employee.employees.show', $team_member->employee_id)}}">{{$team_member->employee_name}}</a>
                                                    @else
                                                        {{$team_member->employee_name}}
                                                    @endif
                                                @endif
                                                @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                                    @if(Auth::user()->can('View employee'))
                                                        <a href="{{route('Responsable_gestionnaire.employees.show', $team_member->employee_id)}}">{{$team_member->employee_name}}</a>
                                                    @else
                                                        {{$team_member->employee_name}}
                                                    @endif
                                                @endif
                                                </h6>
                                                @php
                                                    $employee = App\Models\Employees::where('id', $team_member->employee_id)->first();
                                                    $employee_user=App\Models\User::where('id', $employee->user_id)->first();
                                                @endphp
                                                @if($employee_user->is_loggedIn == 0)
                                                    <span class="badge badge-danger badge-sm">Off Work</span>
                                                @elseif($employee_user->is_loggedIn == 1)
                                                    <span class="badge badge-success badge-sm">On Work</span>
                                                @else
                                                    <span class="badge badge-warning badge-sm">On Pause</span>
                                                @endif
                                            </div>
                                            <div class="col-auto">
                                            @if(Auth::user()->hasRole('Admin'))
                                                @can('View employee')
                                                    <a href="{{route('admin.employees.show', $team_member->employee_id)}}"
                                                        type="button" class="btn btn-outline-primary btn-xs mb-0">
                                                        view
                                                    </a>
                                                @endcan
                                            @endif
                                            @if(Auth::user()->hasRole('Responsable de projet'))
                                                @can('View employee')
                                                    <a href="{{route('Responsable_de_projet.employees.show', $team_member->employee_id)}}"
                                                        type="button" class="btn btn-outline-primary btn-xs mb-0">
                                                        view
                                                    </a>
                                                @endcan
                                            @endif
                                            @if(Auth::user()->hasRole('Employee'))
                                                @can('View employee')
                                                    <a href="{{route('employee.employees.show', $team_member->employee_id)}}"
                                                        type="button" class="btn btn-outline-primary btn-xs mb-0">
                                                        view
                                                    </a>
                                                @endcan
                                            @endif  
                                            @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                                @can('View employee')
                                                    <a href="{{route('Responsable_gestionnaire.employees.show', $team_member->employee_id)}}"
                                                        type="button" class="btn btn-outline-primary btn-xs mb-0">
                                                        view
                                                    </a>
                                                @endcan
                                            @endif
                                            </div>
                                        </div>
                                    </li>
                                    @endforeach

                                </ul>
                            </div>
                        </div>
                    </div>
                    @if ($loop->iteration % 3 == 0 && !$loop->last)
                    <div class="row mb-3">
                    </div>
                    @endif
                    @endforeach
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

<script>
$(document).ready(function(){
    $("#addContact").click(function(e){
        e.preventDefault();
        var additionalContacts = $("#contact_infos");
        var newCard = additionalContacts.children().first().clone();
        newCard.find('input').val('');
        if (additionalContacts.children().length > 0) {
            var deleteButton = $('<button>Delete</button>');
            deleteButton.attr('type', 'button');
            deleteButton.addClass('btn btn-danger mt-3 remove-contact');
            deleteButton.click(function() {
                newCard.remove();
            });
            newCard.append(deleteButton);
        }
        additionalContacts.append(newCard);
    });
});
$(document).ready(function(){
    $("#addContact").click(function(e){
        e.preventDefault();
        var newCard = '<div class="card p-3 border-radius-xl bg->...</div>';
        $("#additionalContacts").append(newCard);
    });
});

</script>


    <script type="text/javascript">
    $(document).ready(function () {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        

        document.getElementById('exportButton').addEventListener('click', function () {
            var events = calendar.getEvents().map(function (event) {
                return {
                    title: event.title,
                    start: event.start ? event.start.toISOString().slice(0, 10) : null,
                    end: event.end ? event.end.toISOString().slice(0, 10) : null,
                    backgroundColor: event.backgroundColor
                };
            });

            var ws = XLSX.utils.json_to_sheet(events);
            var wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, 'Events');

            var blob = XLSX.write(wb, {
                bookType: 'xlsx',
                type: 'blob'
            });

            var downloadLink = document.createElement('a');
            var objectURL = URL.createObjectURL(blob);
            downloadLink.href = objectURL;
            downloadLink.download = 'events.xlsx';
            downloadLink.click();
        });

        document.getElementById('addEventButton').addEventListener('click', function () {
            document.getElementById('addEventForm').style.display = 'block';
        });

        document.getElementById('submitEventButton').addEventListener('click', function () {
            var title = document.getElementById('eventTitle').value;
            var date = document.getElementById('eventDate').value;

            calendar.addEvent({
                title: title,
                start: date,
                allDay: true
            });

            document.getElementById('addEventForm').style.display = 'none';
            document.getElementById('eventTitle').value = '';
            document.getElementById('eventDate').value = '';
        });
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var sendInvoiceModal = document.getElementById('sendInvoiceModal');
        sendInvoiceModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var invoiceId = button.getAttribute('data-invoice-id');
            var form = document.getElementById('sendInvoiceForm');
            
            // Set the correct form action URL based on user role
            @if(Auth::user()->hasRole('Admin'))
                form.action = '{{ route("admin.invoice.sendInvoice", ":invoice_id") }}'.replace(':invoice_id', invoiceId);
            @elseif(Auth::user()->hasRole('Responsable de projet'))
                form.action = '{{ route("Responsable_de_projet.invoice.sendInvoice", ":invoice_id") }}'.replace(':invoice_id', invoiceId);
            @elseif(Auth::user()->hasRole('Employee'))
                form.action = '{{ route("employee.invoice.sendInvoice", ":invoice_id") }}'.replace(':invoice_id', invoiceId);
            @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                form.action = '{{ route("Responsable_gestionnaire.invoice.sendInvoice", ":invoice_id") }}'.replace(':invoice_id', invoiceId);
            @endif

            // Fetch recipient information
            fetch(`/admin/invoice/${invoiceId}/recipients`)
                .then(response => response.json())
                .then(data => {
                    const recipientsList = document.getElementById('recipientsList');
                    recipientsList.innerHTML = '';
                    
                    if (data.recipients && data.recipients.length > 0) {
                        data.recipients.forEach(recipient => {
                            const recipientDiv = document.createElement('div');
                            recipientDiv.className = 'mb-2 d-flex align-items-center';
                            recipientDiv.innerHTML = `
                                <div class="form-check me-2">
                                    <input class="form-check-input recipient-checkbox" type="checkbox" 
                                        value="${recipient.email}" id="recipient_${recipient.id}" 
                                        name="selected_recipients[]" ${recipient.is_comptable === 1 ? 'checked' : ''}>
                                    <label class="form-check-label" for="recipient_${recipient.id}">
                                        <strong>${recipient.name}</strong> (${recipient.email})
                                    </label>
                                </div>
                            `;
                            recipientsList.appendChild(recipientDiv);
                        });

                        // Add select all functionality
                        const selectAllDiv = document.createElement('div');
                        selectAllDiv.className = 'mb-2';
                        selectAllDiv.innerHTML = `
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="selectAllRecipients">
                                <label class="form-check-label" for="selectAllRecipients">
                                    <strong>Select All</strong>
                                </label>
                            </div>
                        `;
                        recipientsList.insertBefore(selectAllDiv, recipientsList.firstChild);

                        // Add select all event listener
                        document.getElementById('selectAllRecipients').addEventListener('change', function() {
                            const checkboxes = document.querySelectorAll('.recipient-checkbox');
                            checkboxes.forEach(checkbox => {
                                checkbox.checked = this.checked;
                            });
                        });

                        // Set initial state of select all checkbox based on comptable recipients
                        const comptableRecipients = data.recipients.filter(r => r.is_comptable === 1);
                        const selectAllCheckbox = document.getElementById('selectAllRecipients');
                        selectAllCheckbox.checked = comptableRecipients.length === data.recipients.length;
                    } else {
                        recipientsList.innerHTML = '<p class="text-muted">No recipients found</p>';
                    }
                })
                .catch(error => {
                    console.error('Error fetching recipients:', error);
                    document.getElementById('recipientsList').innerHTML = '<p class="text-danger">Error loading recipients</p>';
                });
        });

        // Add form submit handler to validate at least one recipient is selected
        document.getElementById('sendInvoiceForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const selectedRecipients = document.querySelectorAll('.recipient-checkbox:checked');
            if (selectedRecipients.length === 0) {
                alert('Please select at least one recipient');
                return;
            }

            // Create array of selected recipient emails
            const recipients = Array.from(selectedRecipients).map(checkbox => checkbox.value);
            
            // Create FormData object
            const formData = new FormData(this);
            
            // Add the encoded recipients array
            formData.append('selected_recipients', JSON.stringify(recipients));
            
            // Submit the form with the encoded data
            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.message || 'Error sending invoice');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error sending invoice');
            });
        });
    });
</script> 

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

<script>
// Function to show the partial credit note modal
function showPartialPopup(invoiceId, totalCost) {
  document.getElementById('invoiceId').value = invoiceId;
  document.getElementById('totalCost').value = totalCost;
  var modal = new bootstrap.Modal(document.getElementById('partialCreditNoteModal'));
  modal.show();
}

// Function to submit the partial credit note
function submitPartialCreditNote() {
  var form = document.getElementById('partialCreditNoteForm');
  var amount = parseFloat(document.getElementById('partialAmount').value);
  var invoiceId = document.getElementById('invoiceId').value;
  var totalCost = parseFloat(document.getElementById('totalCost').value);
  var errorMessage = document.getElementById('error-message');

  if (amount > totalCost) {
    errorMessage.style.display = 'block';
    return;
  } else {
    errorMessage.style.display = 'none';
  }

  // Send AJAX request to the controller
 // Send AJAX request to the controller
 @if(Auth::user()->hasRole('Responsable de projet'))
    var url = `{{ route('Responsable_de_projet.invoice.creditNote', '') }}/${invoiceId}?type=partial&amount=${amount}`;
  @elseif(Auth::user()->hasRole('Admin'))
    var url = `{{ route('admin.invoice.creditNote', '') }}/${invoiceId}?type=partial&amount=${amount}`;
  @elseif(Auth::user()->hasRole('Employee'))
    var url = `{{ route('employee.invoice.creditNote', '') }}/${invoiceId}?type=partial&amount=${amount}`;
  @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
    var url = `{{ route('Responsable_gestionnaire.invoice.creditNote', '') }}/${invoiceId}?type=partial&amount=${amount}`;
  @endif

  fetch(url, {
    method: 'GET',
    headers: {
      'X-Requested-With': 'XMLHttpRequest',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    }
  })
  .then(response => response.text())
  .then(html => {
    document.open();
    document.write(html);
    document.close();
  })
  .catch(error => {
    console.error('Error:', error);
    alert('An error occurred while processing your request: ' + error.message);
  });
}
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('taskSearchInput');
    
    searchInput.addEventListener('keyup', function() {
        const searchTerm = this.value.toLowerCase();
        const tableRows = document.querySelectorAll('table tbody tr');
        
        tableRows.forEach(row => {
            let isVisible = false;
            const taskTitle = row.querySelector('.task-name-cell h6')?.textContent || '';
            const taskDescription = row.querySelector('.description-cell p')?.textContent || '';
            
            if (taskTitle.toLowerCase().includes(searchTerm) || 
                taskDescription.toLowerCase().includes(searchTerm)) {
                isVisible = true;
            }
            
            row.style.display = isVisible ? '' : 'none';
        });
    });
});
</script>

</body>

</html>