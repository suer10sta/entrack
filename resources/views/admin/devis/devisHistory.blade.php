<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Devis History - DalyTrac</title>
    @vite('resources/css/app.css') 
    @livewireStyles
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="//unpkg.com/alpinejs" defer></script>
<style>
        /* Custom select styling */
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
        select:invalid { color: #9ca3af; }
        option { color: #111827; }

        /* Devis link styling */
        .devis-link-normal {
             color: #2563eb; /* blue-600 */
             font-weight: 500; /* medium */
         }
         .devis-link-normal:hover {
             color: #1d4ed8; /* blue-800 */
             text-decoration: underline;
         }

        /* --- STYLES COPIED FROM INVOICE FILE --- */
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

<body class="bg-gray-100 flex h-screen overflow-hidden font-sans" x-data>
    {{-- Sidebar Component --}}
    @if(Auth::user()->hasRole('Admin'))
        <x-dashboard.sidebar />
    @else
        <x-employees.sidebar />
    @endif

    <div class="flex flex-col flex-1 overflow-y-auto">
        {{-- Navbar Component --}}
        <x-dashboard.navbar />
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-8 space-y-8">
        {{-- 1. Header Section --}}
            <div class="flex flex-wrap justify-between items-center gap-4">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">Devis History</h1>
                </div>
                <div class="flex items-center gap-3">
                <button type="button" id="openDevisReportModal" class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Devis Reports PDF
                    </button>

                    @php $archiveRoute = ''; @endphp
                    @if(Auth::user()->hasRole('Admin')) @php $archiveRoute = route('admin.devisArchived.archiveIndex'); @endphp @endif
                    @if(Auth::user()->hasRole('Responsable de projet')) @php $archiveRoute = route('Responsable_de_projet.devisArchived.archiveIndex'); @endphp @endif
                    @if(Auth::user()->hasRole('Employee')) @php $archiveRoute = route('employee.devisArchived.archiveIndex'); @endphp @endif
                    @if(Auth::user()->hasRole('Responsable de projet et gestionnaire')) @php $archiveRoute = route('Responsable_gestionnaire.devisArchived.archiveIndex'); @endphp @endif

                    @if($archiveRoute)
                    <a href="{{ $archiveRoute }}" class="inline-flex items-center bg-white hover:bg-gray-50 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 shadow-sm">
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

            {{-- JS Message Container --}}
             <div id="messageContainer" style="display: none;" class="px-4 py-3 rounded relative" role="alert">
                <strong class="font-bold" id="messageTitle"></strong>
                <span class="block sm:inline" id="messageText"></span>
                <button type="button" onclick="this.parentElement.style.display='none';" class="absolute top-0 bottom-0 right-0 px-4 py-3 font-bold text-xl" aria-label="Close">&times;</button>
             </div>
             {{-- Session Message --}}
             @if(session('message'))
             <div id="sessionMessageContainer" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                 <strong class="font-bold">Success!</strong>
                 <span class="block sm:inline">{{ session('message') }}</span>
                 <button type="button" onclick="this.parentElement.style.display='none';" class="absolute top-0 bottom-0 right-0 px-4 py-3 font-bold text-xl" aria-label="Close">&times;</button>
             </div>
             @endif

             {{-- 3. Totals  Cards Section --}}
             @php
                 $currentYear = date('Y');
                 $Total_Accpeted = App\Models\devis_history::where('entreprise_id', Auth::user()->entreprise_id)->where('acceptanceStatus', 1)->sum('budget');
                 $Total_Declined = App\Models\devis_history::where('entreprise_id', Auth::user()->entreprise_id)->where('acceptanceStatus', 2)->sum('budget');
                 $Total_Pending = App\Models\devis_history::where('entreprise_id', Auth::user()->entreprise_id)->where('acceptanceStatus', 0)->sum('budget');
                 
                 // Current year totals
                 $Total_AccpetedCurrentYear = App\Models\devis_history::where('entreprise_id', Auth::user()->entreprise_id)
                                                        ->where('acceptanceStatus', 1)
                                                        ->whereYear('created_at', $currentYear)
                                                        ->sum('budget');
                 $Total_DeclinedCurrentYear = App\Models\devis_history::where('entreprise_id', Auth::user()->entreprise_id)
                                                        ->where('acceptanceStatus', 2)
                                                        ->whereYear('created_at', $currentYear)
                                                        ->sum('budget');
                 $Total_PendingCurrentYear = App\Models\devis_history::where('entreprise_id', Auth::user()->entreprise_id)
                                                        ->where('acceptanceStatus', 0)
                                                        ->whereYear('created_at', $currentYear)
                                                        ->sum('budget');
             @endphp
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <div id="cardAccepted" class="bg-white rounded-lg shadow border border-gray-200 p-5 flex items-center space-x-4 cursor-pointer">
                    <div class="flex-shrink-0 bg-green-100 rounded-full p-3">
                        <svg class="h-6 w-6 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p class="text-xs text-gray-500 uppercase font-medium">TOTAL ACCEPTED</p>
                        <p class="text-xl font-semibold text-green-600">€{{ number_format($Total_Accpeted, 2, ',', ' ') }}</p>
                        <p class="text-s font-semibold text-black-500 mt-1">{{ $currentYear }}: €{{ number_format($Total_AccpetedCurrentYear, 2, ',', ' ') }}</p>
                    </div>
                </div>
                 {{-- Total Declined Card --}}
                 {{-- [NEW] Added id="cardDeclined" and "cursor-pointer" --}}
                <div id="cardDeclined" class="bg-white rounded-lg shadow border border-gray-200 p-5 flex items-center space-x-4 cursor-pointer">
                    <div class="flex-shrink-0 bg-red-100 rounded-full p-3">
                        <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p class="text-xs text-gray-500 uppercase font-medium">TOTAL DECLINED</p>
                        <p class="text-xl font-semibold text-red-600">€{{ number_format($Total_Declined, 2, ',', ' ') }}</p>
                        <p class="text-s font-semibold text-black-500 mt-1">{{ $currentYear }}: €{{ number_format($Total_DeclinedCurrentYear, 2, ',', ' ') }}</p>
                    </div>
                </div>
                 {{-- Total Pending Card --}}
                 {{-- [NEW] Added id="cardPending" and "cursor-pointer" --}}
                <div id="cardPending" class="bg-white rounded-lg shadow border border-gray-200 p-5 flex items-center space-x-4 cursor-pointer">
                    <div class="flex-shrink-0 bg-yellow-100 rounded-full p-3">
                         <svg class="h-6 w-6 text-yellow-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                           <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                         </svg>
                    </div>
                    <div class="flex-1">
                        <p class="text-xs text-gray-500 uppercase font-medium">TOTAL PENDING</p>
                        <p class="text-xl font-semibold text-yellow-600">€{{ number_format($Total_Pending, 2, ',', ' ') }}</p>
                        <p class="text-s font-semibold text-black-500 mt-1">{{ $currentYear }}: €{{ number_format($Total_PendingCurrentYear, 2, ',', ' ') }}</p>
                    </div>
                </div>
            </div>

            {{-- 4. Filters Section --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                 <h6 class="text-base font-semibold text-gray-800 mb-4">Filters</h6>
                 <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                     <div>
                        <label for="clientFilter" class="block text-sm font-medium text-gray-700 mb-1">Filter by Client</label>
                        <select id="clientFilter" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3 form-select-custom">
                            <option value="">All Clients</option>
                            @foreach($clients as $client)
                            <option value="{{$client->name}}">{{$client->name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="acceptanceFilter" class="block text-sm font-medium text-gray-700 mb-1">Filter by Acceptance</label>
                        <select id="acceptanceFilter" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3 form-select-custom">
                            <option value="">All Devis</option>
                            <option value="Accepted">Accepted</option>
                            <option value="Declined">Declined</option>
                            <option value="Pending">Pending</option>
                        </select>
                    </div>
                 </div>
            </div>

            {{-- 5. Table Section [STYLES UPDATED] --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="table-responsive overflow-x-auto">
                    <table class="w-full divide-y divide-gray-200 table" id="devisTable">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-4 py-3 text-left text-sm font-semibold text-gray-600 uppercase">Devis N</th>
                                <th scope="col" class="px-4 py-3 text-left text-sm font-semibold text-gray-600 uppercase">Project Name</th>
                                <th scope="col" class="px-4 py-3 text-left text-sm font-semibold text-gray-600 uppercase">Client Name / Emails</th>
                                <th scope="col" class="px-4 py-3 text-right text-sm font-semibold text-gray-600 uppercase">Budget</th>
                                <th scope="col" class="px-4 py-3 text-right text-sm font-semibold text-gray-600 uppercase">Unbilled Budget</th>
                                <th scope="col" class="px-4 py-3 text-center text-sm font-semibold text-gray-600 uppercase">Created at</th>
                                <th scope="col" class="px-4 py-3 text-center text-sm font-semibold text-gray-600 uppercase">Status</th>
                                <th scope="col" class="px-4 py-3 text-center text-sm font-semibold text-gray-600 uppercase">Acceptance</th>
                                <th scope="col" class="px-4 py-3 text-center text-sm font-semibold text-gray-600 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($devis as $d)
                            <tr class="{{ $d->sent == 1 ? 'bg-green-50' : '' }} hover:bg-gray-50">
                                <td class="px-4 py-3 whitespace-nowrap text-sm">
                                    @if(Auth::user()->hasRole('Responsable de projet'))
                                        <a href="{{ route('Responsable_de_projet.devis.downloadDevis', $d->devis_number) }}" class="devis-link-normal">{{$entreprise->devis_prefix}}-{{$d->devis_number}}</a>
                                    @endif
                                    @if(Auth::user()->hasRole('Admin'))
                                        <a href="{{ route('admin.devis.downloadDevis', $d->devis_number) }}" class="devis-link-normal">{{$entreprise->devis_prefix}}-{{$d->devis_number}}</a>
                                    @endif
                                    @if(Auth::user()->hasRole('Employee'))
                                        <a href="{{ route('employee.devis.downloadDevis', $d->devis_number) }}" class="devis-link-normal">{{$entreprise->devis_prefix}}-{{$d->devis_number}}</a>
                                    @endif
                                    @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                        <a href="{{ route('Responsable_gestionnaire.devis.downloadDevis', $d->devis_number) }}" class="devis-link-normal">{{$entreprise->devis_prefix}}-{{$d->devis_number}}</a>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-800 font-medium" style="max-width: 200px; word-wrap: break-word; white-space: normal;">
                                    @php $project = \App\Models\Project::where('id', $d->project_id)->first(); @endphp
                                    @if($project)
                                        @if($d->acceptanceStatus == 1 || $d->Existing_project_name != null)
                                            @if(Auth::user()->hasRole('Admin')) <a href="{{route('admin.projects.show', $project->id)}}" class="text-blue-600 hover:text-blue-700">{{$project->name}}</a> @endif
                                            @if(Auth::user()->hasRole('Responsable de projet')) <a href="{{route('Responsable_de_projet.projects.show', $project->id)}}" class="text-blue-600 hover:text-blue-700">{{$project->name}}</a> @endif
                                            @if(Auth::user()->hasRole('Employee')) <a href="{{route('employee.projects.show', $project->id)}}" class="text-blue-600 hover:text-blue-700">{{$project->name}}</a> @endif
                                            @if(Auth::user()->hasRole('Responsable de projet et gestionnaire')) <a href="{{route('Responsable_gestionnaire.projects.show', $project->id)}}" class="text-blue-600 hover:text-blue-700">{{$project->name}}</a> @endif
                                        @else {{$project->name}} @endif
                                    @else N/A @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-800 font-medium" style="max-width: 200px; word-wrap: break-word; white-space: normal;">
                                    @php $client = \App\Models\Client::where('id', $d->client_id)->first(); @endphp
                                    @if ($d->emails)
                                        @php $emailArray = array_map('trim', explode(',', $d->emails)); @endphp
                                        @foreach ($emailArray as $email) <span class="block">{{ $email }}</span> @endforeach
                                    @elseif ($client)
                                        @if(Auth::user()->hasRole('Admin')) <a href="{{route('admin.clients.show', $client->id)}}" class="text-blue-600 hover:text-blue-700">{{$client->name}}</a> @endif
                                        @if(Auth::user()->hasRole('Responsable de projet')) <a href="{{route('Responsable_de_projet.clients.show', $client->id)}}" class="text-blue-600 hover:text-blue-700">{{$client->name}}</a> @endif
                                        @if(Auth::user()->hasRole('Employee')) <a href="{{route('employee.clients.show', $client->id)}}" class="text-blue-600 hover:text-blue-700">{{$client->name}}</a> @endif
                                        @if(Auth::user()->hasRole('Responsable de projet et gestionnaire')) <a href="{{route('Responsable_gestionnaire.clients.show', $client->id)}}" class="text-blue-600 hover:text-blue-700">{{$client->name}}</a> @endif
                                    @else N/A @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm font-semibold text-gray-900 text-right">
                                    €{{ number_format($d->budget, 2, ',', ' ') }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm font-semibold text-gray-900 text-right">
                                    @php
                                        $devisInvoicesTotal = \App\Models\invoices_history::where('devis_id', $d->id)->where('Payment_status', '!=', -1)->sum('total_htva');
                                        $unbilledBudget = ($d->budget ?? 0) - ($devisInvoicesTotal ?? 0);
                                    @endphp
                                    €{{ number_format(max($unbilledBudget, 0), 2, ',', ' ') }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600 text-center whitespace-nowrap">
                                    {{ $d->created_at ? \Carbon\Carbon::parse($d->created_at)->format('Y-m-d') : '' }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-center">
                                    @if($d->acceptanceStatus == 1)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800"> Accepted </span>
                                    @elseif($d->acceptanceStatus == 2 )
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800"> Declined </span>
                                    @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800"> Pending </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-center">
                                    <div class="flex flex-col items-center space-y-1">
                                        @if(Auth::user()->hasRole('Responsable de projet'))
                                        <form action="{{route('Responsable_de_projet.devis.acceptDevis', $d->id) }}" method="post" onsubmit="return confirm('Mark as Accepted?');"> @csrf <button type="submit" class="w-full text-white bg-green-500 hover:bg-green-600 font-medium rounded text-xs px-2 py-1 text-center">Accept</button> </form>
                                        <form action="{{route('Responsable_de_projet.devis.declineDevis', $d->id) }}" method="post" onsubmit="return confirm('Mark as Declined?');"> @csrf <button type="submit" class="w-full text-white bg-red-500 hover:bg-red-600 font-medium rounded text-xs px-2 py-1 text-center">Decline</button> </form>
                                        @endif
                                        @if(Auth::user()->hasRole('Admin'))
                                        <form action="{{route('admin.devis.acceptDevis', $d->id) }}" method="post" onsubmit="return confirm('Mark as Accepted?');"> @csrf <button type="submit" class="w-full text-white bg-green-500 hover:bg-green-600 font-medium rounded text-xs px-2 py-1 text-center">Accept</button> </form>
                                        <form action="{{route('admin.devis.declineDevis', $d->id) }}" method="post" onsubmit="return confirm('Mark as Declined?');"> @csrf <button type="submit" class="w-full text-white bg-red-500 hover:bg-red-600 font-medium rounded text-xs px-2 py-1 text-center">Decline</button> </form>
                                        @endif
                                        @if(Auth::user()->hasRole('Employee'))
                                        <form action="{{route('employee.devis.acceptDevis', $d->id) }}" method="post" onsubmit="return confirm('Mark as Accepted?');"> @csrf <button type="submit" class="w-full text-white bg-green-500 hover:bg-green-600 font-medium rounded text-xs px-2 py-1 text-center">Accept</button> </form>
                                        <form action="{{route('employee.devis.declineDevis', $d->id) }}" method="post" onsubmit="return confirm('Mark as Declined?');"> @csrf <button type="submit" class="w-full text-white bg-red-500 hover:bg-red-600 font-medium rounded text-xs px-2 py-1 text-center">Decline</button> </form>
                                        @endif
                                        @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                        <form action="{{route('Responsable_gestionnaire.devis.acceptDevis', $d->id) }}" method="post" onsubmit="return confirm('Mark as Accepted?');"> @csrf <button type="submit" class="w-full text-white bg-green-500 hover:bg-green-600 font-medium rounded text-xs px-2 py-1 text-center">Accept</button> </form>
                                        <form action="{{route('Responsable_gestionnaire.devis.declineDevis', $d->id) }}" method="post" onsubmit="return confirm('Mark as Declined?');"> @csrf <button type="submit" class="w-full text-white bg-red-500 hover:bg-red-600 font-medium rounded text-xs px-2 py-1 text-center">Decline</button> </form>
                                        @endif
                                    </div>
                                </td>
                                
                                <td class="px-2 py-3">
                                    <div class="flex flex-col items-center gap-2">
                                
                                        {{-- 2x2 Grid for first 4 buttons --}}
                                        <div class="grid grid-cols-2 gap-1.5 w-full">
                                            
                                            {{-- 1. Download Button --}}
                                            @php $dlRoute = '#'; @endphp
                                            @if(Auth::user()->hasRole('Admin')) @php $dlRoute = route('admin.devis.downloadDevis', $d->devis_number); @endphp @endif
                                            @if(Auth::user()->hasRole('Responsable de projet')) @php $dlRoute = route('Responsable_de_projet.devis.downloadDevis', $d->devis_number); @endphp @endif
                                            @if(Auth::user()->hasRole('Employee')) @php $dlRoute = route('employee.devis.downloadDevis', $d->devis_number); @endphp @endif
                                            @if(Auth::user()->hasRole('Responsable de projet et gestionnaire')) @php $dlRoute = route('Responsable_gestionnaire.devis.downloadDevis', $d->devis_number); @endphp @endif
                                            <a class="w-full text-center text-white bg-blue-600 hover:bg-blue-700 font-medium rounded text-xs px-2 py-1" href="{{ $dlRoute }}">Download</a>

                                            {{-- 2. Send Button --}}
                                            @can('Send devis')
                                                @php $sendRoute = '#'; @endphp
                                                @if(Auth::user()->hasRole('Admin')) @php $sendRoute = route('admin.devis.sendDevis'); @endphp @endif
                                                @if(Auth::user()->hasRole('Responsable de projet')) @php $sendRoute = route('Responsable_de_projet.devis.sendDevis'); @endphp @endif
                                                @if(Auth::user()->hasRole('Employee')) @php $sendRoute = route('employee.devis.sendDevis'); @endphp @endif
                                                @if(Auth::user()->hasRole('Responsable de projet et gestionnaire')) @php $sendRoute = route('Responsable_gestionnaire.devis.sendDevis'); @endphp @endif
                                                <form action="{{ $sendRoute }}" method="post" class="inline-block w-full"> @csrf <input type="hidden" name="devis_id" value="{{ $d->id }}"> <button type="submit" class="w-full text-white bg-gray-700 hover:bg-gray-800 font-medium rounded text-xs px-2 py-1 text-center">Send</button> </form>
                                            @else
                                                <div class="w-full"></div> @endcan
                                
                                            {{-- 3. Delete Button --}}
                                            @can('Delete devis')
                                                @php $deleteRoute = '#'; @endphp
                                                @if(Auth::user()->hasRole('Admin')) @php $deleteRoute = route('admin.devis.destroy', $d->id); @endphp @endif
                                                @if(Auth::user()->hasRole('Responsable de projet')) @php $deleteRoute = route('Responsable_de_projet.devis.destroy', $d->id); @endphp @endif
                                                @if(Auth::user()->hasRole('Employee')) @php $deleteRoute = route('employee.devis.destroy', $d->id); @endphp @endif
                                                @if(Auth::user()->hasRole('Responsable de projet et gestionnaire')) @php $deleteRoute = route('Responsable_gestionnaire.devis.destroy', $d->id); @endphp @endif
                                                <form action="{{$deleteRoute}}" method="post" onsubmit="return confirm('Are you sure?');" class="inline-block w-full"> @csrf @method('DELETE') <button type="submit" class="w-full text-white bg-red-600 hover:bg-red-700 font-medium rounded text-xs px-2 py-1 text-center">Delete</button> </form>
                                            @else
                                                 <div class="w-full"></div> @endcan
                                
                                            {{-- 4. Archive Button --}}
                                            @php $archiveFormRoute = '#'; @endphp
                                            @if(Auth::user()->hasRole('Admin')) @php $archiveFormRoute = route('admin.devis.archive', $d->id); @endphp @endif
                                            @if(Auth::user()->hasRole('Responsable de projet')) @php $archiveFormRoute = route('Responsable_de_projet.devis.archive', $d->id); @endphp @endif
                                            @if(Auth::user()->hasRole('Employee')) @php $archiveFormRoute = route('employee.devis.archive', $d->id); @endphp @endif
                                            @if(Auth::user()->hasRole('Responsable de projet et gestionnaire')) @php $archiveFormRoute = route('Responsable_gestionnaire.devis.archive', $d->id); @endphp @endif
                                            <form action="{{$archiveFormRoute}}" method="post" onsubmit="return confirm('Are you sure?');" class="inline-block w-full"> @csrf <button type="submit" title="Archive" class="w-full p-1 rounded bg-gray-500 text-white hover:bg-gray-600 font-medium text-xs flex items-center justify-center gap-1"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" /></svg> Archive</button> </form>
                                        </div>
                                
                                        {{-- Icon buttons below the grid --}}
                                        <div class="flex items-center justify-center gap-1.5 w-full">
                                            
                                            {{-- Duplicate Button --}}
                                            @php $duplicateRoute = '#'; @endphp
                                            @if(Auth::user()->hasRole('Admin')) @php $duplicateRoute = route('admin.devis.duplicateDevis', $d->id); @endphp @endif
                                            @if(Auth::user()->hasRole('Responsable de projet')) @php $duplicateRoute = route('Responsable_de_projet.devis.duplicateDevis', $d->id); @endphp @endif
                                            @if(Auth::user()->hasRole('Employee')) @php $duplicateRoute = route('employee.devis.duplicateDevis', $d->id); @endphp @endif
                                            @if(Auth::user()->hasRole('Responsable de projet et gestionnaire')) @php $duplicateRoute = route('Responsable_gestionnaire.devis.duplicateDevis', $d->id); @endphp @endif
                                            <a href="{{$duplicateRoute}}" title="Duplicate" class="p-1.5 rounded-md bg-cyan-500 text-white hover:bg-cyan-600"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg></a>
                                
                                            {{-- Edit Button --}}
                                            @php $editRoute = '#'; @endphp
                                            @if(Auth::user()->hasRole('Admin')) @php $editRoute = route('admin.devis.editDevis', $d->id); @endphp @endif
                                            @if(Auth::user()->hasRole('Responsable de projet')) @php $editRoute = route('Responsable_de_projet.devis.editDevis', $d->id); @endphp @endif
                                            @if(Auth::user()->hasRole('Employee')) @php $editRoute = route('employee.devis.editDevis', $d->id); @endphp @endif
                                            @if(Auth::user()->hasRole('Responsable de projet et gestionnaire')) @php $editRoute = route('Responsable_gestionnaire.devis.editDevis', $d->id); @endphp @endif
                                            <a href="{{$editRoute}}" title="Edit" class="p-1.5 rounded-md bg-cyan-500 text-white hover:bg-cyan-600"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"> <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /> </svg></a>
                                        </div>
                                
                                    </div>
                                </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center px-4 py-6 text-gray-500">
                                        No devis found matching your criteria.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

{{-- Modals --}}
<div id="devisReportModal" class="fixed inset-0 z-40 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md mx-4">
        <div class="flex justify-between items-center p-4 border-b">
            <h5 class="text-lg font-semibold text-gray-800" id="devisReportModalLabel">Download Devis Reports</h5>
            <button type="button" id="closeDevisReportModal" class="text-gray-400 hover:text-gray-600">&times;</button>
        </div>
        <div class="p-6">
            <div class="mb-3">
                <label class="block text-sm font-medium text-gray-700">Choose statuses (optional):</label>
                <div class="mt-2 space-y-2">
                    <div class="flex items-center">
                        <input id="status_accepted" name="devisReportStatus[]" type="checkbox" value="accepted" class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <label for="status_accepted" class="ml-2 block text-sm text-gray-900">Accepted</label>
                    </div>
                    <div class="flex items-center">
                        <input id="status_declined" name="devisReportStatus[]" type="checkbox" value="declined" class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <label for="status_declined" class="ml-2 block text-sm text-gray-900">Declined</label>
                    </div>
                    <div class="flex items-center">
                        <input id="status_pending" name="devisReportStatus[]" type="checkbox" value="pending" class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <label for="status_pending" class="ml-2 block text-sm text-gray-900">Pending</label>
                    </div>
                    <hr class="my-3">
                    <div class="flex items-center">
                        <input id="include_archived" type="checkbox" value="1" class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <label for="include_archived" class="ml-2 block text-sm text-gray-900">Include archived</label>
                    </div>
                </div>
            </div>
            <small class="text-gray-500">Date range (optional) will be applied from the fields at the top.</small>
        </div>
        <div class="flex justify-end items-center p-4 bg-gray-50 border-t rounded-b-lg space-x-2">
            <button type="button" id="cancelDevisReportBtn" class="px-4 py-2 bg-white border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
            <button type="button" id="confirmDevisReport" class="px-4 py-2 bg-blue-600 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-blue-700">Download PDF</button>
        </div>
    </div>
</div>
<div id="sendDevisModal" class="fixed inset-0 z-40 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-lg mx-4">
        <div class="flex justify-between items-center p-4 border-b">
            <h5 class="text-lg font-semibold text-gray-800" id="sendDevisModalLabel">Send Devis</h5>
            <button type="button" id="closeSendDevisModal" class="text-gray-400 hover:text-gray-600">&times;</button>
        </div>
        <div class="p-6">
            <form id="sendDevisForm" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="devis_id" id="devis_id">
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Select Recipients:</label>
                    <div id="recipientsList" class="border p-3 rounded-md h-32 overflow-y-auto bg-gray-50 space-y-2">
                         </div>
                </div>

                <div class="mb-3">
                    <label for="attachments" class="block text-sm font-medium text-gray-700 mb-1">Additional Attachments (PDF only):</label>
                    <input type="file" class="block w-full text-sm text-gray-700 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none file:bg-gray-100 file:border-0 file:px-3 file:py-2" id="attachments" name="attachments[]" multiple accept=".pdf">
                    <small class="text-gray-500">Select PDF files to be merged with the devis</small>
                </div>
            </form>
        </div>
        <div class="flex justify-end items-center p-4 bg-gray-50 border-t rounded-b-lg space-x-2">
            <button type="button" id="cancelSendDevisModal" class="px-4 py-2 bg-white border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50">Close</button>
            <button type="button" class="px-4 py-2 bg-blue-600 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-blue-700" id="confirmSend">Send Devis</button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // --- 1. FILTER LOGIC ---
    const clientFilter = document.getElementById('clientFilter');
    const acceptanceFilter = document.getElementById('acceptanceFilter');
    const tableBody = document.querySelector('#devisTable tbody');
    const allRows = tableBody.querySelectorAll('tr');
    
    // [NEW] Get the new clickable cards
    const cardAccepted = document.getElementById('cardAccepted');
    const cardDeclined = document.getElementById('cardDeclined');
    const cardPending = document.getElementById('cardPending');
    
    // [NEW] Add click listeners for the stat cards
    if (cardAccepted) {
        cardAccepted.addEventListener('click', function() {
            acceptanceFilter.value = 'Accepted'; // Set dropdown value
            filterTable(); // Call existing filter function
        });
    }

    if (cardDeclined) {
        cardDeclined.addEventListener('click', function() {
            acceptanceFilter.value = 'Declined';
            filterTable();
        });
    }

    if (cardPending) {
        cardPending.addEventListener('click', function() {
            acceptanceFilter.value = 'Pending';
            filterTable();
        });
    }
    
    function filterTable() {
        const clientValue = clientFilter.value.trim();
        const acceptanceValue = acceptanceFilter.value.trim();
        let hasVisibleRows = false;

        allRows.forEach(row => {
            if (row.querySelector('td[colspan="9"]')) {
                return; // Skip the "no results" row
            }

            const clientCell = row.querySelector('td:nth-child(3)');
            const statusCell = row.querySelector('td:nth-child(7)'); // 7th column is Status

            if (!clientCell || !statusCell || !statusCell.querySelector('span')) {
                return; // Skip row if cells are missing
            }

            const clientName = clientCell.textContent.trim();
            const statusText = statusCell.querySelector('span').textContent.trim();

            const clientMatch = clientValue === "" || clientName === clientValue;
            const acceptanceMatch = acceptanceValue === "" || statusText === acceptanceValue;

            if (clientMatch && acceptanceMatch) {
                row.style.display = ""; // Show row
                hasVisibleRows = true;
            } else {
                row.style.display = "none"; // Hide row
            }
        });

        // Toggle the "No results" message
        const noResultsRow = tableBody.querySelector('td[colspan="9"]');
        if (noResultsRow) {
            noResultsRow.parentElement.style.display = hasVisibleRows ? 'none' : 'table-row';
        }
    }
    
    if (clientFilter) clientFilter.addEventListener('change', filterTable);
    if (acceptanceFilter) acceptanceFilter.addEventListener('change', filterTable);
    
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

    function handleSendClick(event) {
        event.preventDefault();
        const form = event.target.closest('form');
        const devisId = form.querySelector('input[name="devis_id"]').value;
        const formAction = form.getAttribute('action');
        
        document.getElementById('devis_id').value = devisId;
        const recipientsList = document.getElementById('recipientsList');
        recipientsList.innerHTML = '<div class="text-center">Loading...</div>';
        
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
                    recipientsList.innerHTML = '<p class="text-gray-500">No contacts found for this client</p>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                recipientsList.innerHTML = '<p class="text-red-600">Error loading contacts</p>';
            });
        
        document.getElementById('sendDevisForm').action = formAction;
        if (sendDevisModalEl) sendDevisModalEl.classList.remove('hidden');
    }

    document.querySelectorAll('form[action*="sendDevis"]').forEach(form => {
        const submitButton = form.querySelector('button[type="submit"]');
        if (submitButton) {
            submitButton.addEventListener('click', handleSendClick);
        }
    });

    document.getElementById('confirmSend').addEventListener('click', function() {
        const form = document.getElementById('sendDevisForm');
        const devisId = document.getElementById('devis_id').value;
        const selectedRecipients = Array.from(document.querySelectorAll('input[name="recipients[]"]:checked'))
            .map(checkbox => checkbox.value);
        
        if (selectedRecipients.length === 0) {
            alert('Please select at least one recipient');
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
                alert('Devis sent successfully!');
                if (sendDevisModalEl) sendDevisModalEl.classList.add('hidden');
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
