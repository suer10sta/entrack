(() => {
    const page = document.querySelector('.cs-list-page');
    if (!page) return;
    const storageKey = 'dalytrac-support-list-feedback';
    try {
        const saved = JSON.parse(sessionStorage.getItem(storageKey) || 'null');
        sessionStorage.removeItem(storageKey);
        if (saved && ['success', 'warning'].includes(saved.kind) && typeof saved.message === 'string') {
            const feedback = document.createElement('div');
            feedback.className = 'cs-feedback cs-feedback-' + saved.kind;
            feedback.setAttribute('role', 'status');
            feedback.textContent = saved.message;
            page.prepend(feedback);
        }
    } catch (_) { /* Storage can be disabled; it is never required to submit. */ }
    let opener = null;
    page.addEventListener('click', (event) => {
        const button = event.target.closest('[data-open-reply]');
        if (button) {
            const dialog = document.getElementById(button.dataset.openReply);
            if (!dialog || typeof dialog.showModal !== 'function') return;
            opener = button;
            dialog.showModal();
            dialog.querySelector('textarea').focus();
        }
        const close = event.target.closest('[data-close-reply]');
        if (close && close.closest('form').dataset.submitting !== '1') close.closest('dialog').close();
    });
    page.querySelectorAll('dialog[data-quick-reply]').forEach((dialog) => {
        dialog.addEventListener('close', () => { if (opener?.isConnected) opener.focus(); });
        dialog.addEventListener('cancel', (event) => { if (dialog.querySelector('form').dataset.submitting === '1') event.preventDefault(); });
        dialog.addEventListener('click', (event) => {
            if (event.target !== dialog || dialog.querySelector('form').dataset.submitting === '1') return;
            const rect = dialog.getBoundingClientRect();
            if (event.clientX < rect.left || event.clientX > rect.right || event.clientY < rect.top || event.clientY > rect.bottom) dialog.close();
        });
        const form = dialog.querySelector('form');
        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            if (form.dataset.submitting === '1') return;
            const error = form.querySelector('.cs-quick-error');
            const submit = form.querySelector('[type="submit"]');
            error.hidden = true;
            form.dataset.submitting = '1';
            form.setAttribute('aria-busy', 'true');
            submit.disabled = true;
            try {
                // Existing Phase 4 endpoint, CSRF token and submission UUID only.
                // The server still owns persistence, status, transport and its redirect.
                const response = await fetch(form.action, { method: 'POST', body: new FormData(form), credentials: 'same-origin', headers: { 'Accept': 'application/json' } });
                if (!response.ok) {
                    let message = [401, 419].includes(response.status) ? page.dataset.sessionError : page.dataset.networkError;
                    if ([409, 422].includes(response.status)) {
                        const data = await response.json();
                        message = Object.values(data.errors || {}).flat().join(' ') || data.message || message;
                    }
                    throw new Error(message);
                }
                const documentResult = new DOMParser().parseFromString(await response.text(), 'text/html');
                const feedback = documentResult.querySelector('.cs-feedback-success, .cs-feedback-warning');
                if (!feedback) throw new Error(page.dataset.networkError);
                const saved = { kind: feedback.classList.contains('cs-feedback-success') ? 'success' : 'warning', message: feedback.textContent.trim() };
                dialog.close();
                try { sessionStorage.setItem(storageKey, JSON.stringify(saved)); }
                catch (_) { window.alert(saved.message); }
                // Refresh the same filtered/paginated URL: cards and KPIs come from SQL.
                window.location.reload();
            } catch (failure) {
                // Preserve the UUID and message: a manual replay remains idempotent.
                error.textContent = failure instanceof TypeError ? page.dataset.networkError : (failure.message || page.dataset.networkError);
                error.hidden = false;
            } finally {
                delete form.dataset.submitting;
                form.removeAttribute('aria-busy');
                submit.disabled = false;
            }
        });
    });
    function previews() {
        page.querySelectorAll('[data-ticket-preview]').forEach((preview) => {
            const more = preview.parentElement.querySelector('[data-preview-more]');
            more.hidden = preview.dataset.truncated !== '1' && preview.scrollHeight <= preview.clientHeight + 1;
        });
    }
    previews();
    window.addEventListener('resize', previews);
})();
