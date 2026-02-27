@extends('manager.layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-semibold text-gray-900">{{ $title }}</h2>
                <p class="mt-1 text-sm text-gray-500">{{ $subtitle }}</p>
                @if(!$isUnique && isset($selectedCountry) && $selectedCountry !== null && $selectedCountry !== '')
                    <p class="mt-1 text-xs text-blue-700">
                        {{ __('messages.Country') }}:
                        {{ $selectedCountry === ($unknownCountryToken ?? '__unknown__') ? __('messages.Unknown location') : $selectedCountry }}
                    </p>
                @endif
            </div>
            <div class="flex items-center gap-2">
                @if(!$isUnique && isset($selectedCountry) && $selectedCountry !== null && $selectedCountry !== '')
                    <a href="{{ route('manager.dashboard.visits.non-client') }}"
                       class="inline-flex items-center px-3 py-1.5 border border-blue-300 text-xs font-medium rounded text-blue-700 hover:bg-blue-50">
                        {{ __('messages.Clear filter') }}
                    </a>
                @endif
                <a href="{{ route('manager.dashboard') }}"
                   class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-xs font-medium rounded text-gray-700 hover:bg-gray-50">
                    {{ __('messages.Back to list') }}
                </a>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="p-6 overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="border-b border-gray-200 bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 font-semibold text-gray-600">IP</th>
                        <th class="px-4 py-2 font-semibold text-gray-600">{{ __('messages.Date') }}</th>
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
                        @endphp
                        @if($countryLabel !== $currentCountry)
                            <tr class="bg-gray-50">
                                <td colspan="5" class="px-4 py-2 text-xs font-semibold uppercase tracking-wide text-gray-500">
                                    {{ $countryLabel }}
                                </td>
                            </tr>
                            @php
                                $currentCountry = $countryLabel;
                            @endphp
                        @endif
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 text-gray-900">{{ $visit->ip ?? '—' }}</td>
                            <td class="px-4 py-2 text-gray-700">
                                @if($isUnique)
                                    {{ $visit->visited_at ? \Carbon\Carbon::parse($visit->visited_at)->format('Y-m-d H:i') : '—' }}
                                @else
                                    {{ optional($visit->created_at)->format('Y-m-d H:i') ?? '—' }}
                                @endif
                            </td>
                            <td class="px-4 py-2 text-gray-700">{{ $visit->country ?? __('messages.Unknown location') }}</td>
                            <td class="px-4 py-2 text-gray-700">{{ $visit->city ?? '—' }}</td>
                            <td class="px-4 py-2 text-gray-700">{{ number_format($visit->visits_count ?? 0) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-4 text-center text-sm text-gray-500">
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


