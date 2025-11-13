<section id="account_type" class="py-24 lg:py-32">    
<div class="container px-6">
    
    {{-- 1. Global Trial Banner --}}
    <div class="text-center mb-12 space-y-4">
        <span class="inline-flex items-center gap-2 rounded-full bg-primary/10 px-4 py-1.5 text-sm font-bold text-primary ring-1 ring-primary/20">
            <span class="relative flex h-2.5 w-2.5">
              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary opacity-75"></span>
              <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-primary"></span>
            </span>
            15-Day Free Trial
        </span>
        <h3 class="text-3xl lg:text-4xl font-bold text-foreground">
            One Platform, Three Plans
        </h3>
        <p class="mx-auto max-w-2xl text-lg text-muted-foreground">
            Enjoy <strong>every feature</strong> regardless of your plan. Simply choose the number of users you need.
        </p>
    </div>

    {{-- 2. The Three Account Types --}}
    <div class="grid gap-6 lg:grid-cols-3 lg:gap-8 items-stretch">
        @php
            $accounts = [
                [
                    'name' => 'Solo',
                    'users' => '1',
                    'users_label' => 'User',
                    'desc' => 'For freelancers and solopreneurs.',
                    'highlight' => false
                ],
                [
                    'name' => 'Premium',
                    'users' => '1 - 10',
                    'users_label' => 'Users',
                    'desc' => 'For startups and small teams.',
                    'highlight' => true
                ],
                [
                    'name' => 'Professionnel',
                    'users' => 'Unlimited',
                    'users_label' => 'Users',
                    'desc' => 'For scaling companies.',
                    'highlight' => false
                ]
            ];
        @endphp

        @foreach($accounts as $account)
            <div class="group relative flex flex-col justify-between rounded-xl border {{ $account['highlight'] ? 'border-primary bg-card shadow-xl shadow-primary/10 ring-1 ring-primary scale-105 z-10' : 'border-border bg-card/40 hover:border-primary/50 hover:bg-card' }} p-8 transition-all duration-300">
                
                @if($account['highlight'])
                    <div class="absolute -top-4 left-1/2 -translate-x-1/2 rounded-full bg-primary px-4 py-1 text-xs font-bold text-primary-foreground uppercase tracking-wide shadow-sm">
                        Recommended
                    </div>
                @endif

                <div>
                    {{-- Plan Name --}}
                    <h4 class="text-lg font-semibold text-muted-foreground uppercase tracking-wider mb-2">
                        {{ $account['name'] }}
                    </h4>

                    {{-- The Big Differentiator: User Count --}}
                    <div class="my-6">
                        <span class="text-5xl font-extrabold text-foreground tracking-tight block">
                            {{ $account['users'] }}
                        </span>
                        <span class="text-lg font-medium text-primary block mt-1">
                            {{ $account['users_label'] }}
                        </span>
                    </div>

                    {{-- Description --}}
                    <p class="text-sm text-muted-foreground mb-6 border-b border-border pb-6">
                        {{ $account['desc'] }}
                    </p>

                    {{-- Common Feature Check --}}
                    <div class="flex items-center gap-3 mb-8">
                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-green-500/10 text-green-600">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <span class="text-sm font-medium text-foreground">
                            Access 100% of the features
                        </span>
                    </div>
                </div>

                {{-- Action Button --}}
                <a href="{{ route('login') }}" class="block text-center w-full rounded-lg py-3 text-sm font-bold transition-all {{ $account['highlight'] ? 'bg-primary text-primary-foreground hover:bg-primary/90 shadow-lg shadow-primary/20' : 'bg-secondary text-secondary-foreground hover:bg-secondary/80' }}">
                    Start Your Trial
                </a>
            </div>
        @endforeach
    </div>
</div>
</section>