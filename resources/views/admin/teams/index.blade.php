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
                        <h6>Teams Table</h6>
                        <div>
                        @if(Auth::user()->hasRole('Admin'))
                            @can('Add team')
                            <a type="button" class="btn btn-primary" href="{{route('admin.teams.create')}}">Add Team</a>
                            @endcan
                            <a type="button" class="btn btn-primary" href="{{route('admin.Teamssrepports.downloadRepports')}}">Teams Reports PDF</a>
                        @endif
                        @if(Auth::user()->hasRole('Responsable de projet'))
                            @can('Add team')
                            <a type="button" class="btn btn-primary" href="{{route('Responsable_de_projet.teams.create')}}">Add Team</a>
                            @endcan
                            <a type="button" class="btn btn-primary" href="{{route('Responsable_de_projet.Teamssrepports.downloadRepports')}}">Teams Reports PDF</a>
                        @endif
                        @if(Auth::user()->hasRole('Employee'))
                            @can('Add team')
                            <a type="button" class="btn btn-primary" href="{{route('employee.teams.create')}}">Add Team</a>
                            @endcan
                            <a type="button" class="btn btn-primary" href="{{route('employee.Teamssrepports.downloadRepports')}}">Teams Reports PDF</a>
                        @endif
                        @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                            @can('Add team')
                            <a type="button" class="btn btn-primary" href="{{route('Responsable_gestionnaire.teams.create')}}">Add Team</a>
                            @endcan
                            <a type="button" class="btn btn-primary" href="{{route('Responsable_gestionnaire.Teamssrepports.downloadRepports')}}">Teams Reports PDF</a>
                        @endif
                        </div>
                    </div>
                @if(session('message'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('message') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">X</button>
                    </div>
                  @endif
                        <div class="table-responsive">
                            <table class="table align-items-center mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-dark text-x font-weight-bolder opacity-10">
                                            Team
                                        </th>
                                        <th class="text-uppercase text-dark text-x font-weight-bolder opacity-10 ps-2">
                                            Projects
                                        </th>
                                        <th class="text-uppercase text-dark text-x font-weight-bolder opacity-10 ps-2">
                                            Tasks
                                        </th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($teams as $team)
                                    <tr>
                                        <td>
                                            <div class="d-flex px-2">
                                                @php
                                                $colors = ['#2ECC71', '#BDC3C7', '#F39C12', '#E74C3C', '#9B59B6',
                                                '#2E86C1','#641E16','#2C3E50','#F4D03F','#F4D03F'];
                                                @endphp
                                                <div class="avatar"
                                                    style="background-color: {{ $colors[$loop->index % count($colors)] }};">
                                                    <span class="initial-letter">{{ strtoupper(substr($team->name,
                                                        0, 1)) }}</span>
                                                </div>
                                                <div class="my-auto" style="margin-left: 10px;">
                                                    <h6 class="mb-0 text-sm">{{$team->name}}</h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @foreach($project_teams->where('team_id', $team->id) as $project)
                                            @if(Auth::user()->hasRole('Admin'))
                                                @if(Auth::user()->can('View project'))
                                                    <a href="{{route('admin.projects.show',App\Models\Project::where('id',$project->project_id)->first()->id)}}">
                                                    <span class="badge badge-dot me-4">
                                                    <i class="bg-info"></i>
                                                    <span class="text-dark text-sm">{{App\Models\Project::where('id',$project->project_id)->first()->name}}</span>
                                                    </span>
                                                    </a>
                                                @else
                                                    <span class="badge badge-dot me-4">
                                                    <i class="bg-info"></i>
                                                    <span class="text-dark text-sm">{{App\Models\Project::where('id',$project->project_id)->first()->name}}</span>
                                                    </span>
                                                @endif
                                            @endif
                                            @if(Auth::user()->hasRole('Responsable de projet'))
                                                @if(Auth::user()->can('View project'))
                                                    <a href="{{route('Responsable_de_projet.projects.show',App\Models\Project::where('id',$project->project_id)->first()->id)}}">
                                                    <span class="badge badge-dot me-4">
                                                    <i class="bg-info"></i>
                                                    <span class="text-dark text-sm">{{App\Models\Project::where('id',$project->project_id)->first()->name}}</span>
                                                    </span>
                                                    </a>
                                                @else
                                                    <span class="badge badge-dot me-4">
                                                    <i class="bg-info"></i>
                                                    <span class="text-dark text-sm">{{App\Models\Project::where('id',$project->project_id)->first()->name}}</span>
                                                    </span>
                                                @endif
                                            @endif
                                            @if(Auth::user()->hasRole('Employee'))
                                                @if(Auth::user()->can('View project'))
                                                    <a href="{{route('employee.projects.show',App\Models\Project::where('id',$project->project_id)->first()->id)}}">
                                                    <span class="badge badge-dot me-4">
                                                    <i class="bg-info"></i>
                                                    <span class="text-dark text-sm">{{App\Models\Project::where('id',$project->project_id)->first()->name}}</span>
                                                    </span>
                                                    </a>
                                                @else
                                                    <span class="badge badge-dot me-4">
                                                    <i class="bg-info"></i>
                                                    <span class="text-dark text-sm">{{App\Models\Project::where('id',$project->project_id)->first()->name}}</span>
                                                    </span>
                                                @endif
                                            @endif
                                            @endforeach
                                        </td>
                                        <td>
                                        @if(Auth::user()->hasRole('Admin'))
                                            @if(Auth::user()->can('View task'))
                                                <a href="{{route('admin.tasks.index')}}">
                                                    @foreach(App\Models\team_tasks::where('team_id', $team->id)->get() as $task)
                                                        <span class="badge badge-dot me-4">
                                                            <i class="bg-success"></i>
                                                            <span class="text-dark text-sm">{{$task->task_name}}</span>
                                                        </span>
                                                    @endforeach
                                                </a>
                                            @else
                                                @foreach(App\Models\team_tasks::where('team_id', $team->id)->get() as $task)
                                                    <span class="badge badge-dot me-4">
                                                        <i class="bg-success"></i>
                                                        <span class="text-dark text-sm">{{$task->task_name}}</span>
                                                    </span>
                                                @endforeach
                                            @endif
                                        @endif
                                        @if(Auth::user()->hasRole('Responsable de projet'))
                                            @if(Auth::user()->can('View task'))
                                                <a href="{{route('Responsable_de_projet.tasks.index')}}">
                                                    @foreach(App\Models\team_tasks::where('team_id', $team->id)->get() as $task)
                                                        <span class="badge badge-dot me-4">
                                                            <i class="bg-success
                                                            "></i>
                                                            <span class="text-dark text-sm">{{$task->task_name}}</span>
                                                        </span>
                                                    @endforeach
                                                </a>
                                            @else
                                                @foreach(App\Models\team_tasks::where('team_id', $team->id)->get() as $task)
                                                    <span class="badge badge-dot me-4">
                                                        <i class="bg-success"></i>
                                                        <span class="text-dark text-sm">{{$task->task_name}}</span>
                                                    </span>
                                                @endforeach
                                            @endif
                                        @endif
                                        @if(Auth::user()->hasRole('Employee'))
                                            @if(Auth::user()->can('View task'))
                                                <a href="{{route('employee.tasks.index')}}">
                                                    @foreach(App\Models\team_tasks::where('team_id', $team->id)->get() as $task)
                                                        <span class="badge badge-dot me-4">
                                                            <i class="bg-success
                                                            "></i>
                                                            <span class="text-dark text-sm">{{$task->task_name}}</span>
                                                        </span>
                                                    @endforeach
                                                </a>
                                            @else
                                                @foreach(App\Models\team_tasks::where('team_id', $team->id)->get() as $task)
                                                    <span class="badge badge-dot me-4">
                                                        <i class="bg-success"></i>
                                                        <span class="text-dark text-sm">{{$task->task_name}}</span>
                                                    </span>
                                                @endforeach
                                            @endif
                                        @endif
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
                <div class="row mb-3">
                    @foreach($teams as $team)
                    <div class="col-lg-4 col-md-6 mb-3 mb-lg-0">
                        <div class="card h-100">
                            <div class="card-header d-flex flex-column align-items-start">
                                <div class="d-flex justify-content-between w-100">
                                    <h5 class="mb-0 text-capitalize">{{$team->name}}</h5>
                                    @if(Auth::user()->hasRole('Admin'))
                                    @can('Delete team')
                                    <form action="{{route('admin.teams.destroy',$team->id) }}" method="post"
                                        onsubmit="return confirm('Are you sure?');" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger ">Delete</button>
                                    </form>
                                    @endcan
                                    @endif
                                    @if(Auth::user()->hasRole('Responsable de projet'))
                                    @can('Delete team')
                                    <form action="{{route('Responsable_de_projet.teams.destroy',$team->id) }}" method="post"
                                        onsubmit="return confirm('Are you sure?');" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger ">Delete</button>
                                    </form>
                                    @endcan
                                    @endif
                                    @if(Auth::user()->hasRole('Employee'))
                                    @can('Delete team')
                                    <form action="{{route('employee.teams.destroy',$team->id) }}" method="post"
                                        onsubmit="return confirm('Are you sure?');" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger ">Delete</button>
                                    </form>
                                    @endcan
                                    @endif
                                    @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                    @can('Delete team')
                                    <form action="{{route('Responsable_gestionnaire.teams.destroy',$team->id) }}" method="post"
                                        onsubmit="return confirm('Are you sure?');" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger ">Delete</button>
                                    </form>
                                    @endcan
                                    @endif
                                </div>
                                <span class="font-weight-bold" style="font-size: 0.8em; padding-left: 10px;">Team
                                    Members :</span>
                            </div>

                            <div class="card-body pt-0">
                                <ul class="list-group list-group-flush">
                                    @foreach(App\Models\team_members::where('team_id', $team->id)->get() as $team_member)
                                    <li class="list-group-item px-0">
                                        <div class="row align-items-center">
                                            <div class="col-auto d-flex align-items-center">
                                                @php
                                                    $colors = ['#2ECC71', '#BDC3C7', '#F39C12', '#E74C3C', '#9B59B6',
                                                    '#2E86C1','#641E16','#2C3E50','#F4D03F','#F4D03F'];
                                                @endphp
                                                @if(App\Models\Employees::where('id', $team_member->employee_id)->first()->image)
                                                    <img class="avatar avatar-sm rounded-circle me-2" src="{{ Storage::disk('do_spaces')->url('employee_images/' . App\Models\Employees::where('id', $team_member->employee_id)->first()->image) }}" alt="employee Image">
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
                                            </div>
                                        </div>
                                    </li>
                                    @endforeach
                                    
                                </ul>
                            </div>

                            <div class="d-flex flex-column align-items-end p-4 pb-0 justify-content-end">
                            @if(Auth::user()->hasRole('Admin'))
                            @can('Update team')
                                <a href="{{route('admin.teams.edit',$team->id) }}" class="btn btn-sm btn-primary">Edit</a>
                            @endcan
                            @endif
                            @if(Auth::user()->hasRole('Responsable de projet'))
                            @can('Update team')
                                <a href="{{route('Responsable_de_projet.teams.edit',$team->id) }}" class="btn btn-sm btn-primary">Edit</a>
                            @endcan
                            @endif
                            @if(Auth::user()->hasRole('Employee'))
                            @can('Update team')
                                <a href="{{route('employee.teams.edit',$team->id) }}" class="btn btn-sm btn-primary">Edit</a>
                            @endcan
                            @endif
                            @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                            @can('Update team')
                                <a href="{{route('Responsable_gestionnaire.teams.edit',$team->id) }}" class="btn btn-sm btn-primary">Edit</a>
                            @endcan
                            @endif
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


</body>

</html>