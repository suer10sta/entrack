<!DOCTYPE html>
<html lang="en">

@include('dashboard_layouts.head')

<body class="g-sidenav-show bg-gray-100">
  <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-NKDMSK6" height="0" width="0"
      style="display: none; visibility: hidden"></iframe></noscript>
  <div class="min-height-300 bg-primary position-absolute w-100"></div>

  @include('superadmin.sidebar')

  <main class="main-content position-relative border-radius-lg">
    <nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl z-index-sticky"
      id="navbarBlur" data-scroll="false">
      <div class="container-fluid py-1 px-3">

        @include('dashboard_layouts.navbar')

      </div>

      <div class="container-fluid py-4">
        @include('superadmin.navinfo')

          <div class="row mt-3">
            <div class="col-12">
              <div class="card">
              <div class="card-header p-3 pb-0">
                <h6>Users table</h6>
              </div>
                <div class="table-responsive p-4 pt-1">
                  <table id="example" class="table table-flush" style="width:100%">
                    <thead class="thead-light">
                      <tr class="text-center">
                        <th >Name</th>
                        <th class="text-center text-uppercase text-secondary text-sm font-weight-bolder opacity-10">Email</th>
                        <th class="text-center text-uppercase text-secondary text-sm font-weight-bolder opacity-10">Role</th>
                        <th class="text-center text-uppercase text-secondary text-sm font-weight-bolder opacity-10">Entreprise</th>
                        <th class="text-center text-uppercase text-secondary text-sm font-weight-bolder opacity-10">Action</th>
                      </tr>
                    </thead>
                    <tbody>
                    @foreach($users as $user)
                      <tr class="text-center">
                        <td>{{$user->name}}</td>
                        <td>{{$user->email}}</td>
                        <td>
                        @if($user->roles->count() > 0)
                            @foreach($user->roles as $role)
                                <span class="badge bg-gradient-success">{{$role->name}}</span>
                            @endforeach
                        @else
                            <span class="badge bg-gradient-danger">No roles</span>
                        @endif
                            
                        </td>
                        <td>
                            @if(isset($user->entreprise_name))
                                {{$user->entreprise_name}}
                            @else
                                <span class="text-danger" >Don't have entreprise</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{route('superadmin.users.edit', $user->id)}}" class="btn btn-primary btn-sm">Edit</a>
                            <form action="{{route('superadmin.users.destroy',$user->id) }}" method="post" onsubmit="return confirm('Are you sure?');" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </td>
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
