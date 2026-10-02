<section class="bg-[#16394a] py-28 text-white max-md:py-20" aria-labelledby="onboarding-title">
  <div class="mx-auto grid max-w-[1200px] items-center gap-[90px] px-6 max-md:gap-[55px] max-md:px-4 lg:grid-cols-[0.8fr_1.2fr] max-md:grid-cols-1">
    <div>
      <p class="mb-5 flex items-center gap-2.5 text-[0.75rem] font-bold uppercase tracking-[0.13em] text-[#8ccfd0]"><span class="h-0.5 w-7 bg-brand-accent"></span>{{ __('messages.product_onboarding_eyebrow') }}</p>
      <h2 id="onboarding-title" class="text-[clamp(2.6rem,3.9vw,4rem)] font-bold leading-[1.05] tracking-[-0.052em] text-white max-sm:text-[2.2rem]">{{ __('messages.product_onboarding_title') }} <em class="not-italic text-brand-accent">{{ __('messages.product_onboarding_title_highlight') }}</em></h2>
      <p class="mb-[30px] mt-6 text-[#b8cbd3]">{{ __('messages.product_onboarding_intro') }}</p>
      <a href="{{ route('register') }}" class="group inline-flex min-h-[52px] items-center justify-center gap-2.5 rounded-full border border-transparent btn-sweep btn-sweep-navy bg-brand-accent px-[22px] text-[0.95rem] font-semibold text-brand-primary no-underline shadow-[0_12px_28px_rgba(231,184,75,0.2)] transition-all duration-300 ease-out hover:-translate-y-0.5 hover:scale-[1.02] hover:text-white hover:shadow-[0_16px_34px_rgba(231,184,75,0.3)] cursor-pointer max-sm:w-full">{{ __('messages.product_onboarding_cta') }} <span aria-hidden="true" class="inline-block transition-transform duration-300 group-hover:translate-x-0.5 group-hover:-translate-y-0.5"><i data-lucide="arrow-up-right" class="h-4 w-4"></i></span></a>
    </div>
    <ol class="m-0 list-none border-t border-white/15 p-0">
      <li class="grid min-h-[145px] grid-cols-[74px_1fr] items-start gap-5 border-b border-white/15 py-[26px] max-sm:grid-cols-[57px_1fr]">
        <span class="grid h-12 w-12 place-items-center rounded-full bg-[#8ccfd0] text-[0.72rem] font-bold text-brand-primary">01</span>
        <div><small class="text-[0.62rem] font-bold uppercase tracking-[0.12em] text-[#8ccfd0]">{{ __('messages.product_onboarding_step1_label') }}</small><h3 class="mb-2 mt-[5px] text-[1.18rem] font-semibold text-white">{{ __('messages.product_onboarding_step1_title') }}</h3><p class="m-0 text-[0.86rem] text-[#b8cbd3]">{{ __('messages.product_onboarding_step1_text') }}</p></div>
      </li>
      <li class="grid min-h-[145px] grid-cols-[74px_1fr] items-start gap-5 border-b border-white/15 py-[26px] max-sm:grid-cols-[57px_1fr]">
        <span class="grid h-12 w-12 place-items-center rounded-full bg-[#8ccfd0] text-[0.72rem] font-bold text-brand-primary">02</span>
        <div><small class="text-[0.62rem] font-bold uppercase tracking-[0.12em] text-[#8ccfd0]">{{ __('messages.product_onboarding_step2_label') }}</small><h3 class="mb-2 mt-[5px] text-[1.18rem] font-semibold text-white">{{ __('messages.product_onboarding_step2_title') }}</h3><p class="m-0 text-[0.86rem] text-[#b8cbd3]">{{ __('messages.product_onboarding_step2_text') }}</p></div>
      </li>
      <li class="grid min-h-[145px] grid-cols-[74px_1fr] items-start gap-5 border-b border-white/15 py-[26px] max-sm:grid-cols-[57px_1fr]">
        <span class="grid h-12 w-12 place-items-center rounded-full bg-[#8ccfd0] text-[0.72rem] font-bold text-brand-primary">03</span>
        <div><small class="text-[0.62rem] font-bold uppercase tracking-[0.12em] text-[#8ccfd0]">{{ __('messages.product_onboarding_step3_label') }}</small><h3 class="mb-2 mt-[5px] text-[1.18rem] font-semibold text-white">{{ __('messages.product_onboarding_step3_title') }}</h3><p class="m-0 text-[0.86rem] text-[#b8cbd3]">{{ __('messages.product_onboarding_step3_text') }}</p></div>
      </li>
    </ol>
  </div>
</section>
