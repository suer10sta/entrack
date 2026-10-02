@extends('manager.layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-[22px] font-bold tracking-tight text-[#0B2638]">
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
            <div class="dt-card p-5 sm:p-6">
                <h3 class="dt-panel-title mb-3">{{ __('messages.General information') }}</h3>
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

            <div class="dt-card p-5 sm:p-6">
                <h3 class="dt-panel-title mb-3">{{ __('messages.Current subscription') }}</h3>
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

        <div class="dt-card">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="dt-panel-title">{{ __('messages.Stripe invoices') }}</h3>
            </div>
            <div class="manager-table-wrapper p-6 overflow-x-auto">
                <table class="manager-table min-w-full text-left text-sm">
                    <thead class="border-b border-slate-100">
                    <tr>
                        <th class="px-4 py-2 font-semibold text-gray-600">{{ __('messages.Date') }}</th>
                        <th class="px-4 py-2 font-semibold text-gray-600">{{ __('messages.Total') }}</th>
                        <th class="px-4 py-2 font-semibold text-gray-600">{{ __('messages.Status') }}</th>
                        <th class="px-4 py-2 font-semibold text-gray-600">{{ __('messages.Invoice #') }}</th>
                        <th class="px-4 py-2 font-semibold text-gray-600 text-right">{{ __('messages.Actions') }}</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                    @forelse ($invoices as $invoice)
                        @php
                            $stripeInvoice = $invoice->asStripeInvoice();
                            $invoiceAmount = isset($stripeInvoice->total) ? ((float) $stripeInvoice->total / 100) : null;

                            // Reuse the same global sequence used in the PDF, backed by the DB table.
                            $rawNumber = (string) ($stripeInvoice->number ?? $invoice->number);
                            $sequenceRow = \App\Models\SubscriptionInvoiceSequence::where('stripe_invoice_number', $rawNumber)->first();
                            $seq = $sequenceRow ? (int) $sequenceRow->id : null;
                            $displayInvoiceNumber = $rawNumber;
                            if (is_int($seq) && $seq > 0) {
                                $displayInvoiceNumber = trim($rawNumber . ' - ' . $seq);
                            }
                        @endphp
                        <tr class="manager-card-row" aria-expanded="true">
                            <td data-label="{{ __('messages.Date') }}" data-manager-card-toggle class="manager-toggle-cell px-4 py-2 text-gray-700">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        {{ $invoice->date()->format('Y-m-d') }}
                                    </div>
                                    <span class="shrink-0 mt-0.5 text-slate-400 lg:hidden" aria-hidden="true">
                                        <svg class="manager-chevron w-5 h-5" viewBox="0 0 24 24" fill="none">
                                            <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </span>
                                </div>
                            </td>
                            <td data-label="{{ __('messages.Total') }}" class="px-4 py-2 text-gray-700">{{ $invoiceAmount !== null ? number_format($invoiceAmount, 2) . ' EUR' : '—' }}</td>
                            <td data-label="{{ __('messages.Status') }}" class="px-4 py-2 text-gray-700">{{ $stripeInvoice->status }}</td>
                            <td data-label="{{ __('messages.Invoice #') }}" class="px-4 py-2 text-gray-700">{{ $displayInvoiceNumber }}</td>
                            <td data-label="{{ __('messages.Actions') }}" data-span="2" class="px-4 py-2 text-right space-x-2">
                                <a href="{{ $invoice->hosted_invoice_url }}" target="_blank"
                                   class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-xs font-medium rounded text-gray-700 hover:bg-gray-50">
                                    {{ __('messages.View in Stripe') }}
                                </a>
                                <a href="{{ route('manager.subscriptions.invoices.download', ['entreprise' => $entreprise->id, 'invoiceId' => $stripeInvoice->id]) }}"
                                   class="inline-flex items-center px-3 py-1.5 border border-[#2D7080] text-xs font-medium rounded text-[#2D7080] hover:bg-[#2D7080]/10">
                                    {{ __('messages.Download PDF') }}
                                </a>
                                <button type="button"
                                        class="inline-flex items-center px-3 py-1.5 border border-[#2D7080] text-xs font-semibold rounded-lg text-[#2D7080] hover:bg-[#2D7080]/10"
                                        data-open-send-modal
                                        data-invoice-id="{{ $stripeInvoice->id }}">
                                    {{ __('messages.Send') }}
                                </button>
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

    {{-- Send subscription invoice modal --}}
    <div id="send-subscription-invoice-modal"
         class="fixed inset-0 z-40 hidden items-center justify-center bg-black bg-opacity-40">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
            <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200">
                <h3 class="dt-panel-title">
                    {{ __('messages.Send') }} {{ __('messages.Subscription invoice') }}
                </h3>
                <button type="button"
                        class="text-gray-400 hover:text-gray-600"
                        data-send-modal-close>
                    <span class="sr-only">{{ __('messages.Close') }}</span>
                    &times;
                </button>
            </div>

            <div class="px-4 py-4 space-y-4">
                {{-- Step 1: choose channel --}}
                <div data-send-step="select">
                    <p class="text-sm text-gray-700 mb-3">
                        {{ __('messages.Choose how to send:') }}
                    </p>
                    <div class="space-y-2">
                        <button type="button"
                                class="w-full inline-flex items-center justify-center px-4 py-2.5 border border-gray-300 text-sm font-medium rounded text-gray-800 hover:bg-gray-50"
                                data-send-channel-choice="email">
                            {{ __('messages.Send via Email') }}
                        </button>
                        <button type="button"
                                class="w-full inline-flex items-center justify-center px-4 py-2.5 border border-gray-300 text-sm font-medium rounded text-gray-800 hover:bg-gray-50"
                                data-send-channel-choice="peppol">
                            {{ __('messages.Send via Peppol') }}
                        </button>
                        @php
                            // Allow Peppol based on Belgian TVA number instead of the free‑text country field.
                            $tvaNumber = strtoupper(trim((string) ($entreprise->tva ?? '')));
                            $peppolAllowed = str_starts_with($tvaNumber, 'BE');
                        @endphp
                        @unless($peppolAllowed)
                            <p class="mt-1 text-xs text-red-500">
                                Entreprise Pay does not support Peppol for this entreprise because its TVA number is not Belgian.
                            </p>
                        @endunless
                    </div>
                </div>

                {{-- Step 2 (email only): select recipients --}}
                <div class="hidden space-y-4" data-send-step="recipients">
                    <div>
                        <p class="text-sm text-gray-700 mb-2">
                            {{ __('messages.Select Recipients (Email):') }}
                        </p>
                        <div class="space-y-2 text-sm text-gray-700">
                            <label class="flex items-start gap-2">
                                <input type="checkbox"
                                       class="mt-0.5 h-4 w-4 rounded border-gray-300 text-[#2D7080] focus:ring-[#2D7080]"
                                       data-recipient-checkbox="client"
                                       checked>
                                <span>
                                    <span class="font-medium">{{ __('messages.Client') ?? 'Client' }}</span>
                                    <span class="text-gray-500" data-client-email-text></span>
                                </span>
                            </label>

                            <label class="flex items-start gap-2">
                                <input type="checkbox"
                                       class="mt-0.5 h-4 w-4 rounded border-gray-300 text-[#2D7080] focus:ring-[#2D7080]"
                                       data-recipient-checkbox="copy"
                                       checked>
                                <span>
                                    <span class="font-medium">{{ __('messages.CopyRecipient') ?? 'CopyRecipient' }}</span>
                                    <span class="text-gray-500" data-copy-email-text></span>
                                </span>
                            </label>

                            <p class="text-xs text-gray-500">
                                {{ __('messages.Send the invoice only to the checked emails.') ?? 'Send the invoice only to the checked emails.' }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center justify-end space-x-2">
                        <button type="button"
                                class="px-3 py-1.5 text-xs font-medium text-gray-700 border border-gray-300 rounded hover:bg-gray-50"
                                data-send-recipients-back>
                            {{ __('messages.Back') ?? 'Back' }}
                        </button>
                        <button type="button"
                                class="px-3 py-1.5 text-xs font-semibold text-white bg-[#2D7080] rounded-lg hover:bg-[#245f6d]"
                                data-send-recipients-next>
                            {{ __('messages.Next') ?? 'Next' }}
                        </button>
                    </div>
                </div>

                {{-- Step 2: confirm --}}
                <div class="hidden space-y-4" data-send-step="confirm">
                    <p class="text-sm text-gray-700" data-send-confirm-text>
                        <!-- Filled by JS -->
                    </p>
                    <div class="flex items-center justify-end space-x-2">
                        <button type="button"
                                class="px-3 py-1.5 text-xs font-medium text-gray-700 border border-gray-300 rounded hover:bg-gray-50"
                                data-send-back>
                            {{ __('messages.Back') ?? 'Back' }}
                        </button>
                        <button type="button"
                                class="px-3 py-1.5 text-xs font-semibold text-white bg-[#2D7080] rounded-lg hover:bg-[#245f6d]"
                                data-send-confirm>
                            {{ __('messages.Confirm') ?? 'Confirm' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
            const csrfToken = csrfTokenMeta ? csrfTokenMeta.getAttribute('content') : null;
            const sendUrlTemplate = "{{ route('manager.subscriptions.invoices.send', ['entreprise' => $entreprise->id, 'invoiceId' => '__INVOICE_ID__']) }}";
            const peppolAllowed = @json($peppolAllowed);
            const recipientEmail = @json($entreprise->email);
            const copyRecipientEmail = @json($subscriptionInvoiceCopyRecipient ?? 'Dessin@bureaumercier.com');

            const modal = document.getElementById('send-subscription-invoice-modal');
            const stepSelect = modal.querySelector('[data-send-step="select"]');
            const stepRecipients = modal.querySelector('[data-send-step="recipients"]');
            const stepConfirm = modal.querySelector('[data-send-step="confirm"]');
            const confirmTextEl = modal.querySelector('[data-send-confirm-text]');
            const closeButtons = modal.querySelectorAll('[data-send-modal-close]');
            const backButton = modal.querySelector('[data-send-back]');
            const confirmButton = modal.querySelector('[data-send-confirm]');

            const recipientsBackButton = modal.querySelector('[data-send-recipients-back]');
            const recipientsNextButton = modal.querySelector('[data-send-recipients-next]');
            const clientCheckbox = modal.querySelector('[data-recipient-checkbox="client"]');
            const copyCheckbox = modal.querySelector('[data-recipient-checkbox="copy"]');
            const clientEmailText = modal.querySelector('[data-client-email-text]');
            const copyEmailText = modal.querySelector('[data-copy-email-text]');

            let currentInvoiceId = null;
            let selectedChannel = null;
            let selectedRecipients = [];

            function openModal(invoiceId) {
                currentInvoiceId = invoiceId;
                selectedChannel = null;
                selectedRecipients = [];
                // Reset to step 1
                stepSelect.classList.remove('hidden');
                stepRecipients.classList.add('hidden');
                stepConfirm.classList.add('hidden');
                modal.classList.remove('hidden');
                modal.classList.add('flex');

                // Populate labels and defaults
                if (clientEmailText) {
                    clientEmailText.textContent = recipientEmail && recipientEmail !== '' ? ` (${recipientEmail})` : ' (—)';
                }
                if (copyEmailText) {
                    copyEmailText.textContent = copyRecipientEmail && copyRecipientEmail !== '' ? ` (${copyRecipientEmail})` : ' (—)';
                }
                if (clientCheckbox) {
                    clientCheckbox.checked = !!(recipientEmail && recipientEmail !== '');
                    clientCheckbox.disabled = !(recipientEmail && recipientEmail !== '');
                }
                if (copyCheckbox) {
                    copyCheckbox.checked = !!(copyRecipientEmail && copyRecipientEmail !== '');
                    copyCheckbox.disabled = !(copyRecipientEmail && copyRecipientEmail !== '');
                }
            }

            function closeModal() {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                currentInvoiceId = null;
                selectedChannel = null;
                selectedRecipients = [];
            }

            function buildSelectedRecipients() {
                const recipients = [];
                if (clientCheckbox && clientCheckbox.checked && recipientEmail) {
                    recipients.push(recipientEmail);
                }
                if (copyCheckbox && copyCheckbox.checked && copyRecipientEmail) {
                    recipients.push(copyRecipientEmail);
                }
                // unique
                return Array.from(new Set(recipients));
            }

            document.querySelectorAll('[data-open-send-modal]').forEach(button => {
                button.addEventListener('click', function () {
                    const invoiceId = this.getAttribute('data-invoice-id');
                    if (!invoiceId) return;
                    openModal(invoiceId);
                });
            });

            closeButtons.forEach(btn => {
                btn.addEventListener('click', function () {
                    closeModal();
                });
            });

            // Close when clicking on the dark backdrop (but not the inner content)
            modal.addEventListener('click', function (e) {
                if (e.target === modal) {
                    closeModal();
                }
            });

            // Step 1: choose channel
            modal.querySelectorAll('[data-send-channel-choice]').forEach(btn => {
                btn.addEventListener('click', function () {
                    const channel = this.getAttribute('data-send-channel-choice');
                    if (!channel) return;

                    if (channel === 'peppol' && !peppolAllowed) {
                        alert('Entreprise Pay does not support Peppol for this entreprise\'s country.');
                        return;
                    }
                    selectedChannel = channel;

                    if (channel === 'email') {
                        stepSelect.classList.add('hidden');
                        stepRecipients.classList.remove('hidden');
                        return;
                    } else {
                        confirmTextEl.textContent =
                            'Are you sure you want to send this subscription invoice via Peppol?';
                    }

                    stepSelect.classList.add('hidden');
                    stepRecipients.classList.add('hidden');
                    stepConfirm.classList.remove('hidden');
                });
            });

            // Step 2 (email): back to channel selection
            recipientsBackButton.addEventListener('click', function () {
                selectedRecipients = [];
                selectedChannel = null;
                stepRecipients.classList.add('hidden');
                stepConfirm.classList.add('hidden');
                stepSelect.classList.remove('hidden');
            });

            // Step 2 (email): next to confirm
            recipientsNextButton.addEventListener('click', function () {
                selectedRecipients = buildSelectedRecipients();
                if (!selectedRecipients || selectedRecipients.length === 0) {
                    alert('Please select at least one recipient.');
                    return;
                }

                confirmTextEl.textContent =
                    'Are you sure you want to send this subscription invoice via Email to: ' + selectedRecipients.join(', ') + '?';

                stepRecipients.classList.add('hidden');
                stepConfirm.classList.remove('hidden');
            });

            // Step 2: back to selection
            backButton.addEventListener('click', function () {
                selectedChannel = null;
                selectedRecipients = [];
                stepConfirm.classList.add('hidden');
                stepSelect.classList.remove('hidden');
            });

            // Step 2: confirm and send
            confirmButton.addEventListener('click', function () {
                if (!currentInvoiceId || !selectedChannel) {
                    return;
                }

                if (!csrfToken) {
                    alert('Unable to send invoice: missing CSRF token.');
                    return;
                }

                const url = sendUrlTemplate.replace('__INVOICE_ID__', currentInvoiceId);
                const payload = { channel: selectedChannel };
                if (selectedChannel === 'email') {
                    const recipients = selectedRecipients && selectedRecipients.length > 0
                        ? selectedRecipients
                        : buildSelectedRecipients();
                    payload.recipients = recipients;
                }

                fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify(payload),
                })
                    .then(response => response.json().catch(() => ({})))
                    .then(data => {
                        if (data && data.message) {
                            alert(data.message);
                        } else {
                            alert('Request completed, but no response message was provided.');
                        }
                    })
                    .catch(() => {
                        alert('An error occurred while trying to send this invoice.');
                    })
                    .finally(() => {
                        closeModal();
                    });
            });
        });
    </script>
@endpush

