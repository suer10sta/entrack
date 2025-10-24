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
        </nav>
        <div class="container-fluid py-4">
            <div class="row mt-3">
                <div class="col-12">
                    <div class="card">
                    <div class="card-header p-3 pb-0">
                        <div class="d-flex justify-content-between align-items-start">
                            <h6>Expense Note History Table</h6>
                            <div class="d-flex align-items-start">
                                <div x-data="datePicker()" class="d-flex align-items-center">
                                    <div class="me-2">
                                        <label for="startDate" class="form-label">Start Date:</label>
                                        <input type="date" id="startDate" class="form-control" x-model="startDate">
                                    </div>
                                    <div class="me-2">
                                        <label for="endDate" class="form-label">End Date:</label>
                                        <input type="date" id="endDate" class="form-control" x-model="endDate">
                                    </div>
                                    <div>
                                        <label class="form-label d-block">&nbsp;</label> <!-- Adjusted for alignment -->
                                        <a href="#" @click.prevent="updateHref" class="btn btn-primary mt-2" id="downloadLink">Expense Note Reports PDF</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @php
                            $Total_budget = App\Models\note_de_frais::where('entreprise_id', Auth::user()->entreprise_id)->sum('budget');
                        @endphp
                        <div class="d-flex flex-column align-items-end ms-3"> <!-- Adjusted for alignment and spacing -->
                            <div class="d-flex align-items-center">
                                <div>
                                    <label for="totalPaid" class="form-label" style="font-size: 13px !important;">Total Accepted: €{{number_format($Total_budget, 2, ',', '')}} </label>
                                </div>
                                
                            </div>
                        </div>
                    </div>

                        
                        @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert" style="color: white; font-weight: bold;">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">X</button>
                </div>
                  @endif
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th
                                        class="text-center text-uppercase text-secondary text-x font-weight-bolder opacity-10">
                                        Note N
                                    </th>
                                    <th
                                        class="text-center text-uppercase text-secondary text-x font-weight-bolder opacity-10">
                                        Employee Name
                                    </th>
                                    
                                    <th
                                        class="text-center text-uppercase text-secondary text-x font-weight-bolder opacity-10 ps-2">
                                        Budget
                                    </th>
                                    <th
                                        class="text-center text-uppercase text-secondary text-x font-weight-bolder opacity-10">
                                        Created at
                                    </th>
                                    
                                    <th
                                        class="text-center text-uppercase text-secondary text-x font-weight-bolder opacity-10">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($notedefrais as $note)
                                <tr>
                                    <td>
                                        <p class="align-middle text-center text-sm font-weight-bold mb-0">
                                            @if(Auth::user()->hasRole('Responsable de projet'))
                                                <a href="{{ route('Responsable_de_projet.notedefrais.downloadNoteDeFrais', $note->note_number) }}">{{$entreprise->notedefraisPrefix}}-{{date('ym')}}{{$note->note_number}}
                                                </a>
                                            @endif
                                            @if(Auth::user()->hasRole('Admin'))
                                                <a href="{{ route('admin.notedefrais.downloadNoteDeFrais', $note->note_number) }}">{{$entreprise->notedefraisPrefix}}-{{date('ym')}}{{$note->note_number}}
                                                </a>
                                            @endif
                                            @if(Auth::user()->hasRole('Employee'))
                                                <a href="{{ route('employee.notedefrais.downloadNoteDeFrais', $note->note_number) }}">{{$entreprise->notedefraisPrefix}}-{{date('ym')}}{{$note->note_number}}
                                                </a>
                                            @endif
                                            @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                                <a href="{{ route('Responsable_gestionnaire.notedefrais.downloadNoteDeFrais', $note->note_number) }}">{{$entreprise->notedefraisPrefix}}-{{date('ym')}}{{$note->note_number}}
                                                </a>
                                            @endif
                                        </p>
                                    <td>
                                        <p class="align-middle text-center text-sm font-weight-bold mb-0">
                                            {{$note->employee_name}}
                                        </p>
                                    </td>

                                    
                                    <td>
                                        <p class="text-center text-sm font-weight-bold mb-0">{{$note->budget}}€</p>
                                    </td>
                                    <td>
                                        <p class="text-center text-sm font-weight-bold mb-0">{{$note->created_at}}
                                        </p>
                                    </td>
                                    <td>
                                        <div class="">
                                            @if(Auth::user()->hasRole('Admin'))
                                                <a class="btn btn-primary btn-xs p-2 mb-0" href="{{ route('admin.notedefrais.downloadNoteDeFrais', $note->note_number) }}">
                                                    download
                                                </a>
                                                @can('Delete expense note')
                                                <form action="{{route('admin.notedefrais.destroy', $note->id)}}" method="post" onsubmit="return confirm('Are you sure?');"
                                                    class="d-inline ">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-xs p-2 mb-0">Delete</button>
                                                </form>
                                                @endcan
                                            @endif
                                            @if(Auth::user()->hasRole('Responsable de projet'))
                                                <a class="btn btn-primary btn-xs p-2 mb-0" href="{{ route('Responsable_de_projet.notedefrais.downloadNoteDeFrais', $note->note_number) }}">
                                                    download
                                                </a>
                                                @can('Delete expense note')
                                                <form action="{{route('Responsable_de_projet.notedefrais.destroy', $note->id)}}" method="post" onsubmit="return confirm('Are you sure?');"
                                                    class="d-inline ">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-xs p-2 mb-0">Delete</button>
                                                </form>
                                                @endcan
                                            @endif
                                            @if(Auth::user()->hasRole('Employee'))
                                                <a class="btn btn-primary btn-xs p-2 mb-0" href="{{ route('employee.notedefrais.downloadNoteDeFrais', $note->note_number) }}">
                                                    download
                                                </a>
                                                @can('Delete expense note')
                                                <form action="{{route('employee.notedefrais.destroy', $note->id)}}" method="post" onsubmit="return confirm('Are you sure?');"
                                                    class="d-inline ">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-xs p-2 mb-0">Delete</button>
                                                </form>
                                                @endcan
                                            @endif
                                            @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                                <a class="btn btn-primary btn-xs p-2 mb-0" href="{{ route('Responsable_gestionnaire.notedefrais.downloadNoteDeFrais', $note->note_number) }}">
                                                    download
                                                </a>
                                                @can('Delete expense note')
                                                <form action="{{route('Responsable_gestionnaire.notedefrais.destroy', $note->id)}}" method="post" onsubmit="return confirm('Are you sure?');"
                                                    class="d-inline ">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-xs p-2 mb-0">Delete</button>
                                                </form>
                                                @endcan
                                            @endif
                                        </div>
                                    </td>
                                            
                                        </div>
                                    </td>
                                </tr>
                                @endforeach



                            </tbody>
                        </table>

                    </div>
                </div>
            </div>

        </div>
    </main>




    @include('dashboard_layouts.scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.4/xlsx.full.min.js"></script>

    <script>
    document.getElementById('exportBtn').addEventListener('click', function() {
        var table = document.getElementById(
            'taskTable'); // Replace 'yourTableId' with the actual ID of your table
        var wb = XLSX.utils.table_to_book(table, {
            sheet: 'Sheet1'
        });
        // Get the start date, end date, and task information
        var startDate = document.getElementById('startDateInput').value;
        var endDate = document.getElementById('endDateInput').value;
        var taskInfo = 'TasksInfo'; // Example task information

        // Format the file name
        var fileName = startDate + '__' + endDate + '_' + taskInfo + '.xlsx';

        // Trigger the file download with the custom file name
        XLSX.writeFile(wb, fileName);
    });
    </script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
    // JavaScript for filtering the table
    document.addEventListener("DOMContentLoaded", function() {
        // Get references to the filter dropdowns
        const clientFilter = document.getElementById('clientFilter');
        const acceptanceFilter = document.getElementById('acceptanceFilter');

        // Add event listeners to filter the table when selection changes
        clientFilter.addEventListener('change', filterTable);
        acceptanceFilter.addEventListener('change', filterTable);

        function filterTable() {
            const clientValue = clientFilter.value.toLowerCase();
            const acceptanceValue = acceptanceFilter.value.toLowerCase();

            // Loop through each row in the table
            const rows = document.querySelectorAll('tbody tr');
            rows.forEach(row => {
                const clientName = row.querySelector('td:nth-child(3)').textContent.trim().toLowerCase();
                const Acceptance = row.querySelector('td:nth-child(6)').textContent.trim().toLowerCase();

                // Show or hide the row based on filter selections
                if ((clientValue === '' || clientName.includes(clientValue)) &&
                    (acceptanceValue === '' || Acceptance.includes(acceptanceValue))) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }
    });
    </script>

    <script src="//unpkg.com/alpinejs" defer></script>
    <script>
        function datePicker() {
            return {
                startDate: '',
                endDate: '',
                updateHref() {
                    @if(Auth::user()->hasRole('Responsable de projet'))
                    const baseUrl = "{{route('Responsable_de_projet.notedefrais.downloadRepports')}}";
                    @endif
                    @if(Auth::user()->hasRole('Admin'))
                    const baseUrl = "{{route('admin.notedefrais.downloadRepports')}}";
                    @endif
                    @if(Auth::user()->hasRole('Employee'))
                    const baseUrl = "{{route('employee.notedefrais.downloadRepports')}}";
                    @endif
                    @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                    const baseUrl = "{{route('Responsable_gestionnaire.notedefrais.downloadRepports')}}";
                    @endif
                    let urlWithParams = baseUrl;
                    if (this.startDate && this.endDate) {
                        urlWithParams += `?startDate=${this.startDate}&endDate=${this.endDate}`;
                    }
                    window.location.href = urlWithParams;
                }
            }
        }
    </script>




</body>

</html>



                        