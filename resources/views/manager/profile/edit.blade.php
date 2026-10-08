@extends('manager.layouts.app')

@section('content')
    <div class="space-y-6">
        <div>
            <h2 class="text-[22px] sm:text-[24px] font-bold tracking-tight text-[#0B2638] leading-tight">{{ __('messages.Edit Profile') }}</h2>
            <p class="mt-1 text-[13.5px] text-slate-500">
                {{ __('messages.Update your personal information and password.') }}
            </p>
        </div>

        @if(session('success'))
            <div class="rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 text-sm" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        {{-- Profile Information Form --}}
        <div class="dt-card p-5 sm:p-6">
            <h3 class="text-[15px] font-bold tracking-tight text-[#0B2638] mb-6">{{ __('messages.Profile Information') }}</h3>

            <form method="POST" action="{{ route('manager.profile.update') }}">
                @csrf
                @method('PATCH')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-slate-700 mb-1">{{ __('messages.Full Name') }}</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $manager->name) }}" required
                            class="w-full rounded-xl border-slate-200 bg-white text-slate-800 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-slate-700 mb-1">{{ __('messages.Email Address') }}</label>
                        <input type="email" name="email" id="email" value="{{ old('email', $manager->email) }}" required
                            class="w-full rounded-xl border-slate-200 bg-white text-slate-800 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3">
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-6 flex flex-col sm:flex-row sm:justify-end gap-2">
                    <a href="{{ route('manager.profile.index') }}"
                        class="dt-btn dt-btn-ghost">
                        {{ __('messages.Cancel') }}
                    </a>
                    <button type="submit"
                        class="dt-btn dt-btn-teal">
                        {{ __('messages.Save Changes') }}
                    </button>
                </div>
            </form>
        </div>

        {{-- Password Update Form --}}
        <div class="dt-card p-5 sm:p-6">
            <h3 class="text-[15px] font-bold tracking-tight text-[#0B2638] mb-6">{{ __('messages.Update Password') }}</h3>

            <form method="POST" action="{{ route('manager.profile.password.update') }}">
                @csrf
                @method('PATCH')

                <div class="space-y-4">
                    <div>
                        <label for="current_password" class="block text-sm font-medium text-slate-700 mb-1">{{ __('messages.Current Password') }}</label>
                        <input type="password" name="current_password" id="current_password" required
                            class="w-full rounded-xl border-slate-200 bg-white text-slate-800 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3">
                        @error('current_password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-slate-700 mb-1">{{ __('messages.New Password') }}</label>
                        <input type="password" name="password" id="password" required
                            class="w-full rounded-xl border-slate-200 bg-white text-slate-800 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3">
                        @error('password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-slate-700 mb-1">{{ __('messages.Confirm New Password') }}</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" required
                            class="w-full rounded-xl border-slate-200 bg-white text-slate-800 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3">
                    </div>
                </div>

                <div class="mt-6 flex flex-col sm:flex-row sm:justify-end gap-2">
                    <a href="{{ route('manager.profile.index') }}"
                        class="dt-btn dt-btn-ghost">
                        {{ __('messages.Cancel') }}
                    </a>
                    <button type="submit"
                        class="dt-btn dt-btn-teal">
                        {{ __('messages.Update Password') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection


