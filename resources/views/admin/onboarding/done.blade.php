@extends('layouts.onboarding')

@section('title', __('messages.onboarding_done_title') . ' — DalyTrac')

@section('content')
<div class="max-w-xl mx-auto text-center relative">
    <div class="pointer-events-none absolute -top-8 left-1/2 -translate-x-1/2 w-[380px] h-[200px] rounded-full bg-emerald-500/[0.07] blur-2xl" aria-hidden="true"></div>
    <div class="relative bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8">
        <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        </div>
        <h1 class="text-[22px] sm:text-[24px] font-bold tracking-tight text-[#0B2638]">
            {{ __('messages.onboarding_done_title') }}
        </h1>
        <p class="mt-2 text-[13px] text-slate-500 max-w-md mx-auto">
            {{ __('messages.onboarding_done_desc') }}
        </p>
        <a href="{{ route('admin.index') }}" class="mt-6 inline-flex items-center justify-center h-10 px-6 rounded-xl bg-[#0B2638] text-white text-[13.5px] font-semibold hover:bg-[#14364d] transition-colors no-underline">
            {{ __('messages.onboarding_done_cta') }}
        </a>
    </div>
</div>
@endsection
