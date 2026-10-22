@extends('auth.layouts.recovery')
@section('page-title', __('messages.DalyTrac Reset Password - Secure your account'))
@section('page-description', __('messages.Choose a strong password to secure your DalyTrac account.'))
@section('recovery-content')
    <div class="mb-7 text-left">
        <h1 class="text-[2rem] font-bold leading-[1.1] tracking-[-0.05em] text-[#0B2638]">{{ __('messages.Create new password') }}</h1>
        <p class="mt-2 text-[0.95rem] leading-6 text-[#5b6b73]">{{ __('messages.Choose a strong password to secure your DalyTrac account.') }}</p>
    </div>
    @if(session('status'))<div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700" role="status">{{ session('status') }}</div>@endif
    <form id="resetPasswordForm" method="POST" action="{{ route('password.store') }}" class="space-y-5">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">
<div class="space-y-2">
    <label for="email" class="block text-sm font-medium text-[#0B2638]">{{ __('messages.Email') }}</label>
    <div class="relative">
        <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-[#5b6b73]"><i data-lucide="mail" class="h-4 w-4" aria-hidden="true"></i></span>
        <input id="email" type="email" name="email" required value="{{ old('email', $request->email) }}" autofocus autocomplete="username" @error('email') aria-invalid="true" aria-describedby="email-error" @enderror class="w-full rounded-full border border-[#dfe9e7] bg-[#f7faf9] py-3 pl-10 pr-4 text-[0.95rem] text-[#0B2638] outline-none transition-all duration-300 placeholder:text-[#6a7c84] focus:border-[#2D7080] focus:bg-white focus:ring-4 focus:ring-[#2D7080]/10">
    </div>
    @error('email')<p id="email-error" role="alert" class="text-xs text-red-600">{{ $message }}</p>@enderror
</div>
<div class="space-y-2">
    <label for="password" class="block text-sm font-medium text-[#0B2638]">{{ __('messages.New password') }}</label>
    <div class="relative">
        <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-[#5b6b73]"><i data-lucide="lock-keyhole" class="h-4 w-4" aria-hidden="true"></i></span>
        <input id="password" type="password" name="password" required autocomplete="new-password" @error('password') aria-invalid="true" aria-describedby="password-error" @enderror class="w-full rounded-full border border-[#dfe9e7] bg-[#f7faf9] py-3 pl-10 pr-4 text-[0.95rem] text-[#0B2638] outline-none transition-all duration-300 placeholder:text-[#6a7c84] focus:border-[#2D7080] focus:bg-white focus:ring-4 focus:ring-[#2D7080]/10">
    </div>
    @error('password')<p id="password-error" role="alert" class="text-xs text-red-600">{{ $message }}</p>@enderror
</div>
<div class="space-y-2">
    <label for="password_confirmation" class="block text-sm font-medium text-[#0B2638]">{{ __('messages.Confirm password') }}</label>
    <div class="relative">
        <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-[#5b6b73]"><i data-lucide="lock-keyhole" class="h-4 w-4" aria-hidden="true"></i></span>
        <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" @error('password_confirmation') aria-invalid="true" aria-describedby="password_confirmation-error" @enderror class="w-full rounded-full border border-[#dfe9e7] bg-[#f7faf9] py-3 pl-10 pr-4 text-[0.95rem] text-[#0B2638] outline-none transition-all duration-300 placeholder:text-[#6a7c84] focus:border-[#2D7080] focus:bg-white focus:ring-4 focus:ring-[#2D7080]/10">
    </div>
    @error('password_confirmation')<p id="password_confirmation-error" role="alert" class="text-xs text-red-600">{{ $message }}</p>@enderror
</div>
<button type="submit" class="btn-sweep btn-sweep-navy group inline-flex min-h-[52px] w-full items-center justify-center gap-2.5 rounded-full border border-transparent bg-brand-secondary px-[22px] text-[0.95rem] font-semibold text-white shadow-[0_12px_28px_rgba(45,112,128,0.2)] transition-all duration-300 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-[0_16px_34px_rgba(45,112,128,0.3)] cursor-pointer"><span>{{ __('messages.Reset password') }}</span><i data-lucide="arrow-right" class="h-4 w-4" aria-hidden="true"></i></button>
    </form>
<p class="mt-7 text-center text-sm text-[#5b6b73]">{{ __('messages.Remember your password?') }} <a href="{{ route('login') }}" class="font-medium text-[#2D7080] no-underline hover:text-[#0B2638] hover:underline">{{ __('messages.Back to sign in') }}</a></p>
@endsection
