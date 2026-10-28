@props(['videoId'])
@php($video = collect(config('demo.youtube_videos', []))->firstWhere('id', $videoId))
@if($video)
<div data-product-video data-video-id="{{ $video['id'] }}" data-video-title="{{ $video['title'] }}">
  <div data-product-video-player class="relative aspect-video overflow-hidden rounded-[14px] bg-brand-primary shadow-lg">
    <button type="button" data-product-video-play aria-label="{{ __('messages.demo_youtube_play') }} : {{ $video['title'] }}" class="relative block h-full w-full">
      <img src="https://i.ytimg.com/vi/{{ $video['id'] }}/hqdefault.jpg" alt="" width="480" height="360" loading="lazy" class="h-full w-full object-cover">
      <span class="absolute inset-0 flex items-center justify-center bg-brand-primary/20"><span class="grid h-[68px] w-[68px] place-items-center rounded-full bg-brand-secondary text-white shadow-lg"><i data-lucide="play" class="h-6 w-6 fill-current" aria-hidden="true"></i></span></span>
    </button>
  </div>
  <a href="https://www.youtube.com/watch?v={{ $video['id'] }}" target="_blank" rel="noopener noreferrer" class="mt-3 inline-flex items-center gap-1.5 text-sm font-semibold text-brand-secondary">{{ __('messages.demo_youtube_watch') }}<i data-lucide="external-link" class="h-4 w-4" aria-hidden="true"></i></a>
</div>
@endif
