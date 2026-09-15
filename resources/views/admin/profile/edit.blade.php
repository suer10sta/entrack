<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.Edit Profile Page Title') }}</title>
    @vite('resources/css/app.css')
</head>
<body class="bg-gray-50 flex h-screen overflow-hidden">
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
        <div class="container mx-auto max-w-4xl p-4 md:p-8">

            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">{{ __('messages.Edit Profile') }}</h1>
                <p class="text-gray-600 mt-1">{{ __('messages.Edit Profile subtitle') }}</p>
            </div>

            {{-- === PROFILE INITIALS BLOCK === --}}
            <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
                <div class="flex items-center space-x-6">
                    
                    {{-- Creates initials like "JD" from the user's name --}}
                    @php
                        $nameParts = explode(' ', $user->name);
                        $initials = count($nameParts) > 1 
                                    ? strtoupper(substr($nameParts[0], 0, 1) . substr(end($nameParts), 0, 1))
                                    : strtoupper(substr($user->name, 0, 2));
                    @endphp

                    <div class="w-20 h-20 rounded-full bg-blue-600 flex items-center justify-center flex-shrink-0">
                        <span class="text-white text-3xl font-bold">{{ $initials }}</span>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">{{ __('messages.Profile Initials') }}</h2>
                        <p class="text-sm text-gray-500 mt-1">{{ __('messages.Profile Initials description') }}</p>
                    </div>
                </div>
            </div>

            {{-- === SIMPLIFIED PROFILE FORM === --}}
            <form method="POST" action="{{ route('profile.update') }}" class="bg-white rounded-lg shadow-lg p-6">
                @csrf
                @method('PATCH') {{-- Required for updates --}}

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    
                    {{-- 1. Name --}}
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">{{ __('messages.Full Name') }}</label>
                        <input type="text" name="name" id="name" value="{{ $user->name }}" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    </div>

                    {{-- 2. Email --}}
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">{{ __('messages.Email Address') }}</label>
                        <input type="email" name="email" id="email" value="{{ $user->email }}" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    </div>

                    {{-- Language --}}
                    <div>
                        <label for="locale" class="block text-sm font-medium text-gray-700">{{ __('messages.Language') }}</label>
                        @php
                            $selectedLocale = old('locale', $user->locale ?? app()->getLocale());
                        @endphp
                        <select id="locale"
                                name="locale"
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <option value="en" {{ $selectedLocale === 'en' ? 'selected' : '' }}>{{ __('messages.English') }}</option>
                            <option value="fr" {{ $selectedLocale === 'fr' ? 'selected' : '' }}>{{ __('messages.Français') }}</option>
                            <option value="es" {{ $selectedLocale === 'es' ? 'selected' : '' }}>{{ __('messages.Español') }}</option>
                            <option value="nl" {{ $selectedLocale === 'nl' ? 'selected' : '' }}>{{ __('messages.Nederlands') }}</option>
                        </select>
                        @error('locale')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- 3. Phone Number --}}
                    <div>
                        <label for="phoneNumber" class="block text-sm font-medium text-gray-700">{{ __('messages.Phone Number') }}</label>
                        {{-- The '?' (null-safe operator) prevents errors if the employee record doesn't exist --}}
                        <input type="text" name="phoneNumber" id="phoneNumber" value="{{ $user->employee?->phoneNumber }}" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    </div>

                    {{-- 4. Status --}}
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700">{{ __('messages.Status') }}</label>
                        <select id="status" name="is_activate" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <option value="1" {{ $user->is_activate ? 'selected' : '' }}>{{ __('messages.Active') }}</option>
                            <option value="0" {{ !$user->is_activate ? 'selected' : '' }}>{{ __('messages.Inactive') }}</option>
                        </select>
                    </div>

                    {{-- 5. CNSS --}}
                    <div class="md:col-span-2">
                        <label for="cnss" class="block text-sm font-medium text-gray-700">{{ __('messages.CNSS Number') }}</label>
                        <input type="text" name="cnss" id="cnss" value="{{ $user->employee?->cnss }}" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    </div>
                    
                    {{-- 6. Bank Account --}}
                    <div class="md:col-span-2">
                        <label for="bankAccountNumber" class="block text-sm font-medium text-gray-700">{{ __('messages.Bank Account Number') }}</label>
                        <input type="text" name="bankAccountNumber" id="bankAccountNumber" value="{{ $user->employee?->bankAccountNumber }}" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    </div>

                </div> {{-- End of grid --}}

                {{-- Form Buttons --}}
                <div class="mt-8 flex justify-end space-x-4">
                    {{-- This links back to your main profile page --}}
                    <a href="{{ route('profile') }}" class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        {{ __('messages.Cancel') }}
                    </a>
                    <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        {{ __('messages.Save Changes') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
    
</body>
</html>
