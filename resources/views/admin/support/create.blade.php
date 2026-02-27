<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.Help & Support') }} - DalyTrac</title>
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
        <main class="flex-1 overflow-y-auto ">
            <div class="w-full px-4 md:px-6 py-4">
                <div class="flex flex-wrap -mx-3 mb-5">
                    <div class="w-full">
                        <div class="w-full lg:w-10/12 xl:w-8/12 mx-auto my-4">

                            {{-- Page Header --}}
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <h1 class="text-3xl font-bold text-gray-800">{{ __('messages.Help & Support') }}</h1>
                                    <p class="text-base text-gray-500">
                                        {{ __('messages.This support form is optimized for your enterprise account. Tell us what you need and our team will assist you.') }}
                                    </p>
                                </div>
                                <div class="mt-3 md:mt-0">
                                    <a href="{{ route('admin.support.index') }}"
                                       class="inline-flex items-center px-4 py-2 border border-blue-200 text-sm font-medium rounded-md shadow-sm text-blue-700 bg-blue-50 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        {{ __('messages.My Tickets') }}
                                    </a>
                                </div>
                            </div>
                            <div class="mb-4"></div>

                            {{-- Flash messages --}}
                            @if(session('success'))
                                <div class="mb-4 rounded-md bg-green-50 p-4 border border-green-200 text-sm text-green-800">
                                    {{ session('success') }}
                                </div>
                            @endif

                            @if(session('error'))
                                <div class="mb-4 rounded-md bg-red-50 p-4 border border-red-200 text-sm text-red-800">
                                    {{ session('error') }}
                                </div>
                            @endif

                            @if ($errors->any())
                                <div class="mb-4 rounded-md bg-red-50 p-4 border border-red-200">
                                    <ul class="list-disc list-inside text-sm text-red-700">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            @php
                                $labelClasses = "block text-sm font-medium text-gray-700";
                                $inputClasses = "mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm bg-gray-50";
                                $textareaClasses = $inputClasses . " resize-vertical";
                            @endphp

                            {{-- Card --}}
                            <div class="bg-white rounded-xl shadow-lg p-6 md:p-8 mb-6">
                                {{-- Enterprise context --}}
                                <div class="border border-gray-200 rounded-lg p-4 mb-6 bg-gray-50">
                                    <h6 class="text-xs font-semibold tracking-wide text-gray-500 uppercase mb-3">{{ __('messages.Enterprise Context') }}</h6>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-700">
                                        <div>
                                            <p class="mb-1"><span class="font-medium">{{ __('messages.Name:') }}</span> {{ optional($entreprise)->name ?? 'N/A' }}</p>
                                            @if(optional($entreprise)->created_at)
                                                <p class="mb-1"><span class="font-medium">{{ __('messages.Member since:') }}</span> {{ optional($entreprise->created_at)->format('Y-m-d') }}</p>
                                            @endif
                                        </div>
                                        <div>
                                            <p class="mb-1"><span class="font-medium">{{ __('messages.Admin:') }}</span> {{ $user->name }} ({{ $user->email }})</p>
                                        </div>
                                    </div>
                                </div>

                                {{-- Support form --}}
                                <form method="POST" action="{{ route('admin.support.store') }}" class="space-y-6">
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

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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

                                    <div class="flex justify-end">
                                        <button type="submit"
                                            class="inline-flex items-center px-5 py-2.5 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                            {{ __('messages.Submit Support Request') }}
                                        </button>
                                    </div>
                                </form>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>


