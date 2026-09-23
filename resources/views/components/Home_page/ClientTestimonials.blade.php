{{-- resources/views/components/Home_page/ClientTestimonials.blade.php --}}

<section id="demo-banner" class="py-16 sm:py-24 bg-[#f8fafc]">
    <div class="container mx-auto px-4 sm:px-6">
        
        {{-- PART 1: VIDEO DEMO BANNER --}}
        <div class="max-w-5xl mx-auto rounded-3xl bg-slate-50 border border-slate-200/90 p-6 sm:p-10 shadow-xs">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                
                {{-- Left Graphic / Play Media Card --}}
                <div class="lg:col-span-5 h-48 sm:h-56 rounded-2xl bg-teal-900/10 border border-teal-800/20 p-6 flex flex-col items-center justify-center text-center relative overflow-hidden group">
                    <div class="w-12 h-12 rounded-full bg-[#1e535f] text-white flex items-center justify-center shadow-md group-hover:scale-110 transition-transform">
                        <svg class="w-5 h-5 ml-0.5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M8 5v14l11-7z"/>
                        </svg>
                    </div>
                    <span class="mt-3 text-xs font-bold text-slate-700">DalyTrac en action</span>
                    <span class="text-[10px] text-slate-400">Vidéo de présentation (2 min)</span>
                </div>

                {{-- Right Content --}}
                <div class="lg:col-span-7 space-y-4">
                    <span class="text-xs font-bold tracking-wider text-amber-600 uppercase">DÉMONSTRATION EN VIDÉO</span>
                    <h3 class="text-xl sm:text-3xl font-extrabold text-slate-900 leading-tight">
                        Suivez le parcours complet, <span class="text-[#1e535f]">de la création du projet aux premières données.</span>
                    </h3>
                    <p class="text-slate-600 text-sm sm:text-base leading-relaxed">
                        Découvrez en quelques minutes comment DalyTrac simplifie la gestion de vos projets et accélère votre facturation.
                    </p>
                    <div class="pt-2 flex flex-wrap items-center gap-4">
                        <a href="{{ route('demo') }}" 
                           class="rounded-full bg-[#1e535f] hover:bg-[#163f47] text-white font-medium px-6 py-3 text-sm transition-all shadow-sm">
                            Voir la démonstration
                        </a>
                        <a href="#features" class="text-sm font-semibold text-[#1e535f] hover:underline flex items-center gap-1">
                            <span>En savoir plus</span>
                            <span>→</span>
                        </a>
                    </div>
                </div>

            </div>
        </div>

        {{-- PART 2: FINAL BANNER CTA --}}
        <div class="max-w-5xl mx-auto mt-16 sm:mt-24 rounded-3xl bg-[#0f2830] text-white p-8 sm:p-14 text-center shadow-2xl">
            <div class="max-w-2xl mx-auto space-y-4">
                <h2 class="text-2xl sm:text-4xl font-extrabold text-white leading-tight">
                    Choisissez un projet. <br class="hidden sm:inline"/>Obtenez une première vision claire.
                </h2>
                <p class="text-slate-300 text-sm sm:text-base leading-relaxed">
                    Rejoignez les entreprises de services qui piloteront leurs marges avec DalyTrac.
                </p>
                <div class="pt-4">
                    <a href="{{ route('register') }}" 
                       class="inline-block rounded-full bg-[#f59e0b] hover:bg-amber-400 text-slate-900 font-extrabold px-8 py-4 text-sm sm:text-base transition-all shadow-lg hover:scale-105">
                        Essayer gratuitement
                    </a>
                </div>
            </div>
        </div>

    </div>
</section>
