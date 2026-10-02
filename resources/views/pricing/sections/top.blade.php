<section class="border-b border-[#dce8e4] bg-[#f3f7f5] pb-[82px] pt-[72px] max-md:pb-[65px] max-md:pt-[58px]" aria-labelledby="tariff-title">
  <div class="mx-auto grid max-w-[1200px] items-center gap-[84px] px-6 max-md:gap-[34px] max-md:px-4 lg:grid-cols-[1fr_0.78fr]">
    <div>
      <p class="mb-[15px] text-[0.72rem] font-bold uppercase tracking-[0.15em] text-[#2d7080]">{{ __('messages.public_pricing_eyebrow') }} <span aria-hidden="true" class="mx-2 text-[#8ea3aa]">/</span> {{ __('messages.public_pricing_eyebrow_suffix') }}</p>
      <h1 id="tariff-title" class="max-w-[680px] text-[clamp(2.5rem,4.1vw,3.6rem)] font-semibold leading-[1.08] tracking-[-0.052em] text-brand-primary sm:text-[clamp(2.3rem,11vw,3rem)]">{{ __('messages.public_pricing_title') }}</h1>
      <p class="mt-[23px] max-w-[630px] text-[1.03rem] text-[#5b6b73]">{{ __('messages.public_pricing_intro') }}</p>
      <a href="#offres" class="mt-[17px] inline-block text-[0.89rem] font-semibold text-brand-secondary underline underline-offset-[5px] transition-colors duration-300 hover:text-[#245f6d]">{{ __('messages.public_pricing_compare') }} <span aria-hidden="true" class="pl-1"><i data-lucide="arrow-down" class="inline h-4 w-4"></i></span></a>
    </div>
    <div class="motion-safe:animate-float rounded-xl border border-[#dbe7e3] border-t-4 border-t-brand-accent bg-white p-[30px] shadow-[0_14px_34px_rgba(11,38,56,0.06)] max-md:max-w-[610px] max-sm:p-[23px] max-sm:px-[17px]" aria-labelledby="finder-title">
      <p class="text-[0.68rem] font-extrabold uppercase tracking-[0.15em] text-brand-secondary">{{ __('messages.public_pricing_finder') }}</p>
      <h2 id="finder-title" class="mb-6 mt-[7px] text-[1.65rem] font-semibold tracking-[-0.035em] text-brand-primary">{{ __('messages.public_pricing_finder_title') }}</h2>
      <div class="grid grid-cols-3 gap-2 max-sm:gap-[5px]" role="group" aria-label="{{ __('messages.public_pricing_finder_group_label') }}">
        <button type="button" @click="plan = 'solo'" :aria-pressed="plan === 'solo'" :class="plan === 'solo' ? 'bg-brand-primary text-white border-brand-primary' : 'bg-[#f7faf8] text-brand-primary border-[#cddfda]'" class="min-h-[56px] rounded-lg border px-2.5 py-2 text-[0.82rem] font-bold transition-all duration-300 cursor-pointer hover:border-brand-secondary max-sm:px-[5px] max-sm:text-[0.72rem]">{{ __('messages.public_pricing_plan_1') }}</button>
        <button type="button" @click="plan = 'premium'" :aria-pressed="plan === 'premium'" :class="plan === 'premium' ? 'bg-brand-primary text-white border-brand-primary' : 'bg-[#f7faf8] text-brand-primary border-[#cddfda]'" class="min-h-[56px] rounded-lg border px-2.5 py-2 text-[0.82rem] font-bold transition-all duration-300 cursor-pointer hover:border-brand-secondary max-sm:px-[5px] max-sm:text-[0.72rem]">{{ __('messages.public_pricing_plan_2') }}</button>
        <button type="button" @click="plan = 'pro'" :aria-pressed="plan === 'pro'" :class="plan === 'pro' ? 'bg-brand-primary text-white border-brand-primary' : 'bg-[#f7faf8] text-brand-primary border-[#cddfda]'" class="min-h-[56px] rounded-lg border px-2.5 py-2 text-[0.82rem] font-bold transition-all duration-300 cursor-pointer hover:border-brand-secondary max-sm:px-[5px] max-sm:text-[0.72rem]">{{ __('messages.public_pricing_plan_3') }}</button>
      </div>
      <div class="mt-[21px] min-h-[104px] rounded-lg bg-[#ecf4f1] px-[19px] py-[17px]" aria-live="polite">
        <span class="text-[0.65rem] font-extrabold uppercase tracking-[0.12em] text-brand-secondary">{{ __('messages.public_pricing_indication') }}</span>
        <p class="mt-[5px] text-[0.89rem] font-bold text-brand-primary" x-text="plan ? messages[plan] : defaultMessage">{{ __('messages.public_pricing_default_message') }}</p>
      </div>
      <small class="mt-[13px] block text-[0.74rem] text-[#5b6b73]">{{ __('messages.public_pricing_trial_note') }}</small>
    </div>
  </div>
</section>
