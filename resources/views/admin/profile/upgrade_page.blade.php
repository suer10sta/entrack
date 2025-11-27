<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DailyTrac Dashboard</title>
    @vite('resources/css/app.css')
</head>
<body class="bg-gray-50 flex h-screen overflow-hidden">
    {{-- Sidebar --}}
    <x-dashboard.sidebar />
    <div class="flex flex-col flex-1 overflow-y-auto">
        {{-- Navbar --}}
        <x-dashboard.navbar />

        {{-- Main Content --}}
        <div class="container mx-auto max-w-6xl px-4 py-12 sm:px-6 lg:px-8">
        
        <div class="text-center mb-10">
            <h1 class="text-4xl font-bold text-gray-900 sm:text-5xl">
                Upgrade Your Plan
            </h1>
            <p class="mt-4 text-xl text-gray-600">
                Unlock powerful features to grow your business
            </p>
        </div>

        <div class="mb-12 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
            
            <div class="bg-white rounded-2xl shadow-lg p-6">
                <div class="flex-shrink-0 bg-purple-100 p-3 rounded-lg inline-block">
                    <svg class="h-6 w-6 text-purple-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mt-4">15-Day Trial</h3>
                <p class="text-gray-600 mt-1">Test everything unlimited for free</p>
            </div>

            <div class="bg-white rounded-2xl shadow-lg p-6">
                <div class="flex-shrink-0 bg-blue-100 p-3 rounded-lg inline-block">
                    <svg class="h-6 w-6 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c.139.132.28.255.424.372M9.75 3.104A2.25 2.25 0 0112 5.25v13.5A2.25 2.25 0 019.75 21v-5.714A2.25 2.25 0 0112 13.688v-5.714a2.25 2.25 0 012.25-2.162M14.25 3.104c.139.132.28.255.424.372M14.25 3.104A2.25 2.25 0 0012 5.25v13.5A2.25 2.25 0 0014.25 21v-5.714a2.25 2.25 0 00-2.25-2.612v-5.714a2.25 2.25 0 002.25-2.162M12 2.25c-.14.118-.277.243-.41.372M12 2.25c.14.118.277.243.41.372" /></svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mt-4">Advanced Features</h3>
                <p class="text-gray-600 mt-1">Powerful tools for invoicing and reporting</p>
            </div>

            <div class="bg-white rounded-2xl shadow-lg p-6">
                <div class="flex-shrink-0 bg-green-100 p-3 rounded-lg inline-block">
                    <svg class="h-6 w-6 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-2.144M9 13a4 4 0 100-8 4 4 0 000 8zm0 0v1.5a2.5 2.5 0 005 0V13m-5 0v0a5 5 0 00-5 0v0m5 0v0a5 5 0 015 0v0m-5 0v0a5 5 0 01-5 0v0" /></svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mt-4">Team Collaboration</h3>
                <p class="text-gray-600 mt-1">Add team members on Premium & Pro</p>
            </div>

            <div class="bg-white rounded-2xl shadow-lg p-6">
                <div class="flex-shrink-0 bg-yellow-100 p-3 rounded-lg inline-block">
                     <svg class="h-6 w-6 text-yellow-700" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" /></svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mt-4">Expert Support</h3>
                <p class="text-gray-600 mt-1">Email, phone, and priority assistance</p>
            </div>
        </div>
        {{-- Pricing Cards --}}
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-stretch">

                    {{-- Solo Plan --}}
                    <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-8 flex flex-col hover:shadow-xl transition-shadow duration-300">
                        <div class="mb-4">
                            <h2 class="text-2xl font-bold text-gray-900">Solo</h2>
                            <p class="text-gray-500 mt-2">Perfect for freelancers</p>
                        </div>
                        <div class="mb-6">
                            <span class="text-5xl font-extrabold text-gray-900">€19</span>
                            <span class="text-gray-500 font-medium">/ month</span>
                        </div>
                        <a href="{{ route('subscription.choose', ['plan' => 'solo']) }}" class="block w-full py-3 px-6 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg text-center transition-colors mb-8">
                            Choose Solo
                        </a>
                        <ul class="space-y-4 text-gray-600 flex-grow">
                            <li class="flex items-start"><svg class="h-5 w-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> <strong>1 User Maximum</strong></li>
                            <li class="flex items-start"><svg class="h-5 w-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> Advanced Invoicing</li>
                            <li class="flex items-start"><svg class="h-5 w-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> Unlimited Clients</li>
                            <li class="flex items-start"><svg class="h-5 w-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> Advanced Reports</li>
                        </ul>
                    </div>

                    <div class="bg-white rounded-2xl shadow-xl border-2 border-green-500 p-8 flex flex-col relative z-10">
                        <div class="absolute top-0 left-1/2 transform -translate-x-1/2 -translate-y-1/2">
                            <span class="bg-green-500 text-white text-xs font-bold uppercase tracking-wide py-1 px-3 rounded-full shadow-sm">Most Popular</span>
                        </div>
                        <div class="mb-4">
                            <h2 class="text-2xl font-bold text-gray-900">Premium</h2>
                            <p class="text-gray-500 mt-2">For growing teams</p>
                        </div>
                        <div class="mb-6">
                            <span class="text-5xl font-extrabold text-gray-900">€49</span>
                            <span class="text-gray-500 font-medium">/ month</span>
                        </div>
                        <a href="{{ route('subscription.choose', ['plan' => 'premium']) }}" class="block w-full py-3 px-6 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg text-center transition-colors mb-8 shadow-md">
                            Choose Premium
                        </a>
                        <ul class="space-y-4 text-gray-600 flex-grow">
                            <li class="flex items-start"><svg class="h-5 w-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> <strong>Up to 10 Users</strong></li>
                            <li class="flex items-start"><svg class="h-5 w-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> Advanced Invoicing</li>
                            <li class="flex items-start"><svg class="h-5 w-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> Unlimited Clients</li>
                            <li class="flex items-start"><svg class="h-5 w-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> Advanced Reports</li>
                        </ul>
                    </div>

                    {{-- Pro Plan --}}
                    <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-8 flex flex-col hover:shadow-xl transition-shadow duration-300">
                        <div class="mb-4">
                            <h2 class="text-2xl font-bold text-gray-900">Pro</h2>
                            <p class="text-gray-500 mt-2">For large enterprises</p>
                        </div>
                        <div class="mb-6">
                            <span class="text-5xl font-extrabold text-gray-900">€99</span>
                            <span class="text-gray-500 font-medium">/ month</span>
                        </div>
                        <a href="{{ route('subscription.choose', ['plan' => 'pro']) }}" class="block w-full py-3 px-6 bg-orange-500 hover:bg-orange-600 text-white font-semibold rounded-lg text-center transition-colors mb-8">
                            Choose Pro
                        </a>
                        <ul class="space-y-4 text-gray-600 flex-grow">
                            <li class="flex items-start"><svg class="h-5 w-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> <strong>Unlimited Users</strong></li>
                            <li class="flex items-start"><svg class="h-5 w-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> Advanced Invoicing</li>
                            <li class="flex items-start"><svg class="h-5 w-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> Unlimited Clients</li>
                            <li class="flex items-start"><svg class="h-5 w-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> Advanced Reports</li>
                        </ul>
                    </div>
                </div>
            </div>        
        </div>
    </body>
</html>