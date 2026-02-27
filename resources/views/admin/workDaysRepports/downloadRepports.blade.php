<style>
body {
    font-family: Arial, sans-serif;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 15px; 
}

th {
    border: 1px solid black;
    padding: 8px;
    text-align: left;
    font-family: Arial, sans-serif;
    font-size: 12px;
    font-weight: bold;
}

td {
    border: 1px solid black;
    padding: 8px;
    text-align: left;
    font-family: Arial, sans-serif;
    font-size: 11px;
    font-weight: normal;
}

thead {
    background-color: #686ce4;
    color: white;
}

.company-logo {
    width: 80px;
    margin-right: 20px;
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

.footer {
  width: 100%;
  display: flex;
  justify-content: space-between;
  margin-bottom: 20px;
  position: fixed;
  bottom: 0; /* Ensure footer sticks to the bottom */
  left: 0; /* Align footer to the left */
}

.footer-left {
  width: 48%;
  font-size: 12px;
}

.footer-right {
  width: 48%;
  font-size: 12px;
  text-align: right;
  position: absolute;
  right: 0;
  top: 0;
}

.page-number:before {
    content: counter(page);
}

@media print {
    th, td { 
        font-size: 10px; 
    }
    .footer {
        position: fixed;
        bottom: 0;
        left: 0;
    }
    .page-break {
        page-break-before: always;
    }
}
</style>

<div class="container">
    <div class="details">
        <div class="details-left">
       
        </div>
        <div class="details-right">
            <div class="header-info">
                <h2>{{ __('messages.Work Days Reports') }} {{ __('messages.For period label') }} <strong>{{ $month }}-{{ $year }} </strong></h2>
            </div>
        </div>
    </div>
    <br>
    <table>
        <thead>
            <tr>
                <th>{{ __('messages.Employee Name header') }}</th> 
                @if (!in_array('work_days_gross', $excludedColumns)) <th>{{ __('messages.Gross Days') }}</th> @endif
                @if (!in_array('days_worked', $excludedColumns)) <th>{{ __('messages.Internal Work Days') }}</th> @endif
                @if (!in_array('days_worked_gross', $excludedColumns)) <th>{{ __('messages.Work Days Without Holidays') }}</th> @endif
                @if (!in_array('days_of_unpaid_absence', $excludedColumns)) <th>{{ __('messages.Unpaid Absence Days') }}</th> @endif
                @if (!in_array('sick_days', $excludedColumns)) <th>{{ __('messages.Sick Days') }}</th> @endif
                @if (!in_array('holidays', $excludedColumns)) <th>{{ __('messages.Holidays') }}</th> @endif
                @if (!in_array('remaining_days_of_paid_leave', $excludedColumns)) <th>{{ __('messages.Remaining Paid Leave') }}</th> @endif
                @if (!in_array('number_of_days_of_paid_leave_acquired', $excludedColumns)) <th>{{ __('messages.Paid Leave Acquired') }}</th> @endif
                @if (!in_array('number_of_days_of_paid_leave_taken', $excludedColumns)) <th>{{ __('messages.Paid Leave Taken') }}</th> @endif
                @if (!in_array('holiday_balance', $excludedColumns)) <th>{{ __('messages.Leave Balance') }}</th> @endif
                @if (!in_array('dates_of_holidays', $excludedColumns)) <th>{{ __('messages.Paid Leave Dates') }}</th> @endif
                @if (!in_array('days_to_recover', $excludedColumns)) <th>{{ __('messages.Days To Recover') }}</th> @endif
                @if (!in_array('interne_remarque', $excludedColumns)) <th>{{ __('messages.Internal Remark') }}</th> @endif
                @if (!in_array('remarque', $excludedColumns)) <th>{{ __('messages.Remark') }}</th> @endif
                @if (!in_array('month', $excludedColumns)) <th>{{ __('messages.Month') }}</th> @endif
                @if (!in_array('year', $excludedColumns)) <th>{{ __('messages.Year') }}</th> @endif
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
        <div class='footer-left'>{{ __('messages.Download Date') }}: {{ date('d-m-Y') }}</div>
        <div class='footer-right'>{{ __('messages.Page Number') }}: <span class="page-number"></span></div>
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