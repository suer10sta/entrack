@extends('manager.layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-[22px] font-bold tracking-tight text-[#0B2638]">{{ $title }}</h2>
                <p class="mt-1 text-sm text-gray-500">{{ $subtitle }}</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('manager.dashboard') }}"
                   class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-xs font-medium rounded text-gray-700 hover:bg-gray-50">
                    {{ __('messages.Back to list') }}
                </a>
            </div>
        </div>

        <div class="dt-card">
            {{-- Filters --}}
            <div class="p-4 border-b border-slate-100/50">
                <form method="GET" class="flex flex-wrap items-center gap-x-6 gap-y-3">
                    <div class="flex items-center gap-2">
                        <label class="text-sm text-gray-600">{{ __('messages.Country') }}:</label>
                        <select name="country"
                                class="text-sm border border-gray-300 rounded px-2 py-1 focus:ring-[#2D7080] focus:border-[#2D7080]">
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
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700 cursor-pointer select-none">
                        <input type="hidden" name="exclude_bots" value="0">
                        <input type="checkbox" name="exclude_bots" value="1" class="rounded border-gray-300 text-[#2D7080] shadow-sm focus:ring-[#2D7080]" {{ ($filters['exclude_bots'] ?? false) ? 'checked' : '' }}>
                        <span>{{ __('messages.Exclude known bots') }}</span>
                    </label>
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700 cursor-pointer select-none">
                        <input type="hidden" name="exclude_nojs" value="0">
                        <input type="checkbox" name="exclude_nojs" value="1" class="rounded border-gray-300 text-[#2D7080] shadow-sm focus:ring-[#2D7080]" {{ ($filters['exclude_nojs'] ?? false) ? 'checked' : '' }}>
                        <span>{{ __('messages.Exclude no-JS visits') }}</span>
                    </label>
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700 cursor-pointer select-none">
                        <input type="hidden" name="exclude_hosting" value="0">
                        <input type="checkbox" name="exclude_hosting" value="1" class="rounded border-gray-300 text-[#2D7080] shadow-sm focus:ring-[#2D7080]" {{ ($filters['exclude_hosting'] ?? false) ? 'checked' : '' }}>
                        <span>{{ __('messages.Exclude hosting IPs') }}</span>
                    </label>
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700 cursor-pointer select-none">
                        <input type="hidden" name="exclude_vpn" value="0">
                        <input type="checkbox" name="exclude_vpn" value="1" class="rounded border-gray-300 text-[#2D7080] shadow-sm focus:ring-[#2D7080]" {{ ($filters['exclude_vpn'] ?? false) ? 'checked' : '' }}>
                        <span>{{ __('messages.Exclude VPN IPs') }}</span>
                    </label>
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700 cursor-pointer select-none">
                        <input type="hidden" name="exclude_abnormal" value="0">
                        <input type="checkbox" name="exclude_abnormal" value="1" class="rounded border-gray-300 text-[#2D7080] shadow-sm focus:ring-[#2D7080]" {{ ($filters['exclude_abnormal'] ?? false) ? 'checked' : '' }}>
                        <span>{{ __('messages.Exclude abnormal traffic') }}</span>
                    </label>
                    <button type="submit" class="px-3 py-1.5 text-xs font-medium text-white bg-[#2D7080] rounded hover:bg-[#245f6d] transition-colors">
                        {{ __('messages.Apply filters') }}
                    </button>
                    @if($filters['exclude_bots'] ?? $filters['exclude_nojs'] ?? $filters['exclude_hosting'] ?? $filters['exclude_vpn'] ?? $filters['exclude_abnormal'] ?? request()->has('country'))
                        <a href="{{ route(request()->route()->getName()) }}" class="text-xs text-gray-500 hover:text-gray-700 underline">
                            {{ __('messages.Clear filters') }}
                        </a>
                    @endif
                </form>
            </div>
            <div class="manager-table-wrapper p-6 overflow-x-auto">
                <table class="manager-table min-w-full text-left text-sm">
                    <thead class="border-b border-slate-100">
                    <tr>
                        <th class="px-4 py-2 font-semibold text-gray-600">IP</th>
                        <th class="px-4 py-2 font-semibold text-gray-600">{{ __('messages.Country') }}</th>
                        <th class="px-4 py-2 font-semibold text-gray-600">{{ __('messages.Location') }}</th>
                        <th class="px-4 py-2 font-semibold text-gray-600">{{ __('messages.Visits (non client)') }}</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                    @php
                        $currentCountry = null;
                    @endphp
                    @forelse($visitsList as $visit)
                        @php
                            $countryLabel = $visit->country ?? __('messages.Unknown location');
                            $ipUrl = isset($visit->ip)
                                ? route('manager.dashboard.visits.non-client-ip', array_merge(['ip' => $visit->ip], request()->only(['exclude_bots', 'exclude_nojs', 'exclude_hosting', 'exclude_vpn', 'exclude_abnormal'])))
                                : null;
                        @endphp
                        @if($countryLabel !== $currentCountry)
                            <tr class="manager-table-group-row bg-gray-50">
                                <td data-span="2" class="px-4 py-2 text-xs font-semibold uppercase tracking-wide text-gray-500">
                                    {{ $countryLabel }}
                                </td>
                            </tr>
                            @php
                                $currentCountry = $countryLabel;
                            @endphp
                        @endif
                        <tr class="manager-card-row cursor-pointer"
                            @if($ipUrl) onclick="window.location.href='{{ $ipUrl }}'" @endif
                            role="button"
                            tabindex="0"
                            aria-expanded="true">
                            <td data-label="IP" data-span="2" data-manager-card-toggle class="manager-toggle-cell px-4 py-2 text-gray-900">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        {{ $visit->ip ?? '—' }}
                                    </div>
                                    <span class="shrink-0 mt-0.5 text-slate-400 lg:hidden" aria-hidden="true">
                                        <svg class="manager-chevron w-5 h-5" viewBox="0 0 24 24" fill="none">
                                            <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </span>
                                </div>
                            </td>
                            <td data-label="{{ __('messages.Country') }}" class="px-4 py-2 text-gray-700">{{ $visit->country ?? __('messages.Unknown location') }}</td>
                            <td data-label="{{ __('messages.Location') }}" class="px-4 py-2 text-gray-700">{{ $visit->city ?? '—' }}</td>
                            <td data-label="{{ __('messages.Visits (non client)') }}" class="px-4 py-2 text-gray-700">{{ number_format($visit->visits_count ?? 0) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-4 text-center text-sm text-gray-500">
                                {{ __('messages.No visit data yet.') }}
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>

                <div class="mt-4">
                    {{ $visitsList->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection



