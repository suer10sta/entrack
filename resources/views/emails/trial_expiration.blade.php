@php($appName = 'DalyTrac')
<x-email.layout :title="__('emails.trial_title')" :app-name="$appName">
    <x-email.text>{{ __('emails.hello') }} {{ $userName }},</x-email.text>
    <x-email.text>{{ __('emails.trial_intro', ['company' => $entrepriseName]) }}</x-email.text>
    <x-email.panel :warning="true">
        <strong style="color:#0B2638;">{{ __('emails.trial_time', ['days' => $daysRemaining, 'unit' => $daysRemaining > 1 ? __('emails.days') : __('emails.day'), 'date' => $trialEndsAt]) }}</strong>
    </x-email.panel>
    <x-email.text>{{ __('emails.trial_continue', ['app' => $appName]) }}</x-email.text>
    <ul style="margin:0 0 24px;padding-left:22px;color:#355460;font-size:15px;line-height:28px;">
        <li>{{ __('emails.trial_features') }}</li>
        <li>{{ __('emails.trial_users') }}</li>
        <li>{{ __('emails.trial_support') }}</li>
    </ul>
    <x-email.button :href="$upgradeUrl">{{ __('emails.trial_cta') }}</x-email.button>
    <x-email.text>{{ __('emails.trial_limit') }}</x-email.text>
</x-email.layout>
