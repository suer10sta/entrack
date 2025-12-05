<!DOCTYPE html>
<html lang="en">

@include('dashboard_layouts.head')

<body class="g-sidenav-show bg-gray-100">
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-NKDMSK6" height="0" width="0"
            style="display: none; visibility: hidden"></iframe></noscript>
    <div class="min-height-300 bg-primary position-absolute w-100"></div>

    @if(Auth::user()->hasRole('Responsable de projet') || Auth::user()->hasRole('Employee'))
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
                            <h6 class="mb-0">New to do task</h6>
                            <p class="text-sm mb-0">Create to do task</p>
                            <hr class="horizontal dark my-3" />

                            @if(Auth::user()->hasRole('Admin'))
                            <form method="POST" action="{{route('admin.todoliste.store')}}" autocomplete="off">
                            @endif
                            @if(Auth::user()->hasRole('Responsable de projet'))
                            <form method="POST" action="{{route('Responsable_de_projet.todoliste.store')}}" autocomplete="off">
                            @endif
                            @if(Auth::user()->hasRole('Employee'))
                            <form method="POST" action="{{route('employee.todoliste.store')}}" autocomplete="off">
                            @endif
                                @csrf
                                @method('')
                                <div class="row">
                                    <div class="col-6">
                                        <label for="projectName" class="form-label">Name</label>
                                        <input type="text" class="form-control" id="projectName" name='title' />
                                    </div>
                                </div>
                                <label class="mt-4">Description</label>
                                <input type="text" class="form-control" id="projectDescription" name='description' />
                                <br>
                                
                                <div class="row">
                                    <div class="col-6">
                                        <label class="form-label">Start Date</label>
                                        <input class="form-control datetimepicker" type="date"
                                            placeholder="Please select start date" data-input name='start' />
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label">End Date</label>
                                        <input class="form-control datetimepicker" type="date"
                                            placeholder="Please select end date" data-input name='end' />
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end mt-4">
                                    @if(Auth::user()->hasRole('Admin'))
                                    <a class='btn btn-light m-0' href="{{route('admin.index')}}">
                                    @endif
                                    @if(Auth::user()->hasRole('Responsable de projet'))
                                    <a class='btn btn-light m-0' href="{{route('Responsable_de_projet.index')}}">
                                    @endif
                                    @if(Auth::user()->hasRole('Employee'))
                                    <a class='btn btn-light m-0' href="{{route('employee.index')}}">
                                    @endif
                                        Cancel
                                    </a>
                                    <button type="submit" name="button" class="btn bg-gradient-primary m-0 ms-2">
                                        Create task
                                    </button>
                                </div>

                            </form>

                        </div>
                    </div>
                </div>



    </main>

    <div class="fixed-plugin">
        <a class="fixed-plugin-button text-dark position-fixed px-3 py-2">
            <i class="fa fa-cog py-2"> </i>
        </a>
        <div class="card shadow-lg">
            <div class="card-header pb-0 pt-3 bg-transparent">
                <div class="float-start">
                    <h5 class="mt-3 mb-0">Argon Configurator</h5>
                    <p>See our dashboard options.</p>
                </div>
                <div class="float-end mt-4">
                    <button class="btn btn-link text-dark p-0 fixed-plugin-close-button">
                        <i class="fa fa-close"></i>
                    </button>
                </div>
            </div>
            <hr class="horizontal dark my-1" />
            <div class="card-body pt-sm-3 pt-0 overflow-auto">
                <div>
                    <h6 class="mb-0">Sidebar Colors</h6>
                </div>
                <a href="javascript:void(0)" class="switch-trigger background-color">
                    <div class="badge-colors my-2 text-start">
                        <span class="badge filter bg-gradient-primary active" data-color="primary"
                            onclick="sidebarColor(this)"></span>
                        <span class="badge filter bg-gradient-dark" data-color="dark"
                            onclick="sidebarColor(this)"></span>
                        <span class="badge filter bg-gradient-info" data-color="info"
                            onclick="sidebarColor(this)"></span>
                        <span class="badge filter bg-gradient-success" data-color="success"
                            onclick="sidebarColor(this)"></span>
                        <span class="badge filter bg-gradient-warning" data-color="warning"
                            onclick="sidebarColor(this)"></span>
                        <span class="badge filter bg-gradient-danger" data-color="danger"
                            onclick="sidebarColor(this)"></span>
                    </div>
                </a>

                <div class="mt-3">
                    <h6 class="mb-0">Sidenav Type</h6>
                    <p class="text-sm">Choose between 2 different sidenav types.</p>
                </div>
                <div class="d-flex">
                    <button class="btn bg-gradient-primary w-100 px-3 mb-2 active me-2" data-class="bg-white"
                        onclick="sidebarType(this)">
                        White
                    </button>
                    <button class="btn bg-gradient-primary w-100 px-3 mb-2" data-class="bg-default"
                        onclick="sidebarType(this)">
                        Dark
                    </button>
                </div>
                <p class="text-sm d-xl-none d-block mt-2">
                    You can change the sidenav type just on desktop view.
                </p>

                <div class="d-flex my-3">
                    <h6 class="mb-0">Navbar Fixed</h6>
                    <div class="form-check form-switch ps-0 ms-auto my-auto">
                        <input class="form-check-input mt-1 ms-auto" type="checkbox" id="navbarFixed"
                            onclick="navbarFixed(this)" />
                    </div>
                </div>
                <hr class="horizontal dark mb-1" />
                <div class="d-flex my-4">
                    <h6 class="mb-0">Sidenav Mini</h6>
                    <div class="form-check form-switch ps-0 ms-auto my-auto">
                        <input class="form-check-input mt-1 ms-auto" type="checkbox" id="navbarMinimize"
                            onclick="navbarMinimize(this)" />
                    </div>
                </div>
                <hr class="horizontal dark my-sm-4" />
                <div class="mt-2 mb-5 d-flex">
                    <h6 class="mb-0">Light / Dark</h6>
                    <div class="form-check form-switch ps-0 ms-auto my-auto">
                        <input class="form-check-input mt-1 ms-auto" type="checkbox" id="dark-version"
                            onclick="darkMode(this)" />
                    </div>
                </div>
                <a class="btn btn-primary w-100" href="https://www.creative-tim.com/product/argon-dashboard-pro">Buy
                    now</a>
                <a class="btn btn-dark w-100" href="https://www.creative-tim.com/product/argon-dashboard">Free demo</a>
                <a class="btn btn-outline-dark w-100"
                    href="https://www.creative-tim.com/learning-lab/bootstrap/overview/argon-dashboard">View
                    documentation</a>
                <div class="w-100 text-center">
                    <a class="github-button" href="https://github.com/creativetimofficial/ct-argon-dashboard-pro"
                        data-icon="octicon-star" data-size="large" data-show-count="true"
                        aria-label="Star creativetimofficial/argon-dashboard on GitHub">Star</a>
                    <h6 class="mt-3">Thank you for sharing!</h6>
                    <a href="https://twitter.com/intent/tweet?text=Check%20Argon%20Dashboard%20PRO%20made%20by%20%40CreativeTim%20%23webdesign%20%23dashboard%20%23bootstrap5&amp;url=https%3A%2F%2Fwww.creative-tim.com%2Fproduct%2Fargon-dashboard-pro"
                        class="btn btn-dark mb-0 me-2" target="_blank">
                        <i class="fab fa-twitter me-1" aria-hidden="true"></i> Tweet
                    </a>
                    <a href="https://www.facebook.com/sharer/sharer.php?u=https://www.creative-tim.com/product/argon-dashboard-pro"
                        class="btn btn-dark mb-0 me-2" target="_blank">
                        <i class="fab fa-facebook-square me-1" aria-hidden="true"></i>
                        Share
                    </a>
                </div>
            </div>
        </div>
    </div>

    @include('dashboard_layouts.scripts')

    <script>
  window.addEventListener('load', function() {
    var inputs = document.getElementsByTagName('input');
    for (var i = 0; i < inputs.length; i++) {
      inputs[i].setAttribute('autocomplete', 'none');
    }
  });
</script>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const dropdownButton = document.getElementById('ClientDropdown');
        const selectedProjectInput = document.getElementById('selectedClient');

        document.querySelectorAll('.dropdown-item').forEach(item => {
            item.addEventListener('click', function (event) {
                event.preventDefault();

                const selectedClient = event.target.getAttribute('data-value');

                // Update the button text
                dropdownButton.textContent = selectedClient;

                // Update the hidden input value
                selectedProjectInput.value = selectedClient;
            });
        });
    });
</script>


</body>

</html>
