@extends('manager.layouts.app')

@section('content')
{{-- Page heading --}}
<div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-6">
    <div class="min-w-0">
        <p class="dt-overline mb-1">{{ __('messages.Manager area') }}</p>
        <h1 class="text-[22px] sm:text-[24px] font-bold tracking-tight text-[#0B2638] leading-tight">
            {{ __('messages.Overview') }}
        </h1>
        <p class="text-[13.5px] text-slate-500 mt-1">{{ __('messages.High-level metrics about entreprises and subscriptions.') }}</p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('manager.subscriptions.index') }}" class="dt-btn dt-btn-teal">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.598 1M12 8V6m0 10v2m8-8a8 8 0 11-16 0 8 8 0 0116 0z"/>
            </svg>
            {{ __('messages.Subscriptions') }}
        </a>
        <a href="{{ route('manager.blog.index') }}" class="dt-btn dt-btn-ghost">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 20h9M16.5 3.5a2.121 2.121 0 013 3L9 17l-4 1 1-4 10.5-10.5z"/>
            </svg>
            {{ __('messages.Blog') }}
        </a>
    </div>
</div>

{{-- Top stat cards --}}
<div class="grid grid-cols-2 xl:grid-cols-5 gap-4">

    {{-- Total entreprises --}}
    <a href="{{ route('manager.dashboard.entreprises') }}" class="dt-card dt-card-hover p-4 flex items-center gap-3.5 no-underline">
        <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-[#0B2638]/[0.06] text-[#0B2638] ring-1 ring-[#0B2638]/10 flex-shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/>
            </svg>
        </div>
        <div class="min-w-0 flex-1">
            <p class="dt-stat-label">{{ __('messages.Total entreprises') }}</p>
            <p class="dt-stat-value">{{ number_format($totalEntreprises) }}</p>
        </div>
    </a>

    {{-- Active subscriptions --}}
    <a href="{{ route('manager.dashboard.active-subscriptions') }}" class="dt-card dt-card-hover p-4 flex items-center gap-3.5 no-underline">
        <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 ring-1 ring-emerald-600/15 flex-shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/>
            </svg>
        </div>
        <div class="min-w-0 flex-1">
            <p class="dt-stat-label">{{ __('messages.Active subscriptions') }}</p>
            <p class="dt-stat-value">{{ number_format($activeEntreprises) }}</p>
        </div>
    </a>

    {{-- Total users --}}
    <a href="{{ route('manager.dashboard.users') }}" class="dt-card dt-card-hover p-4 flex items-center gap-3.5 no-underline">
        <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-[#0B2638]/[0.06] text-[#0B2638] ring-1 ring-[#0B2638]/10 flex-shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <circle cx="9" cy="8" r="3.5"/>
                <path d="M3.5 20c0-3.3 2.5-5 5.5-5s5.5 1.7 5.5 5" stroke-linecap="round"/>
                <path d="M16 5.6a3.5 3.5 0 010 6.5M17.7 15.4c1.9.7 2.8 2 2.8 4.1" stroke-linecap="round"/>
            </svg>
        </div>
        <div class="min-w-0 flex-1">
            <p class="dt-stat-label">{{ __('messages.Total users') }}</p>
            <p class="dt-stat-value">{{ number_format($totalUsers) }}</p>
        </div>
    </a>

    {{-- Unique visitors --}}
    <a href="{{ route('manager.dashboard.visits.non-client-unique') }}" class="dt-card dt-card-hover p-4 flex items-center gap-3.5 no-underline">
        <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-[#2D7080]/[0.08] text-[#2D7080] ring-1 ring-[#2D7080]/15 flex-shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
        </div>
        <div class="min-w-0 flex-1">
            <p class="dt-stat-label">{{ __('messages.Unique visitors (non client)') }}</p>
            <p class="dt-stat-value">{{ number_format($uniqueNonClientVisits) }}</p>
        </div>
    </a>

    {{-- Conversion rate --}}
    <div class="col-span-2 xl:col-span-1 dt-card p-4 flex items-center gap-3.5">
        <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-[#E7B84B]/20 text-[#0B2638] ring-1 ring-[#E7B84B]/40 flex-shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941"/>
            </svg>
        </div>
        <div class="min-w-0 flex-1">
            <p class="dt-stat-label">{{ __('messages.Visit to signup conversion (30d)') }}</p>
            <p class="dt-stat-value">{{ number_format($visitToSignupConversionRate, 1) }}%</p>
        </div>
    </div>
</div>

{{-- Engagement row --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4">

    <a href="{{ route('manager.dashboard.users.daily-active') }}" class="dt-card dt-card-hover p-4 no-underline">
        <p class="dt-stat-label">{{ __('messages.Daily active users (24h)') }}</p>
        <p class="dt-stat-value !text-[22px] mt-1">{{ number_format($dau) }}</p>
    </a>

    <a href="{{ route('manager.dashboard.users.monthly-active') }}" class="dt-card dt-card-hover p-4 no-underline">
        <p class="dt-stat-label">{{ __('messages.Monthly active users (30d)') }}</p>
        <p class="dt-stat-value !text-[22px] mt-1">{{ number_format($mau) }}</p>
    </a>

    <a href="{{ route('manager.dashboard.users.new-signups') }}" class="dt-card dt-card-hover p-4 no-underline">
        <p class="dt-stat-label">{{ __('messages.New signups (30d)') }}</p>
        <p class="dt-stat-value !text-[22px] mt-1">{{ number_format($newSignupsLast30) }}</p>
    </a>

    <div class="dt-card p-4">
        <p class="dt-stat-label">{{ __('messages.Trial to paid conversion') }}</p>
        <p class="dt-stat-value !text-[22px] mt-1">{{ number_format($trialToPaidConversionRate, 1) }}%</p>
    </div>
</div>

{{-- Revenue cards --}}
<div class="grid grid-cols-2 md:grid-cols-3 gap-4 mt-4">
    <a href="{{ route('manager.dashboard.invoices') }}"
       class="dt-card dt-card-hover p-4 flex items-center gap-3 relative overflow-hidden no-underline">
        <span class="absolute left-0 top-0 h-full w-1 bg-[#E7B84B]"></span>
        <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-[#E7B84B]/20 text-[#0B2638] ring-1 ring-[#E7B84B]/40 flex-shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
        </div>
        <div class="min-w-0 flex-1">
            <p class="dt-stat-label">{{ __('messages.Total invoiced') }}</p>
            <p class="dt-stat-value !text-[20px] mt-0.5">{{ number_format($totalInvoiced, 2) }} EUR</p>
        </div>
    </a>

    <div class="dt-card p-4 flex items-center gap-3">
        <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 ring-1 ring-emerald-600/15 flex-shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z"/>
            </svg>
        </div>
        <div class="min-w-0 flex-1">
            <p class="dt-stat-label">{{ __('messages.Total paid') }}</p>
            <p class="dt-stat-value !text-[20px] mt-0.5">{{ number_format($totalPaid, 2) }} EUR</p>
        </div>
    </div>

    <div class="col-span-2 md:col-span-1 dt-card p-4 flex items-center gap-3">
        <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-amber-50 text-amber-600 ring-1 ring-amber-600/15 flex-shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div class="min-w-0 flex-1">
            <p class="dt-stat-label">{{ __('messages.Outstanding') }}</p>
            <p class="dt-stat-value !text-[20px] mt-0.5">{{ number_format($totalOutstanding, 2) }} EUR</p>
        </div>
    </div>
</div>

{{-- Charts row --}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">

    {{-- Invoiced vs Paid chart --}}
    <div class="dt-card p-5">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="dt-panel-title">{{ __('messages.Invoiced vs. paid (last 12 months)') }}</h3>
                <p class="dt-panel-sub">{{ __('messages.Visual overview of Stripe invoice amounts compared to payments.') }}</p>
            </div>
        </div>
        <div class="h-72">
            <canvas id="manager-budget-chart"></canvas>
        </div>
    </div>

    {{-- Top visitor locations --}}
    <div class="dt-card p-5">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="dt-panel-title">{{ __('messages.Top visitor locations') }}</h3>
                <p class="dt-panel-sub">{{ __('messages.Based on recorded visits to the public website.') }}</p>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="dt-table min-w-full">
                <thead class="border-b border-slate-100">
                    <tr class="dt-table-head">
                        <th class="px-4 py-2.5">{{ __('messages.Country') }}</th>
                        <th class="px-4 py-2.5">{{ __('messages.Visits (non client)') }}</th>
                        <th class="px-4 py-2.5">{{ __('messages.Unique visitors (non client)') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($visitsByCountry as $row)
                        @php
                            $countryFilter = ($row->country ?? null) !== null && $row->country !== ''
                                ? $row->country : '__unknown__';
                        @endphp
                        <tr>
                            <td class="px-4 py-2.5">
                                <a href="{{ route('manager.dashboard.visits.non-client-unique', ['country' => $countryFilter]) }}"
                                   class="text-[#2D7080] hover:text-[#0B2638] hover:underline font-medium text-[13px]">
                                    {{ $row->country ?? __('messages.Unknown location') }}
                                </a>
                            </td>
                            <td class="px-4 py-2.5 text-[13px]">{{ number_format($row->total) }}</td>
                            <td class="px-4 py-2.5 text-[13px]">{{ number_format($row->unique_total) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-4 py-8 text-center">
                                <div class="dt-empty-icon mx-auto mb-3">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                                    </svg>
                                </div>
                                <p class="text-[13px] font-semibold text-[#0B2638]">{{ __('messages.No visit data yet.') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($visitsByCountry->hasPages())
            <div class="mt-4">{{ $visitsByCountry->links() }}</div>
        @endif
    </div>
</div>

{{-- Traffic sources & Visits over time --}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">

    {{-- Top traffic sources --}}
    <div class="dt-card p-5">
        <h3 class="dt-panel-title mb-4">{{ __('messages.Top traffic sources') }}</h3>
        <div class="overflow-x-auto">
            <table class="dt-table min-w-full">
                <thead class="border-b border-slate-100">
                    <tr class="dt-table-head">
                        <th class="px-4 py-2.5">{{ __('messages.Source') }}</th>
                        <th class="px-4 py-2.5">{{ __('messages.Visits (non client)') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topTrafficSources as $row)
                        <tr>
                            <td class="px-4 py-2.5 text-[13px] font-medium text-slate-700">{{ $row->source ?? '—' }}</td>
                            <td class="px-4 py-2.5 text-[13px] text-slate-600">{{ number_format($row->total) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="px-4 py-6 text-center text-[13px] text-slate-500">{{ __('messages.No visit data yet.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Visits over time by device --}}
    <div class="dt-card p-5">
        <h3 class="dt-panel-title mb-4">{{ __('messages.Visits over time (30d) by device') }}</h3>
        <div class="h-64">
            <canvas id="manager-visits-device-chart"></canvas>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const budgetCanvas = document.getElementById('manager-budget-chart');
    const visitsCanvas = document.getElementById('manager-visits-device-chart');
    const labels     = @json($chartMonths);
    const invoiced   = @json($chartInvoiced);
    const paid       = @json($chartPaid);
    const visitsOverTime = @json($visitsOverTime);

    const loadScript = src => new Promise((res, rej) => {
        const s = document.createElement('script');
        s.src = src; s.async = true;
        s.onload = () => res(true); s.onerror = () => rej();
        document.head.appendChild(s);
    });

    async function ensureChart() {
        if (window.Chart) return true;
        try { await loadScript("{{ asset('js/plugins/chartjs.min.js') }}"); if (window.Chart) return true; } catch(e){}
        try { await loadScript('https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js'); return !!window.Chart; } catch(e){}
        return false;
    }

    ensureChart().then(ok => {
        if (!ok) return;

        // Budget chart
        if (budgetCanvas) {
            const ctx = budgetCanvas.getContext('2d');
            const gi = ctx.createLinearGradient(0,0,0,300);
            gi.addColorStop(0,'rgba(45,112,128,0.35)'); gi.addColorStop(1,'rgba(45,112,128,0)');
            const gp = ctx.createLinearGradient(0,0,0,300);
            gp.addColorStop(0,'rgba(231,184,75,0.45)'); gp.addColorStop(1,'rgba(231,184,75,0)');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels,
                    datasets: [
                        { label: @js(__('messages.Invoiced')), data: invoiced, tension:0.4, borderColor:'#2D7080', backgroundColor:gi, fill:true, borderWidth:2, pointRadius:3 },
                        { label: @js(__('messages.Paid')),     data: paid,     tension:0.4, borderColor:'#C9971F', backgroundColor:gp, fill:true, borderWidth:2, pointRadius:3 },
                    ],
                },
                options: {
                    responsive:true, maintainAspectRatio:false,
                    plugins: { legend:{ labels:{ boxWidth:12, usePointStyle:true } }, tooltip:{ mode:'index', intersect:false, callbacks:{ label: c => `${c.dataset.label}: ${(c.parsed.y||0).toLocaleString(undefined,{minimumFractionDigits:2})} EUR` } } },
                    interaction:{ mode:'index', intersect:false },
                    scales:{ x:{ grid:{ display:false } }, y:{ beginAtZero:true, grid:{ color:'rgba(148,163,184,0.2)' } } },
                },
            });
        }

        // Visits by device chart
        if (visitsCanvas && Array.isArray(visitsOverTime) && visitsOverTime.length) {
            const vctx = visitsCanvas.getContext('2d');
            new Chart(vctx, {
                type: 'bar',
                data: {
                    labels: visitsOverTime.map(r => r.date),
                    datasets: [
                        { label:'Desktop', data: visitsOverTime.map(r=>r.desktop), borderColor:'#2D7080', backgroundColor:'rgba(45,112,128,0.15)', borderWidth:2, borderRadius:4 },
                        { label:'Mobile',  data: visitsOverTime.map(r=>r.mobile),  borderColor:'#0B2638', backgroundColor:'rgba(11,38,56,0.12)',    borderWidth:2, borderRadius:4 },
                        { label:'Tablet',  data: visitsOverTime.map(r=>r.tablet),  borderColor:'#C9971F', backgroundColor:'rgba(231,184,75,0.20)',  borderWidth:2, borderRadius:4 },
                        { label:'Unknown', data: visitsOverTime.map(r=>r.unknown), borderColor:'#6b7280', backgroundColor:'rgba(107,114,128,0.1)',  borderWidth:1.5, borderRadius:4 },
                    ],
                },
                options: {
                    responsive:true, maintainAspectRatio:false,
                    plugins:{ legend:{ position:'bottom', labels:{ boxWidth:10, usePointStyle:true } } },
                    interaction:{ mode:'index', intersect:false },
                    scales:{ x:{ grid:{ display:false } }, y:{ beginAtZero:true, grid:{ color:'rgba(148,163,184,0.2)' } } },
                },
            });
        }
    });
});
</script>
@endpush
