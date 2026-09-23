<header class="fixed top-0 z-50 w-full bg-white/90 backdrop-blur-md border-b border-slate-100 shadow-sm transition-all duration-300">
  <div class="container mx-auto flex items-center justify-between px-6 h-20 relative">
    
    <!-- ===== Logo ===== -->
    <a href="{{ route('welcome') }}" class="flex items-center group">
      <span class="text-2xl sm:text-3xl font-extrabold tracking-tight">
        <span class="text-slate-900">Daly</span><span class="text-[#1e535f]">Trac</span>
      </span>
    </a>

    <!-- ===== Desktop Navigation (Centered) ===== -->
    <nav class="hidden lg:flex items-center gap-8 text-sm font-medium absolute left-1/2 -translate-x-1/2 transform text-slate-700">
      <a 
        href="{{ route('welcome') }}#features" 
        class="hover:text-slate-900 transition-colors {{ request()->routeIs('welcome') ? 'text-slate-900 font-semibold' : '' }}">
        Produit
      </a>
      <a 
        href="{{ route('welcome') }}#account_type" 
        class="hover:text-slate-900 transition-colors"> 
        Tarifs 
      </a> 
      <a 
        href="{{ route('demo') }}" 
        class="hover:text-slate-900 transition-colors {{ request()->routeIs('demo') ? 'text-slate-900 font-semibold' : '' }}">
        Démonstration
      </a>
      <a 
        href="{{ route('login') }}" 
        class="hover:text-slate-900 transition-colors">
        Se connecter
      </a>
    </nav>

    <!-- ===== Desktop Auth & Actions (Right Aligned) ===== -->
    <div class="hidden lg:flex items-center justify-end flex-shrink-0 ml-auto gap-4">
      {{-- Language Switcher --}}
      <div class="relative" x-data="{ open: false }">
        <button 
          @click="open = !open"
          @click.away="open = false"
          type="button"
          class="relative p-2 border border-slate-200 bg-white rounded-lg hover:bg-slate-50 transition-colors flex items-center gap-1.5 text-slate-700"
          title="{{ __('messages.Language') }}"
        >
          <span class="text-xs font-semibold uppercase flex items-center gap-1">
            @switch(app()->getLocale())
              @case('fr')
                <img src="https://flagcdn.com/fr.svg" alt="Français" class="w-4 h-3 rounded-sm object-cover">
                <span>FR</span>
                @break
              @case('es')
                <img src="https://flagcdn.com/es.svg" alt="Español" class="w-4 h-3 rounded-sm object-cover">
                <span>ES</span>
                @break
              @case('nl')
                <img src="https://flagcdn.com/be.svg" alt="Nederlands" class="w-4 h-3 rounded-sm object-cover">
                <span>NL</span>
                @break
              @default
                <img src="https://flagcdn.com/gb.svg" alt="English" class="w-4 h-3 rounded-sm object-cover">
                <span>EN</span>
            @endswitch
          </span>
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3.5 h-3.5 text-slate-500">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
          </svg>
        </button>
        
        <div 
          x-show="open"
          x-cloak
          x-transition:enter="transition ease-out duration-100"
          x-transition:enter-start="transform opacity-0 scale-95"
          x-transition:enter-end="transform opacity-100 scale-100"
          x-transition:leave="transition ease-in duration-75"
          x-transition:leave-start="transform opacity-100 scale-100"
          x-transition:leave-end="transform opacity-0 scale-95"
          class="absolute right-0 mt-2 w-44 bg-white rounded-xl shadow-xl border border-slate-100 z-50 py-1"
        >
          <a 
            href="{{ route('changeLang', ['lang' => 'fr']) }}"
            class="px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 flex items-center gap-2 {{ app()->getLocale() === 'fr' ? 'font-semibold text-[#1e535f] bg-slate-50' : '' }}"
          >
            <img src="https://flagcdn.com/fr.svg" alt="Français" class="w-4 h-3 rounded-sm object-cover">
            <span>Français</span>
          </a>
          <a 
            href="{{ route('changeLang', ['lang' => 'en']) }}"
            class="px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 flex items-center gap-2 {{ app()->getLocale() === 'en' ? 'font-semibold text-[#1e535f] bg-slate-50' : '' }}"
          >
            <img src="https://flagcdn.com/gb.svg" alt="English" class="w-4 h-3 rounded-sm object-cover">
            <span>English</span>
          </a>
          <a 
            href="{{ route('changeLang', ['lang' => 'es']) }}"
            class="px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 flex items-center gap-2 {{ app()->getLocale() === 'es' ? 'font-semibold text-[#1e535f] bg-slate-50' : '' }}"
          >
            <img src="https://flagcdn.com/es.svg" alt="Español" class="w-4 h-3 rounded-sm object-cover">
            <span>Español</span>
          </a>
          <a 
            href="{{ route('changeLang', ['lang' => 'nl']) }}"
            class="px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 flex items-center gap-2 {{ app()->getLocale() === 'nl' ? 'font-semibold text-[#1e535f] bg-slate-50' : '' }}"
          >
            <img src="https://flagcdn.com/be.svg" alt="Nederlands" class="w-4 h-3 rounded-sm object-cover">
            <span>Nederlands</span>
          </a>
        </div>
      </div>

      {{-- CTA Button --}}
      @auth
        <a href="{{ url('/dashboard') }}" 
           class="inline-flex items-center gap-2 rounded-full bg-[#1e535f] hover:bg-[#163f47] px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition-all duration-200">
          {{ __('messages.Dashboard') }}
        </a>
      @else
        <a href="{{ route('register') }}"
           class="inline-flex items-center gap-2 rounded-full bg-[#1e535f] hover:bg-[#163f47] px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition-all duration-200 hover:shadow-md">
          Essayer gratuitement
        </a>
      @endauth
    </div>

    <!-- ===== Mobile Menu Button ===== -->
    <div class="lg:hidden">
      <button id="mobile-menu-btn" class="inline-flex items-center justify-center rounded-lg p-2 text-slate-700 hover:bg-slate-100 focus:outline-none">
        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
      </button>
    </div>
  </div>

  <!-- ===== Mobile Menu ===== -->
  <div id="mobile-menu" class="hidden lg:hidden bg-white border-b border-slate-100 shadow-xl">
    <nav class="flex flex-col gap-3 p-6 text-sm">
      <a href="{{ route('welcome') }}#features" class="text-slate-700 hover:text-slate-900 font-medium py-1">
        Produit
      </a>
      <a href="{{ route('welcome') }}#account_type" class="text-slate-700 hover:text-slate-900 font-medium py-1">
        Tarifs
      </a>
      <a href="{{ route('demo') }}" class="text-slate-700 hover:text-slate-900 font-medium py-1">
        Démonstration
      </a>
      <a href="{{ route('login') }}" class="text-slate-700 hover:text-slate-900 font-medium py-1">
        Se connecter
      </a>
      <div class="pt-3 border-t border-slate-100">
        <a href="{{ route('register') }}" class="w-full inline-flex items-center justify-center rounded-full bg-[#1e535f] px-6 py-2.5 text-sm font-semibold text-white shadow-sm">
          Essayer gratuitement
        </a>
      </div>
    </nav>
  </div>
</header>

<style>
  /* Hide Alpine.js elements until Alpine is initialized */
  [x-cloak] {
    display: none !important;
  }

  /* Force desktop logo size (avoid legacy CSS overrides) */
  @media (min-width: 1024px) {
    .home-header-logo {
      height: 140px !important;
      width: auto !important;
      max-height: none !important;
      max-width: none !important;
    }
  }
</style>

<script>
  // Load Alpine.js if not already loaded
  if (!window.Alpine) {
    const alpineScript = document.createElement('script');
    alpineScript.src = 'https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js';
    alpineScript.defer = true;
    document.head.appendChild(alpineScript);
  }

  // Mobile menu toggle
  const btn = document.getElementById('mobile-menu-btn');
  const menu = document.getElementById('mobile-menu');
  if (btn && menu) {
    btn.addEventListener('click', () => {
      menu.classList.toggle('hidden');
    });
  }
</script>
