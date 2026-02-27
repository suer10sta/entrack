@extends('manager.layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-semibold text-gray-900">{{ __('messages.Total invoiced') }}</h2>
                <p class="mt-1 text-sm text-gray-500">
                    {{ __('messages.Invoiced vs. paid (last 12 months)') }}
                </p>
            </div>
            <a href="{{ route('manager.dashboard') }}"
               class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-xs font-medium rounded text-gray-700 hover:bg-gray-50">
                {{ __('messages.Back to list') }}
            </a>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="p-6 overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="border-b border-gray-200 bg-gray-50">
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
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 text-gray-900">{{ $invoiceRow['entreprise']->name }}</td>
                            <td class="px-4 py-2 text-gray-500">
                                {{ $invoiceRow['date']->format('Y-m-d') }}
                            </td>
                            <td class="px-4 py-2 text-gray-700">
                                {{ number_format($invoiceRow['total'], 2) }} EUR
                            </td>
                            <td class="px-4 py-2 text-gray-700">
                                {{ ucfirst($invoiceRow['status'] ?? '—') }}
                            </td>
                            <td class="px-4 py-2 text-gray-500">
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


