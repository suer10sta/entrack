<header class="fixed top-0 z-50 w-full bg-[#0EA5E9] shadow-md transition-all duration-300">
  <div class="container mx-auto flex items-center justify-between px-6 h-20">
    
    <!-- ===== Logo ===== -->
    <a href="{{ route('welcome') }}" class="flex items-center gap-3">
      <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-white shadow-md">
        <svg class="h-5 w-5 text-primary" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round"
            d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8zM14 2v6h6" />
          <path stroke-linecap="round" stroke-linejoin="round" d="M8 13h8M8 16h8" />
        </svg>
      </div>
      <span class="text-lg font-semibold text-white">Enterprise Tracker Pro</span>
    </a>

    <!-- ===== Desktop Navigation ===== -->
    <nav class="hidden md:flex items-center gap-8">
      <a href="{{ route('welcome') }}#features" class="text-sm font-medium text-white hover:text-accent transition-colors">Features</a>
      <a href="{{ route('welcome') }}#customers" class="text-sm font-medium text-white hover:text-accent transition-colors">The Three Plans</a>
      <a href="{{ route('about') }}" class="text-sm font-medium text-white hover:text-accent transition-colors">About Us</a>

      @if (Route::has('login'))
        @auth
          <a href="{{ url('/dashboard') }}" 
             class="ml-4 rounded-lg bg-white px-5 py-2 text-sm font-semibold text-primary shadow hover:bg-gray-100 transition">
            Dashboard
          </a>
        @else
          <a href="{{ route('login') }}" 
             class="ml-4 rounded-lg bg-white px-5 py-2 text-sm font-semibold text-primary shadow hover:bg-gray-100 transition">
            Login
          </a>
        @endauth
      @endif
    </nav>

    <!-- ===== Mobile Menu Button ===== -->
    <div class="md:hidden">
      <button id="mobile-menu-btn" class="text-white hover:text-accent focus:outline-none">
        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
      </button>
    </div>
  </div>

  <!-- ===== Mobile Menu ===== -->
  <div id="mobile-menu" class="hidden md:hidden bg-primary shadow-lg border-t border-primary/30">
    <nav class="flex flex-col gap-4 p-6">
      <a href="{{ route('welcome') }}#features" class="text-white hover:text-accent font-medium">Features</a>
      <a href="{{ route('welcome') }}#customers" class="text-white hover:text-accent font-medium">The Three Plans</a>
      <a href="{{ route('about') }}" class="text-white hover:text-accent font-medium">About Us</a>

      @if (Route::has('login'))
        @auth
          <a href="{{ url('/dashboard') }}" 
             class="rounded-lg bg-white px-5 py-2 text-sm font-semibold text-primary hover:bg-gray-100 transition">
            Dashboard
          </a>
        @else
          <a href="{{ route('login') }}" 
             class="rounded-lg bg-white px-5 py-2 text-sm font-semibold text-primary hover:bg-gray-100 transition">
            Login
          </a>
        @endauth
      @endif
    </nav>
  </div>
</header>

<script>
  const btn = document.getElementById('mobile-menu-btn');
  const menu = document.getElementById('mobile-menu');
  btn.addEventListener('click', () => menu.classList.toggle('hidden'));
</script>
