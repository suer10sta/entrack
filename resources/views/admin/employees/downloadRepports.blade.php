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
        word-wrap: break-word; /* Allow long text to wrap */
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
    
    .page-number:before {
        content: counter(page);
    }
    </style>

<div class="container">

    <div class="details">
        <div class="details-left">
            <img src="{{ Storage::disk('do_spaces')->url($entreprise->image) }}" alt="Company Logo" class="company-logo">
        </div>
        <div class="details-right">
            <div class="header-info">
                <h2>Employees Table Reports</h2>
                <h5>Number of Employees: {{ $employees->count() }}</h5>
            </div>
        </div>
    </div>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Login Email</th>
                <th>Contact Email</th>
                <th>Phone</th>
                <th>Age</th>
                <th>CNSS</th>
                <th>Bank Account Number</th>
                <th>Salary</th>
                <th>Cost Per Hour</th>
                <th>Work Hours Limit</th>
                <th>Cotisation CNSS</th>
                <th>Cotisation AMO</th>
                <th>Date Release</th>
            </tr>
        </thead>
        <tbody>
            @foreach($employees as $employee)
            <tr>
                <td>{{$employee->name}}</td>
                <td>{{$employee->email}}</td>
                <td>{{$employee->contact_email}}</td>
                <td>{{$employee->phoneNumber}}</td>
                <td>{{$employee->age}}</td>
                <td>{{$employee->cnss}}</td>
                <td>{{$employee->bankAccountNumber}}</td>
                <td>{{$employee->salary}} €</td>
                <td>{{$employee->cost_per_hour}} €</td>
                <td>{{$employee->workHours_limit}} h</td>
                <td>{{$employee->Cotisation_CNSS}} €</td>
                <td>{{$employee->Cotisation_AMO}} €</td>
                <td>{{$employee->Date_Release}}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <div class='footer-left'>Download Date: {{ date('d-m-Y') }}</div>
        <div class='footer-right'>Page Number: <span class="page-number"></span></div>
    </div>

</div>