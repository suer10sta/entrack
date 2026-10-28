document.addEventListener('DOMContentLoaded', () => {
    const playlist = document.querySelector('[data-demo-playlist]');
    if (!playlist) return;
    const videos = [...playlist.querySelectorAll('[data-demo-video]')];
    const player = playlist.querySelector('[data-demo-player]');
    const previous = playlist.querySelector('[data-demo-previous]');
    const next = playlist.querySelector('[data-demo-next]');
    let current = 0;

    const play = index => {
        const video = videos[index];
        if (!video || !/^[\w-]{11}$/.test(video.dataset.videoId)) return;
        current = index;
        const iframe = document.createElement('iframe');
        iframe.src = `https://www.youtube-nocookie.com/embed/${video.dataset.videoId}?autoplay=1&rel=0`;
        iframe.title = video.dataset.videoTitle;
        iframe.className = 'h-full w-full';
        iframe.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share';
        iframe.allowFullscreen = true;
        iframe.referrerPolicy = 'strict-origin-when-cross-origin';
        // Replace the previous player so only one video can play at a time.
        player.replaceChildren(iframe);
        playlist.querySelector('[data-demo-title]').textContent = video.dataset.videoTitle;
        playlist.querySelector('[data-demo-youtube]').href = `https://www.youtube.com/watch?v=${video.dataset.videoId}`;
        playlist.querySelector('[data-demo-current]').textContent = index + 1;
        videos.forEach((item, itemIndex) => {
            item.classList.toggle('active', itemIndex === index);
            if (itemIndex === index) item.setAttribute('aria-current', 'true');
            else item.removeAttribute('aria-current');
        });
        previous.disabled = index === 0;
        next.disabled = index === videos.length - 1;
    };

    videos.forEach((video, index) => video.addEventListener('click', () => play(index)));
    playlist.querySelector('[data-demo-play]').addEventListener('click', () => play(current));
    previous.addEventListener('click', () => play(current - 1));
    next.addEventListener('click', () => play(current + 1));
});
