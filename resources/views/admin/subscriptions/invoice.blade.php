<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <x-favicon />
    <meta charset="UTF-8">
    <title>Facture abonnement {{ $invoiceNumber }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #333; }
        .header { margin-bottom: 20px; }
        .header h1 { margin: 0 0 5px 0; font-size: 20px; }
        .header p { margin: 0; }
        .section { margin-bottom: 20px; }
        .section-title { font-weight: bold; margin-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        th { background-color: #f5f5f5; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $appName }} - Facture d’abonnement</h1>
        <p>Facture n° {{ $invoiceNumber }}</p>
        <p>Date : {{ $createdAt->format('d/m/Y') }}</p>
    </div>

    <div class="section">
        <div class="section-title">{{ __('messages.Client') }}</div>
        <p>
            {{ $entreprise->name }}<br>
            {{ $entreprise->Address }}<br>
            {{ $customerCountry }}<br>
            {{ $customerZipCode ?? '' }}<br>
            @if(!empty($customerVat))
                TVA : {{ $customerVat }}
            @endif
        </p>
    </div>

    <div class="section">
        <div class="section-title">{{ __('messages.Détail de l’abonnement') }}</div>
        @if($periodStart && $periodEnd)
            <p>Période : du {{ $periodStart->format('d/m/Y') }} au {{ $periodEnd->format('d/m/Y') }}</p>
        @endif
        <table>
            <thead>
                <tr>
                    <th>{{ __('messages.Description column') }}</th>
                    <th class="text-right">Montant HT ({{ strtoupper($currency) }})</th>
                    <th class="text-right">TVA @if($vatRate > 0) ({{ number_format($vatRate, 2, ',', ' ') }} %) @endif</th>
                    <th class="text-right">Montant TTC ({{ strtoupper($currency) }})</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Abonnement {{ ucfirst($entreprise->type_compt ?? '') }}</td>
                    <td class="text-right">{{ number_format($amountHt, 2, ',', ' ') }}</td>
                    <td class="text-right">
                        @if($isZeroVat)
                            0,00 (TVA 0 % - client éligible à taux zéro)
                        @else
                            {{ number_format($amountVat, 2, ',', ' ') }}
                        @endif
                    </td>
                    <td class="text-right">{{ number_format($amountTtc, 2, ',', ' ') }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="section">
        <div class="section-title">{{ __('messages.Total column') }}</div>
        <table>
            <tbody>
                <tr>
                    <td>{{ __('messages.Total HT') }}</td>
                    <td class="text-right">{{ number_format($amountHt, 2, ',', ' ') }} {{ strtoupper($currency) }}</td>
                </tr>
                <tr>
                    <td>{{ __('messages.TVA') }}</td>
                    <td class="text-right">
                        @if($isZeroVat)
                            0,00 {{ strtoupper($currency) }}
                        @else
                            {{ number_format($amountVat, 2, ',', ' ') }} {{ strtoupper($currency) }}
                        @endif
                    </td>
                </tr>
                <tr>
                    <td><strong>{{ __('messages.Total Incl VAT') }}</strong></td>
                    <td class="text-right"><strong>{{ number_format($amountTtc, 2, ',', ' ') }} {{ strtoupper($currency) }}</strong></td>
                </tr>
            </tbody>
        </table>
    </div>
</body>
</html>


