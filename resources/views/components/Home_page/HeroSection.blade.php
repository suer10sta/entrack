{{-- resources/views/components/Home_page/HeroSection.blade.php --}}

<section id="hero" class="relative overflow-hidden pt-6 sm:pt-8 lg:pt-10 pb-12 lg:pb-16 bg-[#f8fafc]">
    
    {{-- Subtle background decoration --}}
    <div class="absolute inset-0 pointer-events-none">
        <div class="absolute -top-40 left-1/2 -translate-x-1/2 w-[1000px] h-[600px] bg-gradient-to-b from-slate-100/80 via-slate-50/50 to-transparent rounded-full blur-3xl opacity-70"></div>
    </div>

    <div class="container relative z-10 mx-auto px-4 sm:px-6">
        <div class="mx-auto max-w-3xl text-center">
            
            {{-- Eyebrow Tag --}}
            <div class="inline-flex items-center gap-2 text-xs sm:text-sm font-bold tracking-wider text-slate-900 uppercase mb-3">
                <span class="w-4 h-0.5 bg-amber-500 rounded-full inline-block"></span>
                <span>Fonctionnalités DalyTrac</span>
            </div>

            {{-- Main Heading --}}
            <h1 class="text-2xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight text-slate-900 leading-[1.15]">
                Tout ce qu’il faut pour suivre un projet, <span class="text-[#1e535f]">du devis jusqu’à la marge.</span>
            </h1>

            {{-- Subheading --}}
            <p class="mx-auto mt-4 max-w-xl text-sm sm:text-base leading-relaxed text-slate-600 font-normal">
                DalyTrac réunit les outils nécessaires aux entreprises de services. Découvrez comment chaque fonctionnalité relie vos projets, vos équipes et votre facturation.
            </p>

            {{-- CTA Buttons --}}
            <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="{{ route('register') }}" 
                   class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-full bg-[#1e535f] hover:bg-[#163f47] text-white font-medium text-sm sm:text-base px-7 py-3.5 shadow-md transition-all hover:scale-[1.02]">
                    <span>Tester toutes les fonctionnalités</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 17L17 7M17 7H7M17 7V17"/>
                    </svg>
                </a>

                <a href="#features" 
                   class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-full bg-white border border-slate-200 text-slate-800 font-medium text-sm sm:text-base px-7 py-3.5 shadow-sm hover:bg-slate-50 transition-all hover:scale-[1.02]">
                    <span>Découvrir les fonctionnalités</span>
                    <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                    </svg>
                </a>
            </div>

            {{-- Trust Indicators Badges --}}
            <div class="mt-6 flex flex-wrap items-center justify-center gap-4 sm:gap-6 text-xs sm:text-sm font-medium text-slate-500">
                <span class="flex items-center gap-1.5">
                    <span class="font-semibold text-slate-700">15</span> jours d’essai
                </span>
                <span class="text-slate-300">•</span>
                <span class="flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>Toutes les fonctionnalités</span>
                </span>
                <span class="text-slate-300">•</span>
                <span class="flex items-center gap-1.5">
                    <span class="text-base leading-none font-bold text-slate-700">∞</span>
                    <span>Utilisateurs illimités</span>
                </span>
            </div>

            {{-- Browser / App Showcase Frame --}}
            <div class="mt-12 sm:mt-16 max-w-5xl mx-auto rounded-2xl sm:rounded-3xl border border-slate-200/90 shadow-2xl bg-white overflow-hidden text-left">
                
                {{-- Browser Header Bar --}}
                <div class="flex items-center justify-between px-4 py-3 bg-slate-50 border-b border-slate-200/80">
                    <div class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-slate-300 inline-block"></span>
                        <span class="w-2.5 h-2.5 rounded-full bg-slate-300 inline-block"></span>
                        <span class="w-2.5 h-2.5 rounded-full bg-slate-300 inline-block"></span>
                    </div>
                    <div class="text-[11px] sm:text-xs text-slate-400 font-mono">
                        app.dalytrac.com - Vue projet
                    </div>
                </div>

                {{-- Mockup Window Content Body --}}
                <div class="min-h-[360px] sm:min-h-[420px] flex bg-slate-50/40">
                    
                    {{-- Left Dark Sidebar --}}
                    <div class="w-14 sm:w-16 bg-[#0f172a] p-3 flex flex-col items-center gap-4 flex-shrink-0">
                        <div class="w-8 h-8 rounded-xl bg-[#1e535f] text-white font-extrabold text-sm flex items-center justify-center shadow-sm">
                            D
                        </div>
                        <div class="w-6 h-1 rounded-full bg-slate-700/60 mt-2"></div>
                        <div class="w-6 h-1 rounded-full bg-slate-700/60"></div>
                        <div class="w-6 h-1 rounded-full bg-slate-700/60"></div>
                        <div class="w-6 h-1 rounded-full bg-slate-700/60"></div>
                        <div class="w-6 h-1 rounded-full bg-slate-700/60"></div>
                        <div class="w-6 h-1 rounded-full bg-slate-700/60"></div>
                    </div>

                    {{-- Main Content Area --}}
                    <div class="flex-1 p-4 sm:p-6 flex flex-col gap-4 overflow-hidden">
                        
                        {{-- Top Placeholders Row --}}
                        <div class="flex items-center justify-between">
                            <div class="space-y-1.5">
                                <div class="w-28 sm:w-36 h-2.5 rounded-full bg-slate-200"></div>
                                <div class="w-16 sm:w-20 h-2 rounded-full bg-slate-200/70"></div>
                            </div>
                            <div class="w-16 sm:w-20 h-6 sm:h-7 rounded-lg bg-teal-100/60"></div>
                        </div>

                        {{-- Stats Cards Placeholders --}}
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                            <div class="h-14 sm:h-16 rounded-xl border border-slate-200/60 bg-white p-3 shadow-2xs"></div>
                            <div class="h-14 sm:h-16 rounded-xl border border-slate-200/60 bg-white p-3 shadow-2xs"></div>
                            <div class="h-14 sm:h-16 rounded-xl border border-slate-200/60 bg-white p-3 shadow-2xs"></div>
                            <div class="h-14 sm:h-16 rounded-xl border border-slate-200/60 bg-white p-3 shadow-2xs"></div>
                        </div>

                        {{-- Central Showcase Banner --}}
                        <div class="flex-1 min-h-[200px] border-2 border-dashed border-slate-200/90 rounded-2xl p-6 sm:p-8 flex flex-col items-center justify-center text-center bg-white/90">
                            <span class="text-amber-600 font-bold text-[10px] sm:text-xs tracking-wider uppercase mb-1.5">
                                Visuel Produit 01 · Vue Générale
                            </span>
                            <h3 class="text-slate-900 font-bold text-base sm:text-xl mb-2">
                                Capture réelle du tableau de bord DalyTrac
                            </h3>
                            <p class="text-slate-500 text-xs sm:text-sm max-w-md mx-auto leading-relaxed">
                                Utiliser un projet anonymisé pour montrer les tâches, le temps consommé, le budget et la facturation dans une même vue.
                            </p>
                        </div>
                    </div>

                    {{-- Right Side Cards (Hidden on small screens) --}}
                    <div class="hidden md:flex flex-col gap-3 w-44 sm:w-52 p-4 border-l border-slate-200/60 bg-white/60 flex-shrink-0">
                        <div class="h-20 rounded-xl bg-teal-500/10 border border-teal-500/20"></div>
                        <div class="h-10 rounded-xl bg-slate-100"></div>
                        <div class="h-10 rounded-xl bg-slate-100"></div>
                        <div class="h-10 rounded-xl bg-slate-100"></div>
                    </div>
                </div>

                {{-- Bottom Sub-navigation Tabs --}}
                <div class="flex items-center justify-center gap-4 sm:gap-8 border-t border-slate-200/80 bg-white px-4 sm:px-6 py-3.5 text-xs sm:text-sm font-medium text-slate-500 overflow-x-auto">
                    <span class="relative text-slate-900 font-semibold py-1 after:absolute after:-bottom-3.5 after:left-0 after:right-0 after:h-0.5 after:bg-amber-500 whitespace-nowrap">
                        Projets
                    </span>
                    <span class="hover:text-slate-900 transition-colors whitespace-nowrap cursor-pointer">Tâches</span>
                    <span class="hover:text-slate-900 transition-colors whitespace-nowrap cursor-pointer">Temps</span>
                    <span class="hover:text-slate-900 transition-colors whitespace-nowrap cursor-pointer">Budgets</span>
                    <span class="hover:text-slate-900 transition-colors whitespace-nowrap cursor-pointer">Facturation</span>
                    <span class="hover:text-slate-900 transition-colors whitespace-nowrap cursor-pointer">Tableaux de bord</span>
                </div>
            </div>

        </div>
    </div>
</section>
