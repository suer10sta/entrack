@php
$clients = [
    ['id' => 1, 'name' => 'Smart Ladies', 'image' => 'https://hebbkx1anhila5yf.public.blob.vercel-storage.com/image-ljXgD91jQkgTZZpTX83ZiJLsaYSbRI.png', 'logo' => 'SL'],
    ['id' => 2, 'name' => 'Shoes Factory', 'image' => 'https://hebbkx1anhila5yf.public.blob.vercel-storage.com/image-ljXgD91jQkgTZZpTX83ZiJLsaYSbRI.png', 'logo' => 'SF'],
    ['id' => 3, 'name' => 'Tech Solutions', 'image' => '/modern-office-team-meeting.jpg', 'logo' => 'TS'],
];

$testimonials = [
    ['id'=>1, 'text'=>"Enterprise Tracker Pro has transformed the way we manage our projects and invoices. The interface is intuitive and the features are exactly what we needed.", 'author'=>'Marie Dubois', 'company'=>'Smart Ladies', 'role'=>'CEO'],
    ['id'=>2, 'text'=>"Thanks to this platform, we reduced our billing time by 60%. Real-time project tracking is a real asset for our team.", 'author'=>'Pierre Martin', 'company'=>'Shoes Factory', 'role'=>'Chief Financial Officer'],
    ['id'=>3, 'text'=>"A complete solution that meets all our business management needs. Customer support is outstanding and frequent updates always bring useful new features.", 'author'=>'Sophie Laurent', 'company'=>'Tech Solutions', 'role'=>'Project Manager'],
];
@endphp

<section id="customers" class="py-24 lg:py-32">
    <div class="container px-6">
        <div class="mb-16 text-center">
            <div class="mb-4 inline-flex items-center gap-2 rounded-full border border-primary/20 bg-primary/10 px-4 py-2 text-sm font-semibold text-primary">
                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                </svg>
                Customer Testimonials
            </div>
            <h2 class="text-balance text-4xl font-bold text-foreground lg:text-5xl">Trusted by Growing Teams</h2>
            <p class="mt-4 text-pretty text-lg text-muted-foreground">
                See what our customers say about the platform
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
    </div>
</section>