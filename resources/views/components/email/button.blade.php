@props(['href'])
<table role="presentation" cellpadding="0" cellspacing="0" border="0" style="border-spacing:0;margin:8px 0 24px;max-width:100%;">
    <tr><td align="center" bgcolor="#0B2638" style="background-color:#0B2638;border-radius:8px;mso-padding-alt:14px 22px;">
        <a class="email-button" href="{{ $href }}" target="_blank" rel="noopener" style="display:inline-block;padding:14px 22px;border:1px solid #0B2638;border-radius:8px;color:#FFFFFF!important;font-family:Arial,Helvetica,sans-serif;font-size:15px;line-height:22px;font-weight:700;text-align:center;text-decoration:none;white-space:normal;">{{ $slot }}</a>
    </td></tr>
</table>
