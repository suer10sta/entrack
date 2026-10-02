<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.Expense Note Parameters - DalyTrac') }}</title>
    @vite('resources/css/app.css')
    @livewireStyles
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

    <style>
        /* ── Form input base ── */
        .dt-input {
            display: block; width: 100%;
            border: 1px solid #e2e8f0; border-radius: 8px;
            background: #f8fafc; color: #1e293b;
            font-size: 13px; padding: 8px 12px;
            transition: border-color .15s, background .15s;
            outline: none;
        }
        .dt-input:focus {
            border-color: #2D7080;
            box-shadow: 0 0 0 3px rgba(45,112,128,.12);
            background: #fff;
        }
        .dt-input.is-editing {
            background: #fff;
            border-color: #2D7080;
            box-shadow: 0 0 0 3px rgba(45,112,128,.12);
            cursor: text;
        }
        .dt-input.is-saved   { border-color: #22c55e; }
        .dt-input.is-error   { border-color: #ef4444; }
        .dt-input[readonly]  { cursor: default; }

        .dt-label {
            display: block; font-size: 11.5px; font-weight: 600;
            color: #475569; margin-bottom: 5px;
        }
    </style>
</head>

<body class="dt-app-shell bg-[#f8fafc] text-slate-900 antialiased flex h-screen overflow-hidden">

    {{-- Sidebar --}}
    @if(Auth::user()->hasRole('Admin'))
        <x-dashboard.sidebar />
    @else
        <x-employees.sidebar />
    @endif

    <div class="flex flex-col flex-1 overflow-y-auto">
        <x-dashboard.navbar />

        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-[#f8fafc] px-3 py-4 sm:px-5 sm:py-5 lg:px-16 lg:py-7">

            {{-- ── Page header ── --}}
            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-2 mb-5">
                <div class="min-w-0">
                    <p class="text-[10px] font-semibold uppercase tracking-[.08em] text-slate-400 mb-0.5">{{ __('messages.Expense Note History') }}</p>
                    <h1 class="text-[18px] sm:text-[20px] lg:text-[22px] font-bold tracking-tight text-[#0B2638] leading-tight">
                        {{ __('messages.Expense Note Parameters') }}
                    </h1>
                    <p class="text-[12px] text-slate-500 mt-0.5 hidden sm:block">{{ __('messages.Configure expense note settings') }}</p>
                </div>
            </div>

            {{-- ── Save feedback banner ── --}}
            <div id="saveBanner" class="hidden mb-4 flex items-center gap-3 px-4 py-3 rounded-xl border relative text-sm font-medium"></div>

            {{-- ── Settings card ── --}}
            <div class="dt-card overflow-hidden mb-5">

                {{-- Card header --}}
                <div class="px-5 py-4 border-b border-slate-100 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-[#2D7080]/10 flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-[#2D7080]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-[13px] font-semibold text-[#0B2638]">{{ __('messages.Expense Note Information') }}</p>
                        <p class="text-[11.5px] text-slate-400">{{ __('messages.Mandatory information expense') }}</p>
                    </div>
                </div>

                <div class="p-5 sm:p-6">

                    {{-- Hint --}}
                    <div class="flex items-start gap-2.5 bg-[#2D7080]/[0.06] border border-[#2D7080]/20 rounded-xl px-4 py-3 mb-5">
                        <svg class="w-4 h-4 text-[#2D7080] mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-[12px] text-[#2D7080]">{{ __('messages.Prefix help expense') }}</p>
                    </div>

                    {{-- Prefix field --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="dt-label">{{ __('messages.Prefix') }}</label>
                            <input
                                class="editable dt-input"
                                type="text"
                                name="notedefraisPrefix"
                                value="{{ $entreprise->notedefraisPrefix }}"
                                readonly />
                            <p class="text-[11px] text-slate-400 mt-1">{{ __('messages.Prefix help expense') }}</p>
                        </div>
                        {{-- Placeholder for future fields --}}
                        <div></div>
                    </div>

                </div>
            </div>

            {{-- ── Action buttons ── --}}
            <div class="flex flex-col sm:flex-row sm:justify-end items-stretch sm:items-center gap-2">
                @php
                    $backRoute = '#';
                    if(Auth::user()->hasRole('Responsable de projet')) $backRoute = route('Responsable_de_projet.index');
                    elseif(Auth::user()->hasRole('Admin'))              $backRoute = route('admin.index');
                    elseif(Auth::user()->hasRole('Employee'))           $backRoute = route('employee.index');
                    elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire')) $backRoute = route('Responsable_gestionnaire.index');
                @endphp

                <a href="{{ url()->previous() ?: $backRoute }}" class="dt-btn dt-btn-ghost justify-center">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    {{ __('messages.Back') }}
                </a>

                <button type="button" id="saveChangesBtn" class="dt-btn dt-btn-primary justify-center">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span id="saveBtnLabel">{{ __('messages.Save Changes Expense') }}</span>
                </button>
            </div>

        </main>
    </div>

    @livewireScripts

<script>
$(document).ready(function () {

    // ── Double-click to edit ──
    $('.editable').dblclick(function () {
        $(this).prop('readonly', false)
               .removeClass('is-saved is-error')
               .addClass('is-editing')
               .focus();
    });

    // ── Store value on focus ──
    $('.editable').focus(function () {
        $(this).data('originalValue', $(this).val());
    });

    // ── Blur: make read-only again ──
    $('.editable').blur(function () {
        $(this).prop('readonly', true).removeClass('is-editing');
    });

    // ── Save button ──
    $('#saveChangesBtn').click(function () {
        const btn   = $(this);
        const label = $('#saveBtnLabel');
        const banner= $('#saveBanner');

        let updatedUrl = '';
        @if(Auth::user()->hasRole('Responsable de projet'))               updatedUrl = '/Responsable_de_projet/Enterprise_infos/updateInfos'; @endif
        @if(Auth::user()->hasRole('Admin'))                               updatedUrl = '/admin/Enterprise_infos/updateInfos'; @endif
        @if(Auth::user()->hasRole('Employee'))                            updatedUrl = '/employee/Enterprise_infos/updateInfos'; @endif
        @if(Auth::user()->hasRole('Responsable de projet et gestionnaire')) updatedUrl = '/Responsable_gestionnaire/Enterprise_infos/updateInfos'; @endif

        if (!updatedUrl) {
            alert(@json(__('messages.Cannot save. No permissions expense')));
            return;
        }

        btn.prop('disabled', true);
        label.text(@json(__('messages.Saving Expense...')));
        banner.addClass('hidden');

        let hasErrors = false;
        let hasChanges = false;

        $('.editable').each(function () {
            const input = $(this);
            const newVal = input.val();
            const oldVal = input.data('originalValue');

            input.prop('readonly', true).removeClass('is-editing');
            if (newVal === oldVal || oldVal === undefined) return;

            hasChanges = true;
            const col = input.attr('name');

            $.ajax({
                url: updatedUrl, type: 'post', async: false,
                data: {
                    content: newVal,
                    id: '{{ Auth::user()->entreprise_id }}',
                    column: col,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function () {
                    input.data('originalValue', newVal)
                         .addClass('is-saved')
                         .removeClass('is-error');
                    setTimeout(() => input.removeClass('is-saved'), 2500);
                },
                error: function () {
                    input.val(oldVal).addClass('is-error').removeClass('is-saved');
                    hasErrors = true;
                }
            });
        });

        btn.prop('disabled', false);

        if (!hasChanges) {
            // Nothing changed
            banner.html('<svg class="w-4 h-4 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><span>' + @json(__('messages.No changes to save!')) + '</span>')
                         .attr('class', 'mb-4 flex items-center gap-3 px-4 py-3 rounded-xl border relative text-sm font-medium bg-slate-50 border-slate-200 text-slate-600');
            label.text(@json(__('messages.Save Changes Expense')));
        } else if (hasErrors) {
            banner.html('<svg class="w-4 h-4 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg><span>' + @json(__('messages.Some changes failed to save. Please check fields marked in red.')) + '</span>')
                         .attr('class', 'mb-4 flex items-center gap-3 px-4 py-3 rounded-xl border relative text-sm font-medium bg-red-50 border-red-200 text-red-800');
            label.text(@json(__('messages.Error Expense!')));
            setTimeout(() => { label.text(@json(__('messages.Save Changes Expense'))); }, 3000);
        } else {
            banner.html('<svg class="w-4 h-4 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg><span>' + @json(__('messages.Saved Expense!')) + '</span>')
                         .attr('class', 'mb-4 flex items-center gap-3 px-4 py-3 rounded-xl border relative text-sm font-medium bg-emerald-50 border-emerald-200 text-emerald-800');
            label.text(@json(__('messages.Saved Expense!')));
            setTimeout(() => { label.text(@json(__('messages.Save Changes Expense'))); banner.addClass('hidden'); }, 3000);
        }

        banner.removeClass('hidden');
    });
});
</script>
</body>
</html>
