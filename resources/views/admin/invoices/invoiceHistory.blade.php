<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoices History - BizManager</title>
    @vite('resources/css/app.css') 
    @livewireStyles
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Keep Bootstrap JS for Modals & Dropdowns --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    {{-- Minimal custom styles if absolutely necessary --}}
<style>
      /* Style for overdue invoice links */
    .invoice-link-overdue {
        color: #dc2626; /* red-600 */
        font-weight: 600; /* semibold */
    }
    .invoice-link-overdue:hover {
        color: #991b1b; /* red-800 */
    }
    .invoice-link-normal {
        color: #2563eb; /* blue-600 */
    }
    .invoice-link-normal:hover {
        color: #1d4ed8; /* blue-800 */
    }
    /* Ensure dropdown menus appear correctly */
    .dropdown-menu {
        display: none; /* Hidden by default */
        position: absolute;
        z-index: 1000; /* Ensure it's above other content */
    }
    .dropdown-menu.show {
        display: block; /* Show when toggled */
    }
</style>
</head>
<body class="bg-gray-100 flex h-screen overflow-hidden font-sans">

    {{-- Sidebar Component --}}
    <x-dashboard.sidebar />

    {{-- Main Wrapper (Navbar + Content) --}}
    <div class="flex flex-col flex-1 overflow-y-auto">

        {{-- Navbar Component --}}
        <x-dashboard.navbar />

        {{-- Main Content Area --}}
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-8 space-y-8"> 

            {{-- 1. Header Section --}}
            <div class="flex flex-wrap justify-between items-center gap-4">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">Invoices History</h1>
                    <p class="text-sm text-gray-600">Manage and track all your invoices in one place</p>
                </div>
                <div class="flex items-center gap-3">
                    <button type="button" id="openInvoiceReportModal" class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg shadow-sm">
                        {{-- Download Icon --}}
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Invoices Reports PDF
                    </button>

                    @php $archiveRoute = ''; @endphp
                    @if(Auth::user()->hasRole('Admin')) @php $archiveRoute = route('admin.invoiceArchived.archiveIndex'); @endphp @endif
                    @if(Auth::user()->hasRole('Responsable de projet')) @php $archiveRoute = route('Responsable_de_projet.invoiceArchived.archiveIndex'); @endphp @endif
                    @if(Auth::user()->hasRole('Employee')) @php $archiveRoute = route('employee.invoiceArchived.archiveIndex'); @endphp @endif
                    @if(Auth::user()->hasRole('Responsable de projet et gestionnaire')) @php $archiveRoute = route('Responsable_gestionnaire.invoiceArchived.archiveIndex'); @endphp @endif
                    @if($archiveRoute)
                    <a href="{{ $archiveRoute }}" class="inline-flex items-center bg-white hover:bg-gray-50 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 shadow-sm">
                        {{-- View Archive Icon --}}
                         <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                          </svg>
                        View Archive
                    </a>
                    @endif
                </div>
            </div>

            {{-- 2. Date Pickers Section --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                 <div>
                    <label for="startDate" class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                    <input type="date" id="startDate" placeholder="jj/mm/aaaa" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3">
                 </div>
                 <div>
                    <label for="endDate" class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                    <input type="date" id="endDate" placeholder="jj/mm/aaaa" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3">
                 </div>
            </div>

             {{-- JS Message Container (Tailwind Alert) - Placed after date pickers --}}
             <div id="messageContainer" style="display: none;" class="px-4 py-3 rounded relative" role="alert">
                <strong class="font-bold" id="messageTitle"></strong>
                <span class="block sm:inline" id="messageText"></span>
                <button type="button" onclick="this.parentElement.style.display='none';" class="absolute top-0 bottom-0 right-0 px-4 py-3 font-bold text-xl" aria-label="Close">&times;</button>
            </div>
             {{-- Session Message (Tailwind Alert) - Placed after date pickers --}}
             @if(session('message'))
             <div id="sessionMessageContainer" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                 <strong class="font-bold">Success!</strong>
                 <span class="block sm:inline">{{ session('message') }}</span>
                 <button type="button" onclick="this.parentElement.style.display='none';" class="absolute top-0 bottom-0 right-0 px-4 py-3 font-bold text-xl" aria-label="Close">&times;</button>
             </div>
             @endif


            {{-- 3. Totals Cards Section --}}
             @php
                 // Your PHP logic for totals remains the same
                 $totalPaid = App\Models\invoices_history::where('entreprise_id', Auth::user()->entreprise_id)->where('Payment_status', 1)->sum('total_ttc');
                 $totalUnpaid = App\Models\invoices_history::where('entreprise_id', Auth::user()->entreprise_id)
                                     ->where(function($query) {
                                         $query->where('Payment_status', 0)->orWhereNull('Payment_status');
                                     })->sum('total_ttc');
                 $totalAmount = $totalPaid + $totalUnpaid; // Renamed from $total
                 $totalTva = App\Models\invoices_history::where('entreprise_id', Auth::user()->entreprise_id)->sum('total_tva'); // Renamed from $totaltva
             @endphp
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                {{-- Total Paid Card --}}
                <div class="bg-white rounded-lg shadow border border-gray-200 p-5 flex items-center space-x-4">
                    <div class="flex-shrink-0 bg-green-100 rounded-full p-3">
                        {{-- Check Icon --}}
                        <svg class="h-6 w-6 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase font-medium">TOTAL PAID</p>
                        <p class="text-xl font-semibold text-green-600">€{{ number_format($totalPaid, 2, ',', '.') }}</p> {{-- Adjusted formatting --}}
                    </div>
                </div>
                 {{-- Total Unpaid Card --}}
                <div class="bg-white rounded-lg shadow border border-gray-200 p-5 flex items-center space-x-4">
                    <div class="flex-shrink-0 bg-red-100 rounded-full p-3">
                        {{-- Warning Icon --}}
                        <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase font-medium">TOTAL UNPAID</p>
                        <p class="text-xl font-semibold text-red-600">€{{ number_format($totalUnpaid, 2, ',', '.') }}</p> {{-- Adjusted formatting --}}
                    </div>
                </div>
                 {{-- Total Amount Card --}}
                <div class="bg-white rounded-lg shadow border border-gray-200 p-5 flex items-center space-x-4">
                    <div class="flex-shrink-0 bg-blue-100 rounded-full p-3">
                         {{-- Money Bag Icon --}}
                        <svg class="h-6 w-6 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a2.25 2.25 0 00-2.25-2.25H15a3 3 0 11-6 0H5.25A2.25 2.25 0 003 12m18 0v6a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 18v-6m18 0V9M3 12V9m18 3a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 12m15 0a2.25 2.25 0 01-2.25 2.25H15a3 3 0 11-6 0H5.25A2.25 2.25 0 013 12m15 0V6a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 6v6" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase font-medium">TOTAL AMOUNT</p>
                        <p class="text-xl font-semibold text-blue-600">€{{ number_format($totalAmount, 2, ',', '.') }}</p> {{-- Adjusted formatting --}}
                    </div>
                </div>
                 {{-- Total TVA Card --}}
                <div class="bg-white rounded-lg shadow border border-gray-200 p-5 flex items-center space-x-4">
                    <div class="flex-shrink-0 bg-indigo-100 rounded-full p-3">
                        {{-- Calculator Icon --}}
                        <svg class="h-6 w-6 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 15.75V18m-7.5-6.75h.008v.008H8.25v-.008zm0 2.25h.008v.008H8.25V13.5zm0 2.25h.008v.008H8.25v-.008zm0 2.25h.008v.008H8.25V18zm2.498-6.75h.007v.008h-.007v-.008zm0 2.25h.007v.008h-.007V13.5zm0 2.25h.007v.008h-.007v-.008zm0 2.25h.007v.008h-.007V18zm2.504-6.75h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008V13.5zm0 2.25h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008V18zm2.498-6.75h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008V13.5zM8.25 6h7.5v2.25h-7.5V6zM12 2.25c-1.892 0-3.758.11-5.593.322C5.307 2.7 4.5 3.65 4.5 4.757V19.5a2.25 2.25 0 002.25 2.25h10.5a2.25 2.25 0 002.25-2.25V4.757c0-1.108-.806-2.057-1.907-2.185A48.208 48.208 0 0012 2.25z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase font-medium">TOTAL TVA</p>
                        <p class="text-xl font-semibold text-gray-800">€{{ number_format($totalTva, 2, ',', '.') }}</p> {{-- Adjusted formatting --}}
                    </div>
                </div>
            </div>

            {{-- 4. Filters Section --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                 <h6 class="text-base font-semibold text-gray-800 mb-4">Filters</h6>
                 <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                     <div>
                        {{-- Adjusted label style --}}
                        <label for="clientFilter" class="block text-sm font-medium text-gray-700 mb-1">Filter by Client</label>
                        <select id="clientFilter" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3">
                            <option value="">All Clients</option>
                            @foreach($clients as $client)
                            <option value="{{$client->name}}">{{$client->name}}</option>
                            @endforeach
                        </select>
                     </div>
                     <div>
                        <label for="projectFilter" class="block text-sm font-medium text-gray-700 mb-1">Filter by Project</label>
                        <select id="projectFilter" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3">
                            <option value="">All Projects</option>
                            @foreach($projects as $project)
                            <option value="{{$project->name}}">{{strtoupper($project->name)}}</option>
                            @endforeach
                        </select>
                     </div>
                     <div>
                        <label for="paymentFilter" class="block text-sm font-medium text-gray-700 mb-1">Filter by Payment</label>
                        <select id="paymentFilter" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3">
                            <option value="">All Invoices</option>
                            <option value="Paid">Paid</option>
                            <option value="Unpaid">Unpaid</option>
                        </select>
                     </div>
                     <div>
                        <label for="typeFilter" class="block text-sm font-medium text-gray-700 mb-1">Filter by Type</label>
                        <select id="typeFilter" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3">
                            <option value="">All Invoices</option>
                            <option value="partial">Partial</option>
                            <option value="percentage">Percentage</option>
                            <option value="period">Period</option>
                            <option value="detailed">Detailed</option>
                            <option value="full">Full</option>
                        </select>
                     </div>
                 </div>
            </div>

            {{-- 5. Table Section --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-full divide-y divide-gray-200" id="invoicesTable">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Invoice N°</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Project</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">TVA</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Client</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Type</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Created At</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Amount</th>
                                <th scope="col" class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                           @forelse($invoices as $invoice)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 whitespace-nowrap text-sm font-medium">
                                    {{-- PHP logic for overdue calculation --}}
                                    @php
                                        $dueDate = \Carbon\Carbon::parse($invoice->due_date);
                                        $today = \Carbon\Carbon::now();
                                        $isOverdue = $dueDate->isPast() && !$invoice->Payment_status;
                                        $daysOverdue = $isOverdue ? $today->diffInDays($dueDate) : 0;
                                        $pdfRoute = ''; // Determine route based on role
                                        if(Auth::user()->hasRole('Responsable de projet')) $pdfRoute = route('Responsable_de_projet.invoices.servePdf', ['invoiceName' => $invoice->id]);
                                        elseif(Auth::user()->hasRole('Admin')) $pdfRoute = route('admin.invoices.servePdf', ['invoiceName' => $invoice->id]);
                                        elseif(Auth::user()->hasRole('Employee')) $pdfRoute = route('employee.invoices.servePdf', ['invoiceName' => $invoice->id]);
                                        elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire')) $pdfRoute = route('Responsable_gestionnaire.invoices.servePdf', ['invoiceName' => $invoice->id]);
                                    @endphp
                                    {{-- Link styling based on overdue status --}}
                                    <a href="{{ $pdfRoute }}" target="_blank" class="{{ $isOverdue ? 'invoice-link-overdue' : 'invoice-link-normal' }}">
                                        {{$entreprise_infos->invoice_prefix}}-{{date('ym', strtotime($invoice->created_at))}}{{$invoice->number}}
                                        @if($isOverdue)
                                            <span class="block text-xs">({{ $daysOverdue }} days overdue)</span>
                                        @endif
                                    </a>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-800">{{strtoupper($invoice->project_name)}}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">
                                    @php $project = App\Models\Project::find($invoice->project_id); @endphp
                                    {{$project->tva ?? 'N/A'}}%
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-800">
                                     @php $client = \App\Models\Client::where('id', $invoice->client_id)->first(); @endphp
                                     {{$client->name ?? 'N/A'}}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600 capitalize">
                                    @if($invoice->type == 'Partial') {{$invoice->type}}-{{$invoice->partial}}
                                    @elseif($invoice->type == 'Percentage') {{$invoice->type}}-{{$invoice->percentage}}
                                    @else {{$invoice->type}}
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">{{ \Carbon\Carbon::parse($invoice->created_at)->format('Y-m-d H:i') }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm font-semibold text-green-700">€{{ number_format($invoice->total_ttc, 2, ',', ' ') }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-center">
    <div class="flex items-center justify-center gap-1.5">
        
        {{-- Payment Status Toggle --}}
        @php
            $isPaid = \App\Models\invoices_history::where('id', $invoice->id)->value('Payment_status') == 1;
            $toggleRoute = '';
            
            if(Auth::user()->hasRole('Admin'))
                $toggleRoute = $isPaid ? route('admin.invoice.deactivate', $invoice->id) : route('admin.invoice.activate', $invoice->id);
            elseif(Auth::user()->hasRole('Responsable de projet'))
                $toggleRoute = $isPaid ? route('Responsable_de_projet.invoice.deactivate', $invoice->id) : route('Responsable_de_projet.invoice.activate', $invoice->id);
            elseif(Auth::user()->hasRole('Employee'))
                $toggleRoute = $isPaid ? route('employee.invoice.deactivate', $invoice->id) : route('employee.invoice.activate', $invoice->id);
            elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                $toggleRoute = $isPaid ? route('Responsable_gestionnaire.invoice.deactivate', $invoice->id) : route('Responsable_gestionnaire.invoice.activate', $invoice->id);
        @endphp
        
        @if($toggleRoute)
            <form action="{{ $toggleRoute }}" method="post" onsubmit="return confirm('Change payment status?');" class="inline-block">
                @csrf
                <button type="submit"
                        title="{{ $isPaid ? 'Mark as Unpaid' : 'Mark as Paid' }}"
                        class="payment-button p-1 rounded-md {{ $isPaid ? 'bg-green-600 hover:bg-green-700 text-white' : 'bg-red-600 hover:bg-red-700 text-white' }}">
                    @if($isPaid)
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                    @else
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    @endif
                </button>
            </form>
        @endif

        {{-- Send Button --}}
        @can('Send invoice')
        <button type="button" 
                title="{{ $invoice->sentInvoice == 0 ? 'Send Invoice' : 'Resend Invoice' }}"
                class="p-1 rounded-md bg-gray-600 text-white hover:bg-gray-700"
                data-bs-toggle="modal" 
                data-bs-target="#sendInvoiceModal" 
                data-invoice-id="{{ $invoice->id }}">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
            </svg>
        </button>
        @endcan

        {{-- Reminder Button --}}
        @if(!$isPaid)
            @can('Send invoice')
                @php 
                    $reminderRouteBase = '';
                    if(Auth::user()->hasRole('Admin')) 
                        $reminderRouteBase = 'admin.invoice.sendManualremind';
                    elseif(Auth::user()->hasRole('Responsable de projet'))
                        $reminderRouteBase = 'Responsable_de_projet.invoice.sendManualremind';
                    elseif(Auth::user()->hasRole('Employee'))
                        $reminderRouteBase = 'employee.invoice.sendManualremind';
                    elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                        $reminderRouteBase = 'Responsable_gestionnaire.invoice.sendManualremind';
                @endphp
                
                @if($reminderRouteBase && (!$invoice->first_reminder_date || !$invoice->second_reminder_date || !$invoice->third_reminder_date))
                    <div class="relative inline-block text-left dropdown">
                        <button type="button" 
                                title="Send Reminder" 
                                class="p-1 rounded-md bg-yellow-500 text-white hover:bg-yellow-600 dropdown-toggle" 
                                data-bs-toggle="dropdown" 
                                aria-expanded="false">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                        </button>
                        <ul class="dropdown-menu absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none z-10">
                            @if(!$invoice->first_reminder_date)
                                <li><a class="dropdown-item block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" href="{{ route($reminderRouteBase, ['id' => $invoice->id, 'reminder' => 1]) }}">Reminder 1</a></li>
                            @endif
                            @if($invoice->first_reminder_date && !$invoice->second_reminder_date)
                                <li><a class="dropdown-item block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" href="{{ route($reminderRouteBase, ['id' => $invoice->id, 'reminder' => 2]) }}">Reminder 2</a></li>
                            @endif
                            @if($invoice->second_reminder_date && !$invoice->third_reminder_date)
                                <li><a class="dropdown-item block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" href="{{ route($reminderRouteBase, ['id' => $invoice->id, 'reminder' => 3]) }}">Reminder 3</a></li>
                            @endif
                        </ul>
                    </div>
                @endif
            @endcan
        @endif

        {{-- Credit Note Button --}}
        <div class="relative inline-block text-left dropdown">
            <button type="button" 
                    title="Create Credit Note" 
                    class="p-1 rounded-md bg-purple-600 text-white hover:bg-purple-700 dropdown-toggle" 
                    data-bs-toggle="dropdown" 
                    aria-expanded="false">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </button>
            <ul class="dropdown-menu absolute right-0 mt-2 w-40 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none z-10">
                @php 
                    $creditNoteRoute = '';
                    $detailedCreditNoteRoute = '';
                    
                    if(Auth::user()->hasRole('Admin')) {
                        $creditNoteRoute = 'admin.invoice.creditNote';
                        $detailedCreditNoteRoute = 'admin.invoice.detailedCreditNote';
                    } elseif(Auth::user()->hasRole('Responsable de projet')) {
                        $creditNoteRoute = 'Responsable_de_projet.invoice.creditNote';
                        $detailedCreditNoteRoute = 'Responsable_de_projet.invoice.detailedCreditNote';
                    } elseif(Auth::user()->hasRole('Employee')) {
                        $creditNoteRoute = 'employee.invoice.creditNote';
                        $detailedCreditNoteRoute = 'employee.invoice.detailedCreditNote';
                    } elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire')) {
                        $creditNoteRoute = 'Responsable_gestionnaire.invoice.creditNote';
                        $detailedCreditNoteRoute = 'Responsable_gestionnaire.invoice.detailedCreditNote';
                    }
                @endphp
                
                @if($invoice->type == 'Detailed')
                    @if($detailedCreditNoteRoute)
                        <li><a class="dropdown-item block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" href="{{ route($detailedCreditNoteRoute, $invoice->id) }}?type=full">Full</a></li>
                    @endif
                @else
                    @if($creditNoteRoute)
                        <li><a class="dropdown-item block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" href="{{ route($creditNoteRoute, $invoice->id) }}?type=full">Full</a></li>
                    @endif
                @endif
            </ul>
        </div>

        {{-- Edit Button --}}
        @if($invoice->id === $invoices->max('id') || $invoice->sentInvoice == 0)
            @php 
                $editRoute = '';
                
                if(Auth::user()->hasRole('Admin')) {
                    if($invoice->type == 'Partial')
                        $editRoute = route('admin.invoices.editPartialInvoice', $invoice->id);
                    elseif($invoice->type == 'Percentage')
                        $editRoute = route('admin.invoices.editPercentageInvoice', $invoice->id);
                    elseif($invoice->type == 'Period')
                        $editRoute = route('admin.invoices.editPeriodInvoice', $invoice->id);
                    elseif($invoice->type == 'Detailed')
                        $editRoute = route('admin.invoices.editDetailedInvoice', $invoice->id);
                    else
                        $editRoute = route('admin.invoices.editFullInvoice', $invoice->id);
                } elseif(Auth::user()->hasRole('Responsable de projet')) {
                    if($invoice->type == 'Partial')
                        $editRoute = route('Responsable_de_projet.invoices.editPartialInvoice', $invoice->id);
                    elseif($invoice->type == 'Percentage')
                        $editRoute = route('Responsable_de_projet.invoices.editPercentageInvoice', $invoice->id);
                    elseif($invoice->type == 'Period')
                        $editRoute = route('Responsable_de_projet.invoices.editPeriodInvoice', $invoice->id);
                    elseif($invoice->type == 'Detailed')
                        $editRoute = route('Responsable_de_projet.invoices.editDetailedInvoice', $invoice->id);
                    else
                        $editRoute = route('Responsable_de_projet.invoices.editFullInvoice', $invoice->id);
                } elseif(Auth::user()->hasRole('Employee')) {
                    if($invoice->type == 'Partial')
                        $editRoute = route('employee.invoices.editPartialInvoice', $invoice->id);
                    elseif($invoice->type == 'Percentage')
                        $editRoute = route('employee.invoices.editPercentageInvoice', $invoice->id);
                    elseif($invoice->type == 'Period')
                        $editRoute = route('employee.invoices.editPeriodInvoice', $invoice->id);
                    elseif($invoice->type == 'Detailed')
                        $editRoute = route('employee.invoices.editDetailedInvoice', $invoice->id);
                    else
                        $editRoute = route('employee.invoices.editFullInvoice', $invoice->id);
                } elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire')) {
                    if($invoice->type == 'Partial')
                        $editRoute = route('Responsable_gestionnaire.invoices.editPartialInvoice', $invoice->id);
                    elseif($invoice->type == 'Percentage')
                        $editRoute = route('Responsable_gestionnaire.invoices.editPercentageInvoice', $invoice->id);
                    elseif($invoice->type == 'Period')
                        $editRoute = route('Responsable_gestionnaire.invoices.editPeriodInvoice', $invoice->id);
                    elseif($invoice->type == 'Detailed')
                        $editRoute = route('Responsable_gestionnaire.invoices.editDetailedInvoice', $invoice->id);
                    else
                        $editRoute = route('Responsable_gestionnaire.invoices.editFullInvoice', $invoice->id);
                }
            @endphp
            
            @if($editRoute)
            <a href="{{ $editRoute }}" 
               title="Edit Invoice" 
               class="p-1 rounded-md bg-blue-600 text-white hover:bg-blue-700">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                </svg>
            </a>
            @endif
        @endif

        {{-- Delete Button --}}
        @if($invoice->id === $invoices->max('id'))
            @can('Delete invoice')
                @php 
                    $deleteRoute = '';
                    
                    if(Auth::user()->hasRole('Admin'))
                        $deleteRoute = route('admin.invoice.destroy', $invoice->id);
                    elseif(Auth::user()->hasRole('Responsable de projet'))
                        $deleteRoute = route('Responsable_de_projet.invoice.destroy', $invoice->id);
                    elseif(Auth::user()->hasRole('Employee'))
                        $deleteRoute = route('employee.invoice.destroy', $invoice->id);
                    elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                        $deleteRoute = route('Responsable_gestionnaire.invoice.destroy', $invoice->id);
                @endphp
                
                @if($deleteRoute)
                <form action="{{ $deleteRoute }}" 
                      method="post" 
                      onsubmit="return confirm('Delete this invoice? This cannot be undone.');" 
                      class="inline-block">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            title="Delete Invoice" 
                            class="p-1 rounded-md bg-red-600 text-white hover:bg-red-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                </form>
                @endif
            @endcan
        @endif
        
    </div>
</td>
                            </tr>
                           @empty
                            <tr>
                                <td colspan="8" class="text-center px-4 py-6 text-gray-500">
                                    No invoices found matching your criteria.
                                </td>
                            </tr>
                           @endforelse
                        </tbody>
                    </table>
                </div>
            </div> 

            {{-- Modals (Keep Bootstrap structure, ideally style inside partials with Tailwind)
            @include('admin.invoices.partials.send_invoice_modal')
            @include('admin.invoices.partials.invoice_report_modal')
            @include('admin.invoices.partials.partial_credit_note_modal')
            @include('admin.invoices.partials.partial_detailed_credit_note_modal')--}}

            {{-- Send Invoice Modal --}}
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
                        <h6 class="text-sm font-medium text-gray-700 mb-2">Select Recipients:</h6>
                        <div id="recipientsList" class="space-y-2 p-3 border border-gray-200 rounded-lg bg-gray-50">
                            <!-- Recipients will be populated here -->
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="fileUpload" class="block text-sm font-medium text-gray-700 mb-2">Upload Additional Files (Optional):</label>
                        <input type="file" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" id="fileUpload" name="file[]" multiple>
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

{{-- Partial Credit Note Modal --}}
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
                        <label for="partialAmount" class="block text-sm font-medium text-gray-700 mb-2">Enter Amount:</label>
                        <input type="number" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" id="partialAmount" name="partialAmount" step="0.0001" required>
                        <span id="error-message" class="text-red-600 text-sm mt-1 hidden">Amount exceeds total cost.</span>
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

{{-- Partial Detailed Credit Note Modal --}}
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
                        <label for="partialDetailedAmount" class="block text-sm font-medium text-gray-700 mb-2">Enter Amount:</label>
                        <input type="number" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" id="partialDetailedAmount" name="partialDetailedAmount" step="0.0001" required>
                        <span id="detailed-error-message" class="text-red-600 text-sm mt-1 hidden">Amount exceeds total cost.</span>
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

{{-- Invoice Report Modal --}}
<div class="modal fade" id="invoiceReportModal" tabindex="-1" aria-labelledby="invoiceReportModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="invoiceReportModalLabel">Download Invoices Reports</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Choose payment statuses (optional):</label>
                    <div class="space-y-2">
                        <div class="flex items-center">
                            <input class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" type="checkbox" name="invoiceReportStatus[]" id="status_paid" value="paid">
                            <label class="ml-2 text-sm text-gray-700" for="status_paid">Paid</label>
                        </div>
                        <div class="flex items-center">
                            <input class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" type="checkbox" name="invoiceReportStatus[]" id="status_unpaid" value="unpaid">
                            <label class="ml-2 text-sm text-gray-700" for="status_unpaid">Unpaid</label>
                        </div>
                    </div>
                    
                    <hr class="my-4">
                    
                    <label class="block text-sm font-medium text-gray-700 mb-2">Choose invoice types (optional):</label>
                    <div class="space-y-2">
                        <div class="flex items-center">
                            <input class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" type="checkbox" name="invoiceReportTypes[]" id="type_partial" value="partial">
                            <label class="ml-2 text-sm text-gray-700" for="type_partial">Partial</label>
                        </div>
                        <div class="flex items-center">
                            <input class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" type="checkbox" name="invoiceReportTypes[]" id="type_percentage" value="percentage">
                            <label class="ml-2 text-sm text-gray-700" for="type_percentage">Percentage</label>
                        </div>
                        <div class="flex items-center">
                            <input class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" type="checkbox" name="invoiceReportTypes[]" id="type_full" value="full">
                            <label class="ml-2 text-sm text-gray-700" for="type_full">Full</label>
                        </div>
                        <div class="flex items-center">
                            <input class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" type="checkbox" name="invoiceReportTypes[]" id="type_period" value="period">
                            <label class="ml-2 text-sm text-gray-700" for="type_period">Period</label>
                        </div>
                        <div class="flex items-center">
                            <input class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" type="checkbox" name="invoiceReportTypes[]" id="type_detailed" value="detailed">
                            <label class="ml-2 text-sm text-gray-700" for="type_detailed">Detailed</label>
                        </div>
                    </div>
                    
                    <hr class="my-4">
                    
                    <div class="flex items-center">
                        <input class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" type="checkbox" id="credit_note_applied" value="1">
                        <label class="ml-2 text-sm text-gray-700" for="credit_note_applied">Credit note applied</label>
                    </div>
                </div>
                <small class="text-gray-500 text-xs">Date range (optional) will be applied from the fields at the top.</small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmInvoiceReport">Download PDF</button>
            </div>
        </div>
    </div>
</div>
        </main>
    </div> 

    {{-- SCRIPTS --}}
    @livewireScripts
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    {{-- IMPORTANT: Keep Bootstrap JS for Modals & Dropdowns --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="//unpkg.com/alpinejs" defer></script>

<script>
        // All your previous JS functions (showPartialPopup, submitPartialCreditNote, showPartialDetailedPopup, submitPartialDetailedCreditNote, datePicker, showMessage, filtering logic, modal logic) should go here.
        // Ensure the showMessage function uses the Tailwind classes as updated in the previous response.

        // Example: Updated showMessage
        function showMessage(message, type) {
            const messageContainer = document.getElementById('messageContainer');
            const messageText = document.getElementById('messageText');
            const messageTitle = document.getElementById('messageTitle');

            let alertClasses = ''; let titleText = '';
            if (type === 'success') {
                alertClasses = 'bg-green-100 border border-green-400 text-green-700'; titleText = 'Success!';
            } else if (type === 'danger') {
                 alertClasses = 'bg-red-100 border border-red-400 text-red-700'; titleText = 'Error!';
            } else {
                 alertClasses = 'bg-blue-100 border border-blue-400 text-blue-700'; titleText = 'Info';
            }

            messageContainer.className = `mb-6 px-4 py-3 rounded relative ${alertClasses}`;
            messageTitle.textContent = titleText;
            messageText.textContent = message;
            messageContainer.style.display = 'block';

            setTimeout(() => { if (messageContainer) { messageContainer.style.display = 'none'; } }, 5000);
        }

        // --- Keep ALL other original script blocks here ---
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

          var url = '';
          @if(Auth::user()->hasRole('Responsable de projet'))
             url = `{{ route('Responsable_de_projet.invoice.creditNote', '') }}/${invoiceId}?type=partial&amount=${amount}`;
           @elseif(Auth::user()->hasRole('Admin'))
             url = `{{ route('admin.invoice.creditNote', '') }}/${invoiceId}?type=partial&amount=${amount}`;
           @elseif(Auth::user()->hasRole('Employee'))
             url = `{{ route('employee.invoice.creditNote', '') }}/${invoiceId}?type=partial&amount=${amount}`;
           @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
             url = `{{ route('Responsable_gestionnaire.invoice.creditNote', '') }}/${invoiceId}?type=partial&amount=${amount}`;
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
             // Instead of replacing the whole document, maybe update the table or show a success message?
             // For now, keeping the original behavior:
            document.open();
            document.write(html);
            document.close();
             // Consider redirecting or using showMessage instead:
             // showMessage('Credit note generated successfully.', 'success');
             // setTimeout(() => window.location.reload(), 1500);
          })
          .catch(error => {
            console.error('Error:', error);
            showMessage('An error occurred: ' + error.message, 'danger');
          });
        }

        // Function to show the partial detailed credit note modal
        function showPartialDetailedPopup(invoiceId, totalCost) {
            document.getElementById('detailedInvoiceId').value = invoiceId;
            document.getElementById('detailedTotalCost').value = totalCost;
            document.getElementById('detailed-error-message').style.display = 'none';
            document.getElementById('partialDetailedAmount').value = '';
            var myModal = new bootstrap.Modal(document.getElementById('partialDetailedCreditNoteModal'));
            myModal.show();
        }

        // Function to submit the partial detailed credit note
        function submitPartialDetailedCreditNote() {
            var invoiceId = document.getElementById('detailedInvoiceId').value;
            var totalCost = parseFloat(document.getElementById('detailedTotalCost').value);
            var amount = parseFloat(document.getElementById('partialDetailedAmount').value);

            if (isNaN(amount) || amount <= 0) {
                 showMessage('Please enter a valid amount greater than 0', 'danger');
                 return;
             }

            if (amount > totalCost) {
                document.getElementById('detailed-error-message').style.display = 'block';
                return;
            } else {
                 document.getElementById('detailed-error-message').style.display = 'none';
            }

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
            window.location.href = url; // Redirects to the credit note generation page/PDF
        }


        // JavaScript for filtering the table
        document.addEventListener("DOMContentLoaded", function() {
            // Invoices report modal opener
            const invoiceReportModalEl = document.getElementById('invoiceReportModal');
            let invoiceReportModal; // Define variable outside
            if (invoiceReportModalEl) {
                 invoiceReportModal = new bootstrap.Modal(invoiceReportModalEl); // Initialize if element exists
            }

            const openInvoiceReportModalBtn = document.getElementById('openInvoiceReportModal');
            if (openInvoiceReportModalBtn && invoiceReportModal) { // Check if modal exists
                openInvoiceReportModalBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    invoiceReportModal.show();
                });
            }

            const confirmInvoiceReportBtn = document.getElementById('confirmInvoiceReport');
            if (confirmInvoiceReportBtn && invoiceReportModal) { // Check if modal exists
                confirmInvoiceReportBtn.addEventListener('click', function() {
                    let baseUrl = '';
                    // Role-based routing (keep as is)
                     @if(Auth::user()->hasRole('Responsable de projet')) baseUrl = "{{route('Responsable_de_projet.Invoicessrepports.downloadRepports')}}"; @endif
                     @if(Auth::user()->hasRole('Admin')) baseUrl = "{{route('admin.Invoicessrepports.downloadRepports')}}"; @endif
                     // ... other roles ...

                    const startDateEl = document.getElementById('startDate');
                    const endDateEl = document.getElementById('endDate');
                    const startDate = startDateEl ? startDateEl.value : '';
                    const endDate = endDateEl ? endDateEl.value : '';
                    const selectedStatuses = Array.from(document.querySelectorAll('input[name="invoiceReportStatus[]"]:checked')).map(x => x.value);
                    const selectedTypes = Array.from(document.querySelectorAll('input[name="invoiceReportTypes[]"]:checked')).map(x => x.value);
                    const creditNoteApplied = document.getElementById('credit_note_applied')?.checked;

                    const params = new URLSearchParams();
                    if (startDate && endDate) { params.append('startDate', startDate); params.append('endDate', endDate); }
                    if (selectedStatuses.length > 0) { params.append('filters', selectedStatuses.join(',')); }
                    if (selectedTypes.length > 0) { params.append('types', selectedTypes.join(',')); }
                    if (creditNoteApplied) { params.append('credit_note', '1'); }

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

            // Add event listeners
            if (clientFilter) clientFilter.addEventListener('change', filterTable);
            if (projectFilter) projectFilter.addEventListener('change', filterTable);
            if (paymentFilter) paymentFilter.addEventListener('change', filterTable);
            if (typeFilter) typeFilter.addEventListener('change', filterTable);

            function filterTable() {
                const clientValue = clientFilter ? clientFilter.value.toLowerCase() : '';
                const projectValue = projectFilter ? projectFilter.value.toLowerCase() : '';
                const paymentValue = paymentFilter ? paymentFilter.value : ''; // Paid/Unpaid/''
                const typeValue = typeFilter ? typeFilter.value.toLowerCase() : '';

                const rows = document.querySelectorAll('#invoicesTable tbody tr');
                let hasVisibleRows = false;
                rows.forEach(row => {
                    // Handle potential empty table row message
                     if (row.children.length === 1 && row.children[0].getAttribute('colspan') === '8') {
                         row.style.display = 'none'; // Hide the "No invoices" message initially
                         return;
                     }

                    const clientName = row.querySelector('td:nth-child(4)')?.textContent.trim().toLowerCase() || '';
                    const projectName = row.querySelector('td:nth-child(2)')?.textContent.trim().toLowerCase() || '';
                    const paymentButton = row.querySelector('.payment-button'); // Find the button
                    // Determine status based on button text/class - needs careful check of button structure
                     let paymentStatus = '';
                     if (paymentButton) {
                         // Example: Check text content or a class
                          paymentStatus = paymentButton.textContent.trim(); // Assumes text is 'Paid' or 'Unpaid'
                         // Or if you use classes: paymentStatus = paymentButton.classList.contains('bg-green-600') ? 'Paid' : 'Unpaid';
                     }

                    const invoiceTypeCell = row.querySelector('td:nth-child(5)')?.textContent.trim().toLowerCase() || '';
                    const invoiceType = invoiceTypeCell.split('-')[0]; // Get base type

                    // Apply filters
                    const clientMatch = clientValue === '' || clientName.includes(clientValue);
                    const projectMatch = projectValue === '' || projectName.includes(projectValue);
                    const paymentMatch = paymentValue === '' || paymentStatus === paymentValue;
                    const typeMatch = typeValue === '' || invoiceType === typeValue;

                    if (clientMatch && projectMatch && paymentMatch && typeMatch) {
                        row.style.display = ''; // Show row
                        hasVisibleRows = true;
                    } else {
                        row.style.display = 'none'; // Hide row
                    }
                });

                // Show "No invoices" message if no rows are visible
                 const noInvoicesRow = document.querySelector('#invoicesTable tbody tr td[colspan="8"]');
                 if (noInvoicesRow) {
                     noInvoicesRow.parentElement.style.display = hasVisibleRows ? 'none' : '';
                 }
            }
             // Initial filter on load
            filterTable();
        });


         // Send Invoice Modal script
         document.addEventListener('DOMContentLoaded', function () {
             var sendInvoiceModalEl = document.getElementById('sendInvoiceModal');
             if (!sendInvoiceModalEl) return; // Exit if modal doesn't exist

             sendInvoiceModalEl.addEventListener('show.bs.modal', function (event) {
                 var button = event.relatedTarget;
                 var invoiceId = button.getAttribute('data-invoice-id');
                 var form = document.getElementById('sendInvoiceForm');

                 // Set form action based on role (keep as is)
                 @if(Auth::user()->hasRole('Admin')) form.action = '{{ route("admin.invoice.sendInvoice", ":invoice_id") }}'.replace(':invoice_id', invoiceId); @endif
                 // ... other roles ...

                 // Fetch recipients (keep as is, check URL /admin/... if needed)
                 fetch(`/admin/invoice/${invoiceId}/recipients`) // Check this URL if roles change path
                     .then(response => response.json())
                     .then(data => {
                         const recipientsList = document.getElementById('recipientsList');
                         recipientsList.innerHTML = '';
                         if (data.recipients && data.recipients.length > 0) {
                             data.recipients.forEach(recipient => {
                                 // Create checkbox and label (keep as is)
                                 const recipientDiv = document.createElement('div');
                                 recipientDiv.className = 'mb-2 flex items-center'; // Use flex for alignment

                                 const checkbox = document.createElement('input');
                                 checkbox.type = 'checkbox';
                                 checkbox.name = 'selected_recipients[]';
                                 checkbox.value = recipient.email;
                                 checkbox.id = `recipient-${recipient.email}`;
                                 checkbox.className = 'form-check-input h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 mr-2'; // Tailwind classes
                                 checkbox.checked = recipient.is_comptable === 1; // Default check

                                 const label = document.createElement('label');
                                 label.className = 'form-check-label text-sm text-gray-700'; // Tailwind classes
                                 label.htmlFor = `recipient-${recipient.email}`;
                                 label.innerHTML = `<span class="font-medium">${recipient.name}</span> (${recipient.email})`; // Adjusted style

                                 recipientDiv.appendChild(checkbox);
                                 recipientDiv.appendChild(label);
                                 recipientsList.appendChild(recipientDiv);
                             });
                         } else {
                             recipientsList.innerHTML = '<p class="text-sm text-gray-500">No recipients found</p>'; // Tailwind classes
                         }
                     })
                     .catch(error => { /* ... error handling ... */ });
             });

             // Handle form submission (keep as is)
             const sendInvoiceForm = document.getElementById('sendInvoiceForm');
             if (sendInvoiceForm) {
                 sendInvoiceForm.addEventListener('submit', function(e) {
                     e.preventDefault();
                     const selectedRecipients = Array.from(document.querySelectorAll('input[name="selected_recipients[]"]:checked')).map(checkbox => checkbox.value);
                     if (selectedRecipients.length === 0) { showMessage('Please select at least one recipient', 'danger'); return; }

                     const formData = new FormData(this);
                     formData.delete('selected_recipients[]');
                     formData.append('selected_recipients', JSON.stringify(selectedRecipients)); // Send as JSON string

                     fetch(this.action, { /* ... POST request ... */ })
                         .then(response => response.json())
                         .then(data => {
                             if (data.success) {
                                 const modalInstance = bootstrap.Modal.getInstance(sendInvoiceModalEl);
                                 if (modalInstance) modalInstance.hide();
                                 showMessage(data.message, 'success');
                                 setTimeout(() => window.location.reload(), 2000);
                             } else { /* ... error handling ... */ }
                         })
                         .catch(error => { /* ... error handling ... */ });
                 });
             }
         });

         // AlpineJS function (Keep as is)
        function datePicker() {
            return { startDate: '', endDate: '' }
        }
</script>

</body>
</html>