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
        word-wrap: break-word; /* Allow long text to wrap */
    }

    th {
        background-color: #f2f2f2;
        font-weight: bold;
    }

    /* [MODIFIED] Footer is no longer fixed */
    .footer {
        width: 595px; /* Match report width */
        margin: 0 auto; /* Center it */
        padding: 20px; /* Match report padding */
        padding-top: 20px;
        box-sizing: border-box; /* Include padding in width */
        border-top: 1px solid #000;
        position: relative; /* Changed from fixed */
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
        width: 100px; /* From template */
        height: auto;
    }
    
    /* [NEW] Print-specific styles for flowing table */
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
            page-break-after: avoid; /* Don't break after the main content */
        }
        
        thead {
            display: table-header-group; /* Repeat table header on each page */
        }

        tr, td, th {
            page-break-inside: avoid; /* Try to keep rows from splitting */
        }
        
        .footer {
            width: 100%;
            padding: 20px;
            margin: 0;
            position: static; /* Footer flows at the end */
            page-break-before: always; /* Put footer on its own page if needed, or 'auto' */
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
                <h2>Projects Table Reports</h2>
                <h5>Number of Projects: {{ $projects->count() }}</h5>
            </div>
        </div>
    </div>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Budget</th>
                <th>Cost Per Hour</th>
                <th>Estimated Hours</th>
                <th>TVA</th>
                <th>Client Name</th>
                <th>Informations</th>
            </tr>
        </thead>
        <tbody>
            @foreach($projects as $project)
            <tr>
                <td>{{$project->name}}</td>
                <td>{{$project->budget}}€</td>
                <td>{{$project->cost_per_hour}}€</td>
                <td>{{$project->estimated_hours}}h</td>
                <td>{{$project->TVA}}</td>
                @php
                $client = App\Models\Client::where('id', $project->client_id)->first();
                @endphp
                <td>{{$client->name}}</td>
                <td>{{$project->description}}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <div class='footer-left'>Download Date: {{ date('d-m-Y') }}</div>
        <div class='footer-right'>Page Number: <span class="page-number"></span></div>
    </div>

</div>