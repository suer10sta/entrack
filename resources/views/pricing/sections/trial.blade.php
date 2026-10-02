<section class="bg-brand-primary py-[38px] text-white" aria-labelledby="trial-title">
  <div class="mx-auto grid max-w-[1200px] items-center gap-[35px] px-6 max-md:gap-[22px] max-md:px-4 max-sm:grid-cols-1 lg:grid-cols-[92px_1fr_auto]">
    <div class="max-sm:flex max-sm:flex-col  max-sm:gap-[13px]">
      <strong class="block text-[3.7rem] font-bold leading-none tracking-[-0.065em] text-brand-accent">15</strong>
      <span class="text-[0.65rem] font-bold uppercase leading-[1.4] tracking-[0.08em] text-[#b5d4d4]">{{ __('messages.public_pricing_trial_days') }}</span>
    </div>
    <div>
      <p class="mb-[7px] text-[0.72rem] font-bold uppercase tracking-[0.15em] text-[#8ccfd0]">{{ __('messages.public_pricing_trial_label') }}</p>
      <h2 id="trial-title" class="text-[1.9rem] font-bold tracking-[-0.035em] text-white">{{ __('messages.public_pricing_trial_heading') }}</h2>
      <p class="mt-[9px] max-w-[625px] text-[0.87rem] text-[#c8d6d8]">{{ __('messages.public_pricing_trial_text') }}</p>
    </div>
    <a href="{{ $trialLink }}" class="group inline-flex min-h-[52px] min-w-[245px] items-center justify-center gap-2.5 rounded-full border border-transparent btn-sweep btn-sweep-navy bg-brand-accent px-[22px] text-[0.95rem] font-bold text-brand-primary no-underline shadow-[0_12px_28px_rgba(231,184,75,0.2)] transition-all duration-300 ease-out hover:-translate-y-0.5 hover:scale-[1.02] hover:text-white hover:shadow-[0_16px_34px_rgba(231,184,75,0.3)] cursor-pointer max-sm:w-full max-sm:min-w-0">{{ __('messages.public_pricing_trial_cta') }} <span aria-hidden="true" class="inline-block transition-transform duration-300 group-hover:translate-x-0.5 group-hover:-translate-y-0.5"><i data-lucide="arrow-up-right" class="h-4 w-4"></i></span></a>
  </div>
</section>
