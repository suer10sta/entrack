<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.Work Days Report') }}</title>
    <style>
        /* [MODIFIED] Styles adapted for a flowing report */
        body {
            font-family: Tahoma, sans-serif;
            font-size: 11px; /* Reduced size to fit many columns */
            color: #333333;
            background-color: #FFFFFF;
            margin: 0;
            padding: 0;
        }

        /* [NEW] This wrapper constrains the width but allows content to flow */
        .report-wrapper {
            width: 100%;
            max-width: 800px; /* Adjusted for landscape/wider data */
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

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            table-layout: auto; /* Allows columns to size based on content */
        }

        table, th, td {
            border: 1px solid #CCCCCC;
        }

        th, td {
            padding: 5px; /* Reduced padding */
            text-align: left;
            font-size: 10px; /* Reduced font size for table data */
            word-wrap: break-word;
            vertical-align: middle;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        p {
            margin: 0;
        }

        /* [MODIFIED] Footer is no longer fixed to screen bottom, but flows or prints at bottom */
        .footer {
            width: 100%;
            max-width: 800px; /* Match report width */
            margin: 0 auto;
            padding: 20px;
            padding-top: 20px;
            box-sizing: border-box;
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
            font-size: 10px;
        }

        .company-logo {
            width: 100px;
            height: auto;
        }

        /* [NEW] Print-specific styles */
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
                    @if(filled(Auth::user()->entreprise?->image) && Storage::disk('public')->exists(Auth::user()->entreprise->image))
<img src="{{ storage_path('app/public/' . Auth::user()->entreprise->image) }}" alt="Company Logo" class="company-logo">
@endif
                @endif
            </div>
            <div class="details-right">
                <div class="header-info">
                    <h2>{{ __('messages.Work Days Report') }}</h2>
                    <h4>{{ __('messages.Period:') }} <strong>{{ $month }}-{{ $year }}</strong></h4>
                </div>
            </div>
        </div>

        <br>

        <table>
            <thead>
                <tr>
                    <th>{{ __('messages.Nom d\'employé') }}</th>
                    @if (!in_array('work_days_gross', $excludedColumns)) <th>{{ __('messages.Jrs brut') }}</th> @endif
                    @if (!in_array('days_worked', $excludedColumns)) <th>{{ __('messages.Travaillés (Int)') }}</th> @endif
                    @if (!in_array('days_worked_gross', $excludedColumns)) <th>{{ __('messages.Jrs Net') }}</th> @endif
                    @if (!in_array('days_of_unpaid_absence', $excludedColumns)) <th>{{ __('messages.Absence') }}</th> @endif
                    @if (!in_array('sick_days', $excludedColumns)) <th>{{ __('messages.Maladie') }}</th> @endif
                    @if (!in_array('holidays', $excludedColumns)) <th>{{ __('messages.Fériés') }}</th> @endif
                    @if (!in_array('remaining_days_of_paid_leave', $excludedColumns)) <th>{{ __('messages.Reliquat CP') }}</th> @endif
                    @if (!in_array('number_of_days_of_paid_leave_acquired', $excludedColumns)) <th>{{ __('messages.Acquis CP') }}</th> @endif
                    @if (!in_array('number_of_days_of_paid_leave_taken', $excludedColumns)) <th>{{ __('messages.Pris CP') }}</th> @endif
                    @if (!in_array('holiday_balance', $excludedColumns)) <th>{{ __('messages.Balance') }}</th> @endif
                    @if (!in_array('dates_of_holidays', $excludedColumns)) <th>{{ __('messages.Dates CP') }}</th> @endif
                    @if (!in_array('days_to_recover', $excludedColumns)) <th>{{ __('messages.Récup') }}</th> @endif
                    @if (!in_array('interne_remarque', $excludedColumns)) <th>{{ __('messages.Remarque Int') }}</th> @endif
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

                                $totalHours = number_format($totalMinutes / 60, 2);
                            @endphp
                            @continue(!$employee)
                            <strong>{{ optional($employee)->name }}</strong>
                        </p>
                    </td>

                    @if (!in_array('work_days_gross', $excludedColumns))
                        <td><p><b>{{$workDay->work_days_gross}}</b></p></td>
                    @endif

                    @if (!in_array('days_worked', $excludedColumns))
                        <td><p>{{$workDay->days_worked}}</p></td>
                    @endif

                    @php
                        // Logic for calculating Saturdays
                        $monthNumber = match(strtolower($month)) {
                            'january' => 1, 'february' => 2, 'march' => 3, 'april' => 4, 'may' => 5, 'june' => 6,
                            'july' => 7, 'august' => 8, 'september' => 9, 'october' => 10, 'november' => 11, 'december' => 12,
                            default => 1
                        };

                        $firstDay = Carbon\Carbon::create($year, $monthNumber, 1);
                        $lastDay = $firstDay->copy()->endOfMonth();

                        $saturdays = 0;
                        for($date = $firstDay; $date->lte($lastDay); $date->addDay()) {
                            if($date->isSaturday()) {
                                $saturdays++;
                            }
                        }

                        $totalDays = $workDay->days_worked + $saturdays + $workDay->days_to_recover;
                    @endphp

                    @if (!in_array('days_worked_gross', $excludedColumns))
                        <td><p><b>{{$totalDays}}</b></p></td>
                    @endif

                    @if (!in_array('days_of_unpaid_absence', $excludedColumns))
                        <td><p>{{$workDay->days_of_unpaid_absence}}</p></td>
                    @endif

                    @if (!in_array('sick_days', $excludedColumns))
                        <td><p>{{$workDay->sick_days}}</p></td>
                    @endif

                    @if (!in_array('holidays', $excludedColumns))
                        <td><p>{{$workDay->holidays}}</p></td>
                    @endif

                    @if (!in_array('remaining_days_of_paid_leave', $excludedColumns))
                        <td><p>{{$workDay->remaining_days_of_paid_leave}}</p></td>
                    @endif

                    @if (!in_array('number_of_days_of_paid_leave_acquired', $excludedColumns))
                        <td><p>{{$workDay->number_of_days_of_paid_leave_acquired}}</p></td>
                    @endif

                    @if (!in_array('number_of_days_of_paid_leave_taken', $excludedColumns))
                        <td><p>{{$workDay->number_of_days_of_paid_leave_taken}}</p></td>
                    @endif

                    @if (!in_array('holiday_balance', $excludedColumns))
                        <td><p>{{$workDay->holiday_balance}}</p></td>
                    @endif

                    @if (!in_array('dates_of_holidays', $excludedColumns))
                        <td><p>{{$workDay->dates_of_holidays}}</p></td>
                    @endif

                    @if (!in_array('days_to_recover', $excludedColumns))
                        <td><p>{{$workDay->days_to_recover}}</p></td>
                    @endif

                    @if (!in_array('interne_remarque', $excludedColumns))
                        <td><p>{{$workDay->interne_remarque}}</p></td>
                    @endif

                    @if (!in_array('remarque', $excludedColumns))
                        <td><p>{{$workDay->remarque}}</p></td>
                    @endif

                    @if (!in_array('month', $excludedColumns))
                        <td><p>{{$workDay->month}}</p></td>
                    @endif

                    @if (!in_array('year', $excludedColumns))
                        <td><p>{{$workDay->year}}</p></td>
                    @endif
                </tr>
                @endforeach
            </tbody>
        </table>

    </div>

    <div class="footer">
        <div class="footer-content">
            <div class="footer-left footer-text">
@include('partials.invoice-company-details', ['section' => 'identity'])
</div>
            <div class="footer-center footer-text">
@include('partials.invoice-company-details', ['section' => 'contact'])
</div>
            <div class="footer-right footer-text">
@include('partials.invoice-company-details', ['section' => 'bank'])
</div>
        </div>
    </div>
</body>
</html>
