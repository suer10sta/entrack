@props(['href' => '/'])
<div class="dt-sidebar-brand sidebar-logo-wrap px-4 flex items-center">
    <a href="{{ $href }}" aria-label="DalyTrac, accueil" class="flex items-center gap-2.5 min-w-0 flex-1">
        <x-application-mark />
        <x-application-logo class="sidebar-logo-text inline-flex items-baseline text-[1.35rem] font-extrabold tracking-[-0.04em] text-white no-underline leading-none" />
    </a>
</div>
