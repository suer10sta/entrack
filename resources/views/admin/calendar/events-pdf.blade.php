<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendar Events - {{ $monthName }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            color: #111827;
            background: #f9fafb;
            padding: 20px;
        }
        
        .calendar-container {
            background: white;
            padding: 20px;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            max-width: 100%;
        }
        
        .calendar-header {
            text-align: center;
            margin-bottom: 1.5rem;
        }
        
        .calendar-header h2 {
            font-size: 1.25rem;
            font-weight: 600;
            color: #111827;
        }
        
        .calendar-grid {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        
        .calendar-grid th {
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            padding: 8px;
            text-align: center;
            font-size: 0.75rem;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
        }
        
        .calendar-grid td {
            border: 1px solid #e5e7eb;
            padding: 4px;
            vertical-align: top;
            width: 14.28%;
            height: 100px;
            position: relative;
        }
        
        .calendar-day {
            font-size: 0.875rem;
            color: #111827;
            font-weight: 500;
            margin-bottom: 4px;
        }
        
        .calendar-day.other-month {
            color: #9ca3af;
        }
        
        .calendar-day.today {
            background-color: #dbeafe;
            border-radius: 4px;
            padding: 2px 4px;
            display: inline-block;
        }
        
        .event-bar {
            background-color: #2563eb;
            color: white;
            padding: 2px 4px;
            margin: 2px 0;
            border-radius: 4px;
            font-size: 0.7rem;
            line-height: 1.2;
            cursor: pointer;
        }
        
        .event-bar.finished {
            background-color: #6b7280 !important;
        }
        
        .event-bar.continuation {
            padding: 2px 4px;
            margin: 2px 0;
            border-radius: 0;
            min-height: auto;
            background-color: inherit;
        }
        
        .event-bar.continuation-start {
            border-top-left-radius: 4px;
            border-bottom-left-radius: 4px;
        }
        
        .event-bar.continuation-end {
            border-top-right-radius: 4px;
            border-bottom-right-radius: 4px;
        }
        
        .event-title {
            font-weight: 500;
            display: block;
        }
        
        .event-description {
            font-size: 0.65rem;
            opacity: 0.8;
            margin-top: 2px;
            display: block;
            word-wrap: break-word;
        }
        
        .event-continuation {
            height: 20px;
            margin: 2px 0;
            background-color: #2563eb;
            border-radius: 0;
        }
        
        .event-continuation.continuation-end {
            border-top-right-radius: 4px;
            border-bottom-right-radius: 4px;
        }
        
        .cell-with-event {
            position: relative;
        }
        
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div class="calendar-container">
        <div class="calendar-header">
            <h2>Planning Calendar - {{ $monthName }}</h2>
        </div>
        
        @php
            // Parse month and year from monthName (e.g., "January 2026")
            try {
                $monthYear = \Carbon\Carbon::parse($monthName);
                $year = $monthYear->year;
                $month = $monthYear->month;
            } catch (\Exception $e) {
                // Fallback to current month if parsing fails
                $monthYear = \Carbon\Carbon::now();
                $year = $monthYear->year;
                $month = $monthYear->month;
            }
            
            // Get first day of month and last day
            $firstDay = \Carbon\Carbon::create($year, $month, 1);
            $lastDay = $firstDay->copy()->endOfMonth();
            
            // Get first day of week (0 = Sunday, 6 = Saturday)
            $startDayOfWeek = $firstDay->dayOfWeek;
            
            // Get number of days in month
            $daysInMonth = $firstDay->daysInMonth;
            
            // Calculate days from previous month to show
            $daysFromPrevMonth = $startDayOfWeek;
            
            // Group events by date and track their position in the span
            $eventsByDate = [];
            foreach ($events as $event) {
                $startDate = \Carbon\Carbon::parse($event['start']);
                $endDate = \Carbon\Carbon::parse($event['end']);
                
                // Add event to all days it spans with position info
                $currentDate = $startDate->copy();
                $dayIndex = 0;
                $totalDays = $startDate->diffInDays($endDate) + 1;
                
                while ($currentDate->lte($endDate)) {
                    $dateKey = $currentDate->format('Y-m-d');
                    if (!isset($eventsByDate[$dateKey])) {
                        $eventsByDate[$dateKey] = [];
                    }
                    $eventsByDate[$dateKey][] = [
                        'event' => $event,
                        'isStart' => $dayIndex == 0,
                        'isEnd' => $dayIndex == $totalDays - 1,
                        'dayIndex' => $dayIndex,
                        'totalDays' => $totalDays
                    ];
                    $currentDate->addDay();
                    $dayIndex++;
                }
            }
            
            // Get today's date for highlighting
            $today = \Carbon\Carbon::today();
        @endphp
        
        <table class="calendar-grid">
            <thead>
                <tr>
                    <th>Sun</th>
                    <th>Mon</th>
                    <th>Tue</th>
                    <th>Wed</th>
                    <th>Thu</th>
                    <th>Fri</th>
                    <th>Sat</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $currentDate = $firstDay->copy()->subDays($daysFromPrevMonth);
                    $weeks = ceil(($daysFromPrevMonth + $daysInMonth) / 7);
                @endphp
                
                @for($week = 0; $week < $weeks; $week++)
                    <tr>
                        @for($day = 0; $day < 7; $day++)
                            @php
                                $isCurrentMonth = $currentDate->month == $month && $currentDate->year == $year;
                                $dateKey = $currentDate->format('Y-m-d');
                                $isToday = $currentDate->format('Y-m-d') == $today->format('Y-m-d');
                                $dayEvents = isset($eventsByDate[$dateKey]) ? $eventsByDate[$dateKey] : [];
                            @endphp
                            <td class="{{ $isCurrentMonth && count($dayEvents) > 0 ? 'cell-with-event' : '' }}">
                                <div class="calendar-day {{ !$isCurrentMonth ? 'other-month' : '' }} {{ $isToday ? 'today' : '' }}">
                                    {{ $currentDate->day }}
                                </div>
                                @if($isCurrentMonth && count($dayEvents) > 0)
                                    @foreach($dayEvents as $eventData)
                                        @php
                                            $event = $eventData['event'];
                                            $eventStart = \Carbon\Carbon::parse($event['start']);
                                            $eventEnd = \Carbon\Carbon::parse($event['end']);
                                            $eventColor = $event['color'] ?? '#2563eb';
                                            $isStart = $eventData['isStart'];
                                            $isEnd = $eventData['isEnd'];
                                            $dayIndex = $eventData['dayIndex'];
                                            $totalDays = $eventData['totalDays'];
                                        @endphp
                                        @if($isStart)
                                            {{-- Show full event bar with details at the start --}}
                                            <div class="event-bar {{ $event['is_finished'] ? 'finished' : '' }} {{ $totalDays > 1 ? 'continuation-start' : '' }}" style="background-color: {{ $eventColor }};">
                                                <span class="event-title">{{ $event['title'] }}</span>
                                                @if(!empty($event['description']))
                                                    <span class="event-description">{{ $event['description'] }}</span>
                                                @endif
                                            </div>
                                        @elseif(!$isEnd)
                                            {{-- Show continuation bar with text in middle days --}}
                                            <div class="event-bar {{ $event['is_finished'] ? 'finished' : '' }} continuation" style="background-color: {{ $eventColor }};">
                                                <span class="event-title">{{ $event['title'] }}</span>
                                                @if(!empty($event['description']))
                                                    <span class="event-description">{{ $event['description'] }}</span>
                                                @endif
                                            </div>
                                        @else
                                            {{-- Show end of continuation bar with text --}}
                                            <div class="event-bar {{ $event['is_finished'] ? 'finished' : '' }} continuation continuation-end" style="background-color: {{ $eventColor }};">
                                                <span class="event-title">{{ $event['title'] }}</span>
                                                @if(!empty($event['description']))
                                                    <span class="event-description">{{ $event['description'] }}</span>
                                                @endif
                                            </div>
                                        @endif
                                    @endforeach
                                @endif
                            </td>
                            @php
                                $currentDate->addDay();
                            @endphp
                        @endfor
                    </tr>
                @endfor
            </tbody>
        </table>
    </div>
</body>
</html>
