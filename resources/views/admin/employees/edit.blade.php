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
                                            <div class="card-body">
                                                <div class="multisteps-form__progress">
                                                    <button class="multisteps-form__progress-btn js-active"
                                                        type="button" title="employee Info">
                                                        <span>Assign Role</span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-12 col-lg-8 m-auto">
                                    @if(Auth::user()->hasRole('Admin'))
                                        <form class="multisteps-form__form mb-8" method="POST"
                                            action="{{ route('admin.employees.update', $employee) }}">
                                    @endif
                                    @if(Auth::user()->hasRole('Responsable de projet'))
                                        <form class="multisteps-form__form mb-8" method="POST"
                                            action="{{ route('Responsable_de_projet.employees.update', $employee) }}">
                                    @endif
                                    @if(Auth::user()->hasRole('Employee'))
                                        <form class="multisteps-form__form mb-8" method="POST"
                                            action="{{ route('employee.employees.update', $employee) }}">
                                    @endif
                                    @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                        <form class="multisteps-form__form mb-8" method="POST"
                                            action="{{ route('Responsable_gestionnaire.employees.update', $employee) }}">
                                    @endif
                                            @csrf
                                            @method('PUT')

                                            <div class="card  p-3 border-radius-xl bg-white">
                                                <h5 class="font-weight-bolder">Assign Roles :</h5>
                                                <div class="multisteps-form__content">
                                                    <div class="row mt-3">
                                                        @foreach($role_name as $role)
                                                        <div class="form-check">
                                                        @if(Auth::user()->hasRole('SuperAdmin') || (!$user->hasRole('SuperAdmin') && $role != 'SuperAdmin'))
                                                            <input id="vue-checkbox-{{ $role }}" name="roles[]"
                                                                type="checkbox" value="{{ $role }}"
                                                                @if($user->hasRole($role)) checked @endif>
                                                            <label for="vue-checkbox-{{ $role }}" class="form-check-label">
                                                                {{ $role }}
                                                            </label>
                                                        @endif

                                                        </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                                
                                                <h5 class="font-weight-bolder mt-4">Timezone Settings :</h5>
                                                <div class="multisteps-form__content">
                                                    <div class="row mt-3">
                                                        <div class="col-12">
                                                            <label>Timezone</label>
                                                            <select class="multisteps-form__input form-control" name="timezone">
                                                                @foreach (timezone_identifiers_list() as $timezone)
                                                                    <option value="{{ $timezone }}" @if($user->timezone == $timezone) selected @endif>
                                                                        {{ $timezone }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="button-row d-flex mt-4 col-12">
                                                    @if(Auth::user()->hasRole('Responsable de projet'))
                                                        <a class='btn btn-light m-0' href="{{route('Responsable_de_projet.index')}}">
                                                            Cancel
                                                        </a>
                                                    @endif
                                                    @if(Auth::user()->hasRole('Admin'))
                                                    <a class='btn btn-light m-0' href="{{route('admin.index')}}">
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
                                                            Save
                                                        </button>
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
    
    @include('dashboard_layouts.scripts')

</body>

</html>