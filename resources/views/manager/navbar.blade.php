<header class="dt-app-navbar sticky top-0 z-30 flex items-center justify-between gap-3 px-4 sm:px-6 py-3 bg-white/90 backdrop-blur border-b border-slate-200">
    <div class="flex items-center gap-2.5 flex-1 min-w-0">
        {{-- Desktop collapse toggle --}}
        <button
            type="button"
            class="hidden lg:inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-[#0B2638] hover:bg-slate-50 hover:border-slate-300 transition-colors"
            onclick="toggleManagerCollapse()"
            aria-label="Toggle sidebar"
            title="Collapse / Expand sidebar"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h10" />
            </svg>
        </button>
        {{-- Mobile hamburger --}}
        <button
            type="button"
            class="lg:hidden inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-[#0B2638] hover:bg-slate-50 transition-colors"
            onclick="toggleManagerSidebar(true)"
            aria-label="{{ __('messages.Open menu') }}"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h10" />
            </svg>
        </button>

        {{-- Page title area --}}
        <div class="min-w-0 hidden sm:block">
            <h1 class="text-[15px] font-bold tracking-tight text-[#0B2638] leading-tight truncate">
                {{ __('messages.Manager') }}
            </h1>
            <p class="text-[12px] text-slate-500 truncate">{{ __('messages.Monitor subscriptions and entreprises') }}</p>
        </div>
    </div>

    <div class="flex items-center gap-2.5 ml-4 flex-shrink-0">
        {{-- Language switcher --}}
        <div class="relative" x-data="{ open: false }">
            <button
                @click="open = !open"
                @click.away="open = false"
                type="button"
                class="relative p-2.5 border border-slate-200 bg-white rounded-xl hover:bg-slate-50 hover:border-slate-300 transition-colors flex items-center gap-2 shadow-sm"
                title="{{ __('messages.Language') }}"
            >
                <span class="text-sm font-medium text-gray-700 uppercase flex items-center gap-1">
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
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-gray-400">
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
                class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-gray-200 z-50"
            >
                <div class="py-2">
                    <a href="{{ route('changeLang', ['lang' => 'en']) }}"
                       class="block px-4 py-2 text-sm text-gray-700 hover:bg-slate-50 transition-colors flex items-center gap-2 {{ app()->getLocale() === 'en' ? 'bg-[#2D7080]/10 text-[#2D7080]' : '' }}">
                        <img src="https://flagcdn.com/gb.svg" alt="English" class="w-4 h-3 rounded-sm object-cover">
                        <span>English</span>
                        @if(app()->getLocale() === 'en')
                            <svg class="w-4 h-4 ml-auto" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        @endif
                    </a>
                    <a href="{{ route('changeLang', ['lang' => 'fr']) }}"
                       class="block px-4 py-2 text-sm text-gray-700 hover:bg-slate-50 transition-colors flex items-center gap-2 {{ app()->getLocale() === 'fr' ? 'bg-[#2D7080]/10 text-[#2D7080]' : '' }}">
                        <img src="https://flagcdn.com/fr.svg" alt="Français" class="w-4 h-3 rounded-sm object-cover">
                        <span>Français</span>
                        @if(app()->getLocale() === 'fr')
                            <svg class="w-4 h-4 ml-auto" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        @endif
                    </a>
                    <a href="{{ route('changeLang', ['lang' => 'es']) }}"
                       class="block px-4 py-2 text-sm text-gray-700 hover:bg-slate-50 transition-colors flex items-center gap-2 {{ app()->getLocale() === 'es' ? 'bg-[#2D7080]/10 text-[#2D7080]' : '' }}">
                        <img src="https://flagcdn.com/es.svg" alt="Español" class="w-4 h-3 rounded-sm object-cover">
                        <span>Español</span>
                        @if(app()->getLocale() === 'es')
                            <svg class="w-4 h-4 ml-auto" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        @endif
                    </a>
                    <a href="{{ route('changeLang', ['lang' => 'nl']) }}"
                       class="block px-4 py-2 text-sm text-gray-700 hover:bg-slate-50 transition-colors flex items-center gap-2 {{ app()->getLocale() === 'nl' ? 'bg-[#2D7080]/10 text-[#2D7080]' : '' }}">
                        <img src="https://flagcdn.com/be.svg" alt="Nederlands" class="w-4 h-3 rounded-sm object-cover">
                        <span>{{ __('messages.Nederlands') }}</span>
                        @if(app()->getLocale() === 'nl')
                            <svg class="w-4 h-4 ml-auto" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        @endif
                    </a>
                </div>
            </div>
        </div>

        {{-- Profile avatar button --}}
        @php $mgr = auth('manager')->user(); @endphp
        <a href="{{ route('manager.profile.index') }}"
           class="flex-shrink-0 w-9 h-9 rounded-full bg-[#E7B84B] flex items-center justify-center text-[#0B2638] text-[13px] font-bold hover:ring-2 hover:ring-[#E7B84B]/60 transition-all"
           title="{{ $mgr?->name ?? 'Manager' }}">
            {{ strtoupper(substr(trim($mgr?->name ?? 'M'), 0, 1)) }}
        </a>
    </div>
</header>

@once
    <script>
        if (!window.Alpine) {
            const s = document.createElement('script');
            s.src = 'https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js';
            s.defer = true;
            document.head.appendChild(s);
        }
    </script>
@endonce
