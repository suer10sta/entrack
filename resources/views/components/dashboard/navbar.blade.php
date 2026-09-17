@php
    use App\Models\Alert;
    use Illuminate\Support\Facades\Auth;
    use Carbon\Carbon;

    $navbarAlerts = collect();
    $navbarAlertCount = 0;

    if (Auth::check() && Auth::user()->entreprise_id) {
        Alert::syncUpcomingEventAlerts(Auth::user()->entreprise_id, Auth::id());

        $threshold = Carbon::now()->subHour();

        $navbarAlerts = Alert::query()
            ->where('entreprise_id', Auth::user()->entreprise_id)
            ->where('type', 'event')
            ->whereNull('read_at')
            ->where('trigger_time', '>=', $threshold)
            ->orderBy('trigger_time')
            ->limit(5)
            ->get();

        $navbarAlertCount = $navbarAlerts->count();
    }
@endphp

@if(Auth::check() && Auth::user()->entreprise_id && Auth::user()->entreprise && Auth::user()->entreprise->trial_ends_at)
        @php
            $entreprise = Auth::user()->entreprise;
            $isOnTrial = $entreprise->isOnTrial();
            $isExpired = $entreprise->trial_ends_at->isPast();
            $daysLeft = $isOnTrial ? (int) $entreprise->trial_ends_at->diffInDays(now()) : null;
        @endphp
        @if($isOnTrial && $daysLeft > 0)
            <div class="bg-amber-50 border-b border-amber-200 px-4 py-2 text-center">
                <span class="text-sm font-semibold text-amber-800">
                    ⏳ <strong>{{ $daysLeft }} jour{{ $daysLeft > 1 ? 's' : '' }}</strong> d'essai restant{{ $daysLeft > 1 ? 's' : '' }} avant la fin de votre période d'essai.
                    <a href="{{ route('profile.upgrade') }}" class="ml-2 text-amber-600 underline font-medium hover:text-amber-800">Mettre à niveau</a>
                </span>
            </div>
        @elseif($isExpired)
            <div class="bg-red-50 border-b border-red-200 px-4 py-2 text-center">
                <span class="text-sm font-semibold text-red-800">
                    ⚠️ Votre essai a expiré. <a href="{{ route('profile.upgrade') }}" class="text-red-600 underline font-medium hover:text-red-800">Choisissez un plan</a> pour continuer.
                </span>
            </div>
        @endif
    @endif

<header class="flex items-center justify-between px-4 sm:px-6 py-3 bg-white border-b border-gray-200">
    <div class="flex items-center gap-3 flex-1 min-w-0">
        {{-- Mobile hamburger (shows sidebar) --}}
        <button
            type="button"
            class="lg:hidden inline-flex h-10 w-10 items-center justify-center rounded-lg border border-gray-300 bg-white text-gray-700 hover:bg-gray-50"
            onclick="toggleAppSidebar(true)"
            aria-label="{{ __('messages.Open menu') }}"
        >
            <span class="sr-only">{{ __('messages.Open menu') }}</span>
            <div class="flex flex-col gap-1">
                <span class="block h-1 w-5 bg-gray-800"></span>
                <span class="block h-1 w-5 bg-gray-800"></span>
                <span class="block h-1 w-5 bg-gray-800"></span>
            </div>
        </button>

        <div class="flex items-center w-full max-w-lg min-w-0" x-data="searchDropdown()" x-init="init()">
        <div class="relative w-full">
            <span class="absolute inset-y-0 left-4 flex items-center pointer-events-none text-gray-400">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
            </span>
            <input 
                type="text" 
                placeholder="{{ __('messages.search_placeholder') }}" 
                x-model="query"
                @input.debounce.300ms="performSearch()"
                @focus="showResults = true"
                @keydown.escape="showResults = false"
                @keydown.arrow-down.prevent="navigateResults(1)"
                @keydown.arrow-up.prevent="navigateResults(-1)"
                @keydown.enter.prevent="selectResult()"
                class="w-full pl-11 pr-4 py-2.5 border rounded-lg bg-white focus:ring-0 focus:border-blue-500 text-sm border-gray-300" 
            />
            
            {{-- Search Results Dropdown --}}
            <div 
                x-show="showResults && (results.length > 0 || (query.length >= 2 && !loading))"
                @click.away="showResults = false"
                x-cloak
                x-transition:enter="transition ease-out duration-100"
                x-transition:enter-start="transform opacity-0 scale-95"
                x-transition:enter-end="transform opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-75"
                x-transition:leave-start="transform opacity-100 scale-100"
                x-transition:leave-end="transform opacity-0 scale-95"
                class="absolute z-50 w-full mt-2 bg-white rounded-lg shadow-lg border border-gray-200 max-h-96 overflow-y-auto"
            >
                <div x-show="loading" class="px-4 py-3 text-sm text-gray-500 text-center">
                    <span>{{ __('messages.Searching...') }}</span>
                </div>
                
                <div x-show="!loading && results.length === 0 && query.length >= 2" class="px-4 py-8 text-sm text-gray-500 text-center">
                    {{ __('messages.No results found for') }} "<span x-text="query"></span>"
                </div>
                
                <div x-show="!loading && results.length > 0" class="py-2">
                    <template x-for="(result, index) in results" :key="result.id + '-' + result.type">
                        <a 
                            :href="result.url"
                            @click="showResults = false"
                            :class="{
                                'bg-blue-50': selectedIndex === index,
                                'hover:bg-gray-50': selectedIndex !== index
                            }"
                            class="block px-4 py-3 border-b border-gray-100 last:border-b-0 transition-colors cursor-pointer"
                        >
                            <div class="flex items-start gap-3">
                                <div class="flex-shrink-0 mt-0.5">
                                    <div class="w-8 h-8 rounded-lg flex items-center justify-center"
                                         :class="{
                                             'bg-blue-100 text-blue-600': result.type === 'client',
                                             'bg-green-100 text-green-600': result.type === 'project',
                                             'bg-purple-100 text-purple-600': result.type === 'task',
                                             'bg-orange-100 text-orange-600': result.type === 'employee',
                                             'bg-indigo-100 text-indigo-600': result.type === 'team',
                                             'bg-red-100 text-red-600': result.type === 'invoice',
                                             'bg-yellow-100 text-yellow-600': result.type === 'devis'
                                         }">
                                        <svg x-show="result.type === 'client'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                        <svg x-show="result.type === 'project'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                                        </svg>
                                        <svg x-show="result.type === 'task'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <svg x-show="result.type === 'employee'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                        </svg>
                                        <svg x-show="result.type === 'team'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                        </svg>
                                        <svg x-show="result.type === 'invoice'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        <svg x-show="result.type === 'devis'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <p class="text-sm font-semibold text-gray-900 truncate" x-text="result.title"></p>
                                        <span
                                            x-show="result.archived"
                                            class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-red-100 text-red-800"
                                        >
                                            Archived
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-500 truncate" x-text="result.subtitle"></p>
                                    <span class="inline-block mt-1 text-xs px-2 py-0.5 rounded"
                                          :class="{
                                              'bg-blue-100 text-blue-700': result.type === 'client',
                                              'bg-green-100 text-green-700': result.type === 'project',
                                              'bg-purple-100 text-purple-700': result.type === 'task',
                                              'bg-orange-100 text-orange-700': result.type === 'employee',
                                              'bg-indigo-100 text-indigo-700': result.type === 'team',
                                              'bg-red-100 text-red-700': result.type === 'invoice',
                                              'bg-yellow-100 text-yellow-700': result.type === 'devis'
                                          }"
                                          x-text="result.type.charAt(0).toUpperCase() + result.type.slice(1)"></span>
                                </div>
                            </div>
                        </a>
                    </template>
                </div>
            </div>
        </div>
        </div>
    </div>

<div class="flex items-center gap-4 ml-6">
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

        <div class="relative" x-data="alertDropdown({ initialCount: {{ $navbarAlertCount }} })" x-init="init()">
         <!-- <button 
                @click="open = !open"
                type="button"
                class="relative p-2.5 border border-gray-300 bg-white rounded-lg hover:bg-gray-50 transition-colors"
            > 
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-600">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                </svg>
                <span 
                    x-show="count > 0"
                    x-text="count"
                    x-cloak
                    class="absolute top-0 right-0 block h-5 w-5 rounded-full bg-red-500 text-white text-xs font-semibold flex items-center justify-center transform translate-x-1/2 -translate-y-1/2"
                ></span>
            </button> -->

            <div 
                x-show="open"
                @click.away="open = false"
                x-cloak
                x-transition:enter="transition ease-out duration-100"
                x-transition:enter-start="transform opacity-0 scale-95"
                x-transition:enter-end="transform opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-75"
                x-transition:leave-start="transform opacity-100 scale-100"
                x-transition:leave-end="transform opacity-0 scale-95"
                class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border border-gray-200 z-50 max-h-96 overflow-y-auto"
            >

                <div class="py-2" x-ref="alertList">
                    @foreach($navbarAlerts as $alert)
                        <div 
                            id="alert-card-{{ $alert->id }}" 
                            data-alert-card
                            class="px-4 py-3 hover:bg-gray-50 border-b border-gray-100 last:border-b-0 transition-colors"
                        >
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-start gap-3">
                                    <div class="flex-shrink-0">
                                        <div class="w-10 h-10 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center">
                                            <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h3m6 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0z" />
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-semibold text-gray-900">{{ __('messages.Event Reminder') }}</p>
                                        <p class="text-sm text-gray-700">{{ $alert->message }}</p>
                                        <p class="text-xs text-gray-500 mt-1">
                                            {{ optional($alert->trigger_time)->format('H:i') ?? '—' }} · {{ $alert->title }}
                                        </p>
                                        @if(data_get($alert->metadata, 'description'))
                                            <p class="text-xs text-gray-500 mt-1 line-clamp-2">{{ data_get($alert->metadata, 'description') }}</p>
                                        @endif
                                    </div>
                                </div>
                                <button 
                                    type="button"
                                    class="text-gray-400 hover:text-gray-600"
                                    aria-label="Dismiss alert"
                                    @click.stop="deleteAlert({{ $alert->id }}, '{{ route('alerts.destroy', $alert) }}')"
                                >
                                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                            <div class="mt-3 flex items-center justify-between text-xs text-gray-500">
                                <span>{{ optional($alert->created_at)->diffForHumans() ?? __('messages.Just now') }}</span>
                                @if(is_null($alert->read_at))
                                    <button 
                                        type="button"
                                        class="text-blue-600 font-semibold hover:underline"
                                        @click.stop.prevent="markAsRead({{ $alert->id }}, '{{ route('alerts.read', $alert) }}')"
                                    >
                                        {{ __('messages.Mark as read') }}
                                    </button>
                                @else
                                    <span class="text-green-600 font-medium">{{ __('messages.Read') }}</span>
                                @endif
                            </div>
                        </div>
                    @endforeach

                    <!-- <div 
                        class="px-4 py-8 text-center text-sm text-gray-500"
                        x-show="showEmptyState"
                        x-cloak
                    >
                        No notifications scheduled for the next hour.
                    </div> -->
                </div>
            </div>
        </div>
    </div>
</header>

@php
    $hideTrialModalRoutes = [
        'profile.*',
        'subscription.*',
        'profile.index',
    ];
@endphp

@unless(request()->routeIs($hideTrialModalRoutes))
    <x-modals.trial-expired :show="$showTrialExpiredModal ?? false" />
@endunless

@once
    <script>
        if (!window.Alpine) {
            const alpineScript = document.createElement('script');
            alpineScript.src = 'https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js';
            alpineScript.defer = true;
            document.head.appendChild(alpineScript);
        }

        function searchDropdown() {
            return {
                query: '',
                results: [],
                loading: false,
                showResults: false,
                selectedIndex: -1,
                searchTimeout: null,
                init() {
                    this.query = '';
                    this.results = [];
                    this.loading = false;
                    this.showResults = false;
                    this.selectedIndex = -1;
                },
                async performSearch() {
                    if (this.query.length < 2) {
                        this.results = [];
                        this.showResults = false;
                        return;
                    }

                    this.loading = true;
                    this.selectedIndex = -1;

                    try {
                        const token = document.querySelector('meta[name="csrf-token"]')?.content;
                        const response = await fetch('{{ route("search") }}?q=' + encodeURIComponent(this.query), {
                            method: 'GET',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                ...(token ? { 'X-CSRF-TOKEN': token } : {})
                            }
                        });

                        if (!response.ok) {
                            throw new Error('Search request failed');
                        }

                        const data = await response.json();
                        this.results = data.results || [];
                        this.showResults = this.results.length > 0 || this.query.length >= 2;
                    } catch (error) {
                        console.error('Search error:', error);
                        this.results = [];
                    } finally {
                        this.loading = false;
                    }
                },
                navigateResults(direction) {
                    if (this.results.length === 0) return;
                    
                    this.selectedIndex += direction;
                    
                    if (this.selectedIndex < 0) {
                        this.selectedIndex = this.results.length - 1;
                    } else if (this.selectedIndex >= this.results.length) {
                        this.selectedIndex = 0;
                    }
                },
                selectResult() {
                    if (this.selectedIndex >= 0 && this.selectedIndex < this.results.length) {
                        window.location.href = this.results[this.selectedIndex].url;
                        this.showResults = false;
                    } else if (this.results.length > 0) {
                        window.location.href = this.results[0].url;
                        this.showResults = false;
                    }
                }
            };
        }

        function alertDropdown(config = {}) {
            return {
                open: false,
                count: Number(config.initialCount ?? 0),
                hasAlerts: (config.initialCount ?? 0) > 0,
                init() {
                    this.open = false;
                    this.updateAlertState();
                },
                async sendRequest(url, method = 'POST') {
                    const token = document.querySelector('meta[name="csrf-token"]')?.content;

                    if (!token) {
                        throw new Error('CSRF token not found.');
                    }

                    const response = await fetch(url, {
                        method,
                        headers: {
                            'X-CSRF-TOKEN': token,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({}),
                    });

                    if (!response.ok) {
                        throw new Error('Unable to update alert state.');
                    }
                },
                async markAsRead(id, url) {
                    try {
                        await this.sendRequest(url, 'POST');
                        this.removeAlertCard(id);
                    } catch (error) {
                        console.error(error);
                    }
                },
                async deleteAlert(id, url) {
                    try {
                        await this.sendRequest(url, 'DELETE');
                        this.removeAlertCard(id);
                    } catch (error) {
                        console.error(error);
                    }
                },
                removeAlertCard(id) {
                    const card = document.getElementById(`alert-card-${id}`);
                    if (card) {
                        card.remove();
                    }

                    if (this.count > 0) {
                        this.count -= 1;
                    }

                    this.updateAlertState();
                },
                updateAlertState() {
                    const list = this.$refs?.alertList?.querySelectorAll('[data-alert-card]') ?? [];
                    this.hasAlerts = list.length > 0;
                },
                get showEmptyState() {
                    return this.count <= 0 || !this.hasAlerts;
                },
            };
        }
    </script>
@endonce

<script>
    // Mobile sidebar toggle (shared by admin + employee sidebars)
    function toggleAppSidebar(forceOpen = null) {
        const sidebar = document.getElementById('appSidebar');
        const backdrop = document.getElementById('appSidebarBackdrop');
        if (!sidebar || !backdrop) return;

        const isOpen = !sidebar.classList.contains('hidden') && !sidebar.classList.contains('-translate-x-full');
        const shouldOpen = forceOpen === null ? !isOpen : Boolean(forceOpen);

        if (shouldOpen) {
            sidebar.classList.remove('hidden');
            // Ensure flex layout on mobile when shown
            sidebar.classList.add('flex');
            sidebar.classList.remove('-translate-x-full');
            sidebar.classList.add('translate-x-0');
            backdrop.classList.remove('hidden');
        } else {
            sidebar.classList.add('-translate-x-full');
            sidebar.classList.remove('translate-x-0');
            backdrop.classList.add('hidden');
            // After slide-out, fully hide so it never “sticks”
            window.setTimeout(() => {
                sidebar.classList.add('hidden');
                sidebar.classList.remove('flex');
            }, 200);
        }
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') toggleAppSidebar(false);
    });

    // Hard-close on initial load (mobile)
    document.addEventListener('DOMContentLoaded', function () {
        toggleAppSidebar(false);
    });
</script>
