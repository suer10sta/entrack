<!DOCTYPE html>
<html lang="fr">
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

        .details-container {
            width: 100%;
            position: relative;
            margin-bottom: 20px;
            min-height: 80px;
        }

        .details-left {
            position: absolute;
            left: 0;
            top: 0;
            width: 48%;
            font-size: 12px;
        }

        .details-right {
            position: absolute;
            right: 0;
            top: 0;
            width: 48%;
            font-size: 12px;
            text-align: right;
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
    <div style="display: none;" id="debug-info">
        <p>Partial: {{ $partial ? 'Yes' : 'No' }}</p>
        <p>Original Total: {{ $original_total ?? 'N/A' }}</p>
    </div>

    <button onclick="document.getElementById('debug-info').style.display = document.getElementById('debug-info').style.display === 'none' ? 'block' : 'none'" style="position: absolute; top: 5px; right: 5px; z-index: 1000; font-size: 10px;">Debug</button>

    <div class="container">
        <div class="header">
            <div class="header logo-right">
                @if($entreprise->image)
                        <img src="{{ storage_path('app/public/' . $entreprise->image) }}" alt="Company Logo" class="company-logo">
                    @endif
            </div>
        </div>

        <div class="details-container">
            <div class="details-left">
                <p>
                    Date de La Note de Credit: {{ date('d-m-Y') }}<br />
                </p>
            </div>
            <div class="details-right">
                <strong>{{ $client->name }}</strong><br />
                {{ $client->billing_street }} <br />
                {{ $client->billing_zip }}-{{ $client->billing_city }} <br />
                {{ $client->billing_country }}<br />
                TVA Client: {{ $client->TVA }}
            </div>
        </div>

        <div style="text-align: center; margin: 20px 0; margin-top: 100px;">
            <h2>Note De Credit : NC-{{date('ym')}}{{ str_pad($last_credit_note_number + 1, 3, '0', STR_PAD_LEFT) }}</h2>
            <p>Ref: {{$entreprise->invoice_prefix}}-{{date('ym')}}{{$invoice_number}}</p>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Désignation</th>
                    <th>Quantité</th>
                    <th>Prix Unitaire</th>
                    <th>Montant</th>
                    @if($partial == 1)
                    <th>Montant Original</th>
                    @endif
                </tr>
            </thead>
            <tbody>
            <tr>
                <td colspan="@if($partial == 1)5 @else 4 @endif" style="background-color: #f9f9f9;">
                    <strong>Projet: {{ $projectinfos->name }}</strong>
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
            <tfoot>
                <tr>
                    <td colspan="@if($partial == 1)4 @else 3 @endif" style="text-align: right;"><strong>Total HTVA</strong></td>
                    <td>-{{number_format($total_htva, 2, ',', '')}}€</td>
                </tr>
                <tr>
                    <td colspan="@if($partial == 1)4 @else 3 @endif" style="text-align: right;"><strong>TVA ({{$projectinfos->tva}}%)</strong></td>
                    <td>-{{number_format($tva_amount, 2, ',', '')}}€</td>
                </tr>
                <tr>
                    <td colspan="@if($partial == 1)4 @else 3 @endif" style="text-align: right;"><strong>Total TTC</strong></td>
                    <td>-{{number_format($total_ttc, 2, ',', '')}}€</td>
                </tr>
            </tfoot>
        </table>

        @if($comment || $projectinfos->description)
        <div style="margin: 20px 0; padding: 10px; border-top: 1px solid #CCCCCC; border-bottom: 1px solid #CCCCCC;">
            @if($projectinfos->description)
            <strong> - Informations :</strong> <br>
            {!! nl2br(e($projectinfos->description)) !!} <br>
            @endif
            @if($comment)
            <strong>Commentaire:</strong><br>
            {!! nl2br(e($comment)) !!}
            @endif
        </div>
        @endif

        <div class="payment">
            - Cette note de crédit annule @if($partial)partiellement @else totalement @endif la facture {{$entreprise->invoice_prefix}}-{{date('ym')}}{{$invoice_number}}
            <br>
            - Dans le cas de livraison intracommunautaire pour les services :  "Autoliquidation Art 21 § 2 du code TVA belge"
            <br>
            - Voir conditions générales en annexe
        </div>

        <div style="text-align: center; margin: 20px 0;">
            @if(Auth::user()->hasRole('Admin'))
            <form action="{{ route('admin.invoices.confirmDetailedCreditNote', ['invoiceID' => $invoice_id]) }}" method="POST" style="display: inline-block;">
            @endif
            @if(Auth::user()->hasRole('Responsable de projet'))
            <form action="{{ route('Responsable_de_projet.invoices.confirmDetailedCreditNote', ['invoiceID' => $invoice_id]) }}" method="POST" style="display: inline-block;">
            @endif
            @if(Auth::user()->hasRole('Employee'))
            <form action="{{ route('employee.invoices.confirmDetailedCreditNote', ['invoiceID' => $invoice_id]) }}" method="POST" style="display: inline-block;">
            @endif
            @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
            <form action="{{ route('Responsable_gestionnaire.invoices.confirmDetailedCreditNote', ['invoiceID' => $invoice_id]) }}" method="POST" style="display: inline-block;">
            @endif
                @csrf
                <input type="hidden" name="details" value='{{ json_encode($details) }}'>
                <input type="hidden" name="comment" value="{{ $comment ?? '' }}">
                <input type="hidden" name="partial" value="{{ $partial }}">
                <input type="hidden" name="total_htva" value="{{ $total_htva }}">
                <input type="hidden" name="total_tva" value="{{ $tva_amount }}">
                <input type="hidden" name="total_ttc" value="{{ $total_ttc }}">
                <button type="submit" style="padding: 6px 12px; margin-right: 10px; background-color: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer;">
                    Confirmer
                </button>
            </form>
            <button onclick="window.history.back()" style="padding: 6px 12px; background-color: #f44336; color: white; border: none; border-radius: 4px; cursor: pointer;">
                Retour
            </button>
        </div>
    </div>

    <div class="footer">
        <div style="float: left; width: 30%; height: 80%;text-align: center;">
            <b>{{ $entreprise->name }}</b>
            <br>
            <b>Numéro TVA:</b> {{ $entreprise->tva}}
        </div>
        <div style="float: left; margin-left: 5%; width: 30%; height: 80%; text-align: center;">
            {{ $entreprise->Address }}
            <br>
            Tél: 0{{ $entreprise->phone }}
        </div>
        <div style="float: left; margin-left: 5%; width: 30%; height: 80%; text-align: center;">
            <b>BANK</b> : {{ $entreprise->bank_name }} <br>
            <b>IBAN</b> : {{ $entreprise->iban }} <br>
            <b>SWIFT/BIC</b> : {{ $entreprise->swift }} / {{ $entreprise->bic }}
        </div>
    </div>
</body>
</html> 
