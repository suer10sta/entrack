document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-delete-campaign]').forEach(form => {
        form.addEventListener('submit', event => {
            if (!window.confirm(`Supprimer la campagne « ${form.dataset.campaignName} » ? Les envois restants seront arrêtés. Les emails déjà envoyés ne peuvent pas être rappelés.`)) {
                event.preventDefault();
                return;
            }
            form.querySelector('button').disabled = true;
        });
    });
    // Only read-only, queued/sending campaign pages refresh. Draft forms are never reloaded.
    if (document.querySelector('[data-campaign-refresh]')) {
        const refresh = () => window.setTimeout(() => {
            if (document.visibilityState === 'visible') window.location.reload();
            else refresh();
        }, 15000);
        refresh();
    }
    const selection = document.querySelector('[data-recipient-selection]');
    const send = document.querySelector('[data-campaign-send] button');
    const markDirty = () => {
        if (send) send.disabled = true;
        const feedback = document.querySelector('[data-selection-feedback]');
        if (feedback) feedback.textContent = 'Modifications non enregistrées : enregistrez le message ou la sélection avant l’envoi.';
    };
    document.querySelector('[data-campaign-content]')?.addEventListener('input', markDirty);
    if (!selection) return;
    const saved = new Map(Object.entries(JSON.parse(selection.dataset.selected)));
    const checkboxes = [...selection.querySelectorAll('[name="selected_ids[]"]')];
    const all = selection.querySelector('[data-select-visible]');
    const update = () => {
        const current = new Map(saved);
        checkboxes.forEach((checkbox) => {
            current.delete(checkbox.value);
            if (checkbox.checked && checkbox.dataset.email) current.set(checkbox.value, checkbox.dataset.email);
        });
        const count = new Set(current.values()).size;
        selection.querySelector('[data-selection-count]').textContent = count;
        const summary = document.querySelector('[data-wizard-recipient-count]');
        if (summary) summary.textContent = count;
        const enabled = checkboxes.filter((checkbox) => !checkbox.disabled);
        all.checked = enabled.length > 0 && enabled.every((checkbox) => checkbox.checked);
        all.indeterminate = enabled.some((checkbox) => checkbox.checked) && !all.checked;
    };
    checkboxes.forEach((checkbox) => checkbox.addEventListener('change', () => { update(); markDirty(); }));
    all.addEventListener('change', () => {
        checkboxes.filter((checkbox) => !checkbox.disabled).forEach((checkbox) => { checkbox.checked = all.checked; });
        update(); markDirty();
    });
    update();
});
