<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entrack Dashboard</title>
    @vite('resources/css/app.css')
</head>
<body class="bg-gray-50 flex h-screen overflow-hidden">
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

            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-lg p-6">
                    
                    <div class="flex flex-col items-center">
                    {{-- This PHP block creates the "JD" initials from the user's name --}}
                            @php
                                $nameParts = explode(' ', $user->name);
                                $initials = count($nameParts) > 1 
                                            ? strtoupper(substr($nameParts[0], 0, 1) . substr(end($nameParts), 0, 1))
                                            : strtoupper(substr($user->name, 0, 2));
                            @endphp
                        <div class="w-20 h-20 rounded-full bg-blue-600 flex items-center justify-center">
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
                                <span class="w-2 h-2 bg-green-500 rounded-full mr-2"></span>
                                <span class="text-gray-800 font-medium">Free Plan</span>
                            </div>
                        </div>
                        <div>
                            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider">Member Since</h3>
                            <p class="text-gray-800 font-medium mt-2">
                            {{ $user->employee?->Date_Entree ? $user->employee->Date_Entree->format('F j, Y') : 'N/A' }}                            </p>
                        </div>
                        <div>
                                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider">Status</h3>
                                {{-- 4. User's Status from DB --}}
                                <div class="flex items-center mt-2">
                                    @if($user->is_activate)
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

            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-lg p-6">
                    
                    <div class="flex items-center">
                        <div class="flex-shrink-0 p-2 bg-gray-100 rounded-lg">
                            <svg class="w-6 h-6 text-gray-700" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h2 class="text-lg font-bold text-gray-900">Free Plan Features</h2>
                            <p class="text-sm text-gray-500">Get started with essential tools</p>
                        </div>
                    </div>

                    <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                        
                        <div class="flex items-center p-4 bg-green-50 border border-green-200 rounded-lg">
                            <svg class="w-5 h-5 text-green-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            <span class="ml-3 text-gray-800 font-medium">Basic Invoicing</span>
                        </div>

                        <div class="flex items-center p-4 bg-green-50 border border-green-200 rounded-lg">
                            <svg class="w-5 h-5 text-green-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            <span class="ml-3 text-gray-800 font-medium">Up to 5 Clients</span>
                        </div>

                        <div class="flex items-center p-4 bg-green-50 border border-green-200 rounded-lg">
                            <svg class="w-5 h-5 text-green-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            <span class="ml-3 text-gray-800 font-medium">Basic Reports</span>
                        </div>

                        <div class="flex items-center p-4 bg-green-50 border border-green-200 rounded-lg">
                            <svg class="w-5 h-5 text-green-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            <span class="ml-3 text-gray-800 font-medium">Email Support</span>
                        </div>

                        <div class="flex items-center p-4 bg-gray-100 border border-gray-200 rounded-lg">
                            <svg class="w-5 h-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd" />
                            </svg>
                            <span class="ml-3 text-gray-500">Advanced Analytics</span>
                        </div>

                        <div class="flex items-center p-4 bg-gray-100 border border-gray-200 rounded-lg">
                            <svg class="w-5 h-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd" />
                            </svg>
                            <span class="ml-3 text-gray-500">Unlimited Clients</span>
                        </div>

                        <div class="flex items-center p-4 bg-gray-100 border border-gray-200 rounded-lg">
                            <svg class="w-5 h-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd" />
                            </svg>
                            <span class="ml-3 text-gray-500">Priority Support</span>
                        </div>

                        <div class="flex items-center p-4 bg-gray-100 border border-gray-200 rounded-lg">
                            <svg class="w-5 h-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd" />
                            </svg>
                            <span class="ml-3 text-gray-500">Custom Branding</span>
                        </div>

                    </div>

                    <a href="{{ route('profile.upgrade') }}" class="mt-8 w-full block bg-orange-500 text-white py-3 px-6 rounded-lg font-bold text-center hover:bg-orange-600 transition duration-300">
                            Upgrade to Premium
                        </a>
                </div>
            </div>

        </div>
        
    </div>
    
</body>
</html>
