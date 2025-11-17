<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
        vertical-align: top; /* Aligns list content to the top */
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
</head>

<body>
    <div class="report-wrapper">

        <div class="details">
            <div class="details-left">
                <img src="{{ Storage::disk('do_spaces')->url($entreprise->image) }}" alt="Company Logo" class="company-logo">
            </div>
            <div class="details-right">
                <div class="header-info">
                    <h2>Tasks Table Reports</h2>
                    <h5>Number of Tasks: {{ $tasks->count() }}</h5>
                </div>
            </div>
        </div>

        <br>

        <table>
            <thead>
                <tr>
                    <th>Task Name</th>
                    <th>Project Name</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Employees</th>
                    <th>Teams</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                @foreach($tasks as $task)
                <tr>
                    <td>{{$task->title}}</td>
                    <td>{{$task->project_name}}</td>
                    <td>{{$task->start}}</td>
                    <td>{{$task->end}}</td>
                    <td>
                        @php
                        $employee_id = App\Models\employees_tasks::where('task_id', $task->id)->pluck('employee_id');
                        @endphp
                        @foreach(App\Models\Employees::whereIn('id', $employee_id)->get() as $employee)
                        <div>{{$employee->name}}</div>
                        @endforeach
                    </td>
                    <td>
                        @php
                        $team_tasks = App\Models\team_tasks::where('task_id', $task->id)->get();
                        @endphp
                        @forelse($team_tasks as $team_task)
                            @php
                            $team = App\Models\Teams::find($team_task->team_id);
                            @endphp
                            <div>{{$team->name}}</div>
                        @empty
                            <div>No team</div>
                        @endforelse
                    </td>
                    <td>{{$task->description}}</td>
                </tr>
                @endforeach
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
</body>
</html>