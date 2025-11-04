<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Devis - BizManager</title>
    @vite('resources/css/app.css')
    @livewireStyles

    {{-- Custom styles from your target file --}}
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
        .dropdown-menu {
            display: none; 
        }
        .dropdown-menu.show {
            display: block; 
        }
        /* Style from your original file */
        #clientInputs {
            display: none;
        }
    </style>
</head>

<body class="bg-gray-50 flex h-screen overflow-hidden">

    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-NKDMSK6" height="0" width="0"
            style="display: none; visibility: hidden"></iframe></noscript>

    {{-- Sidebar Component (from target style) --}}
    <x-dashboard.sidebar />

    {{-- Main Wrapper (Navbar + Content) (from target style) --}}
    <div class="flex flex-col flex-1 overflow-y-auto">

        {{-- Navbar Component (from target style) --}}
        <x-dashboard.navbar />

        {{-- Main Content Area --}}
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-8">

            <div class="w-full max-w-4xl mx-auto">

                {{-- Session Message (Bootstrap alert translated to Tailwind) --}}
                @if(session('message'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <strong class="font-bold">Success!</strong>
                    <span class="block sm:inline">{{ session('message') }}</span>
                    <button type="button" onclick="this.parentElement.style.display='none';" class="absolute top-0 bottom-0 right-0 px-4 py-3 font-bold text-xl" aria-label="Close">&times;</button>
                </div>
                @endif

                {{-- Main Card (from target style) --}}
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    
                    {{-- Card Header --}}
                    <div class="p-6 border-b border-gray-200">
                        <h6 class="text-lg font-semibold text-gray-900">New Devis</h6>
                        <p class="text-sm text-gray-600 mt-1">Create new Offre</p>
                    </div>

                    {{-- Card Body --}}
                    <div class="p-6">
                        
                        {{-- Form (retains original logic) --}}
                        @if(Auth::user()->hasRole('Responsable de projet'))
                        <form id="devisForm" method="POST" action="{{route('Responsable_de_projet.devis.saveDevisPdf')}}" autocomplete="off">
                        @endif
                        @if(Auth::user()->hasRole('Employee'))
                        <form id="devisForm" method="POST" action="{{route('employee.devis.saveDevisPdf')}}" autocomplete="off">
                        @endif
                        @if(Auth::user()->hasRole('Admin'))
                        <form id="devisForm" method="POST" action="{{route('admin.devis.saveDevisPdf')}}" autocomplete="off">
                        @endif
                        @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                        <form id="devisForm" method="POST" action="{{route('Responsable_gestionnaire.devis.saveDevisPdf')}}" autocomplete="off">
                        @endif
                            @csrf

                            {{-- Project Type Row (Bootstrap row/col-* translated to Tailwind grid) --}}
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label for="projectType" class="block text-sm font-medium text-gray-700 mb-1">Select Project Type <span class="text-red-600">*</span></label>
                                    <select class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3 form-select-custom" id="projectType" name="project_type" onchange="toggleProjectInputs()">
                                        <option value="">Select</option>
                                        <option value="new">New Project</option>
                                        <option value="existing">Existing Project</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="projectName" class="block text-sm font-medium text-gray-700 mb-1">New Project Name <span class="text-red-600">*</span></label>
                                    <input type="text" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3 bg-gray-100" id="projectName" name="Project_name" disabled />
                                </div>
                                <div>
                                    <label for="existingProjectName" class="block text-sm font-medium text-gray-700 mb-1">Existing Project Name <span class="text-red-600">*</span></label>
                                    <select class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3 form-select-custom bg-gray-100" id="existingProjectName" name="Existing_project_id" disabled onchange="populateProjectDetails()">
                                        <option value="">Select a project</option>
                                        @foreach($projects as $project)
                                            <option value="{{ $project->id }}" data-tva="{{ $project->tva }}" data-cost-per-hour="{{ $project->cost_per_hour }}">{{ $project->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            {{-- Project Details Row (Bootstrap row/col-* translated to Tailwind grid) --}}
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                                <div>
                                    <label for="projectBudget" class="block text-sm font-medium text-gray-700 mb-1">Total Budget</label>
                                    <input type="number" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3 bg-gray-100" id="projectBudget" name='budget' readonly/>
                                </div>
                                <div>
                                    <label for="costPerHour" class="block text-sm font-medium text-gray-700 mb-1">Cost per Hour</label>
                                    <input type="number" step="0.0001" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3" id="costPerHour" name='cost_per_hour' />
                                </div>
                                <div>
                                    <label for="projectTva" class="block text-sm font-medium text-gray-700 mb-1">Project TVA</label>
                                    <input type="number" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3" id="projectTva" name='project_tva' />
                                </div>
                            </div>

                            {{-- Description --}}
                            <div class="mt-6">
                                <label for="projectDescription" class="block text-sm font-medium text-gray-700 mb-1">Devis Description <span class="text-red-600">*</span></label>
                                <textarea class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3" id="projectDescription" rows="5" name="description"></textarea>
                            </div>
                            
                            {{-- Dynamic Inputs --}}
                            <div class="mt-6">
                                <label class="block text-base font-semibold text-gray-800 mb-2">Devis Details</label>
                                <div id="dynamicInput" class="space-y-4">
                                    {{-- Initial Row --}}
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-4 border border-gray-200 rounded-lg">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Details <span class="text-red-600">*</span></label>
                                            <textarea class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3" name="task[]" rows="3"></textarea>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Budget <span class="text-red-600">*</span></label>
                                            <input type="number" step="0.0001" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3 taskBudget" name="taskBudget[]" />
                                        </div>
                                    </div>
                                </div>
                                {{-- Add/Delete Buttons (styled like target file) --}}
                                <div class="flex space-x-2 mt-4">
                                    <button type="button" class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-lg text-sm" onclick="addInput('dynamicInput');">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" /></svg>
                                        Add
                                    </button>
                                    <button type="button" class="inline-flex items-center bg-red-600 hover:bg-red-700 text-white font-medium px-4 py-2 rounded-lg text-sm" onclick="removeInput('dynamicInput');">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
                                        Delete
                                    </button>
                                </div>
                            </div>

                            {{-- Divider --}}
                            <hr class="border-gray-200 my-6" />

                            {{-- Client Informations --}}
                            <h5 class="text-lg font-semibold text-gray-900 mb-4">Client Information</h5>
                            
                            {{-- New Client Checkbox (Tailwind styled) --}}
                            <div class="flex items-center mb-4">
                                <input type="checkbox" id="newClientCheckbox" class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                <label for="newClientCheckbox" class="ml-2 block text-sm font-medium text-gray-900">New Client</label>
                            </div>

                            {{-- New Client Form --}}
                            <div id="clientInputs" style="display: none;" class="p-4 border border-gray-200 rounded-lg space-y-4 mb-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Name : <span class="text-red-600">*</span></label>
                                        <input class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3" type="text" placeholder="Full Name" name="newclient_name" />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Client Number : <span class="text-red-600">*</span></label>
                                        @error('client_number')
                                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                        @enderror
                                        <input class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3" type="number" placeholder="Client Number" name="newclient_number" />
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number : </label>
                                        <input class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3" type="number" placeholder="Phone Number" name="newclient_phone" />
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Street : <span class="text-red-600">*</span></label>
                                        <input class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3" type="text" placeholder="Your Full Address Here" name="newclient_street" />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">City : <span class="text-red-600">*</span></label>
                                        <input class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3" type="text" placeholder="City" name="newclient_city" />
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Zip Code : <span class="text-red-600">*</span></label>
                                        <input class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3" type="number" placeholder="Zip Code " name="newclient_zip" />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Country : <span class="text-red-600">*</span></label>
                                        <input class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3" type="text" placeholder="Contry" name="newclient_country" />
                                    </div>
                                </div>
                                <div class="pt-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Emails </label>
                                    <textarea class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3" id="emails" name="emails" rows="4"></textarea>
                                    <p class="text-sm text-gray-500 mt-1">Entrez les adresses Email de vos différents destinataires séparés par virgule</p>
                                </div>
                            </div>
                            
                            {{-- Existing Client & Contacts --}}
                            <div class="existing-client-section grid grid-cols-1 md:grid-cols-3 gap-6" style="display: {{ old('emails') === null ? 'grid' : 'none' }}">
                                <div class="dropdown">
                                    <label class="block text-sm font-medium text-gray-700 mb-1" for="task-for-project">Client for Project:</label>
                                    <input type="hidden" name="client" id="selectedClient">
                                    
                                    {{-- Bootstrap Dropdown, styled to look like Tailwind select --}}
                                    <div class="relative">
                                        <button class="relative w-full bg-white border border-gray-300 rounded-lg shadow-sm py-2 px-3 text-left cursor-default focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm" type="button" id="ClientDropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <span class="block truncate text-gray-500">Choose Client</span>
                                            <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                                <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.25 4.25a.75.75 0 01-1.06 0L5.23 8.29a.75.75 0 01.02-1.06z" clip-rule="evenodd" /></svg>
                                            </span>
                                        </button>
                                        <ul class="dropdown-menu absolute z-10 mt-1 w-full bg-white shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm" aria-labelledby="ClientDropdown">
                                            <li><a class="dropdown-item text-gray-900 cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-gray-100 block" href="#" data-value="none">None</a></li>
                                            @foreach($clients as $client)
                                            <li><a class="dropdown-item text-gray-900 cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-gray-100 block" href="#" data-value="{{$client->id}}">
                                                <span class="font-semibold">{{$client->client_number}}</span>-<span class="truncate">{{$client->name}}</span>
                                            </a></li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1" for="project-for-project">Contacts :</label>
                                    <div class="col-8" id="contactsDiv">
                                    </div>
                                </div>
                            </div>

                            {{-- Form Actions (styled like target file) --}}
                            <div class="flex justify-end items-center mt-8 pt-6 border-t border-gray-200 space-x-3">
                                @if(Auth::user()->hasRole('Responsable de projet'))
                                    <a class='bg-white hover:bg-gray-100 text-gray-700 font-medium px-4 py-2 rounded-lg text-sm border border-gray-300' href="{{route('Responsable_de_projet.index')}}">Cancel</a>
                                @endif
                                @if(Auth::user()->hasRole('Admin'))
                                    <a class='bg-white hover:bg-gray-100 text-gray-700 font-medium px-4 py-2 rounded-lg text-sm border border-gray-300' href="{{route('admin.index')}}">Cancel</a>
                                @endif
                                @if(Auth::user()->hasRole('Employee'))
                                    <a class='bg-white hover:bg-gray-100 text-gray-700 font-medium px-4 py-2 rounded-lg text-sm border border-gray-300' href="{{route('employee.index')}}">Cancel</a>
                                @endif
                                @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                    <a class='bg-white hover:bg-gray-100 text-gray-700 font-medium px-4 py-2 rounded-lg text-sm border border-gray-300' href="{{route('Responsable_gestionnaire.index')}}">Cancel</a>
                                @endif
                                <button type="submit" name="button" class="bg-green-600 hover:bg-green-700 text-white font-medium px-5 py-2.5 rounded-lg">
                                    Generate devis
                                </button>
                            </div>

                        </form>
                    </div> {{-- End p-6 --}}
                </div> {{-- End Card --}}
            </div> {{-- End max-w-4xl --}}

        </main>
    </div> {{-- End Main Wrapper --}}

    @livewireScripts
    {{-- Scripts from your original file --}}
    @include('dashboard_layouts.scripts')
    {{-- jQuery is required for the $.ajax call in your original scripts --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    // Disable autocomplete
    window.addEventListener('load', function() {
        var inputs = document.getElementsByTagName('input');
        for (var i = 0; i < inputs.length; i++) {
            inputs[i].setAttribute('autocomplete', 'none');
        }
    });

    // **UPDATED** AddInput function to generate Tailwind-styled HTML
    function addInput(divName) {
        var newdiv = document.createElement('div');
        // Set class for the new row
        newdiv.className = 'grid grid-cols-1 md:grid-cols-2 gap-6 p-4 border border-gray-200 rounded-lg';
        // Set inner HTML with Tailwind-styled inputs
        newdiv.innerHTML =
            '<div>' +
                '<textarea class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3" name="task[]" rows="3"></textarea>' +
            '</div>' +
            '<div>' +
                '<input type="number" step="0.0001" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3 taskBudget" name="taskBudget[]" />' +
            '</div>';
        
        document.getElementById(divName).appendChild(newdiv);

        // Add event listener to the new input
        const newTaskBudgetInput = newdiv.querySelector('.taskBudget');
        newTaskBudgetInput.addEventListener('input', updateProjectBudget);
    }

    // Modify the removeInput function to update the budget after removing an input
    function removeInput(divName) {
        var div = document.getElementById(divName);
        if (div.children.length > 1) { // prevent removing initial set of inputs
            div.removeChild(div.lastChild);
            updateProjectBudget();
        }
    }
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const dropdownButton = document.getElementById('ClientDropdown');
        const dropdownButtonText = dropdownButton.querySelector('span'); // Get the span inside the button
        const selectedClientIdInput = document.getElementById('selectedClient');
        const newClientCheckbox = document.getElementById('newClientCheckbox');
        const clientInputs = document.getElementById('clientInputs');
        const existingClientSection = document.querySelector('.existing-client-section');

        // Handle new client checkbox
        newClientCheckbox.addEventListener('change', function() {
            if (this.checked) {
                clientInputs.style.display = 'block';
                existingClientSection.style.display = 'none';
                selectedClientIdInput.value = '';
                // Clear contacts when switching to new client
                document.getElementById('contactsDiv').innerHTML = '';
                // Reset dropdown button
                dropdownButtonText.textContent = 'Choose Client';
                dropdownButtonText.classList.add('text-gray-500');
            } else {
                clientInputs.style.display = 'none';
                existingClientSection.style.display = 'grid'; // Use 'grid' to match our layout
            }
        });

        // Handle client selection from dropdown
        document.querySelectorAll('.dropdown-item').forEach(item => {
            item.addEventListener('click', function(event) {
                event.preventDefault();
                const clientId = event.currentTarget.getAttribute('data-value');
                const clientName = event.currentTarget.textContent.trim(); // Use textContent and trim

                if (clientId === 'none') {
                    // **UPDATED** to set the span's text
                    dropdownButtonText.textContent = 'Choose Client';
                    dropdownButtonText.classList.add('text-gray-500'); // Add placeholder color
                    selectedClientIdInput.value = '';
                    document.getElementById('contactsDiv').innerHTML = '';
                } else {
                    // **UPDATED** to set the span's text
                    dropdownButtonText.textContent = clientName;
                    dropdownButtonText.classList.remove('text-gray-500'); // Remove placeholder color
                    selectedClientIdInput.value = clientId;
                    loadContacts(clientId);
                }
            });
        });

        // **UPDATED** Function to load contacts and generate Tailwind-styled checkboxes
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
                    contactsDiv.empty(); // Clear previous contacts

                    data.forEach(function(contact) {
                        // **UPDATED** checkbox HTML with Tailwind classes
                        const checkbox = `
                            <div class="flex items-center">
                                <input class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" type="checkbox" 
                                       name="contacts[]" 
                                       id="contact${contact.id}" 
                                       value="${contact.id}">
                                <label class="ml-2 block text-sm text-gray-900" for="contact${contact.id}">
                                    ${contact.name}
                                </label>
                            </div>`;
                        contactsDiv.append(checkbox);
                    });
                },
                error: function(xhr, status, error) {
                    console.error('Error loading contacts:', error);
                    $('#contactsDiv').html('<div class="text-red-600 p-2">Error loading contacts</div>');
                }
            });
        }
    });
</script>

<script>
    // Update total budget when task budgets change
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
    // Add event listeners to all initial task budget inputs
    document.querySelectorAll('.taskBudget').forEach(function(input) {
        input.addEventListener('input', updateProjectBudget);
    });

    // Toggle between New/Existing project inputs
    function toggleProjectInputs() {
        var projectType = document.getElementById('projectType').value;
        var projectNameInput = document.getElementById('projectName');
        var existingProjectNameInput = document.getElementById('existingProjectName');

        if (projectType === 'new') {
            projectNameInput.disabled = false;
            projectNameInput.classList.remove('bg-gray-100'); // Make it look editable
            existingProjectNameInput.disabled = true;
            existingProjectNameInput.classList.add('bg-gray-100'); // Make it look disabled
            existingProjectNameInput.value = '';
            // Clear existing project details
            document.getElementById('projectTva').value = '';
            document.getElementById('costPerHour').value = '';
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
            // Clear existing project details
            document.getElementById('projectTva').value = '';
            document.getElementById('costPerHour').value = '';
        }
    }

    // Populate details when an existing project is selected
    function populateProjectDetails() {
        var existingProjectSelect = document.getElementById('existingProjectName');
        var selectedOption = existingProjectSelect.options[existingProjectSelect.selectedIndex];
        
        var tva = selectedOption.getAttribute('data-tva');
        var costPerHour = selectedOption.getAttribute('data-cost-per-hour');
        
        document.getElementById('projectTva').value = tva;
        document.getElementById('costPerHour').value = costPerHour;
    }
</script>

</body>
</html>