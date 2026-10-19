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

  <div class="relative motion-safe:animate-float z-[2] mx-auto mt-[70px] max-w-[1200px] overflow-hidden rounded-t-[20px] border border-b-0 border-[#071b32]/10 bg-white px-6 shadow-[0_28px_80px_rgba(7,27,50,0.14)] max-md:mt-[52px] max-md:px-4" aria-label="{{ __('messages.product_hero_mockup_label') }}">
    <div class="flex h-[46px] items-center gap-[7px] border-b border-[#dce6e4] bg-[#f5f8f8] px-[18px]"><span class="h-2 w-2 rounded-full bg-[#c6d3d6]"></span><span class="h-2 w-2 rounded-full bg-[#c6d3d6]"></span><span class="h-2 w-2 rounded-full bg-[#c6d3d6]"></span><small class="ml-auto text-[0.64rem] font-bold uppercase tracking-[0.11em] text-[#788990]">{{ __('messages.product_hero_mockup_view') }}</small></div>
    <div class="grid min-h-[545px] grid-cols-[78px_1fr] max-md:min-h-[480px] max-md:grid-cols-[56px_1fr] max-sm:min-h-[440px] max-sm:grid-cols-[42px_1fr]">
      <aside aria-hidden="true" class="flex flex-col items-center gap-6 bg-brand-primary py-[23px] max-md:gap-[18px]">
        <x-application-mark :size="34" /><i class="h-1.5 w-[26px] rounded bg-white/20 max-sm:w-5"></i><i class="h-1.5 w-[26px] rounded bg-white/20 max-sm:w-5"></i><i class="h-1.5 w-[26px] rounded bg-white/20 max-sm:w-5"></i><i class="h-1.5 w-[26px] rounded bg-white/20 max-sm:w-5"></i><i class="h-1.5 w-[26px] rounded bg-white/20 max-sm:w-5"></i><i class="h-1.5 w-[26px] rounded bg-white/20 max-sm:w-5"></i>
      </aside>
      <div class="bg-[#f7f9f9] px-9 pb-[34px] pt-8 max-md:p-6 max-sm:p-[17px]">
        <div aria-hidden="true" class="flex h-9 items-center justify-between"><div class="grid gap-[7px]"><i class="block h-[7px] w-[150px] rounded-[5px] bg-[#d7e1e2]"></i><i class="block h-[7px] w-[90px] rounded-[5px] bg-[#d7e1e2]"></i></div><span class="h-[30px] w-[110px] rounded-full bg-[#d5e8e8] max-sm:hidden"></span></div>
        <div aria-hidden="true" class="mt-6 grid grid-cols-4 gap-[13px] max-md:grid-cols-2"><i class="h-[78px] rounded-[10px] border border-[#dce6e4] bg-white max-sm:h-[54px]"></i><i class="h-[78px] rounded-[10px] border border-[#dce6e4] bg-white max-sm:h-[54px]"></i><i class="h-[78px] rounded-[10px] border border-[#dce6e4] bg-white max-sm:h-[54px]"></i><i class="h-[78px] rounded-[10px] border border-[#dce6e4] bg-white max-sm:h-[54px]"></i></div>
        <div class="mt-[15px] grid gap-[14px] lg:grid-cols-[1.5fr_0.5fr] max-md:grid-cols-1">
          <div class="grid min-h-[300px] content-center justify-items-center rounded-[13px] border border-dashed border-[#8fa9b0] bg-white p-10 text-center max-sm:min-h-[250px] max-sm:p-[22px]">
            <small class="text-[0.67rem] font-bold uppercase tracking-[0.13em] text-brand-secondary">{{ __('messages.product_hero_visual_label') }}</small>
            <strong class="mt-2 text-[1.08rem] font-bold text-brand-primary">{{ __('messages.product_hero_visual_title') }}</strong>
            <p class="mx-auto mt-[7px] max-w-[470px] text-[0.82rem] leading-[1.55] text-[#5b6b73]">{{ __('messages.product_hero_visual_text') }}</p>
          </div>
          <div aria-hidden="true" class="grid content-start gap-3 rounded-[13px] border border-[#dce6e4] bg-white p-[22px] max-md:hidden"><i class="h-[68px] rounded-lg bg-[#d7eaea]"></i><i class="h-[38px] rounded-lg bg-[#e8eeee]"></i><i class="h-[38px] rounded-lg bg-[#e8eeee]"></i><i class="h-[38px] rounded-lg bg-[#e8eeee]"></i></div>
        </div>
      </div>
    </div>
    <div class="flex min-h-[66px] items-center justify-center gap-[34px] border-t border-[#dce6e4] bg-white px-[34px] text-[0.78rem] font-medium text-[#53686f] max-lg:justify-start max-lg:gap-[25px] max-lg:overflow-x-auto max-lg:px-[18px] max-lg:whitespace-nowrap" aria-label="{{ __('messages.product_hero_tabs_label') }}">
      <span class="relative font-bold text-brand-secondary">{{ __('messages.product_hero_tab_projects') }}<span class="absolute -bottom-[23px] left-0 right-0 h-[3px] bg-brand-accent"></span></span><span>{{ __('messages.product_hero_tab_tasks') }}</span><span>{{ __('messages.product_hero_tab_time') }}</span><span>{{ __('messages.product_hero_tab_budgets') }}</span><span>{{ __('messages.product_hero_tab_billing') }}</span><span>{{ __('messages.product_hero_tab_dashboards') }}</span>
    </div>
  </div>
</section>
