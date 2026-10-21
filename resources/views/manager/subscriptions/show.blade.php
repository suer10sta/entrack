@extends('manager.layouts.app')

@section('content')
<div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-6">
    <div class="min-w-0">
        <p class="dt-overline mb-1">{{ __('messages.Manager area') }}</p>
        <h1 class="text-[22px] sm:text-[24px] font-bold tracking-tight text-[#0B2638] leading-tight truncate">
            {{ $entreprise->name }}
        </h1>
        <p class="text-[13.5px] text-slate-500 mt-1">{{ __('messages.General information, current subscription and Stripe invoices.') }}</p>
    </div>
    <a href="{{ route('manager.subscriptions.index') }}" class="dt-btn dt-btn-ghost flex-shrink-0">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
        </svg>
        {{ __('messages.Back to list') }}
    </a>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">

    {{-- General info --}}
    <div class="dt-card p-5 sm:p-6">
        <div class="flex items-center gap-2.5 mb-4">
            <span class="w-1.5 h-6 bg-[#E7B84B] rounded-full flex-shrink-0"></span>
            <h3 class="dt-section-title">{{ __('messages.General information') }}</h3>
        </div>
        <dl class="space-y-3">
            @foreach([
                [__('messages.Entreprise name'), $entreprise->name],
                [__('messages.Email'),           $entreprise->email],
                [__('messages.Address'),         $entreprise->Address ?? '—'],
                [__('messages.Country'),         $entreprise->country ?? '—'],
                [__('messages.City'),            $entreprise->city ?? '—'],
                [__('messages.Zip Code'),        $entreprise->zip_code ?? '—'],
                [__('messages.TVA'),             $entreprise->tva ?? '—'],
            ] as [$label, $value])
                <div class="flex items-start justify-between gap-4 py-1.5 border-b border-slate-50 last:border-0">
                    <dt class="text-[12px] font-semibold uppercase tracking-[0.06em] text-slate-400 flex-shrink-0 w-32">{{ $label }}</dt>
                    <dd class="text-[13px] text-slate-700 text-right">{{ $value }}</dd>
                </div>
            @endforeach
            <div class="flex items-start justify-between gap-4 py-1.5 border-b border-slate-50">
                <dt class="text-[12px] font-semibold uppercase tracking-[0.06em] text-slate-400 flex-shrink-0 w-32">{{ __('messages.Plan') }}</dt>
                <dd class="text-right">
                    @if($entreprise->type_compt)
                        <span class="dt-badge dt-badge-navy"><span class="dt-dot"></span>{{ $entreprise->type_compt }}</span>
                    @else
                        <span class="text-[13px] text-slate-400">{{ __('messages.Not subscribed') }}</span>
                    @endif
                </dd>
            </div>
            <div class="flex items-start justify-between gap-4 py-1.5 border-b border-slate-50">
                <dt class="text-[12px] font-semibold uppercase tracking-[0.06em] text-slate-400 flex-shrink-0 w-32">{{ __('messages.Trial ends at') }}</dt>
                <dd class="text-[13px] text-slate-700 text-right tabular-nums">{{ $entreprise->trial_ends_at ? $entreprise->trial_ends_at->format('Y-m-d') : '—' }}</dd>
            </div>
            <div class="flex items-start justify-between gap-4 py-1.5">
                <dt class="text-[12px] font-semibold uppercase tracking-[0.06em] text-slate-400 flex-shrink-0 w-32">{{ __('messages.Account status') }}</dt>
                <dd>
                    @if($stripeAccountIsActive ?? false)
                        <span class="dt-badge dt-badge-success"><span class="dt-dot"></span>{{ __('messages.Active') }}</span>
                    @else
                        <span class="dt-badge dt-badge-danger"><span class="dt-dot"></span>{{ __('messages.Inactive') }}</span>
                    @endif
                </dd>
            </div>
        </dl>
    </div>

    {{-- Current subscription --}}
    <div class="dt-card p-5 sm:p-6">
        <div class="flex items-center gap-2.5 mb-4">
            <span class="w-1.5 h-6 bg-[#2D7080] rounded-full flex-shrink-0"></span>
            <h3 class="dt-section-title">{{ __('messages.Current subscription') }}</h3>
        </div>
        @if($hasCurrentStripeSubscription ?? false)
            <dl class="space-y-3">
                @foreach([
                    [__('messages.Stripe ID'), $stripeSubscriptionId ?: '—'],
                    [__('messages.Status'),    $stripeSubscriptionStatus ?: '—'],
                    [__('messages.Created at'), optional($stripeSubscriptionCreatedAt)->format('Y-m-d H:i') ?? '—'],
                ] as [$label, $value])
                    <div class="flex items-start justify-between gap-4 py-1.5 border-b border-slate-50 last:border-0">
                        <dt class="text-[12px] font-semibold uppercase tracking-[0.06em] text-slate-400 flex-shrink-0 w-28">{{ $label }}</dt>
                        <dd class="text-[13px] text-slate-700 text-right tabular-nums font-mono">{{ $value }}</dd>
                    </div>
                @endforeach
                <div class="flex items-start justify-between gap-4 py-1.5">
                    <dt class="text-[12px] font-semibold uppercase tracking-[0.06em] text-slate-400 flex-shrink-0 w-28">{{ __('messages.Ends at') }}</dt>
                    <dd class="text-[13px] text-slate-700 text-right tabular-nums">{{ $stripePlanEndsAt ? $stripePlanEndsAt->format('Y-m-d H:i') : '—' }}</dd>
                </div>
            </dl>
        @else
            <div class="rounded-xl border border-dashed border-slate-200 px-5 py-8 text-center">
                <div class="dt-empty-icon mx-auto mb-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/>
                    </svg>
                </div>
                <p class="text-[13px] font-semibold text-[#0B2638]">{{ __('messages.No active subscription found for this entreprise.') }}</p>
            </div>
        @endif
    </div>
</div>

{{-- Stripe invoices --}}
<div class="dt-card overflow-hidden">
    <div class="px-5 sm:px-6 py-4 border-b border-slate-100 bg-white">
        <h3 class="dt-section-title">{{ __('messages.Stripe invoices') }}</h3>
    </div>

    <div class="manager-table-wrapper overflow-x-auto">
        <table class="dt-table manager-table min-w-full">
            <thead>
                <tr class="dt-table-head">
                    <th class="px-4 py-2.5">{{ __('messages.Date') }}</th>
                    <th class="px-4 py-2.5">{{ __('messages.Total') }}</th>
                    <th class="px-4 py-2.5">{{ __('messages.Status') }}</th>
                    <th class="px-4 py-2.5">{{ __('messages.Invoice #') }}</th>
                    <th class="px-4 py-2.5 text-right">{{ __('messages.Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoices as $invoice)
                    @php
                        $stripeInvoice = $invoice->asStripeInvoice();
                        $invoiceAmount = isset($stripeInvoice->total) ? ((float)$stripeInvoice->total / 100) : null;
                        $rawNumber     = (string)($stripeInvoice->number ?? $invoice->number);
                        $sequenceRow   = \App\Models\SubscriptionInvoiceSequence::where('stripe_invoice_number', $rawNumber)->first();
                        $seq           = $sequenceRow ? (int)$sequenceRow->id : null;
                        $displayNumber = $seq > 0 ? trim($rawNumber . ' - ' . $seq) : $rawNumber;
                        $isPaid        = ($stripeInvoice->status ?? '') === 'paid';
                    @endphp
                    <tr class="manager-card-row" aria-expanded="true">
                        <td data-label="{{ __('messages.Date') }}" data-manager-card-toggle class="manager-toggle-cell px-4 py-2.5">
                            <div class="flex items-start justify-between gap-3">
                                <span class="text-[13px] text-slate-700 tabular-nums">{{ $invoice->date()->format('Y-m-d') }}</span>
                                <span class="shrink-0 mt-0.5 text-slate-400 min-[1366px]:hidden">
                                    <svg class="manager-chevron w-5 h-5" viewBox="0 0 24 24" fill="none"><path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </span>
                            </div>
                        </td>
                        <td data-label="{{ __('messages.Total') }}" class="px-4 py-2.5 text-[13px] font-semibold text-slate-800 tabular-nums">
                            {{ $invoiceAmount !== null ? number_format($invoiceAmount, 2) . ' EUR' : '—' }}
                        </td>
                        <td data-label="{{ __('messages.Status') }}" class="px-4 py-2.5">
                            @if($isPaid)
                                <span class="dt-badge dt-badge-success"><span class="dt-dot"></span>{{ __('messages.Paid') }}</span>
                            @elseif(($stripeInvoice->status ?? '') === 'open')
                                <span class="dt-badge dt-badge-warning"><span class="dt-dot"></span>{{ __('messages.Open') }}</span>
                            @else
                                <span class="dt-badge dt-badge-neutral"><span class="dt-dot"></span>{{ ucfirst($stripeInvoice->status ?? '—') }}</span>
                            @endif
                        </td>
                        <td data-label="{{ __('messages.Invoice #') }}" class="px-4 py-2.5 text-[13px] text-slate-500 font-mono">
                            {{ $displayNumber }}
                        </td>
                        <td data-label="{{ __('messages.Actions') }}" data-span="2" class="px-4 py-2.5 text-right">
                            <div class="flex items-center justify-end gap-2 flex-wrap">
                                @if($invoice->hosted_invoice_url ?? null)
                                    <a href="{{ $invoice->hosted_invoice_url }}" target="_blank"
                                       class="dt-btn dt-btn-ghost !py-1.5 !px-3 !text-[12px]">
                                        {{ __('messages.View in Stripe') }}
                                    </a>
                                @endif
                                <a href="{{ route('manager.subscriptions.invoices.download', ['entreprise' => $entreprise->id, 'invoiceId' => $stripeInvoice->id]) }}"
                                   class="dt-btn dt-btn-teal !py-1.5 !px-3 !text-[12px]">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                                    </svg>
                                    {{ __('messages.Download PDF') }}
                                </a>
                                <button type="button"
                                        class="dt-btn dt-btn-ghost !py-1.5 !px-3 !text-[12px]"
                                        data-open-send-modal
                                        data-invoice-id="{{ $stripeInvoice->id }}">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/>
                                    </svg>
                                    {{ __('messages.Send') }}
                                </button>
                            </div>
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

{{-- Send invoice modal --}}
<div id="send-subscription-invoice-modal"
     class="fixed inset-0 z-50 hidden items-center justify-center bg-[#0B2638]/50 backdrop-blur-sm">
    <div class="dt-card w-full max-w-md mx-4">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="dt-section-title">{{ __('messages.Send') }} {{ __('messages.Subscription invoice') }}</h3>
            <button type="button" data-send-modal-close
                    class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div class="px-5 py-4 space-y-4">
            {{-- Step 1: channel --}}
            <div data-send-step="select">
                <p class="text-[13px] text-slate-700 mb-3 font-medium">{{ __('messages.Choose how to send:') }}</p>
                <div class="space-y-2">
                    <button type="button" data-send-channel-choice="email"
                            class="w-full dt-btn dt-btn-ghost !justify-start gap-3">
                        <svg class="w-4 h-4 text-[#2D7080]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/>
                        </svg>
                        {{ __('messages.Send via Email') }}
                    </button>
                    <button type="button" data-send-channel-choice="peppol"
                            class="w-full dt-btn dt-btn-ghost !justify-start gap-3">
                        <svg class="w-4 h-4 text-[#2D7080]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253"/>
                        </svg>
                        {{ __('messages.Send via Peppol') }}
                    </button>
                    @php
                        $tvaNumber    = strtoupper(trim((string)($entreprise->tva ?? '')));
                        $peppolAllowed = str_starts_with($tvaNumber, 'BE');
                    @endphp
                    @unless($peppolAllowed)
                        <p class="text-[12px] text-amber-600 bg-amber-50 border border-amber-100 rounded-lg px-3 py-2">
                            {{ __('messages.Peppol is only supported for entreprises with a Belgian TVA number.') }}
                        </p>
                    @endunless
                </div>
            </div>

            {{-- Step 2: recipients (email only) --}}
            <div class="hidden space-y-4" data-send-step="recipients">
                <p class="text-[13px] text-slate-700 font-medium">{{ __('messages.Select Recipients (Email):') }}</p>
                <div class="space-y-2 text-[13px] text-slate-700">
                    <label class="flex items-center gap-2.5 cursor-pointer">
                        <input type="checkbox" data-recipient-checkbox="client" checked
                               class="h-4 w-4 rounded border-slate-300 text-[#2D7080] focus:ring-[#2D7080]">
                        <span>
                            <span class="font-medium">{{ __('messages.Client') }}</span>
                            <span class="text-slate-400" data-client-email-text></span>
                        </span>
                    </label>
                    <label class="flex items-center gap-2.5 cursor-pointer">
                        <input type="checkbox" data-recipient-checkbox="copy" checked
                               class="h-4 w-4 rounded border-slate-300 text-[#2D7080] focus:ring-[#2D7080]">
                        <span>
                            <span class="font-medium">{{ __('messages.CopyRecipient') }}</span>
                            <span class="text-slate-400" data-copy-email-text></span>
                        </span>
                    </label>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" data-send-recipients-back class="dt-btn dt-btn-ghost !text-[12px]">{{ __('messages.Back') }}</button>
                    <button type="button" data-send-recipients-next class="dt-btn dt-btn-teal !text-[12px]">{{ __('messages.Next') }}</button>
                </div>
            </div>

            {{-- Step 3: confirm --}}
            <div class="hidden space-y-4" data-send-step="confirm">
                <p class="text-[13px] text-slate-700 bg-slate-50 rounded-xl border border-slate-100 px-4 py-3" data-send-confirm-text></p>
                <div class="flex justify-end gap-2">
                    <button type="button" data-send-back class="dt-btn dt-btn-ghost !text-[12px]">{{ __('messages.Back') }}</button>
                    <button type="button" data-send-confirm class="dt-btn dt-btn-primary !text-[12px]">{{ __('messages.Confirm') }}</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const csrf          = document.querySelector('meta[name="csrf-token"]')?.content;
    const sendUrlTmpl   = "{{ route('manager.subscriptions.invoices.send', ['entreprise' => $entreprise->id, 'invoiceId' => '__INVOICE_ID__']) }}";
    const peppolAllowed = @json($peppolAllowed);
    const recipientEmail      = @json($entreprise->email);
    const copyRecipientEmail  = @json($subscriptionInvoiceCopyRecipient ?? 'Dessin@bureaumercier.com');

    const modal           = document.getElementById('send-subscription-invoice-modal');
    const stepSelect      = modal.querySelector('[data-send-step="select"]');
    const stepRecipients  = modal.querySelector('[data-send-step="recipients"]');
    const stepConfirm     = modal.querySelector('[data-send-step="confirm"]');
    const confirmTextEl   = modal.querySelector('[data-send-confirm-text]');
    const clientCheckbox  = modal.querySelector('[data-recipient-checkbox="client"]');
    const copyCheckbox    = modal.querySelector('[data-recipient-checkbox="copy"]');
    const clientEmailText = modal.querySelector('[data-client-email-text]');
    const copyEmailText   = modal.querySelector('[data-copy-email-text]');

    let currentInvoiceId = null;
    let selectedChannel  = null;
    let selectedRecipients = [];

    function showStep(step) {
        [stepSelect, stepRecipients, stepConfirm].forEach(s => s.classList.add('hidden'));
        step.classList.remove('hidden');
    }

    function openModal(invoiceId) {
        currentInvoiceId = invoiceId; selectedChannel = null; selectedRecipients = [];
        if (clientEmailText) clientEmailText.textContent = recipientEmail ? ` (${recipientEmail})` : '';
        if (copyEmailText)   copyEmailText.textContent   = copyRecipientEmail ? ` (${copyRecipientEmail})` : '';
        if (clientCheckbox) { clientCheckbox.checked = !!recipientEmail; clientCheckbox.disabled = !recipientEmail; }
        if (copyCheckbox)   { copyCheckbox.checked = !!copyRecipientEmail; copyCheckbox.disabled = !copyRecipientEmail; }
        showStep(stepSelect);
        modal.classList.remove('hidden'); modal.classList.add('flex');
    }

    function closeModal() {
        modal.classList.add('hidden'); modal.classList.remove('flex');
        currentInvoiceId = null;
    }

    document.querySelectorAll('[data-open-send-modal]').forEach(btn => {
        btn.addEventListener('click', () => openModal(btn.dataset.invoiceId));
    });
    modal.querySelectorAll('[data-send-modal-close]').forEach(btn => btn.addEventListener('click', closeModal));
    modal.addEventListener('click', e => { if (e.target === modal) closeModal(); });

    modal.querySelectorAll('[data-send-channel-choice]').forEach(btn => {
        btn.addEventListener('click', function () {
            const ch = this.dataset.sendChannelChoice;
            if (ch === 'peppol' && !peppolAllowed) { alert(@js(__('messages.Peppol not supported for this entreprise.'))); return; }
            selectedChannel = ch;
            if (ch === 'email') { showStep(stepRecipients); return; }
            confirmTextEl.textContent = 'Send this invoice via Peppol?';
            showStep(stepConfirm);
        });
    });

    modal.querySelector('[data-send-recipients-back]').addEventListener('click', () => { selectedChannel = null; showStep(stepSelect); });
    modal.querySelector('[data-send-recipients-next]').addEventListener('click', () => {
        selectedRecipients = [clientCheckbox?.checked && recipientEmail, copyCheckbox?.checked && copyRecipientEmail].filter(Boolean);
        if (!selectedRecipients.length) { alert(@js(__('messages.Select at least one recipient.'))); return; }
        confirmTextEl.textContent = `Send via Email to: ${selectedRecipients.join(', ')}?`;
        showStep(stepConfirm);
    });
    modal.querySelector('[data-send-back]').addEventListener('click', () => { selectedChannel = null; showStep(stepSelect); });

    modal.querySelector('[data-send-confirm]').addEventListener('click', function () {
        if (!currentInvoiceId || !selectedChannel || !csrf) return;
        const url     = sendUrlTmpl.replace('__INVOICE_ID__', currentInvoiceId);
        const payload = { channel: selectedChannel };
        if (selectedChannel === 'email') payload.recipients = selectedRecipients;
        fetch(url, { method:'POST', headers:{ 'Content-Type':'application/json', 'Accept':'application/json', 'X-CSRF-TOKEN': csrf }, body: JSON.stringify(payload) })
            .then(r => r.json().catch(() => ({})))
            .then(d => alert(d.message || 'Done.'))
            .catch(() => alert(@js(__('messages.Error sending invoice'))))
            .finally(closeModal);
    });
});
</script>
@endpush
