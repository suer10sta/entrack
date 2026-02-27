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
                        <img src="{{ storage_path('app/public/' . Auth::user()->entreprise->image) }}" alt="Company Logo" class="company-logo">
                    @endif
                </div>
            </div>

            <br><br>

            <div class="details">
                <div class="details-left">
                    <p>{{ __('messages.Reminder Date') }}: {{$last_reminder_date}}<br>
                        {{ __('messages.Due Date') }}:
                        {{ \Carbon\Carbon::parse($invoice->due_date)->format('d-m-Y') }}<br>
                        {{ __('messages.Client Number') }} : {{ $client->client_number }} <br>
                        {{ __('messages.Invoice Number') }} : {{date('ym')}}@if(request()->has('invoice_number')){{request()->input('invoice_number')}}@else{{$invoice_id }}@endif<br>
                        {{ __('messages.Reminder Number') }} : {{$reminder_number}}
                        @if($isOverdue)
                            <br>
                            <span style="color: red;">{{ __('messages.Days overdue') }} : {{ $days_overdue }}</span>
                        @endif
                    </p>
                </div>
                <div class="details-right">
                    <strong>{{ $client->name }}</strong><br>
                    {{ $client->billing_street }}<br>
                    {{ $client->billing_zip }}-{{ $client->billing_city }}<br>
                    {{ $client->billing_country }}<br>
                    {{ __('messages.Client TVA') }}: {{ $client->TVA }}
                </div>
            </div>
            <br><br><br><br><br> 
            <div class="invoice-number">
                {{ __('messages.Invoice Reminder') }} :
                {{$entreprise->invoice_prefix}}-{{date('ym')}}@if(request()->has('invoice_number')){{request()->input('invoice_number')}}@else{{$invoice_id }}@endif
            </div>

            @if(isset($invoice) && $invoice->hours !== null)
                @php
                    $budget = $invoice->hours * $projectinfos->cost_per_hour;
                @endphp
            @else
                @php
                    $budget = $invoice->total_htva;
                @endphp
            @endif

            <table>
                <thead>
                    <tr>
                        <th>{{ __('messages.Designation') }}</th>
                        @if(isset($invoice) && $invoice->hours !== null)
                            <th>{{ __('messages.Hours') }}</th>
                            <th>{{ __('messages.Cost per Hour') }}</th>
                        @else
                            <th>{{ __('messages.Quantity') }}</th>
                            <th>{{ __('messages.Unit Price') }}</th>
                        @endif
                        <th>{{ __('messages.Amount') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{strtoupper($projectinfos->name)}}</td>
                        @if(isset($invoice) && $invoice->hours !== null)
                            <td>{{$invoice->hours}}</td>
                            <td>{{$projectinfos->cost_per_hour}}€</td>
                        @else
                            <td>1</td>
                            <td>{{$budget}}€</td>
                        @endif
                        <td>{{$budget}}€</td>
                    </tr>
                </tbody>
            </table>

            <table class="totals">
                <tr>
                    <td style="text-align: right; width: 80%;">{{ __('messages.Total Excl VAT') }}</td>
                    <td style="text-align: right;">{{$budget}}€</td>
                </tr>
                <tr>
                    <td style="text-align: right;">{{ __('messages.VAT') }} {{ $projectinfos->tva }}%</td>
                    <td style="text-align: right;">
                        {{number_format($budget* ($projectinfos->tva / 100), 2, ',', '')}}€</td>
                </tr>
                <tr>
                    <td style="text-align: right;">{{ __('messages.Total Incl VAT') }}</td>
                    <td style="text-align: right;">
                        <b>{{number_format($budget + ($budget*($projectinfos->tva/100)), 2, ',', '')}}€</b>
                    </td>
                </tr>
            </table>

            <div class="description">
                <strong>- {{ __('messages.Information') }} :</strong><br>
                {!! nl2br(e($projectinfos->description)) !!} <br>
                <strong>- {{ __('messages.Comment') }} :</strong><br>
               <p style="white-space: pre-wrap;">{!! nl2br(e($comment)) !!}</p> 
            </div>

            <div class="payment">
                - {{ __('messages.Please pay the amount of') }}
                <strong>{{number_format($budget + ($budget*($projectinfos->tva/100)), 2, ',', '')}}€</strong>
                {{ __('messages.on the account mentioned below before') }}
                {{ \Carbon\Carbon::parse($invoice->due_date)->format('d-m-Y') }}
                {{ __('messages.mentioning the invoice number') }} <strong>{{ $invoice->created_at->format('ym') }}@if(request()->has('invoice_number')){{request()->input('invoice_number')}}@else{{$invoice_id }}@endif</strong><br>
                - {{ __('messages.In case of intra-community delivery for services') }}:  "Autoliquidation Art 21 § 2 du code TVA belge"
                <br>
                - {{ __('messages.See general conditions in annex') }}<br>
                - {{ __('messages.Thank you for your trust') }}
            </div>
        </div>

        <div class="footer">
            <div class="footer-content">
                <div class="footer-left footer-text">
                    <b>{{ $entreprise->name }}</b><br>
                    <b>{{ __('messages.VAT Number') }}:</b> {{ $entreprise->tva}}
                </div>
                <div class="footer-center footer-text">
                    {{ $entreprise->Address }}<br>
                    {{ __('messages.Phone') }}: 0{{ $entreprise->phone }}
                </div>
                <div class="footer-right footer-text">
                    <b>{{ __('messages.BANK') }}</b> : {{ $entreprise->bank_name }}<br>
                    <b>{{ __('messages.IBAN') }}</b> : {{ $entreprise->iban }}<br>
                    <b>{{ __('messages.SWIFT/BIC') }}</b> : {{ $entreprise->swift }} / {{ $entreprise->bic }}
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
                    <b>{{ $entreprise->name }}</b><br>
                    <b>Numéro TVA:</b> {{ $entreprise->tva }}
                </div>
                <div class="footer-center footer-text">
                    {{ $entreprise->Address }}<br>
                    Tél: 0{{ $entreprise->phone }}
                </div>
                <div class="footer-right footer-text">
                    <b>BANK</b> : {{ $entreprise->bank_name }}<br>
                    <b>IBAN</b> : {{ $entreprise->iban }}<br>
                    <b>SWIFT/BIC</b> : {{ $entreprise->swift }} / {{ $entreprise->bic }}
                </div>
            </div>
        </div>
    </div>
</body>

</html>
