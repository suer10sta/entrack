<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teams - DailyTrac</title> {{-- Updated title --}}
    @vite('resources/css/app.css')
    @livewireStyles
    {{-- Include dashboard_layouts.head if it contains essential meta tags or CSS links not covered by Vite --}}
    {{-- @include('dashboard_layouts.head') --}}
</head>
<body class="bg-gray-50 flex h-screen overflow-hidden">

    {{-- Sidebar Component --}}
    <x-dashboard.sidebar />

    {{-- Main Wrapper (Navbar + Content) --}}
    <div class="flex flex-col flex-1 overflow-y-auto">

        {{-- Navbar Component --}}
        <x-dashboard.navbar />

        {{-- Main Content Area --}}
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-[#eaebef] p-8">

            {{-- Container for the top table --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden mb-8"> {{-- Added margin-bottom --}}

                {{-- Header Section --}}
                <div class="p-6 border-b border-gray-200 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <h6 class="text-lg font-semibold text-gray-900">Teams Table Overview</h6>

                    {{-- Buttons --}}
                    <div class="flex flex-wrap gap-2">
                        {{-- Add Team Button --}}
                        @can('Add team')
                            @php
                                $createRoute = '#'; // Default fallback
                                if (Auth::user()->hasRole('Admin')) $createRoute = route('admin.teams.create');
                                elseif (Auth::user()->hasRole('Responsable de projet')) $createRoute = route('Responsable_de_projet.teams.create');
                                elseif (Auth::user()->hasRole('Employee')) $createRoute = route('employee.teams.create');
                                elseif (Auth::user()->hasRole('Responsable de projet et gestionnaire')) $createRoute = route('Responsable_gestionnaire.teams.create');
                            @endphp
                            <a href="{{ $createRoute }}" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg flex items-center gap-1.5">
                                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                                Add Team
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
                        <a href="{{ $reportRoute }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 border border-gray-300 text-sm font-medium px-4 py-2 rounded-lg flex items-center gap-1.5">
                           <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                           Teams Reports PDF
                        </a>
                    </div>
                </div>

                {{-- Session Message Alert --}}
                @if(session('message'))
                    <div class="m-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded relative" role="alert">
                        <span class="block sm:inline">{{ session('message') }}</span>
                        <button type="button" onclick="this.parentElement.style.display='none'" class="absolute top-0 bottom-0 right-0 px-4 py-3 text-green-700 hover:text-green-900 font-bold text-xl" aria-label="Close">
                           &times;
                        </button>
                    </div>
                @endif

                {{-- Table Wrapper --}}
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse min-w-full">
                            <thead class="border-b border-gray-200 bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-left">Team</th>
                                    <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-left">Projects</th>
                                    <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-left">Tasks</th>
                                    <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-left"></th> {{-- Empty header for ellipsis --}}
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse($teams as $team)
                                <tr class="hover:bg-gray-50">
                                    {{-- Team Name + Avatar --}}
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            @php
                                                $colors = ['#2ECC71', '#BDC3C7', '#F39C12', '#E74C3C', '#9B59B6', '#2E86C1','#641E16','#2C3E50','#F4D03F','#F4D03F'];
                                                $bgColor = $colors[$loop->index % count($colors)];
                                            @endphp
                                            <div class="h-10 w-10 rounded-full flex items-center justify-center text-white font-medium flex-shrink-0" style="background-color: {{ $bgColor }};">
                                                {{ strtoupper(substr($team->name, 0, 1)) }}
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">{{$team->name}}</div>
                                            </div>
                                        </div>
                                    </td>
                                    {{-- Projects List --}}
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <div class="flex flex-wrap gap-1">
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
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                        <svg class="-ml-0.5 mr-1.5 h-2 w-2 text-blue-400" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="3" /></svg>
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
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <div class="flex flex-wrap gap-1">
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
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                     <svg class="-ml-0.5 mr-1.5 h-2 w-2 text-green-400" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="3" /></svg>
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
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button class="text-gray-400 hover:text-gray-600">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                              <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                     <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">
                                         No teams found.
                                     </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Team Cards Grid --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                 @foreach($teams as $team)
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 flex flex-col"> {{-- Use flex-col for structure --}}
                        {{-- Card Header --}}
                        <div class="p-4 border-b border-gray-200 flex justify-between items-start">
                             <div>
                                 <h5 class="text-base font-semibold text-gray-900 capitalize">{{$team->name}}</h5>
                                 <p class="text-sm text-gray-500 mt-1">Team Members:</p>
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
                                <form action="{{ $deleteRoute }}" method="post" onsubmit="return confirm('Are you sure?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700 p-1 -mt-1 -mr-1"> {{-- Adjust positioning --}}
                                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                                    </button>
                                </form>
                             @endcan
                        </div>

                        {{-- Card Body (Members List) --}}
                        <div class="p-4 flex-grow"> {{-- flex-grow makes body take available space --}}
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
                                            <div>
                                                <h6 class="text-sm font-medium text-gray-800 mb-0">
                                                    @if($canViewMember)
                                                        <a href="{{ $memberShowRoute }}" class="hover:underline">{{$employee->name}}</a>
                                                    @else
                                                        {{$employee->name}}
                                                    @endif
                                                </h6>
                                                @if($employee_user)
                                                    @if($employee_user->is_loggedIn == 0)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">Off Work</span>
                                                    @elseif($employee_user->is_loggedIn == 1)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">On Work</span>
                                                    @else
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">On Pause</span>
                                                    @endif
                                                @endif
                                            </div>
                                        </div>
                                        {{-- View Button --}}
                                        @if($canViewMember)
                                           <a href="{{ $memberShowRoute }}" class="text-xs text-blue-600 border border-blue-600 rounded px-2 py-0.5 hover:bg-blue-50">
                                               View
                                           </a>
                                        @endif
                                    </li>
                                    @endif
                                @empty
                                    <li class="text-sm text-gray-500">No members in this team.</li>
                                @endforelse
                            </ul>
                        </div>

                        {{-- Card Footer (Edit Button) --}}
                        <div class="p-4 border-t border-gray-200 text-right mt-auto"> {{-- mt-auto pushes footer down --}}
                             @can('Update team')
                                @php
                                    $editRoute = '#';
                                    if (Auth::user()->hasRole('Admin')) $editRoute = route('admin.teams.edit',$team->id);
                                    elseif (Auth::user()->hasRole('Responsable de projet')) $editRoute = route('Responsable_de_projet.teams.edit',$team->id);
                                    elseif (Auth::user()->hasRole('Employee')) $editRoute = route('employee.teams.edit',$team->id);
                                    elseif (Auth::user()->hasRole('Responsable de projet et gestionnaire')) $editRoute = route('Responsable_gestionnaire.teams.edit',$team->id);
                                @endphp
                                <a href="{{ $editRoute }}" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    Edit Team
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
