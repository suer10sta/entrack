@extends('manager.layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-semibold text-gray-900">
                    {{ __('messages.Subscription detail — :name', ['name' => $entreprise->name]) }}
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    {{ __('messages.General information, current subscription and Stripe invoices.') }}
                </p>
            </div>
            <a href="{{ route('manager.subscriptions.index') }}"
               class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-xs font-medium rounded text-gray-700 hover:bg-gray-50">
                {{ __('messages.Back to list') }}
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-sm font-semibold text-gray-900 mb-3">{{ __('messages.General information') }}</h3>
                <dl class="space-y-2 text-sm text-gray-700">
                    <div>
                        <dt class="font-medium text-gray-600">{{ __('messages.Entreprise name') }}</dt>
                        <dd>{{ $entreprise->name }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-gray-600">{{ __('messages.Address') }}</dt>
                        <dd>{{ $entreprise->Address ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-gray-600">{{ __('messages.Country') }}</dt>
                        <dd>{{ $entreprise->country ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-gray-600">{{ __('messages.City') }}</dt>
                        <dd>{{ $entreprise->city ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-gray-600">{{ __('messages.Zip Code') }}</dt>
                        <dd>{{ $entreprise->zip_code ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-gray-600">{{ __('messages.TVA') }}</dt>
                        <dd>{{ $entreprise->tva ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-gray-600">{{ __('messages.Email') }}</dt>
                        <dd>{{ $entreprise->email }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-gray-600">{{ __('messages.Plan') }}</dt>
                        <dd>{{ $entreprise->type_compt ?? __('messages.Not subscribed') }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-gray-600">{{ __('messages.Trial ends at') }}</dt>
                        <dd>{{ $entreprise->trial_ends_at ? $entreprise->trial_ends_at->format('Y-m-d') : '—' }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-gray-600">{{ __('messages.Account status') }}</dt>
                        <dd>
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border {{ ($stripeAccountIsActive ?? false) ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-red-50 text-red-700 border-red-200' }}">
                                {{ ($stripeAccountIsActive ?? false) ? __('messages.Active') : __('messages.Inactive') }}
                            </span>
                        </dd>
                    </div>
                </dl>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-sm font-semibold text-gray-900 mb-3">{{ __('messages.Current subscription') }}</h3>
                @if ($hasCurrentStripeSubscription ?? false)
                    <dl class="space-y-2 text-sm text-gray-700">
                        <div>
                            <dt class="font-medium text-gray-600">{{ __('messages.Stripe ID') }}</dt>
                            <dd>{{ $stripeSubscriptionId ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-600">{{ __('messages.Status') }}</dt>
                            <dd>{{ $stripeSubscriptionStatus ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-600">{{ __('messages.Created at') }}</dt>
                            <dd>{{ optional($stripeSubscriptionCreatedAt)->format('Y-m-d H:i') ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-600">{{ __('messages.Ends at') }}</dt>
                            <dd>
                                @if ($stripePlanEndsAt)
                                    {{ $stripePlanEndsAt->format('Y-m-d H:i') }}
                                @else
                                    —
                                @endif
                            </dd>
                        </div>
                    </dl>
                @else
                    <p class="text-sm text-gray-500">
                        {{ __('messages.No active subscription found for this entreprise.') }}
                    </p>
                @endif
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-sm font-semibold text-gray-900">{{ __('messages.Stripe invoices') }}</h3>
            </div>
            <div class="p-6 overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="border-b border-gray-200 bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 font-semibold text-gray-600">{{ __('messages.Date') }}</th>
                        <th class="px-4 py-2 font-semibold text-gray-600">{{ __('messages.Total') }}</th>
                        <th class="px-4 py-2 font-semibold text-gray-600">{{ __('messages.Status') }}</th>
                        <th class="px-4 py-2 font-semibold text-gray-600">{{ __('messages.Invoice #') }}</th>
                        <th class="px-4 py-2 font-semibold text-gray-600"></th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                    @forelse ($invoices as $invoice)
                        @php
                            $stripeInvoice = $invoice->asStripeInvoice();
                            $invoiceAmount = isset($stripeInvoice->total) ? ((float) $stripeInvoice->total / 100) : null;
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 text-gray-700">{{ $invoice->date()->format('Y-m-d') }}</td>
                            <td class="px-4 py-2 text-gray-700">{{ $invoiceAmount !== null ? number_format($invoiceAmount, 2) . ' EUR' : '—' }}</td>
                            <td class="px-4 py-2 text-gray-700">{{ $stripeInvoice->status }}</td>
                            <td class="px-4 py-2 text-gray-700">{{ $invoice->number }}</td>
                            <td class="px-4 py-2 text-right">
                                <a href="{{ $invoice->hosted_invoice_url }}" target="_blank"
                                   class="inline-flex items-center px-3 py-1.5 border border-blue-600 text-xs font-medium rounded text-blue-600 hover:bg-blue-50">
                                    {{ __('messages.View') }}
                                </a>
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



