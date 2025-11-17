<style>
    /* [MODIFIED] Styles adapted for a flowing report */
    body {
        font-family: Tahoma, sans-serif;
        font-size: 12px;
        color: #333333;
        background-color: #FFFFFF;
        margin: 0;
        padding: 0;
    }

    /* [NEW] This wrapper constrains the width like an A4 page but allows content to flow in height */
    .report-wrapper {
        width: 595px;
        min-height: 842px;
        margin: 0 auto;
        padding: 20px;
        padding-bottom: 100px; /* Space for footer */
        position: relative;
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
        word-wrap: break-word; 
    }

    th {
        background-color: #f2f2f2;
        font-weight: bold;
    }

    .footer {
        width: 595px; 
        margin: 0 auto; 
        padding: 20px; 
        padding-top: 20px;
        box-sizing: border-box; 
        border-top: 1px solid #000;
        position: relative; 
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
        font-size: 12px; 
    }

    .logo-right {
        text-align: left;
    }
    
    .company-logo {
        width: 100px; 
        height: auto;
    }
        @media print {
        body {
            margin: 0;
            padding: 0;
        }
        .report-wrapper {
            width: 100%;
            padding: 20px;
            margin: 0;
            min-height: 0;
            border: none;
            page-break-after: avoid;
        }
        
        thead {
            display: table-header-group; 
        }

        tr, td, th {
            page-break-inside: avoid; 
        }
        
        .footer {
            width: 100%;
            padding: 20px;
            margin: 0;
            position: static; 
            page-break-before: always; 
        }
    }

    </style>

<div class="container">

    <div class="details">
        <div class="details-left">
            <img src="{{ Storage::disk('do_spaces')->url($entreprise->image) }}" alt="Company Logo" class="company-logo">
        </div>
        <div class="details-right">
            <div class="header-info">
                <h2>Clients Table Reports</h2>
                <h5>Number of Clients: {{ $clients->count() }}</h5>
            </div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Number</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Billing Address</th>
                <th>TVA</th>
                <th>Due Date</th>
                <th>Address</th>
                <th>City</th>
                <th>Zip</th>
                <th>Country</th>
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
                <td>{{$client->country}}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

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