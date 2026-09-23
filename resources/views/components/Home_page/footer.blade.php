{{-- resources/views/components/Home_page/footer.blade.php --}}

<footer class="bg-[#0b1329] text-slate-400 border-t border-slate-800/80">
    <div class="container mx-auto px-6 py-12 lg:py-16">
        <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-5">

            {{-- Logo & About Section --}}
            <div class="lg:col-span-2 space-y-4">
                <a href="{{ route('welcome') }}" class="inline-flex items-center group">
                    <span class="text-2xl font-extrabold tracking-tight">
                        <span class="text-white">Daly</span><span class="text-[#1e535f]">Trac</span>
                    </span>
                </a>

                <p class="text-sm text-slate-400 max-w-sm leading-relaxed">
                    La solution complète pour piloter vos projets, enregistrer votre temps et suivre vos marges opérationnelles du devis à la facturation.
                </p>

                <div class="text-xs text-slate-500 pt-2">
                    DalyTrac © {{ now()->year }}. Tous droits réservés.
                </div>
            </div>

            {{-- Empty Spacer --}}
            <div class="hidden lg:block lg:col-span-1"></div>

            {{-- Links Columns --}}
            <div>
                <h4 class="mb-4 text-xs font-bold uppercase tracking-wider text-slate-200">
                    Produit
                </h4>
                <ul class="space-y-2.5 text-sm">
                    <li><a href="#features" class="hover:text-white transition-colors">Fonctionnalités</a></li>
                    <li><a href="#account_type" class="hover:text-white transition-colors">Formules & Tarifs</a></li>
                    <li><a href="{{ route('demo') }}" class="hover:text-white transition-colors">Démonstration</a></li>
                    <li><a href="{{ route('register') }}" class="hover:text-white transition-colors">Essai gratuit</a></li>
                </ul>
            </div>

            <div>
                <h4 class="mb-4 text-xs font-bold uppercase tracking-wider text-slate-200">
                    Entreprise
                </h4>
                <ul class="space-y-2.5 text-sm">
                    <li><a href="{{ route('about') }}" class="hover:text-white transition-colors">À propos</a></li>
                    <li><a href="{{ route('faq') }}" class="hover:text-white transition-colors">FAQ</a></li>
                    <li><a href="{{ url('/contact') }}" class="hover:text-white transition-colors">Contact</a></li>
                    <li><a href="{{ route('blog.index') }}" class="hover:text-white transition-colors">Blog</a></li>
                </ul>
            </div>

        </div>

        <div class="mt-12 border-t border-slate-800/80 pt-6 flex flex-col md:flex-row items-center justify-between gap-4 text-xs text-slate-500">
            <p>DalyTrac — Plateforme de gestion pour entreprises de services.</p>
            <div class="flex gap-6">
                <a href="{{ route('welcome') }}" class="hover:text-slate-300 transition-colors">Accueil</a>
                <a href="{{ route('login') }}" class="hover:text-slate-300 transition-colors">Connexion</a>
                <a href="{{ route('register') }}" class="hover:text-slate-300 transition-colors">Inscription</a>
            </div>
        </div>
    </div>
</footer>
