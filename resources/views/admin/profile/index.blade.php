<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>DailyTrac Dashboard</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{-- Tailwind is required for the color classes --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-50 flex h-screen overflow-hidden">

    {{-- 
        =========================================
        LOGIC BLOCK: DEFINE PLAN CONFIGURATIONS
        =========================================
    --}}
    @php
        // IMPORTANT: $planKey is now passed from the controller
        // It will be: 'trial', 'solo', 'premium', or 'pro'
        
        // Define the look & feel for each plan
        $plans = [
            'trial' => [
                'label' => 'Free Trial - 15 Days',
                'title' => 'Free Trial Features',
                'subtitle' => 'Test everything for 15 days',
                'color_theme' => 'purple',
                'bg_theme' => 'purple',
                'button_color' => 'bg-blue-600 hover:bg-blue-700',
                'button_text' => 'Choose a Plan After Trial',
                'icon' => 'clock',
                'show_timer' => true,
                'features' => [
                    'Unlimited Invoices', 'Unlimited Clients', 
                    'Unlimited Projects', 'Advanced Reports', 
                    'Email Support', 'No Credit Card Required', 
                    '15 Day Trial Period'
                ]
            ],
            'solo' => [
                'label' => 'Solo Plan',
                'title' => 'Solo Plan',
                'subtitle' => 'Perfect for freelancers',
                'color_theme' => 'blue',
                'bg_theme' => 'blue',
                'button_color' => 'bg-blue-600 hover:bg-blue-700',
                'button_text' => 'Solo Plan - €19/month',
                'icon' => 'user',
                'show_timer' => false,
                'features' => [
                    '1 User Maximum', 'Advanced Invoicing',
                    'Unlimited Clients', 'Advanced Reports'
                ]
            ],
            'premium' => [
                'label' => 'Premium Plan',
                'title' => 'Premium Plan',
                'subtitle' => 'For growing teams',
                'color_theme' => 'emerald',
                'bg_theme' => 'emerald',
                'button_color' => 'bg-emerald-600 hover:bg-emerald-700',
                'button_text' => 'Premium Plan - €49/month',
                'icon' => 'crown',
                'show_timer' => false,
                'features' => [
                    'Up to 10 Users', 'Advanced Invoicing',
                    'Unlimited Clients', 'Advanced Reports'
                ]
            ],
            'pro' => [
                'label' => 'Pro Plan',
                'title' => 'Pro Plan',
                'subtitle' => 'For large enterprises',
                'color_theme' => 'orange',
                'bg_theme' => 'green',
                'button_color' => 'bg-orange-600 hover:bg-orange-700',
                'button_text' => 'Pro Plan - €99/month',
                'icon' => 'crown',
                'show_timer' => false,
                'features' => [
                    'Unlimited Users', 'Advanced Invoicing',
                    'Unlimited Clients', 'Advanced Reports'
                ]
            ]
        ];

        // Load the config for the current plan (passed from controller)
        $currentPlan = $plans[$planKey] ?? $plans['trial'];
    @endphp

    {{-- Sidebar --}}
    <x-dashboard.sidebar />
    
    <div class="flex flex-col flex-1 overflow-y-auto">
        {{-- Navbar --}}
        <x-dashboard.navbar />

        {{-- Main Content --}}
        <div class="container mx-auto max-w-6xl p-4 md:p-8">

            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">My Profile</h1>
                <p class="text-gray-600 mt-1">Manage your account and subscription</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                {{-- LEFT COLUMN: User Profile Card --}}
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-lg shadow-lg p-6 h-full">
                        
                        <div class="flex flex-col items-center">
                            {{-- Initials Logic --}}
                            @php
                                $nameParts = explode(' ', $user->name);
                                $initials = count($nameParts) > 1 
                                    ? strtoupper(substr($nameParts[0], 0, 1) . substr(end($nameParts), 0, 1))
                                    : strtoupper(substr($user->name, 0, 2));
                            @endphp
                            <div class="w-24 h-24 rounded-full bg-blue-600 flex items-center justify-center shadow-md">
                                <span class="text-white text-3xl font-bold">{{$initials}}</span>
                            </div>
                            <h2 class="text-xl font-bold text-gray-900 mt-4">{{$user->name}}</h2>
                            <p class="text-sm text-gray-500">{{$user->email}}</p>
                        </div>

                        <hr class="my-6 border-gray-200">

                        <div class="space-y-4">
                            <div>
                                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider">Account Type</h3>
                                <div class="flex items-center mt-2">
                                    {{-- Dynamic Icon based on plan --}}
                                    @if($currentPlan['icon'] === 'crown')
                                        <svg class="w-5 h-5 mr-2 text-{{ $currentPlan['color_theme'] }}-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 3.75V16.5L12 14.25L7.5 16.5V3.75m9 0H18A2.25 2.25 0 0120.25 6v12A2.25 2.25 0 0118 20.25H6A2.25 2.25 0 013.75 18V6A2.25 2.25 0 016 3.75h1.5m9 0h-9" />
                                        </svg>
                                    @elseif($currentPlan['icon'] === 'clock')
                                        <svg class="w-5 h-5 mr-2 text-{{ $currentPlan['color_theme'] }}-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    @else
                                        <svg class="w-5 h-5 mr-2 text-{{ $currentPlan['color_theme'] }}-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                        </svg>
                                    @endif
                                    
                                    <span class="text-{{ $currentPlan['color_theme'] }}-600 font-bold">
                                        {{ $currentPlan['label'] }}
                                    </span>
                                </div>
                            </div>

                            <div>
                                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider">Member Since</h3>
                                <p class="text-gray-800 font-medium mt-2">
                                    {{ $user->employee?->Date_Entree ? $user->employee->Date_Entree->format('F j, Y') : now()->format('F j, Y') }}
                                </p>
                            </div>

                            <div>
                                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider">Status</h3>
                                <div class="flex items-center mt-2">
                                    @if($user->is_activate ?? true)
                                        <span class="w-2 h-2 bg-green-500 rounded-full mr-2"></span>
                                        <span class="text-gray-800 font-medium">Active</span>
                                    @else
                                        <span class="w-2 h-2 bg-red-500 rounded-full mr-2"></span>
                                        <span class="text-gray-800 font-medium">Inactive</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <a href="{{ route('profile.edit') }}" class="mt-8 w-full bg-blue-600 text-white py-2 px-4 rounded-lg font-semibold hover:bg-blue-700 transition duration-300 block text-center">
                            Edit Profile
                        </a>
                    </div>
                </div>

                {{-- RIGHT COLUMN: Plan Details --}}
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-lg shadow-lg p-6 h-full relative">
                        
                        {{-- Header Section --}}
                        <div class="flex justify-between items-start">
                            <div class="flex items-center mb-6">
                                {{-- Dynamic Icon Background Color --}}
                                <div class="flex-shrink-0 p-3 bg-{{ $currentPlan['color_theme'] }}-100 rounded-lg">
                                    
                                    {{-- Dynamic Icon SVG --}}
                                    @if($currentPlan['icon'] === 'crown')
                                        <svg class="w-8 h-8 text-{{ $currentPlan['color_theme'] }}-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 3.75V16.5L12 14.25L7.5 16.5V3.75m9 0H18A2.25 2.25 0 0120.25 6v12A2.25 2.25 0 0118 20.25H6A2.25 2.25 0 013.75 18V6A2.25 2.25 0 016 3.75h1.5m9 0h-9" />
                                        </svg>
                                    @elseif($currentPlan['icon'] === 'clock')
                                        <svg class="w-8 h-8 text-{{ $currentPlan['color_theme'] }}-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    @else
                                        <svg class="w-8 h-8 text-{{ $currentPlan['color_theme'] }}-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                        </svg>
                                    @endif
                                </div>
                                <div class="ml-4">
                                    <h2 class="text-xl font-bold text-gray-900">{{ $currentPlan['title'] }}</h2>
                                    <p class="text-gray-500">{{ $currentPlan['subtitle'] }}</p>
                                </div>
                            </div>

                            {{-- Days Left Timer (Only for Trial) - Now using $daysLeft from controller --}}
                            @if($currentPlan['show_timer'])
                                <div class="text-center hidden sm:block">
                                    <div class="text-3xl font-bold text-{{ $currentPlan['color_theme'] }}-600">
                                        {{ $daysLeft }}
                                    </div>
                                    <div class="text-xs text-gray-500 font-medium uppercase">days left</div>
                                </div>
                            @endif
                        </div>

                        {{-- Features Grid --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2">
                            @foreach($currentPlan['features'] as $feature)
                                <div class="flex items-center p-4 bg-{{ $currentPlan['bg_theme'] }}-50 border border-{{ $currentPlan['bg_theme'] }}-100 rounded-lg">
                                    <svg class="w-5 h-5 text-{{ $currentPlan['color_theme'] }}-500 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    <span class="ml-3 text-gray-800 font-medium">{{ $feature }}</span>
                                </div>
                            @endforeach
                        </div>

                        {{-- Action Button --}}
                        <div class="mt-8">
                            <a href="{{ route('profile.upgrade') }}" class="w-full block {{ $currentPlan['button_color'] }} text-white py-3 px-6 rounded-lg font-bold text-center transition duration-300 shadow-md">
                                {{ $currentPlan['button_text'] }}
                            </a>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>