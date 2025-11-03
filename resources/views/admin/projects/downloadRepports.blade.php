<style>
    /* --- Base & Typography --- */
    * {
        /* Applies a more predictable box model to all elements */
        box-sizing: border-box;
    }

    body {
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        font-size: 14px; /* Increased base font size */
        line-height: 1.6; /* Improved line spacing */
        margin: 0;
        padding: 0;
        color: #333;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
    }

    .container {
        width: 100%;
        padding: 30px; /* Added more breathing room */
        box-sizing: border-box;
    }

    h2,
    h5 {
        margin: 0 0 5px 0;
    }

    h2 {
        font-size: 22px; /* Made title stronger */
        font-weight: 600;
        color: #111;
    }

    h5 {
        font-size: 14px;
        font-weight: normal;
        color: #555;
    }

    /* --- Header / Details --- */
    .details {
        display: flex;
        justify-content: space-between;
        align-items: center; /* Vertically aligns logo with text */
        margin-bottom: 40px; /* More space above table */
    }

    .company-logo {
        width: 100px; /* Slightly larger logo */
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
        background-color: #1E9FD8;
        color: white;
    }
    
    /* Unified borders for a cleaner grid */
    th,
    td {
        border: 1px solid #E2E8F0; /* Single, light gray border */
        padding: 12px 15px; /* More generous padding */
        text-align: left;
    }

    th {
        font-size: 12px; /* Made header font legible */
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* Table Body */
    td {
        font-size: 13px; /* Increased data font size */
        vertical-align: top; /* Ensures long text aligns neatly */
    }

    /* --- Zebra Striping --- */
    tbody tr:nth-of-type(even) {
        background-color: #F7FAFC; /* Adds alternating row color */
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
        padding: 20px 30px; /* Matches container padding */
        box-sizing: border-box;
        border-top: 1px solid #eee;
        background: #fff;
    }

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