<!doctype html>
<html lang="{{ app()->getLocale() }}"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
<body style="margin:0;background:#f3f7f9;color:#0b2638;font-family:Arial,sans-serif;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr><td align="center" style="padding:32px 16px;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;background:#fff;border:1px solid #dfe9ee;border-radius:14px;"><tr><td style="padding:32px;">
<x-application-logo email style="font-size:28px" />
<h1 style="font-size:24px;line-height:1.4;margin:28px 0 16px;">{{ __('d1-actions.subject') }}</h1>
<p style="font-size:15px;line-height:1.8;">{{ __('d1-actions.body') }}</p>
<p style="font-size:15px;line-height:1.8;">{{ __('d1-actions.stop') }}</p>
<p style="margin-top:24px;"><a href="{{ url('/') }}" style="color:#2d7080;">{{ __('d1-actions.visit') }}</a></p>
</td></tr></table></td></tr></table>
</body></html>
