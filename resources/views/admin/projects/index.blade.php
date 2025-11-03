<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projects - BizManager</title> {{-- Updated title --}}
    @vite('resources/css/app.css')
    @livewireStyles
    {{-- Include dashboard_layouts.head if needed --}}
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
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-8">

            {{-- Container for the table --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">

                {{-- Header Section --}}
                <div class="p-6 border-b border-gray-200">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-4">
                        <h6 class="text-lg font-semibold text-gray-900">Projects Table</h6>

                        {{-- Buttons --}}
                        <div class="flex flex-wrap gap-2">
                            @php 
                                $reportRoute = '#';
                                $archiveRoute = '#';
                                if (Auth::user()->hasRole('Admin')) {
                                    $reportRoute = route('admin.Projectsrepports.downloadRepports');
                                    $archiveRoute = route('admin.projectsArchived.archiveIndex');
                                } elseif (Auth::user()->hasRole('Responsable de projet')) {
                                    $reportRoute = route('Responsable_de_projet.Projectsrepports.downloadRepports');
                                    $archiveRoute = route('Responsable_de_projet.projectsArchived.archiveIndex');
                                } elseif (Auth::user()->hasRole('Employee')) {
                                    $reportRoute = route('employee.Projectsrepports.downloadRepports');
                                    $archiveRoute = route('employee.projectsArchived.archiveIndex');
                                } elseif (Auth::user()->hasRole('Responsable de projet et gestionnaire')) {
                                    $reportRoute = route('Responsable_gestionnaire.Projectsrepports.downloadRepports');
                                    $archiveRoute = route('Responsable_gestionnaire.projectsArchived.archiveIndex');
                                }
                            @endphp
                            <a href="{{ $reportRoute }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 border border-gray-300 text-sm font-medium px-4 py-2 rounded-lg flex items-center gap-1.5">
                               <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                                Projects Reports PDF
                            </a>
                             <a href="{{ $archiveRoute }}" class="bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium px-4 py-2 rounded-lg flex items-center gap-1.5">
                                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" /></svg>
                                View Archive
                            </a>
                        </div>
                    </div>

                     {{-- Filters --}}
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                        <div class="w-full sm:w-auto">
                            <label for="projectFilter" class="block text-sm font-medium text-gray-700 mb-1">Filter by Project</label>
                            <select id="projectFilter" class="mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                                <option value="">All Projects</option>
                                {{-- Use distinct project names for filter options --}}
                                @foreach($projects->unique('name') as $project)
                                    <option value="{{ $project->name }}">{{ $project->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="w-full sm:w-auto">
                            <label for="nameFilter" class="block text-sm font-medium text-gray-700 mb-1">Filter by Name</label>
                            <input type="text" id="nameFilter" class="block w-full sm:w-64 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" placeholder="Filter by Name">
                        </div>
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
                <div class="p-0"> {{-- Removed padding for edge-to-edge table --}}
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse min-w-full" id="projectsTable">
                            <thead class="border-b border-gray-200 bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-left">Project</th>
                                    @if(Auth::user()->hasRole('Admin'))
                                        <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-left">Budget</th>
                                        <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-left">CPH</th>
                                        <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-left">Duration</th>
                                        <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-left">Est. Hours</th>
                                        <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-left">Difference</th>
                                        <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-left">Progress</th>
                                    @endif
                                    <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-left">VAT</th>
                                    <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-left">Teams</th>
                                    @can('Delete project')
                                    <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-left">Actions</th>
                                    @endcan
                                </tr>
                            </thead>
                            {{-- Body grouped by client --}}
                            <tbody class="divide-y divide-gray-200">
                                @php $groupedProjects = $projects->groupBy('client_id'); @endphp
                                @forelse($groupedProjects as $clientId => $clientProjects)
                                    @php $client = App\Models\Client::find($clientId); @endphp
                                    {{-- Client Header Row --}}
                                    <tr class="bg-gray-100 client-header"> {{-- Add class for filtering --}}
                                        <td colspan="{{ Auth::user()->hasRole('Admin') ? 10 : 3 }}" class="px-6 py-3 font-semibold text-gray-800 text-sm"> {{-- Adjust colspan --}}
                                            @php
                                                $clientShowRoute = '#';
                                                $canViewClient = Auth::user()->can('View client');
                                                if ($canViewClient && $client) {
                                                    if (Auth::user()->hasRole('Admin')) $clientShowRoute = route('admin.clients.show', $clientId);
                                                    elseif (Auth::user()->hasRole('Responsable de projet')) $clientShowRoute = route('Responsable_de_projet.clients.show', $clientId);
                                                    elseif (Auth::user()->hasRole('Employee')) $clientShowRoute = route('employee.clients.show', $clientId);
                                                    elseif (Auth::user()->hasRole('Responsable de projet et gestionnaire')) $clientShowRoute = route('Responsable_gestionnaire.clients.show', $clientId);
                                                }
                                            @endphp
                                            @if($client)
                                                @if($canViewClient)
                                                    <a href="{{ $clientShowRoute }}" class="text-blue-600 hover:underline">
                                                        {{$client->name}}
                                                    </a>
                                                @else
                                                    {{$client->name}}
                                                @endif
                                            @else
                                                Unknown Client
                                            @endif
                                        </td>
                                    </tr>
                                    {{-- Projects for this client --}}
                                    @foreach($clientProjects as $project)
                                        <tr class="hover:bg-gray-50 project-row"> {{-- Add class for filtering --}}
                                            {{-- Project Name --}}
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 editable project-name" data-id="{{$project->id}}" data-column="name"> {{-- Add class for filtering --}}
                                                @php
                                                    $projectShowRoute = '#';
                                                    $canViewProject = Auth::user()->can('View project');
                                                    if ($canViewProject) {
                                                        if (Auth::user()->hasRole('Responsable de projet')) $projectShowRoute = route('Responsable_de_projet.projects.show', $project->id);
                                                        elseif (Auth::user()->hasRole('Admin')) $projectShowRoute = route('admin.projects.show', $project->id);
                                                        elseif (Auth::user()->hasRole('Employee')) $projectShowRoute = route('employee.projects.show', $project->id);
                                                        elseif (Auth::user()->hasRole('Responsable de projet et gestionnaire')) $projectShowRoute = route('Responsable_gestionnaire.projects.show', $project->id);
                                                    }
                                                @endphp
                                                @if($canViewProject)
                                                    <a href="{{ $projectShowRoute }}" class="text-blue-600 hover:underline">
                                                        {{ strtoupper($project->name) }}
                                                    </a>
                                                @else
                                                    {{ strtoupper($project->name) }}
                                                @endif
                                            </td>
                                            {{-- Admin Only Columns --}}
                                            @if(Auth::user()->hasRole('Admin'))
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600 editable" data-id="{{$project->id}}" data-column="budget">€{{ number_format($project->budget, 2, ',', ' ') }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600 editable" data-id="{{$project->id}}" data-column="cost_per_hour">€{{ number_format($project->cost_per_hour, 2, ',', ' ') }}</td>
                                                @php
                                                    // Consider optimizing these calculations if slow
                                                    $projecttasksId = App\Models\Tasks::where('project_id', $project->id)->pluck('id');
                                                    $totalduration = App\Models\tasks_timer::whereIn('task_id', $projecttasksId)->sum('duration'); // Duration in minutes
                                                    $hours = floor(abs($totalduration) / 60);
                                                    $minutes = abs($totalduration) % 60;
                                                    
                                                    $estimatedMinutes = ($project->estimated_hours ?? 0) * 60;
                                                    $difference = $estimatedMinutes - $totalduration; 
                                                    $diffhours = floor(abs($difference) / 60);
                                                    $diffminutes = abs($difference) % 60;
                                                    
                                                    $progressPercentage = $estimatedMinutes > 0 ? max(0, min(100, ($totalduration / $estimatedMinutes) * 100)) : 0; // Clamp between 0-100
                                                    $formattedProgressPercentage = number_format($progressPercentage, 2);
                                                    
                                                    $progressBarColor = 'bg-green-500'; // Default green
                                                    if ($progressPercentage >= 75) $progressBarColor = 'bg-red-500';
                                                    elseif ($progressPercentage >= 50) $progressBarColor = 'bg-yellow-500';
                                                @endphp
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ sprintf('%02d', $hours) }}h {{ sprintf('%02d', $minutes) }}m</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 editable" data-id="{{$project->id}}" data-column="estimated_hours">{{ $project->estimated_hours ?? 0 }}h</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                    @if($project->estimated_hours !== null)
                                                        @if($difference > 0)
                                                            <span class="text-green-600">+{{ sprintf('%02d', $diffhours) }}h {{ sprintf('%02d', $diffminutes) }}m</span>
                                                        @elseif($difference < 0)
                                                            <span class="text-red-600">-{{ sprintf('%02d', $diffhours) }}h {{ sprintf('%02d', $diffminutes) }}m</span>
                                                        @else
                                                            <span class="text-blue-600">{{ sprintf('%02d', $diffhours) }}h {{ sprintf('%02d', $diffminutes) }}m</span>
                                                        @endif
                                                    @else
                                                         <span class="text-gray-400">-</span>
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                                    <div class="flex items-center gap-2">
                                                        <div class="w-20 bg-gray-200 rounded-full h-1.5">
                                                            <div class="{{ $progressBarColor }} h-1.5 rounded-full" style="width: {{ $formattedProgressPercentage }}%"></div>
                                                        </div>
                                                        <span class="text-xs font-medium">{{ $formattedProgressPercentage }}%</span>
                                                    </div>
                                                </td>
                                            @endif
                                            {{-- VAT --}}
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 editable" data-id="{{$project->id}}" data-column="tva">{{$project->tva ?? 'N/A'}}%</td>
                                            {{-- Teams --}}
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                 <div class="flex flex-wrap gap-1">
                                                    @php
                                                        $teamIndexRoute = '#';
                                                        $canViewTeam = Auth::user()->can('View team');
                                                        if($canViewTeam) {
                                                            if (Auth::user()->hasRole('Admin')) $teamIndexRoute = route('admin.teams.index');
                                                            elseif (Auth::user()->hasRole('Responsable de projet')) $teamIndexRoute = route('Responsable_de_projet.teams.index');
                                                            elseif (Auth::user()->hasRole('Employee')) $teamIndexRoute = route('employee.teams.index');
                                                            elseif (Auth::user()->hasRole('Responsable de projet et gestionnaire')) $teamIndexRoute = route('Responsable_gestionnaire.teams.index');
                                                        }
                                                        // Consider eager loading teams for performance
                                                        $project_teams_list = App\Models\project_teams::where('project_id', $project->id)->get();
                                                    @endphp
                                                    @forelse($project_teams_list as $project_team)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                                            @if($canViewTeam)
                                                                <a href="{{ $teamIndexRoute }}" class="hover:underline">{{$project_team->team_name}}</a>
                                                            @else
                                                                {{$project_team->team_name}}
                                                            @endif
                                                        </span>
                                                    @empty
                                                        <span class="text-xs text-gray-400">No teams</span>
                                                    @endforelse
                                                 </div>
                                            </td>
                                            {{-- Actions --}}
                                            @can('Delete project')
                                            <td class="px-6 py-4 whitespace-nowrap text-sm space-x-1">
                                                @php
                                                    $deleteRoute = '#'; $editRoute = '#'; $archiveRoute = '#';
                                                    if (Auth::user()->hasRole('Admin')) {
                                                        $deleteRoute = route('admin.projects.destroy', $project->id);
                                                        $editRoute = route('admin.projects.edit', $project->id);
                                                        $archiveRoute = route('admin.projects.archive', $project->id);
                                                    } elseif (Auth::user()->hasRole('Responsable de projet')) {
                                                        $deleteRoute = route('Responsable_de_projet.projects.destroy', $project->id);
                                                        $editRoute = route('Responsable_de_projet.projects.edit', $project->id);
                                                        $archiveRoute = route('Responsable_de_projet.projects.archive', $project->id);
                                                    } elseif (Auth::user()->hasRole('Employee')) {
                                                        $deleteRoute = route('employee.projects.destroy', $project->id);
                                                        $editRoute = route('employee.projects.edit', $project->id);
                                                        $archiveRoute = route('employee.projects.archive', $project->id);
                                                    } elseif (Auth::user()->hasRole('Responsable de projet et gestionnaire')) {
                                                        $deleteRoute = route('Responsable_gestionnaire.projects.destroy', $project->id);
                                                        $editRoute = route('Responsable_gestionnaire.projects.edit', $project->id);
                                                        $archiveRoute = route('Responsable_gestionnaire.projects.archive', $project->id);
                                                    }
                                                @endphp
                                                <a href="{{ $editRoute }}" class="inline-flex items-center px-2.5 py-1 border border-transparent text-xs font-medium rounded shadow-sm text-white bg-sky-600 hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500">
                                                    Edit
                                                </a>
                                                <form action="{{ $archiveRoute }}" method="post" onsubmit="return confirm('Archive this project?');" class="inline">
                                                    @csrf
                                                    <button type="submit" class="inline-flex items-center px-2.5 py-1 border border-gray-300 text-xs font-medium rounded shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                        Archive
                                                    </button>
                                                </form>
                                                <form action="{{ $deleteRoute }}" method="post" onsubmit="return confirm('Are you sure you want to delete this project?');" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="inline-flex items-center px-2.5 py-1 border border-transparent text-xs font-medium rounded shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                                        Delete
                                                    </button>
                                                </form>
                                            </td>
                                            @endcan
                                        </tr>
                                    @endforeach
                                @empty
                                    <tr>
                                         <td colspan="{{ Auth::user()->hasRole('Admin') ? 10 : 3 }}" class="px-6 py-4 text-center text-sm text-gray-500">
                                             No projects found.
                                         </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </main>
    </div>

    @livewireScripts
    {{-- @include('dashboard_layouts.scripts') --}}

    {{-- Keep jQuery and potentially filter scripts --}}
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    
    {{-- Inline Editing Script (Commented out as in original) --}}
    {{-- 
    @can('Update project')
    <script>
    $(document).ready(function(){
        // Use event delegation for dblclick
        $('body').on('dblclick', '.editable', function(){
             if ($(this).find('input').length > 0) return; // Prevent re-editing

            var originalContent = $(this).text().trim();
            var cellWidth = $(this).outerWidth();
            
            // Add Tailwind classes
            $(this).html("<input type='text' value='" + originalContent + "' class='w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-1' style='min-width: " + cellWidth + "px;' />");
            
            var input = $(this).children('input').first();
            input.focus();
            input[0].select(); 

            input.blur(function(){
                var newContent = $(this).val();
                var $parentCell = $(this).parent();
                var projectId = $parentCell.data('id'); // Changed from clientId
                var columnName = $parentCell.data('column');
                
                $parentCell.text(originalContent); // Revert temporarily

                $.ajax({
                    url: '/admin/projects/updateProject', // Example route - ADJUST THIS
                    type: 'POST', // Use POST
                    data: {
                        content: newContent,
                        id: projectId, // Use projectId
                        column: columnName,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        $parentCell.text(newContent); 
                        // Optional success message
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        $parentCell.text(originalContent); // Revert on error
                        alert('Error updating project: ' + errorThrown);
                        console.error("AJAX Error:", textStatus, errorThrown, jqXHR.responseText);
                    }
                });
            });

            input.keypress(function(e){
                if(e.which == 13){ // Enter key
                    $(this).blur(); 
                    return false; 
                }
            });
        });
    });
    </script>
    @endcan
    --}}

    {{-- Filter Scripts --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const projectFilter = document.getElementById('projectFilter');
            const nameFilter = document.getElementById('nameFilter');
            const tableBody = document.querySelector('#projectsTable tbody');
            const allRows = Array.from(tableBody.querySelectorAll('tr')); // All rows including headers

            function applyFilters() {
                const projectValue = projectFilter.value.toLowerCase();
                const nameValue = nameFilter.value.toLowerCase();

                let visibleClientHeaders = new Set();

                // Filter project rows first
                allRows.forEach(row => {
                    if (row.classList.contains('project-row')) {
                        const projectNameElement = row.querySelector('.project-name');
                        const projectName = projectNameElement ? projectNameElement.textContent.toLowerCase() : '';
                        
                        const matchesProject = projectValue === "" || projectName.includes(projectValue);
                        const matchesName = nameValue === "" || projectName.includes(nameValue);

                        if (matchesProject && matchesName) {
                            row.style.display = "";
                            // Find the preceding client header and mark it for visibility
                            let header = row.previousElementSibling;
                            while(header && !header.classList.contains('client-header')) {
                                header = header.previousElementSibling;
                            }
                            if(header) {
                                visibleClientHeaders.add(header);
                            }
                        } else {
                            row.style.display = "none";
                        }
                    }
                });

                // Show/hide client headers based on visible projects
                allRows.forEach(row => {
                     if (row.classList.contains('client-header')) {
                         if(visibleClientHeaders.has(row)) {
                             row.style.display = "";
                         } else {
                             row.style.display = "none";
                         }
                     }
                });
            }

            projectFilter.addEventListener('change', applyFilters);
            nameFilter.addEventListener('input', applyFilters);
        });
    </script>

</body>
</html>