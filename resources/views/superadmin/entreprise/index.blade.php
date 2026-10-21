<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

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
                <h6>{{ __('messages.Entreprises Table') }}</h6>
              </div>
                <div class="table-responsive p-4 pt-1">
                  <table id="example" class="table table-flush" style="width:100%">
                    <thead class="thead-light">
                      <tr class="text-center">
                        <th class="text-center text-uppercase text-secondary text-sm font-weight-bolder opacity-10"> {{ __('messages.Name') }}</th>
                        <th class="text-center text-uppercase text-secondary text-sm font-weight-bolder opacity-10"> {{ __('messages.Status') }}</th>
                        <th class="text-center text-uppercase text-secondary text-sm font-weight-bolder opacity-10"> {{ __('messages.Duration') }}</th>
                        <th class="text-center text-uppercase text-secondary text-sm font-weight-bolder opacity-10"> {{ __('messages.Action column') }}</th>
                      </tr>
                    </thead>
                    <tbody>
                    @foreach($entreprises as $entreprise)
                      <tr class="text-center">
                        <td>
                        <img class="avatar avatar-sm rounded-circle me-2" 
                          src="{{ Storage::disk('public')->url('entreprise_images/' . $entreprise->image) }}" 
                          alt="Entreprise Image">                            
                          {{$entreprise->name}}
                        </td>
                        <td> 
                            @if($entreprise->is_activated == 1)
                                <span class="badge badge-success badge-sm">{{ __('messages.Active') }}</span>
                            @else
                                <span class="badge badge-danger badge-sm">{{ __('messages.Deactive') }}</span>
                            @endif
                        </td>
                        <td>{{$entreprise->duration}}</td>
                        <td>
                            @if($entreprise->is_activated == 1)
                            <form action="{{route('superadmin.entreprise.disactivate', $entreprise->id) }}" method="post" onsubmit="return confirm(@js(__('messages.Are you sure?')));" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-danger btn-sm">{{ __('messages.Deactivate') }}</button>
                            </form>
                            @else
                            <form action="{{route('superadmin.entreprise.activate', $entreprise->id) }}" method="post" onsubmit="return confirm(@js(__('messages.Are you sure?')));" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-success btn-sm">{{ __('messages.Activate') }}</button>
                            </form>
                            @endif
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
