<!DOCTYPE html>
<html lang="en">

@include('dashboard_layouts.head')
<style>
  a {
    color: blue;
}
th {
    color: black;
    font-weight: bold;
    font-size: 1.2em; /* Adjust the size as needed */
}
</style>
<body class="g-sidenav-show bg-gray-100">
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-NKDMSK6" height="0" width="0"
            style="display: none; visibility: hidden"></iframe></noscript>
    <div class="min-height-300 bg-primary position-absolute w-100"></div>

    

    <main class="main-content position-relative border-radius-lg">
        <nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl z-index-sticky"
            id="navbarBlur" data-scroll="false">
            <div class="container-fluid py-1 px-3">

                @include('dashboard_layouts.navbar')

            </div>

            <div class="container-fluid py-4">
                @include('employees.navinfos')

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table align-items-center mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-dark text-sm font-weight-bolder opacity-10">
                                            Task
                                        </th>
                                        <th class="text-uppercase text-dark text-sm font-weight-bolder opacity-10 ps-2">
                                            Project Name
                                        </th>
                                        <th class="text-uppercase text-dark text-sm font-weight-bolder opacity-10 ps-2">
                                            Teams
                                        </th>
                                        <th class="text-uppercase text-dark text-sm font-weight-bolder opacity-10 ps-2">
                                            Employees
                                        </th>
                                        <th class="text-uppercase text-dark text-sm font-weight-bolder opacity-10 ps-2">
                                            Estimated Hours
                                        </th>
                                        <th class="text-uppercase text-dark text-sm font-weight-bolder opacity-10 ps-2">
                                            Difference
                                        </th>
                                        <th class="text-uppercase text-dark text-sm font-weight-bolder opacity-10 ps-2">
                                            Status
                                        </th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($tasks as $task)
                                    <tr>
                                        <td>
                                            <div class="d-flex px-2">
                                                <div class="my-auto">
                                                    <h6 class="mb-0 text-sm">{{$task->title}}</h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <p class="text-sm font-weight-bold mb-0">
                                                <a href="{{route('admin.projects.show',App\Models\Project::where('name',$task->project_name)->first()->id )}}">
                                                    {{$task->project_name}}
                                                </a>
                                            </p>
                                        </td>
                                        <td>
                                            @foreach(App\Models\team_tasks::where('task_id', $task->id)->get() as $team_task)
                                            @php
                                            $team = App\Models\Teams::find($team_task->team_id);
                                            @endphp
                                            @if(count(App\Models\team_tasks::where('task_id', $task->id)->get()) > 0)
                                            <span class="badge badge-dot me-4">
                                                <i class="bg-info"></i>
                                                <a href="{{route('admin.teams.index')}}">
                                                    <span class="text-dark text-sm">{{$team->name}}</span> 
                                                </a>
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
                                                $employee_id = App\Models\employees_tasks::where('task_id', $task->id)->pluck('employee_id');
                                            @endphp
                                            @foreach(App\Models\Employees::whereIn('id', $employee_id)->get() as $employee)
                                            <span class="badge badge-dot me-4">
                                                <i class="bg-info"></i>
                                                <a href="{{route('admin.employees.show',$employee->id )}}">
                                                    <span class="text-dark text-sm">{{$employee->name}}</span>
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
                                            <p class="text-sm font-weight-bold mb-0">
                                                @if($task->estimated_hours !== null)
                                                    @php
                                                        $estimatedMinutes = ($task->estimated_hours ?? 0) * 60;
                                                        $consumedMinutes = ($task->consumed_hours ?? 0) * 60;
                                                        $remainingMinutes = $estimatedMinutes - $consumedMinutes;
                                                    @endphp
                                                    @if($remainingMinutes > $totalduration)
                                                        @php
                                                            $difference = $remainingMinutes - $totalduration;
                                                            $hours = floor($difference / 60);
                                                            $minutes = $difference % 60;
                                                        @endphp
                                                        <span style="color: #5ce65c;">+{{$hours}}h:{{$minutes}}m</span>
                                                    @else
                                                        @php
                                                            $difference = $totalduration - $remainingMinutes;
                                                            $hours = floor($difference / 60);
                                                            $minutes = $difference % 60;
                                                        @endphp
                                                        <span style="color: red;">-{{$hours}}h:{{$minutes}}m</span>
                                                    @endif
                                                @endif
                                            </p>
                                        </td>
                                        <td>
                                            <p class="text-sm font-weight-bold mb-0">
                                                @if($task->status==1)
                                                <span class="badge badge-sm badge-warning">Working on it</span>
                                                @elseif($task->status==2)
                                                <span class="badge badge-sm badge-success">Done</span>
                                                @else
                                                <span class="badge badge-sm badge-danger">Inactive</span>
                                                @endif
                                            </p>
                                        </td>
                                        <td class="align-middle">
                                            <button class="btn btn-link text-dark mb-0">
                                                <i class="fa fa-ellipsis-v text-sm" aria-hidden="true"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <br>

                <div class="row gx-4">

                    <!-- TASKS CHART CARD -->
                    <div class="col-lg-6">
                        <div class="card overflow-hidden">
                            <div class="card-header p-3 pb-0">
                                @if(session('message'))
                                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                                        {{ session('message') }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">X</button>
                                    </div>
                                @endif
                                <div class="d-flex align-items-center">
                                    <div class="icon icon-shape bg-gradient-info shadow text-center border-radius-md">
                                        <i class="ni ni-calendar-grid-58 text-lg opacity-10" aria-hidden="true"></i>
                                    </div>

                                    <div class="ms-3">
                                        <p class="text-sm mb-0 text-capitalize font-weight-bold">
                                            Tasks
                                        </p>
                                        <h5 class="font-weight-bolder mb-0">{{count($tasks)}}</h5>
                                    </div>
                                    <div class="progress-wrapper ms-auto w-25">
                                        <div class="progress-info">
                                            <div class="progress-percentage">
                                                <span class="text-sm font-weight-bold">60%</span>
                                            </div>
                                        </div>
                                        <div class="progress">
                                            <div class="progress-bar bg-gradient-info w-60" role="progressbar"
                                                aria-valuenow="60" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body mt-3 p-0">
                                <div class="chart">
                                    <canvas id="chart-line" class="chart-canvas" height="100"></canvas>
                                </div>
                            </div>
                        </div>
                        <br>

                        <!-- TO DO LISTE CARD -->
                        <div class="card">
                            <div class="card-header p-3">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6 class="mb-0">Tasks list</h6>
                                    </div>
                                    <div class="col-md-6 d-flex justify-content-end align-items-center">
                                        <small>{{ date('d - M Y') }}</small>
                                    </div>
                                </div>
                                <hr class="horizontal dark mb-0" />
                            </div>
                            <div class="card-body p-3 pt-0">
                                <ul class="list-group list-group-flush" data-toggle="checklist">
                                    @foreach ($tasks as $task)
                                    <li class="list-group-item border-0 flex-column align-items-start ps-0 py-0 mb-3">
                                        <div class="checklist-item checklist-item-primary ps-2 ms-3">
                                            <div class="d-flex align-items-center">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" value
                                                        id="flexCheckDefault" />
                                                </div>
                                                <h6 class="mb-0 text-dark font-weight-bold text-sm">
                                                    {{$task->title}}
                                                </h6>
                                                <div class="dropdown float-lg-end ms-auto pe-4">
                                                    <a href="javascript:;" class="cursor-pointer" id="dropdownTable2"
                                                        data-bs-toggle="dropdown" aria-expanded="false">
                                                        <i class="fa fa-ellipsis-h text-secondary"
                                                            aria-hidden="true"></i>
                                                    </a>
                                                    <ul class="dropdown-menu px-2 py-3 ms-sm-n4 ms-n5"
                                                        aria-labelledby="dropdownTable2" style>
                                                        <li>
                                                            <a class="dropdown-item border-radius-md"
                                                                href="javascript:;">Edit</a>
                                                        </li>
                                                        <li>
                                                            @php
                                                            $status = 2;
                                                            @endphp
                                                            <form
                                                                action="{{ route('employee.tasks.status', ['task' => $task->id, 'status' => $status])}}"
                                                                method="post"
                                                                onsubmit="return confirm('Are you sure?');"
                                                                class="d-inline">
                                                                @csrf
                                                                <button type="submit"
                                                                    class="dropdown-item border-radius-md">Mark as
                                                                    Done</button>
                                                            </form>

                                                        </li>
                                                        <li>
                                                            @php
                                                            $status = 1;
                                                            @endphp
                                                            <form
                                                                action="{{ route('employee.tasks.status', ['task' => $task->id, 'status' => $status])}}"
                                                                method="post"
                                                                onsubmit="return confirm('Are you sure?');"
                                                                class="d-inline">
                                                                @csrf
                                                                <button type="submit"
                                                                    class="dropdown-item border-radius-md">Working on
                                                                    it</button>
                                                            </form>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                            <div class="d-flex align-items-center ms-4 mt-3 ps-1">
                                                <div>
                                                    <p class="text-sm mb-0 text-secondary font-weight-bold">
                                                        Start Date
                                                    </p>
                                                    <span class="text-sm font-weight-bolder">{{$task->start}}</span>
                                                </div>
                                                <div class="ms-auto">
                                                    <p class="text-sm mb-0 text-secondary font-weight-bold">
                                                        End Date
                                                    </p>
                                                    <span class="text-sm font-weight-bolder">{{$task->end}}</span>
                                                </div>
                                                <div class="mx-auto">
                                                    <p class="text-sm mb-0 text-secondary font-weight-bold">
                                                        Project Name
                                                    </p>
                                                    <span
                                                        class="text-sm font-weight-bolder">{{$task->project_name}}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <hr class="horizontal dark mt-4 mb-0" />
                                    </li>
                                    @endforeach

                                </ul>
                            </div>
                        </div>

                    </div>

                    <!-- TIME LINE CARD -->
                    <div class="col-lg-6 mt-4 mt-lg-0">
                        <div class="card bg-gradient-dark">
                            <div class="card-header bg-transparent pb-0">
                                <h6 class="text-white">Tasks Timeline </h6>
                            </div>
                            <div class="card-body p-3">
                                <div class="timeline timeline-one-side" data-timeline-axis-style="dashed">
                                    @foreach ($tasks_ordered as $task)
                                    <div class="timeline-block mb-3">
                                        <span class="timeline-step bg-dark">
                                            <i class="ni ni-bell-55 text-success text-gradient"></i>
                                        </span>
                                        <div class="timeline-content">
                                            <div class="d-flex justify-content-between">
                                                <h6 class="text-white text-sm font-weight-bold mb-0">
                                                    {{$task->title}}
                                                </h6>
                                                @if($task->status==1)
                                                <span class="badge badge-sm badge-warning">Working on it</span>
                                                @elseif($task->status==2)
                                                <span class="badge badge-sm badge-success">Done</span>
                                                @else
                                                <span class="badge badge-sm badge-danger">Inactive</span>
                                                @endif
                                            </div>
                                            <p class="text-secondary text-sm mt-3 mb-2">
                                                {{$task->description}}
                                            </p>
                                            <form method="post" action="{{route('admin.tasks.destroy', $task->id)}}"
                                                onsubmit="return confirm('Are you sure?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm bg-gradient-danger">delete
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    
                </div>

                



    </main>



    @include('dashboard_layouts.scripts')


</body>

</html>
