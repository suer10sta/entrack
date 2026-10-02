<section class="bg-white py-28 max-md:py-20" aria-labelledby="access-title">
  <div class="mx-auto max-w-[1200px] px-6 max-md:px-4">
    <div class="mb-14 grid items-end gap-20 max-md:grid-cols-1 max-md:gap-6 lg:grid-cols-[1.35fr_0.65fr]">
      <div>
        <p class="mb-5 flex items-center gap-2.5 text-[0.75rem] font-bold uppercase tracking-[0.13em] text-brand-secondary"><span class="h-0.5 w-7 bg-brand-accent"></span>{{ __('messages.product_access_eyebrow') }}</p>
        <h2 id="access-title" class="text-[clamp(2.5rem,4.1vw,3.8rem)] font-semibold leading-[1.02] tracking-[-0.052em] text-brand-primary max-sm:text-[2.1rem]">{{ __('messages.product_access_title') }} <em class="not-italic text-brand-secondary">{{ __('messages.product_access_title_highlight') }}</em></h2>
      </div>
      <p class="mb-[5px] max-w-[620px] text-[1.05rem] text-[#5b6b73]">{{ __('messages.product_access_intro') }}</p>
    </div>
    <div class="overflow-hidden rounded-[18px] border border-[#dce6e4]" role="table" aria-label="{{ __('messages.product_access_table_label') }}">
      <div class="grid min-h-[57px] grid-cols-[0.8fr_0.8fr_1.4fr] items-center gap-7 bg-brand-primary px-7 py-[23px] max-md:grid-cols-[1fr_1fr] max-md:gap-[10px] max-sm:grid-cols-1" role="row">
        <span role="columnheader" class="text-[0.67rem] font-bold uppercase tracking-[0.11em] text-[#bcd0d6]">{{ __('messages.product_access_col_role') }}</span>
        <span role="columnheader" class="text-[0.67rem] font-bold uppercase tracking-[0.11em] text-[#bcd0d6]">{{ __('messages.product_access_col_mission') }}</span>
        <span role="columnheader" class="text-[0.67rem] font-bold uppercase tracking-[0.11em] text-[#bcd0d6] max-md:hidden max-sm:hidden">{{ __('messages.product_access_col_usage') }}</span>
      </div>
      <div class="grid min-h-[112px] grid-cols-[0.8fr_0.8fr_1.4fr] items-center gap-7 border-b border-[#dce6e4] px-7 py-[23px] max-md:grid-cols-[1fr_1fr] max-md:gap-[10px] max-sm:grid-cols-1" role="row">
        <strong role="cell" class="flex items-center gap-[14px] font-bold text-brand-primary"><i class="grid h-[38px] w-[38px] flex-none place-items-center rounded-[10px] bg-[#e6f1f0] text-[0.75rem] font-bold not-italic text-brand-secondary">A</i>{{ __('messages.product_access_admin_name') }}</strong>
        <span role="cell" class="text-[0.9rem] font-medium text-[#1b3444]">{{ __('messages.product_access_admin_mission') }}</span>
        <p role="cell" class="m-0 text-[0.86rem] text-[#5b6b73] max-md:col-span-full">{{ __('messages.product_access_admin_usage') }}</p>
      </div>
      <div class="grid min-h-[112px] grid-cols-[0.8fr_0.8fr_1.4fr] items-center gap-7 border-b border-[#dce6e4] px-7 py-[23px] max-md:grid-cols-[1fr_1fr] max-md:gap-[10px] max-sm:grid-cols-1" role="row">
        <strong role="cell" class="flex items-center gap-[14px] font-bold text-brand-primary"><i class="grid h-[38px] w-[38px] flex-none place-items-center rounded-[10px] bg-[#e6f1f0] text-[0.75rem] font-bold not-italic text-brand-secondary">G</i>{{ __('messages.product_access_manager_name') }}</strong>
        <span role="cell" class="text-[0.9rem] font-medium text-[#1b3444]">{{ __('messages.product_access_manager_mission') }}</span>
        <p role="cell" class="m-0 text-[0.86rem] text-[#5b6b73] max-md:col-span-full">{{ __('messages.product_access_manager_usage') }}</p>
      </div>
      <div class="grid min-h-[112px] grid-cols-[0.8fr_0.8fr_1.4fr] items-center gap-7 border-b border-[#dce6e4] px-7 py-[23px] max-md:grid-cols-[1fr_1fr] max-md:gap-[10px] max-sm:grid-cols-1" role="row">
        <strong role="cell" class="flex items-center gap-[14px] font-bold text-brand-primary"><i class="grid h-[38px] w-[38px] flex-none place-items-center rounded-[10px] bg-[#e6f1f0] text-[0.75rem] font-bold not-italic text-brand-secondary">P</i>{{ __('messages.product_access_lead_name') }}</strong>
        <span role="cell" class="text-[0.9rem] font-medium text-[#1b3444]">{{ __('messages.product_access_lead_mission') }}</span>
        <p role="cell" class="m-0 text-[0.86rem] text-[#5b6b73] max-md:col-span-full">{{ __('messages.product_access_lead_usage') }}</p>
      </div>
      <div class="grid min-h-[112px] grid-cols-[0.8fr_0.8fr_1.4fr] items-center gap-7 bg-[#eef6f5] px-7 py-[23px] max-md:grid-cols-[1fr_1fr] max-md:gap-[10px] max-sm:grid-cols-1" role="row">
        <strong role="cell" class="flex items-center gap-[14px] font-bold text-brand-primary"><i class="grid h-[38px] w-[38px] flex-none place-items-center rounded-[10px] bg-brand-accent text-[0.75rem] font-bold not-italic text-brand-primary">E</i>{{ __('messages.product_access_employee_name') }}</strong>
        <span role="cell" class="text-[0.9rem] font-medium text-[#1b3444]">{{ __('messages.product_access_employee_mission') }}</span>
        <p role="cell" class="m-0 text-[0.86rem] text-[#5b6b73] max-md:col-span-full">{{ __('messages.product_access_employee_usage') }}</p>
      </div>
    </div>
  </div>
</section>
