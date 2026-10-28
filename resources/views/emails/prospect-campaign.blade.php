<!doctype html>
<html lang="fr">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="x-apple-disable-message-reformatting"><title>{{ $campaignSubject }}</title></head>
<body style="margin:0;padding:0;background-color:#f3f7f9;color:#0B2638;font-family:Arial,Helvetica,sans-serif;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%;">
@if($preheader)<div style="display:none;font-size:1px;line-height:1px;max-height:0;max-width:0;opacity:0;overflow:hidden;mso-hide:all">{{ $preheader }}</div>@endif
<table role="presentation" width="100%" border="0" cellpadding="0" cellspacing="0" style="width:100%;background-color:#f3f7f9;">
    <tr><td height="48" style="height:48px;font-size:1px;line-height:48px;">&nbsp;</td></tr>
    <tr><td align="center" style="padding:0 16px;">
        <!--[if mso]><table role="presentation" width="620" border="0" cellpadding="0" cellspacing="0"><tr><td><![endif]-->
        <table role="presentation" width="100%" border="0" cellpadding="0" cellspacing="0" style="width:100%;max-width:620px;background-color:#ffffff;border:1px solid #e2e9ee;border-radius:12px;">
            <tr><td style="padding:36px 32px 28px;font-size:28px;line-height:34px;">
                <x-application-logo :email="true" />
            </td></tr>
            <tr><td style="padding:0 32px 20px;font-family:Arial,Helvetica,sans-serif;">
                <p style="margin:0 0 18px;font-size:17px;line-height:26px;font-weight:600;color:#0B2638;">{{ $greetingName !== '' ? 'Bonjour '.$greetingName.',' : 'Bonjour,' }}</p>
                <div style="font-size:15px;line-height:26px;color:#3e5868;overflow-wrap:anywhere;word-break:break-word;">{!! $trackedContentHtml ?? nl2br(e($content)) !!}</div>
            </td></tr>
            @if($image)
                <tr><td style="padding:4px 32px 24px;">
                    <img src="{{ $message->embed(\Illuminate\Support\Facades\Storage::disk('local')->path($image['path'])) }}" width="554" alt="Illustration de la campagne" style="display:block;width:100%;max-width:554px;height:auto;border:0;border-radius:8px;">
                </td></tr>
            @endif
            @if($ctaText && $ctaUrl)
                <tr><td style="padding:4px 32px 28px;">
                    <table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td align="center" bgcolor="#2D7080" style="background-color:#2D7080;border-radius:8px;mso-padding-alt:13px 22px;">
                        <a href="{{ $ctaUrl }}" style="display:inline-block;border:1px solid #2D7080;border-radius:8px;padding:13px 22px;font-family:Arial,Helvetica,sans-serif;font-size:14px;line-height:20px;font-weight:bold;text-decoration:none;color:#ffffff;mso-padding-alt:0;">{{ $ctaText }}</a>
                    </td></tr></table>
                </td></tr>
            @endif
            @if($document)<tr><td style="padding:0 32px 24px;font-size:12px;line-height:20px;color:#7b8e9b;">Pièce jointe : {{ $document['name'] }}</td></tr>@endif
            <tr><td style="padding:4px 32px 30px;">
                <table role="presentation" width="100%" border="0" cellpadding="0" cellspacing="0"><tr><td style="border-top:1px solid #e7eef2;padding-top:24px;">
                    <p style="margin:0 0 6px;font-size:14px;line-height:22px;font-weight:bold;color:#0B2638;">L’équipe DalyTrac</p>
                    <p style="margin:0;font-size:12px;line-height:20px;color:#8a9ca8;">DalyTrac <span style="color:#E7B84B;">&bull;</span> Simplifiez votre quotidien professionnel.</p>
                </td></tr></table>
            </td></tr>
        </table>
        <!--[if mso]></td></tr></table><![endif]-->
    </td></tr>
    <tr><td height="48" style="height:48px;font-size:1px;line-height:48px;">&nbsp;</td></tr>
</table>
@if($pixelUrl)<img src="{{ $pixelUrl }}" width="1" height="1" alt="" style="display:block;width:1px;height:1px;border:0" aria-hidden="true">@endif
</body>
</html>
