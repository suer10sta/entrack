@props(['label'])
<tr>
    <th class="email-detail-label" width="34%" scope="row" align="left" valign="top" style="width:34%;padding:12px 12px 12px 0;border-bottom:1px solid #E4ECEF;color:#617985;font-family:Arial,Helvetica,sans-serif;font-size:13px;line-height:22px;font-weight:400;word-wrap:break-word;">{{ $label }}</th>
    <td class="email-detail-value" valign="top" style="padding:12px 0;border-bottom:1px solid #E4ECEF;color:#0B2638;font-family:Arial,Helvetica,sans-serif;font-size:14px;line-height:22px;word-wrap:break-word;overflow-wrap:anywhere;">{{ $slot }}</td>
</tr>
