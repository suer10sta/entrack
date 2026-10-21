@once
{{-- Correct spelling on confirmation; translate only on explicit language selection. --}}
<div id="chronoStopModal" class="chrono-modal" role="dialog" aria-modal="true" aria-labelledby="chronoStopTitle">
    <div class="chrono-modal-card">
        <button type="button" id="chronoStopClose" class="chrono-close-button" aria-label="{{ __('messages.Cancel') }}">&times;</button>
        <h2 id="chronoStopTitle" class="text-lg font-semibold text-[#0B2638] mb-2">{{ __('messages.Stop Task') }}</h2>
        <p class="text-sm text-slate-500 mb-4">{{ __('messages.chrono_stop_auto_description') }}</p>
        <div class="text-left mb-4">
            <label for="chronoStopText" class="block text-sm font-medium text-slate-700 mb-2">{{ __('messages.Comment') }} <span class="text-red-500">*</span></label>
            <textarea id="chronoStopText" rows="4" maxlength="500" placeholder="{{ __('messages.Describe what you worked on...') }}" class="border border-slate-300 rounded-xl px-3 py-2 w-full text-sm focus:outline-none focus:ring-2 focus:ring-[#2D7080]/25 resize-none"></textarea>
            <p id="chronoStopError" role="alert" class="text-xs text-red-600 mt-2 hidden"></p>
            <p id="chronoStopStatus" role="status" aria-live="polite" class="text-xs text-[#2D7080] mt-2 hidden"></p>
        </div>
        <div class="flex items-center justify-between gap-1 sm:gap-2 pt-2">
            <div id="chronoStopTranslateWrap" class="relative">
                <div id="chronoStopLanguageMenu" hidden role="menu" aria-labelledby="chronoStopTranslateBtn" class="absolute bottom-full left-0 z-20 mb-2 w-40 rounded-xl border border-slate-200 bg-white p-1 shadow-lg text-left">
                    <button type="button" id="chronoStopLanguage-fr" role="menuitem" tabindex="-1" class="block w-full rounded-lg px-3 py-2 text-left text-sm text-slate-700 hover:bg-slate-100 focus:bg-slate-100 focus:outline-none" lang="fr">Français</button>
                    <button type="button" id="chronoStopLanguage-en" role="menuitem" tabindex="-1" class="block w-full rounded-lg px-3 py-2 text-left text-sm text-slate-700 hover:bg-slate-100 focus:bg-slate-100 focus:outline-none" lang="en">English</button>
                    <button type="button" id="chronoStopLanguage-nl" role="menuitem" tabindex="-1" class="block w-full rounded-lg px-3 py-2 text-left text-sm text-slate-700 hover:bg-slate-100 focus:bg-slate-100 focus:outline-none" lang="nl">Nederlands</button>
                    <button type="button" id="chronoStopLanguage-es" role="menuitem" tabindex="-1" class="block w-full rounded-lg px-3 py-2 text-left text-sm text-slate-700 hover:bg-slate-100 focus:bg-slate-100 focus:outline-none" lang="es">Español</button>
                </div>
                <button type="button" id="chronoStopTranslateBtn" class="dt-btn dt-btn-ghost !px-2 !text-xs sm:!px-3 sm:!text-sm disabled:opacity-50" aria-haspopup="menu" aria-controls="chronoStopLanguageMenu" aria-expanded="false" disabled>{{ __('messages.chrono_stop_translate') }}</button>
            </div>
            <div class="flex items-center gap-1 sm:gap-2">
                <button type="button" id="chronoStopCancelBtn" class="dt-btn dt-btn-ghost !px-2 !text-xs sm:!px-3 sm:!text-sm">{{ __('messages.Cancel') }}</button>
                <button type="button" id="chronoStopConfirmBtn" class="dt-btn dt-btn-primary !px-2 !text-xs sm:!px-3 sm:!text-sm disabled:opacity-50" disabled>{{ __('messages.Stop') }}</button>
            </div>
        </div>
    </div>
</div>
<script>
(function () {
    if (window.ChronoStopModal) return;
    const modal = document.getElementById('chronoStopModal');
    const text = document.getElementById('chronoStopText');
    const error = document.getElementById('chronoStopError');
    const status = document.getElementById('chronoStopStatus');
    const confirm = document.getElementById('chronoStopConfirmBtn');
    const translate = document.getElementById('chronoStopTranslateBtn');
    const languageMenu = document.getElementById('chronoStopLanguageMenu');
    const translateWrap = document.getElementById('chronoStopTranslateWrap');
    const languageButtons = ['fr', 'en', 'nl', 'es'].map(language => ({ language, button: document.getElementById('chronoStopLanguage-' + language) }));
    const cancel = document.getElementById('chronoStopCancelBtn');
    const close = document.getElementById('chronoStopClose');
    let action = null, submitHandler = null, busy = false;
    function closeLanguageMenu() {
        languageMenu.hidden = true;
        translate.setAttribute('aria-expanded', 'false');
    }
    function open(url, handler = null) {
        if (busy) return;
        action = url; submitHandler = handler; text.value = '';
        closeLanguageMenu();
        error.classList.add('hidden'); status.classList.add('hidden'); confirm.disabled = true; translate.disabled = true;
        if (typeof stopAutoRefresh === 'function') stopAutoRefresh();
        modal.style.display = 'block'; text.focus();
    }
    function closeAll() {
        if (busy) return;
        closeLanguageMenu();
        modal.style.display = 'none'; action = null; submitHandler = null;
        if (typeof startAutoRefresh === 'function') startAutoRefresh();
    }
    function setBusy(value) {
        busy = value; text.disabled = value; cancel.disabled = value; close.disabled = value;
        if (value) closeLanguageMenu();
        confirm.disabled = value || !text.value.trim();
        translate.disabled = value || !text.value.trim();
        modal.setAttribute('aria-busy', String(value));
    }
    text.addEventListener('input', () => { confirm.disabled = busy || !text.value.trim(); translate.disabled = confirm.disabled; if (translate.disabled) closeLanguageMenu(); error.classList.add('hidden'); });
    text.addEventListener('keydown', event => {
        if (event.key === 'Enter' && !event.shiftKey) { event.preventDefault(); if (!confirm.disabled) confirm.click(); }
    });
    translate.addEventListener('click', () => {
        if (busy || !text.value.trim()) return;
        languageMenu.hidden = !languageMenu.hidden;
        translate.setAttribute('aria-expanded', String(!languageMenu.hidden));
        if (!languageMenu.hidden) languageButtons[0].button.focus();
    });
    languageMenu.addEventListener('keydown', event => {
        const index = languageButtons.findIndex(item => item.button === document.activeElement);
        if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
            event.preventDefault();
            languageButtons[(index + (event.key === 'ArrowDown' ? 1 : 3)) % 4].button.focus();
        }
    });
    languageButtons.forEach(({language, button}) => button.addEventListener('click', () => translateDescription(language)));
    async function translateDescription(language) {
        if (busy || !text.value.trim()) return;
        setBusy(true); error.classList.add('hidden');
        status.textContent = @json(__('messages.Translating...')); status.classList.remove('hidden');
        try {
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const response = await fetch('/tasks/prepare-stop-comment', {
                method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token },
                body: JSON.stringify({ comment: text.value, translate: true, language })
            });
            if (!response.ok) throw new Error('Translation failed');
            const result = await response.json();
            if (typeof result.corrected !== 'string' || !result.corrected.trim() || result.corrected.length > 500) throw new Error('Invalid translation');
            text.value = result.corrected.trim();
        } catch (err) {
            error.textContent = @json(__('messages.chrono_stop_translation_failed')); error.classList.remove('hidden');
        } finally { setBusy(false); status.classList.add('hidden'); }
    }
    confirm.addEventListener('click', async () => {
        if (busy || !action || !text.value.trim()) return;
        setBusy(true); error.classList.add('hidden');
        status.textContent = @json(__('messages.chrono_stop_processing')); status.classList.remove('hidden');
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        try {
            const prepared = await fetch('/tasks/prepare-stop-comment', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token },
                body: JSON.stringify({ comment: text.value, translate: false })
            });
            if (!prepared.ok) throw new Error('Correction failed');
            const result = await prepared.json();
            if (typeof result.corrected !== 'string' || !result.corrected.trim() || result.corrected.length > 500) throw new Error('Invalid correction');
            text.value = result.corrected;
            if (submitHandler) {
                await submitHandler(text.value);
            } else {
                const data = new FormData(); data.append('_token', token); data.append('stop_comment', text.value);
                const stopped = await fetch(action, { method: 'POST', body: data, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                if (!stopped.ok) throw new Error('Stop failed');
                if (typeof refreshEmployeeData === 'function') refreshEmployeeData();
                window.dispatchEvent(new CustomEvent('chrono-revalidate', { detail: { force: true } }));
            }
            setBusy(false); closeAll();
        } catch (err) {
            error.textContent = @json(__('messages.chrono_stop_processing_failed')); error.classList.remove('hidden');
            setBusy(false);
        } finally { status.classList.add('hidden'); }
    });
    cancel.addEventListener('click', closeAll); close.addEventListener('click', closeAll);
    modal.addEventListener('click', event => { if (event.target === modal) closeAll(); });
    document.addEventListener('click', event => { if (!translateWrap.contains(event.target)) closeLanguageMenu(); });
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape') {
            if (!languageMenu.hidden) { closeLanguageMenu(); translate.focus(); }
            else closeAll();
        }
    });
    window.ChronoStopModal = { open, close: closeAll };
})();
</script>
@endonce
