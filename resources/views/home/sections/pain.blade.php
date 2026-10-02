<section class="bg-white py-28 max-md:py-20" aria-labelledby="pain-title">
  <div class="mx-auto max-w-[1200px] px-6 max-md:px-4">
    <div class="mb-14 grid items-end gap-20 max-md:grid-cols-1 max-md:gap-6 lg:grid-cols-[1.35fr_0.65fr]">
      <div>
        <p class="mb-5 flex items-center gap-2.5 text-[0.75rem] font-extrabold uppercase tracking-[0.13em] text-brand-secondary"><span class="h-0.5 w-7 bg-brand-accent"></span>{{ __('messages.public_pain_eyebrow') }}</p>
        <h2 id="pain-title" class="text-[4rem] font-semibold leading-[1.02] tracking-[-0.052em] text-brand-primary max-sm:text-[clamp(2.25rem,11vw,3.1rem)]">{{ __('messages.public_pain_title') }}<br><em class="not-italic text-brand-secondary">{{ __('messages.public_pain_title_highlight') }}</em></h2>
      </div>
      <p class="mb-[5px] max-w-[620px] text-[1.05rem] text-[#5b6b73]">{{ __('messages.public_pain_intro') }}</p>
    </div>
    <div class="grid border-l border-t border-[#dce6e4] md:grid-cols-3">
      <article class="relative min-h-[340px] border-b border-r border-[#dce6e4] bg-white p-[34px] transition-all duration-300 ease-out hover:-translate-y-1 hover:shadow-[0_18px_45px_rgba(7,27,50,0.08)] max-md:min-h-[270px]">
        <span class="text-[0.72rem] font-extrabold text-[#9aabba]">01</span>
        <div aria-hidden="true" class="mb-5 mt-11 text-brand-secondary max-md:mt-[30px]"><i data-lucide="timer" class="h-8 w-8"></i></div>
        <h3 class="text-[1.25rem] font-semibold leading-[1.25] text-brand-primary">{{ __('messages.public_pain_card_1_title') }}</h3>
        <p class="mt-3.5 text-[0.92rem] text-[#5b6b73]">{{ __('messages.public_pain_card_1_text') }}</p>
      </article>
      <article class="relative min-h-[340px] border-b border-r border-[#dce6e4] bg-white p-[34px] transition-all duration-300 ease-out hover:-translate-y-1 hover:shadow-[0_18px_45px_rgba(7,27,50,0.08)] max-md:min-h-[270px]">
        <span class="text-[0.72rem] font-extrabold text-[#9aabba]">02</span>
        <div aria-hidden="true" class="mb-5 mt-11 text-brand-secondary max-md:mt-[30px]"><i data-lucide="trending-down" class="h-8 w-8"></i></div>
        <h3 class="text-[1.25rem] font-semibold leading-[1.25] text-brand-primary">{{ __('messages.public_pain_card_2_title') }}</h3>
        <p class="mt-3.5 text-[0.92rem] text-[#5b6b73]">{{ __('messages.public_pain_card_2_text') }}</p>
      </article>
      <article class="relative min-h-[340px] border-b border-r border-[#dce6e4] bg-white p-[34px] transition-all duration-300 ease-out hover:-translate-y-1 hover:shadow-[0_18px_45px_rgba(7,27,50,0.08)] max-md:min-h-[270px]">
        <span class="text-[0.72rem] font-extrabold text-[#9aabba]">03</span>
        <div aria-hidden="true" class="mb-5 mt-11 text-brand-secondary max-md:mt-[30px]"><i data-lucide="receipt" class="h-8 w-8"></i></div>
        <h3 class="text-[1.25rem] font-semibold leading-[1.25] text-brand-primary">{{ __('messages.public_pain_card_3_title') }}</h3>
        <p class="mt-3.5 text-[0.92rem] text-[#5b6b73]">{{ __('messages.public_pain_card_3_text') }}</p>
      </article>
    </div>
  </div>
</section>
