
          <div class="sidenav-toggler sidenav-toggler-inner d-xl-block d-none">
          <a href="javascript:;" class="nav-link p-0" onclick="toggleSidebar()">
  <div class="sidenav-toggler-inner">
    <i class="sidenav-toggler-line bg-white"></i>
    <i class="sidenav-toggler-line bg-white"></i>
    <i class="sidenav-toggler-line bg-white"></i>
  </div>
</a>
<style>
  .sidebar {
  width: 250px;
  transition: width 0.5s;
}

.sidebar.hidden {
  width: 0;
  overflow: hidden;
}
</style>
          </div>
          <div
            class="collapse navbar-collapse mt-sm-0 mt-2 me-md-0 me-sm-4"
            id="navbar"
          >
            
            <ul class="navbar-nav justify-content-end">
              <li class="nav-item d-flex align-items-center">

                <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            @php 
                                $user = Auth::user();
                                $name=$user->name;
                            @endphp
                            <x-dropdown-link :href="route('logout')" onclick="event.preventDefault();
                                                    this.closest('form').submit();" class="nav-link text-white font-weight-bold">
                              {{ __('Log Out') }}
                              <div class="text-gray">{{ $name }}</div>
                            </x-dropdown-link>
                            
                           
                        </form>

                
              </li>
              <li class="nav-item d-xl-none ps-3 d-flex align-items-center">
                
              </li>
              <li class="nav-item px-3 d-flex align-items-center">
                
              </li>
              <li class="nav-item dropdown pe-2 d-flex align-items-center">
                
                
              </li>
            </ul>
          </div>
        </div>
      </nav>
<script>
  function toggleSidebar() {
  var sidebar = document.getElementById("sidenav-main");
  sidebar.classList.toggle("hidden");
}
</script>
