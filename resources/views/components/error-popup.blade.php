<dialog id="dt-error-popup" class="dt-error-popup" aria-labelledby="dt-error-title" aria-describedby="dt-error-message">
    <div class="dt-error-content">
        <span class="dt-error-icon" aria-hidden="true">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <circle cx="12" cy="12" r="9"/><path stroke-linecap="round" d="M12 7.5v5M12 16h.01"/>
            </svg>
        </span>
        <div class="dt-error-copy">
            <h2 id="dt-error-title">{{ __('messages.Error') }}</h2>
            <p id="dt-error-message"></p>
        </div>
        <button type="button" class="dt-error-dismiss" aria-label="{{ __('messages.Close') }}" data-error-close>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" d="m6 6 12 12M6 18 18 6"/></svg>
        </button>
    </div>
    <div class="dt-error-footer">
        <a class="dt-error-button" id="dt-error-fix" hidden></a>
        <button type="button" class="dt-error-button" data-error-close autofocus>{{ __('messages.Close') }}</button>
    </div>
</dialog>
<style>
    .dt-error-popup { width: min(460px, calc(100vw - 32px)); max-height: calc(100dvh - 32px); margin: auto; padding: 0; border: 1px solid #e2e8f0; border-radius: 18px; background: #fff; color: #0b2638; box-shadow: 0 24px 64px #0b263833; overflow: auto; }
    .dt-error-popup::backdrop { background: #0b263866; backdrop-filter: blur(3px); }
    .dt-error-content { display: flex; align-items: flex-start; gap: 14px; padding: 24px; }
    .dt-error-icon { display: flex; align-items: center; justify-content: center; flex-shrink: 0; width: 44px; height: 44px; border-radius: 13px; color: #dc2626; background: #fef2f2; }
    .dt-error-copy { flex: 1; min-width: 0; }
    .dt-error-copy h2 { margin: 1px 0 8px; font-size: 17px; line-height: 24px; font-weight: 650; }
    .dt-error-copy p { margin: 0; color: #64748b; font-size: 14px; line-height: 1.65; white-space: pre-line; overflow-wrap: anywhere; }
    .dt-error-dismiss { display: flex; padding: 4px; border: 0; border-radius: 6px; background: transparent; color: #64748b; cursor: pointer; }
    .dt-error-dismiss:hover { background: #f1f5f9; color: #0b2638; }
    .dt-error-footer { display: flex; flex-wrap: wrap; gap: 10px; justify-content: flex-end; padding: 14px 24px; border-top: 1px solid #f1f5f9; background: #f8fafc; }
    #dt-error-fix { text-decoration: none; }
    #dt-error-fix[hidden] { display: none; }
    .dt-error-button { min-height: 40px; padding: 9px 22px; border: 0; border-radius: 10px; background: #2d7080; color: #fff; font-size: 14px; font-weight: 600; cursor: pointer; }
    .dt-error-button:hover { background: #245c69; }
    .dt-error-popup button:focus-visible { outline: 2px solid #2d7080; outline-offset: 3px; }
    @media (max-width: 480px) { .dt-error-content { gap: 12px; padding: 20px 16px; } .dt-error-footer { padding: 12px 16px; } }
</style>
<script>
    (() => {
        const dialog = document.getElementById('dt-error-popup');
        dialog.querySelectorAll('[data-error-close]').forEach(button => {
            button.addEventListener('click', () => dialog.close());
        });
        window.showDalyTracError = (message, action = {}) => {
            dialog.querySelector('#dt-error-message').textContent = String(message ?? '');
            const link = dialog.querySelector('#dt-error-fix');
            link.hidden = true;
            link.removeAttribute('href');
            if (action.url && action.label) {
                const url = new URL(action.url, window.location.origin);
                if (url.origin === window.location.origin && ['http:', 'https:'].includes(url.protocol)) {
                    link.href = url.href;
                    link.textContent = action.label;
                    link.hidden = false;
                }
            }
            if (!dialog.open) dialog.showModal();
        };
    })();
</script>
