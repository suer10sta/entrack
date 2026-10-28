document.addEventListener('DOMContentLoaded', () => {
    const deleteDialog = document.querySelector('[data-delete-campaign-dialog]');
    let pendingDelete = null;
    let deleteConfirmed = false;
    document.querySelectorAll('[data-delete-campaign]').forEach(form => {
        form.addEventListener('submit', event => {
            if (deleteConfirmed && pendingDelete === form) {
                form.querySelector('button').disabled = true;
                return;
            }
            event.preventDefault();
            if (!deleteDialog || deleteDialog.open) return;
            pendingDelete = form;
            deleteDialog.querySelector('[data-delete-campaign-title]').textContent = `Supprimer la campagne « ${form.dataset.campaignName} » ?`;
            deleteDialog.showModal();
        });
    });
    deleteDialog?.querySelector('[data-confirm-campaign-delete]').addEventListener('click', event => {
        if (!pendingDelete || deleteConfirmed) return;
        deleteConfirmed = true;
        event.currentTarget.disabled = true;
        pendingDelete.requestSubmit();
        deleteDialog.close();
    });
    deleteDialog?.querySelectorAll('[data-cancel-campaign-delete]').forEach(button => {
        button.addEventListener('click', () => deleteDialog.close());
    });
    deleteDialog?.addEventListener('close', () => {
        pendingDelete?.querySelector('button').focus();
        pendingDelete = null;
        deleteConfirmed = false;
        deleteDialog.querySelector('[data-confirm-campaign-delete]').disabled = false;
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
    const audience = document.querySelector('[data-campaign-audience]');
    const audienceOptions = document.querySelector('[data-audience-options]');
    let audienceRequest = 0;
    const updateAudience = async () => {
        if (!audience) return;
        const request = ++audienceRequest;
        const custom = audience.value === 'custom';
        const audienceLabel = document.querySelector('[data-wizard-audience-label]');
        if (audienceLabel) audienceLabel.textContent = audience.selectedOptions[0].textContent;
        document.querySelector('[data-custom-audience]').hidden = !custom;
        document.querySelector('[data-save-preset]').hidden = custom;
        const feedback = document.querySelector('[data-audience-feedback]');
        const buttons = [...document.querySelectorAll('button[form="campaign-audience-selection"]')];
        buttons.forEach(button => button.disabled = !custom);
        feedback.textContent = custom ? '' : 'Calcul des destinataires…';
        if (custom) {
            update();
            return;
        }
        const summary = document.querySelector('[data-wizard-recipient-count]');
        if (summary) summary.textContent = '…';
        try {
            const url = new URL(audienceOptions.dataset.countUrl, window.location.href);
            url.searchParams.set('audience', audience.value);
            const response = await fetch(url, {headers: {'Accept': 'application/json'}, credentials: 'same-origin', cache: 'no-store'});
            const data = await response.json();
            if (request !== audienceRequest) return;
            if (!response.ok) throw new Error(Object.values(data.errors || {}).flat()[0] || 'Impossible de calculer cette audience. Réessayez.');
            feedback.textContent = `${data.count} destinataire(s) unique(s). Cette audience remplacera la sélection enregistrée.`;
            if (summary) summary.textContent = data.count;
            buttons.forEach(button => button.disabled = false);
        } catch (error) {
            if (request !== audienceRequest) return;
            feedback.textContent = error.message;
            if (summary) summary.textContent = '—';
        }
    };
    audience?.addEventListener('change', updateAudience);
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
    updateAudience();
});
