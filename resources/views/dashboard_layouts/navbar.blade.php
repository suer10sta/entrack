
@if(Auth::check() && Auth::user()->entreprise_id && Auth::user()->entreprise && Auth::user()->entreprise->trial_ends_at)
            @php
                $isOnTrial = Auth::user()->entreprise->isOnTrial();
                $isExpired = Auth::user()->entreprise->trial_ends_at->isPast();
                $daysLeft = $isOnTrial ? max(0, (int) ceil(now()->floatDiffInDays(Auth::user()->entreprise->trial_ends_at))) : null;
            @endphp
            @if($isOnTrial && $daysLeft > 0)
                <div class="bg-amber-50 border-b border-amber-200 px-4 py-2 text-center">
                <span class="text-sm font-semibold text-amber-800">
                        ⏳ {{ __('messages.days_left_trial', ['days' => $daysLeft]) }}.
                        <a href="{{ route('profile.upgrade') }}" class="ml-2 text-amber-600 underline font-medium hover:text-amber-800">{{ __('messages.upgrade') }}</a>
                    </span>
                </div>
            @elseif($isExpired)
                <div class="bg-red-50 border-b border-red-200 px-4 py-2 text-center">
                    <span class="text-sm font-semibold text-red-800">
                        ⚠️ {{ __('messages.trial_expired') }} <a href="{{ route('profile.upgrade') }}" class="text-red-600 underline font-medium hover:text-red-800">{{ __('messages.upgrade_plan') }}</a> pour continuer.
                    </span>
                </div>
            @endif
        @endif

           <div class="sidenav-toggler sidenav-toggler-inner d-lg-none d-block">
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
