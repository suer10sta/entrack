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

<header class="flex items-center justify-between px-6 py-3 bg-white border-b border-gray-200">
    
    <div class="flex items-center w-full max-w-lg">
        <div class="relative w-full">
            <span class="absolute inset-y-0 left-4 flex items-center pointer-events-none text-gray-400">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
            </span>
            {{-- CHANGED: rounded-xl to rounded-lg, border to border-gray-300, focus:border-primary to focus:border-blue-500 --}}
            <input type="text" placeholder="{{ __('messages.search_placeholder') }}" 
                   class="w-full pl-11 pr-4 py-2.5 border rounded-lg bg-white focus:ring-0 focus:border-blue-500 text-sm border-gray-300" />
                   
                {{--<select onchange="window.location.href='{{ url('lang/change') }}?lang='+this.value">
                    <option value="en" {{ app()->getLocale() == 'en' ? 'selected' : '' }}>English</option>
                    <option value="fr" {{ app()->getLocale() == 'fr' ? 'selected' : '' }}>French</option>
                    <option value="es" {{ app()->getLocale() == 'es' ? 'selected' : '' }}>Spanish</option>
                </select>--}}
            </div>
        </div>

<div class="flex items-center gap-4 ml-6" x-data="alertDropdown({ initialCount: {{ $navbarAlertCount }} })" x-init="init()">
        <div class="relative">
            <button 
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
            </button>

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
                                        <p class="text-sm font-semibold text-gray-900">Event Reminder</p>
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
                                <span>{{ optional($alert->created_at)->diffForHumans() ?? 'Just now' }}</span>
                                @if(is_null($alert->read_at))
                                    <button 
                                        type="button"
                                        class="text-blue-600 font-semibold hover:underline"
                                        @click.stop.prevent="markAsRead({{ $alert->id }}, '{{ route('alerts.read', $alert) }}')"
                                    >
                                        Mark as read
                                    </button>
                                @else
                                    <span class="text-green-600 font-medium">Read</span>
                                @endif
                            </div>
                        </div>
                    @endforeach

                    <div 
                        class="px-4 py-8 text-center text-sm text-gray-500"
                        x-show="showEmptyState"
                        x-cloak
                    >
                        No notifications scheduled for the next hour.
                    </div>
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
