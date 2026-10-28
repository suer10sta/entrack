<section class="relative overflow-hidden bg-[linear-gradient(180deg,#ffffff_0%,#eef5f4_100%)] pt-[76px] pb-[40px] max-md:pt-[58px]">
  <div class="mx-auto grid max-w-[1200px] justify-items-center px-6 text-center max-md:px-4">
    <p class="mb-5 flex items-center justify-center gap-2.5 text-[0.75rem] font-bold uppercase tracking-[0.13em] text-brand-secondary"><span class="h-0.5 w-7 bg-brand-accent"></span>{{ __('messages.product_hero_eyebrow') }}</p>
    <h1 class="max-w-[930px] text-[clamp(3rem,4.65vw,4.95rem)] font-semibold leading-[1.02] tracking-[-0.052em] text-brand-primary max-sm:text-[2.4rem]">{{ __('messages.product_hero_title') }} <em class="not-italic text-brand-secondary">{{ __('messages.product_hero_title_highlight') }}</em></h1>
    <p class="mt-[27px] max-w-[760px] text-[1.1rem] leading-[1.72] text-[#5b6b73]">{{ __('messages.product_hero_intro') }}</p>
    <div class="mt-[34px] flex flex-wrap justify-center gap-3 max-sm:flex-col max-sm:items-stretch">
      <a href="{{ route('register') }}" class="group inline-flex min-h-[52px] items-center justify-center gap-2.5 rounded-full border border-transparent btn-sweep btn-sweep-navy bg-brand-secondary px-[22px] text-[0.95rem] font-semibold text-white no-underline shadow-[0_12px_28px_rgba(45,112,128,0.2)] transition-all duration-300 ease-out hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-[0_16px_34px_rgba(45,112,128,0.3)] cursor-pointer max-sm:w-full">{{ __('messages.product_hero_cta_primary') }} <span aria-hidden="true" class="inline-block transition-transform duration-300 group-hover:translate-x-0.5 group-hover:-translate-y-0.5"><i data-lucide="arrow-up-right" class="h-4 w-4"></i></span></a>
      <a href="#modules" class="group inline-flex min-h-[52px] items-center justify-center gap-2.5 rounded-full border border-[#dce6e4] btn-sweep btn-sweep-teal bg-white px-[22px] text-[0.95rem] font-semibold text-brand-primary no-underline transition-all duration-300 ease-out hover:-translate-y-0.5 hover:border-brand-secondary hover:shadow-[0_14px_30px_rgba(7,27,50,0.1)] hover:text-white cursor-pointer max-sm:w-full">{{ __('messages.product_hero_cta_secondary') }} <span aria-hidden="true" class="inline-block transition-transform duration-300 group-hover:translate-y-0.5"><i data-lucide="arrow-down" class="h-4 w-4"></i></span></a>
    </div>
    <div class="mt-[30px] flex flex-wrap justify-center gap-x-[22px] gap-y-2.5 text-[0.84rem] font-medium text-[#496076] max-sm:grid" aria-label="{{ __('messages.product_hero_trial_conditions') }}">
      <span class="flex items-center gap-[7px]"><span class="grid h-[21px] min-w-[21px] place-items-center rounded-full border border-[#dce6e4] bg-white text-brand-secondary"><i data-lucide="check" class="h-3 w-3"></i></span> {{ __('messages.product_hero_trial_days') }}</span>
      <span class="flex items-center gap-[7px]"><span class="grid h-[21px] min-w-[21px] place-items-center rounded-full border border-[#dce6e4] bg-white text-brand-secondary"><i data-lucide="check" class="h-3 w-3"></i></span> {{ __('messages.product_hero_trial_features') }}</span>
      <span class="flex items-center gap-[7px]"><span class="grid h-[21px] min-w-[21px] place-items-center rounded-full border border-[#dce6e4] bg-white text-brand-secondary"><i data-lucide="infinity" class="h-3 w-3"></i></span> {{ __('messages.product_hero_trial_users') }}</span>
    </div>
  </div>

  <div class="relative motion-safe:animate-float z-[2] mx-auto mt-[70px] max-w-[1440px] overflow-hidden rounded-[20px] border border-[#071b32]/10 bg-white shadow-[0_28px_80px_rgba(7,27,50,0.14)] max-md:mt-[52px]" aria-label="{{ __('messages.product_hero_mockup_label') }}">
    <div class="flex h-[46px] items-center gap-[7px] border-b border-[#dce6e4] bg-[#f5f8f8] px-[18px]"><span class="h-2 w-2 rounded-full bg-[#c6d3d6]"></span><span class="h-2 w-2 rounded-full bg-[#c6d3d6]"></span><span class="h-2 w-2 rounded-full bg-[#c6d3d6]"></span><small class="ml-auto text-[0.64rem] font-bold uppercase tracking-[0.11em] text-[#788990]">{{ __('messages.product_hero_mockup_view') }}</small></div>
    <img src="{{ asset('assets/images/dalytrac-product-dashboard-demo.png') }}" alt="{{ __('messages.product_hero_visual_title') }}" width="1704" height="923" class="block h-auto w-full" decoding="async">

  </div>
</section>
