@props(['href' => '/'])
<div class="dt-sidebar-brand sidebar-logo-wrap px-4 flex items-center">
    <a href="{{ $href }}" aria-label="DalyTrac, accueil" class="flex items-center gap-2.5 min-w-0 flex-1">
        <span class="flex-shrink-0 w-9 h-9 rounded-xl bg-[#0B2638] flex items-center justify-center text-[#2D7080] font-extrabold text-[20px] tracking-tight">D</span>
        <x-application-logo class="sidebar-logo-text inline-flex items-baseline text-[1.35rem] font-extrabold tracking-[-0.04em] text-white no-underline leading-none" />
    </a>
</div>
