<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.Help & Support') }} - DalyTrac</title>
    @vite('resources/css/app.css')
</head>
<body class="dt-app-shell bg-[#f8fafc] text-slate-900 antialiased flex h-screen overflow-hidden">
    {{-- Sidebar --}}
    @if(Auth::user()->hasRole('Admin'))
        <x-dashboard.sidebar />
    @else
        <x-employees.sidebar />
    @endif

    <div class="flex flex-col flex-1 overflow-y-auto">
        {{-- Navbar --}}
        <x-dashboard.navbar />

        {{-- Main Content --}}
        <main class="flex-1 min-w-0 overflow-x-hidden overflow-y-auto bg-[#f8fafc] px-3 py-4 sm:px-5 sm:py-5 lg:px-16 lg:py-7">
            <div class="w-full max-w-5xl mx-auto">

                            {{-- Page Header --}}
                            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3 mb-4">
                                <div class="min-w-0">
                                    <p class="dt-overline mb-0.5">{{ __('messages.New Support Request') }}</p>
                                    <h1 class="text-[18px] sm:text-[20px] lg:text-[22px] font-bold tracking-tight text-[#0B2638] leading-tight">{{ __('messages.Help & Support') }}</h1>
                                    <p class="text-[12px] text-slate-500 mt-0.5 max-w-2xl">
                                        {{ __('messages.This support form is optimized for your enterprise account. Tell us what you need and our team will assist you.') }}
                                    </p>
                                </div>
                                <div class="flex-shrink-0">
                                    <a href="{{ route('admin.support.index') }}"
                                       class="dt-btn dt-btn-ghost justify-center w-full sm:w-auto">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                                        {{ __('messages.My Tickets') }}
                                    </a>
                                </div>
                            </div>

                            {{-- Flash messages --}}
                            @if(session('success'))
                                <div class="mb-4 rounded-xl bg-emerald-50 p-4 border border-emerald-200 text-sm text-emerald-800">
                                    {{ __('messages.Support request sent successfully') }}
                                </div>
                            @endif

                            @if(session('error'))
                                <div class="mb-4 rounded-xl bg-red-50 p-4 border border-red-200 text-sm text-red-800">
                                    {{ __('messages.Support request email failed') }}
                                </div>
                            @endif

                            @if ($errors->any())
                                <div class="mb-4 rounded-xl bg-red-50 p-4 border border-red-200">
                                    <ul class="list-disc list-inside text-sm text-red-700">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            @php
                                $labelClasses = "block text-[11px] font-semibold uppercase tracking-[.07em] text-slate-400 mb-1.5";
                                $inputClasses = "block w-full rounded-lg border border-slate-200 bg-slate-50 py-2 px-3 text-[13px] text-slate-700 shadow-sm transition-colors focus:border-[#2D7080] focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#2D7080]";
                                $textareaClasses = $inputClasses . " resize-y";
                            @endphp

                            {{-- Card --}}
                            <div class="dt-card overflow-hidden mb-5">
                                {{-- Enterprise context --}}
                                <div class="px-5 sm:px-6 py-4 bg-[#2D7080]/[0.05] border-b border-slate-100">
                                    <h6 class="text-[10.5px] font-bold tracking-[.08em] text-slate-400 uppercase mb-2">{{ __('messages.Enterprise Context') }}</h6>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-5 gap-y-1 text-[12.5px] text-slate-600">
                                        <div>
                                            <p class="mb-1"><span class="font-semibold text-slate-700">{{ __('messages.Name:') }}</span> {{ optional($entreprise)->name ?? __('messages.N/A') }}</p>
                                            @if(optional($entreprise)->created_at)
                                                <p class="mb-1"><span class="font-semibold text-slate-700">{{ __('messages.Member since:') }}</span> {{ optional($entreprise->created_at)->format('Y-m-d') }}</p>
                                            @endif
                                        </div>
                                        <div>
                                            <p class="mb-1"><span class="font-semibold text-slate-700">{{ __('messages.Admin:') }}</span> {{ $user->name }} ({{ $user->email }})</p>
                                        </div>
                                    </div>
                                </div>

                                {{-- Support form --}}
                                <div class="px-5 sm:px-6 py-4 border-b border-slate-100 flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-[#2D7080]/10 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-4 h-4 text-[#2D7080]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8 10h8M8 14h5m-8 7l-2 1 1-4a9 9 0 1116 0 9 9 0 01-12 3z"/></svg>
                                    </div>
                                    <div>
                                        <h2 class="text-[13px] font-semibold text-[#0B2638]">{{ __('messages.New Support Request') }}</h2>
                                        <p class="text-[11px] text-slate-400">{{ __('messages.Share as much detail as possible so we can help you faster.') }}</p>
                                    </div>
                                </div>
                                <form method="POST" action="{{ route('admin.support.store') }}" class="p-5 sm:p-6 space-y-5">
                                    @csrf

                                    <div>
                                        <label for="subject" class="{{ $labelClasses }}">{{ __('messages.Subject') }}</label>
                                        <input type="text" name="subject" id="subject"
                                            class="{{ $inputClasses }} @error('subject') border-red-300 focus:border-red-500 focus:ring-red-500 @enderror"
                                            value="{{ old('subject') }}" required>
                                        @error('subject')
                                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                                        <div>
                                            <label for="category" class="{{ $labelClasses }}">{{ __('messages.Category (optional)') }}</label>
                                            <select name="category" id="category" class="{{ $inputClasses }}">
                                                <option value="">{{ __('messages.Select a category') }}</option>
                                                <option value="billing" {{ old('category') === 'billing' ? 'selected' : '' }}>{{ __('messages.Billing & invoices') }}</option>
                                                <option value="technical" {{ old('category') === 'technical' ? 'selected' : '' }}>{{ __('messages.Technical issue') }}</option>
                                                <option value="account" {{ old('category') === 'account' ? 'selected' : '' }}>{{ __('messages.Account / access') }}</option>
                                                <option value="feature" {{ old('category') === 'feature' ? 'selected' : '' }}>{{ __('messages.Feature request') }}</option>
                                                <option value="other" {{ old('category') === 'other' ? 'selected' : '' }}>{{ __('messages.Other') }}</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label for="priority" class="{{ $labelClasses }}">{{ __('messages.Priority') }}</label>
                                            <select name="priority" id="priority"
                                                class="{{ $inputClasses }} @error('priority') border-red-300 focus:border-red-500 focus:ring-red-500 @enderror"
                                                required>
                                                <option value="low" {{ old('priority', 'medium') === 'low' ? 'selected' : '' }}>{{ __('messages.Low') }}</option>
                                                <option value="medium" {{ old('priority', 'medium') === 'medium' ? 'selected' : '' }}>{{ __('messages.Medium') }}</option>
                                                <option value="high" {{ old('priority', 'medium') === 'high' ? 'selected' : '' }}>{{ __('messages.High') }}</option>
                                            </select>
                                            @error('priority')
                                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>

                                    <div>
                                        <label for="message" class="{{ $labelClasses }}">{{ __('messages.Describe your issue or request') }}</label>
                                        <textarea name="message" id="message" rows="6"
                                            class="{{ $textareaClasses }} @error('message') border-red-300 focus:border-red-500 focus:ring-red-500 @enderror"
                                            placeholder="{{ __('messages.Share as much detail as possible so we can help you faster.') }}"
                                            required>{{ old('message') }}</textarea>
                                        @error('message')
                                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="flex flex-col sm:flex-row sm:justify-end gap-2 pt-4 border-t border-slate-100">
                                        <button type="submit"
                                            class="dt-btn dt-btn-primary justify-center w-full sm:w-auto">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4l16 8-16 8 3-8-3-8zm3 8h13"/></svg>
                                            {{ __('messages.Submit Support Request') }}
                                        </button>
                                    </div>
                                </form>
                            </div>
            </div>
        </main>
    </div>
</body>
</html>


