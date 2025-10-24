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
</style>
<style>
.popup-card {
    display: none;
    position: fixed;
    z-index: 1;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgb(0,0,0);
    background-color: rgba(0,0,0,0.4);
}

.popup-content {
    background-color: #fefefe;
    margin: 15% auto;
    padding: 20px;
    border: 1px solid #888;
    width: 80%;
    max-width: 600px;
}

.close-btn {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
}

.close-btn:hover,
.close-btn:focus {
    color: black;
    text-decoration: none;
    cursor: pointer;
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
                                                teams
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
                                                Estimated Hours
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
                                                Action
                                            </th>

                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                                <div class="d-flex px-2">
                                                    <div class="my-auto">
                                                        <h6 class="mb-0 text-sm"><b>{{$task->title}}</b></h6>
                                                    </div>
                                                </div>
                                            </td>
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
                                            <td>
                                                <p class="text-sm font-weight-bold mb-0">
                                                    {{$task->description}}
                                                </p>
                                            </td>
                                            <td>
                                                @php
                                                $employee_id = App\Models\employees_tasks::where('task_id',
                                                $task->id)->pluck('employee_id');
                                                @endphp
                                                @foreach(App\Models\Employees::whereIn('id',
                                                $employee_id)->where('user_id', '!=',
                                                Illuminate\Support\Facades\Auth::user()->id)->get() as $employee)
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
                                                    <a
                                                        href="{{route('Responsable_de_projet.employees.show',$employee->id )}}">
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
                                                    <a
                                                        href="{{route('employee.employees.show',$employee->id )}}">
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
                                                        <a href="{{route('Responsable_gestionnaire.employees.show',$employee->id )}}">
                                                            <span class="text-dark text-sm">{{$employee->name}}</span>
                                                        </a>
                                                    @else
                                                        <span class="text-dark text-sm">{{$employee->name}}</span>
                                                    @endif
                                                @endif
                                                <br>
                                                @endforeach
                                            </td>

                                            <td>
                                                    @php
                                                        $employees = App\Models\Employees::whereIn('id', $employee_id)->where('user_id', '!=', Illuminate\Support\Facades\Auth::user()->id)->get();
                                                    @endphp
                                                    @foreach($employees as $employee)
                                                        @php
                                                            $employeeTaskDuration = App\Models\tasks_timer::where('task_id', $task->id)
                                                                ->where('employee_id', $employee->id)
                                                                ->sum('duration');
                                                            $taskhours = floor($employeeTaskDuration / 60);
                                                            $taskminutes = $employeeTaskDuration % 60;
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
                                                    {{$task->estimated_hours}}h
                                                </p>
                                            </td>
                                            <td>
                                                @php
                                                // Total duration across all employees for this task
                                                $overallTaskDuration = App\Models\tasks_timer::where('task_id', $task->id)->sum('duration');

                                                $taskdifference = ($task->estimated_hours * 60) - $overallTaskDuration; 
                                                //Convert estimated hours to minutes and subtract total duration
                                                $hours = floor(abs($taskdifference) / 60);
                                                $minutes = abs($taskdifference) % 60;

                                                $taskestimatedMinutes = $task->estimated_hours * 60;
                                                $taskprogressPercentage = $taskestimatedMinutes > 0 ?
                                                    ($overallTaskDuration / $taskestimatedMinutes) * 100 : 0;
                                                $taskformattedProgressPercentage = number_format($taskprogressPercentage, 2);
                                                @endphp
                                                <p class="text-sm font-weight-bold">
                                                    @if($task->estimated_hours !== null)


                                                    @if($taskdifference > 0)
                                                <p class="text-sm font-weight-bold mb-0 text-success">
                                                    +{{$hours}}h:{{$minutes}}m</p>
                                                @elseif($taskdifference < 0) @php $hours=floor(abs($taskdifference) /
                                                    60); $minutes=abs($taskdifference) % 60; @endphp <p
                                                    class="text-sm font-weight-bold mb-0 text-danger">
                                                    -{{$hours}}h:{{$minutes}}m</p> 
                                                    @else
                                                    <span style="color: blue;">{{$hours}}h:{{$minutes}}m</span>
                                                    @endif
                                                    @endif
                                                    </p>
                                            </td>
                                            <td class="" data-id="" data-column="">
                                                <div class="progress"
                                                    style="height: 12px; background-color: #e9ecef; border-radius: 0.25rem;">
                                                    <div class="progress-bar 
                                                            @if($taskprogressPercentage < 50) bg-success 
                                                            @elseif($taskprogressPercentage < 75) bg-warning 
                                                            @else bg-danger 
                                                            @endif" role="progressbar"
                                                        style="width: {{$taskformattedProgressPercentage}}%; font-size: 10px; display: flex; align-items: center; justify-content: center; overflow: hidden; color: #fff; text-align: center; white-space: nowrap; transition: width 0.6s ease;"
                                                        aria-valuenow="{{$taskformattedProgressPercentage}}"
                                                        aria-valuemin="0" aria-valuemax="100">
                                                        {{$taskformattedProgressPercentage}}%
                                                    </div>
                                                </div>
                                            </td>
                                            @if(Auth::user()->hasRole('Admin'))
                                            <td>
                                                @can('Update task')
                                                <a href="{{ route('admin.tasks.edit', $task->id) }}"
                                                    class="btn btn-sm btn-primary p-2 m-2">Edit</a>
                                                @endcan
                                                @can('Delete task')
                                                <form method="post"
                                                    action="{{ route('admin.tasks.destroy', $task->id) }}"
                                                    onsubmit="return confirm('Are you sure?');"
                                                    style="display: inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="btn btn-sm btn-danger p-2 m-2">X</button>
                                                </form>
                                                @endcan
                                            </td>
                                            @endif
                                            @if(Auth::user()->hasRole('Responsable de projet'))
                                            @can('Update task')
                                            <td>
                                                <a href="{{ route('Responsable_de_projet.tasks.edit', $task->id) }}" 
                                                    class="btn btn-sm btn-primary p-2 m-2">Edit</a>
                                            </td>
                                            @endcan
                                            @can('Delete task')
                                            <form method="post"
                                                action="{{ route('Responsable_de_projet.tasks.destroy', $task->id) }}"
                                                onsubmit="return confirm('Are you sure?');" style="display: inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger p-2 m-2">X</button>
                                            </form>
                                            @endcan
                                            @endif
                                            @if(Auth::user()->hasRole('Employee'))
                                            @can('Update task')
                                            <td>
                                                <a href="{{ route('employee.tasks.edit', $task->id) }}"
                                                    class="btn btn-sm btn-primary p-2 m-2">Edit</a>
                                            </td>
                                            @endcan
                                            @can('Delete task')
                                            <form method="post"
                                                action="{{ route('employee.tasks.destroy', $task->id) }}"
                                                onsubmit="return confirm('Are you sure?');" style="display: inline;">
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


</body>

</html>