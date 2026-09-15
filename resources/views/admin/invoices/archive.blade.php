<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.Invoices Archive - DalyTrac') }}</title>
    
    {{-- 1. ONLY LOAD TAILWIND (No Bootstrap) --}}
    @vite('resources/css/app.css') 
    @livewireStyles
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <style>
        /* Custom select style */
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
        .credit-note-row { background-color: #fffbeb; }
        .credit-note-text { color: #d97706; font-weight: 600; }
        [x-cloak] { display: none !important; }

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

        /* Mobile/tablet: show invoice rows as modern 2-col cards (no horizontal scroll) */
        @media (max-width: 1024px) {
            .table-responsive {
                overflow-x: visible !important;
            }

            #invoicesTable thead {
                display: none;
            }

            #invoicesTable tbody {
                display: block;
                width: 100%;
            }

            #invoicesTable tbody tr {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 10px;
                margin-bottom: 16px;
                padding: 12px;
                border: 1px solid rgba(148, 163, 184, 0.55);
                border-radius: 16px;
                background: linear-gradient(180deg, rgba(255,255,255,0.95), rgba(248,250,252,0.92));
                box-shadow: 0 18px 45px rgba(15,23,42,0.10), 0 3px 10px rgba(15,23,42,0.08);
                transition: transform 180ms ease, box-shadow 180ms ease;
            }

            #invoicesTable tbody td {
                display: block;
                width: 100%;
                padding: 10px 12px !important;
                border: 1px solid rgba(226, 232, 240, 0.9);
                border-radius: 12px;
                background: rgba(255,255,255,0.78);
                text-align: left !important;
                white-space: normal !important;
                word-break: break-word;
            }

            #invoicesTable tbody td[data-span="2"] {
                grid-column: span 2 / span 2;
            }

            #invoicesTable tbody td[data-label]::before {
                content: attr(data-label);
                display: block;
                font-size: 11px;
                font-weight: 700;
                letter-spacing: .06em;
                text-transform: uppercase;
                color: #64748b; /* slate-500 */
                margin-bottom: 6px;
            }

            /* Mobile accordion: collapsed invoice shows only invoice number */
            #invoicesTable tbody tr.invoice-row.is-collapsed td:not(.invoice-toggle-cell) {
                display: none !important;
            }
            #invoicesTable tbody tr.invoice-row td.invoice-toggle-cell {
                cursor: pointer;
                user-select: none;
            }
            /* We render the "Invoice N°" label inline in markup for this cell */
            #invoicesTable tbody tr.invoice-row td.invoice-toggle-cell[data-label]::before {
                content: none !important;
            }
            #invoicesTable tbody tr.invoice-row td.invoice-toggle-cell .invoice-chevron {
                transition: transform 180ms ease;
            }
            #invoicesTable tbody tr.invoice-row[aria-expanded="true"] td.invoice-toggle-cell .invoice-chevron {
                transform: rotate(180deg);
            }
        }
    </style>
</head>

<body class="bg-gray-100 flex h-screen overflow-hidden font-sans" x-data>

    {{-- Sidebar --}}
    @if(Auth::user()->hasRole('Admin'))
        <x-dashboard.sidebar />
    @else
        <x-employees.sidebar />
    @endif

    <div class="flex flex-col flex-1 overflow-y-auto">
        {{-- Navbar --}}
        <x-dashboard.navbar />

        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-4 sm:p-6 lg:p-8 space-y-8">
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4"> 

                {{-- Session Message --}}
                @if(session('message'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative w-full" role="alert">
                    <strong class="font-bold">{{ __('messages.Success!') }}</strong>
                    <span class="block sm:inline">{{ session('message') }}</span>
                    <button type="button" onclick="this.parentElement.style.display='none';" class="absolute top-0 bottom-0 right-0 px-4 py-3 font-bold text-xl" aria-label="Close">&times;</button>
                </div>
                @endif
                
                @php
                    // Route Logic ... (kept exactly as your original)
                    $returnRoute = '';
                    if(Auth::user()->hasRole('Admin')) $returnRoute = route('admin.invoices.invoicehistory');
                    elseif(Auth::user()->hasRole('Responsable de projet')) $returnRoute = route('Responsable_de_projet.invoices.invoicehistory');
                    elseif(Auth::user()->hasRole('Employee')) $returnRoute = route('employee.invoices.invoicehistory');
                    elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire')) $returnRoute = route('Responsable_gestionnaire.invoices.invoicehistory');
                    
                    $totalPaid = App\Models\invoices_history::where('entreprise_id', Auth::user()->entreprise_id)->where('Payment_status', 1)->where('archived', 1)->sum('total_ttc');
                    $totaltva = App\Models\invoices_history::where('entreprise_id', Auth::user()->entreprise_id)->where('archived', 1)->sum('total_tva');
                @endphp

                {{-- Card --}}
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden w-full">
                    <div class="p-4 sm:p-6 border-b border-gray-200">
                        <div class="flex flex-col sm:flex-row justify-between items-start gap-4">
                            <div><h2 class="text-lg font-semibold text-gray-900">{{ __('messages.Invoices Archive') }}</h2></div>
                            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                                <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4 text-sm">
                                    <div>
                                        <span class="font-medium text-gray-700">{{ __('messages.TOTAL PAID') }} :</span>
                                        <span class="font-bold text-green-600">€{{ number_format($totalPaid, 2, ',', ' ') }}</span>
                                    </div>
                                    <span class="hidden lg:inline text-gray-300">|</span>
                                    <div>
                                        <span class="font-medium text-gray-700">{{ __('messages.TOTAL TVA') }} :</span>
                                        <span class="font-bold text-gray-800">€{{ number_format($totaltva, 2, ',', ' ') }}</span>
                                    </div>
                                </div>
                                <a class="inline-flex justify-center bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-lg text-sm shadow-sm w-full sm:w-auto" href="{{ $returnRoute }}">
                                    {{ __('messages.Return Back') }}
                                </a>
                            </div>
                        </div>
                        
                        {{-- Filters --}}
                        <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Client') }} :</label>
                                <select id="clientFilter" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3 form-select-custom">
                                    <option value="">{{ __('messages.All Clients') }}</option>
                                    @foreach($clients as $client) <option value="{{$client->name}}">{{$client->name}}</option> @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Project') }} :</label>
                                <select id="projectFilter" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3 form-select-custom">
                                    <option value="">{{ __('messages.All Projects') }}</option>
                                    @foreach($projects as $project) <option value="{{$project->name}}">{{strtoupper($project->name)}}</option> @endforeach
                                </select>
                            </div>                           
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Filter by Type') }} :</label>
                                <select id="typeFilter" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3 form-select-custom">
                                    <option value="">{{ __('messages.All Invoices') }}</option>
                                    <option value="partial">{{ __('messages.Partial Invoice') }}</option>
                                    <option value="percentage">{{ __('messages.Percentage Invoice') }}</option>
                                    <option value="period">{{ __('messages.Period Invoice') }}</option>
                                    <option value="detailed">{{ __('messages.Detailed Invoice') }}</option>
                                    <option value="full">{{ __('messages.Full Invoice') }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Sort by Date') }} :</label>
                                <select id="dateSort" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3 form-select-custom">
                                    <option value="">{{ __('messages.Default') ?? 'Default' }}</option>
                                    <option value="desc">{{ __('messages.Descending') ?? 'Descending' }}</option>
                                    <option value="asc">{{ __('messages.Ascending') ?? 'Ascending' }}</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Table --}}
                    <div class="table-responsive overflow-x-auto" style="-webkit-overflow-scrolling: touch;">
                        <table class="w-full text-left divide-y divide-gray-200 table table-auto" id="invoicesTable">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-4 py-3 text-left text-sm font-semibold text-gray-600 uppercase whitespace-nowrap">{{ __('messages.Invoice N°') }}</th>
                                    <th scope="col" class="px-4 py-3 text-left text-sm font-semibold text-gray-600 uppercase whitespace-nowrap">{{ __('messages.Project Name') }}</th>
                                    <th scope="col" class="px-4 py-3 text-left text-sm font-semibold text-gray-600 uppercase whitespace-nowrap">{{ __('messages.VAT') }}</th>
                                    <th scope="col" class="px-4 py-3 text-left text-sm font-semibold text-gray-600 uppercase whitespace-nowrap">{{ __('messages.Client') }}</th>
                                    <th scope="col" class="px-4 py-3 text-left text-sm font-semibold text-gray-600 uppercase whitespace-nowrap">{{ __('messages.Type') }}</th>
                                    <th scope="col" class="px-4 py-3 text-left text-sm font-semibold text-gray-600 uppercase whitespace-nowrap">{{ __('messages.Created at') }}</th>
                                    <th scope="col" class="px-4 py-3 text-left text-sm font-semibold text-gray-600 uppercase whitespace-nowrap">{{ __('messages.Payment Date') }}</th>
                                    <th scope="col" class="px-4 py-3 text-left text-sm font-semibold text-gray-600 uppercase whitespace-nowrap">{{ __('messages.Total Cost') }}</th>
                                    <th scope="col" class="px-4 py-3 text-center text-sm font-semibold text-gray-600 uppercase whitespace-nowrap">{{ __('messages.Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($invoices as $invoice)
                                <tr
                                    data-payment-status="{{ $invoice->Payment_status == 1 ? 'paid' : ($invoice->Payment_status == -1 ? 'credit_note' : 'unpaid') }}"
                                    @class([
                                    'invoice-row' => true,
                                    'credit-note-row' => $invoice->Payment_status == -1,
                                    'bg-green-50 hover:bg-green-100' => $invoice->Payment_status == 1,
                                ])
                                    data-invoice-row
                                    aria-expanded="true"
                                >
                                    <td data-label="{{ __('messages.Invoice N°') }}" data-span="2" class="px-6 py-4 whitespace-nowrap text-sm font-medium invoice-toggle-cell" data-invoice-toggle>
                                        @php
                                            $invoiceRoute = '#';
                                            if(Auth::user()->hasRole('Admin')) $invoiceRoute = route('admin.invoices.servePdf', ['invoiceName' => $invoice->id]);
                                            elseif(Auth::user()->hasRole('Responsable de projet')) $invoiceRoute = route('Responsable_de_projet.invoices.servePdf', ['invoiceName' => $invoice->id]);
                                            // ... add other roles
                                        @endphp
                                        {{-- Desktop: keep original simple link layout --}}
                                        <a href="{{ $invoiceRoute }}" target="_blank" class="text-blue-600 hover:text-blue-800 hidden lg:inline">
                                            {{$entreprise_infos->invoice_prefix}}-{{date('ym', strtotime($invoice->created_at))}}{{$invoice->number}}
                                        </a>

                                        {{-- Mobile: accordion header (label + number inline + chevron) --}}
                                        <div class="flex items-start justify-between gap-3 lg:hidden">
                                            <div class="flex items-start gap-2 min-w-0">
                                                <span class="text-[11px] font-bold tracking-[.06em] uppercase text-slate-500 whitespace-nowrap">
                                                    {{ __('messages.Invoice N°') }}
                                                </span>
                                                <a href="{{ $invoiceRoute }}" target="_blank" class="text-blue-600 hover:text-blue-800 truncate">
                                                    {{$entreprise_infos->invoice_prefix}}-{{date('ym', strtotime($invoice->created_at))}}{{$invoice->number}}
                                                </a>
                                            </div>
                                            <span class="shrink-0 mt-0.5 text-slate-400">
                                                <svg class="invoice-chevron w-5 h-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                    <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                            </span>
                                        </div>
                                    </td>
                                    <td data-label="{{ __('messages.Project Name') }}" class="px-6 py-4 text-sm text-gray-700">{{ strtoupper($invoice->project_name) }}</td>
                                    <td data-label="{{ __('messages.VAT') }}" class="px-6 py-4 text-sm text-gray-700">{{ \App\Models\Project::find($invoice->project_id)->tva ?? 'N/A' }}%</td>
                                    <td data-label="{{ __('messages.Client') }}" class="px-6 py-4 text-sm text-gray-700">{{ \App\Models\Client::find($invoice->client_id)->name ?? 'N/A' }}</td>
                                    <td data-label="{{ __('messages.Type') }}" class="px-6 py-4 text-sm text-gray-700 capitalize">{{ $invoice->type }}</td>
                                    <td data-label="{{ __('messages.Created at') }}" class="px-6 py-4 text-sm text-gray-700">{{ \Carbon\Carbon::parse($invoice->created_at)->format('Y-m-d') }}</td>
                                    <td data-label="{{ __('messages.Payment Date') }}" class="px-6 py-4 text-sm text-gray-700">
                                        @if($invoice->Payment_status == 1 && !empty($invoice->payment_date))
                                            {{ \Carbon\Carbon::parse($invoice->payment_date)->format('Y-m-d') }}
                                        @else <span class="text-gray-400">-</span> @endif
                                    </td>
                                    <td data-label="{{ __('messages.Total Cost') }}" data-span="2" class="px-6 py-4 text-sm font-bold text-green-600">€{{ number_format($invoice->total_ttc, 2, ',', ' ') }}</td>
                                    <td data-label="{{ __('messages.Actions') }}" data-span="2" class="px-6 py-4 text-sm font-medium">
                                        @php      
                                        $isPaid = $invoice->Payment_status == 1;
                                        $isCreditNote = $invoice->Payment_status == -1;
                                        // ... all your route logic ...
                                        $activateRoute = ''; 
                                        $deactivateRoute = '';
                                        $unarchiveRoute = '';
                                        $reminderRouteBase = '';
                                        $creditNoteRoute = '';
                                        $detailedCreditNoteRoute = '';
                                        if(Auth::user()->hasRole('Admin')) {
                                            $deactivateRoute = route('admin.invoice.deactivate', $invoice->id);
                                            $activateRoute = route('admin.invoice.activate', $invoice->id);
                                            $unarchiveRoute = route('admin.invoice.unarchive', $invoice->id);
                                            $reminderRouteBase = 'admin.invoice.sendManualremind';
                                            $creditNoteRoute = 'admin.invoice.creditNote';
                                            $detailedCreditNoteRoute = 'admin.invoice.detailedCreditNote';
                                        } elseif(Auth::user()->hasRole('Responsable de projet')) {
                                            $deactivateRoute = route('Responsable_de_projet.invoice.deactivate', $invoice->id);
                                            $activateRoute = route('Responsable_de_projet.invoice.activate', $invoice->id);
                                            $unarchiveRoute = route('Responsable_de_projet.invoice.unarchive', $invoice->id);
                                            $reminderRouteBase = 'Responsable_de_projet.invoice.sendManualremind';
                                            $creditNoteRoute = 'Responsable_de_projet.invoice.creditNote';
                                            $detailedCreditNoteRoute = 'Responsable_de_projet.invoice.detailedCreditNote';
                                        }
                                        // ...   
                                        @endphp
                                        
                                        <div class="flex flex-col items-center gap-1"> 
                                            @if($isCreditNote)
                                            <span class="credit-note-text px-3 py-1 text-xs font-semibold rounded-full">{{ __('messages.Credit Note') }}</span>
                                            @elseif($isPaid)
                                            <form action="{{ $deactivateRoute }}" method="post" onsubmit="return confirm('{{ __('messages.Are you sure?') }}');" class="inline-block w-full">
                                                @csrf <button type="submit" class="payment-button px-3 py-1 bg-green-600 hover:bg-green-700 text-white rounded-lg text-xs font-medium w-full">{{ __('messages.Paid') }}</button>
                                            </form>
                                            @else
                                            <form action="{{ $activateRoute }}" method="post" onsubmit="return confirm('{{ __('messages.Are you sure?') }}');" class="inline-block w-full">
                                                @csrf <button type="submit" class="payment-button px-3 py-1 bg-red-600 hover:bg-red-700 text-white rounded-lg text-xs font-medium w-full">{{ __('messages.Unpaid') }}</button>
                                            </form>
                                            @endif

                                            {{-- Unarchive button --}}
                                            <form action="{{ $unarchiveRoute }}" method="post" onsubmit="return confirm('{{ __('messages.Unarchive this invoice?') }}');" class="inline-block w-full mt-1">
                                                @csrf
                                                <button type="submit" class="px-3 py-1 bg-white border border-gray-300 hover:bg-gray-100 text-gray-700 rounded-lg text-xs font-medium w-full">
                                                    {{ __('messages.Unarchive') }}
                                                </button>
                                            </form>
        
                                            <button type="button" class="px-3 py-1 bg-gray-700 hover:bg-gray-800 text-white rounded-lg text-xs font-medium w-full"
                                                @click="$dispatch('open-send-modal', { invoiceId: {{ $invoice->id }} })">
                                                {{ $invoice->sentInvoice == 0 ? __('messages.Send') : __('messages.Resend') }}
                                            </button>
                                        @if(!$isPaid && !$isCreditNote && Auth::user()->can('Send invoice'))
                                        <div class="inline-block relative w-full" x-data="{ open: false }" @click.outside="open = false">
                                            <button @click="open = !open" class="px-3 py-1 bg-yellow-500 hover:bg-yellow-600 text-white rounded-lg text-xs font-medium w-full">
                                                {{ __('messages.Reminder') }} <svg class="inline-block w-4 h-4 ml-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                                            </button>
                                            <ul x-show="open" x-transition class="absolute z-10 right-0 w-48 bg-white rounded-md shadow-lg py-1 ring-1 ring-black ring-opacity-5" style="display: none;">
                                                <li><a href="{{ route($reminderRouteBase, ['id' => $invoice->id, 'reminder' => 1]) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">{{ __('messages.Reminder 1') }}</a></li>
                                                <li><a href="{{ route($reminderRouteBase, ['id' => $invoice->id, 'reminder' => 2]) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">{{ __('messages.Reminder 2') }}</a></li>
                                                <li><a href="{{ route($reminderRouteBase, ['id' => $invoice->id, 'reminder' => 3]) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">{{ __('messages.Reminder 3') }}</a></li>
                                            </ul>        
                                        </div>
                                        @endif
                                        <div class="inline-block relative w-full" x-data="{ open: false }" @click.outside="open = false">
                                            <button @click="open = !open" class="px-3 py-1 bg-purple-600 hover:bg-purple-700 text-white rounded-lg text-xs font-medium w-full">
                                                {{ __('messages.Credit Note') }} <svg class="inline-block w-4 h-4 ml-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                                            </button>
                                            <ul x-show="open" x-transition class="absolute z-10 right-0 w-48 bg-white rounded-md shadow-lg py-1 ring-1 ring-black ring-opacity-5" style="display: none;">
                                                <li>
                                                    <a href="#" @click.prevent="$dispatch('open-partial-modal', { invoiceId: {{ $invoice->id }}, totalCost: {{ $invoice->total_ttc }} }); open = false" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                        {{ __('messages.Partial') }}
                                                    </a>
                                                </li>
                                                @if($invoice->type == 'Detailed')
                                                    <li><a href="{{ route($detailedCreditNoteRoute, $invoice->id) }}?type=full" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">{{ __('messages.Full') }}</a></li>
                                                @else
                                                    <li><a href="{{ route($creditNoteRoute, $invoice->id) }}?type=full" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">{{ __('messages.Full') }}</a></li>
                                                @endif
                                            </ul>
                                        </div>                    
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    {{-- 1. PARTIAL CREDIT NOTE MODAL --}}
    <div x-data="{ show: false, invoiceId: null, totalCost: 0, amount: '' }"
         @open-partial-modal.window="show = true; invoiceId = $event.detail.invoiceId; totalCost = $event.detail.totalCost; amount = ''"
         x-show="show" x-cloak
         class="relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        
        <div x-show="show" x-transition.opacity class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div x-show="show" @click.away="show = false"
                     x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                    
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg font-medium leading-6 text-gray-900" id="modal-title">{{ __('messages.Partial Credit Note') }}</h3>
                        <div class="mt-2">
                            <label class="block text-sm font-medium text-gray-700">{{ __('messages.Enter Amount:') }}</label>
                            <input type="number" x-model="amount" step="0.0001" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2 border">
                            <p x-show="parseFloat(amount) > totalCost" class="mt-2 text-sm text-red-600">{{ __('messages.Amount exceeds total cost.') }}</p>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                        <button type="button" @click="submitPartial()" class="inline-flex w-full justify-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-base font-medium text-white shadow-sm hover:bg-blue-700 sm:ml-3 sm:w-auto sm:text-sm">
                            {{ __('messages.Submit') }}
                        </button>
                        <button type="button" @click="show = false" class="mt-3 inline-flex w-full justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-base font-medium text-gray-700 shadow-sm hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            {{ __('messages.Cancel') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 2. SEND INVOICE MODAL --}}
    <div x-data="{ show: false, invoiceId: null }"
         @open-send-modal.window="show = true; invoiceId = $event.detail.invoiceId; updateFormAction(invoiceId)"
         x-show="show" x-cloak
         class="relative z-50">
        
        <div x-show="show" x-transition.opacity class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div x-show="show" @click.away="show = false"
                     x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                    
                    <form id="sendInvoiceFormAlpine" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="text-lg font-medium leading-6 text-gray-900">{{ __('messages.Send Invoice') }}</h3>
                            <div class="mt-4">
                                <label class="block text-sm font-medium text-gray-700">{{ __('messages.Upload Additional Files (Optional):') }}</label>
                                <input type="file" name="file[]" multiple class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                            <button type="submit" class="inline-flex w-full justify-center rounded-md border border-transparent bg-green-600 px-4 py-2 text-base font-medium text-white shadow-sm hover:bg-green-700 sm:ml-3 sm:w-auto sm:text-sm">
                                {{ __('messages.Send') }}
                            </button>
                            <button type="button" @click="show = false" class="mt-3 inline-flex w-full justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-base font-medium text-gray-700 shadow-sm hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                {{ __('messages.Cancel') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @livewireScripts
    @include('dashboard_layouts.scripts')
    <script src="//unpkg.com/alpinejs" defer></script>

<script>
    // 1. Filter & Sort Logic
    document.addEventListener("DOMContentLoaded", function() {
        const filters = ['clientFilter', 'projectFilter', 'paymentFilter', 'typeFilter', 'dateSort'];
        const elements = filters.map(id => document.getElementById(id)).filter(Boolean);
        
        elements.forEach(el => el.addEventListener('change', filterAndSortTable));

        function filterAndSortTable() {
            const getLowerValue = (id) => {
                const el = document.getElementById(id);
                return el ? (el.value || '').toLowerCase() : '';
            };
            const vals = {
                client: getLowerValue('clientFilter'),
                project: getLowerValue('projectFilter'),
                payment: getLowerValue('paymentFilter'),
                type: getLowerValue('typeFilter'),
                sort: (document.getElementById('dateSort')?.value || '')
            };

            const tbody = document.querySelector('#invoicesTable tbody');
            const rows = Array.from(tbody.querySelectorAll('tr')); 

            rows.forEach(row => {
                if(row.cells.length < 2) return;
                
                const txt = {
                    client: row.querySelector('td:nth-child(4)')?.textContent.trim().toLowerCase() || '',
                    project: row.querySelector('td:nth-child(2)')?.textContent.trim().toLowerCase() || '',
                    type: row.querySelector('td:nth-child(5)')?.textContent.trim().toLowerCase() || '',
                    status: (row.dataset.paymentStatus || '').toLowerCase()
                };

                const matches = 
                    (vals.client === '' || txt.client.includes(vals.client)) &&
                    (vals.project === '' || txt.project.includes(vals.project)) &&
                    (vals.payment === '' || txt.status === vals.payment) &&
                    (vals.type === '' || txt.type.includes(vals.type));

                row.style.display = matches ? '' : 'none';
            });

            if (vals.sort) {
                rows.sort((a, b) => {
                    let dA = a.querySelector('td:nth-child(7)')?.textContent.trim();
                    let dB = b.querySelector('td:nth-child(7)')?.textContent.trim();
                    if (!dA || dA === '-') dA = '0000-00-00';
                    if (!dB || dB === '-') dB = '0000-00-00';
                    return vals.sort === 'asc' ? dA.localeCompare(dB) : dB.localeCompare(dA);
                });
                rows.forEach(r => tbody.appendChild(r));
            }
        }

        // Run once on page load
        filterAndSortTable();
    });

    // 2. Alpine Logic Functions (accessible globally)
    function updateFormAction(id) {
        var form = document.getElementById('sendInvoiceFormAlpine');
        var actionUrl = '';
        @if(Auth::user()->hasRole('Admin')) actionUrl = '{{ route("admin.invoice.sendInvoice", ":id") }}';
        @elseif(Auth::user()->hasRole('Responsable de projet')) actionUrl = '{{ route("Responsable_de_projet.invoice.sendInvoice", ":id") }}';
        @elseif(Auth::user()->hasRole('Employee')) actionUrl = '{{ route("employee.invoice.sendInvoice", ":id") }}';
        @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire')) actionUrl = '{{ route("Responsable_gestionnaire.invoice.sendInvoice", ":id") }}';
        @endif
        form.action = actionUrl.replace(':id', id);
    }

    // Alpine Scope hack for submitting from function
    function submitPartial() {
        const el = document.querySelector('[x-data*="open-partial-modal"]');
        const data = Alpine.$data(el);
        
        if(parseFloat(data.amount) > data.totalCost) return; 

        var url = '';
        @if(Auth::user()->hasRole('Admin')) url = `{{ route('admin.invoice.creditNote', '') }}/${data.invoiceId}?type=partial&amount=${data.amount}`;
        @elseif(Auth::user()->hasRole('Responsable de projet')) url = `{{ route('Responsable_de_projet.invoice.creditNote', '') }}/${data.invoiceId}?type=partial&amount=${data.amount}`;
        // ... others
        @endif

        if(url) window.location.href = url;
    }

    // Mobile accordion per invoice row (show only invoice number when collapsed)
    document.addEventListener('DOMContentLoaded', function () {
        const mql = window.matchMedia('(max-width: 1024px)');

        function setInvoiceRowCollapsed(row, collapsed) {
            if (!row) return;
            row.classList.toggle('is-collapsed', collapsed);
            row.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
        }

        function initInvoiceRowAccordions() {
            const rows = document.querySelectorAll('#invoicesTable tbody tr.invoice-row');
            const isMobile = mql.matches;
            rows.forEach(row => setInvoiceRowCollapsed(row, isMobile));
        }

        document.addEventListener('click', function (e) {
            const toggleCell = e.target.closest('[data-invoice-toggle]');
            if (!toggleCell) return;

            // Let normal link clicks open the PDF without toggling.
            if (e.target.closest('a')) return;

            const row = toggleCell.closest('tr.invoice-row');
            if (!row) return;

            const collapsed = row.classList.contains('is-collapsed');
            setInvoiceRowCollapsed(row, !collapsed);
        });

        if (mql.addEventListener) mql.addEventListener('change', initInvoiceRowAccordions);
        else if (mql.addListener) mql.addListener(initInvoiceRowAccordions);

        initInvoiceRowAccordions();
    });
</script>
</body>
</html>
