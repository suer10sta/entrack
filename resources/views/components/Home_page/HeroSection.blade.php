{{-- resources/views/components/hero-section.blade.php --}}
@php
    $theme = session('theme', 'teal');
@endphp

<section class="relative overflow-hidden
    @if($theme === 'blue') 
        bg-gradient-to-b from-primary/5 to-background
    @else
        bg-gradient-to-br from-primary via-primary to-[#0d9488]
    @endif
">
    {{-- Background Decorations --}}
    @if($theme !== 'blue')
        <div class="absolute inset-0 z-0">
            <svg class="absolute bottom-0 right-0 h-full w-full" viewBox="0 0 1440 800" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
                <path d="M800 0C600 200 400 400 300 600C200 800 0 800 0 800V0H800Z" fill="white"/>
            </svg>
        </div>
        <div class="absolute left-10 top-20 z-0 h-64 w-64 rounded-full bg-white/5 blur-3xl"></div>
        <div class="absolute right-20 top-40 z-0 h-96 w-96 rounded-full bg-accent/10 blur-3xl"></div>
    @endif

    {{-- Content --}}
    <div class="container relative z-20 px-6 py-24 lg:py-32">
        <div class="grid items-center gap-12 lg:grid-cols-2 lg:gap-16">

            {{-- Left Column --}}
            <div class="space-y-8 animate-fade-in-left" data-visible="true">
                {{-- Theme-based Badge --}}
                @if($theme === 'blue')
                    <div class="inline-flex items-center gap-2 rounded-full border border-primary/20 bg-primary/10 px-4 py-2 text-sm font-medium text-primary transition-all duration-300 hover:scale-105 hover:border-primary/40 hover:bg-primary/20">
                        <span class="relative flex h-2 w-2">
                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-primary opacity-75"></span>
                            <span class="relative inline-flex h-2 w-2 rounded-full bg-primary"></span>
                        </span>
                        New: Automated Reports
                    </div>
                @endif

                {{-- Heading & Subtext --}}
                <h1 class="text-balance text-5xl font-bold leading-tight lg:text-6xl xl:text-7xl
                    @if($theme === 'blue') text-foreground @else text-white @endif">
                    @if($theme === 'blue')
                        Manage your business with 
                        <span class="bg-gradient-to-r from-primary to-primary/60 bg-clip-text text-transparent">simplicity</span>
                    @else
                        Manage your invoices and business <span class="text-accent">in one click</span>
                    @endif
                </h1>
                <p class="text-pretty text-lg leading-relaxed lg:text-xl
                    @if($theme === 'blue') text-muted-foreground @else text-white/90 @endif">
                    @if($theme === 'blue')
                        A complete platform for invoicing, client management, and project tracking. Everything you need in one place.
                    @else
                        Simplify your accounting, track your clients, and automate your tasks. A complete solution for modern business management.
                    @endif
                </p>

                {{-- Buttons --}}
                <div class="flex flex-col gap-4 sm:flex-row">
                    <a href="{{ route('login') }}" class="group inline-flex items-center justify-center gap-2 rounded-lg px-8 py-4 text-base font-semibold shadow-lg
                        @if($theme === 'blue')
                            bg-primary text-primary-foreground
                        @else
                            bg-accent rounded-full text-accent-foreground
                        @endif
                        transition-all duration-300
                    ">
                        Get Started Free
                        <svg class="h-5 w-5 transition-transform duration-300 group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </a>

                    <button class="inline-flex items-center justify-center gap-2 rounded-lg border-2 px-8 py-4 text-base font-semibold transition-all duration-300 shadow-lg
                        @if($theme === 'blue')
                            border-border bg-background text-foreground hover:border-primary hover:bg-secondary
                        @else
                            border-white bg-transparent text-white hover:bg-white hover:text-primary
                        @endif
                    ">
                        @if($theme === 'blue') View Demo @else Learn More @endif
                    </button>
                </div>

                {{-- Stats --}}
                <div class="flex flex-wrap gap-8 pt-4 @if($theme === 'blue') border-t border-border pt-8 @endif">
                    <div class="transition-transform duration-300 hover:scale-110">
                        <div class="text-3xl font-bold @if($theme === 'blue') text-foreground @else text-white @endif">
                            @if($theme === 'blue') 2,500+ @else 500+ @endif
                        </div>
                        <div class="text-sm @if($theme === 'blue') text-muted-foreground @else text-white/80 @endif">
                            @if($theme === 'blue') Active Companies @else Companies @endif
                        </div>
                    </div>
                    <div class="transition-transform duration-300 hover:scale-110">
                        <div class="text-3xl font-bold @if($theme === 'blue') text-foreground @else text-white @endif">
                            @if($theme === 'blue') 98% @else 50K+ @endif
                        </div>
                        <div class="text-sm @if($theme === 'blue') text-muted-foreground @else text-white/80 @endif">
                            @if($theme === 'blue') Satisfaction Rate @else Invoices Created @endif
                        </div>
                    </div>
                    <div class="transition-transform duration-300 hover:scale-110">
                        <div class="text-3xl font-bold @if($theme === 'blue') text-foreground @else text-white @endif">24/7</div>
                        <div class="text-sm @if($theme === 'blue') text-muted-foreground @else text-white/80 @endif">Support Available</div>
                    </div>
                </div>
            </div>

            {{-- Right Column (Visuals) --}}
            <div class="relative flex justify-center animate-fade-in-right" data-visible="true" style="animation-delay: 300ms;">
                <div class="relative w-full max-w-lg">
                    @if($theme === 'blue')
                        <div class="absolute -left-4 -top-4 h-72 w-72 rounded-full bg-primary/10 blur-3xl"></div>
                        <div class="absolute -bottom-4 -right-4 h-72 w-72 rounded-full bg-accent/10 blur-3xl"></div>
                        {{-- Example cards for blue theme --}}
                        <div class="relative space-y-4">
                            <!-- Card 1 -->
                            <div class="hover-lift rounded-2xl border border-border bg-card p-6 shadow-xl">
                                <div class="mb-4 flex items-center justify-between">
                                    <div class="text-sm font-medium text-muted-foreground">Monthly Revenue</div>
                                    <div class="animate-pulse-subtle rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">+24%</div>
                                </div>
                                <div class="text-3xl font-bold text-foreground">$45,280</div>
                                <div class="mt-4 h-2 w-full rounded-full bg-secondary">
                                    <div class="animate-progress h-2 w-3/4 rounded-full bg-primary"></div>
                                </div>
                            </div>
                        </div>
                    @else
                        {{-- Non-blue theme visuals --}}
                        <div class="hover-lift relative rounded-3xl bg-white p-6 shadow-2xl">
                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <div class="h-3 w-32 rounded-full bg-gray-200"></div>
                                    <div class="h-8 w-8 rounded-full bg-primary"></div>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="rounded-xl bg-gradient-to-br from-primary to-[#0d9488] p-4">
                                        <div class="h-2 w-16 rounded bg-white/50"></div>
                                        <div class="mt-2 h-6 w-20 rounded bg-white"></div>
                                    </div>
                                    <div class="rounded-xl bg-gradient-to-br from-accent to-[#f59e0b] p-4">
                                        <div class="h-2 w-16 rounded bg-white/50"></div>
                                        <div class="mt-2 h-6 w-20 rounded bg-white"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</section>

{{-- Animations --}}
<style>
@keyframes fadeInLeft { from { opacity: 0; transform: translateX(-40px); } to { opacity: 1; transform: translateX(0); } }
@keyframes fadeInRight { from { opacity: 0; transform: translateX(40px); } to { opacity: 1; transform: translateX(0); } }
@keyframes pulseSubtle { 0%, 100% { opacity: 1; } 50% { opacity: 0.8; } }
@keyframes progress { from { width: 0%; } to { width: 75%; } }
@keyframes hoverLift { from { transform: translateY(0); } to { transform: translateY(-4px); } }

[data-visible="true"].animate-fade-in-left { animation: fadeInLeft 1s ease-out; }
[data-visible="true"].animate-fade-in-right { animation: fadeInRight 1s ease-out; }
.animate-pulse-subtle { animation: pulseSubtle 2s ease-in-out infinite; }
.animate-progress { animation: progress 2s ease-out; }
.hover-lift:hover { animation: hoverLift 0.3s ease-out forwards; }
</style>
