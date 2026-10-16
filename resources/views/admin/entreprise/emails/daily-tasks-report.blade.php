<x-email.layout :title="__('emails.daily_title')">
    <x-email.text>{{ __('emails.hello') }},</x-email.text>
    <x-email.text>{{ __('emails.daily_intro', ['company' => $enterprise->name, 'date' => \Carbon\Carbon::now()->format('d-m-Y')]) }}</x-email.text>
    <x-email.text>{{ __('emails.daily_include') }}</x-email.text>
    <x-email.panel>
        <ul style="margin:0;padding-left:20px;line-height:28px;">
            <li>{{ __('emails.report_tasks') }}</li>
            <li>{{ __('emails.report_projects') }}</li>
            <li>{{ __('emails.report_employees') }}</li>
            <li>{{ __('emails.report_clients') }}</li>
            <li>{{ __('emails.report_pointage') }}</li>
            <li>{{ __('emails.report_quotes') }}</li>
            <li>{{ __('emails.report_invoices') }}</li>
        </ul>
    </x-email.panel>
</x-email.layout>
