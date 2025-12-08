{{-- resources/views/components/hero-section.blade.php --}}

{{-- UPDATE: Reduced top padding --}}
<section id="hero" class="relative overflow-hidden pt-12 pb-24 lg:pt-16 lg:pb-32
        bg-gradient-to-b from-primary/5 to-background">
    {{-- Background Decorations --}}
        <div class="absolute inset-0 z-0">
            <svg class="absolute bottom-0 right-0 h-full w-full" viewBox="0 0 1440 800" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
                <path d="M800 0C600 200 400 400 300 600C200 800 0 800 0 800V0H800Z" fill="white"/>
            </svg>
        </div>
        <div class="absolute left-10 top-20 z-0 h-64 w-64 rounded-full bg-white/5 blur-3xl"></div>
        <div class="absolute right-20 top-40 z-0 h-96 w-96 rounded-full bg-accent/10 blur-3xl"></div>
    

    {{-- Content --}}
    <div class="container relative z-20 px-6">
        <div class="grid items-center gap-12 lg:grid-cols-2 lg:gap-16">

            {{-- Left Column --}}
            <div class="space-y-8 animate-fade-in-left" data-visible="true">
                

                {{-- Heading & subtext (matching the design) --}}
                <h1 class="text-balance text-5xl font-bold leading-tight lg:text-6xl xl:text-7xlf">
                        Manage your business with 
                        <span class="bg-gradient-to-r from-primary to-primary/60 bg-clip-text text-transparent">simplicity</span>
                </h1>
                <p class="text-pretty text-lg leading-relaxed lg:text-xl">
                        A complete platform for invoicing, client management, and project tracking. Everything you need in one place.
                </p>

                {{-- Buttons --}}
                <div class="flex flex-col gap-4 sm:flex-row">
                    <a href="{{ route('login') }}" class="group inline-flex items-center justify-center gap-2 rounded-lg px-8 py-4 text-base font-semibold shadow-lg
                            bg-[#0EA5E9] text-primary-foreground">
                        Get Started Free
                        <svg class="h-5 w-5 transition-transform duration-300 group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </a>

                    <button class="inline-flex items-center justify-center gap-2 rounded-lg px-8 py-4 text-base font-semibold transition-all duration-300 shadow-lg
                            bg-card text-foreground hover:bg-secondary">
                         View Demo 
                    </button>
                </div>

                {{-- Stats section removed --}}
            </div>

            {{-- Right Column (Visuals) --}}
            <div class="relative flex justify-center animate-fade-in-right" data-visible="true" style="animation-delay: 300ms;">
                <div class="relative w-full max-w-lg">
                        <div class="absolute -left-4 -top-4 h-72 w-72 rounded-full bg-primary/10 blur-3xl"></div>
                        <div class="absolute -bottom-4 -right-4 h-72 w-72 rounded-full bg-accent/10 blur-3xl"></div>
                        
                        <div class="relative space-y-4">
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
                            
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                
                                <div class="hover-lift rounded-2xl border border-border bg-card p-6 shadow-xl">
                                    {{-- UPDATE: Heroicons SVG for "Clients" --}}
                                    <div class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10 text-primary">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-6 w-6">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h6M9 10.5h6M9 14.25h6M9 18h6" />
                                        </svg>
                                    </div>
                                    <div class="mt-4 text-3xl font-bold text-foreground">156</div>
                                    <div class="text-sm font-medium text-muted-foreground">Active Clients</div>
                                </div>
                                
                                <div class="hover-lift rounded-2xl border border-border bg-card p-6 shadow-xl">
                                    {{-- UPDATE: Heroicons SVG for "Invoices" --}}
                                    <div class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10 text-primary">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-6 w-6">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A3.375 3.375 0 0 0 11.25 8.25v2.625m9 0v-4.125c0-.621-.504-1.125-1.125-1.125H11.25c-.621 0-1.125.504-1.125 1.125v4.125m9 0a2.25 2.25 0 0 1-2.25 2.25h-1.5a2.25 2.25 0 0 1-2.25-2.25m3.75 0v.007c0 .014-.002.028-.005.042l-.006.018-.01.028-.015.041-.02.05-.026.06a.744.744 0 0 1-.039.079l-.045.085-.05.088a.752.752 0 0 1-.06.102l-.062.102c-.03.048-.062.092-.096.132l-.098.115-.102.112a.75.75 0 0 1-.112.108l-.113.103-.115.1-.118.093-.12.083-.123.078-.125.07-.128.06-.13.052-.133.044-.135.035-.138.026-.14.016-.143.007-.145-.001H12.75a.75.75 0 0 1-.75-.75V6.375c0-.621.504-1.125 1.125-1.125h6.75c.621 0 1.125.504 1.125 1.125v6.375m-6.375 4.125a.75.75 0 0 1-.75-.75V11.25a.75.75 0 0 1 .75-.75h3.75a.75.75 0 0 1 .75.75v3.75a.75.75 0 0 1-.75.75h-3.75Z" />
                                        </svg>
                                    </div>
                                    <div class="mt-4 text-3xl font-bold text-foreground">89</div>
                                    <div class="text-sm font-medium text-muted-foreground">Invoices This Month</div>
                                </div>
                            </div>

                        </div>
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
