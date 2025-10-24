<!DOCTYPE html>
<html lang="en">

@include('dashboard_layouts.head')
<style>
  a {
    color: blue;
}
.font-weight-bolder{
    color : black !important;
}
th {
    font-size: 14px !important; 
}
td, td * {
    color: #332f2f !important;
}
td a {
    color: blue !important;
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

          <div class="row mt-3">
            <div class="col-12">
              <div class="card">
              <div class="card-header p-3 pb-0">
              <div class="d-flex justify-content-between">
                <h6>Employees Table</h6>
                <div>
                  @if(Auth::user()->hasRole('Responsable de projet'))
                    <a type="button" class="btn btn-primary" href="{{route('Responsable_de_projet.Employeesrepports.downloadRepports')}}">Employees Reports PDF</a>
                  @endif
                  @if(Auth::user()->hasRole('Admin'))
                    <a type="button" class="btn btn-primary" href="{{route('admin.Employeesrepports.downloadRepports')}}">Employees Reports PDF</a>
                  @endif
                  @if(Auth::user()->hasRole('Employee'))
                    <a type="button" class="btn btn-primary" href="{{route('employee.Employeesrepports.downloadRepports')}}">Employees Reports PDF</a>
                  @endif
                  @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                    <a type="button" class="btn btn-primary" href="{{route('Responsable_gestionnaire.Employeesrepports.downloadRepports')}}">Employees Reports PDF</a>
                  @endif
                  @can('Add teams')
                  <a type="button" class="btn btn-primary" href="{{route('admin.teams.create')}}">Add Team</a>
                  @endcan
                  @can('Add employee')
                  <a type="button" class="btn btn-primary me-2" href="{{route('admin.employees.create')}}">Add Employee</a>
                  @endcan
                  <a type="button" class="btn btn-primary me-2" href="{{route('admin.employeesArchived.archiveIndex')}}">View Archive</a>

                </div>
              </div>
                @if(session('message'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('message') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">X</button>
                    </div>
                  @endif
              </div>
                <div class="table-responsive p-4 pt-1">
                  <table id="example" class="table table-flush" style="width:100%">
                    <thead class="thead-light">
                      <tr class="text-center">
                        <th class="text-center text-uppercase text-secondary text-x font-weight-bolder opacity-10"></th>
                        <th class="text-center text-uppercase text-secondary text-x font-weight-bolder opacity-10"> Name</th> 
                        <th class="text-center text-uppercase text-secondary text-x font-weight-bolder opacity-10"> Login email</th>
                        <th class="text-center text-uppercase text-secondary text-x font-weight-bolder opacity-10"> phoneNumber</th>
                        <th class="text-center text-uppercase text-secondary text-x font-weight-bolder opacity-10"> Status</th>
                        <th class="text-center text-uppercase text-secondary text-x font-weight-bolder opacity-10"> age</th>
                        <th class="text-center text-uppercase text-secondary text-x font-weight-bolder opacity-10"> cnss</th>
                        <th class="text-center text-uppercase text-secondary text-x font-weight-bolder opacity-10"> bankAccountNumber</th>
                        <th class="text-center text-uppercase text-secondary text-x font-weight-bolder opacity-10"> Salary</th>
                        <th class="text-center text-uppercase text-secondary text-x font-weight-bolder opacity-10"> Cost_per_hour</th>
                        <th class="text-center text-uppercase text-secondary text-x font-weight-bolder opacity-10"> workHours_limit</th>
                        <th class="text-center text-uppercase text-secondary text-x font-weight-bolder opacity-10"> Cotisation CNSS</th>
                        <th class="text-center text-uppercase text-secondary text-x font-weight-bolder opacity-10"> Cotisation AMO</th>
                        <th class="text-center text-uppercase text-secondary text-x font-weight-bolder opacity-10"> Date_Release</th>
                        <th class="text-center text-uppercase text-secondary text-x font-weight-bolder opacity-10"> Role</th>
                        <th class="text-center text-uppercase text-secondary text-x font-weight-bolder opacity-10"> Action</th>
                      </tr>
                    </thead>
                    <tbody>
                    @foreach($employees as $employee)
                      <tr class="text-center">
                        <td>

                        @php
                            $colors = ['#2ECC71', '#BDC3C7', '#F39C12', '#E74C3C', '#9B59B6',
                            '#2E86C1','#641E16','#2C3E50','#F4D03F','#F4D03F'];
                          @endphp
                          @if($employee->image)
                          <img class="avatar avatar-sm rounded-circle me-2" 
                            src="{{ Storage::disk('do_spaces')->url('employee_images/' . $employee->image) }}" 
                            alt="employee Image">
                          @else
                          <div class="avatar avatar-sm rounded-circle me-2"
                            style="background-color: {{ $colors[$loop->index % count($colors)] }};">
                            <span class="initial-letter" style="color: white !important;">{{ strtoupper(substr($employee->name,
                                0, 1)) }}</span>
                          </div>
                          @endif
                        </td>
                        <td class="editable" data-id="{{$employee->id}}" data-column="name">

                        @if(Auth::user()->hasRole('Responsable de projet'))
                          @if(Auth::user()->can('View employee'))
                            <a href="{{route('Responsable_de_projet.employees.show',$employee->id )}}">
                              {{$employee->name}}
                            </a>
                            @else
                            {{$employee->name}}
                          @endif
                        @endif
                        @if(Auth::user()->hasRole('Admin'))
                          @if(Auth::user()->can('View employee'))
                            <a href="{{route('admin.employees.show',$employee->id )}}">
                              {{$employee->name}}
                            </a>
                            @else
                            {{$employee->name}}
                          @endif
                        @endif
                        @if(Auth::user()->hasRole('Employee'))
                          @if(Auth::user()->can('View employee'))
                            <a href="{{route('employee.employees.show',$employee->id )}}">
                              {{$employee->name}}
                            </a>
                          @else
                            {{$employee->name}}
                          @endif
                        @endif
                        @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                          @if(Auth::user()->can('View employee'))
                            <a href="{{route('Responsable_gestionnaire.employees.show',$employee->id )}}">
                              {{$employee->name}}
                            </a>
                          @else
                            {{$employee->name}}
                          @endif
                        @endif
                            
                        </td>

                        <td class="editable" data-id="{{$employee->id}}" data-column="email">

                            {{$employee->email}}
                        </td>

                        <td class="editable" data-id="{{$employee->id}}" data-column="phoneNumber">

                            {{$employee->phoneNumber}}
                        </td>

                        <td >

                            @if(\App\Models\User::find($employee->user_id) && \App\Models\User::find($employee->user_id)->is_activate==1)
                            @if(Auth::user()->hasRole('Responsable de projet'))
                            <form action="{{route('Responsable_de_projet.employee.deactivate', $employee->id) }}"
                                  method="post" onsubmit="return confirm('Are you sure?');">
                                @csrf
                                <span class="badge badge-sm badge-success">
                                  <button type="submit" style="border: none; background: none; padding: 0; color: inherit;">
                                    Active
                                  </button>
                                </span>
                              </form>
                            @endif
                            @if(Auth::user()->hasRole('Admin'))
                              <form action="{{route('admin.employee.deactivate', $employee->id) }}"
                                  method="post" onsubmit="return confirm('Are you sure?');">
                                @csrf
                                <span class="badge badge-sm badge-success">
                                  <button type="submit" style="border: none; background: none; padding: 0; color: inherit;">
                                    Active
                                  </button>
                                </span>
                              </form>
                            @endif
                            @if(Auth::user()->hasRole('Employee'))
                              <form action="{{route('employee.employee.activate', $employee->id) }}"
                                  method="post" onsubmit="return confirm('Are you sure?');">
                                @csrf
                                <span class="badge badge-sm badge-success">
                                  <button type="submit" style="border: none; background: none; padding: 0; color: inherit;">
                                    Active
                                  </button>
                                </span>
                              </form> 
                            @endif
                            @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                              <form action="{{route('Responsable_gestionnaire.employee.activate', $employee->id) }}"
                                  method="post" onsubmit="return confirm('Are you sure?');">
                                @csrf
                                <span class="badge badge-sm badge-success">
                                  <button type="submit" style="border: none; background: none; padding: 0; color: inherit;">
                                    Active
                                  </button>
                                </span>
                              </form>
                            @endif
                            @else
                              @if(Auth::user()->hasRole('Admin'))
                                <form action="{{route('admin.employee.activate', $employee->id) }}"
                                    method="post" onsubmit="return confirm('Are you sure?');">
                                  @csrf
                                  <span class="badge badge-sm badge-danger">
                                    <button type="submit" style="border: none; background: none; padding: 0; color: inherit;">
                                      Inactive
                                    </button>
                                  </span>
                                </form>
                              @endif
                              @if(Auth::user()->hasRole('Responsable de projet'))
                                <form action="{{route('Responsable_de_projet.employee.activate', $employee->id) }}"
                                    method="post" onsubmit="return confirm('Are you sure?');">
                                  @csrf
                                  <span class="badge badge-sm badge-danger">
                                    <button type="submit" style="border: none; background: none; padding: 0; color: inherit;">
                                      Inactive
                                    </button>
                                  </span>
                                </form>
                              @endif    
                              @if(Auth::user()->hasRole('Employee'))
                                <form action="{{route('employee.employee.activate', $employee->id) }}"
                                    method="post" onsubmit="return confirm('Are you sure?');">
                                  @csrf
                                  <span class="badge badge-sm badge-danger">
                                    <button type="submit" style="border: none; background: none; padding: 0; color: inherit;">
                                      Inactive
                                    </button>
                                  </span>
                                </form>
                              @endif
                              @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                <form action="{{route('Responsable_gestionnaire.employee.activate', $employee->id) }}"
                                    method="post" onsubmit="return confirm('Are you sure?');">
                                  @csrf
                                  <span class="badge badge-sm badge-danger">
                                    <button type="submit" style="border: none; background: none; padding: 0; color: inherit;">
                                      Inactive
                                    </button>
                                  </span>
                                </form>
                              @endif
                            @endif  
                        </td>

                        <td class="editable" data-id="{{$employee->id}}" data-column="age">

                            {{$employee->age}}
                        </td>

                        <td class="editable" data-id="{{$employee->id}}" data-column="cnss">

                            {{$employee->cnss}}
                        </td>

                        <td class="editable" data-id="{{$employee->id}}" data-column="bankAccountNumber">

                            {{$employee->bankAccountNumber}}
                        </td>

                        <td class="text-success editable" data-id="{{$employee->id}}" data-column="salary">
                            {{$employee->salary}} €
                        </td>

                        <td class="text-success editable" data-id="{{$employee->id}}" data-column="cost_per_hour">
                            {{number_format($employee->cost_per_hour, 2, '.', '')}} €
                        </td>
                        
                        <td class="editable" data-id="{{$employee->id}}" data-column="workHours_limit">
                            {{$employee->workHours_limit}} h
                        </td>

                        <td class="text-success editable" data-id="{{$employee->id}}" data-column="Cotisation_CNSS">
                            {{$employee->Cotisation_CNSS}} €
                        </td>

                        <td class="text-success editable" data-id="{{$employee->id}}" data-column="Cotisation_AMO">
                            {{$employee->Cotisation_AMO}} €
                        </td>

                        <td class="editable" data-id="{{$employee->id}}" data-column="Date_Release">

                            {{$employee->Date_Release}}
                        </td>

                        <td >

                          @if(count($employee->roles)!=0)
                          @foreach($employee->roles as $role)
                          <span class="badge bg-gradient-success" style="color: white !important;">{{$role}}</span>
                          @endforeach
                          @else
                          <span class="badge bg-gradient-danger" style="color: white !important;">No Role</span>
                          @endif
                        </td>

                        <td>
                        @if(Auth::user()->hasRole('Admin'))
                          @can('Edit employee roles')
                            <a href="{{route('admin.employees.edit', $employee->id)}}" class="btn btn-primary btn-sm" style="color: white !important;">Edit Roles</a>
                          @endcan
                          <form
                              action="{{ route('admin.employees.archive', $employee->id)}}"
                              method="post"
                              onsubmit="return confirm('Are you sure?');"
                              class="d-inline">
                              @csrf
                              <button type="submit"
                                  class="btn btn-secondary btn-xs p-2 mb-3" style="color: white !important;">archive</button>
                          </form>
                          @can('Delete employee')
                            @if(\App\Models\User::find($employee->user_id)->id != auth()->user()->id)
                            <form action="{{route('admin.employees.destroy',$employee->id) }}" method="post" onsubmit="return confirm('Are you sure?');" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" style="color: white !important;">Delete</button>
                            </form>
                            @endif
                          @endcan
                        @endif
                        @if(Auth::user()->hasRole('Responsable de projet'))
                          @can('Edit employee roles')
                            <a href="{{route('Responsable_de_projet.employees.edit', $employee->id)}}" class="btn btn-primary btn-sm" style="color: white !important;">Edit Roles</a>
                          @endcan
                          <form
                              action="{{ route('Responsable_de_projet.employees.archive', $employee->id)}}"
                              method="post"
                              onsubmit="return confirm('Are you sure?');"
                              class="d-inline">
                              @csrf
                              <button type="submit"
                                  class="btn btn-secondary btn-xs p-2 mb-3" style="color: white !important;">archive</button>
                          </form>
                          @can('Delete employee')
                            @if(\App\Models\User::find($employee->user_id)->id != auth()->user()->id)
                            <form action="{{route('Responsable_de_projet.employees.destroy',$employee->id) }}" method="post" onsubmit="return confirm('Are you sure?');" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" style="color: white !important;">Delete</button>
                            </form>
                            @endif
                          @endcan
                        @endif 
                        @if(Auth::user()->hasRole('Employee'))
                          @can('Edit employee roles')
                            <a href="{{route('employee.employees.edit', $employee->id)}}" class="btn btn-primary btn-sm" style="color: white !important;">Edit Roles</a>
                          @endcan
                          <form
                              action="{{ route('employee.employees.archive', $employee->id)}}"
                              method="post"
                              onsubmit="return confirm('Are you sure?');"
                              class="d-inline">
                              @csrf
                              <button type="submit"
                                  class="btn btn-secondary btn-xs p-2 mb-3" style="color: white !important;">archive</button>
                          </form>
                          @can('Delete employee')
                            @if(\App\Models\User::find($employee->user_id)->id != auth()->user()->id)
                            <form action="{{route('employee.employees.destroy',$employee->id) }}" method="post" onsubmit="return confirm('Are you sure?');" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" style="color: white !important;">Delete</button>
                            </form>
                            @endif
                          @endcan
                        @endif  
                        @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                          @can('Edit employee roles')
                            <a href="{{route('Responsable_gestionnaire.employees.edit', $employee->id)}}" class="btn btn-primary btn-sm" style="color: white !important;">Edit Roles</a>
                          @endcan
                          <form
                              action="{{ route('Responsable_gestionnaire.employees.archive', $employee->id)}}"
                              method="post"
                              onsubmit="return confirm('Are you sure?');"
                              class="d-inline">
                              @csrf
                              <button type="submit"
                                  class="btn btn-secondary btn-xs p-2 mb-3" style="color: white !important;">archive</button>
                          </form>
                          @can('Delete employee')
                            @if(\App\Models\User::find($employee->user_id)->id != auth()->user()->id)
                            <form action="{{route('Responsable_gestionnaire.employees.destroy',$employee->id) }}" method="post" onsubmit="return confirm('Are you sure?');" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" style="color: white !important;">Delete</button>
                            </form>
                            @endif
                          @endcan
                        @endif  
                        </td>
                      </tr>
                    @endforeach
                    </tbody>

                  </table>
                </div>

              </div>
            </div>
          </div>


        
  </main>

  

  @include('dashboard_layouts.scripts')
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
  <script>
    @can('Update employee')
    $(document).ready(function(){
        // Use event delegation by attaching the event handler to a static parent
        $(document).on('dblclick', '.editable', function(){
            var originalContent = $(this).text().trim();
            var width = $(this).width();
            $(this).html("<input type='text' value='" + originalContent + "' style='width: 100%; min-width: " + width + "px;' />");
            var input = $(this).children().first();
            input.focus();

            // Place cursor at the beginning of the input
            input[0].setSelectionRange(0, 0);

            input.blur(function(){
                var newContent = $(this).val();
                var employeeId = $(this).parent().data('id');
                var columnName = $(this).parent().data('column');
                $(this).parent().text(newContent);
                $(this).parent().removeClass("cellEditing");

                // Make AJAX call to update the database here
                $.ajax({
                    url: 'employees/updateEmployee', // your update route
                    type: 'post',
                    data: {
                        content: newContent,
                        id: employeeId,
                        column: columnName,
                        _token: '{{ csrf_token() }}' // add CSRF token for Laravel
                    },
                    success: function(response) {
                        // Refresh the page after the update is successful
                        location.reload();
                    }
                });
            });
        });
    });
    @endcan
  </script>
  

</body>

</html>