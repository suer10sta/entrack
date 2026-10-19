{{-- Canonical DalyTrac wordmark: fixed original brand colors on every surface. --}}
@props(['email' => false])
@php($logoDisplayStyle = $email ? 'display:inline-block;white-space:nowrap;font-family:Figtree,sans-serif;font-weight:800;letter-spacing:-0.065em;line-height:1.15;' : 'display:inline-flex;align-items:baseline;white-space:nowrap;font-family:Figtree,sans-serif;font-weight:800;letter-spacing:-0.065em;line-height:1.15;')
<span {{ $attributes->merge(['class' => 'dt-logo', 'style' => $logoDisplayStyle]) }} role="img" aria-label="DalyTrac"><span style="color:#0B2638">Daly</span><strong style="color:#2D7080;font-weight:800">Trac</strong></span>
