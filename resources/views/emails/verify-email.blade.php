@php
    $appName = config('app.name', 'DalyTrac');
    $locale = app()->getLocale();
@endphp
<x-email.layout :title="__('messages.Verify Your Email')" :app-name="$appName">
    <x-email.panel>{{ __('messages.Email Verification Required') }}</x-email.panel>
    <x-email.text>
        {{ __('messages.Hello') }} <strong>{{ $userName }}</strong>,<br>
        {{ __('messages.Thank you for registering on :app', ['app' => $appName]) }}.<br>
        {{ __('messages.Click the button below to activate your account.') }}
    </x-email.text>
    <x-email.button :href="$verificationUrl">{{ __('messages.Verify my email') }}</x-email.button>
    <x-email.panel :warning="true">
        {{ __('messages.This link expires in :time', ['time' => __('emails.minutes', ['count' => 60])]) }}.
        {{ __('messages.If you did not create an account, ignore this email.') }}
    </x-email.panel>
    <x-slot:footer>
        <p style="margin:0 0 20px;">{{ __('messages.If the button does not work, copy this link:') }}<br>
            <a href="{{ $verificationUrl }}" style="color:#2D7080;word-break:break-all;overflow-wrap:anywhere;">{{ $verificationUrl }}</a>
        </p>
    </x-slot:footer>
</x-email.layout>
