<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="robots" content="noindex,nofollow"><meta name="referrer" content="no-referrer">
    <title>{{ __(($surveyUi ?? 'surveys.ui').'.title') }} — DalyTrac</title>
    <link rel="stylesheet" href="{{ asset('css/survey-d1.css') }}">
</head>
<body>
<header class="survey-header"><a href="/" aria-label="DalyTrac"><x-application-logo style="font-size:30px" /></a><span>{{ __('surveys.'.($surveyType ?? 'D1').'.title') }}</span></header>
<main class="survey-shell">
    <div class="survey-intro"><span class="survey-eyebrow">{{ __(($surveyUi ?? 'surveys.ui').'.eyebrow') }}</span>
        <h1>{{ isset($finished) ? __(($surveyUi ?? 'surveys.ui').'.thank_you') : __(($surveyUi ?? 'surveys.ui').'.title') }}</h1>
        <p>{{ isset($finished) ? __(($surveyUi ?? 'surveys.ui').'.done') : (isset($failure) ? __(($surveyUi ?? 'surveys.ui').'.'.$failure) : __(($surveyUi ?? 'surveys.ui').'.intro')) }}</p>
    </div>
    @if(isset($failure) || isset($finished))
        <div class="survey-card survey-result"><span aria-hidden="true">{{ isset($finished) ? '✓' : '—' }}</span><p>{{ isset($finished) ? __(($surveyUi ?? 'surveys.ui').'.done') : __(($surveyUi ?? 'surveys.ui').'.'.$failure) }}</p><a class="survey-button" href="/">{{ __(($surveyUi ?? 'surveys.ui').'.back') }}</a></div>
    @else
        @if($formErrors)<div class="survey-alert" role="alert">@foreach($formErrors as $messages)@foreach($messages as $message)<p>{{ $message }}</p>@endforeach @endforeach</div>@endif
        @if($locked)<div class="survey-note" role="status">{{ __(($surveyUi ?? 'surveys.ui').'.confirmed') }} {{ __(($surveyUi ?? 'surveys.ui').'.q1_locked') }}</div>@endif
        <form method="POST" action="{{ $action }}" class="survey-form">
            @csrf
            @foreach($questions as $question)
                @php($id = $question['id'])
                <fieldset class="survey-card" data-question="{{ $id }}" @if(!in_array($id, $visible, true)) hidden @endif>
                    <legend>{{ $catalog->label($question['label_key'], app()->getLocale()) }} <small>{{ __(($surveyUi ?? 'surveys.ui').'.'.($question['required'] ? 'required_mark' : 'optional')) }}</small></legend>
                    @if($question['type'] === 'text')
                        <textarea name="answers[{{ $id }}]" id="{{ $id }}" maxlength="{{ $question['max_length'] }}" rows="4" aria-label="{{ $catalog->label($question['label_key'], app()->getLocale()) }}">{{ $values[$id] ?? '' }}</textarea><small>{{ __(($surveyUi ?? 'surveys.ui').'.character_limit') }}</small>
                    @elseif($id === 'q1')
                        <div class="survey-choices">
                        @foreach($question['choices'] as $choice)
                            <label><input type="radio" name="answers[q1]" value="{{ $choice['value'] }}" @checked(($values['q1'] ?? '') === $choice['value']) @disabled($locked) required><span>{{ $catalog->label($choice['label_key'], app()->getLocale()) }}</span></label>
                        @endforeach
                        </div>
                        @if($locked)<input type="hidden" name="answers[q1]" value="{{ $values['q1'] }}">@endif
                    @else
                        <select name="answers[{{ $id }}]" id="{{ $id }}" aria-label="{{ $catalog->label($question['label_key'], app()->getLocale()) }}">
                            <option value="">{{ __(($surveyUi ?? 'surveys.ui').'.choose') }}</option>
                            @foreach($question['choices'] as $choice)<option value="{{ $choice['value'] }}" @selected(($values[$id] ?? '') === $choice['value'])>{{ $catalog->label($choice['label_key'], app()->getLocale()) }}</option>@endforeach
                        </select>
                    @endif
                </fieldset>
            @endforeach
            <div class="survey-actions">
                @unless($locked)<button class="survey-button secondary" type="submit" name="intent" value="confirm_q1">{{ __(($surveyUi ?? 'surveys.ui').'.confirm') }}</button>@endunless
                <button class="survey-button" type="submit" name="intent" value="complete">{{ __(($surveyUi ?? 'surveys.ui').'.submit') }}</button>
            </div>
            <noscript><p>{{ __(($surveyUi ?? 'surveys.ui').'.no_script') }}</p><button class="survey-button secondary" type="submit" name="intent" value="preview">{{ __(($surveyUi ?? 'surveys.ui').'.preview') }}</button></noscript>
        </form>
        <script type="application/json" id="survey-questions">@json($questions)</script>
        <script src="{{ asset('js/survey-d1.js') }}" defer></script>
    @endif
    <footer>{{ __(($surveyUi ?? 'surveys.ui').'.privacy') }}</footer>
</main>
</body></html>
