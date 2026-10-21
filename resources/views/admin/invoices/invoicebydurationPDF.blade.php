<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
    }

    .general-conditions {
        text-align: justify;
        line-height: 1.1;
        margin-bottom: 20px;
        font-size: 10px;
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
    </style>
</head>

<body>
    @php
        // Some controller paths (e.g. direct download/regenerate) don't pass this flag.
        // Default it so the PDF can render normally.
        $is_Editing = $is_Editing ?? false;

        // Some controller paths also don't pass these pre-formatted dates.
        // Derive them from the provided invoice model when missing.
        if (! isset($invoice_date)) {
            $invoice_date = isset($invoice) && $invoice?->created_at
                ? \Carbon\Carbon::parse($invoice->created_at)->format('d-m-Y')
                : '';
        }

        if (! isset($due_date)) {
            $due_date = isset($invoice) && $invoice?->due_date
                ? \Carbon\Carbon::parse($invoice->due_date)->format('d-m-Y')
                : '';
        }

        // Comment may be passed by some flows, but not by the download/regenerate path.
        $comment = $comment ?? ($invoice->comment ?? '');

    @endphp
    <div class="page">
        <div class="content-wrappe">
            <div class="header">
                <div class="header logo-right">
                    @if(Auth::user()->entreprise && Auth::user()->entreprise->image)
                        @if(filled(Auth::user()->entreprise?->image) && Storage::disk('public')->exists(Auth::user()->entreprise->image))
<img src="{{ storage_path('app/public/' . Auth::user()->entreprise->image) }}" alt="Company Logo" class="company-logo">
@endif
                    @endif
                </div>

            </div>

            <br><br>

            <div class="details">
                <div class="details-left">
                    <p>{{ __('messages.Invoice Date') }}: {{$is_Editing ? $invoice_last_date : $invoice_date}}<br />
                        {{ __('messages.Due Date') }}:
                        {{$is_Editing ? $due_last_date : $due_date}}
                        <br>
@if(filled($client->client_number))
                        {{ __('messages.Client Number') }} : {{ $client->client_number }} <br>
@endif
                        {{ __('messages.Invoice Number') }} : {{ $invoice->number ?? $invoice_id }}
                    </p>
                </div>
                <div class="details-right">
                    @include('partials.invoice-client-details')
                </div>
            </div>

            <br><br><br><br><br>
            <div class="invoice-number">
                {{ __('messages.Invoice') }} :
                {{$entreprise->invoice_prefix}}-{{ $invoice->number ?? $invoice_id }}
            </div>

            <table>
                <thead>
                    <tr>
                        <th>{{ __('messages.Designation') }}</th>
                        <th>{{ __('messages.Duration') }}</th>
                        <th>{{ __('messages.Unit Price') }}</th>
                        <th>{{ __('messages.Amount') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{strtoupper($projectinfos->name)}}</td>
                        <td>
                            <?php
                            $durationMinutes = $duration;
                            $hours = floor($durationMinutes / 60);
                            $minutes = $durationMinutes % 60;
                            echo $hours . 'h:' . sprintf('%02d', $minutes) . 'm';
                        ?>
                        </td>
                        <td>{{ $projectinfos->cost_per_hour }}€</td>
                        <td>
                            @php
                            $hours = round($duration / 60, 2);
                            $prixtotal = $projectinfos->cost_per_hour * $hours;
                            @endphp
                            {{ number_format($prixtotal, 2, ',', '') }}€
                        </td>
                    </tr>
                </tbody>
            </table>

            <style>
            .totals {
                border: none;
            }
            </style>

            <table class="totals">
                <tbody>
                    <tr>
                        <td colspan="3" class="totals">
                            <div class="label">{{ __('messages.Total Excl VAT') }}</div>
                            <div class="value">{{ number_format($prixtotal, 2, ',', '') }}€</div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="3" class="totals">
                            <div class="label">{{ __('messages.VAT') }} {{$projectinfos->tva}}%
                            </div>
                            <div class="value">
                                {{number_format(($prixtotal * ($projectinfos->tva / 100)), 2, ',', '')}}€</div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="3" class="totals">
                            <div class="label">{{ __('messages.Total Incl VAT') }}</div>
                            <div class="value">
                                <b>{{number_format(($prixtotal + ($prixtotal * ($projectinfos->tva / 100))), 2, ',', '')}}€</b>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>



            <div class="description">
@if(filled($projectinfos->description))
                <strong> - {{ __('messages.Information') }} :</strong> <br>
                {!! nl2br(e($projectinfos->description)) !!} <br>
@endif
@if(filled($comment ?? null))
                <strong> - {{ __('messages.Comment') }} : </strong> <br>
               <p style="white-space: pre-wrap;">{!! nl2br(e($comment)) !!}</p>
@endif
            </div>

            <div class="payment">
                - {{ __('messages.Please pay the amount of') }}
                <strong>{{number_format(($prixtotal + ($prixtotal * ($projectinfos->tva / 100))), 2, ',', '')}}€</strong>
                {{ __('messages.on the account mentioned below before') }}
                {{$is_Editing ? $due_last_date : $due_date}}
                {{ __('messages.mentioning the invoice number') }} <strong>{{ $invoice->number ?? $invoice_id }}</strong>
                <br>
@if(\App\Support\InvoicePresentation::showReverseChargeNotice($entreprise))
                - {{ __('messages.Intracommunity delivery services note') }}
@endif
                <br>
                - {{ __('messages.See tasks table in annex') }}
                <br>
                - {{ __('messages.See general conditions in annex') }}
                <br>
                - {{ __('messages.Thank you for your trust') }}
            </div>
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
    </div>

    <div class="page">
        <div class="content-wrapper">
            <table>
                <thead>
                    <td bordercolor=' #ccc' bgcolor='#f2f2f2' style='font-size:12px;'><strong>{{ __('messages.Task Name') }}</strong></td>
                    <td bordercolor='#ccc' bgcolor='#f2f2f2' style='font-size:12px;'><strong>{{ __('messages.Project / Subproject') }}</strong></td>
                    <td bordercolor='#ccc' bgcolor='#f2f2f2' style='font-size:12px;'><strong>{{ __('messages.Comment') }}</strong></td>
                    <td bordercolor='#ccc' bgcolor='#f2f2f2' style='font-size:12px;'><strong>{{ __('messages.Start Date') }} </strong>
                    </td>
                    <td bordercolor='#ccc' bgcolor='#f2f2f2' style='font-size:12px;'><strong>{{ __('messages.End Date') }}</strong></td>
                    <td bordercolor='#ccc' bgcolor='#f2f2f2' style='font-size:12px;'><strong>{{ __('messages.Duration') }}</strong></td>

                </thead>
                <tbody>
                    @php
                    $totalMinutes = 0; // Initialize total duration in minutes
                    @endphp
                    @foreach($collection as $coll)
                    <tr>
                        <td valign='top' style='font-size:9px;'>{{$coll->task_name}}</td>
                        <td valign='top' style='font-size:9px;'>{{ $coll->project_name ?? $projectinfos->name }}</td>
                        <td valign='top' style='font-size:9px;'>{{ $coll->comment }}</td>
                        <td valign='top' style='font-size:9px;'>{{ \Carbon\Carbon::parse($coll->start_date)->format('d-m-Y H:i') }}</td>
                        <td valign='top' style='font-size:9px;'>{{ \Carbon\Carbon::parse($coll->end_date)->format('d-m-Y H:i') }}</td>
                        <td valign='top' style='font-size:9px;'>
                            <?php
                // Convert duration from minutes to hours:minutes format
                $durationMinutes = $coll->duration;
                $totalMinutes += $durationMinutes; // Add current duration to total
                $hours = floor($durationMinutes / 60);
                $minutes = $durationMinutes % 60;
                echo $hours . 'h:' . sprintf('%02d', $minutes) . 'm';
                                            ?>
                        </td>
                    </tr>
                    @endforeach
                    <tr>
                        <td colspan="5" style='font-size:9px; text-align: right;'><strong>{{ __('messages.Total Duration') }}:</strong></td>
                        <td valign='top' style='font-size:9px;'>
                            {{ floor($totalMinutes / 60) }}h:{{ sprintf('%02d', $totalMinutes % 60) }}m
                        </td>
                    </tr>
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
    </div>



    <div class="page">
        <div class="content-wrapper">
            <h2>{{ __('messages.General Conditions') }} :</h2>
            <div class="general-conditions">
                <p>{!! nl2br(e($entreprise->general_conditions)) !!}</p>
            </div>
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
    </div>

</body>

</html>
