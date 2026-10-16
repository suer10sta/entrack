<x-email.layout :title="$subject">
    <x-email.text>{!! nl2br(e($body)) !!}</x-email.text>
</x-email.layout>
