@php($appName = 'DalyTrac')
<x-email.layout :title="__('emails.reset_title')" :app-name="$appName">
    <x-email.text>{{ __('emails.hello') }}{{ isset($userName) ? ' ' . $userName : '' }},</x-email.text>
    <x-email.text>{{ __('emails.reset_intro', ['app' => $appName]) }}</x-email.text>
    <x-email.button :href="$resetUrl">{{ __('emails.reset_cta') }}</x-email.button>
    <x-email.panel :warning="true">{{ __('emails.reset_expiry', ['minutes' => $expireMinutes]) }}</x-email.panel>
    <x-email.text>{{ __('emails.reset_ignore') }}</x-email.text>
    <x-slot:footer>
        <p style="margin:0 0 20px;">{{ __('emails.reset_fallback') }}<br>
            <a href="{{ $resetUrl }}" target="_blank" rel="noopener" style="color:#2D7080;word-break:break-all;overflow-wrap:anywhere;">{{ $resetUrl }}</a>
        </p>
    </x-slot:footer>
</x-email.layout>
