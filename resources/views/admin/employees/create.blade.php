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
                                                <h4 class="font-weight-bolder mb-0">Add New Employee</h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-12 col-lg-12 m-auto">
                                    @if(Auth::user()->hasRole('Admin'))
                                        <form class=" mb-8" method="POST" action="{{route('admin.employees.store')}}" enctype="multipart/form-data" autocomplete="off">
                                    @endif
                                    @if(Auth::user()->hasRole('Responsable de projet'))
                                        <form class=" mb-8" method="POST" action="{{route('Responsable_de_projet.employees.store')}}" enctype="multipart/form-data" autocomplete="off">
                                    @endif
                                    @if(Auth::user()->hasRole('Employee'))
                                        <form class=" mb-8" method="POST" action="{{route('employee.employees.store')}}" enctype="multipart/form-data" autocomplete="off">
                                    @endif
                                    @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                        <form class=" mb-8" method="POST" action="{{route('Responsable_gestionnaire.employees.store')}}" enctype="multipart/form-data" autocomplete="off">
                                    @endif
                                            @csrf 
                                            <div class="row">
                                                <div class="col-12 col-md-4">   
                                                    <div class="card p-3 border-radius-xl bg-white js-active"
                                                        data-animation="FadeIn">
                                                        <h5 class="font-weight-bolder mb-0">Personal Infos</h5>
                                                        <p class="mb-0 text-sm">Mandatory informations</p>
                                                        <div class="multisteps-form__content ">
                                                            <div class="row mt-3">
                                                                <div class="col-12 col-sm-6">
                                                                    <label>Name : <span class="text-danger">*</span></label>
                                                                    <input class="multisteps-form__input form-control"
                                                                        type="text" placeholder="Full Name" name="name" />
                                                                </div>
                                                                <div class="col-12 col-sm-6 mt-3 mt-sm-0">
                                                                    <label>Login Email : <span class="text-danger">*</span></label>
                                                                    <input class="multisteps-form__input form-control"
                                                                        type="text" placeholder="*****@gmail.com" name="email"/>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-12 col-sm-6">
                                                                    <label>Age :</label>
                                                                    <input class="multisteps-form__input form-control"
                                                                        type="text" placeholder="Employee Age " name="age"/>
                                                                </div>
                                                                <div class="col-12 col-sm-6 mt-3 mt-sm-0">
                                                                    <label>Company Phone Number : </label>
                                                                    <input id="entreprise_image" name="Employee_Image" placeholder="Company Phone Number"
                                                                    class="multisteps-form__input form-control">
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-12 col-sm-6">
                                                                    <label>Personal Phone Number :</label>
                                                                    <input class="multisteps-form__input form-control"
                                                                        type="phoneNumber" placeholder="Personal Phone Number" name="phoneNumber"/>
                                                                </div>
                                                                <div class="col-12 col-sm-6 mt-3 mt-sm-0">
                                                                    <label>Contact Email : <span class="text-danger">*</span></label>
                                                                    <input class="multisteps-form__input form-control"
                                                                        type="text" placeholder="Contact Email" name="contact_email"/>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-12 col-sm-6 mt-3 mt-sm-0">
                                                                    <label>Password : <span class="text-danger">*</span></label>
                                                                    <input class="multisteps-form__input form-control"
                                                                        type="password" placeholder="******" name="password"/>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        
                                                    </div>
                                                </div>
                                                <div class="col-12 col-md-4">   
                                                    <div class="card p-3 border-radius-xl bg-white js-active"
                                                        data-animation="FadeIn">
                                                        <h5 class="font-weight-bolder mb-0">Professional Infos</h5>
                                                        <p class="mb-0 text-sm">Mandatory informations</p>
                                                        <div class="multisteps-form__content ">
                                                            <div class="row mt-3">
                                                                <div class="col-12 col-sm-6">
                                                                    <label>Cnss : </label>
                                                                    <input class="multisteps-form__input form-control"
                                                                        type="text" placeholder="Cnss Number" name="CNSS" />
                                                                </div>
                                                                <div class="col-12 col-sm-6 mt-3 mt-sm-0">
                                                                    <label>bankAccountNumber : </label>
                                                                    <input class="multisteps-form__input form-control"
                                                                        type="text" placeholder="bank Account Number" name="bankAccountNumber"/>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                            <div class="col-12 col-sm-6">
                                                                <label>Cost Per Hour : <span class="text-danger">*</span></label>
                                                                    <input class="multisteps-form__input form-control"
                                                                        type="text" placeholder="Cost Per Hour" name="cost_per_hour"/>
                                                                </div>
                                                                <div class="col-12 col-sm-6">
                                                                <label>Salary : </label>
                                                                    <input class="multisteps-form__input form-control"
                                                                        type="text" placeholder="salary" name="salary"/>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-12 col-sm-6">
                                                                    <label>Cotisation CNSS :</label>
                                                                    <input class="multisteps-form__input form-control"
                                                                        type="number" step="0.0001" placeholder="Cotisation CNSS " name="Cotisation_CNSS"/>
                                                                </div>
                                                                <div class="col-12 col-sm-6">
                                                                    <label>Cotisation AMO : </label>
                                                                    <input class="multisteps-form__input form-control"
                                                                        type="number" step="0.0001" placeholder="Cotisation AMO" name="Cotisation_AMO"/>
                                                                </div>
                                                                <div class="col-12 col-sm-6">
                                                                    <label>work Hours limit : <span class="text-danger">*</span></label>
                                                                    <input class="multisteps-form__input form-control"
                                                                        type="number" step="0.0001" placeholder="workHours_limit" name="workHours_limit"/>
                                                                </div>
                                                                <div class="col-12 col-sm-6">
                                                                    <label>Release Date :</label>
                                                                    <input class="multisteps-form__input form-control"
                                                                        type="date" placeholder="Company Name " name="Date_Release"/>
                                                                </div>
                                                                <div class="col-12 col-sm-6">
                                                                    <label>Timezone</label>
                                                                    <select class="multisteps-form__input form-control" name="timezone">
                                                                        @foreach (timezone_identifiers_list() as $timezone)
                                                                            <option value="{{ $timezone }}">{{ $timezone }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        
                                                    </div>
                                              
                                                <div class="col-12 col-md-4">   
                                                    <div class="card p-3 border-radius-xl bg-white js-active"
                                                        data-animation="FadeIn">
                                                        <h5 class="font-weight-bolder mb-0">Assign Roles <span class="text-danger">*</span></h5>
                                                        <p class="mb-0 text-sm">Roles informations</p>
                                                        <div class="multisteps-form__content ">
                                                        <div class="row mt-3">
                                                            @foreach($roles as $role)
                                                            <div class="form-check">
                                                                @if(Auth::user()->hasRole('SuperAdmin') || (!$user->hasRole('SuperAdmin') && $role != 'SuperAdmin'))
                                                                <input id="vue-checkbox" name="roles[]" type="checkbox"
                                                                    value="{{ $role->name }}" >
                                                                <label for="vue-checkbox" class="form-check-label">
                                                                {{ $role->name }}
                                                                </label>
                                                                @endif
                                                            </div>
                                                            @endforeach
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="button-row d-flex mt-4">
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
                                                    type="submit" title="create">
                                                    Create
                                                </button>
                                            </div>
                                        </form>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

    </main>
    @include('dashboard_layouts.scripts')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

    <script>
  window.addEventListener('load', function() {
    var inputs = document.getElementsByTagName('input');
    for (var i = 0; i < inputs.length; i++) {
      inputs[i].setAttribute('autocomplete', 'none');
    }
  });
</script>
    

</body>

</html>