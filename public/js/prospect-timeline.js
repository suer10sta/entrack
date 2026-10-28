document.addEventListener('DOMContentLoaded', () => {
    const timeline = document.querySelector('[data-prospect-timeline]');
    if (!timeline) return;

    let formatter;
    try {
        // Only the Manager guard's preference is used; otherwise use the browser's zone.
        const timeZone = timeline.dataset.timezone || Intl.DateTimeFormat().resolvedOptions().timeZone;
        formatter = new Intl.DateTimeFormat('fr-FR', {
            timeZone, day: '2-digit', month: '2-digit', year: 'numeric',
            hour: '2-digit', minute: '2-digit', hourCycle: 'h23',
        });
    } catch (_) {
        return; // Keep the explicit UTC fallback if timezone formatting is unavailable.
    }

    timeline.querySelectorAll('time[datetime]').forEach((element) => {
        // Always format the original ISO instant, never the previously displayed text.
        const instant = new Date(element.getAttribute('datetime'));
        if (Number.isNaN(instant.getTime())) return;
        const parts = Object.fromEntries(formatter.formatToParts(instant).map(part => [part.type, part.value]));
        element.textContent = `${parts.day}/${parts.month}/${parts.year} ${parts.hour}:${parts.minute}`;
        element.title = formatter.resolvedOptions().timeZone;
    });
    const label = document.querySelector('[data-timeline-timezone]');
    if (label) label.textContent = formatter.resolvedOptions().timeZone;
});
