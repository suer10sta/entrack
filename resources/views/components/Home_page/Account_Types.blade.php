{{-- resources/views/components/Home_page/Account_Types.blade.php --}}

<section id="account_type" class="py-16 sm:py-24 bg-white">
    <div class="container mx-auto px-4 sm:px-6">
        
        {{-- PART 1: RÔLES ET ACCÈS --}}
        <div class="max-w-6xl mx-auto space-y-12">
            
            {{-- Section Header --}}
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-12 items-end">
                <div class="lg:col-span-8 space-y-3">
                    <div class="inline-flex items-center gap-2.5 text-xs font-bold tracking-wider text-slate-800 uppercase">
                        <span class="w-5 h-0.5 bg-amber-500 rounded-full inline-block"></span>
                        <span>RÔLES ET ACCÈS</span>
                    </div>
                    <h2 class="text-3xl sm:text-4xl lg:text-[2.6rem] font-extrabold text-slate-900 leading-tight">
                        Chaque membre de l'équipe accède <span class="text-[#1e535f]">aux outils dont il a besoin.</span>
                    </h2>
                </div>
                <div class="lg:col-span-4 text-slate-500 text-sm sm:text-base leading-relaxed">
                    L'interface employé reste simple, tandis que les responsables disposent des informations nécessaires pour suivre les projets et prendre des décisions.
                </div>
            </div>

            {{-- Roles Table --}}
            <div class="rounded-3xl border border-slate-200/80 shadow-sm bg-white overflow-hidden">
                
                {{-- Table Header --}}
                <div class="bg-[#0b2230] px-6 py-4 sm:px-8 sm:py-5 hidden md:grid md:grid-cols-12 gap-4 items-center text-[11px] font-bold tracking-widest text-slate-300 uppercase">
                    <div class="md:col-span-4">Rôle</div>
                    <div class="md:col-span-3">Mission principale</div>
                    <div class="md:col-span-5">Usages dans DalyTrac</div>
                </div>

                <div class="divide-y divide-slate-100">
                    
                    {{-- Row 1: Administrateur --}}
                    <div class="p-6 sm:px-8 sm:py-6 grid grid-cols-1 md:grid-cols-12 gap-4 items-center hover:bg-slate-50/50 transition-colors">
                        <div class="md:col-span-4 flex items-center gap-3.5">
                            <div class="w-8 h-8 rounded-lg bg-teal-500/10 text-[#1e535f] font-bold flex items-center justify-center text-xs shrink-0">
                                A
                            </div>
                            <span class="font-bold text-slate-900 text-base">Administrateur</span>
                        </div>
                        <div class="md:col-span-3 font-bold text-slate-900 text-sm">
                            Structurer l'environnement
                        </div>
                        <div class="md:col-span-5 text-slate-500 text-sm leading-relaxed">
                            Configure l'entreprise, crée les collaborateurs, les projets et les tâches, puis exploite les données.
                        </div>
                    </div>

                    {{-- Row 2: Gestionnaire --}}
                    <div class="p-6 sm:px-8 sm:py-6 grid grid-cols-1 md:grid-cols-12 gap-4 items-center hover:bg-slate-50/50 transition-colors">
                        <div class="md:col-span-4 flex items-center gap-3.5">
                            <div class="w-8 h-8 rounded-lg bg-teal-500/10 text-[#1e535f] font-bold flex items-center justify-center text-xs shrink-0">
                                G
                            </div>
                            <span class="font-bold text-slate-900 text-base">Gestionnaire</span>
                        </div>
                        <div class="md:col-span-3 font-bold text-slate-900 text-sm">
                            Coordonner l'activité
                        </div>
                        <div class="md:col-span-5 text-slate-500 text-sm leading-relaxed">
                            Accède aux informations utiles au suivi de l'équipe et de son organisation.
                        </div>
                    </div>

                    {{-- Row 3: Responsable de projet --}}
                    <div class="p-6 sm:px-8 sm:py-6 grid grid-cols-1 md:grid-cols-12 gap-4 items-center hover:bg-slate-50/50 transition-colors">
                        <div class="md:col-span-4 flex items-center gap-3.5">
                            <div class="w-8 h-8 rounded-lg bg-teal-500/10 text-[#1e535f] font-bold flex items-center justify-center text-xs shrink-0">
                                P
                            </div>
                            <span class="font-bold text-slate-900 text-base">Responsable de projet</span>
                        </div>
                        <div class="md:col-span-3 font-bold text-slate-900 text-sm">
                            Suivre l'avancement
                        </div>
                        <div class="md:col-span-5 text-slate-500 text-sm leading-relaxed">
                            Suit les tâches, les temps et les projets dont il assure le suivi.
                        </div>
                    </div>

                    {{-- Row 4: Employé (Highlighted row) --}}
                    <div class="p-6 sm:px-8 sm:py-6 grid grid-cols-1 md:grid-cols-12 gap-4 items-center bg-[#edf5f7] transition-colors">
                        <div class="md:col-span-4 flex items-center gap-3.5">
                            <div class="w-8 h-8 rounded-lg bg-amber-500 text-slate-950 font-bold flex items-center justify-center text-xs shrink-0">
                                E
                            </div>
                            <span class="font-bold text-slate-900 text-base">Employé</span>
                        </div>
                        <div class="md:col-span-3 font-bold text-slate-900 text-sm">
                            Faire avancer le travail
                        </div>
                        <div class="md:col-span-5 text-slate-500 text-sm leading-relaxed">
                            Consulte ses tâches et son planning, lance ou arrête une tâche, commente et exporte en PDF.
                        </div>
                    </div>

                </div>
            </div>
        </div>

        {{-- PART 2: QUICK START ONBOARDING (DARK SECTION) --}}
        <div class="max-w-6xl mx-auto mt-20 sm:mt-28 rounded-3xl bg-[#0b2230] text-white p-8 sm:p-14 shadow-xl">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
                
                {{-- Left Text Column --}}
                <div class="lg:col-span-5 space-y-4">
                    <span class="text-xs font-bold tracking-wider text-amber-400 uppercase">PRISE EN MAIN RAPIDE</span>
                    <h3 class="text-2xl sm:text-3xl font-extrabold text-white leading-tight">
                        Commencez simplement avec <span class="text-amber-400">un premier projet.</span>
                    </h3>
                    <p class="text-slate-300 text-sm leading-relaxed">
                        Pas besoin de paramétrage complexe. En 3 étapes simples, votre équipe est opérationnelle.
                    </p>
                    <div class="pt-2">
                        <a href="{{ route('register') }}" 
                           class="inline-block rounded-full bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold px-7 py-3.5 text-sm transition-all shadow-md hover:scale-105">
                            Créer mon compte gratuit
                        </a>
                    </div>
                </div>

                {{-- Right 3 Steps Column --}}
                <div class="lg:col-span-7 space-y-6">
                    
                    {{-- Step 1 --}}
                    <div class="flex items-start gap-4 p-4 rounded-2xl bg-white/5 border border-white/10 hover:bg-white/10 transition-colors">
                        <div class="w-10 h-10 rounded-xl bg-amber-400 text-slate-900 font-extrabold flex items-center justify-center flex-shrink-0 text-sm">
                            01
                        </div>
                        <div class="space-y-1">
                            <h4 class="font-bold text-white text-base">Créez votre compte</h4>
                            <p class="text-xs sm:text-sm text-slate-300 leading-relaxed">
                                Sans carte bancaire, accès immédiat à toutes les fonctionnalités pendant 14 jours.
                            </p>
                        </div>
                    </div>

                    {{-- Step 2 --}}
                    <div class="flex items-start gap-4 p-4 rounded-2xl bg-white/5 border border-white/10 hover:bg-white/10 transition-colors">
                        <div class="w-10 h-10 rounded-xl bg-teal-400 text-slate-900 font-extrabold flex items-center justify-center flex-shrink-0 text-sm">
                            02
                        </div>
                        <div class="space-y-1">
                            <h4 class="font-bold text-white text-base">Invitez votre équipe</h4>
                            <p class="text-xs sm:text-sm text-slate-300 leading-relaxed">
                                Attribuez les rôles et permettez à chacun de démarrer son suivi de temps.
                            </p>
                        </div>
                    </div>

                    {{-- Step 3 --}}
                    <div class="flex items-start gap-4 p-4 rounded-2xl bg-white/5 border border-white/10 hover:bg-white/10 transition-colors">
                        <div class="w-10 h-10 rounded-xl bg-cyan-400 text-slate-900 font-extrabold flex items-center justify-center flex-shrink-0 text-sm">
                            03
                        </div>
                        <div class="space-y-1">
                            <h4 class="font-bold text-white text-base">Lancez votre premier projet</h4>
                            <p class="text-xs sm:text-sm text-slate-300 leading-relaxed">
                                Définissez le budget, ajoutez les tâches et suivez la rentabilité en direct.
                            </p>
                        </div>
                    </div>

                </div>

            </div>
        </div>

    </div>
</section>
