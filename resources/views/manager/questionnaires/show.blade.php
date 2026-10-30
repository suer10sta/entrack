@extends('manager.layouts.app')
@section('content')
<link rel="stylesheet" href="{{ asset('css/manager-questionnaires.css') }}">
@php($recordedResponses = collect($responses)->filter(fn ($answer) => $answer['value'] !== null && $answer['value'] !== ''))
<div class="mq-page"><a class="mq-back" href="{{ route('manager.questionnaires.index',['type'=>$survey->survey_type->value]) }}">← {{ __('manager-surveys.back') }}</a><header class="mq-header"><div><p class="mq-eyebrow">DalyTrac · {{ __('manager-surveys.title') }}</p><h1>{{ $reader->name($survey->survey_type) }} — {{ $reader->label($reader->definition($survey->survey_type, $survey->survey_version)['title_key']) }}</h1>@if(filled($survey->entreprise?->name))<p class="mq-company-name">{{ $survey->entreprise->name }}</p>@endif<p>{{ $survey->email }}</p>@if($recordedResponses->isEmpty())<p class="mq-no-response">{{ __('manager-surveys.no_response') }}</p>@endif</div><span class="mq-status mq-status-{{ $survey->status->value }}">{{ __('manager-surveys.statuses.'.$survey->status->value) }}</span></header>
<section class="mq-card mq-email-preview">
    <h2>{{ __('manager-surveys.email_preview') }}</h2>
    <p class="mq-email-notice">{{ __('manager-surveys.email_notice') }}</p>
    @forelse($emails as $email)
        <article class="mq-email-message">
            <div class="mq-section-head">
                <h3>{{ __('manager-surveys.'.($email['reminder'] ? 'reminder_email' : 'initial_email')) }}</h3>
                <time>{{ $email['sent_at']->format('d/m/Y H:i') }}</time>
            </div>
            <dl class="mq-email-envelope">
                <div><dt>{{ __('manager-surveys.recipient') }}</dt><dd>{{ $survey->email }}</dd></div>
                <div><dt>{{ __('manager-surveys.subject') }}</dt><dd>{{ $email['subject'] }}</dd></div>
                <div><dt>{{ __('manager-surveys.email_language') }}</dt><dd>{{ strtoupper($email['locale']) }}</dd></div>
            </dl>
            <iframe class="mq-email-frame" title="{{ __('manager-surveys.email_preview') }} — {{ $email['subject'] }}" sandbox="" referrerpolicy="no-referrer" srcdoc="{{ $email['html'] }}"></iframe>
        </article>
    @empty
        <p>{{ __('manager-surveys.no_email') }}</p>
    @endforelse
</section>
<section class="mq-card mq-meta">@foreach(['sent_at'=>$survey->sent_at,'answered_at'=>($survey->completed_at ?? $survey->clicked_at),'recall'=>$survey->recall_due_at] as $key=>$date)<div><span>{{ __('manager-surveys.'.$key) }}</span><strong>{{ $date?->format('d/m/Y H:i') ?? '—' }}</strong></div>@endforeach<div><span>{{ __('manager-surveys.user') }}</span><strong>{{ $survey->user?->name ?? '—' }}</strong></div><div><span>{{ __('manager-surveys.contact') }}</span>@if($survey->contact)<a class="mq-ticket" href="{{ route('manager.support.show',$survey->contact) }}">Ticket #{{ $survey->contact->ticket_num }}</a>@else — @endif</div></section>
@if($recordedResponses->isNotEmpty())
<section class="mq-card">
    <h2>{{ __('manager-surveys.responses') }}</h2>
    <dl class="mq-detail-answers">
        @foreach($recordedResponses as $answer)
            <div><dt>{{ $answer['label'] }}</dt><dd>{{ $answer['value'] }}</dd></div>
        @endforeach
    </dl>
</section>
@endif
<section class="mq-card"><h2>{{ __('manager-surveys.history') }}</h2><ol class="mq-events">@forelse($events as $event)<li><div><strong>{{ __('manager-surveys.actions.'.$event->action_type->value) }}</strong><time>{{ $event->triggered_at?->format('d/m/Y H:i') }}</time></div>@if($event->detail)<pre>{{ is_array($event->detail) ? json_encode($event->detail,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) : $event->detail }}</pre>@endif</li>@empty<li>{{ __('manager-surveys.no_events') }}</li>@endforelse</ol>@include('manager.questionnaires.pagination',['items'=>$events])</section></div>
@endsection
