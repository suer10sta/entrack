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
            <img src="{{ Storage::disk('do_spaces')->url(Auth::user()->entreprise->image) }}" alt="Company Logo" class="company-logo">
            </div>
        </div>

        <br><br><br><br>

        <div class="details">
            <div class="details-left">
                <p>Date de facturation: <?php echo date('d-m-Y'); ?><br />
                    Échéance:
                    {{ \Carbon\Carbon::now()->addDays(abs(filter_var($client->due_date, FILTER_SANITIZE_NUMBER_INT)))->format('d-m-Y') }}
                    <br>
                    Numéro client : {{ $client->client_number }} <br>
                    Numéro de facture : {{date('ym')}}@if(request()->has('invoice_number')){{request()->input('invoice_number')}}@else{{$invoice_id }} @endif
                    @php
                        $devis_number = null;
                        if(request()->has('devis_id') && !empty(request()->query('devis_id'))){
                            $dv = \App\Models\devis_history::find((int) request()->query('devis_id'));
                            $devis_number = $dv ? $dv->devis_number : null;
                        }
                    @endphp
                    @if(!empty($devis_number))<br>
                    Sold devis : {{$entreprise->devis_prefix}}-{{$devis_number}}
                    @endif
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

        <br><br>
        @php
        $invoicenumber = '';
        if(request()->has('invoice_number')) {
        $invoicenumber = request()->input('invoice_number');
        }
        @endphp
        <div class="invoice-number">
            Facture : {{$entreprise->invoice_prefix}}-{{date('ym')}}@if(request()->has('invoice_number')){{request()->input('invoice_number')}}@else{{$invoice_id }} @endif
        </div>

        <table>
            <thead>
                <tr>
                    <th>Désignation</th>
                    <th>Quantite</th>
                    <th>Montant Principal</th>
                    <th>Acompte</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{strtoupper($projectinfos->name)}}</td>
                    <td>
                        1
                    </td>
                    <td>{{ isset($principalBudget) ? $principalBudget : $projectinfos->budget }}€</td>
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
                        <div class="label">Total HTVA</div>
                        <div class="value">{{$amount}}€</div>
                    </td>
                </tr>
                <tr>
                    <td colspan="3" class="totals">
                        <div class="label">TVA {{ $projectinfos->tva }}%
                        </div>
                        <div class="value">{{number_format($amount* ($projectinfos->tva / 100), 2, ',', '')}}€</div>
                    </td>
                </tr>
                <tr>
                    <td colspan="3" class="totals">
                        <div class="label">Total TTC</div>
                        <div class="value"><b>{{number_format($amount + ($amount*($projectinfos->tva/100)), 2, ',', '')}}€</b></div>
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="description">
            <strong> - Informations :</strong> <br>
            {!! nl2br(e($projectinfos->description)) !!} <br>
            <strong> - Commentaire : </strong> <br>
           <p style="white-space: pre-wrap;">{!! nl2br(e($comment)) !!}</p>
        </div>

        <div class="payment">
            - Veuillez payer le montant de
            <strong>{{number_format($amount + ($amount*($projectinfos->tva/100)), 2, ',', '')}}€</strong> sur le compte mentionné
            ci-dessous avant le
            {{ \Carbon\Carbon::now()->addDays(abs(filter_var($client->due_date, FILTER_SANITIZE_NUMBER_INT)))->format('d-m-Y') }}
            en mentionnant le numéro Facture <strong>{{date('ym')}}@if(request()->has('invoice_number')){{request()->input('invoice_number')}}@else{{$invoice_id }} @endif</strong>
            <br>
            - Dans le cas de livraison intracommunautaire pour les services :  "Autoliquidation Art 21 § 2 du code TVA belge"
            <br>
            - Voir conditions générales en annexe
            <br>
            - Merci de votre confiance

        </div>
        <div style="display: flex; justify-content: center; align-items: center; flex-direction: column;">
        @php
            $devisIdParam = request()->query('devis_id');
            $querySuffix = $devisIdParam ? ('?devis_id=' . urlencode($devisIdParam)) : '';
        @endphp
        @if(Auth::user()->hasRole('Admin'))
            <form action="{{ route('admin.projects.confirmPartialInvoice', ['projectID' => $projectinfos->id, 'amount' => $amount, 'comment' => base64_encode($comment)]) }}{{ $querySuffix }}" method="POST" style="display: inline-block;">
                @csrf
                <button type="submit" style="padding: 6px 12px; margin-bottom: 6px; font-size: 14px; font-weight: 400; line-height: 1.42857143; text-align: center; white-space: nowrap; vertical-align: middle; -ms-touch-action: manipulation; touch-action: manipulation; cursor: pointer; -webkit-user-select: none; -moz-user-select: none; -ms-user-select: none; user-select: none; background-image: none; border: 1px solid transparent; border-radius: 4px; color: #fff; background-color: green; border-color: green;">
                    Confirm 
                </button>
            </form>
            <form action="{{ route('admin.projects.declineInvoice') }}" method="POST" style="display: inline-block;">
                @csrf
                <button type="submit" style="padding: 6px 12px; margin-bottom: 0; font-size: 14px; font-weight: 400; line-height: 1.42857143; text-align: center; white-space: nowrap; vertical-align: middle; -ms-touch-action: manipulation; touch-action: manipulation; cursor: pointer; -webkit-user-select: none; -moz-user-select: none; -ms-user-select: none; user-select: none; background-image: none; border: 1px solid transparent; border-radius: 4px; color: #fff; background-color: red; border-color: red;">
                    decline 
                </button>
            </form>
        @endif
        @if(Auth::user()->hasRole('Responsable de projet'))
            <form action="{{ route('Responsable_de_projet.projects.confirmPartialInvoice', ['projectID' => $projectinfos->id, 'amount' => $amount, 'comment' => base64_encode($comment)]) }}{{ $querySuffix }}" method="POST" style="display: inline-block;">
                @csrf
                <button type="submit" style="padding: 6px 12px; margin-bottom: 6px; font-size: 14px; font-weight: 400; line-height: 1.42857143; text-align: center; white-space: nowrap; vertical-align: middle; -ms-touch-action: manipulation; touch-action: manipulation; cursor: pointer; -webkit-user-select: none; -moz-user-select: none; -ms-user-select: none; user-select: none; background-image: none; border: 1px solid transparent; border-radius: 4px; color: #fff; background-color: green; border-color: green;">
                    Confirm 
                </button>
            </form>
            <form action="{{ route('Responsable_de_projet.projects.declineInvoice') }}" method="POST" style="display: inline-block;">
                @csrf
                <button type="submit" style="padding: 6px 12px; margin-bottom: 0; font-size: 14px; font-weight: 400; line-height: 1.42857143; text-align: center; white-space: nowrap; vertical-align: middle; -ms-touch-action: manipulation; touch-action: manipulation; cursor: pointer; -webkit-user-select: none; -moz-user-select: none; -ms-user-select: none; user-select: none; background-image: none; border: 1px solid transparent; border-radius: 4px; color: #fff; background-color: red; border-color: red;">
                    decline 
                </button>
            </form>
        @endif
        @if(Auth::user()->hasRole('Employee'))
            <form action="{{ route('employee.projects.confirmPartialInvoice', ['projectID' => $projectinfos->id, 'amount' => $amount, 'comment' => base64_encode($comment)]) }}{{ $querySuffix }}" method="POST" style="display: inline-block;">
                @csrf
                <button type="submit" style="padding: 6px 12px; margin-bottom: 6px; font-size: 14px; font-weight: 400; line-height: 1.42857143; text-align: center; white-space: nowrap; vertical-align: middle; -ms-touch-action: manipulation; touch-action: manipulation; cursor: pointer; -webkit-user-select: none; -moz-user-select: none; -ms-user-select: none; user-select: none; background-image: none; border: 1px solid transparent; border-radius: 4px; color: #fff; background-color: green; border-color: green;">
                    Confirm 
                </button>
            </form>
            <form action="{{ route('employee.projects.declineInvoice') }}" method="POST" style="display: inline-block;">
                @csrf
                <button type="submit" style="padding: 6px 12px; margin-bottom: 0; font-size: 14px; font-weight: 400; line-height: 1.42857143; text-align: center; white-space: nowrap; vertical-align: middle; -ms-touch-action: manipulation; touch-action: manipulation; cursor: pointer; -webkit-user-select: none; -moz-user-select: none; -ms-user-select: none; user-select: none; background-image: none; border: 1px solid transparent; border-radius: 4px; color: #fff; background-color: red; border-color: red;">
                    decline 
                </button>
            </form>
        @endif
        @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
            <form action="{{ route('Responsable_gestionnaire.projects.confirmPartialInvoice', ['projectID' => $projectinfos->id, 'amount' => $amount, 'comment' => base64_encode($comment)]) }}{{ $querySuffix }}" method="POST" style="display: inline-block;">
                @csrf
                <button type="submit" style="padding: 6px 12px; margin-bottom: 6px; font-size: 14px; font-weight: 400; line-height: 1.42857143; text-align: center; white-space: nowrap; vertical-align: middle; -ms-touch-action: manipulation; touch-action: manipulation; cursor: pointer; -webkit-user-select: none; -moz-user-select: none; -ms-user-select: none; user-select: none; background-image: none; border: 1px solid transparent; border-radius: 4px; color: #fff; background-color: green; border-color: green;">
                    Confirm 
                </button>
            </form>
            <form action="{{ route('Responsable_gestionnaire.projects.declineInvoice') }}" method="POST" style="display: inline-block;">
                @csrf
                <button type="submit" style="padding: 6px 12px; margin-bottom: 0; font-size: 14px; font-weight: 400; line-height: 1.42857143; text-align: center; white-space: nowrap; vertical-align: middle; -ms-touch-action: manipulation; touch-action: manipulation; cursor: pointer; -webkit-user-select: none; -moz-user-select: none; -ms-user-select: none; user-select: none; background-image: none; border: 1px solid transparent; border-radius: 4px; color: #fff; background-color: red; border-color: red;">
                    decline 
                </button>
            </form>
        @endif
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