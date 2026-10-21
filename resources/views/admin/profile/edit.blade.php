<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <x-favicon />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.Edit Profile Page Title') }}</title>
    @vite('resources/css/app.css')
@include('partials.edit-layout-styles')
</head>
<body class="dt-app-shell bg-[#f8fafc] text-slate-900 antialiased flex h-screen overflow-hidden">
    {{-- Sidebar --}}
    @if(Auth::user()->hasRole('Admin'))
        <x-dashboard.sidebar />
    @else
        <x-employees.sidebar />
    @endif
    <div class="flex flex-col flex-1 min-w-0 overflow-y-auto">
        {{-- Navbar --}}
        <x-dashboard.navbar />

        {{-- Main Content --}}
        <div class="dt-edit-container dt-edit-profile mx-auto w-full max-w-4xl p-4 sm:p-6 lg:p-8">

            <div class="mb-5 sm:mb-6">
                <p class="dt-overline">{{ __('messages.My Profile') }}</p>
                <h1 class="mt-1 text-[22px] sm:text-2xl font-bold tracking-tight text-[#0B2638]">{{ __('messages.Edit Profile') }}</h1>
                <p class="dt-section-subtitle mt-1">{{ __('messages.Edit Profile subtitle') }}</p>
            </div>

            {{-- === PROFILE INITIALS BLOCK === --}}
            <div class="dt-card p-5 sm:p-6 mb-4">
                <div class="flex flex-col sm:flex-row sm:items-center gap-4 sm:gap-5">
                    {{-- Creates initials like "JD" from the user's name --}}
                    @php
                        $nameParts = explode(' ', $user->name);
                        $initials = count($nameParts) > 1
                                    ? strtoupper(substr($nameParts[0], 0, 1) . substr(end($nameParts), 0, 1))
                                    : strtoupper(substr($user->name, 0, 2));
                    @endphp

                    <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-[#2D7080] flex items-center justify-center flex-shrink-0">
                        <span class="text-white text-2xl sm:text-3xl font-bold">{{ $initials }}</span>
                    </div>
                    <div class="min-w-0">
                        <h2 class="text-[15px] font-semibold tracking-tight text-[#0B2638]">{{ __('messages.Profile Initials') }}</h2>
                        <p class="dt-section-subtitle mt-0.5">{{ __('messages.Profile Initials description') }}</p>
                        <p class="mt-1.5 truncate text-sm font-semibold text-[#0B2638]">{{ $user->name }}</p>
                        <p class="truncate text-[12.5px] text-slate-500">{{ $user->email }}</p>
                    </div>
                </div>
            </div>

            {{-- === SIMPLIFIED PROFILE FORM === --}}
            <form method="POST" action="{{ route('profile.update') }}" class="dt-card p-5 sm:p-6">
                @csrf
                @method('PATCH') {{-- Required for updates --}}

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4">

                    {{-- 1. Name --}}
                    <div>
                        <label for="name" class="block text-[12.5px] font-medium text-slate-700 mb-1">{{ __('messages.Full Name') }}</label>
                        <input type="text" name="name" id="name" value="{{ $user->name }}" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none">
                    </div>

                    {{-- 2. Email --}}
                    <div>
                        <label for="email" class="block text-[12.5px] font-medium text-slate-700 mb-1">{{ __('messages.Email Address') }}</label>
                        <input type="email" name="email" id="email" value="{{ $user->email }}" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none">
                    </div>

                    {{-- Language --}}
                    <div>
                        <label for="locale" class="block text-[12.5px] font-medium text-slate-700 mb-1">{{ __('messages.Language') }}</label>
                        @php
                            $selectedLocale = old('locale', $user->locale ?? app()->getLocale());
                        @endphp
                        <select id="locale"
                                name="locale"
                                class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none">
                            <option value="en" {{ $selectedLocale === 'en' ? 'selected' : '' }}>{{ __('messages.English') }}</option>
                            <option value="fr" {{ $selectedLocale === 'fr' ? 'selected' : '' }}>{{ __('messages.Français') }}</option>
                            <option value="es" {{ $selectedLocale === 'es' ? 'selected' : '' }}>{{ __('messages.Español') }}</option>
                            <option value="nl" {{ $selectedLocale === 'nl' ? 'selected' : '' }}>{{ __('messages.Nederlands') }}</option>
                        </select>
                        @error('locale')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- 3. Phone Number --}}
                    <div>
                        <label for="phoneNumber" class="block text-[12.5px] font-medium text-slate-700 mb-1">{{ __('messages.Phone Number') }}</label>
                        {{-- The '?' (null-safe operator) prevents errors if the employee record doesn't exist --}}
                        <input type="text" name="phoneNumber" id="phoneNumber" value="{{ $user->employee?->phoneNumber }}" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none">
                    </div>

                    {{-- 4. Status --}}
                    <div>
                        <label for="status" class="block text-[12.5px] font-medium text-slate-700 mb-1">{{ __('messages.Status') }}</label>
                        <select id="status" name="is_activate" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none">
                            <option value="1" {{ $user->is_activate ? 'selected' : '' }}>{{ __('messages.Active') }}</option>
                            <option value="0" {{ !$user->is_activate ? 'selected' : '' }}>{{ __('messages.Inactive') }}</option>
                        </select>
                    </div>

                    {{-- 5. CNSS --}}
                    <div>
                        <label for="cnss" class="block text-[12.5px] font-medium text-slate-700 mb-1">{{ __('messages.CNSS Number') }}</label>
                        <input type="text" name="cnss" id="cnss" value="{{ $user->employee?->cnss }}" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none">
                    </div>

                    {{-- 6. Bank Account --}}
                    <div class="md:col-span-2">
                        <label for="bankAccountNumber" class="block text-[12.5px] font-medium text-slate-700 mb-1">{{ __('messages.Bank Account Number') }}</label>
                        <input type="text" name="bankAccountNumber" id="bankAccountNumber" value="{{ $user->employee?->bankAccountNumber }}" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none">
                    </div>

                </div> {{-- End of grid --}}

                {{-- Form Buttons --}}
                <div class="mt-6 flex flex-col-reverse sm:flex-row sm:justify-end gap-2">
                    {{-- This links back to your main profile page --}}
                    <a href="{{ route('profile') }}" class="dt-btn dt-btn-ghost min-h-[42px] sm:w-auto w-full">
                        {{ __('messages.Cancel') }}
                    </a>
                    <button type="submit" class="dt-btn dt-btn-primary min-h-[42px] sm:w-auto w-full">
                        {{ __('messages.Save Changes') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>
