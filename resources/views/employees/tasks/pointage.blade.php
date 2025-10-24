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
</style>
<audio id="alertSound" src="{{ asset('audio/alert_sound.mp3') }}" preload="auto"></audio>
<meta name="csrf-token" content="{{ csrf_token() }}">

<body class="g-sidenav-show bg-gray-100">
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-NKDMSK6" height="0" width="0"
            style="display: none; visibility: hidden"></iframe></noscript>
    <div class="min-height-300 bg-primary position-absolute w-100"></div>

    <style>
    /* The Modal (background) */
    .modal {
        position: fixed;
        /* Stay in place */
        z-index: 1;
        /* Sit on top */
        left: 0;
        top: 0;
        width: 100%;
        /* Full width */
        height: 100%;
        /* Full height */
        overflow: auto;
        /* Enable scroll if needed */
        background-color: rgb(0, 0, 0);
        /* Fallback color */
        background-color: rgba(0, 0, 0, 0.4);
        /* Black w/ opacity */
        backdrop-filter: blur(8px);
    }

    /* Modal Content */
    .modal-content {
        background-color: #fefefe;
        margin: 15% auto;
        /* 15% from the top and centered */
        padding: 20px;
        border: 1px solid #888;
        width: 80%;
        /* Could be more or less, depending on screen size */
        text-align: center;
        /* Center align the text */
        position: relative;
        /* Needed for absolute positioning of the close button */
    }

    .modal-content p {
        color: red;
        /* Make the text color red */
        margin: 0;
        /* Remove default margin */
        font-weight: bold;
        /* Make the text bold */
    }

    /* The Close Button */
    .close-button {
        color: red;
        /* Change the close button color to red */
        position: absolute;
        /* Position it absolutely within its parent */
        top: 10px;
        /* 10px from the top */
        right: 10px;
        /* 10px from the right */
        font-size: 28px;
        font-weight: bold;
    }

    .close-button:hover,
    .close-button:focus {
        color: darkred;
        /* Darker red on hover/focus for better visibility */
        text-decoration: none;
        cursor: pointer;
    }

    .padding {
        padding-left: 100px !important;
    }
    </style>

    
        @include('employees.sidebar')
  

    <main class="main-content position-relative border-radius-lg">
        <nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl z-index-sticky"
            id="navbarBlur" data-scroll="false">
            <div class="container-fluid py-1 px-3">

                @include('dashboard_layouts.navbar')

            </div>

            <div class="container-fluid py-4">
                @include('employees.navinfos')

                <div class="row mt-3">
                    <div class="col-12">

                        <div class="card">
                            <div class="card-header p-3 pb-0">
                                @php
                                    $employee_id = App\Models\employees::where('user_id', Auth::user()->id)->first()->id;
                                    $today = \Carbon\Carbon::today()->toDateString();
                                    $totalDurationAllTasks = App\Models\tasks_timer::where('employee_id', $employee_id)
                                        ->whereDate('start_date', '=', $today)
                                        ->whereNotNull('duration')
                                        ->sum('duration');
                                    $totalHours = intdiv($totalDurationAllTasks, 60);
                                    $totalMinutes = $totalDurationAllTasks % 60;
                                    
                                    // Group tasks by project
                                    $tasksByProject = $employees_tasks->groupBy(function($task) {
                                        return App\Models\Tasks::where('id', $task->task_id)->first()->project_name;
                                    });
                                @endphp
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <h6>Employee Tasks Table</h6>
                                    <div>
                                        Todays Duration : <strong>{{ (isset($totalHours) && $totalHours > 0) || (isset($totalMinutes) && $totalMinutes > 0) ? $totalHours . 'h:' . sprintf('%02d', $totalMinutes) . 'm' : '00h:00m' }}</strong>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between mt-3">
                                    <div>
                                        <select id="projectFilter" class="form-select">
                                            <label for="">
                                                <b>Filter by Project</b>
                                            </label>
                                            <option value="">All Projects</option>
                                            @foreach($projects as $project)
                                                <option value="{{ $project->name }}">{{ $project->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                @if(session('alertmessage'))
                                <div class="alert alert-danger alert-dismissible fade show" style="color:white;" role="alert">
                                    {{ session('alertmessage') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">X</button>
                                </div>
                                @elseif(session('message'))
                                <div class="alert alert-success alert-dismissible fade show" style="color:white;" role="alert">
                                    {{ session('message') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">X</button>
                                </div>
                                @endif
                            </div>

                            <div class="table-responsive p-4 pt-1">
                                <table id="tasksTable" class="table table-flush" style="width:100%">
                                    <thead class="thead-light">
                                        <tr class="text-center">
                                            <th class="text-center text-uppercase text-secondary font-weight-bolder opacity-10">Task Name</th>
                                            <th class="text-center text-uppercase text-secondary font-weight-bolder opacity-10">Total Duration</th>
                                            <th class="text-center text-uppercase text-secondary font-weight-bolder opacity-10">Estimated Hours</th>
                                            <th class="text-center text-uppercase text-secondary font-weight-bolder opacity-10">Difference</th>
                                            <th class="text-center text-uppercase text-secondary font-weight-bolder opacity-10">Progress</th>
                                            <th class="text-center text-uppercase text-secondary font-weight-bolder opacity-10">timing</th>
                                            <th class="text-center text-uppercase text-secondary font-weight-bolder opacity-10 padding">Today's Duration</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($tasksByProject as $projectName => $projectTasks)
                                            <tr>
                                                <td colspan="7" class="bg-light">
                                                    <h6 class="font-weight-bolder mb-0 text-center">{{strtoupper($projectName)}}</h6>
                                                </td>
                                            </tr>
                                            
                                            @foreach($projectTasks as $task)
                                                @php
                                                    $task_id = $task->task_id;
                                                    $task_name = App\Models\Tasks::where('id', $task_id)->first();
                                                    $task_timer = App\Models\tasks_timer::where('task_employee_id', $task->id)
                                                        ->whereNull('end_date')
                                                        ->first();
                                                    $employee_id = App\Models\employees::where('user_id', Auth::user()->id)->first()->id;
                                                    $user = App\Models\User::where('id', Auth::user()->id)->first();
                                                @endphp
                                                <tr class="text-center">
                                                    <td>
                                                       <b>{{$task->task_name}}</b> 
                                                    </td>
                                                    <td>
                                                        @php 
                                                            $tasktotalduration = App\Models\tasks_timer::where('task_id', $task_id)
                                                                ->where('employee_id', $employee_id)
                                                                ->sum('duration');
                                                            $taskhours = floor($tasktotalduration / 60);
                                                            $taskminutes = $tasktotalduration % 60;
                                                        @endphp
                                                        {{$taskhours}}h:{{$taskminutes}}m
                                                    </td>
                                                    <td>{{$task_name->estimated_hours}}h</td>
                                                    <td>
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
                                                                <span class="text-success">+{{$hours}}h:{{$minutes}}m</span>
                                                            @elseif($taskdifference < 0)
                                                                <span class="text-danger">-{{$hours}}h:{{$minutes}}m</span>
                                                            @else
                                                                <span style="color: blue;">{{$hours}}h:{{$minutes}}m</span>
                                                            @endif
                                                        @else
                                                            <span>No estimated hours inserted</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div class="progress" style="height: 12px; background-color: #e9ecef; border-radius: 0.25rem;">
                                                            <div class="progress-bar @if($taskprogressPercentage < 50) bg-success @elseif($taskprogressPercentage < 75) bg-warning @else bg-danger @endif"
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
                                                        @if($task_timer)
                                                        <!--
                                                            @if(($task_timer->start_date != null && $task_timer->end_date == null)
                                                            && $task_timer->paused == 0 )
                                                            @if(Auth::user()->hasRole('Employee'))
                                                            <form class="d-inline"
                                                                action="{{ route('employee.tasks.pause', ['tasks' => $task->id, 'employee_id' => $employee_id]) }}"
                                                                method="POST">
                                                            @endif
                                                            @if(Auth::user()->hasRole('Responsable de projet'))
                                                            <form class="d-inline"
                                                                action="{{ route('Responsable_de_projet.tasks.pause', ['tasks' => $task->id, 'employee_id' => $employee_id]) }}"
                                                                method="POST">
                                                            @endif
                                                            @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                                            <form class="d-inline"
                                                                action="{{ route('Responsable_gestionnaire.tasks.pause', ['tasks' => $task->id, 'employee_id' => $employee_id]) }}"
                                                                method="POST">
                                                            @endif
                                                                @csrf
                                                                <button class="btn btn-secondary pause-button" type="submit" id="pause-btn"
                                                                    data-task-id="{{$task->id}}">
                                                                    Pause
                                                                </button>
                                                            </form>
                                                            @endif
                                                        
                                                            @if(($task_timer->end_date == null && $task_timer->start_date != null )
                                                            && $task_timer->paused == 1)
                                                            @if(Auth::user()->hasRole('Employee'))
                                                            <form class="d-inline"
                                                                action="{{ route('employee.tasks.resume', ['tasks' => $task->id, 'employee_id' => $employee_id]) }}"
                                                                method="POST">
                                                            @endif
                                                            @if(Auth::user()->hasRole('Responsable de projet'))
                                                            <form class="d-inline"
                                                                action="{{ route('Responsable_de_projet.tasks.resume', ['tasks' => $task->id, 'employee_id' => $employee_id]) }}"
                                                                method="POST">
                                                            @endif
                                                            @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                                            <form class="d-inline"
                                                                action="{{ route('Responsable_gestionnaire.tasks.resume', ['tasks' => $task->id, 'employee_id' => $employee_id]) }}"
                                                                method="POST">
                                                            @endif
                                                                @csrf
                                                                <button class="btn btn-primary resume-button" type="submit" id="continue-btn"
                                                                    data-task-id="{{$task->id}}">
                                                                    Continue
                                                                </button>
                                                            </form>
                                                            @endif
                                                            -->
                                                            @else
                                                            @if(Auth::user()->hasRole('Employee'))
                                                            <form class="d-inline-flex align-items-center"
                                                                action="{{ route('employee.tasks.start', ['tasks' => $task->id, 'employee_id' => $employee_id]) }}"
                                                                method="POST">
                                                            @endif
                                                            @if(Auth::user()->hasRole('Responsable de projet'))
                                                            <form class="d-inline-flex align-items-center"
                                                                action="{{ route('Responsable_de_projet.tasks.start', ['tasks' => $task->id, 'employee_id' => $employee_id]) }}"
                                                                method="POST">
                                                            @endif
                                                            @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                                            <form class="d-inline-flex align-items-center"
                                                                action="{{ route('Responsable_gestionnaire.tasks.start', ['tasks' => $task->id, 'employee_id' => $employee_id]) }}"
                                                                method="POST">
                                                            @endif
                                                              @csrf
                                                              <input type="text" name="comment" id="comment" class="form-control mr-3" placeholder="Enter comment" style="margin-right: 10px; height: 38px;">
                                                              <button class="btn btn-success start-button" id="start-btn" type="submit" data-task-id="{{$task->id}}" style="height: 38px;">
                                                                  Start
                                                              </button>
                                                          </form>


                                                            @endif

                                                            @if(Auth::user()->hasRole('Employee'))  
                                                            <form class="d-inline stop-form"
                                                                action="{{ route('employee.tasks.stop', ['tasks' => $task->id, 'employee_id' => $employee_id]) }}"
                                                                method="POST">
                                                            @endif
                                                            @if(Auth::user()->hasRole('Responsable de projet'))
                                                            <form class="d-inline stop-form"
                                                                action="{{ route('Responsable_de_projet.tasks.stop', ['tasks' => $task->id, 'employee_id' => $employee_id]) }}"
                                                                method="POST">
                                                            @endif
                                                            @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                                            <form class="d-inline stop-form"
                                                                action="{{ route('Responsable_gestionnaire.tasks.stop', ['tasks' => $task->id, 'employee_id' => $employee_id]) }}"
                                                                method="POST">
                                                            @endif
                                                                @csrf
                                                                <button class="btn btn-danger stop-button" type="submit"
                                                                    data-task-id="{{$task->id}}">
                                                                    Stop
                                                                </button>
                                                            </form>
                                                        </td>
                                                        @php
                                                            $task_id2 = $task->task_id;
                                                            $task_name2 = App\Models\Tasks::where('id', $task_id2)->first();
                                                            $today = \Carbon\Carbon::today()->toDateString();


                                                            $totalDurationToday = App\Models\tasks_timer::where('task_employee_id',
                                                            $task->id)
                                                            ->whereNotNull('duration')
                                                            ->whereDate('start_date', '=', $today)
                                                            ->sum('duration');


                                                            $hours = intdiv($totalDurationToday, 60);
                                                            $minutes = $totalDurationToday % 60;
                                                        @endphp

                                                        <td>
                                                            <span id="duration-{{$task->id}}">
                                                                @if($totalDurationToday)
                                                                    {{ $hours . 'h:' . sprintf('%02d', $minutes).'m'}}
                                                                @else
                                                                    00h:00m
                                                                @endif
                                                            </span>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <br>

                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center p-3 pb-0">
                                    <h6>Tasks Timer Table</h6>
                                    <div>
                                        <button id="exportBtn" class="btn btn-primary">Export to Excel</button>
                                        <button id="exportPDFBtn" class="btn btn-primary">Export to PDF</button>
                                    </div>

                                </div>
                                <div class="row p-4">
                                    <div class="col-md-3">
                                        <input type="date" id="startDateInput" class="form-control">
                                    </div>
                                    <div class="col-md-3">
                                        <input type="date" id="endDateInput" class="form-control">
                                    </div>

                                    <div class="col-md-4 ml-auto mr-auto">
                                        <select id="projectFilter" class="form-control">
                                            <option value="">All Projects</option>
                                            @foreach($projects as $project)
                                            <option value="{{ $project->id }}">{{ $project->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="table-responsive p-4 pt-1">
                                    <table id="taskTable" class="table table-flush" style="width:100%">
                                        <thead class="thead-light">
                                            <tr class="text-center">
                                                <th class="text-center text-uppercase text-secondary font-weight-bolder opacity-10"> Project name</th>
                                                <th class="text-center text-uppercase text-secondary font-weight-bolder opacity-10"> task name</th>
                                                <th class="text-center text-uppercase text-secondary font-weight-bolder opacity-10"> start date</th>
                                                <th class="text-center text-uppercase text-secondary font-weight-bolder opacity-10"> end date</th>
                                                <th class="text-center text-uppercase text-secondary font-weight-bolder opacity-10"> Comment</th>
                                                <th class="text-center text-uppercase text-secondary font-weight-bolder opacity-10"> duration </th>
                                                <!--  <th class="text-center text-uppercase text-secondary text-sm font-weight-bolder opacity-10"> Cost </th> -->
                                            </tr>
                                        </thead>

                                        <tbody id="taskTableBody">
                                            <!-- Table rows will be dynamically added here -->

                                        </tbody>

                                    </table>
                                </div>

                            </div>

                            <br>

                            <div class="card  p-3 border-radius-xl bg-white mb-3" >
                                <div class="card-header p-3 pb-0">
                                    <h6>Work Days Repports</h6>
                                    
                                </div>
                                        
                                <div class="table-responsive"> 
                                    <table id="editableTable" class="table align-items-center mb-0">
                                        <thead>
                                            <tr>
                                                <th
                                                    class="text-uppercase text-dark  font-weight-bolder opacity-10  ps-1">
                                                    Nombre jours <br> travaillés 
                                                </th>
                                                <th
                                                    class="text-uppercase text-dark  font-weight-bolder opacity-10  ps-1">
                                                    Nombre jours <br> maladie
                                                </th>
                                                <th
                                                    class="text-uppercase text-dark  font-weight-bolder opacity-10  ps-1">
                                                    Nombre jours <br> fériés
                                                </th>
                                                <th
                                                    class="text-uppercase text-dark  font-weight-bolder opacity-10  ps-1"> 
                                                    Reilquat jours <br> congé <br> payé
                                                </th>
                                                <th
                                                    class="text-uppercase text-dark  font-weight-bolder opacity-10  ps-1"> 
                                                    Nombre jours <br> congé payé <br> acquis
                                                </th>
                                                <th
                                                    class="text-uppercase text-dark  font-weight-bolder opacity-10  ps-1"> 
                                                    Nombre jours <br> congé payé <br> pris
                                                </th>
                                                <th
                                                    class="text-uppercase text-dark  font-weight-bolder opacity-10  ps-1"> 
                                                    Solde <br> congés
                                                </th>
                                                <th
                                                    class="text-uppercase text-dark  font-weight-bolder opacity-10  ps-1"> 
                                                    Jours <br> d'absence
                                                </th>
                                                <th
                                                    class="text-uppercase text-dark  font-weight-bolder opacity-10  ps-1"> 
                                                    Dates des jours <br> congé payé
                                                </th>
                                                <th
                                                    class="text-uppercase text-dark  font-weight-bolder opacity-10  ps-1"> 
                                                    Mois
                                                </th>
                                                <th
                                                    class="text-uppercase text-dark  font-weight-bolder opacity-10  ps-1"> 
                                                    Annee
                                                </th>
                                                <th class="text-center text-uppercase text-secondary  font-weight-bolder opacity-10"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @if($work_days_repports)
                                            <tr >
                                                <td class="editable" data-id="{{$work_days_repports->id}}" data-column="days_worked" >
                                                @php
                                                    // Get the first and last day of the month
                                                    $firstDay = Carbon\Carbon::create($work_days_repports->year, $work_days_repports->month, 1);
                                                    $lastDay = $firstDay->copy()->endOfMonth();
                                                    
                                                    // Count Saturdays in the month
                                                    $saturdays = 0;
                                                    for($date = $firstDay; $date->lte($lastDay); $date->addDay()) {
                                                        if($date->isSaturday()) {
                                                            $saturdays++;
                                                        }
                                                    }
                                                    
                                                    // Add Saturdays to worked days
                                                    $totalDays = $work_days_repports->days_worked + $saturdays + $work_days_repports->days_to_recover;
                                                @endphp
                                                <p class="text-sm font-weight-bold mb-0">{{$totalDays}}</p>
                                                </td>
                                                <td class="editable" data-id="{{$work_days_repports->id}}" data-column="sick_days" >
                                                    <p class="text-sm font-weight-bold mb-0">{{$work_days_repports->sick_days}}</p>
                                                </td>
                                                <td class="editable" data-id="{{$work_days_repports->id}}" data-column="holidays" >
                                                    <p class="text-sm font-weight-bold mb-0">{{$work_days_repports->holidays}}</p>
                                                </td>
                                                <td class="editable" data-id="{{$work_days_repports->id}}" data-column="remaining_days_of_paid_leave" >
                                                    <p class="text-sm font-weight-bold mb-0">{{$work_days_repports->remaining_days_of_paid_leave}}</p>
                                                </td>
                                                <td class="editable" data-id="{{$work_days_repports->id}}" data-column="number_of_days_of_paid_leave_acquired" >
                                                    <p class="text-sm font-weight-bold mb-0">{{$work_days_repports->number_of_days_of_paid_leave_acquired}}</p>
                                                </td>
                                                <td class="editable" data-id="{{$work_days_repports->id}}" data-column="number_of_days_of_paid_leave_taken" >
                                                    <p class="text-sm font-weight-bold mb-0">{{$work_days_repports->number_of_days_of_paid_leave_taken}}</p>
                                                </td>
                                                <td class="editable" data-id="{{$work_days_repports->id}}" data-column="holiday_balance" >
                                                    <p class="text-sm font-weight-bold mb-0">{{$work_days_repports->holiday_balance}}</p>
                                                </td>
                                                <td class="editable" data-id="{{$work_days_repports->id}}" data-column="days_of_unpaid_absence" >
                                                    <p class="text-sm font-weight-bold mb-0">{{$work_days_repports->days_of_unpaid_absence}}</p>
                                                </td>
                                                <td class="editable" data-id="{{$work_days_repports->id}}" data-column="dates_of_holidays" >
                                                    <p class="text-sm font-weight-bold mb-0">{{$work_days_repports->dates_of_holidays}}</p>
                                                </td>
                                                <td class="editable" data-id="{{$work_days_repports->id}}" data-column="month" >
                                                    <p class="text-sm font-weight-bold mb-0">{{$work_days_repports->month}}</p>
                                                </td>
                                                <td class="editable" data-id="{{$work_days_repports->id}}" data-column="year" >
                                                    <p class="text-sm font-weight-bold mb-0">{{$work_days_repports->year}}</p>
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

                <div id="customAlertModal" class="modal" style="display:none; wdith : 800px">
                    <div class="modal-content">
                        <span class="close-button">&times;</span>
                        <p>Did you forget to launch your timer?</p>
                    </div>
                </div>

        </main>



        @include('dashboard_layouts.scripts')

        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.3.1/jspdf.umd.min.js"></script>
        <script src="https://unpkg.com/jspdf-autotable@3.5.17/dist/jspdf.plugin.autotable.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.4/xlsx.full.min.js"></script>

        <script>
        // Object to store interval IDs for each task
        var taskIntervals = {};
        // Object to track if a task is paused
        var taskPaused = {};
        
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize paused state based on the current task state
            @foreach($employees_tasks as $task)
                @php
                    $task_timer = App\Models\tasks_timer::where('task_employee_id', $task->id)
                        ->whereNull('end_date')
                        ->first();
                @endphp
                @if($task_timer && $task_timer->paused === 1)
                    taskPaused["{{$task->id}}"] = true;
                @else
                    taskPaused["{{$task->id}}"] = false;
                @endif
            @endforeach
            
            // Add click event listeners for pause and resume buttons
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
                    // Clear the interval when stop is clicked
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
                    a.download = 'pointageRepports.pdf';
                    document.body.appendChild(a); // we need to append the element to the dom -> otherwise it will not work in firefox
                    a.click();    
                    a.remove();  //afterwards we remove the element again         
                })
                .catch((error) => {
                    console.error('Error:', error);
                });
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
                    a.download = 'pointageRepports.pdf';
                    document.body.appendChild(a); // we need to append the element to the dom -> otherwise it will not work in firefox
                    a.click();    
                    a.remove();  //afterwards we remove the element again         
                })
                .catch((error) => {
                    console.error('Error:', error);
                });
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

        span.onclick = function() {
            modal.style.display = "none";
        }

        function showAlert() {
            modal.style.display = "block";
            alertSound.play();
        }

        function startAlertInterval() {
            clearInterval(alertInterval);
            alertInterval = setInterval(showAlert, 300000); // Show alert every 1 minute
        }

        function clearAlertInterval() {
            clearInterval(alertInterval);
        }

        // Get PHP variables
        var tasksTimer = @json($tasks_timer);

        function checkTimerState() {
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

                if (tasksTimer.pause_date !== null && tasksTimer.paused === 0) {
                    // If paused_date is not null and paused is 0, clear the interval
                    clearAlertInterval();
                }
            }
        }

        // Initial check
        checkTimerState();

        // Add event listeners to buttons
        document.getElementById('start-btn').addEventListener('click', function() {
            // You might want to update tasksTimer here based on the server response
            checkTimerState();
        });

        document.getElementById('pause-btn').addEventListener('click', function() {
            // You might want to update tasksTimer here based on the server response
            checkTimerState();
        });

        document.getElementById('continue-btn').addEventListener('click', function() {
            // You might want to update tasksTimer here based on the server response
            checkTimerState();
        });

        document.getElementById('stop-btn').addEventListener('click', function() {
            // You might want to update tasksTimer here based on the server response
            checkTimerState();
        });
    });
        </script>


        <script>
        document.addEventListener('DOMContentLoaded', function() {
            var today = new Date();
            var dd = String(today.getDate()).padStart(2, '0');
            var mm = String(today.getMonth() + 1).padStart(2, '0'); //January is 0!
            var yyyy = today.getFullYear();

            today = yyyy + '-' + mm + '-' + dd;
            document.getElementById('startDateInput').value = today;
        });
        </script>

        <script type="text/javascript">
            var employeeId = @json($employee_id); // Correctly inject employee_id
        </script>

        <script>

        document.addEventListener('DOMContentLoaded', function() {
            var startDateInput = document.getElementById('startDateInput');


            // Filter the table when the selected Projrct changes
            document.getElementById('projectFilter').addEventListener('change', function() {
                var selectedProjectName = this.options[this.selectedIndex].text;
                var tableRows = document.querySelectorAll('#taskTableBody tr');
                var total = 0; // Initialize total
                var totalRecette = 0;
                var totalDuration = 0; // Initialize totalDuration


                // Remove the total row and existing invoice button if they exist
                var totalRow = document.querySelector('.total-row');
                if (totalRow) {
                    totalRow.remove();
                }
                var existingInvoiceButton = document.querySelector('.invoice-button');
                if (existingInvoiceButton) {
                    existingInvoiceButton.remove();
                }

                tableRows.forEach(row => {
                    var projectCell = row.cells[
                    0]; // Assuming the project name is in the first column
                    if (selectedProjectName === "All Projects" || projectCell.textContent ===
                        selectedProjectName) {
                        row.style.display =
                        ''; // Show rows that match the selected project or if "All Projects" is selected
                        
                        var DurationValue = row.cells[5].textContent
                    .trim(); // Assuming the duration value is in the sixth column

                        // Parse duration string and calculate total duration in seconds
                        var durationInSeconds = parseDuration(DurationValue);
                        totalDuration += durationInSeconds / 3600;

                       
                    } else {
                        row.style.display =
                        'none'; // Hide rows that don't match the selected project
                    }
                });

                // After the forEach loop, add the total row to the table for both "All Projects" and specific projects
                var tableBody = document.getElementById('taskTableBody');
                var hours = Math.floor(totalDuration);
                var minutes = Math.round((totalDuration - hours) * 60);
                var formattedDuration = `${hours.toString().padStart(2, '0')}h:${minutes.toString().padStart(2, '0')}m`;
                var totalRow = `
                  <tr class="text-center total-row">
                    <td colspan="5"></td>
                    <td><strong>${formattedDuration}</strong></td>
                    
                  </tr>`;
                tableBody.insertAdjacentHTML('beforeend', totalRow);
            });


            // Fetch tasks for the current date when the page is loaded

            window.onload = function() {
                var currentDate = new Date().toISOString().split('T')[
                    0]; // Get the current date in YYYY-MM-DD format
                startDateInput.value = currentDate;
                var event = new Event('change');
                startDateInput.dispatchEvent(event);
            }




            // Fetch tasks for the selected date interval when the end date input changes
            endDateInput.addEventListener('change', function() {
                var startDateInput = document.getElementById('startDateInput');
                var startDate = startDateInput.value;
                var endDate = this.value;

                if (startDate && endDate) {
                    // Both start date and end date are selected, fetch tasks for the interval
                    console.log('From endDate input fetching tasks for Interval:', startDate,'To', endDate);
                    fetchTasksForInterval(startDate, endDate, employeeId); // Assume this function is defined
                } else if (endDate) {
                    // Only end date is selected, fetch tasks for the specific date
                    console.log('From endDate input fetching tasks for date:', startDate);
                    fetchTasksForDate(startDate, employeeId); // Assume this function is defined
                }
            });

            // Fetch tasks for the selected date when the date input changes
            startDateInput.addEventListener('change', function() {
                var endDateInput = document.getElementById('endDateInput');
                var startDate = this.value;
                var endDate = endDateInput.value;

                console.log(startDate, endDate);

                if (startDate && endDate) {
                    // Both start date and end date are selected, fetch tasks for the interval
                    console.log('From startDate input fetching tasks for Interval:', startDate,'To', endDate ,'for employee : ',employeeId);
                    fetchTasksForInterval(startDate, endDate, employeeId); // Assume this function is defined
                } else if (startDate) {
                    // Only start date is selected, fetch tasks for the specific date
                    console.log('From starDate input fetching tasks for date:', startDate, 'for employee : ',employeeId);
                    fetchTasksForDate(startDate, employeeId); // Assume this function is defined
                }
            });



            // Fetch tasks for the selected date interval
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
                            tableBody.innerHTML = ''; // Clear existing table rows

                            var totalCost = 0; // Initialize total cost
                            var totalRecette = 0;
                            var totalDuration = 0;

                            // Use Promise.all to wait for all fetch requests to complete
                            Promise.all(data.map(task => {
                                var startTime = new Date(task.start_date);
                                var endTime = task.end_date ? new Date(task.end_date) :
                                    new Date(); // Use current date if end_date is null
                                var duration = endTime - startTime; // Duration in milliseconds

                                // Calculate hours, minutes, and seconds
                                var hours = Math.floor(duration / (1000 * 60 * 60));
                                var minutes = Math.floor((duration % (1000 * 60 * 60)) / (1000 *
                                    60));
                                var seconds = Math.floor((duration % (1000 * 60)) / 1000);

                                // Format the duration as HH:MM:SS
                                var formattedDuration =
                                    `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;

                                var endDateText = task.end_date ? task.end_date : 'Still working';

                                @if(Auth::user()->hasRole('Employee'))
                                var fetchurl = '/employee/tasks/get-project-name/'
                                @endif
                                @if(Auth::user()->hasRole('Responsable de projet'))
                                var fetchurl = '/Responsable_de_projet/tasks/get-project-name/'
                                @endif
                                @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                var fetchurl = '/Responsable_gestionnaire/tasks/get-project-name/'
                                @endif
                                // Fetch the project name
                                return fetch(fetchurl + encodeURIComponent(
                                        task.task_id))
                                    .then(response => response.json())
                                    .then(projectData => {
                                        // Fetch the employee name
                                        return fetch('/employee/tasks/get-employee-name/' +
                                                encodeURIComponent(task.employee_id))
                                            .then(response => response.json())
                                            .then(employeeData => {

                                                var cost = 0;
                                                var rowId = 'row-' + task.id;

                                                let duration_hours = Math.floor(task
                                                    .duration / 60);
                                                let duration_minutes = task.duration % 60;
                                                let duration_seconds =
                                                    0; // Assuming there are no seconds in your duration

                                                let formattedDuration =
                                                    `${duration_hours.toString().padStart(2, '0')}h:${duration_minutes.toString().padStart(2, '0')}m:${duration_seconds.toString().padStart(2, '0')}s`;

                                                var row = `
                          <tr id="${rowId}" class="text-center">
                              <td>${projectData.name.toUpperCase().toUpperCase()}</td>                              
                              <td>${task.task_name}</td>                             
                              <td>${task.start_date}</td>
                              <td>${endDateText}</td>
                              <td>${task.comment ? task.comment : "No Comment"}</td>
                              <td class="task_duration">${task.end_date ? formattedDuration : ""}</td> <!-- Assign a class to the duration cell -->
                           
                          </tr>`;



                                                function getEmployeeShowUrl(id) {
                                                    return `/employee/employees/${id}`;
                                                }

                                                function getProjectShowUrl(id) {
                                                    return `/employee/projects/${id}`;
                                                }
                                                // Check if the task is still working
                                                if (task.end_date === null) {
                                    var startDate = new Date(task.start_date);
                                    var taskId = task.id;
                                    var rowId = 'row-' + taskId;
                                    
                                    // Initialize task as not paused
                                    taskPaused[taskId] = task.paused === 1;
                                    
                                    // Store the start timestamp to calculate elapsed time correctly
                                    var startTimestamp = startDate.getTime();
                                    var elapsedTime = 0;
                                    
                                    // Clear any existing interval for this task
                                    if(taskIntervals[taskId]) {
                                        clearInterval(taskIntervals[taskId]);
                                    }
                                    
                                    // Start an interval that runs every second
                                    taskIntervals[taskId] = setInterval(function() {
                                        // Only update if the task is not paused
                                        if(!taskPaused[taskId]) {
                                            var now = new Date();
                                            var durationInMilliseconds = now - startDate + elapsedTime;
                                            var hours = Math.floor(durationInMilliseconds / 3600000);
                                            var minutes = Math.floor((durationInMilliseconds % 3600000) / 60000);
                                            var seconds = Math.floor((durationInMilliseconds % 60000) / 1000);
                                            
                                            var durationFormatted = (hours < 10 ? '0' : '') + hours + 'h:' +
                                                                    (minutes < 10 ? '0' : '') + minutes + 'm:' +
                                                                    (seconds < 10 ? '0' : '') + seconds + 's';
                                                            
                                                            // Find the duration cell
                                                            var durationCell = document.querySelector('#' + rowId + ' .duration');
                                                            
                                                            // Check if the duration cell exists
                                                            if (durationCell) {
                                                                // Update the duration cell
                                                                durationCell.textContent = durationFormatted;
                                                            } else {
                                                                console.error('Element not found: #' + rowId + ' .duration');
                                                                clearInterval(taskIntervals[taskId]); // Stop the interval
                                                            }
                                                        }
                                                    }, 1000); // 1000 milliseconds = 1 second
                                }
                                                // Add the row to the table and get the added row element
                                                tableBody.insertAdjacentHTML('beforeend',
                                                    row);

                                                
                                                var rowElement = tableBody.lastElementChild;


                                                var DurationValue = rowElement.cells[5]
                                                    .textContent
                                                    .trim(); // Assuming the duration value is in the fifth column
                                                // Now you can access the cells of the rowElement
                                               

                                               
                                               
                                                // Parse duration string and calculate total duration in seconds
                                                var durationInSeconds = parseDuration(
                                                    DurationValue);
                                                totalDuration += durationInSeconds / 3600;
                                            });
                                    });
                            })).then(() => {
                                // After all fetch requests have completed, add the total cost row
                                var hours = Math.floor(totalDuration);
                                var minutes = Math.round((totalDuration - hours) * 60);
                                var formattedDuration = `${hours.toString().padStart(2, '0')}h:${minutes.toString().padStart(2, '0')}m`;
                                var totalRow = `
                    <tr class="text-center">
                            <td colspan="5"></td>
                            <td>    <strong> ${formattedDuration} </strong>   </td>
                            
                    </tr>`;
                                tableBody.insertAdjacentHTML('beforeend', totalRow);
                            });
                        } else {
                            console.error('Table body not found.');
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching data:', error);
                    });
            }

            // Fetch tasks for the selected date
            function fetchTasksForDate(selectedDate, employeeId) {
                let totalCost = 0;
                let totalRecette = 0;
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
                            tableBody.innerHTML = ''; // Clear existing table rows

                            // Use Promise.all to wait for all fetch requests to complete
                            Promise.all(data.map(task => {
                                var startTime = new Date(task.start_date);
                                var endTime = task.end_date ? new Date(task.end_date) :
                                    new Date(); // Use current date if end_date is null
                                var duration = endTime - startTime; // Duration in milliseconds

                                // Calculate hours, minutes, and seconds
                                var hours = Math.floor(duration / (1000 * 60 * 60));
                                var minutes = Math.floor((duration % (1000 * 60 * 60)) / (1000 *
                                    60));
                                var seconds = Math.floor((duration % (1000 * 60)) / 1000);

                                // Format the duration as HH:MM:SS
                                var formattedDuration =
                                    `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;

                                var endDateText = task.end_date ? task.end_date : 'Still working';

                                // Fetch the project name
                                @if(Auth::user()->hasRole('Employee'))
                                var fetchurl = '/employee/tasks/get-project-name/'
                                @endif
                                @if(Auth::user()->hasRole('Responsable de projet'))
                                var fetchurl = '/Responsable_de_projet/tasks/get-project-name/'
                                @endif
                                @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                var fetchurl = '/Responsable_gestionnaire/tasks/get-project-name/'
                                @endif
                                return fetch(fetchurl + encodeURIComponent(
                                        task.task_id))
                                    .then(response => response.json())
                                    .then(projectData => {
                                        // Fetch the employee name
                                        @if(Auth::user()->hasRole('Employee'))
                                        var fetchurl = '/employee/tasks/get-employee-name/'
                                        @endif
                                        @if(Auth::user()->hasRole('Responsable de projet'))
                                        var fetchurl = '/Responsable_de_projet/tasks/get-employee-name/'
                                        @endif
                                        @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                        var fetchurl = '/Responsable_gestionnaire/tasks/get-employee-name/'
                                        @endif
                                        return fetch(fetchurl + encodeURIComponent(task.employee_id))
                                            .then(response => response.json())
                                            .then(employeeData => {
                                                var cost = 0;
                                                var rowId = 'row-' + task
                                                    .id; // Assign a unique id to each row 

                                                let duration_hours = Math.floor(task
                                                    .duration / 60);
                                                let duration_minutes = task.duration % 60;
                                                let duration_seconds =
                                                    0; // Assuming there are no seconds in your duration

                                                let formattedDuration =
                                                    `${duration_hours.toString().padStart(2, '0')}h:${duration_minutes.toString().padStart(2, '0')}m:${duration_seconds.toString().padStart(2, '0')}s`;

                                                var row = `
                          <tr id="${rowId}" class="text-center">
                              <td>${projectData.name.toUpperCase()}</td>                              
                              <td>${task.task_name}</td>
                                               
                              <td>${task.start_date}</td>
                              <td>${endDateText}</td>
                              <td>${task.comment ? task.comment : "No Comment"}</td>
                              <td class="task_duration">${task.end_date ? formattedDuration : ""}</td>
                            
                          </tr>`;

                                                tableBody.insertAdjacentHTML('beforeend',
                                                    row);

                                                

                                                function getEmployeeShowUrl(id) {
                                                    return `/employee/employees/${id}`;
                                                }

                                                function getProjectShowUrl(id) {
                                                    return `/employee/projects/${id}`;
                                                }

                                                // Check if the task is still working
                                                if (task.end_date === null) {
                                                    var startDate = new Date(task.start_date);
                                                    var taskId = task.id;
                                    var rowId = 'row-' + taskId;
                                    
                                    // Initialize task as not paused
                                    taskPaused[taskId] = task.paused === 1;
                                    
                                    // Store the start timestamp to calculate elapsed time correctly
                                    var startTimestamp = startDate.getTime();
                                    var elapsedTime = 0;
                                    
                                    // Clear any existing interval for this task
                                    if(taskIntervals[taskId]) {
                                        clearInterval(taskIntervals[taskId]);
                                    }
                                    
                                    // Start an interval that runs every second
                                    taskIntervals[taskId] = setInterval(function() {
                                        // Only update if the task is not paused
                                        if(!taskPaused[taskId]) {
                                            var now = new Date();
                                            var durationInMilliseconds = now - startDate + elapsedTime;
                                            var hours = Math.floor(durationInMilliseconds / 3600000);
                                            var minutes = Math.floor((durationInMilliseconds % 3600000) / 60000);
                                            var seconds = Math.floor((durationInMilliseconds % 60000) / 1000);
                                            
                                            var durationFormatted = (hours < 10 ? '0' : '') + hours + 'h:' +
                                                                    (minutes < 10 ? '0' : '') + minutes + 'm:' +
                                                                    (seconds < 10 ? '0' : '') + seconds + 's';
                                                                
                                                                // Find the duration cell
                                                                var durationCell = document.querySelector('#' + rowId + ' .task_duration');
                                                                
                                                                // Check if the duration cell exists
                                                                if (durationCell) {
                                                                    // Update the duration cell
                                                                    durationCell.textContent = durationFormatted;
                                                                } else {
                                                                    console.error('Element not found: #' + rowId + ' .task_duration');
                                                                    clearInterval(taskIntervals[taskId]); // Stop the interval
                                                                }
                                                            }
                                                        }, 1000); // 1000 milliseconds = 1 second
                                                }
                                                var rowElement = tableBody.lastElementChild;
                                                var DurationValue = rowElement.cells[5]
                                                    .textContent
                                                    .trim(); // Assuming the duration value is in the fifth column



                                               
                                               
                                                // Parse duration string and calculate total duration in seconds
                                                var durationInSeconds = parseDuration(
                                                    DurationValue);
                                                totalDuration += durationInSeconds / 3600;
                                            });
                                    });
                            })).then(() => {
                                // After all fetch requests have completed, add the total cost row
                                var hours = Math.floor(totalDuration);
                                var minutes = Math.round((totalDuration - hours) * 60);
                                var formattedDuration = `${hours.toString().padStart(2, '0')}h:${minutes.toString().padStart(2, '0')}m`;
                                var totalRow = `
                    <tr class="text-center">
                        <td colspan="5"></td>
                        <td><strong>${formattedDuration}</strong></td>
                        
                    </tr>`;
                                tableBody.insertAdjacentHTML('beforeend', totalRow);
                            });
                        } else {
                            console.error('Table body not found.');
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching data:', error);
                    });
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
                    data: {
                        task_id: taskId,
                        employee_id: employeeId
                    },
                    success: function(response) {
                        // handle success
                    },
                    error: function(response) {
                        // handle error
                    }
                });
            });

            $('.stop-button').click(function() {
                var taskId = $(this).data('task-id');
                var employeeId = $(this).data('employee-id');

                $.ajax({
                    url: '/tasks/' + taskId + '/stop/' + employeeId,
                    type: 'POST',
                    data: {
                        task_id: taskId,
                        employee_id: employeeId
                    },
                    success: function(response) {
                        // handle success
                    },
                    error: function(response) {
                        // handle error
                    }
                });
            });
        });
        </script>
        <script>
            // Function to close the banner
            function closeBanner() {
                var closeButton = document.querySelector('#close-bar');
                if (closeButton) {
                    closeButton.click();
                }
            }

            // Call the function on page load
            window.onload = closeBanner;
        </script>
    <script>
        document.getElementById('projectFilter').addEventListener('change', function() {
            var filter = this.value.toLowerCase();
            var rows = document.querySelectorAll('#tasksTable tbody tr');
            var currentProject = null;
            var shouldShowCurrentProject = false;

            rows.forEach(function(row) {
                // Check if this is a project banner row (has colspan="7")
                if (row.cells.length === 1 && row.cells[0].colSpan === 7) {
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