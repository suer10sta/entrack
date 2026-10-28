document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-summary-field]').forEach(input => {
        const update = () => {
            const target = document.querySelector(`[data-summary-value="${input.dataset.summaryField}"]`);
            if (target) target.textContent = input.value.trim() || (input.name === 'utm_campaign' ? 'Non définie' : 'À renseigner');
        };
        input.addEventListener('input', update);
        update();
    });
    const form = document.querySelector('[data-wizard-composition]');
    if (!form) return;
    const scheduleToggle = form.querySelector('[data-schedule-toggle]');
    const scheduleFields = form.querySelector('[data-schedule-fields]');
    const scheduleDate = form.querySelector('[name="scheduled_date"]');
    const scheduleTime = form.querySelector('[name="scheduled_time"]');
    const updateSchedule = () => {
        const enabled = scheduleToggle.checked;
        scheduleFields.hidden = !enabled;
        [scheduleDate, scheduleTime].forEach(input => { input.disabled = !enabled; input.required = enabled; });
        form.querySelector('[data-schedule-help]').textContent = enabled
            ? 'L’envoi commencera à partir de la date et de l’heure choisies (UTC).'
            : 'Désactivé : l’envoi démarrera après confirmation.';
        document.querySelector('[data-send-label]').textContent = enabled ? 'Programmer la campagne' : 'Envoyer la campagne';
        const date = scheduleDate.value.split('-').reverse().join('/');
        document.querySelector('[data-schedule-summary]').textContent = enabled
            ? (scheduleDate.value && scheduleTime.value ? `${date} à ${scheduleTime.value} UTC` : 'Date et heure à renseigner (UTC)')
            : 'Dès confirmation';
    };
    scheduleToggle.addEventListener('change', updateSchedule);
    scheduleDate.addEventListener('input', updateSchedule);
    scheduleTime.addEventListener('input', updateSchedule);
    updateSchedule();
    form.addEventListener('input', event => {
        if (event.target.name === 'confirm') return;
        document.querySelector('[data-preview-feedback]').textContent = 'Aperçu non actualisé : enregistrez le message pour le mettre à jour.';
    });
    document.querySelectorAll('[data-media-file]').forEach(input => {
        const url = form.querySelector(`[data-media-url="${input.dataset.mediaFile}"]`);
        input.addEventListener('change', () => { if (input.files.length) url.value = ''; });
        url.addEventListener('input', () => { if (url.value.trim()) input.value = ''; });
    });
    const destination = form.querySelector('[data-cta-destination]');
    const generated = form.querySelector('[data-cta-generated]');
    const updateLink = () => {
        try {
            const url = new URL(destination.value.trim());
            if (!['http:', 'https:'].includes(url.protocol) || url.username || url.password) throw new Error('Invalid URL');
            // Match the backend RFC3986 encoding of UTM values.
            const parts = destination.value.trim().split(/[?#]/)[0];
            const withoutFragment = destination.value.trim().split('#')[0];
            const query = withoutFragment.includes('?') ? withoutFragment.slice(withoutFragment.indexOf('?') + 1) : '';
            const pairs = query.split('&').filter(pair => {
                const key = decodeURIComponent(pair.split('=')[0].replace(/\+/g, ' ')).toLowerCase();
                return pair && !['utm_source', 'utm_medium', 'utm_campaign'].includes(key);
            }) || [];
            const encode = value => encodeURIComponent(value).replace(/[!'()*]/g, char => '%' + char.charCodeAt(0).toString(16).toUpperCase());
            pairs.push('utm_source=dalytrac', 'utm_medium=email');
            if (generated.dataset.utmCampaign) pairs.push('utm_campaign=' + encode(generated.dataset.utmCampaign));
            generated.value = parts + '?' + pairs.join('&') + (destination.value.includes('#') ? '#' + destination.value.split('#').slice(1).join('#') : '');
        } catch { generated.value = ''; }
    };
    destination.addEventListener('input', updateLink);
    updateLink();
    const confirm = form.querySelector('[name="confirm"]');
    confirm.addEventListener('change', () => confirm.setCustomValidity(''));
    form.addEventListener('submit', event => {
        if (event.submitter?.name === 'wizard_final' && !confirm.checked) {
            event.preventDefault();
            confirm.setCustomValidity('Confirmez l’envoi de cette campagne.');
            confirm.reportValidity();
        } else confirm.setCustomValidity('');
    });
});
