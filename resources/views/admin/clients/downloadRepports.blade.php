<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clients Report</title>
    <style>
        /* [MODIFIED] Increased font size slightly for better readability (Zoom effect) */
        body {
            font-family: Tahoma, sans-serif;
            font-size: 11px; /* Changed from 10px to 11px */
            color: #333333;
            background-color: #FFFFFF;
            margin: 0;
            padding: 0;
        }

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

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            margin-top: 20px;
            table-layout: fixed; 
        }

        table, th, td {
            border: 1px solid #CCCCCC;
        }

        th, td {
            /* [MODIFIED] Increased padding slightly */
            padding: 6px 5px; 
            text-align: left;
            vertical-align: top;
            word-wrap: break-word; 
            overflow-wrap: break-word;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
            /* [MODIFIED] Header font size matched to body */
            font-size: 11px; 
        }

        .footer {
            width: 100%;
            max-width: 800px;
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
            font-size: 10px; /* Footer stays small */
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
                    <h2>Clients Table Reports</h2>
                    <h4>Number of Clients: {{ $clients->count() }}</h4>
                </div>
            </div>
        </div>

        <div style="height: 20px;"></div>

        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">No.</th>
                    <th style="width: 15%;">Name</th>
                    <th style="width: 18%;">Email</th>
                    <th style="width: 10%;">Phone</th>
                    <th style="width: 15%;">Billing Addr.</th>
                    <th style="width: 8%;">TVA</th>
                    <th style="width: 5%;">Due</th>
                    <th style="width: 12%;">Address</th>
                    <th style="width: 7%;">City</th>
                    <th style="width: 5%;">Zip</th>
                </tr>
            </thead>
            <tbody>
                @foreach($clients as $client)
                <tr>
                    <td>{{$client->client_number}}</td>
                    <td>{{$client->name}}</td>
                    <td>{{$client->email}}</td>
                    <td>{{$client->phone}}</td>
                    <td>{{$client->Billing_address}}</td>
                    <td>{{$client->TVA}}</td>
                    <td>{{$client->due_date}}</td>
                    <td>{{$client->address}}</td>
                    <td>{{$client->city}}</td>
                    <td>{{$client->zip}}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

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