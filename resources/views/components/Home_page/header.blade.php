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
        {{ __('messages.Features') }}
      </a>
      <a 
        href="{{ route('welcome') }}#account_type" 
        class="relative text-foreground/80 hover:text-foreground transition-colors">
        {{ __('messages.Plans') }}
      </a>
      <a 
        href="{{ route('demo') }}" 
        class="relative text-foreground/80 hover:text-foreground transition-colors
               {{ request()->routeIs('demo') ? 'after:absolute after:left-0 after:-bottom-1 after:h-0.5 after:w-full after:rounded-full after:bg-primary text-foreground' : '' }}">
        {{ __('messages.Watch Demo') }}
      </a>
      <a 
        href="{{ route('faq') }}" 
        class="relative text-foreground/80 hover:text-foreground transition-colors
               {{ request()->routeIs('faq') ? 'after:absolute after:left-0 after:-bottom-1 after:h-0.5 after:w-full after:rounded-full after:bg-primary text-foreground' : '' }}">
        {{ __('messages.FAQ') }}
      </a>
      <a 
        href="{{ route('about') }}" 
        class="relative text-foreground/80 hover:text-foreground transition-colors
               {{ request()->routeIs('about') ? 'after:absolute after:left-0 after:-bottom-1 after:h-0.5 after:w-full after:rounded-full after:bg-primary text-foreground' : '' }}">
        {{ __('messages.About Us') }}
      </a>
      <a 
        href="{{ url('/contact') }}" 
        class="relative text-foreground/80 hover:text-foreground transition-colors">
        {{ __('messages.Contact') }}
      </a>
    </nav>

    <!-- ===== Desktop Auth Buttons (right aligned) ===== -->
    <div class="hidden md:flex items-center justify-end flex-shrink-0 ml-auto gap-3">
      {{-- Language Switcher --}}
      <div class="relative" x-data="{ open: false }">
        <button 
          @click="open = !open"
          @click.away="open = false"
          type="button"
          class="relative p-2 border border-border/60 bg-background/80 rounded-lg hover:bg-secondary transition-colors flex items-center gap-2"
          title="{{ __('messages.Language') }}"
        >
          
          <span class="text-xs font-medium text-foreground/80 uppercase flex items-center gap-1">
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
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-foreground/60">
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
          class="absolute right-0 mt-2 w-48 bg-background rounded-lg shadow-lg border border-border z-50"
        >
          <div class="py-2">
            <a 
              href="{{ route('changeLang', ['lang' => 'en']) }}"
              class="block px-4 py-2 text-sm text-foreground/90 hover:bg-secondary transition-colors flex items-center gap-2 {{ app()->getLocale() === 'en' ? 'bg-primary/10 text-primary' : '' }}"
            >
              <img src="https://flagcdn.com/gb.svg" alt="English" class="w-4 h-3 rounded-sm object-cover">
              <span>{{ __('messages.English') }}</span>
              @if(app()->getLocale() === 'en')
                <svg class="w-4 h-4 ml-auto" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
              @endif
            </a>
            <a 
              href="{{ route('changeLang', ['lang' => 'fr']) }}"
              class="block px-4 py-2 text-sm text-foreground/90 hover:bg-secondary transition-colors flex items-center gap-2 {{ app()->getLocale() === 'fr' ? 'bg-primary/10 text-primary' : '' }}"
            >
              <img src="https://flagcdn.com/fr.svg" alt="Français" class="w-4 h-3 rounded-sm object-cover">
              <span>{{ __('messages.Français') }}</span>
              @if(app()->getLocale() === 'fr')
                <svg class="w-4 h-4 ml-auto" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
              @endif
            </a>
            <a 
              href="{{ route('changeLang', ['lang' => 'es']) }}"
              class="block px-4 py-2 text-sm text-foreground/90 hover:bg-secondary transition-colors flex items-center gap-2 {{ app()->getLocale() === 'es' ? 'bg-primary/10 text-primary' : '' }}"
            >
              <img src="https://flagcdn.com/es.svg" alt="Español" class="w-4 h-3 rounded-sm object-cover">
              <span>{{ __('messages.Español') }}</span>
              @if(app()->getLocale() === 'es')
                <svg class="w-4 h-4 ml-auto" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
              @endif
            </a>
            <a 
              href="{{ route('changeLang', ['lang' => 'nl']) }}"
              class="block px-4 py-2 text-sm text-foreground/90 hover:bg-secondary transition-colors flex items-center gap-2 {{ app()->getLocale() === 'nl' ? 'bg-primary/10 text-primary' : '' }}"
            >
              <img src="https://flagcdn.com/be.svg" alt="Nederlands" class="w-4 h-3 rounded-sm object-cover">
              <span>{{ __('messages.Nederlands') }}</span>
              @if(app()->getLocale() === 'nl')
                <svg class="w-4 h-4 ml-auto" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
              @endif
            </a>
          </div>
        </div>
      </div>

      @if (Route::has('login'))
        @auth
          <a href="{{ url('/dashboard') }}" 
             class="inline-flex items-center gap-2 rounded-full bg-primary/10 px-4 py-1.5 text-xs font-semibold text-primary hover:bg-primary hover:text-primary-foreground transition-colors">
            {{ __('messages.Dashboard') }}
          </a>
        @else
          <div class="flex items-center gap-3">
            <a href="{{ route('login') }}" 
               class="inline-flex items-center gap-2 rounded-full border border-primary/60 bg-background px-4 py-1.5 text-xs font-semibold text-primary hover:bg-primary/5 transition-colors">
              {{ __('messages.Sign in') }}
            </a>
            @if (Route::has('register'))
              <a href="{{ route('register') }}"
                 class="inline-flex items-center gap-2 rounded-full bg-primary px-4 py-1.5 text-xs font-semibold text-primary-foreground shadow-sm hover:bg-primary/90 transition-all duration-200 hover:-translate-y-0.5">
                {{ __('messages.Try it for free') }}
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
        {{ __('messages.Features') }}
      </a>
      <a href="{{ route('welcome') }}#account_type" class="text-foreground/90 hover:text-primary font-medium">
        {{ __('messages.Plans') }}
      </a>
      <a href="{{ route('demo') }}" class="text-foreground/90 hover:text-primary font-medium">
        {{ __('messages.Watch Demo') }}
      </a>
      <a href="{{ route('faq') }}" class="text-foreground/90 hover:text-primary font-medium">
        {{ __('messages.FAQ') }}
      </a>
      <a href="{{ route('about') }}" class="text-foreground/90 hover:text-primary font-medium">
        {{ __('messages.About Us') }}
      </a>
      <a href="{{ url('/contact') }}" class="text-foreground/90 hover:text-primary font-medium">
        {{ __('messages.Contact') }}
      </a>

      {{-- Mobile Language Switcher --}}
      <div class="pt-2 border-t border-border/60">
        <p class="text-xs font-semibold text-foreground/60 uppercase mb-2">{{ __('messages.Language') }}</p>
        <div class="flex flex-col gap-2">
          <a 
            href="{{ route('changeLang', ['lang' => 'en']) }}"
            class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm {{ app()->getLocale() === 'en' ? 'bg-primary/10 text-primary font-semibold' : 'text-foreground/90 hover:bg-secondary' }}"
          >
            <img src="https://flagcdn.com/gb.svg" alt="English" class="w-4 h-3 rounded-sm object-cover">
            <span>{{ __('messages.English') }}</span>
            @if(app()->getLocale() === 'en')
              <svg class="w-4 h-4 ml-auto" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
              </svg>
            @endif
          </a>
          <a 
            href="{{ route('changeLang', ['lang' => 'fr']) }}"
            class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm {{ app()->getLocale() === 'fr' ? 'bg-primary/10 text-primary font-semibold' : 'text-foreground/90 hover:bg-secondary' }}"
          >
            <img src="https://flagcdn.com/fr.svg" alt="Français" class="w-4 h-3 rounded-sm object-cover">
            <span>{{ __('messages.Français') }}</span>
            @if(app()->getLocale() === 'fr')
              <svg class="w-4 h-4 ml-auto" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
              </svg>
            @endif
          </a>
          <a 
            href="{{ route('changeLang', ['lang' => 'es']) }}"
            class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm {{ app()->getLocale() === 'es' ? 'bg-primary/10 text-primary font-semibold' : 'text-foreground/90 hover:bg-secondary' }}"
          >
            <img src="https://flagcdn.com/es.svg" alt="Español" class="w-4 h-3 rounded-sm object-cover">
            <span>{{ __('messages.Español') }}</span>
            @if(app()->getLocale() === 'es')
              <svg class="w-4 h-4 ml-auto" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
              </svg>
            @endif
          </a>
          <a 
            href="{{ route('changeLang', ['lang' => 'nl']) }}"
            class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm {{ app()->getLocale() === 'nl' ? 'bg-primary/10 text-primary font-semibold' : 'text-foreground/90 hover:bg-secondary' }}"
          >
            <img src="https://flagcdn.com/be.svg" alt="Nederlands" class="w-4 h-3 rounded-sm object-cover">
            <span>{{ __('messages.Nederlands') }}</span>
            @if(app()->getLocale() === 'nl')
              <svg class="w-4 h-4 ml-auto" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
              </svg>
            @endif
          </a>
        </div>
      </div>

      @if (Route::has('login'))
        @auth
          <a href="{{ url('/dashboard') }}" 
             class="mt-2 inline-flex items-center justify-center rounded-full bg-primary/10 px-5 py-2 text-sm font-semibold text-primary hover:bg-primary hover:text-primary-foreground transition-colors">
            {{ __('messages.Dashboard') }}
          </a>
        @else
          <div class="mt-4 flex flex-col gap-2">
            @if (Route::has('register'))
              <a href="{{ route('register') }}"
                 class="inline-flex items-center justify-center rounded-full bg-primary px-5 py-2 text-sm font-semibold text-primary-foreground shadow-sm hover:bg-primary/90 transition-all duration-200">
                {{ __('messages.Try it for free') }}
              </a>
            @endif
            <a href="{{ route('login') }}" 
               class="inline-flex items-center justify-center rounded-full border border-primary/60 bg-background px-5 py-2 text-sm font-semibold text-primary hover:bg-primary/5 transition-colors">
              {{ __('messages.Login') }}
            </a>
          </div>
        @endauth
      @endif
    </nav>
  </div>
</header>

<style>
  /* Hide Alpine.js elements until Alpine is initialized */
  [x-cloak] {
    display: none !important;
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
