{{-- Shared Stop-Task comment modal for navbar-initiated stops.
     Same popup, fields, validation and backend as the Tasks-page stop flow
     (comment required, AI correct, translate, spellcheck gates, POST stop_comment
     to the existing stop endpoint). Suffixed ids so it coexists with the
     page-level copies without collisions. Vanilla JS: works on every page. --}}
<div id="chronoStopModal" class="chrono-modal">
    <div class="chrono-modal-card">
        <span id="chronoStopClose" class="chrono-close-button">&times;</span>
        <div class="flex items-center justify-center mb-3">
            <div class="bg-red-100 rounded-full p-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                </svg>
            </div>
        </div>
        <h2 class="text-lg font-semibold text-slate-800 mb-2">{{ __('messages.Stop Task') }}</h2>
        <p class="text-sm text-slate-500 mb-4">{{ __('messages.Please leave a comment describing the work done before stopping the task.') }}</p>
        <div class="text-left mb-4">
            <div class="flex justify-between items-center mb-1">
                <label for="chronoStopText" class="block text-sm font-medium text-slate-700">{{ __('messages.Comment') }} <span class="text-red-500">*</span></label>
                <div class="flex gap-3 items-center">
                    <button type="button" id="chronoStopAiBtn" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 flex items-center gap-1 transition-colors opacity-50 cursor-not-allowed" title="{{ __('messages.Correct with AI') }}" disabled>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                        </svg>
                        <span id="chronoStopAiBtnText">{{ __('messages.Correct with AI') }}</span>
                    </button>
                    <button type="button" id="chronoStopTranslateBtn" class="text-xs font-semibold text-brand-secondary hover:text-[#245f6d] flex items-center gap-1 transition-colors opacity-50 cursor-not-allowed" title="{{ __('messages.Translate to Dutch') }}" disabled>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5c-.006 3.125-1.026 6.042-2.751 8.498m-1.782-2.5A18.022 18.022 0 014.888 5h7.863z" />
                        </svg>
                        <span id="chronoStopTranslateBtnText">{{ __('messages.Translate to Dutch') }}</span>
                    </button>
                </div>
            </div>
            <textarea id="chronoStopText" rows="3" maxlength="500" placeholder="{{ __('messages.Describe what you worked on...') }}" spellcheck="true" class="border border-slate-300 rounded-lg px-3 py-2 w-full text-sm focus:outline-none focus:ring-2 focus:ring-red-400 resize-none transition-colors"></textarea>
            <p id="chronoStopError" class="text-xs text-red-500 mt-1 hidden">{{ __('messages.Comment is required to stop the task.') }}</p>
            <p id="chronoStopAiFeedback" class="text-xs mt-1 hidden"></p>
        </div>
        <div class="flex justify-end gap-2 pt-2">
            <button type="button" id="chronoStopCancelBtn" class="px-4 py-2 text-sm font-medium text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors">{{ __('messages.Cancel') }}</button>
            <button type="button" id="chronoStopConfirmBtn" class="px-4 py-2 text-sm font-medium text-white bg-red-500 rounded-lg hover:bg-red-600 transition-colors opacity-50 cursor-not-allowed" disabled>{{ __('messages.Confirm Stop') }}</button>
        </div>
    </div>
</div>

<div id="chronoSpellCheckModal" class="chrono-modal" style="z-index: 70;">
    <div class="chrono-modal-card" style="max-width: 420px;">
        <div class="flex items-center justify-center mb-3">
            <div class="bg-amber-100 rounded-full p-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>
        <h2 class="text-lg font-semibold text-slate-800 mb-2">{{ __('messages.Check Spelling?') }}</h2>
        <p class="text-sm text-slate-500 mb-5">{{ __('messages.Would you like to verify the spelling of your comment before submitting?') }}</p>
        <div class="flex flex-col gap-2">
            <button type="button" id="chronoSpellVerifyBtn" class="w-full px-4 py-2.5 text-sm font-medium text-white bg-slate-500 rounded-lg hover:bg-slate-600 transition-colors flex items-center justify-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                {{ __('messages.Yes, verify spelling') }}
            </button>
            <button type="button" id="chronoSpellSkipBtn" class="w-full px-4 py-2.5 text-sm font-medium text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors">
                {{ __('messages.No, submit as is') }}
            </button>
        </div>
    </div>
</div>

<div id="chronoTranslateCheckModal" class="chrono-modal" style="z-index: 70;">
    <div class="chrono-modal-card" style="max-width: 420px;">
        <div class="flex items-center justify-center mb-3">
            <div class="bg-blue-100 rounded-full p-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-brand-secondary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5c-.006 3.125-1.026 6.042-2.751 8.498m-1.782-2.5A18.022 18.022 0 014.888 5h7.863z" />
                </svg>
            </div>
        </div>
        <h2 class="text-lg font-semibold text-slate-800 mb-2">{{ __('messages.Translate to Dutch?') }}</h2>
        <p class="text-sm text-slate-500 mb-5">{{ __('messages.Would you like to translate your comment into Dutch before submitting?') }}</p>
        <div class="flex flex-col gap-2">
            <button type="button" id="chronoTranslateVerifyBtn" class="w-full px-4 py-2.5 text-sm font-medium text-white bg-brand-secondary rounded-lg hover:bg-[#245f6d] transition-colors flex items-center justify-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                {{ __('messages.Yes, translate to Dutch') }}
            </button>
            <button type="button" id="chronoTranslateSkipBtn" class="w-full px-4 py-2.5 text-sm font-medium text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors">
                {{ __('messages.No, submit as is') }}
            </button>
        </div>
    </div>
</div>

<script>
(function () {
    if (window.ChronoStopModal) return;
    var storedAction = null;
    var modal = document.getElementById('chronoStopModal');
    var text = document.getElementById('chronoStopText');
    var errBox = document.getElementById('chronoStopError');
    var confirmBtn = document.getElementById('chronoStopConfirmBtn');
    var cancelBtn = document.getElementById('chronoStopCancelBtn');
    var closeBtn = document.getElementById('chronoStopClose');
    var aiBtn = document.getElementById('chronoStopAiBtn');
    var aiBtnText = document.getElementById('chronoStopAiBtnText');
    var trBtn = document.getElementById('chronoStopTranslateBtn');
    var trBtnText = document.getElementById('chronoStopTranslateBtnText');
    var feedback = document.getElementById('chronoStopAiFeedback');
    var spellModal = document.getElementById('chronoSpellCheckModal');
    var spellVerifyBtn = document.getElementById('chronoSpellVerifyBtn');
    var spellSkipBtn = document.getElementById('chronoSpellSkipBtn');
    var trModal = document.getElementById('chronoTranslateCheckModal');
    var trVerifyBtn = document.getElementById('chronoTranslateVerifyBtn');
    var trSkipBtn = document.getElementById('chronoTranslateSkipBtn');
    var spelled = false, translated = false;
    if (!modal || !text || !confirmBtn) return;

    function setEnabled(btn, on) {
        btn.disabled = !on;
        btn.classList.toggle('opacity-50', !on);
        btn.classList.toggle('cursor-not-allowed', !on);
    }

    function open(actionUrl) {
        storedAction = actionUrl;
        try { if (typeof stopAutoRefresh === 'function') stopAutoRefresh(); } catch {}
        text.value = '';
        errBox.classList.add('hidden');
        feedback.classList.add('hidden');
        feedback.textContent = '';
        setEnabled(confirmBtn, false);
        setEnabled(aiBtn, false);
        setEnabled(trBtn, false);
        spelled = false; translated = false;
        modal.style.display = 'block';
        text.focus();
    }

    function closeAll() {
        modal.style.display = 'none';
        spellModal.style.display = 'none';
        trModal.style.display = 'none';
        storedAction = null;
        try { if (typeof startAutoRefresh === 'function') startAutoRefresh(); } catch {}
    }

    text.addEventListener('input', function () {
        var hasContent = this.value.trim().length > 0;
        setEnabled(confirmBtn, hasContent);
        setEnabled(aiBtn, hasContent);
        setEnabled(trBtn, hasContent);
        if (hasContent) errBox.classList.add('hidden');
    });

    text.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            if (!confirmBtn.disabled) confirmBtn.click();
        }
    });

    aiBtn.addEventListener('click', function () {
        var comment = text.value.trim();
        if (!comment) return;
        setEnabled(confirmBtn, false);
        aiBtn.disabled = true;
        aiBtn.classList.add('opacity-50', 'cursor-not-allowed');
        trBtn.disabled = true;
        trBtn.classList.add('opacity-50', 'cursor-not-allowed');
        text.disabled = true;
        var oldBtnText = aiBtnText.textContent;
        aiBtnText.textContent = "{{ __('messages.Correcting...') }}";
        feedback.classList.add('hidden');
        var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        fetch('/tasks/correct-comment', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ comment: comment })
        })
        .then(function (res) {
            if (!res.ok) {
                return res.json().then(function (err) { throw new Error(err.error || 'Failed to correct comment.'); });
            }
            return res.json();
        })
        .then(function (data) {
            if (data.corrected) {
                text.value = data.corrected;
                feedback.textContent = "{{ __('messages.Spelling and grammar corrected!') }}";
                feedback.className = "text-xs mt-1 text-emerald-600 font-medium";
                feedback.classList.remove('hidden');
                spelled = true;
            }
        })
        .catch(function (err) {
            console.error(err);
            feedback.textContent = "{{ __('messages.Failed to correct comment. Please check API configuration.') }}";
            feedback.className = "text-xs mt-1 text-red-500 font-medium";
            feedback.classList.remove('hidden');
        })
        .finally(function () {
            aiBtn.disabled = false;
            aiBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            trBtn.disabled = false;
            trBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            text.disabled = false;
            aiBtnText.textContent = oldBtnText;
            setEnabled(confirmBtn, text.value.trim().length > 0);
        });
    });

    trBtn.addEventListener('click', function () {
        var comment = text.value.trim();
        if (!comment) return;
        trBtn.disabled = true;
        trBtn.classList.add('opacity-50', 'cursor-not-allowed');
        aiBtn.disabled = true;
        aiBtn.classList.add('opacity-50', 'cursor-not-allowed');
        text.disabled = true;
        var oldBtnText = trBtnText.textContent;
        trBtnText.textContent = "{{ __('messages.Translating...') }}";
        feedback.classList.add('hidden');
        var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        fetch('/tasks/translate-comment', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ comment: comment })
        })
        .then(function (res) {
            if (!res.ok) {
                return res.json().then(function (err) { throw new Error(err.error || 'Failed to translate comment.'); });
            }
            return res.json();
        })
        .then(function (data) {
            if (data.translated) {
                text.value = data.translated;
                feedback.textContent = "{{ __('messages.Translated to Dutch!') }}";
                feedback.className = "text-xs mt-1 text-emerald-600 font-medium";
                feedback.classList.remove('hidden');
                translated = true;
            }
        })
        .catch(function (err) {
            console.error(err);
            feedback.textContent = "{{ __('messages.Failed to translate comment. Please check API configuration.') }}";
            feedback.className = "text-xs mt-1 text-red-500 font-medium";
            feedback.classList.remove('hidden');
        })
        .finally(function () {
            trBtn.disabled = false;
            trBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            aiBtn.disabled = false;
            aiBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            text.disabled = false;
            trBtnText.textContent = oldBtnText;
        });
    });

    function doSubmit() {
        var comment = text.value.trim();
        if (!storedAction) return;
        confirmBtn.disabled = true;
        confirmBtn.textContent = "{{ __('messages.Stopping...') }}";
        var formData = new FormData();
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
        formData.append('stop_comment', comment);
        fetch(storedAction, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function (response) {
            if (response.ok) {
                closeAll();
                if (typeof refreshEmployeeData === 'function') refreshEmployeeData();
                if (typeof startAutoRefresh === 'function') startAutoRefresh();
                try { window.dispatchEvent(new CustomEvent('chrono-revalidate', { detail: { force: true } })); } catch {}
            } else {
                throw new Error('Request failed');
            }
        })
        .catch(function (err) {
            console.error('Stop task error:', err);
            alert("{{ __('messages.An error occurred while stopping the task.') }}");
            confirmBtn.disabled = false;
            confirmBtn.textContent = "{{ __('messages.Confirm Stop') }}";
            if (typeof startAutoRefresh === 'function') startAutoRefresh();
        });
    }

    confirmBtn.addEventListener('click', function () {
        var comment = text.value.trim();
        if (!comment) {
            errBox.classList.remove('hidden');
            text.focus();
            return;
        }
        if (!storedAction) return;
        if (!spelled) { spellModal.style.display = 'block'; }
        else if (!translated) { trModal.style.display = 'block'; }
        else { doSubmit(); }
    });

    spellVerifyBtn.addEventListener('click', function () {
        spellModal.style.display = 'none';
        if (!aiBtn.disabled) aiBtn.click();
    });
    spellSkipBtn.addEventListener('click', function () {
        spellModal.style.display = 'none';
        spelled = true;
        if (!translated) { trModal.style.display = 'block'; }
        else { doSubmit(); }
    });
    spellModal.addEventListener('click', function (e) {
        if (e.target === spellModal) spellModal.style.display = 'none';
    });

    trVerifyBtn.addEventListener('click', function () {
        trModal.style.display = 'none';
        translated = true;
        if (!trBtn.disabled) trBtn.click();
    });
    trSkipBtn.addEventListener('click', function () {
        trModal.style.display = 'none';
        translated = true;
        doSubmit();
    });
    trModal.addEventListener('click', function (e) {
        if (e.target === trModal) trModal.style.display = 'none';
    });

    cancelBtn.addEventListener('click', closeAll);
    closeBtn.addEventListener('click', closeAll);
    modal.addEventListener('click', function (e) {
        if (e.target === modal) closeAll();
    });

    window.ChronoStopModal = { open: open, close: closeAll };
})();
</script>
