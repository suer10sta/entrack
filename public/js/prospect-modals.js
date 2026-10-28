document.addEventListener('DOMContentLoaded', () => {
    const dialogs = [...document.querySelectorAll('[data-prospect-modal]')];
    const openers = new Map();
    const country = document.querySelector('[data-prospect-country]');
    if (country && window.TomSelect && !country.tomselect) {
        const dialog = country.closest('dialog');
        const selector = new TomSelect(country, {
            plugins: ['dropdown_input'],
            dropdownParent: '#prospect-create-modal',
            create: false,
            maxOptions: null,
            allowEmptyOption: true,
            placeholder: 'Choisir un pays',
            searchField: ['text', 'value'],
            sortField: { field: 'text', direction: 'asc' },
            render: { no_results: () => '<div class="no-results">Aucun pays trouvé.</div>' },
            onInitialize() {
                this.control_input.placeholder = 'Rechercher un pays…';
                this.control_input.setAttribute('aria-label', 'Rechercher un pays');
                this.dropdown.classList.add('crm-country-dropdown');
                if (country.hasAttribute('aria-invalid')) this.control_input.setAttribute('aria-invalid', 'true');
            },
        });
        // Keep the dropdown in the native dialog's top layer, clear of the form's clipping.
        selector.positionDropdown = () => {
            if (!selector.isOpen || !dialog.open) return;
            const control = selector.control.getBoundingClientRect();
            const box = dialog.getBoundingClientRect();
            const below = box.bottom - control.bottom - 10;
            const above = control.top - box.top - 10;
            const upwards = below < 190 && above > below;
            const space = Math.max(100, upwards ? above : below);
            selector.dropdown_content.style.maxHeight = `${Math.min(170, Math.max(60, space - 48))}px`;
            selector.dropdown.style.width = `${control.width}px`;
            selector.dropdown.style.left = `${control.left - box.left - dialog.clientLeft + dialog.scrollLeft}px`;
            const top = (upwards ? control.top - selector.dropdown.offsetHeight - 5 : control.bottom + 5);
            selector.dropdown.style.top = `${top - box.top - dialog.clientTop + dialog.scrollTop}px`;
        };
        country.setAttribute('aria-hidden', 'true');
        selector.on('dropdown_open', selector.positionDropdown);
        selector.on('type', () => requestAnimationFrame(selector.positionDropdown));
        dialog.addEventListener('scroll', selector.positionDropdown);
        window.addEventListener('resize', selector.positionDropdown);
        dialog.addEventListener('close', () => selector.close());
    }

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
