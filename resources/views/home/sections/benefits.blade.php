<section data-home-section="benefits" class="bg-[#f8faf9] py-28 max-md:py-20" aria-labelledby="benefits-title">
  <div class="mx-auto max-w-[1440px] px-6 max-md:px-4">
    <div class="mb-14 grid items-end gap-20 max-md:grid-cols-1 max-md:gap-6 lg:grid-cols-[1.35fr_0.65fr]">
      <div>
        <p class="mb-5 flex items-center gap-2.5 text-[0.75rem] font-bold uppercase tracking-[0.13em] text-brand-secondary"><span class="h-0.5 w-7 bg-brand-accent"></span>{{ __('messages.public_benefits_eyebrow') }}</p>
        <h2 id="benefits-title" class="text-[clamp(2.5rem,4.1vw,4.15rem)] font-semibold leading-[1.02] tracking-[-0.052em] text-brand-primary max-sm:text-[clamp(2.25rem,11vw,3.1rem)]">{{ __('messages.public_benefits_title') }}<br><em class="not-italic text-brand-secondary">{{ __('messages.public_benefits_title_highlight') }}</em></h2>
      </div>
      <a href="demo" class="group justify-self-end pb-2 font-semibold text-brand-secondary no-underline transition-colors duration-300 hover:text-[#245f6d] max-md:justify-self-start">{{ __('messages.public_benefits_link') }} <span class="ml-2 inline-block transition-transform duration-300 group-hover:translate-x-1"><i data-lucide="arrow-right" class="h-4 w-4"></i></span></a>
    </div>
    <div class="grid items-center gap-8 lg:grid-cols-[1.3fr_0.9fr] lg:gap-10">
      <figure class="m-0 overflow-hidden rounded-[22px] border border-[#dce6e4] bg-white p-3 shadow-lg">
        <a href="{{ asset('assets/images/dalytrac-project-demo.png') }}" target="_blank" rel="noopener noreferrer" class="block">
          <img src="{{ asset('assets/images/dalytrac-project-demo.png') }}" alt="{{ __('messages.public_home_visual_2_title') }}" width="1704" height="923" loading="lazy" decoding="async" class="block h-auto w-full rounded-xl">
        </a>
        <figcaption class="px-2 pb-2 pt-3 text-center text-sm text-[#5b6b73]">{{ __('messages.public_home_visual_2_label') }}</figcaption>
      </figure>
      <div class="border-t border-[#dce6e4]">
        @foreach(range(1, 4) as $number)
          <article class="group grid grid-cols-[32px_1fr] items-start gap-4 border-b border-[#dce6e4] py-5">
            <b class="pt-1 text-xs font-semibold text-brand-secondary">0{{ $number }}</b>
            <div>
              <h3 class="text-[1.1rem] font-semibold leading-snug text-brand-primary">{{ __('messages.public_benefits_card_' . $number . '_title') }}</h3>
              <p class="mt-2 text-[0.9rem] leading-relaxed text-[#5b6b73]">{{ __('messages.public_benefits_card_' . $number . '_text') }}</p>
            </div>
          </article>
        @endforeach
      </div>
    </div>
  </div>
</section>
