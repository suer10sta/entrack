<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <x-favicon />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.Teams - DalyTrac') }}</title> {{-- Updated title --}}
    @vite('resources/css/app.css')
    @livewireStyles
    {{-- Include dashboard_layouts.head if it contains essential meta tags or CSS links not covered by Vite --}}
    {{-- @include('dashboard_layouts.head') --}}
    <style>
        /* Mobile: show team rows as stacked cards (same pattern as other Reports pages) */
        @media (max-width: 1024px) {
            #teamsTable thead {
                display: none;
            }
            #teamsTable tbody {
                display: block;
            }
            #teamsTable tbody tr {
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
            #teamsTable tbody tr:hover {
                border-color: rgba(45, 112, 128, 0.45);
            }
            #teamsTable tbody td {
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
            #teamsTable tbody td[data-span="2"] {
                grid-column: 1 / -1;
            }
            #teamsTable tbody td[data-label]::before {
                content: attr(data-label);
                display: block;
                font-size: 11px;
                font-weight: 700;
                letter-spacing: .06em;
                text-transform: uppercase;
                color: #64748b;
                margin-bottom: 6px;
            }
            #teamsTable tbody td[data-label=""]::before {
                content: none;
            }
        }
    </style>
</head>
<body class="dt-app-shell bg-[#f8fafc] text-slate-900 antialiased flex h-screen overflow-hidden">

    {{-- Sidebar Component --}}
    @if(Auth::user()->hasRole('Admin'))
        <x-dashboard.sidebar />
    @else
        <x-employees.sidebar />
    @endif

    {{-- Main Wrapper (Navbar + Content) --}}
    <div class="flex flex-col flex-1 overflow-y-auto">

        {{-- Navbar Component --}}
        <x-dashboard.navbar />

        {{-- Main Content Area --}}
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-[#f8fafc] p-4 sm:p-6 lg:px-20 lg:py-8">

            {{-- Page heading (same design as Clients/Employee Reports pages) --}}
            <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-5">
                <div class="min-w-0">
                    <p class="dt-overline mb-1">{{ __('messages.Reports') }}</p>
                    <h1 class="text-[22px] sm:text-[24px] font-bold tracking-tight text-[#0B2638] leading-tight">
                        {{ __('messages.Teams Table Overview') }}
                    </h1>
                </div>

                {{-- Buttons --}}
                <div class="flex flex-col sm:flex-row sm:flex-wrap gap-2 w-full sm:w-auto">
                    {{-- Add Team Button --}}
                    @can('Add team')
                        @php
                            $createRoute = '#'; // Default fallback
                            if (Auth::user()->hasRole('Admin')) $createRoute = route('admin.teams.create');
                            elseif (Auth::user()->hasRole('Responsable de projet')) $createRoute = route('Responsable_de_projet.teams.create');
                            elseif (Auth::user()->hasRole('Employee')) $createRoute = route('employee.teams.create');
                            elseif (Auth::user()->hasRole('Responsable de projet et gestionnaire')) $createRoute = route('Responsable_gestionnaire.teams.create');
                        @endphp
                        <a href="{{ $createRoute }}" class="dt-btn dt-btn-teal w-full sm:w-auto">
                            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                            {{ __('messages.Add Team') }}
                        </a>
                    @endcan

                    {{-- Report Button --}}
                    @php
                        $reportRoute = '#'; // Default fallback
                        if (Auth::user()->hasRole('Admin')) $reportRoute = route('admin.Teamssrepports.downloadRepports');
                        elseif (Auth::user()->hasRole('Responsable de projet')) $reportRoute = route('Responsable_de_projet.Teamssrepports.downloadRepports');
                        elseif (Auth::user()->hasRole('Employee')) $reportRoute = route('employee.Teamssrepports.downloadRepports');
                        elseif (Auth::user()->hasRole('Responsable de projet et gestionnaire')) $reportRoute = route('Responsable_gestionnaire.Teamssrepports.downloadRepports');
                    @endphp
                    <a href="{{ $reportRoute }}" class="dt-btn dt-btn-ghost w-full sm:w-auto">
                       <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                        {{ __('messages.Teams Reports PDF') }}
                    </a>
                </div>
            </div>

            {{-- Container for the top table --}}
            <div class="dt-card overflow-hidden mb-6">

                {{-- Session Message Alert --}}
                @if(session('message'))
                    <div class="m-4 sm:m-5 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl relative flex items-center gap-2" role="alert">
                        <span class="block sm:inline text-[13px] font-medium">{{ session('message') }}</span>
                        <button type="button" onclick="this.parentElement.style.display='none'" class="absolute top-0 bottom-0 right-0 px-4 py-3 text-emerald-600 hover:text-emerald-800 font-bold text-xl" aria-label="{{ __('messages.Close') }}">
                           &times;
                        </button>
                    </div>
                @endif

                {{-- Table Wrapper --}}
                <div class="overflow-x-auto custom-scrollbar">
                        <table id="teamsTable" class="w-full text-left border-collapse min-w-full">
                            <thead class="border-b border-slate-100 bg-slate-50/80">
                                <tr class="dt-table-head">
                                    <th class="px-4 py-3 text-left whitespace-nowrap min-w-[200px]">{{ __('messages.Team') }}</th>
                                    <th class="px-4 py-3 text-left whitespace-nowrap min-w-[220px]">{{ __('messages.Projects') }}</th>
                                    <th class="px-4 py-3 text-left whitespace-nowrap min-w-[220px]">{{ __('messages.Tasks column') }}</th>
                                    <th class="px-4 py-3 text-right whitespace-nowrap min-w-[60px]"></th> {{-- Empty header for ellipsis --}}
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($teams as $team)
                                <tr class="hover:bg-slate-50/70 transition-colors">
                                    {{-- Team Name + Avatar --}}
                                    <td data-label="{{ __('messages.Team') }}" data-span="2" class="px-4 py-3 whitespace-nowrap">
                                        <div class="flex items-center">
                                            @php
                                                $colors = ['#2ECC71', '#BDC3C7', '#F39C12', '#E74C3C', '#9B59B6', '#2E86C1','#641E16','#2C3E50','#F4D03F','#F4D03F'];
                                                $bgColor = $colors[$loop->index % count($colors)];
                                            @endphp
                                            <div class="h-9 w-9 rounded-full flex items-center justify-center text-white text-[13px] font-semibold flex-shrink-0" style="background-color: {{ $bgColor }};">
                                                {{ strtoupper(substr($team->name, 0, 1)) }}
                                            </div>
                                            <div class="ml-3">
                                                <div class="text-[13px] font-semibold text-[#0B2638]">{{$team->name}}</div>
                                            </div>
                                        </div>
                                    </td>
                                    {{-- Projects List --}}
                                    <td data-label="{{ __('messages.Projects') }}" data-span="2" class="px-4 py-3 text-[13px] text-slate-600">
                                        <div class="flex flex-wrap gap-1.5">
                                            {{-- Optimize project fetching if needed --}}
                                            @foreach($project_teams->where('team_id', $team->id) as $project_team)
                                                @php $project = App\Models\Project::find($project_team->project_id); @endphp
                                                @if($project)
                                                    @php
                                                        $projectShowRoute = '#';
                                                        $canViewProject = Auth::user()->can('View project');
                                                        if($canViewProject) {
                                                            if (Auth::user()->hasRole('Admin')) $projectShowRoute = route('admin.projects.show', $project->id);
                                                            elseif (Auth::user()->hasRole('Responsable de projet')) $projectShowRoute = route('Responsable_de_projet.projects.show', $project->id);
                                                            elseif (Auth::user()->hasRole('Employee')) $projectShowRoute = route('employee.projects.show', $project->id);
                                                            // Add other roles if needed
                                                        }
                                                    @endphp
                                                    <span class="dt-badge dt-badge-navy">
                                                        <span class="dt-dot"></span>
                                                        @if($canViewProject)
                                                            <a href="{{ $projectShowRoute }}" class="hover:underline">{{ $project->name }}</a>
                                                        @else
                                                            {{ $project->name }}
                                                        @endif
                                                    </span>
                                                @endif
                                            @endforeach
                                        </div>
                                    </td>
                                    {{-- Tasks List --}}
                                    <td data-label="{{ __('messages.Tasks column') }}" data-span="2" class="px-4 py-3 text-[13px] text-slate-600">
                                        <div class="flex flex-wrap gap-1.5">
                                            @php
                                                $taskIndexRoute = '#';
                                                $canViewTask = Auth::user()->can('View task');
                                                if ($canViewTask) {
                                                     if (Auth::user()->hasRole('Admin')) $taskIndexRoute = route('admin.tasks.index');
                                                     elseif (Auth::user()->hasRole('Responsable de projet')) $taskIndexRoute = route('Responsable_de_projet.tasks.index');
                                                     elseif (Auth::user()->hasRole('Employee')) $taskIndexRoute = route('employee.tasks.index');
                                                     // Add other roles if needed
                                                }
                                                // Optimize task fetching if needed
                                                $tasks = App\Models\team_tasks::where('team_id', $team->id)->get();
                                            @endphp
                                            @foreach($tasks as $task)
                                                <span class="dt-badge dt-badge-success">
                                                    <span class="dt-dot"></span>
                                                    @if($canViewTask)
                                                         <a href="{{ $taskIndexRoute }}" class="hover:underline">{{$task->task_name}}</a>
                                                    @else
                                                        {{$task->task_name}}
                                                    @endif
                                                </span>
                                            @endforeach
                                        </div>
                                    </td>
                                    {{-- Ellipsis Button (Placeholder) --}}
                                    <td data-label="{{ __('messages.Action') }}" class="px-4 py-3 whitespace-nowrap text-right text-sm font-medium">
                                        <button class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                              <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                     <td colspan="4" class="px-6 py-10 text-center">
                                         <div class="dt-empty-icon mx-auto mb-3">
                                             <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 21v-2a4 4 0 00-8 0v2m8 0H5a2 2 0 01-2-2V7a2 2 0 012-2h5l2 2h5a2 2 0 012 2v12a2 2 0 01-2 2z"/></svg>
                                         </div>
                                         <p class="text-[13.5px] font-semibold text-[#0B2638]">{{ __('messages.No teams found.') }}</p>
                                     </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                </div>
            </div>

            {{-- Team Cards Grid --}}
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                 @foreach($teams as $team)
                    <div class="dt-card dt-card-hover flex flex-col overflow-hidden">
                        {{-- Card Header --}}
                        <div class="px-5 py-4 border-b border-slate-100 flex justify-between items-start gap-3">
                             <div class="min-w-0">
                                 <h5 class="dt-section-title capitalize truncate">{{$team->name}}</h5>
                                 <p class="dt-section-subtitle mt-0.5">{{ __('messages.Team Members:') }}</p>
                             </div>
                             {{-- Delete Button --}}
                             @can('Delete team')
                                @php
                                    $deleteRoute = '#';
                                    if (Auth::user()->hasRole('Admin')) $deleteRoute = route('admin.teams.destroy',$team->id);
                                    elseif (Auth::user()->hasRole('Responsable de projet')) $deleteRoute = route('Responsable_de_projet.teams.destroy',$team->id);
                                    elseif (Auth::user()->hasRole('Employee')) $deleteRoute = route('employee.teams.destroy',$team->id);
                                    elseif (Auth::user()->hasRole('Responsable de projet et gestionnaire')) $deleteRoute = route('Responsable_gestionnaire.teams.destroy',$team->id);
                                @endphp
                                <form action="{{ $deleteRoute }}" method="post" onsubmit="return confirm(@js(__('messages.Are you sure?')));" class="inline flex-shrink-0">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" title="{{ __('messages.Delete') }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-slate-200 bg-white text-red-600 hover:bg-red-50 hover:border-red-200 transition-colors">
                                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                                    </button>
                                </form>
                             @endcan
                        </div>

                        {{-- Card Body (Members List) --}}
                        <div class="px-5 py-4 flex-grow">
                            <ul class="space-y-3">
                                {{-- Optimize member fetching if needed --}}
                                @php $team_members = App\Models\team_members::where('team_id', $team->id)->get(); @endphp
                                @forelse($team_members as $team_member)
                                    @php
                                        $employee = App\Models\Employees::find($team_member->employee_id);
                                        $employee_user = $employee ? App\Models\User::find($employee->user_id) : null;
                                        $memberShowRoute = '#';
                                        $canViewMember = Auth::user()->can('View employee');
                                        if($canViewMember && $employee) {
                                             if (Auth::user()->hasRole('Admin')) $memberShowRoute = route('admin.employees.show', $employee->id);
                                             elseif (Auth::user()->hasRole('Responsable de projet')) $memberShowRoute = route('Responsable_de_projet.employees.show', $employee->id);
                                             elseif (Auth::user()->hasRole('Employee')) $memberShowRoute = route('employee.employees.show', $employee->id);
                                             // Add other roles if needed
                                        }
                                    @endphp
                                    @if($employee) {{-- Ensure employee exists --}}
                                    <li class="flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            {{-- Avatar/Initials --}}
                                            @php
                                                $colors = ['#2ECC71', '#BDC3C7', '#F39C12', '#E74C3C', '#9B59B6', '#2E86C1','#641E16','#2C3E50','#F4D03F','#F4D03F'];
                                                $bgColor = $colors[$loop->index % count($colors)];
                                            @endphp
                                            @if($employee->image)
                                                <img class="h-8 w-8 rounded-full object-cover" src="{{ Storage::disk('public')->url('employee_images/' . $employee->image) }}" alt="{{ $employee->name }}">
                                            @else
                                                <div class="h-8 w-8 rounded-full flex items-center justify-center text-white text-xs font-medium" style="background-color: {{ $bgColor }};">
                                                    {{ strtoupper(substr($employee->name, 0, 1)) }}
                                                </div>
                                            @endif
                                            {{-- Name and Status --}}
                                            <div class="min-w-0">
                                                <h6 class="text-[13px] font-semibold text-[#0B2638] mb-1 truncate">
                                                    @if($canViewMember)
                                                        <a href="{{ $memberShowRoute }}" class="text-[#2D7080] hover:text-[#0B2638] hover:underline">{{$employee->name}}</a>
                                                    @else
                                                        {{$employee->name}}
                                                    @endif
                                                </h6>
                                                @if($employee_user)
                                                    @if($employee_user->is_loggedIn == 0)
                                                        <span class="dt-badge dt-badge-danger"><span class="dt-dot"></span>{{ __('messages.Off Work') }}</span>
                                                    @elseif($employee_user->is_loggedIn == 1)
                                                        <span class="dt-badge dt-badge-success"><span class="dt-dot"></span>{{ __('messages.On Work') }}</span>
                                                    @else
                                                        <span class="dt-badge dt-badge-warning"><span class="dt-dot"></span>{{ __('messages.On Pause') }}</span>
                                                    @endif
                                                @endif
                                            </div>
                                        </div>
                                        {{-- View Button --}}
                                        @if($canViewMember)
                                           <a href="{{ $memberShowRoute }}" class="dt-btn dt-btn-ghost !px-2.5 !py-1 !text-[11px] flex-shrink-0">
                                               {{ __('messages.View') }}
                                           </a>
                                        @endif
                                    </li>
                                    @endif
                                @empty
                                    <li class="rounded-xl border border-dashed border-slate-200 px-4 py-5 text-center text-[13px] text-slate-500">{{ __('messages.No members in this team.') }}</li>
                                @endforelse
                            </ul>
                        </div>

                        {{-- Card Footer (Edit Button) --}}
                        <div class="px-5 py-3.5 border-t border-slate-100 text-right mt-auto">
                             @can('Update team')
                                @php
                                    $editRoute = '#';
                                    if (Auth::user()->hasRole('Admin')) $editRoute = route('admin.teams.edit',$team->id);
                                    elseif (Auth::user()->hasRole('Responsable de projet')) $editRoute = route('Responsable_de_projet.teams.edit',$team->id);
                                    elseif (Auth::user()->hasRole('Employee')) $editRoute = route('employee.teams.edit',$team->id);
                                    elseif (Auth::user()->hasRole('Responsable de projet et gestionnaire')) $editRoute = route('Responsable_gestionnaire.teams.edit',$team->id);
                                @endphp
                                <a href="{{ $editRoute }}" class="dt-btn dt-btn-teal !px-3.5 !py-1.5 !text-xs w-full sm:w-auto">
                                    {{ __('messages.Edit Team') }}
                                </a>
                             @endcan
                        </div>
                    </div>
                @endforeach
            </div>

        </main>
    </div>

    @livewireScripts
    {{-- @include('dashboard_layouts.scripts') --}}

</body>
</html>
