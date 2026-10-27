document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-prospect-link]').forEach((section) => {
        const input = section.querySelector('[data-prospect-link-value]');
        const button = section.querySelector('[data-copy-prospect-link]');
        const status = section.querySelector('[data-copy-prospect-link-status]');
        button.addEventListener('click', async () => {
            try {
                if (!navigator.clipboard?.writeText) throw new Error('Clipboard unavailable');
                await navigator.clipboard.writeText(input.value);
                status.textContent = 'Lien copié.';
            } catch {
                input.focus();
                input.select();
                status.textContent = 'Lien sélectionné. Utilisez Ctrl+C (ou ⌘C) pour le copier.';
            }
        });
    });
});
