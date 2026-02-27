<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DalyTrac Dashboard - Edit Devis</title>
    @vite('resources/css/app.css')

    {{-- Your custom styles for ".editable" fields --}}
    <style>
        .editable[readonly] {
            border-color: #E5E7EB; /* gray-200 */
            background-color: #F9FAFB; /* gray-50 */
            padding-left: 0.75rem;
            padding-right: 0.75rem;
            cursor: pointer;
            font-weight: 500;
            color: #111827; /* text-gray-900 */
        }
        .editable[readonly]:hover {
            color: #2563EB; /* text-blue-600 */
            background-color: #F3F4F6; /* gray-100 */
        }
        .editable:not([readonly]) {
            border-color: #2563EB !important;
            box-shadow: 0 0 0 2px #BFDBFE !important; /* ring-blue-200 */
            background-color: #ffffff !important;
        }
        td.editable {
            cursor: pointer;
        }
        td.editable:hover {
            background-color: #f9fafb; /* gray-50 */
        }
    </style>
</head>
<body class="bg-gray-50 flex h-screen overflow-hidden">
    {{-- Sidebar --}}
    @if(Auth::user()->hasRole('Admin'))
        <x-dashboard.sidebar />
    @else
        <x-employees.sidebar />
    @endif

    {{-- Main content area --}}
    <div class="flex flex-col flex-1 overflow-y-auto">
        
        {{-- Navbar --}}
        <x-dashboard.navbar />

        {{-- Page Content --}}
        <main class="p-6 md:p-8 lg:p-10">

            
            @php
                $user = Auth::user();
                $contactsBaseUrl = '';
                if ($user->hasRole('Responsable de projet')) {
                    $contactsBaseUrl = url('/Responsable_de_projet/get-contacts');
                } elseif ($user->hasRole('Admin')) {
                    $contactsBaseUrl = url('/admin/get-contacts');
                } elseif ($user->hasRole('Employee')) {
                    $contactsBaseUrl = url('/employee/get-contacts');
                } elseif ($user->hasRole('Responsable de projet et gestionnaire')) {
                    $contactsBaseUrl = url('/Responsable_gestionnaire/get-contacts');
                }
            @endphp

            <div class="max-w-4xl mx-auto">
                {{-- Converted .card to Tailwind --}}
                <div class="bg-white shadow-lg rounded-xl p-6 md:p-8">
                    
                    <h2 class="text-xl font-semibold text-gray-900">{{ __('messages.Edit Devis') }}</h2>
                    <p class="text-sm text-gray-600">{{ __('messages.Edit Offre') }}</p>

                    {{-- Converted .alert-success to Tailwind --}}
                    @if(session('message'))
                        <div class="mt-4 rounded-md bg-green-50 p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-green-800">{{ session('message') }}</p>
                                </div>
                            </div>
                        </div>
                    @endif
                    
                    <hr class="my-4 border-gray-200" />

                    {{-- Preserving your original @if logic for user roles --}}
                    @if(Auth::user()->hasRole('Responsable de projet'))
                    <form id="devisForm" method="POST" action="{{route('Responsable_de_projet.devis.updateDevis', $devis->id)}}" autocomplete="off">
                    @endif
                    @if(Auth::user()->hasRole('Admin'))
                    <form id="devisForm" method="POST" action="{{route('admin.devis.updateDevis', $devis->id)}}" autocomplete="off">
                    @endif
                    @if(Auth::user()->hasRole('Employee'))
                    <form id="devisForm" method="POST" action="{{route('employee.devis.updateDevis', $devis->id)}}" autocomplete="off">
                    @endif
                    @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                    <form id="devisForm" method="POST" action="{{route('Responsable_gestionnaire.devis.updateDevis', $devis->id)}}" autocomplete="off">
                    @endif
                        @csrf
                        @method('PUT')

                        @php
                            $billingType = old('billing_type', $devis->billing_type ?? 'forfait');
                            $isHourly = $billingType === 'horaire';
                            $isDetailedHourly = $billingType === 'detailed_hourly';
                            $hoursValue = old('hours', $devis->hours);
                            $amountValue = old('amount_per_hour', $devis->amount_per_hour ?? $devis->cost_per_hour);
                        @endphp

                        {{-- Base input/select style: "mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" --}}

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label for="projectType" class="block text-sm font-medium text-gray-700">{{ __('messages.Select project type') }} <span class="text-red-600">*</span></label>
                                <select id="projectType" name="project_type" class="mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                                    <option value="">{{ __('messages.Select') }}</option>
                                    <option value="new" {{ $devis->Existing_project_name === null ? 'selected' : '' }}>{{ __('messages.New Project') }}</option>
                                    <option value="existing" {{ $devis->Existing_project_name !== null ? 'selected' : '' }}>{{ __('messages.Existing Project') }}</option>
                                </select>
                            </div>

                            <div>
                                <label for="projectName" class="block text-sm font-medium text-gray-700">{{ __('messages.New project name') }} <span class="text-red-600">*</span></label>
                                <input type="text" id="projectName" name="Project_name"
                                       value="{{ $devis->Existing_project_name === null ? $devis->Project_name : '' }}"
                                       {{ $devis->Existing_project_name !== null ? 'disabled' : '' }}
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm disabled:bg-gray-50" />
                            </div>

                            <div>
                                <label for="existingProjectId" class="block text-sm font-medium text-gray-700">{{ __('messages.Existing project name') }} <span class="text-red-600">*</span></label>
                                <select id="existingProjectId" name="Existing_project_id"
                                        {{ $devis->Existing_project_name === null ? 'disabled' : '' }}
                                        class="mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                                    <option value="">{{ __('messages.Select a project') }}</option>
                                    @foreach($projects as $project)
                                        <option value="{{ $project->id }}" 
                                                {{ ($devis->Existing_project_name !== null && $project->id == $devis->project_id) ? 'selected' : '' }}>
                                            {{ $project->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                        </div>

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

                        {{-- Devis Type (Forfait / Hourly / Detailed Hourly) --}}
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                            <div>
                                <span class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Quote type') }} <span class="text-red-600">*</span></span>
                                <div class="mt-2 flex items-center space-x-6">
                                    <label class="inline-flex items-center">
                                        <input type="radio"
                                               name="billing_type"
                                               id="billingForfait"
                                               value="forfait"
                                               class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                                               {{ $billingType === 'forfait' ? 'checked' : '' }}>
                                        <span class="ml-2 text-sm text-gray-700">{{ __('messages.Fixed price') }}</span>
                                    </label>
                                    <label class="inline-flex items-center">
                                        <input type="radio"
                                               name="billing_type"
                                               id="billingHoraire"
                                               value="horaire"
                                               class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                                               {{ $isHourly ? 'checked' : '' }}>
                                        <span class="ml-2 text-sm text-gray-700">{{ __('messages.Hourly') }}</span>
                                    </label>
                                    <label class="inline-flex items-center">
                                        <input type="radio"
                                               name="billing_type"
                                               id="billingDetailedHourly"
                                               value="detailed_hourly"
                                               class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                                               {{ $isDetailedHourly ? 'checked' : '' }}>
                                        <span class="ml-2 text-sm text-gray-700">{{ __('messages.Detailed Hourly') }}</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        {{-- Hourly Inputs (shown when Hourly is selected) --}}
                        <div id="hourlyInputs" class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6" style="display: {{ $isHourly ? 'grid' : 'none' }};">
                            <div>
                                <label for="hourlyHours" class="block text-sm font-medium text-gray-700">{{ __('messages.Number of hours') }}</label>
                                <input type="number"
                                       step="0.01"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                       id="hourlyHours"
                                       name="hours"
                                       value="{{ $hoursValue }}">
                            </div>
                            <div>
                                <label for="hourlyAmount" class="block text-sm font-medium text-gray-700">{{ __('messages.Amount per hour (€)') }}</label>
                                <input type="number"
                                       step="0.01"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                       id="hourlyAmount"
                                       name="amount_per_hour"
                                       value="{{ $amountValue }}">
                            </div>
                        </div>

                        <div class="mt-6">
                            <label for="projectDescription" class="block text-sm font-medium text-gray-700">{{ __('messages.Quote description') }} <span class="text-red-600">*</span></label>
                            <textarea id="projectDescription" name="description" rows="4"
                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{$devis->description}}</textarea>
                        </div>

                        <div id="devisDetailsSection" class="mt-6" style="display: {{ $isHourly ? 'none' : 'block' }};">
                            <h3 class="text-lg font-medium text-gray-900">{{ __('messages.Quote details') }}</h3>
                            <div id="dynamicInput" class="space-y-4">
                            @forelse($devis_details as $detail)
                                <div class="grid grid-cols-12 gap-4 detail-row">
                                    <div class="col-span-12 md:col-span-4">
                                        <label class="block text-sm font-medium text-gray-700">{{ __('messages.Details') }} <span class="text-red-600">*</span></label>
                                        <textarea class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" name="task[]" rows="3">{{ $detail->task_details }}</textarea>
                                    </div>
                                    <div class="col-span-12 md:col-span-2 task-hours-column" style="display: {{ $isDetailedHourly ? 'block' : 'none' }};">
                                        <label class="block text-sm font-medium text-gray-700">{{ __('messages.Number of hours') }} <span class="text-red-600">*</span></label>
                                        <input type="number" step="0.01" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm taskHours" name="taskHours[]" value="{{ $detail->hours ?? '' }}" />
                                    </div>
                                    <div class="col-span-12 md:col-span-2 task-budget-column">
                                        <label class="block text-sm font-medium text-gray-700">{{ __('messages.Budget') }} <span class="text-red-600">*</span></label>
                                        <input type="number" step="0.01" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm taskBudget" name="taskBudget[]" value="{{ $detail->task_budget }}" />
                                    </div>
                                    <div class="col-span-12 md:col-span-2 task-cost-per-hour-column" style="display: {{ $isDetailedHourly ? 'block' : 'none' }};">
                                        <label class="block text-sm font-medium text-gray-700">{{ __('messages.Cost per hour') }} <span class="text-red-600">*</span></label>
                                        <input type="number" step="0.0001" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm taskCostPerHour" name="taskCostPerHour[]" value="{{ $detail->cost_per_hour ?? '' }}" />
                                    </div>
                                    <div class="col-span-12 md:col-span-2 task-total-column">
                                        <label class="block text-sm font-medium text-gray-700">{{ __('messages.Total') }}</label>
                                        <input type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm bg-gray-100 taskTotal" readonly value="{{ $isDetailedHourly && isset($detail->hours) && isset($detail->cost_per_hour) ? number_format(($detail->hours ?? 0) * ($detail->cost_per_hour ?? 0), 2) : ($detail->task_budget ?? '0.00') }}" />
                                    </div>
                                    <div class="col-span-12 md:col-span-1 flex items-end">
                                        <button type="button" class="mt-1 inline-flex items-center justify-center rounded-md border border-transparent bg-red-600 px-3 py-2 text-sm font-medium text-white shadow-sm hover:bg-red-700" style="margin-bottom: 35px;" onclick="deleteDetailRow(this)">
                                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
                                        </button>
                                    </div>
                                </div>
                            @empty
                                <div class="grid grid-cols-12 gap-4 detail-row">
                                    <div class="col-span-12 md:col-span-4">
                                        <label class="block text-sm font-medium text-gray-700">Details <span class="text-red-600">*</span></label>
                                        <textarea class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" name="task[]" rows="3"></textarea>
                                    </div>
                                    <div class="col-span-12 md:col-span-2 task-hours-column" style="display: {{ $isDetailedHourly ? 'block' : 'none' }};">
                                        <label class="block text-sm font-medium text-gray-700">Number of Hours <span class="text-red-600">*</span></label>
                                        <input type="number" step="0.01" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm taskHours" name="taskHours[]" />
                                    </div>
                                    <div class="col-span-12 md:col-span-2 task-budget-column">
                                        <label class="block text-sm font-medium text-gray-700">Budget <span class="text-red-600">*</span></label>
                                        <input type="number" step="0.01" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm taskBudget" name="taskBudget[]" />
                                    </div>
                                    <div class="col-span-12 md:col-span-2 task-cost-per-hour-column" style="display: {{ $isDetailedHourly ? 'block' : 'none' }};">
                                        <label class="block text-sm font-medium text-gray-700">Cost per Hour <span class="text-red-600">*</span></label>
                                        <input type="number" step="0.0001" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm taskCostPerHour" name="taskCostPerHour[]" />
                                    </div>
                                    <div class="col-span-12 md:col-span-2 task-total-column">
                                        <label class="block text-sm font-medium text-gray-700">Total</label>
                                        <input type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm bg-gray-100 taskTotal" readonly />
                                    </div>
                                    <div class="col-span-12 md:col-span-1 flex items-end">
                                        <button type="button" class="mt-1 inline-flex items-center justify-center rounded-md border border-transparent bg-red-600 px-3 py-2 text-sm font-medium text-white shadow-sm hover:bg-red-700" onclick="deleteDetailRow(this)">
                                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
                                        </button>
                                    </div>
                                </div>
                            @endforelse
                            </div>

                            <div class="flex space-x-2 mt-4">
                                <button type="button" class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-lg text-sm" onclick="addInput('dynamicInput');">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" /></svg>
                                    {{ __('messages.Add') }}
                                </button>
                                <button type="button" class="inline-flex items-center bg-red-600 hover:bg-red-700 text-white font-medium px-4 py-2 rounded-lg text-sm" onclick="removeInput('dynamicInput');">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
                                    {{ __('messages.Delete') }}
                                </button>
                            </div>
                        </div>

                        <div class="mt-8 border-t border-gray-200 pt-6">
                            <h3 class="text-lg font-medium text-gray-900">{{ __('messages.Client Information') }}</h3>
                            <div class="relative flex items-start mt-4">
                                <div class="flex h-5 items-center">
                                    <input id="newClientCheckbox" name="newClientCheckbox" type="checkbox" 
                                           class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                           {{ $devis->emails !== null ? 'checked' : '' }}>
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="newClientCheckbox" class="font-medium text-gray-700">{{ __('messages.Create New Client') }}</label>
                                </div>
                            </div>

                            <div id="clientInputs" class="mt-4" style="display: {{ $devis->emails !== null ? 'block' : 'none' }};">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">{{ __('messages.Name') }} <span class="text-red-600">*</span></label>
                                        <input type="text" placeholder="{{ __('messages.Full name') }}" name="newclient_name" value="{{ $devis->emails !== null ? $devis->client_name : '' }}"
                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">{{ __('messages.Client Number') }}</label>
                                        @error('client_number')
                                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                        @enderror
                                        <input type="number" placeholder="{{ __('messages.Client Number') }}" name="newclient_number" value="{{ ($devis->emails !== null && $client) ? $client->client_number : '' }}"
                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">{{ __('messages.Phone number') }}</label>
                                        <input type="number" placeholder="{{ __('messages.Phone number') }}" name="newclient_phone" value="{{ ($devis->emails !== null && $client) ? $client->phone : '' }}"
                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">{{ __('messages.Street') }} <span class="text-red-600">*</span></label>
                                        <input type="text" placeholder="{{ __('messages.Your complete address here') }}" name="newclient_street" value="{{ ($devis->emails !== null && $client) ? $client->address : '' }}"
                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">{{ __('messages.City') }} <span class="text-red-600">*</span></label>
                                        <input type="text" placeholder="{{ __('messages.City') }}" name="newclient_city" value="{{ ($devis->emails !== null && $client) ? $client->city : '' }}"
                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">{{ __('messages.Zip Code') }} <span class="text-red-600">*</span></label>
                                        <input type="number" placeholder="{{ __('messages.Zip Code') }}" name="newclient_zip" value="{{ ($devis->emails !== null && $client) ? $client->zip : '' }}"
                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">{{ __('messages.Country') }} <span class="text-red-600">*</span></label>
                                        <input type="text" placeholder="{{ __('messages.Country') }}" name="newclient_country" value="{{ ($devis->emails !== null && $client) ? $client->country : '' }}"
                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                                    </div>
                                </div>
                                <div class="mt-6">
                                    <label for="emails" class="block text-sm font-medium text-gray-700">{{ __('messages.Emails') }}</label>
                                    <textarea id="emails" name="emails" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ $devis->emails }}</textarea>
                                    <p class="mt-2 text-sm text-gray-500">{{ __('messages.Enter email addresses of your different recipients separated by comma') }}</p>
                                </div>
                            </div>

                            <div id="existingClientSection" class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4" style="display: {{ $devis->emails === null ? 'grid' : 'none' }};">
                                <div>
                                    <label for="clientSelect" class="block text-sm font-medium text-gray-700">{{ __('messages.Client for the Project') }}:</label>
                                    {{-- This <select> now has the same class as the <input> fields --}}
                                    <select id="clientSelect" name="client" 
                                            class="mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                                            data-contacts-url="{{ $contactsBaseUrl }}">
                                        <option value="">{{ __('messages.Choose Client') }}</option>
                                        @foreach($clients as $clientItem)
                                            <option value="{{ $clientItem->id }}" {{ $clientItem->id == $devis->client_id ? 'selected' : '' }}>
                                                {{ $clientItem->client_number }} - {{ $clientItem->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">{{ __('messages.Contacts') }} :</label>
                                    <div id="contactsDiv" class="mt-2 space-y-2">
                                        </div>
                                </div>
                            </div>
                        </div>
                        
                        {{-- Preserving your original @if logic for user roles --}}
                        <div class="flex justify-end items-center gap-x-4 mt-8 pt-6 border-t border-gray-200">
                           <a href="javascript:history.back()" class='bg-white hover:bg-gray-100 text-gray-700 font-medium px-4 py-2 rounded-lg text-sm border border-gray-300 shadow-sm'>
                           {{ __('messages.Cancel') }}
                        </a>
                            
                            <button type="submit" name="button" 
                                    class="rounded-md bg-indigo-600 px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                                {{ __('messages.Update Devis') }}
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </main>
    </div>

    @include('dashboard_layouts.scripts')

    {{-- CONSOLIDATED SCRIPT BLOCK --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            
            // --- Project Type Toggle ---
            const projectTypeSelect = document.getElementById('projectType');
            const projectNameInput = document.getElementById('projectName');
            const existingProjectSelect = document.getElementById('existingProjectId');
            
            function toggleProjectInputs() {
                const projectType = projectTypeSelect.value;
                if (projectType === 'new') {
                    projectNameInput.disabled = false;
                    existingProjectSelect.disabled = true;
                    existingProjectSelect.value = '';
                } else if (projectType === 'existing') {
                    projectNameInput.disabled = true;
                    projectNameInput.value = '';
                    existingProjectSelect.disabled = false;
                    @if($devis->Existing_project_name !== null)
                        existingProjectSelect.value = '{{ $devis->project_id }}';
                    @endif
                } else {
                    projectNameInput.disabled = true;
                    projectNameInput.value = '';
                    existingProjectSelect.disabled = true;
                    existingProjectSelect.value = '';
                }
            }
            projectTypeSelect.addEventListener('change', toggleProjectInputs);
            toggleProjectInputs(); // Run on page load

            // --- New/Existing Client Toggle ---
            const newClientCheckbox = document.getElementById('newClientCheckbox');
            const clientInputs = document.getElementById('clientInputs');
            const existingClientSection = document.getElementById('existingClientSection');
            const clientSelect = document.getElementById('clientSelect');

            newClientCheckbox.addEventListener('change', function() {
                if (this.checked) {
                    clientInputs.style.display = 'block';
                    existingClientSection.style.display = 'none';
                    clientSelect.value = ''; // Clear existing client selection
                } else {
                    clientInputs.style.display = 'none';
                    existingClientSection.style.display = 'grid'; // Use grid to match layout
                }
            });

            // --- Contact Loading via AJAX ---
            const contactsBaseUrl = clientSelect.dataset.contactsUrl;
            const selectedContacts = {!! json_encode($selectedContacts ?? []) !!};

            function loadContacts(clientId) {
                if (!clientId || clientId === "") {
                    $('#contactsDiv').empty();
                    return;
                }

                $.ajax({
                    url: `${contactsBaseUrl}/${clientId}`,
                    type: 'GET',
                    success: function(data) {
                        const contactsDiv = $('#contactsDiv');
                        contactsDiv.empty();
                        
                        data.forEach(function(contact) {
                            const isChecked = selectedContacts.includes(contact.id) ? 'checked' : '';
                            const checkbox = `
                                <div class="relative flex items-start">
                                    <div class="flex h-5 items-center">
                                        <input class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" 
                                               type="checkbox" 
                                               name="contacts[]" 
                                               id="contact${contact.id}" 
                                               value="${contact.id}" ${isChecked}>
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="contact${contact.id}" class="font-medium text-gray-700">${contact.name}</label>
                                    </div>
                                </div>`;
                            contactsDiv.append(checkbox);
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error('Error loading contacts:', error);
                        $('#contactsDiv').html('<p class="text-sm text-red-600">{{ __('messages.Error loading contacts') }}</p>');
                    }
                });
            }
            
            clientSelect.addEventListener('change', () => loadContacts(clientSelect.value));
            loadContacts(clientSelect.value); // Load contacts for the pre-selected client

            // --- Budget Calculation (Forfait / Hourly / Detailed Hourly) ---
            function updateProjectBudget() {
                const projectBudgetInput = document.getElementById('projectBudget');
                const billingHoraireRadio = document.getElementById('billingHoraire');
                const billingDetailedHourlyRadio = document.getElementById('billingDetailedHourly');

                // Hourly: budget = hours * amount_per_hour
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
            
            // Add listener to all current and future budget inputs, hours, and cost per hour
            document.getElementById('dynamicInput').addEventListener('input', function(e) {
                if (e.target.classList.contains('taskBudget') || e.target.classList.contains('taskHours') || e.target.classList.contains('taskCostPerHour')) {
                    updateProjectBudget();
                }
            });
            // Run on page load to set initial budget
            updateProjectBudget();
        });

        // --- Dynamic Row Functions (Global) ---
        function updateProjectBudget() {
            const projectBudgetInput = document.getElementById('projectBudget');
            const billingHoraireRadio = document.getElementById('billingHoraire');
            const billingDetailedHourlyRadio = document.getElementById('billingDetailedHourly');

            // Hourly: budget = hours * amount_per_hour
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

        function addInput(divName) {
            var newdiv = document.createElement('div');
            const isDetailedHourly = document.getElementById('billingDetailedHourly') && document.getElementById('billingDetailedHourly').checked;
            const hoursColumnDisplay = isDetailedHourly ? 'block' : 'none';
            
            newdiv.className = 'grid grid-cols-12 gap-4 detail-row';
            
            let hoursColumnHtml = '';
            if (isDetailedHourly) {
                hoursColumnHtml = `
                <div class="col-span-12 md:col-span-2 task-hours-column" style="display: ${hoursColumnDisplay};">
                    <label class="block text-sm font-medium text-gray-700">{{ __('messages.Number of hours') }} <span class="text-red-600">*</span></label>
                    <input type="number" step="0.01" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm taskHours" name="taskHours[]" />
                </div>`;
            } else {
                hoursColumnHtml = `
                <div class="col-span-12 md:col-span-2 task-hours-column" style="display: ${hoursColumnDisplay};">
                    <label class="block text-sm font-medium text-gray-700">{{ __('messages.Number of hours') }} <span class="text-red-600">*</span></label>
                    <input type="number" step="0.01" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm taskHours" name="taskHours[]" />
                </div>`;
            }
            
            const budgetColumnDisplay = isDetailedHourly ? 'none' : 'block';
            const costPerHourColumnDisplay = isDetailedHourly ? 'block' : 'none';
            const totalColumnDisplay = 'block';
            
            newdiv.innerHTML = `
                <div class="col-span-12 md:col-span-4">
                    <label class="block text-sm font-medium text-gray-700">{{ __('messages.Details') }} <span class="text-red-600">*</span></label>
                    <textarea class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" name="task[]" rows="3"></textarea>
                </div>
                ${hoursColumnHtml}
                <div class="col-span-12 md:col-span-2 task-budget-column" style="display: ${budgetColumnDisplay};">
                    <label class="block text-sm font-medium text-gray-700">{{ __('messages.Budget') }} <span class="text-red-600">*</span></label>
                    <input type="number" step="0.01" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm taskBudget" name="taskBudget[]" />
                </div>
                <div class="col-span-12 md:col-span-2 task-cost-per-hour-column" style="display: ${costPerHourColumnDisplay};">
                    <label class="block text-sm font-medium text-gray-700">{{ __('messages.Cost per hour') }} <span class="text-red-600">*</span></label>
                    <input type="number" step="0.0001" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm taskCostPerHour" name="taskCostPerHour[]" />
                </div>
                <div class="col-span-12 md:col-span-2 task-total-column" style="display: ${totalColumnDisplay};">
                    <label class="block text-sm font-medium text-gray-700">{{ __('messages.Total') }}</label>
                    <input type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm bg-gray-100 taskTotal" readonly />
                </div>
                <div class="col-span-12 md:col-span-1 flex items-end">
                    <button type="button" class="mt-1 inline-flex items-center justify-center rounded-md border border-transparent bg-red-600 px-3 py-2 text-sm font-medium text-white shadow-sm hover:bg-red-700" style="margin-bottom: 35px;" onclick="deleteDetailRow(this)">
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
                    </button>
                </div>
            `;
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

        function removeInput(divName) {
            var div = document.getElementById(divName);
            const detailRows = document.querySelectorAll('.detail-row');
            if (detailRows.length > 1) {
                div.removeChild(div.lastChild);
                updateProjectBudget();
            } else {
                alert('{{ __('messages.At least one detail row must remain') }}.');
            }
        }

        // Disable autocomplete
        window.addEventListener('load', function() {
            var inputs = document.getElementsByTagName('input');
            for (var i = 0; i < inputs.length; i++) {
                inputs[i].setAttribute('autocomplete', 'off'); // Use "off" for modern browsers
            }
        });
    </script>
    {{-- New script for hourly and forfait quotes --}}
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

            // Update grid columns based on billing type (using fixed 12-column grid)
            const detailRows = document.querySelectorAll('.detail-row');
            detailRows.forEach(function(row) {
                row.className = 'grid grid-cols-12 gap-4 detail-row';
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
