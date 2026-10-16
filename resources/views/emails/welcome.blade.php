@php($appName = 'DalyTrac')
<x-email.layout :title="__('emails.welcome_title', ['name' => $userName])" :app-name="$appName">
    <x-email.text>{{ __('emails.welcome_account', ['company' => $entrepriseName, 'app' => $appName]) }}</x-email.text>
    <x-email.panel>
        <strong style="color:#0B2638;">{{ __('emails.welcome_trial', ['days' => $trialDays]) }}</strong><br>
        {{ __('emails.welcome_commitment') }}
    </x-email.panel>
    <x-email.text>{{ __('emails.welcome_next') }}</x-email.text>
    <ul style="margin:0 0 24px;padding-left:22px;color:#355460;font-size:15px;line-height:28px;">
        <li>{{ __('emails.welcome_people') }}</li>
        <li>{{ __('emails.welcome_days') }}</li>
        <li>{{ __('emails.welcome_documents') }}</li>
        <li>{{ __('emails.welcome_calendar') }}</li>
    </ul>
    <x-email.button :href="url('/dashboard')">{{ __('emails.dashboard_cta') }}</x-email.button>
</x-email.layout>
