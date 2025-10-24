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
                            <h6 class="mb-0">New Team</h6>
                            <p class="text-sm mb-0">Edit Team</p>
                            <hr class="horizontal dark my-3" />
                            @if(Auth::user()->hasRole('Admin'))
                            <form method="POST" action="{{route('admin.teams.update', $team->id)}}" autocomplete="off">
                            @endif
                            @if(Auth::user()->hasRole('Responsable de projet'))
                            <form method="POST" action="{{route('Responsable_de_projet.teams.update', $team->id)}}" autocomplete="off">
                            @endif
                            @if(Auth::user()->hasRole('Employee'))
                            <form method="POST" action="{{route('employee.teams.update', $team->id)}}" autocomplete="off">
                            @endif
                            @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                            <form method="POST" action="{{route('Responsable_gestionnaire.teams.update', $team->id)}}" autocomplete="off">
                            @endif
                                @csrf
                                @method('PUT')
                                <label for="teamName" class="form-label">Team Name</label>
                                <input type="text" class="form-control" id="teamName" name='name' value="{{$team->name}}" />

                                <label class="form-label" for="team-for-project">Team Members:</label>
                                <!-- Hidden input field to store the selected project value -->
                                @foreach($employee_users as $employee)
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="members[]" id="employee{{$employee->id}}" value="{{$employee->name}}">
                                        <label class="form-check-label" for="employee{{$employee->id}}">
                                            {{$employee->name}}
                                        </label>
                                    </div>
                                @endforeach
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
                                        Save
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

    <!-- Your JavaScript code to initialize Choices with removeItemButton -->
<script>
  if (document.getElementById("choices-multiple-remove-button")) {
    var element = document.getElementById("choices-multiple-remove-button");

    const choicesInstance = new Choices(element, {
      removeItemButton: true,
    });

    // Optionally, you can set choices dynamically
    choicesInstance.setChoices(
      [
        { value: "One", label: "Label One", disabled: true },
        { value: "Two", label: "Label Two", selected: true },
        { value: "Three", label: "Label Three" },
      ],
      "value",
      "label",
      false
    );
  }
</script>

</body>

</html>