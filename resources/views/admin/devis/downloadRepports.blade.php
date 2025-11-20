<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Devis Report</title>
    <style>
        /* [MODIFIED] Consistent Font and Size */
        body {
            font-family: Tahoma, sans-serif;
            font-size: 11px;
            color: #333333;
            background-color: #FFFFFF;
            margin: 0;
            padding: 0;
        }

        /* [NEW] Consistent Wrapper */
        .report-wrapper {
            width: 100%;
            max-width: 900px;
            margin: 0 auto;
            padding: 40px 20px 100px 20px;
            position: relative;
        }

        .header {
            width: 100%;
            text-align: center;
            margin-bottom: 30px;
        }

        .details {
            width: 100%;
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
            position: relative;
            padding-bottom: 10px;
        }

        .details-left {
            width: 40%;
            font-size: 12px;
        }

        .details-right {
            width: 58%;
            font-size: 12px;
            text-align: right;
            position: absolute;
            right: 0;
            top: 0;
        }
        
        .header-info h2 {
            margin-top: 0;
            margin-bottom: 5px;
        }
        
        .header-info h4 {
            margin-top: 0;
            margin-bottom: 0;
            font-weight: normal;
        }

        .company-logo {
            width: 100px;
            height: auto;
        }

        /* [NEW] Styled Summary Box */
        .totals-summary {
            margin-top: 10px;
            margin-bottom: 20px;
            padding: 10px;
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            font-size: 11px;
        }
        .summary-row {
            display: flex;
            justify-content: flex-end;
            gap: 20px;
        }
        .summary-item strong { color: #555; }
        .summary-item .accepted { color: #28a745; font-weight: bold; }
        .summary-item .declined { color: #dc3545; font-weight: bold; }
        .summary-item .pending { color: #ffc107; font-weight: bold; }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            margin-top: 20px;
            table-layout: fixed; 
        }

        table, th, td {
            border: 1px solid #CCCCCC;
        }

        th, td {
            padding: 6px 5px;
            text-align: left;
            font-size: 11px;
            vertical-align: top;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        /* [MODIFIED] Fixed Footer */
        .footer {
            width: 100%;
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
            border-top: 1px solid #000;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background-color: white;
        }

        .footer-content {
            position: relative;
            width: 100%;
            height: 50px;
            display: flex;
            justify-content: space-between;
        }

        .footer-left { position: absolute; left: 0; text-align: left; }
        .footer-center { position: absolute; left: 50%; transform: translateX(-50%); text-align: center; }
        .footer-right { position: absolute; right: 0; text-align: right; }

        .footer-text {
            color: #333333;
            line-height: 1.4;
            font-size: 10px;
        }

        @media print {
            body { margin: 0; padding: 0; }
            .report-wrapper { width: 100%; max-width: 100%; padding: 20px; margin: 0; border: none; }
            .footer { position: fixed; bottom: 0; width: 100%; }
            thead { display: table-header-group; }
            tr { page-break-inside: avoid; }
        }
    </style>
</head>

<body>

    <div class="report-wrapper">

        <div class="details">
            <div class="details-left">
                @if(Auth::user()->entreprise && Auth::user()->entreprise->image)
                    <img src="{{ Storage::disk('do_spaces')->url(Auth::user()->entreprise->image) }}" alt="Company Logo" class="company-logo">
                @endif
            </div>
            <div class="details-right">
                <div class="header-info">
                    <h2>Devis Table Reports</h2>
                    <h4>Number of Devis: {{ $devis->count() }}</h4>
                </div>
            </div>
        </div>

        @php 
            $Total_Accpeted = 0;
            $Total_Declined = 0;
            $Total_Pending = 0;
        @endphp

        <table>
            <thead>
                <tr>
                    <th style="width: 12%;">Devis N</th>
                    <th style="width: 15%;">Project Name</th>
                    <th style="width: 6%;">TVA</th>
                    <th style="width: 15%;">Client Name</th>
                    <th style="width: 20%;">Emails</th>
                    <th style="width: 10%;">Status</th>
                    <th style="width: 10%;">Budget</th>
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
                            <span style="color: #28a745; font-weight: bold;">Accepted</span>
                        @elseif($devi->acceptanceStatus == 2)
                            <span style="color: #dc3545; font-weight: bold;">Declined</span>
                        @else
                            <span style="color: #ffc107; font-weight: bold;">Pending</span> 
                        @endif
                    </td>
                    <td>
                        €{{number_format($devi->budget, 2, ',', ' ')}}
                        @if($devi->acceptanceStatus == 1)
                            @php $Total_Accpeted += $devi->budget; @endphp
                        @elseif($devi->acceptanceStatus == 2)
                            @php $Total_Declined += $devi->budget; @endphp
                        @else
                            @php $Total_Pending += $devi->budget; @endphp
                        @endif
                    </td> 
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Summary Section using consistent styling --}}
        <div class="totals-summary">
            <div class="summary-row">
                <div class="summary-item">
                    <strong>Totals Accepted:</strong> <span class="accepted">€{{ number_format($Total_Accpeted, 2, ',', ' ') }}</span>
                </div>
                <div class="summary-item">
                    <strong>Totals Declined:</strong> <span class="declined">€{{ number_format($Total_Declined, 2, ',', ' ') }}</span>
                </div>
                <div class="summary-item">
                    <strong>Totals Pending:</strong> <span class="pending">€{{ number_format($Total_Pending, 2, ',', ' ') }}</span>
                </div>
            </div>
        </div>

    </div>

    <div class="footer">
        <div class="footer-content">
            <div class="footer-left footer-text">
                @if(Auth::user()->entreprise)
                    <b>{{ Auth::user()->entreprise->name }}</b><br>
                    <b>Numéro TVA:</b> {{ Auth::user()->entreprise->tva}}
                @endif
            </div>
            <div class="footer-center footer-text">
                @if(Auth::user()->entreprise)
                    {{ Auth::user()->entreprise->Address }}<br>
                    Tél: 0{{ Auth::user()->entreprise->phone }}
                @endif
            </div>
            <div class="footer-right footer-text">
                @if(Auth::user()->entreprise)
                    <b>BANK</b> : {{ Auth::user()->entreprise->bank_name }}<br>
                    <b>IBAN</b> : {{ Auth::user()->entreprise->iban }}<br>
                    <b>SWIFT/BIC</b> : {{ Auth::user()->entreprise->swift }} / {{ Auth::user()->entreprise->bic }}
                @endif
                <br>
                <span style="font-size: 9px; color: #666;">Printed: {{ date('d-m-Y') }}</span>
            </div>
        </div>
    </div>

</body>

</html>