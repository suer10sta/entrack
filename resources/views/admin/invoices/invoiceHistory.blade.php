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

.table-responsive {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    margin: 0;
    padding: 0;
}

.table {
    width: 100%;
    margin-bottom: 0;
}

/* Optional: Adjust column widths */
.table th:nth-child(1) { width: 10%; }  /* N column */
.table th:nth-child(2) { width: 20%; }  /* PROJECT NAME */
.table th:nth-child(3) { width: 20%; }  /* CLIENT NAME */
.table th:nth-child(4) { width: 15%; }  /* INVOICE TYPE */
.table th:nth-child(5) { width: 15%; }  /* CREATED AT */
.table th:nth-child(6) { width: 20%; }  /* ACTIONS */01

/* Make sure the action buttons stay on one line */
.table td:last-child {
    white-space: nowrap;
}

/* Optional: Add some padding to cells */
.table td, .table th {
    padding: 0.75rem;
}

.form-check-input {
    border: 1px solid #ccc;
    border-radius: 4px;
    padding: 10px;
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
                            <h6>Invoices History Table</h6>
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
                                        <a href="#" class="btn btn-primary mt-2" id="openInvoiceReportModal">Invoices Reports PDF</a>
                                    </div>
                                    <div>
                                        <label class="form-label d-block">&nbsp;</label> <!-- Adjusted for alignment -->
                                        @if(Auth::user()->hasRole('Admin'))
                                        <a href="{{route('admin.invoiceArchived.archiveIndex')}}" class="btn btn-primary mt-2 ms-2" >View Archive</a>
                                        @endif
                                        @if(Auth::user()->hasRole('Responsable de projet'))
                                        <a href="{{route('Responsable_de_projet.invoiceArchived.archiveIndex')}}" class="btn btn-primary mt-2 ms-2" >View Archive</a>
                                        @endif
                                        @if(Auth::user()->hasRole('Employee'))
                                        <a href="{{route('employee.invoiceArchived.archiveIndex')}}" class="btn btn-primary mt-2 ms-2" >View Archive</a>
                                        @endif
                                        @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                        <a href="{{route('Responsable_gestionnaire.invoiceArchived.archiveIndex')}}" class="btn btn-primary mt-2 ms-2" >View Archive</a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        @php
                            $totalPaid = App\Models\invoices_history::where('entreprise_id', Auth::user()->entreprise_id)->where('Payment_status', 1)->sum('total_ttc');
                            $totalUnpaid = App\Models\invoices_history::where('entreprise_id', Auth::user()->entreprise_id)
                                            ->where(function($query) {
                                                $query->where('Payment_status', 0)
                                                    ->orWhereNull('Payment_status');
                                            })
                                            ->sum('total_ttc');
                            $total = $totalPaid + $totalUnpaid;
                            $totaltva = App\Models\invoices_history::where('entreprise_id', Auth::user()->entreprise_id)->sum('total_tva');                           
                        @endphp
                        <div class="d-flex flex-column align-items-end ms-3"> <!-- Adjusted for alignment and spacing -->
                            <div class="d-flex align-items-center">
                                <div>
                                    <label for="totalPaid" class="form-label" style="font-size: 16px !important;">Total Paid: <label style=" color: #2dce89 !important;font-size: 16px !important;">€{{ number_format($totalPaid, 2, ',', ' ') }}</label> | </label>
                                </div>
                                <div>
                                    <label for="totalUnpaid" class="form-label" style="font-size: 16px !important;">Total Unpaid: <label style=" color: red !important;font-size: 16px !important;">€{{ number_format($totalUnpaid, 2, ',', ' ') }}</label> | </label>
                                </div>
                                <div>
                                    <label for="total" class="form-label" style="font-size: 16px !important;">Total: <label style="font-size: 16px !important;color: #4876ff;">€{{ number_format($total, 2, ',', ' ') }}</label> | </label>
                                </div>
                                <div>
                                    <label for="totalUnpaid" class="form-label" style="font-size: 16px !important;">Total TVA: <label style="font-size: 16px !important;">€{{ number_format($totaltva, 2, ',', ' ') }}</label></label>
                                </div>
                            </div>
                        </div>
                    </div>
                        <!-- Add message container here -->
                        <div id="messageContainer" style="display: none;" class="alert alert-dismissible fade show mt-3">
                            <span id="messageText"></span>
                            <button type="button" class="btn-close" onclick="this.parentElement.style.display='none'" aria-label="Close"></button>
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
                                    <option value="partial">Partial</option>
                                    <option value="percentage">Percentage</option>
                                    <option value="period">Period</option>
                                    <option value="detailed">Detailed</option>
                                    <option value="full">Full</option>
                                </select>
                            </div>
                        </div>

                        <div class="table-responsive">
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
                                    <tr>
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
                                            <div class="card-footer d-flex justify-content-end">

                                                
                                                    @if(Auth::user()->hasRole('Admin'))
                                                        @if(\App\Models\invoices_history::where('id',
                                                        $invoice->id)->first()->Payment_status==1)
                                                        <form action="{{route('admin.invoice.deactivate', $invoice->id) }}"
                                                            method="post" onsubmit="return confirm('Are you sure?');">
                                                            @csrf
                                                            <button type="submit" class="btn btn-success m-2 payment-button p-2" style="height: 30px; width: 78px; text-align: center; line-height: 0px;">
                                                                Paid
                                                            </button>
                                                        </form>
                                                        @else
                                                        <form action="{{route('admin.invoice.activate', $invoice->id) }}"
                                                            method="post" onsubmit="return confirm('Are you sure?');">
                                                            @csrf
                                                            <button type="submit" class="btn btn-danger m-2 payment-button p-2" style="height: 30px; width: 78px; text-align: center; line-height: 0px;">
                                                                Unpaid
                                                            </button>
                                                        </form>
                                                        @endif
                                                    @endif
                                                    @if(Auth::user()->hasRole('Responsable de projet'))
                                                        @if(\App\Models\invoices_history::where('id',
                                                        $invoice->id)->first()->Payment_status==1)
                                                        <form action="{{route('Responsable_de_projet.invoice.deactivate', $invoice->id) }}"
                                                            method="post" onsubmit="return confirm('Are you sure?');">
                                                            @csrf
                                                            <button type="submit" class="btn btn-success btn-xs m-2 payment-button" style="height: 30px; width: 78px; text-align: center; line-height: 0px;">
                                                                Paid
                                                            </button>
                                                        </form>
                                                        @else
                                                        <form action="{{route('Responsable_de_projet.invoice.activate', $invoice->id) }}"
                                                            method="post" onsubmit="return confirm('Are you sure?');">
                                                            @csrf
                                                            <button type="submit" class="btn btn-danger btn-xs m-2 payment-button" style="height: 30px; width: 78px; text-align: center; line-height: 0px;"> 
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
                                                            <button type="submit" class="btn btn-success btn-xs m-2 payment-button" style="height: 30px; width: 78px; text-align: center; line-height: 0px;">
                                                                Paid
                                                            </button>
                                                        </form>
                                                        @else
                                                        <form action="{{route('employee.invoice.activate', $invoice->id) }}"
                                                            method="post" onsubmit="return confirm('Are you sure?');">
                                                            @csrf
                                                            <button type="submit" class="btn btn-danger btn-xs m-2 payment-button" style="height: 30px; width: 78px; text-align: center; line-height: 0px;"> 
                                                                Unpaid
                                                            </button>
                                                        </form>
                                                        @endif
                                                    @endif
                                                    @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                                        <form action="{{route('Responsable_gestionnaire.invoice.deactivate', $invoice->id) }}" method="post">
                                                            @csrf
                                                            <button type="submit" class="btn btn-success btn-xs m-2 payment-button" style="height: 30px; width: 78px; text-align: center; line-height: 0px;">
                                                                Paid
                                                            </button>
                                                        </form>
                                                        @if(\App\Models\invoices_history::where('id',
                                                        $invoice->id)->first()->Payment_status==1)
                                                        <form action="{{route('Responsable_gestionnaire.invoice.activate', $invoice->id) }}" method="post">
                                                            @csrf
                                                            <button type="submit" class="btn btn-danger btn-xs m-2 payment-button" style="height: 30px; width: 78px; text-align: center; line-height: 0px;">
                                                                Unpaid
                                                            </button>
                                                        </form>
                                                        @endif
                                                    @endif
                                                    @if ($invoice->sentInvoice == 0)
                                                    @can('Send invoice')
                                                    <!-- Send Button Triggering Modal --> 
                                                    <button type="button" class="btn btn-dark btn-xs p-2 m-2" style="height: 30px; width: 70px;"
                                                        data-bs-toggle="modal" data-bs-target="#sendInvoiceModal"
                                                        data-invoice-id="{{ $invoice->id }}">
                                                        Send
                                                    </button>
                                                    @endcan
                                                    @else
                                                    @can('Send invoice')
                                                    <button type="button" class="btn btn-dark btn-xs p-2 m-2" style="height: 30px; width: 70px;"
                                                        data-bs-toggle="modal" data-bs-target="#sendInvoiceModal"
                                                        data-invoice-id="{{ $invoice->id }}">
                                                        Resend
                                                    </button>
                                                    @endcan
                                                    @endif
                                                    
                                                

                                                @if(!$invoice->Payment_status == 1)
                                               
                                                    @if(Auth::user()->hasRole('Admin') || Auth::user()->hasRole('Responsable de projet') || Auth::user()->hasRole('Employee'))
                                                        @can('Send invoice')
                                                            <div class="dropdown m-2">
                                                                <button class="btn btn-secondary btn-xs dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                                                    Send Reminder
                                                                </button>
                                                                <ul class="dropdown-menu">
                                                                    @if(!$invoice->first_reminder_date)
                                                                        <li><a class="dropdown-item" href="{{ route((Auth::user()->hasRole('Admin') ? 'admin.invoice.sendManualremind' : (Auth::user()->hasRole('Responsable de projet') ? 'Responsable_de_projet.invoice.sendManualremind' : 'employee.invoice.sendManualremind')), ['id' => $invoice->id, 'reminder' => 1]) }}">Send Reminder 1</a></li>
                                                                    @endif
                                                                    @if($invoice->first_reminder_date && !$invoice->second_reminder_date)
                                                                        <li><a class="dropdown-item" href="{{ route((Auth::user()->hasRole('Admin') ? 'admin.invoice.sendManualremind' : (Auth::user()->hasRole('Responsable de projet') ? 'Responsable_de_projet.invoice.sendManualremind' : 'employee.invoice.sendManualremind')), ['id' => $invoice->id, 'reminder' => 2]) }}">Send Reminder 2</a></li>
                                                                    @endif
                                                                    @if($invoice->second_reminder_date && !$invoice->third_reminder_date)
                                                                        <li><a class="dropdown-item" href="{{ route((Auth::user()->hasRole('Admin') ? 'admin.invoice.sendManualremind' : (Auth::user()->hasRole('Responsable de projet') ? 'Responsable_de_projet.invoice.sendManualremind' : 'employee.invoice.sendManualremind')), ['id' => $invoice->id, 'reminder' => 3]) }}">Send Reminder 3</a></li>
                                                                    @endif
                                                                </ul>
                                                            </div>
                                                        @endcan
                                                    @endif
                                                    @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                                        @can('Send invoice')
                                                            <div class="dropdown m-2">
                                                                <button class="btn btn-secondary btn-xs dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                                                    Send Reminder
                                                                </button>
                                                                <ul class="dropdown-menu">
                                                                    @if(!$invoice->first_reminder_date)
                                                                        <li><a class="dropdown-item" href="{{ route('Responsable_gestionnaire.invoice.sendManualremind', ['id' => $invoice->id, 'reminder' => 1]) }}">Send Reminder 1</a></li>
                                                                    @endif
                                                                    @if($invoice->first_reminder_date && !$invoice->second_reminder_date)
                                                                        <li><a class="dropdown-item" href="{{ route('Responsable_gestionnaire.invoice.sendManualremind', ['id' => $invoice->id, 'reminder' => 2]) }}">Send Reminder 2</a></li>
                                                                    @endif
                                                                    @if($invoice->second_reminder_date && !$invoice->third_reminder_date)
                                                                        <li><a class="dropdown-item" href="{{ route('Responsable_gestionnaire.invoice.sendManualremind', ['id' => $invoice->id, 'reminder' => 3]) }}">Send Reminder 3</a></li>
                                                                    @endif
                                                                </ul>
                                                            </div>
                                                        @endcan
                                                    @endif
                                                @endif

                                                <div class="dropdown m-2">
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
                                                            <!--<li><a class="dropdown-item" href="#" onclick="showPartialDetailedPopup({{ $invoice->id }}, {{ number_format($invoice->total_ttc, 2, '.', '') }})">Partial</a></li> -->
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
                                                            <!--<li><a class="dropdown-item" href="#" onclick="showPartialPopup({{ $invoice->id }}, {{ number_format($invoice->total_ttc, 2, '.', '') }})">Partial</a></li> -->
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
                                                
                                                <!-- Partial Detailed Credit Note Modal -->
                                                <div class="modal fade" id="partialDetailedCreditNoteModal" tabindex="-1" aria-labelledby="partialDetailedCreditNoteModalLabel" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="partialDetailedCreditNoteModalLabel">Partial Credit Note for Detailed Invoice</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <form id="partialDetailedCreditNoteForm">
                                                                    <div class="mb-3">
                                                                        <label for="partialDetailedAmount" class="form-label">Enter Amount:</label>
                                                                        <input type="number" class="form-control" id="partialDetailedAmount" name="partialDetailedAmount" step="0.0001" required>
                                                                        <span id="detailed-error-message" style="color: red; display: none;">Amount exceeds total cost.</span>
                                                                    </div>
                                                                    <input type="hidden" id="detailedInvoiceId" name="detailedInvoiceId">
                                                                    <input type="hidden" id="detailedTotalCost" name="detailedTotalCost">
                                                                </form>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                                <button type="button" class="btn btn-primary" onclick="submitPartialDetailedCreditNote()">Submit</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                @if($invoice->id === $invoices->max('id') || $invoice->sentInvoice == 0)
                                                    @if(Auth::user()->hasRole('Admin'))
                                                        @if($invoice->type == 'Partial')
                                                            <a href="{{ route('admin.invoices.editPartialInvoice', $invoice->id) }}" class="btn btn-primary btn-xs m-2" style="height: 30px;">Edit</a>
                                                        @elseif($invoice->type == 'Percentage')
                                                            <a href="{{ route('admin.invoices.editPercentageInvoice', $invoice->id) }}" class="btn btn-primary btn-xs m-2" style="height: 30px;">Edit</a>
                                                        @elseif($invoice->type == 'Period')
                                                            <a href="{{ route('admin.invoices.editPeriodInvoice', $invoice->id) }}" class="btn btn-primary btn-xs m-2" style="height: 30px;">Edit</a>
                                                            @elseif($invoice->type == 'Detailed')
                                                            <a href="{{ route('admin.invoices.editDetailedInvoice', $invoice->id) }}" class="btn btn-primary btn-xs m-2" style="height: 30px;">Edit</a>
                                                        @else
                                                            <a href="{{ route('admin.invoices.editFullInvoice', $invoice->id) }}" class="btn btn-primary btn-xs m-2" style="height: 30px;">Edit</a>
                                                        @endif
                                                    @endif
                                                    @if(Auth::user()->hasRole('Responsable de projet'))
                                                        @if($invoice->type == 'Partial')
                                                            <a href="{{ route('Responsable_de_projet.invoices.editPartialInvoice', $invoice->id) }}" class="btn btn-primary btn-xs m-2" style="height: 30px;">Edit</a>
                                                        @elseif($invoice->type == 'Percentage')
                                                            <a href="{{ route('Responsable_de_projet.invoices.editPercentageInvoice', $invoice->id) }}" class="btn btn-primary btn-xs m-2" style="height: 30px;">Edit</a>
                                                        @elseif($invoice->type == 'Period')
                                                            <a href="{{ route('Responsable_de_projet.invoices.editPeriodInvoice', $invoice->id) }}" class="btn btn-primary btn-xs m-2" style="height: 30px;">Edit</a>
                                                        @elseif($invoice->type == 'Detailed')
                                                            <a href="{{ route('Responsable_de_projet.invoices.editDetailedInvoice', $invoice->id) }}" class="btn btn-primary btn-xs m-2" style="height: 30px;">Edit</a>
                                                        @else
                                                            <a href="{{ route('Responsable_de_projet.invoices.editFullInvoice', $invoice->id) }}" class="btn btn-primary btn-xs m-2" style="height: 30px;">Edit</a>
                                                        @endif
                                                    @endif
                                                    @if(Auth::user()->hasRole('Employee'))
                                                        @if($invoice->type == 'Partial')
                                                            <a href="{{ route('employee.invoices.editPartialInvoice', $invoice->id) }}" class="btn btn-primary btn-xs m-2" style="height: 30px;">Edit</a>
                                                        @elseif($invoice->type == 'Percentage')
                                                            <a href="{{ route('employee.invoices.editPercentageInvoice', $invoice->id) }}" class="btn btn-primary btn-xs m-2" style="height: 30px;">Edit</a>
                                                        @elseif($invoice->type == 'Period')
                                                            <a href="{{ route('employee.invoices.editPeriodInvoice', $invoice->id) }}" class="btn btn-primary btn-xs m-2" style="height: 30px;">Edit</a>
                                                        @elseif($invoice->type == 'Detailed')
                                                            <a href="{{ route('employee.invoices.editDetailedInvoice', $invoice->id) }}" class="btn btn-primary btn-xs m-2" style="height: 30px;">Edit</a>
                                                        @else
                                                            <a href="{{ route('employee.invoices.editFullInvoice', $invoice->id) }}" class="btn btn-primary btn-xs m-2" style="height: 30px;">Edit</a>
                                                        @endif
                                                    @endif
                                                
                                                    @if(Auth::user()->hasRole('Admin'))
                                                        @can('Delete invoice')
                                                        {{-- Only show delete button if this is the latest invoice --}}
                                                        @if($invoice->id === $invoices->max('id'))
                                                        <form action="{{route('admin.invoice.destroy',$invoice->id) }}"
                                                            method="post" onsubmit="return confirm('Are you sure?');"
                                                            class="d-inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-danger btn-xs m-2">X</button>
                                                        </form>
                                                        @endif
                                                        @endcan
                                                    @endif
                                                    @if(Auth::user()->hasRole('Responsable de projet'))
                                                        @can('Delete invoice')
                                                        {{-- Only show delete button if this is the latest invoice --}}
                                                        @if($invoice->id === $invoices->max('id'))
                                                        <form action="{{route('Responsable_de_projet.invoice.destroy',$invoice->id) }}"
                                                            method="post" onsubmit="return confirm('Are you sure?');"
                                                            class="d-inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-danger btn-xs m-2">X</button>
                                                        </form>
                                                        @endif
                                                        @endcan
                                                    @endif
                                                    @if(Auth::user()->hasRole('Employee'))
                                                        @can('Delete invoice')
                                                        {{-- Only show delete button if this is the latest invoice --}}
                                                        @if($invoice->id === $invoices->max('id'))
                                                        <form action="{{route('employee.invoice.destroy',$invoice->id) }}"
                                                            method="post" onsubmit="return confirm('Are you sure?');"
                                                            class="d-inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-danger btn-xs m-2">X</button>
                                                        </form>
                                                        @endif
                                                        @endcan
                                                    @endif

                                                @endif

                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach



                                </tbody>
                            </table>
                        </div>
                        <div id="creditNoteContent"></div>
                        <!-- Attachement Modal -->
                        <div class="modal fade" id="sendInvoiceModal" tabindex="-1" aria-labelledby="sendInvoiceModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="sendInvoiceModalLabel">Send Invoice</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <form id="sendInvoiceForm" method="post" enctype="multipart/form-data">
                                        @csrf
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <h6>Select Recipients:</h6>
                                                <div id="recipientsList" class="mb-3 p-2 border rounded">
                                                    <!-- Recipients will be populated here -->
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label for="fileUpload" class="form-label">Upload Additional Files (Optional):</label>
                                                <input type="file" class="form-control" id="fileUpload" name="file[]" multiple>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                            <button type="submit" class="btn btn-primary">Send Invoice</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Modal: Invoices Reports Filter -->
                        <div class="modal fade" id="invoiceReportModal" tabindex="-1" aria-labelledby="invoiceReportModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="invoiceReportModalLabel">Download Invoices Reports</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label">Choose payment statuses (optional):</label>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="invoiceReportStatus[]" id="status_paid" value="paid">
                                                <label class="form-check-label" for="status_paid">Paid</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="invoiceReportStatus[]" id="status_unpaid" value="unpaid">
                                                <label class="form-check-label" for="status_unpaid">Unpaid</label>
                                            </div>
                                            <hr>
                                            <label class="form-label">Choose invoice types (optional):</label>
        
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="invoiceReportTypes[]" id="type_partial" value="partial">
                                                <label class="form-check-label" for="type_partial">Partial</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="invoiceReportTypes[]" id="type_percentage" value="percentage">
                                                <label class="form-check-label" for="type_percentage">Percentage</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="invoiceReportTypes[]" id="type_full" value="full">
                                                <label class="form-check-label" for="type_full">Full</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="invoiceReportTypes[]" id="type_period" value="period">
                                                <label class="form-check-label" for="type_period">Period</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="invoiceReportTypes[]" id="type_detailed" value="detailed">
                                                <label class="form-check-label" for="type_detailed">Detailed</label>
                                            </div>
                                            <hr>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="credit_note_applied" value="1">
                                                <label class="form-check-label" for="credit_note_applied">Credit note applied</label>
                                            </div>
                                        </div>
                                        <small class="text-muted">Date range (optional) will be applied from the fields at the top.</small>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="button" class="btn btn-primary" id="confirmInvoiceReport">Download PDF</button>
                                    </div>
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
  var amount = parseFloat(document.getElementById('partialAmount').value);
  var invoiceId = document.getElementById('invoiceId').value;
  var totalCost = parseFloat(document.getElementById('totalCost').value);
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
        // Invoices report modal opener
        const invoiceReportModal = new bootstrap.Modal(document.getElementById('invoiceReportModal'));
        const openInvoiceReportModalBtn = document.getElementById('openInvoiceReportModal');
        if (openInvoiceReportModalBtn) {
            openInvoiceReportModalBtn.addEventListener('click', function(e) {
                e.preventDefault();
                invoiceReportModal.show();
            });
        }

        const confirmInvoiceReportBtn = document.getElementById('confirmInvoiceReport');
        if (confirmInvoiceReportBtn) {
            confirmInvoiceReportBtn.addEventListener('click', function() {
                let baseUrl = '';
                try {
                    @if(Auth::user()->hasRole('Responsable de projet'))
                        baseUrl = "{{route('Responsable_de_projet.Invoicessrepports.downloadRepports')}}";
                    @endif
                    @if(Auth::user()->hasRole('Admin'))
                        baseUrl = "{{route('admin.Invoicessrepports.downloadRepports')}}";
                    @endif
                    @if(Auth::user()->hasRole('Employee'))
                        baseUrl = "{{route('employee.Invoicessrepports.downloadRepports')}}";
                    @endif
                    @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                        baseUrl = "{{route('Responsable_gestionnaire.Invoicessrepports.downloadRepports')}}";
                    @endif
                } catch (e) {}

                const startDateEl = document.getElementById('startDate');
                const endDateEl = document.getElementById('endDate');
                const startDate = startDateEl ? startDateEl.value : '';
                const endDate = endDateEl ? endDateEl.value : '';
                const selectedStatuses = Array.from(document.querySelectorAll('input[name="invoiceReportStatus[]"]:checked')).map(x => x.value);
                const selectedTypes = Array.from(document.querySelectorAll('input[name="invoiceReportTypes[]"]:checked')).map(x => x.value);
                const creditNoteApplied = document.getElementById('credit_note_applied')?.checked;
 
                const params = new URLSearchParams();
                if (startDate && endDate) {
                    params.append('startDate', startDate);
                    params.append('endDate', endDate);
                }
                if (selectedStatuses.length > 0) {
                    params.append('filters', selectedStatuses.join(','));
                }
                if (selectedTypes.length > 0) {
                    params.append('types', selectedTypes.join(','));
                }
                if (creditNoteApplied) {
                    params.append('credit_note', '1');
                }
 
                const finalUrl = params.toString() ? `${baseUrl}?${params.toString()}` : baseUrl;
                window.location.href = finalUrl;
                invoiceReportModal.hide();
            });
        }

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
                const paymentButton = row.querySelector('.payment-button');
                const paymentStatus = paymentButton.textContent.trim();
                const invoiceTypeCell = row.querySelector('td:nth-child(5)').textContent.trim().toLowerCase();
                
                // Extract the base type (before the hyphen if it exists)
                const invoiceType = invoiceTypeCell.split('-')[0];

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
                // Date values are read by the modal logic
            }
        }
    </script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Function to show message
        function showMessage(message, type) {
            const messageContainer = document.getElementById('messageContainer');
            const messageText = document.getElementById('messageText');
            
            messageContainer.className = 'alert alert-' + type + ' alert-dismissible fade show mt-3';
            messageText.textContent = message;
            messageContainer.style.display = 'block';
            
            // Auto-hide after 5 seconds
            setTimeout(() => {
                messageContainer.style.display = 'none';
            }, 5000);
        }

        var sendInvoiceModal = document.getElementById('sendInvoiceModal');
        sendInvoiceModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var invoiceId = button.getAttribute('data-invoice-id');
            var form = document.getElementById('sendInvoiceForm');
            
            // Set the correct form action URL based on user role
            @if(Auth::user()->hasRole('Admin'))
            form.action = '{{ route("admin.invoice.sendInvoice", ":invoice_id") }}'.replace(':invoice_id', invoiceId);
            @elseif(Auth::user()->hasRole('Responsable de projet'))
                form.action = '{{ route("Responsable_de_projet.invoice.sendInvoice", ":invoice_id") }}'.replace(':invoice_id', invoiceId);
            @elseif(Auth::user()->hasRole('Employee'))
                form.action = '{{ route("employee.invoice.sendInvoice", ":invoice_id") }}'.replace(':invoice_id', invoiceId);
            @endif

            // Fetch recipient information
            fetch(`/admin/invoice/${invoiceId}/recipients`)
                .then(response => response.json())
                .then(data => {
                    const recipientsList = document.getElementById('recipientsList');
                    recipientsList.innerHTML = '';
                    
                    if (data.recipients && data.recipients.length > 0) {
                        data.recipients.forEach(recipient => {
                            const recipientDiv = document.createElement('div');
                            recipientDiv.className = 'mb-2';
                            
                            // Create checkbox
                            const checkbox = document.createElement('input');
                            checkbox.type = 'checkbox';
                            checkbox.name = 'selected_recipients[]';
                            checkbox.value = recipient.email;
                            checkbox.id = `recipient-${recipient.email}`;
                            checkbox.className = 'form-check-input me-2';
                            
                            // Check by default if recipient is comptable
                            if (recipient.is_comptable === 1) {
                                checkbox.checked = true;
                            }
                            
                            // Create label
                            const label = document.createElement('label');
                            label.className = 'form-check-label';
                            label.htmlFor = `recipient-${recipient.email}`;
                            label.innerHTML = `<strong>${recipient.name}</strong> (${recipient.email})`;
                            
                            // Append elements
                            recipientDiv.appendChild(checkbox);
                            recipientDiv.appendChild(label);
                            recipientsList.appendChild(recipientDiv);
                        });
                    } else {
                        recipientsList.innerHTML = '<p class="text-muted">No recipients found</p>';
                    }
                })
                .catch(error => {
                    console.error('Error fetching recipients:', error);
                    document.getElementById('recipientsList').innerHTML = '<p class="text-danger">Error loading recipients</p>';
                });
        });

        // Handle form submission
        document.getElementById('sendInvoiceForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Get selected recipients
            const selectedRecipients = Array.from(document.querySelectorAll('input[name="selected_recipients[]"]:checked'))
                .map(checkbox => checkbox.value);
            
            if (selectedRecipients.length === 0) {
                showMessage('Please select at least one recipient', 'danger');
                return;
            }

            // Create FormData object
            const formData = new FormData(this);
            formData.delete('selected_recipients[]'); // Remove the original checkboxes data
            formData.append('selected_recipients', JSON.stringify(selectedRecipients));

            // Submit the form
            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Close the modal
                    const modal = bootstrap.Modal.getInstance(sendInvoiceModal);
                    modal.hide();
                    
                    // Show success message
                    showMessage(data.message, 'success');
                    
                    // Reload the page after a short delay
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                } else {
                    showMessage('Error sending invoice: ' + (data.message || 'Unknown error'), 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage('An error occurred while sending the invoice. Please try again.', 'danger');
                });
        });
    });
</script> 

    <script>
    function showPartialDetailedPopup(invoiceId, totalCost) {
        document.getElementById('detailedInvoiceId').value = invoiceId;
        document.getElementById('detailedTotalCost').value = totalCost;
        
        // Reset error message and input
        document.getElementById('detailed-error-message').style.display = 'none';
        document.getElementById('partialDetailedAmount').value = '';
        
        // Show the modal
        var myModal = new bootstrap.Modal(document.getElementById('partialDetailedCreditNoteModal'));
        myModal.show();
    }

    function submitPartialDetailedCreditNote() {
        var invoiceId = document.getElementById('detailedInvoiceId').value;
        var totalCost = parseFloat(document.getElementById('detailedTotalCost').value);
        var amount = parseFloat(document.getElementById('partialDetailedAmount').value);
        
        if (isNaN(amount) || amount <= 0) {
            alert('Please enter a valid amount greater than 0');
            return;
        }
        
        if (amount > totalCost) {
            document.getElementById('detailed-error-message').style.display = 'block';
            return;
        }
        
        // Redirect to the appropriate route based on user role
        var url = '';
        @if(Auth::user()->hasRole('Admin'))
            url = '{{ route("admin.invoice.detailedCreditNote", ":id") }}?type=partial&amount=' + amount;
        @elseif(Auth::user()->hasRole('Responsable de projet'))
            url = '{{ route("Responsable_de_projet.invoice.detailedCreditNote", ":id") }}?type=partial&amount=' + amount;
        @elseif(Auth::user()->hasRole('Employee'))
            url = '{{ route("employee.invoice.detailedCreditNote", ":id") }}?type=partial&amount=' + amount;
        @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
            url = '{{ route("Responsable_gestionnaire.invoice.detailedCreditNote", ":id") }}?type=partial&amount=' + amount;
        @endif
        
        url = url.replace(':id', invoiceId);
        console.log('Redirecting to: ' + url); // Debug log
        window.location.href = url;
    }
    </script>




</body>

</html>