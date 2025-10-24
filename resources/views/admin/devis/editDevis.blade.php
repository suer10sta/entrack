<!DOCTYPE html>
<html lang="en">

@include('dashboard_layouts.head')
<style>
    #clientInputs {
  display: none;
}
</style>

<body class="g-sidenav-show bg-gray-100">
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-NKDMSK6" height="0" width="0"
            style="display: none; visibility: hidden"></iframe></noscript>
    <div class="min-height-300 bg-primary position-absolute w-100"></div>

    @if(Auth::user()->hasRole('Responsable de projet') || Auth::user()->hasRole('Employee') || Auth::user()->hasRole('Responsable de projet et gestionnaire'))
        @include('employees.sidebar')
    @endif
    @if(Auth::user()->hasRole('Admin'))
        @include('admin.sidebar')
    @endif

    <main class="main-content position-relative border-radius-lg">
        <nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl z-index-sticky"
            id="navbarBlur" data-scroll="false">
            <div class="container-fluid py-1 px-3">

                @include('dashboard_layouts.navbar')

            </div>

            <div class="container-fluid py-4">

                <div class="row">
                    <div class="col-lg-9 col-12 mx-auto">
                        <div class="card card-body mt-4">
                            <h6 class="mb-0">Edit Devis</h6>
                            <p class="text-sm mb-0">Edit Offre</p>
                            @if(session('message'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('message') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close">X</button>
                            </div>
                            @endif
                            <hr class="horizontal dark my-3" />
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
                                <div class="row">
                                    <div class="col-4">
                                        <label for="projectType" class="form-label">Select Project Type <span class="text-danger">*</span></label>
                                        <select class="form-control" id="projectType" name="project_type" onchange="toggleProjectInputs()">
                                            <option value="">Select</option>
                                            <option value="new" {{ $devis->Existing_project_name === null ? 'selected' : '' }}>New Project</option>
                                            <option value="existing" {{ $devis->Existing_project_name !== null ? 'selected' : '' }}>Existing Project</option>
                                        </select>
                                    </div>
                                    <div class="col-4">
                                        <label for="projectName" class="form-label">New Project Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="projectName" name="Project_name" 
                                            value="{{ $devis->Existing_project_name === null ? $devis->Project_name : '' }}" 
                                            {{ $devis->Existing_project_name !== null ? 'disabled' : '' }} />
                                    </div>
                                    <div class="col-4">
                                        <label for="existingProjectName" class="form-label">Existing Project Name <span class="text-danger">*</span></label>
                                        <select class="form-control" id="existingProjectId" name="Existing_project_id" 
                                                {{ $devis->Existing_project_name === null ? 'disabled' : '' }}>
                                            <option value="">Select Project</option>
                                            @foreach($projects as $project)
                                                <option value="{{ $project->id }}" 
                                                        {{ ($devis->Existing_project_name !== null && $project->id == $devis->project_id) ? 'selected' : '' }}>
                                                    {{ $project->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-4"> 
                                        <label for="projectBudget" class="form-label">Total Budget</label>
                                        <input type="number" class="form-control" id="projectBudget" name='budget' 
                                            value="{{ $devis->budget }}" readonly/>
                                    </div>
                                    <div class="col-4">
                                        <label for="costPerHour" class="form-label">Cost per Hour</label>
                                        <input type="number" step="0.0001" class="form-control" id="costPerHour" name='cost_per_hour' 
                                            value="{{$devis->cost_per_hour}}"/>
                                    </div>
                                    <div class="col-4">
                                        <label for="projectTva" class="form-label">Project TVA</label>
                                        <input type="number" class="form-control" id="projectTva" name='project_tva' 
                                            value="{{$devis->tva}}" />
                                    </div>
                                </div>


                                <label class="mt-4">Devis Description <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="projectDescription" name="description">{{$devis->description}}</textarea>
                                
                                <div id="dynamicInput" class="mt-4">
                                    @if($devis_details->count() > 0)
                                        @foreach($devis_details as $detail)
                                        <div class="row mt-4 detail-row">
                                            <div class="col-6">
                                                <label for="task" class="form-label">Details <span class="text-danger">*</span></label>
                                                <textarea class="form-control" name="task[]" rows="3">{{ $detail->task_details }}</textarea>
                                            </div>
                                            <div class="col-5">
                                                <label for="costPerHour" class="form-label">Budget <span class="text-danger">*</span></label>
                                                <input type="number" step="0.0001" class="form-control taskBudget" name="taskBudget[]" value="{{ $detail->task_budget }}" />
                                            </div>
                                            <div class="col-1 d-flex align-items-end mb-2">
                                                <button type="button" class="btn btn-danger btn-sm delete-row" onclick="deleteDetailRow(this)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                        @endforeach
                                    @else
                                        <div class="row mt-4 detail-row">
                                            <div class="col-6">
                                                <label for="task" class="form-label">Details <span class="text-danger">*</span></label>
                                                <textarea class="form-control" name="task[]" rows="3"></textarea>
                                            </div>
                                            <div class="col-5">
                                                <label for="costPerHour" class="form-label">Budget <span class="text-danger">*</span></label>
                                                <input type="number" step="0.0001" class="form-control taskBudget" name="taskBudget[]" />
                                            </div>
                                            <div class="col-1 d-flex align-items-end mb-2">
                                                <button type="button" class="btn btn-danger btn-sm delete-row" onclick="deleteDetailRow(this)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                <button type="button" class="btn btn-sm btn-primary p-2 mt-4"
                                    onclick="addInput('dynamicInput');">Add</button>
                                <button type="button" class="btn btn-sm btn-danger p-2 mt-4"
                                    onclick="removeInput('dynamicInput');">Delete</button>
                                <br>

                                <!-- Client Section -->
                                <h5>Client Informations :</h5>
                                <input type="checkbox" id="newClientCheckbox" class="form-label" 
                                    {{ $devis->emails !== null ? 'checked' : '' }}> New Client
                                <br>

                                <div id="clientInputs" style="display: {{ $devis->emails !== null ? 'block' : 'none' }};">
                                    <div class="multisteps-form__content">
                                        <div class="row mt-3">
                                            <div class="col-12 col-sm-6">
                                                <label>Name : <span class="text-danger">*</span></label>
                                                <input class="multisteps-form__input form-control"
                                                    type="text" placeholder="Full Name" name="newclient_name" 
                                                    value="{{ $devis->emails !== null ? $devis->client_name : '' }}"/>
                                            </div>
                                            <div class="col-12 col-sm-6">
                                                <label>Client Number : </label>
                                                @error('client_number')
                                                    <div class="alert alert-danger mt-1">{{ $message }}</div>
                                                @enderror
                                                <input class="multisteps-form__input form-control"
                                                    type="number" placeholder="Client Number" name="newclient_number" 
                                                    value="{{ $devis->emails !== null ? $client->client_number : '' }}"/>
                                            </div>
                                            
                                            <div class="col-12 col-sm-6 mt-3 mt-sm-0">
                                                <label>Phone Number : </label>
                                                <input class="multisteps-form__input form-control"
                                                    type="number" placeholder="Phone Number" name="newclient_phone"
                                                    value="{{ $devis->emails !== null ? $client->phone : '' }}"/>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="multisteps-form__content">
                                        <div class="row mt-3">
                                            <div class="col-12 col-sm-6">
                                                <label>Street : <span class="text-danger">*</span></label>
                                                <input class="multisteps-form__input form-control"
                                                    type="text" placeholder="Your Full Address Here" name="newclient_street" 
                                                    value="{{ $devis->emails !== null ? $client->address : '' }}"/>
                                            </div>
                                            <div class="col-12 col-sm-6 mt-3 mt-sm-0">
                                                <label>City : <span class="text-danger">*</span></label>
                                                <input class="multisteps-form__input form-control"
                                                    type="text" placeholder="City" name="newclient_city"
                                                    value="{{ $devis->emails !== null ? $client->city : '' }}"/>
                                            </div>
                                        </div>
                                        <div class="row mt-3">
                                            <div class="col-12 col-sm-6">
                                                <label>Zip Code : <span class="text-danger">*</span></label>
                                                <input class="multisteps-form__input form-control"
                                                    type="number" placeholder="Zip Code" name="newclient_zip"
                                                    value="{{ $devis->emails !== null ? $client->zip : '' }}"/>
                                            </div>
                                            <div class="col-12 col-sm-6">
                                                <label>Country : <span class="text-danger">*</span></label>
                                                <input class="multisteps-form__input form-control"
                                                    type="text" placeholder="Country" name="newclient_country"
                                                    value="{{ $devis->emails !== null ? $client->country : '' }}"/>
                                            </div>
                                        </div>
                                    </div>

                                    <label class="mt-4">Emails </label>
                                    <textarea class="form-control" id="emails" name="emails" rows="4">{{ $devis->emails }}</textarea>
                                    <p>Entrez les adresses Email de vos différents destinataires séparés par virgule</p>
                                </div>

                                <!-- Existing Client Dropdown -->
                                <div class="row">
                                    <div class="col-4">
                                        <label class="form-label" for="task-for-project">Client for Project:</label>
                                        <input type="hidden" name="client" id="selectedClient" value="{{ $devis->client_id }}">

                                        <div class="dropdown" style="display: {{ $devis->emails === null ? 'block' : 'none' }}">
                                            <button class="btn btn-secondary dropdown-toggle" type="button"
                                                id="ClientDropdown" data-bs-toggle="dropdown" aria-haspopup="true"
                                                aria-expanded="false">
                                                {{ $devis->emails === null ? $devis->client_name . ' - ' . $client->client_number : 'Choose Client' }}
                                            </button>
                                            <ul class="dropdown-menu" aria-labelledby="ClientDropdown">
                                                <li><a class="dropdown-item" href="#" data-value="none">None</a></li>
                                                @foreach($clients as $client)
                                                <li><a class="dropdown-item" href="#" data-value="{{$client->id}}" 
                                                    class="{{ $client->id == $devis->client_id ? 'active' : '' }}">
                                                    <b style="color:black">{{$client->client_number}}</b>-{{$client->name}}
                                                </a></li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>


                                    <div class="col-8">
                                        <label class="form-label" for="project-for-project">Contacts :</label>

                                        <div class="col-8" id="contactsDiv">

                                            <!-- Contacts will be appended here -->
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end mt-4">
                                @if(Auth::user()->hasRole('Responsable de projet'))
                                    <a class='btn btn-light m-0' href="{{route('Responsable_de_projet.devis.devishistory')}}">
                                        Cancel
                                    </a>
                                @endif
                                @if(Auth::user()->hasRole('Admin'))
                                    <a class='btn btn-light m-0' href="{{route('admin.devis.devishistory')}}">
                                        Cancel
                                    </a>
                                @endif
                                @if(Auth::user()->hasRole('Employee'))
                                    <a class='btn btn-light m-0' href="{{route('employee.devis.devishistory')}}">
                                        Cancel
                                    </a>
                                @endif
                                @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                    <a class='btn btn-light m-0' href="{{route('Responsable_gestionnaire.devis.devishistory')}}">
                                        Cancel
                                    </a>
                                @endif
                                    <button type="submit" name="button" class="btn bg-gradient-primary m-0 ms-2">
                                        Update devis
                                    </button>
                                </div>

                            </form>

                        </div>
                    </div>
                </div>



    </main>



    @include('dashboard_layouts.scripts')

    
<script>
document.addEventListener('DOMContentLoaded', function() {
    const newClientCheckbox = document.getElementById('newClientCheckbox');
    const clientInputs = document.getElementById('clientInputs');
    const existingClientDropdown = document.querySelector('.dropdown');
    const selectedClientInput = document.getElementById('selectedClient');

    newClientCheckbox.addEventListener('change', function() {
        if (this.checked) {
            clientInputs.style.display = 'block';
            existingClientDropdown.style.display = 'none';
            selectedClientInput.value = '';
        } else {
            clientInputs.style.display = 'none';
            existingClientDropdown.style.display = 'block';
        }
    });

    // Initialize dropdown state based on emails field
    if ('{{ $devis->emails }}' !== '') {
        existingClientDropdown.style.display = 'none';
    }
});

// Your existing dropdown JavaScript
document.addEventListener('DOMContentLoaded', function() {
    const dropdownButton = document.getElementById('ClientDropdown');
    const selectedClientIdInput = document.getElementById('selectedClient');
    const selectedClientId = "{{ $devis->client_id }}";

    const defaultClient = document.querySelector(`.dropdown-item[data-value="${selectedClientId}"]`);
    if (defaultClient) {
        dropdownButton.textContent = defaultClient.textContent;
        selectedClientIdInput.value = selectedClientId;
    }

    document.querySelectorAll('.dropdown-item').forEach(item => {
        item.addEventListener('click', function(event) {
            event.preventDefault();
            const clientId = event.target.getAttribute('data-value');
            const clientName = event.target.textContent;

            if (clientId === 'none') {
                dropdownButton.textContent = 'Choose Client';
                selectedClientIdInput.value = '';
            } else {
                dropdownButton.textContent = clientName;
                selectedClientIdInput.value = clientId;
            }
        });
    });
});
</script>


<script>
function toggleProjectInputs() {
    var projectType = document.getElementById('projectType').value;
    var projectNameInput = document.getElementById('projectName');
    var existingProjectSelect = document.getElementById('existingProjectId');

    if (projectType === 'new') {
        projectNameInput.disabled = false;
        existingProjectSelect.disabled = true;
        existingProjectSelect.value = ''; // Reset to empty
    } else if (projectType === 'existing') {
        projectNameInput.disabled = true;
        projectNameInput.value = '';
        existingProjectSelect.disabled = false;
        // Restore the original project selection if it exists
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

// Set initial state on page load
document.addEventListener('DOMContentLoaded', function() {
    toggleProjectInputs();
});
</script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const dropdownButton = document.getElementById('ClientDropdown');
        const selectedClientIdInput = document.getElementById('selectedClient');
        const selectedClientId = "{{ $devis->client_id }}"; // Get the selected client ID from the server

        // Set the default selected client
        const defaultClient = document.querySelector(`.dropdown-item[data-value="${selectedClientId}"]`);
        if (defaultClient) {
            dropdownButton.textContent = defaultClient.textContent;
            selectedClientIdInput.value = selectedClientId;
        }

        // Add event listeners to update the selected client
        document.querySelectorAll('.dropdown-item').forEach(item => {
            item.addEventListener('click', function(event) {
                event.preventDefault();
                const clientId = event.target.getAttribute('data-value');
                const clientName = event.target.textContent;

                if (clientId === 'none') {
                    dropdownButton.textContent = 'Choose Client';
                    selectedClientIdInput.value = '';
                } else {
                    dropdownButton.textContent = clientName;
                    selectedClientIdInput.value = clientId;
                }
            });
        });
    });
</script>
    <script>
  window.addEventListener('load', function() {
    var inputs = document.getElementsByTagName('input');
    for (var i = 0; i < inputs.length; i++) {
      inputs[i].setAttribute('autocomplete', 'none');
    }
  });
</script>

    <script>
    // Update the addInput function
function addInput(divName) {
    var newdiv = document.createElement('div');
    newdiv.innerHTML = `
        <div class="row mt-4 detail-row">
            <div class="col-6">
                <label for="task" class="form-label">Details <span class="text-danger">*</span></label>
                <textarea class="form-control" name="task[]" rows="3"></textarea>
            </div>
            <div class="col-5">
                <label for="costPerHour" class="form-label">Budget <span class="text-danger">*</span></label>
                <input type="number" step="0.0001" class="form-control taskBudget" name="taskBudget[]" />
            </div>
            <div class="col-1 d-flex align-items-end mb-2">
                <button type="button" class="btn btn-danger btn-sm delete-row" onclick="deleteDetailRow(this)">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    `;
    document.getElementById(divName).appendChild(newdiv);

    // Add event listener to the new input
    const newTaskBudgetInput = newdiv.querySelector('.taskBudget');
    newTaskBudgetInput.addEventListener('input', updateProjectBudget);
}

// Add new function to delete specific rows
function deleteDetailRow(button) {
    const detailRows = document.querySelectorAll('.detail-row');
    if (detailRows.length > 1) { // Ensure at least one row remains
        const row = button.closest('.detail-row');
        row.remove();
        updateProjectBudget(); // Update the total budget after deletion
    } else {
        alert('At least one detail row must remain.');
    }
}

// You can remove or keep the removeInput function depending on if you want to keep the general delete button
function removeInput(divName) {
    var div = document.getElementById(divName);
    const detailRows = document.querySelectorAll('.detail-row');
    if (detailRows.length > 1) { // prevent removing when only one row remains
        div.removeChild(div.lastChild);
        updateProjectBudget();
    }
}
    </script>

    <script>
       document.addEventListener('DOMContentLoaded', function() {
            const dropdownButton = document.getElementById('ClientDropdown');
            const selectedClientIdInput = document.getElementById('selectedClient'); // Assuming this is the hidden input for the ID

            document.querySelectorAll('.dropdown-item').forEach(item => {
                item.addEventListener('click', function(event) {
                    event.preventDefault();

                    const clientId = event.target.getAttribute('data-value'); // Client's ID
                    const clientName = event.target.textContent; // Client's name

                    if (clientId === 'none') {
                        // Handle the "None" selection
                        dropdownButton.textContent = 'Choose Client'; // Reset to default text
                        selectedClientIdInput.value = ''; // Clear the hidden input value
                    } else {
                        // Update the button text to show the client's name
                        dropdownButton.textContent = clientName;

                        // Update the hidden input value to send the client's ID
                        selectedClientIdInput.value = clientId;
                    }
                });
            });
    });
    </script>

    <script>
    $(document).ready(function() {
    $('.dropdown-item').click(function() {
        var clientId = $(this).data('value');

        if (clientId === 'none') {
            // Clear the contacts div if "None" is selected
            $('#contactsDiv').empty();
        } else {
            // Proceed with AJAX call if a valid client ID is selected
            var updatedUrl = '';

            @if(Auth::user()->hasRole('Responsable de projet'))
                updatedUrl = '/Responsable_de_projet/get-contacts/' + clientId;
            @elseif(Auth::user()->hasRole('Admin'))
                updatedUrl = '/admin/get-contacts/' + clientId;
            @elseif(Auth::user()->hasRole('Employee'))
                updatedUrl = '/employee/get-contacts/' + clientId;
            @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                updatedUrl = '/Responsable_gestionnaire/get-contacts/' + clientId;
            @endif 
            $.ajax({
                url: updatedUrl, // replace with your Laravel route
                type: 'GET',
                success: function(data) {
                    var contactsDiv = $('#contactsDiv');
                    contactsDiv.empty(); // remove previous contacts
 
                    $.each(data, function(i, contact) {
                        var checkbox = '<div class="form-check">' +
                            '<input class="form-check-input" type="checkbox" name="contacts[]" id="contact' +
                            contact.id + '" value="' + contact.id + '">' +
                            '<label class="form-check-label" for="contact' + contact
                            .id + '">' +
                            contact.name +
                            '</label>' +
                            '</div>';

                        contactsDiv.append(checkbox);
                    });
                }
            });
        }
    });
});
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
  const newClientCheckbox = document.getElementById('newClientCheckbox');
  const clientInputs = document.getElementById('clientInputs');

  newClientCheckbox.addEventListener('change', function() {
    if (this.checked) {
      clientInputs.style.display = 'block';
    } else {
      clientInputs.style.display = 'none';
    }
  });
});
    </script>

<script>


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
// Add event listeners to all task budget inputs
document.querySelectorAll('.taskBudget').forEach(function(input) {
    input.addEventListener('input', updateProjectBudget);
})
</script>
<script>
function toggleProjectInputs() {
    var projectType = document.getElementById('projectType').value;
    var projectNameInput = document.getElementById('projectName');
    var existingProjectNameInput = document.getElementById('existingProjectName');

    if (projectType === 'new') {
        projectNameInput.disabled = false;
        existingProjectNameInput.disabled = true;
        existingProjectNameInput.value = '';
    } else if (projectType === 'existing') {
        projectNameInput.disabled = true;
        projectNameInput.value = '';
        existingProjectNameInput.disabled = false;
    } else {
        projectNameInput.disabled = true;
        projectNameInput.value = '';
        existingProjectNameInput.disabled = true;
        existingProjectNameInput.value = '';
    }
}
</script>

<script>
function populateProjectDetails() {
    var existingProjectSelect = document.getElementById('existingProjectName');
    var selectedOption = existingProjectSelect.options[existingProjectSelect.selectedIndex];
    
    var tva = selectedOption.getAttribute('data-tva');
    var costPerHour = selectedOption.getAttribute('data-cost-per-hour');
    
    document.getElementById('projectTva').value = tva;
    document.getElementById('costPerHour').value = costPerHour;
}
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initial load of contacts for the selected client
    const selectedClientId = document.getElementById('selectedClient').value;
    if (selectedClientId) {
        loadContacts(selectedClientId);
    }

    // Event listener for client selection
    $(document).on('click', '.dropdown-item', function(e) {
        e.preventDefault();
        const clientId = $(this).data('value');
        
        if (clientId === 'none') {
            $('#contactsDiv').empty();
        } else {
            loadContacts(clientId);
        }
    });

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

                // Get currently selected contacts
                const selectedContacts = {!! json_encode($selectedContacts ?? []) !!};

                data.forEach(function(contact) {
                    const isChecked = selectedContacts.includes(contact.id) ? 'checked' : '';
                    const checkbox = `
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" 
                                name="contacts[]" 
                                id="contact${contact.id}" 
                                value="${contact.id}"
                                ${isChecked}>
                            <label class="form-check-label" for="contact${contact.id}">
                                ${contact.name}
                            </label>
                        </div>`;
                    contactsDiv.append(checkbox);
                });
            },
            error: function(xhr, status, error) {
                console.error('Error loading contacts:', error);
                $('#contactsDiv').html('<div class="alert alert-danger">Error loading contacts</div>');
            }
        });
    }
});
</script>

</body>

</html>