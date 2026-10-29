<!doctype html>
<html lang="{{ $surveyLocale }}">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
<body style="margin:0;background:#f3f6f8;color:#0b2638;font-family:Arial,sans-serif;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr><td align="center" style="padding:32px 16px;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;background:#fff;border-radius:16px;">
<tr><td style="padding:32px;">
    <x-application-logo email style="font-size:28px" />
    <h1 style="font-size:24px;line-height:1.3;margin:28px 0 14px;">{{ __('surveys.ui.title') }}</h1>
    <p style="line-height:1.7;">{{ __('surveys.ui.'.($reminder ? 'email_reminder' : 'email_intro')) }}</p>
    <h2 style="font-size:18px;line-height:1.5;margin-top:24px;">{{ $question }}</h2>
    @foreach($choices as $choice)
        <p style="margin:12px 0;"><a href="{{ $choice['url'] }}" style="display:block;padding:14px 18px;border:1px solid #cbdde2;border-radius:8px;color:#245d6b;text-decoration:none;font-weight:bold;">{{ $choice['label'] }}</a></p>
    @endforeach
    <p style="font-size:13px;line-height:1.6;color:#526976;">{{ __('surveys.ui.email_safety') }}</p>
    <p style="font-size:12px;line-height:1.6;color:#526976;">{{ __('surveys.ui.email_expiry') }}</p>
</td></tr></table></td></tr></table>
</body></html>
