document.addEventListener('DOMContentLoaded', () => {
    const dialog = document.getElementById('crm-link-dialog');
    if (!dialog) return;
    const input = dialog.querySelector('[data-prospect-link-value]');
    const status = dialog.querySelector('[data-copy-prospect-link-status]');
    let opener;
    document.querySelectorAll('[data-show-tracked-link]').forEach((button) => {
        button.addEventListener('click', () => {
            opener = button;
            input.value = button.dataset.showTrackedLink;
            status.textContent = '';
            dialog.showModal();
        });
    });
    dialog.querySelector('[data-close-tracked-link]').addEventListener('click', () => dialog.close());
    dialog.addEventListener('click', (event) => {
        if (event.target !== dialog) return;
        const box = dialog.getBoundingClientRect();
        if (event.clientX < box.left || event.clientX > box.right || event.clientY < box.top || event.clientY > box.bottom) dialog.close();
    });
    dialog.addEventListener('close', () => opener?.focus());
});
