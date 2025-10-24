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
                <h6>permissions table</h6>
              </div>
                <div class="table-responsive p-4 pt-1">
                  <table id="example" class="table table-flush" style="width:100%">
                    <thead class="thead-light">
                      <tr class="text-center">
                        <th class="text-center text-uppercase text-secondary text-sm font-weight-bolder opacity-10">permission</th>
                        <th class="text-center text-uppercase text-secondary text-sm font-weight-bolder opacity-10">Action</th>
                      </tr>
                    </thead>
                    <tbody>
                    
                    @forelse($permissions as $permission)
                      <tr class="text-center">
                        <td>{{$permission->name}}</td>
                        <td>
                          <a href="{{route('superadmin.permissions.edit', $permission->id)}}" class="btn btn-primary btn-sm">Edit</a>
                          <form action="{{route('superadmin.permissions.destroy',$permission->id) }}" method="post" onsubmit="return confirm('Are you sure?');" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                          </form>
                        </td>
                      </tr>
                    @empty
                      <tr>
                        <td colspan="2" class="text-center">No permissions found</td>
                      </tr>
                    @endforelse
                    
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