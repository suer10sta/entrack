<section class="bg-[#f8faf8] py-[105px] max-md:py-[76px]" aria-labelledby="next-title">
  <div class="mx-auto grid max-w-[1200px] items-center gap-[92px] px-6 max-md:gap-[34px] max-md:px-4 lg:grid-cols-[0.95fr_1.05fr]">
    <div>
      <p class="mb-[15px] text-[0.72rem] font-bold uppercase tracking-[0.15em] text-[#2d7080]">{{ __('messages.public_pricing_after_trial_label') }}</p>
      <h2 id="next-title" class="text-[clamp(2.35rem,3.4vw,3.65rem)] font-semibold leading-[1.1] tracking-[-0.052em] text-brand-primary">{{ __('messages.public_pricing_after_trial_title') }}</h2>
      <p class="mt-[21px] max-w-[560px] text-[#5b6b73]">{{ __('messages.public_pricing_after_trial_text') }}</p>
      <ol class="mt-[25px] list-none p-0">
        <li class="flex items-baseline gap-[18px] border-t border-[#dbe6e3] py-3 text-[0.88rem]"><b class="text-[0.7rem] font-bold text-brand-secondary">01</b><span>{{ __('messages.public_pricing_after_trial_step_1') }}</span></li>
        <li class="flex items-baseline gap-[18px] border-t border-[#dbe6e3] py-3 text-[0.88rem]"><b class="text-[0.7rem] font-bold text-brand-secondary">02</b><span>{{ __('messages.public_pricing_after_trial_step_2') }}</span></li>
        <li class="flex items-baseline gap-[18px] border-y border-[#dbe6e3] py-3 text-[0.88rem]"><b class="text-[0.7rem] font-bold text-brand-secondary">03</b><span>{{ __('messages.public_pricing_after_trial_step_3') }}</span></li>
      </ol>
    </div>
    <div data-home-demo-player data-video-id="3yk1gGhdmCw" class="relative aspect-video overflow-hidden rounded-[22px] border border-[#dce6e4] bg-brand-primary shadow-lg">
      <button type="button" data-home-demo-play aria-label="{{ __('messages.demo_youtube_play') }} : {{ collect(config('demo.youtube_videos'))->firstWhere('id', '3yk1gGhdmCw')['title'] }}" class="relative block h-full w-full">
        <img src="https://i.ytimg.com/vi/3yk1gGhdmCw/hqdefault.jpg" alt="" width="480" height="360" loading="lazy" class="h-full w-full object-cover">
        <span class="absolute inset-0 flex items-center justify-center bg-brand-primary/20"><span class="grid h-[68px] w-[68px] place-items-center rounded-full bg-brand-secondary text-white shadow-lg"><i data-lucide="play" class="h-6 w-6 fill-current" aria-hidden="true"></i></span></span>
      </button>
    </div>
  </div>
</section>

<script src="{{ asset('js/home-demo.js') }}" defer></script>
