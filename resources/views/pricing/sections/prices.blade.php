<section id="offres" class="scroll-mt-[85px] bg-white py-[83px] max-sm:py-[66px]" aria-labelledby="offers-title">
  <div class="mx-auto max-w-[1200px] px-6 max-md:px-4">
    <div class="mb-9 grid items-end gap-[72px] max-md:mb-7 max-md:grid-cols-1 max-md:gap-[34px] lg:grid-cols-[1.1fr_0.9fr]">
      <div>
        <p class="mb-[15px] text-[0.72rem] font-bold uppercase tracking-[0.15em] text-[#2d7080]">{{ __('messages.public_pricing_paid_title') }}</p>
        <h2 id="offers-title" class="text-[clamp(2.25rem,3.35vw,2.8rem)] font-semibold leading-[1.1] tracking-[-0.052em] text-brand-primary">{{ __('messages.public_pricing_paid_heading') }}</h2>
      </div>
      <p class="mb-1 text-[#5b6b73]">{{ __('messages.public_pricing_paid_intro') }}</p>
    </div>
    <div class="overflow-x-auto rounded-xl border border-[#cddeda]" role="region" aria-label="{{ __('messages.public_pricing_table_label') }}" tabindex="0">
      <table class="w-full min-w-[730px] table-fixed border-collapse">
        <caption class="sr-only">{{ __('messages.public_pricing_table_caption') }}</caption>
        <thead class="bg-[#f5f8f6]">
          <tr>
            <th scope="col" class="h-[120px] p-5 text-left text-[0.84rem] font-bold text-[#556e70]">{{ __('messages.public_pricing_table_legend') }}</th>
            <th scope="col" :class="plan === 'solo' ? 'bg-[#eaf4ef] shadow-[inset_0_4px_#2d7080]' : ''" class="h-[120px] p-5 text-left text-brand-primary transition-colors duration-300"><span class="block text-[0.62rem] uppercase tracking-[0.11em] text-brand-secondary">{{ __('messages.public_pricing_table_solo') }}</span><strong class="mt-[5px] block text-[1.5rem] font-semibold leading-[1.2]">{{ __('messages.Solo') }}</strong><span class="mt-[2px] block text-[0.78rem] text-[#5b6b73]">Travail seul</span></th>
            <th scope="col" :class="plan === 'premium' ? 'bg-[#eaf4ef] shadow-[inset_0_4px_#2d7080]' : ''" class="h-[120px] p-5 text-left text-brand-primary transition-colors duration-300"><span class="block text-[0.62rem] uppercase tracking-[0.11em] text-brand-secondary">{{ __('messages.public_pricing_table_premium') }}</span><strong class="mt-[5px] block text-[1.5rem] font-semibold leading-[1.2]">{{ __('messages.Premium') }}</strong><span class="mt-[2px] block text-[0.78rem] text-[#5b6b73]">Espace partagé</span></th>
            <th scope="col" :class="plan === 'pro' ? 'bg-[#eaf4ef] shadow-[inset_0_4px_#2d7080]' : ''" class="h-[120px] p-5 text-left text-brand-primary transition-colors duration-300"><span class="block text-[0.62rem] uppercase tracking-[0.11em] text-brand-secondary">{{ __('messages.public_pricing_table_pro') }}</span><strong class="mt-[5px] block text-[1.5rem] font-semibold leading-[1.2]">{{ __('messages.Pro') }}</strong><span class="mt-[2px] block text-[0.78rem] text-[#5b6b73]">Espace partagé</span></th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <th scope="row" class="border-b border-r border-[#e0eae7] p-5 pl-[30px] text-left text-[0.87rem] font-bold text-[#3e5860] max-sm:px-[17px]">{{ __('messages.public_pricing_table_price') }}</th>
            <td :class="plan === 'solo' ? 'bg-[#eaf4ef]' : ''" class="border-b border-r border-[#e0eae7] p-5 text-brand-primary transition-colors duration-300 max-sm:px-[17px]"><strong class="text-[2.2rem] font-semibold tracking-[-0.055em]">19 €</strong><span class="block text-[0.79rem] text-[#5b6b73]">{{ __('messages.public_pricing_per_month') }}</span></td>
            <td :class="plan === 'premium' ? 'bg-[#eaf4ef]' : ''" class="border-b border-r border-[#e0eae7] p-5 text-brand-primary transition-colors duration-300 max-sm:px-[17px]"><strong class="text-[2.2rem] font-semibold tracking-[-0.055em]">49 €</strong><span class="block text-[0.79rem] text-[#5b6b73]">{{ __('messages.public_pricing_per_month') }}</span></td>
            <td :class="plan === 'pro' ? 'bg-[#eaf4ef]' : ''" class="border-b border-[#e0eae7] p-5 text-brand-primary transition-colors duration-300 max-sm:px-[17px]"><strong class="text-[2.2rem] font-semibold tracking-[-0.055em]">99 €</strong><span class="block text-[0.79rem] text-[#5b6b73]">{{ __('messages.public_pricing_per_month') }}</span></td>
          </tr>
          <tr>
            <th scope="row" class="border-b border-r border-[#e0eae7] p-5 pl-[30px] text-left text-[0.87rem] font-bold text-[#3e5860] max-sm:px-[17px]">{{ __('messages.public_pricing_table_users') }}</th>
            <td :class="plan === 'solo' ? 'bg-[#eaf4ef]' : ''" class="border-b border-r border-[#e0eae7] p-5 text-[0.91rem] font-bold text-brand-primary transition-colors duration-300 max-sm:px-[17px]">{{ __('messages.public_pricing_users_solo') }}</td>
            <td :class="plan === 'premium' ? 'bg-[#eaf4ef]' : ''" class="border-b border-r border-[#e0eae7] p-5 text-[0.91rem] font-bold text-brand-primary transition-colors duration-300 max-sm:px-[17px]">{{ __('messages.public_pricing_users_premium') }}</td>
            <td :class="plan === 'pro' ? 'bg-[#eaf4ef]' : ''" class="border-b border-[#e0eae7] p-5 text-[0.91rem] font-bold text-brand-primary transition-colors duration-300 max-sm:px-[17px]">{{ __('messages.public_pricing_users_pro') }}</td>
          </tr>
          <tr>
            <th scope="row" class="border-b border-r border-[#e0eae7] p-5 pl-[30px] text-left text-[0.87rem] font-bold text-[#3e5860] max-sm:px-[17px]">{{ __('messages.public_pricing_table_features') }}</th>
            <td :class="plan === 'solo' ? 'bg-[#eaf4ef]' : ''" class="border-b border-r border-[#e0eae7] p-5 text-[0.91rem] text-brand-primary transition-colors duration-300 max-sm:px-[17px]">{{ __('messages.public_pricing_features_all') }}</td>
            <td :class="plan === 'premium' ? 'bg-[#eaf4ef]' : ''" class="border-b border-r border-[#e0eae7] p-5 text-[0.91rem] text-brand-primary transition-colors duration-300 max-sm:px-[17px]">{{ __('messages.public_pricing_features_all') }}</td>
            <td :class="plan === 'pro' ? 'bg-[#eaf4ef]' : ''" class="border-b border-[#e0eae7] p-5 text-[0.91rem] text-brand-primary transition-colors duration-300 max-sm:px-[17px]">{{ __('messages.public_pricing_features_all') }}</td>
          </tr>
          <tr class="bg-[#fbfcfb]">
            <th scope="row" class="border-r border-[#e0eae7] p-5 pl-[30px] text-left text-[0.87rem] font-bold text-[#3e5860] max-sm:px-[17px]">{{ __('messages.public_pricing_table_before') }}</th>
            <td colspan="3" class="p-5 text-center transition-colors duration-300 max-sm:px-[17px]"><a href="{{ $planLinks['pro'] }}" class="group inline-flex items-center gap-[7px] text-[0.84rem] font-bold text-brand-secondary underline underline-offset-4 transition-colors duration-300 hover:text-[#245f6d]">{{ __('messages.public_pricing_try') }} <span aria-hidden="true" class="inline-block transition-transform duration-300 group-hover:translate-x-0.5 group-hover:-translate-y-0.5"><i data-lucide="arrow-up-right" class="h-4 w-4"></i></span></a></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</section>
