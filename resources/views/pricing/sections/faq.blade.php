<section class="bg-white py-[85px] max-md:py-[76px]" aria-labelledby="questions-title">
  <div class="mx-auto grid max-w-[1200px] gap-[90px] px-6 max-md:grid-cols-1 max-md:gap-[34px] max-md:px-4 lg:grid-cols-[0.72fr_1.28fr]">
    <div>
      <p class="mb-[15px] text-[0.72rem] font-bold uppercase tracking-[0.15em] text-[#2d7080]">{{ __('messages.public_pricing_faq_title') }}</p>
      <h2 id="questions-title" class="text-[clamp(2.2rem,3.2vw,3.3rem)] font-semibold leading-[1.1] tracking-[-0.052em] text-brand-primary">{{ __('messages.public_pricing_faq_title') }}</h2>
      <a href="{{ route('contact') }}" class="group mt-6 inline-block text-[0.9rem] font-bold text-brand-secondary transition-colors duration-300 hover:text-[#245f6d]">{{ __('messages.public_pricing_faq_contact') }} <span aria-hidden="true" class="inline-block transition-transform duration-300 group-hover:translate-x-0.5 group-hover:-translate-y-0.5"><i data-lucide="arrow-up-right" class="inline h-4 w-4"></i></span></a>
    </div>
    <div class="mt-[2px] border-t border-[#dce6e4]">
      <details class="group border-b border-[#dce6e4]">
        <summary class="faq-summary flex min-h-[66px] cursor-pointer list-none items-center justify-between gap-5 font-semibold text-brand-primary transition-colors duration-300 hover:text-brand-secondary">{{ __('messages.public_pricing_faq_q1') }}<span class="text-brand-secondary transition-transform duration-200 group-open:rotate-45"><i data-lucide="plus" class="h-6 w-6"></i></span></summary>
        <p class="mb-6 mr-10 mt-[-4px] max-w-[670px] text-[#5b6b73]">{{ __('messages.public_pricing_faq_a1') }}</p>
      </details>
      <details class="group border-b border-[#dce6e4]">
        <summary class="faq-summary flex min-h-[66px] cursor-pointer list-none items-center justify-between gap-5 font-semibold text-brand-primary transition-colors duration-300 hover:text-brand-secondary">{{ __('messages.public_pricing_faq_q2') }}<span class="text-brand-secondary transition-transform duration-200 group-open:rotate-45"><i data-lucide="plus" class="h-6 w-6"></i></span></summary>
        <p class="mb-6 mr-10 mt-[-4px] max-w-[670px] text-[#5b6b73]">{{ __('messages.public_pricing_faq_a2') }}</p>
      </details>
      <details class="group border-b border-[#dce6e4]">
        <summary class="faq-summary flex min-h-[66px] cursor-pointer list-none items-center justify-between gap-5 font-semibold text-brand-primary transition-colors duration-300 hover:text-brand-secondary">{{ __('messages.public_pricing_faq_q3') }}<span class="text-brand-secondary transition-transform duration-200 group-open:rotate-45"><i data-lucide="plus" class="h-6 w-6"></i></span></summary>
        <p class="mb-6 mr-10 mt-[-4px] max-w-[670px] text-[#5b6b73]">{{ __('messages.public_pricing_faq_a3') }}</p>
      </details>
    </div>
  </div>
</section>
