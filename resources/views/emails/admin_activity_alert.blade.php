@php
    $appName = 'DalyTrac';
    $emailTitle = $title ?? __('emails.activity_title');
@endphp
<x-email.layout :title="$emailTitle" :app-name="$appName">
    @if(!empty($body))
        <x-email.text>{{ $body }}</x-email.text>
    @endif
    @if(!empty($meta) && is_array($meta))
        <x-email.text><strong>{{ __('emails.details') }}</strong></x-email.text>
        <x-email.details>
            @foreach($meta as $key => $value)
                <x-email.detail :label="ucfirst(str_replace('_', ' ', $key))">{{ is_scalar($value) ? $value : json_encode($value) }}</x-email.detail>
            @endforeach
        </x-email.details>
    @endif
    <x-slot:footer><p style="margin:0 0 20px;">{{ __('emails.activity_footer', ['app' => $appName]) }}</p></x-slot:footer>
</x-email.layout>
