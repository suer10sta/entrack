@php
    $street = trim((string) ($client->billing_street ?: $client->address));
    $locality = trim(implode(' ', array_filter([
        $client->billing_zip ?: $client->zip,
        $client->billing_city ?: $client->city,
    ], fn ($value) => filled($value))));
    $country = trim((string) ($client->billing_country ?: $client->country));
    $clientVat = trim((string) $client->TVA);
@endphp
<strong>{{ $client->name }}</strong><br>
@if($street !== ''){{ $street }}<br>@endif
@if($locality !== ''){{ $locality }}<br>@endif
@if($country !== ''){{ $country }}<br>@endif
@if($clientVat !== '' && $clientVat !== '0'){{ __('messages.Client VAT') }}: {{ $clientVat }}@endif
