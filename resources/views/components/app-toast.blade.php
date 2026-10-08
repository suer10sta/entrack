{{-- Global toast notifications (success / error).
     Renders server flash messages (session message/error/alertmessage) and
     exposes window.AppToast.success(msg) / .error(msg) for AJAX flows. --}}
<div id="app-toast-stack" class="app-toast-stack" aria-live="polite" aria-atomic="false"></div>
<style>
    .app-toast-stack {
        position: fixed; right: 16px; bottom: 16px; z-index: 100;
        display: flex; flex-direction: column; gap: 8px;
        width: min(360px, calc(100vw - 24px)); pointer-events: none;
    }
    .app-toast {
        pointer-events: auto; display: flex; align-items: flex-start; gap: 10px;
        background: #fff; border: 1px solid #e2e8f0; border-left-width: 4px;
        border-radius: 12px; padding: 10px 12px;
        box-shadow: 0 12px 24px -8px rgba(11,38,56,.25);
        font-size: 13px; color: #0B2638;
        animation: app-toast-in .25s ease-out;
    }
    .app-toast.success { border-left-color: #16a34a; }
    .app-toast.error { border-left-color: #dc2626; }
    .app-toast .app-toast-icon { flex-shrink: 0; margin-top: 1px; }
    .app-toast.success .app-toast-icon { color: #16a34a; }
    .app-toast.error .app-toast-icon { color: #dc2626; }
    .app-toast .app-toast-msg { flex: 1; min-width: 0; font-weight: 500; line-height: 1.4; overflow-wrap: break-word; }
    .app-toast .app-toast-close {
        flex-shrink: 0; color: #94a3b8; font-size: 16px; line-height: 1;
        padding: 0 2px; cursor: pointer; background: none; border: none;
    }
    .app-toast .app-toast-close:hover { color: #475569; }
    .app-toast.hide { opacity: 0; transform: translateY(6px); transition: opacity .2s, transform .2s; }
    @keyframes app-toast-in { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
    @media (max-width: 640px) {
        .app-toast-stack { left: 12px; right: 12px; bottom: 12px; width: auto; }
    }
</style>
<script>
(function () {
    if (window.AppToast) return;
    var ICONS = {
        success: '<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>',
        error: '<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
    };
    function show(type, message, timeout) {
        if (!message) return;
        var stack = document.getElementById('app-toast-stack');
        if (!stack) return;
        while (stack.children.length >= 3) stack.removeChild(stack.firstChild);
        var el = document.createElement('div');
        el.className = 'app-toast ' + (type === 'error' ? 'error' : 'success');
        el.setAttribute('role', type === 'error' ? 'alert' : 'status');
        var icon = document.createElement('span');
        icon.className = 'app-toast-icon';
        icon.innerHTML = ICONS[type === 'error' ? 'error' : 'success'];
        var msg = document.createElement('div');
        msg.className = 'app-toast-msg';
        msg.textContent = message;
        var close = document.createElement('button');
        close.className = 'app-toast-close';
        close.setAttribute('type', 'button');
        close.setAttribute('aria-label', '×');
        close.textContent = '×';
        var timer = setTimeout(dismiss, timeout || 4500);
        function dismiss() {
            clearTimeout(timer);
            el.classList.add('hide');
            setTimeout(function () { if (el.parentNode) el.parentNode.removeChild(el); }, 220);
        }
        close.addEventListener('click', dismiss);
        el.appendChild(icon);
        el.appendChild(msg);
        el.appendChild(close);
        stack.appendChild(el);
    }
    window.AppToast = {
        success: function (message, timeout) { show('success', message, timeout); },
        error: function (message, timeout) { show('error', message, timeout); }
    };
    @if(session('message'))
        show('success', @json(session('message')));
    @endif
    @if(session('error'))
        show('error', @json(session('error')));
    @endif
    @if(session('alertmessage'))
        show('error', @json(session('alertmessage')));
    @endif
})();
</script>
