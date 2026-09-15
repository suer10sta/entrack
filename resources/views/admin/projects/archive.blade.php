<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.Archived Projects') }}</title>
    @vite('resources/css/app.css')
</head>
<body class="bg-gray-50 flex h-screen overflow-hidden">
    {{-- Sidebar --}}
    @if(Auth::user()->hasRole('Admin'))
        <x-dashboard.sidebar />
    @else
        <x-employees.sidebar />
    @endif

    <div class="flex flex-col flex-1 overflow-y-auto">
        {{-- Navbar --}}
        <x-dashboard.navbar />

        {{-- Main Content --}}
        <main class="flex-1 p-6 bg-[#eaebef]">
            {{-- Header --}}
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center bg-white p-6 rounded-lg shadow border border-gray-200 mb-4">
                <h1 class="text-lg font-semibold text-gray-900">{{ __('messages.Archived Projects') }}</h1>
                <div class="flex flex-wrap gap-2 mt-4 sm:mt-0">
                    @php
                        $returnRoute = '#';
                        if(Auth::user()->hasRole('Admin')) $returnRoute = route('admin.projects.index');
                        elseif(Auth::user()->hasRole('Responsable de projet')) $returnRoute = route('Responsable_de_projet.projects.index');
                        elseif(Auth::user()->hasRole('Employee')) $returnRoute = route('employee.projects.index');
                        elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire')) $returnRoute = route('Responsable_gestionnaire.projects.index');
                    @endphp
                    <a href="{{ $returnRoute }}" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg">
                        {{ __('messages.Return Back') }}
                    </a>
                </div>
            </div>

            {{-- Session message --}}
            @if(session('message'))
                <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded relative" role="alert">
                    {{ session('message') }}
                    <button type="button" onclick="this.parentElement.style.display='none'" class="absolute top-0 right-0 px-4 py-3 text-green-700 hover:text-green-900 font-bold text-xl">&times;</button>
                </div>
            @endif

            {{-- Table --}}
            <div class="bg-white rounded-lg shadow border border-gray-200 overflow-x-auto">
                <table class="w-full text-left border-collapse table-fixed">
                    <thead class="border-b bg-gray-50">
                        <tr class="text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            <th class="px-3 py-3 text-sm font-bold text-gray-600 uppercase tracking-wider text-left " style="width: 10%;">{{ __('messages.Project column') }}</th>
                            @if(Auth::user()->hasRole('Admin'))
                            <th class="px-3 py-3 text-sm font-bold text-gray-600 uppercase tracking-wider text-left" style="width: 5%;">{{ __('messages.Budget column') }}</th>
                            <th class="px-3 py-3 text-sm font-bold text-gray-600 uppercase tracking-wider text-left" style="width: 5%;">{{ __('messages.CPH') }}</th>
                            <th class="px-3 py-3 text-sm font-bold text-gray-600 uppercase tracking-wider text-left" style="width: 5%;">{{ __('messages.Duration column') }}</th>
                            <th class="px-3 py-3 text-sm font-bold text-gray-600 uppercase tracking-wider text-center" style="width: 5%;">{{ __('messages.Est. Hours column') }}</th>
                            <th class="px-3 py-3 text-sm font-bold text-gray-600 uppercase tracking-wider text-left" style="width: 5%;">{{ __('messages.Difference column') }}</th>
                            <th class="px-3 py-3 text-sm font-bold text-gray-600 uppercase tracking-wider text-center" style="width: 5%;">{{ __('messages.Progress column') }}</th>
                            @endif
                            <th class="px-3 py-3 text-sm font-bold text-gray-600 uppercase tracking-wider text-left" style="width: 5%;">{{ __('messages.VAT') }}</th>
                            <th class="px-3 py-3 text-sm font-bold text-gray-600 uppercase tracking-wider text-left" style="width: 5%;">{{ __('messages.Client') }}</th>
                            <th class="px-3 py-3 text-sm font-bold text-gray-600 uppercase tracking-wider text-left" style="width: 4%;">{{ __('messages.Teams column header') }}</th>
                            @can('Delete project')
                            <th class="px-3 py-3 text-sm font-bold text-gray-600 uppercase tracking-wider text-left" style="width: 8%;">{{ __('messages.Actions') }}</th>
                            @endcan
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($projects as $project)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm editable" data-id="{{$project->id}}" data-column="name">
                                @php $projectShowRoute = '#'; @endphp
                                @if(Auth::user()->hasRole('Admin') && Auth::user()->can('View project'))
                                    @php $projectShowRoute = route('admin.projects.show',$project->id); @endphp
                                @elseif(Auth::user()->hasRole('Responsable de projet') && Auth::user()->can('View project'))
                                    @php $projectShowRoute = route('Responsable_de_projet.projects.show',$project->id); @endphp
                                @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire') && Auth::user()->can('View project'))
                                    @php $projectShowRoute = route('Responsable_gestionnaire.projects.show',$project->id); @endphp
                                @elseif(Auth::user()->hasRole('Employee') && Auth::user()->can('View project'))
                                    @php $projectShowRoute = route('employee.projects.show',$project->id); @endphp
                                @endif

                                @if($projectShowRoute != '#')
                                    <a href="{{ $projectShowRoute }}" class="text-blue-600 hover:underline font-medium">{{ strtoupper($project->name) }}</a>
                                @else
                                    <span class="font-medium text-gray-900">{{ strtoupper($project->name) }}</span>
                                @endif
                            </td>
                            
                            @if(Auth::user()->hasRole('Admin'))
                            <td class="px-6 py-4 text-sm text-green-600 font-medium editable" data-id="{{$project->id}}" data-column="budget">
                                €{{$project->budget}}
                            </td>
                            <td class="px-6 py-4 text-sm text-green-600 font-medium editable" data-id="{{$project->id}}" data-column="cost_per_hour">
                                €{{$project->cost_per_hour}}
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
                                // Clamp the visual width between 0% and 100% so the bar does not overflow its column
                                $clampedProgressPercentage = max(0, min($progressPercentage, 100));
                                $formattedProgressPercentage = number_format($progressPercentage, 2);
                            @endphp

                            <td class="px-6 py-4 text-sm text-gray-700">{{$hours}}h:{{$minutes}}m</td>
                            <td class="px-6 py-4 text-sm text-gray-700 editable" data-id="{{$project->id}}" data-column="estimated_hours">
                                {{$project->estimated_hours}}h
                            </td>
                            <td class="px-6 py-4 text-sm font-medium">
                                @if($project->estimated_hours !== null)
                                    @if($difference > 0)
                                        <span class="text-green-600">+{{$diffhours}}h:{{$diffminutes}}m</span>
                                    @elseif($difference < 0)
                                        <span class="text-red-600">-{{$diffhours}}h:{{$diffminutes}}m</span>
                                    @else
                                        <span class="text-blue-600">{{$diffhours}}h:{{$diffminutes}}m</span>
                                    @endif
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm min-w-[150px]">
                                <div class="w-full bg-gray-200 rounded-full h-3 relative overflow-hidden">
                                    @php
                                        $progressColor = 'bg-green-500';
                                        if ($progressPercentage >= 75) $progressColor = 'bg-red-500';
                                        elseif ($progressPercentage >= 50) $progressColor = 'bg-yellow-500';
                                    @endphp
                                    <div class="absolute inset-0 flex items-center justify-center">
                                        <span class="text-xs font-semibold {{ $progressPercentage > 40 ? 'text-white' : 'text-gray-700' }}">{{$formattedProgressPercentage}}%</span>
                                    </div>
                                    <div class="{{ $progressColor }} h-3 rounded-full" style="width: {{$clampedProgressPercentage}}%"></div>
                                </div>
                            </td>
                            @endif
                            
                            <td class="px-6 py-4 text-sm text-green-600 font-medium editable" data-id="{{$project->id}}" data-column="tva">
                                %{{$project->tva}}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700">
                                @php $clientName = App\Models\Client::where('id', $project->client_id)->first()->name; $clientRoute = '#'; @endphp
                                @if(Auth::user()->hasRole('Admin') && Auth::user()->can('View client'))
                                    @php $clientRoute = route('admin.clients.show', $project->client_id); @endphp
                                @elseif(Auth::user()->hasRole('Responsable de projet') && Auth::user()->can('View client'))
                                    @php $clientRoute = route('Responsable_de_projet.clients.show', $project->client_id); @endphp
                                @elseif(Auth::user()->hasRole('Employee') && Auth::user()->can('View client'))
                                    @php $clientRoute = route('employee.clients.show', $project->client_id); @endphp
                                @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire') && Auth::user()->can('View client'))
                                    @php $clientRoute = route('Responsable_gestionnaire.clients.show', $project->client_id); @endphp
                                @endif

                                @if($clientRoute != '#')
                                    <a href="{{ $clientRoute }}" class="text-blue-600 hover:underline">{{ $clientName }}</a>
                                @else
                                    <span>{{ $clientName }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700">
                                @foreach(App\Models\project_teams::where('project_id', $project->id)->get() as $project_team)
                                    @php $teamRoute = '#'; @endphp
                                    @if(Auth::user()->hasRole('Admin') && Auth::user()->can('View team'))
                                        @php $teamRoute = route('admin.teams.index'); @endphp
                                    @elseif(Auth::user()->hasRole('Responsable de projet') && Auth::user()->can('View team'))
                                        @php $teamRoute = route('Responsable_de_projet.teams.index'); @endphp
                                    @elseif(Auth::user()->hasRole('Employee') && Auth::user()->can('View team'))
                                        @php $teamRoute = route('employee.teams.index'); @endphp
                                    @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire') && Auth::user()->can('View team'))
                                        @php $teamRoute = route('Responsable_gestionnaire.teams.index'); @endphp
                                    @endif

                                    @if($teamRoute != '#')
                                        <a href="{{ $teamRoute }}" class="text-blue-600 hover:underline block">{{$project_team->team_name}}</a>
                                    @else
                                        <span class="block">{{$project_team->team_name}}</span>
                                    @endif
                                @endforeach
                            </td>
                            
                            @can('Delete project')
                            <td class="px-6 py-4 text-sm space-x-1">
                                @php
                                    $unarchiveRoute = '#'; $deleteRoute = '#';
                                    if(Auth::user()->hasRole('Admin')) {
                                        $unarchiveRoute = route('admin.projects.unarchive', $project->id);
                                        $deleteRoute = route('admin.projects.destroy', $project->id);
                                    } elseif(Auth::user()->hasRole('Responsable de projet')) {
                                        $unarchiveRoute = route('Responsable_de_projet.projects.unarchive', $project->id);
                                        $deleteRoute = route('Responsable_de_projet.projects.destroy', $project->id);
                                    } elseif(Auth::user()->hasRole('Employee')) {
                                        $unarchiveRoute = route('employee.projects.unarchive', $project->id);
                                        $deleteRoute = route('employee.projects.destroy', $project->id);
                                    } elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire')) {
                                        $unarchiveRoute = route('Responsable_gestionnaire.projects.unarchive', $project->id);
                                        $deleteRoute = route('Responsable_gestionnaire.projects.destroy', $project->id);
                                    }
                                @endphp
                                
                                <form action="{{ $unarchiveRoute }}" method="post" onsubmit="return confirm('{{ __('messages.Are you sure?') }}');" class="inline">
                                    @csrf
                                    <button type="submit" class="bg-gray-500 hover:bg-gray-600 text-white px-2 py-1 rounded text-xs">{{ __('messages.Unarchive') }}</button>
                                </form>
                                <form action="{{ $deleteRoute }}" method="post" onsubmit="return confirm('{{ __('messages.Are you sure?') }}');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-2 py-1 rounded text-xs">{{ __('messages.Delete') }}</button>
                                </form>
                            </td>
                            @endcan
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </main>
    </div>

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
            input[0].setSelectionRange(0, 0);
            
            input.blur(function(){
                var newContent = $(this).val();
                var projectId = $(this).parent().data('id');
                var columnName = $(this).parent().data('column');
                $(this).parent().text(newContent);
                
                var updatedUrl = '';
                @if(Auth::user()->hasRole('Responsable de projet'))
                    updatedUrl = '/Responsable_de_projet/projects/updateProject';
                @elseif(Auth::user()->hasRole('Admin'))
                    updatedUrl = '/admin/projects/updateProject';
                @elseif(Auth::user()->hasRole('Employee'))
                    updatedUrl = '/employee/projects/updateProject';
                @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                    updatedUrl = '/Responsable_gestionnaire/projects/updateProject';
                @endif
                
                $.ajax({
                    url: updatedUrl,
                    type: 'post',
                    data: {
                        content: newContent,
                        id: projectId,
                        column: columnName,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        location.reload();
                    },
                    error: function(response) {
                        console.error("Update failed:", response);
                        $(this).parent().text(originalContent);
                    }
                });
            });
            
            input.keypress(function(e) {
                if(e.which == 13) {
                    $(this).blur();
                }
            });
        });
    });
    </script>
    @endcan
</body>
</html>
