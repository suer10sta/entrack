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

            </div>

            <div class="container-fluid py-4">

                <div class="row mt-3">
                    <div class="col-12">

                        <div class="card">
                            <div class="card-header p-3 pb-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6>Employee Tasks Table</h6>
                                    <div>
                                        @php
                                            $today = \Carbon\Carbon::today()->toDateString();
                                            $totalDurationAllTasks = App\Models\tasks_timer::where('employee_id', $employee_id)
                                                                                            ->whereDate('start_date', '=', $today)
                                                                                            ->whereNotNull('duration')
                                                                                            ->sum('duration');
                                            $totalHours = intdiv($totalDurationAllTasks, 60);
                                            $totalMinutes = $totalDurationAllTasks % 60;
                                        @endphp
                                        Todays Duration : <strong>{{ (isset($totalHours) && $totalHours > 0) || (isset($totalMinutes) && $totalMinutes > 0) ? $totalHours . 'h:' . sprintf('%02d', $totalMinutes) . 'm' : '00h:00m' }}</strong>
                                    </div>
                                </div>

                                @if(session('alertmessage'))
                                <div class="alert alert-danger alert-dismissible fade show" style="color:white;" role="alert">
                                    {{ session('alertmessage') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">X</button>
                                </div>
                                @elseif(session('success'))
                                <div class="alert alert-success alert-dismissible fade show" style="color:white;" role="alert">
                                    {{ session('success') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">X</button>
                                </div>
                                @endif
                            </div>
                            <div class="p-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <label for="clientFilter">Select Client:</label>
                                        <select id="clientFilter" class="form-control">
                                            <option value="">All Clients</option>
                                            @php
                                                $sortedClients = $clients->sortBy('name');
                                            @endphp
                                            @foreach($sortedClients as $client)
                                            <option value="{{ strtoupper($client->name) }}">{{strtoupper($client->name)}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="table-search">
                                        <label for="taskSearch">Search for tasks:</label>
                                        <input type="text" id="taskSearchInput" placeholder="Search for tasks..." class="form-control mb-3">
                                    </div>
                                </div>
                            </div>
                            <div class="table-responsive p-4 pt-1">
                                <table id="pointageTable" class="table table-flush" style="width:100%">
                                    <thead class="thead-light">   
                                        <tr class="text-center">
                                            <th class="text-center text-uppercase text-secondary text-x font-weight-bolder opacity-10">Client Name</th>
                                            <th class="text-center text-uppercase text-secondary text-x font-weight-bolder opacity-10">Project Name</th>
                                            <th class="text-center text-uppercase text-secondary text-x font-weight-bolder opacity-10">Total Duration</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($clients as $client)
                                            <tr class="table-secondary">
                                                <td colspan="3" class="font-weight-bold">
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
                                                    
                                                    // Get all tasks for this project
                                                    $projectTasks = App\Models\Tasks::where('project_id', $project->id)->pluck('id');
                                                    
                                                    // Calculate total duration for project today
                                                    $totalProjectDuration = App\Models\tasks_timer::whereIn('task_id', $projectTasks)
                                                        ->where('employee_id', $employee_id)
                                                        ->whereDate('start_date', '=', $today)
                                                        ->whereNotNull('duration')
                                                        ->sum('duration');
                                                    
                                                    $hours = intdiv($totalProjectDuration, 60);
                                                    $minutes = $totalProjectDuration % 60;
                                                @endphp
                                                
                                                <tr class="text-center">
                                                    <td></td>
                                                    <td>
                                                        @if(Auth::user()->hasRole('Admin') && Auth::user()->can('View project'))
                                                            <a href="{{route('admin.projects.show', $project->id)}}">
                                                                {{strtoupper($project->name)}}
                                                            </a>
                                                        @elseif(Auth::user()->hasRole('Responsable de projet') && Auth::user()->can('View project'))
                                                            <a href="{{route('Responsable_de_projet.projects.show', $project->id)}}">
                                                                {{strtoupper($project->name)}}
                                                            </a>
                                                        @elseif(Auth::user()->hasRole('Employee') && Auth::user()->can('View project'))
                                                            <a href="{{route('employee.projects.show', $project->id)}}">
                                                                {{strtoupper($project->name)}}
                                                            </a>
                                                        @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire') && Auth::user()->can('View project'))
                                                            <a href="{{route('Responsable_gestionnaire.projects.show', $project->id)}}">
                                                                {{strtoupper($project->name)}}
                                                            </a>
                                                        @else
                                                            {{strtoupper($project->name)}}
                                                        @endif
                                                    </td>
                                                    <td >
                                                       <p class="text-sm">{{ $hours }}h:{{ sprintf('%02d', $minutes) }}m</p>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <br>


                    </div>
                </div>


    </main>



    @include('dashboard_layouts.scripts')

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        // Get the input field and the table
        var input = document.getElementById("taskSearchInput");
        var table = document.getElementById("pointageTable");
        var tbody = table.getElementsByTagName("tbody")[0];
        var rows = tbody.getElementsByTagName("tr");

        // Function to filter rows based on the input
        function filterTasks() {
            var searchQuery = input.value.toLowerCase();

            for (var i = 0; i < rows.length; i++) {
                var row = rows[i];
                var clientCell = row.cells[0];
                var projectCell = row.cells[1];

                // For client header rows (with colspan)
                if (row.querySelector('td[colspan]')) {
                    var clientName = row.querySelector('td[colspan]').textContent.toLowerCase();
                    if (clientName.includes(searchQuery)) {
                        row.style.display = "";
                        // Show all project rows under this client
                        var nextRow = row.nextElementSibling;
                        while (nextRow && !nextRow.querySelector('td[colspan]')) {
                            nextRow.style.display = "";
                            nextRow = nextRow.nextElementSibling;
                        }
                    } else {
                        row.style.display = "none";
                        // Hide all project rows under this client
                        var nextRow = row.nextElementSibling;
                        while (nextRow && !nextRow.querySelector('td[colspan]')) {
                            nextRow.style.display = "none";
                            nextRow = nextRow.nextElementSibling;
                        }
                    }
                } else if (clientCell && projectCell) {
                    // For project rows
                    var clientName = clientCell.textContent.toLowerCase();
                    var projectName = projectCell.textContent.toLowerCase();
                    if (clientName.includes(searchQuery) || projectName.includes(searchQuery)) {
                        row.style.display = "";
                    } else {
                        row.style.display = "none";
                    }
                }
            }
        }

        // Listen for keyup events on the input field
        input.addEventListener("keyup", filterTasks);
    });
    
    </script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const clientFilter = document.getElementById('clientFilter');
        clientFilter.addEventListener('change', function() {
            const selectedClient = this.value.toLowerCase();
            const tableRows = document.querySelectorAll('#pointageTable tbody tr');

            tableRows.forEach(row => {
                // For client header rows (they have colspan)
                if (row.querySelector('td[colspan]')) {
                    const clientName = row.querySelector('td[colspan]').textContent.toLowerCase();
                    if (selectedClient === "" || clientName.includes(selectedClient)) {
                        row.style.display = '';
                        // Show all project rows under this client
                        let nextRow = row.nextElementSibling;
                        while (nextRow && !nextRow.querySelector('td[colspan]')) {
                            nextRow.style.display = '';
                            nextRow = nextRow.nextElementSibling;
                        }
                    } else {
                        row.style.display = 'none';
                        // Hide all project rows under this client
                        let nextRow = row.nextElementSibling;
                        while (nextRow && !nextRow.querySelector('td[colspan]')) {
                            nextRow.style.display = 'none';
                            nextRow = nextRow.nextElementSibling;
                        }
                    }
                }
            });
        });
    });
    </script>

</body>

</html>