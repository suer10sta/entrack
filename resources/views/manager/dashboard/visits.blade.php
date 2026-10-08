@extends('manager.layouts.app')

@section('content')
<div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-6">
    <div class="min-w-0">
        <p class="dt-overline mb-1">{{ __('messages.Manager area') }}</p>
        <h1 class="text-[22px] sm:text-[24px] font-bold tracking-tight text-[#0B2638] leading-tight">
            {{ $title }}
        </h1>
        <p class="text-[13.5px] text-slate-500 mt-1">{{ $subtitle }}</p>
    </div>
    <a href="{{ route('manager.dashboard') }}" class="dt-btn dt-btn-ghost flex-shrink-0">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
        </svg>
        {{ __('messages.Back') }}
    </a>
</div>

{{-- Filters --}}
<div class="dt-card p-4 sm:p-5 mb-6">
    <form method="GET" class="flex flex-wrap items-end gap-4">
        {{-- Country filter --}}
        <div class="flex flex-col gap-1 min-w-[140px]">
            <label class="text-[11px] font-semibold uppercase tracking-[0.07em] text-slate-500">{{ __('messages.Country') }}</label>
            <select name="country"
                    class="rounded-xl border border-slate-200 bg-white py-2 pl-3 pr-8 text-[13px] text-slate-700 focus:border-[#2D7080] focus:ring-1 focus:ring-[#2D7080]">
                <option value="">{{ __('messages.All') }}</option>
                @if(isset($unknownCountryToken))
                    <option value="{{ $unknownCountryToken }}" {{ $selectedCountry === $unknownCountryToken ? 'selected' : '' }}>
                        {{ __('messages.Unknown location') }}
                    </option>
                @endif
                @foreach($countries ?? [] as $c)
                    <option value="{{ $c }}" {{ $selectedCountry === $c ? 'selected' : '' }}>{{ $c }}</option>
                @endforeach
            </select>
        </div>

        {{-- Toggle filters --}}
        <div class="flex flex-wrap gap-x-5 gap-y-2 items-center pt-5">
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

        <div class="flex items-center gap-2 pt-5">
            <button type="submit" class="dt-btn dt-btn-teal">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
                </svg>
                {{ __('messages.Apply filters') }}
            </button>
            @if(array_filter($filters ?? []) || $selectedCountry)
                <a href="{{ route(request()->route()->getName()) }}" class="dt-btn dt-btn-ghost">
                    {{ __('messages.Clear') }}
                </a>
            @endif
        </div>
    </form>
</div>

{{-- Table --}}
<div class="dt-card overflow-hidden">
    <div class="manager-table-wrapper overflow-x-auto">
        <table class="dt-table manager-table min-w-full">
            <thead>
                <tr class="dt-table-head">
                    <th class="px-4 py-2.5">IP</th>
                    <th class="px-4 py-2.5">{{ __('messages.Country') }}</th>
                    <th class="px-4 py-2.5">{{ __('messages.Location') }}</th>
                    <th class="px-4 py-2.5">{{ __('messages.Visits (non client)') }}</th>
                </tr>
            </thead>
            <tbody>
                @php $currentCountry = null; @endphp
                @forelse($visitsList as $visit)
                    @php
                        $countryLabel = $visit->country ?? __('messages.Unknown location');
                        $ipUrl = isset($visit->ip)
                            ? route('manager.dashboard.visits.non-client-ip', array_merge(
                                ['ip' => $visit->ip],
                                request()->only(['exclude_bots','exclude_nojs','exclude_hosting','exclude_vpn','exclude_abnormal'])
                              ))
                            : null;
                    @endphp

                    {{-- Country group header --}}
                    @if($countryLabel !== $currentCountry)
                        <tr class="bg-slate-50/80">
                            <td colspan="4" class="px-4 py-2 text-[11px] font-semibold uppercase tracking-[0.07em] text-slate-500 border-b border-slate-100">
                                <div class="flex items-center gap-2">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#2D7080]"></span>
                                    {{ $countryLabel }}
                                </div>
                            </td>
                        </tr>
                        @php $currentCountry = $countryLabel; @endphp
                    @endif

                    <tr class="manager-card-row {{ $ipUrl ? 'cursor-pointer hover:bg-slate-50/70' : '' }}"
                        @if($ipUrl) onclick="window.location.href='{{ $ipUrl }}'" @endif
                        aria-expanded="true">
                        <td data-label="IP" data-span="2" data-manager-card-toggle class="manager-toggle-cell px-4 py-2.5">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    @if($ipUrl)
                                        <a href="{{ $ipUrl }}" class="text-[13px] font-mono font-medium text-[#2D7080] hover:text-[#0B2638] hover:underline" onclick="event.stopPropagation()">
                                            {{ $visit->ip ?? '—' }}
                                        </a>
                                    @else
                                        <span class="text-[13px] font-mono text-slate-700">{{ $visit->ip ?? '—' }}</span>
                                    @endif
                                </div>
                                <span class="shrink-0 mt-0.5 text-slate-400 min-[1366px]:hidden">
                                    <svg class="manager-chevron w-5 h-5" viewBox="0 0 24 24" fill="none"><path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </span>
                            </div>
                        </td>
                        <td data-label="{{ __('messages.Country') }}" class="px-4 py-2.5 text-[13px] text-slate-600">
                            {{ $visit->country ?? __('messages.Unknown location') }}
                        </td>
                        <td data-label="{{ __('messages.Location') }}" class="px-4 py-2.5 text-[13px] text-slate-500">
                            {{ $visit->city ?? '—' }}
                        </td>
                        <td data-label="{{ __('messages.Visits (non client)') }}" class="px-4 py-2.5 text-[13px] font-semibold text-slate-700">
                            {{ number_format($visit->visits_count ?? 0) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center">
                            <div class="dt-empty-icon mx-auto mb-3">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </div>
                            <p class="text-[13.5px] font-semibold text-[#0B2638]">{{ __('messages.No visit data yet.') }}</p>
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
