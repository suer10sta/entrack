<x-email.layout :title="__('emails.project_title')">
    <x-email.text>{!! nl2br(e($body)) !!}</x-email.text>
    <x-slot:footer><p style="margin:0 0 20px;">{{ __('emails.automated') }}</p></x-slot:footer>
</x-email.layout>
