<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
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
            position: relative;
        }

        .container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            width: 100%;
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }

        .header img {
            max-width: 150px;
            height: auto;
        }

        .details-container {
            width: 100%;
            position: relative;
            margin-bottom: 30px;
            min-height: 80px;
        }

        .details-left {
            position: absolute;
            left: 0;
            top: 0;
            width: 50%;
            font-size: 12px;
        }

        .details-right {
            position: absolute;
            right: 0;
            top: 0;
            width: 50%;
            font-size: 12px;
            text-align: right;
        }

        .invoice-number {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 20px;
            margin-top: 100px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table, th, td {
            border: 1px solid #CCCCCC;
        }

        th, td {
            padding: 8px;
            text-align: left;
            font-size: 12px;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        .totals {
            width: 100%;
            margin-top: 10px;
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
            margin-bottom: 20px;
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
            padding-left: 20px;
            padding-right: 20px;
        }

        .page {
            padding: 20px;
            page-break-after: always;
        }
        
        .page:last-child {
            page-break-after: avoid;
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="content-wrapper">
            <div class="header">
                <div class="logo-right">
                    @if($entreprise->image)
                        <img src="{{ storage_path('app/public/' . $entreprise->image) }}" alt="Company Logo" class="company-logo">
                    @endif
                </div>
            </div>

            <div class="details-container">
                <div class="details-left">
                    <p>
                        {{ __('messages.Credit Note Date') }}: {{ date('d-m-Y') }}<br/>
                    </p>
                </div>
                <div class="details-right">
                    <strong>{{ $client->name }}</strong><br />
                    {{ $client->billing_street }} <br />
                    {{ $client->billing_zip }}-{{ $client->billing_city }} <br />
                    {{ $client->billing_country }}<br />
                    {{ __('messages.Client VAT') }}: {{ $client->TVA }}
                </div>
            </div>

            <div class="invoice-number">
                {{ __('messages.Credit Note') }} : NC-{{date('ym')}}{{$credit_note_number}}
                <br>{{ __('messages.Ref') }}: {{ $invoice->invoice_reference ?? (($entreprise->invoice_prefix ?? '').'-'.date('ym').$invoice_number) }}
            </div>

            <table>
                <thead>
                    <tr>
                        <th>{{ __('messages.Designation') }}</th>
                        <th>{{ __('messages.Quantity') }}</th>
                        <th>{{ __('messages.Unit Price') }}</th>
                        <th>{{ __('messages.Amount') }}</th>
                        @if($partial == 1)
                        <th>{{ __('messages.Original Amount') }}</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="@if($partial == 1)5 @else 4 @endif" style="background-color: #f9f9f9;">
                            <strong>{{ __('messages.Project') }}: {{ $projectinfos->name }}</strong>
                        </td>
                    </tr>
                    @foreach($details as $detail)
                    <tr>
                        <td>{{$detail['description']}}</td>
                        <td>-{{ number_format($detail['quantity'], 2, ',', '') }}</td>
                        <td>{{number_format($detail['unit_price'], 2, ',', '')}}€</td>
                        <td>-{{number_format($detail['total'], 2, ',', '')}}€</td>
                        @if($partial == 1)
                        <td>{{number_format(abs($original_total), 2, ',', '')}}€</td>
                        @endif
                    </tr>
                    @endforeach
                </tbody>
            </table>


            <table class="totals">
                <tr>
                    <td style="text-align: right; width: 80%;">{{ __('messages.Total Excl VAT') }}</td>
                    <td style="text-align: right;">-{{number_format($total_htva, 2, ',', '')}}€</td>
                </tr>
                <tr>
                    <td style="text-align: right;">{{ __('messages.VAT') }} {{ $projectinfos->tva }}%</td>
                    <td style="text-align: right;">-{{number_format($tva_amount, 2, ',', '')}}€</td>
                </tr>
                <tr>
                    <td style="text-align: right;">{{ __('messages.Total Incl VAT') }}</td>
                    <td style="text-align: right;"><b>-{{number_format($total_ttc, 2, ',', '')}}€</b></td>
                </tr>
            </table>


            @if($projectinfos->description || $comment)
            <div class="description" style="margin-bottom: 2px;">
                @if($projectinfos->description)
                <strong> - {{ __('messages.Information') }} :</strong> <br>
                {!! nl2br(e($projectinfos->description)) !!} <br>
                @endif
                @if($comment)
                <strong> - {{ __('messages.Comment') }} : </strong> <br>
               <p style="white-space: pre-wrap;">{!! nl2br(e($comment)) !!}</p>
               @endif
            </div>
            @endif

            <div class="payment">
                - {{ __('messages.This credit note cancels') }} @if($partial){{ __('messages.partially') }} @else {{ __('messages.totally') }} @endif {{ __('messages.the invoice') }} {{ $invoice->invoice_reference ?? (($entreprise->invoice_prefix ?? '').'-'.date('ym').$invoice_number) }}
                <br>
                - {{ __('messages.Intracommunity delivery services note') }}
                <br>
                - {{ __('messages.See general conditions in annex') }}
            </div>

            <div class="footer">
                <div class="footer-content">
                    <div class="footer-left footer-text">
                        <b>{{ $entreprise->name }}</b><br>
                        <b>{{ __('messages.VAT Number') }}:</b> {{ $entreprise->tva }}
                    </div>
                    <div class="footer-center footer-text">
                        {{ $entreprise->Address }}<br>
                        {{ __('messages.Phone') }}: 0{{ $entreprise->phone }}
                    </div>
                    <div class="footer-right footer-text">
                        <b>{{ __('messages.BANK') }}</b>: {{ $entreprise->bank_name }}<br>
                        <b>{{ __('messages.IBAN') }}</b>: {{ $entreprise->iban }}<br>
                        <b>{{ __('messages.SWIFT/BIC') }}</b>: {{ $entreprise->swift }} / {{ $entreprise->bic }}
                    </div>
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
                    <b>{{ __('messages.Numéro TVA:') }}</b> {{ $entreprise->tva }}
                </div>
                <div class="footer-center footer-text">
                    {{ $entreprise->Address }}<br>
                    Tél: 0{{ $entreprise->phone }}
                </div>
                <div class="footer-right footer-text">
                    <b>{{ __('messages.BANK') }}</b>: {{ $entreprise->bank_name }}<br>
                    <b>{{ __('messages.IBAN') }}</b>: {{ $entreprise->iban }}<br>
                    <b>{{ __('messages.SWIFT/BIC') }}</b>: {{ $entreprise->swift }} / {{ $entreprise->bic }}
                </div>
            </div>
        </div>
    </div>
</body>
</html> 
