@props(['title', 'appName' => 'DalyTrac'])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="x-apple-disable-message-reformatting">
    <title>{{ $title }} | {{ $appName }}</title>
    <style>
        body, table, td, a { -webkit-text-size-adjust:100%; -ms-text-size-adjust:100%; }
        table, td { mso-table-lspace:0pt; mso-table-rspace:0pt; }
        img { border:0; outline:none; text-decoration:none; }
        .email-content a { color:#2D7080; }
        .email-content img { max-width:100%; height:auto; }
        @media only screen and (max-width:600px) {
            .email-outer { padding:16px 8px!important; }
            .email-padding { padding:24px 20px!important; }
            .email-title { font-size:24px!important; line-height:32px!important; }
            .email-button { box-sizing:border-box; max-width:100%; }
            .email-detail-label, .email-detail-value { display:block!important; width:auto!important; }
            .email-detail-label { border-bottom:0!important; padding-bottom:4px!important; }
            .email-detail-value { padding-top:0!important; }
        }
    </style>
    <!--[if mso]><style>body,table,td,a,h1,p{font-family:Arial,sans-serif!important;}table{border-collapse:collapse;}</style><![endif]-->
</head>
<body style="margin:0;padding:0;width:100%;background-color:#F3F6F8;color:#355460;font-family:Arial,Helvetica,sans-serif;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#F3F6F8" style="width:100%;border-collapse:collapse;">
    <tr><td class="email-outer" align="center" style="padding:40px 16px;">
        <!--[if mso]><table role="presentation" width="640" cellpadding="0" cellspacing="0" border="0"><tr><td><![endif]-->
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#FFFFFF" style="width:100%;max-width:640px;border:1px solid #DFE7EB;border-radius:16px;border-spacing:0;background-color:#FFFFFF;">
            <tr><td height="5" bgcolor="#2D7080" style="height:5px;line-height:5px;font-size:1px;border-radius:16px 16px 0 0;">&nbsp;</td></tr>
            <tr><td class="email-padding" style="padding:28px 36px;border-bottom:1px solid #E4ECEF;background-color:#F8FAFB;">
                <x-application-logo :email="true" style="font-size:28px;" />
            </td></tr>
            <tr><td class="email-padding email-content" style="padding:32px 36px;color:#355460;font-family:Arial,Helvetica,sans-serif;font-size:15px;line-height:25px;overflow-wrap:anywhere;word-wrap:break-word;">
                <h1 class="email-title" style="margin:0 0 24px;color:#0B2638;font-size:28px;line-height:36px;font-weight:700;">{{ $title }}</h1>
                {{ $slot }}
            </td></tr>
            <tr><td class="email-padding" style="padding:24px 36px;border-top:1px solid #E4ECEF;background-color:#F8FAFB;border-radius:0 0 16px 16px;color:#617985;font-family:Arial,Helvetica,sans-serif;font-size:12px;line-height:20px;">
                @isset($footer)
                    {{ $footer }}
                @endisset
                <p style="margin:0 0 8px;">{{ __('emails.regards') }}<br><strong style="color:#0B2638;">{{ __('emails.team', ['app' => $appName]) }}</strong></p>
                <p style="margin:0;">&copy; {{ date('Y') }} {{ $appName }}. {{ __('messages.All rights reserved.') }}</p>
            </td></tr>
        </table>
        <!--[if mso]></td></tr></table><![endif]-->
    </td></tr>
</table>
</body>
</html>
