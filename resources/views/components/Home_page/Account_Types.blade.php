<section id="account_type" class="py-24 lg:py-32 bg-gray-50">    
<div class="container px-6 mx-auto max-w-7xl">
    
    {{-- 1. Global Trial Banner --}}
    <div class="text-center mb-8 space-y-4">
        <span class="inline-flex items-center gap-2 rounded-full bg-primary/10 px-4 py-1.5 text-sm font-bold text-primary ring-1 ring-primary/20">
            <span class="relative flex h-2.5 w-2.5">
              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary opacity-75"></span>
              <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-primary"></span>
            </span>
            {{ __('messages.15-Day Free Trial') }}
        </span>
        <h3 class="text-3xl lg:text-4xl font-bold text-foreground">
            {{ __('messages.One Platform, Three Plans') }}
        </h3>
        <p class="mx-auto max-w-2xl text-lg text-muted-foreground">
            {{ __('messages.Enjoy') }} <strong>{{ __('messages.every feature') }}</strong> {{ __('messages.regardless of your plan. Simply choose the number of users you need.') }}
        </p>
    </div>

    {{-- 2. The Three Account Types --}}
    <div class="grid gap-6 lg:grid-cols-3 lg:gap-6 items-start">
        @php
            $accounts = [
                [
                    'name' => 'Solo',
                    'users' => '1',
                    'users_label' => 'User',
                    'desc' => 'For freelancers and solopreneurs.',
                    'price' => '€19',
                    'badge' => '1 User Maximum',
                    'highlight' => false,
                    'features' => [
                        'Advanced Invoicing',
                        'Unlimited Clients',
                        'Advanced Reports',
                    ],
                ],
                [
                    'name' => 'Premium',
                    'users' => '1 - 10',
                    'users_label' => 'Users',
                    'desc' => 'For startups and small teams.',
                    'price' => '€49',
                    'badge' => 'Up to 10 Users',
                    'highlight' => true,
                    'features' => [
                        'Advanced Invoicing',
                        'Unlimited Clients',
                        'Advanced Reports',
                    ],
                ],
                [
                    'name' => 'Pro',
                    'users' => 'Unlimited',
                    'users_label' => 'Users',
                    'desc' => 'For scaling companies.',
                    'price' => '€99',
                    'badge' => 'Unlimited Users',
                    'highlight' => false,
                    'features' => [
                        'Advanced Invoicing',
                        'Unlimited Clients',
                        'Advanced Reports',
                    ],
                ],
            ];
        @endphp

        @foreach($accounts as $account)
            <div class="relative flex flex-col rounded-2xl {{ $account['highlight'] ? 'border-4 border-yellow-400 bg-white shadow-xl' : 'border border-gray-200 bg-white shadow-sm' }} p-8 transition-all duration-300">
                
                @if($account['highlight'])
                    <div class="absolute -top-4 left-1/2 -translate-x-1/2 rounded-full bg-yellow-400 px-6 py-1.5 text-xs font-bold text-gray-900 uppercase tracking-wider shadow-md">
                        {{ __('messages.Recommended') }}
                    </div>
                @endif

                <div class="flex-1">
                    {{-- Plan Name --}}
                    <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-[0.2em] mb-6">
                        {{ __('messages.' . $account['name']) }}
                    </h4>

                    {{-- The Big Differentiator: User Count --}}
                    <div class="mb-4">
                        <span class="text-5xl font-black text-gray-900 tracking-tight block leading-none">
                            {{ $account['users'] }}
                        </span>
                        <span class="text-base font-medium text-gray-600 block mt-2">
                            {{ __('messages.' . $account['users_label']) }}
                        </span>
                    </div>

                    {{-- Description --}}
                    <p class="text-sm text-gray-600 mb-6">
                        {{ __('messages.' . $account['desc']) }}
                    </p>

                    {{-- Badge --}}
                    <div class="inline-flex items-center gap-2 rounded-md bg-green-50 border border-green-200 px-4 py-2 text-sm font-semibold text-green-700 mb-8">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        {{ __('messages.' . $account['badge']) }}
                    </div>

                    {{-- Price --}}
                    <div class="mb-8">
                        <div class="flex items-baseline gap-1">
                            <span class="text-4xl font-black text-gray-900">{{ $account['price'] }}</span>
                            <span class="text-gray-500 text-sm font-medium">{{ __('messages./per month') }}</span>
                        </div>
                    </div>

                    {{-- Action Button --}}
                    <a href="{{ route('register') }}" class="block text-center w-full rounded-lg py-3.5 text-sm font-bold transition-all {{ $account['highlight'] ? 'bg-yellow-400 text-gray-900 hover:bg-yellow-500' : 'bg-gray-700 text-white hover:bg-gray-800' }}">
                        {{ __('messages.Start Your Trial') }}
                    </a>

                    {{-- Feature list --}}
                    <div class="mt-10 pt-6 border-t border-gray-200">
                        <h5 class="text-xs font-semibold text-gray-900 mb-4">{{ __('messages.All plans include:') }}</h5>
                        <ul class="space-y-3">
                            @foreach($account['features'] as $feature)
                                <li class="flex items-center gap-3 text-sm text-gray-700">
                                    <span class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </span>
                                    <span class="font-medium">{{ __('messages.' . $feature) }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
</section>
