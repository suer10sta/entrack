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
                            <h6>Devis History Table</h6>
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
                                        <a href="#" class="btn btn-primary mt-2" id="openDevisReportModal">Devis Reports PDF</a>
                                        @if(Auth::user()->hasRole('Admin'))
                                        <a type="button" class="btn btn-primary" href="{{route('admin.devisArchived.archiveIndex')}}">View Archive</a>
                                        @endif
                                        @if(Auth::user()->hasRole('Responsable de projet'))
                                        <a type="button" class="btn btn-primary" href="{{route('Responsable_de_projet.devisArchived.archiveIndex')}}">View Archive</a>
                                        @endif
                                        @if(Auth::user()->hasRole('Employee'))
                                        <a type="button" class="btn btn-primary" href="{{route('employee.devisArchived.archiveIndex')}}">View Archive</a>
                                        @endif
                                        @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                        <a type="button" class="btn btn-primary" href="{{route('Responsable_gestionnaire.devisArchived.archiveIndex')}}">View Archive</a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        @php
                            $Total_Accpeted = App\Models\devis_history::where('entreprise_id', Auth::user()->entreprise_id)->where('acceptanceStatus', 1)->sum('budget');
                            $Total_Declined = App\Models\devis_history::where('entreprise_id', Auth::user()->entreprise_id)->where('acceptanceStatus', 2)->sum('budget');
                            $Total_Pending = App\Models\devis_history::where('entreprise_id', Auth::user()->entreprise_id)->where('acceptanceStatus', 0)->sum('budget');
                        @endphp
                        <div class="d-flex flex-column align-items-end ms-3"> <!-- Adjusted for alignment and spacing -->
                            <div class="d-flex align-items-center">
                                <div>
                                    <label for="totalPaid" class="form-label" style="font-size: 16px !important;">Total Accepted: <label style=" color: #2dce89 !important;font-size: 16px !important;">€{{ number_format($Total_Accpeted, 2, ',', ' ') }}</label> | </label>
                                </div>
                                <div>
                                    <label for="totalUnpaid" class="form-label" style="font-size: 16px !important;">Total Declined: <label style=" color: red !important;font-size: 16px !important;">€{{ number_format($Total_Declined, 2, ',', ' ') }}</label> | </label>
                                </div>
                                <div>
                                    <label for="totalUnpaid" class="form-label" style="font-size: 16px !important;">Total Pending: <label style=" color: orange !important;font-size: 16px !important;">€{{ number_format($Total_Pending, 2, ',', ' ') }}</label></label>
                                </div>
                            </div>
                        </div>
                    </div>

                        <div class="row mb-3 p-4">
                            <div class="col-md-3">
                                <label for="clientFilter" class="form-label">Filter by Client:</label>
                                <select id="clientFilter" class="form-select">
                                    <option value="">All Clients</option>
                                    <!-- Populate dropdown with unique client names -->
                                    @foreach($clients as $client)
                                    <option value="{{$client->name}}">{{$client->name}}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label for="acceptanceFilter" class="form-label">Filter by Accpetance:</label>
                                <select id="acceptanceFilter" class="form-select">
                                    <option value="">All Devis</option>
                                    <option value="Accepted">Accepted</option>
                                    <option value="Declined">Declined</option>
                                    <option value="Pending">Pending</option>
                                </select>
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
                                        devis N
                                    </th>
                                    <th
                                        class="text-center text-uppercase text-secondary text-x font-weight-bolder opacity-10">
                                        Project Name
                                    </th>
                                    <th
                                        class="text-center text-uppercase text-secondary text-x font-weight-bolder opacity-10">
                                        Client Name / Emails
                                    </th>
                                    <th
                                        class="text-center text-uppercase text-secondary text-x font-weight-bolder opacity-10 ps-2">
                                        Budget
                                    </th>
                                    <th
                                        class="text-center text-uppercase text-secondary text-x font-weight-bolder opacity-10 ps-2">
                                        Unbilled Budget
                                    </th>
                                    <th
                                        class="text-center text-uppercase text-secondary text-x font-weight-bolder opacity-10">
                                        Created at
                                    </th>
                                    <th
                                        class="text-center text-uppercase text-secondary text-x font-weight-bolder opacity-10">
                                        Status 
                                    </th>
                                    <th
                                        class="text-center text-uppercase text-secondary text-x font-weight-bolder opacity-10">
                                        Acceptance
                                    </th>
                                    <th
                                        class="text-center text-uppercase text-secondary text-x font-weight-bolder opacity-10">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($devis as $d)
                                <tr style="{{ $d->sent == 1 ? 'background-color: #B6FFA1;' : '' }}">
                                    <td>
                                        <p class="align-middle text-center text-sm font-weight-bold mb-0">
                                            @if(Auth::user()->hasRole('Responsable de projet'))
                                                <a href="{{ route('Responsable_de_projet.devis.downloadDevis', $d->devis_number) }}">{{$entreprise->devis_prefix}}-{{$d->devis_number}}
                                                </a>
                                            @endif
                                            @if(Auth::user()->hasRole('Admin'))
                                                <a href="{{ route('admin.devis.downloadDevis', $d->devis_number) }}">{{$entreprise->devis_prefix}}-{{$d->devis_number}}
                                                </a>
                                            @endif
                                            @if(Auth::user()->hasRole('Employee'))
                                                <a href="{{ route('employee.devis.downloadDevis', $d->devis_number) }}">{{$entreprise->devis_prefix}}-{{$d->devis_number}}
                                                </a>
                                            @endif
                                            @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                                <a href="{{ route('Responsable_gestionnaire.devis.downloadDevis', $d->devis_number) }}">{{$entreprise->devis_prefix}}-{{$d->devis_number}}
                                                </a>
                                            @endif
                                        </p>
                                    <td>
                                        <p class="align-middle text-center text-sm font-weight-bold mb-0">
                                            @php
                                            $project = \App\Models\Project::where('id', $d->project_id)->first();
                                            @endphp
                                            @if($d->acceptanceStatus == 1 || $d->Existing_project_name != null)
                                            @if(Auth::user()->hasRole('Admin'))
                                            <a href="{{route('admin.projects.show', $project->id)}}">{{$project->name}}</a>
                                            @endif
                                            @if(Auth::user()->hasRole('Responsable de projet'))
                                            <a href="{{route('Responsable_de_projet.projects.show', $project->id)}}">{{$project->name}}</a>
                                            @endif
                                            @if(Auth::user()->hasRole('Employee'))
                                            <a href="{{route('employee.projects.show', $project->id)}}">{{$project->name}}</a>
                                            @endif
                                            @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                            <a href="{{route('Responsable_gestionnaire.projects.show', $project->id)}}">{{$project->name}}</a>
                                            @endif
                                            @else
                                            {{$project->name}}
                                            @endif
                                        </p>
                                    </td>

                                    <td>
                                        <p class="align-middle text-center text-sm font-weight-bold mb-0">
                                            @php
                                            $client = \App\Models\Client::where('id', $d->client_id)->first();
                                            $emails = explode(',', $d->emails);
                                            @endphp
                                            @if ($d->emails)
                                                @php
                                                    $emailArray = array_map('trim', explode(',', $d->emails));
                                                @endphp
                                                @foreach ($emailArray as $email)
                                                    {{ $email }}<br />
                                                @endforeach
                                            @else
                                                @if(Auth::user()->hasRole('Admin'))
                                                    <a href="{{route('admin.clients.show', $client->id)}}">{{$client->name}}</a>
                                                @endif
                                                @if(Auth::user()->hasRole('Responsable de projet'))
                                                    <a href="{{route('Responsable_de_projet.clients.show', $client->id)}}">{{$client->name}}</a>
                                                @endif
                                                @if(Auth::user()->hasRole('Employee'))
                                                    <a href="{{route('employee.clients.show', $client->id)}}">{{$client->name}}</a>
                                                @endif
                                                @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                                    <a href="{{route('Responsable_gestionnaire.clients.show', $client->id)}}">{{$client->name}}</a>
                                                @endif
                                            @endif
                                        </p>
                                    </td>
                                    <td>
                                        <p class="text-center text-sm font-weight-bold mb-0 text-success">€{{ number_format($d->budget, 2, ',', ' ') }}</p>
                                    </td>
                                    <td>
                                        @php
                                            $devisInvoicesTotal = \App\Models\invoices_history::where('devis_id', $d->id)
                                                ->sum('total_htva');
                                            $unbilledBudget = ($d->budget ?? 0) - ($devisInvoicesTotal ?? 0);
                                        @endphp
                                        <p class="text-center text-sm font-weight-bold mb-0">€{{ number_format(max($unbilledBudget, 0), 2, ',', ' ') }}</p>
                                    </td>
                                    <td>
                                        <p class="text-center text-sm font-weight-bold mb-0">{{$d->created_at}}
                                        </p>
                                    </td>
                                    <td>
                                        <p class="text-center text-sm font-weight-bold mb-0">
                                            @if($d->acceptanceStatus == 1)
                                            <span class="badge  badge-success"> Accepted </sp> 
                                            @elseif($d->acceptanceStatus == 2 )
                                             <span class="badge  badge-danger"> Declined </span> 
                                            @else
                                            <span class="badge  badge-warning"> Pending </span> 
                                            @endif
                                        </p>
                                    <td>
                                        <div class="d-flex text-center"> 
                                            <div class="d-flex flex-column mr-2">
                                            @if(Auth::user()->hasRole('Responsable de projet'))

                                                <form action="{{route('Responsable_de_projet.devis.acceptDevis', $d->id) }}"
                                                    method="post" onsubmit="return confirm('Are you sure?');">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success btn-xs p-2 mb-0 payment-button">
                                                        Accepted
                                                    </button>
                                                </form>
                                            
                                                <form action="{{route('Responsable_de_projet.devis.declineDevis', $d->id) }}"
                                                    method="post" onsubmit="return confirm('Are you sure?');">
                                                    @csrf
                                                    <button type="submit" class="btn btn-danger btn-xs p-2 mt-2 mb-0 payment-button">
                                                        Declined
                                                    </button>
                                                </form>
                                            @endif
                                            @if(Auth::user()->hasRole('Admin'))
                                                <form action="{{route('admin.devis.acceptDevis', $d->id) }}"
                                                    method="post" onsubmit="return confirm('Are you sure?');">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success btn-xs p-2 mb-0 payment-button">
                                                        Accepted
                                                    </button>
                                                </form>
                                            
                                                <form action="{{route('admin.devis.declineDevis', $d->id) }}"
                                                    method="post" onsubmit="return confirm('Are you sure?');">
                                                    @csrf
                                                    <button type="submit" class="btn btn-danger btn-xs p-2 mt-2 mb-0 payment-button">
                                                        Declined
                                                    </button>
                                                </form>
                                            @endif
                                            @if(Auth::user()->hasRole('Employee'))
                                                <form action="{{route('employee.devis.acceptDevis', $d->id) }}"
                                                    method="post" onsubmit="return confirm('Are you sure?');">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success btn-xs p-2 mb-0 payment-button">
                                                        Accepted
                                                    </button>
                                                </form>
                                            
                                                <form action="{{route('employee.devis.declineDevis', $d->id) }}"
                                                    method="post" onsubmit="return confirm('Are you sure?');">
                                                    @csrf
                                                    <button type="submit" class="btn btn-danger btn-xs p-2 mt-2 mb-0 payment-button">
                                                        Declined
                                                    </button>
                                                </form>
                                            @endif
                                            @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                                <form action="{{route('Responsable_gestionnaire.devis.acceptDevis', $d->id) }}"
                                                    method="post" onsubmit="return confirm('Are you sure?');">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success btn-xs p-2 mb-0 payment-button">
                                                        Accepted
                                                    </button>
                                                </form>
                                            
                                                <form action="{{route('Responsable_gestionnaire.devis.declineDevis', $d->id) }}"
                                                    method="post" onsubmit="return confirm('Are you sure?');">
                                                    @csrf
                                                    <button type="submit" class="btn btn-danger btn-xs p-2 mt-2 mb-0 payment-button">
                                                        Declined
                                                    </button>
                                                </form>
                                            @endif
                                            </div>
                                        </div>
                                    <td>
                                        <div class="">
                                            @if(Auth::user()->hasRole('Admin'))
                                                <a class="btn btn-primary btn-xs p-2 mb-0" href="{{ route('admin.devis.downloadDevis', $d->devis_number) }}">
                                                    download
                                                </a>
                                                @can('Send devis')
                                                <form action="{{ route('admin.devis.sendDevis') }}" method="post" class="d-inline ">
                                                    @csrf
                                                    <input type="hidden" name="devis_id" value="{{ $d->id }}">
                                                    <button type="submit" class="btn btn-success btn-xs p-2 mb-0">Send</button>
                                                </form>
                                                @endcan 
                                                @can('Delete devis')
                                                <form action="{{route('admin.devis.destroy', $d->id)}}" method="post" onsubmit="return confirm('Are you sure?');"
                                                    class="d-inline ">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-xs p-2 mb-0">Delete</button>
                                                </form>
                                                @endcan
                                                <form action="{{route('admin.devis.archive', $d->id)}}" method="post" onsubmit="return confirm('Are you sure?');"
                                                    class="d-inline ">
                                                    @csrf
                                                    <button type="submit" class="btn btn-secondary btn-xs p-2 mb-0">Archive</button>
                                                </form>
                                                <a href="{{route('admin.devis.duplicateDevis', $d->id)}}" class="btn btn-info btn-xs p-2 mb-0">Duplicate</a>
                                                <a href="{{route('admin.devis.editDevis', $d->id)}}" class="btn btn-info btn-xs p-2 mb-0">Edit</a>
                                            @endif
                                            @if(Auth::user()->hasRole('Responsable de projet'))
                                                <a class="btn btn-primary btn-xs p-2 mb-0" href="{{ route('Responsable_de_projet.devis.downloadDevis', $d->devis_number) }}">
                                                    download
                                                </a>
                                                @can('Send devis')
                                                <form action="{{ route('Responsable_de_projet.devis.sendDevis') }}" method="post" class="d-inline ">
                                                    @csrf
                                                    <input type="hidden" name="devis_id" value="{{ $d->id }}">
                                                    <button type="submit" class="btn btn-success btn-xs p-2 mb-0">Send</button>
                                                </form>
                                                @endcan
                                                @can('Delete devis')
                                                <form action="{{route('Responsable_de_projet.devis.destroy', $d->id)}}" method="post" onsubmit="return confirm('Are you sure?');"
                                                    class="d-inline ">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-xs p-2 mb-0">Delete</button>
                                                </form>
                                                @endcan
                                                <form action="{{route('Responsable_de_projet.devis.archive', $d->id)}}" method="post" onsubmit="return confirm('Are you sure?');"
                                                    class="d-inline ">
                                                    @csrf
                                                    <button type="submit" class="btn btn-secondary btn-xs p-2 mb-0">Archive</button>
                                                </form>
                                                <a href="{{route('Responsable_de_projet.devis.duplicateDevis', $d->id)}}" class="btn btn-info btn-xs p-2 mb-0">Duplicate</a>
                                                <a href="{{route('Responsable_de_projet.devis.editDevis', $d->id)}}" class="btn btn-info btn-xs p-2 mb-0">Edit</a>
                                            @endif
                                            @if(Auth::user()->hasRole('Employee'))
                                                <a class="btn btn-primary btn-xs p-2 mb-0" href="{{ route('employee.devis.downloadDevis', $d->devis_number) }}">
                                                    download
                                                </a>
                                                @can('Send devis')
                                                <form action="{{ route('employee.devis.sendDevis') }}" method="post" class="d-inline ">
                                                    @csrf
                                                    <input type="hidden" name="devis_id" value="{{ $d->id }}">
                                                    <button type="submit" class="btn btn-success btn-xs p-2 mb-0">Send</button>
                                                </form>
                                                @endcan
                                                @can('Delete devis')
                                                <form action="{{route('employee.devis.destroy', $d->id)}}" method="post" onsubmit="return confirm('Are you sure?');"
                                                    class="d-inline ">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-xs p-2 mb-0">Delete</button>
                                                </form>
                                                @endcan
                                                <form action="{{route('employee.devis.archive', $d->id)}}" method="post" onsubmit="return confirm('Are you sure?');"
                                                    class="d-inline ">
                                                    @csrf
                                                    <button type="submit" class="btn btn-secondary btn-xs p-2 mb-0">Archive</button>
                                                </form>
                                                <a href="{{route('employee.devis.duplicateDevis', $d->id)}}" class="btn btn-info btn-xs p-2 mb-0">Duplicate</a>
                                                <a href="{{route('employee.devis.editDevis', $d->id)}}" class="btn btn-info btn-xs p-2 mb-0">Edit</a>
                                            @endif
                                            @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                                <a class="btn btn-primary btn-xs p-2 mb-0" href="{{ route('Responsable_gestionnaire.devis.downloadDevis', $d->devis_number) }}">
                                                    download
                                                </a>
                                                @can('Send devis')
                                                <form action="{{ route('Responsable_gestionnaire.devis.sendDevis') }}" method="post" class="d-inline ">
                                                    @csrf
                                                    <input type="hidden" name="devis_id" value="{{ $d->id }}">
                                                    <button type="submit" class="btn btn-success btn-xs p-2 mb-0">Send</button>
                                                </form>
                                                @endcan
                                                @can('Delete devis')
                                                <form action="{{route('Responsable_gestionnaire.devis.destroy', $d->id)}}" method="post" onsubmit="return confirm('Are you sure?');"
                                                    class="d-inline ">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-xs p-2 mb-0">Delete</button>
                                                </form>
                                                @endcan
                                                <form action="{{route('Responsable_gestionnaire.devis.archive', $d->id)}}" method="post" onsubmit="return confirm('Are you sure?');"
                                                    class="d-inline ">
                                                    @csrf
                                                    <button type="submit" class="btn btn-secondary btn-xs p-2 mb-0">Archive</button>
                                                </form>
                                                <a href="{{route('Responsable_gestionnaire.devis.duplicateDevis', $d->id)}}" class="btn btn-info btn-xs p-2 mb-0">Duplicate</a>
                                                <a href="{{route('Responsable_gestionnaire.devis.editDevis', $d->id)}}" class="btn btn-info btn-xs p-2 mb-0">Edit</a>
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

    <!-- Add Modal for Recipient Selection and Attachments -->
    <div class="modal fade" id="sendDevisModal" tabindex="-1" aria-labelledby="sendDevisModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="sendDevisModalLabel">Send Devis</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="sendDevisForm" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="devis_id" id="devis_id">
                        
                        <div class="mb-3">
                            <label class="form-label">Select Recipients:</label>
                            <div id="recipientsList" class="border p-3 rounded">
                                <!-- Recipients will be populated here -->
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="attachments" class="form-label">Additional Attachments (PDF only):</label>
                            <input type="file" class="form-control" id="attachments" name="attachments[]" multiple accept=".pdf">
                            <small class="text-muted">Select PDF files to be merged with the devis</small>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="confirmSend">Send Devis</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Devis Reports Filter -->
    <div class="modal fade" id="devisReportModal" tabindex="-1" aria-labelledby="devisReportModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="devisReportModalLabel">Download Devis Reports</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Choose statuses (optional):</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="devisReportStatus[]" id="status_accepted" value="accepted">
                            <label class="form-check-label" for="status_accepted">Accepted</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="devisReportStatus[]" id="status_declined" value="declined">
                            <label class="form-check-label" for="status_declined">Declined</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="devisReportStatus[]" id="status_pending" value="pending">
                            <label class="form-check-label" for="status_pending">Pending</label>
                        </div>
                        <hr>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="include_archived" value="1">
                            <label class="form-check-label" for="include_archived">Include archived</label>
                        </div>
                    </div>
                    <small class="text-muted">Date range (optional) will be applied from the fields at the top.</small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="confirmDevisReport">Download PDF</button>
                </div>
            </div>
        </div>
    </div>

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
                    const baseUrl = "{{route('Responsable_de_projet.Devissrepports.downloadRepports')}}";
                    @endif
                    @if(Auth::user()->hasRole('Admin'))
                    const baseUrl = "{{route('admin.Devissrepports.downloadRepports')}}";
                    @endif
                    @if(Auth::user()->hasRole('Employee'))
                    const baseUrl = "{{route('employee.Devissrepports.downloadRepports')}}";
                    @endif
                    @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                    const baseUrl = "{{route('Responsable_gestionnaire.Devissrepports.downloadRepports')}}";
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

    <!-- Add this new script for handling the modal and sending functionality -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const devisReportModalInstance = new bootstrap.Modal(document.getElementById('devisReportModal'));
        const openDevisReportModalBtn = document.getElementById('openDevisReportModal');
        if (openDevisReportModalBtn) {
            openDevisReportModalBtn.addEventListener('click', function(e) {
                e.preventDefault();
                devisReportModalInstance.show();
            });
        }

        const confirmDevisReportBtn = document.getElementById('confirmDevisReport');
        if (confirmDevisReportBtn) {
            confirmDevisReportBtn.addEventListener('click', function() {
                // Build base URL by role
                let baseUrl = '';
                try {
                    // These blade blocks resolve to only one const at runtime
                    @if(Auth::user()->hasRole('Responsable de projet'))
                    baseUrl = "{{route('Responsable_de_projet.Devissrepports.downloadRepports')}}";
                    @endif
                    @if(Auth::user()->hasRole('Admin'))
                    baseUrl = "{{route('admin.Devissrepports.downloadRepports')}}";
                    @endif
                    @if(Auth::user()->hasRole('Employee'))
                    baseUrl = "{{route('employee.Devissrepports.downloadRepports')}}";
                    @endif
                    @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                    baseUrl = "{{route('Responsable_gestionnaire.Devissrepports.downloadRepports')}}";
                    @endif
                } catch (e) {}

                const startDateEl = document.getElementById('startDate');
                const endDateEl = document.getElementById('endDate');
                const startDate = startDateEl ? startDateEl.value : '';
                const endDate = endDateEl ? endDateEl.value : '';
                const selectedStatuses = Array.from(document.querySelectorAll('input[name="devisReportStatus[]"]:checked')).map(x => x.value);
                const includeArchived = document.getElementById('include_archived')?.checked;

                const params = new URLSearchParams();
                if (startDate && endDate) {
                    params.append('startDate', startDate);
                    params.append('endDate', endDate);
                }
                if (selectedStatuses.length > 0) {
                    // Send as comma-separated list
                    params.append('filters', selectedStatuses.join(','));
                }
                if (includeArchived) {
                    params.append('archived', '1');
                }

                const finalUrl = params.toString() ? `${baseUrl}?${params.toString()}` : baseUrl;
                window.location.href = finalUrl;
                devisReportModalInstance.hide();
            });
        }

        const sendDevisModal = new bootstrap.Modal(document.getElementById('sendDevisModal'));
        
        // Function to handle the send button click
        function handleSendClick(event) {
            event.preventDefault();
            const form = event.target.closest('form');
            const devisId = form.querySelector('input[name="devis_id"]').value;
            const formAction = form.getAttribute('action');
            
            // Populate the modal
            document.getElementById('devis_id').value = devisId;
            const recipientsList = document.getElementById('recipientsList');
            recipientsList.innerHTML = '<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>';
            
            // Fetch client contacts for this devis
            fetch(`/admin/devis/get-contacts/${devisId}`)
                .then(response => response.json())
                .then(data => {
                    recipientsList.innerHTML = '';
                    if (data.contacts && data.contacts.length > 0) {
                        data.contacts.forEach(contact => {
                            const div = document.createElement('div');
                            div.className = 'form-check';
                            div.innerHTML = `
                                <input class="form-check-input" type="checkbox" name="recipients[]" value="${contact.email}" id="recipient_${contact.id}" checked>
                                <label class="form-check-label" for="recipient_${contact.id}">
                                    ${contact.name} (${contact.email})
                                </label>
                            `;
                            recipientsList.appendChild(div);
                        });
                    } else {
                        recipientsList.innerHTML = '<p class="text-muted">No contacts found for this client</p>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    recipientsList.innerHTML = '<p class="text-danger">Error loading contacts</p>';
                });
            
            // Set the form action for sending
            document.getElementById('sendDevisForm').action = formAction;
            
            // Show the modal
            sendDevisModal.show();
        }

        // Add click event listeners to all send buttons
        document.querySelectorAll('form[action*="sendDevis"]').forEach(form => {
            form.querySelector('button[type="submit"]').addEventListener('click', handleSendClick);
        });

        // Handle the confirm send button click
        document.getElementById('confirmSend').addEventListener('click', function() {
            const form = document.getElementById('sendDevisForm');
            const devisId = document.getElementById('devis_id').value;
            const selectedRecipients = Array.from(document.querySelectorAll('input[name="recipients[]"]:checked'))
                .map(checkbox => checkbox.value);
            
            if (selectedRecipients.length === 0) {
                alert('Please select at least one recipient');
                return;
            }

            // Create FormData object
            const formData = new FormData();
            formData.append('_token', document.querySelector('input[name="_token"]').value);
            formData.append('devis_id', devisId);
            formData.append('recipients', JSON.stringify(selectedRecipients));
            
            // Add attachments if any
            const attachments = document.getElementById('attachments').files;
            for (let i = 0; i < attachments.length; i++) {
                formData.append('attachments[]', attachments[i]);
            }

            // Send the request
            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    alert('Devis sent successfully!');
                    sendDevisModal.hide();
                    // Optionally refresh the page or update the UI
                    location.reload();
                } else {
                    alert('Error sending devis: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while sending the devis');
            });
        });
    });
</script>




</body>

</html>



                        