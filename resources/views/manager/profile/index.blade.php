@extends('manager.layouts.app')

@section('content')
    <div class="space-y-6">
        <div>
            <h2 class="text-2xl font-semibold text-gray-900">{{ __('messages.My Profile') }}</h2>
            <p class="mt-1 text-sm text-gray-500">
                {{ __('messages.Manage your account information and settings.') }}
            </p>
        </div>

        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- LEFT COLUMN: Manager Profile Card --}}
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex flex-col items-center">
                        @php
                            $nameParts = explode(' ', $manager->name ?? 'Manager');
                            $initials = count($nameParts) > 1 
                                ? strtoupper(substr($nameParts[0], 0, 1) . substr(end($nameParts), 0, 1))
                                : strtoupper(substr($manager->name ?? 'M', 0, 2));
                        @endphp
                        <div class="w-24 h-24 rounded-full bg-blue-600 flex items-center justify-center shadow-md">
                            <span class="text-white text-3xl font-bold">{{ $initials }}</span>
                        </div>
                        <h2 class="text-xl font-bold text-gray-900 mt-4">{{ $manager->name ?? 'Manager' }}</h2>
                        <p class="text-sm text-gray-500">{{ $manager->email }}</p>
                    </div>

                    <hr class="my-6 border-gray-200">

                    <div class="space-y-4">
                        <div>
                            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider">{{ __('messages.Role') }}</h3>
                            <div class="flex items-center mt-2">
                                <svg class="w-5 h-5 mr-2 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                                </svg>
                                <span class="text-blue-600 font-bold">{{ __('messages.Manager') }}</span>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider">{{ __('messages.Member Since') }}</h3>
                            <p class="text-gray-800 font-medium mt-2">
                                {{ $manager->created_at ? $manager->created_at->format('F j, Y') : 'N/A' }}
                            </p>
                        </div>

                        <div>
                            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider">{{ __('messages.Status') }}</h3>
                            <div class="flex items-center mt-2">
                                <span class="w-2 h-2 bg-green-500 rounded-full mr-2"></span>
                                <span class="text-gray-800 font-medium">{{ __('messages.Active') }}</span>
                            </div>
                        </div>
                    </div>

                    <a href="{{ route('manager.profile.edit') }}" class="mt-8 w-full bg-blue-600 text-white py-2 px-4 rounded-lg font-semibold hover:bg-blue-700 transition duration-300 block text-center">
                        {{ __('messages.Edit Profile') }}
                    </a>
                </div>
            </div>

            {{-- RIGHT COLUMN: Account Information --}}
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6">{{ __('messages.Account Information') }}</h3>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Full Name') }}</label>
                            <p class="text-gray-900">{{ $manager->name ?? 'N/A' }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Email Address') }}</label>
                            <p class="text-gray-900">{{ $manager->email ?? 'N/A' }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Account Created') }}</label>
                            <p class="text-gray-900">{{ $manager->created_at ? $manager->created_at->format('F j, Y, g:i a') : 'N/A' }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Last Updated') }}</label>
                            <p class="text-gray-900">{{ $manager->updated_at ? $manager->updated_at->format('F j, Y, g:i a') : 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

