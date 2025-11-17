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
        width: 100%; /* Ensure totals table takes full width */
    }

    .totals td {
        border: none;
        padding: 4px 8px; /* Adjust padding for totals */
    }
    
    /* [MODIFIED] Added styles for the totals table labels and values */
    .totals .label {
        font-weight: bold;
        text-align: right;
        width: 80%;
    }
    .totals .value {
        font-weight: bold;
        text-align: right;
        white-space: nowrap;
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
        font-size: 12px; /* Ensure footer text matches body */
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
    
    .company-logo {
        width: 100px; /* From template */
        height: auto;
    }
    </style>
</head>

<body>
    <div class="page">
        <div class="content-wrapper">
            
            <div class="header">
                <div class="logo-right">
                    <img src="{{ Storage::disk('do_spaces')->url($entreprise->image) }}" alt="Company Logo" class="company-logo">
                </div>
            </div>

            <br><br><br>

            <div class="details">
                <div class="details-left">
                    <p>Date: {{ date('d-m-Y') }}<br />
                    </p>
                </div>
                <div class="details-right">
                    <strong>Devis Table Reports</strong><br />
                    Number of Devis: {{ $devis->count() }}
                </div>
            </div>
            
            <br>
            
            @php 
                $Total_Accpeted = 0;
                $Total_Declined = 0;
                $Total_Pending = 0;
            @endphp

            <table>
                <thead>
                    <tr>
                        <th>devis N</th>
                        <th>Project Name</th>
                        <th>TVA</th>
                        <th>Client Name</th>
                        <th>Emails</th>
                        <th>Acceptance status</th>
                        <th>Budget</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($devis as $devi)
                    <tr>
                        <td>{{$entreprise->devis_prefix}}-{{$devi->devis_number}}</td>
                        <td>
                            @php 
                            $project = App\Models\Project::find($devi->project_id);
                            @endphp
                            {{$project->name}}
                        </td>
                        <td>
                            {{$project->tva}}%
                        </td>
                        <td>
                            @php 
                            $client = App\Models\Client::find($devi->client_id);
                            @endphp
                            {{$client->name}}
                        </td>
                        <td>{{$devi->emails}}</td>
                        <td>
                            @if($devi->acceptanceStatus == 1)
                                Accepted
                            @elseif($devi->acceptanceStatus == 2)
                                Declined
                            @else
                                Pending 
                            @endif
                        </td>
                        <td>
                            €{{number_format($devi->budget, 2, ',', ' ')}}
                            @if($devi->acceptanceStatus == 1)
                                @php
                                    $Total_Accpeted += $devi->budget;
                                @endphp
                            @elseif($devi->acceptanceStatus == 2)
                                @php
                                    $Total_Declined += $devi->budget;
                                @endphp
                            @else
                                @php
                                    $Total_Pending += $devi->budget;
                                @endphp
                            @endif
                        </td> 
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <table class="totals">
                <tbody>
                    <tr>
                        <td class="label">Totals Accepted :</td>
                        <td class="value">€{{ number_format($Total_Accpeted, 2, ',', ' ') }}</td>
                    </tr>
                    <tr>
                        <td class="label">Totals Declined :</td>
                        <td class="value">€{{ number_format($Total_Declined, 2, ',', ' ') }}</td>
                    </tr>
                    <tr>
                        <td class="label">Totals Pending :</td>
                        <td class="value">€{{ number_format($Total_Pending, 2, ',', ' ') }}</td>
                    </tr>
                </tbody>
            </table>

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