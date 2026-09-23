{{-- resources/views/components/Home_page/FeaturesSection.blade.php --}}

<section id="features" class="py-16 sm:py-24 bg-[#f0f7f9]">
    <div class="container mx-auto px-4 sm:px-6">
        
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-start">
            
            {{-- Left Sticky Navigation --}}
            <div class="lg:col-span-4 lg:sticky lg:top-28">
                <div class="space-y-5">
                    <div class="inline-flex items-center gap-2.5 text-xs font-bold tracking-wider text-slate-800 uppercase">
                        <span class="w-5 h-0.5 bg-amber-500 rounded-full inline-block"></span>
                        <span>Découvrir DalyTrac</span>
                    </div>

                    <h2 class="text-2xl sm:text-[2rem] font-extrabold text-slate-900 leading-tight">
                        Toutes les fonctionnalités, en détail.
                    </h2>

                    <nav class="pt-2 flex flex-col gap-0.5 font-medium text-sm text-slate-400">
                        <a href="#feature-01" class="feature-nav-link active flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all text-slate-900 font-semibold">
                            <span class="text-xs font-semibold text-slate-400 w-5">01</span>
                            <span>Clients et projets</span>
                        </a>
                        <a href="#feature-02" class="feature-nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg hover:text-slate-700 transition-all">
                            <span class="text-xs font-semibold text-slate-400 w-5">02</span>
                            <span>Tâches et équipe</span>
                        </a>
                        <a href="#feature-03" class="feature-nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg hover:text-slate-700 transition-all">
                            <span class="text-xs font-semibold text-slate-400 w-5">03</span>
                            <span>Temps et planning</span>
                        </a>
                        <a href="#feature-04" class="feature-nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg hover:text-slate-700 transition-all">
                            <span class="text-xs font-semibold text-slate-400 w-5">04</span>
                            <span>Budgets et dépenses</span>
                        </a>
                        <a href="#feature-05" class="feature-nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg hover:text-slate-700 transition-all">
                            <span class="text-xs font-semibold text-slate-400 w-5">05</span>
                            <span>Devis et factures</span>
                        </a>
                        <a href="#feature-06" class="feature-nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg hover:text-slate-700 transition-all">
                            <span class="text-xs font-semibold text-slate-400 w-5">06</span>
                            <span>Tableaux de bord</span>
                        </a>
                    </nav>
                </div>
            </div>

            {{-- Right Stack of Feature Cards --}}
            <div class="lg:col-span-8 space-y-10 sm:space-y-14">
                
                {{-- CARD 01: Clients et projets --}}
                <div id="feature-01" class="rounded-3xl bg-white border border-slate-200/70 p-6 sm:p-10 space-y-8 shadow-sm overflow-hidden">
                    <div class="space-y-4">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-[#1e535f]/10 text-[#1e535f] font-bold flex items-center justify-center text-sm shrink-0">
                                01
                            </div>
                            <span class="text-xs font-bold tracking-widest text-[#1e535f] uppercase">Clients et projets</span>
                        </div>
                        <h3 class="text-2xl sm:text-[1.75rem] lg:text-3xl font-bold text-slate-900 leading-snug">
                            Centralisez chaque mission dans un espace de travail structuré.
                        </h3>
                        <p class="text-slate-500 text-sm sm:text-[0.94rem] leading-relaxed max-w-xl">
                            Créez le client, le projet et ses sous-projets, puis réunissez les informations dont l'équipe a besoin pour avancer : périmètre, budget, responsables, échéances et documents.
                        </p>
                    </div>

                    {{-- Visual Showcase Area --}}
                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-4">
                        <div class="sm:col-span-8 border-2 border-dashed border-slate-200 rounded-2xl p-6 sm:p-8 flex flex-col items-center justify-center text-center min-h-[220px]">
                            <span class="text-slate-400 font-semibold text-[10px] sm:text-xs tracking-wider uppercase mb-2">
                                Visuel Produit 02
                            </span>
                            <h4 class="text-slate-900 font-bold text-sm sm:text-base mb-1.5">
                                Fiche projet complète
                            </h4>
                            <p class="text-slate-400 text-xs sm:text-sm max-w-sm leading-relaxed">
                                Capture à fournir : informations client, sous-projets, budget, équipe et échéances.
                            </p>
                        </div>
                        <div class="sm:col-span-4 flex flex-col gap-3">
                            <div class="flex-1 min-h-[60px] rounded-xl bg-white border border-slate-200/60 shadow-sm"></div>
                            <div class="flex-1 min-h-[60px] rounded-xl bg-white border border-slate-200/60 shadow-sm"></div>
                            <div class="flex-1 min-h-[60px] rounded-xl bg-white border border-slate-200/60 shadow-sm"></div>
                        </div>
                    </div>

                    {{-- Bottom 3-Column Highlights Bar --}}
                    <div class="-mx-6 -mb-6 sm:-mx-10 sm:-mb-10 mt-8 border-t border-slate-200/80 grid grid-cols-1 md:grid-cols-3 divide-y md:divide-y-0 md:divide-x divide-slate-200/80 bg-white rounded-b-3xl">
                        <div class="p-6 sm:p-7 space-y-2">
                            <h5 class="text-xs sm:text-sm font-extrabold tracking-wider text-[#1e535f] uppercase">
                                Structurez
                            </h5>
                            <p class="text-slate-600 text-xs sm:text-sm leading-relaxed">
                                les projets complexes sans perdre la vision globale.
                            </p>
                        </div>
                        <div class="p-6 sm:p-7 space-y-2">
                            <h5 class="text-xs sm:text-sm font-extrabold tracking-wider text-[#1e535f] uppercase">
                                Retrouvez
                            </h5>
                            <p class="text-slate-600 text-xs sm:text-sm leading-relaxed">
                                le contexte client et les documents au même endroit.
                            </p>
                        </div>
                        <div class="p-6 sm:p-7 space-y-2">
                            <h5 class="text-xs sm:text-sm font-extrabold tracking-wider text-[#1e535f] uppercase">
                                Partagez
                            </h5>
                            <p class="text-slate-600 text-xs sm:text-sm leading-relaxed">
                                une information commune avec les personnes concernées.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- CARD 02: Tâches et équipe --}}
                <div id="feature-02" class="rounded-3xl bg-white border border-slate-200/70 p-6 sm:p-10 space-y-8 shadow-sm overflow-hidden">
                    <div class="space-y-4">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-[#1e535f]/10 text-[#1e535f] font-bold flex items-center justify-center text-sm shrink-0">
                                02
                            </div>
                            <span class="text-xs font-bold tracking-widest text-[#1e535f] uppercase">Tâches et équipe</span>
                        </div>
                        <h3 class="text-2xl sm:text-[1.75rem] lg:text-3xl font-bold text-slate-900 leading-snug">
                            Transformez le projet en actions claires pour chaque collaborateur.
                        </h3>
                        <p class="text-slate-500 text-sm sm:text-[0.94rem] leading-relaxed max-w-xl">
                            Décomposez le travail en tâches, attribuez-les aux bonnes personnes et suivez l'avancement. L'équipe sait ce qu'elle doit faire ; les responsables voient ce qui progresse et ce qui demande une décision.
                        </p>
                    </div>

                    {{-- Visual Showcase Area --}}
                    <div class="p-4 sm:p-6 rounded-2xl bg-[#edf5f7] border border-slate-200/60 grid grid-cols-1 sm:grid-cols-12 gap-4 items-center">
                        <div class="sm:col-span-6 grid grid-cols-3 gap-2">
                            <div class="bg-slate-100/80 rounded-xl p-2 space-y-2">
                                <div class="h-10 bg-white rounded-lg shadow-2xs"></div>
                                <div class="h-10 bg-white rounded-lg shadow-2xs"></div>
                            </div>
                            <div class="bg-slate-100/80 rounded-xl p-2 space-y-2">
                                <div class="h-10 bg-white rounded-lg shadow-2xs"></div>
                                <div class="h-10 bg-white rounded-lg shadow-2xs"></div>
                                <div class="h-10 bg-white rounded-lg shadow-2xs"></div>
                            </div>
                            <div class="bg-slate-100/80 rounded-xl p-2 space-y-2">
                                <div class="h-10 bg-white rounded-lg shadow-2xs"></div>
                            </div>
                        </div>

                        <div class="sm:col-span-6 border-2 border-dashed border-[#1e535f]/25 bg-white rounded-2xl p-6 sm:p-8 flex flex-col items-center justify-center text-center min-h-[200px]">
                            <span class="text-[#1e535f] font-bold text-[10px] sm:text-xs tracking-wider uppercase mb-2">
                                Visuel Produit 03
                            </span>
                            <h4 class="text-slate-900 font-bold text-sm sm:text-base mb-1.5">
                                Vue des tâches et des responsables
                            </h4>
                            <p class="text-slate-400 text-xs sm:text-sm max-w-xs leading-relaxed">
                                Montrer les statuts, priorités, personnes assignées et échéances.
                            </p>
                        </div>
                    </div>

                    {{-- Bottom 3-Column Highlights Bar --}}
                    <div class="-mx-6 -mb-6 sm:-mx-10 sm:-mb-10 mt-8 border-t border-slate-200/80 grid grid-cols-1 md:grid-cols-3 divide-y md:divide-y-0 md:divide-x divide-slate-200/80 bg-white rounded-b-3xl">
                        <div class="p-6 sm:p-7 space-y-2">
                            <h5 class="text-xs sm:text-sm font-extrabold tracking-wider text-[#1e535f] uppercase">
                                Attribuez
                            </h5>
                            <p class="text-slate-600 text-xs sm:text-sm leading-relaxed">
                                chaque tâche à la bonne personne.
                            </p>
                        </div>
                        <div class="p-6 sm:p-7 space-y-2">
                            <h5 class="text-xs sm:text-sm font-extrabold tracking-wider text-[#1e535f] uppercase">
                                Priorisez
                            </h5>
                            <p class="text-slate-600 text-xs sm:text-sm leading-relaxed">
                                le travail à partir des échéances réelles.
                            </p>
                        </div>
                        <div class="p-6 sm:p-7 space-y-2">
                            <h5 class="text-xs sm:text-sm font-extrabold tracking-wider text-[#1e535f] uppercase">
                                Suivez
                            </h5>
                            <p class="text-slate-600 text-xs sm:text-sm leading-relaxed">
                                l'avancement sans multiplier les messages de relance.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- CARD 03: Temps et planning --}}
                <div id="feature-03" class="rounded-3xl bg-[#0b2230] border border-slate-800 p-6 sm:p-10 space-y-8 shadow-sm overflow-hidden text-white">
                    <div class="space-y-4">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-amber-500 text-slate-950 font-bold flex items-center justify-center text-sm shrink-0">
                                03
                            </div>
                            <span class="text-xs font-bold tracking-widest text-teal-400 uppercase">Temps et planning</span>
                        </div>
                        <h3 class="text-2xl sm:text-[1.75rem] lg:text-3xl font-bold text-white leading-snug">
                            Enregistrez le temps pendant le travail, pas en fin de semaine.
                        </h3>
                        <p class="text-slate-300 text-sm sm:text-[0.94rem] leading-relaxed max-w-xl">
                            L'employé consulte ses tâches et démarre ou arrête celle sur laquelle il travaille. Chaque heure est immédiatement rattachée au bon projet pour fiabiliser le suivi et préparer la facturation.
                        </p>
                    </div>

                    {{-- Visual Showcase Area --}}
                    <div class="p-4 sm:p-6 rounded-2xl bg-[#133244] border border-slate-700/50 grid grid-cols-1 sm:grid-cols-12 gap-4 items-center">
                        <div class="sm:col-span-6 bg-white rounded-2xl p-6 sm:p-8 flex flex-col items-center justify-center text-center space-y-3 shadow-md">
                            <span class="text-slate-400 font-bold text-[10px] sm:text-xs tracking-wider uppercase">
                                Tâche en cours
                            </span>
                            <div class="text-slate-900 font-extrabold text-3xl sm:text-4xl tracking-tight">
                                01:42:18
                            </div>
                            <p class="text-slate-500 text-xs sm:text-sm font-medium">
                                Projet client · Étude préliminaire
                            </p>
                            <button type="button" class="mt-1 bg-[#1e535f] hover:bg-[#153e47] text-white text-xs font-bold px-5 py-2 rounded-full shadow-xs transition-colors">
                                Arrêter
                            </button>
                        </div>

                        <div class="sm:col-span-6 border-2 border-dashed border-teal-400/30 bg-[#091c27]/70 rounded-2xl p-6 sm:p-8 flex flex-col items-center justify-center text-center min-h-[200px]">
                            <span class="text-teal-400 font-bold text-[10px] sm:text-xs tracking-wider uppercase mb-2">
                                Visuel Produit 04
                            </span>
                            <h4 class="text-white font-bold text-sm sm:text-base mb-1.5">
                                Chrono et feuille de temps
                            </h4>
                            <p class="text-slate-400 text-xs sm:text-sm max-w-xs leading-relaxed">
                                Prévoir la vue employé puis la consolidation par projet et collaborateur.
                            </p>
                        </div>
                    </div>

                    {{-- Bottom 3-Column Highlights Bar --}}
                    <div class="-mx-6 -mb-6 sm:-mx-10 sm:-mb-10 mt-8 border-t border-slate-700/60 grid grid-cols-1 md:grid-cols-3 divide-y md:divide-y-0 md:divide-x divide-slate-700/60 bg-[#071620] rounded-b-3xl">
                        <div class="p-6 sm:p-7 space-y-2">
                            <h5 class="text-xs sm:text-sm font-extrabold tracking-wider text-teal-400 uppercase">
                                Réduisez
                            </h5>
                            <p class="text-slate-300 text-xs sm:text-sm leading-relaxed">
                                les heures oubliées ou renseignées trop tard.
                            </p>
                        </div>
                        <div class="p-6 sm:p-7 space-y-2">
                            <h5 class="text-xs sm:text-sm font-extrabold tracking-wider text-teal-400 uppercase">
                                Rattachez
                            </h5>
                            <p class="text-slate-300 text-xs sm:text-sm leading-relaxed">
                                le temps au projet et à la tâche concernés.
                            </p>
                        </div>
                        <div class="p-6 sm:p-7 space-y-2">
                            <h5 class="text-xs sm:text-sm font-extrabold tracking-wider text-teal-400 uppercase">
                                Obtenez
                            </h5>
                            <p class="text-slate-300 text-xs sm:text-sm leading-relaxed">
                                une base fiable pour piloter et facturer.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- CARD 04: Budgets et dépenses --}}
                <div id="feature-04" class="rounded-3xl bg-white border border-slate-200/70 p-6 sm:p-10 space-y-8 shadow-sm overflow-hidden">
                    <div class="space-y-4">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-[#1e535f]/10 text-[#1e535f] font-bold flex items-center justify-center text-sm shrink-0">
                                04
                            </div>
                            <span class="text-xs font-bold tracking-widest text-[#1e535f] uppercase">Budgets et dépenses</span>
                        </div>
                        <h3 class="text-2xl sm:text-[1.75rem] lg:text-3xl font-bold text-slate-900 leading-snug">
                            Comparez ce qui était prévu à ce que le projet consomme réellement.
                        </h3>
                        <p class="text-slate-500 text-sm sm:text-[0.94rem] leading-relaxed max-w-xl">
                            Suivez le budget d'heures et les dépenses dans le contexte du projet. Vous identifiez plus tôt les écarts qui peuvent fragiliser la marge et disposez des éléments nécessaires pour arbitrer.
                        </p>
                    </div>

                    {{-- Visual Showcase Area --}}
                    <div class="p-4 sm:p-6 rounded-2xl bg-[#edf5f7] border border-slate-200/60 grid grid-cols-1 sm:grid-cols-12 gap-4 items-center">
                        <div class="sm:col-span-6 border-2 border-dashed border-[#1e535f]/25 bg-white rounded-2xl p-6 sm:p-8 flex flex-col items-center justify-center text-center min-h-[200px]">
                            <span class="text-[#1e535f] font-bold text-[10px] sm:text-xs tracking-wider uppercase mb-2">
                                Visuel Produit 05
                            </span>
                            <h4 class="text-slate-900 font-bold text-sm sm:text-base mb-1.5">
                                Budget prévu / consommé
                            </h4>
                            <p class="text-slate-400 text-xs sm:text-sm max-w-xs leading-relaxed">
                                Capture à fournir : progression du budget, dépenses et niveau d'avancement.
                            </p>
                        </div>

                        <div class="sm:col-span-6 bg-white rounded-2xl p-6 sm:p-8 flex flex-col justify-center space-y-4 min-h-[200px] shadow-2xs">
                            <div class="w-full bg-slate-100 rounded-full h-4 overflow-hidden">
                                <div class="bg-[#1e535f] h-full rounded-full w-[72%]"></div>
                            </div>
                            <div class="w-full bg-slate-100 rounded-full h-4 overflow-hidden">
                                <div class="bg-[#1e535f] h-full rounded-full w-[45%]"></div>
                            </div>
                            <div class="w-full bg-slate-100 rounded-full h-4 overflow-hidden">
                                <div class="bg-[#1e535f] h-full rounded-full w-[82%]"></div>
                            </div>
                        </div>
                    </div>

                    {{-- Bottom 3-Column Highlights Bar --}}
                    <div class="-mx-6 -mb-6 sm:-mx-10 sm:-mb-10 mt-8 border-t border-slate-200/80 grid grid-cols-1 md:grid-cols-3 divide-y md:divide-y-0 md:divide-x divide-slate-200/80 bg-white rounded-b-3xl">
                        <div class="p-6 sm:p-7 space-y-2">
                            <h5 class="text-xs sm:text-sm font-extrabold tracking-wider text-[#1e535f] uppercase">
                                Comparez
                            </h5>
                            <p class="text-slate-600 text-xs sm:text-sm leading-relaxed">
                                les heures prévues et réellement consommées.
                            </p>
                        </div>
                        <div class="p-6 sm:p-7 space-y-2">
                            <h5 class="text-xs sm:text-sm font-extrabold tracking-wider text-[#1e535f] uppercase">
                                Rattachez
                            </h5>
                            <p class="text-slate-600 text-xs sm:text-sm leading-relaxed">
                                les dépenses à la mission correspondante.
                            </p>
                        </div>
                        <div class="p-6 sm:p-7 space-y-2">
                            <h5 class="text-xs sm:text-sm font-extrabold tracking-wider text-[#1e535f] uppercase">
                                Intervenez
                            </h5>
                            <p class="text-slate-600 text-xs sm:text-sm leading-relaxed">
                                avant que l'écart ne soit irréversible.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- CARD 05: Devis et factures --}}
                <div id="feature-05" class="rounded-3xl bg-white border border-slate-200/70 p-6 sm:p-10 space-y-8 shadow-sm overflow-hidden">
                    <div class="space-y-4">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-[#1e535f]/10 text-[#1e535f] font-bold flex items-center justify-center text-sm shrink-0">
                                05
                            </div>
                            <span class="text-xs font-bold tracking-widest text-[#1e535f] uppercase">Devis et facturation</span>
                        </div>
                        <h3 class="text-2xl sm:text-[1.75rem] lg:text-3xl font-bold text-slate-900 leading-snug">
                            Réutilisez les informations commerciales sans les ressaisir à chaque étape.
                        </h3>
                        <p class="text-slate-500 text-sm sm:text-[0.94rem] leading-relaxed max-w-xl">
                            Préparez les devis et les documents de facturation à partir des informations déjà présentes dans DalyTrac. Le travail réalisé reste relié au client, au projet et au document correspondant.
                        </p>
                    </div>

                    {{-- Visual Showcase Area --}}
                    <div class="p-4 sm:p-6 rounded-2xl bg-[#edf5f7] border border-slate-200/60 grid grid-cols-1 sm:grid-cols-12 gap-4 items-center">
                        <div class="sm:col-span-6 bg-white rounded-2xl p-6 sm:p-8 flex flex-col justify-between min-h-[200px] shadow-2xs space-y-5">
                            <div class="space-y-3">
                                <span class="text-[#1e535f] font-bold text-[10px] sm:text-xs tracking-wider uppercase block">
                                    Facture
                                </span>
                                <div class="h-3 bg-slate-200/70 rounded-full w-2/5"></div>
                                <div class="h-3 bg-slate-200/70 rounded-full w-4/5"></div>
                                <div class="h-3 bg-slate-200/70 rounded-full w-3/5"></div>
                            </div>
                            <div class="border-t border-slate-100 pt-3 flex justify-end">
                                <span class="font-bold text-slate-900 text-sm sm:text-base">Total</span>
                            </div>
                        </div>

                        <div class="sm:col-span-6 border-2 border-dashed border-[#1e535f]/25 bg-white rounded-2xl p-6 sm:p-8 flex flex-col items-center justify-center text-center min-h-[200px]">
                            <span class="text-[#1e535f] font-bold text-[10px] sm:text-xs tracking-wider uppercase mb-2">
                                Visuel Produit 06
                            </span>
                            <h4 class="text-slate-900 font-bold text-sm sm:text-base mb-1.5">
                                Devis, facture et suivi du statut
                            </h4>
                            <p class="text-slate-400 text-xs sm:text-sm max-w-xs leading-relaxed">
                                Montrer le document généré et son rattachement au projet.
                            </p>
                        </div>
                    </div>

                    {{-- Bottom 3-Column Highlights Bar --}}
                    <div class="-mx-6 -mb-6 sm:-mx-10 sm:-mb-10 mt-8 border-t border-slate-200/80 grid grid-cols-1 md:grid-cols-3 divide-y md:divide-y-0 md:divide-x divide-slate-200/80 bg-white rounded-b-3xl">
                        <div class="p-6 sm:p-7 space-y-2">
                            <h5 class="text-xs sm:text-sm font-extrabold tracking-wider text-[#1e535f] uppercase">
                                Préparez
                            </h5>
                            <p class="text-slate-600 text-xs sm:text-sm leading-relaxed">
                                les documents depuis une information déjà structurée.
                            </p>
                        </div>
                        <div class="p-6 sm:p-7 space-y-2">
                            <h5 class="text-xs sm:text-sm font-extrabold tracking-wider text-[#1e535f] uppercase">
                                Limitez
                            </h5>
                            <p class="text-slate-600 text-xs sm:text-sm leading-relaxed">
                                les consolidations et les doubles saisies.
                            </p>
                        </div>
                        <div class="p-6 sm:p-7 space-y-2">
                            <h5 class="text-xs sm:text-sm font-extrabold tracking-wider text-[#1e535f] uppercase">
                                Visualisez
                            </h5>
                            <p class="text-slate-600 text-xs sm:text-sm leading-relaxed">
                                ce qui est facturé ou encore à préparer.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- CARD 06: Tableaux de bord --}}
                <div id="feature-06" class="rounded-3xl bg-white border border-slate-200/70 p-6 sm:p-10 space-y-8 shadow-sm overflow-hidden">
                    <div class="space-y-4">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-[#1e535f]/10 text-[#1e535f] font-bold flex items-center justify-center text-sm shrink-0">
                                06
                            </div>
                            <span class="text-xs font-bold tracking-widest text-[#1e535f] uppercase">Tableaux de bord</span>
                        </div>
                        <h3 class="text-2xl sm:text-[1.75rem] lg:text-3xl font-bold text-slate-900 leading-snug">
                            Transformez les données du quotidien en décisions de pilotage.
                        </h3>
                        <p class="text-slate-500 text-sm sm:text-[0.94rem] leading-relaxed max-w-xl">
                            Les projets, les temps, les budgets, les dépenses et la facturation alimentent une lecture consolidée de l'activité. Vous voyez les projets à surveiller sans reconstruire manuellement les chiffres.
                        </p>
                    </div>

                    {{-- Visual Showcase Area --}}
                    <div class="p-4 sm:p-6 rounded-2xl bg-[#edf5f7] border border-slate-200/60 grid grid-cols-1 sm:grid-cols-12 gap-4 items-center">
                        <div class="sm:col-span-6 border-2 border-dashed border-[#1e535f]/25 bg-white rounded-2xl p-6 sm:p-8 flex flex-col items-center justify-center text-center min-h-[200px]">
                            <span class="text-[#1e535f] font-bold text-[10px] sm:text-xs tracking-wider uppercase mb-2">
                                Visuel Produit 07
                            </span>
                            <h4 class="text-slate-900 font-bold text-sm sm:text-base mb-1.5">
                                Tableau de bord de rentabilité
                            </h4>
                            <p class="text-slate-400 text-xs sm:text-sm max-w-xs leading-relaxed">
                                Capture à fournir : écarts, temps suivi, montants à facturer et projets à surveiller.
                            </p>
                        </div>

                        <div class="sm:col-span-6 bg-white rounded-2xl p-6 sm:p-8 flex items-end justify-between gap-2.5 min-h-[200px] shadow-2xs">
                            <div class="bg-[#b5dbe0] h-16 w-full rounded-t-md"></div>
                            <div class="bg-[#b5dbe0] h-28 w-full rounded-t-md"></div>
                            <div class="bg-[#b5dbe0] h-20 w-full rounded-t-md"></div>
                            <div class="bg-[#1e535f] h-36 w-full rounded-t-md"></div>
                            <div class="bg-[#b5dbe0] h-30 w-full rounded-t-md"></div>
                        </div>
                    </div>

                    {{-- Bottom 3-Column Highlights Bar --}}
                    <div class="-mx-6 -mb-6 sm:-mx-10 sm:-mb-10 mt-8 border-t border-slate-200/80 grid grid-cols-1 md:grid-cols-3 divide-y md:divide-y-0 md:divide-x divide-slate-200/80 bg-white rounded-b-3xl">
                        <div class="p-6 sm:p-7 space-y-2">
                            <h5 class="text-xs sm:text-sm font-extrabold tracking-wider text-[#1e535f] uppercase">
                                Repérez
                            </h5>
                            <p class="text-slate-600 text-xs sm:text-sm leading-relaxed">
                                les projets qui s'éloignent de leur budget.
                            </p>
                        </div>
                        <div class="p-6 sm:p-7 space-y-2">
                            <h5 class="text-xs sm:text-sm font-extrabold tracking-wider text-[#1e535f] uppercase">
                                Comprenez
                            </h5>
                            <p class="text-slate-600 text-xs sm:text-sm leading-relaxed">
                                où le temps de l'équipe est réellement investi.
                            </p>
                        </div>
                        <div class="p-6 sm:p-7 space-y-2">
                            <h5 class="text-xs sm:text-sm font-extrabold tracking-wider text-[#1e535f] uppercase">
                                Décidez
                            </h5>
                            <p class="text-slate-600 text-xs sm:text-sm leading-relaxed">
                                à partir d'une information reliée et exploitable.
                            </p>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>
</section>
