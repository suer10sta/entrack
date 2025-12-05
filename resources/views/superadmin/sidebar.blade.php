<aside class="sidenav bg-white navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-4"
    id="sidenav-main">
    <div class="sidenav-header">
        <i class="fas fa-times p-3 cursor-pointer text-secondary opacity-5 position-absolute end-0 top-0 d-none d-xl-none"
            aria-hidden="true" id="iconSidenav"></i>
        <a class="navbar-brand m-0"
            href=" https://demos.creative-tim.com/argon-dashboard-pro/pages/dashboards/default.html " target="_blank">
            <img src="{{asset('img/logo-ct-dark.png')}}" class="navbar-brand-img h-100" alt="main_logo" />
            <span class="ms-1 font-weight-bold">SuperAdmin Dashboard</span>
        </a>
    </div>
    <hr class="horizontal dark mt-0" />
    <div class="collapse navbar-collapse w-auto h-auto" id="sidenav-collapse-main">
        <ul class="navbar-nav">
        <li class="nav-item">
                <a class="nav-link active" href="{{route('superadmin.index')}}">
                    <span class="sidenav-mini-icon"> U </span>
                    <i class="fa-solid fa-house" style="color: #B197FC;"></i>
                    <span class="sidenav-normal "> Dashboard <b class="nav-link-ms-1"></b></span>
                </a>
                
            </li>
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="collapse" aria-expanded="false" href="#usersCollapse">
                    <span class="sidenav-mini-icon"> U </span>
                    <i class="fa-regular fa-user" style="color: #18cd5e;"></i>
                    <span class="sidenav-normal"> Users <b class="nav-link-text ms-1"></b></span>
                </a>
                <div class="collapse" id="usersCollapse">
                <ul class="nav nav-sm flex-column">
                  <li class="nav-item">
                    <a class="nav-link" href="{{route('superadmin.users.index')}}">
                      <span class="sidenav-mini-icon text-sm"> R </span>
                      <span class="sidenav-normal "><i class="fa-solid fa-chevron-right"></i> Reports </span>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" href="{{route('superadmin.users.create')}}">
                      <span class="sidenav-mini-icon text-sm"> N </span>
                      <span class="sidenav-normal"><i class="fa-solid fa-chevron-right"></i> New User </span>
                    </a>
                  </li>
                </ul>
                </div>
            </li>


            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="collapse" aria-expanded="false" href="#enterprisesCollapse">
                    <span class="sidenav-mini-icon"> E </span>
                    <i class="fa-solid fa-shop" style="color: #00aaff;"></i>
                    <span class="sidenav-normal "> Enterprises <b class="nav-link-text ms-1"></b></span>
                </a>
                <div class="collapse" id="enterprisesCollapse">
                <ul class="nav nav-sm flex-column">
                <li class="nav-item">
                    <a class="nav-link" href="{{route('superadmin.entreprise.index')}}">
                      <span class="sidenav-mini-icon text-sm"> R </span>
                      <span class="sidenav-normal"><i class="fa-solid fa-chevron-right"></i> Reports </span>
                    </a>
                  </li>
                </ul>
                </div>
            </li>


            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="collapse" aria-expanded="false" href="#rolesCollapse">
                    <span class="sidenav-mini-icon"> R </span>
                    <i class="fa-solid fa-hand-sparkles" style="color: #ff1f6d;"></i>
                    <span class="sidenav-normal"> Roles <b class="nav-link-text ms-1"></b></span>
                </a>
                <div class="collapse" id="rolesCollapse">
                <ul class="nav nav-sm flex-column">
                <li class="nav-item">
                    <a class="nav-link" href="{{route('superadmin.roles.index')}}">
                      <span class="sidenav-mini-icon text-sm"> R </span>
                      <span class="sidenav-normal"><i class="fa-solid fa-chevron-right"></i> Reports </span>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" href="{{route('superadmin.roles.create')}}">
                      <span class="sidenav-mini-icon text-sm"> N </span>
                      <span class="sidenav-normal"><i class="fa-solid fa-chevron-right"></i> New Role </span>
                    </a>
                  </li>
                </ul>
                </div>
            </li>


            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="collapse" aria-expanded="false" href="#permissionsCollapse">
                    <span class="sidenav-mini-icon"> P </span>
                    <i class="fa-solid fa-wand-sparkles" style="color: #ffc21a;"></i>
                    <span class="sidenav-normal"> Permissions <b class="nav-link-text ms-1"></b></span>
                </a>
                <div class="collapse" id="permissionsCollapse">
                <ul class="nav nav-sm flex-column">
                <li class="nav-item">
                    <a class="nav-link" href="{{route('superadmin.permissions.index')}}">
                      <span class="sidenav-mini-icon text-sm"> R </span>
                      <span class="sidenav-normal"><i class="fa-solid fa-chevron-right"></i>  Reports </span>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" href="{{route('superadmin.permissions.create')}}">
                      <span class="sidenav-mini-icon text-sm"> N </span>
                      <span class="sidenav-normal"> <i class="fa-solid fa-chevron-right"></i> New Permission </span>
                    </a>
                  </li>
                </ul>
                </div>
            </li>
            <!-- ... -->
        </ul>
       
    </div>

</aside>
