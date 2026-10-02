<section id="modules" class="bg-[#f4f7f6] py-[135px] max-md:py-[90px]" aria-labelledby="modules-title">
  <div class="mx-auto grid max-w-[1200px] grid-cols-1 items-start gap-[30px] px-6 max-md:px-4 lg:grid-cols-[290px_1fr] lg:gap-[55px]">
    <aside class="lg:sticky lg:top-[116px] max-md:static">
      <p class="mb-5 flex items-center gap-2.5 text-[0.75rem] font-bold uppercase tracking-[0.13em] text-brand-secondary"><span class="h-0.5 w-7 bg-brand-accent"></span>{{ __('messages.product_modules_eyebrow') }}</p>
      <h2 id="modules-title" class="text-[2.65rem] font-semibold leading-[1.1] tracking-[-0.052em] text-brand-primary max-sm:text-[1.6rem]">{{ __('messages.product_modules_title') }}<br class="max-md:hidden">{{ __('messages.product_modules_title_highlight') }}</h2>
      <nav id="modules-nav" aria-label="{{ __('messages.product_modules_nav_label') }}" class="mt-[37px] grid border-t border-[#d5dfdd] max-md:grid-cols-2 max-md:gap-x-5 max-sm:grid-cols-1">
        <a href="#clients-projets" data-module-link="clients-projets" class="flex min-h-[52px] items-center gap-[13px] border-b border-[#d5dfdd] text-[0.83rem] font-medium text-[#5c7077] no-underline transition-all duration-300 hover:text-brand-secondary"><span class="text-[0.66rem] font-bold text-[#93a4a8] transition-colors duration-300">01</span>{{ __('messages.product_modules_nav_clients') }}</a>
        <a href="#taches" data-module-link="taches" class="flex min-h-[52px] items-center gap-[13px] border-b border-[#d5dfdd] text-[0.83rem] font-medium text-[#5c7077] no-underline transition-all duration-300 hover:text-brand-secondary"><span class="text-[0.66rem] font-bold text-[#93a4a8] transition-colors duration-300">02</span>{{ __('messages.product_modules_nav_tasks') }}</a>
        <a href="#temps" data-module-link="temps" class="flex min-h-[52px] items-center gap-[13px] border-b border-[#d5dfdd] text-[0.83rem] font-medium text-[#5c7077] no-underline transition-all duration-300 hover:text-brand-secondary"><span class="text-[0.66rem] font-bold text-[#93a4a8] transition-colors duration-300">03</span>{{ __('messages.product_modules_nav_time') }}</a>
        <a href="#budgets" data-module-link="budgets" class="flex min-h-[52px] items-center gap-[13px] border-b border-[#d5dfdd] text-[0.83rem] font-medium text-[#5c7077] no-underline transition-all duration-300 hover:text-brand-secondary"><span class="text-[0.66rem] font-bold text-[#93a4a8] transition-colors duration-300">04</span>{{ __('messages.product_modules_nav_budgets') }}</a>
        <a href="#facturation" data-module-link="facturation" class="flex min-h-[52px] items-center gap-[13px] border-b border-[#d5dfdd] text-[0.83rem] font-medium text-[#5c7077] no-underline transition-all duration-300 hover:text-brand-secondary"><span class="text-[0.66rem] font-bold text-[#93a4a8] transition-colors duration-300">05</span>{{ __('messages.product_modules_nav_billing') }}</a>
        <a href="#tableaux" data-module-link="tableaux" class="flex min-h-[52px] items-center gap-[13px] border-b border-[#d5dfdd] text-[0.83rem] font-medium text-[#5c7077] no-underline transition-all duration-300 hover:text-brand-secondary"><span class="text-[0.66rem] font-bold text-[#93a4a8] transition-colors duration-300">06</span>{{ __('messages.product_modules_nav_dashboards') }}</a>
      </nav>
    </aside>

    <div id="modules-content" class="grid gap-[26px]">
      <article id="clients-projets" data-module-article="clients-projets" class="scroll-mt-[105px] overflow-hidden rounded-[22px] border border-[#dce6e4] bg-white shadow-[0_15px_45px_rgba(7,27,50,0.055)] max-md:scroll-mt-20">
        <div class="px-[56px] pb-[38px] pt-[54px] max-lg:px-10 max-sm:px-[26px] max-sm:pb-[30px] max-sm:pt-[38px]">
          <div class="mb-[26px] flex items-center gap-3">
            <span class="grid h-9 w-9 place-items-center rounded-full bg-[#8ccfd0] text-[0.67rem] font-bold text-brand-primary">01</span>
            <p class="m-0 text-[0.7rem] font-bold uppercase tracking-[0.12em] text-brand-secondary">{{ __('messages.product_module_clients_label') }}</p>
          </div>
          <h2 class="max-w-[720px] text-[clamp(2.1rem,3.1vw,3rem)] font-semibold leading-[1.1] tracking-[-0.052em] text-brand-primary max-sm:text-[1.6rem]">{{ __('messages.product_module_clients_title') }}</h2>
          <p class="mt-5 max-w-[710px] text-[1rem] leading-[1.72] text-[#5b6b73]">{{ __('messages.product_module_clients_intro') }}</p>
        </div>
        <div class="grid items-center gap-6 border-y border-[#d3e1df] bg-[#e7f1f1] p-[42px] max-lg:p-[30px] lg:grid-cols-[1.05fr_0.95fr] max-md:grid-cols-1 max-sm:min-h-[360px] max-sm:p-[22px]">
          <div class="grid min-h-[260px] content-center justify-items-center rounded-[14px] border border-dashed border-[#8ba7ae] bg-white/90 p-[30px] text-center max-sm:min-h-[225px] max-sm:p-[22px]">
            <small class="text-[0.68rem] font-bold uppercase tracking-[0.12em] text-brand-secondary">{{ __('messages.product_module_clients_visual_label') }}</small>
            <strong class="mt-[7px] block text-[1rem] font-bold text-brand-primary">{{ __('messages.product_module_clients_visual_title') }}</strong>
            <p class="mx-auto mt-[7px] max-w-[340px] text-[0.78rem] leading-[1.5] text-[#5b6b73]">{{ __('messages.product_module_clients_visual_text') }}</p>
          </div>
          <div class="grid gap-[13px]" aria-hidden="true"><i class="h-[79px] rounded-xl border border-[#071b32]/10 bg-white/80"></i><i class="h-[79px] w-[88%] rounded-xl border border-[#071b32]/10 bg-white/80"></i><i class="h-[79px] w-[95%] rounded-xl border border-[#071b32]/10 bg-white/80"></i></div>
        </div>
        <div class="grid sm:grid-cols-3 max-sm:grid-cols-1">
          <p class="m-0 min-h-[145px] border-r border-[#dce6e4] px-[30px] py-7 max-sm:min-h-0 max-sm:border-b max-sm:border-r-0"><strong class="block text-[0.76rem] font-bold uppercase tracking-[0.08em] text-brand-secondary">{{ __('messages.product_module_clients_feat1_title') }}</strong><span class="mt-[9px] block text-[0.84rem] leading-[1.5] text-[#5b6b73]">{{ __('messages.product_module_clients_feat1_text') }}</span></p>
          <p class="m-0 min-h-[145px] border-r border-[#dce6e4] px-[30px] py-7 max-sm:min-h-0 max-sm:border-b max-sm:border-r-0"><strong class="block text-[0.76rem] font-bold uppercase tracking-[0.08em] text-brand-secondary">{{ __('messages.product_module_clients_feat2_title') }}</strong><span class="mt-[9px] block text-[0.84rem] leading-[1.5] text-[#5b6b73]">{{ __('messages.product_module_clients_feat2_text') }}</span></p>
          <p class="m-0 min-h-[145px] px-[30px] py-7 max-sm:min-h-0"><strong class="block text-[0.76rem] font-bold uppercase tracking-[0.08em] text-brand-secondary">{{ __('messages.product_module_clients_feat3_title') }}</strong><span class="mt-[9px] block text-[0.84rem] leading-[1.5] text-[#5b6b73]">{{ __('messages.product_module_clients_feat3_text') }}</span></p>
        </div>
      </article>

      <article id="taches" data-module-article="taches" class="scroll-mt-[105px] overflow-hidden rounded-[22px] border border-[#dce6e4] bg-white shadow-[0_15px_45px_rgba(7,27,50,0.055)] max-md:scroll-mt-20">
        <div class="px-[56px] pb-[38px] pt-[54px] max-lg:px-10 max-sm:px-[26px] max-sm:pb-[30px] max-sm:pt-[38px]">
          <div class="mb-[26px] flex items-center gap-3">
            <span class="grid h-9 w-9 place-items-center rounded-full bg-[#8ccfd0] text-[0.67rem] font-bold text-brand-primary">02</span>
            <p class="m-0 text-[0.7rem] font-bold uppercase tracking-[0.12em] text-brand-secondary">{{ __('messages.product_module_tasks_label') }}</p>
          </div>
          <h2 class="max-w-[720px] text-[clamp(2.1rem,3.1vw,3rem)] font-semibold leading-[1.1] tracking-[-0.052em] text-brand-primary max-sm:text-[1.6rem]">{{ __('messages.product_module_tasks_title') }}</h2>
          <p class="mt-5 max-w-[710px] text-[1rem] leading-[1.72] text-[#5b6b73]">{{ __('messages.product_module_tasks_intro') }}</p>
        </div>
        <div class="grid items-center gap-6 border-y border-[#d3e1df] bg-[#e7f1f1] p-[42px] max-lg:p-[30px] lg:grid-cols-[1.05fr_0.95fr] max-md:grid-cols-1 max-sm:min-h-[360px] max-sm:p-[22px]">
          <div class="grid min-h-[265px] grid-cols-3 gap-2.5 max-sm:min-h-[210px]" aria-hidden="true">
            <div class="flex flex-col gap-2.5 rounded-xl border border-[#071b32]/10 bg-white/60 p-3"><i class="h-[74px] rounded-lg bg-white shadow-[0_5px_15px_rgba(7,27,50,0.05)] max-sm:h-[58px]"></i><i class="h-[74px] rounded-lg bg-white shadow-[0_5px_15px_rgba(7,27,50,0.05)] max-sm:h-[58px]"></i></div>
            <div class="flex flex-col gap-2.5 rounded-xl border border-[#071b32]/10 bg-white/60 p-3"><i class="h-[74px] rounded-lg bg-white shadow-[0_5px_15px_rgba(7,27,50,0.05)] max-sm:h-[58px]"></i><i class="h-[74px] rounded-lg bg-white shadow-[0_5px_15px_rgba(7,27,50,0.05)] max-sm:h-[58px]"></i><i class="h-[74px] rounded-lg bg-white shadow-[0_5px_15px_rgba(7,27,50,0.05)] max-sm:h-[58px]"></i></div>
            <div class="flex flex-col gap-2.5 rounded-xl border border-[#071b32]/10 bg-white/60 p-3"><i class="h-[74px] rounded-lg bg-white shadow-[0_5px_15px_rgba(7,27,50,0.05)] max-sm:h-[58px]"></i></div>
          </div>
          <div class="grid min-h-[260px] content-center justify-items-center rounded-[14px] border border-dashed border-[#8ba7ae] bg-white/90 p-[30px] text-center max-sm:min-h-[225px] max-sm:p-[22px]">
            <small class="text-[0.68rem] font-bold uppercase tracking-[0.12em] text-brand-secondary">{{ __('messages.product_module_tasks_visual_label') }}</small>
            <strong class="mt-[7px] block text-[1rem] font-bold text-brand-primary">{{ __('messages.product_module_tasks_visual_title') }}</strong>
            <p class="mx-auto mt-[7px] max-w-[340px] text-[0.78rem] leading-[1.5] text-[#5b6b73]">{{ __('messages.product_module_tasks_visual_text') }}</p>
          </div>
        </div>
        <div class="grid sm:grid-cols-3 max-sm:grid-cols-1">
          <p class="m-0 min-h-[145px] border-r border-[#dce6e4] px-[30px] py-7 max-sm:min-h-0 max-sm:border-b max-sm:border-r-0"><strong class="block text-[0.76rem] font-bold uppercase tracking-[0.08em] text-brand-secondary">{{ __('messages.product_module_tasks_feat1_title') }}</strong><span class="mt-[9px] block text-[0.84rem] leading-[1.5] text-[#5b6b73]">{{ __('messages.product_module_tasks_feat1_text') }}</span></p>
          <p class="m-0 min-h-[145px] border-r border-[#dce6e4] px-[30px] py-7 max-sm:min-h-0 max-sm:border-b max-sm:border-r-0"><strong class="block text-[0.76rem] font-bold uppercase tracking-[0.08em] text-brand-secondary">{{ __('messages.product_module_tasks_feat2_title') }}</strong><span class="mt-[9px] block text-[0.84rem] leading-[1.5] text-[#5b6b73]">{{ __('messages.product_module_tasks_feat2_text') }}</span></p>
          <p class="m-0 min-h-[145px] px-[30px] py-7 max-sm:min-h-0"><strong class="block text-[0.76rem] font-bold uppercase tracking-[0.08em] text-brand-secondary">{{ __('messages.product_module_tasks_feat3_title') }}</strong><span class="mt-[9px] block text-[0.84rem] leading-[1.5] text-[#5b6b73]">{{ __('messages.product_module_tasks_feat3_text') }}</span></p>
        </div>
      </article>

      <article id="temps" data-module-article="temps" class="scroll-mt-[105px] overflow-hidden rounded-[22px] border border-brand-primary bg-brand-primary text-white shadow-[0_15px_45px_rgba(7,27,50,0.055)] max-md:scroll-mt-20">
        <div class="px-[56px] pb-[38px] pt-[54px] max-lg:px-10 max-sm:px-[26px] max-sm:pb-[30px] max-sm:pt-[38px]">
          <div class="mb-[26px] flex items-center gap-3">
            <span class="grid h-9 w-9 place-items-center rounded-full bg-brand-accent text-[0.67rem] font-bold text-brand-primary">03</span>
            <p class="m-0 text-[0.7rem] font-bold uppercase tracking-[0.12em] text-[#8ccfd0]">{{ __('messages.product_module_time_label') }}</p>
          </div>
          <h2 class="max-w-[720px] text-[clamp(2.1rem,3.1vw,3rem)] font-semibold leading-[1.1] tracking-[-0.052em] text-white max-sm:text-[1.6rem]">{{ __('messages.product_module_time_title') }}</h2>
          <p class="mt-5 max-w-[710px] text-[1rem] leading-[1.72] text-[#b5c7cf]">{{ __('messages.product_module_time_intro') }}</p>
        </div>
        <div class="grid items-center gap-6 border-y border-white/10 bg-[#163b4c] p-[42px] max-lg:p-[30px] lg:grid-cols-[1.05fr_0.95fr] max-md:grid-cols-1 max-sm:min-h-[360px] max-sm:p-[22px]">
          <div class="grid min-h-[270px] content-center justify-items-center rounded-[17px] bg-white p-8 text-[#1b3444] shadow-[0_20px_44px_rgba(0,0,0,0.18)] max-sm:min-h-[235px] max-sm:p-6" aria-hidden="true">
            <small class="text-[0.64rem] font-bold uppercase tracking-[0.12em] text-[#82949b]">{{ __('messages.product_module_time_current_task') }}</small>
            <strong class="mt-[9px] text-[2.75rem] font-bold leading-none tracking-[-0.04em] text-brand-primary max-sm:text-[2.15rem]">01:42:18</strong>
            <span class="text-[0.78rem] text-[#5b6b73]">{{ __('messages.product_module_time_project') }}</span>
            <b class="mt-[25px] rounded-full bg-brand-secondary px-6 py-[9px] text-[0.72rem] font-bold text-white">{{ __('messages.product_module_time_stop') }}</b>
          </div>
          <div class="grid min-h-[260px] content-center justify-items-center rounded-[14px] border border-white/25 bg-[#0e2e40] p-[30px] text-center max-sm:min-h-[225px] max-sm:p-[22px]">
            <small class="text-[0.68rem] font-bold uppercase tracking-[0.12em] text-brand-secondary">{{ __('messages.product_module_time_visual_label') }}</small>
            <strong class="mt-[7px] block text-[1rem] font-bold text-white">{{ __('messages.product_module_time_visual_title') }}</strong>
            <p class="mx-auto mt-[7px] max-w-[340px] text-[0.78rem] leading-[1.5] text-[#adc2ca]">{{ __('messages.product_module_time_visual_text') }}</p>
          </div>
        </div>
        <div class="grid sm:grid-cols-3 max-sm:grid-cols-1">
          <p class="m-0 min-h-[145px] border-r border-white/10 px-[30px] py-7 max-sm:min-h-0 max-sm:border-b"><strong class="block text-[0.76rem] font-bold uppercase tracking-[0.08em] text-[#8ccfd0]">{{ __('messages.product_module_time_feat1_title') }}</strong><span class="mt-[9px] block text-[0.84rem] leading-[1.5] text-[#b5c7cf]">{{ __('messages.product_module_time_feat1_text') }}</span></p>
          <p class="m-0 min-h-[145px] border-r border-white/10 px-[30px] py-7 max-sm:min-h-0 max-sm:border-b"><strong class="block text-[0.76rem] font-bold uppercase tracking-[0.08em] text-[#8ccfd0]">{{ __('messages.product_module_time_feat2_title') }}</strong><span class="mt-[9px] block text-[0.84rem] leading-[1.5] text-[#b5c7cf]">{{ __('messages.product_module_time_feat2_text') }}</span></p>
          <p class="m-0 min-h-[145px] px-[30px] py-7 max-sm:min-h-0"><strong class="block text-[0.76rem] font-bold uppercase tracking-[0.08em] text-[#8ccfd0]">{{ __('messages.product_module_time_feat3_title') }}</strong><span class="mt-[9px] block text-[0.84rem] leading-[1.5] text-[#b5c7cf]">{{ __('messages.product_module_time_feat3_text') }}</span></p>
        </div>
      </article>

      <article id="budgets" data-module-article="budgets" class="scroll-mt-[105px] overflow-hidden rounded-[22px] border border-[#dce6e4] bg-white shadow-[0_15px_45px_rgba(7,27,50,0.055)] max-md:scroll-mt-20">
        <div class="px-[56px] pb-[38px] pt-[54px] max-lg:px-10 max-sm:px-[26px] max-sm:pb-[30px] max-sm:pt-[38px]">
          <div class="mb-[26px] flex items-center gap-3">
            <span class="grid h-9 w-9 place-items-center rounded-full bg-[#8ccfd0] text-[0.67rem] font-bold text-brand-primary">04</span>
            <p class="m-0 text-[0.7rem] font-bold uppercase tracking-[0.12em] text-brand-secondary">{{ __('messages.product_module_budgets_label') }}</p>
          </div>
          <h2 class="max-w-[720px] text-[clamp(2.1rem,3.1vw,2.8rem)] font-semibold leading-[1.1] tracking-[-0.052em] text-brand-primary max-sm:text-[1.6rem]">{{ __('messages.product_module_budgets_title') }}</h2>
          <p class="mt-5 max-w-[710px] text-[1rem] leading-[1.72] text-[#5b6b73]">{{ __('messages.product_module_budgets_intro') }}</p>
        </div>
        <div class="grid items-center gap-6 border-y border-[#d3e1df] bg-[#e7f1f1] p-[42px] max-lg:p-[30px] lg:grid-cols-[1.05fr_0.95fr] max-md:grid-cols-1 max-sm:min-h-[360px] max-sm:p-[22px]">
          <div class="grid min-h-[260px] content-center justify-items-center rounded-[14px] border border-dashed border-[#8ba7ae] bg-white/90 p-[30px] text-center max-sm:min-h-[225px] max-sm:p-[22px]">
            <small class="text-[0.68rem] font-bold uppercase tracking-[0.12em] text-brand-secondary">{{ __('messages.product_module_budgets_visual_label') }}</small>
            <strong class="mt-[7px] block text-[1rem] font-bold text-brand-primary">{{ __('messages.product_module_budgets_visual_title') }}</strong>
            <p class="mx-auto mt-[7px] max-w-[340px] text-[0.78rem] leading-[1.5] text-[#5b6b73]">{{ __('messages.product_module_budgets_visual_text') }}</p>
          </div>
          <div class="grid gap-6 rounded-[14px] bg-white/75 p-[30px]" aria-hidden="true">
            <span class="relative block h-[21px] overflow-hidden rounded-full bg-[#dce7e6]"><span class="absolute inset-y-0 left-0 rounded-full bg-brand-secondary" style="width: 72%"></span></span>
            <span class="relative block h-[21px] overflow-hidden rounded-full bg-[#dce7e6]"><span class="absolute inset-y-0 left-0 rounded-full bg-brand-secondary" style="width: 46%"></span></span>
            <span class="relative block h-[21px] overflow-hidden rounded-full bg-[#dce7e6]"><span class="absolute inset-y-0 left-0 rounded-full bg-brand-secondary" style="width: 88%"></span></span>
          </div>
        </div>
        <div class="grid sm:grid-cols-3 max-sm:grid-cols-1">
          <p class="m-0 min-h-[145px] border-r border-[#dce6e4] px-[30px] py-7 max-sm:min-h-0 max-sm:border-b max-sm:border-r-0"><strong class="block text-[0.76rem] font-bold uppercase tracking-[0.08em] text-brand-secondary">{{ __('messages.product_module_budgets_feat1_title') }}</strong><span class="mt-[9px] block text-[0.84rem] leading-[1.5] text-[#5b6b73]">{{ __('messages.product_module_budgets_feat1_text') }}</span></p>
          <p class="m-0 min-h-[145px] border-r border-[#dce6e4] px-[30px] py-7 max-sm:min-h-0 max-sm:border-b max-sm:border-r-0"><strong class="block text-[0.76rem] font-bold uppercase tracking-[0.08em] text-brand-secondary">{{ __('messages.product_module_budgets_feat2_title') }}</strong><span class="mt-[9px] block text-[0.84rem] leading-[1.5] text-[#5b6b73]">{{ __('messages.product_module_budgets_feat2_text') }}</span></p>
          <p class="m-0 min-h-[145px] px-[30px] py-7 max-sm:min-h-0"><strong class="block text-[0.76rem] font-bold uppercase tracking-[0.08em] text-brand-secondary">{{ __('messages.product_module_budgets_feat3_title') }}</strong><span class="mt-[9px] block text-[0.84rem] leading-[1.5] text-[#5b6b73]">{{ __('messages.product_module_budgets_feat3_text') }}</span></p>
        </div>
      </article>

      <article id="facturation" data-module-article="facturation" class="scroll-mt-[105px] overflow-hidden rounded-[22px] border border-[#dce6e4] bg-white shadow-[0_15px_45px_rgba(7,27,50,0.055)] max-md:scroll-mt-20">
        <div class="px-[56px] pb-[38px] pt-[54px] max-lg:px-10 max-sm:px-[26px] max-sm:pb-[30px] max-sm:pt-[38px]">
          <div class="mb-[26px] flex items-center gap-3">
            <span class="grid h-9 w-9 place-items-center rounded-full bg-[#8ccfd0] text-[0.67rem] font-bold text-brand-primary">05</span>
            <p class="m-0 text-[0.7rem] font-bold uppercase tracking-[0.12em] text-brand-secondary">{{ __('messages.product_module_billing_label') }}</p>
          </div>
          <h2 class="max-w-[720px] text-[clamp(2.1rem,3.1vw,2.6rem)] font-semibold leading-[1.1] tracking-[-0.052em] text-brand-primary max-sm:text-[1.6rem]">{{ __('messages.product_module_billing_title') }}</h2>
          <p class="mt-5 max-w-[710px] text-[1rem] leading-[1.72] text-[#5b6b73]">{{ __('messages.product_module_billing_intro') }}</p>
        </div>
        <div class="grid items-center gap-6 border-y border-[#d3e1df] bg-[#e7f1f1] p-[42px] max-lg:p-[30px] lg:grid-cols-[1.05fr_0.95fr] max-md:grid-cols-1 max-sm:min-h-[360px] max-sm:p-[22px]">
          <div class="mx-auto flex min-h-[310px] w-[min(75%,330px)] flex-col gap-[18px] rounded-[5px] bg-white p-7 shadow-[0_18px_38px_rgba(7,27,50,0.12)] max-sm:w-[88%]" aria-hidden="true">
            <span class="text-[0.7rem] font-bold uppercase tracking-[0.15em] text-brand-secondary">{{ __('messages.product_module_billing_invoice') }}</span>
            <i class="block h-[9px] rounded-[5px] bg-[#e6edef]"></i>
            <i class="block h-[9px] w-[55%] rounded-[5px] bg-[#e6edef]"></i>
            <i class="mt-5 block h-[9px] w-[82%] rounded-[5px] bg-[#e6edef]"></i>
            <i class="block h-[9px] w-[74%] rounded-[5px] bg-[#e6edef]"></i>
            <b class="mt-auto border-t-2 border-[#dce6e4] pt-[15px] text-right font-bold text-brand-primary">{{ __('messages.product_module_billing_total') }}</b>
          </div>
          <div class="grid min-h-[260px] content-center justify-items-center rounded-[14px] border border-dashed border-[#8ba7ae] bg-white/90 p-[30px] text-center max-sm:min-h-[225px] max-sm:p-[22px]">
            <small class="text-[0.68rem] font-bold uppercase tracking-[0.12em] text-brand-secondary">{{ __('messages.product_module_billing_visual_label') }}</small>
            <strong class="mt-[7px] block text-[1rem] font-bold text-brand-primary">{{ __('messages.product_module_billing_visual_title') }}</strong>
            <p class="mx-auto mt-[7px] max-w-[340px] text-[0.78rem] leading-[1.5] text-[#5b6b73]">{{ __('messages.product_module_billing_visual_text') }}</p>
          </div>
        </div>
        <div class="grid sm:grid-cols-3 max-sm:grid-cols-1">
          <p class="m-0 min-h-[145px] border-r border-[#dce6e4] px-[30px] py-7 max-sm:min-h-0 max-sm:border-b max-sm:border-r-0"><strong class="block text-[0.76rem] font-bold uppercase tracking-[0.08em] text-brand-secondary">{{ __('messages.product_module_billing_feat1_title') }}</strong><span class="mt-[9px] block text-[0.84rem] leading-[1.5] text-[#5b6b73]">{{ __('messages.product_module_billing_feat1_text') }}</span></p>
          <p class="m-0 min-h-[145px] border-r border-[#dce6e4] px-[30px] py-7 max-sm:min-h-0 max-sm:border-b max-sm:border-r-0"><strong class="block text-[0.76rem] font-bold uppercase tracking-[0.08em] text-brand-secondary">{{ __('messages.product_module_billing_feat2_title') }}</strong><span class="mt-[9px] block text-[0.84rem] leading-[1.5] text-[#5b6b73]">{{ __('messages.product_module_billing_feat2_text') }}</span></p>
          <p class="m-0 min-h-[145px] px-[30px] py-7 max-sm:min-h-0"><strong class="block text-[0.76rem] font-bold uppercase tracking-[0.08em] text-brand-secondary">{{ __('messages.product_module_billing_feat3_title') }}</strong><span class="mt-[9px] block text-[0.84rem] leading-[1.5] text-[#5b6b73]">{{ __('messages.product_module_billing_feat3_text') }}</span></p>
        </div>
      </article>

      <article id="tableaux" data-module-article="tableaux" class="scroll-mt-[105px] overflow-hidden rounded-[22px] border border-[#dce6e4] bg-white shadow-[0_15px_45px_rgba(7,27,50,0.055)] max-md:scroll-mt-20">
        <div class="px-[56px] pb-[38px] pt-[54px] max-lg:px-10 max-sm:px-[26px] max-sm:pb-[30px] max-sm:pt-[38px]">
          <div class="mb-[26px] flex items-center gap-3">
            <span class="grid h-9 w-9 place-items-center rounded-full bg-[#8ccfd0] text-[0.67rem] font-bold text-brand-primary">06</span>
            <p class="m-0 text-[0.7rem] font-bold uppercase tracking-[0.12em] text-brand-secondary">{{ __('messages.product_module_dashboards_label') }}</p>
          </div>
          <h2 class="max-w-[720px] text-[clamp(2.1rem,3.1vw,3rem)] font-semibold leading-[1.1] tracking-[-0.052em] text-brand-primary max-sm:text-[1.6rem]">{{ __('messages.product_module_dashboards_title') }}</h2>
          <p class="mt-5 max-w-[710px] text-[1rem] leading-[1.72] text-[#5b6b73]">{{ __('messages.product_module_dashboards_intro') }}</p>
        </div>
        <div class="grid items-center gap-6 border-y border-[#d3e1df] bg-[#e7f1f1] p-[42px] max-lg:p-[30px] lg:grid-cols-[1.05fr_0.95fr] max-md:grid-cols-1 max-sm:min-h-[360px] max-sm:p-[22px]">
          <div class="grid min-h-[260px] content-center justify-items-center rounded-[14px] border border-dashed border-[#8ba7ae] bg-white/90 p-[30px] text-center max-sm:min-h-[225px] max-sm:p-[22px]">
            <small class="text-[0.68rem] font-bold uppercase tracking-[0.12em] text-brand-secondary">{{ __('messages.product_module_dashboards_visual_label') }}</small>
            <strong class="mt-[7px] block text-[1rem] font-bold text-brand-primary">{{ __('messages.product_module_dashboards_visual_title') }}</strong>
            <p class="mx-auto mt-[7px] max-w-[340px] text-[0.78rem] leading-[1.5] text-[#5b6b73]">{{ __('messages.product_module_dashboards_visual_text') }}</p>
          </div>
          <div class="flex h-[270px] items-end gap-4 rounded-[14px] bg-white/80 px-[26px] pt-[30px] max-sm:h-[220px]" aria-hidden="true">
            <i class="h-[38%] flex-1 rounded-t-md bg-[#bad9da]"></i><i class="h-[68%] flex-1 rounded-t-md bg-[#bad9da]"></i><i class="h-[52%] flex-1 rounded-t-md bg-[#bad9da]"></i><i class="h-[88%] flex-1 rounded-t-md bg-brand-secondary"></i><i class="h-[72%] flex-1 rounded-t-md bg-[#bad9da]"></i>
          </div>
        </div>
        <div class="grid sm:grid-cols-3 max-sm:grid-cols-1">
          <p class="m-0 min-h-[145px] border-r border-[#dce6e4] px-[30px] py-7 max-sm:min-h-0 max-sm:border-b max-sm:border-r-0"><strong class="block text-[0.76rem] font-bold uppercase tracking-[0.08em] text-brand-secondary">{{ __('messages.product_module_dashboards_feat1_title') }}</strong><span class="mt-[9px] block text-[0.84rem] leading-[1.5] text-[#5b6b73]">{{ __('messages.product_module_dashboards_feat1_text') }}</span></p>
          <p class="m-0 min-h-[145px] border-r border-[#dce6e4] px-[30px] py-7 max-sm:min-h-0 max-sm:border-b max-sm:border-r-0"><strong class="block text-[0.76rem] font-bold uppercase tracking-[0.08em] text-brand-secondary">{{ __('messages.product_module_dashboards_feat2_title') }}</strong><span class="mt-[9px] block text-[0.84rem] leading-[1.5] text-[#5b6b73]">{{ __('messages.product_module_dashboards_feat2_text') }}</span></p>
          <p class="m-0 min-h-[145px] px-[30px] py-7 max-sm:min-h-0"><strong class="block text-[0.76rem] font-bold uppercase tracking-[0.08em] text-brand-secondary">{{ __('messages.product_module_dashboards_feat3_title') }}</strong><span class="mt-[9px] block text-[0.84rem] leading-[1.5] text-[#5b6b73]">{{ __('messages.product_module_dashboards_feat3_text') }}</span></p>
        </div>
      </article>
    </div>
  </div>
</section>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    const links = [...document.querySelectorAll('#modules-nav a[data-module-link]')];
    const articles = [...document.querySelectorAll('#modules-content article[data-module-article]')];

    const setActiveModule = (moduleId) => {
      links.forEach((link) => {
        const isActive = link.dataset.moduleLink === moduleId;
        const number = link.querySelector('span');

        link.style.color = isActive ? '#071b32' : '#5c7077';
        link.style.fontWeight = isActive ? '600' : '500';
        link.style.borderLeft = isActive ? '2px solid #2a7c88' : '2px solid transparent';
        link.style.paddingLeft = isActive ? '10px' : '0';
        link.style.transition = 'color 0.3s ease, font-weight 0.3s ease, border-left-color 0.3s ease, padding-left 0.3s ease';

        if (number) {
          number.style.color = isActive ? '#071b32' : '#93a4a8';
        }
      });
    };

    const observer = new IntersectionObserver((entries) => {
      const visibleEntries = entries
        .filter((entry) => entry.isIntersecting)
        .sort((a, b) => b.intersectionRatio - a.intersectionRatio);

      if (visibleEntries.length > 0) {
        setActiveModule(visibleEntries[0].target.id);
      }
    }, {
      root: null,
      threshold: [0.2, 0.35, 0.5, 0.7],
      rootMargin: '-12% 0px -38% 0px'
    });

    articles.forEach((article) => observer.observe(article));
    if (articles.length > 0) {
      setActiveModule(articles[0].id);
    }
  });
</script>
