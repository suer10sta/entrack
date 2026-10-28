<section id="demonstration" class="scroll-mt-24 bg-[#edf5f4] py-28 max-md:py-20" aria-labelledby="demo-title">
  <div class="mx-auto grid max-w-[1280px] items-center gap-[50px] px-6 max-md:gap-[34px] max-md:px-4 lg:grid-cols-[1.2fr_0.8fr] max-md:grid-cols-1">
    <div data-home-demo-player class="relative aspect-video overflow-hidden rounded-[22px] border border-[#dce6e4] bg-brand-primary shadow-lg">
      <button type="button" data-home-demo-play aria-label="{{ __('messages.demo_youtube_play') }}" class="relative block h-full w-full">
        <img src="https://i.ytimg.com/vi/NcIsXZQU9wQ/hqdefault.jpg" alt="" width="480" height="360" loading="lazy" class="h-full w-full object-cover">
        <span class="absolute inset-0 flex items-center justify-center bg-brand-primary/20"><span class="grid h-[68px] w-[68px] place-items-center rounded-full bg-brand-secondary text-white shadow-lg transition-transform hover:scale-105"><i data-lucide="play" class="h-6 w-6 fill-current" aria-hidden="true"></i></span></span>
      </button>
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

<script src="{{ asset('js/home-demo.js') }}" defer></script>
