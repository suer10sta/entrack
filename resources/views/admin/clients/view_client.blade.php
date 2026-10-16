<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <x-favicon />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.Client') }} {{ __('messages.Details') ?? 'Details' }}: {{ $client->name }}</title>
    @vite('resources/css/app.css')

    {{-- This CSS block styles the "editable" fields to look like text until double-clicked --}}
    <style>
        .editable[readonly],
        .editable-contact[readonly] {
            border-color: #e2e8f0; /* slate-200 */
            background-color: #f8fafc; /* slate-50 */
            padding-left: 0.75rem;
            padding-right: 0.75rem;
            cursor: pointer;
            font-weight: 500;
            color: #0f172a; /* slate-900 */
        }
        .editable[readonly]:hover,
        .editable-contact[readonly]:hover {
            color: #2D7080; /* brand-secondary */
            background-color: #f1f5f9; /* slate-100 */
        }
        .editable:not([readonly]),
        .editable-contact:not([readonly]) {
            border-color: #2D7080 !important;
            box-shadow: 0 0 0 2px rgba(45, 112, 128, 0.25) !important;
            background-color: #ffffff !important;
        }

        /* Style for editable table cells */
        td.editable {
            cursor: pointer;
        }
        td.editable:hover {
            background-color: #f8fafc; /* slate-50 */
        }

        /* Section collapse/expand chevron */
        .section-chevron.rotate-180 { transform: rotate(180deg); }

        /* Native date inputs: match the custom control family */
        input[type="date"]::-webkit-calendar-picker-indicator {
            opacity: 0.55;
            cursor: pointer;
        }
        input[type="date"]:hover::-webkit-calendar-picker-indicator {
            opacity: 0.85;
        }

        /* Mobile: Projects/Invoices tables become modern 2-column cards */
        @media (max-width: 1024px) {
            #clientProjectsTable thead,
            #clientInvoicesTable thead {
                display: none;
            }

            #clientProjectsTable tbody,
            #clientInvoicesTable tbody {
                display: block;
            }

            #clientProjectsTable tbody tr,
            #clientInvoicesTable tbody tr {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 10px;
                margin-bottom: 16px;
                border: 1px solid rgba(148, 163, 184, 0.55);
                border-radius: 16px;
                background: linear-gradient(180deg, rgba(255,255,255,0.98) 0%, rgba(248,250,252,0.98) 100%);
                overflow: hidden;
                box-shadow: 0 18px 45px rgba(15, 23, 42, 0.10), 0 3px 10px rgba(15, 23, 42, 0.08);
                padding: 12px;
            }

            #clientProjectsTable tbody td,
            #clientInvoicesTable tbody td {
                display: block;
                width: 100%;
                padding: 10px 10px;
                border: 1px solid rgba(226, 232, 240, 0.9);
                border-radius: 12px;
                background: rgba(255,255,255,0.75);
                text-align: left !important;
                white-space: normal !important;
            }

            #clientProjectsTable tbody td[data-span="2"],
            #clientInvoicesTable tbody td[data-span="2"] {
                grid-column: 1 / -1;
            }

            #clientProjectsTable tbody td[data-label]::before,
            #clientInvoicesTable tbody td[data-label]::before {
                content: attr(data-label);
                display: block;
                font-size: 11px;
                font-weight: 700;
                letter-spacing: .04em;
                text-transform: uppercase;
                color: #64748b;
                margin-bottom: 4px;
            }

            #clientProjectsTable tbody td[data-label] .client-project-actions,
            #clientInvoicesTable tbody td[data-label] .client-invoice-actions {
                justify-content: center !important;
            }
        }
    </style>
</head>

<body class="dt-app-shell bg-[#f8fafc] text-slate-900 antialiased flex h-screen overflow-hidden font-sans">
    {{-- Sidebar --}}
    @if(Auth::user()->hasRole('Admin'))
        <x-dashboard.sidebar />
    @else
        <x-employees.sidebar />
    @endif

    <div class="flex flex-col flex-1 overflow-y-auto">
        {{-- Navbar --}}
        <x-dashboard.navbar />

        {{-- Main Content --}}
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-[#f8fafc] p-4 sm:p-6 lg:px-20 lg:py-8">
            <div class="space-y-5">

                {{-- Back route computation --}}
                @php
                    $backRoute = route('admin.clients.index');
                    if (Auth::user()->hasRole('Responsable de projet'))
                        $backRoute = route('Responsable_de_projet.clients.index');
                    elseif (Auth::user()->hasRole('Employee'))
                        $backRoute = route('employee.clients.index');
                    elseif (Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                        $backRoute = route('Responsable_gestionnaire.clients.index');
                @endphp

                {{-- Page Header --}}
                <div class="flex items-center gap-3 mb-5">
                    <a href="{{ $backRoute }}" title="{{ __('messages.Clients') }}" aria-label="{{ __('messages.Clients') }}"
                       class="dt-btn dt-btn-ghost !px-3 flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                             stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                        </svg>
                    </a>
                    <div class="min-w-0">
                        <p class="dt-overline mb-1">{{ __('messages.Clients') }}</p>
                        <div class="flex items-center gap-2 flex-wrap">
                            <h1 class="text-[22px] sm:text-[24px] font-bold tracking-tight text-[#0B2638] leading-tight truncate">{{ $client->name }}</h1>
                            @if(!empty($client->archived))
                                <span class="dt-badge dt-badge-danger">
                                    {{ __('messages.Archived') }}
                                </span>
                            @endif
                        </div>
                        <p class="text-[13.5px] text-slate-500 mt-1">{{ __('messages.Client Number') }}: {{ $client->client_number }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

                    {{-- Card: Personal Infos --}}
                    <div class="dt-card overflow-hidden">
                        <div class="px-5 sm:px-6 py-4 border-b border-slate-100 flex items-center gap-3">
                            <div class="flex items-center justify-center w-9 h-9 rounded-xl bg-[#0B2638]/[0.06] text-[#0B2638] ring-1 ring-[#0B2638]/10 flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <circle cx="12" cy="8" r="3.5" />
                                    <path stroke-linecap="round" d="M5 20c0-3.3 3.1-5 7-5s7 1.7 7 5" />
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <h2 class="dt-section-title truncate">{{ __('messages.Personal Infos') }}</h2>
                                <p class="dt-section-subtitle mt-0.5 truncate">{{ __('messages.Mandatory informations') }}</p>
                            </div>
                        </div>
                        <div class="px-5 sm:px-6 py-5 space-y-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="min-w-0">
                                    <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">{{ __('messages.Name') }}</label>
                                    <input type="text" name="name" value="{{$client->name}}"
                                           class="editable w-full rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3"
                                           data-id="{{$client->id}}" data-column="name" readonly />
                                </div>
                                <div class="min-w-0">
                                    <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">{{ __('messages.Client Number') }}</label>
                                    <input type="text" name="client_number" value="{{$client->client_number}}"
                                           class="editable w-full rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3"
                                           data-id="{{$client->id}}" data-column="client_number" readonly />
                                </div>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="min-w-0">
                                    <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">{{ __('messages.Email') }}</label>
                                    <input type="text" name="email" value="{{$client->email}}"
                                           class="editable w-full rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3"
                                           data-id="{{$client->id}}" data-column="email" readonly />
                                </div>
                                <div class="min-w-0">
                                    <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">{{ __('messages.Phone') }}</label>
                                    <input type="number" name="phone" value="{{$client->phone}}"
                                           class="editable w-full rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3"
                                           data-id="{{$client->id}}" data-column="phone" readonly />
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Card: Address Infos --}}
                    <div class="dt-card overflow-hidden">
                        <div class="px-5 sm:px-6 py-4 border-b border-slate-100 flex items-center gap-3">
                            <div class="flex items-center justify-center w-9 h-9 rounded-xl bg-[#2D7080]/10 text-[#2D7080] ring-1 ring-[#2D7080]/20 flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <circle cx="12" cy="11" r="3" />
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <h2 class="dt-section-title truncate">{{ __('messages.Address Infos') }}</h2>
                                <p class="dt-section-subtitle mt-0.5 truncate">{{ __('messages.Mandatory informations') }}</p>
                            </div>
                        </div>
                        <div class="px-5 sm:px-6 py-5 space-y-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="min-w-0">
                                    <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">{{ __('messages.Street') }}</label>
                                    <input type="text" name="address" value="{{$client->address}}"
                                           class="editable w-full rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3"
                                           data-id="{{$client->id}}" data-column="address" readonly />
                                </div>
                                <div class="min-w-0">
                                    <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">{{ __('messages.City') }}</label>
                                    <input type="text" name="city" value="{{$client->city}}"
                                           class="editable w-full rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3"
                                           data-id="{{$client->id}}" data-column="city" readonly />
                                </div>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="min-w-0">
                                    <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">{{ __('messages.Zip Code') }}</label>
                                    <input type="number" name="zip" value="{{$client->zip}}"
                                           class="editable w-full rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3"
                                           data-id="{{$client->id}}" data-column="zip" readonly />
                                </div>
                                <div class="min-w-0">
                                    <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">{{ __('messages.Country') }}</label>
                                    <input type="text" name="country" value="{{$client->country}}"
                                           class="editable w-full rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3"
                                           data-id="{{$client->id}}" data-column="country" readonly />
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Card: Billing Infos --}}
                    <div class="dt-card overflow-hidden">
                        <div class="px-5 sm:px-6 py-4 border-b border-slate-100 flex items-center gap-3">
                            <div class="flex items-center justify-center w-9 h-9 rounded-xl bg-[#E7B84B]/20 text-[#0B2638] ring-1 ring-[#E7B84B]/40 flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <rect x="3" y="6" width="18" height="12" rx="2" stroke-linecap="round" stroke-linejoin="round" />
                                    <circle cx="12" cy="12" r="2.5" />
                                    <path stroke-linecap="round" d="M6.5 9.5h.01M17.5 14.5h.01" />
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <h2 class="dt-section-title truncate">{{ __('messages.Billing Infos') }}</h2>
                                <p class="dt-section-subtitle mt-0.5 truncate">{{ __('messages.Mandatory informations') }}</p>
                            </div>
                        </div>
                        <div class="px-5 sm:px-6 py-5 space-y-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="min-w-0">
                                    <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">{{ __('messages.TVA Number') }}</label>
                                    <input type="text" name="TVA" value="{{$client->TVA}}"
                                           class="editable w-full rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3"
                                           data-id="{{$client->id}}" data-column="TVA" readonly />
                                </div>
                                <div class="min-w-0">
                                    <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">{{ __('messages.Due Date (Days)') }}</label>
                                    <input type="text" name="due_date" value="{{$client->due_date}}"
                                           class="editable w-full rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3"
                                           data-id="{{$client->id}}" data-column="due_date" readonly />
                                </div>
                            </div>

                            <div class="flex items-center rounded-xl bg-slate-50 px-3 py-2.5">
                                <input id="billingSameAsClient" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-[#2D7080] focus:ring-[#2D7080]/30">
                                <label for="billingSameAsClient" class="ml-2 block text-[13px] font-medium text-slate-700">{{ __('messages.Billing address is same as client') }}</label>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="min-w-0">
                                    <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">{{ __('messages.Billing Street') }}</label>
                                    <input type="text" name="billing_street" value="{{$client->billing_street}}"
                                           class="editable w-full rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3"
                                           data-id="{{$client->id}}" data-column="billing_street" readonly />
                                </div>
                                <div class="min-w-0">
                                    <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">{{ __('messages.Billing City') }}</label>
                                    <input type="text" name="billing_city" value="{{$client->billing_city}}"
                                           class="editable w-full rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3"
                                           data-id="{{$client->id}}" data-column="billing_city" readonly />
                                </div>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="min-w-0">
                                    <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">{{ __('messages.Billing Zip Code') }}</label>
                                    <input type="text" name="billing_zip" value="{{$client->billing_zip}}"
                                           class="editable w-full rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3"
                                           data-id="{{$client->id}}" data-column="billing_zip" readonly />
                                </div>
                                <div class="min-w-0">
                                    <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">{{ __('messages.Billing Country') }}</label>
                                    <input type="text" name="billing_country" value="{{$client->billing_country}}"
                                           class="editable w-full rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3"
                                           data-id="{{$client->id}}" data-column="billing_country" readonly />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Card: Projects --}}
                <div class="dt-card overflow-hidden">
                    <div class="px-5 sm:px-6 py-4 border-b border-slate-100 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="flex items-center justify-center w-9 h-9 rounded-xl bg-[#0B2638]/[0.06] text-[#0B2638] ring-1 ring-[#0B2638]/10 flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V7z" />
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <h2 class="dt-section-title truncate">{{ __('messages.Projects Infos') }}</h2>
                                <p class="dt-section-subtitle mt-0.5 truncate">{{ __('messages.Mandatory informations') }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 flex-shrink-0">
                            @can('Add project')
                                @php
                                    $createProjectRoute = '';
                                    if(Auth::user()->hasRole('Admin')) $createProjectRoute = route('admin.projects.create', $client->id);
                                    elseif(Auth::user()->hasRole('Employee')) $createProjectRoute = route('employee.projects.create', $client->id);
                                    elseif(Auth::user()->hasRole('Responsable de projet')) $createProjectRoute = route('Responsable_de_projet.projects.create', $client->id);
                                    elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire')) $createProjectRoute = route('Responsable_gestionnaire.projects.create', $client->id);
                                @endphp
                                <a href="{{ $createProjectRoute }}" class="dt-btn dt-btn-teal">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                    {{ __('messages.Add Project') }}
                                </a>
                            @endcan
                            <button type="button"
                                    class="section-toggle inline-flex items-center gap-1.5 text-[13px] font-semibold text-slate-400 hover:text-[#0B2638] transition-colors"
                                    data-target="clientProjectsBody" aria-expanded="false">
                                <span class="hidden lg:inline">{{ __('messages.Show') }}</span>
                                <svg class="section-chevron w-4 h-4 transition-transform" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div id="clientProjectsBody" class="hidden">
                    <div class="px-3 sm:px-4 py-4">
                    <div class="overflow-x-auto table-responsive rounded-xl border border-slate-200">
                        <table class="min-w-full table-auto" id="clientProjectsTable">
                            <thead class="bg-slate-50/80 dt-table-head">
                                <tr class="border-b border-slate-200">
                                    <th scope="col" class="px-4 py-3 text-left whitespace-nowrap">{{ __('messages.Project') }}</th>
                                    <th scope="col" class="px-4 py-3 text-left whitespace-nowrap">{{ __('messages.Budget') }}</th>
                                    <th scope="col" class="px-4 py-3 text-left whitespace-nowrap">{{ __('messages.CPH') }}</th>
                                    <th scope="col" class="px-4 py-3 text-left whitespace-nowrap">{{ __('messages.Duration') }}</th>
                                    <th scope="col" class="px-4 py-3 text-left whitespace-nowrap">{{ __('messages.Estimated Hours') }}</th>
                                    <th scope="col" class="px-4 py-3 text-left whitespace-nowrap">{{ __('messages.Difference') }}</th>
                                    <th scope="col" class="px-4 py-3 text-left whitespace-nowrap">{{ __('messages.Progress') }}</th>
                                    <th scope="col" class="px-4 py-3 text-left whitespace-nowrap">{{ __('messages.VAT') }}</th>
                                    <th scope="col" class="px-4 py-3 text-left whitespace-nowrap">{{ __('messages.Client column') }}</th>
                                    <th scope="col" class="px-4 py-3 text-left whitespace-nowrap">{{ __('messages.Teams column') }}</th>
                                    <th scope="col" class="px-4 py-3 text-right whitespace-nowrap">{{ __('messages.Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-slate-100">
                                @foreach($projects as $project)
                                <tr class="hover:bg-slate-50/70">
                                    <td data-label="{{ __('messages.Project') }}" data-span="2" class="px-4 py-2.5 text-sm font-medium">
                                        @php
                                            // Resolve project show route based on role; fallback to admin if available
                                            $roleRouteMap = [
                                                'Admin' => 'admin.projects.show',
                                                'Responsable de projet' => 'Responsable_de_projet.projects.show',
                                                'Responsable de projet et gestionnaire' => 'Responsable_gestionnaire.projects.show',
                                                'Employee' => 'employee.projects.show',
                                            ];

                                            $projectShowRoute = null;
                                            foreach ($roleRouteMap as $role => $routeName) {
                                                if (Auth::user()->hasRole($role) && Route::has($routeName)) {
                                                    $projectShowRoute = route($routeName, $project->id);
                                                    break;
                                                }
                                            }

                                            if (!$projectShowRoute && Route::has('admin.projects.show')) {
                                                $projectShowRoute = route('admin.projects.show', $project->id);
                                            }
                                        @endphp

                                        @if($projectShowRoute)
                                            <a href="{{ $projectShowRoute }}" class="text-[#2D7080] hover:text-[#0B2638] font-medium">{{ strtoupper($project->name) }}</a>
                                        @else
                                            <span class="text-slate-900">{{ strtoupper($project->name) }}</span>
                                        @endif
                                    </td>
                                    <td data-label="{{ __('messages.Budget') }}" class="px-4 py-2.5 whitespace-nowrap text-sm text-green-600 font-semibold">€{{$project->budget}}</td>
                                    <td data-label="{{ __('messages.CPH') }}" class="px-4 py-2.5 whitespace-nowrap text-sm text-green-600 font-semibold">€{{$project->cost_per_hour}}</td>

                                    @php
                                        $projecttasksId = App\Models\Tasks::where('project_id', $project->id)->pluck('id');
                                        $totalduration = App\Models\tasks_timer::whereIn('task_id', $projecttasksId)->sum('duration');
                                        $difference = $project->estimated_hours * 60 - $totalduration;
                                        $hours = floor(abs($totalduration) / 60);
                                        $minutes = abs($totalduration) % 60;
                                        $diffhours = floor(abs($difference) / 60);
                                        $diffminutes = abs($difference) % 60;
                                        $estimatedMinutes = $project->estimated_hours * 60;
                                        $progressPercentage = $estimatedMinutes > 0 ? ($totalduration / $estimatedMinutes) * 100 : 0;
                                        $formattedProgressPercentage = number_format($progressPercentage, 2);
                                    @endphp

                                    <td data-label="{{ __('messages.Duration') }}" class="px-4 py-2.5 whitespace-nowrap text-sm text-slate-700 font-medium">{{$hours}}h:{{$minutes}}m</td>
                                    <td data-label="{{ __('messages.Estimated Hours') }}" class="px-4 py-2.5 whitespace-nowrap text-sm text-slate-700 font-medium">{{$project->estimated_hours}}h</td>
                                    <td data-label="{{ __('messages.Difference') }}" class="px-4 py-2.5 whitespace-nowrap text-sm font-bold">
                                        @if($project->estimated_hours !== null)
                                            @if($difference > 0)
                                                <span class="text-green-600">+{{$diffhours}}h:{{$diffminutes}}m</span>
                                            @elseif($difference < 0)
                                                <span class="text-red-600">-{{$diffhours}}h:{{$diffminutes}}m</span>
                                            @else
                                                <span class="text-blue-600">{{$diffhours}}h:{{$diffminutes}}m</span>
                                            @endif
                                        @endif
                                    </td>
                                    <td data-label="{{ __('messages.Progress') }}" data-span="2" class="px-4 py-2.5 whitespace-nowrap text-sm text-slate-700 min-w-[150px]">
                                        <div class="w-full bg-slate-200 rounded-full h-3 relative">
                                            @php
                                                $progressColor = 'bg-green-500'; // Default: < 50%
                                                if ($progressPercentage >= 75) $progressColor = 'bg-red-500';
                                                elseif ($progressPercentage >= 50) $progressColor = 'bg-yellow-500';
                                            @endphp
                                            <div class="absolute inset-0 flex items-center justify-center">
                                                <span class="text-xs font-semibold {{ $progressPercentage > 40 ? 'text-white' : 'text-slate-700' }}">{{$formattedProgressPercentage}}%</span>
                                            </div>
                                            <div class="{{ $progressColor }} h-3 rounded-full" style="width: {{$formattedProgressPercentage}}%"></div>
                                        </div>
                                    </td>
                                    <td data-label="{{ __('messages.VAT') }}" class="px-4 py-2.5 whitespace-nowrap text-sm text-slate-700">%{{$project->tva}}</td>
                                    <td data-label="{{ __('messages.Client column') }}" class="px-4 py-2.5 whitespace-nowrap text-sm text-slate-700">
                                        {{-- Client link logic truncated for brevity, assuming it's the current client --}}
                                        {{ $project->client_id ? (App\Models\Client::where('id', $project->client_id)->first()->name ?? 'No client') : 'No client' }}
                                    </td>
                                    <td data-label="{{ __('messages.Teams column') }}" data-span="2" class="px-4 py-2.5 text-sm text-slate-700">
                                        @foreach(App\Models\project_teams::where('project_id', $project->id)->get() as $project_team)
                                            <span class="block">{{$project_team->team_name}}</span>
                                        @endforeach
                                    </td>
                                    <td data-label="{{ __('messages.Actions') }}" data-span="2" class="px-4 py-2.5 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                        @php
                                            $editRoute = '#'; $archiveRoute = '#'; $deleteRoute = '#';
                                            if(Auth::user()->hasRole('Admin')) {
                                                $editRoute = route('admin.projects.edit', $project->id);
                                                $archiveRoute = route('admin.projects.archive', $project->id);
                                                $deleteRoute = route('admin.projects.destroy', $project->id);
                                            } elseif(Auth::user()->hasRole('Responsable de projet')) {
                                                $editRoute = route('Responsable_de_projet.projects.edit', $project->id);
                                                $archiveRoute = route('Responsable_de_projet.projects.archive', $project->id);
                                                $deleteRoute = route('Responsable_de_projet.projects.destroy', $project->id);
                                            } elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire')) {
                                                $editRoute = route('Responsable_gestionnaire.projects.edit', $project->id);
                                                $archiveRoute = route('Responsable_gestionnaire.projects.archive', $project->id);
                                                $deleteRoute = route('Responsable_gestionnaire.projects.destroy', $project->id);
                                            } elseif(Auth::user()->hasRole('Employee')) {
                                                $editRoute = route('employee.projects.edit', $project->id);
                                                $archiveRoute = route('employee.projects.archive', $project->id);
                                                $deleteRoute = route('employee.projects.destroy', $project->id);
                                            }
                                        @endphp

                                        @if($editRoute != '#')
                                        <div class="client-project-actions flex flex-wrap items-center justify-center gap-2">
                                        <a href="{{ $editRoute }}" class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold text-[#2D7080] border border-[#2D7080]/30 hover:bg-[#2D7080]/5 transition-colors">{{ __('messages.Edit') }}</a>

                                <form action="{{ $archiveRoute }}" method="post" onsubmit="return confirm('{{ __('messages.Are you sure?') }}');" class="inline-block">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-slate-200 text-slate-700 hover:bg-slate-300 transition-colors">{{ __('messages.Archive') }}</button>
                                        </form>

                                        <form action="{{ $deleteRoute }}" method="post" onsubmit="return confirm('{{ __('messages.Are you sure?') }}');" class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-red-600 text-white hover:bg-red-700 transition-colors">{{ __('messages.Delete') }}</button>
                                        </form>
                                        </div>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    </div>
                    </div>
                </div>

                {{-- Card: Invoices --}}
                <div class="dt-card overflow-hidden">
                    <div class="px-5 sm:px-6 py-4 border-b border-slate-100 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="flex items-center justify-center w-9 h-9 rounded-xl bg-[#E7B84B]/20 text-[#0B2638] ring-1 ring-[#E7B84B]/40 flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                </svg>
                            </div>
                            <h2 class="dt-section-title truncate">{{ __('messages.Invoices') }}</h2>
                        </div>
                        <button type="button"
                                class="section-toggle inline-flex items-center gap-1.5 text-[13px] font-semibold text-slate-400 hover:text-[#0B2638] transition-colors flex-shrink-0"
                                data-target="clientInvoicesBody" aria-expanded="false">
                            <span class="hidden lg:inline">{{ __('messages.Show') }}</span>
                            <svg class="section-chevron w-4 h-4 transition-transform" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6" />
                            </svg>
                        </button>
                    </div>

                    <div id="clientInvoicesBody" class="hidden">
                    <div class="px-5 sm:px-6 py-5 space-y-5">
                        <div class="flex flex-col xl:flex-row xl:items-end gap-4 justify-between">
                            <div class="min-w-0">
                                @php
                                    $filterRoute = Auth::user()->hasRole('Admin') ? route('admin.clients.filterInvoices', $client->id) :
                                                (Auth::user()->hasRole('Employee') ? route('employee.clients.filterInvoices', $client->id) :
                                                (Auth::user()->hasRole('Responsable de projet') ? route('Responsable_de_projet.clients.filterInvoices', $client->id) :
                                                route('Responsable_gestionnaire.clients.filterInvoices', $client->id)));
                                @endphp
                                <form action="{{ $filterRoute }}" method="POST" class="flex flex-col sm:flex-row sm:items-end gap-2" id="filterForm">
                                    @csrf
                                    <input type="hidden" name="client_id" value="{{ $client->id }}">
                                    <div class="min-w-0">
                                        <label for="start_date" class="block text-[13px] font-semibold text-slate-700 mb-1.5">{{ __('messages.Start Date') }}</label>
                                        <input type="date" name="start_date" id="start_date" class="block w-full rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3" value="{{ request()->session()->get('start_date') }}">
                                    </div>
                                    <div class="min-w-0">
                                        <label for="end_date" class="block text-[13px] font-semibold text-slate-700 mb-1.5">{{ __('messages.End Date') }}</label>
                                        <input type="date" name="end_date" id="end_date" class="block w-full rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3" value="{{ request()->session()->get('end_date') }}">
                                    </div>
                                    <div class="flex gap-2">
                                        <button type="submit" class="dt-btn dt-btn-primary">{{ __('messages.Filter') }}</button>
                                        <a href="#" onclick="downloadReport(event)" class="dt-btn dt-btn-teal">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17v2a2 2 0 002 2h14a2 2 0 002-2v-2"/></svg>
                                            {{ __('messages.Download Report') }}
                                        </a>
                                    </div>
                                </form>
                            </div>

                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 xl:text-right">
                                <div class="rounded-xl bg-emerald-50/70 ring-1 ring-emerald-600/10 px-3 py-2.5">
                                    <div class="text-[11px] font-semibold uppercase tracking-[0.07em] text-slate-500">{{ __('messages.Total Paid') }}</div>
                                    <div class="text-[18px] font-bold text-emerald-700 tracking-tight">€{{ number_format($totalPaid, 2, ',', ' ') }}</div>
                                </div>
                                <div class="rounded-xl bg-red-50/70 ring-1 ring-red-600/10 px-3 py-2.5">
                                    <div class="text-[11px] font-semibold uppercase tracking-[0.07em] text-slate-500">{{ __('messages.Total Unpaid') }}</div>
                                    <div class="text-[18px] font-bold text-red-600 tracking-tight">€{{ number_format($totalUnpaid, 2, ',', ' ') }}</div>
                                </div>
                                <div class="rounded-xl bg-slate-50 ring-1 ring-slate-600/10 px-3 py-2.5">
                                    <div class="text-[11px] font-semibold uppercase tracking-[0.07em] text-slate-500">{{ __('messages.Total TVA') }}</div>
                                    <div class="text-[18px] font-bold text-[#0B2638] tracking-tight">€{{ number_format($totalTVA, 2, ',', ' ') }}</div>
                                </div>
                                <div class="rounded-xl bg-slate-50 ring-1 ring-slate-600/10 px-3 py-2.5">
                                    <div class="text-[11px] font-semibold uppercase tracking-[0.07em] text-slate-500">{{ __('messages.Total Budget') }}</div>
                                    <div class="text-[18px] font-bold text-[#0B2638] tracking-tight">€{{ number_format($totalBudget, 2, ',', ' ') }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="overflow-x-auto table-responsive rounded-xl border border-slate-200">
                        <table class="min-w-full table-auto" id="clientInvoicesTable">
                            <thead class="bg-slate-50/80 dt-table-head">
                                <tr class="border-b border-slate-200">
                                    <th scope="col" class="px-4 py-3 text-left whitespace-nowrap">{{ __('messages.Invoice N') }}</th>
                                    <th scope="col" class="px-4 py-3 text-left whitespace-nowrap">{{ __('messages.Project Name') }}</th>
                                    <th scope="col" class="px-4 py-3 text-left whitespace-nowrap">{{ __('messages.Cost Total') }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-slate-100">
                                @foreach($invoices as $invoice)
                                <tr class="hover:bg-slate-50/70">
                                    <td data-label="{{ __('messages.Invoice N') }}" data-span="2" class="px-4 py-2.5 text-sm font-medium">
                                        @php
                                            $dueDate = \Carbon\Carbon::parse($invoice->due_date);
                                            $today = \Carbon\Carbon::now();
                                            $isOverdue = $dueDate->isPast() && !$invoice->Payment_status;
                                            $daysOverdue = $isOverdue ? $today->diffInDays($dueDate) : 0;

                                            $invoiceRoute = '#';
                                            if(Auth::user()->hasRole('Responsable de projet')) $invoiceRoute = route('Responsable_de_projet.invoices.servePdf', ['invoiceName' => $invoice->id]);
                                            elseif(Auth::user()->hasRole('Admin')) $invoiceRoute = route('admin.invoices.servePdf', ['invoiceName' => $invoice->id]);
                                            elseif(Auth::user()->hasRole('Employee')) $invoiceRoute = route('employee.invoices.servePdf', ['invoiceName' => $invoice->id]);
                                            elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire')) $invoiceRoute = route('Responsable_gestionnaire.invoices.servePdf', ['invoiceName' => $invoice->id]);
                                        @endphp
                                        <a href="{{ $invoiceRoute }}" target="_blank" class="{{ $isOverdue ? 'text-red-600 font-bold' : 'text-[#2D7080] hover:text-[#0B2638] font-medium' }}">
                                            {{$entreprise_infos->invoice_prefix}}-{{date('ym', strtotime($invoice->created_at))}}{{$invoice->number}}
                                            @if($isOverdue)
                                                <span class="ml-2">({{ $daysOverdue }} {{ __('messages.days overdue') }})</span>
                                            @endif
                                        </a>
                                    </td>
                                    <td data-label="{{ __('messages.Project Name') }}" class="px-4 py-2.5 whitespace-nowrap text-sm text-slate-700">{{ strtoupper($invoice->project_name) }}</td>
                                    <td data-label="{{ __('messages.Cost Total') }}" class="px-4 py-2.5 whitespace-nowrap text-sm font-bold {{ $invoice->Payment_status==1 ? 'text-green-600' : 'text-red-600' }}">
                                        €{{ number_format($invoice->total_ttc, 2, ',', ' ') }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        </div>
                    </div>
                    </div>
                </div>

                @php
                    $comptable = App\Models\client_contacts::where('client_id', $client->id)->where('is_comptable', 1)->first();
                @endphp

                @if($comptable)
                {{-- Card: Comptable Infos --}}
                <div class="dt-card overflow-hidden">
                    <div class="px-5 sm:px-6 py-4 border-b border-slate-100 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="flex items-center justify-center w-9 h-9 rounded-xl bg-[#2D7080]/10 text-[#2D7080] ring-1 ring-[#2D7080]/20 flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m-5 4h4m5 6H5a2 2 0 01-2-2V5a2 2 0 012-2h14a2 2 0 012 2v10a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <h2 class="dt-section-title truncate">{{ __('messages.Comptable Infos') }}</h2>
                                <p class="dt-section-subtitle mt-0.5 truncate">{{ __('messages.Mandatory informations') }}</p>
                            </div>
                        </div>
                        <button type="button"
                                class="section-toggle inline-flex items-center gap-1.5 text-[13px] font-semibold text-slate-400 hover:text-[#0B2638] transition-colors flex-shrink-0"
                                data-target="clientComptableBody" aria-expanded="false">
                            <span class="hidden lg:inline">{{ __('messages.Show') }}</span>
                            <svg class="section-chevron w-4 h-4 transition-transform" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6" />
                            </svg>
                        </button>
                    </div>
                    <div id="clientComptableBody" class="hidden">
                    <div class="px-5 sm:px-6 py-5 space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="min-w-0">
                                <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">{{ __('messages.Full Name') }}</label>
                                <input class="editable-contact w-full rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3" data-id="{{$comptable->id}}" data-column="name" type="text" value="{{$comptable->name}}" name="name" readonly />
                            </div>
                            <div class="min-w-0">
                                <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">{{ __('messages.Email') }}</label>
                                <input class="editable-contact w-full rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3" data-id="{{$comptable->id}}" data-column="email" type="text" value="{{$comptable->email}}" name="email" readonly />
                            </div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="min-w-0">
                                <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">{{ __('messages.Mobile Fix') }}</label>
                                <input class="editable-contact w-full rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3" data-id="{{$comptable->id}}" data-column="mobile_fix" type="number" value="{{$comptable->mobile_fix}}" name="mobile_fix" readonly />
                            </div>
                            <div class="min-w-0">
                                <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">{{ __('messages.Address') }}</label>
                                <input class="editable-contact w-full rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3" data-id="{{$comptable->id}}" data-column="address" type="text" value="{{$comptable->address}}" name="address" readonly />
                            </div>
                        </div>
                    </div>
                    </div>
                </div>
                @endif

                @php
                    $contacts = App\Models\client_contacts::where('client_id', $client->id)->get();
                    $i=1;
                @endphp

                {{-- Card: Contacts Infos --}}
                <div class="dt-card overflow-hidden">
                    <div class="px-5 sm:px-6 py-4 border-b border-slate-100 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="flex items-center justify-center w-9 h-9 rounded-xl bg-[#0B2638]/[0.06] text-[#0B2638] ring-1 ring-[#0B2638]/10 flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5c-.006 3.125-1.026 6.042-2.751 8.498m-1.782-2.5A18.022 18.022 0 014.888 5h7.863z" />
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <h2 class="dt-section-title truncate">{{ __('messages.Contacts Infos') }}</h2>
                                <p class="dt-section-subtitle mt-0.5 truncate">{{ __('messages.Mandatory informations') }}</p>
                            </div>
                        </div>
                        <button type="button"
                                class="section-toggle inline-flex items-center gap-1.5 text-[13px] font-semibold text-slate-400 hover:text-[#0B2638] transition-colors flex-shrink-0"
                                data-target="clientContactsBody" aria-expanded="false">
                            <span class="hidden lg:inline">{{ __('messages.Show') }}</span>
                            <svg class="section-chevron w-4 h-4 transition-transform" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6" />
                            </svg>
                        </button>
                    </div>

                    <div id="clientContactsBody" class="hidden">
                    <div class="px-5 sm:px-6 py-5">

                    <div id="accordion" class="space-y-3">
                        @foreach ($contacts as $contact)
                        @if($contact->is_comptable==0)
                        <div class="border border-slate-200 rounded-xl overflow-hidden">
                            <div class="accordion-trigger flex justify-between items-center gap-3 px-4 py-3 cursor-pointer hover:bg-slate-50/70 transition-colors" aria-expanded="false" aria-controls="collapse{{$i}}">
                                <span class="flex items-center gap-3 min-w-0">
                                    <svg class="h-5 w-5 text-[#2D7080] flex-shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M2 3a1 1 0 011-1h1.78a1 1 0 01.95.684l1.11 3.109a1 1 0 01-.523 1.28L5.2 8.281a14.95 14.95 0 007.518 7.518l1.212-1.121a1 1 0 011.28-.523l3.109 1.11a1 1 0 01.684.95V17a1 1 0 01-1 1H3a1 1 0 01-1-1V3z"></path></svg>
                                    <span class="text-[14px] font-bold text-[#0B2638] truncate">{{ ucfirst($contact->name) }}</span>
                                </span>
                                <div class="flex items-center gap-3 flex-shrink-0">
                                    <form action="{{route('admin.clients.destroyContact',$contact->id) }}" method="post" onsubmit="return confirm('{{ __('messages.Are you sure?') }}');" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700 hover:bg-red-200 transition-colors">{{ __('messages.Delete') }}</button>
                                    </form>
                                    <svg class="arrow section-chevron h-5 w-5 text-slate-400 transition-transform" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                                </div>
                            </div>
                            <div id="collapse{{$i}}" class="accordion-content hidden px-4 py-4 bg-slate-50/60 border-t border-slate-100">
                                <div class="space-y-4">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div class="min-w-0">
                                            <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">{{ __('messages.Full Name') }}</label>
                                            <input class="editable-contact w-full rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3" data-id="{{$contact->id}}" data-column="name" type="text" value="{{$contact->name}}" name="name" readonly />
                                        </div>
                                        <div class="min-w-0">
                                            <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">{{ __('messages.Functionality') }}</label>
                                            <input class="editable-contact w-full rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3" data-id="{{$contact->id}}" data-column="functionality" type="text" value="{{$contact->functionality}}" name="functionality" readonly />
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div class="min-w-0">
                                            <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">{{ __('messages.Mobile Fix') }}</label>
                                            <input class="editable-contact w-full rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3" data-id="{{$contact->id}}" data-column="mobile_fix" type="number" value="{{$contact->mobile_fix}}" name="mobile_fix" readonly />
                                        </div>
                                        <div class="min-w-0">
                                            <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">{{ __('messages.Email') }}</label>
                                            <input class="editable-contact w-full rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3" data-id="{{$contact->id}}" data-column="email" type="text" value="{{$contact->email}}" name="email" readonly />
                                        </div>
                                    </div>
                                    <div class="min-w-0">
                                        <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">{{ __('messages.Address') }}</label>
                                        <input class="editable-contact w-full rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3" data-id="{{$contact->id}}" data-column="address" type="text" value="{{$contact->address}}" name="address" readonly />
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                        @php $i++; @endphp
                        @endforeach
                    </div>

                    <form class="mt-5 pt-5 border-t border-slate-100" method="POST" action="{{ route('admin.clients.addContact', $client->id) }}">
                        @csrf
                        @method('PUT')
                        <h3 class="text-[15px] font-bold text-[#0B2638] mb-4">{{ __('messages.Add New Contact') }}</h3>

                        {{-- This div is the one that gets cloned --}}
                        <div id="contact_infos" class="space-y-4">
                            <div class="contact-entry space-y-4 p-4 border border-slate-200 rounded-xl bg-slate-50/50">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div class="min-w-0">
                                        <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">{{ __('messages.Full Name') }}</label>
                                        <input class="w-full rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3" type="text" placeholder="{{ __('messages.Full Name') }}" name="contact_name[]" />
                                    </div>
                                    <div class="min-w-0">
                                        <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">{{ __('messages.Functionality') }}</label>
                                        <input class="w-full rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3" type="text" placeholder="{{ __('messages.Functionality') }}" name="contact_function[]" />
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div class="min-w-0">
                                        <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">{{ __('messages.Mobile Fix') }}</label>
                                        <input class="w-full rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3" type="number" placeholder="{{ __('messages.Mobile Fix') }}" name="contact_mobile_fix[]" />
                                    </div>
                                    <div class="min-w-0">
                                        <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">{{ __('messages.Email') }}</label>
                                        <input class="w-full rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3" type="text" placeholder="*****@gmail.com" name="contact_email[]" />
                                    </div>
                                </div>
                                <div class="min-w-0">
                                    <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">{{ __('messages.Address') }}</label>
                                    <input class="w-full rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3" type="text" placeholder="{{ __('messages.Address') }}" name="contact_address[]" />
                                </div>
                            </div>
                            {{-- Cloned entries will be appended here --}}
                        </div>

                        <div class="flex flex-col-reverse sm:flex-row sm:justify-between sm:items-center gap-2 mt-4">
                            <button id="addContact" type="button" class="dt-btn dt-btn-ghost">{{ __('messages.Add Another') }}</button>
                        <button type="submit" class="dt-btn dt-btn-primary">{{ __('messages.Save New Contacts') }}</button>
                        </div>
                    </form>
                    </div>
                    </div>
                </div>

            </div>
        </main>
    </div>

    {{-- SCRIPTS --}}
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

    {{-- I have removed the Bootstrap JS include, as it's no longer needed for the accordion and could conflict --}}
    @include('dashboard_layouts.scripts')

    {{-- Editable Fields Script --}}
    <script>
    @can('Update client')
    $(document).ready(function() {
        // --- Editable Client Fields ---
        $(document).on("dblclick", ".editable", function() {
            $(this).prop('readonly', false).focus();
        });

        $(document).on("blur", ".editable", function() {
            $(this).prop('readonly', true);
            var newContent = $(this).val();
            var clientId = $(this).data('id');
            var columnName = $(this).attr('name');

            var updatedUrl = '';
            @if(Auth::user()->hasRole('Responsable de projet'))
                updatedUrl = '/Responsable_de_projet/clients/updateClient';
            @elseif(Auth::user()->hasRole('Admin'))
                updatedUrl = '/admin/clients/updateClient';
            @elseif(Auth::user()->hasRole('Employee'))
                updatedUrl = '/employee/clients/updateClient';
            @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                updatedUrl = '/Responsable_gestionnaire/clients/updateClient';
            @endif

            $.ajax({
                url: updatedUrl,
                type: 'post',
                data: {
                    content: newContent,
                    id: clientId,
                    column: columnName,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    location.reload(); // Reload to confirm changes
                },
                error: function(response) {
                    console.error("Update failed:", response);
                }
            });
        });

        // --- Editable Contact Fields ---
        $(document).on("dblclick", ".editable-contact", function() {
            $(this).prop('readonly', false).focus();
        });

        $(document).on("blur", ".editable-contact", function() {
            $(this).prop('readonly', true);
            var newContent = $(this).val();
            var contactId = $(this).data('id');
            var columnName = $(this).attr('name');

            var updatedUrl = '';
            @if(Auth::user()->hasRole('Responsable de projet'))
                updatedUrl = '/Responsable_de_projet/clients/updateContact';
            @elseif(Auth::user()->hasRole('Admin'))
                updatedUrl = '/admin/clients/updateContact';
            @elseif(Auth::user()->hasRole('Employee'))
                updatedUrl = '/employee/clients/updateContact';
            @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                updatedUrl = '/Responsable_gestionnaire/clients/updateContact';
            @endif

            $.ajax({
                url: updatedUrl,
                type: 'post',
                data: {
                    content: newContent,
                    id: contactId,
                    column: columnName,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    // No reload here to avoid closing accordion
                    console.log("Contact updated");
                },
                error: function(response) {
                    console.error("Contact update failed:", response);
                }
            });
        });
    });
    @endcan
    </script>

    <script>
        // Section collapse/expand (Projects / Invoices / Comptable / Contacts)
        document.addEventListener('DOMContentLoaded', function () {
            const isMobile = () => window.matchMedia && window.matchMedia('(max-width: 1024px)').matches;

            function setExpanded(btn, expanded) {
                const targetId = btn.getAttribute('data-target');
                const panel = targetId ? document.getElementById(targetId) : null;
                if (!panel) return;

                btn.setAttribute('aria-expanded', expanded ? 'true' : 'false');
                panel.classList.toggle('hidden', !expanded);

                const chevron = btn.querySelector('.section-chevron');
                if (chevron) chevron.classList.toggle('rotate-180', expanded);
            }

            document.querySelectorAll('button.section-toggle[data-target]').forEach(btn => {
                // Mobile: collapsed by default. Desktop: expanded by default.
                setExpanded(btn, !isMobile());

                btn.addEventListener('click', () => {
                    const expanded = btn.getAttribute('aria-expanded') === 'true';
                    setExpanded(btn, !expanded);
                });
            });
        });
    </script>

    {{-- Add New Contact Script --}}
    <script>
    $(document).ready(function() {
        // Your "Add Contact" script had two identical click handlers. I've removed the broken one.
        $("#addContact").click(function(e) {
            e.preventDefault();
            var container = $("#contact_infos");
            var entryToClone = container.children(".contact-entry").first();
            var newEntry = entryToClone.clone();

            newEntry.find('input').val(''); // Clear values

            // Add a styled delete button
            var deleteButton = $('<button type="button" class="remove-contact dt-btn w-full mt-2" style="color: #fff; background: #dc2626;">{{ __('messages.Remove') }}</button>');

            newEntry.append(deleteButton);
            container.append(newEntry);
        });

        // Event delegation for removing cloned entries
        $("#contact_infos").on("click", ".remove-contact", function() {
            $(this).closest(".contact-entry").remove();
        });
    });
    </script>

    {{-- Billing Address Checkbox Script --}}
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const billingCheckbox = document.getElementById('billingSameAsClient');
        if (!billingCheckbox) return;

        const fields = {
            street: document.querySelector('input[name="billing_street"]'),
            city: document.querySelector('input[name="billing_city"]'),
            zip: document.querySelector('input[name="billing_zip"]'),
            country: document.querySelector('input[name="billing_country"]'),
        };

        const clientData = {
            street: "{{$client->address}}",
            city: "{{$client->city}}",
            zip: "{{$client->zip}}",
            country: "{{$client->country}}",
            id: "{{$client->id}}"
        };

        billingCheckbox.addEventListener('change', function() {
            if (this.checked) {
                // Set values
                fields.street.value = clientData.street;
                fields.city.value = clientData.city;
                fields.zip.value = clientData.zip;
                fields.country.value = clientData.country;

                // Make readonly and trigger updates
                Object.entries(fields).forEach(([key, element]) => {
                    element.readOnly = true;
                    // Trigger the blur event to save the data
                    $(element).trigger('blur');
                });
            } else {
                Object.values(fields).forEach(element => {
                    element.readOnly = false;
                });
            }
        });
    });
    </script>

    {{-- New Tailwind-based Accordion Script --}}
    <script>
    $(document).ready(function() {
        $('#accordion').on('click', '.accordion-trigger', function(e) {
            // Don't toggle if the delete button or its form was clicked
            if ($(e.target).closest('form').length > 0) {
                return;
            }

            var content = $(this).next('.accordion-content');
            var isExpanded = $(this).attr('aria-expanded') === 'true';

            // Toggle the current one
            content.toggleClass('hidden');
            $(this).attr('aria-expanded', !isExpanded);
        });
    });
    </script>

    {{-- Report Download & Filter Script (Unchanged) --}}
    <script>
    function downloadReport(e) {
        e.preventDefault();

        const startDate = sessionStorage.getItem('filterStartDate') || document.getElementById('start_date').value;
        const endDate = sessionStorage.getItem('filterEndDate') || document.getElementById('end_date').value;

        let url = '{{ Auth::user()->hasRole("Admin") ?
            route("admin.clients.downloadInvoicesRepports", $client->id) :
            (Auth::user()->hasRole("Employee") ?
                route("employee.clients.downloadInvoicesRepports", $client->id) :
                (Auth::user()->hasRole("Responsable de projet") ?
                    route("Responsable_de_projet.clients.downloadInvoicesRepports", $client->id) :
                    route("Responsable_gestionnaire.clients.downloadInvoicesRepports", $client->id))) }}';

        if (startDate && endDate) {
            url += `?startDate=${startDate}&endDate=${endDate}`;
        }
        window.open(url, '_blank');
    }

    document.getElementById('filterForm').addEventListener('submit', function() {
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;
        sessionStorage.setItem('filterStartDate', startDate);
        sessionStorage.setItem('filterEndDate', endDate);
    });

    window.addEventListener('load', function() {
        const startDate = sessionStorage.getItem('filterStartDate');
        const endDate = sessionStorage.getItem('filterEndDate');
        if (startDate) document.getElementById('start_date').value = startDate;
        if (endDate) document.getElementById('end_date').value = endDate;
    });
    </script>

</body>
</html>
