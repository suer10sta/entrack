{{-- Employee navbar — mirrors x-dashboard.navbar (Admin reference) exactly:
     same sticky header, sidebar toggles, search, language switcher and alerts.
     Only difference: the Profile link and trial-upgrade banner render solely for
     roles allowed to access profile/subscription routes, so plain Employees keep
     their existing experience (Logout only, no dead-end links). --}}
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

    $canAccessProfile = Auth::check() && method_exists(Auth::user(), 'hasAnyRole') && Auth::user()->hasAnyRole(['Admin', 'SuperAdmin', 'super_admin', 'Responsable de projet', 'Responsable de projet et gestionnaire']);
@endphp

@if($canAccessProfile && Auth::user()->entreprise_id && Auth::user()->entreprise && Auth::user()->entreprise->trial_ends_at)
        @php
            $entreprise = Auth::user()->entreprise;
            $isOnTrial = $entreprise->isOnTrial();
            $isExpired = $entreprise->trial_ends_at->isPast();
            $daysLeft = $isOnTrial ? max(0, (int) ceil(now()->floatDiffInDays($entreprise->trial_ends_at))) : null;
        @endphp
        @if($isOnTrial && $daysLeft > 0)
            <div class="bg-[#E7B84B]/15 border-b border-[#E7B84B]/40 px-4 py-2">
                <p class="text-center text-[13px] font-semibold text-[#7a5a12]">
                    {{ __('messages.days_left_trial', ['days' => $daysLeft]) }} <a href="{{ route('profile.upgrade') }}" class="ml-2 inline-flex items-center gap-1 rounded-lg bg-[#0B2638] px-2.5 py-1 text-[12px] font-semibold text-white hover:bg-[#14364d] no-underline">{{ __('messages.upgrade') }}</a>
                </p>
            </div>
        @elseif($isExpired)
            <div class="bg-red-50 border-b border-red-200 px-4 py-2">
                <p class="text-center text-[13px] font-semibold text-red-800">
                    {{ __('messages.trial_expired') }} <a href="{{ route('profile.upgrade') }}" class="ml-2 inline-flex items-center gap-1 rounded-lg bg-red-600 px-2.5 py-1 text-[12px] font-semibold text-white hover:bg-red-700 no-underline">{{ __('messages.upgrade_plan') }}</a>
                </p>
            </div>
        @endif
    @endif

<header class="sticky top-0 z-30 flex items-center justify-between gap-3 px-4 sm:px-6 py-3 bg-white/90 backdrop-blur border-b border-slate-200">
    <div class="flex items-center gap-2.5 flex-1 min-w-0">
        {{-- Desktop collapse toggle --}}
        <button
            type="button"
            class="hidden lg:inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-[#0B2638] hover:bg-slate-50 hover:border-slate-300 transition-colors"
            onclick="toggleDesktopSidebar()"
            aria-label="Toggle sidebar"
            title="Collapse / Expand sidebar"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h10" />
            </svg>
        </button>
        {{-- Mobile hamburger (shows sidebar) --}}
        <button
            type="button"
            class="lg:hidden inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-[#0B2638] hover:bg-slate-50 transition-colors"
            onclick="toggleAppSidebar(true)"
            aria-label="{{ __('messages.Open menu') }}"
        >
            <span class="sr-only">{{ __('messages.Open menu') }}</span>
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h10" />
            </svg>
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
                class="w-full pl-11 pr-4 py-2.5 border rounded-xl bg-slate-50 text-sm border-slate-200 placeholder:text-slate-400 focus:bg-white focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none transition"
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
                                'bg-[#2D7080]/10': selectedIndex === index,
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
                                            {{ __('messages.Archived') }}
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

<div class="flex items-center gap-2.5 ml-4 flex-shrink-0">
        {{-- Language Switcher --}}
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
                class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-50"
            >
                <div class="py-2">
                    <a
                        href="{{ route('changeLang', ['lang' => 'en']) }}"
                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors flex items-center gap-2 {{ app()->getLocale() === 'en' ? 'bg-[#2D7080]/10 text-brand-secondary' : '' }}"
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
                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors flex items-center gap-2 {{ app()->getLocale() === 'fr' ? 'bg-[#2D7080]/10 text-brand-secondary' : '' }}"
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
                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors flex items-center gap-2 {{ app()->getLocale() === 'es' ? 'bg-[#2D7080]/10 text-brand-secondary' : '' }}"
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
                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors flex items-center gap-2 {{ app()->getLocale() === 'nl' ? 'bg-[#2D7080]/10 text-brand-secondary' : '' }}"
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
                                        <div class="w-10 h-10 rounded-2xl bg-[#2D7080]/10 text-brand-secondary flex items-center justify-center">
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
                                        class="text-brand-secondary font-semibold hover:underline"
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
                </div>
            </div>
        </div>
    </div>
</header>

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
    // Desktop collapse fallback (full implementation lives in sidebar component)
    if (typeof toggleDesktopSidebar === 'undefined') {
        function toggleDesktopSidebar(force) {
            const collapsed = typeof force === 'boolean' ? force : !document.body.classList.contains('dt-sidebar-collapsed');
            document.body.classList.toggle('dt-sidebar-collapsed', collapsed);
            try { localStorage.setItem('dalytrac-sidebar-collapsed', collapsed ? '1' : '0'); } catch (e) {}
        }
        try {
            if (localStorage.getItem('dalytrac-sidebar-collapsed') === '1' && window.innerWidth >= 1024) {
                document.body.classList.add('dt-sidebar-collapsed');
            }
        } catch (e) {}
    }

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
