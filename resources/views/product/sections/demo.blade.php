<section id="demonstration" class="scroll-mt-24 bg-[#edf5f4] py-28 max-md:py-20" aria-labelledby="demo-title">
  <div class="mx-auto grid max-w-[1200px] items-center gap-[78px] px-6 max-md:gap-[34px] max-md:px-4 lg:grid-cols-[1.1fr_0.9fr] max-md:grid-cols-1">
    <div class="grid min-h-[480px] content-center justify-items-center rounded-[22px] border border-dashed border-[#8ca6ad] bg-white p-[42px] text-center shadow-[0_18px_50px_rgba(7,27,50,0.08)] max-sm:min-h-[390px] max-sm:p-[25px]">
      <button type="button" aria-label="{{ __('messages.public_demo_button') }}" class="mb-7 grid h-[72px] w-[72px] cursor-pointer place-items-center rounded-full border-8 border-white/70 bg-brand-secondary text-white transition-all duration-300 ease-out hover:scale-105 hover:bg-[#245f6d]"><i data-lucide="play" class="h-6 w-6 fill-current"></i></button>
      <small class="text-[0.67rem] font-bold uppercase tracking-[0.13em] text-brand-secondary">{{ __('messages.public_demo_eyebrow') }}</small>
      <strong class="mt-2 block text-[1.08rem] font-bold text-brand-primary">{{ __('messages.public_demo_title') }}</strong>
      <p class="mx-auto mt-[7px] max-w-[470px] text-[0.82rem] leading-[1.55] text-[#5b6b73]">{{ __('messages.public_demo_intro') }}</p>
    </div>
    <div>
      <p class="mb-5 flex items-center gap-2.5 text-[0.75rem] font-bold uppercase tracking-[0.13em] text-brand-secondary"><span class="h-0.5 w-7 bg-brand-accent"></span>{{ __('messages.public_demo_eyebrow') }}</p>
      <h2 id="demo-title" class="text-[clamp(2.5rem,3.65vw,3.4rem)] font-semibold leading-[1.05] tracking-[-0.052em] text-brand-primary max-sm:text-[2.1rem]">{{ __('messages.public_demo_title') }}</h2>
      <p class="mt-6 text-[1.03rem] text-[#5b6b73]">{{ __('messages.public_demo_intro') }}</p>
      <div class="mt-[31px] flex flex-wrap items-center gap-[13px] max-sm:flex-col max-sm:items-stretch">
        <a href="{{ route('demo') }}" class="group inline-flex min-h-[52px] items-center justify-center gap-2.5 rounded-full border border-transparent btn-sweep btn-sweep-navy bg-brand-secondary px-[22px] text-[0.95rem] font-semibold text-white no-underline shadow-[0_12px_28px_rgba(45,112,128,0.2)] transition-all duration-300 ease-out hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-[0_16px_34px_rgba(45,112,128,0.3)] cursor-pointer max-sm:w-full">{{ __('messages.public_demo_button') }}</a>
        <a href="{{ route('contact') }}" class="group self-center px-[13px] py-[6px] font-semibold text-brand-secondary no-underline transition-colors duration-300 hover:text-[#245f6d]">{{ __('messages.public_demo_contact') }} <span aria-hidden="true" class="ml-2 inline-block transition-transform duration-300 group-hover:translate-x-1"><i data-lucide="arrow-right" class="inline h-4 w-4"></i></span></a>
      </div>
    </div>
  </div>
</section>
