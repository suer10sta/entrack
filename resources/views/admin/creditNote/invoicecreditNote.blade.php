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
        min-height: 100vh;
    }

    .container {
        width: 595px;
        margin: 0 auto;
        padding: 20px;
        padding-bottom: 100px;
        /* to ensure there's space for the footer */
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

    .header div {
        font-size: 14px;
        font-weight: bold;
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
        margin-top: 20px;
        text-align: right;
        font-size: 12px;
    }

    .totals .label,
    .totals .value {
        display: inline-block;
        width: 50%;
    }

    .totals .value {
        font-weight: bold;
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
        bottom: 0;
        width: 100%;
        border-top: 1px solid #000;
        padding-top: 20px;
        padding-bottom: 20px;

    }
    .logo-right {
        text-align: left;
    }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <div class="header logo-right">
            @if($entreprise->image)
            <img src="{{ Storage::disk('public')->url(Auth::user()->entreprise->image) }}" alt="Company Logo" class="company-logo">
                    @endif
            </div>
        </div>

        <br><br><br><br>

        <div class="details">
            <div class="details-left"> 
                <p>{{ __('messages.Credit Note Date') }} : <?php echo date('d-m-Y'); ?><br />
                    
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

        <br><br><br><br>
        @php
        $invoicenumber = '';
        if(request()->has('invoice_number')) {
        $invoicenumber = request()->input('invoice_number');
        }
        @endphp
        <div class="invoice-number">
            {{ __('messages.Credit Note') }} : NC-{{date('ym')}}{{ str_pad($last_credit_note_number + 1, 3, '0', STR_PAD_LEFT) }}
             <br>{{ __('messages.Ref') }}: {{$entreprise->invoice_prefix}}-{{date('ym')}}@if(request()->has('invoice_number')){{request()->input('invoice_number')}}@else{{$invoice_id }} @endif
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
                    <td>{{strtoupper($projectinfos->name)}}</td>
                    <td>
                        -1
                    </td>
                    <td>-{{$budget}}€</td>
                    <td>
                        -{{abs($budget)}}€
                    </td>
                    @if($partial == 1)
                    <td>
                   {{ number_format(abs($invoice->total_ttc), 2, ',', '')}}€
                    </td>
                    @endif
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
                        <div class="value">-{{number_format(abs($budget), 2, ',', '')}}€</div>
                    </td>
                </tr>
                <tr>
                    <td colspan="3" class="totals">
                        <div class="label">{{ __('messages.VAT') }} {{ number_format($projectinfos->tva, 2, ',', '') }}%
                        </div>
                        <div class="value">-{{number_format(abs($budget* ($projectinfos->tva / 100)), 2, ',', '')}}€</div>
                    </td>
                </tr>
                <tr>
                    <td colspan="3" class="totals">
                        <div class="label">{{ __('messages.Total Incl VAT') }}</div>
                        <div class="value"><b>-{{number_format(abs($budget + ($budget*($projectinfos->tva/100))), 2, ',', '')}}€</b></div>
                    </td>
                </tr>
            </tbody>
        </table>

        @if($projectinfos->description || $comment)
        <div class="description">
            @if($projectinfos->description)
            <strong> - {{ __('messages.Information') }} :</strong><br>
            {!! nl2br(e($projectinfos->description)) !!}<br>
            @endif
            @if($comment)
            <strong>{{ __('messages.Comment') }}:</strong><br>
            {!! nl2br(e($comment)) !!}
            @endif
        </div>
        @endif

        <div class="payment">
            - {{ __('messages.This credit note cancels') }} @if($partial){{ __('messages.partially') }} @else {{ __('messages.totally') }} @endif {{ __('messages.the invoice') }} {{$entreprise->invoice_prefix}}-{{date('ym')}}{{$invoice->number}}
            <br>
            - {{ __('messages.Intracommunity delivery services note') }}
            <br>
            - {{ __('messages.See general conditions in annex') }}
        </div>
        
        <div style="display: flex; justify-content: center; align-items: center; flex-direction: column;">
            @if(Auth::user()->hasRole('Admin'))
            <form action="{{ route('admin.creditNote.confirm', ['id' => $invoice->id, 'noteType' => $type, 'noteAmount' => $budget]) }}" method="POST" style="display: inline-block;">
                @csrf
                <button type="submit" style="padding: 6px 12px; margin-bottom: 6px; font-size: 14px; font-weight: 400; line-height: 1.42857143; text-align: center; white-space: nowrap; vertical-align: middle; -ms-touch-action: manipulation; touch-action: manipulation; cursor: pointer; -webkit-user-select: none; -moz-user-select: none; -ms-user-select: none; user-select: none; background-image: none; border: 1px solid transparent; border-radius: 4px; color: #fff; background-color: green; border-color: green;">
                    {{ __('messages.Confirm') }} 
                </button>
            </form>
            <a href="{{route('admin.invoices.invoicehistory')}}" style="padding: 6px 12px; margin-bottom: 6px; font-size: 14px; font-weight: 400; line-height: 1.42857143; text-align: center; white-space: nowrap; vertical-align: middle; -ms-touch-action: manipulation; touch-action: manipulation; cursor: pointer; -webkit-user-select: none; -moz-user-select: none; -ms-user-select: none; user-select: none; background-image: none; border: 1px solid transparent; border-radius: 4px; color: #fff; background-color: red; border-color: red;">
                {{ __('messages.Decline') }}
            </a>
            @endif

            @if(Auth::user()->hasRole('Responsable de projet'))
            <form action="{{ route('Responsable_de_projet.creditNote.confirm', ['id' => $invoice->id, 'noteType' => $type, 'noteAmount' => $budget]) }}" method="POST" style="display: inline-block;">
                @csrf
                <button type="submit" style="padding: 6px 12px; margin-bottom: 6px; font-size: 14px; font-weight: 400; line-height: 1.42857143; text-align: center; white-space: nowrap; vertical-align: middle; -ms-touch-action: manipulation; touch-action: manipulation; cursor: pointer; -webkit-user-select: none; -moz-user-select: none; -ms-user-select: none; user-select: none; background-image: none; border: 1px solid transparent; border-radius: 4px; color: #fff; background-color: green; border-color: green;">
                    Confirm 
                </button>
            </form>
            <a href="{{route('Responsable_de_projet.invoices.invoicehistory')}}" style="padding: 6px 12px; margin-bottom: 6px; font-size: 14px; font-weight: 400; line-height: 1.42857143; text-align: center; white-space: nowrap; vertical-align: middle; -ms-touch-action: manipulation; touch-action: manipulation; cursor: pointer; -webkit-user-select: none; -moz-user-select: none; -ms-user-select: none; user-select: none; background-image: none; border: 1px solid transparent; border-radius: 4px; color: #fff; background-color: red; border-color: red;">
                Decline
            </a>
            @endif

            @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
            <form action="{{ route('Responsable_gestionnaire.creditNote.confirm', ['id' => $invoice->id, 'noteType' => $type, 'noteAmount' => $budget]) }}" method="POST" style="display: inline-block;">
                @csrf
                <button type="submit" style="padding: 6px 12px; margin-bottom: 6px; font-size: 14px; font-weight: 400; line-height: 1.42857143; text-align: center; white-space: nowrap; vertical-align: middle; -ms-touch-action: manipulation; touch-action: manipulation; cursor: pointer; -webkit-user-select: none; -moz-user-select: none; -ms-user-select: none; user-select: none; background-image: none; border: 1px solid transparent; border-radius: 4px; color: #fff; background-color: green; border-color: green;">
                    Confirm 
                </button>
            </form>
            <a href="{{route('Responsable_gestionnaire.invoices.invoicehistory')}}" style="padding: 6px 12px; margin-bottom: 6px; font-size: 14px; font-weight: 400; line-height: 1.42857143; text-align: center; white-space: nowrap; vertical-align: middle; -ms-touch-action: manipulation; touch-action: manipulation; cursor: pointer; -webkit-user-select: none; -moz-user-select: none; -ms-user-select: none; user-select: none; background-image: none; border: 1px solid transparent; border-radius: 4px; color: #fff; background-color: red; border-color: red;">
                Decline
            </a>
            @endif
        </div>

    </div>


    <div class="footer">
        <div style="float: left; width: 30%; height: 80%;text-align: center;">
            <b>{{ $entreprise->name }}</b>
            <br>
            <b>{{ __('messages.VAT Number') }}:</b> {{ $entreprise->tva}}
        </div>
        <div style="float: left; margin-left: 5%; width: 30%; height: 80%; text-align: center;">
            {{ $entreprise->Address }}
            <br>
            {{ __('messages.Phone') }}: 0{{ $entreprise->phone }}
        </div>
        <div style="float: left; margin-left: 5%; width: 30%; height: 80%; text-align: center;">
            <b>{{ __('messages.BANK') }}</b> : {{ $entreprise->bank_name }} <br>
            <b>{{ __('messages.IBAN') }}</b> : {{ $entreprise->iban }} <br>
            <b>{{ __('messages.SWIFT/BIC') }}</b> : {{ $entreprise->swift }} / {{ $entreprise->bic }}
        </div>
    </div>
</body>

</html>
