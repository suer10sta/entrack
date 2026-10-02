<section class="bg-white py-28 max-md:py-20" aria-labelledby="audience-title">
  <div class="mx-auto max-w-[1200px] px-6 max-md:px-4">
    <div class="mb-14 grid items-end gap-20 max-md:grid-cols-1 max-md:gap-6 lg:grid-cols-[1.35fr_0.65fr]">
      <div>
        <p class="mb-5 flex items-center gap-2.5 text-[0.75rem] font-bold uppercase tracking-[0.13em] text-brand-secondary"><span class="h-0.5 w-7 bg-brand-accent"></span>{{ __('messages.public_audience_eyebrow') }}</p>
        <h2 id="audience-title" class="text-[clamp(2.5rem,4.1vw,4.15rem)] font-semibold leading-[1.02] tracking-[-0.052em] text-brand-primary max-sm:text-[clamp(2.25rem,11vw,3.1rem)]">{{ __('messages.public_audience_title') }}<br><em class="not-italic text-brand-secondary">{{ __('messages.public_audience_title_highlight') }}</em></h2>
      </div>
      <p class="mb-[5px] max-w-[620px] text-[1.05rem] text-[#5b6b73]">{{ __('messages.public_audience_intro') }}</p>
    </div>
    <div class="grid gap-[22px] md:grid-cols-3 max-md:grid-cols-1">
      <article class="group border-b-2 border-brand-primary pb-6 transition-all duration-300 ease-out hover:-translate-y-2">
        <img
          src="{{ asset('assets/images/dalytrac-pour-qui-pme-services.webp') }}"
          alt="{{ __('messages.public_audience_card_1_alt') }}"
          width="1200"
          height="827"
          loading="lazy"
          decoding="async"
          class="block h-auto w-full rounded-[14px] object-cover object-center aspect-[1200/827] transition-shadow duration-300 group-hover:shadow-[0_16px_40px_rgba(7,27,50,0.1)]"
        >
        <small class="mt-[22px] block text-[0.68rem] font-extrabold uppercase tracking-[0.12em] text-brand-secondary">{{ __('messages.public_audience_card_1_label') }}</small>
        <h3 class="mt-2.5 text-[1.18rem] leading-[1.35] font-bold text-brand-primary">{{ __('messages.public_audience_card_1_title') }}</h3>
      </article>
      <article class="group border-b-2 border-brand-primary pb-6 transition-all duration-300 ease-out hover:-translate-y-1">
        <img
          src="{{ asset('assets/images/dalytrac-pour-qui-bureau-etudes.webp') }}"
          alt="{{ __('messages.public_audience_card_2_alt') }}"
          width="1200"
          height="827"
          loading="lazy"
          decoding="async"
          class="block h-auto w-full rounded-[14px] object-cover object-center aspect-[1200/827] transition-shadow duration-300 group-hover:shadow-[0_16px_40px_rgba(7,27,50,0.1)]"
        >
        <small class="mt-[22px] block text-[0.68rem] font-extrabold uppercase tracking-[0.12em] text-brand-secondary">{{ __('messages.public_audience_card_2_label') }}</small>
        <h3 class="mt-2.5 text-[1.18rem] leading-[1.35] font-bold text-brand-primary">{{ __('messages.public_audience_card_2_title') }}</h3>
      </article>
      <article class="group border-b-2 border-brand-primary pb-6 transition-all duration-300 ease-out hover:-translate-y-1">
        <img
          src="{{ asset('assets/images/dalytrac-pour-qui-agence-cabinet.webp') }}"
          alt="{{ __('messages.public_audience_card_3_alt') }}"
          width="1200"
          height="827"
          loading="lazy"
          decoding="async"
          class="block h-auto w-full rounded-[14px] object-cover object-center aspect-[1200/827] transition-shadow duration-300 group-hover:shadow-[0_16px_40px_rgba(7,27,50,0.1)]"
        >
        <small class="mt-[22px] block text-[0.68rem] font-extrabold uppercase tracking-[0.12em] text-brand-secondary">{{ __('messages.public_audience_card_3_label') }}</small>
        <h3 class="mt-2.5 text-[1.18rem] leading-[1.35] font-bold text-brand-primary">{{ __('messages.public_audience_card_3_title') }}</h3>
      </article>
    </div>
  </div>
</section>
