document.addEventListener('DOMContentLoaded', () => {
    const player = document.querySelector('[data-home-demo-player]');
    const button = player?.querySelector('[data-home-demo-play]');
    button?.addEventListener('click', () => {
        const iframe = document.createElement('iframe');
        const videoId = player.dataset.videoId || 'NcIsXZQU9wQ';
        if (!/^[\w-]{11}$/.test(videoId)) return;
        const start = player.dataset.videoId ? 0 : 1;
        iframe.src = `https://www.youtube-nocookie.com/embed/${videoId}?start=${start}&autoplay=1&rel=0`;
        iframe.title = button.getAttribute('aria-label');
        iframe.className = 'h-full w-full';
        iframe.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share';
        iframe.allowFullscreen = true;
        iframe.referrerPolicy = 'strict-origin-when-cross-origin';
        player.replaceChildren(iframe);
    }, { once: true });
});
