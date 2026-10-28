document.addEventListener('DOMContentLoaded', () => {
    const videos = [...document.querySelectorAll('[data-product-video]')].map(container => ({
        container,
        player: container.querySelector('[data-product-video-player]'),
        poster: container.querySelector('[data-product-video-play]'),
        visibility: 0,
    }));
    let active = null;
    const stop = video => {
        if (video?.player.querySelector('iframe')) video.player.replaceChildren(video.poster);
        if (active === video) active = null;
    };
    const play = (video, muted) => {
        if (active === video) return;
        const { videoId, videoTitle } = video.container.dataset;
        if (!/^[\w-]{11}$/.test(videoId)) return;
        videos.forEach(other => { if (other !== video) stop(other); });
        const iframe = document.createElement('iframe');
        iframe.src = `https://www.youtube-nocookie.com/embed/${videoId}?autoplay=1&mute=${muted ? 1 : 0}&playsinline=1&rel=0`;
        iframe.title = videoTitle;
        iframe.className = 'h-full w-full';
        iframe.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share';
        iframe.allowFullscreen = true;
        iframe.referrerPolicy = 'strict-origin-when-cross-origin';
        video.player.replaceChildren(iframe);
        active = video;
    };
    videos.forEach(video => {
        video.container.addEventListener('click', event => {
            if (event.target.closest('[data-product-video-play]')) play(video, false);
        });
    });
    if (!('IntersectionObserver' in window)) return; // Keep click-to-play as a fallback.
    const update = () => {
        if (document.hidden) { stop(active); return; }
        const candidate = videos.filter(video => video.visibility >= 0.55)
            .sort((a, b) => b.visibility - a.visibility)[0];
        if (candidate) play(candidate, true);
        else if (active && active.visibility < 0.2) stop(active);
    };
    const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            const video = videos.find(item => item.player === entry.target);
            if (video) video.visibility = entry.isIntersecting ? entry.intersectionRatio : 0;
        });
        update();
    }, { rootMargin: '-85px 0px -10px 0px', threshold: [0, 0.2, 0.55, 0.75, 1] });
    videos.forEach(video => observer.observe(video.player));
    document.addEventListener('visibilitychange', update);
});
