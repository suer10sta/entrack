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
                            <h6 class="mb-0">New Expense Note</h6>
                            @if(session('message'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('message') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close">X</button>
                            </div>
                            @endif
                            <hr class="horizontal dark my-3" />
                            @if(Auth::user()->hasRole('Responsable de projet'))
                            <form id="devisForm" method="POST" action="{{route('Responsable_de_projet.notedefrais.saveNoteDeFraisPdf')}}" autocomplete="off">
                            @endif
                            @if(Auth::user()->hasRole('Admin'))
                            <form id="devisForm" method="POST" action="{{route('admin.notedefrais.saveNoteDeFraisPdf')}}" autocomplete="off">
                            @endif
                            @if(Auth::user()->hasRole('Employee'))
                            <form id="devisForm" method="POST" action="{{route('employee.notedefrais.saveNoteDeFraisPdf')}}" autocomplete="off">
                            @endif
                            @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                            <form id="devisForm" method="POST" action="{{route('Responsable_gestionnaire.notedefrais.saveNoteDeFraisPdf')}}" autocomplete="off">
                            @endif
                                @csrf
                                <div class="row">
                                    <div class="col-4">
                                        <label for="projectName" class="form-label">Employee Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="projectName" name='employee_name' />
                                    </div>
                                    <div class="col-4"> 
                                        <label for="projectName" class="form-label">Total Budget</label>
                                        <input type="number" step="0.0001" class="form-control" id="projectBudget" name='budget' readonly />
                                    </div>
                                    
                                </div>

                                <label class="mt-4">Description <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="projectDescription" name='description' />

                                <div id="dynamicInput" class="mt-4">
                                    <div class="row mt-4">
                                        <div class="col-4">
                                            <label for="task" class="form-label">Details <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="task[]" />
                                        </div>
                                        <div class="col-4">
                                            <label for="task" class="form-label">Quantity <span class="text-danger">*</span></label>
                                            <input type="number" step="0.0001" class="form-control taskQuantity" name="quantity[]" />
                                        </div>
                                        <div class="col-4">
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
                                        Generate Expense Note
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
        '<div class="row mt-4"><div class="col-4"><input type="text" class="form-control" name="task[]" /></div><div class="col-4"><input type="number" step="0.0001" class="form-control taskQuantity" name="quantity[]" /></div><div class="col-4"><input type="number" step="0.0001" class="form-control taskBudget" name="taskBudget[]" /></div></div>';
    document.getElementById(divName).appendChild(newdiv);

    // Add event listener to the new inputs
    const newTaskBudgetInput = newdiv.querySelector('.taskBudget');
    const newTaskQuantityInput = newdiv.querySelector('.taskQuantity');
    newTaskBudgetInput.addEventListener('input', updateProjectBudget);
    newTaskQuantityInput.addEventListener('input', updateProjectBudget);
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
    document.querySelectorAll('.taskBudget').forEach(function(budgetInput, index) {
        let budgetValue = parseFloat(budgetInput.value);
        let quantityValue = parseFloat(document.querySelectorAll('.taskQuantity')[index].value);
        if (!isNaN(budgetValue) && !isNaN(quantityValue)) {
            totalBudget += budgetValue * quantityValue;
        }
    });
    document.getElementById('projectBudget').value = totalBudget.toFixed(2);
}

// Add event listeners to all task budget and quantity inputs
document.querySelectorAll('.taskBudget, .taskQuantity').forEach(function(input) {
    input.addEventListener('input', updateProjectBudget);
});
</script>

</body>

</html>