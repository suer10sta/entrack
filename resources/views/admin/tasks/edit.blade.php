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
                            <h6 class="mb-0">Update Task</h6>
                            <hr class="horizontal dark my-3" />
                            @if(Auth::user()->hasRole('Responsable de projet'))
                                <form method="POST" action="{{route('Responsable_de_projet.tasks.update',$task)}}" autocomplete="off" >
                            @endif
                            @if(Auth::user()->hasRole('Admin'))
                                <form method="POST" action="{{route('admin.tasks.update',$task)}}" autocomplete="off" >
                            @endif
                            @if(Auth::user()->hasRole('Employee'))
                                <form method="POST" action="{{route('employee.tasks.update',$task)}}" autocomplete="off" >
                            @endif
                            @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                <form method="POST" action="{{route('Responsable_gestionnaire.tasks.update',$task)}}" autocomplete="off" >
                            @endif
                                @csrf
                                @method('PUT')
                                <div class="row">
                                    <div class="col-4">
                                        <label for="TaskName" class="form-label">Task Name: <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="TaskName" name='title' value="{{$task->title}}"/>
                                    </div>
                                    <div class="col-4">
                                        <label for="estimated_hours" class="form-label">Estimated Hours: </label>
                                        <input type="text" class="form-control" id="estimated_hours" name='estimated_hours' value="{{$task->estimated_hours}}"/>
                                    </div>
                                </div>
                                <label class="mt-4">Task Description</label>
                                <input type="text" class="form-control" id="TaskDescription" name='description'  value="{{$task->description}}"/>
                                <br>
                                <div class="row">
                                    
                                    <div class="col-4">
                                    <label class="form-label" for="task-for-project">Employees : <span class="text-danger">*</span></label>
                                        @if($old_employee_users)
                                            @foreach($old_employee_users as $old_emp)
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="employees[]" id="employee{{$old_emp->id}}" value="{{$old_emp->id}}" checked>
                                                <label class="form-check-label" for="employee{{$old_emp->id}}">
                                                    {{$old_emp->name}}
                                                </label>
                                            </div>
                                            @endforeach
                                            @foreach($employee_users as $employee)
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="employees[]" id="employee{{$employee->id}}" value="{{$employee->id}}">
                                                    <label class="form-check-label" for="employee{{$employee->id}}">
                                                        {{$employee->name}}
                                                    </label>
                                                </div>
                                            @endforeach
                                        @else
                                            @foreach($employee_users as $employee)
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="employees[]" id="employee{{$employee->id}}" value="{{$employee->id}}">
                                                <label class="form-check-label" for="employee{{$employee->id}}">
                                                    {{$employee->name}}
                                                </label>
                                            </div>
                                            @endforeach
                                        @endif

                                        
                                    </div>
                                    <div class="col-4">
                                    <label class="form-label" for="task-for-project">Teams : </label>
                                        @foreach($teams as $team)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="teams[]" id="team{{$team->id}}" value="{{$team->id}}">
                                            <label class="form-check-label" for="team{{$team->id}}">
                                                {{$team->name}}
                                            </label>
                                        </div>
                                        @endforeach
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
    document.addEventListener('DOMContentLoaded', function () {
        const dropdownButton = document.getElementById('taskDropdown');
        const selectedProjectInput = document.getElementById('selectedProject');

        document.querySelectorAll('.dropdown-item').forEach(item => {
            item.addEventListener('click', function (event) {
                event.preventDefault();

                const selectedProject = event.target.getAttribute('data-value');

                // Update the button text
                dropdownButton.textContent = selectedProject;

                // Update the hidden input value
                selectedProjectInput.value = selectedProject;
            });
        });
    });
</script>


</body>

</html>