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


                <div class="container-fluid py-4">
                    <div class="row mb-5">
                        <div class="col-12">
                            <div class="multisteps-form mb-5">

                                <div class="row">
                                    <div class="col-12 col-md-4">
                                        <div class="card p-3  border-radius-xl bg-white mb-3 ">
                                            <h5 class="font-weight-bolder mb-0">Personal Infos</h5>
                                            <p class="mb-0 text-sm">Mandatory informations</p>
                                            <div class="multisteps-form__content ">
                                                <div class="row mt-3">
                                                    <div class="col-12 col-sm-6">
                                                        <label>Name : </label>
                                                        <input class="multisteps-form__input form-control editable"  data-id="{{$client->id}}" data-column="name"
                                                            type="text" value="{{$client->name}}" name="name" readonly />
                                                    </div>
                                                    <div class="col-12 col-sm-6">
                                                        <label>Client Number : </label>
                                                        <input class="multisteps-form__input form-control editable"  data-id="{{$client->id}}" data-column="client_number"
                                                            type="text" value="{{$client->client_number}}" name="client_number" readonly  />
                                                    </div>
                                                    <div class="col-12 col-sm-6 mt-3 mt-sm-0">
                                                        <label>Email : </label>
                                                        <input class="multisteps-form__input form-control editable"  data-id="{{$client->id}}" data-column="email"
                                                            type="text" value="{{$client->email}}" name="email" readonly/>
                                                    </div>
                                                    <div class="col-12 col-sm-6 mt-3 mt-sm-0">
                                                        <label>Phone : </label>
                                                        <input class="multisteps-form__input form-control editable"  data-id="{{$client->id}}" data-column="phone"
                                                            type="number" value="{{$client->phone}}" name="phone" readonly />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <div class="card p-3 border-radius-xl bg-white mb-3 ">
                                            <h5 class="font-weight-bolder mb-0">Address Infos</h5>
                                            <p class="mb-0 text-sm">Mandatory informations</p>
                                            <div class="multisteps-form__content ">
                                                <div class="row mt-3">
                                                    <div class="col-12 col-sm-6">
                                                        <label>Street : </label>
                                                        <input class="multisteps-form__input form-control editable"  data-id="{{$client->id}}" data-column="address"
                                                            type="text" value="{{$client->address}}" name="address" readonly  />
                                                    </div>
                                                    <div class="col-12 col-sm-6 mt-3 mt-sm-0">
                                                        <label>City : </label>
                                                        <input class="multisteps-form__input form-control editable"  data-id="{{$client->id}}" data-column="city"
                                                            type="text" value="{{$client->city}}" name="city" readonly/>
                                                    </div>
                                                    <div class="col-12 col-sm-6">
                                                        <label>Zip Code :</label>
                                                        <input class="multisteps-form__input form-control editable"  data-id="{{$client->id}}" data-column="zip"
                                                            type="number" value="{{$client->zip}}" name="zip" readonly/>
                                                    </div>
                                                    <div class="col-12 col-sm-6">
                                                    <label>Country : </label>
                                                        <input class="multisteps-form__input form-control editable"  data-id="{{$client->id}}" data-column="country"
                                                            type="text" value="{{$client->country}}" name="country" readonly/>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <div class="card p-3 border-radius-xl bg-white mb-3 ">
                                            <h5 class="font-weight-bolder mb-0">Billing Infos</h5>
                                            <p class="mb-0 text-sm">Mandatory informations</p>
                                            <div class="multisteps-form__content ">
                                                <div class="row mt-3">
                                                    <div class="col-12 col-sm-6">
                                                        <label>TVA number : </label>
                                                        <input class="multisteps-form__input form-control editable"  data-id="{{$client->id}}" data-column="TVA"
                                                            type="text" value="{{$client->TVA}}" name="TVA" readonly />
                                                    </div>
                                                    <div class="col-12 col-sm-6">
                                                        <label>Due Date : </label>
                                                        <input class="multisteps-form__input form-control editable"  data-id="{{$client->id}}" data-column="due_date"
                                                            type="text" value="{{$client->due_date}}" name="due_date" readonly />
                                                    </div>
                                                </div>
                                                <div class="row mt-3">
                                                    <div class="col-12 col sm-6">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" value="" id="billingSameAsClient">
                                                            <label class="form-check-label" for="billingSameAsClient">
                                                                Billing address is the same as client address
                                                            </label>
                                                        </div>
                                                    </div>        
                                                </div>
                                                <div class="row mt-3">
                                                    <div class="col-12 col-sm-6">
                                                        <label>Street : </label>
                                                        <input class="multisteps-form__input form-control editable"  data-id="{{$client->id}}" data-column="billing_street"
                                                            type="text" value="{{$client->billing_street}}" name="billing_street" readonly />
                                                    </div>
                                                    <div class="col-12 col-sm-6">
                                                        <label>City : </label>
                                                        <input class="multisteps-form__input form-control editable"  data-id="{{$client->id}}" data-column="billing_city"
                                                            type="text" value="{{$client->billing_city}}" name="billing_city" readonly />
                                                    </div>
                                                </div>
                                                <div class="row mt-3">
                                                    <div class="col-12 col-sm-6">
                                                        <label>Zip Code : </label>
                                                        <input class="multisteps-form__input form-control editable"  data-id="{{$client->id}}" data-column="billing_zip"
                                                            type="text" value="{{$client->billing_zip}}" name="billing_zip" readonly />
                                                    </div>
                                                    <div class="col-12 col-sm-6">
                                                        <label>Country : </label>
                                                        <input class="multisteps-form__input form-control editable"  data-id="{{$client->id}}" data-column="billing_country"
                                                            type="text" value="{{$client->billing_country}}" name="billing_country" readonly />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                        <div class="card p-3 border-radius-xl bg-white mb-3 ">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <h5 class="font-weight-bolder mb-0">Projects Infos</h5>
                                                <p class="mb-0 text-sm">Mandatory informations</p>
                                            </div>
                                            @can('Add project')
                                            @if(Auth::user()->hasRole('Admin'))
                                            <a type="button" class="btn btn-primary" href="{{route('admin.projects.create', $client->id)}}">Add Project</a> 
                                            @endif
                                            @if(Auth::user()->hasRole('Employee'))
                                            <a type="button" class="btn btn-primary" href="{{route('employee.projects.create', $client->id)}}">Add Project</a> 
                                            @endif
                                            @if(Auth::user()->hasRole('Responsable de projet'))
                                            <a type="button" class="btn btn-primary" href="{{route('Responsable_de_projet.projects.create', $client->id)}}">Add Project</a> 
                                            @endif
                                            @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                            <a type="button" class="btn btn-primary" href="{{route('Responsable_gestionnaire.projects.create', $client->id)}}">Add Project</a> 
                                            @endif
                                            @endcan
                                        </div>
                                            
                                            <div class="multisteps-form__content">
                                            <div class="table-responsive" style="overflow: visible;">
                                                <br>
                                    <table class="table align-items-center mb-0">
                                        <thead>
                                            <tr>
                                                <th
                                                    class="text-uppercase text-dark text-x font-weight-bolder opacity-10">
                                                    Project
                                                </th>
                                                <th
                                                    class="text-uppercase text-dark text-x font-weight-bolder opacity-10 ps-2">
                                                    Budget
                                                </th>
                                                <th
                                                    class="text-uppercase text-dark text-x font-weight-bolder opacity-10 ps-2">
                                                    CPH
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
                                                </th>
                                                
                                               
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($projects as $project)
                                            <tr>
                                                <td>
                                                    <div class="d-flex px-2">
                                                        <div class="my-auto">
                                                        @if(Auth::user()->hasRole('Admin'))
                                                            @if(Auth::user()->can('View project'))
                                                                <a href="{{route('admin.projects.show',$project->id)}}">
                                                                    <h6 class="mb-0 text-sm" style="color: blue;">{{strtoupper($project->name)}}</h6>
                                                                </a>
                                                                @else
                                                                <h6 class="mb-0 text-sm">{{strtoupper($project->name)}}</h6>
                                                            @endif
                                                        @endif
                                                        @if(Auth::user()->hasRole('Responsable de projet'))
                                                            @if(Auth::user()->can('View project'))
                                                                <a href="{{route('Responsable_de_projet.projects.show',$project->id)}}">
                                                                    <h6 class="mb-0 text-sm" style="color: blue;">{{strtoupper($project->name)}}</h6>
                                                                </a>
                                                                @else
                                                                <h6 class="mb-0 text-sm">{{strtoupper($project->name)}}</h6>
                                                            @endif
                                                        @endif
                                                        @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                                            @if(Auth::user()->can('View project'))
                                                                <a href="{{route('Responsable_gestionnaire.projects.show',$project->id)}}">
                                                                    <h6 class="mb-0 text-sm" style="color: blue;">{{strtoupper($project->name)}}</h6>
                                                                </a>
                                                                @else
                                                                <h6 class="mb-0 text-sm">{{strtoupper($project->name)}}</h6>
                                                            @endif
                                                        @endif
                                                        @if(Auth::user()->hasRole('Employee'))
                                                            @if(Auth::user()->can('View project'))
                                                                <a href="{{route('employee.projects.show',$project->id)}}">
                                                                    <h6 class="mb-0 text-sm" style="color: blue;">{{strtoupper($project->name)}}</h6>
                                                                </a>
                                                            @else
                                                                <h6 class="mb-0 text-sm">{{strtoupper($project->name)}}</h6>
                                                            @endif
                                                        @endif
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <p class="text-sm font-weight-bold mb-0 text-success">
                                                    €{{$project->budget}}</p>
                                                </td>
                                                <td>
                                                    <p class="text-sm font-weight-bold mb-0 text-success">
                                                    €{{$project->cost_per_hour}}</p>
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
                                                    <p class="text-sm font-weight-bold"> 
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
                                                <td>
                                                    <p class="text-sm font-weight-bold mb-0 ">
                                                    %{{$project->tva}}</p>
                                                </td>
                                                <td>
                                                    <p class="text-sm font-weight-bold mb-0">
                                                    @if(Auth::user()->hasRole('Admin'))
                                                        @if(Auth::user()->can('View client'))
                                                            <a href="{{route('admin.clients.show', $project->client_id)}}">
                                                                {{App\Models\Client::where('id', $project->client_id)->first()->name}}
                                                            </a>
                                                            @else
                                                            {{App\Models\Client::where('id', $project->client_id)->first()->name}}
                                                        @endif
                                                    @endif
                                                    @if(Auth::user()->hasRole('Responsable de projet'))
                                                        @if(Auth::user()->can('View client'))
                                                            <a href="{{route('Responsable_de_projet.clients.show', $project->client_id)}}">
                                                                {{App\Models\Client::where('id', $project->client_id)->first()->name}}
                                                            </a>
                                                            @else
                                                            {{App\Models\Client::where('id', $project->client_id)->first()->name}}
                                                        @endif
                                                    @endif
                                                    @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                                        @if(Auth::user()->can('View client'))
                                                            <a href="{{route('Responsable_gestionnaire.clients.show', $project->client_id)}}">
                                                                {{App\Models\Client::where('id', $project->client_id)->first()->name}}
                                                            </a>
                                                            @else
                                                            {{App\Models\Client::where('id', $project->client_id)->first()->name}}
                                                        @endif
                                                    @endif
                                                    @if(Auth::user()->hasRole('Employee'))
                                                        @if(Auth::user()->can('View client'))
                                                            <a href="{{route('employee.clients.show', $project->client_id)}}">
                                                                {{App\Models\Client::where('id', $project->client_id)->first()->name}}
                                                            </a>
                                                            @else
                                                            {{App\Models\Client::where('id', $project->client_id)->first()->name}}
                                                        @endif
                                                    @endif
                                                    </p>
                                                </td>
                                                <td>
                                                @if(Auth::user()->hasRole('Admin'))
                                                    @if(Auth::user()->can('View team'))
                                                        <p class="text-sm font-weight-bold mb-0">
                                                            @forEach(App\Models\project_teams::where('project_id',
                                                            $project->id)->get() as $project_team)
                                                            <a href="{{route('admin.teams.index')}}">
                                                                {{$project_team->team_name}}
                                                            </a>
                                                            <br>
                                                            @endforEach
                                                        </p>
                                                        @else
                                                        <p class="text-sm font-weight-bold mb-0">
                                                            @forEach(App\Models\project_teams::where('project_id',
                                                            $project->id)->get() as $project_team)
                                                                {{$project_team->team_name}}
                                                            <br>
                                                            @endforEach
                                                        </p>
                                                    @endif

                                                @endif
                                                @if(Auth::user()->hasRole('Responsable de projet'))
                                                    @if(Auth::user()->can('View team'))
                                                        <p class="text-sm font-weight-bold mb-0">
                                                            @forEach(App\Models\project_teams::where('project_id',
                                                            $project->id)->get() as $project_team)
                                                            <a href="{{route('Responsable_de_projet.teams.index')}}">
                                                                {{$project_team->team_name}}
                                                            </a>
                                                            <br>
                                                            @endforEach
                                                        </p>
                                                        @else
                                                        <p class="text-sm font-weight-bold mb-0">
                                                            @forEach(App\Models\project_teams::where('project_id',
                                                            $project->id)->get() as $project_team)
                                                                {{$project_team->team_name}}
                                                            <br>
                                                            @endforEach
                                                        </p>
                                                    @endif
                                                @endif
                                                @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                                    @if(Auth::user()->can('View team'))
                                                        <p class="text-sm font-weight-bold mb-0">
                                                            @forEach(App\Models\project_teams::where('project_id',
                                                            $project->id)->get() as $project_team)
                                                            <a href="{{route('Responsable_gestionnaire.teams.index')}}">
                                                                {{$project_team->team_name}}
                                                            </a>
                                                            <br>
                                                            @endforEach
                                                        </p>
                                                        @else
                                                        <p class="text-sm font-weight-bold mb-0">
                                                            @forEach(App\Models\project_teams::where('project_id',
                                                            $project->id)->get() as $project_team)
                                                                {{$project_team->team_name}}
                                                            <br>
                                                            @endforEach
                                                        </p>
                                                    @endif
                                                @endif
                                                @if(Auth::user()->hasRole('Employee'))
                                                    @if(Auth::user()->can('View team'))
                                                        <p class="text-sm font-weight-bold mb-0">
                                                            @forEach(App\Models\project_teams::where('project_id',
                                                            $project->id)->get() as $project_team)
                                                            <a href="{{route('employee.teams.index')}}">
                                                                {{$project_team->team_name}}
                                                            </a>
                                                            <br>
                                                            @endforEach
                                                        </p>
                                                        @else
                                                        <p class="text-sm font-weight-bold mb-0">
                                                            @forEach(App\Models\project_teams::where('project_id',
                                                            $project->id)->get() as $project_team)
                                                                {{$project_team->team_name}}
                                                            <br>
                                                            @endforEach
                                                        </p>
                                                    @endif
                                                @endif
                                                </td>
                                                @if(Auth::user()->hasRole('Admin'))
                                                <td>
                                                    <a href="{{route('admin.projects.edit', $project->id)}}" class="btn btn-info btn-xs p-2 mb-0">
                                                        edit
                                                    </a>
                                                    <form
                                                        action="{{ route('admin.projects.archive', $project->id)}}"
                                                        method="post"
                                                        onsubmit="return confirm('Are you sure?');"
                                                        class="d-inline">
                                                        @csrf
                                                        <button type="submit"
                                                            class="btn btn-secondary btn-xs p-2 mb-0">archive</button>
                                                    </form>
                                                    <form
                                                        action="{{ route('admin.projects.destroy', $project->id)}}"
                                                        method="post"
                                                        onsubmit="return confirm('Are you sure?');"
                                                        class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                            class="btn btn-danger btn-xs p-2 mb-0">X</button>
                                                    </form>
                                                </td>
                                                @endif
                                                @if(Auth::user()->hasRole('Responsable de projet'))
                                                <td>
                                                    <a href="{{route('Responsable_de_projet.projects.edit', $project->id)}}" class="btn btn-info btn-xs p-2 mb-0">
                                                        edit
                                                    </a>
                                                    <form
                                                        action="{{ route('Responsable_de_projet.projects.archive', $project->id)}}"
                                                        method="post"
                                                        onsubmit="return confirm('Are you sure?');"
                                                        class="d-inline">
                                                        @csrf
                                                        <button type="submit"
                                                            class="btn btn-secondary btn-xs p-2 mb-0">archive</button>
                                                    </form>
                                                    <form
                                                        action="{{ route('Responsable_de_projet.projects.destroy', $project->id)}}"
                                                        method="post"
                                                        onsubmit="return confirm('Are you sure?');"
                                                        class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                            class="btn btn-danger btn-xs p-2 mb-0">X</button>
                                                    </form>
                                                </td>
                                                @endif
                                                @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                                    <td>
                                                        <a href="{{route('Responsable_gestionnaire.projects.edit', $project->id)}}" class="btn btn-info btn-xs p-2 mb-0">
                                                            edit
                                                        </a>
                                                        <form
                                                            action="{{ route('Responsable_gestionnaire.projects.archive', $project->id)}}"
                                                            method="post"
                                                            onsubmit="return confirm('Are you sure?');"
                                                            class="d-inline">
                                                            @csrf
                                                            <button type="submit"
                                                                class="btn btn-secondary btn-xs p-2 mb-0">archive</button>
                                                        </form>
                                                        <form
                                                            action="{{ route('Responsable_gestionnaire.projects.destroy', $project->id)}}"
                                                            method="post"
                                                            onsubmit="return confirm('Are you sure?');"
                                                            class="d-inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit"
                                                                class="btn btn-danger btn-xs p-2 mb-0">X</button>
                                                        </form>
                                                    </td>
                                                @endif
                                                @if(Auth::user()->hasRole('Employee'))
                                                    <td>
                                                        <a href="{{route('employee.projects.edit', $project->id)}}" class="btn btn-info btn-xs p-2 mb-0">
                                                            edit
                                                        </a>
                                                        <form
                                                            action="{{ route('employee.projects.archive', $project->id)}}"
                                                            method="post"
                                                            onsubmit="return confirm('Are you sure?');"
                                                            class="d-inline">
                                                            @csrf
                                                            <button type="submit"
                                                                class="btn btn-secondary btn-xs p-2 mb-0">archive</button>
                                                        </form>
                                                        <form
                                                            action="{{ route('employee.projects.destroy', $project->id)}}"
                                                            method="post"
                                                            onsubmit="return confirm('Are you sure?');"
                                                            class="d-inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit"
                                                                class="btn btn-danger btn-xs p-2 mb-0">X</button>
                                                        </form>
                                                    </td>
                                                @endif
                                                
                                               
                                            </tr>
                                            @endforeach
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
                                <div class="d-flex align-items-center">
                                    <form action="{{ Auth::user()->hasRole('Admin') ? route('admin.clients.filterInvoices', $client->id) : 
                                           (Auth::user()->hasRole('Employee') ? route('employee.clients.filterInvoices', $client->id) : 
                                           (Auth::user()->hasRole('Responsable de projet') ? route('Responsable_de_projet.clients.filterInvoices', $client->id) : 
                                                   route('Responsable_gestionnaire.clients.filterInvoices', $client->id))) }}" 
                                          method="POST" class="d-flex align-items-center" id="filterForm">
                                        @csrf
                                        <input type="hidden" name="client_id" value="{{ $client->id }}">
                                        <input type="date" name="start_date" id="start_date" class="form-control me-2" style="width: auto;" 
                                            value="{{ request()->session()->get('start_date') }}">
                                        <input type="date" name="end_date" id="end_date" class="form-control me-2" style="width: auto;"
                                            value="{{ request()->session()->get('end_date') }}">
                                        <button type="submit" class="btn btn-primary me-2">Filter</button>
                                    </form>
                                    <a href="#" onclick="downloadReport(event)" class="btn btn-success">Download Report</a>
                                </div>
                                <div class="d-flex flex-column align-items-end">
                                    <div class="d-flex align-items-center">
                                        <div>
                                            <label for="totalPaid" class="form-label" style="font-size: 16px !important;"> Total Paid: <label style=" color: #2dce89 !important;font-size: 16px !important;"> €{{ number_format($totalPaid, 2, ',', ' ') }} </label> | </label>
                                        </div>
                                        <div>
                                            <label for="totalUnpaid" class="form-label" style="font-size: 16px !important;"> Total Unpaid: <label style=" color: red !important;font-size: 16px !important;"> €{{ number_format($totalUnpaid, 2, ',', ' ') }} </label> | </label>
                                        </div>
                                        <div>
                                            <label for="totalUnpaid" class="form-label" style="font-size: 16px !important;">Total TVA: <label style="font-size: 16px !important;"> €{{ number_format($totalTVA, 2, ',', ' ') }} </label> | </label>
                                        </div>
                                        <div>
                                            <label for="totalUnpaid" class="form-label" style="font-size: 16px !important;">Total Budget: <label style="font-size: 16px !important;"> €{{ number_format($totalBudget, 2, ',', ' ') }} </label> </label>
                                        </div>
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
                                                    Cost Total
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($invoices as $invoice)
                                            <tr>
                                                <td>
                                                    <p class="align-middle text-center text-sm font-weight-bold mb-0">
                                                        @php
                                                            $dueDate = \Carbon\Carbon::parse($invoice->due_date);
                                                            $today = \Carbon\Carbon::now();
                                                            $isOverdue = $dueDate->isPast() && !$invoice->Payment_status;
                                                            $daysOverdue = $isOverdue ? $today->diffInDays($dueDate) : 0;
                                                        @endphp
                                                        
                                                        @if(Auth::user()->hasRole('Responsable de projet'))
                                                            <a href="{{ route('Responsable_de_projet.invoices.servePdf', ['invoiceName' => $invoice->id]) }}" 
                                                            target="_blank" 
                                                            style="{{ $isOverdue ? 'color: red;' : '' }}">
                                                                {{$entreprise_infos->invoice_prefix}}-{{date('ym', strtotime($invoice->created_at))}}{{$invoice->number}}
                                                                @if($isOverdue)
                                                                    <span class="text-danger">({{ $daysOverdue }} days overdue)</span>
                                                                @endif
                                                            </a>
                                                        @endif
                                                        @if(Auth::user()->hasRole('Admin'))
                                                            <a href="{{ route('admin.invoices.servePdf', ['invoiceName' => $invoice->id]) }}" 
                                                            target="_blank"
                                                            style="{{ $isOverdue ? 'color: red;' : '' }}">
                                                                {{$entreprise_infos->invoice_prefix}}-{{date('ym', strtotime($invoice->created_at))}}{{$invoice->number}}
                                                                @if($isOverdue)
                                                                    <span class="text-danger">({{ $daysOverdue }} days overdue)</span>
                                                                @endif
                                                            </a>
                                                        @endif
                                                        @if(Auth::user()->hasRole('Employee'))
                                                            <a href="{{ route('employee.invoices.servePdf', ['invoiceName' => $invoice->id]) }}" 
                                                            target="_blank"
                                                            style="{{ $isOverdue ? 'color: red;' : '' }}">
                                                                {{$entreprise_infos->invoice_prefix}}-{{date('ym', strtotime($invoice->created_at))}}{{$invoice->number}}
                                                                @if($isOverdue)
                                                                    <span class="text-danger">({{ $daysOverdue }} days overdue)</span>
                                                                @endif
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
                                                <td id="totalcost">
                                                    @if($invoice->Payment_status==1)
                                                        <p class="text-center text-sm font-weight-bold mb-0 text-success">€<span id="totalCostValue">{{ number_format($invoice->total_ttc, 2, ',', ' ') }}</span>
                                                        </p>
                                                    @else
                                                        <p class="text-center text-sm font-weight-bold mb-0 text-danger">€<span id="totalCostValue">{{ number_format($invoice->total_ttc, 2, ',', ' ') }}</span>
                                                        </p>
                                                    @endif
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

                                @php
                                    $comptable=App\Models\client_contacts::where('client_id',$client->id)
                                    ->where('is_comptable',1)->first();
                                @endphp
                                @if($comptable)
                                    <div class="row">
                                        <div class="card p-3 border-radius-xl bg-white mb-3 ">
                                            <h5 class="font-weight-bolder mb-0">Comptable Infos</h5>
                                            <p class="mb-0 text-sm">Mandatory informations</p>
                                            <div class="multisteps-form__content">
                                                <div class="row mt-3">
                                                    <div class="col-12 col-sm-6">
                                                        <label>Full Name : </label>
                                                        <input class="multisteps-form__input form-control editable-contact"  data-id="{{$comptable->id}}" data-column="name" type="text" value="{{$comptable->name}}" name="name" readonly  />
                                                    </div>
                                                    <div class="col-12 col-sm-6">
                                                        <label>Email : </label>
                                                        <input class="multisteps-form__input form-control editable-contact"  data-id="{{$comptable->id}}" data-column="email" type="text" value="{{$comptable->email}}" name="email" readonly  />
                                                    </div>
                                                    <div class="col-12 col-sm-6">
                                                        <label>Mobile Fix : </label>
                                                        <input class="multisteps-form__input form-control editable-contact"  data-id="{{$comptable->id}}" data-column="mobile_fix" type="number" value="{{$comptable->mobile_fix}}" name="mobile_fix" readonly  />
                                                    </div>
                                                    <div class="col-12 col-sm-6">
                                                        <label>Address : </label>
                                                        <input class="multisteps-form__input form-control editable-contact"  data-id="{{$comptable->id}}" data-column="address" type="text" value="{{$comptable->address}}" name="address" readonly />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                @php
                                    $contacts=App\Models\client_contacts::where('client_id',$client->id)->get();
                                    $i=1;
                                @endphp
                                <div class="row">
                                    <div class="card p-3 border-radius-xl bg-white mb-3 ">
                                        <h5 class="font-weight-bolder mb-0">Contacts Infos</h5>
                                        <p class="mb-0 text-sm">Mandatory informations</p>
                                        <br>
                                        
                                            <div id="accordion">
                                                @foreach ($contacts as $contact)
                                                @if($contact->is_comptable==0)
                                                    <div class="card">
                                                        <div class="card-header text-right"> 
                                                            <form action="{{route('admin.clients.destroyContact',$contact->id) }}" method="post"
                                                            onsubmit="return confirm('Are you sure?');" class="d-inline">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-danger btn-sm float-right"><i class="fa fa-trash"></i> Delete</button>
                                                            </form>
                                                        </div>
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
                                                @endif
                                                    @php
                                                        $i++;
                                                    @endphp
                                                @endforeach
                                            </div>
                                            <br>
                                            <form class="multisteps-form__form " method="POST"
                                        action="{{ route('admin.clients.addContact', $client->id) }}">
                                        @csrf
                                        @method('PUT')
                                            <h5>Add New Contacts :</h5>
                                            <div class="multisteps-form__content ">
                                                <div id="contact_infos">
                                                    <div class="row mt-3">
                                                        <div class="col-12 col-sm-6">
                                                            <label>Full Name : </label>
                                                            <input class="multisteps-form__input form-control"
                                                                type="text" placeholder="Full Name" name="contact_name[]" />
                                                        </div>
                                                        <div class="col-12 col-sm-6">
                                                            <label>Functionality : </label>
                                                            <input class="multisteps-form__input form-control"
                                                                type="text" placeholder="Functionality" name="contact_function[]" />
                                                        </div>
                                                        <div class="col-12 col-sm-6">
                                                            <label>Mobile Fix : </label>
                                                            <input class="multisteps-form__input form-control"
                                                                type="number" placeholder="Mobile Fix " name="contact_mobile_fix[]" />
                                                        </div>
                                                        <div class="col-12 col-sm-6">
                                                            <label>Email : </label>
                                                            <input class="multisteps-form__input form-control"
                                                                type="text" placeholder="*****@gmail.com" name="contact_email[]" />
                                                        </div>
                                                        <div class="col-12 col-sm-6">
                                                            <label>Address : </label>
                                                            <input class="multisteps-form__input form-control"
                                                                type="text" placeholder="Address" name="contact_address[]" />
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <br>
                                            <button id="addContact" class="btn btn-primary">Add</button>
                                            <div class="button-row d-flex mt-4">
                                                <button class="btn bg-gradient-dark ms-auto mb-0" type="submit" title="create">
                                                    Save
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

            </div>

    </main>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>


    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

    

  <script>
    @can('Update client')
  $(document).ready(function(){
    $(".editable").dblclick(function(){
        $(this).prop('readonly', false);
    });

    $(".editable").blur(function(){
        $(this).prop('readonly', true);
        var newContent = $(this).val();
        var clientId = $(this).data('id');
        var columnName = $(this).attr('name');
        var updatedUrl = '';

        @if(Auth::user()->hasRole('Responsable de projet'))
            updatedUrl = '/Responsable_de_projet/clients/updateClient';
        @elseif(Auth::user()->hasRole('Admin'))
            updatedUrl = '/admin/clients/updateClient';
        @elseif(Auth::user()->hasRole('Employee'))
            updatedUrl = '/employee/clients/updateClient';
        @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
            updatedUrl = '/Responsable_gestionnaire/clients/updateClient';
        @endif 
        // Make AJAX call to update the database here
        $.ajax({
            url: updatedUrl, // your update route
            type: 'post',
            data: {
                content: newContent,
                id: clientId,
                column: columnName,
                _token: '{{ csrf_token() }}' // add CSRF token for Laravel
            },
            success: function(response) {
                // Refresh the page after the update is successful
                location.reload();
            },
            error: function(response) {
                // handle error
            }
        });
    });
});

$(document).ready(function(){
    $(".editable-contact").dblclick(function(){
        $(this).prop('readonly', false);
    });

    $(".editable-contact").blur(function(){
        $(this).prop('readonly', true);
        var newContent = $(this).val();
        var contactId = $(this).data('id');
        var columnName = $(this).attr('name');

        var updatedUrl = '';

        @if(Auth::user()->hasRole('Responsable de projet'))
            updatedUrl = '/Responsable_de_projet/clients/updateContact';
        @elseif(Auth::user()->hasRole('Admin'))
            updatedUrl = '/admin/clients/updateContact';
        @elseif(Auth::user()->hasRole('Employee'))
            updatedUrl = '/employee/clients/updateContact';
        @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
            updatedUrl = '/Responsable_gestionnaire/clients/updateContact';
        @endif 
        // Make AJAX call to update the database here
        $.ajax({
            url: updatedUrl, // your update route
            type: 'post',
            data: {
                content: newContent,
                id: contactId,
                column: columnName,
                _token: '{{ csrf_token() }}' // add CSRF token for Laravel
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

  @endcan

  </script>

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

<script>
        document.addEventListener('DOMContentLoaded', function () {
            const billingCheckbox = document.getElementById('billingSameAsClient');
            const billingStreet = document.querySelector('input[name="billing_street"]');
            const billingCity = document.querySelector('input[name="billing_city"]');
            const billingZip = document.querySelector('input[name="billing_zip"]');
            const billingCountry = document.querySelector('input[name="billing_country"]');

            const clientStreet = "{{$client->address}}";
            const clientCity = "{{$client->city}}";
            const clientZip = "{{$client->zip}}";
            const clientCountry = "{{$client->country}}";
            const clientId = "{{$client->id}}"; // Assuming you have the client's ID available

            billingCheckbox.addEventListener('change', function () {
                if (this.checked) {
                    billingStreet.value = clientStreet;
                    billingCity.value = clientCity;
                    billingZip.value = clientZip;
                    billingCountry.value = clientCountry;

                    billingStreet.readOnly = true;
                    billingCity.readOnly = true;
                    billingZip.readOnly = true;
                    billingCountry.readOnly = true;

                    // Make separate AJAX calls for each field
                    updateClientField('billing_street', clientStreet, clientId);
                    updateClientField('billing_city', clientCity, clientId);
                    updateClientField('billing_zip', clientZip, clientId);
                    updateClientField('billing_country', clientCountry, clientId);
                } else {
                    // Optionally clear the values or make them editable
                    billingStreet.readOnly = false;
                    billingCity.readOnly = false;
                    billingZip.readOnly = false;
                    billingCountry.readOnly = false;
                }
            });

            function updateClientField(columnName, content, clientId) {
                var updatedUrl = '';

                @if(Auth::user()->hasRole('Responsable de projet'))
                    updatedUrl = '/Responsable_de_projet/clients/updateClient';
                @elseif(Auth::user()->hasRole('Admin'))
                    updatedUrl = '/admin/clients/updateClient';
                @elseif(Auth::user()->hasRole('Employee'))
                    updatedUrl = '/employee/clients/updateClient';
                @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                    updatedUrl = '/Responsable_gestionnaire/clients/updateClient';
                @endif 

                $.ajax({
                    url: updatedUrl,
                    type: 'post',
                    data: {
                        id: clientId,
                        column: columnName,
                        content: content,
                        _token: '{{ csrf_token() }}' // CSRF token for Laravel
                    },
                    success: function(response) {
                        location.reload();
                    },
                    error: function(response) {
                        console.error(`Failed to update ${columnName}.`);
                    }
                });
            }
        });
    </script>


    @include('dashboard_layouts.scripts')

</body>

</html>

<script>
function downloadReport(e) {
    e.preventDefault();
    
    // Get dates from session storage if they exist, otherwise from inputs
    const startDate = sessionStorage.getItem('filterStartDate') || document.getElementById('start_date').value;
    const endDate = sessionStorage.getItem('filterEndDate') || document.getElementById('end_date').value;
    
    let url = '{{ Auth::user()->hasRole("Admin") ? 
        route("admin.clients.downloadInvoicesRepports", $client->id) : 
        (Auth::user()->hasRole("Employee") ? 
            route("employee.clients.downloadInvoicesRepports", $client->id) : 
            (Auth::user()->hasRole("Responsable de projet") ? 
                route("Responsable_de_projet.clients.downloadInvoicesRepports", $client->id) : 
                route("Responsable_gestionnaire.clients.downloadInvoicesRepports", $client->id))) }}';

    // Add date parameters if they exist
    if (startDate && endDate) {
        url += `?startDate=${startDate}&endDate=${endDate}`;
    }

    // Open in new tab
    window.open(url, '_blank');
}

// Store filter dates in session storage when form is submitted
document.getElementById('filterForm').addEventListener('submit', function() {
    const startDate = document.getElementById('start_date').value;
    const endDate = document.getElementById('end_date').value;
    sessionStorage.setItem('filterStartDate', startDate);
    sessionStorage.setItem('filterEndDate', endDate);
});

// Restore dates from session storage on page load
window.addEventListener('load', function() {
    const startDate = sessionStorage.getItem('filterStartDate');
    const endDate = sessionStorage.getItem('filterEndDate');
    if (startDate) document.getElementById('start_date').value = startDate;
    if (endDate) document.getElementById('end_date').value = endDate;
});
</script>
