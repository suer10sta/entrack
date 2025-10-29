@php
$clients = [
    ['id' => 1, 'name' => 'Smart Ladies', 'image' => 'https://hebbkx1anhila5yf.public.blob.vercel-storage.com/image-ljXgD91jQkgTZZpTX83ZiJLsaYSbRI.png', 'logo' => 'SL'],
    ['id' => 2, 'name' => 'Shoes Factory', 'image' => 'https://hebbkx1anhila5yf.public.blob.vercel-storage.com/image-ljXgD91jQkgTZZpTX83ZiJLsaYSbRI.png', 'logo' => 'SF'],
    ['id' => 3, 'name' => 'Tech Solutions', 'image' => '/modern-office-team-meeting.jpg', 'logo' => 'TS'],
];

$testimonials = [
    ['id'=>1, 'text'=>"Entreprise Tracker Pro a transformé notre façon de gérer nos projets et nos factures. L'interface est intuitive et les fonctionnalités sont exactement ce dont nous avions besoin.", 'author'=>'Marie Dubois', 'company'=>'Smart Ladies', 'role'=>'CEO'],
    ['id'=>2, 'text'=>"Grâce à cette plateforme, nous avons réduit notre temps de facturation de 60%. Le suivi des projets en temps réel est un véritable atout pour notre équipe.", 'author'=>'Pierre Martin', 'company'=>'Shoes Factory', 'role'=>'Directeur Financier'],
    ['id'=>3, 'text'=>"Une solution complète qui répond à tous nos besoins de gestion d'entreprise. Le support client est exceptionnel et les mises à jour régulières apportent toujours de nouvelles fonctionnalités utiles.", 'author'=>'Sophie Laurent', 'company'=>'Tech Solutions', 'role'=>'Chef de Projet'],
];
@endphp

<section id="customers" class="py-24 lg:py-32">
    <div class="container px-6">
        <div class="mb-16 text-center">
            <div class="mb-4 inline-flex items-center gap-2 rounded-full border border-primary/20 bg-primary/10 px-4 py-2 text-sm font-semibold text-primary">
                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                </svg>
                Témoignages clients
            </div>
            <h2 class="text-balance text-4xl font-bold text-foreground lg:text-5xl">Ils nous font confiance</h2>
            <p class="mt-4 text-pretty text-lg text-muted-foreground">
                Découvrez ce que nos clients disent de notre plateforme
            </p>
        </div>

        <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
            @foreach($testimonials as $testimonial)
                <div class="group rounded-2xl border border-border bg-card p-8 shadow-lg transition-all hover:-translate-y-2 hover:shadow-2xl">
                    <div class="mb-6 flex gap-1">
                        @for($i=0; $i<5; $i++)
                            <svg class="h-5 w-5 fill-primary" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                        @endfor
                    </div>
                    <blockquote class="mb-6 text-pretty leading-relaxed text-foreground">
                        "{{ $testimonial['text'] }}"
                    </blockquote>
                    <div class="flex items-center gap-4 border-t border-border pt-6">
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gradient-to-br from-primary to-accent text-lg font-bold text-white shadow-lg">
                            {{ collect(explode(' ', $testimonial['author']))->map(fn($n)=>$n[0])->join('') }}
                        </div>
                        <div>
                            <p class="font-bold text-foreground">{{ $testimonial['author'] }}</p>
                            <p class="text-sm text-muted-foreground">{{ $testimonial['role'] }}, {{ $testimonial['company'] }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-20 rounded-2xl border border-primary/20 bg-gradient-to-br from-primary/5 to-accent/5 p-8 lg:p-12">
            <div class="grid items-center gap-8 lg:grid-cols-2">
                <div>
                    <h3 class="mb-4 text-3xl font-bold text-foreground">Rejoignez des milliers d'entreprises</h3>
                    <p class="mb-6 text-lg leading-relaxed text-muted-foreground">
                        Plus de 2,500 entreprises utilisent notre plateforme pour gérer leur activité au quotidien.
                    </p>
                    <div class="grid grid-cols-3 gap-6">
                        @foreach([ ['value'=>'2,500+', 'label'=>'Entreprises'], ['value'=>'50K+', 'label'=>'Factures'], ['value'=>'98%', 'label'=>'Satisfaction'] ] as $stat)
                            <div>
                                <div class="text-3xl font-bold text-primary">{{ $stat['value'] }}</div>
                                <div class="text-sm text-muted-foreground">{{ $stat['label'] }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="flex flex-wrap gap-4">
                    @foreach($clients as $client)
                        <div class="flex items-center gap-3 rounded-xl border border-border bg-card px-6 py-4 shadow-sm">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary text-sm font-bold text-primary-foreground">
                                {{ $client['logo'] }}
                            </div>
                            <span class="font-semibold text-foreground">{{ $client['name'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>