<section data-home-section="demo" id="demo" class="scroll-mt-24 bg-[#16394a] py-28 text-white max-md:py-20" aria-labelledby="demo-title">
  <div class="mx-auto grid max-w-[1200px] items-center gap-[70px] px-6 max-md:grid-cols-1 max-md:gap-[38px] max-md:px-4 lg:grid-cols-[1.05fr_0.95fr]">
    <div class="grid min-h-[460px] content-center justify-items-center rounded-[22px] border border-dashed border-[#8eacbe] bg-[repeating-linear-gradient(-45deg,#e9f6fa,#e9f6fa_12px,#dff0f5_12px,#dff0f5_24px)] p-[34px] text-center text-[#1b3444]">
      <span class="text-[0.68rem] font-bold uppercase tracking-[0.12em] text-brand-secondary">{{ __('messages.public_demo_eyebrow') }}</span>
      <strong class="mt-[7px] text-[1rem] text-brand-primary">{{ __('messages.public_demo_title') }}</strong>
      <!-- <p class="mx-auto mt-1.5 max-w-[340px] text-[0.78rem] leading-[1.5] text-[#5b6b73]">{{ __('messages.public_demo_intro') }}</p> -->
      <button type="button" aria-label="{{ __('messages.public_demo_button') }}" class="mt-7 grid h-[68px] w-[68px] cursor-pointer place-items-center rounded-full border-0 bg-brand-secondary text-white transition-all duration-300 ease-out hover:scale-105 hover:bg-[#245f6d] hover:shadow-[0_16px_40px_rgba(45,112,128,0.4)]"><i data-lucide="play" class="h-6 w-6 fill-current"></i></button>
    </div>
    <div>
      <p class="mb-5 flex items-center gap-2.5 text-[0.75rem] font-semibold uppercase tracking-[0.13em] text-[#8ccfd0]"><span class="h-0.5 w-7 bg-brand-accent"></span>{{ __('messages.public_demo_eyebrow') }}</p>
      <h2 id="demo-title" class="text-[clamp(2.5rem,4vw,3.8rem)] font-semibold leading-[1.02] tracking-[-0.052em] text-white">{{ __('messages.public_demo_title') }}<br><em class="not-italic text-brand-secondary">{{ __('messages.public_demo_title_highlight') }}</em></h2>
      <p class="mt-[25px] text-[#b3c7d7]">{{ __('messages.public_demo_intro') }}</p>
      <div class="mt-[34px] flex flex-wrap gap-3 max-sm:flex-col max-sm:items-stretch">
        <a href="{{ route('demo') }}" class="inline-flex min-h-[52px] items-center justify-center gap-2.5 rounded-full border border-transparent btn-sweep btn-sweep-navy bg-brand-accent px-[22px] text-[0.95rem] font-semibold text-brand-primary no-underline shadow-[0_12px_28px_rgba(231,184,75,0.2)] transition-all duration-300 ease-out hover:-translate-y-0.5 hover:scale-[1.02] hover:text-white hover:shadow-[0_16px_34px_rgba(231,184,75,0.3)] cursor-pointer max-sm:w-full">{{ __('messages.public_demo_button') }}</a>
        <a href="{{ route('contact') }}" class="inline-flex min-h-[52px] items-center justify-center gap-2.5 rounded-full border border-white/30 btn-sweep btn-sweep-white bg-transparent px-[22px] text-[0.95rem] font-semibold text-white no-underline transition-all duration-300 ease-out hover:-translate-y-0.5 hover:border-white hover:text-brand-primary cursor-pointer max-sm:w-full">{{ __('messages.public_demo_contact') }}</a>
      </div>
    </div>
  </div>
</section>
