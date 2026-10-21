<section data-home-section="cta" id="tarifs" class="scroll-mt-24 bg-[linear-gradient(120deg,#0b2638_0%,#194354_100%)] py-[95px] text-white">
  <div class="mx-auto grid max-w-[1200px] items-center gap-[90px] px-6 max-md:grid-cols-1 max-md:gap-[38px] max-md:px-4 lg:grid-cols-[1.2fr_0.8fr]">
    <div>
      <p class="mb-5 flex items-center gap-2.5 text-[0.75rem] font-bold uppercase tracking-[0.13em] text-[#8ccfd0]"><span class="h-0.5 w-7 bg-brand-accent"></span>{{ __('messages.public_cta_eyebrow') }}</p>
      <h2 class="text-[clamp(2.65rem,4.1vw,3.6rem)] font-semibold leading-[1.02] tracking-[-0.052em] text-white max-sm:text-[clamp(2.25rem,11vw,3.1rem)]">{{ __('messages.public_cta_title') }}<br><em class="not-italic text-[#f0c75e]">{{ __('messages.public_cta_title_highlight') }}</em></h2>
    </div>
    <div>
      <p class="mb-7 text-white/90">{{ __('messages.public_cta_text') }}</p>
      <a href="{{ route('register') }}" class="group inline-flex min-h-[52px] min-w-[270px] items-center justify-center gap-2.5 rounded-full border border-[#f6d77d] bg-brand-accent px-[22px] text-[0.95rem] font-semibold text-brand-primary no-underline shadow-[0_16px_36px_rgba(0,0,0,0.22)] transition-all btn-sweep btn-sweep-navy duration-300 ease-out hover:-translate-y-0.5 hover:scale-[1.02] hover:text-white hover:shadow-[0_20px_42px_rgba(0,0,0,0.18)] cursor-pointer max-sm:w-full">{{ __('messages.public_cta_button') }} <span aria-hidden="true" class="inline-block transition-transform duration-300 group-hover:translate-x-0.5 group-hover:-translate-y-0.5"><i data-lucide="arrow-up-right" class="h-4 w-4"></i></span></a>
    </div>
  </div>
</section>
