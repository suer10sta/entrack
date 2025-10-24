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


                <div class="container-fluid py-4">
                    <div class="row mb-5">
                        <div class="col-12">
                            <div class="multisteps-form mb-5">
                                <div class="row">
                                    <div class="col-12 col-lg-8 mx-auto my-4">
                                        <div class="card">
                                            <div class="card-body text-center">
                                                <span>Role Info</span>
                                            </div>    
                                        </div>
                                    </div>
                                </div> 
                                
<br><br><br><br>
                                <div class="row">
                                    <div class="col-12 col-lg-8 m-auto">
                                        <h5 class="font-weight-bolder mb-0">Role Infos</h5>
                                        <p class="mb-0 text-sm">Mandatory informations</p>
                                        
                                            <div class="row mt-3">
                                                <div class="col-12 col-sm-6">
                                                    <label>Name : </label>
                                                    <input class="multisteps-form__input form-control" type="text"
                                                        name="name" value="{{$role->name}}" readonly />
                                                </div>
                                            </div>
                                            
                                        
                                    </div>
                                    @if(Auth::user()->hasRole('Admin'))
                                    <form class="multisteps-form__form mb-8" method="POST"
                                        action="{{route('admin.roles.assign_permission',$role->id)}}">
                                    @endif
                                    @if(Auth::user()->hasRole('Responsable de projet'))
                                    <form class="multisteps-form__form mb-8" method="POST"
                                        action="{{route('Responsable_de_projet.roles.assign_permission',$role->id)}}">
                                    @endif
                                    @if(Auth::user()->hasRole('Employee'))
                                    <form class="multisteps-form__form mb-8" method="POST"
                                        action="{{route('employee.roles.assign_permission',$role->id)}}">
                                    @endif
                                    @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                    <form class="multisteps-form__form mb-8" method="POST"
                                        action="{{route('Responsable_gestionnaire.roles.assign_permission',$role->id)}}">
                                    @endif
                                        @csrf
                                        <div class="row">
                                            <div class="col-12 col-md-6"> 
                                                <h5 class="font-weight-bolder">Assign Permissions :</h5>
                                                
                                                    <div class="row mt-3">
                                                    @foreach($permissions as $per)
                                                        @php
                                                            $hasPermission = \App\Models\EnterpriseRolePermission::where('entreprise_id', Auth::user()->entreprise_id)
                                                                ->where('role_id', $role->id)
                                                                ->where('permission_id', $per->id)
                                                                ->exists();
                                                        @endphp
                                                        
                                                        <div class="form-check">
                                                            <input id="vue-checkbox-{{ $per->id }}" 
                                                                name="permissions[]" 
                                                                type="checkbox" 
                                                                value="{{ $per->id }}"
                                                                {{ $hasPermission ? 'checked' : '' }}>
                                                            <label for="vue-checkbox-{{ $per->id }}" class="form-check-label">
                                                                {{ $per->name }}
                                                            </label>
                                                        </div>
                                                    @endforeach
                                                    </div>
                                                    <div class="row">
                                                        <div class="button-row d-flex mt-4 col-12">
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
                                                            <button class="btn bg-gradient-dark ms-auto mb-0 js-btn-next"
                                                                type="submit">
                                                                Assign
                                                            </button>
                                                        </div>
                                                    </div>
                                                
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            </div>

    </main>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script>
  window.addEventListener('load', function() {
    var inputs = document.getElementsByTagName('input');
    for (var i = 0; i < inputs.length; i++) {
      inputs[i].setAttribute('autocomplete', 'none');
    }
  });
</script>
    <script>
        $(document).ready(function () {
            $(".multisteps-form__progress-btn").click(function () {
                var index = $(this).index();
                $(".multisteps-form__panel").removeClass("js-active");
                $(".multisteps-form__panel").eq(index).addClass("js-active");
                $(".multisteps-form__progress-btn").removeClass("js-active");
                $(this).addClass("js-active");
            });
        });
    </script>
    @include('dashboard_layouts.scripts')

</body>

</html>