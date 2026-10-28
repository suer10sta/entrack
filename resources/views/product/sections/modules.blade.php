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
        <div class="border-y border-[#d3e1df] bg-[#e7f1f1] p-[30px] max-sm:p-[18px]">
          <x-public.youtube-video video-id="oYTbTZd7pX4" />
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
        <div class="border-y border-[#d3e1df] bg-[#e7f1f1] p-[30px] max-sm:p-[18px]">
          <x-public.youtube-video video-id="qKKcrtPRv08" />
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
        <div class="border-y border-white/10 bg-[#163b4c] p-[30px] max-sm:p-[18px]">
          <x-public.youtube-video video-id="XuyuoK14kUE" />
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
        <div class="border-y border-[#d3e1df] bg-[#e7f1f1] p-[30px] max-sm:p-[18px]">
          <x-public.youtube-video video-id="OGZdxbiwnn4" />
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
        <div class="border-y border-[#d3e1df] bg-[#e7f1f1] p-[30px] max-sm:p-[18px]">
          <x-public.youtube-video video-id="TxlzQyN44PA" />
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
        <div class="border-y border-[#d3e1df] bg-[#e7f1f1] p-[30px] max-sm:p-[18px]">
          <x-public.youtube-video video-id="NcIsXZQU9wQ" />
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
