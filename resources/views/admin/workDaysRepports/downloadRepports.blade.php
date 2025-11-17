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
        vertical-align: top;
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
       
        </div>
        <div class="details-right">
            <div class="header-info">
                <h2>Work Days Reports Table for : <strong>{{ $month }}-{{ $year }} </strong></h2>
            </div>
        </div>
    </div>
    <br>
    <table>
        <thead>
            <tr>
                <th>Nom d'employé</th> 
                @if (!in_array('work_days_gross', $excludedColumns)) <th>Nombre jours de travail brut</th> @endif
                @if (!in_array('days_worked', $excludedColumns)) <th>Nombre jours travaillés interne</th> @endif
                @if (!in_array('days_worked_gross', $excludedColumns)) <th>Nombre de jours de travail sans les jours fériés et sans les congés payés</th> @endif
                @if (!in_array('days_of_unpaid_absence', $excludedColumns)) <th>Jours d'absence</th> @endif
                @if (!in_array('sick_days', $excludedColumns)) <th>Nombre jours maladie</th> @endif
                @if (!in_array('holidays', $excludedColumns)) <th>Nombre jours fériés</th> @endif
                @if (!in_array('remaining_days_of_paid_leave', $excludedColumns)) <th>Reliquat jours congé payé</th> @endif
                @if (!in_array('number_of_days_of_paid_leave_acquired', $excludedColumns)) <th>Nombre jours congé payé acquis</th> @endif
                @if (!in_array('number_of_days_of_paid_leave_taken', $excludedColumns)) <th>Nombre jours congé payé pris</th> @endif
                @if (!in_array('holiday_balance', $excludedColumns)) <th>Solde congés</th> @endif
                @if (!in_array('dates_of_holidays', $excludedColumns)) <th>Dates des jours congé payé</th> @endif
                @if (!in_array('days_to_recover', $excludedColumns)) <th>Jours a récupérer</th> @endif
                @if (!in_array('interne_remarque', $excludedColumns)) <th>Interne remarque</th> @endif
                @if (!in_array('remarque', $excludedColumns)) <th>Remarque</th> @endif
                @if (!in_array('month', $excludedColumns)) <th>Mois</th> @endif
                @if (!in_array('year', $excludedColumns)) <th>Année</th> @endif
            </tr>
        </thead>
        <tbody>
            @foreach($workDaysRepports as $workDay)
            <tr>
                <td>
                    <p>
                        @php 
                        $employee = \App\Models\Employees::where('id', $workDay->employee_id)->where('archived', 0)->first();
                        $totalMinutes = \App\Models\tasks_timer::where('employee_id', $workDay->employee_id)
                                ->whereBetween('start_date', [$startDate, $endDate])
                                ->whereNotNull('end_date')
                                ->sum('duration');

                                $totalHours = number_format($totalMinutes / 60, 2)
                        @endphp
                        @continue(!$employee)
                        {{ optional($employee)->name }}
                    </p>
                </td>
                @if (!in_array('work_days_gross', $excludedColumns)) <td><p><b>{{$workDay->work_days_gross}} jrs</b></p></td> @endif
                @if (!in_array('days_worked', $excludedColumns)) <td><p>{{$workDay->days_worked}} jrs</p></td> @endif
                @php
                                // Convert text month to number
                                $monthNumber = match(strtolower($month)) {
                                    'january' => 1,
                                    'february' => 2,
                                    'march' => 3,
                                    'april' => 4,
                                    'may' => 5,
                                    'june' => 6,
                                    'july' => 7,
                                    'august' => 8,
                                    'september' => 9,
                                    'october' => 10,
                                    'november' => 11,
                                    'december' => 12,
                                    default => 1
                                };

                                // Create Carbon date with numeric month
                                $firstDay = Carbon\Carbon::create($year, $monthNumber, 1);
                                $lastDay = $firstDay->copy()->endOfMonth();
                                
                                // Count Saturdays in the month
                                $saturdays = 0;
                                for($date = $firstDay; $date->lte($lastDay); $date->addDay()) {
                                    if($date->isSaturday()) {
                                        $saturdays++;
                                    }
                                }
                                
                                // Add Saturdays to worked days
                                $totalDays = $workDay->days_worked + $saturdays + $workDay->days_to_recover;
                @endphp
                @if (!in_array('days_worked_gross', $excludedColumns)) <td><p><b>{{$totalDays}} jrs</b></p></td> @endif
                @if (!in_array('days_of_unpaid_absence', $excludedColumns)) <td><p>{{$workDay->days_of_unpaid_absence}}</p></td> @endif
                @if (!in_array('sick_days', $excludedColumns)) <td><p>{{$workDay->sick_days}}</p></td> @endif
                @if (!in_array('holidays', $excludedColumns)) <td><p>{{$workDay->holidays}}</p></td> @endif
                @if (!in_array('remaining_days_of_paid_leave', $excludedColumns)) <td><p>{{$workDay->remaining_days_of_paid_leave}}</p></td> @endif
                @if (!in_array('number_of_days_of_paid_leave_acquired', $excludedColumns)) <td><p>{{$workDay->number_of_days_of_paid_leave_acquired}}</p></td> @endif
                @if (!in_array('number_of_days_of_paid_leave_taken', $excludedColumns)) <td><p>{{$workDay->number_of_days_of_paid_leave_taken}}</p></td> @endif
                @if (!in_array('holiday_balance', $excludedColumns)) <td><p>{{$workDay->holiday_balance}}</p></td> @endif
                @if (!in_array('dates_of_holidays', $excludedColumns)) <td><p>{{$workDay->dates_of_holidays}}</p></td> @endif
                @if (!in_array('days_to_recover', $excludedColumns)) <td><p>{{$workDay->days_to_recover}}</p></td> @endif
                @if (!in_array('interne_remarque', $excludedColumns)) <td><p>{{$workDay->interne_remarque}}</p></td> @endif
                @if (!in_array('remarque', $excludedColumns)) <td><p>{{$workDay->remarque}}</p></td> @endif
                @if (!in_array('month', $excludedColumns)) <td><p>{{$workDay->month}}</p></td> @endif
                @if (!in_array('year', $excludedColumns)) <td><p>{{$workDay->year}}</p></td> @endif
            </tr>
            @endforeach
        </tbody>
    </table>
    <br>
    <div class="footer">
        <div class='footer-left'>Download Date: {{ date('d-m-Y') }}</div>
        <div class='footer-right'>Page Number: <span class="page-number"></span></div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const pages = document.querySelectorAll('.page');
    if (pages.length === 0) {
        console.error('No pages found. Ensure your HTML structure includes elements with a .page class.');
        return;
    }
    pages.forEach((page, index) => {
        const pageNumberElement = page.querySelector('.footer .page-number');
        if (pageNumberElement) {
            pageNumberElement.textContent = index + 1;
        } else {
            console.error('Page number element not found in page ' + (index + 1));
        }
    });
});
</script>