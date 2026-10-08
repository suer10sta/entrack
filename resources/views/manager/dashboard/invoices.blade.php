@extends('manager.layouts.app')

@section('content')
<div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-6">
    <div class="min-w-0">
        <p class="dt-overline mb-1">{{ __('messages.Manager area') }}</p>
        <h1 class="text-[22px] sm:text-[24px] font-bold tracking-tight text-[#0B2638] leading-tight">
            {{ __('messages.Stripe invoices') }}
        </h1>
        <p class="text-[13.5px] text-slate-500 mt-1">{{ __('messages.Invoiced vs. paid (last 12 months)') }}</p>
    </div>
    <a href="{{ route('manager.dashboard') }}" class="dt-btn dt-btn-ghost flex-shrink-0">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
        </svg>
        {{ __('messages.Back') }}
    </a>
</div>

<div class="dt-card overflow-hidden">
    <div class="px-5 sm:px-6 py-4 border-b border-slate-100 bg-white">
        <h2 class="dt-section-title">{{ __('messages.All Stripe invoices') }}</h2>
        <p class="dt-section-subtitle mt-0.5">{{ $invoicesList->count() }} {{ __('messages.total') }}</p>
    </div>

    <div class="manager-table-wrapper overflow-x-auto">
        <table class="dt-table manager-table min-w-full">
            <thead>
                <tr class="dt-table-head">
                    <th class="px-4 py-2.5">{{ __('messages.Entreprise name') }}</th>
                    <th class="px-4 py-2.5">{{ __('messages.Date') }}</th>
                    <th class="px-4 py-2.5">{{ __('messages.Total') }}</th>
                    <th class="px-4 py-2.5">{{ __('messages.Status') }}</th>
                    <th class="px-4 py-2.5">{{ __('messages.Invoice #') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoicesList as $row)
                    <tr class="manager-card-row" aria-expanded="true">
                        <td data-label="{{ __('messages.Entreprise name') }}" data-span="2" data-manager-card-toggle class="manager-toggle-cell px-4 py-2.5">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div class="w-8 h-8 flex-shrink-0 rounded-full bg-[#2D7080]/10 text-[#2D7080] flex items-center justify-center text-[12px] font-bold">
                                        {{ strtoupper(substr($row['entreprise']->name, 0, 1)) }}
                                    </div>
                                    <span class="text-[13px] font-semibold text-slate-800 truncate">{{ $row['entreprise']->name }}</span>
                                </div>
                                <span class="shrink-0 mt-0.5 text-slate-400 min-[1366px]:hidden">
                                    <svg class="manager-chevron w-5 h-5" viewBox="0 0 24 24" fill="none"><path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </span>
                            </div>
                        </td>
                        <td data-label="{{ __('messages.Date') }}" class="px-4 py-2.5 text-[13px] text-slate-500">
                            {{ $row['date']->format('Y-m-d') }}
                        </td>
                        <td data-label="{{ __('messages.Total') }}" class="px-4 py-2.5 text-[13px] font-semibold text-slate-800">
                            {{ number_format($row['total'], 2) }} EUR
                        </td>
                        <td data-label="{{ __('messages.Status') }}" class="px-4 py-2.5">
                            @if(($row['status'] ?? '') === 'paid')
                                <span class="dt-badge dt-badge-success"><span class="dt-dot"></span>{{ __('messages.Paid') }}</span>
                            @elseif(($row['status'] ?? '') === 'open')
                                <span class="dt-badge dt-badge-warning"><span class="dt-dot"></span>{{ __('messages.Open') }}</span>
                            @elseif($row['status'] ?? false)
                                <span class="dt-badge dt-badge-neutral"><span class="dt-dot"></span>{{ ucfirst($row['status']) }}</span>
                            @else
                                <span class="text-slate-400 text-[13px]">—</span>
                            @endif
                        </td>
                        <td data-label="{{ __('messages.Invoice #') }}" class="px-4 py-2.5 text-[13px] text-slate-500 font-mono">
                            {{ $row['number'] ?? '—' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <div class="dt-empty-icon mx-auto mb-3">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <p class="text-[13.5px] font-semibold text-[#0B2638]">{{ __('messages.No invoices found for this entreprise.') }}</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
