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
            @if($entreprise->logo)
                <img src="{{ asset('storage/'.$entreprise->logo) }}" alt="Logo">
            @endif
            <div>{{ $entreprise->name }}</div>
        </div>

        <div class="details">
            <div class="details-left">
                <p>
                    Date de facturation: {{ $invoice_date }}<br />
                    Échéance: {{ $due_date }}<br>
                    Numéro client : {{ $client->client_number }} <br>
                    Numéro de facture : {{date('ym')}}{{$number}}
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

        <div style="text-align: center; margin: 20px 0;">
            <h2>Facture : {{$entreprise->invoice_prefix}}-{{date('ym')}}{{$number}}</h2>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Description</th>
                    <th>Quantité</th>
                    <th>Prix unitaire</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
            <tr>
                <td colspan="4" style="background-color: #f9f9f9;">
                    <strong>Projet: {{ $projectinfos->name }}</strong>
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
                    <td colspan="3" style="text-align: right;"><strong>Total HTVA</strong></td>
                    <td>{{number_format($total_htva, 2, ',', '')}}€</td>
                </tr>
                <tr>
                    <td colspan="3" style="text-align: right;"><strong>TVA ({{$projectinfos->tva}}%)</strong></td>
                    <td>{{number_format($tva_amount, 2, ',', '')}}€</td>
                </tr>
                <tr>
                    <td colspan="3" style="text-align: right;"><strong>Total TTC</strong></td>
                    <td>{{number_format($total_ttc, 2, ',', '')}}€</td>
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
            - Veuillez payer le montant de <strong>{{number_format($total_ttc, 2, ',', '')}}€</strong> 
            sur le compte mentionné ci-dessous avant le {{ $due_date }}
            en mentionnant le numéro Facture <strong>{{date('ym')}}{{$number}}</strong>
            <br>
            - Dans le cas de livraison intracommunautaire pour les services :  "Autoliquidation Art 21 § 2 du code TVA belge"
                <br>
            - Voir conditions générales en annexe
            <br>
            - Merci de votre confiance
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
                    Confirm
                </button>
            </form>
            <button onclick="window.history.back()" style="padding: 6px 12px; background-color: #f44336; color: white; border: none; border-radius: 4px; cursor: pointer;">
                Back
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