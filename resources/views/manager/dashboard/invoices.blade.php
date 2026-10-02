@extends('manager.layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-[22px] font-bold tracking-tight text-[#0B2638]">{{ __('messages.Total invoiced') }}</h2>
                <p class="mt-1 text-sm text-gray-500">
                    {{ __('messages.Invoiced vs. paid (last 12 months)') }}
                </p>
            </div>
            <a href="{{ route('manager.dashboard') }}"
               class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-xs font-medium rounded text-gray-700 hover:bg-gray-50">
                {{ __('messages.Back to list') }}
            </a>
        </div>

        <div class="dt-card">
            <div class="manager-table-wrapper p-6 overflow-x-auto">
                <table class="manager-table min-w-full text-left text-sm">
                    <thead class="border-b border-slate-100">
                    <tr>
                        <th class="px-4 py-2 font-semibold text-gray-600">{{ __('messages.Entreprise name') }}</th>
                        <th class="px-4 py-2 font-semibold text-gray-600">{{ __('messages.Date') }}</th>
                        <th class="px-4 py-2 font-semibold text-gray-600">{{ __('messages.Total') }}</th>
                        <th class="px-4 py-2 font-semibold text-gray-600">{{ __('messages.Status') }}</th>
                        <th class="px-4 py-2 font-semibold text-gray-600">{{ __('messages.Invoice #') }}</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                    @forelse($invoicesList as $invoiceRow)
                        <tr class="manager-card-row" aria-expanded="true">
                            <td data-label="{{ __('messages.Entreprise name') }}" data-span="2" data-manager-card-toggle class="manager-toggle-cell px-4 py-2 text-gray-900">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        {{ $invoiceRow['entreprise']->name }}
                                    </div>
                                    <span class="shrink-0 mt-0.5 text-slate-400 lg:hidden" aria-hidden="true">
                                        <svg class="manager-chevron w-5 h-5" viewBox="0 0 24 24" fill="none">
                                            <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </span>
                                </div>
                            </td>
                            <td data-label="{{ __('messages.Date') }}" class="px-4 py-2 text-gray-500">
                                {{ $invoiceRow['date']->format('Y-m-d') }}
                            </td>
                            <td data-label="{{ __('messages.Total') }}" class="px-4 py-2 text-gray-700">
                                {{ number_format($invoiceRow['total'], 2) }} EUR
                            </td>
                            <td data-label="{{ __('messages.Status') }}" class="px-4 py-2 text-gray-700">
                                {{ ucfirst($invoiceRow['status'] ?? '—') }}
                            </td>
                            <td data-label="{{ __('messages.Invoice #') }}" class="px-4 py-2 text-gray-500">
                                {{ $invoiceRow['number'] ?? '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-4 text-center text-sm text-gray-500">
                                {{ __('messages.No invoices found for this entreprise.') }}
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection



