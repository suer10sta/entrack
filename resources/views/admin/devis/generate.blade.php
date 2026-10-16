<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <x-favicon />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.New Devis - DalyTrac') }}</title>
    @vite('resources/css/app.css')
    @livewireStyles
    @include('admin.devis.partials.dashboardStyles')
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
        
        #clientInputs {
            display: none;
        }
    </style>
</head>

<body class="devis-dashboard-page dt-app-shell bg-[#f8fafc] text-slate-900 antialiased flex h-screen overflow-hidden">

    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-NKDMSK6" height="0" width="0"
            style="display: none; visibility: hidden"></iframe></noscript>

    {{-- Sidebar Component --}}
    @if(Auth::user()->hasRole('Admin'))
        <x-dashboard.sidebar />
    @else
        <x-employees.sidebar />
    @endif

    {{-- Main Wrapper (Navbar + Content)  --}}
    <div class="flex flex-col flex-1 overflow-y-auto">

        {{-- Navbar Component  --}}
        <x-dashboard.navbar />

        {{-- Main Content Area --}}
        <main class="flex-1 min-w-0 overflow-x-hidden overflow-y-auto bg-[#f8fafc] px-3 py-4 sm:px-5 sm:py-5 lg:px-16 lg:py-7">

            <div class="w-full max-w-6xl mx-auto space-y-5">

                <div>
                    <p class="dt-overline mb-0.5">{{ __('messages.Devis History') }}</p>
                    <h1 class="text-[18px] sm:text-[20px] lg:text-[22px] font-bold text-[#0B2638] leading-tight">{{ __('messages.New Devis') }}</h1>
                </div>

                {{-- Session Error --}}
                @if(session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <strong class="font-bold">{{ __('messages.Error!') }}</strong>
                    <span class="block sm:inline">{{ session('error') }}</span>
                    <button type="button" onclick="this.parentElement.style.display='none';" class="absolute top-0 bottom-0 right-0 px-4 py-3 font-bold text-xl" aria-label="{{ __('messages.Close') }}">&times;</button>
                </div>
                @endif

                {{-- Session Message --}}
                @if(session('message'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <strong class="font-bold">{{ __('messages.Success!') }}</strong>
                    <span class="block sm:inline">{{ session('message') }}</span>
                    <button type="button" onclick="this.parentElement.style.display='none';" class="absolute top-0 bottom-0 right-0 px-4 py-3 font-bold text-xl" aria-label="{{ __('messages.Close') }}">&times;</button>
                </div>
                @endif

                {{-- Main Card --}}
                <div class="dt-card overflow-hidden">
                    
                    {{-- Card Header --}}
                    <div class="px-5 py-4 border-b border-slate-100 flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-[#2D7080]/10 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-[#2D7080]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <div>
                            <p class="text-[13px] font-semibold text-[#0B2638]">{{ __('messages.New Devis') }}</p>
                            <p class="text-[11.5px] text-slate-400">{{ __('messages.Create new Offer') }}</p>
                        </div>
                    </div>

                    {{-- Card Body --}}
                    <div class="p-6">
                        
                        {{-- Form --}}
                        @if(Auth::user()->hasRole('Responsable de projet'))
                        <form id="devisForm" class="devis-form" method="POST" action="{{route('Responsable_de_projet.devis.saveDevisPdf')}}" autocomplete="off">
                        @endif
                        @if(Auth::user()->hasRole('Employee'))
                        <form id="devisForm" class="devis-form" method="POST" action="{{route('employee.devis.saveDevisPdf')}}" autocomplete="off">
                        @endif
                        @if(Auth::user()->hasRole('Admin'))
                        <form id="devisForm" class="devis-form" method="POST" action="{{route('admin.devis.saveDevisPdf')}}" autocomplete="off">
                        @endif
                        @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                        <form id="devisForm" class="devis-form" method="POST" action="{{route('Responsable_gestionnaire.devis.saveDevisPdf')}}" autocomplete="off">
                        @endif
                            @csrf

                            {{-- Project Type Row --}}
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label for="projectType" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Select Project Type') }} <span class="text-red-600">*</span></label>
                                    <select class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3 form-select-custom" id="projectType" name="project_type" onchange="toggleProjectInputs()">
                                        <option value="">{{ __('messages.Select option') }}</option>
                                        <option value="new">{{ __('messages.New Project') }}</option>
                                        <option value="existing">{{ __('messages.Existing Project') }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="projectName" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.New Project Name') }} <span class="text-red-600">*</span></label>
                                    <input type="text" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3 bg-gray-100" id="projectName" name="Project_name" disabled />
                                </div>
                                <div>
                                    <label for="existingProjectName" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Existing Project Name') }} <span class="text-red-600">*</span></label>
                                    <select class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3 form-select-custom bg-gray-100" id="existingProjectName" name="Existing_project_id" disabled onchange="populateProjectDetails()">
                                        <option value="">{{ __('messages.Select a project') }}</option>
                                        @foreach($projects as $project)
                                            <option
                                                value="{{ $project->id }}"
                                                data-tva="{{ $project->tva }}"
                                                data-client-id="{{ $project->client_id ?? '' }}"
                                            >
                                                {{ $project->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            {{-- Project Details Row --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                                <div>
                                    <label for="projectBudget" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Total Budget') }}</label>
                                    <input type="number" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3 bg-gray-100" id="projectBudget" name='budget' readonly/>
                                </div>
                                <div>
                                    <label for="projectTva" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Project TVA') }}</label>
                                    <input type="number" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3" id="projectTva" name='project_tva' />
                                </div>
                            </div>

                            {{-- Devis Type Row (Forfait / Hourly / Detailed Hourly) --}}
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                                <div>
                                    <span class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Devis Type') }} <span class="text-red-600">*</span></span>
                                    <div class="mt-2 flex items-center space-x-6">
                                        <label class="inline-flex items-center">
                                            <input type="radio"
                                                   name="billing_type"
                                                   id="billingForfait"
                                                   value="forfait"
                                                   class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                                   checked>
                                            <span class="ml-2 text-sm text-gray-700">{{ __('messages.Fixed Price') }}</span>
                                        </label>
                                        <label class="inline-flex items-center">
                                            <input type="radio"
                                                   name="billing_type"
                                                   id="billingHoraire"
                                                   value="horaire"
                                                   class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                            <span class="ml-2 text-sm text-gray-700">{{ __('messages.Hourly') }}</span>
                                        </label>
                                        <label class="inline-flex items-center">
                                            <input type="radio"
                                                   name="billing_type"
                                                   id="billingDetailedHourly"
                                                   value="detailed_hourly"
                                                   class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                            <span class="ml-2 text-sm text-gray-700">{{ __('messages.Detailed Hourly') }}</span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            {{-- Hourly Inputs (shown when Hourly is selected) --}}
                            <div id="hourlyInputs" class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6" style="display: none;">
                                <div>
                                    <label for="hourlyHours" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Number of hours') }}</label>
                                    <input type="number"
                                           step="0.01"
                                           class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3"
                                           id="hourlyHours"
                                           name="hours">
                                </div>
                                <div>
                                    <label for="hourlyAmount" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Amount per hour (€)') }}</label>
                                    <input type="number"
                                           step="0.01"
                                           class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3"
                                           id="hourlyAmount"
                                           name="amount_per_hour">
                                </div>
                            </div>

                            {{-- Description --}}
                            <div class="mt-6">
                                <label for="projectDescription" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Devis Description') }} <span class="text-red-600">*</span></label>
                                <textarea class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3" id="projectDescription" rows="5" name="description"></textarea>
                            </div>
                            
                            {{-- Dynamic Inputs (Tasks) --}}
                            <div id="devisDetailsSection" class="mt-6">
                                <label class="block text-base font-semibold text-gray-800 mb-2">{{ __('messages.Devis Details') }}</label>
                                <div id="dynamicInput" class="space-y-4">
                                    {{-- Initial Row --}}
                                    <div class="grid grid-cols-12 gap-4 p-4 border border-gray-200 rounded-lg detail-row">
                                        <div class="col-span-12 md:col-span-4">
                                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Details label') }} <span class="text-red-600">*</span></label>
                                            <textarea class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3" name="task[]" rows="3"></textarea>
                                        </div>
                                        <div class="col-span-12 md:col-span-2 task-hours-column" style="display: none;">
                                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Number of Hours') }} <span class="text-red-600">*</span></label>
                                            <input type="number" step="0.01" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3 taskHours" name="taskHours[]" />
                                        </div>
                                        <div class="col-span-12 md:col-span-2 task-budget-column">
                                            <label class="block text-sm font-medium text-gray-700 mb-1 budget-label">{{ __('messages.Budget label devis') }} <span class="text-red-600">*</span></label>
                                            <input type="number" step="0.0001" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3 taskBudget" name="taskBudget[]" />
                                        </div>
                                        <div class="col-span-12 md:col-span-2 task-cost-per-hour-column" style="display: none;">
                                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Cost per Hour') }} <span class="text-red-600">*</span></label>
                                            <input type="number" step="0.0001" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3 taskCostPerHour" name="taskCostPerHour[]" />
                                        </div>
                                        <div class="col-span-12 md:col-span-2 task-total-column">
                                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Total label devis') }}</label>
                                            <input type="text" class="block w-full rounded-lg border-gray-300 shadow-sm bg-gray-100 sm:text-sm py-2 px-3 taskTotal" readonly />
                                        </div>
                                        <div class="col-span-12 md:col-span-1 flex items-end">
                                            <button type="button" aria-label="{{ __('messages.Delete') }}" title="{{ __('messages.Delete') }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-slate-200 text-red-600 hover:bg-red-50" onclick="deleteDetailRow(this)">
                                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                {{-- Add/Delete Buttons --}}
                                <div class="flex space-x-2 mt-4">
                                    <button type="button" class="dt-btn dt-btn-teal text-[12px]" onclick="addInput('dynamicInput');">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" /></svg>
                                        {{ __('messages.Add Row Devis') }}
                                    </button>
                                    <button type="button" class="dt-btn dt-btn-ghost text-[12px] text-red-700" onclick="removeInput('dynamicInput');">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
                                        {{ __('messages.Delete Row Devis') }}
                                    </button>
                                </div>
                            </div>

                            {{-- Divider --}}
                            <hr class="border-gray-200 my-6" />

                            {{-- Client Informations --}}
                            <h5 class="text-lg font-semibold text-gray-900 mb-4">{{ __('messages.Client Information') }}</h5>
                            
                            {{-- New Client Checkbox --}}
                            <div class="flex items-center mb-4">
                                <input type="checkbox" id="newClientCheckbox" class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                <label for="newClientCheckbox" class="ml-2 block text-sm font-medium text-gray-900">{{ __('messages.New Client') }}</label>
                            </div>

                            {{-- New Client Form --}}
                            <div id="clientInputs" style="display: none;" class="p-4 border border-gray-200 rounded-lg space-y-4 mb-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Name field') }} <span class="text-red-600">*</span></label>
                                        <input class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3" type="text" placeholder="{{ __('messages.Full Name placeholder') }}" name="newclient_name" />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Client Number field') }} <span class="text-red-600">*</span></label>
                                        @error('client_number')
                                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                        @enderror
                                        <input class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3" type="number" placeholder="{{ __('messages.Client Number placeholder') }}" name="newclient_number" />
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Phone Number field') }} </label>
                                        <input class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3" type="number" placeholder="{{ __('messages.Phone Number placeholder') }}" name="newclient_phone" />
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Street field') }} <span class="text-red-600">*</span></label>
                                        <input class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3" type="text" placeholder="{{ __('messages.Your Full Address Here') }}" name="newclient_street" />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.City field') }} <span class="text-red-600">*</span></label>
                                        <input class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3" type="text" placeholder="{{ __('messages.City placeholder') }}" name="newclient_city" />
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Zip Code field') }} <span class="text-red-600">*</span></label>
                                        <input class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3" type="number" placeholder="{{ __('messages.Zip Code placeholder') }}" name="newclient_zip" />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Country field') }} <span class="text-red-600">*</span></label>
                                        <input class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3" type="text" placeholder="{{ __('messages.Country placeholder') }}" name="newclient_country" />
                                    </div>
                                </div>
                                <div class="pt-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Emails') }} </label>
                                    <textarea class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3" id="emails" name="emails" rows="4"></textarea>
                                    <p class="text-sm text-gray-500 mt-1">{{ __('messages.Enter recipient email addresses, separated by commas') }}</p>
                                </div>
                            </div>
                            
                            {{-- Existing Client & Contacts --}}
                            <div class="existing-client-section grid grid-cols-1 md:grid-cols-3 gap-6" style="display: {{ old('emails') === null ? 'grid' : 'none' }}">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1" for="clientSelect">{{ __('messages.Client for Project:') }}</label>
                                    <select
                                        id="clientSelect" 
                                        name="client"
                                        class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3 form-select-custom"
                                    >
                                        <option value="">{{ __('messages.Choose Client') }}</option>
                                        <option value="none">{{ __('messages.None option') }}</option>
                                        @foreach($clients as $client)
                                            <option value="{{ $client->id }}">
                                                {{ $client->client_number }} - {{ $client->name }}
                                            </option>
                                        @endforeach
                                        
                                    </select>
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1" for="project-for-project">{{ __('messages.Contacts :') }}</label>
                                    <div class="col-8" id="contactsDiv">
                                    </div>
                                </div>
                            </div>

                            {{-- Form Actions --}}
                            <div class="flex flex-col-reverse sm:flex-row sm:justify-end sm:items-center gap-2 mt-8 pt-5 border-t border-slate-100">
                                @if(Auth::user()->hasRole('Responsable de projet'))
                                    <a class="dt-btn dt-btn-ghost justify-center" href="{{route('Responsable_de_projet.index')}}">{{ __('messages.Cancel Devis') }}</a>
                                @endif
                                @if(Auth::user()->hasRole('Admin'))
                                    <a class="dt-btn dt-btn-ghost justify-center" href="{{route('admin.index')}}">{{ __('messages.Cancel Devis') }}</a>
                                @endif
                                @if(Auth::user()->hasRole('Employee'))
                                    <a class="dt-btn dt-btn-ghost justify-center" href="{{route('employee.index')}}">{{ __('messages.Cancel Devis') }}</a>
                                @endif
                                @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                    <a class="dt-btn dt-btn-ghost justify-center" href="{{route('Responsable_gestionnaire.index')}}">{{ __('messages.Cancel Devis') }}</a>
                                @endif
                                <button type="submit" name="button" class="dt-btn dt-btn-primary justify-center">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19z"/></svg>
                                    {{ __('messages.Generate Devis') }}
                                </button>
                            </div>
                        </form>
                    </div> 
                </div> 
            </div> 

        </main>
    </div> {{-- End Main Wrapper --}}

    @livewireScripts
    @include('dashboard_layouts.scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    // Disable autocomplete
    window.addEventListener('load', function() {
        var inputs = document.getElementsByTagName('input');
        for (var i = 0; i < inputs.length; i++) {
            inputs[i].setAttribute('autocomplete', 'none');
        }
    });

    // Add/Remove Task Inputs
    function addInput(divName) {
        var newdiv = document.createElement('div');
        const isDetailedHourly = document.getElementById('billingDetailedHourly') && document.getElementById('billingDetailedHourly').checked;
        const hoursColumnDisplay = isDetailedHourly ? 'block' : 'none';
        
        newdiv.className = 'grid grid-cols-12 gap-4 p-4 border border-gray-200 rounded-lg detail-row';
        
        const budgetColumnDisplay = isDetailedHourly ? 'none' : 'block';
        const costPerHourColumnDisplay = isDetailedHourly ? 'block' : 'none';
        // Total column is always shown now
        const totalColumnDisplay = 'block';
        
        newdiv.innerHTML =
            '<div class="col-span-12 md:col-span-4">' +
                '<label class="block text-sm font-medium text-gray-700 mb-1">' + @json(__('messages.Details')) + ' <span class="text-red-600">*</span></label>' +
                '<textarea class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3" name="task[]" rows="3"></textarea>' +
            '</div>' +
            '<div class="col-span-12 md:col-span-2 task-hours-column" style="display: ' + hoursColumnDisplay + ';">' +
                '<label class="block text-sm font-medium text-gray-700 mb-1">' + @json(__('messages.Number of Hours')) + ' <span class="text-red-600">*</span></label>' +
                '<input type="number" step="0.01" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3 taskHours" name="taskHours[]" />' +
            '</div>' +
            '<div class="col-span-12 md:col-span-2 task-budget-column" style="display: ' + budgetColumnDisplay + ';">' +
                '<label class="block text-sm font-medium text-gray-700 mb-1 budget-label">' + @json(__('messages.Budget')) + ' <span class="text-red-600">*</span></label>' +
                '<input type="number" step="0.0001" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3 taskBudget" name="taskBudget[]" />' +
            '</div>' +
            '<div class="col-span-12 md:col-span-2 task-cost-per-hour-column" style="display: ' + costPerHourColumnDisplay + ';">' +
                '<label class="block text-sm font-medium text-gray-700 mb-1">' + @json(__('messages.Cost per Hour')) + ' <span class="text-red-600">*</span></label>' +
                '<input type="number" step="0.0001" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3 taskCostPerHour" name="taskCostPerHour[]" />' +
            '</div>' +
            '<div class="col-span-12 md:col-span-2 task-total-column" style="display: ' + totalColumnDisplay + ';">' +
                '<label class="block text-sm font-medium text-gray-700 mb-1">' + @json(__('messages.Total')) + '</label>' +
                '<input type="text" class="block w-full rounded-lg border-gray-300 shadow-sm bg-gray-100 sm:text-sm py-2 px-3 taskTotal" readonly />' +
            '</div>' +
            '<div class="col-span-12 md:col-span-1 flex items-end">' +
                '<button type="button" class="inline-flex items-center justify-center rounded-md border border-transparent bg-red-600 px-3 py-2 text-sm font-medium text-white shadow-sm hover:bg-red-700" style="margin-bottom: 35px;" onclick="deleteDetailRow(this)">' +
                    '<svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>' +
                '</button>' +
            '</div>';
        
        document.getElementById(divName).appendChild(newdiv);
        const newTaskBudgetInput = newdiv.querySelector('.taskBudget');
        const newTaskHoursInput = newdiv.querySelector('.taskHours');
        const newTaskCostPerHourInput = newdiv.querySelector('.taskCostPerHour');
        if (newTaskBudgetInput) {
            newTaskBudgetInput.addEventListener('input', updateProjectBudget);
        }
        if (newTaskHoursInput) {
            newTaskHoursInput.addEventListener('input', updateProjectBudget);
        }
        if (newTaskCostPerHourInput) {
            newTaskCostPerHourInput.addEventListener('input', updateProjectBudget);
        }
    }

    function deleteDetailRow(button) {
        const detailRows = document.querySelectorAll('.detail-row');
        if (detailRows.length > 1) {
            button.closest('.detail-row').remove();
            updateProjectBudget();
        } else {
            alert(@json(__('messages.At least one detail row must remain.')));
        }
    }

    function removeInput(divName) {
        var div = document.getElementById(divName);
        if (div.children.length > 1) {
            div.removeChild(div.lastChild);
            updateProjectBudget();
        }
    }
</script>

<script>
    // Client & Contact Logic (using a native <select> for better positioning)
    document.addEventListener('DOMContentLoaded', function() {
        const clientSelect = document.getElementById('clientSelect');
        const newClientCheckbox = document.getElementById('newClientCheckbox');
        const clientInputs = document.getElementById('clientInputs');
        const existingClientSection = document.querySelector('.existing-client-section');
        const contactsDiv = document.getElementById('contactsDiv');

        newClientCheckbox.addEventListener('change', function() {
            if (this.checked) {
                clientInputs.style.display = 'block';
                existingClientSection.style.display = 'none';
                clientSelect.value = '';
                contactsDiv.innerHTML = '';
            } else {
                clientInputs.style.display = 'none';
                existingClientSection.style.display = 'grid';
            }
        });

        if (clientSelect) {
            clientSelect.addEventListener('change', function () {
                const clientId = this.value;

                if (!clientId || clientId === 'none') {
                    contactsDiv.innerHTML = clientId === 'none'
                        ? '<p class="text-sm text-gray-500 mt-2">' + @json(__('messages.No contacts found.')) + '</p>'
                        : '';
                    return;
                }

                window.loadContacts(clientId);
            });
        }

        window.loadContacts = function (clientId) {
            let baseUrl = '';
            @if(Auth::user()->hasRole('Responsable de projet'))
                baseUrl = '/Responsable_de_projet/get-contacts/';
            @elseif(Auth::user()->hasRole('Admin'))
                baseUrl = '/admin/get-contacts/';
            @elseif(Auth::user()->hasRole('Employee'))
                baseUrl = '/employee/get-contacts/';
            @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                baseUrl = '/Responsable_gestionnaire/get-contacts/';
            @endif

            $.ajax({
                url: baseUrl + clientId,
                type: 'GET',
                success: function(data) {
                    const contactsDiv = $('#contactsDiv');
                    contactsDiv.empty(); 
                    if(data.length === 0) {
                         contactsDiv.html('<p class="text-sm text-gray-500 mt-2">' + @json(__('messages.No contacts found.')) + '</p>');
                         return;
                    }
                    data.forEach(function(contact) {
                        const checkbox = `
                            <div class="flex items-center mt-2">
                                <input class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" type="checkbox" 
                                       name="contacts[]" id="contact${contact.id}" value="${contact.id}">
                                <label class="ml-2 block text-sm text-gray-900" for="contact${contact.id}">
                                    ${contact.name}
                                </label>
                            </div>`;
                        contactsDiv.append(checkbox);
                    });
                },
                error: function(xhr, status, error) {
                    console.error('Error loading contacts:', error);
                    $('#contactsDiv').html('<div class="text-red-600 text-sm p-2">' + @json(__('messages.Error loading contacts devis')) + '</div>');
                }
            });
        }
    });
</script>

<script>
    // **Budget Logic (Forfait / Hourly)**

    // Calculates budget based on billing type
    function updateProjectBudget() {
        const projectBudgetInput = document.getElementById('projectBudget');
        const billingHoraireRadio = document.getElementById('billingHoraire');
        const billingDetailedHourlyRadio = document.getElementById('billingDetailedHourly');

        // Hourly: use hours * amount_per_hour
        if (billingHoraireRadio && billingHoraireRadio.checked) {
            const hoursInput = document.getElementById('hourlyHours');
            const amountInput = document.getElementById('hourlyAmount');

            const hours = parseFloat(hoursInput ? hoursInput.value : '') || 0;
            const amount = parseFloat(amountInput ? amountInput.value : '') || 0;

            if (projectBudgetInput) {
                projectBudgetInput.value = (hours * amount).toFixed(2);
            }
            return;
        }

        // Detailed Hourly: sum of (task hours * task cost_per_hour) for each detail
        if (billingDetailedHourlyRadio && billingDetailedHourlyRadio.checked) {
            let totalBudget = 0;
            
            document.querySelectorAll('.detail-row').forEach(function(row) {
                const hoursInput = row.querySelector('.taskHours');
                const costPerHourInput = row.querySelector('.taskCostPerHour');
                const totalInput = row.querySelector('.taskTotal');
                
                const hours = parseFloat(hoursInput ? hoursInput.value : '') || 0;
                const costPerHour = parseFloat(costPerHourInput ? costPerHourInput.value : '') || 0;
                const total = hours * costPerHour;
                
                if (totalInput) {
                    totalInput.value = total.toFixed(2);
                }
                
                totalBudget += total;
            });
            
            if (projectBudgetInput) {
                projectBudgetInput.value = totalBudget.toFixed(2);
            }
            return;
        }

        // Forfait: sum task budgets and update totals
        let totalBudget = 0;
        document.querySelectorAll('.detail-row').forEach(function(row) {
            const budgetInput = row.querySelector('.taskBudget');
            const totalInput = row.querySelector('.taskTotal');
            
            if (budgetInput) {
                let value = parseFloat(budgetInput.value);
                if (!isNaN(value)) {
                    totalBudget += value;
                    // Update the total column for this row
                    if (totalInput) {
                        totalInput.value = value.toFixed(2);
                    }
                } else if (totalInput) {
                    totalInput.value = '0.00';
                }
            }
        });
        if (projectBudgetInput) {
            projectBudgetInput.value = totalBudget.toFixed(2);
        }
    }
    
    // Add event listeners to all initial task budget inputs
    document.querySelectorAll('.taskBudget').forEach(function(input) {
        input.addEventListener('input', updateProjectBudget);
    });
    
    // Initialize totals for existing rows on page load
    updateProjectBudget();

    // **Project Type Switching Logic**

    function toggleProjectInputs() {
        var projectType = document.getElementById('projectType').value;
        var projectNameInput = document.getElementById('projectName');
        var existingProjectNameInput = document.getElementById('existingProjectName');

        if (projectType === 'new') {
            projectNameInput.disabled = false;
            projectNameInput.classList.remove('bg-gray-100');
            existingProjectNameInput.disabled = true;
            existingProjectNameInput.classList.add('bg-gray-100');
            existingProjectNameInput.value = '';
            document.getElementById('projectTva').value = '';
        } else if (projectType === 'existing') {
            projectNameInput.disabled = true;
            projectNameInput.classList.add('bg-gray-100');
            projectNameInput.value = '';
            existingProjectNameInput.disabled = false;
            existingProjectNameInput.classList.remove('bg-gray-100');
        } else {
            projectNameInput.disabled = true;
            projectNameInput.classList.add('bg-gray-100');
            projectNameInput.value = '';
            existingProjectNameInput.disabled = true;
            existingProjectNameInput.classList.add('bg-gray-100');
            existingProjectNameInput.value = '';
            document.getElementById('projectTva').value = '';
        }
    }

    function populateProjectDetails() {
        var existingProjectSelect = document.getElementById('existingProjectName');
        var selectedOption = existingProjectSelect.options[existingProjectSelect.selectedIndex];
        
        if (!selectedOption) {
            return;
        }

        var tva = selectedOption.getAttribute('data-tva') || '';
        document.getElementById('projectTva').value = tva;

        // Auto-select the corresponding client when an existing project is chosen
        var clientId = selectedOption.getAttribute('data-client-id');
        var clientSelect = document.getElementById('clientSelect');
        var contactsDiv = document.getElementById('contactsDiv');

        if (clientSelect) {
            clientSelect.value = clientId || '';

            if (clientId) {
                if (typeof window.loadContacts === 'function') {
                    window.loadContacts(clientId);
                } else {
                    // Fallback: trigger change event to let existing handler load contacts
                    var event = new Event('change');
                    clientSelect.dispatchEvent(event);
                }
            } else if (contactsDiv) {
                contactsDiv.innerHTML = '';
            }
        }
    }
</script>

{{-- New script for hourly and forfait Deviss --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const billingRadios = document.querySelectorAll('input[name="billing_type"]');
    const hourlyInputs = document.getElementById('hourlyInputs');
    const hoursInput = document.getElementById('hourlyHours');
    const amountInput = document.getElementById('hourlyAmount');

    function toggleHourlyInputs() {
        const isHoraire = document.getElementById('billingHoraire').checked;
        const isDetailedHourly = document.getElementById('billingDetailedHourly').checked;
        
        if (hourlyInputs) {
            hourlyInputs.style.display = isHoraire ? 'grid' : 'none';
        }

        // Hide devis details section when Hourly is selected
        const devisDetailsSection = document.getElementById('devisDetailsSection');
        if (devisDetailsSection) {
            devisDetailsSection.style.display = isHoraire ? 'none' : 'block';
        }

        // Show/hide columns in devis details based on billing type
        const taskHoursColumns = document.querySelectorAll('.task-hours-column');
        const taskBudgetColumns = document.querySelectorAll('.task-budget-column');
        const taskCostPerHourColumns = document.querySelectorAll('.task-cost-per-hour-column');
        const taskTotalColumns = document.querySelectorAll('.task-total-column');
        const budgetLabels = document.querySelectorAll('.budget-label');
        
        taskHoursColumns.forEach(function(column) {
            column.style.display = isDetailedHourly ? 'block' : 'none';
        });
        
        taskBudgetColumns.forEach(function(column) {
            column.style.display = isDetailedHourly ? 'none' : 'block';
        });
        
        taskCostPerHourColumns.forEach(function(column) {
            column.style.display = isDetailedHourly ? 'block' : 'none';
        });
        
        // Total column is always shown now
        taskTotalColumns.forEach(function(column) {
            column.style.display = 'block';
        });
        
        // Update grid columns based on billing type (using 12-column grid)
        const detailRows = document.querySelectorAll('.detail-row');
        detailRows.forEach(function(row) {
            row.className = 'grid grid-cols-12 gap-4 p-4 border border-gray-200 rounded-lg detail-row';
        });

        updateProjectBudget();
    }

    function handleHourlyInputChange() {
        updateProjectBudget();
    }

    billingRadios.forEach(function(radio) {
        radio.addEventListener('change', toggleHourlyInputs);
    });

    [hoursInput, amountInput].forEach(function(input) {
        if (input) {
            input.addEventListener('input', handleHourlyInputChange);
        }
    });

    // Add event listeners to task hours and cost per hour inputs
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('taskHours') || e.target.classList.contains('taskCostPerHour')) {
            updateProjectBudget();
        }
    });

    toggleHourlyInputs();
});
</script>
