<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <x-favicon />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.Credit Note Report') }}</title>
    <style>
        /* [MODIFIED] Consistent Font and Size */
        body {
            font-family: Tahoma, sans-serif;
            font-size: 11px;
            color: #333333;
            background-color: #FFFFFF;
            margin: 0;
            padding: 0;
        }

        /* [NEW] Consistent Wrapper */
        .report-wrapper {
            width: 100%;
            max-width: 900px;
            margin: 0 auto;
            padding: 40px 20px 100px 20px;
            position: relative;
        }

        .header {
            width: 100%;
            text-align: center;
            margin-bottom: 30px;
        }

        .details {
            width: 100%;
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
            position: relative;
            padding-bottom: 10px;
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
        
        .header-info h2 {
            margin-top: 0;
            margin-bottom: 5px;
        }
        
        .header-info h4 {
            margin-top: 0;
            margin-bottom: 0;
            font-weight: normal;
        }

        .company-logo {
            width: 100px;
            height: auto;
        }

        /* [NEW] Styled Summary Box */
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
            justify-content: flex-end;
            gap: 20px;
        }
        .summary-item strong { color: #555; }
        .summary-item span { font-weight: bold; color: #333; }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            margin-top: 20px;
            table-layout: fixed; 
        }

        table, th, td {
            border: 1px solid #CCCCCC;
        }

        th, td {
            padding: 6px 5px;
            text-align: left;
            font-size: 11px;
            vertical-align: top;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        /* [MODIFIED] Fixed Footer */
        .footer {
            width: 100%;
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
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

        .footer-left { position: absolute; left: 0; text-align: left; }
        .footer-center { position: absolute; left: 50%; transform: translateX(-50%); text-align: center; }
        .footer-right { position: absolute; right: 0; text-align: right; }

        .footer-text {
            color: #333333;
            line-height: 1.4;
            font-size: 10px;
        }

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
            @if($entreprise->image)
                        <img src="{{ storage_path('app/public/' . $entreprise->image) }}" alt="Company Logo" class="company-logo">
                    @endif
            </div>
            <div class="details-right">
                <div class="header-info">
                    <h2>{{ __('messages.Credit Note Table Reports') }}</h2>
                    <h4>{{ __('messages.Number of Credit Notes') }}: {{ $creditNote->count() }}</h4>
                </div>
            </div>
        </div>

        @php 
            $totalCost = 0;
            $totaltva = 0;
        @endphp

        <table>
            <thead>
                <tr>
                    <th style="width: 12%;">{{ __('messages.Credit Note N') }}</th>
                    <th style="width: 12%;">{{ __('messages.Invoice N') }}</th>
                    <th style="width: 15%;">{{ __('messages.Project Name') }}</th>
                    <th style="width: 6%;">{{ __('messages.VAT') }}</th>
                    <th style="width: 15%;">{{ __('messages.Client Name') }}</th>
                    <th style="width: 10%;">{{ __('messages.Start Date') }}</th>
                    <th style="width: 10%;">{{ __('messages.End Date') }}</th>
                    <th style="width: 8%;">{{ __('messages.Type') }}</th>
                    <th style="width: 12%;">{{ __('messages.Total Cost') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($creditNote as $cn)
                <tr>
                    <td>
                        {{$entreprise->credit_note_prefix}}-{{date('ym')}}{{$cn->number}}
                    </td>
                    <td>
                        @php
                            $invoice = App\Models\invoices_history::where('id', $cn->invoice_id)->first();
                        @endphp
                        {{ $invoice->invoice_reference ?? (($entreprise->invoice_prefix ?? '').'-'.date('ym').$invoice->number) }}
                    </td>
                    <td>
                        @php 
                        $project = App\Models\Project::find($cn->project_id);
                        @endphp
                        {{$project->name}}
                    </td>
                    <td>
                        {{$project->tva}}%
                    </td>
                    <td>
                        @php 
                        $client = App\Models\Client::find($cn->client_id);
                        @endphp
                        {{$client->name}}
                    </td>
                    <td>{{$cn->start_date}}</td>
                    <td>{{$cn->end_date}}</td>
                    <td>{{$cn->credit_note_type}}</td>
                    <td>
                        -{{number_format($cn->total_ttc, 2, ',', '')}}€
                        @php
                            $totalCost += $cn->total_ttc;
                            $totaltva += $cn->total_tva;
                        @endphp
                    </td> 
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Summary Section using consistent styling --}}
        <div class="totals-summary">
            <div class="summary-row">
                <div class="summary-item">
                    <strong>{{ __('messages.Total TVA') }}:</strong> <span>-{{ number_format($totaltva, 3, ',', '') }}€</span>
                </div>
                <div class="summary-item">
                    <strong>{{ __('messages.Total Cost') }}:</strong> <span>-{{ number_format($totalCost, 3, ',', '') }}€</span>
                </div>
            </div>
        </div>

    </div>

    <div class="footer">
        <div class="footer-content">
            <div class="footer-left footer-text">
                @if(Auth::user()->entreprise)
                    <b>{{ Auth::user()->entreprise->name }}</b><br>
                    <b>{{ __('messages.VAT Number') }}:</b> {{ Auth::user()->entreprise->tva}}
                @endif
            </div>
            <div class="footer-center footer-text">
                @if(Auth::user()->entreprise)
                    {{ Auth::user()->entreprise->Address }}<br>
                    {{ __('messages.Phone') }}: 0{{ Auth::user()->entreprise->phone }}
                @endif
            </div>
            <div class="footer-right footer-text">
                @if(Auth::user()->entreprise)
                    <b>{{ __('messages.BANK') }}</b> : {{ Auth::user()->entreprise->bank_name }}<br>
                    <b>{{ __('messages.IBAN') }}</b> : {{ Auth::user()->entreprise->iban }}<br>
                    <b>{{ __('messages.SWIFT/BIC') }}</b> : {{ Auth::user()->entreprise->swift }} / {{ Auth::user()->entreprise->bic }}
                @endif
                <br>
                <span style="font-size: 9px; color: #666;">{{ __('messages.Printed') }}: {{ date('d-m-Y') }}</span>
            </div>
        </div>
    </div>

</body>

</html>
