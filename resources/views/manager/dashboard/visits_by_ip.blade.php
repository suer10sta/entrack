@extends('manager.layouts.app')

@section('content')
<div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-6">
    <div class="min-w-0">
        <p class="dt-overline mb-1">{{ __('messages.Manager area') }}</p>
        <h1 class="text-[22px] sm:text-[24px] font-bold tracking-tight text-[#0B2638] leading-tight">
            {{ $title }}
        </h1>
        <p class="text-[13.5px] text-slate-500 mt-1">
            {{ $subtitle }} &mdash;
            <span class="font-semibold text-slate-700">{{ number_format($totalVisits) }}</span> {{ __('messages.visits total') }}
        </p>
    </div>
    <a href="{{ route('manager.dashboard.visits.non-client-unique', request()->only(['country','exclude_bots','exclude_nojs','exclude_hosting','exclude_vpn','exclude_abnormal'])) }}"
       class="dt-btn dt-btn-ghost flex-shrink-0">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
        </svg>
        {{ __('messages.Back to visitors') }}
    </a>
</div>

{{-- IP badge --}}
<div class="flex items-center gap-3 mb-6">
    <div class="dt-card inline-flex items-center gap-2.5 px-4 py-2.5">
        <div class="w-8 h-8 rounded-xl bg-[#2D7080]/10 text-[#2D7080] flex items-center justify-center flex-shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253"/>
            </svg>
        </div>
        <div>
            <p class="text-[11px] font-semibold uppercase tracking-[0.07em] text-slate-400">IP</p>
            <p class="text-[14px] font-bold text-[#0B2638] font-mono">{{ $ip }}</p>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="dt-card p-4 sm:p-5 mb-6">
    <form method="GET" class="flex flex-wrap items-end gap-4">
        <div class="flex flex-wrap gap-x-5 gap-y-2 items-center">
            @foreach([
                ['exclude_bots',     __('messages.Exclude known bots')],
                ['exclude_nojs',     __('messages.Exclude no-JS visits')],
                ['exclude_hosting',  __('messages.Exclude hosting IPs')],
                ['exclude_vpn',      __('messages.Exclude VPN IPs')],
                ['exclude_abnormal', __('messages.Exclude abnormal traffic')],
            ] as [$key, $label])
                <label class="inline-flex items-center gap-2 text-[13px] text-slate-700 cursor-pointer select-none">
                    <input type="hidden" name="{{ $key }}" value="0">
                    <input type="checkbox" name="{{ $key }}" value="1"
                           class="rounded border-slate-300 text-[#2D7080] focus:ring-[#2D7080]"
                           {{ ($filters[$key] ?? false) ? 'checked' : '' }}>
                    <span>{{ $label }}</span>
                </label>
            @endforeach
        </div>
        <div class="flex items-center gap-2">
            <button type="submit" class="dt-btn dt-btn-teal">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
                </svg>
                {{ __('messages.Apply filters') }}
            </button>
            @if(array_filter($filters ?? []))
                <a href="{{ route(request()->route()->getName(), ['ip' => $ip]) }}" class="dt-btn dt-btn-ghost">{{ __('messages.Clear') }}</a>
            @endif
        </div>
    </form>
</div>

{{-- Visits table --}}
<div class="dt-card overflow-hidden">
    <div class="manager-table-wrapper overflow-x-auto">
        <table class="dt-table manager-table min-w-full">
            <thead>
                <tr class="dt-table-head">
                    <th class="px-4 py-2.5">{{ __('messages.Date') }}</th>
                    <th class="px-4 py-2.5">{{ __('messages.Country') }}</th>
                    <th class="px-4 py-2.5">{{ __('messages.Location') }}</th>
                    <th class="px-4 py-2.5">{{ __('messages.Page') }}</th>
                    <th class="px-4 py-2.5">{{ __('messages.Duration') }}</th>
                    <th class="px-4 py-2.5">{{ __('messages.Device') }}</th>
                    <th class="px-4 py-2.5">{{ __('messages.Details') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($visitsList as $row)
                    @php
                        $rowId = 'visit-' . $row->id;
                        $page  = null;
                        if (!empty($row->url)) {
                            $page = parse_url($row->url, PHP_URL_PATH);
                        }
                        $page   = $page ?: ($row->url ?? null);
                        $dur    = $row->duration_seconds ?? null;
                        $durStr = $dur !== null ? gmdate('i:s', max(0, (int) $dur)) : null;
                    @endphp
                    <tr class="manager-card-row" aria-expanded="true">
                        <td data-label="{{ __('messages.Date') }}" data-span="2" data-manager-card-toggle class="manager-toggle-cell px-4 py-2.5">
                            <div class="flex items-start justify-between gap-3">
                                <span class="text-[13px] text-slate-700 font-medium tabular-nums">
                                    {{ $row->created_at ? \Carbon\Carbon::parse($row->created_at)->format('Y-m-d H:i:s') : '—' }}
                                </span>
                                <span class="shrink-0 mt-0.5 text-slate-400 min-[1366px]:hidden">
                                    <svg class="manager-chevron w-5 h-5" viewBox="0 0 24 24" fill="none"><path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </span>
                            </div>
                        </td>
                        <td data-label="{{ __('messages.Country') }}" class="px-4 py-2.5 text-[13px] text-slate-600">
                            {{ $row->country ?? __('messages.Unknown location') }}
                        </td>
                        <td data-label="{{ __('messages.Location') }}" class="px-4 py-2.5 text-[13px] text-slate-500">
                            {{ $row->city ?? '—' }}
                        </td>
                        <td data-label="{{ __('messages.Page') }}" data-span="2" class="px-4 py-2.5 text-[12.5px] text-slate-600 font-mono max-w-[220px] truncate" title="{{ $row->url ?? '' }}">
                            {{ $page ?: '—' }}
                        </td>
                        <td data-label="{{ __('messages.Duration') }}" class="px-4 py-2.5 text-[13px] text-slate-500 tabular-nums">
                            {{ $durStr ?? '—' }}
                        </td>
                        <td data-label="{{ __('messages.Device') }}" class="px-4 py-2.5">
                            @if($row->device_type)
                                <span class="dt-badge dt-badge-neutral capitalize">{{ $row->device_type }}</span>
                            @else
                                <span class="text-slate-400 text-[13px]">—</span>
                            @endif
                        </td>
                        <td data-label="{{ __('messages.Details') }}" class="px-4 py-2.5">
                            <button type="button"
                                    onclick="document.getElementById('{{ $rowId }}-details').classList.toggle('hidden'); this.querySelector('svg').classList.toggle('rotate-180');"
                                    class="dt-btn dt-btn-ghost !py-1 !px-2.5 !text-[12px]">
                                <svg class="w-3.5 h-3.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                                {{ __('messages.Info') }}
                            </button>
                        </td>
                    </tr>

                    {{-- Expandable details row --}}
                    <tr id="{{ $rowId }}-details" class="hidden bg-slate-50/60">
                        <td colspan="7" class="px-5 py-3">
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-x-6 gap-y-2 text-[12.5px]">
                                <div>
                                    <span class="font-semibold text-slate-500">{{ __('messages.Hosting:') }}</span>
                                    <span class="text-slate-700 ml-1">
                                        @if($row->is_hosting === null) —
                                        @elseif($row->is_hosting) {{ __('messages.Yes') }}
                                        @else {{ __('messages.No') }}
                                        @endif
                                    </span>
                                </div>
                                <div>
                                    <span class="font-semibold text-slate-500">{{ __('messages.Proxy:') }}</span>
                                    <span class="text-slate-700 ml-1">
                                        @if($row->is_proxy === null) —
                                        @elseif($row->is_proxy) {{ __('messages.Yes') }}
                                        @else {{ __('messages.No') }}
                                        @endif
                                    </span>
                                </div>
                                <div>
                                    <span class="font-semibold text-slate-500">{{ __('messages.Referrer:') }}</span>
                                    <span class="text-slate-700 ml-1 break-all">{{ $row->referrer ?? '—' }}</span>
                                </div>
                                <div>
                                    <span class="font-semibold text-slate-500">{{ __('messages.UTM Source:') }}</span>
                                    <span class="text-slate-700 ml-1">{{ $row->utm_source ?? '—' }}</span>
                                </div>
                                <div class="col-span-2 md:col-span-4">
                                    <span class="font-semibold text-slate-500">{{ __('messages.Full UA:') }}</span>
                                    <span class="text-slate-600 ml-1 break-all">{{ $row->user_agent ?? '—' }}</span>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <div class="dt-empty-icon mx-auto mb-3">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                                </svg>
                            </div>
                            <p class="text-[13.5px] font-semibold text-[#0B2638]">{{ __('messages.No visits found for this IP.') }}</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($visitsList->hasPages())
        <div class="px-5 sm:px-6 py-3 border-t border-slate-100 bg-[#f8fafc]">
            {{ $visitsList->withQueryString()->links() }}
        </div>
    @endif
</div>
@endsection
