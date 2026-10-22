<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
    body {
        font-family: Tahoma, sans-serif;
        font-size: 12px;
        color: #333333;
        background-color: #FFFFFF;
        margin: 0;
        padding: 0;
    }

    .page {
        position: relative;
        width: 595px;
        min-height: 842px;
        /* A4 height */
        margin: 0 auto;
        padding: 20px;
        page-break-after: always;
    }

    /* Remove page-break-after from the last page */
    .page:last-of-type {
        page-break-after: avoid;
    }

    .header {
        width: 100%;
        text-align: center;
        margin-bottom: 20px;
    }

    .header img {
        width: 100px;
        height: auto;
    }

    .details {
        width: 100%;
        display: flex;
        justify-content: space-between;
        margin-bottom: 20px;
        position: relative;
    }

    .details-left {
        width: 48%;
        font-size: 12px;
    }

    .details-right {
        width: 48%;
        font-size: 12px;
        text-align: right;
        position: absolute;
        right: 0;
        top: 0;
    }

    .invoice-number {
        text-align: center;
        font-size: 14px;
        font-weight: bold;
        margin-bottom: 20px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
    }

    table,
    th,
    td {
        border: 1px solid #CCCCCC;
    }

    th,
    td {
        padding: 8px;
        text-align: left;
        font-size: 12px;
    }

    th {
        background-color: #f2f2f2;
        font-weight: bold;
    }

    .totals {
        border: none;
        margin-top: 20px;
        text-align: right;
    }

    .totals td {
        border: none;
    }

    .description {
        width: 100%;
        text-align: justify;
        font-size: 12px;
        border-top: 2px double #CCCCCC;
        border-bottom: 2px double #CCCCCC;
        padding: 10px 0;
        margin-bottom: 20px;
    }

    .payment {
        width: 100%;
        text-align: justify;
        font-size: 12px;
        margin-bottom: 20px;
    }

    .footer {
    position: fixed;
    bottom: 4px;
    left: 20px; /* Match page padding */
    right: 20px; /* Match page padding */
    width: calc(100% - 40px); /* Account for left and right padding */
    border-top: 1px solid #000;
    padding-top: 20px;
}

    .footer-content {
    position: relative;
    width: 100%;
    height: 50px;
    display: flex;
    justify-content: space-between;
}

    .footer-left {
        position: absolute;
        left: 0;
        text-align: left;
    }

    .footer-center {
        position: absolute;
        left: 50%;
        transform: translateX(-50%);
        text-align: center;
    }

    .footer-right {
        position: absolute;
        right: 0;
        text-align: right;
    }

    .footer-text {
        color: #333333;
        line-height: 1.4;
    }

    .general-conditions {
        text-align: justify;
        line-height: 1.1;
        margin-bottom: 20px;
        font-size: 10px;
        /* Space for footer */
        white-space: pre-line;
        /* Preserve line breaks */
    }

    .logo-right {
        text-align: left;
    }

    /* Content wrapper to prevent overlap with footer */
    .content-wrapper {
        padding-bottom: 100px;
        /* Space for footer */
    }
    </style>
</head>

<body>
    <!-- First page - Invoice -->
    <div class="page">
        <div class="content-wrapper">
            <div class="header">
                <div class="logo-right">
                    @if(Auth::user()->entreprise && Auth::user()->entreprise->image)
                        @if(filled(Auth::user()->entreprise?->image) && Storage::disk('public')->exists(Auth::user()->entreprise->image))
<img src="{{ storage_path('app/public/' . Auth::user()->entreprise->image) }}" alt="Company Logo" class="company-logo">
@endif
                    @endif
                </div>
            </div>

            <br><br>

            <div class="details">
                <div class="details-left">
                <p>{{ __('messages.Reminder Date') }}: {{$last_reminder_date}}<br>
                        {{ __('messages.Due Date') }}:
                         {{ \Carbon\Carbon::parse($invoice->due_date)->format('d-m-Y') }}<br>  
@if(filled($client->client_number))
                        {{ __('messages.Client Number') }} : {{ $client->client_number }} <br>
@endif
                        {{ __('messages.Invoice Number') }} : {{ $invoice->invoice_reference ?? $invoice->number ?? \App\Support\InvoiceNumbering::preview($entreprise, $invoice_id) }}<br>
                        {{ __('messages.Reminder Number') }} : {{$reminder_number}}
                        @if($isOverdue)
                            <br>
                            <span style="color: red;">{{ __('messages.Days overdue') }} : {{ $days_overdue }}</span>
                        @endif
                    </p>
                </div>
                <div class="details-right">
                    @include('partials.invoice-client-details')
                </div>
            </div>

            <br><br><br><br><br>

            <div class="invoice-number">
                {{ __('messages.Invoice Reminder') }} :
                {{ \App\Support\InvoiceNumbering::reference($entreprise, $invoice ?? $invoice_id) }}
            </div>

            <table>
                <thead>
                    <tr>
                        <th>{{ __('messages.Designation') }}</th>
                        <th>{{ __('messages.Quantity') }}</th>
                        <th>{{ __('messages.Percentage') }}</th>
                        <th>{{ __('messages.Principal Amount') }}</th>
                        <th>{{ __('messages.Advance Payment') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{strtoupper($projectinfos->name)}}</td>
                        <td>
                            {{ __('messages.public_pricing_users_solo') }}
                        </td>
                        <td>{{$percentage}}%</td>
                        <td>{{$projectinfos->budget}}€</td>
                        <td>
                            {{$amount}}€
                        </td>
                    </tr>
                </tbody>
            </table>

            <style>
            .totals {
                border: none;
            }
            </style>

            <table class="totals">
                <tbody>
                    <tr>
                        <td colspan="3" class="totals">
                            <div class="label">{{ __('messages.Total Excl VAT') }}</div>
                            <div class="value">{{$amount}}€</div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="3" class="totals">
                            <div class="label">{{ __('messages.VAT') }} {{ $projectinfos->tva }}%
                            </div>
                            <div class="value">{{number_format($amount* ($projectinfos->tva / 100), 2, ',', '')}}€</div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="3" class="totals">
                            <div class="label">{{ __('messages.Total Incl VAT') }}</div>
                            <div class="value">
                                <b>{{number_format($amount + ($amount*($projectinfos->tva/100)), 2, ',', '')}}€</b>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div class="description">
@if(filled($projectinfos->description))
                <strong> - {{ __('messages.Information') }} :</strong> <br>
                {!! nl2br(e($projectinfos->description)) !!} <br>
@endif
@if(filled($comment ?? null))
                <strong> - {{ __('messages.Comment') }} : </strong> <br>
               <p style="white-space: pre-wrap;">{!! nl2br(e($comment)) !!}</p>
@endif
            </div>

            <div class="payment">
                - {{ __('messages.Please pay the amount of') }}
                <strong>{{number_format($amount + ($amount*($projectinfos->tva/100)), 2, ',', '')}}€</strong> {{ __('messages.on the account mentioned below before') }}
                {{ \Carbon\Carbon::parse($invoice->due_date)->format('d-m-Y') }}
                {{ __('messages.mentioning the invoice number') }} <strong>{{ $invoice->invoice_reference ?? $invoice->number ?? \App\Support\InvoiceNumbering::preview($entreprise, $invoice_id) }}</strong>
                <br>
@if(\App\Support\InvoicePresentation::showReverseChargeNotice($entreprise))
                - {{ __('messages.In case of intra-community delivery for services') }}:  "Autoliquidation Art 21 § 2 du code TVA belge"
@endif
                <br>
                - {{ __('messages.See general conditions in annex') }}
                <br>
                - {{ __('messages.Thank you for your trust') }}

            </div>
        </div>

        <div class="footer">
            <div class="footer-content">
                <div class="footer-left footer-text">
@include('partials.invoice-company-details', ['section' => 'identity'])
</div>
                <div class="footer-center footer-text">
@include('partials.invoice-company-details', ['section' => 'contact'])
</div>
                <div class="footer-right footer-text">
@include('partials.invoice-company-details', ['section' => 'bank'])
</div>
            </div>
        </div>
    </div>

    <!-- Second page - General Conditions -->
    <div class="page">
        <div class="content-wrapper">
            <h2>{{ __('messages.General Conditions') }} :</h2>
            <div class="general-conditions">
                <p>{!! nl2br(e($entreprise->general_conditions)) !!}</p>
            </div>
        </div>

        <div class="footer">
            <div class="footer-content">
                <div class="footer-left footer-text">
@include('partials.invoice-company-details', ['section' => 'identity'])
</div>
                <div class="footer-center footer-text">
@include('partials.invoice-company-details', ['section' => 'contact'])
</div>
                <div class="footer-right footer-text">
@include('partials.invoice-company-details', ['section' => 'bank'])
</div>
            </div>
        </div>
    </div>
</body>

</html>
