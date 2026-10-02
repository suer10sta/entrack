<section class="bg-white py-28 max-md:py-20" aria-labelledby="faq-title">
  <div class="mx-auto grid max-w-[1200px] gap-[90px] px-6 max-md:grid-cols-1 max-md:gap-12 max-md:px-4 lg:grid-cols-[0.72fr_1.28fr]">
    <div>
      <p class="mb-5 flex items-center gap-2.5 text-[0.75rem] font-bold uppercase tracking-[0.13em] text-brand-secondary"><span class="h-0.5 w-7 bg-brand-accent"></span>{{ __('messages.public_faq_eyebrow') }}</p>
      <h2 id="faq-title" class="text-[clamp(2.7rem,4vw,4.25rem)] font-semibold leading-[1.02] tracking-[-0.052em] text-brand-primary max-sm:text-[clamp(2.25rem,11vw,3.1rem)]">{{ __('messages.public_faq_title') }}<br><em class="not-italic text-brand-secondary">{{ __('messages.public_faq_title_highlight') }}</em></h2>
      <p class="mt-6 text-[#5b6b73]">{{ __('messages.public_faq_intro') }}</p>
    </div>
    <div class="border-t border-[#dce6e4]">
      <details class="group border-b border-[#dce6e4]" open>
        <summary class="faq-summary flex min-h-[78px] cursor-pointer list-none items-center justify-between gap-5 font-bold text-brand-primary transition-colors duration-300 hover:text-brand-secondary">{{ __('messages.public_faq_question_1') }}<span class="text-brand-secondary transition-transform duration-200 group-open:rotate-45"><i data-lucide="plus" class="h-6 w-6"></i></span></summary>
        <p class="mb-6 mr-10 mt-[-4px] max-w-[670px] text-[#5b6b73]">{{ __('messages.public_faq_answer_1') }}</p>
      </details>
      <details class="group border-b border-[#dce6e4]">
        <summary class="faq-summary flex min-h-[78px] cursor-pointer list-none items-center justify-between gap-5 font-bold text-brand-primary transition-colors duration-300 hover:text-brand-secondary">{{ __('messages.public_faq_question_2') }}<span class="text-brand-secondary transition-transform duration-200 group-open:rotate-45"><i data-lucide="plus" class="h-6 w-6"></i></span></summary>
        <p class="mb-6 mr-10 mt-[-4px] max-w-[670px] text-[#5b6b73]">{{ __('messages.public_faq_answer_2') }}</p>
      </details>
      <details class="group border-b border-[#dce6e4]">
        <summary class="faq-summary flex min-h-[78px] cursor-pointer list-none items-center justify-between gap-5 font-bold text-brand-primary transition-colors duration-300 hover:text-brand-secondary">{{ __('messages.public_faq_question_3') }}<span class="text-brand-secondary transition-transform duration-200 group-open:rotate-45"><i data-lucide="plus" class="h-6 w-6"></i></span></summary>
        <p class="mb-6 mr-10 mt-[-4px] max-w-[670px] text-[#5b6b73]">{{ __('messages.public_faq_answer_3') }}</p>
      </details>
    </div>
  </div>
</section>
