{{-- resources/views/components/header.blade.php --}}

<header class="fixed top-0 z-50 w-full bg-primary transition-all duration-300">
  <div class="container mx-auto flex items-center justify-between px-6 h-20">
    
    <a href="#" class="flex items-center gap-3">
      <div class="flex h-12 w-12 items-center justify-center rounded-full bg-primary-foreground shadow-md">
        <svg class="h-6 w-6 text-primary" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round"
            d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8zM14 2v6h6" />
          <path stroke-linecap="round" stroke-linejoin="round" d="M8 13h8M8 16h8" />
        </svg>
      </div>
      <span class="text-lg font-semibold text-primary-foreground">Enterprise Tracker Pro</span>
    </a>

    <nav class="hidden md:flex items-center gap-8">
      <a href="#features" class="relative text-sm font-medium text-primary-foreground hover:text-accent transition-all">Features</a>
      <a href="#testimonials" class="relative text-sm font-medium text-primary-foreground hover:text-accent transition-all">Why Us</a>
      <a href="#pricing" class="relative text-sm font-medium text-primary-foreground hover:text-accent transition-all">Pricing</a>
      @if (Route::has('login'))
        @auth
          <a href="{{ url('/dashboard') }}" class="ml-4 rounded-full bg-primary-foreground/20 px-5 py-2 text-sm font-semibold text-primary-foreground shadow hover:bg-primary-foreground/30 transition">Dashboard</a>
        @else
          <a href="{{ route('login') }}" class="ml-4 rounded-full bg-primary-foreground/20 px-5 py-2 text-sm font-semibold text-primary-foreground shadow hover:bg-primary-foreground/30 transition">Login</a>
        @endauth
      @endif
    </nav>

    <div class="md:hidden">
      <button id="mobile-menu-btn" class="text-primary-foreground hover:text-accent focus:outline-none">
        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
      </button>
    </div>
  </div>

  <div id="mobile-menu" class="hidden md:hidden bg-primary shadow-lg">
    <nav class="flex flex-col gap-4 p-6">
      <a href="#features" class="text-primary-foreground hover:text-accent font-medium">Features</a>
      <a href="#testimonials" class="text-primary-foreground hover:text-accent font-medium">Why Us</a>
      <a href="#pricing" class="text-primary-foreground hover:text-accent font-medium">Pricing</a>
      @if (Route::has('login'))
        @auth
          <a href="{{ url('/dashboard') }}" class="rounded-full bg-primary-foreground/20 px-5 py-2 text-sm font-semibold text-primary-foreground hover:bg-primary-foreground/30 transition">Dashboard</a>
        @else
          <a href="{{ route('login') }}" class="rounded-full bg-primary-foreground/20 px-5 py-2 text-sm font-semibold text-primary-foreground hover:bg-primary-foreground/30 transition">Login</a>
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