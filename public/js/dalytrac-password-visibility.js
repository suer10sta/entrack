(() => {
    if (window.DalyTracPasswordVisibility) return;
    window.DalyTracPasswordVisibility = true;
    const labels = document.currentScript?.dataset || {};
    const showLabel = labels.showPassword || 'Show password';
    const hideLabel = labels.hidePassword || 'Hide password';
    const controls = new WeakMap();
    let sequence = 0;
    const icon = visible => `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/>${visible ? '<path d="m3 3 18 18"/>' : ''}</svg>`;

    function update(input, button) {
        const visible = input.type !== 'password';
        button.setAttribute('aria-label', visible ? hideLabel : showLabel);
        button.setAttribute('title', visible ? hideLabel : showLabel);
        button.setAttribute('aria-pressed', String(visible));
        button.disabled = input.disabled;
        button.innerHTML = icon(visible);
    }

    function enhance(input) {
        if (!input.isConnected) return;
        if (controls.has(input)) return;
        // Billit already has a dedicated reveal button and masked-value handling.
        if (input.name === 'billit_api_key' && input.parentElement?.querySelector('[data-toggle-billit-visibility]')) return;
        if (!input.id) {
            do { input.id = `dt-password-input-${++sequence}`; } while (document.getElementById(input.id) !== input);
        }
        const wrapper = document.createElement('div');
        wrapper.className = 'dt-password-field';
        input.parentNode.insertBefore(wrapper, input);
        wrapper.appendChild(input);
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'dt-password-toggle';
        button.setAttribute('aria-controls', input.id);
        controls.set(input, button);
        // Keep focus in editable inputs: revealing must not trigger blur-to-save.
        button.addEventListener('mousedown', event => event.preventDefault());
        button.addEventListener('click', event => {
            event.preventDefault();
            event.stopPropagation();
            input.type = input.type === 'password' ? 'text' : 'password';
            update(input, button);
        });
        wrapper.appendChild(button);
        update(input, button);
    }

    function scan(root) {
        if (root.matches?.('input[type="password"]')) enhance(root);
        root.querySelectorAll?.('input[type="password"]').forEach(enhance);
    }

    function init() {
        scan(document);
        new MutationObserver(changes => {
            changes.forEach(change => {
                if (change.type === 'childList') change.addedNodes.forEach(scan);
                else if (change.target.matches?.('input')) {
                    const button = controls.get(change.target);
                    if (button) update(change.target, button);
                    else scan(change.target);
                }
            });
        }).observe(document.body, {childList: true, subtree: true, attributes: true, attributeFilter: ['type', 'disabled']});
        // Never carry a revealed field into submission or a new form session.
        ['submit', 'reset'].forEach(event => document.addEventListener(event, e => {
            e.target.querySelectorAll?.('.dt-password-field > input').forEach(input => {
                input.type = 'password';
                update(input, controls.get(input));
            });
        }, true));
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init, {once: true});
    else init();
})();
