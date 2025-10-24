<section id="features" class="py-24 lg:py-32">
    <div class="container px-6">
        <!-- Header -->
        <div class="mx-auto max-w-3xl text-center">
            <div class="mb-4 inline-flex items-center gap-2 rounded-full border px-4 py-2 text-sm font-semibold text-primary">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                Fonctionnalités puissantes
            </div>
            <h2 class="text-4xl font-bold text-foreground lg:text-5xl mt-2">
                Une plateforme complète pour votre entreprise
            </h2>
            <p class="mt-6 text-lg leading-relaxed text-muted-foreground">
                Tous les outils dont vous avez besoin, réunis dans une interface simple et élégante.
            </p>
        </div>

        <!-- Features grid -->
        <div class="mt-20 grid gap-6 md:grid-cols-2 lg:grid-cols-4">
            <!-- Feature 1 -->
            <div class="group relative overflow-hidden rounded-xl border bg-card p-6 transition-all duration-500 hover:shadow-2xl hover:-translate-y-2">
                <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-lg from-blue-500 text-white shadow-lg transition-all group-hover:scale-110">
                    <!-- Icon -->
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <h3 class="mb-2 text-lg font-bold text-foreground">Facturation facile</h3>
                <p class="text-sm leading-relaxed text-muted-foreground">
                    Créez et envoyez des factures professionnelles en quelques clics.
                </p>
            </div>

            <!-- Feature 2 -->
            <div class="group relative overflow-hidden rounded-xl border bg-card p-6 transition-all duration-500 hover:shadow-2xl hover:-translate-y-2">
                <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-lg from-violet-500 text-white shadow-lg transition-all group-hover:scale-110">
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <h3 class="mb-2 text-lg font-bold text-foreground">Suivi des clients</h3>
                <p class="text-sm leading-relaxed text-muted-foreground">Gérez tous vos clients en un seul endroit.</p>
            </div>

            <!-- Feature 3 -->
            <div class="group relative overflow-hidden rounded-xl border bg-card p-6 transition-all duration-500 hover:shadow-2xl hover:-translate-y-2">
                <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-lg from-emerald-500 text-white shadow-lg transition-all group-hover:scale-110">
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <h3 class="mb-2 text-lg font-bold text-foreground">Rapports en temps réel</h3>
                <p class="text-sm leading-relaxed text-muted-foreground">Visualisez vos performances avec des tableaux de bord interactifs.</p>
            </div>

            <!-- Feature 4 -->
            <div class="group relative overflow-hidden rounded-xl border bg-card p-6 transition-all duration-500 hover:shadow-2xl hover:-translate-y-2">
                <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-lg from-orange-500 text-white shadow-lg transition-all group-hover:scale-110">
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3 class="mb-2 text-lg font-bold text-foreground">Gestion du temps</h3>
                <p class="text-sm leading-relaxed text-muted-foreground">Suivez le temps passé sur chaque projet et générez des rapports.</p>
            </div>
        </div>
    </div>

    <!-- Custom CSS Tailwind -->
    <style>
        :root {
            --primary: #3b82f6;
            --accent: #06b6d4;
        }
        .from-blue-500 { background: linear-gradient(to bottom right, #3b82f6, #06b6d4); }
        .from-violet-500 { background: linear-gradient(to bottom right, #8b5cf6, #9333ea); }
        .from-emerald-500 { background: linear-gradient(to bottom right, #10b981, #14b8a6); }
        .from-orange-500 { background: linear-gradient(to bottom right, #f97316, #ef4444); }
    </style>
</section>
