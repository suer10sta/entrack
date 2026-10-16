<!DOCTYPE html>
<html lang="en">
<head>
    <x-favicon />
    <meta charset="UTF-8">
    <title>Subscription Invoice {{ $displayInvoiceNumber ?? $invoiceNumber }}</title>
    <style>
        /* Base */
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 24px 32px;
            font-family: DejaVu Sans, sans-serif;
            font-size: 13px;
            color: #111827;
            background-color: #ffffff; /* remove grey background */
        }

        .invoice-wrapper {
            max-width: 800px;
            margin: 0 auto;
            padding: 28px 34px 24px;
            background-color: #ffffff;
        }

        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: center; /* logo and INVOICE on same line, vertically aligned */
            margin-bottom: 24px;
        }

        .logo-block {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logo-block img {
            height: 110px; /* larger logo for better visibility on PDF */
            margin-bottom: -70px; /* nudge logo down a bit so it aligns nicer with the title */
            margin-left: -25px;
            width: auto;
        }

        .logo-text {
            font-size: 22px;
            font-weight: 600;
            color: #1d9bd6;
        }

        .logo-icon {
            font-size: 18px;
            color: #f7b500;
        }

        .invoice-title {
            font-size: 14px;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: #6b7280;
            margin-left: auto;
            text-align: right;
        }

        .invoice-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px solid #d1e9ff;      /* light blue separator, similar to logo color */
            border-bottom: 1px solid #d1e9ff;   /* light blue separator, similar to logo color */
            padding: 12px 0;
            margin-bottom: 22px;
            font-size: 12px;
        }

        .meta-row {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 4px;
        }

        .meta-right .meta-row {
            justify-content: flex-end;
        }

        .label {
            color: #a1a5b2;
        }

        .value {
            font-weight: 600;
            color: #111827;
        }

        /* Two column layout */
        .two-column {
            display: flex;
            gap: 36px;
            margin-bottom: 28px;
        }

        .column {
            flex: 1;
        }

        .section-title {
            font-size: 12px;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: #a1a5b2;
            margin-bottom: 10px;
        }

        .bill-to-name {
            font-weight: 600;
            margin-bottom: 4px;
        }

        .muted {
            color: #6b7280;
        }

        /* Table */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }

        thead tr {
            border-bottom: 1px solid #edf0f5;
        }

        thead th {
            padding: 8px 0;
            text-align: left;
            font-weight: 600;
            color: #a1a5b2;
        }

        tbody td {
            padding: 10px 0;
            border-bottom: 1px solid #f1f3f8;
        }

        .text-right {
            text-align: right;
        }

        .totals {
            margin-top: 16px;
            margin-left: auto;
            width: 240px;
            font-size: 11px;
        }

        .totals tr td {
            padding: 4px 0;
        }

        .totals tr:last-child td {
            padding-top: 10px;
        }

        .totals-label {
            color: #6b7280;
        }

        .totals-value {
            text-align: right;
        }

        .totals-strong .totals-label {
            font-weight: 700;
            color: #111827;
        }

        .totals-strong .totals-value {
            font-weight: 700;
            color: #111827;
        }

        .total-pill {
            display: inline-block;
            background-color: #0284ff;
            color: #ffffff;
            border-radius: 4px;
            padding: 6px 14px;
            font-weight: 600;
            font-size: 11px;
        }

        /* Footer */
        .invoice-footer {
            margin-top: 30px;
            padding-top: 18px;
            border-top: 1px solid #edf0f5;
            font-size: 11px;
            color: #6b7280;
        }

        .payment-note {
            margin-top: 14px;
            padding: 10px 12px;
            border: 1px solid #bfdbfe;
            background: #eff6ff;
            color: #1f2937;
            border-radius: 6px;
            font-size: 11px;
            line-height: 1.4;
        }

        .payment-note strong {
            color: #111827;
        }

        .footer-line {
            margin-bottom: 4px;
        }

        .footer-email {
            color: #0284ff;
        }
    </style>
</head>
<body>
    <div class="invoice-wrapper">
        <div class="invoice-header">
            <div class="logo-block">
                {{-- Replace with your logo image if desired --}}
                <x-application-logo style="font-family:sans-serif;font-weight:800;font-size:28px;letter-spacing:-0.065em;line-height:1.15" />
            </div>
            <div class="invoice-title">INVOICE</div>
        </div>

        <div class="invoice-meta">
            <div class="meta-left">
                <div class="meta-row">
                    <span class="label">Invoice #:</span>
                    <span class="value">{{ $displayInvoiceNumber ?? $invoiceNumber }}</span>
                </div>
                <div class="meta-row">
                    <span class="label">Date:</span>
                    <span class="value">{{ $createdAt->format('d/m/Y') }}</span>
                </div>
            </div>
            <div class="meta-right text-right">
                @if($periodStart && $periodEnd)
                    <div class="meta-row">
                        <span class="label">Period:</span>
                        <span class="value">
                            {{ $periodStart->format('d/m/Y') }} 
                            To 
                            {{ $periodEnd->format('d/m/Y') }}
                        </span>
                    </div>
                @endif
            </div>
        </div>

        <table style="width: 100%; margin-bottom: 28px;">
            <tr>
                <td style="vertical-align: top; width: 50%;">
                    <div class="section-title">Bill To :</div>
                    <div class="bill-to-name">{{ $entreprise->name }}</div>
                    <div class="muted">
                        {{ $entreprise->Address }}<br>
                        {{ $customerCountry }}<br>
                        {{ $entreprise->zip_code }} {{ $entreprise->city }}<br>
                        @if(!empty($customerVat))
                            VAT: {{ $customerVat }}
                        @endif
                    </div>
                </td>
                <td style="vertical-align: top; width: 23%;">
                    <div class="section-title">Billed Entreprise :</div>
                    <div class="bill-to-name">BMe</div>
                    <div class="muted">
                        Rue Colonel Bourg 127-129<br>
                        Belgique<br>
                        1140 Evere<br>
                        VAT : BE0823758741
                    </div>
                </td>
            </tr>
        </table>

        <table>
            <thead>
                <tr>
                    <th>Description</th>
                    <th class="text-right">Qty</th>
                    <th class="text-right">Unit</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Pro Subscription</td>
                    <td class="text-right">1</td>
                    <td class="text-right">
                        {{ number_format($amountHt, 2, ',', ' ') }} {{ strtoupper($currency) }}
                    </td>
                    <td class="text-right">
                        {{ number_format($amountTtc, 2, ',', ' ') }} {{ strtoupper($currency) }}
                    </td>
                </tr>
            </tbody>
        </table>

        <table class="totals">
            <tbody>
                <tr>
                    <td class="totals-label">Subtotal:</td>
                    <td class="totals-value">
                        {{ number_format($amountHt, 2, ',', ' ') }} {{ strtoupper($currency) }}
                    </td>
                </tr>
                <tr>
                    <td class="totals-label">
                        VAT
                        @if($vatRate > 0)
                            ({{ number_format($vatRate, 2, ',', ' ') }}%)
                        @else
                            (0%)
                        @endif
                        :
                    </td>
                    <td class="totals-value">
                        @if($isZeroVat)
                            0,00 {{ strtoupper($currency) }}
                        @else
                            {{ number_format($amountVat, 2, ',', ' ') }} {{ strtoupper($currency) }}
                        @endif
                    </td>
                </tr>
                <tr class="totals-strong">
                    <td class="totals-label">Total:</td>
                    <td class="totals-value">
                        <span class="total-pill">
                            {{ number_format($amountTtc, 2, ',', ' ') }} {{ strtoupper($currency) }}
                        </span>
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="payment-note">
            <strong>Payment already received.</strong> This invoice is for your records only — you don’t need to pay again, as the payment has already been processed via <strong>Stripe</strong>.
        </div>


        <br>

        <div class="invoice-footer">
            <div class="footer-line">Autoliquidation – Art. 21 § 2 du Code TVA belge</div>
            <div class="footer-line">DalyTrac- BMe - 127-129 rue Colonel Bourg, 1140 Bruxelles, Belgique</div>
            <div class="footer-line">support@dalytrac.com - www.dalytrac.com</div>
        </div>
        <div class="invoice-footer">
            <div class="footer-line">Thank you for your trust.</div>
            <div class="footer-email">support@dalytrac.com</div>
        </div>
    </div>
</body>
</html>


