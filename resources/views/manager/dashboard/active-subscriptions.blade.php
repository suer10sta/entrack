@extends('manager.layouts.app')

@section('content')
<div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-6">
    <div class="min-w-0">
        <p class="dt-overline mb-1">{{ __('messages.Manager area') }}</p>
        <h1 class="text-[22px] sm:text-[24px] font-bold tracking-tight text-[#0B2638] leading-tight">
            {{ __('messages.Active subscriptions') }}
        </h1>
        <p class="text-[13.5px] text-slate-500 mt-1">{{ __('messages.Entreprises and subscriptions') }}</p>
    </div>
    <a href="{{ route('manager.dashboard') }}" class="dt-btn dt-btn-ghost flex-shrink-0">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
        </svg>
        {{ __('messages.Back') }}
    </a>
</div>

<div class="dt-card overflow-hidden">
    <div class="px-5 sm:px-6 py-4 border-b border-slate-100 bg-white flex items-center justify-between">
        <div>
            <h2 class="dt-section-title">{{ __('messages.Active entreprises') }}</h2>
            <p class="dt-section-subtitle mt-0.5">{{ $activeSubscriptionsEntreprises->total() }} {{ __('messages.total') }}</p>
        </div>
    </div>

    <div class="manager-table-wrapper overflow-x-auto">
        <table class="dt-table manager-table min-w-full">
            <thead>
                <tr class="dt-table-head">
                    <th class="px-4 py-2.5">{{ __('messages.Name') }}</th>
                    <th class="px-4 py-2.5">{{ __('messages.Email') }}</th>
                    <th class="px-4 py-2.5">{{ __('messages.Plan') }}</th>
                    <th class="px-4 py-2.5">{{ __('messages.Status') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($activeSubscriptionsEntreprises as $entreprise)
                    @php
                        $sub = $entreprise->subscriptions->first();
                        $status = $sub ? $sub->stripe_status : null;
                    @endphp
                    <tr class="manager-card-row" aria-expanded="true">
                        <td data-label="{{ __('messages.Name') }}" data-span="2" data-manager-card-toggle class="manager-toggle-cell px-4 py-2.5">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div class="w-8 h-8 flex-shrink-0 rounded-full bg-[#2D7080]/10 text-[#2D7080] flex items-center justify-center text-[12px] font-bold">
                                        {{ strtoupper(substr($entreprise->name, 0, 1)) }}
                                    </div>
                                    <a href="{{ route('manager.subscriptions.show', $entreprise) }}"
                                       class="text-[13px] font-semibold text-[#2D7080] hover:text-[#0B2638] hover:underline truncate">
                                        {{ $entreprise->name }}
                                    </a>
                                </div>
                                <span class="shrink-0 mt-0.5 text-slate-400 min-[1366px]:hidden">
                                    <svg class="manager-chevron w-5 h-5" viewBox="0 0 24 24" fill="none"><path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </span>
                            </div>
                        </td>
                        <td data-label="{{ __('messages.Email') }}" class="px-4 py-2.5 text-[13px] text-slate-500">{{ $entreprise->email }}</td>
                        <td data-label="{{ __('messages.Plan') }}" class="px-4 py-2.5">
                            @if($entreprise->type_compt)
                                <span class="dt-badge dt-badge-navy"><span class="dt-dot"></span>{{ $entreprise->type_compt }}</span>
                            @else
                                <span class="text-slate-400 text-[13px]">—</span>
                            @endif
                        </td>
                        <td data-label="{{ __('messages.Status') }}" class="px-4 py-2.5">
                            @if(in_array($status, ['active', 'trialing']))
                                <span class="dt-badge dt-badge-success"><span class="dt-dot"></span>{{ ucfirst($status) }}</span>
                            @elseif($status === 'past_due')
                                <span class="dt-badge dt-badge-warning"><span class="dt-dot"></span>{{ __('messages.Past due') }}</span>
                            @elseif($status)
                                <span class="dt-badge dt-badge-neutral"><span class="dt-dot"></span>{{ ucfirst($status) }}</span>
                            @else
                                <span class="text-slate-400 text-[13px]">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center">
                            <div class="dt-empty-icon mx-auto mb-3">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/>
                                </svg>
                            </div>
                            <p class="text-[13.5px] font-semibold text-[#0B2638]">{{ __('messages.No active subscriptions found.') }}</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($activeSubscriptionsEntreprises->hasPages())
        <div class="px-5 sm:px-6 py-3 border-t border-slate-100 bg-[#f8fafc]">
            {{ $activeSubscriptionsEntreprises->links() }}
        </div>
    @endif
</div>
@endsection
