@php
    $entreprise = $entreprise ?? Auth::user()?->entreprise;
@endphp
@if($entreprise && $section === 'identity')
    @if(filled($entreprise->name))<b>{{ $entreprise->name }}</b><br>@endif
    @if(filled($entreprise->tva) && trim((string) $entreprise->tva) !== '0')
        <b>{{ __('messages.VAT Number') }}:</b> {{ $entreprise->tva }}<br>
    @endif
@elseif($entreprise && $section === 'contact')
    @if(filled($entreprise->Address)){{ $entreprise->Address }}<br>@endif
    @if(filled($entreprise->phone) && trim((string) $entreprise->phone) !== '0')
        {{ __('messages.Phone') }}: {{ $entreprise->phone }}<br>
    @endif
@elseif($entreprise && $section === 'bank')
    @if(filled($entreprise->bank_name)){{ __('messages.BANK') }}: {{ $entreprise->bank_name }}<br>@endif
    @if(filled($entreprise->iban)){{ __('messages.IBAN') }}: {{ $entreprise->iban }}<br>@endif
    @php
        $bankCode = implode(' / ', array_filter([$entreprise->swift, $entreprise->bic], fn ($value) => filled($value)));
    @endphp
    @if($bankCode !== ''){{ __('messages.SWIFT/BIC') }}: {{ $bankCode }}<br>@endif
@endif
