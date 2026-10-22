@extends('auth.layouts.recovery')
@section('page-title', __('messages.auth_fp_meta_title'))
@section('page-description', __('messages.auth_fp_meta_desc'))
@section('recovery-content')
    @php
        $linkSent = !$errors->any()
            && session('status') === __(\Illuminate\Support\Facades\Password::RESET_LINK_SENT);
    @endphp
    @if($linkSent)
        <div role="status" aria-live="polite" class="py-2 flex flex-col items-center ">
            <div class="mb-6 inline-flex h-16 w-16 items-center justify-center rounded-[20px] border border-[#dfe9e7] bg-[#edf7f5] text-[#2D7080]">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" class="h-8 w-8" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="3"/><path d="m3 7 9 6 9-6"/></svg>
            </div>
            <h1 class="text-[1.6rem] font-semibold leading-[1.1] tracking-[-0.05em] text-[#0B2638]">{{ __('messages.auth_fp_sent_title') }}</h1>
            <p class="mt-4 text-[0.95rem] leading-6 text-[#5b6b73]">{{ session('status') }}</p>
            <p class="mt-4 rounded-2xl border border-[#dfe9e7] bg-[#f7faf9] p-4 text-sm text-center leading-6 text-[#5b6b73]">{{ __('messages.auth_fp_sent_help') }}</p>
        </div>
        <a href="{{ route('login') }}" class="btn-sweep btn-sweep-navy mt-7 inline-flex min-h-[52px] w-full items-center justify-center gap-2.5 rounded-full bg-brand-secondary px-[22px] text-[0.95rem] font-semibold text-white no-underline shadow-[0_12px_28px_rgba(45,112,128,0.2)] transition-all duration-300 hover:-translate-y-0.5">{{ __('messages.Back to sign in') }} <i data-lucide="arrow-right" class="h-4 w-4" aria-hidden="true"></i></a>
    @else
        <div class="mb-7 text-left">
            <h1 class="text-[1.7rem] font-semibold leading-[1.1] tracking-[-0.05em] text-[#0B2638]">{{ __('messages.Forgot password?') }}</h1>
            <p class="mt-2 text-[0.95rem] leading-6 text-[#5b6b73]">{{ __('messages.auth_fp_intro') }}</p>
        </div>
        @if(session('error'))<div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">{{ session('error') }}</div>@endif
        @foreach($errors->getMessages() as $field => $messages)
            @if($field !== 'email')
                @foreach($messages as $message)<p class="mb-4 text-sm text-red-700" role="alert">{{ $message }}</p>@endforeach
            @endif
        @endforeach
        <form id="forgotPasswordForm" method="POST" action="{{ route('password.email') }}" class="space-y-5">
            @csrf
            <div style="display: none;">
                <label for="website">{{ __('messages.Website') }}</label>
                <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
            </div>
<div class="space-y-2">
    <label for="email" class="block text-sm font-medium text-[#0B2638]">{{ __('messages.Email') }}</label>
    <div class="relative">
        <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-[#5b6b73]"><i data-lucide="mail" class="h-4 w-4" aria-hidden="true"></i></span>
        <input id="email" type="email" name="email" required value="{{ old('email') }}" autofocus autocomplete="email" placeholder="you@company.com" @error('email') aria-invalid="true" aria-describedby="email-error" @enderror class="w-full rounded-full border border-[#dfe9e7] bg-[#f7faf9] py-3 pl-10 pr-4 text-[0.95rem] text-[#0B2638] outline-none transition-all duration-300 placeholder:text-[#6a7c84] focus:border-[#2D7080] focus:bg-white focus:ring-4 focus:ring-[#2D7080]/10">
    </div>
    @error('email')<p id="email-error" role="alert" class="text-xs text-red-600">{{ $message }}</p>@enderror
</div>
<button type="submit" class="btn-sweep btn-sweep-navy group inline-flex min-h-[52px] w-full items-center justify-center gap-2.5 rounded-full border border-transparent bg-brand-secondary px-[22px] text-[0.95rem] font-semibold text-white shadow-[0_12px_28px_rgba(45,112,128,0.2)] transition-all duration-300 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-[0_16px_34px_rgba(45,112,128,0.3)] cursor-pointer"><span>{{ __('messages.auth_fp_submit') }}</span><i data-lucide="arrow-right" class="h-4 w-4" aria-hidden="true"></i></button>
        </form>
<p class="mt-7 text-center text-sm text-[#5b6b73]">{{ __('messages.Remember your password?') }} <a href="{{ route('login') }}" class="font-medium text-[#2D7080] no-underline hover:text-[#0B2638] hover:underline">{{ __('messages.Back to sign in') }}</a></p>
    @endif
@endsection
