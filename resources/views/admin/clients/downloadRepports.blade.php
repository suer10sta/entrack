<style>
    /* --- Base & Typography --- */
    body {
        font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
        font-size: 14px;
        line-height: 1.5;
        margin: 0;
        padding: 0;
        color: #333;
    }

    .container {
        width: 100%;
        padding: 20px;
    }

    h2,
    h5 {
        margin: 0 0 5px 0;
    }

    /* --- Title Amelioration --- */
    h2 {
        font-size: 24px; /* Made larger */
        font-weight: 600; /* Made bolder */
        color: #111; /* Darker for more contrast */
    }

    h5 {
        font-size: 14px;
        font-weight: normal;
        color: #555; /* Kept subtitle light */
    }

    /* --- Header / Details --- */
    .details {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 30px;
    }

    .company-logo {
        width: 80px;
        height: auto;
    }

    .details-right {
        text-align: right;
    }

    /* --- Table Styles --- */
    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 60px;
    }

    /* Table Header */
    thead {
        background-color: #0EA5E9;
        color: white;
    }

    /* --- Table Header Title Amelioration --- */
    th {
        border: 1px solid #0056b3;
        padding: 10px;
        font-size: 12px;
        font-weight: 700; /* Made stronger */
        text-align: center;
        text-transform: uppercase;
        letter-spacing: 0.5px; /* Added spacing for uppercase text */
    }

    /* Table Body */
    td {
        border: 1px solid #ddd;
        padding: 10px;
        text-align: left;
        font-size: 13px;
    }

    /* --- Footer --- */
    .footer {
        position: fixed;
        bottom: 0;
        left: 0;
        width: 100%;
        display: flex;
        justify-content: space-between;
        font-size: 12px;
        color: #777;
        padding: 15px 20px;
        box-sizing: border-box;
        border-top: 1px solid #eee;
        background: #fff;
    }

    /* CSS counter for print/PDF page numbers */
    .page-number::before {
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
        <div class='footer-left'>Download Date: {{ date('d-m-Y') }}</div>
        <div class='footer-right'>Page Number: <span class="page-number"></span></div>
    </div>

</div>