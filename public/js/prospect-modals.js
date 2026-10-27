document.addEventListener('DOMContentLoaded', () => {
    const dialogs = [...document.querySelectorAll('[data-prospect-modal]')];
    const openers = new Map();
    document.querySelectorAll('[data-open-prospect-modal]').forEach((button) => {
        const dialog = dialogs.find((item) => item.dataset.prospectModal === button.dataset.openProspectModal);
        if (!dialog || typeof dialog.showModal !== 'function') return;
        button.addEventListener('click', (event) => {
            event.preventDefault();
            if (button.dataset.editProspect) {
                const values = JSON.parse(button.dataset.editProspect);
                const form = dialog.querySelector('[data-prospect-edit-form]');
                form.action = button.dataset.editAction;
                form.querySelectorAll('[aria-invalid]').forEach((input) => input.removeAttribute('aria-invalid'));
                const source = form.elements.namedItem('source');
                source.querySelectorAll('[data-custom-source]').forEach((option) => option.remove());
                if (values.source && ![...source.options].some((option) => option.value === values.source)) {
                    const option = new Option(values.source, values.source);
                    option.dataset.customSource = '';
                    source.add(option);
                }
                ['name', 'email', 'phone', 'company_name', 'country', 'source'].forEach((field) => {
                    form.elements.namedItem(field).value = values[field] ?? '';
                });
            }
            openers.set(dialog, button);
            dialog.showModal();
        });
    });
    dialogs.forEach((dialog) => {
        dialog.querySelectorAll('[data-close-prospect-modal]').forEach((button) => {
            button.addEventListener('click', () => dialog.close());
        });
        dialog.addEventListener('click', (event) => {
            if (event.target !== dialog) return;
            const box = dialog.getBoundingClientRect();
            if (event.clientX < box.left || event.clientX > box.right || event.clientY < box.top || event.clientY > box.bottom) dialog.close();
        });
        dialog.addEventListener('close', () => {
            openers.get(dialog)?.focus();
            const url = new URL(window.location.href);
            if (url.searchParams.get('modal') === dialog.dataset.prospectModal) {
                url.searchParams.delete('modal');
                window.history.replaceState(null, '', url);
            }
        });
        if (dialog.hasAttribute('data-auto-open')) {
            const button = document.querySelector(`[data-open-prospect-modal="${dialog.dataset.prospectModal}"]`);
            if (button) openers.set(dialog, button);
            dialog.showModal();
        }
    });
});
