<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoices History - BizManager</title>
    @vite('resources/css/app.css') 
    @livewireStyles
    <meta name="csrf-token" content="{{ csrf_token() }}">
    {{-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet"> --}}
<style>
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
        select:invalid {
            color: #9ca3af;
        }
        option {
            color: #111827;
        }
        /* Ensure dropdown menus (if any) appear correctly */
         .dropdown-menu {
             display: none; /* Hidden by default */
             position: absolute;
             z-index: 1000; /* Ensure it's above other content */
         }
         .dropdown-menu.show {
             display: block; /* Show when toggled */
         }

         /* Link styling like invoices */
        .devis-link-normal {
             color: #2563eb; /* blue-600 */
             font-weight: 500; /* medium */
        }
         .devis-link-normal:hover {
             color: #1d4ed8; /* blue-800 */
             text-decoration: underline;
         }

</style>
</head>

<body class="bg-gray-100 flex h-screen overflow-hidden font-sans">
    {{-- Sidebar Component --}}
    <x-dashboard.sidebar />

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
                        {{-- Download Icon --}}
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
                $Total_Accpeted = App\Models\devis_history::where('entreprise_id', Auth::user()->entreprise_id)->where('acceptanceStatus', 1)->sum('budget');
                $Total_Declined = App\Models\devis_history::where('entreprise_id', Auth::user()->entreprise_id)->where('acceptanceStatus', 2)->sum('budget');
                $Total_Pending = App\Models\devis_history::where('entreprise_id', Auth::user()->entreprise_id)->where('acceptanceStatus', 0)->sum('budget');
            @endphp
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                {{-- Total Accepted Card --}}
                <div class="bg-white rounded-lg shadow border border-gray-200 p-5 flex items-center space-x-4">
                    <div class="flex-shrink-0 bg-green-100 rounded-full p-3">
                        {{-- Check Icon --}}
                        <svg class="h-6 w-6 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase font-medium">TOTAL ACCEPTED</p>
                        <p class="text-xl font-semibold text-green-600">€{{ number_format($Total_Accpeted, 2, ',', ' ') }}</p> 
                    </div>
                </div>
                 {{-- Total Declined Card --}}
                <div class="bg-white rounded-lg shadow border border-gray-200 p-5 flex items-center space-x-4">
                    <div class="flex-shrink-0 bg-red-100 rounded-full p-3">
                        {{-- Warning Icon --}}
                        <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase font-medium">TOTAL DECLINED</p>
                        <p class="text-xl font-semibold text-red-600">€{{ number_format($Total_Declined, 2, ',', ' ') }}</p> 
                    </div>
                </div>
                 {{-- Total Pending Card --}}
                <div class="bg-white rounded-lg shadow border border-gray-200 p-5 flex items-center space-x-4">
                    <div class="flex-shrink-0 bg-yellow-100 rounded-full p-3">
                         {{-- Clock Icon --}}
                        <svg class="h-6 w-6 text-yellow-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                           <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase font-medium">TOTAL PENDING</p>
                        <p class="text-xl font-semibold text-yellow-600">€{{ number_format($Total_Pending, 2, ',', ' ') }}</p> {{-- Adjusted formatting --}}
                    </div>
                </div>
            </div>

            {{-- 4. Filters Section --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                 <h6 class="text-base font-semibold text-gray-800 mb-4">Filters</h6>
                 <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4"> {{-- Adjusted grid cols --}}
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

            {{-- 5. Table Section --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-full divide-y divide-gray-200" >
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Devis N</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Project Name</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Client Name / Emails</th>
                                <th scope="col" class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Budget</th>
                                <th scope="col" class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Unbilled Budget</th>
                                <th scope="col" class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Created at</th>
                                <th scope="col" class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Acceptance</th>
                                <th scope="col" class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($devis as $d)
                            <tr class="{{ $d->sent == 1 ? 'bg-green-50' : '' }} hover:bg-gray-50">
                                <td class="px-4 py-3 whitespace-nowrap text-sm">
                                    {{-- Use devis-link-normal style --}}
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
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-800 font-medium">
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
                                <td class="px-4 py-3 text-sm text-gray-800 font-medium">
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
                                        $devisInvoicesTotal = \App\Models\invoices_history::where('devis_id', $d->id)->sum('total_htva');
                                        $unbilledBudget = ($d->budget ?? 0) - ($devisInvoicesTotal ?? 0);
                                    @endphp
                                    €{{ number_format(max($unbilledBudget, 0), 2, ',', ' ') }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600 text-center">
                                    {{ $d->created_at ? \Carbon\Carbon::parse($d->created_at)->format('Y-m-d') : '' }}
                                    <span class="block text-xs">{{ $d->created_at ? \Carbon\Carbon::parse($d->created_at)->format('H:i:s') : '' }}</span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-center">
                                    {{-- Status Pills matching invoice style --}}
                                    @if($d->acceptanceStatus == 1)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800"> Accepted </span>
                                    @elseif($d->acceptanceStatus == 2 )
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800"> Declined </span>
                                    @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800"> Pending </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-center">
                                    {{-- Acceptance buttons styled like small action buttons --}}
                                    <div class="flex flex-col items-center space-y-1">
                                        @if(Auth::user()->hasRole('Responsable de projet'))
                                        <form action="{{route('Responsable_de_projet.devis.acceptDevis', $d->id) }}" method="post" onsubmit="return confirm('Mark as Accepted?');"> @csrf <button type="submit" class="w-full text-white bg-green-500 hover:bg-green-600 font-medium rounded text-xs px-2 py-1 text-center">Accept</button> </form>
                                        <form action="{{route('Responsable_de_projet.devis.declineDevis', $d->id) }}" method="post" onsubmit="return confirm('Mark as Declined?');"> @csrf <button type="submit" class="w-full text-white bg-red-500 hover:bg-red-600 font-medium rounded text-xs px-2 py-1 text-center">Decline</button> </form>
                                        @endif
                                        {{-- Repeat for other roles --}}
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
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-right">
                                    {{-- Action Buttons - styled like invoices --}}
                                    <div class="flex items-center justify-end gap-1.5">
                                        @if(Auth::user()->hasRole('Admin'))
                                            <a class="text-white bg-blue-600 hover:bg-blue-700 font-medium rounded text-xs px-2 py-1 text-center" href="{{ route('admin.devis.downloadDevis', $d->devis_number) }}">Download</a>
                                            @can('Send devis')
                                            <form action="{{ route('admin.devis.sendDevis') }}" method="post" class="inline-block"> @csrf <input type="hidden" name="devis_id" value="{{ $d->id }}"> <button type="submit" class="text-white bg-gray-700 hover:bg-gray-800 font-medium rounded text-xs px-2 py-1 text-center">Send</button> </form>
                                            @endcan
                                            @can('Delete devis')
                                            <form action="{{route('admin.devis.destroy', $d->id)}}" method="post" onsubmit="return confirm('Are you sure?');" class="inline-block"> @csrf @method('DELETE') <button type="submit" class="text-white bg-red-600 hover:bg-red-700 font-medium rounded text-xs px-2 py-1 text-center">Delete</button> </form>
                                            @endcan
                                            <form action="{{route('admin.devis.archive', $d->id)}}" method="post" onsubmit="return confirm('Are you sure?');" class="inline-block"> @csrf <button type="submit" title="Archive" class="p-1 rounded bg-gray-500 text-white hover:bg-gray-600"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" /></svg></button> </form>
                                            <a href="{{route('admin.devis.duplicateDevis', $d->id)}}" title="Duplicate" class="p-1 rounded bg-cyan-500 text-white hover:bg-cyan-600"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg></a>
                                            <a href="{{route('admin.devis.editDevis', $d->id)}}" title="Edit" class="p-1 rounded bg-cyan-500 text-white hover:bg-cyan-600"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"> <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /> </svg></a>
                                        @endif
                                        {{-- Repeat structure for other roles --}}
                                        @if(Auth::user()->hasRole('Responsable de projet'))
                                            <a class="text-white bg-blue-600 hover:bg-blue-700 font-medium rounded text-xs px-2 py-1 text-center" href="{{ route('Responsable_de_projet.devis.downloadDevis', $d->devis_number) }}">Download</a>
                                            @can('Send devis')
                                            <form action="{{ route('Responsable_de_projet.devis.sendDevis') }}" method="post" class="inline-block"> @csrf <input type="hidden" name="devis_id" value="{{ $d->id }}"> <button type="submit" class="text-white bg-gray-700 hover:bg-gray-800 font-medium rounded text-xs px-2 py-1 text-center">Send</button> </form>
                                            @endcan
                                            @can('Delete devis')
                                            <form action="{{route('Responsable_de_projet.devis.destroy', $d->id)}}" method="post" onsubmit="return confirm('Are you sure?');" class="inline-block"> @csrf @method('DELETE') <button type="submit" class="text-white bg-red-600 hover:bg-red-700 font-medium rounded text-xs px-2 py-1 text-center">Delete</button> </form>
                                            @endcan
                                            <form action="{{route('Responsable_de_projet.devis.archive', $d->id)}}" method="post" onsubmit="return confirm('Are you sure?');" class="inline-block"> @csrf <button type="submit" title="Archive" class="p-1 rounded bg-gray-500 text-white hover:bg-gray-600"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" /></svg></button> </form>
                                            <a href="{{route('Responsable_de_projet.devis.duplicateDevis', $d->id)}}" title="Duplicate" class="p-1 rounded bg-cyan-500 text-white hover:bg-cyan-600"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg></a>
                                            <a href="{{route('Responsable_de_projet.devis.editDevis', $d->id)}}" title="Edit" class="p-1 rounded bg-cyan-500 text-white hover:bg-cyan-600"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"> <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /> </svg></a>
                                        @endif
                                        @if(Auth::user()->hasRole('Employee'))
                                            <a class="text-white bg-blue-600 hover:bg-blue-700 font-medium rounded text-xs px-2 py-1 text-center" href="{{ route('employee.devis.downloadDevis', $d->devis_number) }}">Download</a>
                                            @can('Send devis')
                                            <form action="{{ route('employee.devis.sendDevis') }}" method="post" class="inline-block"> @csrf <input type="hidden" name="devis_id" value="{{ $d->id }}"> <button type="submit" class="text-white bg-gray-700 hover:bg-gray-800 font-medium rounded text-xs px-2 py-1 text-center">Send</button> </form>
                                            @endcan
                                            @can('Delete devis')
                                            <form action="{{route('employee.devis.destroy', $d->id)}}" method="post" onsubmit="return confirm('Are you sure?');" class="inline-block"> @csrf @method('DELETE') <button type="submit" class="text-white bg-red-600 hover:bg-red-700 font-medium rounded text-xs px-2 py-1 text-center">Delete</button> </form>
                                            @endcan
                                            <form action="{{route('employee.devis.archive', $d->id)}}" method="post" onsubmit="return confirm('Are you sure?');" class="inline-block"> @csrf <button type="submit" title="Archive" class="p-1 rounded bg-gray-500 text-white hover:bg-gray-600"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" /></svg></button> </form>
                                            <a href="{{route('employee.devis.duplicateDevis', $d->id)}}" title="Duplicate" class="p-1 rounded bg-cyan-500 text-white hover:bg-cyan-600"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg></a>
                                            <a href="{{route('employee.devis.editDevis', $d->id)}}" title="Edit" class="p-1 rounded bg-cyan-500 text-white hover:bg-cyan-600"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"> <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /> </svg></a>
                                        @endif
                                        @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                            <a class="text-white bg-blue-600 hover:bg-blue-700 font-medium rounded text-xs px-2 py-1 text-center" href="{{ route('Responsable_gestionnaire.devis.downloadDevis', $d->devis_number) }}">Download</a>
                                            @can('Send devis')
                                            <form action="{{ route('Responsable_gestionnaire.devis.sendDevis') }}" method="post" class="inline-block"> @csrf <input type="hidden" name="devis_id" value="{{ $d->id }}"> <button type="submit" class="text-white bg-gray-700 hover:bg-gray-800 font-medium rounded text-xs px-2 py-1 text-center">Send</button> </form>
                                            @endcan
                                            @can('Delete devis')
                                            <form action="{{route('Responsable_gestionnaire.devis.destroy', $d->id)}}" method="post" onsubmit="return confirm('Are you sure?');" class="inline-block"> @csrf @method('DELETE') <button type="submit" class="text-white bg-red-600 hover:bg-red-700 font-medium rounded text-xs px-2 py-1 text-center">Delete</button> </form>
                                            @endcan
                                            <form action="{{route('Responsable_gestionnaire.devis.archive', $d->id)}}" method="post" onsubmit="return confirm('Are you sure?');" class="inline-block"> @csrf <button type="submit" title="Archive" class="p-1 rounded bg-gray-500 text-white hover:bg-gray-600"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" /></svg></button> </form>
                                            <a href="{{route('Responsable_gestionnaire.devis.duplicateDevis', $d->id)}}" title="Duplicate" class="p-1 rounded bg-cyan-500 text-white hover:bg-cyan-600"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg></a>
                                            <a href="{{route('Responsable_gestionnaire.devis.editDevis', $d->id)}}" title="Edit" class="p-1 rounded bg-cyan-500 text-white hover:bg-cyan-600"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"> <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /> </svg></a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                             {{-- Empty state row --}}
                             @if($devis->isEmpty())
                                <tr>
                                    <td colspan="9" class="text-center px-4 py-6 text-gray-500">
                                        No devis found matching your criteria.
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>