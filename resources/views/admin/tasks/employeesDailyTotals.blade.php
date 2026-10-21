<!DOCTYPE html>
<html>
<head>
    <x-favicon />
    <meta charset="utf-8">
    <style>
        @page { size: A4 landscape; margin: 10mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        th, td { border: 1px solid #ddd; padding: 4px; text-align: center; word-wrap: break-word; }
        th { background: #f2f2f2; }
        .left { text-align: left; }
        .emp-col { width: 120px; }
        .day-left { width: 35px; }
        .total { width: 70px; }
    </style>
    <title>{{ __('messages.Employees Daily Totals') }}</title>
  </head>
  <body>
    <h3 style="margin: 0 0 8px 0;">{{ $entreprise->name ?? 'Entreprise' }} - Employees Daily Totals</h3>
    <p style="margin: 0 0 12px 0;">Period: {{ $start->format('Y-m-d') }} to {{ $end->format('Y-m-d') }}</p>

    <table>
      <thead>
        <tr>
          <th class="day-left">{{ __('messages.Day') }}</th>
          @foreach($employees as $emp)
            <th class="emp-col left">{{ $emp->name }}</th>
          @endforeach
          <th class="total">{{ __('messages.Total column') }}</th>
        </tr>
      </thead>
      <tbody>
        @foreach($dates as $d)
          @php $rowTotal = 0; @endphp
          <tr>
            <td class="day-left">{{ \Carbon\Carbon::parse($d)->format('d/m') }}</td>
            @foreach($employees as $emp)
              @php
                $mins = $totals[$emp->id][$d] ?? 0;
                $rowTotal += $mins;
                $h = intdiv($mins, 60);
                $m = $mins % 60;
              @endphp
              <td>{{ $mins ? sprintf('%dh%02dm', $h, $m) : '-' }}</td>
            @endforeach
            @php $th = intdiv($rowTotal, 60); $tm = $rowTotal % 60; @endphp
            <td><strong>{{ sprintf('%dh%02dm', $th, $tm) }}</strong></td>
          </tr>
        @endforeach
      </tbody>
      <tfoot>
        <tr>
          <th class="left">{{ __('messages.Total column') }}</th>
          @foreach($employees as $emp)
            @php
              $empTotal = 0;
              foreach ($dates as $d) {
                  $empTotal += ($totals[$emp->id][$d] ?? 0);
              }
              $eth = intdiv($empTotal, 60); $etm = $empTotal % 60;
            @endphp
            <th>{{ sprintf('%dh%02dm', $eth, $etm) }}</th>
          @endforeach
          @php
            $grand = 0;
            foreach ($dates as $d) {
                foreach ($employees as $emp) {
                    $grand += ($totals[$emp->id][$d] ?? 0);
                }
            }
            $gh = intdiv($grand, 60); $gm = $grand % 60;
          @endphp
          <th>{{ sprintf('%dh%02dm', $gh, $gm) }}</th>
        </tr>
      </tfoot>
    </table>
  </body>
</html>


