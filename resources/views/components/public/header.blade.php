{{-- Shared public Header (single source of truth for all public marketing pages).
     Design: Home page header, preserved as-is. Reused via <x-public.header />. --}}
@php
  $navBase = 'relative text-[0.92rem] font-semibold no-underline transition-colors duration-300 after:absolute after:-bottom-1 after:left-0 after:h-0.5 after:rounded-full after:bg-brand-secondary after:transition-all after:duration-300';
  $navActive = 'text-brand-secondary after:w-full';
  $navIdle = 'text-[#3e5368] after:w-0 hover:text-brand-secondary hover:after:w-full';
  $mobileBase = 'text-[0.92rem] font-semibold no-underline transition-colors duration-300 hover:text-brand-secondary';
@endphp
<a href="#main" class="fixed left-3 top-[-80px] z-[100] rounded-lg bg-brand-primary px-3.5 py-2.5 text-white focus:top-3">{{ __('messages.public_nav_skip') }}</a>

<header id="top" x-data="{ open: false }" class="sticky top-0 z-50 border-b border-[#d9e6ed]/90 bg-white/95 backdrop-blur-md">
  <div class="mx-auto flex min-h-[82px] max-w-[1200px] items-center gap-9 px-6 max-md:min-h-[72px] max-md:px-4">
    <a href="/" aria-label="DalyTrac, accueil" class="inline-flex items-baseline text-[1.65rem] font-extrabold tracking-[-0.065em] text-brand-primary no-underline max-sm:text-[1.42rem]"><span>Daly</span><strong class="font-extrabold text-brand-secondary">Trac</strong></a>

    <button type="button" @click="open = !open" :aria-expanded="open.toString()" aria-controls="main-nav" class="hidden border-0 bg-transparent p-2 text-brand-primary max-md:block" aria-label="{{ __('messages.public_nav_mobile_menu') }}">
      <span class="sr-only">{{ __('messages.public_nav_mobile_menu') }}</span>
      <i data-lucide="menu" class="h-6 w-6"></i>
    </button>

    <nav id="main-nav" aria-label="Navigation principale" class="ml-auto flex items-center gap-[34px] max-md:hidden">
      <a href="{{ route('produit') }}" @class([$navBase, $navActive => request()->routeIs('produit'), $navIdle => !request()->routeIs('produit')]) @if(request()->routeIs('produit')) aria-current="page" @endif>{{ __('messages.public_nav_product') }}</a>
      <a href="{{ route('tarifs') }}" @class([$navBase, $navActive => request()->routeIs('tarifs'), $navIdle => !request()->routeIs('tarifs')]) @if(request()->routeIs('tarifs')) aria-current="page" @endif>{{ __('messages.public_nav_pricing') }}</a>
      <a href="{{ route('demo') }}" @class([$navBase, $navActive => request()->routeIs('demo'), $navIdle => !request()->routeIs('demo')]) @if(request()->routeIs('demo')) aria-current="page" @endif>{{ __('messages.public_nav_demo') }}</a>
      <a href="{{ route('faq') }}" @class([$navBase, $navActive => request()->routeIs('faq'), $navIdle => !request()->routeIs('faq')]) @if(request()->routeIs('faq')) aria-current="page" @endif>{{ __('messages.public_nav_faq') }}</a>
      <a href="{{ route('contact') }}" @class([$navBase, $navActive => request()->routeIs('contact'), $navIdle => !request()->routeIs('contact')]) @if(request()->routeIs('contact')) aria-current="page" @endif>{{ __('messages.public_nav_contact') }}</a>
    </nav>

    <div class="flex items-center gap-[18px]">
      <div class="relative" x-data="{ localeOpen: false }">
        <button type="button" @click="localeOpen = !localeOpen" @click.away="localeOpen = false" class="flex items-center gap-2 rounded-full border border-[#dce6e4] bg-white px-2.5 py-1.5 text-[0.72rem] font-semibold text-brand-primary shadow-sm transition hover:border-brand-secondary hover:text-brand-secondary">
          @switch(app()->getLocale())
            @case('fr')
              <img src="https://flagcdn.com/fr.svg" alt="Français" class="h-3.5 w-5 rounded-sm object-cover">
              <span class="max-md:hidden">FR</span>
              @break
            @case('es')
              <img src="https://flagcdn.com/es.svg" alt="Español" class="h-3.5 w-5 rounded-sm object-cover">
              <span class="max-md:hidden">ES</span>
              @break
            @case('nl')
              <img src="https://flagcdn.com/be.svg" alt="Nederlands" class="h-3.5 w-5 rounded-sm object-cover">
              <span class="max-md:hidden">NL</span>
              @break
            @default
              <img src="https://flagcdn.com/gb.svg" alt="English" class="h-3.5 w-5 rounded-sm object-cover">
              <span class="max-md:hidden">EN</span>
          @endswitch
          <i data-lucide="chevron-down" class="h-3.5 w-3.5"></i>
        </button>

        <div x-show="localeOpen" x-cloak x-transition class="absolute right-0 z-50 mt-2 w-44 overflow-hidden rounded-xl border border-[#dce6e4] bg-white shadow-[0_16px_36px_rgba(7,27,50,0.12)]">
          <a href="{{ route('changeLang', ['lang' => 'en']) }}" class="flex items-center justify-between gap-2 px-3 py-2 text-sm font-medium text-[#3d5866] transition hover:bg-[#f3f7f6] {{ app()->getLocale() === 'en' ? 'bg-[#edf5f4] text-brand-primary' : '' }}">
            <span class="flex items-center gap-2"><img src="https://flagcdn.com/gb.svg" alt="English" class="h-3.5 w-5 rounded-sm object-cover"> English</span>
            @if(app()->getLocale() === 'en')<i data-lucide="check" class="h-4 w-4"></i>@endif
          </a>
          <a href="{{ route('changeLang', ['lang' => 'fr']) }}" class="flex items-center justify-between gap-2 px-3 py-2 text-sm font-medium text-[#3d5866] transition hover:bg-[#f3f7f6] {{ app()->getLocale() === 'fr' ? 'bg-[#edf5f4] text-brand-primary' : '' }}">
            <span class="flex items-center gap-2"><img src="https://flagcdn.com/fr.svg" alt="Français" class="h-3.5 w-5 rounded-sm object-cover"> Français</span>
            @if(app()->getLocale() === 'fr')<i data-lucide="check" class="h-4 w-4"></i>@endif
          </a>
          <a href="{{ route('changeLang', ['lang' => 'es']) }}" class="flex items-center justify-between gap-2 px-3 py-2 text-sm font-medium text-[#3d5866] transition hover:bg-[#f3f7f6] {{ app()->getLocale() === 'es' ? 'bg-[#edf5f4] text-brand-primary' : '' }}">
            <span class="flex items-center gap-2"><img src="https://flagcdn.com/es.svg" alt="Español" class="h-3.5 w-5 rounded-sm object-cover"> Español</span>
            @if(app()->getLocale() === 'es')<i data-lucide="check" class="h-4 w-4"></i>@endif
          </a>
          <a href="{{ route('changeLang', ['lang' => 'nl']) }}" class="flex items-center justify-between gap-2 px-3 py-2 text-sm font-medium text-[#3d5866] transition hover:bg-[#f3f7f6] {{ app()->getLocale() === 'nl' ? 'bg-[#edf5f4] text-brand-primary' : '' }}">
            <span class="flex items-center gap-2"><img src="https://flagcdn.com/be.svg" alt="Nederlands" class="h-3.5 w-5 rounded-sm object-cover"> Nederlands</span>
            @if(app()->getLocale() === 'nl')<i data-lucide="check" class="h-4 w-4"></i>@endif
          </a>
        </div>
      </div>

      <a href="{{ route('login') }}" class="text-[0.92rem] font-semibold text-[#3e5368] no-underline transition-colors duration-300 hover:text-brand-secondary max-md:hidden">{{ __('messages.public_nav_login') }}</a>
      <a href="{{ route('register') }}" class="inline-flex min-h-[44px] items-center justify-center gap-2.5 rounded-full border  btn-sweep btn-sweep-navy bg-brand-secondary px-[18px] text-[0.88rem] font-semibold text-white no-underline shadow-[0_12px_28px_rgba(45,112,128,0.2)] transition-all duration-300 ease-out hover:-translate-y-0.5 hover:scale-[1.02]  hover:shadow-[0_16px_34px_rgba(45,112,128,0.3)] cursor-pointer max-sm:min-h-[40px] max-sm:px-3.5 max-sm:text-[0.76rem]">{{ __('messages.public_nav_trial') }}</a>
    </div>
  </div>

  <nav x-show="open" x-cloak x-transition aria-label="Navigation mobile" class="absolute inset-x-0 top-full flex-col items-start gap-[18px] border-b border-[#dce6e4] bg-white px-6 py-5 shadow-[0_16px_28px_rgba(7,27,50,0.08)] max-md:flex lg:hidden">
    <a href="{{ route('produit') }}" @click="open = false" @class([$mobileBase, 'text-brand-secondary' => request()->routeIs('produit'), 'text-[#3e5368]' => !request()->routeIs('produit')])>{{ __('messages.public_nav_product') }}</a>
    <a href="{{ route('tarifs') }}" @click="open = false" @class([$mobileBase, 'text-brand-secondary' => request()->routeIs('tarifs'), 'text-[#3e5368]' => !request()->routeIs('tarifs')])>{{ __('messages.public_nav_pricing') }}</a>
    <a href="{{ route('demo') }}" @click="open = false" @class([$mobileBase, 'text-brand-secondary' => request()->routeIs('demo'), 'text-[#3e5368]' => !request()->routeIs('demo')])>{{ __('messages.public_nav_demo') }}</a>
    <a href="{{ route('faq') }}" @click="open = false" @class([$mobileBase, 'text-brand-secondary' => request()->routeIs('faq'), 'text-[#3e5368]' => !request()->routeIs('faq')])>{{ __('messages.public_nav_faq') }}</a>
    <a href="{{ route('contact') }}" @click="open = false" @class([$mobileBase, 'text-brand-secondary' => request()->routeIs('contact'), 'text-[#3e5368]' => !request()->routeIs('contact')])>{{ __('messages.public_nav_contact') }}</a>
    <a href="{{ route('login') }}" class="{{ $mobileBase }} text-[#3e5368]">{{ __('messages.public_nav_login') }}</a>
  </nav>
</header>

<script>
  if (!window.Alpine) {
    const alpineScript = document.createElement('script');
    alpineScript.src = 'https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js';
    alpineScript.defer = true;
    document.head.appendChild(alpineScript);
  }
</script>

{{-- Lucide Icons (pinned to the version in package.json). Renders every
     <i data-lucide="..."> into an inline SVG, styled via Tailwind classes. --}}
<script src="https://unpkg.com/lucide@1.47.0/dist/umd/lucide.min.js" defer></script>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    if (window.lucide) {
      lucide.createIcons();
    }
  });
</script>
