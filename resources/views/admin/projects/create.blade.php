<!DOCTYPE html>
<html lang="en">

@include('dashboard_layouts.head')

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
                            <h6 class="mb-0">New project</h6>
                            <p class="text-sm mb-0">Create new project</p>
                            @if(session('message'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('message') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close">X</button>
                            </div>
                            @endif
                            <hr class="horizontal dark my-3" />
                            @if(Auth::user()->hasRole('Admin'))
                            <form method="POST" action="{{route('admin.projects.store', $client_id)}}" autocomplete="off">
                            @endif
                            @if(Auth::user()->hasRole('Responsable de projet'))
                            <form method="POST" action="{{route('Responsable_de_projet.projects.store', $client_id)}}" autocomplete="off">
                            @endif
                            @if(Auth::user()->hasRole('Employee'))
                            <form method="POST" action="{{route('employee.projects.store', $client_id)}}" autocomplete="off">
                            @endif
                            @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                            <form method="POST" action="{{route('Responsable_gestionnaire.projects.store', $client_id)}}" autocomplete="off">
                            @endif
                                @csrf
                                <div class="row">
                                    <div class="col-4">
                                        <label for="projectName" class="form-label">project Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="projectName" name='name' />
                                    </div>
                                    <div class="col-2">
                                        <label for="projectName" class="form-label">project Budget</label>
                                        <input type="number" step="0.0001" class="form-control" id="projectName" name='budget' />
                                    </div>
                                    <div class="col-2">
                                        <label for="projectName" class="form-label">Cost per Hour <span class="text-danger">*</span></label>
                                        <input type="number" step="0.0001" class="form-control" id="projectName"
                                            name='cost_per_hour' />
                                    </div>
                                    <div class="col-2">
                                        <label for="projectName" class="form-label">Number of estimated hours </label>
                                        <input type="number" step="0.0001" class="form-control" id="projectName"
                                            name='estimated_hours' />
                                    </div>
                                    <div class="col-2">
                                        <label for="projectName" class="form-label">Tva % <span class="text-danger">*</span></label>
                                        <input type="number" step="0.0001" class="form-control" id="projectName"
                                            name='tva' />
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-12">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="regie" name="regie" value="1">
                                            <label class="form-check-label" for="regie">
                                                Regie
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <label class="mt-4">Informations</label>
                                <textarea class="form-control" id="projectDescription" name="description"></textarea>                                
                                <br>
                                <div class="row">
                                    <div class="col-4">
                                        <label class="form-label" for="project-for-project">Teams :</label>
                                        @foreach($teams as $team)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="teams[]"
                                                id="team{{$team->id}}" value="{{$team->id}}">
                                            <label class="form-check-label" for="team{{$team->id}}">
                                                {{$team->name}}
                                            </label>
                                        </div>
                                        @endforeach
                                    </div>

                                    <div class="col-4">
                                        <label class="form-label" for="project-for-project">Contacts : <span class="text-danger">*</span></label>
                                        @php 
                                        $client_contacts = App\Models\client_contacts::where('client_id', $client_id)
                                            ->whereNotNull('name')
                                            ->get();
                                        @endphp
                                        <div class="col-4" id="contactsDiv">
                                            @foreach($client_contacts as $contact)
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="contacts[]" id="contact{{ $contact->id }}" value="{{ $contact->id }}">
                                                <label class="form-check-label" for="contact{{ $contact->id }}">
                                                    {{ $contact->name }}
                                                </label>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end mt-4">
                                @if(Auth::user()->hasRole('Admin'))
                                <a class='btn btn-light m-0' href="{{route('admin.index')}}">
                                        Cancel
                                    </a>
                                @endif
                                @if(Auth::user()->hasRole('Responsable de projet'))
                                <a class='btn btn-light m-0' href="{{route('Responsable_de_projet.index')}}">
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
                                        Create project
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
    document.addEventListener('DOMContentLoaded', function() {
        const dropdownButton = document.getElementById('ClientDropdown');
        const selectedProjectInput = document.getElementById('selectedClient');

        document.querySelectorAll('.clients_dropdown').forEach(item => {
            item.addEventListener('click', function(event) {
                event.preventDefault();

                const selectedClient = event.target.getAttribute('data-value');

                // Update the button text
                dropdownButton.textContent = selectedClient;

                // Update the hidden input value
                selectedProjectInput.value = selectedClient;
            });
        });
    });
    </script>

    

    <script>
        document.querySelectorAll('.clients_dropdown').forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault(); // Prevent default anchor action
                var clientId = this.getAttribute('data-id');
                document.getElementById('selectedClientId').value = clientId;
        
                // Optional: Update the dropdown button text to show the selected client's name
                document.getElementById('ClientDropdown').textContent = this.getAttribute('data-value');
            });
        });

        // Handle regie checkbox and project name
        document.getElementById('regie').addEventListener('change', function() {
            const projectNameInput = document.getElementById('projectName');
            const currentName = projectNameInput.value;
            
            if (this.checked) {
                // Add - REGIE if not already present
                if (!currentName.includes(' - REGIE')) {
                    projectNameInput.value = currentName + ' - REGIE';
                }
            } else {
                // Remove - REGIE if present
                projectNameInput.value = currentName.replace(' - REGIE', '');
            }
        });
    </script>

</body>

</html>