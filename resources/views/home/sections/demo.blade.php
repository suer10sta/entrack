<section data-home-section="demo" id="demo" class="scroll-mt-24 bg-[#16394a] py-28 text-white max-md:py-20" aria-labelledby="demo-title">
  <div class="mx-auto grid max-w-[1280px] items-center gap-[50px] px-6 max-md:grid-cols-1 max-md:gap-[38px] max-md:px-4 lg:grid-cols-[1.2fr_0.8fr]">
    <div data-home-demo-player class="relative aspect-video overflow-hidden rounded-[22px] border border-white/20 bg-brand-primary shadow-lg">
      <button type="button" data-home-demo-play aria-label="{{ __('messages.demo_youtube_play') }}" class="relative block h-full w-full">
        <img src="https://i.ytimg.com/vi/NcIsXZQU9wQ/hqdefault.jpg" alt="" width="480" height="360" loading="lazy" class="h-full w-full object-cover">
        <span class="absolute inset-0 flex items-center justify-center bg-brand-primary/20"><span class="grid h-[68px] w-[68px] place-items-center rounded-full bg-brand-secondary text-white shadow-lg transition-transform hover:scale-105"><i data-lucide="play" class="h-6 w-6 fill-current" aria-hidden="true"></i></span></span>
      </button>
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

<script src="{{ asset('js/home-demo.js') }}" defer></script>
