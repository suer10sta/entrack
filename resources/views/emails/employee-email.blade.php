<x-email.layout :title="$subject ?? __('emails.employee_title')">
    <x-email.text>{!! nl2br(e($body)) !!}</x-email.text>
</x-email.layout>
