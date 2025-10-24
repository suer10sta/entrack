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
.credit-note-row {
    background-color: #fff3cd !important; /* Light yellow background */
}
.action-buttons {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 8px;
}
.credit-note-text {
    display: inline-flex;
    align-items: center;
    margin: 0 8px;
    font-weight: bold;
}
</style>
<meta name="csrf-token" content="{{ csrf_token() }}">


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
                    <div class="card table-responsive">
                    <div class="card-header p-3 pb-0">
                        <div class="d-flex justify-content-between align-items-start">
                            <h6>Invoices Archive Table</h6>
                            <div class="d-flex align-items-start">
                                <div x-data="datePicker()" class="d-flex align-items-center">
                                    <div>
                                        <label class="form-label d-block">&nbsp;</label> <!-- Adjusted for alignment -->
                                        @if(Auth::user()->hasRole('Admin'))
                                        <a type="button" class="btn btn-primary" href="{{route('admin.invoices.invoicehistory')}}">Return Back</a>
                                        @endif
                                        @if(Auth::user()->hasRole('Responsable de projet'))
                                        <a type="button" class="btn btn-primary" href="{{route('Responsable_de_projet.invoices.invoicehistory')}}">Return Back</a>
                                        @endif
                                        @if(Auth::user()->hasRole('Employee'))
                                        <a type="button" class="btn btn-primary" href="{{route('employee.invoices.invoicehistory')}}">Return Back</a>
                                        @endif
                                        @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                        <a type="button" class="btn btn-primary" href="{{route('Responsable_gestionnaire.invoices.invoicehistory')}}">Return Back</a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        @php
                            $totalPaid = App\Models\invoices_history::where('entreprise_id', Auth::user()->entreprise_id)->where('Payment_status', 1)->where('archived', 1)->sum('total_ttc');
                            
                            $totaltva = App\Models\invoices_history::where('entreprise_id', Auth::user()->entreprise_id)->where('archived', 1)->sum('total_tva');                           
                        @endphp
                        <div class="d-flex flex-column align-items-end ms-3"> <!-- Adjusted for alignment and spacing -->
                            <div class="d-flex align-items-center">
                                <div>
                                    <label for="totalPaid" class="form-label" style="font-size: 16px !important;">Total Paid: <label style=" color: #2dce89 !important;font-size: 16px !important;">€{{ number_format($totalPaid, 2, ',', ' ') }}</label> | </label>
                                </div>
                                
                                <div>
                                    <label for="totalUnpaid" class="form-label" style="font-size: 16px !important;">Total TVA: <label style="font-size: 16px !important;">€{{ number_format($totaltva, 2, ',', ' ') }}</label></label>
                                </div>
                            </div>
                        </div>
                    </div>
                        @if(session('message'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('message') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">X</button>
                    </div>
                  @endif

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
                                <label for="projectFilter" class="form-label">Filter by Project:</label>
                                <select id="projectFilter" class="form-select">
                                    <option value="">All Projects</option>
                                    <!-- Populate dropdown with unique project names -->
                                    @foreach($projects as $project)
                                    <option value="{{$project->name}}">{{strtoupper($project->name)}}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label for="paymentFilter" class="form-label">Filter by Payment:</label>
                                <select id="paymentFilter" class="form-select">
                                    <option value="">All Invoices</option>
                                    <option value="Paid">Paid</option>
                                    <option value="Unpaid">Unpaid</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label for="typeFilter" class="form-label">Filter by Type:</label>
                                <select id="typeFilter" class="form-select">
                                    <option value="">All Invoices</option>
                                    <option value="Partial Period">Partial Period</option>
                                    <option value="Full Period">Full Period</option>
                                </select>
                            </div>
                        </div>

                        <table class="table align-items-center mb-0" id="invoicesTable">
                            <thead>
                                <tr>
                                    <th
                                        class="text-center text-uppercase text-secondary text-x font-weight-bolder opacity-10">
                                        Invoice N
                                    </th>
                                    <th
                                        class="text-center text-uppercase text-secondary text-x font-weight-bolder opacity-10">
                                        Project Name
                                    </th>
                                    <th
                                        class="text-center text-uppercase text-secondary text-x font-weight-bolder opacity-10">
                                        TVA
                                    </th>
                                    <th
                                        class="text-center text-uppercase text-secondary text-x font-weight-bolder opacity-10">
                                        Client Name
                                    </th>
                                    <th
                                        class="text-center text-uppercase text-secondary text-x font-weight-bolder opacity-10">
                                        Invoice Type
                                    </th>
                                    <th
                                        class="text-center text-uppercase text-secondary text-x font-weight-bolder opacity-10">
                                        Created at
                                    </th>
                                    <th
                                        class="text-center text-uppercase text-secondary text-x font-weight-bolder opacity-10">
                                        Cost Total
                                    </th>
                                    <th
                                        class="text-center text-uppercase text-secondary text-x font-weight-bolder opacity-10">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($invoices as $invoice)
                                <tr @if($invoice->Payment_status == -1) class="credit-note-row" @endif>
                                    <td>
                                        <p class="align-middle text-center text-sm font-weight-bold mb-0">
                                            @php
                                                $dueDate = \Carbon\Carbon::parse($invoice->due_date);
                                                $today = \Carbon\Carbon::now();
                                                $isOverdue = $dueDate->isPast() && !$invoice->Payment_status;
                                                $daysOverdue = $isOverdue ? $today->diffInDays($dueDate) : 0;
                                            @endphp
                                            
                                            @if(Auth::user()->hasRole('Responsable de projet'))
                                                <a href="{{ route('Responsable_de_projet.invoices.servePdf', ['invoiceName' => $invoice->id]) }}" 
                                                    target="_blank" 
                                                    style="{{ $isOverdue ? 'color: red;' : '' }}">
                                                    {{$entreprise_infos->invoice_prefix}}-{{date('ym', strtotime($invoice->created_at))}}{{$invoice->number}}
                                                    @if($isOverdue)
                                                        <span class="text-danger">({{ $daysOverdue }} days overdue)</span>
                                                    @endif
                                                </a>
                                            @endif
                                            @if(Auth::user()->hasRole('Admin'))
                                                <a href="{{ route('admin.invoices.servePdf', ['invoiceName' => $invoice->id]) }}" 
                                                    target="_blank"
                                                    style="{{ $isOverdue ? 'color: red;' : '' }}">
                                                    {{$entreprise_infos->invoice_prefix}}-{{date('ym', strtotime($invoice->created_at))}}{{$invoice->number}}
                                                    @if($isOverdue)
                                                        <span class="text-danger">({{ $daysOverdue }} days overdue)</span>
                                                    @endif
                                                </a>
                                            @endif
                                            @if(Auth::user()->hasRole('Employee'))
                                                <a href="{{ route('employee.invoices.servePdf', ['invoiceName' => $invoice->id]) }}" 
                                                    target="_blank"
                                                    style="{{ $isOverdue ? 'color: red;' : '' }}">
                                                    {{$entreprise_infos->invoice_prefix}}-{{date('ym', strtotime($invoice->created_at))}}{{$invoice->number}}
                                                    @if($isOverdue)
                                                        <span class="text-danger">({{ $daysOverdue }} days overdue)</span>
                                                    @endif
                                                </a>
                                            @endif
                                            @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                                <a href="{{ route('Responsable_gestionnaire.invoices.servePdf', ['invoiceName' => $invoice->id]) }}" target="_blank">
                                                    {{$entreprise_infos->invoice_prefix}}-{{date('ym', strtotime($invoice->created_at))}}{{$invoice->number}}
                                                </a>
                                            @endif
                                        </p>
                                    </td>
                                    <td>
                                        <p class="align-middle text-center text-sm font-weight-bold mb-0">
                                            {{strtoupper($invoice->project_name)}}  
                                        </p>
                                    </td>
                                    <td>
                                        <p class="align-middle text-center text-sm font-weight-bold mb-0 text-success">
                                            @php 
                                                $project = App\Models\Project::find($invoice->project_id);
                                            @endphp
                                            {{$project->tva}}%
                                        </p>
                                    </td>
                                    <td>
                                        <p class="align-middle text-center text-sm font-weight-bold mb-0">
                                            @php
                                            $client = \App\Models\Client::where('id', $invoice->client_id)->first();
                                            @endphp
                                            {{$client->name}}
                                        </p>
                                    </td>
                                    <td>
                                        <p class="text-center text-sm font-weight-bold mb-0">
                                            @if($invoice->type == 'Partial')
                                                {{$invoice->type}}-{{$invoice->partial}}
                                            @elseif($invoice->type == 'Percentage')
                                                {{$invoice->type}}-{{$invoice->percentage}}
                                            @else
                                                {{$invoice->type}}
                                            @endif
                                        </p>
                                    </td>
                                    <td>
                                        <p class="text-center text-sm font-weight-bold mb-0">{{$invoice->created_at}}
                                        </p>
                                    </td>
                                    <td id="totalcost">
                                        <p class="text-center text-sm font-weight-bold mb-0 text-success">€<span id="totalCostValue">{{ number_format($invoice->total_ttc, 2, ',', ' ') }}</span>
                                        </p>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            @if(Auth::user()->hasRole('Admin'))
                                                @if(\App\Models\invoices_history::where('id',
                                                $invoice->id)->first()->Payment_status==1)
                                                <form action="{{route('admin.invoice.deactivate', $invoice->id) }}"
                                                    method="post" onsubmit="return confirm('Are you sure?');">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success m-2 payment-button p-2">
                                                        Paid
                                                    </button>
                                                </form>
                                                @elseif(\App\Models\invoices_history::where('id',
                                                $invoice->id)->first()->Payment_status==-1)
                                                <span class="credit-note-text text-warning"><b>Credit Note Applied</b></span>
                                                @else
                                                <form action="{{route('admin.invoice.activate', $invoice->id) }}"
                                                    method="post" onsubmit="return confirm('Are you sure?');">
                                                    @csrf
                                                    <button type="submit" class="btn btn-danger m-2 payment-button p-2">
                                                        Unpaid
                                                    </button>
                                                </form>
                                                @endif
                                            @endif
                                            @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                                @if(\App\Models\invoices_history::where('id',
                                                $invoice->id)->first()->Payment_status==1)
                                                <form action="{{route('Responsable_gestionnaire.invoice.deactivate', $invoice->id) }}"
                                                    method="post" onsubmit="return confirm('Are you sure?');">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success m-2 payment-button p-2">
                                                        Paid
                                                    </button>
                                                </form>
                                                @elseif(\App\Models\invoices_history::where('id',
                                                $invoice->id)->first()->Payment_status==-1)
                                                <span class="credit-note-text text-warning"><b>Credit Note Applied</b></span>
                                                @else
                                                <form action="{{route('Responsable_gestionnaire.invoice.activate', $invoice->id) }}"
                                                    method="post" onsubmit="return confirm('Are you sure?');">
                                                    @csrf
                                                    <button type="submit" class="btn btn-danger m-2 payment-button p-2">
                                                        Unpaid
                                                    </button>
                                                </form>
                                                @endif
                                            @endif
                                            @if(Auth::user()->hasRole('Employee'))
                                                @if(\App\Models\invoices_history::where('id',
                                                $invoice->id)->first()->Payment_status==1)
                                                <form action="{{route('employee.invoice.deactivate', $invoice->id) }}"
                                                    method="post" onsubmit="return confirm('Are you sure?');">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success btn-xs m-2 payment-button">
                                                        Paid
                                                    </button>
                                                </form>
                                                @elseif(\App\Models\invoices_history::where('id',
                                                $invoice->id)->first()->Payment_status==-1)
                                                <span class="credit-note-text text-warning"><b>Credit Note Applied</b></span>
                                                @else
                                                <form action="{{route('employee.invoice.activate', $invoice->id) }}"
                                                    method="post" onsubmit="return confirm('Are you sure?');">
                                                    @csrf
                                                    <button type="submit" class="btn btn-danger btn-xs m-2 payment-button">
                                                        Unpaid
                                                    </button>
                                                </form>
                                                @endif
                                            @endif
                                            @if ($invoice->sentInvoice == 0)
                                            <button type="button" class="btn btn-dark btn-xs p-2 m-2"
                                                data-bs-toggle="modal" data-bs-target="#sendInvoiceModal"
                                                data-invoice-id="{{ $invoice->id }}">
                                                Send
                                            </button>
                                            @else
                                            <button type="button" class="btn btn-dark btn-xs p-2 m-2"
                                                data-bs-toggle="modal" data-bs-target="#sendInvoiceModal"
                                                data-invoice-id="{{ $invoice->id }}">
                                                Resend
                                            </button>
                                            @endif

                                            @if(!$invoice->Payment_status == 1)
                                                @if(Auth::user()->hasRole('Admin') || Auth::user()->hasRole('Responsable de projet') || Auth::user()->hasRole('Employee'))
                                                    @can('Send invoice')
                                                    <div class="dropdown m-2">
                                                        <button class="btn btn-secondary btn-xs dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                                            Send Reminder
                                                        </button>
                                                        <ul class="dropdown-menu">
                                                            <li><a class="dropdown-item" href="{{ route((Auth::user()->hasRole('Admin') ? 'admin.invoice.sendManualremind' : (Auth::user()->hasRole('Responsable de projet') ? 'Responsable_de_projet.invoice.sendManualremind' : 'employee.invoice.sendManualremind')), ['id' => $invoice->id, 'reminder' => 1]) }}">Send Reminder 1</a></li>
                                                            <li><a class="dropdown-item" href="{{ route((Auth::user()->hasRole('Admin') ? 'admin.invoice.sendManualremind' : (Auth::user()->hasRole('Responsable de projet') ? 'Responsable_de_projet.invoice.sendManualremind' : 'employee.invoice.sendManualremind')), ['id' => $invoice->id, 'reminder' => 2]) }}">Send Reminder 2</a></li>
                                                            <li><a class="dropdown-item" href="{{ route((Auth::user()->hasRole('Admin') ? 'admin.invoice.sendManualremind' : (Auth::user()->hasRole('Responsable de projet') ? 'Responsable_de_projet.invoice.sendManualremind' : 'employee.invoice.sendManualremind')), ['id' => $invoice->id, 'reminder' => 3]) }}">Send Reminder 3</a></li>
                                                        </ul>
                                                    </div>
                                                    @endcan
                                                @endif
                                            @endif

                                            <div class="btn-group">
                                                <button type="button" class="btn btn-dark btn-xs dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                                    Credit Note
                                                </button>
                                                <ul class="dropdown-menu">
                                                    @if($invoice->type == 'Detailed')
                                                        @if(Auth::user()->hasRole('Admin'))
                                                        <li><a class="dropdown-item" href="{{ route('admin.invoice.detailedCreditNote', $invoice->id) }}?type=full">Full</a></li>
                                                        @endif
                                                        @if(Auth::user()->hasRole('Responsable de projet'))
                                                        <li><a class="dropdown-item" href="{{ route('Responsable_de_projet.invoice.detailedCreditNote', $invoice->id) }}?type=full">Full</a></li>
                                                        @endif
                                                        @if(Auth::user()->hasRole('Employee'))
                                                        <li><a class="dropdown-item" href="{{ route('employee.invoice.detailedCreditNote', $invoice->id) }}?type=full">Full</a></li>
                                                        @endif
                                                        @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                                        <li><a class="dropdown-item" href="{{ route('Responsable_gestionnaire.invoice.detailedCreditNote', $invoice->id) }}?type=full">Full</a></li>
                                                        @endif
                                                    @else
                                                        @if(Auth::user()->hasRole('Admin'))
                                                        <li><a class="dropdown-item" href="{{ route('admin.invoice.creditNote', $invoice->id) }}?type=full">Full</a></li>
                                                        @endif
                                                        @if(Auth::user()->hasRole('Responsable de projet'))
                                                        <li><a class="dropdown-item" href="{{ route('Responsable_de_projet.invoice.creditNote', $invoice->id) }}?type=full">Full</a></li>
                                                        @endif
                                                        @if(Auth::user()->hasRole('Employee'))
                                                        <li><a class="dropdown-item" href="{{ route('employee.invoice.creditNote', $invoice->id) }}?type=full">Full</a></li>
                                                        @endif
                                                        @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                                        <li><a class="dropdown-item" href="{{ route('Responsable_gestionnaire.invoice.creditNote', $invoice->id) }}?type=full">Full</a></li>
                                                        @endif
                                                    @endif
                                                </ul>
                                            </div>

                                            <!-- Partial Credit Note Modal -->
                                            <div class="modal fade" id="partialCreditNoteModal" tabindex="-1" aria-labelledby="partialCreditNoteModalLabel" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="partialCreditNoteModalLabel">Partial Credit Note</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <form id="partialCreditNoteForm">
                                                                <div class="mb-3">
                                                                    <label for="partialAmount" class="form-label">Enter Amount:</label>
                                                                    <input type="number" class="form-control" id="partialAmount" name="partialAmount" step="0.0001" required>
                                                                    <span id="error-message" style="color: red; display: none;">Amount exceeds total cost.</span>
                                                                </div>
                                                                <input type="hidden" id="invoiceId" name="invoiceId">
                                                                <input type="hidden" id="totalCost" name="totalCost">
                                                            </form>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                            <button type="button" class="btn btn-primary" onclick="submitPartialCreditNote()">Submit</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach



                            </tbody>
                        </table>
                        <div id="creditNoteContent"></div>
                        <!-- Attachement Modal -->
                        <div class="modal fade" id="sendInvoiceModal" tabindex="-1"
                            aria-labelledby="sendInvoiceModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="sendInvoiceModalLabel">Send Invoice</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <form id="sendInvoiceForm" method="post" enctype="multipart/form-data">
                                        @csrf
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label for="fileUpload" class="form-label">Upload File:</label>
                                                <input type="file" class="form-control" id="fileUpload" name="file[]" multiple>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary"
                                                data-bs-dismiss="modal">Close</button>
                                            <button type="submit" class="btn btn-primary">Send</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </main>




    @include('dashboard_layouts.scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.4/xlsx.full.min.js"></script>

    <script>
// Function to show the partial credit note modal
function showPartialPopup(invoiceId, totalCost) {
  document.getElementById('invoiceId').value = invoiceId;
  document.getElementById('totalCost').value = totalCost;
  var modal = new bootstrap.Modal(document.getElementById('partialCreditNoteModal'));
  modal.show();
}

// Function to submit the partial credit note
function submitPartialCreditNote() {
  var form = document.getElementById('partialCreditNoteForm');
  var amount = parseFloat(document.getElementById('partialAmount').value.replace(',', '.'));  // Added replace to handle comma input
  var invoiceId = document.getElementById('invoiceId').value;
  var totalCost = parseFloat(document.getElementById('totalCost').value.replace(',', '.')); // Added replace to handle comma values
  var errorMessage = document.getElementById('error-message');

  if (amount > totalCost) {
    errorMessage.style.display = 'block';
    return;
  } else {
    errorMessage.style.display = 'none';
  }

  // Send AJAX request to the controller
 // Send AJAX request to the controller
 @if(Auth::user()->hasRole('Responsable de projet'))
    var url = `{{ route('Responsable_de_projet.invoice.creditNote', '') }}/${invoiceId}?type=partial&amount=${amount}`;
  @elseif(Auth::user()->hasRole('Admin'))
    var url = `{{ route('admin.invoice.creditNote', '') }}/${invoiceId}?type=partial&amount=${amount}`;
  @elseif(Auth::user()->hasRole('Employee'))
    var url = `{{ route('employee.invoice.creditNote', '') }}/${invoiceId}?type=partial&amount=${amount}`;
  @endif

  fetch(url, {
    method: 'GET',
    headers: {
      'X-Requested-With': 'XMLHttpRequest',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    }
  })
  .then(response => response.text())
  .then(html => {
    document.open();
    document.write(html);
    document.close();
  })
  .catch(error => {
    console.error('Error:', error);
    alert('An error occurred while processing your request: ' + error.message);
  });
}
</script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    

    <script>
    // JavaScript for filtering the table
    document.addEventListener("DOMContentLoaded", function() {
        // Get references to the filter dropdowns
        const clientFilter = document.getElementById('clientFilter');
        const projectFilter = document.getElementById('projectFilter');
        const paymentFilter = document.getElementById('paymentFilter');
        const typeFilter = document.getElementById('typeFilter');

        // Add event listeners to filter the table when selection changes
        clientFilter.addEventListener('change', filterTable);
        projectFilter.addEventListener('change', filterTable);
        paymentFilter.addEventListener('change', filterTable);
        typeFilter.addEventListener('change', filterTable);

        function filterTable() {
            const clientValue = clientFilter.value.toLowerCase();
            const projectValue = projectFilter.value.toLowerCase();
            const paymentValue = paymentFilter.value;
            const typeValue = typeFilter.value.toLowerCase();;

            // Loop through each row in the table
            const rows = document.querySelectorAll('tbody tr');
            rows.forEach(row => {
                const clientName = row.querySelector('td:nth-child(4)').textContent.trim().toLowerCase();
                const projectName = row.querySelector('td:nth-child(2)').textContent.trim().toLowerCase();
                const paymentButton = row.querySelector('.payment-button'); // Assuming a class of 'payment-button' for the payment button
                const paymentStatus = paymentButton.textContent.trim(); // Extract payment status from the button
                const invoiceType = row.querySelector('td:nth-child(7)').textContent.trim().toLowerCase();

                // Show or hide the row based on filter selections
                if ((clientValue === '' || clientName.includes(clientValue)) &&
                    (projectValue === '' || projectName.includes(projectValue)) &&
                    (paymentValue === '' || paymentStatus === paymentValue) && 
                    (typeValue === '' || invoiceType === typeValue)) {
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
                        const baseUrl = "{{route('Responsable_de_projet.Invoicessrepports.downloadRepports')}}";
                    @endif
                    @if(Auth::user()->hasRole('Admin'))
                        const baseUrl = "{{route('admin.Invoicessrepports.downloadRepports')}}";
                    @endif
                    @if(Auth::user()->hasRole('Employee'))
                        const baseUrl = "{{route('employee.Invoicessrepports.downloadRepports')}}";
                    @endif
                    let urlWithParams = baseUrl;
                    if (this.startDate && this.endDate) {
                        urlWithParams += `?startDate=${this.startDate}&endDate=${this.endDate}`;
                    }
                    window.location.href = urlWithParams; // Redirect to the updated URL
                }
            }
        }
    </script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var sendInvoiceModal = document.getElementById('sendInvoiceModal');
        sendInvoiceModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var invoiceId = button.getAttribute('data-invoice-id');
            var form = document.getElementById('sendInvoiceForm');
            form.action = '{{ route("admin.invoice.sendInvoice", ":invoice_id") }}'.replace(':invoice_id', invoiceId);
        });
    });

    </script> 




</body>

</html>