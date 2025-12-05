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
        padding-bottom: 100px;
        /* Space for footer */
    }

    .detailsTab {
        border: none;
        width: 100%;
        margin-bottom: 5px;
    }

    .task-cell {
        width: 80%;
        border: 0;
        word-wrap: break-word;
        padding-right: 20px;
    }

    .budget-cell {
        width: 20%;
        border: 0;
        text-align: right;
        white-space: nowrap;
    }
    </style>
</head>

<body>
    <!-- First page - Invoice -->
    <div class="page">
        <div class="content-wrapper">
            <div class="header">
                <div class="logo-right">
                <img src="{{ Storage::disk('public')->url(Auth::user()->entreprise->image) }}" alt="Company Logo" class="company-logo">
                </div>
            </div>

            <br><br><br>
            @php
            $devis = App\Models\devis_history::where('id',$devis_id)->first();
            $client = App\Models\Client::where('id',$devis->client_id)->first();
            @endphp
            <div class="details">
    <div class="details-left">
        <p>Date du Devis: {{ $devis->created_at->format('d-m-Y') }}<br />
        </p>
    </div>
   
    <div class="details-right">
        <strong>{{ $client->name }}</strong><br />
        {{ $client->address }} <br />
        {{ $client->zip }}/{{ $client->city }} <br />
        {{ $client->country }} <br />
    </div>
</div>

<div class="invoice-number">
    <h2>Devis : {{$entreprise->devis_prefix}}-{{$devisNum}}</h2>
</div>
<br><br>
<table>
    <thead>
        <tr>
            <th>Désignation</th>
            <th>Quantite</th>
            <th>Prix Unitaire</th>
            <th>Montant</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>
                {{ $project_name }}
            </td>
            <td>
                @if(isset($billing_type) && $billing_type === 'horaire')
                    {{ number_format($hours, 2, ',', ' ') }}
                @else
                    1
                @endif
            </td>
            <td>
                @if(isset($billing_type) && $billing_type === 'horaire')
                    {{ number_format($amount_per_hour, 2, ',', ' ') }}€
                @else
                    {{ number_format($budget, 2, ',', ' ') }}€
                @endif
            </td>
            <td>
                {{ number_format($budget, 2, ',', ' ') }}€
            </td>
        </tr>
    </tbody>
</table>

<h3>Details : </h3>

<style>
.detailsTab {
    border: none;
    width: 100%;
    margin-bottom: 5px;
}
.task-cell {
    width: 80%;
    border: 0;
    word-wrap: break-word;
    padding-right: 20px;
}
.budget-cell {
    width: 20%;
    border: 0;
    text-align: right;
    white-space: nowrap;
}
</style>
@php
    $tasks = $tasks ?? [];
    $taskBudget = $taskBudget ?? [];
@endphp
@foreach ($tasks as $index => $task)
<table class="detailsTab">
    <tr>
        <td class="task-cell">{!! nl2br(e($task)) !!}</td>
        <td class="budget-cell">
            @if(isset($billing_type) && $billing_type === 'horaire')
                -
            @else
                {{ number_format($taskBudget[$index] ?? 0, 2, ',', ' ') }}€
            @endif
        </td>
    </tr>
</table>
@endforeach

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
                <div class="value">{{ number_format($budget, 2, ',', ' ') }}€</div>
            </td>
        </tr>
        <tr>
            <td colspan="3" class="totals">
                <div class="label">TVA {{ $tva }}%</div>
                <div class="value">{{ number_format($budget * ($tva / 100), 2, ',', ' ') }}€</div>
            </td>
        </tr>
        <tr>
            <td colspan="3" class="totals">
                <div class="label">Total TTC</div>
                <div class="value"><b>{{ number_format($budget + ($budget * ($tva / 100)), 2, ',', ' ') }}€</b></div>
            </td>
        </tr>
    </tbody>
</table>

<div class="description">
    {!! nl2br(e($description)) !!}
</div>

<div class="payment">
    - Durée de validité de l'offre : <strong>1 mois</strong> <br>
    - Conditions générales : voire annexes <br>
    - Nous restons à votre disposition pour toute information complémentaire <br>
    - Si ce devis vous convient, veuillez le retourner signé et daté
</div>
        </div>

        <div class="footer">
            <div class="footer-content">
                <div class="footer-left footer-text">
                    <b>{{ $entreprise->name }}</b><br>
                    <b>Numéro TVA:</b> {{ $entreprise->tva}}
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

    <!-- Second page - General Conditions -->
    <div class="page">
        <div class="content-wrapper">
            <h2>Conditions Générales :</h2>
            <div class="general-conditions">
                <p>{!! nl2br(e($entreprise->general_conditions_Devis)) !!}</p>
            </div>
        </div>

        <div class="footer">
            <div class="footer-content">
                <div class="footer-left footer-text">
                    <b>{{ $entreprise->name }}</b><br>
                    <b>Numéro TVA:</b> {{ $entreprise->tva}}
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

