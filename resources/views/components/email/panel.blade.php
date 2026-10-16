@props(['warning' => false])
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="width:100%;border-spacing:0;margin:8px 0 24px;">
    <tr><td bgcolor="{{ $warning ? '#FFF8E7' : '#EFF6F6' }}" style="padding:20px;border-left:4px solid {{ $warning ? '#E7B84B' : '#2D7080' }};border-radius:0 8px 8px 0;color:#355460;font-family:Arial,Helvetica,sans-serif;font-size:14px;line-height:24px;word-wrap:break-word;">{{ $slot }}</td></tr>
</table>
