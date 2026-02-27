<header class="flex items-center justify-between px-6 py-3 bg-white border-b border-gray-200">
    <div>
        <h1 class="text-lg font-semibold text-gray-900">Manager area</h1>
        <p class="text-xs text-gray-500">Monitor subscriptions and entreprises</p>
    </div>
    
    {{-- Language Switcher --}}
    <div class="relative" x-data="{ open: false }">
        <button 
            @click="open = !open"
            @click.away="open = false"
            type="button"
            class="relative p-2.5 border border-gray-300 bg-white rounded-lg hover:bg-gray-50 transition-colors flex items-center gap-2"
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
            class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-50"
        >
            <div class="py-2">
                <a 
                    href="{{ route('changeLang', ['lang' => 'en']) }}"
                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors flex items-center gap-2 {{ app()->getLocale() === 'en' ? 'bg-blue-50 text-blue-700' : '' }}"
                >
                    <img src="https://flagcdn.com/gb.svg" alt="English" class="w-4 h-3 rounded-sm object-cover">
                    <span>English</span>
                    @if(app()->getLocale() === 'en')
                        <svg class="w-4 h-4 ml-auto" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                    @endif
                </a>
                <a 
                    href="{{ route('changeLang', ['lang' => 'fr']) }}"
                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors flex items-center gap-2 {{ app()->getLocale() === 'fr' ? 'bg-blue-50 text-blue-700' : '' }}"
                >
                    <img src="https://flagcdn.com/fr.svg" alt="Français" class="w-4 h-3 rounded-sm object-cover">
                    <span>Français</span>
                    @if(app()->getLocale() === 'fr')
                        <svg class="w-4 h-4 ml-auto" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                    @endif
                </a>
                <a 
                    href="{{ route('changeLang', ['lang' => 'es']) }}"
                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors flex items-center gap-2 {{ app()->getLocale() === 'es' ? 'bg-blue-50 text-blue-700' : '' }}"
                >
                    <img src="https://flagcdn.com/es.svg" alt="Español" class="w-4 h-3 rounded-sm object-cover">
                    <span>Español</span>
                    @if(app()->getLocale() === 'es')
                        <svg class="w-4 h-4 ml-auto" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                    @endif
                </a>
                <a 
                    href="{{ route('changeLang', ['lang' => 'nl']) }}"
                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors flex items-center gap-2 {{ app()->getLocale() === 'nl' ? 'bg-blue-50 text-blue-700' : '' }}"
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
</header>


