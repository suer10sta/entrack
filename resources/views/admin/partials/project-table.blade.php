<table class="table align-items-center mb-0">
    <thead>
        <tr>
            <th class="text-uppercase text-dark text-x font-weight-bolder opacity-10">Project</th>
            <th class="text-uppercase text-dark text-x font-weight-bolder opacity-10 ps-2">Budget</th>
            <th class="text-uppercase text-dark text-x font-weight-bolder opacity-10 ps-2">CPH</th>
            <th class="text-uppercase text-dark text-x font-weight-bolder opacity-10 ps-2">TVA</th>
            <th class="text-uppercase text-dark text-x font-weight-bolder opacity-10 ps-2">Duration</th>
            <th class="text-uppercase text-dark text-x font-weight-bolder opacity-10 ps-2">Estimated Hours</th>
            <th class="text-uppercase text-dark text-x font-weight-bolder opacity-10 ps-2">Difference</th>
            <th class="text-uppercase text-dark text-x font-weight-bolder opacity-10 ps-2">Progress</th>
            <th class="text-uppercase text-dark text-x font-weight-bolder opacity-10 ps-2">Client</th>
            <th class="text-uppercase text-dark text-x font-weight-bolder opacity-10 ps-2">Teams</th>
            <th class="text-center text-uppercase text-secondary text-x font-weight-bolder opacity-10"></th>
        </tr>
    </thead>
    <tbody>
        @foreach($projects as $project)
        <tr>
            <td>
                <div class="d-flex px-2">
                    <div class="my-auto">
                        <a href="{{route('admin.projects.show',$project->id)}}">
                            <h6 class="mb-0 text-sm" style="color: blue !important;"><b>{{strtoupper($project->name)}}</b></h6>
                        </a>
                    </div>
                </div>
            </td>
            <td>
                <p class="text-sm font-weight-bold mb-0 text-success">€{{$project->budget}}</p>
            </td>
            <td>
                <p class="text-sm font-weight-bold mb-0 text-success">€{{$project->cost_per_hour}}</p>
            </td>
            <td>
                <p class="text-sm font-weight-bold mb-0 text-success">%{{$project->tva}}</p>
            </td>
            @php 
                $projecttasksId = App\Models\Tasks::where('project_id', $project->id)->pluck('id');
                $totalduration = App\Models\tasks_timer::whereIn('task_id', $projecttasksId)->sum('duration');
                $difference = $project->estimated_hours * 60 - $totalduration;
                
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
                        <p class="text-sm font-weight-bold mb-0 text-success">+{{$diffhours}}h:{{$diffminutes}}m</p>
                        @elseif($difference < 0)
                        <p class="text-sm font-weight-bold mb-0 text-danger">-{{$diffhours}}h:{{$diffminutes}}m</p>
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
                <p class="text-sm font-weight-bold mb-0">
                    <a href="{{route('admin.clients.show', $project->client_id)}}">
                        {{App\Models\Client::where('id', $project->client_id)->first()->name}}
                    </a>
                </p>
            </td>
            <td>
                <p class="text-sm font-weight-bold mb-0">
                    @forEach(App\Models\project_teams::where('project_id', $project->id)->get() as $project_team)
                    <a href="{{route('admin.teams.index')}}">
                        {{$project_team->team_name}}
                    </a>
                    <br>
                    @endforEach
                </p>
            </td>
            <td class="align-middle">
                <form action="{{ route('admin.projects.destroy', $project->id)}}" method="post" onsubmit="return confirm('Are you sure?');" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-xs p-2 mb-0">X</button>
                </form>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
<div class="d-flex justify-content-center">
    {{ $projects->appends(request()->except('page'))->links() }} 