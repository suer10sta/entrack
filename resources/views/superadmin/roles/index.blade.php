<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

@include('dashboard_layouts.head')

<body class="g-sidenav-show bg-gray-100">
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
                <h6>{{ __('messages.Roles table') }}</h6>
              </div>
                <div class="table-responsive p-4 pt-1">
                  <table id="example" class="table table-flush" style="width:100%">
                    <thead class="thead-light">
                      <tr class="text-center">
                        <th class="text-center text-uppercase text-secondary text-sm font-weight-bolder opacity-10">{{ __('messages.Role') }}</th>
                        <th class="text-center text-uppercase text-secondary text-sm font-weight-bolder opacity-10">{{ __('messages.Permissions') }}</th>
                        <th class="text-center text-uppercase text-secondary text-sm font-weight-bolder opacity-10">{{ __('messages.Action column') }}</th>
                      </tr>
                    </thead>
                    <tbody>
                    @foreach($roles as $role)
                      <tr class="text-center">
                        @php
                          $roleRawName = trim($role->name);
                          $roleCandidates = array_values(array_unique([
                            $roleRawName,
                            strtolower($roleRawName),
                            strtoupper($roleRawName),
                            str_replace(['_', '-'], ' ', $roleRawName),
                            ucwords(strtolower(str_replace(['_', '-'], ' ', $roleRawName))),
                            str_replace(' ', '_', $roleRawName),
                            strtolower(str_replace(' ', '_', $roleRawName)),
                            strtoupper(str_replace(' ', '_', $roleRawName)),
                          ]));
                          $roleLabel = $roleRawName;
                          foreach ($roleCandidates as $candidate) {
                            $candidateKey = 'messages.' . $candidate;
                            $translated = __($candidateKey);
                            if ($translated !== $candidateKey) {
                              $roleLabel = $translated;
                              break;
                            }
                          }
                        @endphp
                        <td>{{ $roleLabel }}</td>
                        <td>
                          @if($role->permissions->count() > 0)
                            @foreach($role->permissions as $per)
                            @php
                              $permissionRawName = trim($per->name);
                              $permissionCandidates = array_values(array_unique([
                                $permissionRawName,
                                strtolower($permissionRawName),
                                strtoupper($permissionRawName),
                                str_replace(['_', '-'], ' ', $permissionRawName),
                                ucwords(strtolower(str_replace(['_', '-'], ' ', $permissionRawName))),
                                str_replace(' ', '_', $permissionRawName),
                                strtolower(str_replace(' ', '_', $permissionRawName)),
                                strtoupper(str_replace(' ', '_', $permissionRawName)),
                              ]));
                              $permissionLabel = $permissionRawName;
                              foreach ($permissionCandidates as $candidate) {
                                $candidateKey = 'messages.' . $candidate;
                                $translated = __($candidateKey);
                                if ($translated !== $candidateKey) {
                                  $permissionLabel = $translated;
                                  break;
                                }
                              }
                            @endphp
                            <span class="badge bg-gradient-success">{{ $permissionLabel }}</span>
                            @endforeach
                          @else
                            <span class="badge bg-gradient-danger">{{ __('messages.No permissions') }}</span>
                          @endif  
                        </td>
                        <td>
                            <a href="{{route('superadmin.roles.edit', $role->id)}}" class="btn btn-primary btn-sm">{{ __('messages.Edit') }}</a>
                            <form action="{{route('superadmin.roles.destroy',$role->id) }}" method="post" onsubmit="return confirm(@js(__('messages.Are you sure?')));" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">{{ __('messages.Delete') }}</button>
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
