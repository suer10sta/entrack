<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <x-favicon />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.Edit Detailed Invoice') }} - DalyTrac</title>
    @vite('resources/css/app.css')
    @livewireStyles
    <link rel="stylesheet" href="{{ asset('css/dalytrac-detailed-invoice-edit.css') }}">
    <script src="{{ asset('js/dalytrac-detailed-invoice-edit.js') }}" defer></script>
</head>
<body class="bg-gray-50 flex h-screen overflow-hidden font-sans">
    @if(Auth::user()->hasRole('Admin'))
        <x-dashboard.sidebar />
    @else
        <x-employees.sidebar />
    @endif
    <div class="flex flex-col flex-1 min-w-0 overflow-y-auto">
        <x-dashboard.navbar />
        @php
            $prefix = 'admin';
            if (!Auth::user()->hasRole('Admin')) {
                if (Auth::user()->hasRole('Responsable de projet')) $prefix = 'Responsable_de_projet';
                elseif (Auth::user()->hasRole('Employee')) $prefix = 'employee';
                elseif (Auth::user()->hasRole('Responsable de projet et gestionnaire')) $prefix = 'Responsable_gestionnaire';
                elseif (Auth::user()->hasRole('SuperAdmin')) $prefix = 'SuperAdmin';
            }
            $cancelRoute = route($prefix.'.invoices.invoicehistory');
            $details = array_values(old('details', json_decode($invoice->details, true) ?: [['description' => '', 'quantity' => '', 'unit_price' => '']]));
            $subtotal = array_sum(array_map(fn ($line) => (float) ($line['quantity'] ?? 0) * (float) ($line['unit_price'] ?? 0), $details));
            $taxRate = (float) $project->tva;
            $formatAmount = fn ($amount) => number_format($amount, 2, app()->getLocale() === 'en' ? '.' : ',', app()->getLocale() === 'en' ? ',' : ' ').' €';
        @endphp
        <main class="detailed-invoice-edit">
            <div class="die-container">
                <nav class="die-breadcrumb" aria-label="{{ __('messages.Invoices') }}"><a href="{{ $cancelRoute }}">{{ __('messages.Invoices') }}</a><span aria-hidden="true">/</span><span>#{{ $invoice->invoice_reference ?? $invoice->number }}</span></nav>
                <header class="die-header">
                    <div><span class="die-eyebrow">{{ __('messages.Invoices') }}</span><h1>{{ __('messages.Edit Detailed Invoice') }}</h1><p>{{ __('messages.detailed_edit_intro') }}</p></div>
                    <span class="die-reference">#{{ $invoice->invoice_reference ?? $invoice->number }}</span>
                </header>
                @if(session('error'))<div class="die-alert die-alert-error" role="alert">{{ session('error') }}</div>@endif
                @if(session('message'))<div class="die-alert die-alert-success" role="status">{{ session('message') }}</div>@endif
                @if($errors->any())
                    <div class="die-alert die-alert-error" role="alert"><strong>{{ __('messages.period_edit_validation') }}</strong><ul>@foreach($errors->all() as $message)<li>{{ $message }}</li>@endforeach</ul></div>
                @endif
                <form id="detailedInvoiceForm" action="{{ route($prefix.'.invoices.updateDetailedInvoice', $invoice->id) }}" method="POST" data-tax-rate="{{ $taxRate }}" data-locale="{{ app()->getLocale() }}">
                    @csrf
                    <input type="hidden" name="project_id" value="{{ $invoice->project_id }}">
                    <div class="die-layout">
                        <div class="die-sections">
                            <section class="die-card" aria-labelledby="die-lines-title">
                                <div class="die-section-heading"><span class="die-step" aria-hidden="true">01</span><div><h2 id="die-lines-title">{{ __('messages.detailed_edit_lines') }}</h2><p>{{ __('messages.detailed_edit_lines_help') }}</p></div></div>
                                <div id="detailsContainer">
                                    @foreach($details as $index => $detail)
                                        <div class="detail-row">
                                            <div class="die-description die-field">
                                                <label for="detail-{{ $index }}-description">{{ __('messages.Detail') }}</label>
                                                <input id="detail-{{ $index }}-description" type="text" name="details[{{ $index }}][description]" value="{{ $detail['description'] ?? '' }}" required>
                                            </div>
                                            <div class="die-field">
                                                <label for="detail-{{ $index }}-quantity">{{ __('messages.Quantity') }}</label>
                                                <input id="detail-{{ $index }}-quantity" class="quantity-input" type="number" min="0" step="0.01" name="details[{{ $index }}][quantity]" value="{{ $detail['quantity'] ?? '' }}" required>
                                            </div>
                                            <div class="die-field">
                                                <label for="detail-{{ $index }}-unit_price">{{ __('messages.Unit Price') }} (€)</label>
                                                <input id="detail-{{ $index }}-unit_price" class="price-input" type="number" min="0" step="0.01" name="details[{{ $index }}][unit_price]" value="{{ $detail['unit_price'] ?? '' }}" required>
                                            </div>
                                            <div class="die-field">
                                                <label for="detail-{{ $index }}-total">{{ __('messages.Total') }} (€)</label>
                                                <input id="detail-{{ $index }}-total" class="total-input" type="text" value="{{ number_format((float) ($detail['quantity'] ?? 0) * (float) ($detail['unit_price'] ?? 0), 2, '.', '') }}" readonly>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="die-line-actions">
                                    <button type="button" id="addRowBtn" class="dt-btn dt-btn-ghost"><span aria-hidden="true">＋</span> {{ __('messages.Add Row') }}</button>
                                    <button type="button" id="deleteRowBtn" class="die-delete">{{ __('messages.Delete Row') }}</button>
                                </div>
                            </section>
                            <section class="die-card" aria-labelledby="die-comment-title">
                                <div class="die-section-heading"><span class="die-step" aria-hidden="true">02</span><div><h2 id="die-comment-title">{{ __('messages.Comment') }} <span class="die-optional">{{ __('messages.period_edit_optional') }}</span></h2><p>{{ __('messages.period_edit_comment_help') }}</p></div></div>
                                <div class="die-field"><label for="comment" class="sr-only">{{ __('messages.Comment') }}</label><textarea name="comment" id="comment" rows="4">{{ old('comment', $invoice->comment) }}</textarea></div>
                            </section>
                        </div>
                        <aside class="die-card die-summary" aria-labelledby="die-summary-title">
                            <h2 id="die-summary-title">{{ __('messages.detailed_edit_summary') }}</h2>
                            <div class="die-project"><span>{{ __('messages.Project') }}</span><strong>{{ $project->name }}</strong></div>
                            <dl>
                                <div><dt>{{ __('messages.Total Amount (HTVA)') }}</dt><dd id="grandTotal">{{ $formatAmount($subtotal) }}</dd></div>
                                <div><dt>{{ __('messages.VAT') }} ({{ $taxRate }} %)</dt><dd id="taxTotal">{{ $formatAmount($subtotal * $taxRate / 100) }}</dd></div>
                                <div class="die-total"><dt>{{ __('messages.period_edit_total_incl_tax') }}</dt><dd id="invoiceTotal">{{ $formatAmount($subtotal * (1 + $taxRate / 100)) }}</dd></div>
                            </dl>
                            <p class="die-summary-help">{{ __('messages.detailed_edit_totals_help') }}</p>
                            <div class="die-save-actions"><button type="submit" class="dt-btn dt-btn-primary">{{ __('messages.Update Invoice') }}</button><a href="{{ $cancelRoute }}" class="dt-btn dt-btn-ghost">{{ __('messages.Cancel') }}</a></div>
                        </aside>
                    </div>
                </form>
            </div>
        </main>
    </div>
    @livewireScripts
</body>
</html>
