{{-- resources/views/components/footer.blade.php --}}
@props(['theme' => 'blue'])

<footer class="bg-[#F5F5F5] border-t border-[#E5E7EB]">
    <div class="container px-6 py-12 mx-auto"> <!-- J'ai ajouté mx-auto ici -->
        <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-5">

            <!-- =========================
                Logo & About Section
            ========================== -->
            <div class="lg:col-span-2 space-y-4">
                <div class="flex items-center gap-3 mb-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-[#007AFF] shadow-lg">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                            />
                        </svg>
                    </div>
                    <span class="text-xl font-bold text-[#1F2937]">
                        Enterprise Tracker Pro
                    </span>
                </div>

                <p class="text-sm text-[#6B7280] max-w-sm">
                    The complete solution to manage your business and invoices with simplicity.
                </p>

                <!-- Social Icons -->
                <div class="flex gap-3">
                    @foreach ([
                        'M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z',
                        'M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z',
                        'M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z'
                    ] as $icon)
                        <a
                            href="#"
                            class="flex h-9 w-9 items-center justify-center rounded-lg bg-[#E5E7EB] text-black
                                   hover:bg-[#007AFF] hover:text-white transition-colors"
                        >
                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                                <path d="{{ $icon }}" />
                            </svg>
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- =========================
                Empty Spacer Column
            ========================== -->
            <!-- Cette div vide pousse les colonnes de liens vers la droite sur les grands écrans -->
            <div class="hidden lg:block lg:col-span-1"></div>


            <!-- =========================
                Links Columns
            ========================== -->
            @foreach ([
                ['title' => 'Quick Links', 'links' => [
                    ['label' => 'Home', 'route' => route('welcome')],
                    ['label' => 'Features', 'route' => '#features'],
                    ['label' => 'Customers / Testimonials', 'route' => '#customers'] ]],
                ['title' => 'Company', 'links' => [
                    ['label' => 'About Us', 'route' => route('about')],
                    ['label' => 'Contact', 'route' => route('contact')],
                    ['label' => 'FAQ', 'route' => route('faq')] ]]
                ]
                as $column)
                <div>
                    <h4 class="mb-4 text-sm font-semibold text-[#1F2937]">
                        {{ $column['title'] }}
                    </h4>
                    <ul class="space-y-3 text-sm">
                        @foreach ($column['links'] as $link)
                            <li>
                                <a href="{{ $link['route'] }}"
                                    class="text-[#6B7280] hover:text-[#007AFF] transition-colors"
                                >
                                    {{ $link['label'] }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>

        <div class="mt-12 flex flex-col md:flex-row items-center justify-between gap-4 border-t border-[#E5E7EB] pt-6 text-sm text-[#9CA3AF]">
            <p>© {{ date('Y') }} Enterprise Tracker Pro. All rights reserved.</p> <!-- Année mise à jour dynamiquement -->

            <div class="flex items-center gap-2">
                <span>Made with</span>
                <svg class="h-4 w-4 text-[#007AFF]" fill="currentColor" viewBox="0 0 20 20">
                    <path
                        fill-rule="evenodd"
                        clip-rule="evenodd"
                        d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z"
                    />
                </svg>
                <span>in Agadir</span>
            </div>
        </div>
    </div>
</footer>