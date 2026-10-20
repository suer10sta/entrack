<!DOCTYPE html>
<html lang="en">
<head>
    <x-favicon />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.Archived Projects') }}</title>
    @vite('resources/css/app.css')
    <style>
        /* Mobile: show project rows as stacked cards (same pattern as other Reports pages) */
        @media (max-width: 1024px) {
            #projectsTable thead {
                display: none;
            }
            #projectsTable tbody {
                display: block;
            }
            #projectsTable tbody tr {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 10px;
                margin-bottom: 16px;
                border: 1px solid rgba(148, 163, 184, 0.55);
                border-radius: 16px;
                background: linear-gradient(180deg, rgba(255,255,255,0.98) 0%, rgba(248,250,252,0.98) 100%);
                overflow: hidden;
                box-shadow:
                    0 12px 30px rgba(15, 23, 42, 0.08),
                    0 2px 6px rgba(15, 23, 42, 0.06);
                padding: 12px;
            }
            #projectsTable tbody tr:hover {
                border-color: rgba(45, 112, 128, 0.45);
            }
            #projectsTable tbody td {
                display: block;
                width: 100%;
                padding: 10px 12px;
                border: 1px solid rgba(226, 232, 240, 0.9);
                border-radius: 12px;
                background: rgba(255,255,255,0.75);
                text-align: left !important;
                white-space: normal !important;
                word-break: break-word;
            }
            #projectsTable tbody td[data-label]::before {
                content: attr(data-label);
                display: block;
                font-size: 11px;
                font-weight: 700;
                letter-spacing: .06em;
                text-transform: uppercase;
                color: #64748b;
                margin-bottom: 6px;
            }
        }
    </style>
</head>
<body class="dt-app-shell bg-[#f8fafc] text-slate-900 antialiased flex h-screen overflow-hidden">
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
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-[#f8fafc] p-4 sm:p-6 lg:px-20 lg:py-8">
            {{-- Page heading with back navigation (same design as Employee Archives page) --}}
            @php
                $returnRoute = '#';
                if(Auth::user()->hasRole('Admin')) $returnRoute = route('admin.projects.index');
                elseif(Auth::user()->hasRole('Responsable de projet')) $returnRoute = route('Responsable_de_projet.projects.index');
                elseif(Auth::user()->hasRole('Employee')) $returnRoute = route('employee.projects.index');
                elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire')) $returnRoute = route('Responsable_gestionnaire.projects.index');
            @endphp
            <div class="flex items-center gap-3 mb-5">

                <div class="min-w-0">
                    <p class="dt-overline mb-1">{{ __('messages.Reports') }}</p>
                    <h1 class="text-[22px] sm:text-[24px] font-bold tracking-tight text-[#0B2638] leading-tight truncate">
                        {{ __('messages.Archived Projects') }}
                    </h1>
                </div>
            </div>

            {{-- Session message --}}
            @if(session('message'))
                <div class="mb-5 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl relative flex items-center gap-2" role="alert">
                    <span class="text-[13px] font-medium">{{ session('message') }}</span>
                    <button type="button" onclick="this.parentElement.style.display='none'" class="absolute top-0 right-0 px-4 py-3 text-emerald-600 hover:text-emerald-800 font-bold text-xl">&times;</button>
                </div>
            @endif

            {{-- Table --}}
            <div class="dt-card overflow-hidden">
                <div class="overflow-x-auto custom-scrollbar">
                <table id="projectsTable" class="w-full text-left border-collapse">
                    <thead class="border-b border-slate-100 bg-slate-50/80">
                        <tr class="dt-table-head">
                            <th class="px-4 py-3 text-left whitespace-nowrap min-w-[200px]">{{ __('messages.Project column') }}</th>
                            @if(Auth::user()->hasRole('Admin'))
                            <th class="px-4 py-3 text-left whitespace-nowrap">{{ __('messages.Budget column') }}</th>
                            <th class="px-4 py-3 text-left whitespace-nowrap">{{ __('messages.CPH') }}</th>
                            <th class="px-4 py-3 text-left whitespace-nowrap">{{ __('messages.Duration column') }}</th>
                            <th class="px-4 py-3 text-center whitespace-nowrap">{{ __('messages.Est. Hours column') }}</th>
                            <th class="px-4 py-3 text-left whitespace-nowrap">{{ __('messages.Difference column') }}</th>
                            <th class="px-4 py-3 text-left whitespace-nowrap min-w-[150px]">{{ __('messages.Progress column') }}</th>
                            @endif
                            <th class="px-4 py-3 text-left whitespace-nowrap">{{ __('messages.VAT') }}</th>
                            <th class="px-4 py-3 text-left whitespace-nowrap min-w-[150px]">{{ __('messages.Client') }}</th>
                            <th class="px-4 py-3 text-left whitespace-nowrap min-w-[130px]">{{ __('messages.Teams column header') }}</th>
                            @can('Delete project')
                            <th class="px-4 py-3 text-center whitespace-nowrap min-w-[110px]">{{ __('messages.Actions') }}</th>
                            @endcan
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($projects as $project)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="px-4 py-3 text-[13px] font-medium text-[#0B2638] editable" data-label="{{ __('messages.Project column') }}" data-id="{{$project->id}}" data-column="name">
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
                                    <a href="{{ $projectShowRoute }}" class="text-[#2D7080] hover:text-[#0B2638] hover:underline font-medium">{{ strtoupper($project->name) }}</a>
                                @else
                                    <span class="font-medium text-[#0B2638]">{{ strtoupper($project->name) }}</span>
                                @endif
                            </td>

                            @if(Auth::user()->hasRole('Admin'))
                            <td class="px-4 py-3 text-[13px] text-emerald-700 font-semibold editable whitespace-nowrap" data-label="{{ __('messages.Budget column') }}" data-id="{{$project->id}}" data-column="budget">
                                €{{$project->budget}}
                            </td>
                            <td class="px-4 py-3 text-[13px] text-emerald-700 font-semibold editable whitespace-nowrap" data-label="{{ __('messages.CPH') }}" data-id="{{$project->id}}" data-column="cost_per_hour">
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

                            <td class="px-4 py-3 text-[13px] text-slate-600 whitespace-nowrap" data-label="{{ __('messages.Duration column') }}">{{$hours}}h:{{$minutes}}m</td>
                            <td class="px-4 py-3 text-[13px] text-slate-600 text-center whitespace-nowrap editable" data-label="{{ __('messages.Est. Hours column') }}" data-id="{{$project->id}}" data-column="estimated_hours">
                                {{$project->estimated_hours}}h
                            </td>
                            <td class="px-4 py-3 text-[13px] font-medium whitespace-nowrap" data-label="{{ __('messages.Difference column') }}">
                                @if($project->estimated_hours !== null)
                                    @if($difference > 0)
                                        <span class="dt-badge dt-badge-success">+{{$diffhours}}h:{{$diffminutes}}m</span>
                                    @elseif($difference < 0)
                                        <span class="dt-badge dt-badge-danger">-{{$diffhours}}h:{{$diffminutes}}m</span>
                                    @else
                                        <span class="dt-badge dt-badge-navy">{{$diffhours}}h:{{$diffminutes}}m</span>
                                    @endif
                                @endif
                            </td>
                            <td class="px-4 py-3 text-[13px] text-slate-600" data-label="{{ __('messages.Progress column') }}">
                                <div class="flex items-center gap-2">
                                    @php
                                        $progressColor = 'bg-emerald-500';
                                        if ($progressPercentage >= 75) $progressColor = 'bg-red-500';
                                        elseif ($progressPercentage >= 50) $progressColor = 'bg-amber-500';
                                    @endphp
                                    <div class="w-full max-w-[110px] bg-slate-200 rounded-full h-1.5 overflow-hidden">
                                        <div class="{{ $progressColor }} h-1.5 rounded-full" style="width: {{$clampedProgressPercentage}}%"></div>
                                    </div>
                                    <span class="text-[11px] font-semibold text-slate-500 whitespace-nowrap">{{$formattedProgressPercentage}}%</span>
                                </div>
                            </td>
                            @endif

                            <td class="px-4 py-3 text-[13px] text-emerald-700 font-semibold editable whitespace-nowrap" data-label="{{ __('messages.VAT') }}" data-id="{{$project->id}}" data-column="tva">
                                %{{$project->tva}}
                            </td>
                            <td class="px-4 py-3 text-[13px] text-slate-600" data-label="{{ __('messages.Client') }}">
                                @php $clientName = $project->client_id ? (App\Models\Client::where('id', $project->client_id)->first()->name ?? 'No client') : 'No client'; $clientRoute = '#'; @endphp
                                @if($project->client_id && Auth::user()->hasRole('Admin') && Auth::user()->can('View client'))
                                    @php $clientRoute = route('admin.clients.show', $project->client_id); @endphp
                                @elseif($project->client_id && Auth::user()->hasRole('Responsable de projet') && Auth::user()->can('View client'))
                                    @php $clientRoute = route('Responsable_de_projet.clients.show', $project->client_id); @endphp
                                @elseif($project->client_id && Auth::user()->hasRole('Employee') && Auth::user()->can('View client'))
                                    @php $clientRoute = route('employee.clients.show', $project->client_id); @endphp
                                @elseif($project->client_id && Auth::user()->hasRole('Responsable de projet et gestionnaire') && Auth::user()->can('View client'))
                                    @php $clientRoute = route('Responsable_gestionnaire.clients.show', $project->client_id); @endphp
                                @endif

                                @if($clientRoute != '#')
                                    <a href="{{ $clientRoute }}" class="text-[#2D7080] hover:text-[#0B2638] hover:underline font-medium">{{ $clientName }}</a>
                                @else
                                    <span>{{ $clientName }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-[13px] text-slate-600" data-label="{{ __('messages.Teams column header') }}">
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
                                        <a href="{{ $teamRoute }}" class="text-[#2D7080] hover:text-[#0B2638] hover:underline block">{{$project_team->team_name}}</a>
                                    @else
                                        <span class="block">{{$project_team->team_name}}</span>
                                    @endif
                                @endforeach
                            </td>

                            @can('Delete project')
                            <td class="px-4 py-3 text-[13px]" data-label="{{ __('messages.Actions') }}">
                                <div class="flex items-center justify-center gap-2">
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
                                    <button type="submit" title="{{ __('messages.Unarchive') }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-slate-200 bg-white text-slate-500 hover:bg-slate-100 hover:border-slate-300 transition-colors">
                                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                                    </button>
                                </form>
                                <form action="{{ $deleteRoute }}" method="post" onsubmit="return confirm('{{ __('messages.Are you sure?') }}');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" title="{{ __('messages.Delete') }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-slate-200 bg-white text-red-600 hover:bg-red-50 hover:border-red-200 transition-colors">
                                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862A2 2 0 015.995 19.142L5 7m5 4v6m4-6v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3M4 7h16" /></svg>
                                    </button>
                                </form>
                                </div>
                            </td>
                            @endcan
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ Auth::user()->hasRole('Admin') ? 11 : 4 }}" class="px-6 py-10 text-center">
                                <div class="dt-empty-icon mx-auto mb-3">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"/></svg>
                                </div>
                                <p class="text-[13.5px] font-semibold text-[#0B2638]">{{ __('messages.No projects found.') }}</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                </div>
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
