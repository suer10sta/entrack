@extends('manager.layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-semibold text-gray-900">{{ $title }}</h2>
                <p class="mt-1 text-sm text-gray-500">{{ $subtitle }}</p>
                <p class="mt-2 text-xs text-gray-500">
                    Total visits for this IP: <span class="font-semibold">{{ number_format($totalVisits) }}</span>
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('manager.dashboard.visits.non-client-unique', request()->only(['country', 'exclude_bots', 'exclude_nojs', 'exclude_hosting', 'exclude_abnormal'])) }}"
                   class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-xs font-medium rounded text-gray-700 hover:bg-gray-50">
                    {{ __('messages.Back to list') }}
                </a>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            {{-- Filters --}}
            <div class="p-4 border-b border-gray-200 bg-gray-50/50">
                <form method="GET" class="flex flex-wrap items-center gap-x-6 gap-y-3">
                    @if($filters['country'] ?? null)
                        <input type="hidden" name="country" value="{{ $filters['country'] }}">
                    @endif
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700 cursor-pointer select-none">
                        <input type="hidden" name="exclude_bots" value="0">
                        <input type="checkbox" name="exclude_bots" value="1" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500" {{ ($filters['exclude_bots'] ?? false) ? 'checked' : '' }}>
                        <span>{{ __('messages.Exclude known bots') }}</span>
                    </label>
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700 cursor-pointer select-none">
                        <input type="hidden" name="exclude_nojs" value="0">
                        <input type="checkbox" name="exclude_nojs" value="1" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500" {{ ($filters['exclude_nojs'] ?? false) ? 'checked' : '' }}>
                        <span>{{ __('messages.Exclude no-JS visits') }}</span>
                    </label>
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700 cursor-pointer select-none">
                        <input type="hidden" name="exclude_hosting" value="0">
                        <input type="checkbox" name="exclude_hosting" value="1" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500" {{ ($filters['exclude_hosting'] ?? false) ? 'checked' : '' }}>
                        <span>{{ __('messages.Exclude hosting IPs') }}</span>
                    </label>
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700 cursor-pointer select-none">
                        <input type="hidden" name="exclude_vpn" value="0">
                        <input type="checkbox" name="exclude_vpn" value="1" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500" {{ ($filters['exclude_vpn'] ?? false) ? 'checked' : '' }}>
                        <span>{{ __('messages.Exclude VPN IPs') }}</span>
                    </label>
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700 cursor-pointer select-none">
                        <input type="hidden" name="exclude_abnormal" value="0">
                        <input type="checkbox" name="exclude_abnormal" value="1" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500" {{ ($filters['exclude_abnormal'] ?? false) ? 'checked' : '' }}>
                        <span>{{ __('messages.Exclude abnormal traffic') }}</span>
                    </label>
                    <button type="submit" class="px-3 py-1.5 text-xs font-medium text-white bg-blue-600 rounded hover:bg-blue-700 transition-colors">
                        {{ __('messages.Apply filters') }}
                    </button>
                    @if($filters['exclude_bots'] ?? $filters['exclude_nojs'] ?? $filters['exclude_hosting'] ?? $filters['exclude_vpn'] ?? $filters['exclude_abnormal'] ?? false)
                        <a href="{{ route(request()->route()->getName(), ['ip' => $ip]) }}" class="text-xs text-gray-500 hover:text-gray-700 underline">
                            {{ __('messages.Clear filters') }}
                        </a>
                    @endif
                </form>
            </div>
            <div class="manager-table-wrapper p-6 overflow-x-auto">
                <table class="manager-table min-w-full text-left text-sm">
                    <thead class="border-b border-gray-200 bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 font-semibold text-gray-600">IP</th>
                        <th class="px-4 py-2 font-semibold text-gray-600">{{ __('messages.Date') }}</th>
                        <th class="px-4 py-2 font-semibold text-gray-600">{{ __('messages.Country') }}</th>
                        <th class="px-4 py-2 font-semibold text-gray-600">{{ __('messages.Location') }}</th>
                        <th class="px-4 py-2 font-semibold text-gray-600">Page</th>
                        <th class="px-4 py-2 font-semibold text-gray-600">{{ __('messages.Visits (non client)') }}</th>
                        <th class="px-4 py-2 font-semibold text-gray-600">Duration</th>
                        <th class="px-4 py-2 font-semibold text-gray-600">UA</th>
                        <th class="px-4 py-2 font-semibold text-gray-600">Details</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                    @forelse($visitsList as $row)
                        @php
                            $rowId = 'visit-' . $row->id;
                        @endphp
                        <tr class="manager-card-row hover:bg-gray-50" aria-expanded="true">
                            <td data-label="IP" data-span="2" data-manager-card-toggle class="manager-toggle-cell px-4 py-2 text-gray-700">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">{{ $ip }}</div>
                                    <span class="shrink-0 mt-0.5 text-slate-400 lg:hidden" aria-hidden="true">
                                        <svg class="manager-chevron w-5 h-5" viewBox="0 0 24 24" fill="none">
                                            <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </span>
                                </div>
                            </td>
                            <td data-label="{{ __('messages.Date') }}" class="px-4 py-2 text-gray-700">
                                {{ $row->created_at ? \Carbon\Carbon::parse($row->created_at)->format('Y-m-d H:i:s') : '—' }}
                            </td>
                            <td data-label="{{ __('messages.Country') }}" class="px-4 py-2 text-gray-700">{{ $row->country ?? __('messages.Unknown location') }}</td>
                            <td data-label="{{ __('messages.Location') }}" class="px-4 py-2 text-gray-700">{{ $row->city ?? '—' }}</td>
                            <td data-label="Page" data-span="2" class="px-4 py-2 text-gray-700">
                                @php
                                    $page = null;
                                    if (!empty($row->url)) {
                                        $page = parse_url($row->url, PHP_URL_PATH);
                                    }
                                    $page = $page ?: ($row->url ?? null);
                                @endphp
                                {{ $page ?: '—' }}
                            </td>
                            <td data-label="{{ __('messages.Visits (non client)') }}" class="px-4 py-2 text-gray-700">1</td>
                            <td data-label="Duration" class="px-4 py-2 text-gray-700">
                                @php
                                    $dur = $row->duration_seconds ?? null;
                                    $durStr = $dur !== null ? gmdate('i:s', max(0, (int) $dur)) : null;
                                @endphp
                                {{ $durStr ?? '—' }}
                            </td>
                            <td data-label="UA" class="px-4 py-2 text-gray-700 max-w-[200px] truncate" title="{{ e($row->user_agent ?? '') }}">
                                {{ $row->user_agent ? \Illuminate\Support\Str::limit($row->user_agent, 50) : '—' }}
                            </td>
                            <td data-label="Details" class="px-4 py-2 text-gray-700">
                                <button type="button" onclick="document.getElementById('{{ $rowId }}-details').classList.toggle('hidden'); this.querySelector('svg').classList.toggle('rotate-180');"
                                        class="inline-flex items-center gap-1 text-xs text-blue-600 hover:text-blue-800">
                                    <svg class="w-4 h-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                    Info
                                </button>
                            </td>
                        </tr>
                        <tr id="{{ $rowId }}-details" class="hidden bg-gray-50/50">
                            <td colspan="9" class="px-4 py-3">
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-xs">
                                    <div>
                                        <span class="font-semibold text-gray-500">Device:</span>
                                        <span class="text-gray-700 ml-1">{{ $row->device_type ?? '—' }}</span>
                                    </div>
                                    <div>
                                        <span class="font-semibold text-gray-500">Hosting:</span>
                                        <span class="text-gray-700 ml-1">
                                            @if($row->is_hosting === null) —
                                            @elseif($row->is_hosting) Yes
                                            @else No
                                            @endif
                                        </span>
                                    </div>
                                    <div>
                                        <span class="font-semibold text-gray-500">Proxy:</span>
                                        <span class="text-gray-700 ml-1">
                                            @if($row->is_proxy === null) —
                                            @elseif($row->is_proxy) Yes
                                            @else No
                                            @endif
                                        </span>
                                    </div>
                                    <div>
                                        <span class="font-semibold text-gray-500">Referrer:</span>
                                        <span class="text-gray-700 ml-1">{{ $row->referrer ?? '—' }}</span>
                                    </div>
                                    <div>
                                        <span class="font-semibold text-gray-500">UTM Source:</span>
                                        <span class="text-gray-700 ml-1">{{ $row->utm_source ?? '—' }}</span>
                                    </div>
                                    <div>
                                        <span class="font-semibold text-gray-500">Duration:</span>
                                        <span class="text-gray-700 ml-1">{{ $durStr ?? '—' }}</span>
                                    </div>
                                    <div class="col-span-2 md:col-span-4">
                                        <span class="font-semibold text-gray-500">Full UA:</span>
                                        <span class="text-gray-700 ml-1 break-all">{{ $row->user_agent ?? '—' }}</span>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-4 text-center text-sm text-gray-500">
                                No visits found for this IP.
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

