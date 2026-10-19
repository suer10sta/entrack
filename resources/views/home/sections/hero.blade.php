
<section class="relative grid items-center overflow-hidden bg-[#f8faf9] lg:min-h-[760px]">
  <div aria-hidden="true" class="pointer-events-none absolute -top-[170px] -right-[160px] h-[480px] w-[480px] rounded-full bg-[radial-gradient(circle,rgba(140,207,208,0.2),rgba(140,207,208,0)_68%)]"></div>

  <div class="mx-auto grid w-full max-w-[1200px] items-center gap-[70px] px-6 py-[96px] max-lg:gap-[38px] max-md:px-4 max-md:py-[76px] max-md:pb-[92px] lg:grid-cols-[1.02fr_0.98fr]">
    <div class="relative z-[2]">
      <p class="mb-5 flex items-center gap-2.5 text-[0.75rem] font-semibold uppercase tracking-[0.13em] text-brand-secondary"><span class="h-0.5 w-7 bg-brand-accent"></span>{{ __('messages.public_home_eyebrow') }}</p>
      <h1 class="max-w-[720px] text-[4.2rem] font-semibold leading-[1.02] tracking-[-0.052em] text-brand-primary max-sm:text-[clamp(2.65rem,13vw,3.7rem)]">{{ __('messages.public_home_title') }} <em class="not-italic text-brand-secondary">{{ __('messages.public_home_title_highlight') }}</em></h1>
      <p class="mt-7 max-w-[660px] text-[1.12rem] leading-[1.72] text-[#5b6b73]">{{ __('messages.public_home_subtitle') }}</p>
      <div class="mt-[34px] flex flex-wrap gap-3 max-sm:flex-col max-sm:items-stretch">
        <a href="{{ route('register') }}" class="group inline-flex min-h-[52px] items-center justify-center gap-2.5 rounded-full btn-sweep btn-sweep-navy bg-brand-secondary px-[22px] text-[0.95rem] font-semibold text-white no-underline shadow-[0_12px_28px_rgba(45,112,128,0.2)] transition-all duration-300 ease-out hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-[0_16px_34px_rgba(45,112,128,0.3)] cursor-pointer max-sm:w-full">{{ __('messages.public_home_trial_button') }} <span aria-hidden="true" class="inline-block transition-transform duration-300 group-hover:translate-x-0.5 group-hover:-translate-y-0.5"><i data-lucide="arrow-up-right" class="h-4 w-4"></i></span></a>
        <a href="demo" class="group inline-flex min-h-[52px] items-center justify-center gap-2.5 rounded-full border border-[#dce6e4] btn-sweep btn-sweep-teal bg-white px-[22px] text-[0.95rem] font-semibold text-brand-primary no-underline transition-all duration-300 ease-out hover:-translate-y-0.5 hover:border-brand-secondary hover:shadow-[0_14px_30px_rgba(7,27,50,0.1)] hover:text-white cursor-pointer max-sm:w-full"><span aria-hidden="true" class="grid h-[25px] w-[25px] place-items-center rounded-full bg-brand-primary text-white transition-colors duration-300 group-hover:bg-white group-hover:text-brand-secondary"><i data-lucide="play" class="h-3 w-3 fill-current"></i></span> {{ __('messages.public_home_demo_button') }}</a>
      </div>
      <div class="mt-[30px] flex flex-wrap gap-x-[22px] gap-y-2.5 text-[0.84rem] font-bold text-[#496076] max-sm:grid" aria-label="{{ __('messages.public_home_trial_conditions_label') }}">
        <span class="flex items-center gap-[7px]"><i class="grid h-[21px] min-w-[21px] place-items-center rounded-full border border-[#dce6e4] bg-white text-[0.7rem] not-italic text-brand-secondary">15</i> {{ __('messages.public_home_trial_days') }}</span>
        <span class="flex items-center gap-[7px]"><span class="grid h-[21px] min-w-[21px] place-items-center rounded-full border border-[#dce6e4] bg-white text-brand-secondary"><i data-lucide="check" class="h-3 w-3"></i></span> {{ __('messages.public_home_trial_features') }}</span>
        <span class="flex items-center gap-[7px]"><span class="grid h-[21px] min-w-[21px] place-items-center rounded-full border border-[#dce6e4] bg-white text-brand-secondary"><i data-lucide="infinity" class="h-3 w-3"></i></span> {{ __('messages.public_home_trial_users') }}</span>
      </div>
    </div>

    <div class="relative z-[2] motion-safe:animate-float max-lg:mr-[14px] max-md:mx-0 max-md:mt-[18px]" aria-label="{{ __('messages.public_home_visual_main_label') }}">
      <div class="overflow-hidden rounded-[18px] border border-[#071b32]/10 bg-white shadow-[0_24px_70px_rgba(7,27,50,0.13)] transition-shadow duration-500 hover:shadow-[0_32px_90px_rgba(7,27,50,0.18)] lg:[transform:perspective(1400px)_rotateY(-4deg)_rotateX(1deg)]">
        <div class="flex h-[43px] items-center gap-1.5 border-b border-[#dce6e4] bg-[#f5f8fa] px-4"><span class="h-2 w-2 rounded-full bg-[#cbd7df]"></span><span class="h-2 w-2 rounded-full bg-[#cbd7df]"></span><span class="h-2 w-2 rounded-full bg-[#cbd7df]"></span><small class="ml-[15px] text-[0.68rem] text-[#8a9aa8]">app.dalytrac.com</small></div>
        <div class="grid min-h-[420px] grid-cols-[66px_1fr] max-sm:min-h-[350px] max-sm:grid-cols-[45px_1fr]">
          <aside aria-hidden="true" class="flex flex-col items-center gap-5 bg-brand-primary py-[19px] max-sm:gap-4">
            <x-application-mark :size="30" /><i class="h-1.5 w-[22px] rounded bg-white/20"></i><i class="h-1.5 w-[22px] rounded bg-white/20"></i><i class="h-1.5 w-[22px] rounded bg-white/20"></i><i class="h-1.5 w-[22px] rounded bg-white/20"></i><i class="h-1.5 w-[22px] rounded bg-white/20"></i>
          </aside>
          <div class="bg-[repeating-linear-gradient(-45deg,#fbfdfe,#fbfdfe_10px,#f5f9fb_10px,#f5f9fb_20px)] p-[30px] max-sm:p-[18px]">
            <div class="grid min-h-[168px] place-content-center border border-dashed border-[#9bb3c4] bg-white/90 p-7 text-center"><small class="text-[0.68rem] font-extrabold uppercase tracking-[0.12em] text-brand-secondary">{{ __('messages.public_home_visual_1_label') }}</small><strong class="mt-[7px] text-[1rem] text-brand-primary">{{ __('messages.public_home_visual_1_title') }}</strong><p class="mx-auto mt-1.5 max-w-[340px] text-[0.78rem] leading-[1.5] text-[#5b6b73]">{{ __('messages.public_home_visual_1_text') }}</p></div>
            <div class="mt-[14px] grid grid-cols-3 gap-3 max-sm:grid-cols-1" aria-hidden="true"><span class="min-h-[64px] rounded-[10px] border border-[#dce6e4] bg-white/90 max-sm:min-h-[36px]"></span><span class="min-h-[64px] rounded-[10px] border border-[#dce6e4] bg-white/90 max-sm:min-h-[36px]"></span><span class="min-h-[64px] rounded-[10px] border border-[#dce6e4] bg-white/90 max-sm:min-h-[36px]"></span></div>
            <div class="mt-[14px] grid grid-cols-[1.5fr_1fr] gap-3 max-sm:grid-cols-1" aria-hidden="true"><span class="min-h-[88px] rounded-[10px] border border-[#dce6e4] bg-white/90 max-sm:min-h-[55px]"></span><span class="min-h-[88px] rounded-[10px] border border-[#dce6e4] bg-white/90 max-sm:min-h-[55px]"></span></div>
          </div>
        </div>
      </div>
      <div class="absolute -right-6 top-11 hidden min-w-[148px] rounded-[13px] border border-[#dce6e4] bg-white px-4 py-[13px] shadow-[0_14px_36px_rgba(7,27,50,0.13)] lg:block"><b class="block text-[1.05rem] text-brand-secondary">{{ __('messages.public_home_metric_margin_value') }}</b><span class="block text-[0.68rem] text-[#5b6b73]">{{ __('messages.public_home_metric_margin_label') }}</span></div>
      <div class="absolute -left-6 bottom-11 hidden min-w-[148px] rounded-[13px] border border-[#dce6e4] bg-white px-4 py-[13px] shadow-[0_14px_36px_rgba(7,27,50,0.13)] lg:block"><b class="block text-[1.05rem] text-brand-secondary">{{ __('messages.public_home_metric_time_value') }}</b><span class="block text-[0.68rem] text-[#5b6b73]">{{ __('messages.public_home_metric_time_label') }}</span></div>
    </div>
  </div>
  <div aria-hidden="true" class="absolute bottom-0 left-0 text-[6rem] font-semibold leading-none text-[#071b32]/10 max-sm:hidden xl:left-[max(1.25rem,calc((100vw-1200px)/2-76px))]">01</div>
</section>
