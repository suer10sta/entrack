@extends('manager.layouts.app')

@section('content')
    <div class="space-y-5">
        <div>
            <p class="dt-overline mb-1">{{ __('messages.Manager area') }}</p>
            <h2 class="text-[22px] sm:text-[24px] font-bold tracking-tight text-[#0B2638] leading-tight">{{ __('messages.Overview') }}</h2>
            <p class="mt-1 text-[13.5px] text-slate-500">
                {{ __('messages.High-level metrics about entreprises and subscriptions.') }}
            </p>
        </div>

        <div class="grid grid-cols-2 xl:grid-cols-5 gap-4 md:gap-6">
            <a href="{{ route('manager.subscriptions.index') }}"
               class="flex-1 dt-stat dt-card-hover">
                <p class="dt-stat-label">{{ __('messages.Total entreprises') }}</p>
                <p class="dt-stat-value">{{ $totalEntreprises }}</p>
            </a>

            <a href="{{ route('manager.subscriptions.index', ['status' => 'active']) }}"
               class="flex-1 dt-stat dt-card-hover">
                <p class="dt-stat-label">{{ __('messages.Active subscriptions') }}</p>
                <p class="dt-stat-value">{{ $activeEntreprises }}</p>
            </a>

            <a href="{{ route('manager.dashboard.users') }}"
               class="flex-1 dt-stat dt-card-hover">
                <p class="dt-stat-label">{{ __('messages.Total users') }}</p>
                <p class="dt-stat-value">{{ $totalUsers }}</p>
            </a>

            <a href="{{ route('manager.dashboard.visits.non-client-unique') }}"
               class="flex-1 dt-stat dt-card-hover">
                <p class="dt-stat-label">{{ __('messages.Unique visitors (non client)') }}</p>
                <p class="dt-stat-value">{{ number_format($uniqueNonClientVisits) }}</p>
            </a>

            <div class="col-span-2 xl:col-span-1 dt-stat">
                <p class="dt-stat-label">{{ __('messages.Visit to signup conversion (30d)') }}</p>
                <p class="dt-stat-value">
                    {{ number_format($visitToSignupConversionRate, 1) }}%
                </p>
            </div>
        </div>

        {{-- Engagement summary --}}
        <div class="grid grid-cols-2 md:grid-cols-2 gap-4 md:gap-6 mt-6">
            <a href="{{ route('manager.dashboard.users.daily-active') }}"
               class="dt-stat dt-card-hover">
                <p class="dt-stat-label">
                    {{ __('messages.Daily active users (24h)') }}
                </p>
                <p class="dt-stat-value !text-[22px]">
                    {{ number_format($dau) }}
                </p>
            </a>

            <a href="{{ route('manager.dashboard.users.monthly-active') }}"
               class="dt-stat dt-card-hover">
                <p class="dt-stat-label">
                    {{ __('messages.Monthly active users (30d)') }}
                </p>
                <p class="dt-stat-value !text-[22px]">
                    {{ number_format($mau) }}
                </p>
            </a>
        </div>

        {{-- Signups, trials and onboarding summary --}}
        <div class="grid grid-cols-2 md:grid-cols-2 gap-4 md:gap-6">

            <a href="{{ route('manager.dashboard.users.new-signups') }}"
               class="dt-stat dt-card-hover">
                <p class="dt-stat-label">
                    {{ __('messages.New signups (30d)') }}
                </p>
                <p class="dt-stat-value !text-[22px]">
                    {{ number_format($newSignupsLast30) }}
                </p>
            </a>


            <div class="dt-stat">
                <p class="dt-stat-label">
                    {{ __('messages.Trial to paid conversion') }}
                </p>
                <p class="dt-stat-value !text-[22px]">
                    {{ number_format($trialToPaidConversionRate, 1) }}%
                </p>
            </div>
        </div>

        {{-- Earnings summary cards (from Stripe invoices) --}}
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4 md:gap-6">
            <a href="{{ route('manager.dashboard.invoices') }}"
               class="dt-stat dt-card-hover">
                <p class="dt-stat-label">{{ __('messages.Total invoiced') }}</p>
                <p class="dt-stat-value !text-[22px]">
                    {{ number_format($totalInvoiced, 2) }} EUR
                </p>
            </a>

            <div class="dt-stat">
                <p class="dt-stat-label">{{ __('messages.Total paid') }}</p>
                <p class="dt-stat-value !text-[22px]">
                    {{ number_format($totalPaid, 2) }} EUR
                </p>
            </div>

            <div class="col-span-2 md:col-span-1 dt-stat">
                <p class="dt-stat-label">{{ __('messages.Outstanding') }}</p>
                <p class="dt-stat-value !text-[22px]">
                    {{ number_format($totalOutstanding, 2) }} EUR  
                </p>
            </div>
        </div>

        {{-- Modern invoiced vs paid chart + Top visitor locations (world map) --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="dt-stat">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="dt-panel-title">{{ __('messages.Invoiced vs. paid (last 12 months)') }}</h3>
                        <p class="dt-panel-sub">
                            {{ __('messages.Visual overview of Stripe invoice amounts compared to payments.') }}
                        </p>
                    </div>
                </div>
                <div class="h-96 md:h-[28rem]">
                    <canvas id="manager-budget-chart"></canvas>
                </div>
            </div>

            <div class="dt-stat">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="dt-panel-title">{{ __('messages.Top visitor locations') }}</h3>
                        <p class="dt-panel-sub">
                            {{ __('messages.Based on recorded visits to the public website.') }}
                        </p>
                    </div>
                </div>

                <div class="manager-table-wrapper overflow-x-auto text-sm">
                    <table class="manager-table dt-table min-w-full text-left">
                        <thead class="border-b border-slate-100">
                        <tr>
                            <th class="px-4 py-2.5">{{ __('messages.Country') }}</th>
                            <th class="px-4 py-2.5">{{ __('messages.Visits (non client)') }}</th>
                            <th class="px-4 py-2.5">{{ __('messages.Unique visitors (non client)') }}</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                        @forelse($visitsByCountry as $row)
                            @php
                                $countryFilter = ($row->country ?? null) !== null && $row->country !== ''
                                    ? $row->country
                                    : '__unknown__';
                            @endphp
                            <tr class="manager-card-row" aria-expanded="true">
                                <td data-label="{{ __('messages.Country') }}" data-span="2" data-manager-card-toggle class="manager-toggle-cell px-4 py-2 text-gray-900">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <a href="{{ route('manager.dashboard.visits.non-client-unique', ['country' => $countryFilter]) }}" class="text-[#2D7080] hover:text-[#0B2638] hover:underline font-medium">
                                                {{ $row->country ?? __('messages.Unknown location') }}
                                            </a>
                                        </div>
                                        <span class="shrink-0 mt-0.5 text-slate-400 lg:hidden" aria-hidden="true">
                                            <svg class="manager-chevron w-5 h-5" viewBox="0 0 24 24" fill="none">
                                                <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </span>
                                    </div>
                                </td>
                                <td data-label="{{ __('messages.Visits (non client)') }}" class="px-4 py-2 text-gray-700">
                                    <a href="{{ route('manager.dashboard.visits.non-client-unique', ['country' => $countryFilter]) }}" class="text-[#2D7080] hover:text-[#0B2638] hover:underline font-medium">
                                        {{ number_format($row->total) }}
                                    </a>
                                </td>
                                <td data-label="{{ __('messages.Unique visitors (non client)') }}" class="px-4 py-2 text-gray-700">
                                    <a href="{{ route('manager.dashboard.visits.non-client-unique', ['country' => $countryFilter]) }}" class="text-[#2D7080] hover:text-[#0B2638] hover:underline font-medium">
                                        {{ number_format($row->unique_total) }}
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-4 text-center text-sm text-gray-500">
                                    {{ __('messages.No visit data yet.') }}
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    {{ $visitsByCountry->links() }}
                </div>
            </div>
        </div>

        {{-- Top traffic sources & Visits over time --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
            <div class="dt-stat">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="dt-panel-title">{{ __('messages.Top traffic sources') }}</h3>
                </div>
                <div class="manager-table-wrapper overflow-x-auto">
                    <table class="manager-table dt-table min-w-full text-left">
                        <thead class="border-b border-slate-100">
                        <tr>
                            <th class="px-4 py-2.5">{{ __('messages.Source') }}</th>
                            <th class="px-4 py-2.5">{{ __('messages.Visits (non client)') }}</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                        @forelse($topTrafficSources as $row)
                            <tr class="manager-card-row" aria-expanded="true">
                                <td data-label="{{ __('messages.Source') }}" data-span="2" data-manager-card-toggle class="manager-toggle-cell px-4 py-2 text-gray-900">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            {{ $row->source ?? '—' }}
                                        </div>
                                        <span class="shrink-0 mt-0.5 text-slate-400 lg:hidden" aria-hidden="true">
                                            <svg class="manager-chevron w-5 h-5" viewBox="0 0 24 24" fill="none">
                                                <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </span>
                                    </div>
                                </td>
                                <td data-label="{{ __('messages.Visits (non client)') }}" class="px-4 py-2 text-gray-700">
                                    {{ number_format($row->total) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="px-4 py-4 text-center text-sm text-gray-500">
                                    {{ __('messages.No visit data yet.') }}
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Visits over time by device (last 30 days) --}}
            <div class="dt-stat">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="dt-panel-title">{{ __('messages.Visits over time (30d) by device') }}</h3>
                </div>
                <div class="h-72">
                    <canvas id="manager-visits-device-chart"></canvas>
                </div>
            </div>
        </div>

    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const budgetCanvas = document.getElementById('manager-budget-chart');
            const visitsCanvas = document.getElementById('manager-visits-device-chart');
            const funnelCanvas = document.getElementById('manager-onboarding-funnel-chart');

            const labels = @json($chartMonths);
            const invoiced = @json($chartInvoiced);
            const paid = @json($chartPaid);

            const visitsOverTime = @json($visitsOverTime);
            const onboardingFunnel = @json($onboardingFunnel);

            const renderCharts = function () {
                if (budgetCanvas) {
                    const ctx = budgetCanvas.getContext('2d');

                    const gradientInvoiced = ctx.createLinearGradient(0, 0, 0, 300);
                    gradientInvoiced.addColorStop(0, 'rgba(45, 112, 128, 0.35)');
                    gradientInvoiced.addColorStop(1, 'rgba(45, 112, 128, 0)');

                    const gradientPaid = ctx.createLinearGradient(0, 0, 0, 300);
                    gradientPaid.addColorStop(0, 'rgba(231, 184, 75, 0.45)');
                    gradientPaid.addColorStop(1, 'rgba(231, 184, 75, 0)');

                    new window.Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: labels,
                            datasets: [
                                {
                                    label: '{{ __('messages.Invoiced') }}',
                                    data: invoiced,
                                    tension: 0.4,
                                    borderColor: '#2D7080',
                                    backgroundColor: gradientInvoiced,
                                    fill: true,
                                    borderWidth: 2,
                                    pointRadius: 3,
                                },
                                {
                                    label: '{{ __('messages.Paid') }}',
                                    data: paid,
                                    tension: 0.4,
                                    borderColor: '#C9971F',
                                    backgroundColor: gradientPaid,
                                    fill: true,
                                    borderWidth: 2,
                                    pointRadius: 3,
                                },
                            ],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    labels: {
                                        boxWidth: 12,
                                        usePointStyle: true,
                                    },
                                },
                                tooltip: {
                                    mode: 'index',
                                    intersect: false,
                                    callbacks: {
                                        label: function (context) {
                                            const value = context.parsed.y || 0;
                                            return `${context.dataset.label}: ${value.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })} EUR`;
                                        },
                                    },
                                },
                            },
                            interaction: {
                                mode: 'index',
                                intersect: false,
                            },
                            scales: {
                                x: {
                                    grid: {
                                        display: false,
                                    },
                                },
                                y: {
                                    beginAtZero: true,
                                    grid: {
                                        color: 'rgba(148, 163, 184, 0.2)',
                                    },
                                },
                            },
                        },
                    });
                }

                if (visitsCanvas && Array.isArray(visitsOverTime) && visitsOverTime.length > 0) {
                    const vctx = visitsCanvas.getContext('2d');

                    const visitLabels = visitsOverTime.map(row => row.date);
                    const desktop = visitsOverTime.map(row => row.desktop);
                    const mobile = visitsOverTime.map(row => row.mobile);
                    const tablet = visitsOverTime.map(row => row.tablet);
                    const unknown = visitsOverTime.map(row => row.unknown);

                    new window.Chart(vctx, {
                        type: 'bar',
                        data: {
                            labels: visitLabels,
                            datasets: [
                                {
                                    label: 'Desktop',
                                    data: desktop,
                                    borderColor: '#2D7080',
                                    backgroundColor: 'rgba(45, 112, 128, 0.15)',
                                    borderWidth: 2,
                                    borderRadius: 4,
                                },
                                {
                                    label: 'Mobile',
                                    data: mobile,
                                    borderColor: '#0B2638',
                                    backgroundColor: 'rgba(11, 38, 56, 0.12)',
                                    borderWidth: 2,
                                    borderRadius: 4,
                                },
                                {
                                    label: 'Tablet',
                                    data: tablet,
                                    borderColor: '#C9971F',
                                    backgroundColor: 'rgba(231, 184, 75, 0.20)',
                                    borderWidth: 2,
                                    borderRadius: 4,
                                },
                                {
                                    label: 'Unknown',
                                    data: unknown,
                                    borderColor: '#6b7280',
                                    backgroundColor: 'rgba(107, 114, 128, 0.1)',
                                    borderWidth: 1.5,
                                    borderRadius: 4,
                                },
                            ],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        boxWidth: 10,
                                        usePointStyle: true,
                                    },
                                },
                            },
                            interaction: {
                                mode: 'index',
                                intersect: false,
                            },
                            scales: {
                                x: {
                                    grid: {
                                        display: false,
                                    },
                                },
                                y: {
                                    beginAtZero: true,
                                    grid: {
                                        color: 'rgba(148, 163, 184, 0.2)',
                                    },
                                },
                            },
                        },
                    });
                }

                if (funnelCanvas && onboardingFunnel) {
                    const fctx = funnelCanvas.getContext('2d');

                    const funnelLabels = [
                        '{{ __('messages.Funnel visitors') }}',
                        '{{ __('messages.Funnel signups') }}',
                        '{{ __('messages.Funnel completed onboarding') }}',
                        '{{ __('messages.Funnel first key action') }}',
                        '{{ __('messages.Funnel paying customers') }}',
                    ];

                    const funnelValues = [
                        onboardingFunnel.visitors || 0,
                        onboardingFunnel.signups || 0,
                        onboardingFunnel.completedOnboarding || 0,
                        onboardingFunnel.firstKeyAction || 0,
                        onboardingFunnel.payingCustomers || 0,
                    ];

                    new window.Chart(fctx, {
                        type: 'bar',
                        data: {
                            labels: funnelLabels,
                            datasets: [
                                {
                                    label: '{{ __('messages.Funnel steps (count)') }}',
                                    data: funnelValues,
                                    backgroundColor: [
                                        'rgba(11, 38, 56, 0.75)',
                                        'rgba(45, 112, 128, 0.65)',
                                        'rgba(45, 112, 128, 0.40)',
                                        'rgba(231, 184, 75, 0.65)',
                                        'rgba(231, 184, 75, 0.35)',
                                    ],
                                    borderColor: [
                                        '#0B2638',
                                        '#2D7080',
                                        '#2D7080',
                                        '#C9971F',
                                        '#C9971F',
                                    ],
                                    borderWidth: 1,
                                    borderRadius: 6,
                                },
                            ],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false,
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function (context) {
                                            const value = context.parsed.y || 0;
                                            return value.toLocaleString();
                                        },
                                    },
                                },
                            },
                            scales: {
                                x: {
                                    grid: {
                                        display: false,
                                    },
                                },
                                y: {
                                    beginAtZero: true,
                                    grid: {
                                        color: 'rgba(148, 163, 184, 0.2)',
                                    },
                                    ticks: {
                                        precision: 0,
                                    },
                                },
                            },
                        },
                    });
                }
            };

            const loadScript = function (src) {
                return new Promise(function (resolve, reject) {
                    const script = document.createElement('script');
                    script.src = src;
                    script.async = true;
                    script.onload = function () { resolve(true); };
                    script.onerror = function () { reject(new Error('Failed to load ' + src)); };
                    document.head.appendChild(script);
                });
            };

            const ensureChartLibrary = async function () {
                if (typeof window.Chart !== 'undefined') {
                    return true;
                }

                try {
                    await loadScript("{{ asset('js/plugins/chartjs.min.js') }}");
                    if (typeof window.Chart !== 'undefined') {
                        return true;
                    }
                } catch (e) {
                    // Ignore and try CDN fallback.
                }

                try {
                    await loadScript('https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js');
                    return typeof window.Chart !== 'undefined';
                } catch (e) {
                    return false;
                }
            };

            ensureChartLibrary().then(function (ok) {
                if (ok) {
                    renderCharts();
                } else if (budgetCanvas) {
                    const fallbackMessage = document.createElement('p');
                    fallbackMessage.className = 'text-sm text-red-600';
                    fallbackMessage.textContent = '{{ __('messages.Unable to load chart library.') }}';
                    budgetCanvas.parentElement.appendChild(fallbackMessage);
                }
            });

        });
    </script>
@endpush







