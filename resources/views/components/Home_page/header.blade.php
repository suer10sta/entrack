<header class="fixed top-0 z-50 w-full bg-background/80 backdrop-blur-xl border-b border-border/60 shadow-sm transition-all duration-300">
<div class="container mx-auto flex items-center justify-between px-6 h-16 md:h-20 relative">
    
    <!-- ===== Logo ===== -->
    <a href="{{ route('welcome') }}" class="flex items-center group">
      <img
        src="{{ asset('img/dalytraclogo.png') }}"
        alt="DalyTrac logo"
        class="h-48 w-auto md:h-52 object-contain"
        style="margin-top: 12px;"
      />
    </a>

    <!-- ===== Desktop Navigation (perfectly centered) ===== -->
    <nav class="hidden md:flex items-center gap-8 text-sm font-medium absolute left-1/2 -translate-x-1/2 transform">
      <a 
        href="{{ route('welcome') }}#features" 
        class="relative text-foreground/80 hover:text-foreground transition-colors
               {{ request()->routeIs('welcome') ? 'after:absolute after:left-0 after:-bottom-1 after:h-0.5 after:w-full after:rounded-full after:bg-primary text-foreground' : '' }}">
        Features
      </a>
      <a 
        href="{{ route('welcome') }}#account_type" 
        class="relative text-foreground/80 hover:text-foreground transition-colors">
        Plans
      </a>
      <a 
        href="{{ route('demo') }}" 
        class="relative text-foreground/80 hover:text-foreground transition-colors
               {{ request()->routeIs('demo') ? 'after:absolute after:left-0 after:-bottom-1 after:h-0.5 after:w-full after:rounded-full after:bg-primary text-foreground' : '' }}">
        Watch Demo
      </a>
      <a 
        href="{{ route('faq') }}" 
        class="relative text-foreground/80 hover:text-foreground transition-colors
               {{ request()->routeIs('faq') ? 'after:absolute after:left-0 after:-bottom-1 after:h-0.5 after:w-full after:rounded-full after:bg-primary text-foreground' : '' }}">
        FAQ
      </a>
      <a 
        href="{{ route('about') }}" 
        class="relative text-foreground/80 hover:text-foreground transition-colors
               {{ request()->routeIs('about') ? 'after:absolute after:left-0 after:-bottom-1 after:h-0.5 after:w-full after:rounded-full after:bg-primary text-foreground' : '' }}">
        About Us
      </a>
      <a 
        href="{{ url('/contact') }}" 
        class="relative text-foreground/80 hover:text-foreground transition-colors">
        Contact
      </a>
    </nav>

    <!-- ===== Desktop Auth Buttons (right aligned) ===== -->
    <div class="hidden md:flex items-center justify-end flex-shrink-0 ml-auto">
      @if (Route::has('login'))
        @auth
          <a href="{{ url('/dashboard') }}" 
             class="inline-flex items-center gap-2 rounded-full bg-primary/10 px-4 py-1.5 text-xs font-semibold text-primary hover:bg-primary hover:text-primary-foreground transition-colors">
            Dashboard
          </a>
        @else
          <div class="flex items-center gap-3">
            <a href="{{ route('login') }}" 
               class="inline-flex items-center gap-2 rounded-full border border-primary/60 bg-background px-4 py-1.5 text-xs font-semibold text-primary hover:bg-primary/5 transition-colors">
              Sign in
            </a>
            @if (Route::has('register'))
              <a href="{{ route('register') }}"
                 class="inline-flex items-center gap-2 rounded-full bg-primary px-4 py-1.5 text-xs font-semibold text-primary-foreground shadow-sm hover:bg-primary/90 transition-all duration-200 hover:-translate-y-0.5">
                Try it for free
              </a>
            @endif
          </div>
        @endauth
      @endif
    </div>

    <!-- ===== Mobile Menu Button ===== -->
    <div class="md:hidden">
      <button id="mobile-menu-btn" class="inline-flex items-center justify-center rounded-lg p-1.5 text-foreground hover:bg-secondary focus:outline-none">
        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
      </button>
    </div>
  </div>

  <!-- ===== Mobile Menu ===== -->
  <div id="mobile-menu" class="hidden md:hidden bg-background/95 backdrop-blur-xl shadow-lg border-t border-border/60">
    <nav class="flex flex-col gap-4 p-6 text-sm">
      <a href="{{ route('welcome') }}#features" class="text-foreground/90 hover:text-primary font-medium">
        Features
      </a>
      <a href="{{ route('welcome') }}#account_type" class="text-foreground/90 hover:text-primary font-medium">
        Plans
      </a>
      <a href="{{ route('demo') }}" class="text-foreground/90 hover:text-primary font-medium">
        Watch Demo
      </a>
      <a href="{{ route('faq') }}" class="text-foreground/90 hover:text-primary font-medium">
        FAQ
      </a>
      <a href="{{ route('about') }}" class="text-foreground/90 hover:text-primary font-medium">
        About Us
      </a>
      <a href="{{ url('/contact') }}" class="text-foreground/90 hover:text-primary font-medium">
        Contact
      </a>

      @if (Route::has('login'))
        @auth
          <a href="{{ url('/dashboard') }}" 
             class="mt-2 inline-flex items-center justify-center rounded-full bg-primary/10 px-5 py-2 text-sm font-semibold text-primary hover:bg-primary hover:text-primary-foreground transition-colors">
            Dashboard
          </a>
        @else
          <div class="mt-4 flex flex-col gap-2">
            @if (Route::has('register'))
              <a href="{{ route('register') }}"
                 class="inline-flex items-center justify-center rounded-full bg-primary px-5 py-2 text-sm font-semibold text-primary-foreground shadow-sm hover:bg-primary/90 transition-all duration-200">
                Try it for free
              </a>
            @endif
            <a href="{{ route('login') }}" 
               class="inline-flex items-center justify-center rounded-full border border-primary/60 bg-background px-5 py-2 text-sm font-semibold text-primary hover:bg-primary/5 transition-colors">
              Login
            </a>
          </div>
        @endauth
      @endif
    </nav>
  </div>
</header>

<script>
  const btn = document.getElementById('mobile-menu-btn');
  const menu = document.getElementById('mobile-menu');
  if (btn && menu) {
    btn.addEventListener('click', () => {
      menu.classList.toggle('hidden');
    });
  }
</script>
