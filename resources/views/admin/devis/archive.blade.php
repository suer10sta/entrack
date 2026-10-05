<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.Archived Devis - DalyTrac') }}</title>
    
    @vite('resources/css/app.css') 
    @livewireStyles
    @include('admin.devis.partials.dashboardStyles')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <script src="//unpkg.com/alpinejs" defer></script>

    <style>
        /* Custom Select Styling */
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

        /* Copied from Invoice file for table consistency */
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
        .table td, .table th {
            padding: 0.75rem;
        }
        
        /* Link styling */
         .devis-link-normal {
             color: #2563eb; /* blue-600 */
             font-weight: 500; /* medium */
        }
         .devis-link-normal:hover {
             color: #1d4ed8; /* blue-800 */
             text-decoration: underline;
         }

        /* Mobile: show devis rows as modern 2-col cards (no horizontal scroll) */
        @media (max-width: 1024px) {
            .table-responsive {
                overflow-x: visible !important;
            }

            #devisTable thead {
                display: none;
            }

            #devisTable tbody {
                display: block;
                width: 100%;
            }

            #devisTable tbody tr {
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

            #devisTable tbody td {
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

            #devisTable tbody td[data-span="2"] {
                grid-column: span 2 / span 2;
            }

            #devisTable tbody td[data-label]::before {
                content: attr(data-label);
                display: block;
                font-size: 11px;
                font-weight: 700;
                letter-spacing: .06em;
                text-transform: uppercase;
                color: #64748b; /* slate-500 */
                margin-bottom: 6px;
            }

            /* Mobile accordion: collapsed row shows only devis number */
            #devisTable tbody tr.devis-row.is-collapsed td:not(.devis-toggle-cell) {
                display: none !important;
            }
            #devisTable tbody tr.devis-row td.devis-toggle-cell {
                cursor: pointer;
                user-select: none;
            }
            /* We render the label inline in markup for this cell */
            #devisTable tbody tr.devis-row td.devis-toggle-cell[data-label]::before {
                content: none !important;
            }
            #devisTable tbody tr.devis-row td.devis-toggle-cell .devis-chevron {
                transition: transform 180ms ease;
            }
            #devisTable tbody tr.devis-row[aria-expanded="true"] td.devis-toggle-cell .devis-chevron {
                transform: rotate(180deg);
            }
        }
    </style>
</head>

<body class="devis-dashboard-page dt-app-shell bg-[#f8fafc] text-slate-900 antialiased flex h-screen overflow-hidden" x-data>

    {{-- Sidebar Component --}}
    @if(Auth::user()->hasRole('Admin'))
        <x-dashboard.sidebar />
    @else
        <x-employees.sidebar />
    @endif

    {{-- Main Wrapper (Navbar + Content) --}}
    <div class="flex flex-col flex-1 overflow-y-auto">

        {{-- Navbar Component --}}
        <x-dashboard.navbar />

        {{-- Main Content Area --}}
        <main class="flex-1 min-w-0 overflow-x-hidden overflow-y-auto bg-[#f8fafc] px-3 py-4 sm:px-5 sm:py-5 lg:px-16 lg:py-7 space-y-5">

            {{-- 1. Header Section --}}
            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3">
                <div>
                    <p class="dt-overline mb-0.5">{{ __('messages.Devis History') }}</p>
                    <h1 class="text-[18px] sm:text-[20px] lg:text-[22px] font-bold text-[#0B2638] leading-tight">{{ __('messages.Archived Devis') }}</h1>
                </div>
                
                @php
                    $returnRoute = '';
                    if(Auth::user()->hasRole('Admin')) $returnRoute = route('admin.devis.devishistory');
                    if(Auth::user()->hasRole('Responsable de projet')) $returnRoute = route('Responsable_de_projet.devis.devishistory');
                    if(Auth::user()->hasRole('Employee')) $returnRoute = route('employee.devis.devishistory');
                    if(Auth::user()->hasRole('Responsable de projet et gestionnaire')) $returnRoute = route('Responsable_gestionnaire.devis.devishistory');
                @endphp
                
                <a href="javascript:history.back()" class="dt-btn dt-btn-ghost w-full sm:w-auto justify-center">
                    {{ __('messages.Return Back') }}
                </a>
            </div>
            
            {{-- Session Message --}}
            @if(session('message'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <strong class="font-bold">{{ __('messages.Success!') }}</strong>
                <span class="block sm:inline">{{ session('message') }}</span>
                <button type="button" onclick="this.parentElement.style.display='none';" class="absolute top-0 bottom-0 right-0 px-4 py-3 font-bold text-xl" aria-label="{{ __('messages.Close') }}">&times;</button>
            </div>
            @endif

            {{-- 3. Totals Cards Section --}}
            @php
                $Total_Accpeted = App\Models\devis_history::where('entreprise_id', Auth::user()->entreprise_id)->where('acceptanceStatus', 1)->where('archived', 1)->sum('budget');
                $Total_Declined = App\Models\devis_history::where('entreprise_id', Auth::user()->entreprise_id)->where('acceptanceStatus', 2)->where('archived', 1)->sum('budget');
                $Total_Pending = App\Models\devis_history::where('entreprise_id', Auth::user()->entreprise_id)->where('acceptanceStatus', 0)->where('archived', 1)->sum('budget');
            @endphp
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                {{-- Accepted Card --}}
                <div id="cardAccepted" class="dt-card p-4 flex items-center gap-3 cursor-pointer hover:border-emerald-200 transition-colors">
                    <div class="flex-shrink-0 bg-green-100 rounded-full p-3">
                        <svg class="h-6 w-6 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase font-medium">{{ __('messages.TOTAL ACCEPTED') }}</p>
                        <p class="text-xl font-semibold text-green-600">€{{ number_format($Total_Accpeted, 2, ',', ' ') }}</p>
                    </div>
                </div>

                {{-- Declined Card --}}
                <div id="cardDeclined" class="dt-card p-4 flex items-center gap-3 cursor-pointer hover:border-red-200 transition-colors">
                    <div class="flex-shrink-0 bg-red-100 rounded-full p-3">
                        <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase font-medium">{{ __('messages.TOTAL DECLINED') }}</p>
                        <p class="text-xl font-semibold text-red-600">€{{ number_format($Total_Declined, 2, ',', ' ') }}</p>
                    </div>
                </div>

                {{-- Pending Card --}}
                <div id="cardPending" class="dt-card p-4 flex items-center gap-3 cursor-pointer hover:border-amber-200 transition-colors">
                    <div class="flex-shrink-0 bg-yellow-100 rounded-full p-3">
                         <svg class="h-6 w-6 text-yellow-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase font-medium">{{ __('messages.TOTAL PENDING') }}</p>
                        <p class="text-xl font-semibold text-yellow-600">€{{ number_format($Total_Pending, 2, ',', ' ') }}</p>
                    </div>
                </div>
            </div>

            {{-- 4. Filters Section --}}
            <div class="dt-card p-4 sm:p-5">
                <h6 class="text-base font-semibold text-gray-800 mb-4">{{ __('messages.Filters') }}</h6>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label for="clientFilter" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Filter by Client') }}</label>
                        <select id="clientFilter" class="devis-filter-control form-select-custom">
                            <option value="">{{ __('messages.All Clients') }}</option>
                            @foreach($clients as $client)
                            <option value="{{$client->name}}">{{$client->name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="acceptanceFilter" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Filter by Acceptance') }}</label>
                        <select id="acceptanceFilter" class="devis-filter-control form-select-custom">
                            <option value="">{{ __('messages.All Devis') }}</option>
                            <option value="{{ __('messages.Accepted') }}">{{ __('messages.Accepted') }}</option>
                            <option value="{{ __('messages.Declined') }}">{{ __('messages.Declined') }}</option>
                            <option value="{{ __('messages.Pending') }}">{{ __('messages.Pending') }}</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- 5. Table Section --}}
            <div class="dt-card overflow-hidden">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 px-4 sm:px-5 py-3.5 border-b border-slate-100">
                    <p class="text-[13px] font-semibold text-[#0B2638]">{{ __('messages.Archived Devis') }}</p>
                    <label for="archivedDevisPerPage" class="flex items-center gap-2 text-[12px] text-slate-500">
                        <span>{{ __('messages.Rows per page') }}:</span>
                        <select id="archivedDevisPerPage" class="devis-filter-control !w-auto !py-1 !pr-7">
                            <option value="10" selected>10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </label>
                </div>
                <div class="table-responsive overflow-x-auto custom-scrollbar">
                    <table class="w-full divide-y divide-gray-200 table table-fixed" id="devisTable">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-4 py-3 text-left text-sm font-semibold text-gray-600 uppercase">{{ __('messages.Devis N') }}</th>
                                <th scope="col" class="px-4 py-3 text-left text-sm font-semibold text-gray-600 uppercase">{{ __('messages.Project') }}</th>
                                <th scope="col" class="px-4 py-3 text-left text-sm font-semibold text-gray-600 uppercase">{{ __('messages.Client') }}</th>
                                <th scope="col" class="px-4 py-3 text-left text-sm font-semibold text-gray-600 uppercase">{{ __('messages.Budget') }}</th>
                                <th scope="col" class="px-4 py-3 text-left text-sm font-semibold text-gray-600 uppercase">{{ __('messages.Unbilled') }}</th>
                                <th scope="col" class="px-4 py-3 text-center text-sm font-semibold text-gray-600 uppercase">{{ __('messages.Created at') }}</th>
                                <th scope="col" class="px-4 py-3 text-center text-sm font-semibold text-gray-600 uppercase">{{ __('messages.Status') }}</th>
                                <th scope="col" class="px-4 py-3 text-center text-sm font-semibold text-gray-600 uppercase whitespace-nowrap min-w-[190px]">{{ __('messages.Acceptance') }}</th>
                                <th scope="col" class="px-4 py-3 text-left text-sm font-semibold text-gray-600 uppercase whitespace-nowrap min-w-[280px]">{{ __('messages.Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($devis as $d)
                            <tr class="devis-row hover:bg-gray-50" data-devis-row aria-expanded="true">
                                <td data-label="{{ __('messages.Devis N') }}" data-span="2" class="px-4 py-3 whitespace-normal break-words text-sm font-medium devis-toggle-cell" data-devis-toggle>
                                    @if(Auth::user()->hasRole('Responsable de projet'))
                                        <a href="{{ route('Responsable_de_projet.devis.downloadDevis', $d->devis_number) }}" class="devis-link-normal hidden lg:inline">{{$entreprise->devis_prefix}}-{{date('ym', strtotime($d->created_at))}}{{$d->devis_number}}</a>
                                    @elseif(Auth::user()->hasRole('Admin'))
                                        <a href="{{ route('admin.devis.downloadDevis', $d->devis_number) }}" class="devis-link-normal hidden lg:inline">{{$entreprise->devis_prefix}}-{{date('ym', strtotime($d->created_at))}}{{$d->devis_number}}</a>
                                    @elseif(Auth::user()->hasRole('Employee'))
                                        <a href="{{ route('employee.devis.downloadDevis', $d->devis_number) }}" class="devis-link-normal hidden lg:inline">{{$entreprise->devis_prefix}}-{{date('ym', strtotime($d->created_at))}}{{$d->devis_number}}</a>
                                    @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                        <a href="{{ route('Responsable_gestionnaire.devis.downloadDevis', $d->devis_number) }}" class="devis-link-normal hidden lg:inline">{{$entreprise->devis_prefix}}-{{date('ym', strtotime($d->created_at))}}{{$d->devis_number}}</a>
                                    @endif

                                    {{-- Mobile accordion header --}}
                                    <div class="flex items-start justify-between gap-3 lg:hidden">
                                        <div class="flex items-baseline gap-2 min-w-0">
                                            <span class="text-[11px] leading-none font-bold tracking-[.06em] uppercase text-slate-500 whitespace-nowrap">
                                                {{ __('messages.Devis N') }}
                                            </span>
                                            @php
                                                $devisRoute = '';
                                                if(Auth::user()->hasRole('Responsable de projet')) $devisRoute = route('Responsable_de_projet.devis.downloadDevis', $d->devis_number);
                                                elseif(Auth::user()->hasRole('Admin')) $devisRoute = route('admin.devis.downloadDevis', $d->devis_number);
                                                elseif(Auth::user()->hasRole('Employee')) $devisRoute = route('employee.devis.downloadDevis', $d->devis_number);
                                                elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire')) $devisRoute = route('Responsable_gestionnaire.devis.downloadDevis', $d->devis_number);
                                            @endphp
                                            <a href="{{ $devisRoute ?: '#' }}" class="devis-link-normal truncate leading-none">
                                                {{$entreprise->devis_prefix}}-{{date('ym', strtotime($d->created_at))}}{{$d->devis_number}}
                                            </a>
                                        </div>
                                        <span class="shrink-0 mt-0.5 text-slate-400">
                                            <svg class="devis-chevron w-5 h-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </span>
                                    </div>
                                </td>
                                <td data-label="{{ __('messages.Project') }}" data-span="2" class="px-4 py-3 whitespace-normal break-words text-sm text-gray-800 font-medium">
                                    @php $project = \App\Models\Project::where('id', $d->project_id)->first(); @endphp
                                    @if($project)
                                        @if($d->acceptanceStatus == 1 || $d->Existing_project_name != null)
                                            @if(Auth::user()->hasRole('Admin')) <a href="{{route('admin.projects.show', $project->id)}}" class="text-blue-600 hover:text-blue-700">{{$project->name}}</a> @endif
                                            @if(Auth::user()->hasRole('Responsable de projet')) <a href="{{route('Responsable_de_projet.projects.show', $project->id)}}" class="text-blue-600 hover:text-blue-700">{{$project->name}}</a> @endif
                                            @if(Auth::user()->hasRole('Employee')) <a href="{{route('employee.projects.show', $project->id)}}" class="text-blue-600 hover:text-blue-700">{{$project->name}}</a> @endif
                                            @if(Auth::user()->hasRole('Responsable de projet et gestionnaire')) <a href="{{route('Responsable_gestionnaire.projects.show', $project->id)}}" class="text-blue-600 hover:text-blue-700">{{$project->name}}</a> @endif
                                        @else {{$project->name}} @endif
                                    @else {{ __('messages.N/A') }} @endif
                                </td>
                                <td data-label="{{ __('messages.Client') }}" data-span="2" class="px-4 py-3 whitespace-normal break-words text-sm text-gray-800">
                                    @php $client = \App\Models\Client::where('id', $d->client_id)->first(); $emails = explode(',', $d->emails); @endphp
                                    @if (empty($client))
                                        @foreach ($emails as $email) {{ $email }}<br /> @endforeach
                                    @else {{$client->name}} @endif
                                </td>
                                <td data-label="{{ __('messages.Budget') }}" class="px-4 py-3 whitespace-normal break-words text-sm font-semibold text-gray-900 text-right">€{{ number_format($d->budget, 2, ',', ' ') }}</td>
                                <td data-label="{{ __('messages.Unbilled') }}" class="px-4 py-3 whitespace-normal break-words text-sm font-semibold text-gray-900 text-right">
                                    @php
                                        $devisInvoicesTotal = \App\Models\invoices_history::where('devis_id', $d->id)->where('Payment_status', '!=', -1)->sum('total_htva');
                                        $unbilledBudget = ($d->budget ?? 0) - ($devisInvoicesTotal ?? 0);
                                    @endphp
                                    €{{ number_format(max($unbilledBudget, 0), 2, ',', ' ') }}
                                </td>
                                <td data-label="{{ __('messages.Created at') }}" class="px-4 py-3 whitespace-normal break-words text-sm text-gray-600 text-center">{{ \Carbon\Carbon::parse($d->created_at)->format('Y-m-d') }}</td>
                                <td data-label="{{ __('messages.Status') }}" class="px-4 py-3 whitespace-normal break-words text-sm text-center">
                                    @if($d->acceptanceStatus == 1)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800"> {{ __('messages.Accepted') }} </span> 
                                    @elseif($d->acceptanceStatus == 2 )
                                     <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800"> {{ __('messages.Declined') }} </span> 
                                    @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800"> {{ __('messages.Pending') }} </span> 
                                    @endif
                                </td>
                                <td data-label="{{ __('messages.Acceptance') }}" data-span="2" class="px-4 py-3 text-sm text-center">
                                    <div class="devis-action-row">
                                        @if(Auth::user()->hasRole('Responsable de projet'))
                                        <form action="{{route('Responsable_de_projet.devis.acceptDevis', $d->id) }}" method="post" onsubmit="return confirm('{{ __('messages.Mark as Accepted?') }}');" class="inline-block"> @csrf <button type="submit" class="text-white bg-green-500 hover:bg-green-600 font-medium rounded text-xs px-3 py-1 text-center whitespace-nowrap">{{ __('messages.Accept') }}</button> </form>
                                        <form action="{{route('Responsable_de_projet.devis.declineDevis', $d->id) }}" method="post" onsubmit="return confirm('{{ __('messages.Mark as Declined?') }}');" class="inline-block"> @csrf <button type="submit" class="text-white bg-red-500 hover:bg-red-600 font-medium rounded text-xs px-3 py-1 text-center whitespace-nowrap">{{ __('messages.Decline') }}</button> </form>
                                        @endif
                                        @if(Auth::user()->hasRole('Admin'))
                                        <form action="{{route('admin.devis.acceptDevis', $d->id) }}" method="post" onsubmit="return confirm('{{ __('messages.Mark as Accepted?') }}');" class="inline-block"> @csrf <button type="submit" class="text-white bg-green-500 hover:bg-green-600 font-medium rounded text-xs px-3 py-1 text-center whitespace-nowrap">{{ __('messages.Accept') }}</button> </form>
                                        <form action="{{route('admin.devis.declineDevis', $d->id) }}" method="post" onsubmit="return confirm('{{ __('messages.Mark as Declined?') }}');" class="inline-block"> @csrf <button type="submit" class="text-white bg-red-500 hover:bg-red-600 font-medium rounded text-xs px-3 py-1 text-center whitespace-nowrap">{{ __('messages.Decline') }}</button> </form>
                                        @endif
                                        @if(Auth::user()->hasRole('Employee'))
                                        <form action="{{route('employee.devis.acceptDevis', $d->id) }}" method="post" onsubmit="return confirm('{{ __('messages.Mark as Accepted?') }}');" class="inline-block"> @csrf <button type="submit" class="text-white bg-green-500 hover:bg-green-600 font-medium rounded text-xs px-3 py-1 text-center whitespace-nowrap">{{ __('messages.Accept') }}</button> </form>
                                        <form action="{{route('employee.devis.declineDevis', $d->id) }}" method="post" onsubmit="return confirm('{{ __('messages.Mark as Declined?') }}');" class="inline-block"> @csrf <button type="submit" class="text-white bg-red-500 hover:bg-red-600 font-medium rounded text-xs px-3 py-1 text-center whitespace-nowrap">{{ __('messages.Decline') }}</button> </form>
                                        @endif
                                        @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                        <form action="{{route('Responsable_gestionnaire.devis.acceptDevis', $d->id) }}" method="post" onsubmit="return confirm('{{ __('messages.Mark as Accepted?') }}');" class="inline-block"> @csrf <button type="submit" class="text-white bg-green-500 hover:bg-green-600 font-medium rounded text-xs px-3 py-1 text-center whitespace-nowrap">{{ __('messages.Accept') }}</button> </form>
                                        <form action="{{route('Responsable_gestionnaire.devis.declineDevis', $d->id) }}" method="post" onsubmit="return confirm('{{ __('messages.Mark as Declined?') }}');" class="inline-block"> @csrf <button type="submit" class="text-white bg-red-500 hover:bg-red-600 font-medium rounded text-xs px-3 py-1 text-center whitespace-nowrap">{{ __('messages.Decline') }}</button> </form>
                                        @endif
                                    </div>
                                </td>
                                
                                <td data-label="{{ __('messages.Actions') }}" data-span="2" class="px-2 py-3">
                                    <div class="devis-action-row">
                                        {{-- 2x2 Grid for buttons --}}
                                        <div class="devis-action-row w-full">
                                            
                                            {{-- 1. Download Button --}}
                                            @php $dlRoute = '#'; @endphp
                                            @if(Auth::user()->hasRole('Admin')) @php $dlRoute = route('admin.devis.downloadDevis', $d->devis_number); @endphp @endif
                                            @if(Auth::user()->hasRole('Responsable de projet')) @php $dlRoute = route('Responsable_de_projet.devis.downloadDevis', $d->devis_number); @endphp @endif
                                            @if(Auth::user()->hasRole('Employee')) @php $dlRoute = route('employee.devis.downloadDevis', $d->devis_number); @endphp @endif
                                            @if(Auth::user()->hasRole('Responsable de projet et gestionnaire')) @php $dlRoute = route('Responsable_gestionnaire.devis.downloadDevis', $d->devis_number); @endphp @endif
                                            <a class="devis-action devis-action-primary" href="{{ $dlRoute }}"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v12m0 0l-4-4m4 4l4-4M5 17v3h14v-3"/></svg>{{ __('messages.Download') }}</a>
                                            
                                            {{-- 2. Send Button --}}
                                            @can('Send devis')
                                                @php $sendRoute = '#'; @endphp
                                                @if(Auth::user()->hasRole('Admin')) @php $sendRoute = route('admin.devis.sendDevis'); @endphp @endif
                                                @if(Auth::user()->hasRole('Responsable de projet')) @php $sendRoute = route('Responsable_de_projet.devis.sendDevis'); @endphp @endif
                                                @if(Auth::user()->hasRole('Employee')) @php $sendRoute = route('employee.devis.sendDevis'); @endphp @endif
                                                @if(Auth::user()->hasRole('Responsable de projet et gestionnaire')) @php $sendRoute = route('Responsable_gestionnaire.devis.sendDevis'); @endphp @endif
                                                <button type="button" 
                                                        class="devis-action devis-action-neutral"
                                                        id="openSendDevisModal"
                                                        data-devis-id="{{ $d->id }}"
                                                        data-action="{{ $sendRoute }}">
                                                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l9 6 9-6M5 5h14a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2z"/></svg>
                                                    {{ __('messages.Send') }}
                                                </button>
                                            @else
                                                <div class="w-full"></div> @endcan

                                            {{-- 3. Delete Button --}}
                                            @can('Delete devis')
                                                @php $deleteRoute = '#'; @endphp
                                                @if(Auth::user()->hasRole('Admin')) @php $deleteRoute = route('admin.devis.destroy', $d->id); @endphp @endif
                                                @if(Auth::user()->hasRole('Responsable de projet')) @php $deleteRoute = route('Responsable_de_projet.devis.destroy', $d->id); @endphp @endif
                                                @if(Auth::user()->hasRole('Employee')) @php $deleteRoute = route('employee.devis.destroy', $d->id); @endphp @endif
                                                @if(Auth::user()->hasRole('Responsable de projet et gestionnaire')) @php $deleteRoute = route('Responsable_gestionnaire.devis.destroy', $d->id); @endphp @endif
                                                <form action="{{$deleteRoute}}" method="post" onsubmit="return confirm('{{ __('messages.Are you sure?') }}');" class="inline-block"> @csrf @method('DELETE') <button type="submit" class="devis-action devis-action-danger"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3M4 7h16"/></svg>{{ __('messages.Delete') }}</button> </form>
                                            @else
                                                <div class="w-full"></div> @endcan
                                            
                                            {{-- 4. Unarchive Button --}}
                                            @php $unarchiveRoute = '#'; @endphp
                                            @if(Auth::user()->hasRole('Admin')) @php $unarchiveRoute = route('admin.devis.unarchive', $d->id); @endphp @endif
                                            @if(Auth::user()->hasRole('Responsable de projet')) @php $unarchiveRoute = route('Responsable_de_projet.devis.unarchive', $d->id); @endphp @endif
                                            @if(Auth::user()->hasRole('Employee')) @php $unarchiveRoute = route('employee.devis.unarchive', $d->id); @endphp @endif
                                            @if(Auth::user()->hasRole('Responsable de projet et gestionnaire')) @php $unarchiveRoute = route('Responsable_gestionnaire.devis.unarchive', $d->id); @endphp @endif
                                            <form action="{{$unarchiveRoute}}" method="post" onsubmit="return confirm('{{ __('messages.Are you sure?') }}');" class="inline-block"> @csrf <button type="submit" class="devis-action devis-action-muted"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8h18M5 8v11h14V8M3 8l1-4h16l1 4M10 12h4"/></svg>{{ __('messages.Unarchive') }}</button> </form>
                                            
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center px-4 py-6 text-gray-500">
                                    {{ __('messages.No archived devis found.') }}
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 px-5 py-3 border-t border-slate-100 bg-[#f8fafc]">
                    <span id="archivedDevisPaginationInfo" class="text-[12px] text-slate-500 text-center sm:text-left"></span>
                    <nav id="archivedDevisPaginationControls" class="flex flex-wrap items-center justify-center gap-1" aria-label="{{ __('messages.Pagination Navigation') }}"></nav>
                </div>
            </div>
            
        </main>
    </div>

    {{-- Modals --}}
    <div id="devisReportModal" class="fixed inset-0 z-40 flex items-center justify-center bg-black bg-opacity-50 hidden">
        <div class="bg-white w-full max-w-md mx-4 overflow-hidden">
            <div class="flex justify-between items-center px-5 py-4 border-b border-slate-100">
                <h5 class="text-[15px] font-bold text-[#0B2638]" id="devisReportModalLabel">{{ __('messages.Download Devis Reports') }}</h5>
                <button type="button" id="closeDevisReportModal" class="text-slate-400 hover:text-slate-700" aria-label="{{ __('messages.Close') }}"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <div class="p-6">
                <div class="mb-3">
                    <label class="block text-sm font-medium text-gray-700">{{ __('messages.Choose statuses (optional):') }}</label>
                    <div class="mt-2 space-y-2">
                        <div class="flex items-center">
                            <input id="status_accepted" name="devisReportStatus[]" type="checkbox" value="accepted" class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                            <label for="status_accepted" class="ml-2 block text-sm text-gray-900">{{ __('messages.Accepted') }}</label>
                        </div>
                        <div class="flex items-center">
                            <input id="status_declined" name="devisReportStatus[]" type="checkbox" value="declined" class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                            <label for="status_declined" class="ml-2 block text-sm text-gray-900">{{ __('messages.Declined') }}</label>
                        </div>
                        <div class="flex items-center">
                            <input id="status_pending" name="devisReportStatus[]" type="checkbox" value="pending" class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                            <label for="status_pending" class="ml-2 block text-sm text-gray-900">{{ __('messages.Pending') }}</label>
                        </div>
                        <hr class="my-3">
                        <div class="flex items-center">
                            <input id="include_archived" type="checkbox" value="1" class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                            <label for="include_archived" class="ml-2 block text-sm text-gray-900">{{ __('messages.Include archived') }}</label>
                        </div>
                    </div>
                </div>
                <small class="text-gray-500">{{ __('messages.Date range (optional) will be applied from the fields at the top.') }}</small>
            </div>
            <div class="flex flex-col-reverse sm:flex-row justify-end items-stretch sm:items-center gap-2 p-4 bg-slate-50 border-t border-slate-100">
                <button type="button" id="cancelDevisReportBtn" class="dt-btn dt-btn-ghost justify-center">{{ __('messages.Cancel') }}</button>
                <button type="button" id="confirmDevisReport" class="dt-btn dt-btn-primary justify-center"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v12m0 0l-4-4m4 4l4-4M5 17v3h14v-3"/></svg>{{ __('messages.Download PDF') }}</button>
            </div>
        </div>
    </div>
    <div id="sendDevisModal" class="fixed inset-0 z-40 flex items-center justify-center bg-black bg-opacity-50 hidden">
        <div class="bg-white w-full max-w-lg mx-4 overflow-hidden">
            <div class="flex justify-between items-center px-5 py-4 border-b border-slate-100">
                <h5 class="text-[15px] font-bold text-[#0B2638]" id="sendDevisModalLabel">{{ __('messages.Send Devis') }}</h5>
                <button type="button" id="closeSendDevisModal" class="text-slate-400 hover:text-slate-700" aria-label="{{ __('messages.Close') }}"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <div class="p-6">
                <form id="sendDevisForm" class="devis-form" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="devis_id" id="devis_id">
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Select Recipients Devis') }}</label>
                        <div id="recipientsList" class="border p-3 rounded-md h-32 overflow-y-auto bg-gray-50 space-y-2">
                            </div>
                    </div>
    
                    <div class="mb-3">
                        <label for="attachments" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Additional Attachments (PDF only):') }}</label>
                        <input type="file" class="block w-full text-sm text-gray-700 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none file:bg-gray-100 file:border-0 file:px-3 file:py-2" id="attachments" name="attachments[]" multiple accept=".pdf">
                        <small class="text-gray-500">{{ __('messages.Select PDF files to be merged with the devis') }}</small>
                    </div>
                </form>
            </div>
            <div class="flex flex-col-reverse sm:flex-row justify-end items-stretch sm:items-center gap-2 p-4 bg-slate-50 border-t border-slate-100">
                <button type="button" id="cancelSendDevisModal" class="dt-btn dt-btn-ghost justify-center">{{ __('messages.Close') }}</button>
                <button type="button" class="dt-btn dt-btn-primary justify-center" id="confirmSend"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4l16 8-16 8 3-8-3-8zm3 8h13"/></svg>{{ __('messages.Send Devis') }}</button>
            </div>
        </div>
    </div>

    @include('dashboard_layouts.scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
document.addEventListener('DOMContentLoaded', function() {
    
    // --- 1. FILTER LOGIC ---
    const clientFilter = document.getElementById('clientFilter');
    const acceptanceFilter = document.getElementById('acceptanceFilter');
    const tableBody = document.querySelector('#devisTable tbody');
    const allRows = tableBody.querySelectorAll('tr');
    const dataRows = Array.from(allRows).filter(row => !row.querySelector('td[colspan="9"]'));
    const perPageSelect = document.getElementById('archivedDevisPerPage');
    const paginationInfo = document.getElementById('archivedDevisPaginationInfo');
    const paginationControls = document.getElementById('archivedDevisPaginationControls');
    let currentPage = 1;
    let perPage = parseInt(perPageSelect.value, 10) || 10;
    let filteredRows = [];
    
    // [NEW] Get the new clickable cards
    const cardAccepted = document.getElementById('cardAccepted');
    const cardDeclined = document.getElementById('cardDeclined');
    const cardPending = document.getElementById('cardPending');
    
    // [NEW] Add click listeners for the stat cards
    if (cardAccepted) {
        cardAccepted.addEventListener('click', function() {
            acceptanceFilter.value = @json(__('messages.Accepted')); // Set dropdown value
            filterTable(); // Call existing filter function
        });
    }

    if (cardDeclined) {
        cardDeclined.addEventListener('click', function() {
            acceptanceFilter.value = @json(__('messages.Declined'));
            filterTable();
        });
    }

    if (cardPending) {
        cardPending.addEventListener('click', function() {
            acceptanceFilter.value = @json(__('messages.Pending'));
            filterTable();
        });
    }
    
    function filterTable() {
        const clientValue = clientFilter.value.trim();
        const acceptanceValue = acceptanceFilter.value.trim();
        filteredRows = dataRows.filter(row => {
            const clientCell = row.querySelector('td:nth-child(3)');
            const statusCell = row.querySelector('td:nth-child(7)'); // 7th column is Status
            if (!clientCell || !statusCell || !statusCell.querySelector('span')) return false;

            const clientName = clientCell.textContent.trim();
            const statusText = statusCell.querySelector('span').textContent.trim();
            const clientMatch = clientValue === "" || clientName === clientValue;
            const acceptanceMatch = acceptanceValue === "" || statusText === acceptanceValue;
            return clientMatch && acceptanceMatch;
        });

        currentPage = 1;
        applyPagination();
    }

    function applyPagination() {
        const total = filteredRows.length;
        const totalPages = Math.max(1, Math.ceil(total / perPage));
        currentPage = Math.min(currentPage, totalPages);
        const start = (currentPage - 1) * perPage;
        const end = Math.min(start + perPage, total);
        dataRows.forEach(row => row.style.display = 'none');
        filteredRows.slice(start, end).forEach(row => row.style.display = '');

        if (paginationInfo) paginationInfo.textContent = total ? `${start + 1}–${end} / ${total}` : '';
        const noResultsRow = tableBody.querySelector('td[colspan="9"]');
        if (noResultsRow) noResultsRow.parentElement.style.display = total ? 'none' : '';
        renderPaginationControls(totalPages);
    }

    function renderPaginationControls(totalPages) {
        paginationControls.replaceChildren();
        const base = 'inline-flex items-center justify-center min-w-[28px] h-[28px] px-1.5 rounded-lg text-[12px] font-medium transition-colors border select-none';
        const normal = `${base} bg-white border-slate-200 text-slate-600 hover:bg-slate-50`;
        const active = `${base} bg-[#2D7080] border-[#2D7080] text-white`;
        const disabled = `${base} bg-slate-50 border-slate-100 text-slate-300 cursor-not-allowed`;

        function addArrow(direction, isDisabled) {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = isDisabled ? disabled : normal;
            button.disabled = isDisabled;
            button.setAttribute('aria-label', direction === 'previous' ? @json(__('messages.Previous')) : @json(__('messages.Next')));
            button.innerHTML = direction === 'previous'
                ? '<svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>'
                : '<svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>';
            button.addEventListener('click', () => {
                currentPage += direction === 'previous' ? -1 : 1;
                applyPagination();
            });
            paginationControls.appendChild(button);
        }

        addArrow('previous', currentPage === 1);
        const range = [];
        for (let page = 1; page <= totalPages; page++) {
            if (page === 1 || page === totalPages || (page >= currentPage - 1 && page <= currentPage + 1)) range.push(page);
            else if (range[range.length - 1] !== '…') range.push('…');
        }
        range.forEach(page => {
            const button = document.createElement('button');
            button.type = 'button';
            if (page === '…') {
                button.className = `${base} bg-white border-slate-100 text-slate-400 cursor-default`;
                button.textContent = '…';
                button.disabled = true;
            } else {
                button.className = page === currentPage ? active : normal;
                button.textContent = page;
                button.setAttribute('aria-label', `${@json(__('messages.Page'))} ${page}`);
                if (page === currentPage) button.setAttribute('aria-current', 'page');
                button.addEventListener('click', () => { currentPage = page; applyPagination(); });
            }
            paginationControls.appendChild(button);
        });
        addArrow('next', currentPage === totalPages);
    }

    // Mobile accordion per devis row (show only number when collapsed)
    const mql = window.matchMedia('(max-width: 1024px)');

    function setRowCollapsed(row, collapsed) {
        if (!row) return;
        row.classList.toggle('is-collapsed', collapsed);
        row.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
    }

    function initAccordions() {
        const rows = document.querySelectorAll('#devisTable tbody tr.devis-row');
        const isMobile = mql.matches;
        rows.forEach(row => setRowCollapsed(row, isMobile));
    }

    document.addEventListener('click', function (e) {
        const toggleCell = e.target.closest('[data-devis-toggle]');
        if (!toggleCell) return;

        // Let normal link clicks open the PDF without toggling.
        if (e.target.closest('a')) return;

        const row = toggleCell.closest('tr.devis-row');
        if (!row) return;

        const collapsed = row.classList.contains('is-collapsed');
        setRowCollapsed(row, !collapsed);
    });

    if (mql.addEventListener) mql.addEventListener('change', initAccordions);
    else if (mql.addListener) mql.addListener(initAccordions);

    initAccordions();
    
    if (clientFilter) clientFilter.addEventListener('change', filterTable);
    if (acceptanceFilter) acceptanceFilter.addEventListener('change', filterTable);
    perPageSelect.addEventListener('change', function () {
        perPage = parseInt(this.value, 10) || 10;
        currentPage = 1;
        applyPagination();
    });
    
    // Initial filter on load
    filterTable();
    
    // --- 2. DEVIS REPORT MODAL LOGIC ---
    const devisReportModal = document.getElementById('devisReportModal');
    const openDevisReportModalBtn = document.getElementById('openDevisReportModal');
    const closeDevisReportModalBtn = document.getElementById('closeDevisReportModal');
    const cancelDevisReportModalBtn = document.getElementById('cancelDevisReportBtn');
    const confirmDevisReportBtn = document.getElementById('confirmDevisReport');

    if (openDevisReportModalBtn) {
        openDevisReportModalBtn.addEventListener('click', function(e) {
            e.preventDefault();
            if (devisReportModal) devisReportModal.classList.remove('hidden');
        });
    }
    if (closeDevisReportModalBtn) {
        closeDevisReportModalBtn.addEventListener('click', () => {
            if (devisReportModal) devisReportModal.classList.add('hidden');
        });
    }
    if (cancelDevisReportModalBtn) {
        cancelDevisReportModalBtn.addEventListener('click', () => {
            if (devisReportModal) devisReportModal.classList.add('hidden');
        });
    }
    if (confirmDevisReportBtn) {
        confirmDevisReportBtn.addEventListener('click', function() {
            let baseUrl = '';
            try {
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
            } catch (e) { console.error("Error setting base URL:", e); }

            const startDate = document.getElementById('startDate')?.value || '';
            const endDate = document.getElementById('endDate')?.value || '';
            const selectedStatuses = Array.from(document.querySelectorAll('input[name="devisReportStatus[]"]:checked')).map(x => x.value);
            const includeArchived = document.getElementById('include_archived')?.checked;

            const params = new URLSearchParams();
            if (startDate && endDate) {
                params.append('startDate', startDate);
                params.append('endDate', endDate);
            }
            if (selectedStatuses.length > 0) {
                params.append('filters', selectedStatuses.join(','));
            }
            if (includeArchived) {
                params.append('archived', '1');
            }

            const finalUrl = params.toString() ? `${baseUrl}?${params.toString()}` : baseUrl;
            window.location.href = finalUrl;
            if (devisReportModal) devisReportModal.classList.add('hidden');
        });
    }

    // --- 3. SEND DEVIS MODAL LOGIC ---
    const sendDevisModalEl = document.getElementById('sendDevisModal');
    const closeSendDevisModalBtn = document.getElementById('closeSendDevisModal');
    const cancelSendDevisModalBtn = document.getElementById('cancelSendDevisModal');

    if (closeSendDevisModalBtn) {
        closeSendDevisModalBtn.addEventListener('click', () => {
            if (sendDevisModalEl) sendDevisModalEl.classList.add('hidden');
        });
    }
    if (cancelSendDevisModalBtn) {
        cancelSendDevisModalBtn.addEventListener('click', () => {
            if (sendDevisModalEl) sendDevisModalEl.classList.add('hidden');
        });
    }
    
    // Add click handler to the "Send" buttons in the table (delegated or directly attached)
    // Since the table might have many rows, direct attachment is okay for now
    document.querySelectorAll('#openSendDevisModal').forEach(btn => {
        btn.addEventListener('click', handleSendClick);
    });

    function handleSendClick(event) {
        event.preventDefault();
        const button = event.target;
        const devisId = button.getAttribute('data-devis-id');
        const formAction = button.getAttribute('data-action');
        
        document.getElementById('devis_id').value = devisId;
        const recipientsList = document.getElementById('recipientsList');
        recipientsList.innerHTML = '<div class="text-center">' + @json(__('messages.Loading...')) + '</div>';
        
        // This URL should be dynamic based on user role
        let contactsUrl = '';
        @if(Auth::user()->hasRole('Admin')) contactsUrl = `/admin/devis/get-contacts/${devisId}`;
        @elseif(Auth::user()->hasRole('Responsable de projet')) contactsUrl = `/Responsable_de_projet/devis/get-contacts/${devisId}`;
        @elseif(Auth::user()->hasRole('Employee')) contactsUrl = `/employee/devis/get-contacts/${devisId}`;
        @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire')) contactsUrl = `/Responsable_gestionnaire/devis/get-contacts/${devisId}`;
        @endif

        fetch(contactsUrl)
            .then(response => response.json())
            .then(data => {
                recipientsList.innerHTML = '';
                if (data.contacts && data.contacts.length > 0) {
                    data.contacts.forEach(contact => {
                        const div = document.createElement('div');
                        div.className = 'flex items-center';
                        div.innerHTML = `
                            <input class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" type="checkbox" name="recipients[]" value="${contact.email}" id="recipient_${contact.id}" checked>
                            <label class="ml-2 block text-sm text-gray-900" for="recipient_${contact.id}">
                                ${contact.name} (${contact.email})
                            </label>`;
                        recipientsList.appendChild(div);
                    });
                } else {
                    recipientsList.innerHTML = '<p class="text-gray-500">' + @json(__('messages.No contacts found for this client')) + '</p>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                recipientsList.innerHTML = '<p class="text-red-600">' + @json(__('messages.Error loading contacts devis history')) + '</p>';
            });
        
        document.getElementById('sendDevisForm').action = formAction;
        if (sendDevisModalEl) sendDevisModalEl.classList.remove('hidden');
    }

    document.getElementById('confirmSend').addEventListener('click', function() {
        const form = document.getElementById('sendDevisForm');
        const devisId = document.getElementById('devis_id').value;
        const selectedRecipients = Array.from(document.querySelectorAll('input[name="recipients[]"]:checked'))
            .map(checkbox => checkbox.value);
        
        if (selectedRecipients.length === 0) {
            alert(@json(__('messages.Please select at least one recipient')));
            return;
        }

        const formData = new FormData();
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        formData.append('_token', csrfToken);
        formData.append('devis_id', devisId);
        formData.append('recipients', JSON.stringify(selectedRecipients));
        
        const attachments = document.getElementById('attachments').files;
        for (let i = 0; i < attachments.length; i++) {
            formData.append('attachments[]', attachments[i]);
        }

        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => {
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            return response.json();
        })
        .then(data => {
            if (data.success) {
                alert(@json(__('messages.Devis sent successfully!')));
                if (sendDevisModalEl) sendDevisModalEl.classList.add('hidden');
                location.reload();
            } else {
                alert(@json(__('messages.Error sending devis: ')) + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert(@json(__('messages.An error occurred while sending the devis')));
        });
    });
});
</script>
</body>
</html>
