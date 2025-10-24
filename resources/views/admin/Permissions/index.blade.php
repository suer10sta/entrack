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
.text-sm{
    color: #332f2f;
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
      @include('admin.navinfos')

          <div class="row mt-3">
            <div class="col-12">
              <div class="card">
              <div class="card-header p-3 pb-0">
                <h6>permissions table</h6>
                @if(session('message'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('message') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">X</button>
                    </div>
                  @endif
              </div>
                <div class="table-responsive p-4 pt-1">
                  <!-- Add new Permission Button 
                  <div class="d-flex justify-content-end mb-2">
                      @if(Auth::user()->hasRole('Admin'))
                      <a type="button" class="btn btn-primary" href="{{route('admin.permissions.create')}}">Add New Permission</a>
                      @endif
                      @if(Auth::user()->hasRole('Responsable de projet'))
                      <a type="button" class="btn btn-primary" href="{{route('Responsable_de_projet.permissions.create')}}">Add New Permission</a>
                      @endif
                      @if(Auth::user()->hasRole('Employee'))
                      <a type="button" class="btn btn-primary" href="{{route('employee.permissions.create')}}">Add New Permission</a>
                      @endif
                      @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                      <a type="button" class="btn btn-primary" href="{{route('Responsable_gestionnaire.permissions.create')}}">Add New Permission</a>
                      @endif
                  </div>
                  -->
                  <table id="example" class="table table-flush" style="width:100%">
                    <thead class="thead-light">
                      <tr class="text-center">
                        <th class="text-center text-uppercase text-secondary text-sm font-weight-bolder opacity-10">permission</th>
                      </tr>
                    </thead>
                    <tbody>
                    @foreach($permissions as $permission)
                      <tr class="text-center">
                        <td class="text-sm">{{$permission->name}}</td>
                        <!-- Edit Button 
                        <td>
                            @if(Auth::user()->hasRole('Admin'))
                            <a href="{{route('admin.permissions.edit', $permission->id)}}" class="btn btn-primary btn-sm">Edit</a>
                            @endif
                            @if(Auth::user()->hasRole('Responsable de projet'))
                            <a href="{{route('Responsable_de_projet.permissions.edit', $permission->id)}}" class="btn btn-primary btn-sm">Edit</a>
                            @endif
                            @if(Auth::user()->hasRole('Employee'))
                            <a href="{{route('employee.permissions.edit', $permission->id)}}" class="btn btn-primary btn-sm">Edit</a>
                            @endif
                            @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                            <a href="{{route('Responsable_gestionnaire.permissions.edit', $permission->id)}}" class="btn btn-primary btn-sm">Edit</a>
                            @endif
-->
                            <!-- Delete Button 
                            @if(Auth::user()->hasRole('Admin'))
                            <form action="{{route('admin.permissions.destroy',$permission->id) }}" method="post" onsubmit="return confirm('Are you sure?');" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                            </form>
                            @endif
                            @if(Auth::user()->hasRole('Responsable de projet'))
                            <form action="{{route('Responsable_de_projet.permissions.destroy',$permission->id) }}" method="post" onsubmit="return confirm('Are you sure?');" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                            </form>
                            @endif
                            @if(Auth::user()->hasRole('Employee'))
                            <form action="{{route('employee.permissions.destroy',$permission->id) }}" method="post" onsubmit="return confirm('Are you sure?');" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                            </form>
                            @endif
                            @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                            <form action="{{route('Responsable_gestionnaire.permissions.destroy',$permission->id) }}" method="post" onsubmit="return confirm('Are you sure?');" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                            </form>
                            @endif
                        </td>
                        -->
                    @endforeach
                    </tbody>

                  </table>
                </div>

              </div>
            </div>
          </div>


        
  </main>

  
  @include('dashboard_layouts.scripts')
  

</body>

</html>