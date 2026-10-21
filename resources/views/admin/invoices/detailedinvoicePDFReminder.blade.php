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
        padding-bottom: 100px; /* Space for footer */
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
    }

    .payment {
        width: 100%;
        text-align: justify;
        font-size: 12px;
        margin-bottom: 5px;
        padding-top: 5px;
        page-break-inside: avoid; /* Prevents the payment div from splitting across pages */
    }

    .footer {
        position: fixed;
        bottom: 0;
        left: 20px;
        right: 20px;
        width: calc(100% - 40px);
        border-top: 1px solid #000;
        padding-top: 10px;
        padding-bottom: 5px;
        margin-top: 2px;
        background-color: #FFFFFF;
        z-index: 1000;
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
        font-size: 10px;
        margin-bottom: 0;
        padding-bottom: 0px;
        /* Space for footer */
        white-space: pre-line;
        /* Preserve line breaks */
    }

    .logo-right {
        text-align: left;
    }

    /* Content wrapper to prevent overlap with footer */
    .content-wrapper {
        position: relative;
        min-height: calc(100% - 80px);
        padding-bottom: 20px;
    }

    /* Add these new styles */
    .page-break {
        page-break-before: always;
    }

    /* Add styles for new page content */
    .new-page {
        page-break-before: always;
        padding-top: 20px;
        min-height: 842px; /* A4 height */
        position: relative;
    }

    /* Style for additional pages */
    .additional-page {
        page-break-before: always;
        position: relative;
        padding-top: 20px;
        min-height: 842px;
    }
    </style>
</head>
<body>
    <!-- Common footer that will appear on all pages -->
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

    <!-- First page content -->
    <div class="page">
        <div class="content-wrapper">
            <div class="header">
                <div class="header logo-right">
                    @if($entreprise->image)
                        @if(filled($entreprise?->image) && Storage::disk('public')->exists($entreprise->image))
<img src="{{ storage_path('app/public/' . $entreprise->image) }}" alt="Company Logo" class="company-logo">
@endif
                    @endif
                </div>
            </div>

            <br><br><br><br>

            <div class="details">
                <div class="details-left">
                    <p>
                        {{ __('messages.Reminder Date') }}: {{ $last_reminder_date }}<br/>
                        {{ __('messages.Due Date') }}: {{ \Carbon\Carbon::parse($invoice->due_date)->format('d-m-Y') }}<br>
@if(filled($client->client_number))
                        {{ __('messages.Client Number') }} : {{ $client->client_number }}<br>
@endif
                        {{ __('messages.Invoice Number') }} : {{ $invoice->number ?? $invoice_id }}<br>
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

            <br><br><br>

            <div class="invoice-number">
            {{ __('messages.Invoice Reminder') }} : {{$entreprise->invoice_prefix}}-{{ $invoice->number ?? $invoice_id }}
            </div>

            <table>
                <thead>
                    <tr>
                        <th>{{ __('messages.Designation') }}</th>
                        <th>{{ __('messages.Quantity') }}</th>
                        <th>{{ __('messages.Unit Price') }}</th>
                        <th>{{ __('messages.Amount') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="4" style="background-color: #f9f9f9;">
                            <strong>{{ __('messages.Project') }}: {{ $projectinfos->name }}</strong>
                        </td>
                    </tr>
                    @foreach($details as $detail)
                    <tr>
                        <td>{{$detail['description']}}</td>
                        <td>{{$detail['quantity']}}</td>
                        <td>{{number_format($detail['unit_price'], 2, ',', '')}}€</td>
                        <td>{{number_format($detail['total'], 2, ',', '')}}€</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>


            <table class="totals">
                <tr>
                    <td style="text-align: right; width: 80%;">{{ __('messages.Total Excl VAT') }}</td>
                    <td style="text-align: right;">{{number_format($total_htva, 2, ',', '')}}€</td>
                </tr>
                <tr>
                    <td style="text-align: right;">{{ __('messages.VAT') }} {{ $projectinfos->tva }}%</td>
                    <td style="text-align: right;">{{number_format($tva_amount, 2, ',', '')}}€</td>
                </tr>
                <tr>
                    <td style="text-align: right;">{{ __('messages.Total Incl VAT') }}</td>
                    <td style="text-align: right;"><b>{{number_format($total_ttc, 2, ',', '')}}€</b></td>
                </tr>
            </table>


            @if($projectinfos->description || $comment)
            <div class="description" style="margin-bottom: 2px;">
                @if($projectinfos->description)
@if(filled($projectinfos->description))
                <strong> - {{ __('messages.Information') }} :</strong> <br>
                {!! nl2br(e($projectinfos->description)) !!} <br>
@endif
                @endif
                @if($comment)
@if(filled($comment ?? null))
                <strong> - {{ __('messages.Comment') }} : </strong> <br>
               <p style="white-space: pre-wrap;">{!! nl2br(e($comment)) !!}</p>
@endif
               @endif
            </div>
            @endif

            <!-- Modify the payment section container -->
            <div style="position: relative;">
                <!-- Adjust bottom value to account for footer height -->
                <div style="position: absolute; bottom: 150px; width: 100%;"></div>
                
                <div class="payment">
                    - {{ __('messages.Please pay the amount of') }} <strong>{{number_format($total_ttc, 2, ',', '')}}€</strong> 
                    {{ __('messages.on the account mentioned below before') }} {{ \Carbon\Carbon::parse($invoice->due_date)->format('d-m-Y') }}
                    {{ __('messages.mentioning the invoice number') }} <strong>{{ $invoice->number ?? $invoice_id }}</strong>
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
    </div>
</body>
</html>
