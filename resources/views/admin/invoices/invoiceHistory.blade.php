<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.Invoices History - DalyTrac') }}</title>
    
    @vite('resources/css/app.css') 
    @livewireStyles
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <script src="//unpkg.com/alpinejs" defer></script>

    <style>
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
        .table td:last-child {
            white-space: nowrap;
        }
        .table td, .table th {
            padding: 0.75rem;
        }
    </style>
</head>
<body class="bg-gray-100 flex h-screen overflow-hidden font-sans">

    {{-- Sidebar Component --}}
    @if(Auth::user()->hasRole('Admin'))
        <x-dashboard.sidebar />
    @else
        <x-employees.sidebar />
    @endif

    {{-- Main Wrapper (Navbar + Content) --}}
    <div class="flex flex-col flex-1 overflow-y-auto" x-data>

        {{-- Navbar Component --}}
        <x-dashboard.navbar />

        {{-- Main Content Area --}}
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-8 space-y-8"> 

            {{-- 1. Header Section --}}
            <div class="flex flex-wrap justify-between items-center gap-4">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">{{ __('messages.Invoices History') }}</h1>
                    <p class="text-sm text-gray-600">{{ __('messages.Manage and track all your invoices in one place') }}</p>
                </div>
                <div class="flex items-center gap-3">
                    <button type="button" 
                            id="openInvoiceReportModal"
                            class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg shadow-sm">
                        {{ __('messages.Invoices Reports PDF') }}
                    </button>

                    @php $archiveRoute = ''; @endphp
                    @if(Auth::user()->hasRole('Admin')) @php $archiveRoute = route('admin.invoiceArchived.archiveIndex'); @endphp @endif
                    @if(Auth::user()->hasRole('Responsable de projet')) @php $archiveRoute = route('Responsable_de_projet.invoiceArchived.archiveIndex'); @endphp @endif
                    @if(Auth::user()->hasRole('Employee')) @php $archiveRoute = route('employee.invoiceArchived.archiveIndex'); @endphp @endif
                    @if(Auth::user()->hasRole('Responsable de projet et gestionnaire')) @php $archiveRoute = route('Responsable_gestionnaire.invoiceArchived.archiveIndex'); @endphp @endif
                    
                    @if($archiveRoute)
                    <a href="{{ $archiveRoute }}" class="inline-flex items-center bg-white hover:bg-gray-50 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 shadow-sm">
                        {{ __('messages.View Archive') }}
                    </a>
                    @endif
                </div>
            </div>

             {{-- 2. Date Pickers Section --}}
             <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                 <div>
                     <label for="startDate" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Start Date') }}</label>
                     <input type="date" id="startDate" placeholder="{{ __('messages.Start Date placeholder') }}" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3">
                 </div>
                 <div>
                     <label for="endDate" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.End Date') }}</label>
                     <input type="date" id="endDate" placeholder="{{ __('messages.End Date placeholder') }}" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3">
                 </div>
             </div>

             {{-- JS Message Container --}}
             <div id="messageContainer" style="display: none;" class="px-4 py-3 rounded relative" role="alert">
                 <strong class="font-bold" id="messageTitle"></strong>
                 <span class="block sm:inline" id="messageText"></span>
                 <button type="button" onclick="this.parentElement.style.display='none';" class="absolute top-0 bottom-0 right-0 px-4 py-3 font-bold text-xl" aria-label="Close">&times;</button>
             </div>
             {{-- Session Message --}}
             @if(session('message'))
             <div id="sessionMessageContainer" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                 <strong class="font-bold">{{ __('messages.Success!') }}</strong>
                 <span class="block sm:inline">{{ session('message') }}</span>
                 <button type="button" onclick="this.parentElement.style.display='none';" class="absolute top-0 bottom-0 right-0 px-4 py-3 font-bold text-xl" aria-label="Close">&times;</button>
             </div>
             @endif
             {{-- Session ERROR Message (NEW BLOCK) --}}
             @if(session('error'))
             <div id="sessionErrorMessageContainer" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                 <strong class="font-bold">{{ __('messages.Error!') }}</strong>
                 <span class="block sm:inline">{{ session('error') }}</span>
                 <button type="button" onclick="this.parentElement.style.display='none';" class="absolute top-0 bottom-0 right-0 px-4 py-3 font-bold text-xl" aria-label="Close">&times;</button>
             </div>
             @endif

            {{-- 3. Totals Cards Section --}}
            @php
                $currentYear = date('Y');
                $totalPaid = App\Models\invoices_history::where('entreprise_id', Auth::user()->entreprise_id)->where('Payment_status', 1)->sum('total_ttc');
                $totalUnpaid = App\Models\invoices_history::where('entreprise_id', Auth::user()->entreprise_id)
                                                ->where(function($query) {
                                                    $query->where('Payment_status', 0)->orWhereNull('Payment_status');
                                                })->sum('total_ttc');
                $totalAmount = $totalPaid + $totalUnpaid;
                $totalTva = App\Models\invoices_history::where('entreprise_id', Auth::user()->entreprise_id)->sum('total_tva');
                
                // Current year totals
                $totalPaidCurrentYear = App\Models\invoices_history::where('entreprise_id', Auth::user()->entreprise_id)
                                                    ->where('Payment_status', 1)
                                                    ->whereYear('created_at', $currentYear)
                                                    ->sum('total_ttc');
                $totalUnpaidCurrentYear = App\Models\invoices_history::where('entreprise_id', Auth::user()->entreprise_id)
                                                    ->where(function($query) {
                                                        $query->where('Payment_status', 0)->orWhereNull('Payment_status');
                                                    })
                                                    ->whereYear('created_at', $currentYear)
                                                    ->sum('total_ttc');
                $totalAmountCurrentYear = $totalPaidCurrentYear + $totalUnpaidCurrentYear;
                $totalTvaCurrentYear = App\Models\invoices_history::where('entreprise_id', Auth::user()->entreprise_id)
                                                    ->whereYear('created_at', $currentYear)
                                                    ->sum('total_tva');
            @endphp
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                {{-- Total Paid Card --}}
                <div onclick="filterByStatus('Paid')" class="bg-white rounded-lg shadow border border-gray-200 p-5 flex items-center space-x-4 cursor-pointer hover:bg-gray-50 transition-colors">
                    <div class="flex-shrink-0 bg-green-100 rounded-full p-3">
                        <svg class="h-6 w-6 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p class="text-xs text-gray-500 uppercase font-medium">{{ __('messages.TOTAL PAID') }}</p>
                        <p class="text-xl font-semibold text-green-600">€{{ number_format($totalPaid, 2, ',', '.') }}</p>
                        <p class="text-s font-semibold text-black-500 mt-1">{{ $currentYear }}: €{{ number_format($totalPaidCurrentYear, 2, ',', '.') }}</p>
                    </div>
                </div>
                {{-- Total Unpaid Card --}}
                <div onclick="filterByStatus('Unpaid')" class="bg-white rounded-lg shadow border border-gray-200 p-5 flex items-center space-x-4 cursor-pointer hover:bg-gray-50 transition-colors">
                    <div class="flex-shrink-0 bg-red-100 rounded-full p-3">
                        <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p class="text-xs text-gray-500 uppercase font-medium">{{ __('messages.TOTAL UNPAID') }}</p>
                        <p class="text-xl font-semibold text-red-600">€{{ number_format($totalUnpaid, 2, ',', '.') }}</p>
                        <p class="text-s font-semibold text-black-500 mt-1">{{ $currentYear }}: €{{ number_format($totalUnpaidCurrentYear, 2, ',', '.') }}</p>
                    </div>
                </div>
                {{-- Total Amount Card --}}
                <div onclick="filterByStatus('')" class="bg-white rounded-lg shadow border border-gray-200 p-5 flex items-center space-x-4 cursor-pointer hover:bg-gray-50 transition-colors">
                    <div class="flex-shrink-0 bg-blue-100 rounded-full p-3">
                        <svg class="h-6 w-6 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a2.25 2.25 0 00-2.25-2.25H15a3 3 0 11-6 0H5.25A2.25 2.25 0 003 12m18 0v6a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 18v-6m18 0V9M3 12V9m18 3a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 12m15 0a2.25 2.25 0 01-2.25 2.25H15a3 3 0 11-6 0H5.25A2.25 2.25 0 013 12m15 0V6a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 6v6" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p class="text-xs text-gray-500 uppercase font-medium">{{ __('messages.TOTAL AMOUNT') }}</p>
                        <p class="text-xl font-semibold text-blue-600">€{{ number_format($totalAmount, 2, ',', '.') }}</p>
                        <p class="text-s font-semibold text-black-500 mt-1">{{ $currentYear }}: €{{ number_format($totalAmountCurrentYear, 2, ',', '.') }}</p>
                    </div>
                </div>
                {{-- Total TVA Card --}}
                <div class="bg-white rounded-lg shadow border border-gray-200 p-5 flex items-center space-x-4">
                    <div class="flex-shrink-0 bg-indigo-100 rounded-full p-3">
                        <svg class="h-6 w-6 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 15.75V18m-7.5-6.75h.008v.008H8.25v-.008zm0 2.25h.008v.008H8.25V13.5zm0 2.25h.008v.008H8.25v-.008zm0 2.25h.008v.008H8.25V18zm2.498-6.75h.007v.008h-.007v-.008zm0 2.25h.007v.008h-.007V13.5zm0 2.25h.007v.008h-.007v-.008zm0 2.25h.007v.008h-.007V18zm2.504-6.75h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008V13.5zm0 2.25h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008V18zm2.498-6.75h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008V13.5zM8.25 6h7.5v2.25h-7.5V6zM12 2.25c-1.892 0-3.758.11-5.593.322C5.307 2.7 4.5 3.65 4.5 4.757V19.5a2.25 2.25 0 002.25 2.25h10.5a2.25 2.25 0 002.25-2.25V4.757c0-1.108-.806-2.057-1.907-2.185A48.208 48.208 0 0012 2.25z" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p class="text-xs text-gray-500 uppercase font-medium">{{ __('messages.TOTAL TVA') }}</p>
                        <p class="text-xl font-semibold text-gray-800">€{{ number_format($totalTva, 2, ',', '.') }}</p>
                        <p class="text-s font-semibold text-black-500 mt-1">{{ $currentYear }}: €{{ number_format($totalTvaCurrentYear, 2, ',', '.') }}</p>
                    </div>
                </div>
            </div>

            {{-- 4. Filters Section --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h6 class="text-base font-semibold text-gray-800 mb-4">{{ __('messages.Filters') }}</h6>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div>
                        <label for="clientFilter" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Filter by Client') }}</label>
                        <select id="clientFilter" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3">
                            <option value="">{{ __('messages.All Clients') }}</option>
                            @foreach($clients as $client)
                            <option value="{{$client->name}}">{{$client->name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="projectFilter" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Filter by Project') }}</label>
                        <select id="projectFilter" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3">
                            <option value="">{{ __('messages.All Projects') }}</option>
                            @foreach($projects as $project)
                            <option value="{{$project->name}}">{{strtoupper($project->name)}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="paymentFilter" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Filter by Payment') }}</label>
                        <select id="paymentFilter" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3">
                            <option value="">{{ __('messages.All Invoices') }}</option>
                            <option value="Paid">{{ __('messages.Paid') }}</option>
                            <option value="Unpaid">{{ __('messages.Unpaid') }}</option>
                            <option value="Overdue">{{ __('messages.Overdue') }}</option> {{-- [ADDED] Overdue Option --}}
                        </select>
                    </div>
                    <div>
                        <label for="typeFilter" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Filter by Type') }}</label>
                        <select id="typeFilter" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3">
                            <option value="">{{ __('messages.All Invoices') }}</option>
                            <option value="partial">{{ __('messages.Partial') }}</option>
                            <option value="percentage">{{ __('messages.Percentage') }}</option>
                            <option value="period">{{ __('messages.Period Invoice') }}</option>
                            <option value="detailed">{{ __('messages.Detailed Invoice Generator') }}</option>
                            <option value="full">{{ __('messages.Full Invoice') }}</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- 5. Table Section --}}
<div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
    <div class="table-responsive overflow-x-auto">
        <table class="w-full divide-y divide-gray-200 table" id="invoicesTable">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-4 py-3 text-left text-sm font-semibold text-gray-600 uppercase">{{ __('messages.Invoice N°') }}</th>
                    <th scope="col" class="px-4 py-3 text-left text-sm font-semibold text-gray-600 uppercase">{{ __('messages.Project') }}</th>
                    <th scope="col" class="px-4 py-3 text-left text-sm font-semibold text-gray-600 uppercase">{{ __('messages.VAT') }}</th>
                    <th scope="col" class="px-4 py-3 text-left text-sm font-semibold text-gray-600 uppercase">{{ __('messages.Client') }}</th>
                    <th scope="col" class="px-4 py-3 text-left text-sm font-semibold text-gray-600 uppercase">{{ __('messages.Type') }}</th>
                    <th scope="col" class="px-4 py-3 text-left text-sm font-semibold text-gray-600 uppercase">{{ __('messages.Created At') }}</th>
                    <th scope="col" class="px-4 py-3 text-left text-sm font-semibold text-gray-600 uppercase">{{ __('messages.Amount') }}</th>
                    <th scope="col" class="px-4 py-3 text-center text-sm font-semibold text-gray-600 uppercase">{{ __('messages.Actions') }}</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
@forelse($invoices as $invoice)
<tr class="{{ $invoice->sentInvoice == 1 ? 'bg-green-50 hover:bg-green-100' : 'hover:bg-gray-50' }}">
    <td class="px-4 py-3 whitespace-nowrap text-sm font-medium">
        @php
            $dueDate = \Carbon\Carbon::parse($invoice->due_date);
            $today = \Carbon\Carbon::now();
            $isOverdue = $dueDate->isPast() && !$invoice->Payment_status;
            $daysOverdue = $isOverdue ? $today->diffInDays($dueDate) : 0;
            $pdfRoute = '';
            if(Auth::user()->hasRole('Responsable de projet')) $pdfRoute = route('Responsable_de_projet.invoices.servePdf', ['invoiceName' => $invoice->id]);
            elseif(Auth::user()->hasRole('Admin')) $pdfRoute = route('admin.invoices.servePdf', ['invoiceName' => $invoice->id]);
            elseif(Auth::user()->hasRole('Employee')) $pdfRoute = route('employee.invoices.servePdf', ['invoiceName' => $invoice->id]);
            elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire')) $pdfRoute = route('Responsable_gestionnaire.invoices.servePdf', ['invoiceName' => $invoice->id]);
        @endphp
        <a href="{{ $pdfRoute }}" target="_blank" class="{{ $isOverdue ? 'invoice-link-overdue' : 'invoice-link-normal' }}">
            {{$entreprise_infos->invoice_prefix}}-{{date('ym', strtotime($invoice->created_at))}}{{$invoice->number}}
            @if($isOverdue)
                <span class="block text-xs">({{ $daysOverdue }} {{ __('messages.Invoice days overdue') }})</span>
            @endif
        </a>
    </td>
    <td class="px-4 py-3 text-sm text-gray-800" style="max-width: 200px; word-wrap: break-word; white-space: normal;">{{strtoupper($invoice->project_name)}}</td>
    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">
        @php $project = App\Models\Project::find($invoice->project_id); @endphp
        {{$project->tva ?? __('messages.N/A')}}%
    </td>
    <td class="px-4 py-3 text-sm text-gray-800" style="max-width: 200px; word-wrap: break-word; white-space: normal;">
        @php $client = \App\Models\Client::where('id', $invoice->client_id)->first(); @endphp
        {{$client->name ?? __('messages.N/A')}}
    </td>
    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600 capitalize">
        @if($invoice->type == 'Partial') {{$invoice->type}}-{{$invoice->partial}}
        @elseif($invoice->type == 'Percentage') {{$invoice->type}}-{{$invoice->percentage}}
        @else {{$invoice->type}}
        @endif
    </td>
    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">{{ \Carbon\Carbon::parse($invoice->created_at)->format('Y-m-d') }}</td>
    
    <td class="px-4 py-3 whitespace-nowrap text-sm font-semibold text-green-700">€{{ number_format($invoice->total_ttc, 2, ',', ' ') }}</td>
    
    <td class="px-2 py-3">
        @php
            $isPaid = $invoice->Payment_status == 1;
            $toggleRoute = '';
            if (Auth::user()->hasRole('Admin')) {
                $toggleRoute = $isPaid ? route('admin.invoice.deactivate', $invoice->id) : route('admin.invoice.activate', $invoice->id);
            } elseif (Auth::user()->hasRole('Responsable de projet')) {
                $toggleRoute = $isPaid ? route('Responsable_de_projet.invoice.deactivate', $invoice->id) : route('Responsable_de_projet.invoice.activate', $invoice->id);
            } elseif (Auth::user()->hasRole('Employee')) {
                $toggleRoute = $isPaid ? route('employee.invoice.deactivate', $invoice->id) : route('employee.invoice.activate', $invoice->id);
            } elseif (Auth::user()->hasRole('Responsable de projet et gestionnaire')) {
                $toggleRoute = $isPaid ? route('Responsable_gestionnaire.invoice.deactivate', $invoice->id) : route('Responsable_gestionnaire.invoice.activate', $invoice->id);
            }
        @endphp

        @if($isPaid)
            {{-- PAID: single row – Paid, Credit Note, Send --}}
            <div class="flex flex-wrap items-center justify-center gap-2">
                {{-- Paid --}}
                @if($toggleRoute)
                    <form action="{{ $toggleRoute }}" method="post" onsubmit="return confirm('{{ __('messages.Change payment status?') }}');">
                        @csrf
                        <button type="submit"
                                title="{{ __('messages.Mark as Unpaid') }}"
                                class="payment-button px-3 py-1 rounded-md text-xs font-medium bg-green-600 hover:bg-green-700 text-white">
                            {{ __('messages.Paid') }}
                        </button>
                    </form>
                @endif

                {{-- Credit Note --}}
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
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" type="button"
                            title="{{ __('messages.Credit Note') }}"
                            class="px-3 py-1 rounded-md bg-purple-600 text-white hover:bg-purple-700 text-xs font-medium">
                        {{ __('messages.Credit Note') }}
                    </button>
                    <ul x-show="open"
                        @click.outside="open = false"
                        x-transition
                        style="display: none;"
                        class="absolute right-0 mt-2 w-40 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none z-50">
                        @if($invoice->type == 'Detailed' && $detailedCreditNoteRoute)
                                <li><a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                   onclick="event.preventDefault(); showPartialDetailedPopup({{ $invoice->id }}, {{ $invoice->total_ttc }});">{{ __('messages.Partial') }}</a></li>
                            <li><a href="{{ route($detailedCreditNoteRoute, $invoice->id) }}?type=full"
                                   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">{{ __('messages.Full') }}</a></li>
                        @elseif($creditNoteRoute)
                            <li><a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                   onclick="event.preventDefault(); showPartialPopup({{ $invoice->id }}, {{ $invoice->total_ttc }});">{{ __('messages.Partial') }}</a></li>
                            <li><a href="{{ route($creditNoteRoute, $invoice->id) }}?type=full"
                                   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">{{ __('messages.Full') }}</a></li>
                        @endif
                    </ul>
                </div>

                {{-- Send (Email + Peppol Combined) --}}
                @can('Send invoice')
                    <button type="button"
                            title="{{ __('messages.Send Invoice') }}"
                            class="px-3 py-1 rounded-md bg-gray-600 text-white hover:bg-gray-700 text-xs font-medium flex items-center gap-1"
                            onclick="event.preventDefault(); if(window.openCombinedSendModal) window.openCombinedSendModal({{ $invoice->id }}, '{{ strtolower($invoice->type ?? 'full') }}'); else setTimeout(function(){ if(window.openCombinedSendModal) window.openCombinedSendModal({{ $invoice->id }}, '{{ strtolower($invoice->type ?? 'full') }}'); }, 100);">
                        {{ __('messages.Send') }}
                    </button>
                @endcan
            </div>
        @else
            {{-- UNPAID: original 2x2 layout --}}
            <div class="flex flex-col items-center gap-2">
                <div class="grid grid-cols-2 gap-1.5 w-full">
                    {{-- Status --}}
                    @if($toggleRoute)
                        <form action="{{ $toggleRoute }}" method="post" onsubmit="return confirm('{{ __('messages.Change payment status?') }}');" class="w-full">
                            @csrf
                            <button type="submit"
                                    title="{{ __('messages.Mark as Paid') }}"
                                    class="payment-button w-full px-2 py-1 rounded-md text-xs font-medium bg-red-600 hover:bg-red-700 text-white">
                                {{ __('messages.Unpaid') }}
                            </button>
                        </form>
                    @endif

                    {{-- Reminder --}}
                    @can('Send invoice')
                        @php
                            $reminderLabel = __('messages.Reminder');
                            if (!$invoice->first_reminder_date) {
                                $reminderLabel = __('messages.Reminder 1');
                            } elseif (!$invoice->second_reminder_date) {
                                $reminderLabel = __('messages.Reminder 2');
                            } elseif (!$invoice->third_reminder_date) {
                                $reminderLabel = __('messages.Reminder 3');
                            }
                        @endphp
                        <div x-data="{ open: false }" class="relative w-full">
                            <button @click="open = !open" type="button"
                                    title="{{ __('messages.Send Reminder') }}"
                                    class="w-full px-2 py-1 rounded-md bg-yellow-500 text-white hover:bg-yellow-600 text-xs font-medium flex justify-center items-center gap-1">
                                {{ $reminderLabel }}
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                            </button>
                            <ul x-show="open"
                                @click.outside="open = false"
                                x-transition
                                style="display: none;"
                                class="absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none z-50">
                                @if(!$invoice->first_reminder_date)
                                    <li><a class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" href="{{ route((Auth::user()->hasRole('Admin') ? 'admin.invoice.sendManualremind' : (Auth::user()->hasRole('Responsable de projet') ? 'Responsable_de_projet.invoice.sendManualremind' : (Auth::user()->hasRole('Responsable de projet et gestionnaire') ? 'Responsable_gestionnaire.invoice.sendManualremind' : 'employee.invoice.sendManualremind'))), ['id' => $invoice->id, 'reminder' => 1]) }}">{{ __('messages.Send Reminder 1') }}</a></li>
                                @endif
                                @if($invoice->first_reminder_date && !$invoice->second_reminder_date)
                                    <li><a class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" href="{{ route((Auth::user()->hasRole('Admin') ? 'admin.invoice.sendManualremind' : (Auth::user()->hasRole('Responsable de projet') ? 'Responsable_de_projet.invoice.sendManualremind' : (Auth::user()->hasRole('Responsable de projet et gestionnaire') ? 'Responsable_gestionnaire.invoice.sendManualremind' : 'employee.invoice.sendManualremind'))), ['id' => $invoice->id, 'reminder' => 2]) }}">{{ __('messages.Send Reminder 2') }}</a></li>
                                @endif
                                @if($invoice->second_reminder_date && !$invoice->third_reminder_date)
                                    <li><a class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" href="{{ route((Auth::user()->hasRole('Admin') ? 'admin.invoice.sendManualremind' : (Auth::user()->hasRole('Responsable de projet') ? 'Responsable_de_projet.invoice.sendManualremind' : (Auth::user()->hasRole('Responsable de projet et gestionnaire') ? 'Responsable_gestionnaire.invoice.sendManualremind' : 'employee.invoice.sendManualremind'))), ['id' => $invoice->id, 'reminder' => 3]) }}">{{ __('messages.Send Reminder 3') }}</a></li>
                                @endif
                            </ul>
                        </div>
                    @endcan

                    {{-- Credit Note --}}
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
                    <div x-data="{ open: false }" class="relative w-full">
                        <button @click="open = !open" type="button"
                                title="Create Credit Note"
                                class="w-full px-2 py-1 rounded-md bg-purple-600 text-white hover:bg-purple-700 text-xs font-medium">
                            Credit Note
                        </button>
                        <ul x-show="open"
                            @click.outside="open = false"
                            x-transition
                            style="display: none;"
                            class="absolute right-0 mt-2 w-40 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none z-50">
                            @if($invoice->type == 'Detailed' && $detailedCreditNoteRoute)
                                <li><a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" onclick="event.preventDefault(); showPartialDetailedPopup({{ $invoice->id }}, {{ $invoice->total_ttc }});">Partial</a></li>
                                <li><a href="{{ route($detailedCreditNoteRoute, $invoice->id) }}?type=full" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Full</a></li>
                            @elseif($creditNoteRoute)
                                <li><a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" onclick="event.preventDefault(); showPartialPopup({{ $invoice->id }}, {{ $invoice->total_ttc }});">Partial</a></li>
                                <li><a href="{{ route($creditNoteRoute, $invoice->id) }}?type=full" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Full</a></li>
                            @endif
                        </ul>
                    </div>

                    {{-- Send (Email + Peppol Combined) --}}
                    @can('Send invoice')
                        <button type="button"
                                title="{{ __('messages.Send Invoice') }}"
                                class="w-full px-2 py-1 rounded-md bg-gray-600 text-white hover:bg-gray-700 text-xs font-medium flex justify-center items-center gap-1"
                                onclick="event.preventDefault(); if(window.openCombinedSendModal) window.openCombinedSendModal({{ $invoice->id }}, '{{ strtolower($invoice->type ?? 'full') }}'); else setTimeout(function(){ if(window.openCombinedSendModal) window.openCombinedSendModal({{ $invoice->id }}, '{{ strtolower($invoice->type ?? 'full') }}'); }, 100);">
                            {{ __('messages.Send') }}
                        </button>
                    @endcan
                </div>
            </div>
        @endif

            {{-- Edit & Delete buttons in a row below the grid --}}
            <div class="flex items-center justify-center gap-1.5 w-full">
                {{-- Edit Button: only for first row and if not paid --}}
                @if($loop->first && $invoice->Payment_status != 1)
                    @php 
                        $editRoute = '';
                        if(Auth::user()->hasRole('Admin')) {
                            if($invoice->type == 'Partial') $editRoute = route('admin.invoices.editPartialInvoice', $invoice->id);
                            elseif($invoice->type == 'Percentage') $editRoute = route('admin.invoices.editPercentageInvoice', $invoice->id);
                            elseif($invoice->type == 'Period') $editRoute = route('admin.invoices.editPeriodInvoice', $invoice->id);
                            elseif($invoice->type == 'Detailed') $editRoute = route('admin.invoices.editDetailedInvoice', $invoice->id);
                            else $editRoute = route('admin.invoices.editFullInvoice', $invoice->id);
                        } elseif(Auth::user()->hasRole('Responsable de projet')) {
                            if($invoice->type == 'Partial') $editRoute = route('Responsable_de_projet.invoices.editPartialInvoice', $invoice->id);
                            elseif($invoice->type == 'Percentage') $editRoute = route('Responsable_de_projet.invoices.editPercentageInvoice', $invoice->id);
                            elseif($invoice->type == 'Period') $editRoute = route('Responsable_de_projet.invoices.editPeriodInvoice', $invoice->id);
                            elseif($invoice->type == 'Detailed') $editRoute = route('Responsable_de_projet.invoices.editDetailedInvoice', $invoice->id);
                            else $editRoute = route('Responsable_de_projet.invoices.editFullInvoice', $invoice->id);
                        } elseif(Auth::user()->hasRole('Employee')) {
                            if($invoice->type == 'Partial') $editRoute = route('employee.invoices.editPartialInvoice', $invoice->id);
                            elseif($invoice->type == 'Percentage') $editRoute = route('employee.invoices.editPercentageInvoice', $invoice->id);
                            elseif($invoice->type == 'Period') $editRoute = route('employee.invoices.editPeriodInvoice', $invoice->id);
                            elseif($invoice->type == 'Detailed') $editRoute = route('employee.invoices.editDetailedInvoice', $invoice->id);
                            else $editRoute = route('employee.invoices.editFullInvoice', $invoice->id);
                        } elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire')) {
                            if($invoice->type == 'Partial') $editRoute = route('Responsable_gestionnaire.invoices.editPartialInvoice', $invoice->id);
                            elseif($invoice->type == 'Percentage') $editRoute = route('Responsable_gestionnaire.invoices.editPercentageInvoice', $invoice->id);
                            elseif($invoice->type == 'Period') $editRoute = route('Responsable_gestionnaire.invoices.editPeriodInvoice', $invoice->id);
                            elseif($invoice->type == 'Detailed') $editRoute = route('Responsable_gestionnaire.invoices.editDetailedInvoice', $invoice->id);
                            else $editRoute = route('Responsable_gestionnaire.invoices.editFullInvoice', $invoice->id);
                        }
                    @endphp
                    @if($editRoute)
                    <a href="{{ $editRoute }}" title="{{ __('messages.Edit Invoice') }}" class="p-1.5 rounded-md bg-blue-600 text-white hover:bg-blue-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                    </a>
                    @endif
                @endif

                {{-- Delete Button: only for first row --}}
                @if($loop->first)
                    @can('Delete invoice')
                        @php 
                            $deleteRoute = '';
                            if(Auth::user()->hasRole('Admin')) $deleteRoute = route('admin.invoice.destroy', $invoice->id);
                            elseif(Auth::user()->hasRole('Responsable de projet')) $deleteRoute = route('Responsable_de_projet.invoice.destroy', $invoice->id);
                            elseif(Auth::user()->hasRole('Employee')) $deleteRoute = route('employee.invoice.destroy', $invoice->id);
                            elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire')) $deleteRoute = route('Responsable_gestionnaire.invoice.destroy', $invoice->id);
                        @endphp
                        @if($deleteRoute)
                        <form action="{{ $deleteRoute }}" method="post" onsubmit="return confirm('{{ __('messages.Delete this invoice? This cannot be undone.') }}');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" title="{{ __('messages.Delete Invoice') }}" class="p-1.5 rounded-md bg-red-600 text-white hover:bg-red-700">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                            </button>
                        </form>
                        @endif
                    @endcan
                @endif
            </div>
        </div>
    </td>
</tr>
@empty
<tr>
    <td colspan="8" class="text-center px-4 py-6 text-gray-500">
        {{ __('messages.No invoices found matching your criteria.') }}
    </td>
</tr>
@endforelse
</tbody>
        </table>
    </div>
</div> 

            {{-- Modals --}}
            <div id="sendInvoiceModal" class="fixed inset-0 z-40 flex items-center justify-center bg-black bg-opacity-50 hidden">
                <div class="bg-white rounded-lg shadow-lg w-full max-w-lg mx-4">
                    <div class="flex justify-between items-center p-4 border-b">
                        <h5 class="text-lg font-semibold text-gray-800">{{ __('messages.Send Invoice') }}</h5>
                        <button type="button" id="closeSendInvoiceModal" class="text-gray-400 hover:text-gray-600">&times;</button>
                    </div>
                    <form id="sendInvoiceForm" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="invoice_id" id="invoice_id">
                        <div class="p-6 space-y-4">
                            <div>
                                <h6 class="text-sm font-medium text-gray-700 mb-2">{{ __('messages.Select Recipients:') }}</h6>
                                <div id="recipientsList" class="space-y-2 p-3 border border-gray-200 rounded-lg bg-gray-50 h-40 overflow-y-auto"></div>
                            </div>
                            <div>
                                <label for="fileUpload" class="block text-sm font-medium text-gray-700 mb-2">{{ __('messages.Upload Additional Files (Optional):') }}</label>
                                <input type="file" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" id="fileUpload" name="file[]" multiple>
                            </div>
                        </div>
                        <div class="flex items-center justify-end p-4 bg-gray-50 border-t rounded-b-lg space-x-2">
                            <button type="button" id="cancelSendInvoiceModal" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-100">{{ __('messages.Close') }}</button>
                            <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">{{ __('messages.Send Invoice') }}</button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Peppol Send Modal --}}
            <div id="sendPeppolModal" class="fixed inset-0 z-40 flex items-center justify-center bg-black bg-opacity-50 hidden">
                <div class="bg-white rounded-lg shadow-lg w-full max-w-lg mx-4">
                    <div class="flex justify-between items-center p-4 border-b">
                        <h5 class="text-lg font-semibold text-gray-800">{{ __('messages.Send Invoice via Peppol') }}</h5>
                        <button type="button" id="closeSendPeppolModal" class="text-gray-400 hover:text-gray-600">&times;</button>
                    </div>
                    <form id="sendPeppolForm" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="invoice_id" id="peppol_invoice_id">
                        <input type="hidden" name="invoice_type" id="peppol_invoice_type">
                        <div class="p-6 space-y-4">
                            <div class="text-sm text-gray-700">
                                <p class="mb-2">
                                    {{ __('messages.You are about to send this invoice via Peppol.') }}
                                </p>
                                <p class="mb-4">
                                    {{ __('messages.Please confirm that you want to proceed. This action will send the invoice through the Peppol network.') }}
                                </p>
                            </div>
                            
                            {{-- File Attachments Section --}}
                            <div class="border-t pt-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ __('messages.Attach Files (Optional)') }}
                                    <span class="text-xs text-gray-500 font-normal">(PDF, Excel, CSV, JPEG, PNG)</span>
                                </label>
                                <div class="mt-2">
                                    <input type="file" 
                                           id="peppol_attachments" 
                                           name="attachments[]" 
                                           multiple 
                                           accept=".pdf,.xlsx,.xls,.csv,.jpg,.jpeg,.png"
                                           class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer border border-gray-300 rounded-lg">
                                    <p class="mt-1 text-xs text-gray-500">You can select multiple files. Maximum file size: 10MB per file.</p>
                                </div>
                                
                                {{-- Selected Files Display --}}
                                <div id="peppol_selected_files" class="mt-3 space-y-2 hidden">
                                    <p class="text-xs font-medium text-gray-700">{{ __('messages.Selected files:') }}</p>
                                    <ul id="peppol_files_list" class="text-xs text-gray-600 space-y-1"></ul>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center justify-end p-4 bg-gray-50 border-t rounded-b-lg space-x-2">
                            <button type="button" id="cancelSendPeppolModal" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-100">{{ __('messages.Annuler') }}</button>
                            <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">{{ __('messages.Confirmer l\'envoi via Peppol') }}</button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Combined Send Modal (Email + Peppol) --}}
            <div id="combinedSendModal" class="fixed inset-0 z-40 flex items-center justify-center bg-black bg-opacity-50 hidden">
                <div class="bg-white rounded-lg shadow-lg w-full max-w-xl mx-4">
                    <div class="flex justify-between items-center p-4 border-b">
                        <h5 class="text-lg font-semibold text-gray-800">{{ __('messages.Send Invoice') }}</h5>
                        <button type="button" id="closeCombinedSendModal" class="text-gray-400 hover:text-gray-600">&times;</button>
                    </div>
                    <form id="combinedSendForm" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="invoice_id" id="combined_invoice_id">
                        <input type="hidden" name="invoice_type" id="combined_invoice_type">
                        <div class="p-6 space-y-5">
                            <div>
                                <h6 class="text-sm font-medium text-gray-700 mb-2">{{ __('messages.Choose how to send:') }}</h6>
                                <div class="space-y-2">
                                    <label class="flex items-center space-x-2">
                                        <input type="checkbox" id="combined_send_email" class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                        <span class="text-sm text-gray-700">{{ __('messages.Send via Email') }}</span>
                                    </label>
                                    <label class="flex items-center space-x-2">
                                        <input type="checkbox" id="combined_send_peppol" class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                        <span class="text-sm text-gray-700">{{ __('messages.Send via Peppol') }}</span>
                                    </label>
                                </div>
                            </div>

                            {{-- Email recipients (visible only when email is checked) --}}
                            <div id="combined_email_section" class="space-y-2 hidden">
                                <h6 class="text-sm font-medium text-gray-700">{{ __('messages.Select Recipients (Email):') }}</h6>
                                <div id="combinedRecipientsList" class="space-y-2 p-3 border border-gray-200 rounded-lg bg-gray-50 h-40 overflow-y-auto text-sm text-gray-700">
                                    <div class="text-center text-gray-500">{{ __('messages.Loading...') }}</div>
                                </div>
                            </div>

                            {{-- Attachments for both channels --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ __('messages.Attach Files (Optional)') }}
                                    <span class="text-xs text-gray-500 font-normal">(PDF, Excel, CSV, JPEG, PNG – max 10MB each)</span>
                                </label>
                                <input type="file"
                                       id="combined_attachments"
                                       name="combined_attachments[]"
                                       multiple
                                       accept=".pdf,.xlsx,.xls,.csv,.jpg,.jpeg,.png"
                                       class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer border border-gray-300 rounded-lg">
                                <div id="combined_selected_files" class="mt-3 space-y-2 hidden">
                                    <p class="text-xs font-medium text-gray-700">{{ __('messages.Selected files:') }}</p>
                                    <ul id="combined_files_list" class="text-xs text-gray-600 space-y-1"></ul>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center justify-end p-4 bg-gray-50 border-t rounded-b-lg space-x-2">
                            <button type="button" id="cancelCombinedSendModal" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-100">{{ __('messages.Close') }}</button>
                            <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">{{ __('messages.Send') }}</button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Partial Credit Note Modal --}}
            <div id="partialCreditNoteModal" class="fixed inset-0 z-40 flex items-center justify-center bg-black bg-opacity-50 hidden">
                <div class="bg-white rounded-lg shadow-lg w-full max-w-md mx-4">
                    <div class="flex justify-between items-center p-4 border-b">
                        <h5 class="text-lg font-semibold text-gray-800">{{ __('messages.Partial Credit Note') }}</h5>
                        <button type="button" id="closePartialCreditNoteModal" class="text-gray-400 hover:text-gray-600">&times;</button>
                    </div>
                    <form id="partialCreditNoteForm">
                        <div class="p-6">
                            <label for="partialAmount" class="block text-sm font-medium text-gray-700 mb-2">{{ __('messages.Enter Amount:') }}</label>
                            <input type="number" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" id="partialAmount" step="0.0001" required>
                            <span id="partialAmountError" class="text-red-600 text-sm mt-1 hidden"></span>
                        </div>
                        <div class="flex items-center justify-end p-4 bg-gray-50 border-t rounded-b-lg space-x-2">
                            <button type="button" id="cancelPartialCreditNoteModal" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-100">{{ __('messages.Close') }}</button>
                            <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">{{ __('messages.Submit') }}</button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Partial Detailed Credit Note Modal --}}
            <div id="partialDetailedCreditNoteModal" class="fixed inset-0 z-40 flex items-center justify-center bg-black bg-opacity-50 hidden">
                <div class="bg-white rounded-lg shadow-lg w-full max-w-md mx-4">
                    <div class="flex justify-between items-center p-4 border-b">
                        <h5 class="text-lg font-semibold text-gray-800">{{ __('messages.Partial Credit Note (Detailed)') }}</h5>
                        <button type="button" id="closePartialDetailedCreditNoteModal" class="text-gray-400 hover:text-gray-600">&times;</button>
                    </div>
                    <form id="partialDetailedCreditNoteForm">
                        <div class="p-6">
                            <label for="partialDetailedAmount" class="block text-sm font-medium text-gray-700 mb-2">{{ __('messages.Enter Amount:') }}</label>
                            <input type="number" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" id="partialDetailedAmount" step="0.0001" required>
                            <span id="partialDetailedAmountError" class="text-red-600 text-sm mt-1 hidden"></span>
                        </div>
                        <div class="flex items-center justify-end p-4 bg-gray-50 border-t rounded-b-lg space-x-2">
                            <button type="button" id="cancelPartialDetailedCreditNoteModal" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-100">{{ __('messages.Close') }}</button>
                            <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">{{ __('messages.Submit') }}</button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Invoice Report Modal --}}
            <div id="invoiceReportModal" class="fixed inset-0 z-40 flex items-center justify-center bg-black bg-opacity-50 hidden">
                <div class="bg-white rounded-lg shadow-lg w-full max-w-md mx-4">
                    <div class="flex justify-between items-center p-4 border-b">
                        <h5 class="text-lg font-semibold text-gray-800">{{ __('messages.Download Invoices Reports') }}</h5>
                        <button type="button" id="closeInvoiceReportModal" class="text-gray-400 hover:text-gray-600">&times;</button>
                    </div>
                    <div class="p-6 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('messages.Choose payment statuses (optional):') }}</label>
                            <div class="space-y-2">
                                <div class="flex items-center">
                                    <input class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" type="checkbox" id="status_paid" value="paid">
                                    <label class="ml-2 text-sm text-gray-700" for="status_paid">{{ __('messages.Paid status label') }}</label>
                                </div>
                                <div class="flex items-center">
                                    <input class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" type="checkbox" id="status_unpaid" value="unpaid">
                                    <label class="ml-2 text-sm text-gray-700" for="status_unpaid">{{ __('messages.Unpaid status label') }}</label>
                                </div>
                                <div class="flex items-center">
                                    <input class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" type="checkbox" id="status_overdue" value="overdue">
                                    <label class="ml-2 text-sm text-gray-700" for="status_overdue">Overdue</label>
                                </div>
                            </div>
                        </div>
                        <hr class="my-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Choose invoice types (optional):</label>
                            <div class="space-y-2">
                                <div class="flex items-center">
                                    <input class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" type="checkbox" id="type_partial" value="partial">
                                    <label class="ml-2 text-sm text-gray-700" for="type_partial">Partial</label>
                                </div>
                                <div class="flex items-center">
                                    <input class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" type="checkbox" id="type_percentage" value="percentage">
                                    <label class="ml-2 text-sm text-gray-700" for="type_percentage">Percentage</label>
                                </div>
                                <div class="flex items-center">
                                    <input class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" type="checkbox" id="type_full" value="full">
                                    <label class="ml-2 text-sm text-gray-700" for="type_full">Full</label>
                                </div>
                                <div class="flex items-center">
                                    <input class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" type="checkbox" id="type_period" value="period">
                                    <label class="ml-2 text-sm text-gray-700" for="type_period">Period</label>
                                </div>
                                <div class="flex items-center">
                                    <input class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" type="checkbox" id="type_detailed" value="detailed">
                                    <label class="ml-2 text-sm text-gray-700" for="type_detailed">Detailed</label>
                                </div>
                            </div>
                        </div>
                        <hr class="my-4">
                        <div class="flex items-center">
                            <input class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" type="checkbox" id="credit_note_applied" value="1">
                            <label class="ml-2 text-sm text-gray-700" for="credit_note_applied">Credit note applied</label>
                        </div>
                        <small class="text-gray-500 text-xs">Date range (optional) will be applied from the fields at the top.</small>
                    </div>
                    <div class="flex items-center justify-end p-4 bg-gray-50 border-t rounded-b-lg space-x-2">
                        <button type="button" id="cancelInvoiceReportModal" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-100">Cancel</button>
                        <button type="button" id="confirmInvoiceReport" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">Download PDF</button>
                    </div>
                </div>
            </div>

        </main>
    </div> 

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    // Setup CSRF token for all jQuery AJAX requests
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    // Global functions for opening modals (defined early so they're available immediately)
    window.showPartialPopup = function(invoiceId, totalCost) {
        window.dispatchEvent(new CustomEvent('open-modal', { detail: { id: 'partialCreditNoteModal', invoiceId: invoiceId, totalCost: totalCost } }));
    };
    window.showPartialDetailedPopup = function(invoiceId, totalCost) {
        window.dispatchEvent(new CustomEvent('open-modal', { detail: { id: 'partialDetailedCreditNoteModal', invoiceId: invoiceId, totalCost: totalCost } }));
    };
    
    // Global modal opening functions (will be defined in DOMContentLoaded, but we'll set up placeholders)
    window.openSendInvoiceModal = null;
    window.openSendPeppolModal = null;
    window.openCombinedSendModal = null;
    window.openPartialCreditNoteModal = null;
    window.openPartialDetailedCreditNoteModal = null;
    
    // Set up event listener immediately (not waiting for DOMContentLoaded)
    window.addEventListener('open-modal', function(event) {
        const detail = event.detail;
        if (detail.id === 'sendInvoiceModal' && detail.invoiceId && window.openSendInvoiceModal) {
            window.openSendInvoiceModal(detail.invoiceId);
        } else if (detail.id === 'sendPeppolModal' && detail.invoiceId && window.openSendPeppolModal) {
            window.openSendPeppolModal(detail.invoiceId, detail.invoiceType);
        } else if (detail.id === 'partialCreditNoteModal' && detail.invoiceId && detail.totalCost && window.openPartialCreditNoteModal) {
            window.openPartialCreditNoteModal(detail.invoiceId, detail.totalCost);
        } else if (detail.id === 'partialDetailedCreditNoteModal' && detail.invoiceId && detail.totalCost && window.openPartialDetailedCreditNoteModal) {
            window.openPartialDetailedCreditNoteModal(detail.invoiceId, detail.totalCost);
        } else if ((detail === 'invoiceReportModal' || detail.id === 'invoiceReportModal')) {
            const invoiceReportModal = document.getElementById('invoiceReportModal');
            if (invoiceReportModal) invoiceReportModal.classList.remove('hidden');
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        // --- MODAL HANDLING (Vanilla JS like devisHistory) ---
        
        // Send Invoice Modal
        const sendInvoiceModal = document.getElementById('sendInvoiceModal');
        const closeSendInvoiceModalBtn = document.getElementById('closeSendInvoiceModal');
        const cancelSendInvoiceModalBtn = document.getElementById('cancelSendInvoiceModal');
        const sendInvoiceForm = document.getElementById('sendInvoiceForm');
        
        if (closeSendInvoiceModalBtn) {
            closeSendInvoiceModalBtn.addEventListener('click', () => {
                if (sendInvoiceModal) sendInvoiceModal.classList.add('hidden');
            });
        }
        if (cancelSendInvoiceModalBtn) {
            cancelSendInvoiceModalBtn.addEventListener('click', () => {
                if (sendInvoiceModal) sendInvoiceModal.classList.add('hidden');
            });
        }
        
        // Handle Send Invoice Form Submission
        if (sendInvoiceForm) {
            sendInvoiceForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const invoiceId = document.getElementById('invoice_id').value;
                const selectedRecipients = Array.from(document.querySelectorAll('input[name="selected_recipients[]"]:checked')).map(checkbox => checkbox.value);
                
                if (selectedRecipients.length === 0) {
                    showMessage('Please select at least one recipient', 'danger');
                    return;
                }
                
                let actionUrl = '';
                @if(Auth::user()->hasRole('Admin')) 
                    actionUrl = '{{ route("admin.invoice.sendInvoice", ":invoice_id") }}';
                @elseif(Auth::user()->hasRole('Responsable de projet')) 
                    actionUrl = '{{ route("Responsable_de_projet.invoice.sendInvoice", ":invoice_id") }}';
                @elseif(Auth::user()->hasRole('Employee')) 
                    actionUrl = '{{ route("employee.invoice.sendInvoice", ":invoice_id") }}';
                @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire')) 
                    actionUrl = '{{ route("Responsable_gestionnaire.invoice.sendInvoice", ":invoice_id") }}';
                @endif
                actionUrl = actionUrl.replace(':invoice_id', invoiceId);
                
                const formData = new FormData(sendInvoiceForm);
                formData.delete('selected_recipients[]');
                selectedRecipients.forEach(recipient => {
                    formData.append('selected_recipients[]', recipient);
                });
                
                fetch(actionUrl, {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }
                })
                .then(response => {
                    if (!response.ok) {
                        return response.text().then(text => {
                            try {
                                return JSON.parse(text);
                            } catch {
                                throw new Error('Server error: ' + response.status);
                            }
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        if (sendInvoiceModal) sendInvoiceModal.classList.add('hidden');
                        showMessage(data.message, 'success');
                        setTimeout(() => window.location.reload(), 2000);
                    } else {
                        showMessage('Error sending invoice: ' + (data.message || 'Unknown error'), 'danger');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showMessage('An error occurred while sending the invoice. Please try again.', 'danger');
                });
            });
        }
        
        // Function to open Send Invoice Modal
        window.openSendInvoiceModal = function(invoiceId) {
            document.getElementById('invoice_id').value = invoiceId;
            const recipientsList = document.getElementById('recipientsList');
            recipientsList.innerHTML = '<div class="text-center">Loading...</div>';
            
            let recipientsUrl = '';
            @if(Auth::user()->hasRole('Admin'))
                recipientsUrl = '/admin/invoice/' + invoiceId + '/recipients';
            @elseif(Auth::user()->hasRole('Responsable de projet'))
                recipientsUrl = '/Responsable_de_projet/invoice/' + invoiceId + '/recipients';
            @elseif(Auth::user()->hasRole('Employee'))
                recipientsUrl = '/employee/invoice/' + invoiceId + '/recipients';
            @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                recipientsUrl = '/Responsable_gestionnaire/invoice/' + invoiceId + '/recipients';
            @endif
            
            fetch(recipientsUrl)
                .then(response => response.json())
                .then(data => {
                    recipientsList.innerHTML = '';
                    if (data.recipients && data.recipients.length > 0) {
                        data.recipients.forEach(recipient => {
                            const div = document.createElement('div');
                            div.className = 'mb-2 flex items-center';
                            div.innerHTML = `
                                <input class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 mr-2" type="checkbox" name="selected_recipients[]" value="${recipient.email}" id="recipient-${recipient.email}" ${recipient.is_comptable === 1 ? 'checked' : ''}>
                                <label class="text-sm text-gray-700" for="recipient-${recipient.email}"><strong>${recipient.name}</strong> (${recipient.email})</label>
                            `;
                            recipientsList.appendChild(div);
                        });
                    } else {
                        recipientsList.innerHTML = '<p class="text-sm text-gray-500">No recipients found</p>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    recipientsList.innerHTML = '<p class="text-sm text-red-600">Error loading recipients</p>';
                });
            
            if (sendInvoiceModal) sendInvoiceModal.classList.remove('hidden');
        };
        
        // Send Peppol Modal
        const sendPeppolModal = document.getElementById('sendPeppolModal');
        const closeSendPeppolModalBtn = document.getElementById('closeSendPeppolModal');
        const cancelSendPeppolModalBtn = document.getElementById('cancelSendPeppolModal');
        const sendPeppolForm = document.getElementById('sendPeppolForm');
        
        if (closeSendPeppolModalBtn) {
            closeSendPeppolModalBtn.addEventListener('click', () => {
                if (sendPeppolModal) sendPeppolModal.classList.add('hidden');
            });
        }
        if (cancelSendPeppolModalBtn) {
            cancelSendPeppolModalBtn.addEventListener('click', () => {
                if (sendPeppolModal) sendPeppolModal.classList.add('hidden');
            });
        }
        
        // Handle Send Peppol Form Submission
        if (sendPeppolForm) {
            sendPeppolForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const invoiceId = document.getElementById('peppol_invoice_id').value;
                const invoiceType = document.getElementById('peppol_invoice_type').value;

                let actionUrl = '';
                @if(Auth::user()->hasRole('Admin')) 
                    actionUrl = '{{ route("admin.billit.send-invoice-type", ["id" => ":invoice_id", "type" => ":invoice_type"]) }}';
                @elseif(Auth::user()->hasRole('Responsable de projet')) 
                    actionUrl = '{{ route("Responsable_de_projet.billit.send-invoice-type", ["id" => ":invoice_id", "type" => ":invoice_type"]) }}';
                @elseif(Auth::user()->hasRole('Employee')) 
                    actionUrl = '{{ route("employee.billit.send-invoice-type", ["id" => ":invoice_id", "type" => ":invoice_type"]) }}';
                @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire')) 
                    actionUrl = '{{ route("Responsable_gestionnaire.billit.send-invoice-type", ["id" => ":invoice_id", "type" => ":invoice_type"]) }}';
                @endif
                actionUrl = actionUrl.replace(':invoice_id', invoiceId).replace(':invoice_type', invoiceType) + '?channel=Peppol';
                
                const formData = new FormData(sendPeppolForm);
                
                fetch(actionUrl, {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }
                })
                .then(response => {
                    if (response.redirected || response.ok) {
                        if (sendPeppolModal) sendPeppolModal.classList.add('hidden');
                        showMessage('Invoice sent successfully via Peppol', 'success');
                        setTimeout(() => window.location.reload(), 2000);
                        return;
                    }
                    return response.text().then(text => {
                        try {
                            const data = JSON.parse(text);
                            if (data.success) {
                                if (sendPeppolModal) sendPeppolModal.classList.add('hidden');
                                showMessage(data.message || 'Invoice sent successfully via Peppol', 'success');
                                setTimeout(() => window.location.reload(), 2000);
                            } else {
                                showMessage('Error sending invoice: ' + (data.message || 'Unknown error'), 'danger');
                            }
                        } catch {
                            if (sendPeppolModal) sendPeppolModal.classList.add('hidden');
                            showMessage('Invoice sent successfully via Peppol', 'success');
                            setTimeout(() => window.location.reload(), 2000);
                        }
                    });
                })
                .catch(error => {
                    console.error('Error:', error);
                    showMessage('An error occurred while sending the invoice. Please try again.', 'danger');
                });
            }); 
        }
        
        // Function to open Send Peppol Modal (simple confirmation, no recipients)
        window.openSendPeppolModal = function(invoiceId, invoiceType) {
            document.getElementById('peppol_invoice_id').value = invoiceId;
            document.getElementById('peppol_invoice_type').value = invoiceType || 'full';
            
            // Reset file input and selected files display
            const fileInput = document.getElementById('peppol_attachments');
            const selectedFilesDiv = document.getElementById('peppol_selected_files');
            const filesList = document.getElementById('peppol_files_list');
            if (fileInput) fileInput.value = '';
            if (selectedFilesDiv) selectedFilesDiv.classList.add('hidden');
            if (filesList) filesList.innerHTML = '';
            
            if (sendPeppolModal) sendPeppolModal.classList.remove('hidden');
        };
        
        // Handle file selection display for Peppol modal
        const peppolFileInput = document.getElementById('peppol_attachments');
        if (peppolFileInput) {
            peppolFileInput.addEventListener('change', function(e) {
                const files = Array.from(e.target.files);
                const selectedFilesDiv = document.getElementById('peppol_selected_files');
                const filesList = document.getElementById('peppol_files_list');
                
                if (files.length === 0) {
                    if (selectedFilesDiv) selectedFilesDiv.classList.add('hidden');
                    if (filesList) filesList.innerHTML = '';
                    return;
                }
                
                // Validate files
                const allowedExtensions = ['pdf', 'xlsx', 'xls', 'csv', 'jpg', 'jpeg', 'png'];
                const maxFileSize = 10 * 1024 * 1024; // 10MB
                const validFiles = [];
                const errors = [];
                
                files.forEach((file) => {
                    const extension = file.name.split('.').pop().toLowerCase();
                    const fileSize = file.size;
                    
                    if (!allowedExtensions.includes(extension)) {
                        errors.push(`${file.name}: Unsupported file type. Allowed: PDF, Excel, CSV, JPEG, PNG`);
                        return;
                    }
                    
                    if (fileSize > maxFileSize) {
                        errors.push(`${file.name}: File too large (max 10MB)`);
                        return;
                    }
                    
                    validFiles.push(file);
                });
                
                // Show errors if any
                if (errors.length > 0) {
                    alert('File validation errors:\n\n' + errors.join('\n'));
                    // Remove invalid files from input
                    const dataTransfer = new DataTransfer();
                    validFiles.forEach(file => dataTransfer.items.add(file));
                    e.target.files = dataTransfer.files;
                }
                
                // Display valid files
                if (validFiles.length === 0) {
                    if (selectedFilesDiv) selectedFilesDiv.classList.add('hidden');
                    if (filesList) filesList.innerHTML = '';
                    return;
                }
                
                if (selectedFilesDiv) selectedFilesDiv.classList.remove('hidden');
                if (filesList) {
                    filesList.innerHTML = '';
                    validFiles.forEach((file, index) => {
                        const li = document.createElement('li');
                        li.className = 'flex items-center justify-between bg-gray-50 px-2 py-1 rounded';
                        li.innerHTML = `
                            <span class="truncate flex-1">${file.name}</span>
                            <span class="text-gray-400 ml-2">${(file.size / 1024 / 1024).toFixed(2)} MB</span>
                        `;
                        filesList.appendChild(li);
                    });
                }
            });
        }

        // Combined Send Modal (Email + Peppol)
        const combinedSendModal = document.getElementById('combinedSendModal');
        const closeCombinedSendModalBtn = document.getElementById('closeCombinedSendModal');
        const cancelCombinedSendModalBtn = document.getElementById('cancelCombinedSendModal');
        const combinedSendForm = document.getElementById('combinedSendForm');
        const combinedSendEmailCheckbox = document.getElementById('combined_send_email');
        const combinedSendPeppolCheckbox = document.getElementById('combined_send_peppol');
        const combinedEmailSection = document.getElementById('combined_email_section');
        const combinedRecipientsList = document.getElementById('combinedRecipientsList');
        const combinedAttachmentsInput = document.getElementById('combined_attachments');
        const combinedSelectedFilesDiv = document.getElementById('combined_selected_files');
        const combinedFilesList = document.getElementById('combined_files_list');

        function toggleCombinedEmailSection() {
            if (!combinedEmailSection) return;
            if (combinedSendEmailCheckbox && combinedSendEmailCheckbox.checked) {
                combinedEmailSection.classList.remove('hidden');
            } else {
                combinedEmailSection.classList.add('hidden');
            }
        }

        if (combinedSendEmailCheckbox) {
            combinedSendEmailCheckbox.addEventListener('change', toggleCombinedEmailSection);
        }

        if (closeCombinedSendModalBtn) {
            closeCombinedSendModalBtn.addEventListener('click', () => {
                if (combinedSendModal) combinedSendModal.classList.add('hidden');
            });
        }
        if (cancelCombinedSendModalBtn) {
            cancelCombinedSendModalBtn.addEventListener('click', () => {
                if (combinedSendModal) combinedSendModal.classList.add('hidden');
            });
        }

        // Open Combined modal
        window.openCombinedSendModal = function(invoiceId, invoiceType) {
            const invoiceIdInput = document.getElementById('combined_invoice_id');
            const invoiceTypeInput = document.getElementById('combined_invoice_type');
            if (invoiceIdInput) invoiceIdInput.value = invoiceId;
            if (invoiceTypeInput) invoiceTypeInput.value = invoiceType || 'full';

            if (combinedSendEmailCheckbox) combinedSendEmailCheckbox.checked = false;
            if (combinedSendPeppolCheckbox) combinedSendPeppolCheckbox.checked = false;
            toggleCombinedEmailSection();

            if (combinedAttachmentsInput) {
                combinedAttachmentsInput.value = '';
            }
            if (combinedSelectedFilesDiv) combinedSelectedFilesDiv.classList.add('hidden');
            if (combinedFilesList) combinedFilesList.innerHTML = '';

            if (combinedRecipientsList) {
                combinedRecipientsList.innerHTML = '<div class="text-center text-gray-500">Loading...</div>';
            }

            let recipientsUrl = '';
            @if(Auth::user()->hasRole('Admin'))
                recipientsUrl = '/admin/invoice/' + invoiceId + '/recipients';
            @elseif(Auth::user()->hasRole('Responsable de projet'))
                recipientsUrl = '/Responsable_de_projet/invoice/' + invoiceId + '/recipients';
            @elseif(Auth::user()->hasRole('Employee'))
                recipientsUrl = '/employee/invoice/' + invoiceId + '/recipients';
            @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                recipientsUrl = '/Responsable_gestionnaire/invoice/' + invoiceId + '/recipients';
            @endif

            if (recipientsUrl && combinedRecipientsList) {
                fetch(recipientsUrl)
                    .then(response => response.json())
                    .then(data => {
                        combinedRecipientsList.innerHTML = '';
                        if (data.recipients && data.recipients.length > 0) {
                            data.recipients.forEach(recipient => {
                                const div = document.createElement('div');
                                div.className = 'mb-2 flex items-center';
                                div.innerHTML = `
                                    <input class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 mr-2" type="checkbox" name="combined_selected_recipients[]" value="${recipient.email}" id="combined-recipient-${recipient.email}" ${recipient.is_comptable === 1 ? 'checked' : ''}>
                                    <label class="text-sm text-gray-700" for="combined-recipient-${recipient.email}"><strong>${recipient.name}</strong> (${recipient.email})</label>
                                `;
                                combinedRecipientsList.appendChild(div);
                            });
                        } else {
                            combinedRecipientsList.innerHTML = '<p class="text-sm text-gray-500">No recipients found</p>';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        combinedRecipientsList.innerHTML = '<p class="text-sm text-red-600">Error loading recipients</p>';
                    });
            }

            if (combinedSendModal) combinedSendModal.classList.remove('hidden');
        };

        // Handle file selection display for combined modal
        if (combinedAttachmentsInput) {
            combinedAttachmentsInput.addEventListener('change', function(e) {
                const files = Array.from(e.target.files);
                if (!combinedSelectedFilesDiv || !combinedFilesList) return;

                if (files.length === 0) {
                    combinedSelectedFilesDiv.classList.add('hidden');
                    combinedFilesList.innerHTML = '';
                    return;
                }

                const allowedExtensions = ['pdf', 'xlsx', 'xls', 'csv', 'jpg', 'jpeg', 'png'];
                const maxFileSize = 10 * 1024 * 1024;
                const validFiles = [];
                const errors = [];

                files.forEach((file) => {
                    const extension = file.name.split('.').pop().toLowerCase();
                    const fileSize = file.size;

                    if (!allowedExtensions.includes(extension)) {
                        errors.push(`${file.name}: Unsupported file type. Allowed: PDF, Excel, CSV, JPEG, PNG`);
                        return;
                    }

                    if (fileSize > maxFileSize) {
                        errors.push(`${file.name}: File too large (max 10MB)`);
                        return;
                    }

                    validFiles.push(file);
                });

                if (errors.length > 0) {
                    alert('File validation errors:\n\n' + errors.join('\n'));
                    const dataTransfer = new DataTransfer();
                    validFiles.forEach(file => dataTransfer.items.add(file));
                    e.target.files = dataTransfer.files;
                }

                const finalFiles = validFiles.length > 0 ? validFiles : [];
                if (finalFiles.length === 0) {
                    combinedSelectedFilesDiv.classList.add('hidden');
                    combinedFilesList.innerHTML = '';
                    return;
                }

                combinedSelectedFilesDiv.classList.remove('hidden');
                combinedFilesList.innerHTML = '';
                finalFiles.forEach(file => {
                    const li = document.createElement('li');
                    li.className = 'flex items-center justify-between bg-gray-50 px-2 py-1 rounded';
                    li.innerHTML = `
                        <span class="truncate flex-1">${file.name}</span>
                        <span class="text-gray-400 ml-2">${(file.size / 1024 / 1024).toFixed(2)} MB</span>
                    `;
                    combinedFilesList.appendChild(li);
                });
            });
        }

        if (combinedSendForm) {
            combinedSendForm.addEventListener('submit', async function(e) {
                e.preventDefault();

                const sendEmail = combinedSendEmailCheckbox && combinedSendEmailCheckbox.checked;
                const sendPeppol = combinedSendPeppolCheckbox && combinedSendPeppolCheckbox.checked;

                if (!sendEmail && !sendPeppol) {
                    showMessage('Please select at least one sending option (Email or Peppol).', 'danger');
                    return;
                }

                const invoiceId = document.getElementById('combined_invoice_id')?.value;
                const invoiceType = document.getElementById('combined_invoice_type')?.value || 'full';
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                let attachmentsFiles = [];
                if (combinedAttachmentsInput) {
                    attachmentsFiles = Array.from(combinedAttachmentsInput.files);
                }

                try {
                    // First: send by email if requested
                    if (sendEmail) {
                        const selectedRecipients = Array.from(document.querySelectorAll('input[name="combined_selected_recipients[]"]:checked')).map(cb => cb.value);
                        if (selectedRecipients.length === 0) {
                            showMessage('Please select at least one email recipient.', 'danger');
                            return;
                        }

                        let emailActionUrl = '';
                        @if(Auth::user()->hasRole('Admin')) 
                            emailActionUrl = '{{ route("admin.invoice.sendInvoice", ":invoice_id") }}';
                        @elseif(Auth::user()->hasRole('Responsable de projet')) 
                            emailActionUrl = '{{ route("Responsable_de_projet.invoice.sendInvoice", ":invoice_id") }}';
                        @elseif(Auth::user()->hasRole('Employee')) 
                            emailActionUrl = '{{ route("employee.invoice.sendInvoice", ":invoice_id") }}';
                        @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire')) 
                            emailActionUrl = '{{ route("Responsable_gestionnaire.invoice.sendInvoice", ":invoice_id") }}';
                        @endif
                        emailActionUrl = emailActionUrl.replace(':invoice_id', invoiceId);

                        const emailFormData = new FormData();
                        selectedRecipients.forEach(recipient => {
                            emailFormData.append('selected_recipients[]', recipient);
                        });
                        attachmentsFiles.forEach(file => {
                            emailFormData.append('file[]', file);
                        });

                        const emailResponse = await fetch(emailActionUrl, {
                            method: 'POST',
                            body: emailFormData,
                            headers: { 'X-CSRF-TOKEN': csrfToken }
                        });

                        if (!emailResponse.ok) {
                            const text = await emailResponse.text();
                            try {
                                const data = JSON.parse(text);
                                if (!data.success) {
                                    showMessage('Error sending invoice by email: ' + (data.message || 'Unknown error'), 'danger');
                                    return;
                                }
                            } catch {
                                throw new Error('Server error while sending email: ' + emailResponse.status);
                            }
                        } else {
                            const data = await emailResponse.json();
                            if (!data.success) {
                                showMessage('Error sending invoice by email: ' + (data.message || 'Unknown error'), 'danger');
                                return;
                            }
                        }
                    }

                    // Second: send via Peppol if requested
                    if (sendPeppol) {
                        let peppolActionUrl = '';
                        @if(Auth::user()->hasRole('Admin')) 
                            peppolActionUrl = '{{ route("admin.billit.send-invoice-type", ["id" => ":invoice_id", "type" => ":invoice_type"]) }}';
                        @elseif(Auth::user()->hasRole('Responsable de projet')) 
                            peppolActionUrl = '{{ route("Responsable_de_projet.billit.send-invoice-type", ["id" => ":invoice_id", "type" => ":invoice_type"]) }}';
                        @elseif(Auth::user()->hasRole('Employee')) 
                            peppolActionUrl = '{{ route("employee.billit.send-invoice-type", ["id" => ":invoice_id", "type" => ":invoice_type"]) }}';
                        @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire')) 
                            peppolActionUrl = '{{ route("Responsable_gestionnaire.billit.send-invoice-type", ["id" => ":invoice_id", "type" => ":invoice_type"]) }}';
                        @endif
                        peppolActionUrl = peppolActionUrl.replace(':invoice_id', invoiceId).replace(':invoice_type', invoiceType) + '?channel=Peppol';

                        const peppolFormData = new FormData();
                        attachmentsFiles.forEach(file => {
                            peppolFormData.append('attachments[]', file);
                        });

                        const peppolResponse = await fetch(peppolActionUrl, {
                            method: 'POST',
                            body: peppolFormData,
                            headers: { 'X-CSRF-TOKEN': csrfToken }
                        });

                        if (!(peppolResponse.redirected || peppolResponse.ok)) {
                            const text = await peppolResponse.text();
                            try {
                                const data = JSON.parse(text);
                                if (!data.success) {
                                    showMessage('Error sending invoice via Peppol: ' + (data.message || 'Unknown error'), 'danger');
                                    return;
                                }
                            } catch {
                                showMessage('Error sending invoice via Peppol.', 'danger');
                                return;
                            }
                        }
                    }

                    if (combinedSendModal) combinedSendModal.classList.add('hidden');

                    if (sendEmail && sendPeppol) {
                        showMessage('Invoice sent successfully by email and via Peppol.', 'success');
                    } else if (sendEmail) {
                        showMessage('Invoice sent successfully by email.', 'success');
                    } else if (sendPeppol) {
                        showMessage('Invoice sent successfully via Peppol.', 'success');
                    }

                    setTimeout(() => window.location.reload(), 2000);
                } catch (error) {
                    console.error('Error:', error);
                    showMessage('An error occurred while sending the invoice. Please try again.', 'danger');
                }
            });
        }

        // Partial Credit Note Modal
        const partialCreditNoteModal = document.getElementById('partialCreditNoteModal');
        const closePartialCreditNoteModalBtn = document.getElementById('closePartialCreditNoteModal');
        const cancelPartialCreditNoteModalBtn = document.getElementById('cancelPartialCreditNoteModal');
        const partialCreditNoteForm = document.getElementById('partialCreditNoteForm');
        let currentPartialInvoiceId = null;
        let currentPartialTotalCost = 0;
        
        if (closePartialCreditNoteModalBtn) {
            closePartialCreditNoteModalBtn.addEventListener('click', () => {
                if (partialCreditNoteModal) partialCreditNoteModal.classList.add('hidden');
            });
        }
        if (cancelPartialCreditNoteModalBtn) {
            cancelPartialCreditNoteModalBtn.addEventListener('click', () => {
                if (partialCreditNoteModal) partialCreditNoteModal.classList.add('hidden');
            });
        }
        
        if (partialCreditNoteForm) {
            partialCreditNoteForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const amountInput = document.getElementById('partialAmount');
                const errorSpan = document.getElementById('partialAmountError');
                const amount = parseFloat(amountInput.value);
                
                errorSpan.classList.add('hidden');
                
                if (isNaN(amount) || amount <= 0) {
                    errorSpan.textContent = 'Please enter a valid amount greater than 0.';
                    errorSpan.classList.remove('hidden');
                    return;
                }
                if (amount > currentPartialTotalCost) {
                    errorSpan.textContent = 'Amount exceeds total cost.';
                    errorSpan.classList.remove('hidden');
                    return;
                }
                
                let url = '';
                @if(Auth::user()->hasRole('Responsable de projet'))
                    url = `{{ route('Responsable_de_projet.invoice.creditNote', '') }}/${currentPartialInvoiceId}?type=partial&amount=${amount}`;
                @elseif(Auth::user()->hasRole('Admin'))
                    url = `{{ route('admin.invoice.creditNote', '') }}/${currentPartialInvoiceId}?type=partial&amount=${amount}`;
                @elseif(Auth::user()->hasRole('Employee'))
                    url = `{{ route('employee.invoice.creditNote', '') }}/${currentPartialInvoiceId}?type=partial&amount=${amount}`;
                @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                    url = `{{ route('Responsable_gestionnaire.invoice.creditNote', '') }}/${currentPartialInvoiceId}?type=partial&amount=${amount}`;
                @endif
                window.location.href = url;
            });
        }
        
        window.openPartialCreditNoteModal = function(invoiceId, totalCost) {
            currentPartialInvoiceId = invoiceId;
            currentPartialTotalCost = parseFloat(totalCost);
            document.getElementById('partialAmount').value = '';
            document.getElementById('partialAmountError').classList.add('hidden');
            if (partialCreditNoteModal) partialCreditNoteModal.classList.remove('hidden');
        };
        
        // Partial Detailed Credit Note Modal
        const partialDetailedCreditNoteModal = document.getElementById('partialDetailedCreditNoteModal');
        const closePartialDetailedCreditNoteModalBtn = document.getElementById('closePartialDetailedCreditNoteModal');
        const cancelPartialDetailedCreditNoteModalBtn = document.getElementById('cancelPartialDetailedCreditNoteModal');
        const partialDetailedCreditNoteForm = document.getElementById('partialDetailedCreditNoteForm');
        let currentPartialDetailedInvoiceId = null;
        let currentPartialDetailedTotalCost = 0;
        
        if (closePartialDetailedCreditNoteModalBtn) {
            closePartialDetailedCreditNoteModalBtn.addEventListener('click', () => {
                if (partialDetailedCreditNoteModal) partialDetailedCreditNoteModal.classList.add('hidden');
            });
        }
        if (cancelPartialDetailedCreditNoteModalBtn) {
            cancelPartialDetailedCreditNoteModalBtn.addEventListener('click', () => {
                if (partialDetailedCreditNoteModal) partialDetailedCreditNoteModal.classList.add('hidden');
            });
        }
        
        if (partialDetailedCreditNoteForm) {
            partialDetailedCreditNoteForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const amountInput = document.getElementById('partialDetailedAmount');
                const errorSpan = document.getElementById('partialDetailedAmountError');
                const amount = parseFloat(amountInput.value);
                
                errorSpan.classList.add('hidden');
                
                if (isNaN(amount) || amount <= 0) {
                    errorSpan.textContent = 'Please enter a valid amount greater than 0.';
                    errorSpan.classList.remove('hidden');
                    return;
                }
                if (amount > currentPartialDetailedTotalCost) {
                    errorSpan.textContent = 'Amount exceeds total cost.';
                    errorSpan.classList.remove('hidden');
                    return;
                }
                
                let url = '';
                @if(Auth::user()->hasRole('Admin'))
                    url = '{{ route("admin.invoice.detailedCreditNote", ":id") }}?type=partial&amount=' + amount;
                @elseif(Auth::user()->hasRole('Responsable de projet'))
                    url = '{{ route("Responsable_de_projet.invoice.detailedCreditNote", ":id") }}?type=partial&amount=' + amount;
                @elseif(Auth::user()->hasRole('Employee'))
                    url = '{{ route("employee.invoice.detailedCreditNote", ":id") }}?type=partial&amount=' + amount;
                @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                    url = '{{ route("Responsable_gestionnaire.invoice.detailedCreditNote", ":id") }}?type=partial&amount=' + amount;
                @endif
                window.location.href = url.replace(':id', currentPartialDetailedInvoiceId);
            });
        }
        
        window.openPartialDetailedCreditNoteModal = function(invoiceId, totalCost) {
            currentPartialDetailedInvoiceId = invoiceId;
            currentPartialDetailedTotalCost = parseFloat(totalCost);
            document.getElementById('partialDetailedAmount').value = '';
            document.getElementById('partialDetailedAmountError').classList.add('hidden');
            if (partialDetailedCreditNoteModal) partialDetailedCreditNoteModal.classList.remove('hidden');
        };
        
        // Invoice Report Modal
        const invoiceReportModal = document.getElementById('invoiceReportModal');
        const openInvoiceReportModalBtn = document.getElementById('openInvoiceReportModal');
        const closeInvoiceReportModalBtn = document.getElementById('closeInvoiceReportModal');
        const cancelInvoiceReportModalBtn = document.getElementById('cancelInvoiceReportModal');
        const confirmInvoiceReportBtn = document.getElementById('confirmInvoiceReport');
        
        if (openInvoiceReportModalBtn) {
            openInvoiceReportModalBtn.addEventListener('click', function(e) {
                e.preventDefault();
                if (invoiceReportModal) invoiceReportModal.classList.remove('hidden');
            });
        }
        
        if (closeInvoiceReportModalBtn) {
            closeInvoiceReportModalBtn.addEventListener('click', () => {
                if (invoiceReportModal) invoiceReportModal.classList.add('hidden');
            });
        }
        if (cancelInvoiceReportModalBtn) {
            cancelInvoiceReportModalBtn.addEventListener('click', () => {
                if (invoiceReportModal) invoiceReportModal.classList.add('hidden');
            });
        }
        
        if (confirmInvoiceReportBtn) {
            confirmInvoiceReportBtn.addEventListener('click', function() {
                let baseUrl = '';
                @if(Auth::user()->hasRole('Responsable de projet')) baseUrl = "{{route('Responsable_de_projet.Invoicessrepports.downloadRepports')}}"; @endif
                @if(Auth::user()->hasRole('Admin')) baseUrl = "{{route('admin.Invoicessrepports.downloadRepports')}}"; @endif
                @if(Auth::user()->hasRole('Employee')) baseUrl = "{{route('employee.Invoicessrepports.downloadRepports')}}"; @endif
                @if(Auth::user()->hasRole('Responsable de projet et gestionnaire')) baseUrl = "{{route('Responsable_gestionnaire.Invoicessrepports.downloadRepports')}}"; @endif
                
                const startDate = document.getElementById('startDate')?.value || '';
                const endDate = document.getElementById('endDate')?.value || '';
                const selectedStatuses = Array.from(document.querySelectorAll('#invoiceReportModal input[type="checkbox"][id^="status_"]:checked')).map(x => x.value);
                const selectedTypes = Array.from(document.querySelectorAll('#invoiceReportModal input[type="checkbox"][id^="type_"]:checked')).map(x => x.value);
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
                if (invoiceReportModal) invoiceReportModal.classList.add('hidden');
            });
        }
        
        // Listen for modal open events
        window.addEventListener('open-modal', function(event) {
            const detail = event.detail;
            if (detail.id === 'sendInvoiceModal' && detail.invoiceId) {
                openSendInvoiceModal(detail.invoiceId);
            } else if (detail.id === 'sendPeppolModal' && detail.invoiceId) {
                openSendPeppolModal(detail.invoiceId, detail.invoiceType);
            } else if (detail.id === 'partialCreditNoteModal' && detail.invoiceId && detail.totalCost) {
                openPartialCreditNoteModal(detail.invoiceId, detail.totalCost);
            } else if (detail.id === 'partialDetailedCreditNoteModal' && detail.invoiceId && detail.totalCost) {
                openPartialDetailedCreditNoteModal(detail.invoiceId, detail.totalCost);
            } else if (detail === 'invoiceReportModal' || detail.id === 'invoiceReportModal') {
                if (invoiceReportModal) invoiceReportModal.classList.remove('hidden');
            }
        });
    });

    // [NEW] Filter by status function for cards
    function filterByStatus(status) {
        const paymentFilter = document.getElementById('paymentFilter');
        if (paymentFilter) {
            // Set the value
            paymentFilter.value = status;
            
            // Dispatch 'change' event so any listeners react
            const event = new Event('change');
            paymentFilter.dispatchEvent(event);
        }
    }

    // Fonction globale pour afficher les messages
    function showMessage(message, type) {
        const messageContainer = document.getElementById('messageContainer');
        const messageText = document.getElementById('messageText');
        const messageTitle = document.getElementById('messageTitle');
        let alertClasses = ''; 
        let titleText = '';
        
        if (type === 'success') {
            alertClasses = 'bg-green-100 border border-green-400 text-green-700'; 
            titleText = 'Success!';
        } else if (type === 'danger') {
            alertClasses = 'bg-red-100 border border-red-400 text-red-700'; 
            titleText = 'Error!';
        } else {
            alertClasses = 'bg-blue-100 border border-blue-400 text-blue-700'; 
            titleText = 'Info';
        }

        messageContainer.className = `mb-6 px-4 py-3 rounded relative ${alertClasses}`;
        messageTitle.textContent = titleText;
        messageText.textContent = message;
        messageContainer.style.display = 'block';
        setTimeout(() => { 
            if (messageContainer) { 
                messageContainer.style.display = 'none'; 
            } 
        }, 5000);
    }

    // Logique de filtre
    document.addEventListener("DOMContentLoaded", function() {
        const clientFilter = document.getElementById('clientFilter');
        const projectFilter = document.getElementById('projectFilter');
        const paymentFilter = document.getElementById('paymentFilter');
        const typeFilter = document.getElementById('typeFilter');

        if (clientFilter) clientFilter.addEventListener('change', filterTable);
        if (projectFilter) projectFilter.addEventListener('change', filterTable);
        if (paymentFilter) paymentFilter.addEventListener('change', filterTable);
        if (typeFilter) typeFilter.addEventListener('change', filterTable);

        function filterTable() {
            const clientValue = clientFilter ? clientFilter.value.trim() : '';
            const projectValue = projectFilter ? projectFilter.value.trim() : '';
            const paymentValue = paymentFilter ? paymentFilter.value.trim() : '';
            const typeValue = typeFilter ? typeFilter.value.trim().toLowerCase() : '';

            const rows = document.querySelectorAll('#invoicesTable tbody tr');
            let hasVisibleRows = false;
            
            rows.forEach(row => {
                if (row.children.length === 1 && row.children[0].getAttribute('colspan') === '8') {
                    row.style.display = 'none';
                    return;
                }

                const clientName = row.querySelector('td:nth-child(4)')?.textContent.trim() || '';
                const projectName = row.querySelector('td:nth-child(2)')?.textContent.trim() || '';
                
                // [NEW] Overdue detection for filtering
                const isOverdueRow = row.querySelector('.invoice-link-overdue') !== null;
                
                const paymentButton = row.querySelector('.payment-button');
                let paymentStatus = '';
                if (paymentButton) {
                    // Check text content instead of class
                    const buttonText = paymentButton.textContent.trim();
                    if (buttonText === 'Paid') {
                        paymentStatus = 'Paid';
                    } else if (buttonText === 'Unpaid') {
                        paymentStatus = 'Unpaid';
                    }
                }

                const invoiceTypeCell = row.querySelector('td:nth-child(5)')?.textContent.trim().toLowerCase() || '';
                const invoiceType = invoiceTypeCell.split('-')[0].trim();

                const clientMatch = clientValue === '' || clientName === clientValue;
                const projectMatch = projectValue === '' || projectName.toUpperCase() === projectValue.toUpperCase();
                
                // [MODIFIED] Enhanced Payment Matching Logic
                let paymentMatch = false;
                if (paymentValue === '') {
                    paymentMatch = true;
                } else if (paymentValue === 'Overdue') {
                    paymentMatch = isOverdueRow;
                } else {
                    paymentMatch = paymentStatus === paymentValue;
                }

                const typeMatch = typeValue === '' || invoiceType === typeValue;

                if (clientMatch && projectMatch && paymentMatch && typeMatch) {
                    row.style.display = '';
                    hasVisibleRows = true;
                } else {
                    row.style.display = 'none';
                }
            });

            const noInvoicesRow = document.querySelector('#invoicesTable tbody tr td[colspan="8"]');
            if (noInvoicesRow) {
                noInvoicesRow.parentElement.style.display = hasVisibleRows ? 'none' : '';
            }
        }
        filterTable();
    });
</script>
</body>
</html>
