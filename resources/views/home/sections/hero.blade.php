
<section data-home-section="hero" class="relative grid items-center overflow-hidden bg-[#f8faf9] lg:min-h-[760px]">
  <div aria-hidden="true" class="pointer-events-none absolute -top-[170px] -right-[160px] h-[480px] w-[480px] rounded-full bg-[radial-gradient(circle,rgba(140,207,208,0.2),rgba(140,207,208,0)_68%)]"></div>

  <div class="mx-auto grid w-full max-w-[1440px] items-center gap-[40px] px-6 py-[96px] max-lg:gap-[38px] max-md:px-4 max-md:py-[76px] max-md:pb-[92px] lg:grid-cols-[0.85fr_1.35fr]">
    <div class="relative z-[2]">
      <p class="mb-5 flex items-center gap-2.5 text-[0.75rem] font-semibold uppercase tracking-[0.13em] text-brand-secondary"><span class="h-0.5 w-7 bg-brand-accent"></span>{{ __('messages.public_home_eyebrow') }}</p>
      <h1 class="max-w-[720px] text-[4.2rem] font-semibold leading-[1.02] tracking-[-0.052em] text-brand-primary max-sm:text-[clamp(2.65rem,13vw,3.7rem)]">{{ __('messages.public_home_title') }} <em class="not-italic text-brand-secondary">{{ __('messages.public_home_title_highlight') }}</em></h1>
      <p class="mt-7 max-w-[660px] text-[1.12rem] leading-[1.72] text-[#5b6b73]">{{ __('messages.public_home_subtitle') }}</p>
      <div class="mt-[34px] flex flex-wrap gap-3 max-sm:flex-col max-sm:items-stretch">
        <a href="{{ route('register') }}" class="group inline-flex min-h-[52px] items-center justify-center gap-2.5 rounded-full btn-sweep btn-sweep-navy bg-brand-secondary px-[22px] text-[0.95rem] font-semibold text-white no-underline shadow-[0_12px_28px_rgba(45,112,128,0.2)] transition-all duration-300 ease-out hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-[0_16px_34px_rgba(45,112,128,0.3)] cursor-pointer max-sm:w-full">{{ __('messages.public_home_trial_button') }} <span aria-hidden="true" class="inline-block transition-transform duration-300 group-hover:translate-x-0.5 group-hover:-translate-y-0.5"><i data-lucide="arrow-up-right" class="h-4 w-4"></i></span></a>
        <a href="demo" class="group inline-flex min-h-[52px] items-center justify-center gap-2.5 rounded-full border border-[#dce6e4] btn-sweep btn-sweep-teal bg-white px-[22px] text-[0.95rem] font-semibold text-brand-primary no-underline transition-all duration-300 ease-out hover:-translate-y-0.5 hover:border-brand-secondary hover:shadow-[0_14px_30px_rgba(7,27,50,0.1)] hover:text-white cursor-pointer max-sm:w-full"><span aria-hidden="true" class="dt-hero-demo-icon grid h-[25px] w-[25px] flex-shrink-0 place-items-center rounded-full bg-brand-primary text-white transition-colors duration-300 group-hover:bg-white group-hover:text-brand-secondary"><svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M8 5v14l11-7z" /></svg></span> {{ __('messages.public_home_demo_button') }}</a>
      </div>
      <div class="dt-hero-trial-details mt-[30px] flex flex-wrap gap-x-[22px] gap-y-2.5 text-[0.84rem] font-bold text-[#496076] max-sm:grid" aria-label="{{ __('messages.public_home_trial_conditions_label') }}">
        <span class="flex items-center gap-[7px]"><span aria-hidden="true" class="dt-hero-trial-icon grid h-[21px] w-[21px] flex-shrink-0 place-items-center rounded-full border border-[#dce6e4] bg-white text-[0.7rem] text-brand-secondary">15</span> {{ __('messages.public_home_trial_days') }}</span>
        <span class="flex items-center gap-[7px]"><span aria-hidden="true" class="dt-hero-trial-icon grid h-[21px] w-[21px] flex-shrink-0 place-items-center rounded-full border border-[#dce6e4] bg-white text-brand-secondary"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 4 4L19 6" /></svg></span> {{ __('messages.public_home_trial_features') }}</span>
        <span class="flex items-center gap-[7px]"><span aria-hidden="true" class="dt-hero-trial-icon grid h-[21px] w-[21px] flex-shrink-0 place-items-center rounded-full border border-[#dce6e4] bg-white text-brand-secondary"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 12c-2-3-3-4-5-4a4 4 0 0 0 0 8c2 0 3-1 5-4s3-4 5-4a4 4 0 0 1 0 8c-2 0-3-1-5-4Z" /></svg></span> {{ __('messages.public_home_trial_users') }}</span>
      </div>
    </div>

    <div class="relative z-[2] motion-safe:animate-float max-lg:mr-[14px] max-md:mx-0 max-md:mt-[18px]" aria-label="{{ __('messages.public_home_visual_main_label') }}">
      <div style="width:480px;max-width:100%;margin-inline:auto" class="overflow-hidden rounded-[18px] border border-[#071b32]/10 bg-white shadow-[0_24px_70px_rgba(7,27,50,0.13)] transition-shadow duration-500 hover:shadow-[0_32px_90px_rgba(7,27,50,0.18)] lg:[transform:perspective(1400px)_rotateY(-4deg)_rotateX(1deg)]">
        <div class="flex h-[43px] items-center gap-1.5 border-b border-[#dce6e4] bg-[#f5f8fa] px-4"><span class="h-2 w-2 rounded-full bg-[#cbd7df]"></span><span class="h-2 w-2 rounded-full bg-[#cbd7df]"></span><span class="h-2 w-2 rounded-full bg-[#cbd7df]"></span><small class="ml-[15px] text-[0.68rem] text-[#8a9aa8]">app.dalytrac.com</small></div>
        <video autoplay muted loop playsinline controls preload="metadata" width="1080" height="1350" aria-label="{{ __('messages.public_demo_title') }}" class="block h-auto" style="width:100%;height:auto;aspect-ratio:4/5">
          <source src="{{ asset('assets/videos/teaser-linkedin-dalytrac.mp4') }}" type="video/mp4">
          {{ __('messages.Your browser does not support the video tag.') }}
        </video>
      </div>
      <!-- <div class="absolute -right-6 top-11 hidden min-w-[148px] rounded-[13px] border border-[#dce6e4] bg-white px-4 py-[13px] shadow-[0_14px_36px_rgba(7,27,50,0.13)] lg:block"><b class="block text-[1.05rem] text-brand-secondary">{{ __('messages.public_home_metric_margin_value') }}</b><span class="block text-[0.68rem] text-[#5b6b73]">{{ __('messages.public_home_metric_margin_label') }}</span></div>
      <div class="absolute -left-6 bottom-11 hidden min-w-[148px] rounded-[13px] border border-[#dce6e4] bg-white px-4 py-[13px] shadow-[0_14px_36px_rgba(7,27,50,0.13)] lg:block"><b class="block text-[1.05rem] text-brand-secondary">{{ __('messages.public_home_metric_time_value') }}</b><span class="block text-[0.68rem] text-[#5b6b73]">{{ __('messages.public_home_metric_time_label') }}</span></div> -->
    </div>
  </div>

</section>
