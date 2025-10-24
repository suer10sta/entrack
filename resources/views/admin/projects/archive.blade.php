<!DOCTYPE html>
<html lang="en">

@include('dashboard_layouts.head')
<style>
  a {
    color: blue;
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

        <div class="row mt-3">
          <div class="col-12">
            <div class="card">
              <div class="card-header p-3 pb-0">
                <div class="d-flex justify-content-between">
                    <h6>Archived Projects Table</h6>
                    <!--
                    @if(Auth::user()->hasRole('Admin'))
                        <div>
                            <a type="button" class="btn btn-primary" href="{{route('admin.Projectsrepports.downloadRepports')}}">Projects Reports PDF</a>
                        </div>
                    @endif
                    @if(Auth::user()->hasRole('Responsable de projet'))
                        <div>
                            <a type="button" class="btn btn-primary" href="{{route('Responsable_de_projet.Projectsrepports.downloadRepports')}}">Projects Reports PDF</a>
                        </div>
                    @endif
                    -->
                    @if(Auth::user()->hasRole('Admin'))
                        <div>
                            <a type="button" class="btn btn-primary" href="{{route('admin.projects.index')}}">Return Back</a>
                        </div>
                    @endif
                    @if(Auth::user()->hasRole('Responsable de projet'))
                        <div>
                            <a type="button" class="btn btn-primary" href="{{route('Responsable_de_projet.projects.index')}}">Return Back</a>
                        </div>
                    @endif
                    @if(Auth::user()->hasRole('Employee'))
                        <div>
                            <a type="button" class="btn btn-primary" href="{{route('employee.projects.index')}}">Return Back</a>
                        </div>
                    @endif
                    @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                        <div>
                            <a type="button" class="btn btn-primary" href="{{route('Responsable_gestionnaire.projects.index')}}">Return Back</a>
                        </div>
                    @endif
                    
                    
                </div>
                  @if(session('message'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('message') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">X</button>
                    </div>
                  @endif
              </div>
              
    
        <div class="card-body">
            <div class="table-responsive" style="overflow: visible;">
                <table class="table align-items-center mb-0">
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
                            
                           @can('Delete project')
                            <th class="text-center text-uppercase text-secondary text-x font-weight-bolder opacity-10"></th>
                            @endcan
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($projects as $project)
                        <tr>

                            <td class="editable" data-id="{{$project->id}}" data-column="name">
                                <div class="d-flex px-2">
                                    <div class="my-auto">
                                    @if(Auth::user()->hasRole('Responsable de projet'))
                                        @if(Auth::user()->can('View project'))
                                            <a href="{{route('Responsable_de_projet.projects.show',$project->id)}}">
                                                <h6 class="mb-0 text-sm"><b>{{strtoupper($project->name)}}</b></h6>
                                            </a>
                                        @else
                                            <h6 class="mb-0 text-sm"><b>{{strtoupper($project->name)}}</b></h6>
                                        @endif
                                    @endif
                                    @if(Auth::user()->hasRole('Admin'))
                                        @if(Auth::user()->can('View project'))
                                            <a href="{{route('admin.projects.show',$project->id)}}">
                                                <h6 class="mb-0 text-sm"><b>{{strtoupper($project->name)}}</b></h6>
                                            </a>
                                            @else
                                            <h6 class="mb-0 text-sm"><b>{{strtoupper($project->name)}}</b></h6>
                                        @endif
                                    @endif
                                    @if(Auth::user()->hasRole('Employee'))
                                        @if(Auth::user()->can('View project'))
                                            <a href="{{route('employee.projects.show',$project->id)}}">
                                                <h6 class="mb-0 text-sm"><b>{{strtoupper($project->name)}}</b></h6>
                                            </a>
                                        @else
                                            <h6 class="mb-0 text-sm"><b>{{strtoupper($project->name)}}</b></h6>
                                        @endif
                                    @endif
                                    @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                        @if(Auth::user()->can('View project'))
                                            <a href="{{route('Responsable_gestionnaire.projects.show',$project->id)}}">
                                                <h6 class="mb-0 text-sm"><b>{{strtoupper($project->name)}}</b></h6>
                                            </a>
                                        @else
                                            <h6 class="mb-0 text-sm"><b>{{strtoupper($project->name)}}</b></h6>
                                        @endif
                                    @endif
                                        
                                    </div>
                                </div>
                            </td>
                            @if(Auth::user()->hasRole('Admin'))
                                <td class="editable" data-id="{{$project->id}}" data-column="budget">

                                    <p class="text-sm font-weight-bold mb-0 text-success">
                                    €{{$project->budget}}</p>
                                </td>
                                <td class="editable" data-id="{{$project->id}}" data-column="cost_per_hour">
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
                                            +{{$diffhours}}h:{{$diffminutes}}m</span>
                                            @elseif($difference < 0)
                                            <p class="text-sm font-weight-bold mb-0 text-danger">
                                            -{{$diffhours}}h:{{$diffminutes}}m</p>
                                            @else
                                                <span style="color: blue;">{{$diffhours}}h:{{$diffminutes}}m</p>
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
                                <p class="text-sm font-weight-bold mb-0 text-success">
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
                                @if(Auth::user()->hasRole('Employee'))
                                    @if(Auth::user()->can('View client'))
                                        <a href="{{route('employee.clients.show', $project->client_id)}}">
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
                                </p>
                            </td>
                            <td>
                                <p class="text-sm font-weight-bold mb-0">
                                    @forEach(App\Models\project_teams::where('project_id', $project->id)->get() as $project_team)
                                    @if(Auth::user()->hasRole('Admin'))
                                        @if(Auth::user()->can('View team'))
                                        <a href="{{route('admin.teams.index')}}">
                                            {{$project_team->team_name}}
                                        </a>
                                        @else
                                            {{$project_team->team_name}}
                                        @endif
                                    @endif
                                    @if(Auth::user()->hasRole('Responsable de projet'))
                                        @if(Auth::user()->can('View team'))
                                        <a href="{{route('Responsable_de_projet.teams.index')}}">
                                            {{$project_team->team_name}}
                                        </a>
                                        @else
                                            {{$project_team->team_name}}
                                        @endif
                                    @endif
                                    @if(Auth::user()->hasRole('Employee'))
                                        @if(Auth::user()->can('View team'))
                                        <a href="{{route('employee.teams.index')}}">
                                            {{$project_team->team_name}}
                                        </a>
                                        @else
                                            {{$project_team->team_name}}
                                        @endif
                                    @endif
                                    @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                        @if(Auth::user()->can('View team'))
                                        <a href="{{route('Responsable_gestionnaire.teams.index')}}">
                                            {{$project_team->team_name}}
                                        </a>
                                        @else
                                            {{$project_team->team_name}}
                                        @endif
                                    @endif
                                    <br>
                                    @endforEach
                                </p>
                            </td>
                            @if(Auth::user()->hasRole('Admin'))
                            @can('Delete project')
                                <td class="align-middle">
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
                                    <form
                                        action="{{ route('admin.projects.unarchive', $project->id)}}"
                                        method="post"
                                        onsubmit="return confirm('Are you sure?');"
                                        class="d-inline">
                                        @csrf
                                        <button type="submit"
                                            class="btn btn-secondary btn-xs p-2 mb-0">unarchive</button>
                                    </form>
                                </td>
                            @endcan
                            @endif
                            @if(Auth::user()->hasRole('Responsable de projet'))
                            @can('Delete project')
                                <td class="align-middle">
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
                                    <form
                                        action="{{ route('Responsable_de_projet.projects.unarchive', $project->id)}}"
                                        method="post"
                                        onsubmit="return confirm('Are you sure?');"
                                        class="d-inline">
                                        @csrf
                                        <button type="submit"
                                            class="btn btn-secondary btn-xs p-2 mb-0">unarchive</button>
                                    </form>
                                </td>
                            @endcan
                            @endif
                            @if(Auth::user()->hasRole('Employee'))
                            @can('Delete project')
                                <td class="align-middle">
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
                                    <form
                                        action="{{ route('employee.projects.unarchive', $project->id)}}"
                                        method="post"
                                        onsubmit="return confirm('Are you sure?');"
                                        class="d-inline">
                                        @csrf
                                        <button type="submit"
                                            class="btn btn-secondary btn-xs p-2 mb-0">unarchive</button>
                                    </form>
                                </td>
                            @endcan
                            @endif
                            @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                            @can('Delete project')
                                <td class="align-middle">
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
                                    <form
                                        action="{{ route('Responsable_gestionnaire.projects.unarchive', $project->id)}}"
                                        method="post"
                                        onsubmit="return confirm('Are you sure?');"
                                        class="d-inline">
                                        @csrf
                                        <button type="submit"
                                            class="btn btn-secondary btn-xs p-2 mb-0">unarchive</button>
                                    </form>
                                </td>
                            @endcan
                            @endif

                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
    
</div>

            </div>
          </div>
        </div>



  </main>


  @include('dashboard_layouts.scripts')
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
  @can('Update project')
  <script>
  $(document).ready(function(){
  $(".editable").dblclick(function(){
    var originalContent = $(this).text().trim();
    var width = $(this).width();
    $(this).html("<input type='text' value='" + originalContent + "' style='width: 100%; min-width: " + width + "px;' />");
    var input = $(this).children().first();
    input.focus();
    
    // Place cursor at the beginning of the input
    input[0].setSelectionRange(0, 0);

    input.blur(function(){
      var newContent = $(this).val();
      var clientId = $(this).parent().data('id');
      var columnName = $(this).parent().data('column');
      $(this).parent().text(newContent);
      $(this).parent().removeClass("cellEditing");
      
      $.ajax({
        url: 'projects/updateProject',
        type: 'post',
        data: {
          content: newContent,
          id: clientId,
          column: columnName,
          _token: '{{ csrf_token() }}'
        },
        success: function(response) {
          location.reload();
        }
      });
    });
  });
});
  </script>
@endcan

</body>

</html>






