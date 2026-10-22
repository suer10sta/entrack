<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <x-favicon />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.Invoices Report') }}</title>
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
            width: 40%;
            font-size: 12px;
        }

        .details-right {
            width: 58%;
            font-size: 12px;
            text-align: right;
            position: absolute;
            right: 0;
            top: 0;
        }

        /* Totals Summary Box Style */
        .totals-summary {
            margin-top: 10px;
            margin-bottom: 20px;
            padding: 10px;
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            font-size: 11px;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
        }
        .summary-item {
            margin-right: 15px;
            margin-bottom: 5px;
        }
        .summary-item strong { color: #555; }
        .summary-item .paid { color: #28a745; font-weight: bold; }
        .summary-item .unpaid { color: #dc3545; font-weight: bold; }
        .summary-item .credit { color: #007bff; font-weight: bold; }
        .summary-item .total { color: #333; font-weight: bold; }

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
            padding: 6px; /* Reduced padding */
            text-align: left;
            font-size: 10px; /* Reduced font size for table data */
            word-wrap: break-word;
            vertical-align: top;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        /* [MODIFIED] Footer is fixed to bottom of page/screen */
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
                    <h2>{{ __('messages.Invoices Report') }}</h2>
                    <h4>{{ __('messages.Number of Invoices') }}: {{ $invoices->count() }}</h4>
                </div>
            </div>
        </div>

        {{-- Totals Summary Section --}}
        <div class="totals-summary">
            <div class="summary-row">
                <div class="summary-item">
                    <strong>{{ __('messages.Invoices Total Paid') }}</strong> <span class="paid">{{ number_format($totalPaid, 2, ',', ' ') }}€</span>
                </div>
                <div class="summary-item">
                    <strong>{{ __('messages.Invoices Total Unpaid') }}</strong> <span class="unpaid">{{ number_format($totalUnpaid, 2, ',', ' ') }}€</span>
                </div>
                <div class="summary-item">
                    <strong>{{ __('messages.Invoices Total TVA') }}</strong> <span>{{ number_format($totaltva, 2, ',', ' ') }}€</span>
                </div>
                <div class="summary-item">
                    <strong>{{ __('messages.Invoices Total Credit Note') }}</strong> <span class="credit">{{ number_format($totalCreditNote, 2, ',', ' ') }}€</span>
                </div>
                <div class="summary-item">
                    <strong>{{ __('messages.Invoices Total') }}</strong> <span class="total">{{ number_format($total, 2, ',', ' ') }}€</span>
                </div>
            </div>
        </div>

        <br>

        <table>
            <thead>
                <tr>
                    <th style="width: 12%;">{{ __('messages.Invoice N') }}</th>
                    <th>{{ __('messages.Project Name') }}</th>
                    <th>{{ __('messages.VAT') }}</th>
                    <th>{{ __('messages.Client Name') }}</th>
                    <th>{{ __('messages.Start Date') }}</th>
                    <th>{{ __('messages.End Date') }}</th>
                    <th>{{ __('messages.Invoice Type') }}</th>
                    <th style="width: 10%;">{{ __('messages.Created At') }}</th>
                    <th style="width: 8%;">{{ __('messages.Payment column') }}</th>
                    <th>{{ __('messages.Cost') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoices as $invoice)
                <tr>
                    <td>
                        @php 
                            $dueDate = \Carbon\Carbon::parse($invoice->due_date);
                            $today = \Carbon\Carbon::now();
                            $isOverdue = $dueDate->isPast() && !$invoice->Payment_status;
                            $daysOverdue = $isOverdue ? $today->diffInDays($dueDate) : 0;
                        @endphp
                        <span style="{{ $isOverdue ? 'color: red;' : '' }}">
                            <b>{{ \App\Support\InvoiceNumbering::reference($entreprise, $invoice, true) }}</b>
                            @if($isOverdue)
                                <br><span style="color: red; font-size: 9px;">({{ $daysOverdue }} days overdue)</span>
                            @endif
                        </span>
                    </td>
                    <td>
                        @php 
                        $project = App\Models\Project::find($invoice->project_id);
                        @endphp
                        {{$project->name ?? __('messages.N/A')}}
                    </td>
                    <td>
                        {{$project->tva ?? 0}}%
                    </td>
                    <td>
                        @php 
                        $client = App\Models\Client::find($invoice->client_id);
                        @endphp
                        {{$client->name ?? __('messages.N/A')}}
                    </td>
                    <td>{{$invoice->start_date}}</td>
                    <td>{{$invoice->end_date}}</td>
                    <td>{{$invoice->type}}</td>
                    <td>{{ \Carbon\Carbon::parse($invoice->created_at)->format('d-m-Y') }}</td>
                    <td>
                        @if($invoice->Payment_status == 1)
                            <span style="color: #28a745; font-weight: bold;">{{ __('messages.Paid') }}</span>
                        @elseif($invoice->Payment_status == -1)
                            <span style="color: #007bff; font-weight: bold;">{{ __('messages.Credit Note') }}</span>
                        @else
                            <span style="color: #dc3545; font-weight: bold;">{{ __('messages.Unpaid') }}</span>                     
                        @endif
                    </td>
                    <td>
                        <span style="font-weight: bold; color: {{ $invoice->Payment_status == 1 ? '#28a745' : ($invoice->Payment_status == -1 ? '#007bff' : '#dc3545') }}">
                            €{{number_format($invoice->total_ttc, 2, ',', ' ')}}
                        </span>
                    </td> 
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
