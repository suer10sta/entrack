{{-- Compact logo: the dashboard Sidebar is the canonical design.
     Size scales the complete mark proportionally for miniature previews. --}}
@props(['size' => 36])
<span {{ $attributes->merge(['class' => 'dt-application-mark', 'style' => 'display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;width:'.$size.'px;height:'.$size.'px;border-radius:'.($size / 3).'px;background:#0B2638;color:#2D7080;font-family:Figtree,sans-serif;font-size:'.($size * 20 / 36).'px;font-weight:800;letter-spacing:-0.025em;line-height:1;']) }} role="img" aria-label="DalyTrac">D</span>
