(() => {
    if (window.DalyTracConsent) return;
    const measurement = 'G-MDJNVZY2WF';
    const bannerKey = 'dalytrac_cookie_banner_state', settingsKey = 'dalytrac_cookie_consent';
    let version = 0;
    const read = key => { try { return JSON.parse(localStorage.getItem(key)); } catch (_) { return null; } };
    function allowed() {
        const banner = read(bannerKey), settings = read(settingsKey);
        if (settings && (!banner || Date.parse(settings.at) >= Date.parse(banner.timestamp))) return settings.audience === true;
        return banner?.choice === 'all';
    }
    async function sync(granted) {
        const current = ++version;
        // A refusal reaches the server immediately, without waiting for the Google tag.
        const context = { granted, revision: Date.now() };
        if (granted) {
            const get = field => new Promise(resolve => {
                const timer = setTimeout(() => resolve(null), 1200);
                window.gtag('get', measurement, field, value => { clearTimeout(timer); resolve(value); });
            });
            const [client, session] = await Promise.all([get('client_id'), get('session_id')]);
            if (client && /^[0-9]+\.[0-9]+$/.test(String(client))) context.client_id = String(client);
            if (session && /^[0-9]+$/.test(String(session))) context.session_id = String(session);
        }
        if (current !== version) return;
        const endpoint = document.querySelector('meta[name="analytics-consent-url"]')?.content;
        const csrf = document.querySelector('meta[name="analytics-csrf-token"]')?.content;
        if (endpoint && csrf) {
            const controller = new AbortController();
            const timeout = setTimeout(() => controller.abort(), 2000);
            try {
                await fetch(endpoint, { method: 'POST', credentials: 'same-origin', keepalive: true, signal: controller.signal,
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, Accept: 'application/json' }, body: JSON.stringify(context) }).catch(() => {});
            } finally { clearTimeout(timeout); }
        }
    }
    function update() {
        const granted = allowed();
        window.gtag('consent', 'update', { analytics_storage: granted ? 'granted' : 'denied', ad_storage: 'denied', ad_user_data: 'denied', ad_personalization: 'denied' });
        return sync(granted);
    }
    window.DalyTracConsent = {
        allowed,
        choose(choice, preferences = choice === 'all') {
            const at = new Date().toISOString();
            try {
                localStorage.setItem(bannerKey, JSON.stringify({ choice, timestamp: at }));
                localStorage.setItem(settingsKey, JSON.stringify({ necessary: true, preferences, audience: choice === 'all', at }));
            } catch (_) {
                // Storage unavailable: apply this choice to the current page only.
                window.gtag('consent', 'update', { analytics_storage: choice === 'all' ? 'granted' : 'denied', ad_storage: 'denied', ad_user_data: 'denied', ad_personalization: 'denied' });
                return sync(choice === 'all');
            }
            return update();
        }
    };
    window.addEventListener('storage', event => { if ([bannerKey, settingsKey].includes(event.key)) update(); });
    update();
    // Preserve the existing form action; only synchronize consent/client context before submission.
    document.querySelectorAll('form').forEach(form => {
        if (new URL(form.action, location.href).pathname !== '/register' || form.method.toLowerCase() !== 'post') return;
        let ready = false, submitting = false;
        form.addEventListener('submit', async event => {
            if (ready) return;
            event.preventDefault();
            if (submitting) return;
            submitting = true;
            await sync(allowed()); ready = true;
            if (event.submitter) form.requestSubmit(event.submitter); else form.requestSubmit();
        });
    });
})();
