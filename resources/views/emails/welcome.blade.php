@php($appName = 'DalyTrac')
<x-email.layout :title="__('emails.welcome_title', ['name' => $userName])" :app-name="$appName">
    <x-email.text>{{ filled($entrepriseName ?? null)
        ? __('emails.welcome_account', ['company' => $entrepriseName, 'app' => $appName])
        : __('emails.welcome_account_ready', ['app' => $appName]) }}</x-email.text>
    <x-email.panel>
        <strong style="color:#0B2638;">{{ __('emails.welcome_workflow') }}</strong>
    </x-email.panel>
    <x-email.text>{{ __('emails.welcome_next') }}</x-email.text>
    <ul style="margin:0 0 24px;padding-left:22px;color:#355460;font-size:15px;line-height:28px;">
        <li><strong>{{ __('emails.welcome_projects_title') }}</strong> — {{ __('emails.welcome_projects_text') }}</li>
        <li><strong>{{ __('emails.welcome_time_title') }}</strong> — {{ __('emails.welcome_time_text') }}</li>
        <li><strong>{{ __('emails.welcome_invoices_title') }}</strong> — {{ __('emails.welcome_invoices_text') }}</li>
    </ul>
    <x-email.button :href="url('/dashboard')">{{ __('emails.dashboard_cta') }}</x-email.button>
</x-email.layout>
