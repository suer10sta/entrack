<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <x-favicon />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.Duplicate a Quote') }} - DalyTrac</title>
    @vite('resources/css/app.css')
    @include('admin.devis.partials.dashboardStyles')
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Styles personnalisés de votre fichier --}}
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
        /* Requis pour votre menu déroulant client Bootstrap JS
           Scoped pour ne pas impacter les sous-menus du sidebar */
        .existing-client-section .dropdown-menu {
            display: none; 
            position: absolute;
            z-index: 1000;
        }
        .existing-client-section .dropdown-menu.show {
            display: block; 
        }
        #clientInputs {
            display: none;
        }
    </style>
</head>

<body class="devis-dashboard-page dt-app-shell bg-[#f8fafc] text-slate-900 antialiased flex h-screen overflow-hidden">


    {{-- Sidebar Component --}}
    @if(Auth::user()->hasRole('Admin'))
        <x-dashboard.sidebar />
    @else
        <x-employees.sidebar />
    @endif

    {{-- Conteneur principal (Navbar + Contenu) --}}
    <div class="flex flex-col flex-1 overflow-y-auto">

        {{-- Navbar Component --}}
        <x-dashboard.navbar />

        {{-- Zone de contenu principal --}}
        <main class="flex-1 min-w-0 overflow-x-hidden overflow-y-auto bg-[#f8fafc] px-3 py-4 sm:px-5 sm:py-5 lg:px-16 lg:py-7">

            <div class="w-full max-w-6xl mx-auto space-y-5">

                <div>
                    <p class="dt-overline mb-0.5">{{ __('messages.Devis History') }}</p>
                    <h1 class="text-[18px] sm:text-[20px] lg:text-[22px] font-bold text-[#0B2638] leading-tight">{{ __('messages.Duplicate a Quote') }}</h1>
                </div>

                {{-- Message d'erreur de session (style Tailwind) --}}
                @if(session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <strong class="font-bold">{{ __('messages.Error!') }}</strong>
                    <span class="block sm:inline">{{ session('error') }}</span>
                    <button type="button" onclick="this.parentElement.style.display='none';" class="absolute top-0 bottom-0 right-0 px-4 py-3 font-bold text-xl" aria-label="{{ __('messages.Close') }}">&times;</button>
                </div>
                @endif

                {{-- Message de session (style Tailwind) --}}
                @if(session('message'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <strong class="font-bold">{{ __('messages.Success') }}!</strong>
                    <span class="block sm:inline">{{ session('message') }}</span>
                    <button type="button" onclick="this.parentElement.style.display='none';" class="absolute top-0 bottom-0 right-0 px-4 py-3 font-bold text-xl" aria-label="{{ __('messages.Close') }}">&times;</button>
                </div>
                @endif
                
                {{-- Déterminer les routes --}}
                @php
                    $saveRoute = '';
                    $cancelRoute = '';
                    if(Auth::user()->hasRole('Responsable de projet')) {
                        $saveRoute = route('Responsable_de_projet.devis.saveDuplicateDevis');
                        $cancelRoute = route('Responsable_de_projet.index');
                    } elseif(Auth::user()->hasRole('Admin')) {
                        $saveRoute = route('admin.devis.saveDuplicateDevis');
                        $cancelRoute = route('admin.index');
                    } elseif(Auth::user()->hasRole('Employee')) {
                        $saveRoute = route('employee.devis.saveDuplicateDevis');
                        $cancelRoute = route('employee.index');
                    } elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire')) {
                        $saveRoute = route('Responsable_gestionnaire.devis.saveDuplicateDevis');
                        $cancelRoute = route('Responsable_gestionnaire.index');
                    }
                @endphp

                {{-- Carte principale (style Tailwind) --}}
                <div class="dt-card overflow-hidden">
                    
                    {{-- En-tête de la carte --}}
                    <div class="px-5 py-4 border-b border-slate-100 flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-[#2D7080]/10 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-[#2D7080]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                        </div>
                        <div>
                            <p class="text-[13px] font-semibold text-[#0B2638]">{{ __('messages.Duplicate a Quote') }}</p>
                            <p class="text-[11.5px] text-slate-400">{{ __('messages.Duplicate an existing quote') }}</p>
                        </div>
                    </div>

                    {{-- Corps de la carte --}}
                    <div class="p-5 sm:p-6">
                        
                        @php
                            $billingType = old('billing_type', $devis->billing_type ?? 'forfait');
                            $isHourly = $billingType === 'horaire';
                            $isDetailedHourly = $billingType === 'detailed_hourly';
                            $hoursValue = old('hours', $devis->hours);
                            $amountValue = old('amount_per_hour', $devis->amount_per_hour ?? $devis->cost_per_hour);
                        @endphp

                        <form id="devisForm" class="devis-form" method="POST" action="{{ $saveRoute }}" autocomplete="off">
                            @csrf

                            {{-- Ligne Type de Projet (Grille Tailwind) --}}
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label for="projectType" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Select project type') }} <span class="text-red-600">*</span></label>
                                    <select class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3 form-select-custom" id="projectType" name="project_type" onchange="toggleProjectInputs()" required>
                                        <option value="">{{ __('messages.Select') }}</option>
                                        <option value="new" {{ $devis->Existing_project_name === null ? 'selected' : '' }}>{{ __('messages.New Project') }}</option>
                                        <option value="existing" {{ $devis->Existing_project_name !== null ? 'selected' : '' }}>{{ __('messages.Existing Project') }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="projectName" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.New project name') }} <span class="text-red-600">*</span></label>
                                    <input type="text" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3 {{ $devis->Existing_project_name !== null ? 'bg-gray-100' : '' }}" id="projectName" name="Project_name" value="{{ $devis->Existing_project_name === null ? ($devis->Project_name ?? '') : '' }}" {{ $devis->Existing_project_name !== null ? 'disabled' : '' }} required />
                                </div>
                                <div>
                                    <label for="existingProjectId" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Existing project name') }} <span class="text-red-600">*</span></label>
                                    {{-- NOTE : 'existingProjectName' a été renommé en 'existingProjectId' pour correspondre à votre 'name' --}}
                                    <select class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3 form-select-custom {{ $devis->Existing_project_name === null ? 'bg-gray-100' : '' }}" id="existingProjectId" name="Existing_project_id" {{ $devis->Existing_project_name === null ? 'disabled' : '' }} {{ $devis->Existing_project_name === null ? '' : 'required' }} onchange="populateProjectDetails()">
                                        <option value="">{{ __('messages.Select a project') }}</option>
                                        @foreach($projects as $project)
                                            <option value="{{ $project->id }}" 
                                                    {{ ($devis->Existing_project_name !== null && $project->id == $devis->project_id) ? 'selected' : '' }}
                                                    data-tva="{{ $project->tva }}" 
                                                {{ $project->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            {{-- Ligne Détails Projet (Grille Tailwind) --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                                <div>
                                    <label for="projectBudget" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Total Budget') }}</label>
                                    <input type="number" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3 bg-gray-100" id="projectBudget" name='budget' value="{{ old('budget', $devis->budget) }}" readonly/>
                                </div>
                                <div>
                                    <label for="projectTva" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Project VAT') }}</label>
                                    <input type="number" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3" id="projectTva" name='project_tva' value="{{$devis->tva}}" />
                                </div>
                            </div>

                            {{-- Type de devis (Forfait / Horaire / Detailed Hourly) --}}
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                                <div>
                                    <span class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Quote type') }} <span class="text-red-600">*</span></span>
                                    <div class="mt-2 flex items-center space-x-6">
                                        <label class="inline-flex items-center">
                                            <input type="radio"
                                                   name="billing_type"
                                                   id="billingForfait"
                                                   value="forfait"
                                                   class="h-4 w-4 text-[#2D7080] border-gray-300 rounded focus:ring-[#2D7080]"
                                                   {{ $billingType === 'forfait' ? 'checked' : '' }}>
                                            <span class="ml-2 text-sm text-gray-700">{{ __('messages.Fixed price') }}</span>
                                        </label>
                                        <label class="inline-flex items-center">
                                            <input type="radio"
                                                   name="billing_type"
                                                   id="billingHoraire"
                                                   value="horaire"
                                                   class="h-4 w-4 text-[#2D7080] border-gray-300 rounded focus:ring-[#2D7080]">
                                                   {{ $isHourly ? 'checked' : '' }}>
                                            <span class="ml-2 text-sm text-gray-700">{{ __('messages.Hourly') }}</span>
                                        </label>
                                        <label class="inline-flex items-center">
                                            <input type="radio"
                                                   name="billing_type"
                                                   id="billingDetailedHourly"
                                                   value="detailed_hourly"
                                                   class="h-4 w-4 text-[#2D7080] border-gray-300 rounded focus:ring-[#2D7080]">
                                                   {{ $isDetailedHourly ? 'checked' : '' }}>
                                            <span class="ml-2 text-sm text-gray-700">{{ __('messages.Detailed Hourly') }}</span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            {{-- Champs horaire (affichés si "Horaire" est sélectionné) --}}
                            <div id="hourlyInputs" class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6" style="display: {{ $isHourly ? 'grid' : 'none' }};">
                                <div>
                                    <label for="hourlyHours" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Number of hours') }}</label>
                                    <input type="number"
                                           step="0.01"
                                           class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3"
                                           id="hourlyHours"
                                           name="hours"
                                           value="{{ $hoursValue }}">
                                </div>
                                <div>
                                    <label for="hourlyAmount" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Amount per hour (€)') }}</label>
                                    <input type="number"
                                           step="0.01"
                                           class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3"
                                           id="hourlyAmount"
                                           name="amount_per_hour"
                                           value="{{ $amountValue }}">
                                </div>
                            </div>

                            {{-- Description --}}
                            <div class="mt-6">
                                <label for="projectDescription" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Quote description') }} <span class="text-red-600">*</span></label>
                                <textarea class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3" id="projectDescription" rows="5" name="description" required>{{$devis->description}}</textarea>
                            </div>
                            
                            {{-- Entrées Dynamiques --}}
                            <div id="devisDetailsSection" class="mt-6" style="display: {{ $isHourly ? 'none' : 'block' }};">
                                <label class="block text-base font-semibold text-gray-800 mb-2">{{ __('messages.Quote details') }}</label>
                                <div id="dynamicInput" class="space-y-4">
                                    @forelse($devis_details as $detail)
                                        <div class="grid grid-cols-12 gap-4 p-4 border border-gray-200 rounded-lg detail-row">
                                            <div class="col-span-12 md:col-span-4">
                                                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Details') }}</label>
                                                <textarea class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3" name="task[]" rows="3">{{ $detail->task_details }}</textarea>
                                            </div>
                                            <div class="col-span-12 md:col-span-2 task-hours-column" style="display: {{ $isDetailedHourly ? 'block' : 'none' }};">
                                                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Number of hours') }}</label>
                                                <input type="number" step="0.01" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3 taskHours" name="taskHours[]" value="{{ $detail->hours ?? '' }}" />
                                            </div>
                                            <div class="col-span-12 md:col-span-2 task-budget-column">
                                                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Budget') }}</label>
                                                <input type="number" step="0.0001" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3 taskBudget" name="taskBudget[]" value="{{ $detail->task_budget }}" />
                                            </div>
                                            <div class="col-span-12 md:col-span-2 task-cost-per-hour-column" style="display: {{ $isDetailedHourly ? 'block' : 'none' }};">
                                                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Cost per hour') }}</label>
                                                <input type="number" step="0.0001" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3 taskCostPerHour" name="taskCostPerHour[]" value="{{ $detail->cost_per_hour ?? '' }}" />
                                            </div>
                                            <div class="col-span-12 md:col-span-2 task-total-column">
                                                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Total') }}</label>
                                                <input type="text" class="block w-full rounded-lg border-gray-300 shadow-sm bg-gray-100 sm:text-sm py-2 px-3 taskTotal" readonly value="{{ $isDetailedHourly && isset($detail->hours) && isset($detail->cost_per_hour) ? number_format(($detail->hours ?? 0) * ($detail->cost_per_hour ?? 0), 2) : ($detail->task_budget ?? '0.00') }}" />
                                            </div>
                                            <div class="col-span-12 md:col-span-1 flex items-end">
                                                <button type="button" aria-label="{{ __('messages.Delete') }}" title="{{ __('messages.Delete') }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-slate-200 text-red-600 hover:bg-red-50" onclick="deleteDetailRow(this)">
                                                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
                                                </button>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="grid grid-cols-12 gap-4 p-4 border border-gray-200 rounded-lg detail-row">
                                            <div class="col-span-12 md:col-span-4">
                                                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Details') }}</label>
                                                <textarea class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3" name="task[]" rows="3"></textarea>
                                            </div>
                                            <div class="col-span-12 md:col-span-2 task-hours-column" style="display: {{ $isDetailedHourly ? 'block' : 'none' }};">
                                                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Number of hours') }}</label>
                                                <input type="number" step="0.01" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3 taskHours" name="taskHours[]" />
                                            </div>
                                            <div class="col-span-12 md:col-span-2 task-budget-column">
                                                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Budget') }}</label>
                                                <input type="number" step="0.0001" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3 taskBudget" name="taskBudget[]" />
                                            </div>
                                            <div class="col-span-12 md:col-span-2 task-cost-per-hour-column" style="display: {{ $isDetailedHourly ? 'block' : 'none' }};">
                                                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Cost per hour') }}</label>
                                                <input type="number" step="0.0001" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3 taskCostPerHour" name="taskCostPerHour[]" />
                                            </div>
                                            <div class="col-span-12 md:col-span-2 task-total-column">
                                                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Total') }}</label>
                                                <input type="text" class="block w-full rounded-lg border-gray-300 shadow-sm bg-gray-100 sm:text-sm py-2 px-3 taskTotal" readonly />
                                            </div>
                                            <div class="col-span-12 md:col-span-1 flex items-end">
                                                <button type="button" class="inline-flex items-center justify-center rounded-md border border-transparent bg-red-600 px-3 py-2 text-sm font-medium text-white shadow-sm hover:bg-red-700" style="margin-bottom: 35px;" onclick="deleteDetailRow(this)">
                                                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
                                                </button>
                                            </div>
                                        </div>
                                    @endforelse
                                </div>
                                {{-- Boutons Ajouter/Supprimer (style Tailwind) --}}
                                <div class="flex space-x-2 mt-4">
                                    <button type="button" class="dt-btn dt-btn-teal text-[12px]" onclick="addInput('dynamicInput');">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" /></svg>
                                        {{ __('messages.Add') }}
                                    </button>
                                    <button type="button" class="dt-btn dt-btn-ghost text-[12px] text-red-700" onclick="removeInput('dynamicInput');">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
                                        {{ __('messages.Remove') }}
                                    </button>
                                </div>
                            </div>

                            {{-- Séparateur --}}
                            <hr class="border-gray-200 my-6" />

                            {{-- Informations Client --}}
                            <h5 class="text-lg font-semibold text-gray-900 mb-4">{{ __('messages.Client Information') }}</h5>
                            
                            {{-- Checkbox Nouveau Client (style Tailwind) --}}
                            <div class="flex items-center mb-4">
                                <input type="checkbox" id="newClientCheckbox" class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                <label for="newClientCheckbox" class="ml-2 block text-sm font-medium text-gray-900">{{ __('messages.New Client') }}</label>
                            </div>

                            {{-- Formulaire Nouveau Client --}}
                            <div id="clientInputs" style="display: none;" class="p-4 border border-gray-200 rounded-lg space-y-4 mb-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Name') }} : <span class="text-red-600">*</span></label>
                                        <input class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3" type="text" placeholder="{{ __('messages.Full name') }}" name="newclient_name" data-required="true" />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Client Number') }} : <span class="text-red-600">*</span></label>
                                        @error('client_number')
                                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                        @enderror
                                        <input class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3" type="text" pattern="[0-9]*" inputmode="numeric" placeholder="{{ __('messages.Client Number') }}" name="newclient_number" data-required="true" />
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Phone') }} : </label>
                                        <input class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3" type="text" pattern="[0-9]*" inputmode="numeric" placeholder="{{ __('messages.Phone number') }}" name="newclient_phone" />
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Street') }} : <span class="text-red-600">*</span></label>
                                        <input class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3" type="text" placeholder="{{ __('messages.Your complete address here') }}" name="newclient_street" data-required="true" />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.City') }} : <span class="text-red-600">*</span></label>
                                        <input class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3" type="text" placeholder="{{ __('messages.City') }}" name="newclient_city" data-required="true" />
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Postal Code') }} : <span class="text-red-600">*</span></label>
                                        <input class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3" type="text" pattern="[0-9]*" inputmode="numeric" placeholder="{{ __('messages.Postal Code') }}" name="newclient_zip" data-required="true" />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Country') }} : <span class="text-red-600">*</span></label>
                                        <input class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3" type="text" placeholder="{{ __('messages.Country') }}" name="newclient_country" data-required="true" />
                                    </div>
                                </div>
                                <div class="pt-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Emails') }} </label>
                                    <textarea class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3" id="emails" name="emails" rows="4"></textarea>
                                    <p class="text-sm text-gray-500 mt-1">{{ __('messages.Enter email addresses of your different recipients separated by comma') }}</p>
                                </div>
                            </div>
                            
                            {{-- Client Existant & Contacts --}}
                            <div class="existing-client-section grid grid-cols-1 md:grid-cols-3 gap-6" style="display: {{ old('emails') === null ? 'grid' : 'none' }}">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1" for="task-for-project">{{ __('messages.Client for the Project') }}:</label>
                                    <input type="hidden" name="client" id="selectedClient" value="{{ $devis->client_id }}">
                                    
                                    {{-- Votre menu déroulant Bootstrap JS. --}}
                                    <div class="relative dropdown">
                                        <button class="relative w-full bg-white border border-gray-300 rounded-lg shadow-sm py-2 px-3 text-left cursor-default focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm" type="button" id="ClientDropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <span class="block truncate {{ $client ? 'text-gray-900' : 'text-gray-500' }}">
                                                {{ $client ? $client->name . ' - ' . $client->client_number : __('messages.Choose Client') }}
                                            </span>
                                            <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                                <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.25 4.25a.75.75 0 01-1.06 0L5.23 8.29a.75.75 0 01.02-1.06z" clip-rule="evenodd" /></svg>
                                            </span>
                                        </button>
                                        <ul class="dropdown-menu absolute z-10 mt-1 w-full bg-white shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm" aria-labelledby="ClientDropdown">
                                            <li><a class="dropdown-item text-gray-900 cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-gray-100 block" href="#" data-value="none">{{ __('messages.None') }}</a></li>
                                            @foreach($clients as $c)
                                            <li>
                                                <a class="dropdown-item text-gray-900 cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-gray-100 block" href="#" data-value="{{$c->id}}">
                                                    <span class="font-semibold">{{$c->client_number}}</span>-<span class="truncate">{{$c->name}}</span>
                                                </a>
                                            </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1" for="project-for-project">{{ __('messages.Contacts') }} :</label>
                                    <div id="contactsDiv" class="space-y-2 mt-2">
                                        {{-- Les contacts seront ajoutés ici par JS --}}
                                    </div>
                                </div>
                            </div>

                            {{-- Actions du Formulaire (style Tailwind) --}}
                            <div class="flex flex-col-reverse sm:flex-row sm:justify-end sm:items-center gap-2 mt-8 pt-5 border-t border-slate-100">
                                <a href="javascript:history.back()" class="dt-btn dt-btn-ghost justify-center">
                                {{ __('messages.Cancel') }}
                                </a>
                                <button type="submit" name="button" class="dt-btn dt-btn-primary justify-center">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19z"/></svg>
                                    {{ __('messages.Generate quote') }}
                                </button>
                            </div>

                        </form>
                    </div> 
                </div> 
            </div> 

        </main>
    </div> 

    
    
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

    
    <script>
        // Désactiver l'autocomplétion
        window.addEventListener('load', function() {
            var inputs = document.getElementsByTagName('input');
            for (var i = 0; i < inputs.length; i++) {
                inputs[i].setAttribute('autocomplete', 'none');
            }
        });

        // **MIS À JOUR** Fonction AddInput pour générer du HTML Tailwind
        function addInput(divName) {
            var newdiv = document.createElement('div');
            const isDetailedHourly = document.getElementById('billingDetailedHourly') && document.getElementById('billingDetailedHourly').checked;
            const hoursColumnDisplay = isDetailedHourly ? 'block' : 'none';
            
            newdiv.className = 'grid grid-cols-12 gap-4 p-4 border border-gray-200 rounded-lg detail-row';
            
            let hoursColumnHtml = '';
            if (isDetailedHourly) {
                hoursColumnHtml = '<div class="col-span-12 md:col-span-2 task-hours-column" style="display: ' + hoursColumnDisplay + ';">' +
                    '<label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Number of hours') }}</label>' +
                    '<input type="number" step="0.01" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3 taskHours" name="taskHours[]" />' +
                '</div>';
            } else {
                hoursColumnHtml = '<div class="col-span-12 md:col-span-2 task-hours-column" style="display: none;">' +
                    '<label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Number of hours') }}</label>' +
                    '<input type="number" step="0.01" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3 taskHours" name="taskHours[]" />' +
                '</div>';
            }
            
            const budgetColumnDisplay = isDetailedHourly ? 'none' : 'block';
            const costPerHourColumnDisplay = isDetailedHourly ? 'block' : 'none';
            const totalColumnDisplay = 'block';
            
            newdiv.innerHTML =
                '<div class="col-span-12 md:col-span-4">' +
                    '<label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Details') }}</label>' +
                    '<textarea class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3" name="task[]" rows="3"></textarea>' +
                '</div>' +
                hoursColumnHtml +
                '<div class="col-span-12 md:col-span-2 task-budget-column" style="display: ' + budgetColumnDisplay + ';">' +
                    '<label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Budget') }}</label>' +
                    '<input type="number" step="0.0001" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3 taskBudget" name="taskBudget[]" />' +
                '</div>' +
                '<div class="col-span-12 md:col-span-2 task-cost-per-hour-column" style="display: ' + costPerHourColumnDisplay + ';">' +
                    '<label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Cost per hour') }}</label>' +
                    '<input type="number" step="0.0001" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3 taskCostPerHour" name="taskCostPerHour[]" />' +
                '</div>' +
                '<div class="col-span-12 md:col-span-2 task-total-column" style="display: ' + totalColumnDisplay + ';">' +
                    '<label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Total') }}</label>' +
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
                alert('{{ __('messages.At least one detail row must remain') }}.');
            }
        }

        // Fonction de suppression
        function removeInput(divName) {
            var div = document.getElementById(divName);
            if (div.children.length > 1) { 
                div.removeChild(div.lastChild);
                updateProjectBudget();
            }
        }

        // Mettre à jour le budget total en fonction du type de devis
        function updateProjectBudget() {
            const projectBudgetInput = document.getElementById('projectBudget');
            const billingHoraireRadio = document.getElementById('billingHoraire');
            const billingDetailedHourlyRadio = document.getElementById('billingDetailedHourly');

            // Si "Horaire" est sélectionné : budget = heures * montant par heure
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

            // Si "Horaire Détaillé" est sélectionné : somme de (heures tâche * coût par heure tâche) pour chaque détail
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

            // Sinon (Forfait) : somme des budgets des tâches et mise à jour des totaux
            let totalBudget = 0;
            document.querySelectorAll('.detail-row').forEach(function(row) {
                const budgetInput = row.querySelector('.taskBudget');
                const totalInput = row.querySelector('.taskTotal');
                
                if (budgetInput) {
                    let value = parseFloat(budgetInput.value);
                    if (!isNaN(value)) {
                        totalBudget += value;
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
        // Ajouter des écouteurs d'événements à toutes les entrées de budget, heures et coût par heure
        document.getElementById('dynamicInput').addEventListener('input', function(e) {
            if (e.target.classList.contains('taskBudget') || e.target.classList.contains('taskHours') || e.target.classList.contains('taskCostPerHour')) {
                updateProjectBudget();
            }
        });
        // Calculer le budget au chargement de la page
        updateProjectBudget(); 

        // Basculer entre les entrées Nouveau/Existant projet
        function toggleProjectInputs() {
            var projectType = document.getElementById('projectType').value;
            var projectNameInput = document.getElementById('projectName');
            var existingProjectSelect = document.getElementById('existingProjectId');

            if (projectType === 'new') {
                projectNameInput.disabled = false;
                projectNameInput.classList.remove('bg-gray-100'); 
                projectNameInput.required = true;
                existingProjectSelect.disabled = true;
                existingProjectSelect.classList.add('bg-gray-100'); 
                existingProjectSelect.required = false;
                existingProjectSelect.value = '';
                document.getElementById('projectTva').value = '';
            } else if (projectType === 'existing') {
                projectNameInput.disabled = true;
                projectNameInput.classList.add('bg-gray-100');
                projectNameInput.required = false;
                projectNameInput.value = '';
                existingProjectSelect.disabled = false;
                existingProjectSelect.classList.remove('bg-gray-100');
                existingProjectSelect.required = true;
                @if($devis->Existing_project_name !== null)
                    existingProjectSelect.value = '{{ $devis->project_id }}';
                @endif
                populateProjectDetails(); // Remplir les détails pour la sélection actuelle
            } else {
                projectNameInput.disabled = true;
                projectNameInput.classList.add('bg-gray-100');
                projectNameInput.required = false;
                projectNameInput.value = '';
                existingProjectSelect.disabled = true;
                existingProjectSelect.classList.add('bg-gray-100');
                existingProjectSelect.required = false;
                existingProjectSelect.value = '';
                document.getElementById('projectTva').value = '';
            }
        }

        // Remplir les détails lorsqu'un projet existant est sélectionné
        function populateProjectDetails() {
            var existingProjectSelect = document.getElementById('existingProjectId');
            var selectedOption = existingProjectSelect.options[existingProjectSelect.selectedIndex];
            
            var tva = selectedOption.getAttribute('data-tva') || '';
            
            document.getElementById('projectTva').value = tva;
        }

        // Logique pour le menu déroulant Bootstrap et le chargement des contacts
        document.addEventListener('DOMContentLoaded', function() {
            // Déclencher le toggle au chargement pour définir le bon état initial
            toggleProjectInputs();
            
            const dropdownButton = document.getElementById('ClientDropdown');
            const dropdownButtonText = dropdownButton.querySelector('span');
            const selectedClientIdInput = document.getElementById('selectedClient');
            const newClientCheckbox = document.getElementById('newClientCheckbox');
            const clientInputs = document.getElementById('clientInputs');
            const existingClientSection = document.querySelector('.existing-client-section');

            // Gérer la case à cocher nouveau client
            newClientCheckbox.addEventListener('change', function() {
                const newClientInputs = document.querySelectorAll('#clientInputs input, #clientInputs textarea');
                if (this.checked) {
                    clientInputs.style.display = 'block';
                    existingClientSection.style.display = 'none';
                    selectedClientIdInput.value = '';
                    document.getElementById('contactsDiv').innerHTML = '';
                    dropdownButtonText.textContent = @js(__('messages.Choose Client'));
                    dropdownButtonText.classList.add('text-gray-500');
                    newClientInputs.forEach(input => {
                        input.disabled = false; // Activer les champs nouveau client
                        // Ajouter l'attribut required si le champ a data-required="true"
                        if (input.getAttribute('data-required') === 'true') {
                            input.required = true;
                        }
                    });
                } else {
                    clientInputs.style.display = 'none';
                    existingClientSection.style.display = 'grid'; 
                    newClientInputs.forEach(input => {
                        input.disabled = true; // Désactiver les champs nouveau client
                        input.required = false; // Retirer l'attribut required quand caché
                    });
                }
            });

            // Gérer la sélection du client dans le menu déroulant
            document.querySelectorAll('.dropdown-item').forEach(item => {
                item.addEventListener('click', function(event) {
                    event.preventDefault();
                    const clientId = event.currentTarget.getAttribute('data-value');
                    const clientName = event.currentTarget.textContent.trim(); 

                    if (clientId === 'none') {
                        dropdownButtonText.textContent = @js(__('messages.Choose Client'));
                        dropdownButtonText.classList.add('text-gray-500');
                        selectedClientIdInput.value = '';
                        document.getElementById('contactsDiv').innerHTML = '';
                    } else {
                        dropdownButtonText.textContent = clientName;
                        dropdownButtonText.classList.remove('text-gray-500');
                        selectedClientIdInput.value = clientId;
                        loadContacts(clientId);
                    }
                });
            });

            // **MIS À JOUR** Fonction pour charger les contacts et générer des cases à cocher Tailwind
            function loadContacts(clientId) {
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
                        const selectedContacts = {!! json_encode($selectedContacts ?? []) !!};

                        data.forEach(function(contact) {
                            const isChecked = selectedContacts.includes(contact.id) ? 'checked' : '';
                            const checkbox = `
                                <div class="flex items-center">
                                    <input class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" type="checkbox" 
                                           name="contacts[]" 
                                           id="contact${contact.id}" 
                                           value="${contact.id}"
                                           ${isChecked}>
                                    <label class="ml-2 block text-sm text-gray-900" for="contact${contact.id}">
                                        ${contact.name}
                                    </label>
                                </div>`;
                            contactsDiv.append(checkbox);
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error('Erreur lors du chargement des contacts :', error);
                        $('#contactsDiv').html('<div class="text-red-600 p-2">{{ __('messages.Error loading contacts') }}</div>');
                    }
                });
            }
            
            if(selectedClientIdInput.value) {
                loadContacts(selectedClientIdInput.value);
            }
        });
    </script>

    {{-- Nouveau script pour les devis horaire / forfait --}}
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
            
            taskHoursColumns.forEach(function(column) {
                column.style.display = isDetailedHourly ? 'block' : 'none';
            });
            
            taskBudgetColumns.forEach(function(column) {
                column.style.display = isDetailedHourly ? 'none' : 'block';
            });
            
            taskCostPerHourColumns.forEach(function(column) {
                column.style.display = isDetailedHourly ? 'block' : 'none';
            });
            
            // Total column is always shown
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

</body>
</html>
