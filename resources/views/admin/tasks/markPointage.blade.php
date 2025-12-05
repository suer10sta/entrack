<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Tasks - DailyTrac</title>
    @vite('resources/css/app.css')
    @livewireStyles
    <meta name="csrf-token" content="{{ csrf_token() }}">
<style>
        select.form-select-custom {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 0.5rem center;
            background-repeat: no-repeat;
            background-size: 1.5em 1.5em;
            padding-right: 2.5rem;
        }
        select:invalid {
            color: #9ca3af;
        }
        option {
            color: #111827;
        }
        .project-link {
             color: #2563eb; /* blue-600 */
             font-weight: 500; /* medium */
        }
         .project-link:hover {
             color: #1d4ed8; /* blue-800 */
             text-decoration: underline;
         }
        .client-header-row {
            background-color: #f3f4f6; /* bg-gray-100 */
            border-top: 1px solid #e5e7eb; /* border-gray-200 */
            border-bottom: 1px solid #e5e7eb; /* border-gray-200 */
        }
        .client-header-row td {
            font-weight: 600; /* font-semibold */
            color: #1f2937; /* text-gray-800 */
        }
</style>
</head>
<body class="bg-gray-100 flex h-screen overflow-hidden font-sans">
    <x-dashboard.sidebar />
    
    <div class="flex flex-col flex-1 overflow-y-auto">
        <x-dashboard.navbar />
        
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-8 space-y-8">

            {{-- 
              MODIFICATION: 
              - Header is now a flex container to hold the title and the small duration card.
              - `items-start` is used to align the top of the title with the top of the card.
            --}}
            <div class="flex flex-wrap justify-between items-start gap-4">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">Employee Tasks</h1>
                </div>
                
                {{-- 
                  MODIFICATION: 
                  - "Todays Duration" is now a small card placed on the right.
                  - Padding reduced to `p-4` (from p-5).
                  - Icon size reduced to `h-5 w-5` (from h-6 w-6).
                  - Text size for value reduced to `text-lg` (from text-xl).
                --}}
                <div class="bg-white rounded-lg shadow border border-gray-200 p-4 flex items-center space-x-3">
                    <div class="flex-shrink-0 bg-blue-100 rounded-full p-2"> {{-- Reduced padding --}}
                        {{-- Clock Icon --}}
                        <svg class="h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"> {{-- Reduced size --}}
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase font-medium">Todays Duration</p>
                        @php
                            $today = \Carbon\Carbon::today()->toDateString();
                            $totalDurationAllTasks = App\Models\tasks_timer::where('employee_id', $employee_id)
                                                                ->whereDate('start_date', '=', $today)
                                                                ->whereNotNull('duration')
                                                                ->sum('duration');
                            $totalHours = intdiv($totalDurationAllTasks, 60);
                            $totalMinutes = $totalDurationAllTasks % 60;
                        @endphp
                        <p class="text-lg font-semibold text-gray-900"> {{-- Reduced text size --}}
                            {{ (isset($totalHours) && $totalHours > 0) || (isset($totalMinutes) && $totalMinutes > 0) ? $totalHours . 'h:' . sprintf('%02d', $totalMinutes) . 'm' : '00h:00m' }}
                        </p> 
                    </div>
                </div>
            </div>

            {{-- Session Alerts (Placed after the header section) --}}
            @if(session('alertmessage'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative flex justify-between items-center" role="alert">
                <span class="block sm:inline">{{ session('alertmessage') }}</span>
                <button type="button" onclick="this.parentElement.style.display='none';" class="p-1 -mr-1" aria-label="Close">
                    <svg class="w-5 h-5 text-red-700" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            @elseif(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative flex justify-between items-center" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
                <button type="button" onclick="this.parentElement.style.display='none';" class="p-1 -mr-1" aria-label="Close">
                    <svg class="w-5 h-5 text-green-700" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            @endif

            {{-- 2. Filters Section Card --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h6 class="text-base font-semibold text-gray-800 mb-4">Filters</h6>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="clientFilter" class="block text-sm font-medium text-gray-700 mb-1">Select Client:</label>
                        <select id="clientFilter" class="form-select-custom block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3">
                            <option value="">All Clients</option>
                            @php
                                $sortedClients = $clients->sortBy('name');
                            @endphp
                            @foreach($sortedClients as $client)
                            <option value="{{ strtoupper($client->name) }}">{{strtoupper($client->name)}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="taskSearchInput" class="block text-sm font-medium text-gray-700 mb-1">Search for projects:</label>
                        <input type="text" id="taskSearchInput" placeholder="Search for projects..." class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3">
                    </div>
                </div>
            </div>

            {{-- 3. Table Section Card --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table id="pointageTable" class="w-full min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50"> 
                            <tr class="text-left">
                                <th scope="col" class="px-4 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider">Client Name</th>
                                <th scope="col" class="px-4 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider">Project Name</th>
                                <th scope="col" class="px-4 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider">Total Duration</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($clients as $client)
                                {{-- Ligne de l'en-tête du client --}}
                                <tr class="client-header-row text-left" data-client-name="{{ strtoupper($client->name) }}"> 
                                    <td colspan="3" class="px-4 py-3"> 
                                        {{strtoupper($client->name)}}
                                    </td>
                                </tr>
                                
                                @php
                                    $clients_projects = App\Models\Project::where('entreprise_id', Auth::user()->entreprise_id)
                                        ->where(function($query) {
                                            $query->whereNull('AcceptedDevisProject')
                                                  ->orWhere('AcceptedDevisProject', '=', 1);
                                        })
                                        ->where('client_id', $client->id)
                                        ->where('archived', 0)
                                        ->get();
                                @endphp

                                @foreach($clients_projects as $project)
                                    @php
                                        $today = \Carbon\Carbon::today()->toDateString();
                                        $projectTasks = App\Models\Tasks::where('project_id', $project->id)->pluck('id');
                                        $totalProjectDuration = App\Models\tasks_timer::whereIn('task_id', $projectTasks)
                                            ->where('employee_id', $employee_id)
                                            ->whereDate('start_date', '=', $today)
                                            ->whereNotNull('duration')
                                            ->sum('duration');
                                        
                                        $hours = intdiv($totalProjectDuration, 60);
                                        $minutes = $totalProjectDuration % 60;
                                    @endphp
                                    
                                    {{-- Ligne du projet --}}
                                    <tr class="project-row text-left hover:bg-gray-50" 
                                        data-client-name="{{ strtoupper($client->name) }}" 
                                        data-project-name="{{ strtoupper($project->name) }}">
                                        
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500"></td> 
                                        
                                        <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-800">
                                            @if(Auth::user()->hasRole('Admin') && Auth::user()->can('View project'))
                                                <a href="{{route('admin.projects.show', $project->id)}}" class="project-link">
                                                    {{strtoupper($project->name)}}
                                                </a>
                                            @elseif(Auth::user()->hasRole('Responsable de projet') && Auth::user()->can('View project'))
                                                <a href="{{route('Responsable_de_projet.projects.show', $project->id)}}" class="project-link">
                                                    {{strtoupper($project->name)}}
                                                </a>
                                            @elseif(Auth::user()->hasRole('Employee') && Auth::user()->can('View project'))
                                                <a href="{{route('employee.projects.show', $project->id)}}" class="project-link">
                                                    {{strtoupper($project->name)}}
                                                </a>
                                            @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire') && Auth::user()->can('View project'))
                                                <a href="{{route('Responsable_gestionnaire.projects.show', $project->id)}}" class="project-link">
                                                    {{strtoupper($project->name)}}
                                                </a>
                                            @else
                                                {{strtoupper($project->name)}}
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600 text-left">
                                           {{ $hours }}h:{{ sprintf('%02d', $minutes) }}m
                                        </td>
                                    </tr>
                                @endforeach
                            @empty
                                {{-- Empty state row (Inspired by Devis History) --}}
                                <tr>
                                    <td colspan="3" class="text-center px-4 py-6 text-gray-500">
                                        No clients or projects found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            
        </main>
        
    </div>

    @livewireScripts

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const clientFilter = document.getElementById("clientFilter");
    const searchInput = document.getElementById("taskSearchInput");
    const tableBody = document.getElementById("pointageTable").getElementsByTagName("tbody")[0];
    const projectRows = tableBody.querySelectorAll(".project-row");
    const clientHeaderRows = tableBody.querySelectorAll(".client-header-row");

    function applyFilters() {
        const clientQuery = clientFilter.value.toLowerCase();
        const searchQuery = searchInput.value.toLowerCase();

        const visibleClients = new Set();

        // 1. Filter project rows
        projectRows.forEach(row => {
            const clientName = row.dataset.clientName.toLowerCase();
            const projectName = row.dataset.projectName.toLowerCase(); 

            const clientMatch = clientQuery === "" || clientName.includes(clientQuery);
            const searchMatch = searchQuery === "" || projectName.includes(searchQuery);

            if (clientMatch && searchMatch) {
                row.style.display = "";
                visibleClients.add(row.dataset.clientName); 
            } else {
                row.style.display = "none";
            }
        });

        // 2. Filter client header rows
        clientHeaderRows.forEach(row => {
            const clientName = row.dataset.clientName;
            if (visibleClients.has(clientName)) {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }
        });
    }

    // Add event listeners to both filters
    clientFilter.addEventListener("change", applyFilters);
    searchInput.addEventListener("keyup", applyFilters);
});
</script>

</body> 
</html>
