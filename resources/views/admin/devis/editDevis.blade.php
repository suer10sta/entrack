<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BizManager Dashboard - Edit Devis</title>
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
    <x-dashboard.sidebar />

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
                    
                    <h2 class="text-xl font-semibold text-gray-900">Edit Devis</h2>
                    <p class="text-sm text-gray-600">Edit Offre</p>

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

                        {{-- Base input/select style: "mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" --}}

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label for="projectType" class="block text-sm font-medium text-gray-700">Select Project Type <span class="text-red-600">*</span></label>
                                <select id="projectType" name="project_type" class="mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                                    <option value="">Select</option>
                                    <option value="new" {{ $devis->Existing_project_name === null ? 'selected' : '' }}>New Project</option>
                                    <option value="existing" {{ $devis->Existing_project_name !== null ? 'selected' : '' }}>Existing Project</option>
                                </select>
                            </div>

                            <div>
                                <label for="projectName" class="block text-sm font-medium text-gray-700">New Project Name <span class="text-red-600">*</span></label>
                                <input type="text" id="projectName" name="Project_name"
                                       value="{{ $devis->Existing_project_name === null ? $devis->Project_name : '' }}"
                                       {{ $devis->Existing_project_name !== null ? 'disabled' : '' }}
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm disabled:bg-gray-50" />
                            </div>

                            <div>
                                <label for="existingProjectId" class="block text-sm font-medium text-gray-700">Existing Project Name <span class="text-red-600">*</span></label>
                                <select id="existingProjectId" name="Existing_project_id"
                                        {{ $devis->Existing_project_name === null ? 'disabled' : '' }}
                                        class="mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                                    <option value="">Select Project</option>
                                    @foreach($projects as $project)
                                        <option value="{{ $project->id }}" 
                                                {{ ($devis->Existing_project_name !== null && $project->id == $devis->project_id) ? 'selected' : '' }}>
                                            {{ $project->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="projectBudget" class="block text-sm font-medium text-gray-700">Total Budget</label>
                                <input type="number" id="projectBudget" name='budget' value="{{ $devis->budget }}" readonly
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm bg-gray-100" />
                            </div>
                            
                            <div>
                                <label for="costPerHour" class="block text-sm font-medium text-gray-700">Cost per Hour</label>
                                <input type="number" step="0.01" id="costPerHour" name='cost_per_hour' value="{{$devis->cost_per_hour}}"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                            </div>

                            <div>
                                <label for="projectTva" class="block text-sm font-medium text-gray-700">Project TVA (%)</label>
                                <input type="number" id="projectTva" name='project_tva' value="{{$devis->tva}}"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                            </div>
                        </div>

                        <div class="mt-6">
                            <label for="projectDescription" class="block text-sm font-medium text-gray-700">Devis Description <span class="text-red-600">*</span></label>
                            <textarea id="projectDescription" name="description" rows="4"
                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{$devis->description}}</textarea>
                        </div>

                        <div id="dynamicInput" class="mt-6 space-y-4">
                            <h3 class="text-lg font-medium text-gray-900">Devis Details</h3>
                            @forelse($devis_details as $detail)
                                <div class="grid grid-cols-12 gap-4 detail-row">
                                    <div class="col-span-12 md:col-span-6">
                                        <label class="block text-sm font-medium text-gray-700">Details <span class="text-red-600">*</span></label>
                                        <textarea class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" name="task[]" rows="3">{{ $detail->task_details }}</textarea>
                                    </div>
                                    <div class="col-span-12 md:col-span-5">
                                        <label class="block text-sm font-medium text-gray-700">Budget <span class="text-red-600">*</span></label>
                                        <input type="number" step="0.01" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm taskBudget" name="taskBudget[]" value="{{ $detail->task_budget }}" />
                                    </div>
                                    <div class="col-span-12 md:col-span-1 flex items-end">
                                        <button type="button" class="mt-1 inline-flex items-center justify-center rounded-md border border-transparent bg-red-600 px-3 py-2 text-sm font-medium text-white shadow-sm hover:bg-red-700" onclick="deleteDetailRow(this)">
                                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
                                        </button>
                                    </div>
                                </div>
                            @empty
                                <div class="grid grid-cols-12 gap-4 detail-row">
                                    <div class="col-span-12 md:col-span-6">
                                        <label class="block text-sm font-medium text-gray-700">Details <span class="text-red-600">*</span></label>
                                        <textarea class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" name="task[]" rows="3"></textarea>
                                    </div>
                                    <div class="col-span-12 md:col-span-5">
                                        <label class="block text-sm font-medium text-gray-700">Budget <span class="text-red-600">*</span></label>
                                        <input type="number" step="0.01" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm taskBudget" name="taskBudget[]" />
                                    </div>
                                    <div class="col-span-12 md:col-span-1 flex items-end">
                                        <button type="button" class="mt-1 inline-flex items-center justify-center rounded-md border border-transparent bg-red-600 px-3 py-2 text-sm font-medium text-white shadow-sm hover:bg-red-700" onclick="deleteDetailRow(this)">
                                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
                                        </button>
                                    </div>
                                </div>
                            @endforelse
                        </div>

                        <div class="flex gap-x-2 mt-4">
                            <button type="button" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500" onclick="addInput('dynamicInput');">
                                Add Detail
                            </button>
                            <button type="button" class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50" onclick="removeInput('dynamicInput');">
                                Delete Last
                            </button>
                        </div>

                        <div class="mt-8 border-t border-gray-200 pt-6">
                            <h3 class="text-lg font-medium text-gray-900">Client Information</h3>
                            <div class="relative flex items-start mt-4">
                                <div class="flex h-5 items-center">
                                    <input id="newClientCheckbox" name="newClientCheckbox" type="checkbox" 
                                           class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                           {{ $devis->emails !== null ? 'checked' : '' }}>
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="newClientCheckbox" class="font-medium text-gray-700">Create New Client</label>
                                </div>
                            </div>

                            <div id="clientInputs" class="mt-4" style="display: {{ $devis->emails !== null ? 'block' : 'none' }};">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Name <span class="text-red-600">*</span></label>
                                        <input type="text" placeholder="Full Name" name="newclient_name" value="{{ $devis->emails !== null ? $devis->client_name : '' }}"
                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Client Number</label>
                                        @error('client_number')
                                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                        @enderror
                                        <input type="number" placeholder="Client Number" name="newclient_number" value="{{ ($devis->emails !== null && $client) ? $client->client_number : '' }}"
                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Phone Number</label>
                                        <input type="number" placeholder="Phone Number" name="newclient_phone" value="{{ ($devis->emails !== null && $client) ? $client->phone : '' }}"
                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Street <span class="text-red-600">*</span></label>
                                        <input type="text" placeholder="Your Full Address Here" name="newclient_street" value="{{ ($devis->emails !== null && $client) ? $client->address : '' }}"
                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">City <span class="text-red-600">*</span></label>
                                        <input type="text" placeholder="City" name="newclient_city" value="{{ ($devis->emails !== null && $client) ? $client->city : '' }}"
                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Zip Code <span class="text-red-600">*</span></label>
                                        <input type="number" placeholder="Zip Code" name="newclient_zip" value="{{ ($devis->emails !== null && $client) ? $client->zip : '' }}"
                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Country <span class="text-red-600">*</span></label>
                                        <input type="text" placeholder="Country" name="newclient_country" value="{{ ($devis->emails !== null && $client) ? $client->country : '' }}"
                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                                    </div>
                                </div>
                                <div class="mt-6">
                                    <label for="emails" class="block text-sm font-medium text-gray-700">Emails</label>
                                    <textarea id="emails" name="emails" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ $devis->emails }}</textarea>
                                    <p class="mt-2 text-sm text-gray-500">Entrez les adresses Email de vos différents destinataires séparés par virgule.</p>
                                </div>
                            </div>

                            <div id="existingClientSection" class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4" style="display: {{ $devis->emails === null ? 'grid' : 'none' }};">
                                <div>
                                    <label for="clientSelect" class="block text-sm font-medium text-gray-700">Client for Project:</label>
                                    {{-- This <select> now has the same class as the <input> fields --}}
                                    <select id="clientSelect" name="client" 
                                            class="mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                                            data-contacts-url="{{ $contactsBaseUrl }}">
                                        <option value="">Choose Client</option>
                                        @foreach($clients as $clientItem)
                                            <option value="{{ $clientItem->id }}" {{ $clientItem->id == $devis->client_id ? 'selected' : '' }}>
                                                {{ $clientItem->client_number }} - {{ $clientItem->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Contacts :</label>
                                    <div id="contactsDiv" class="mt-2 space-y-2">
                                        </div>
                                </div>
                            </div>
                        </div>
                        
                        {{-- Preserving your original @if logic for user roles --}}
                        <div class="flex justify-end items-center gap-x-4 mt-8 pt-6 border-t border-gray-200">
                           <a href="javascript:history.back()" class='bg-white hover:bg-gray-100 text-gray-700 font-medium px-4 py-2 rounded-lg text-sm border border-gray-300 shadow-sm'>
                           Cancel
                        </a>
                            
                            <button type="submit" name="button" 
                                    class="rounded-md bg-indigo-600 px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                                Update Devis
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
                        $('#contactsDiv').html('<p class="text-sm text-red-600">Error loading contacts</p>');
                    }
                });
            }
            
            clientSelect.addEventListener('change', () => loadContacts(clientSelect.value));
            loadContacts(clientSelect.value); // Load contacts for the pre-selected client

            // --- Budget Calculation ---
            function updateProjectBudget() {
                let totalBudget = 0;
                document.querySelectorAll('.taskBudget').forEach(function(input) {
                    let value = parseFloat(input.value);
                    if (!isNaN(value)) {
                        totalBudget += value;
                    }
                });
                document.getElementById('projectBudget').value = totalBudget.toFixed(2);
            }
            
            // Add listener to all current and future budget inputs
            document.getElementById('dynamicInput').addEventListener('input', function(e) {
                if (e.target.classList.contains('taskBudget')) {
                    updateProjectBudget();
                }
            });
            // Run on page load to set initial budget
            updateProjectBudget();
        });

        // --- Dynamic Row Functions (Global) ---
        function updateProjectBudget() {
            let totalBudget = 0;
            document.querySelectorAll('.taskBudget').forEach(function(input) {
                let value = parseFloat(input.value);
                if (!isNaN(value)) {
                    totalBudget += value;
                }
            });
            document.getElementById('projectBudget').value = totalBudget.toFixed(2);
        }

        function addInput(divName) {
            var newdiv = document.createElement('div');
            newdiv.className = 'grid grid-cols-12 gap-4 detail-row';
            newdiv.innerHTML = `
                <div class="col-span-12 md:col-span-6">
                    <label class="block text-sm font-medium text-gray-700">Details <span class="text-red-600">*</span></label>
                    <textarea class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" name="task[]" rows="3"></textarea>
                </div>
                <div class="col-span-12 md:col-span-5">
                    <label class="block text-sm font-medium text-gray-700">Budget <span class="text-red-600">*</span></label>
                    <input type="number" step="0.01" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm taskBudget" name="taskBudget[]" />
                </div>
                <div class="col-span-12 md:col-span-1 flex items-end">
                    <button type="button" class="mt-1 inline-flex items-center justify-center rounded-md border border-transparent bg-red-600 px-3 py-2 text-sm font-medium text-white shadow-sm hover:bg-red-700" onclick="deleteDetailRow(this)">
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
                    </button>
                </div>
            `;
            document.getElementById(divName).appendChild(newdiv);
        }

        function deleteDetailRow(button) {
            const detailRows = document.querySelectorAll('.detail-row');
            if (detailRows.length > 1) {
                button.closest('.detail-row').remove();
                updateProjectBudget();
            } else {
                alert('At least one detail row must remain.');
            }
        }

        function removeInput(divName) {
            var div = document.getElementById(divName);
            const detailRows = document.querySelectorAll('.detail-row');
            if (detailRows.length > 1) {
                div.removeChild(div.lastChild);
                updateProjectBudget();
            } else {
                alert('At least one detail row must remain.');
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
</body>
</html>