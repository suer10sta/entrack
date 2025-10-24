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
                            <h6 class="mb-0">New Devis</h6>
                            <p class="text-sm mb-0">Create new Offre</p>
                            @if(session('message'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('message') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close">X</button>
                            </div>
                            @endif
                            <hr class="horizontal dark my-3" />
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
                                <div class="row">
                                    <div class="col-4">
                                        <label for="projectType" class="form-label">Select Project Type <span class="text-danger">*</span></label>
                                        <select class="form-control" id="projectType" name="project_type" onchange="toggleProjectInputs()">
                                            <option value="">Select</option>
                                            <option value="new">New Project</option>
                                            <option value="existing">Existing Project</option>
                                        </select>
                                    </div>
                                    <div class="col-4">
                                        <label for="projectName" class="form-label">New Project Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="projectName" name="Project_name" disabled />
                                    </div>
                                    <div class="col-4">
                                        <label for="existingProjectName" class="form-label">Existing Project Name <span class="text-danger">*</span></label>
                                        <select class="form-control" id="existingProjectName" name="Existing_project_id" disabled onchange="populateProjectDetails()">
                                            <option value="">Select a project</option>
                                            @foreach($projects as $project)
                                                <option value="{{ $project->id }}" data-tva="{{ $project->tva }}" data-cost-per-hour="{{ $project->cost_per_hour }}">{{ $project->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-4"> 
                                        <label for="projectBudget" class="form-label">Total Budget</label>
                                        <input type="number" class="form-control" id="projectBudget" name='budget' readonly/>
                                    </div>
                                    <div class="col-4">
                                        <label for="costPerHour" class="form-label">Cost per Hour</label>
                                        <input type="number" step="0.0001" class="form-control" id="costPerHour" name='cost_per_hour' />
                                    </div>
                                    <div class="col-4">
                                        <label for="projectTva" class="form-label">Project TVA</label>
                                        <input type="number" class="form-control" id="projectTva" name='project_tva' />
                                    </div>
                                </div>

                                <label class="mt-4">Devis Description <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="projectDescription" rows="5" name="description"></textarea>
                                
                                <div id="dynamicInput" class="mt-4">
                                    <div class="row mt-4">
                                        <div class="col-6">
                                            <label for="task" class="form-label">Details <span class="text-danger">*</span></label>
                                            <textarea class="form-control" name="task[]" rows="3"></textarea>
                                        </div>
                                        <div class="col-6">
                                            <label for="costPerHour" class="form-label">Budget <span class="text-danger">*</span></label>
                                            <input type="number" step="0.0001" class="form-control taskBudget" name="taskBudget[]" />
                                        </div>
                                        
                                    </div>
                                </div>

                                <button type="button" class="btn btn-sm btn-primary p-2 mt-4"
                                    onclick="addInput('dynamicInput');">Add</button>
                                <button type="button" class="btn btn-sm btn-danger p-2 mt-4"
                                    onclick="removeInput('dynamicInput');">Delete</button>
                                <br>

                                <h5>Client Informations :</h5>
                                <input type="checkbox" id="newClientCheckbox" class="form-label"> New Client
                                <br>

                                <div id="clientInputs" style="display: none;">

                                    <div class="multisteps-form__content ">
                                        <div class="row mt-3">
                                            <div class="col-12 col-sm-6">
                                                <label>Name : <span class="text-danger">*</span> </label>
                                                <input class="multisteps-form__input form-control"
                                                    type="text" placeholder="Full Name" name="newclient_name" />
                                            </div>
                                            <div class="col-12 col-sm-6">
                                                <label>Client Number : <span class="text-danger">*</span></label>
                                                @error('client_number')
                                                    <div class="alert alert-danger mt-1">{{ $message }}</div>
                                                @enderror
                                                <input class="multisteps-form__input form-control"
                                                    type="number" placeholder="Client Number" name="newclient_number" />
                                            </div>
                                            
                                            <div class="col-12 col-sm-6 mt-3 mt-sm-0">
                                                <label>Phone Number : </label>
                                                <input class="multisteps-form__input form-control"
                                                    type="number" placeholder="Phone Number" name="newclient_phone"/>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="multisteps-form__content ">
                                        <div class="row mt-3">
                                            <div class="col-12 col-sm-6">
                                                <label>Street : <span class="text-danger">*</span></label>
                                                <input class="multisteps-form__input form-control"
                                                    type="text" placeholder="Your Full Address Here" name="newclient_street" />
                                            </div>
                                            <div class="col-12 col-sm-6 mt-3 mt-sm-0">
                                                <label>City : <span class="text-danger">*</span></label>
                                                <input class="multisteps-form__input form-control"
                                                    type="text" placeholder="City" name="newclient_city"/>
                                            </div>
                                        </div>
                                        <div class="row mt-3">
                                            <div class="col-12 col-sm-6">
                                                <label>Zip Code : <span class="text-danger">*</span></label>
                                                <input class="multisteps-form__input form-control"
                                                    type="number" placeholder="Zip Code " name="newclient_zip"/>
                                            </div>
                                            <div class="col-12 col-sm-6">
                                            <label>Country : <span class="text-danger">*</span></label>
                                                <input class="multisteps-form__input form-control"
                                                    type="text" placeholder="Contry" name="newclient_country"/>
                                            </div>
                                        </div>
                                    </div>

                                    <label class="mt-4">Emails </label>
                                    <textarea class="form-control" id="emails" name="emails" rows="4"></textarea>
                                    <p>Entrez les adresses Email de vos différents destinataires séparés par virgule</p>
                                </div>
                                <br>
                                <div class="row">
                                    <div class="col-4">
                                        <label class="form-label" for="task-for-project">Client for Project:</label>
                                        <input type="hidden" name="client" id="selectedClient">

                                        <div class="dropdown" style="display: {{ old('emails') === null ? 'block' : 'none' }}">
                                            <button class="btn btn-secondary dropdown-toggle" type="button"
                                                id="ClientDropdown" data-bs-toggle="dropdown" aria-haspopup="true"
                                                aria-expanded="false">
                                                Choose Client
                                            </button>
                                            <ul class="dropdown-menu" aria-labelledby="ClientDropdown">
                                                <li><a class="dropdown-item" href="#" data-value="none">None</a></li>
                                                @foreach($clients as $client)
                                                <li><a class="dropdown-item" href="#" data-value="{{$client->id}}">
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
                                    <a class='btn btn-light m-0' href="{{route('Responsable_de_projet.index')}}">
                                        Cancel
                                    </a>
                                @endif
                                @if(Auth::user()->hasRole('Admin'))
                                    <a class='btn btn-light m-0' href="{{route('admin.index')}}">
                                        Cancel
                                    </a>
                                @endif
                                @if(Auth::user()->hasRole('Employee'))
                                    <a class='btn btn-light m-0' href="{{route('employee.index')}}">
                                        Cancel
                                    </a>
                                @endif
                                @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                    <a class='btn btn-light m-0' href="{{route('Responsable_gestionnaire.index')}}">
                                        Cancel
                                    </a>
                                @endif
                                    <button type="submit" name="button" class="btn bg-gradient-primary m-0 ms-2">
                                        Generate devis
                                    </button>
                                </div>

                            </form>

                        </div>
                    </div>
                </div>



    </main>



    @include('dashboard_layouts.scripts')

    <script>
  window.addEventListener('load', function() {
    var inputs = document.getElementsByTagName('input');
    for (var i = 0; i < inputs.length; i++) {
      inputs[i].setAttribute('autocomplete', 'none');
    }
  });
</script>

    <script>
    // Modify the addInput function to add event listener to new inputs
function addInput(divName) {
    var newdiv = document.createElement('div');
    newdiv.innerHTML =
        '<div class="row mt-4"><div class="col-6"><textarea class="form-control" name="task[]" rows="3"></textarea></div><div class="col-6"><input type="number" step="0.0001" class="form-control taskBudget" name="taskBudget[]" /></div></div>';
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
            const selectedClientIdInput = document.getElementById('selectedClient');
            const newClientCheckbox = document.getElementById('newClientCheckbox');
            const clientInputs = document.getElementById('clientInputs');
            const existingClientDropdown = document.querySelector('.dropdown');

            // Handle new client checkbox
            newClientCheckbox.addEventListener('change', function() {
                if (this.checked) {
                    clientInputs.style.display = 'block';
                    existingClientDropdown.style.display = 'none';
                    selectedClientIdInput.value = '';
                    // Clear contacts when switching to new client
                    document.getElementById('contactsDiv').innerHTML = '';
                } else {
                    clientInputs.style.display = 'none';
                    existingClientDropdown.style.display = 'block';
                }
            });

            // Handle client selection from dropdown
            document.querySelectorAll('.dropdown-item').forEach(item => {
                item.addEventListener('click', function(event) {
                    event.preventDefault();
                    const clientId = event.target.getAttribute('data-value');
                    const clientName = event.target.textContent;

                    if (clientId === 'none') {
                        dropdownButton.textContent = 'Choose Client';
                        selectedClientIdInput.value = '';
                        document.getElementById('contactsDiv').innerHTML = '';
                    } else {
                        dropdownButton.textContent = clientName;
                        selectedClientIdInput.value = clientId;
                        loadContacts(clientId);
                    }
                });
            });

            // Function to load contacts
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

                        data.forEach(function(contact) {
                            const checkbox = `
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" 
                                        name="contacts[]" 
                                        id="contact${contact.id}" 
                                        value="${contact.id}">
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
    const clientSelect = document.getElementById('clientSelect');
    const contactsContainer = document.getElementById('contactsContainer');
    
    clientSelect.addEventListener('change', function() {
        const selectedClientId = this.value;
        
        if (!selectedClientId) {
            contactsContainer.innerHTML = '';
            return;
        }

        // Show loading state
        contactsContainer.innerHTML = '<p>Loading contacts...</p>';

        // Use the route() helper to generate the correct URL
        fetch(`/get-contacts/${selectedClientId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                console.log('Received data:', data); // Debug log
                let contactsHtml = '';
                
                if (data.contacts && data.contacts.length > 0) {
                    data.contacts.forEach(contact => {
                        contactsHtml += `
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" 
                                    name="contacts[]" value="${contact.email}" 
                                    id="contact_${contact.id}">
                                <label class="form-check-label" for="contact_${contact.id}">
                                    ${contact.email}
                                </label>
                            </div>`;
                    });
                } else {
                    contactsHtml = '<p>No contacts found for this client.</p>';
                }
                contactsContainer.innerHTML = contactsHtml;
            })
            .catch(error => {
                console.error('Error:', error);
                contactsContainer.innerHTML = `<p class="text-danger">Error loading contacts: ${error.message}</p>`;
            });
    });
});
</script>

</body>

</html>