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
            position: relative;
            min-height: 100vh;
        }

        .container {
            width: 595px;
            margin: 0 auto;
            padding: 20px;
            padding-bottom: 100px;
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

        .footer {
            position: absolute;
            bottom: 0;
            width: 100%;
            height: 100px;
            border-top: 2px solid #CCCCCC;
            padding-top: 10px;
            font-size: 10px;
        }

        .payment {
            margin: 20px 0;
            padding: 10px;
            border-top: 1px solid #CCCCCC;
            border-bottom: 1px solid #CCCCCC;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            @if($entreprise->image)
                <img src="{{ Storage::disk('public')->url($entreprise->image) }}" alt="Logo">
            @endif
            <div>{{ $entreprise->name }}</div>
        </div>

        <div class="details">
            <div class="details-left">
                <p>
                    {{ __('messages.Invoice Date') }}: {{ $invoice_date }}<br />
                    {{ __('messages.Due Date') }}: {{ $due_date }}<br>
                    {{ __('messages.Client Number') }} : {{ $client->client_number }} <br>
                    {{ __('messages.Invoice Number') }} : {{date('ym')}}{{$number}}
                </p>
            </div>
            <div class="details-right">
                <strong>{{ $client->name }}</strong><br />
                {{ $client->billing_street }} <br />
                {{ $client->billing_zip }}-{{ $client->billing_city }} <br />
                {{ $client->billing_country }}<br />
                {{ __('messages.Client TVA') }}: {{ $client->TVA }}
            </div>
        </div>

        <div style="text-align: center; margin: 20px 0;">
            <h2>{{ __('messages.Invoice') }} : {{$entreprise->invoice_prefix}}-{{date('ym')}}{{$number}}</h2>
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
            <tfoot>
                <tr>
                    <td colspan="3" style="text-align: right;"><strong>{{ __('messages.Total Excl VAT') }}</strong></td>
                    <td>{{number_format($total_htva, 2, ',', '')}}€</td>
                </tr>
                <tr>
                    <td colspan="3" style="text-align: right;"><strong>{{ __('messages.VAT') }} ({{$projectinfos->tva}}%)</strong></td>
                    <td>{{number_format($tva_amount, 2, ',', '')}}€</td>
                </tr>
                <tr>
                    <td colspan="3" style="text-align: right;"><strong>{{ __('messages.Total Incl VAT') }}</strong></td>
                    <td>{{number_format($total_ttc, 2, ',', '')}}€</td>
                </tr>
            </tfoot>
        </table>

        @if($comment || $projectinfos->description)
        <div style="margin: 20px 0; padding: 10px; border-top: 1px solid #CCCCCC; border-bottom: 1px solid #CCCCCC;">
            @if($projectinfos->description)
            <strong> - {{ __('messages.Information') }} :</strong> <br>
            {!! nl2br(e($projectinfos->description)) !!} <br>
            @endif
            @if($comment)
            <strong>{{ __('messages.Comment') }}:</strong><br>
            {!! nl2br(e($comment)) !!}
            @endif
        </div>
        @endif

        <div class="payment">
            - {{ __('messages.Please pay the amount of') }} <strong>{{number_format($total_ttc, 2, ',', '')}}€</strong> 
            {{ __('messages.on the account mentioned below before') }} {{ $due_date }}
            {{ __('messages.mentioning the invoice number') }} <strong>{{date('ym')}}{{$number}}</strong>
            <br>
            - {{ __('messages.In case of intra-community delivery for services') }}:  "Autoliquidation Art 21 § 2 du code TVA belge"
                <br>
            - {{ __('messages.See general conditions in annex') }}
            <br>
            - {{ __('messages.Thank you for your trust') }}
        </div>

        <div style="text-align: center; margin: 20px 0;">
            @if(Auth::user()->hasRole('Admin'))
            <form action="{{ route('admin.invoices.confirmDetailedInvoice', ['projectID' => $projectinfos->id]) }}" method="POST" style="display: inline-block;">
            @endif
            @if(Auth::user()->hasRole('Responsable de projet'))
            <form action="{{ route('Responsable_de_projet.invoices.confirmDetailedInvoice', ['projectID' => $projectinfos->id]) }}" method="POST" style="display: inline-block;">
            @endif
            @if(Auth::user()->hasRole('Employee'))
            <form action="{{ route('employee.invoices.confirmDetailedInvoice', ['projectID' => $projectinfos->id]) }}" method="POST" style="display: inline-block;">
            @endif
            @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
            <form action="{{ route('Responsable_gestionnaire.invoices.confirmDetailedInvoice', ['projectID' => $projectinfos->id]) }}" method="POST" style="display: inline-block;">
            @endif
                @csrf
                <input type="hidden" name="details" value='{{ json_encode($details) }}'>
                <input type="hidden" name="comment" value="{{ $comment ?? '' }}">
                <button type="submit" style="padding: 6px 12px; margin-right: 10px; background-color: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer;">
                    {{ __('messages.Confirm') }}
                </button>
            </form>
            <button onclick="window.history.back()" style="padding: 6px 12px; background-color: #f44336; color: white; border: none; border-radius: 4px; cursor: pointer;">
                {{ __('messages.Back') }}
            </button>
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
