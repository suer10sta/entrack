@extends('layouts.onboarding')

@section('title', __('messages.onboarding_welcome_title', ['name' => $user->name ?? '']) . ' — DalyTrac')

@section('content')
<div class="text-center max-w-2xl mx-auto">
    {{-- Modern onboarding illustration (inline SVG, brand palette) --}}
    <svg class="mx-auto mb-5 h-28 w-auto sm:h-32" viewBox="0 0 320 140" fill="none" role="img" aria-hidden="true">
        <ellipse cx="160" cy="126" rx="110" ry="10" fill="#0B2638" opacity="0.06"/>
        <circle cx="52" cy="42" r="16" fill="#E7B84B" opacity="0.25"/>
        <circle cx="272" cy="34" r="10" fill="#2D7080" opacity="0.20"/>
        <rect x="118" y="34" width="84" height="58" rx="10" fill="#ffffff" stroke="#e2e8f0" stroke-width="2"/>
        <rect x="130" y="48" width="40" height="7" rx="3.5" fill="#2D7080" opacity="0.85"/>
        <rect x="130" y="61" width="60" height="6" rx="3" fill="#e2e8f0"/>
        <rect x="130" y="72" width="48" height="6" rx="3" fill="#e2e8f0"/>
        <circle cx="236" cy="34" r="20" fill="#0B2638"/>
        <path d="M229 34l5 5 9-10" stroke="#E7B84B" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"/>
        <rect x="66" y="66" width="40" height="40" rx="9" fill="#2D7080" opacity="0.12"/>
        <path d="M76 86l7 7 12-14" stroke="#2D7080" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
        <circle cx="116" cy="30" r="5" fill="#E7B84B"/>
        <circle cx="214" cy="98" r="6" fill="#2D7080" opacity="0.35"/>
        <circle cx="258" cy="86" r="4" fill="#E7B84B" opacity="0.7"/>
    </svg>
    <h1 class="text-[26px] sm:text-[32px] font-bold tracking-tight text-[#0B2638] leading-tight">
        {{ __('messages.onboarding_welcome_title', ['name' => $user->name ?? '']) }}
    </h1>
    <p class="mt-2.5 text-[13.5px] sm:text-[14.5px] text-slate-500 max-w-xl mx-auto leading-relaxed">
        {{ __('messages.onboarding_welcome_subtitle') }}
    </p>
    <h2 class="mt-8 text-[15px] sm:text-[16px] font-bold text-[#0B2638]">{{ __('messages.onboarding_how_to_start') }}</h2>
</div>

<div class="mt-5 grid grid-cols-1 md:grid-cols-2 gap-4 max-w-4xl mx-auto w-full">
    {{-- Option 1: guided --}}
    <div class="bg-white rounded-2xl border-2 border-[#2D7080]/30 shadow-[0_10px_36px_-12px_rgba(11,38,56,0.18)] p-6 sm:p-7 flex flex-col transition-shadow hover:shadow-[0_16px_44px_-12px_rgba(45,112,128,0.35)]">
        <div class="flex items-center gap-2 flex-wrap">
            <h3 class="text-[16px] font-bold text-[#0B2638]">{{ __('messages.onboarding_guided_title') }}</h3>
            <span class="text-[10px] font-bold uppercase tracking-wide text-[#2D7080] bg-[#2D7080]/10 px-2 py-0.5 rounded-full">{{ __('messages.onboarding_recommended') }}</span>
        </div>
        <div class="mt-3 flex items-center gap-2 text-[12px] font-semibold text-slate-500">
            <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-[#0B2638] text-white text-[10.5px] font-bold">2</span>
            {{ __('messages.onboarding_steps_note') }}
        </div>
        <p class="mt-2.5 text-[13px] text-slate-500 leading-relaxed flex-1">{{ __('messages.onboarding_guided_desc') }}</p>
        <a href="{{ route('admin.onboarding.step1') }}" class="mt-5 w-full inline-flex items-center justify-center gap-2 h-11 rounded-xl bg-[#0B2638] text-white text-[14px] font-semibold hover:bg-[#14364d] transition-colors no-underline">
            {{ __('messages.onboarding_guided_cta') }}
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 6l6 6-6 6M5 12h14"/></svg>
        </a>
    </div>
    {{-- Option 2: explore alone --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-7 flex flex-col">
        <h3 class="text-[16px] font-bold text-[#0B2638]">{{ __('messages.onboarding_self_title') }}</h3>
        <p class="mt-2.5 text-[13px] text-slate-500 leading-relaxed flex-1">{{ __('messages.onboarding_self_desc') }}</p>
        <form method="POST" action="{{ route('admin.onboarding.discover') }}" class="mt-5">
            @csrf
            <button type="submit" class="w-full inline-flex items-center justify-center h-11 rounded-xl bg-white border border-slate-300 text-[#0B2638] text-[14px] font-semibold hover:bg-slate-50 hover:border-slate-400 transition-colors">
                {{ __('messages.onboarding_self_cta') }}
            </button>
        </form>
    </div>
</div>
@endsection
