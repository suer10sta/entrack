<section class="bg-[#edf5f4] py-28 max-md:py-20" aria-labelledby="roles-title">
  <div class="mx-auto max-w-[1200px] px-6 max-md:px-4">
    <div class="mb-[62px] max-w-[790px] text-start lg:mx-auto lg:text-center">
      <p class="mb-5 flex items-center justify-start gap-2.5 text-[0.75rem] font-bold uppercase tracking-[0.13em] text-brand-secondary lg:justify-center"><span class="h-0.5 w-7 bg-brand-accent"></span>{{ __('messages.public_roles_eyebrow') }}</p>
      <h2 id="roles-title" class="text-[clamp(2.5rem,4.1vw,4rem)] font-semibold  leading-[1.02] tracking-[-0.052em] text-brand-primary max-sm:text-[clamp(2.25rem,11vw,3.1rem)]">{{ __('messages.public_roles_title') }}<br><em class="not-italic text-brand-secondary">{{ __('messages.public_roles_title_highlight') }}</em></h2>
      <p class="mt-6 max-w-[630px] text-[1.05rem] text-[#5b6b73] lg:mx-auto">{{ __('messages.public_roles_intro') }}</p>
    </div>
    <div class="grid gap-[14px] sm:grid-cols-2 lg:grid-cols-4 max-sm:grid-cols-1">
      <article class="group relative min-h-[330px] rounded-[17px] border border-[#071b32]/10 bg-white px-[26px] py-[30px] transition-all duration-300 ease-out hover:-translate-y-1 hover:shadow-[0_18px_45px_rgba(7,27,50,0.12)]">
        <span class="grid h-[50px] w-[50px] place-items-center rounded-[14px] bg-[#edf5f4] text-[1.15rem] font-bold text-brand-secondary transition-transform duration-300 group-hover:scale-110">A</span>
        <small class="absolute right-6 top-[31px] font-bold text-[#9badbb]">01</small>
        <h3 class="mt-[62px] text-[1.2rem] font-semibold text-brand-primary">{{ __('messages.public_roles_admin') }}</h3>
        <p class="mt-3 text-[0.88rem] text-[#5b6b73]">{{ __('messages.public_roles_card_1_text') }}</p>
      </article>
      <article class="group relative min-h-[330px] rounded-[17px] border border-[#071b32]/10 bg-white px-[26px] py-[30px] transition-all duration-300 ease-out hover:-translate-y-1 hover:shadow-[0_18px_45px_rgba(7,27,50,0.12)]">
        <span class="grid h-[50px] w-[50px] place-items-center rounded-[14px] bg-[#edf5f4] text-[1.15rem] font-bold text-brand-secondary transition-transform duration-300 group-hover:scale-110">G</span>
        <small class="absolute right-6 top-[31px] font-bold text-[#9badbb]">02</small>
        <h3 class="mt-[62px] text-[1.2rem] font-semibold text-brand-primary">{{ __('messages.public_roles_manager') }}</h3>
        <p class="mt-3 text-[0.88rem] text-[#5b6b73]">{{ __('messages.public_roles_card_2_text') }}</p>
      </article>
      <article class="group relative min-h-[330px] rounded-[17px] border border-[#071b32]/10 bg-white px-[26px] py-[30px] transition-all duration-300 ease-out hover:-translate-y-1 hover:shadow-[0_18px_45px_rgba(7,27,50,0.12)]">
        <span class="grid h-[50px] w-[50px] place-items-center rounded-[14px] bg-[#edf5f4] text-[1.15rem] font-bold text-brand-secondary transition-transform duration-300 group-hover:scale-110">P</span>
        <small class="absolute right-6 top-[31px] font-bold text-[#9badbb]">03</small>
        <h3 class="mt-[62px] text-[1.2rem] font-semibold text-brand-primary">{{ __('messages.public_roles_project_lead') }}</h3>
        <p class="mt-3 text-[0.88rem] text-[#5b6b73]">{{ __('messages.public_roles_card_3_text') }}</p>
      </article>
      <article class="group relative min-h-[330px] rounded-[17px] border border-[#071b32]/10 bg-brand-secondary px-[26px] py-[30px] text-white transition-all duration-300 ease-out hover:-translate-y-1 hover:shadow-[0_18px_45px_rgba(7,27,50,0.25)]">
        <span class="grid h-[50px] w-[50px] place-items-center rounded-[14px] bg-[#8ccfd0] text-[1.15rem] font-bold text-brand-primary transition-transform duration-300 group-hover:scale-110">E</span>
        <small class="absolute right-6 top-[31px] font-bold text-white/70">04</small>
        <h3 class="mt-[62px] text-[1.2rem] font-semibold text-white">{{ __('messages.public_roles_employee') }}</h3>
        <p class="mt-3 text-[0.88rem] text-white/80">{{ __('messages.public_roles_card_4_text') }}</p>
      </article>
    </div>
  </div>
</section>
