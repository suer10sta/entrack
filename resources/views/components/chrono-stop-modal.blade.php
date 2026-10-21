@once
{{-- One confirmation; description processing completes before the existing stop request. --}}
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
        <div class="flex justify-end gap-2 pt-2">
            <button type="button" id="chronoStopCancelBtn" class="dt-btn dt-btn-ghost">{{ __('messages.Cancel') }}</button>
            <button type="button" id="chronoStopConfirmBtn" class="dt-btn dt-btn-primary disabled:opacity-50" disabled>{{ __('messages.Confirm Stop') }}</button>
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
    const cancel = document.getElementById('chronoStopCancelBtn');
    const close = document.getElementById('chronoStopClose');
    let action = null, submitHandler = null, busy = false;
    function open(url, handler = null) {
        if (busy) return;
        action = url; submitHandler = handler; text.value = '';
        error.classList.add('hidden'); status.classList.add('hidden'); confirm.disabled = true;
        if (typeof stopAutoRefresh === 'function') stopAutoRefresh();
        modal.style.display = 'block'; text.focus();
    }
    function closeAll() {
        if (busy) return;
        modal.style.display = 'none'; action = null; submitHandler = null;
        if (typeof startAutoRefresh === 'function') startAutoRefresh();
    }
    function setBusy(value) {
        busy = value; text.disabled = value; cancel.disabled = value; close.disabled = value;
        confirm.disabled = value || !text.value.trim();
        modal.setAttribute('aria-busy', String(value));
    }
    text.addEventListener('input', () => { confirm.disabled = busy || !text.value.trim(); error.classList.add('hidden'); });
    text.addEventListener('keydown', event => {
        if (event.key === 'Enter' && !event.shiftKey) { event.preventDefault(); if (!confirm.disabled) confirm.click(); }
    });
    confirm.addEventListener('click', async () => {
        if (busy || !action || !text.value.trim()) return;
        setBusy(true); error.classList.add('hidden');
        status.textContent = @json(__('messages.chrono_stop_processing')); status.classList.remove('hidden');
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        try {
            const response = await fetch('/tasks/prepare-stop-comment', {
                method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token },
                body: JSON.stringify({ comment: text.value.trim() })
            });
            if (!response.ok) throw new Error('Description processing failed');
            const result = await response.json();
            if (typeof result.corrected !== 'string' || !result.corrected.trim() || result.corrected.length > 500) throw new Error(@js(__('messages.Invalid processed description')));
            text.value = result.corrected.trim();
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
    document.addEventListener('keydown', event => { if (event.key === 'Escape') closeAll(); });
    window.ChronoStopModal = { open, close: closeAll };
})();
</script>
@endonce
