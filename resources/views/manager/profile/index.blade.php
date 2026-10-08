@extends('manager.layouts.app')

@section('content')
    <div class="space-y-6">
        <div>
            <h2 class="text-[22px] sm:text-[24px] font-bold tracking-tight text-[#0B2638] leading-tight">{{ __('messages.My Profile') }}</h2>
            <p class="mt-1 text-[13.5px] text-slate-500">
                {{ __('messages.Manage your account information and settings.') }}
            </p>
        </div>

        @if(session('success'))
            <div class="rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 text-sm" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- LEFT COLUMN: Manager Profile Card --}}
            <div class="lg:col-span-1">
                <div class="dt-card p-5 sm:p-6">
                    <div class="flex flex-col items-center">
                        @php
                            $nameParts = explode(' ', $manager->name ?? 'Manager');
                            $initials = count($nameParts) > 1 
                                ? strtoupper(substr($nameParts[0], 0, 1) . substr(end($nameParts), 0, 1))
                                : strtoupper(substr($manager->name ?? 'M', 0, 2));
                        @endphp
                        <div class="w-24 h-24 rounded-full bg-[#E7B84B] flex items-center justify-center shadow-md ring-4 ring-[#E7B84B]/25">
                            <span class="text-[#0B2638] text-3xl font-bold">{{ $initials }}</span>
                        </div>
                        <h2 class="text-lg font-bold tracking-tight text-[#0B2638] mt-4">{{ $manager->name ?? 'Manager' }}</h2>
                        <p class="text-sm text-slate-500">{{ $manager->email }}</p>
                    </div>

                    <hr class="my-6 border-slate-200">

                    <div class="space-y-4">
                        <div>
                            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">{{ __('messages.Role') }}</h3>
                            <div class="flex items-center mt-2">
                                <svg class="w-5 h-5 mr-2 text-[#2D7080]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                                </svg>
                                <span class="text-[#2D7080] font-bold">{{ __('messages.Manager') }}</span>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">{{ __('messages.Member Since') }}</h3>
                            <p class="text-slate-800 font-medium mt-2">
                                {{ $manager->created_at ? $manager->created_at->format('F j, Y') : 'N/A' }}
                            </p>
                        </div>

                        <div>
                            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">{{ __('messages.Status') }}</h3>
                            <div class="flex items-center mt-2">
                                <span class="dt-badge dt-badge-success"><span class="dt-dot"></span>{{ __('messages.Active') }}</span>
                            </div>
                        </div>
                    </div>

                    <a href="{{ route('manager.profile.edit') }}" class="dt-btn dt-btn-teal w-full mt-8">
                        {{ __('messages.Edit Profile') }}
                    </a>
                </div>
            </div>

            {{-- RIGHT COLUMN: Account Information --}}
            <div class="lg:col-span-2">
                <div class="dt-card p-5 sm:p-6">
                    <h3 class="text-[15px] font-bold tracking-tight text-[#0B2638] mb-6">{{ __('messages.Account Information') }}</h3>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('messages.Full Name') }}</label>
                            <p class="text-slate-800">{{ $manager->name ?? 'N/A' }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('messages.Email Address') }}</label>
                            <p class="text-slate-800">{{ $manager->email ?? 'N/A' }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('messages.Account Created') }}</label>
                            <p class="text-slate-800">{{ $manager->created_at ? $manager->created_at->format('F j, Y, g:i a') : 'N/A' }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('messages.Last Updated') }}</label>
                            <p class="text-slate-800">{{ $manager->updated_at ? $manager->updated_at->format('F j, Y, g:i a') : 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection


