<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.Project Details - DalyTrac') }}</title>
    @vite('resources/css/app.css')
    @livewireStyles
    <style>
        /* ── Table base ── */
        #taskTable th, #invoicesTable th, #devisTable th, .project-files-table th {
            font-size: 11px; font-weight: 700; letter-spacing: .07em;
            text-transform: uppercase; color: #64748b;
            padding: 10px 12px; white-space: nowrap;
            background: #f8fafc; border-bottom: 1px solid #e2e8f0;
        }
        #taskTable td, #invoicesTable td, #devisTable td, .project-files-table td {
            padding: 9px 12px; font-size: 13px;
            vertical-align: middle; border-bottom: 1px solid #f1f5f9;
        }
        #taskTable th:nth-child(1), #taskTable td:nth-child(1) { min-width: 140px; }
        #taskTable th:nth-child(2), #taskTable td:nth-child(2) { min-width: 160px; }
        #taskTable th:nth-child(3), #taskTable td:nth-child(3) { min-width: 120px; }
        #taskTable th:nth-child(4), #taskTable td:nth-child(4) { min-width: 90px; }
        #taskTable th:nth-child(5), #taskTable td:nth-child(5) { min-width: 90px; }
        #taskTable th:nth-child(6), #taskTable td:nth-child(6) { min-width: 70px; }
        #taskTable th:nth-child(7), #taskTable td:nth-child(7) { min-width: 90px; }
        #taskTable th:nth-child(8), #taskTable td:nth-child(8) { min-width: 110px; }
        #taskTable th:nth-child(9), #taskTable td:nth-child(9) { min-width: 100px; }
        #taskTable th:nth-child(10), #taskTable td:nth-child(10) { min-width: 100px; }
        #taskTable th:nth-child(11), #taskTable td:nth-child(11) { min-width: 130px; }
        #taskTable th:nth-child(12), #taskTable td:nth-child(12) { min-width: 220px; }
        #taskTable tbody tr:last-child td, #invoicesTable tbody tr:last-child td,
        #devisTable tbody tr:last-child td, .project-files-table tbody tr:last-child td { border-bottom: none; }
        #taskTable tbody tr:hover td, #invoicesTable tbody tr:hover td,
        #devisTable tbody tr:hover td, .project-files-table tbody tr:hover td { background: #f8fafc; }

        /* ── Row highlights ── */
        .highlighted-task td { background-color: #eff6ff !important; } /* blue-50 */
        .regie-task td { background-color: #fefce8 !important; } /* yellow-50 */

        /* ── Clickable Date Styles ── */
        td[data-field="start_date"],
        td[data-field="end_date"] {
            color: #2D7080 !important; /* brand-secondary */
            cursor: pointer !important;
            font-weight: 500;
        }
        td[data-field="start_date"]:hover,
        td[data-field="end_date"]:hover {
            color: #0B2638 !important; /* brand-primary */
            text-decoration: underline;
        }

        /* ── In-place editable fields ── */
        .editable { cursor: pointer; }
        .editable:hover { color: #2D7080; }

        /* ── Type badge ── */
        .inv-type-badge {
            display: inline-flex; align-items: center; gap: 4px;
            padding: 2px 8px; border-radius: 99px;
            font-size: 11px; font-weight: 600; white-space: nowrap;
        }

        /* ── Action buttons ── */
        .act-btn {
            display: inline-flex; align-items: center; justify-content: center;
            gap: 4px; height: 26px; padding: 0 10px; border-radius: 6px;
            flex-shrink: 0; cursor: pointer; font-size: 11.5px; font-weight: 600;
            white-space: nowrap; transition: background .15s, border-color .15s;
            outline: none; line-height: 1; text-decoration: none;
        }
        .act-btn-icon { width: 12px; height: 12px; flex-shrink: 0; }
        .act-btn-chevron { width: 9px; height: 9px; flex-shrink: 0; }

        .act-btn-paid       { background:#f0fdf4; color:#15803d; border:1px solid #86efac; }
        .act-btn-paid:hover { background:#dcfce7; border-color:#4ade80; }

        .act-btn-unpaid       { background:#fff1f2; color:#b91c1c; border:1px solid #fca5a5; }
        .act-btn-unpaid:hover { background:#fee2e2; border-color:#f87171; }

        .act-btn-send       { background:#f0fafb; color:#2D7080; border:1px solid #99d6de; }
        .act-btn-send:hover { background:#e0f2f1; border-color:#2D7080; }

        .act-btn-remind       { background:#fffbeb; color:#92400e; border:1px solid #fcd34d; }
        .act-btn-remind:hover { background:#fef3c7; border-color:#f59e0b; }

        .act-btn-credit       { background:#f0f4f8; color:#0B2638; border:1px solid #b0bec5; }
        .act-btn-credit:hover { background:#e2eaf0; border-color:#78909c; }

        .act-btn-edit       { background:#f0fafb; color:#2D7080; border:1px solid #99d6de; }
        .act-btn-edit:hover { background:#e0f2f1; border-color:#2D7080; }

        .act-btn-delete       { background:#dc2626; color:#fff; border:1px solid #dc2626; }
        .act-btn-delete:hover { background:#b91c1c; border-color:#b91c1c; }

        .act-btn-neutral       { background:#f1f5f9; color:#475569; border:1px solid #cbd5e1; }
        .act-btn-neutral:hover { background:#e2e8f0; border-color:#94a3b8; }

        /* ── Filter / search select ── */
        .dt-filter-select {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2394a3b8' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E");
            background-repeat: no-repeat; background-position: right 10px center; background-size: 14px;
            padding-right: 32px;
        }
        select.form-select-custom {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 0.5rem center;
            background-repeat: no-repeat;
            background-size: 1.5em 1.5em;
            padding-right: 2.5rem;
        }
        select:invalid { color: #9ca3af; }
        option { color: #111827; }

        /* ── act-row ── */
        .act-row {
            display: flex; flex-wrap: wrap;
            align-items: center; gap: 3px;
            justify-content: flex-start;
        }

        /* ── Section toggle (collapsible cards) ── */
        .section-toggle {
            display: inline-flex; align-items: center; gap: 6px;
            font-size: 13px; font-weight: 600; color: #94a3b8;
            cursor: pointer; white-space: nowrap; flex-shrink: 0;
        }
        .section-toggle:hover { color: #0B2638; }
        .section-chevron { width: 16px; height: 16px; transition: transform 180ms ease; }
        .section-chevron.rotate-180 { transform: rotate(180deg); }

        /* ── Mobile card layout (≤ 1024px) ── */
        @media (max-width: 1024px) {
            #taskTable thead, #invoicesTable thead, #devisTable thead, .project-files-table thead { display: none; }
            #taskTable tbody, #invoicesTable tbody, #devisTable tbody, .project-files-table tbody {
                display: flex; flex-direction: column; gap: 12px;
            }
            #taskTable tbody tr, #invoicesTable tbody tr, #devisTable tbody tr, .project-files-table tbody tr {
                display: grid; grid-template-columns: 1fr 1fr;
                gap: 8px; padding: 12px;
                border: 1px solid rgba(148,163,184,.45); border-radius: 14px;
                background: linear-gradient(160deg,#fff 0%,#f8fafc 100%);
                box-shadow: 0 2px 16px -4px rgba(11,38,56,.10);
            }
            #taskTable tbody tr > td, #invoicesTable tbody tr > td,
            #devisTable tbody tr > td, .project-files-table tbody tr > td {
                display: block; padding: 8px 10px !important;
                border: 1px solid rgba(226,232,240,.85);
                border-radius: 10px; background: rgba(255,255,255,.82);
                white-space: normal !important; word-break: break-word;
            }
            #taskTable tbody tr > td[data-span="2"], #invoicesTable tbody tr > td[data-span="2"],
            #devisTable tbody tr > td[data-span="2"], .project-files-table tbody tr > td[data-span="2"] { grid-column: span 2; }
            #taskTable tbody tr > td[data-label]::before, #invoicesTable tbody tr > td[data-label]::before,
            #devisTable tbody tr > td[data-label]::before, .project-files-table tbody tr > td[data-label]::before {
                content: attr(data-label);
                display: block; font-size: 10px; font-weight: 700;
                letter-spacing: .06em; text-transform: uppercase;
                color: #94a3b8; margin-bottom: 4px;
            }
            #taskTable tbody tr > td[data-label] .task-actions,
            #invoicesTable tbody tr > td[data-label] .invoice-actions,
            #devisTable tbody tr > td[data-label] .devis-actions { justify-content: flex-start !important; flex-wrap: wrap; }
        }

        [x-cloak] { display: none !important; }

        /* ── Modal base ── */
        .dt-modal { display: none; position: fixed; z-index: 50; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.5); backdrop-filter: blur(4px); }
        .dt-modal-content { animation: slideDown 0.3s ease-out; }
        @keyframes slideDown { from { transform: translateY(-50px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        .dt-modal-content-custom {
            background-color: #fff;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #e2e8f0;
            width: 80%;
            max-width: 600px;
            text-align: center;
            position: relative;
            border-radius: 1rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        .dt-close-button {
            color: #94a3b8;
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .dt-close-button:hover { color: #475569; }
    </style>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script>
        // Register Alpine.js components BEFORE Alpine loads
        document.addEventListener('alpine:init', () => {
            Alpine.data('editDateModal', () => ({
                show: false,
                taskId: null,
                field: '',
                date: '',
                time: '',
                open(detail) {
                    this.taskId = detail.taskId;
                    this.field = detail.field;
                    if (detail.datetime) {
                        this.date = detail.datetime.substring(0, 10);
                        this.time = detail.datetime.substring(11, 16);
                    } else {
                        const now = new Date();
                        now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
                        const isoNow = now.toISOString().substring(0, 16);
                        this.date = isoNow.substring(0, 10);
                        this.time = isoNow.substring(11, 16);
                    }
                    this.show = true;
                },
                save() {
                    const newValue = `${this.date} ${this.time}`;
                    let url = '';
                    @if(Auth::user()->hasRole('Admin')) url = `/admin/tasks/update-${this.field}/${this.taskId}`; @endif
                    @if(Auth::user()->hasRole('Responsable de projet')) url = `/Responsable_de_projet/tasks/update-${this.field}/${this.taskId}`; @endif
                    @if(Auth::user()->hasRole('Employee')) url = `/employee/tasks/update-${this.field}/${this.taskId}`; @endif
                    @if(Auth::user()->hasRole('Responsable de projet et gestionnaire')) url = `/Responsable_gestionnaire/tasks/update-${this.field}/${this.taskId}`; @endif

                    $.ajax({
                        url: url, type: 'POST',
                        data: { [this.field]: newValue, _token: '{{ csrf_token() }}' },
                        success: (response) => {
                            if (response.status === 'success') location.reload();
                            else alert(response.message || @js(__('messages.Failed to update.')));
                        },
                        error: (xhr) => {
                            let msg = @js(__('messages.Failed to update.'));
                            if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                            alert(msg);
                        }
                    });
                    this.show = false;
                }
            }));

            Alpine.data('moveDurationModal', () => ({
                show: false,
                originalTaskId: null,
                projectId: '',
                taskId: '',
                employeeId: '',
                startDate: '',
                endDate: '',
                duration: '',
                durationMinutes: 0,
                open(detail) {
                    this.originalTaskId = detail.taskId;
                    this.projectId = detail.projectId;
                    this.employeeId = detail.employeeId;
                    this.startDate = detail.startDate;
                    this.endDate = detail.endDate;
                    this.durationMinutes = detail.duration;
                    this.duration = this.minutesToHoursMinutes(detail.duration);
                    this.loadTasks(detail.taskId);
                    this.show = true;
                },
                loadTasks(selectedTaskId = null) {
                    let url = '';
                    @if(Auth::user()->hasRole('Admin')) url = `/admin/tasks/get-project-tasks/${this.projectId}`; @endif
                    @if(Auth::user()->hasRole('Responsable de projet')) url = `/Responsable_de_projet/tasks/get-project-tasks/${this.projectId}`; @endif
                    @if(Auth::user()->hasRole('Employee')) url = `/employee/tasks/get-project-tasks/${this.projectId}`; @endif
                    @if(Auth::user()->hasRole('Responsable de projet et gestionnaire')) url = `/Responsable_gestionnaire/tasks/get-project-tasks/${this.projectId}`; @endif

                    fetch(url)
                        .then(response => response.json())
                        .then(tasks => {
                            const taskSelect = document.getElementById('taskSelect');
                            taskSelect.innerHTML = '';
                            tasks.forEach(task => {
                                const option = new Option(task.name, task.id);
                                taskSelect.add(option);
                            });
                            this.taskId = selectedTaskId || (tasks.length > 0 ? tasks[0].id : '');
                        });
                },
                calculateDuration() {
                    if (!this.startDate || !this.endDate) return;
                    const start = new Date(this.startDate);
                    const end = new Date(this.endDate);
                    const diffMs = end - start;
                    if (diffMs < 0) {
                        this.duration = '00h:00m'; this.durationMinutes = 0; return;
                    }
                    const minutes = Math.floor(diffMs / 60000);
                    this.durationMinutes = minutes;
                    this.duration = this.minutesToHoursMinutes(minutes);
                },
                validateDurationFormat() {
                    if (/^(\d+)h:(\d{2})m$/.test(this.duration)) {
                        this.durationMinutes = this.hoursMinutesToMinutes(this.duration);
                    }
                },
                saveMove() {
                    if (!this.duration.match(/^(\d+)h:(\d{2})m$/)) {
                        alert(@js(__('messages.Please enter duration in format (e.g., 01h:30m or 10h:00m)')));
                        return;
                    }
                    const formData = {
                        original_task_id: this.originalTaskId, new_task_id: this.taskId,
                        employee_id: this.employeeId, start_date: this.startDate,
                        end_date: this.endDate, duration: this.durationMinutes,
                        _token: '{{ csrf_token() }}'
                    };
                    let url = '';
                    @if(Auth::user()->hasRole('Admin')) url = '/admin/tasks/move-duration'; @endif
                    @if(Auth::user()->hasRole('Responsable de projet')) url = '/Responsable_de_projet/tasks/move-duration'; @endif
                    @if(Auth::user()->hasRole('Employee')) url = '/employee/tasks/move-duration'; @endif
                    @if(Auth::user()->hasRole('Responsable de projet et gestionnaire')) url = '/Responsable_gestionnaire/tasks/move-duration'; @endif

                    $.ajax({
                        url: url, type: 'POST', data: formData,
                        success: (response) => {
                            if (response.status === 'success') location.reload();
                            else alert(@js(__('messages.Operation failed:')) + ' ' + (response.message || @js(__('messages.Unknown error'))));
                        },
                        error: (xhr) => alert(@js(__('messages.Error:')) + ' ' + xhr.responseText)
                    });
                    this.show = false;
                },
                minutesToHoursMinutes(minutes) {
                    const hours = Math.floor(minutes / 60);
                    const remainingMinutes = minutes % 60;
                    return `${hours.toString().padStart(2, '0')}h:${remainingMinutes.toString().padStart(2, '0')}m`;
                },
                hoursMinutesToMinutes(durationString) {
                    const matches = durationString.match(/^(\d+)h:(\d{2})m$/);
                    if (!matches) return 0;
                    return (parseInt(matches[1]) * 60) + parseInt(matches[2]);
                }
            }));

            Alpine.data('manualTimerModal', () => ({
                show: false,
                taskId: '',
                employeeId: '',
                startDate: '',
                endDate: '',
                open(detail) {
                    this.taskId = detail.taskId || '';
                    this.employeeId = detail.employeeId || '';
                    this.startDate = '';
                    this.endDate = '';
                    this.show = true;
                },
                save() {
                    if (!this.taskId || !this.employeeId || !this.startDate || !this.endDate) {
                        alert(@js(__('messages.Please select an employee and both start and end dates.')));
                        return;
                    }

                    let url = '';
                    @if(Auth::user()->hasRole('Admin')) url = '/admin/tasks/manual-timer'; @endif
                    @if(Auth::user()->hasRole('Responsable de projet')) url = '/Responsable_de_projet/tasks/manual-timer'; @endif
                    @if(Auth::user()->hasRole('Employee')) url = '/employee/tasks/manual-timer'; @endif
                    @if(Auth::user()->hasRole('Responsable de projet et gestionnaire')) url = '/Responsable_gestionnaire/tasks/manual-timer'; @endif

                    $.ajax({
                        url: url,
                        type: 'POST',
                        data: {
                            task_id: this.taskId,
                            employee_id: this.employeeId,
                            start_date: this.startDate,
                            end_date: this.endDate,
                            _token: '{{ csrf_token() }}'
                        },
                        success: (response) => {
                            if (response.status === 'success') {
                                location.reload();
                            } else {
                                alert(response.message || @js(__('messages.Failed to create timer.')));
                            }
                        },
                        error: (xhr) => {
                            let msg = @js(__('messages.Failed to create timer.'));
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                msg = xhr.responseJSON.message;
                            }
                            alert(msg);
                        }
                    });

                    this.show = false;
                }
            }));

            Alpine.data('exportReportModal', () => ({
                show: false,
                isCustomerReport: false,
                open(isCustomer) {
                    this.isCustomerReport = isCustomer;
                    this.show = true;
                },
                download() {
                    alert(@js(__('messages.Download logic not implemented in Alpine.js modal yet.')));
                    this.show = false;
                }
            }));
            Alpine.data('sendInvoiceModal', () => ({
                show: false,
                invoiceId: null,
                form: null,
                open(invoiceId) {
                    this.invoiceId = invoiceId;
                    this.show = true;
                    this.form = document.getElementById('sendInvoiceForm');
                    if (this.form) {
                        this.updateFormAction();
                        this.fetchRecipients();
                    }
                },
                updateFormAction() {
                    let actionUrl = '';
                    @if(Auth::user()->hasRole('Admin')) actionUrl = '{{ route("admin.invoice.sendInvoice", ":id") }}'; @endif
                    @if(Auth::user()->hasRole('Responsable de projet')) actionUrl = '{{ route("Responsable_de_projet.invoice.sendInvoice", ":id") }}'; @endif
                    @if(Auth::user()->hasRole('Employee')) actionUrl = '{{ route("employee.invoice.sendInvoice", ":id") }}'; @endif
                    @if(Auth::user()->hasRole('Responsable de projet et gestionnaire')) actionUrl = '{{ route("Responsable_gestionnaire.invoice.sendInvoice", ":id") }}'; @endif
                    if (this.form) {
                        this.form.action = actionUrl.replace(':id', this.invoiceId);
                    }
                },
                fetchRecipients() {
                    let fetchUrl = '';
                    @if(Auth::user()->hasRole('Admin')) fetchUrl = `/admin/invoice/${this.invoiceId}/recipients`; @endif
                    @if(Auth::user()->hasRole('Responsable de projet')) fetchUrl = `/Responsable_de_projet/invoice/${this.invoiceId}/recipients`; @endif
                    @if(Auth::user()->hasRole('Employee')) fetchUrl = `/employee/invoice/${this.invoiceId}/recipients`; @endif
                    @if(Auth::user()->hasRole('Responsable de projet et gestionnaire')) fetchUrl = `/Responsable_gestionnaire/invoice/${this.invoiceId}/recipients`; @endif

                    fetch(fetchUrl)
                        .then(response => response.json())
                        .then(data => {
                            const recipientsList = document.getElementById('recipientsList');
                            if (!recipientsList) return;
                            recipientsList.innerHTML = '';
                            if (data.message && (!data.recipients || data.recipients.length === 0)) {
                                recipientsList.innerHTML = '<p class="text-sm text-red-600">' + data.message + '</p>';
                            } else if (data.recipients && data.recipients.length > 0) {
                                recipientsList.innerHTML += `
                                    <div class="flex items-center">
                                        <input class="h-4 w-4 rounded border-slate-300 text-[#2D7080] focus:ring-[#2D7080]/30" type="checkbox" id="selectAllRecipients">
                                        <label class="ml-2 text-sm font-bold text-slate-900" for="selectAllRecipients">{{ __('messages.Select All') }}</label>
                                    </div>`;
                                data.recipients.forEach(recipient => {
                                    recipientsList.innerHTML += `
                                        <div class="flex items-center">
                                            <input class="h-4 w-4 rounded border-slate-300 text-[#2D7080] focus:ring-[#2D7080]/30 recipient-checkbox" type="checkbox"
                                                   name="selected_recipients[]" value="${recipient.email}" id="recipient-${recipient.email}"
                                                   ${recipient.is_comptable === 1 ? 'checked' : ''}>
                                            <label class="ml-2 text-sm text-slate-700" for="recipient-${recipient.email}">
                                                <strong>${recipient.name}</strong> (${recipient.email})
                                            </label>
                                        </div>`;
                                });
                                const selectAll = document.getElementById('selectAllRecipients');
                                if (selectAll) {
                                    selectAll.addEventListener('change', function() {
                                        document.querySelectorAll('.recipient-checkbox').forEach(checkbox => {
                                            checkbox.checked = this.checked;
                                        });
                                    });
                                }
                            } else {
                                recipientsList.innerHTML = '<p class="text-sm text-slate-500">{{ __('messages.No recipients found') }}</p>';
                            }
                        });
                },
                submitForm() {
                    if (!this.form) return;
                    const selectedRecipients = Array.from(document.querySelectorAll('input[name="selected_recipients[]"]:checked')).map(cb => cb.value);
                    if (selectedRecipients.length === 0) {
                        alert(@js(__('messages.Please select at least one recipient'))); return;
                    }
                    const formData = new FormData(this.form);
                    formData.delete('selected_recipients[]');
                    formData.append('selected_recipients', JSON.stringify(selectedRecipients));

                    fetch(this.form.action, {
                        method: 'POST',
                        body: formData,
                        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }
                    }).then(response => response.json())
                    .then(data => {
                        if (data.success) location.reload();
                        else alert(data.message || @js(__('messages.Error sending invoice')));
                    }).catch(error => alert(@js(__('messages.Error sending invoice'))));
                    this.show = false;
                }
            }));

            Alpine.data('partialCreditNoteModal', () => ({
                show: false,
                invoiceId: null,
                totalCost: 0,
                amount: null,
                error: false,
                errorMessage: '',
                open(invoiceId, totalCost) {
                    this.invoiceId = invoiceId;
                    this.totalCost = parseFloat(totalCost);
                    this.amount = null;
                    this.error = false;
                    this.show = true;
                },
                submitForm() {
                    this.error = false;
                    const amount = parseFloat(this.amount);
                    if (isNaN(amount) || amount <= 0) {
                        this.error = true; this.errorMessage = @js(__('messages.Please enter a valid amount greater than zero.')); return;
                    }
                    if (amount > this.totalCost) {
                        this.error = true; this.errorMessage = @js(__('messages.Amount exceeds total cost.')); return;
                    }
                    let url = '';
                    @if(Auth::user()->hasRole('Admin')) url = `{{ route('admin.invoice.creditNote', '') }}/${this.invoiceId}?type=partial&amount=${amount}`; @endif
                    @if(Auth::user()->hasRole('Responsable de projet')) url = `{{ route('Responsable_de_projet.invoice.creditNote', '') }}/${this.invoiceId}?type=partial&amount=${amount}`; @endif
                    @if(Auth::user()->hasRole('Employee')) url = `{{ route('employee.invoice.creditNote', '') }}/${this.invoiceId}?type=partial&amount=${amount}`; @endif
                    @if(Auth::user()->hasRole('Responsable de projet et gestionnaire')) url = `{{ route('Responsable_gestionnaire.invoice.creditNote', '') }}/${this.invoiceId}?type=partial&amount=${amount}`; @endif

                    fetch(url, {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                    .then(response => response.text())
                    .then(html => {
                        document.open(); document.write(html); document.close();
                    })
                    .catch(error => alert(@js(__('messages.An error occurred:')) + ' ' + error.message));
                }
            }));

            Alpine.data('moveTaskModal', () => ({
                show: false,
                taskId: null,
                currentProjectId: null,
                selectedProjectId: '',
                allProjects: @json($projects),
                get availableProjects() {
                    return this.allProjects.filter(proj => proj.id != this.currentProjectId);
                },
                open(taskId, currentProjectId) {
                    this.taskId = taskId;
                    this.currentProjectId = currentProjectId;
                    this.selectedProjectId = '';
                    this.show = true;
                },
                moveTask() {
                    if (!this.selectedProjectId) {
                        alert('{{ __('messages.Please select a project to move the task to.') }}');
                        return;
                    }
                    if (this.selectedProjectId == this.currentProjectId) {
                        alert('{{ __('messages.Please select a different project.') }}');
                        return;
                    }

                    let url = '';
                    @if(Auth::user()->hasRole('Admin')) url = '/admin/tasks/move-task'; @endif
                    @if(Auth::user()->hasRole('Responsable de projet')) url = '/Responsable_de_projet/tasks/move-task'; @endif
                    @if(Auth::user()->hasRole('Employee')) url = '/employee/tasks/move-task'; @endif
                    @if(Auth::user()->hasRole('Responsable de projet et gestionnaire')) url = '/Responsable_gestionnaire/tasks/move-task'; @endif

                    $.ajax({
                        url: url,
                        type: 'POST',
                        data: {
                            task_id: this.taskId,
                            new_project_id: this.selectedProjectId,
                            _token: '{{ csrf_token() }}'
                        },
                        success: (response) => {
                            if (response.status === 'success') {
                                if (response.redirect_url) {
                                    window.location.href = response.redirect_url;
                                } else {
                                    location.reload();
                                }
                            } else {
                                alert(response.message || @js(__('messages.Failed to move task.')));
                            }
                        },
                        error: (xhr) => {
                            let msg = '{{ __('messages.Failed to move task.') }}';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                msg = xhr.responseJSON.message;
                            }
                            alert(msg);
                        }
                    });
                    this.show = false;
                }
            }));
        });
    </script>
    <script src="//unpkg.com/alpinejs" defer></script>
</head>
<body class="dt-app-shell bg-[#f8fafc] text-slate-900 antialiased flex h-screen overflow-hidden font-sans" x-data>

    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-NKDMSK6" height="0" width="0"
            style="display: none; visibility: hidden"></iframe></noscript>

    {{-- Sidebar --}}
    @if(Auth::user()->hasRole('Admin'))
        <x-dashboard.sidebar />
    @else
        <x-employees.sidebar />
    @endif

    <div class="flex flex-col flex-1 overflow-y-auto">
        <x-dashboard.navbar />

        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-[#f8fafc] px-3 py-4 sm:px-5 sm:py-5 lg:px-16 lg:py-7">
            <div class="mx-auto max-w-[1600px] space-y-4">

                @php
                    // --- Project Header Logic ---
                    $projecttasksId = \App\Models\Tasks::where('project_id', $project->id)->pluck('id');
                    $totalduration = \App\Models\tasks_timer::whereIn('task_id', $projecttasksId)->sum('duration');
                    $hours = floor($totalduration / 60);
                    $minutes = $totalduration % 60;
                    $estimatedMinutes = $project->estimated_hours * 60;
                    $difference = $estimatedMinutes - $totalduration;
                    $diffhours = floor(abs($difference) / 60);
                    $diffminutes = abs($difference) % 60;
                    $progressPercentage = $estimatedMinutes > 0 ? ($totalduration / $estimatedMinutes) * 100 : 0;
                    $formattedProgressPercentage = number_format($progressPercentage, 2);
                    $projectTeams = \App\Models\project_teams::where('project_id', $project->id)->get();
                    $totalBilled = \App\Models\invoices_history::where('project_id', $project->id)->where('Payment_status', '!=', -1)->sum('total_htva');
                    $unbilledBudget = $project->budget - $totalBilled;
                    $projectStatusKey = match ((int) $project->status) {
                        0 => 'Project inactive',
                        1 => 'Project in progress',
                        2 => 'Project completed',
                        default => 'Status unknown',
                    };
                    $projectStatusClass = match ((int) $project->status) {
                        0 => 'bg-slate-100 text-slate-700 ring-1 ring-slate-600/10',
                        1 => 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/15',
                        2 => 'bg-[#E7B84B]/20 text-[#7a5a12] ring-1 ring-[#E7B84B]/40',
                        default => 'bg-slate-100 text-slate-700 ring-1 ring-slate-600/10',
                    };
                    $projectStartDate = $project->start_date ? \Carbon\Carbon::parse($project->start_date)->format('d-m-Y') : '-';
                    $projectEndDate = $project->end_date ? \Carbon\Carbon::parse($project->end_date)->format('d-m-Y') : '-';

                    // --- Determine Route Prefixes Once ---
                    $taskRoutePrefix = 'admin.'; // Default
                    if (Auth::user()->hasRole('Responsable de projet')) $taskRoutePrefix = 'Responsable_de_projet.';
                    elseif (Auth::user()->hasRole('Responsable de projet et gestionnaire')) $taskRoutePrefix = 'Responsable_gestionnaire.';
                    elseif (Auth::user()->hasRole('Employee')) $taskRoutePrefix = 'employee.';
                    $projectIndexRoute = route($taskRoutePrefix . 'projects.index');
                @endphp

                @if(session('error'))
                    <div class="flex items-center gap-3 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl mb-4 relative" role="alert">
                        <svg class="w-4 h-4 flex-shrink-0 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        <span class="text-sm font-medium">{{ session('error') }}</span>
                        <button type="button" onclick="this.parentElement.style.display='none';" class="absolute top-2.5 right-3 text-red-500 hover:text-red-700 text-lg leading-none" aria-label="{{ __('messages.Close') }}">&times;</button>
                    </div>
                @endif

                @if(session('alertmessage'))
                    <div class="flex items-center gap-3 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl mb-4 relative" role="alert">
                        <svg class="w-4 h-4 flex-shrink-0 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        <span class="text-sm font-medium">{{ session('alertmessage') }}</span>
                        <button type="button" onclick="this.parentElement.style.display='none';" class="absolute top-2.5 right-3 text-red-500 hover:text-red-700 text-lg leading-none" aria-label="{{ __('messages.Close') }}">&times;</button>
                    </div>
                @endif

                {{-- ── Page header ── --}}
                <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-2">
                    <div class="min-w-0">
                        <p class="text-[10px] font-semibold uppercase tracking-[.08em] text-slate-400 mb-0.5">{{ __('messages.Projects') }}</p>
                        <div class="flex items-center gap-2 flex-wrap">
                            <h1 class="text-[18px] sm:text-[20px] lg:text-[22px] font-bold tracking-tight text-[#0B2638] leading-tight break-words">{{ $project->name }}</h1>
                            <span class="dt-badge {{ $projectStatusClass }} text-[11px]">
                                <span class="dt-dot"></span>
                                {{ __('messages.' . $projectStatusKey) }}
                            </span>
                            @if(!empty($project->archived))
                                <span class="dt-badge dt-badge-danger text-[11px]">
                                    <span class="dt-dot"></span>
                                    {{ __('messages.Archived') }}
                                </span>
                            @endif
                        </div>
                        <p class="text-[12px] text-slate-500 mt-1">
                            {{ __('messages.Client') }}: {{ $client->name ?? $project->client->name ?? '-' }}
                            · {{ __('messages.Start Date') }}: {{ $projectStartDate }}
                            · {{ __('messages.End Date') }}: {{ $projectEndDate }}
                            · {{ __('messages.Created') }}: {{ optional($project->created_at)->format('d-m-Y') ?? '-' }}
                        </p>
                        @if(!$project->client_id)
                            <div class="flex items-center gap-3 bg-amber-50 border border-amber-200 text-amber-800 px-4 py-3 rounded-xl mt-3" role="alert">
                                <svg class="w-4 h-4 flex-shrink-0 text-amber-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                                <span class="text-sm font-medium">This project has no client assigned. You must assign a client before creating invoices, devis, or credit notes.</span>
                                <a href="{{ route($taskRoutePrefix . 'projects.edit', $project->id) }}" class="dt-btn dt-btn-teal text-[12px] ml-auto flex-shrink-0">Assign Client</a>
                            </div>
                        @endif
                    </div>
                    <div class="flex flex-wrap items-center gap-2 flex-shrink-0">
                        <a href="{{ $projectIndexRoute }}" class="dt-btn dt-btn-ghost text-[12px]">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                            {{ __('messages.Back to Reports') }}
                        </a>
                        @can('Add task')
                            <a href="{{ route($taskRoutePrefix . 'tasks.create', $project->id) }}" class="dt-btn dt-btn-teal text-[12px]">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                {{ __('messages.Add Task') }}
                            </a>
                        @endcan
                        @if(Auth::user()->hasRole('Admin') && Route::has('admin.projects.time-transfer.export') && Route::has('admin.projects.time-transfer.import'))
                            <a href="{{ route('admin.projects.time-transfer.export', $project) }}" class="dt-btn dt-btn-ghost text-[12px]">{{ __('messages.Export time transfer') }}</a>
                            <a href="{{ route('admin.projects.time-transfer.import', $project) }}" class="dt-btn dt-btn-ghost text-[12px]">{{ __('messages.Import time transfer') }}</a>
                        @endif
                    </div>
                </div>

                <section id="editableTable" class="dt-card p-4 sm:p-5">
                    <div class="flex items-center justify-between gap-3 mb-3">
                        <h2 class="text-[13px] font-semibold text-[#0B2638]">{{ __('messages.Project Overview') }}</h2>
                        <span class="dt-badge dt-badge-navy text-[11px]">
                            <span class="dt-dot"></span>{{ __('messages.Est. Hours header') }}: {{ $project->estimated_hours ?? 0 }}h
                        </span>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-5 gap-2.5">
                        <div class="dt-card p-3 sm:p-4 flex items-center gap-2 sm:gap-3">
                            <div class="flex-shrink-0 w-8 h-8 sm:w-10 sm:h-10 rounded-xl bg-[#E7B84B]/15 ring-1 ring-[#E7B84B]/30 flex items-center justify-center">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-[#c9971f]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><rect x="3" y="6" width="18" height="12" rx="2" stroke-linecap="round" stroke-linejoin="round"/><circle cx="12" cy="12" r="2.5"/><path stroke-linecap="round" d="M6.5 9.5h.01M17.5 14.5h.01"/></svg>
                            </div>
                            <div class="min-w-0">
                                <p class="text-[9px] sm:text-[10.5px] font-semibold uppercase tracking-[.07em] text-slate-400 truncate">{{ __('messages.Budget') }}</p>
                                <p class="text-[13px] sm:text-[17px] font-bold text-[#0B2638] leading-tight editable" data-id="{{$project->id}}" data-column="budget">€{{ number_format($project->budget, 2, ',', ' ') }}</p>
                            </div>
                        </div>
                        <div class="dt-card p-3 sm:p-4 flex items-center gap-2 sm:gap-3">
                            <div class="flex-shrink-0 w-8 h-8 sm:w-10 sm:h-10 rounded-xl bg-emerald-50 ring-1 ring-emerald-600/15 flex items-center justify-center">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <div class="min-w-0">
                                <p class="text-[9px] sm:text-[10.5px] font-semibold uppercase tracking-[.07em] text-slate-400 truncate">{{ __('messages.Total invoiced') }}</p>
                                <p class="text-[13px] sm:text-[17px] font-bold text-emerald-600 leading-tight">€{{ number_format($totalBilled, 2, ',', ' ') }}</p>
                            </div>
                        </div>
                        <div class="dt-card p-3 sm:p-4 flex items-center gap-2 sm:gap-3">
                            <div class="flex-shrink-0 w-8 h-8 sm:w-10 sm:h-10 rounded-xl bg-[#2D7080]/10 ring-1 ring-[#2D7080]/20 flex items-center justify-center">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-[#2D7080]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                            </div>
                            <div class="min-w-0">
                                <p class="text-[9px] sm:text-[10.5px] font-semibold uppercase tracking-[.07em] text-slate-400 truncate">{{ __('messages.Unbilled') }}</p>
                                <p class="text-[13px] sm:text-[17px] font-bold text-[#0B2638] leading-tight">€{{ number_format(max($unbilledBudget, 0), 2, ',', ' ') }}</p>
                            </div>
                        </div>
                        <div class="dt-card p-3 sm:p-4 flex items-center gap-2 sm:gap-3">
                            <div class="flex-shrink-0 w-8 h-8 sm:w-10 sm:h-10 rounded-xl bg-[#E7B84B]/15 ring-1 ring-[#E7B84B]/30 flex items-center justify-center">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-[#c9971f]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M12 6a6 6 0 016 6 6 6 0 11-6-6z"/></svg>
                            </div>
                            <div class="min-w-0">
                                <p class="text-[9px] sm:text-[10.5px] font-semibold uppercase tracking-[.07em] text-slate-400 truncate">{{ __('messages.Cost/Hour label') }}</p>
                                <p class="text-[13px] sm:text-[17px] font-bold text-[#0B2638] leading-tight editable" data-id="{{$project->id}}" data-column="cost_per_hour">€{{ number_format($project->cost_per_hour, 2, ',', ' ') }}</p>
                            </div>
                        </div>
                        <div class="dt-card p-3 sm:p-4 flex items-center gap-2 sm:gap-3">
                            <div class="flex-shrink-0 w-8 h-8 sm:w-10 sm:h-10 rounded-xl bg-[#2D7080]/10 ring-1 ring-[#2D7080]/20 flex items-center justify-center">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-[#2D7080]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 7v5l3 3"/></svg>
                            </div>
                            <div class="min-w-0">
                                <p class="text-[9px] sm:text-[10.5px] font-semibold uppercase tracking-[.07em] text-slate-400 truncate">{{ __('messages.Duration') }}</p>
                                <p class="text-[13px] sm:text-[17px] font-bold text-[#0B2638] leading-tight">{{ $hours }}h:{{ $minutes }}m</p>
                            </div>
                        </div>
                        <div class="dt-card p-3 sm:p-4 flex items-center gap-2 sm:gap-3">
                            <div class="flex-shrink-0 w-8 h-8 sm:w-10 sm:h-10 rounded-xl bg-[#0B2638]/[0.06] ring-1 ring-[#0B2638]/10 flex items-center justify-center">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-[#0B2638]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            </div>
                            <div class="min-w-0">
                                <p class="text-[9px] sm:text-[10.5px] font-semibold uppercase tracking-[.07em] text-slate-400 truncate">{{ __('messages.Est. Hours header') }}</p>
                                <p class="text-[13px] sm:text-[17px] font-bold text-[#0B2638] leading-tight editable" data-id="{{$project->id}}" data-column="estimated_hours">{{ $project->estimated_hours }}h</p>
                            </div>
                        </div>
                        <div class="dt-card p-3 sm:p-4 flex items-center gap-2 sm:gap-3">
                            <div class="flex-shrink-0 w-8 h-8 sm:w-10 sm:h-10 rounded-xl bg-emerald-50 ring-1 ring-emerald-600/15 flex items-center justify-center">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                            </div>
                            <div class="min-w-0">
                                <p class="text-[9px] sm:text-[10.5px] font-semibold uppercase tracking-[.07em] text-slate-400 truncate">{{ __('messages.Difference') }}</p>
                                <p class="text-[13px] sm:text-[17px] font-bold text-[#0B2638] leading-tight">
                                    @if($difference > 0)
                                        <span class="text-emerald-600">+{{ $diffhours }}h:{{ $diffminutes }}m</span>
                                    @elseif($difference < 0)
                                        <span class="text-red-600">-{{ $diffhours }}h:{{ $diffminutes }}m</span>
                                    @else
                                        <span class="text-slate-600">{{ $diffhours }}h:{{ $diffminutes }}m</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="dt-card p-3 sm:p-4 flex items-center gap-2 sm:gap-3">
                            <div class="flex-shrink-0 w-8 h-8 sm:w-10 sm:h-10 rounded-xl bg-[#E7B84B]/15 ring-1 ring-[#E7B84B]/30 flex items-center justify-center">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-[#c9971f]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
                            </div>
                            <div class="min-w-0">
                                <p class="text-[9px] sm:text-[10.5px] font-semibold uppercase tracking-[.07em] text-slate-400 truncate">{{ __('messages.VAT') }}</p>
                                <p class="text-[13px] sm:text-[17px] font-bold text-[#0B2638] leading-tight editable" data-id="{{$project->id}}" data-column="tva">{{ $project->tva }}%</p>
                            </div>
                        </div>
                        <div class="dt-card p-3 sm:p-4 flex items-center gap-2 sm:gap-3">
                            <div class="flex-shrink-0 w-8 h-8 sm:w-10 sm:h-10 rounded-xl bg-[#2D7080]/10 ring-1 ring-[#2D7080]/20 flex items-center justify-center">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-[#2D7080]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 8v5m4 0v5m4-9v13a2 2 0 01-2 2H6a2 2 0 01-2-2V7a2 2 0 012-2h9"/></svg>
                            </div>
                            <div class="min-w-0">
                                <p class="text-[9px] sm:text-[10.5px] font-semibold uppercase tracking-[.07em] text-slate-400 truncate">{{ __('messages.Progress') }}</p>
                                <p class="text-[13px] sm:text-[17px] font-bold text-[#0B2638] leading-tight">{{ $formattedProgressPercentage }}%</p>
                                <div class="mt-2 w-full h-2 bg-slate-200 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full @if($progressPercentage < 50) bg-emerald-500 @elseif($progressPercentage < 75) bg-yellow-500 @else bg-red-500 @endif"
                                         style="width: {{ min($formattedProgressPercentage, 100) }}%">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @if(Auth::user()->hasRole('Admin') || Auth::user()->hasRole('Responsable de projet'))
                        <div class="pt-4 mt-4 border-t border-slate-100">
                            <div class="flex flex-wrap items-center justify-start gap-2">
                            @can('Update project')
                                <a href="{{ route(Auth::user()->hasRole('Admin') ? 'admin.projects.edit' : 'Responsable_de_projet.projects.edit', $project->id) }}" class="dt-btn dt-btn-primary">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    {{ __('messages.Edit') }}
                                </a>
                            @endcan

                            @can('Delete project')
                                @php
                                    $archiveRoute = route($taskRoutePrefix . 'projects.archive', $project->id);
                                    $deleteRoute = route($taskRoutePrefix . 'projects.destroy', $project->id);
                                @endphp

                                <form action="{{ $archiveRoute }}" method="post" onsubmit="return confirm('{{ __('messages.Archive this project?') }}');" class="inline">
                                    @csrf
                                    <button type="submit" class="dt-btn dt-btn-ghost">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                                        {{ __('messages.Archive') }}
                                    </button>
                                </form>

                                <form action="{{ $deleteRoute }}" method="post" onsubmit="return confirm('{{ __('messages.Are you sure you want to delete this project?') }}');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="dt-btn dt-btn-ghost !text-red-600 !border-red-200 hover:!bg-red-50">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        {{ __('messages.Delete') }}
                                    </button>
                                </form>
                            @endcan

                            @can('Add project')
                                @php
                                    $createRouteName = Auth::user()->hasRole('Admin')
                                        ? 'admin.projects.create'
                                        : 'Responsable_de_projet.projects.create';
                                @endphp
                                <a href="{{ route($createRouteName, ['client_id' => $project->client_id ?? null, 'parent_id' => $project->id]) }}"
                                   class="dt-btn dt-btn-teal">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                    {{ __('messages.Create SubProject') }}
                                </a>
                            @endcan
                            </div>
                        </div>
                    @endif
                </section>

                @if(isset($subprojects) && $subprojects->count() > 0)
                    <div class="dt-card overflow-hidden">
                        <div class="px-5 py-3.5 border-b border-slate-100">
                            <p class="text-[13px] font-semibold text-[#0B2638]">{{ __('messages.Subprojects Summary') }}</p>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 p-4 sm:p-5">
                            <div class="rounded-xl bg-slate-50 ring-1 ring-slate-600/10 px-4 py-3">
                                <p class="text-[11px] font-semibold uppercase tracking-[.07em] text-slate-400 mb-1">{{ __('messages.Total Subprojects') }}</p>
                                <p class="text-[17px] font-bold text-[#0B2638]">{{ $subprojects->count() }}</p>
                            </div>
                            <div class="rounded-xl bg-slate-50 ring-1 ring-slate-600/10 px-4 py-3">
                                <p class="text-[11px] font-semibold uppercase tracking-[.07em] text-slate-400 mb-1">{{ __('messages.Total Tasks header') }}</p>
                                <p class="text-[17px] font-bold text-[#0B2638]">{{ $subprojectsSummary['total_tasks'] ?? 0 }}</p>
                            </div>
                            <div class="rounded-xl bg-slate-50 ring-1 ring-slate-600/10 px-4 py-3">
                                <p class="text-[11px] font-semibold uppercase tracking-[.07em] text-slate-400 mb-1">{{ __('messages.Total Hours') }}</p>
                                <p class="text-[17px] font-bold text-[#0B2638]">
                                    {{ $subprojectsSummary['total_hours'] ?? 0 }}h:{{ str_pad($subprojectsSummary['remaining_minutes'] ?? 0, 2, '0', STR_PAD_LEFT) }}m
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="dt-card overflow-hidden">
                        <div class="flex items-center justify-between px-5 py-3.5 border-b border-slate-100">
                            <p class="text-[13px] font-semibold text-[#0B2638]">{{ __('messages.Subprojects') }}</p>
                            <span class="text-[12px] text-slate-500">{{ $subprojects->count() }} {{ __('messages.Total') ?? 'total' }}</span>
                        </div>
                        <div class="divide-y divide-slate-100">
                            @foreach($subprojects as $sub)
                                <div class="px-5 py-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                                    @php
                                        $subProjectShowRoute = route($taskRoutePrefix . 'projects.show', $sub->id);
                                        $subProjectDeleteRoute = route($taskRoutePrefix . 'projects.destroy', $sub->id);
                                    @endphp
                                    <div class="min-w-0">
                                        <a href="{{ $subProjectShowRoute }}" class="text-[13px] font-semibold text-[#2D7080] hover:text-[#0B2638] hover:underline">
                                            {{ $sub->name }}
                                        </a>
                                        <p class="text-[12px] text-slate-500 mt-0.5">
                                            {{ __('messages.Budget') }}: {{ $sub->budget ?? '-' }} | {{ __('messages.Cost/Hour label') }}: {{ $sub->cost_per_hour ?? '-' }}
                                        </p>
                                    </div>
                                    <div class="flex items-center gap-3 text-[12px] text-slate-500 flex-shrink-0">
                                        <span>{{ __('messages.Created') }}: {{ $sub->created_at?->format('Y-m-d') }}</span>
                                        @can('Delete project')
                                            <form action="{{ $subProjectDeleteRoute }}" method="post" onsubmit="return confirm('{{ __('messages.Are you sure you want to delete this project?') }}');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" title="{{ __('messages.Delete project') }}" class="act-btn act-btn-delete">
                                                    <svg class="act-btn-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862A2 2 0 015.995 19.142L5 7m5 4v6m4-6v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3M4 7h16" />
                                                    </svg>
                                                    {{ __('messages.Delete') }}
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @php
                    $current_employee_id = \App\Models\Employees::where('user_id', Auth::user()->id)->first()?->id;
                    $taskIds = $tasks->pluck('id');
                    $allTaskEmployees = \App\Models\employees_tasks::whereIn('task_id', $taskIds)->get()->groupBy('task_id');
                    $allEmployeeDetails = \App\Models\Employees::whereIn('id', $allTaskEmployees->flatten()->pluck('employee_id'))->get()->keyBy('id');
                    $allTaskTimers = \App\Models\tasks_timer::whereIn('task_id', $taskIds)->get();
                    $allTaskTeams = \App\Models\team_tasks::whereIn('task_id', $taskIds)->get()->groupBy('task_id');
                    $allTeamDetails = \App\Models\Teams::whereIn('id', $allTaskTeams->flatten()->pluck('team_id'))->get()->keyBy('id');
                    $activeTimers = $allTaskTimers->where('employee_id', $current_employee_id)->whereNull('end_date')->keyBy('task_id');
                @endphp

                <div class="dt-card overflow-hidden">
                    <div class="px-5 py-3.5 border-b border-slate-100 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
                        <div class="flex items-center justify-between gap-3 w-full">
                            <p class="text-[13px] font-semibold text-[#0B2638]">{{ __('messages.Tasks header') }}</p>
                            <button type="button" class="section-toggle"
                                    data-target="projectTasksBody" aria-expanded="false">
                                <span class="hidden lg:inline">{{ __('messages.Show') ?? 'Show' }}</span>
                                <svg class="section-chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6" />
                                </svg>
                            </button>
                        </div>
                        <div class="relative">
                            <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-3 top-2.5 h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 1010.5 18.5a7.5 7.5 0 006.15-3.85z"/></svg>
                            <input type="text" id="taskSearchInput" placeholder="{{ __('messages.Search tasks...') }}" class="dt-filter-select block w-full sm:w-64 rounded-lg border border-slate-200 bg-slate-50 text-[13px] text-slate-700 focus:border-[#2D7080] focus:ring-1 focus:ring-[#2D7080] py-2 pl-9 pr-3 transition-all">
                        </div>
                    </div>

                    <div id="projectTasksBody" class="hidden">
                    <div class="overflow-x-auto" style="-webkit-overflow-scrolling: touch;">
                        <table class="w-full text-left border-collapse table-auto" id="taskTable">
                            <thead>
                                <tr>
                                    <th class="text-center whitespace-nowrap">{{ __('messages.Task column') }}</th>
                                    <th class="text-center whitespace-nowrap">{{ __('messages.Description column') }}</th>
                                    <th class="text-center whitespace-nowrap">{{ __('messages.Employees column') }}</th>
                                    <th class="text-center whitespace-nowrap">{{ __('messages.Duration') }}</th>
                                    <th class="text-center whitespace-nowrap">{{ __('messages.Total column') }}</th>
                                    <th class="text-center whitespace-nowrap">{{ __('messages.Estimated column') }}</th>
                                    <th class="text-center whitespace-nowrap">{{ __('messages.Difference column') }}</th>
                                    <th class="text-center whitespace-nowrap">{{ __('messages.End Date') }}</th>
                                    <th class="text-center whitespace-nowrap">{{ __('messages.Progress column') }}</th>
                                    <th class="text-center whitespace-nowrap">{{ __('messages.Teams column') }}</th>
                                    <th class="text-center whitespace-nowrap">{{ __('messages.Pointage') }}</th>
                                    <th class="text-center whitespace-nowrap" style="min-width: 220px">{{ __('messages.Action column') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($tasks as $task)
                                @php
                                    $taskEmployees = $allTaskEmployees->get($task->id, collect());
                                    $employeeIds = $taskEmployees->pluck('employee_id');
                                    $taskEmployeeDetails = $allEmployeeDetails->whereIn('id', $employeeIds);
                                    $taskTotalDuration = $allTaskTimers->where('task_id', $task->id)->sum('duration');
                                    $taskEndDate = $allTaskTimers->where('task_id', $task->id)->max('end_date');
                                    $taskHours = floor($taskTotalDuration / 60);
                                    $taskMinutes = $taskTotalDuration % 60;
                                    $estimatedMinutes = max(0, (($task->estimated_hours ?? 0) - ($task->consumed_hours ?? 0)) * 60);
                                    $taskDifference = $estimatedMinutes - $taskTotalDuration;
                                    $diffHours = floor(abs($taskDifference) / 60);
                                    $diffMinutes = abs($taskDifference) % 60;
                                    $taskProgress = $estimatedMinutes > 0 ? ($taskTotalDuration / $estimatedMinutes) * 100 : 0;
                                    $taskProgressFormatted = number_format($taskProgress, 2);
                                    $teamTasks = $allTaskTeams->get($task->id, collect());
                                    $task_timer = $activeTimers->get($task->id);
                                    $employee_task = $taskEmployees->where('employee_id', $current_employee_id)->first();
                                @endphp
                                <tr class="hover:bg-slate-50/70">
                                    <td data-label="{{ __('messages.Task column') }}" data-span="2" class="text-sm text-slate-900 whitespace-normal break-words">
                                        <a href="{{ route('admin.tasks.show', $task->id) }}" class="text-[#2D7080] hover:text-[#0B2638] hover:underline font-semibold">
                                            {{ $task->title }}
                                        </a>
                                    </td>
                                    <td data-label="{{ __('messages.Description column') }}" data-span="2" class="text-[12.5px] text-slate-600 max-w-xs truncate whitespace-normal break-words">{{ $task->description }}</td>
                                    <td data-label="{{ __('messages.Employees column') }}" class="text-[12.5px] text-slate-900 whitespace-normal break-words">
                                        @foreach($taskEmployeeDetails as $employee)
                                            <span class="inline-block bg-[#2D7080]/10 text-[#2D7080] ring-1 ring-[#2D7080]/20 px-2 py-0.5 rounded-full text-[11px] font-semibold mb-1">{{ $employee->name }}</span><br>
                                        @endforeach
                                    </td>
                                    <td data-label="{{ __('messages.Duration') }}" class="text-[12.5px] text-slate-900 whitespace-normal break-words">
                                        @foreach($taskEmployeeDetails as $employee)
                                            @php
                                                $duration = $allTaskTimers->where('task_id', $task->id)->where('employee_id', $employee->id)->sum('duration');
                                                $h = floor($duration / 60);
                                                $m = $duration % 60;
                                            @endphp
                                            <a href="{{ route($taskRoutePrefix . 'tasks.durationInfos', ['employee' => $employee->id, 'task' => $task->id]) }}" class="text-[#2D7080] hover:text-[#0B2638]">
                                                {{ $h }}h:{{ $m }}m
                                            </a><br>
                                        @endforeach
                                    </td>
                                    <td data-label="{{ __('messages.Total column') }}" class="text-[12.5px] text-slate-900 whitespace-nowrap">{{ $taskHours }}h:{{ $taskMinutes }}m</td>
                                    <td data-label="{{ __('messages.Estimated column') }}" class="text-[12.5px] text-slate-900 whitespace-nowrap">{{ $task->estimated_hours ?? '-' }}</td>
                                    <td data-label="{{ __('messages.Difference column') }}" class="text-[12.5px] whitespace-nowrap">
                                        @if($taskDifference > 0)
                                            <span class="text-emerald-600">+{{ $diffHours }}h:{{ $diffMinutes }}m</span>
                                        @elseif($taskDifference < 0)
                                            <span class="text-red-600">-{{ $diffHours }}h:{{ $diffMinutes }}m</span>
                                        @else
                                            <span class="text-slate-600">0h:0m</span>
                                        @endif
                                    </td>
                                    <td data-label="{{ __('messages.End Date') }}" class="text-[12.5px] text-slate-900 whitespace-nowrap">
                                        @if($taskEndDate)
                                            {{ \Carbon\Carbon::parse($taskEndDate)->format('d-m-Y H:i') }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td data-label="{{ __('messages.Progress column') }}" class="text-[12.5px]">
                                        <div class="w-24 h-2 bg-slate-200 rounded-full overflow-hidden">
                                            <div class="h-full rounded-full @if($taskProgress < 50) bg-emerald-500 @elseif($taskProgress < 75) bg-yellow-500 @else bg-red-500 @endif" style="width: {{ min($taskProgressFormatted, 100) }}%"></div>
                                        </div>
                                        <span class="text-[11px] text-slate-700 mt-1">{{ $taskProgressFormatted }}%</span>
                                    </td>
                                    <td data-label="{{ __('messages.Teams column') }}" class="text-[12.5px] text-slate-900 whitespace-normal break-words">
                                        @foreach($teamTasks as $teamTask)
                                            {{ $allTeamDetails->get($teamTask->team_id)?->name }}<br>
                                        @endforeach
                                    </td>
                                    <td data-label="{{ __('messages.Pointage') }}" data-span="2" class="text-[12.5px] text-slate-900">
                                        @if($employee_task)
                                            @if(!$task_timer)
                                                <form class="flex flex-col gap-2" action="{{ route($taskRoutePrefix . 'tasks.start', ['tasks' => $employee_task->id, 'employee_id' => $current_employee_id]) }}" method="POST"
                                                    data-chrono-start data-chrono-employee="{{ $current_employee_id }}"
                                                    data-chrono-emp-task="{{ $employee_task->id }}" data-chrono-task="{{ $task->id }}">
                                                    @csrf
                                                    <textarea name="comment" class="block w-full rounded-lg border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-xs py-1.5 px-2.5" placeholder="{{ __('messages.Comment placeholder') }}" rows="1" style="width: 110px;"></textarea>
                                                    <button class="act-btn act-btn-send" type="submit">
                                                        <svg class="act-btn-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                                        {{ __('messages.Start') }}
                                                    </button>
                                                </form>
                                            @else
                                                <form class="mt-2 stop-form" action="{{ route($taskRoutePrefix . 'tasks.stop', ['tasks' => $employee_task->id, 'employee_id' => $current_employee_id]) }}" method="POST"
                                                    data-chrono-stop data-chrono-employee="{{ $current_employee_id }}"
                                                    data-chrono-emp-task="{{ $employee_task->id }}" data-chrono-task="{{ $task->id }}">
                                                    @csrf
                                                    <input type="hidden" name="stop_comment" class="stop-comment-field">
                                                    <button class="stop-button act-btn act-btn-unpaid" type="submit" data-task-id="{{ $task->id }}">
                                                        <svg class="act-btn-icon" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                                                        {{ __('messages.Stop') }}
                                                    </button>
                                                </form>
                                            @endif
                                        @endif
                                    </td>
                                    <td data-label="{{ __('messages.Action column') }}" data-span="2">
                                        <div class="task-actions flex flex-wrap items-center justify-center gap-1.5">
                                            @can('Update task')
                                                <a href="{{ route($taskRoutePrefix . 'tasks.edit', $task->id) }}" class="act-btn act-btn-edit" title="{{ __('messages.Edit Task') }}">
                                                    <svg class="act-btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                                    </svg>
                                                    {{ __('messages.Edit') }}
                                                </a>
                                            @endcan

                                            @can('Add manual timer')
                                                <button type="button"
                                                        class="act-btn act-btn-neutral"
                                                        title="{{ __('messages.Add Manual Time') }}"
                                                        @click="$dispatch('open-manual-timer-modal', { taskId: {{ $task->id }}, employeeId: {{ $current_employee_id ?? 'null' }} })">
                                                    <svg class="act-btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M12 6a6 6 0 016 6 6 6 0 11-6-6z" />
                                                    </svg>
                                                    {{ __('messages.Add Manual Time') }}
                                                </button>
                                            @endcan

                                            @can('Update task')
                                                <button type="button" class="act-btn act-btn-neutral" title="{{ __('messages.Move Task') }}" @click="$dispatch('open-move-task-modal', { taskId: {{ $task->id }}, currentProjectId: {{ $project->id }} })">
                                                    <svg class="act-btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                                    </svg>
                                                    {{ __('messages.Move') }}
                                                </button>
                                            @endcan
                                            @can('Delete task')
                                            <form method="post" action="{{ route($taskRoutePrefix . 'tasks.destroy', $task->id) }}" onsubmit="return confirm('{{ __('messages.Are you sure?') }}');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="act-btn act-btn-delete" title="{{ __('messages.Delete Task') }}">
                                                    <svg class="act-btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                    {{ __('messages.Delete') }}
                                                </button>
                                            </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="11" class="py-8 text-center">
                                        <div class="flex flex-col items-center gap-3">
                                            <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center">
                                                <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                                </svg>
                                            </div>
                                            <p class="text-[13px] font-medium text-slate-500">{{ __('messages.No tasks found') }}</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    </div>
                </div>

                @can('View invoices')
                @if($invoices !== null)
                <div class="dt-card overflow-hidden">
                    <div class="px-5 py-3.5 border-b border-slate-100 flex flex-col sm:flex-row justify-between items-center gap-3">
                        <div class="flex items-center justify-between gap-3 w-full">
                            <p class="text-[13px] font-semibold text-[#0B2638]">{{ __('messages.Invoices header') }}</p>
                            <button type="button" class="section-toggle"
                                    data-target="projectInvoicesBody" aria-expanded="false">
                                <span class="hidden lg:inline">{{ __('messages.Show') ?? 'Show' }}</span>
                                <svg class="section-chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6" />
                                </svg>
                            </button>
                        </div>
                        @php
                            $totalPaid = App\Models\invoices_history::where('entreprise_id', Auth::user()->entreprise_id)->where('project_id',$project->id)->where('Payment_status', 1)->sum('total_ttc');
                            $totalUnpaid = App\Models\invoices_history::where('entreprise_id', Auth::user()->entreprise_id)->where('project_id',$project->id)
                                            ->where(function($query) {
                                                $query->where('Payment_status', 0)->orWhereNull('Payment_status');
                                            })->sum('total_ttc');
                            $totaltva = App\Models\invoices_history::where('entreprise_id', Auth::user()->entreprise_id)->where('project_id',$project->id)->sum('total_tva');
                        @endphp
                        <div class="flex flex-wrap gap-x-4 gap-y-1 text-[12.5px] mt-2 sm:mt-0">
                            <span>{{ __('messages.Total Paid') }}: <strong class="text-emerald-600">€{{ number_format($totalPaid, 2, ',', ' ') }}</strong></span>
                            <span>{{ __('messages.Total Unpaid') }}: <strong class="text-red-600">€{{ number_format($totalUnpaid, 2, ',', ' ') }}</strong></span>
                            <span>{{ __('messages.Total TVA') }}: <strong class="text-[#0B2638]">€{{ number_format($totaltva, 2, ',', ' ') }}</strong></span>
                        </div>
                    </div>
                    <div id="projectInvoicesBody" class="hidden">
                    <div class="overflow-x-auto" style="-webkit-overflow-scrolling: touch;">
                        <table class="w-full table-auto" id="invoicesTable">
                            <thead>
                                <tr>
                                    <th class="text-left pl-5">{{ __('messages.Invoice N') }}</th>
                                    <th class="text-left whitespace-nowrap">{{ __('messages.VAT') }}</th>
                                    <th class="text-left">{{ __('messages.Client') }}</th>
                                    <th class="text-left whitespace-nowrap">{{ __('messages.Type') }}</th>
                                    <th class="text-left whitespace-nowrap">{{ __('messages.Created At') }}</th>
                                    <th class="text-right whitespace-nowrap">{{ __('messages.Cost HT') ?? 'Cost HT' }}</th>
                                    <th class="text-right whitespace-nowrap">{{ __('messages.Cost TVA') ?? 'Cost TVA' }}</th>
                                    <th class="text-right whitespace-nowrap">{{ __('messages.Cost TTC') ?? 'Cost TTC' }}</th>
                                    <th class="text-center">{{ __('messages.Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($invoices as $invoice)
                                <tr class="hover:bg-slate-50/70">
                                    <td data-label="{{ __('messages.Invoice N') }}" data-span="2" class="pl-5 text-sm font-medium whitespace-nowrap">
                                        @php $pdfRoute = '#'; @endphp
                                        @if(Auth::user()->hasRole('Responsable de projet')) @php $pdfRoute = route('Responsable_de_projet.invoices.servePdf', ['invoiceName' => $invoice->id]); @endphp @endif
                                        @if(Auth::user()->hasRole('Admin')) @php $pdfRoute = route('admin.invoices.servePdf', ['invoiceName' => $invoice->id]); @endphp @endif
                                        @if(Auth::user()->hasRole('Employee')) @php $pdfRoute = route('employee.invoices.servePdf', ['invoiceName' => $invoice->id]); @endphp @endif
                                        @if(Auth::user()->hasRole('Responsable de projet et gestionnaire')) @php $pdfRoute = route('Responsable_gestionnaire.invoices.servePdf', ['invoiceName' => $invoice->id]); @endphp @endif

                                        <a href="{{ $pdfRoute }}" target="_blank" class="text-[12.5px] font-semibold text-[#2D7080] hover:text-[#0B2638] hover:underline">
                                            {{$entreprise_infos->invoice_prefix}}-{{date('ym', strtotime($invoice->created_at))}}{{$invoice->number}}
                                        </a>
                                    </td>
                                    <td data-label="{{ __('messages.VAT') }}" class="text-[12.5px] text-slate-500 whitespace-nowrap">
                                        @php $project = App\Models\Project::find($invoice->project_id); @endphp
                                        {{$project->tva ?? 'N/A'}}%
                                    </td>
                                    <td data-label="{{ __('messages.Client') }}" class="text-[12.5px] text-slate-700 whitespace-normal break-words">{{ $invoice->client_name }}</td>
                                    <td data-label="{{ __('messages.Type') }}">
                                        @php
                                            $typeBadgeClass = match(strtolower($invoice->type ?? '')) {
                                                'partial'    => 'bg-violet-50  text-violet-700  ring-1 ring-violet-600/15',
                                                'percentage' => 'bg-blue-50    text-blue-700    ring-1 ring-blue-600/15',
                                                'period'     => 'bg-cyan-50    text-cyan-700    ring-1 ring-cyan-600/15',
                                                'detailed'   => 'bg-amber-50   text-amber-700   ring-1 ring-amber-600/15',
                                                default      => 'bg-slate-100  text-slate-600   ring-1 ring-slate-600/10',
                                            };
                                        @endphp
                                        <span class="inv-type-badge {{ $typeBadgeClass }}">@if(($invoice->type ?? '') === 'Percentage'){{ $invoice->type }} ({{ (float)($invoice->percentage ?? 0) }}%)@else{{ $invoice->type }}@endif</span>
                                    </td>
                                    <td data-label="{{ __('messages.Created At') }}" class="text-[12px] text-slate-500 whitespace-nowrap">{{ $invoice->created_at->format('Y-m-d') }}</td>
                                    <td data-label="{{ __('messages.Cost HT') ?? 'Cost HT' }}" class="text-[12.5px] font-semibold text-slate-900 text-right whitespace-nowrap">
                                        €{{ number_format((float)($invoice->total_htva ?? 0), 2, ',', ' ') }}
                                    </td>
                                    <td data-label="{{ __('messages.Cost TVA') ?? 'Cost TVA' }}" class="text-[12.5px] font-semibold text-slate-900 text-right whitespace-nowrap">
                                        €{{ number_format((float)($invoice->total_tva ?? 0), 2, ',', ' ') }}
                                    </td>
                                    <td data-label="{{ __('messages.Cost TTC') ?? 'Cost TTC' }}" class="text-[12.5px] font-semibold text-slate-900 text-right whitespace-nowrap">
                                        €{{ number_format((float)($invoice->total_ttc ?? 0), 2, ',', ' ') }}
                                    </td>
                                    <td data-label="{{ __('messages.Actions') }}" data-span="2" class="whitespace-nowrap">
                                        <div class="invoice-actions flex flex-row items-center justify-center gap-2">
                                            @php
                                                $isPaid = $invoice->Payment_status == 1;
                                                $toggleRoute = '#';
                                                if(Auth::user()->hasRole('Admin')) $toggleRoute = $isPaid ? route('admin.invoice.deactivate', $invoice->id) : route('admin.invoice.activate', $invoice->id);
                                                elseif(Auth::user()->hasRole('Responsable de projet')) $toggleRoute = $isPaid ? route('Responsable_de_projet.invoice.deactivate', $invoice->id) : route('Responsable_de_projet.invoice.activate', $invoice->id);
                                                elseif(Auth::user()->hasRole('Employee')) $toggleRoute = $isPaid ? route('employee.invoice.deactivate', $invoice->id) : route('employee.invoice.activate', $invoice->id);
                                                elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire')) $toggleRoute = $isPaid ? route('Responsable_gestionnaire.invoice.deactivate', $invoice->id) : route('Responsable_gestionnaire.invoice.activate', $invoice->id);
                                            @endphp
                                            <form action="{{ $toggleRoute }}" method="post" onsubmit="return confirm('{{ __('messages.Change payment status?') }}');">
                                                @csrf
                                                <button type="submit" title="{{ $isPaid ? __('messages.Mark as Unpaid') : __('messages.Mark as Paid') }}"
                                                        class="payment-button act-btn {{ $isPaid ? 'act-btn-paid' : 'act-btn-unpaid' }}">
                                                    <svg class="act-btn-icon" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                                    {{ $isPaid ? __('messages.Paid') : __('messages.Unpaid') }}
                                                </button>
                                            </form>

                                            <button type="button" title="{{ $invoice->sentInvoice == 0 ? __('messages.Send Invoice') : __('messages.Resend Invoice') }}"
                                                    class="act-btn act-btn-send"
                                                    @click="$dispatch('open-send-invoice-modal', { invoiceId: {{ $invoice->id }} })">
                                                <svg class="act-btn-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                                {{ __('messages.Send') }}
                                            </button>

                                            <div x-data="{ open: false }" class="relative">
                                                <button @click="open = !open" type="button" title="{{ __('messages.Create Credit Note') }}" class="act-btn act-btn-credit">
                                                    <svg class="act-btn-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
                                                    {{ __('messages.Credit Note') }}
                                                    <svg class="act-btn-chevron" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                                </button>
                                                <ul x-show="open" @click.outside="open = false" x-transition style="display: none;"
                                                    class="absolute right-0 bottom-full mb-2 w-40 rounded-xl shadow-lg bg-white ring-1 ring-slate-200 focus:outline-none z-50 py-1">
                                                    @php
                                                    $creditNoteRoute = ''; $detailedCreditNoteRoute = '';
                                                    if(Auth::user()->hasRole('Admin')) { $creditNoteRoute = 'admin.invoice.creditNote'; $detailedCreditNoteRoute = 'admin.invoice.detailedCreditNote'; }
                                                    elseif(Auth::user()->hasRole('Responsable de projet')) { $creditNoteRoute = 'Responsable_de_projet.invoice.creditNote'; $detailedCreditNoteRoute = 'Responsable_de_projet.invoice.detailedCreditNote'; }
                                                    elseif(Auth::user()->hasRole('Employee')) { $creditNoteRoute = 'employee.invoice.creditNote'; $detailedCreditNoteRoute = 'employee.invoice.detailedCreditNote'; }
                                                    elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire')) { $creditNoteRoute = 'Responsable_gestionnaire.invoice.creditNote'; $detailedCreditNoteRoute = 'Responsable_gestionnaire.invoice.detailedCreditNote'; }
                                                    @endphp

                                                    @if($invoice->type == 'Detailed' && $detailedCreditNoteRoute)
                                                        <li><a href="#" class="block px-3 py-2 text-[12.5px] text-slate-700 hover:bg-slate-50 rounded-lg mx-1" @click.prevent="$dispatch('open-partial-credit-modal', { invoiceId: {{ $invoice->id }}, totalCost: {{ $invoice->total_ttc }} }); open = false;">{{ __('messages.Partial') }}</a></li>
                                                        <li><a href="{{ route($detailedCreditNoteRoute, $invoice->id) }}?type=full" class="block px-3 py-2 text-[12.5px] text-slate-700 hover:bg-slate-50 rounded-lg mx-1">{{ __('messages.Full') }}</a></li>
                                                    @elseif($creditNoteRoute)
                                                        <li><a href="#" class="block px-3 py-2 text-[12.5px] text-slate-700 hover:bg-slate-50 rounded-lg mx-1" @click.prevent="$dispatch('open-partial-credit-modal', { invoiceId: {{ $invoice->id }}, totalCost: {{ $invoice->total_ttc }} }); open = false;">{{ __('messages.Partial') }}</a></li>
                                                        <li><a href="{{ route($creditNoteRoute, $invoice->id) }}?type=full" class="block px-3 py-2 text-[12.5px] text-slate-700 hover:bg-slate-50 rounded-lg mx-1">{{ __('messages.Full') }}</a></li>
                                                    @endif
                                                </ul>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="py-8 text-center">
                                        <div class="flex flex-col items-center gap-3">
                                            <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center">
                                                <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                                                </svg>
                                            </div>
                                            <p class="text-[13px] font-medium text-slate-500">{{ __('messages.No invoices found') }}</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    </div>
                </div>
                @endif
                @endcan

                @can('view devis')
                @if($devis !== null)
                @php
                    $Total_Accpeted = \App\Models\devis_history::where('entreprise_id', Auth::user()->entreprise_id)->where('project_id',$project->id)->where('acceptanceStatus', 1)->sum('budget');
                    $devisRoutePrefix = 'admin.';
                    if (Auth::user()->hasRole('Responsable de projet')) $devisRoutePrefix = 'Responsable_de_projet.';
                    elseif (Auth::user()->hasRole('Responsable de projet et gestionnaire')) $devisRoutePrefix = 'Responsable_gestionnaire.';
                    elseif (Auth::user()->hasRole('Employee')) $devisRoutePrefix = 'employee.';
                    $devisClientIds = $devis->pluck('client_id')->filter();
                    $devisClients = \App\Models\Client::whereIn('id', $devisClientIds)->get()->keyBy('id');
                    $devisIds = $devis->pluck('id');
                    $devisInvoicesTotal = \App\Models\invoices_history::where('project_id', $project->id)
                        ->whereIn('devis_id', $devisIds)
                        ->where('Payment_status', '!=', -1)
                        ->select('devis_id', \DB::raw('SUM(total_htva) as total'))
                        ->groupBy('devis_id')
                        ->get()
                        ->keyBy('devis_id');
                    $singleDevisOnProject = $devis->count() === 1;
                @endphp
                <div class="dt-card overflow-hidden">
                    <div class="px-5 py-3.5 border-b border-slate-100 flex flex-col sm:flex-row justify-between items-center gap-3">
                        <div class="flex items-center justify-between gap-3 w-full">
                            <p class="text-[13px] font-semibold text-[#0B2638]">{{ __('messages.Devis') }}</p>
                            <button type="button" class="section-toggle"
                                    data-target="projectDevisBody" aria-expanded="false">
                                <span class="hidden lg:inline">{{ __('messages.Show') ?? 'Show' }}</span>
                                <svg class="section-chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6" />
                                </svg>
                            </button>
                        </div>
                        <div class="text-right mt-2 sm:mt-0">
                            <span class="text-[12.5px] font-medium text-slate-700">{{ __('messages.TOTAL ACCEPTED') }}: </span>
                            <strong class="text-emerald-600">€{{ number_format($Total_Accpeted, 2, ',', ' ') }}</strong>
                        </div>
                    </div>
                    <div id="projectDevisBody" class="hidden">
                    <div class="overflow-x-auto" style="-webkit-overflow-scrolling: touch;">
                        <table class="w-full table-auto" id="devisTable">
                            <thead>
                                <tr>
                                    <th class="text-left pl-5">{{ __('messages.devis N') }}</th>
                                    <th class="text-left">{{ __('messages.Client Name / Emails') }}</th>
                                    <th class="text-right whitespace-nowrap">{{ __('messages.Budget label devis') }}</th>
                                    <th class="text-right whitespace-nowrap">{{ __('messages.Billed Budget') ?? 'Budget Facturé' }}</th>
                                    <th class="text-right whitespace-nowrap">{{ __('messages.Unbilled Budget') }}</th>
                                    <th class="text-center whitespace-nowrap">{{ __('messages.Created at') }}</th>
                                    <th class="text-center whitespace-nowrap">{{ __('messages.Acceptance') }}</th>
                                    <th class="text-center">{{ __('messages.Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($devis as $d)
                                <tr class="hover:bg-slate-50/70">
                                    <td data-label="{{ __('messages.devis N') }}" data-span="2" class="pl-5 text-sm font-semibold whitespace-nowrap">
                                        <a href="{{ route($devisRoutePrefix . 'devis.downloadDevis', $d->devis_number) }}" class="text-[12.5px] font-semibold text-[#2D7080] hover:text-[#0B2638] hover:underline">
                                            {{$entreprise_infos->devis_prefix}}-{{$d->devis_number}}
                                        </a>
                                    </td>
                                    <td data-label="{{ __('messages.Client Name / Emails') }}" data-span="2" class="text-[12.5px] text-slate-700 whitespace-normal break-words">
                                        @php $client = $devisClients->get($d->client_id); @endphp
                                        @if (!$client)
                                            @foreach (explode(',', $d->emails) as $email)
                                                <span class="block">{{ $email }}</span>
                                            @endforeach
                                        @else
                                            {{$client->name}}
                                        @endif
                                    </td>
                                    <td data-label="{{ __('messages.Budget label devis') }}" class="text-[12.5px] font-medium text-slate-900 text-right whitespace-nowrap">€{{ number_format($d->budget, 2, ',', ' ') }}</td>
                                    @php
                                        $totalBilledForDevis = $singleDevisOnProject
                                            ? $totalBilled
                                            : (float) ($devisInvoicesTotal->get($d->id)?->total ?? 0);
                                        $unbilledBudget = ($d->budget ?? 0) - $totalBilledForDevis;
                                    @endphp
                                    <td data-label="{{ __('messages.Billed Budget') ?? 'Budget Facturé' }}" class="text-[12.5px] font-medium text-slate-900 text-right whitespace-nowrap">
                                        €{{ number_format($totalBilledForDevis, 2, ',', ' ') }}
                                    </td>
                                    <td data-label="{{ __('messages.Unbilled Budget') }}" class="text-[12.5px] font-medium text-slate-900 text-right whitespace-nowrap">
                                        €{{ number_format(max($unbilledBudget, 0), 2, ',', ' ') }}
                                    </td>
                                    <td data-label="{{ __('messages.Created at') }}" class="text-[12px] text-slate-500 text-center whitespace-nowrap">{{$d->created_at->format('Y-m-d')}}</td>
                                    <td data-label="{{ __('messages.Acceptance') }}" class="text-center whitespace-nowrap">
                                        @if($d->acceptanceStatus == 1)
                                            <span class="dt-badge dt-badge-success"><span class="dt-dot"></span>{{ __('messages.Accepted') }}</span>
                                        @elseif($d->acceptanceStatus == 2)
                                            <span class="dt-badge dt-badge-danger"><span class="dt-dot"></span>{{ __('messages.Declined') }}</span>
                                        @else
                                            <span class="dt-badge dt-badge-warning"><span class="dt-dot"></span>{{ __('messages.Pending') }}</span>
                                        @endif
                                    </td>
                                    <td data-label="{{ __('messages.Actions') }}" data-span="2">
                                        <div class="devis-actions flex flex-wrap items-center justify-center gap-1.5">
                                            <a href="{{ route($devisRoutePrefix . 'devis.downloadDevis', $d->devis_number) }}" class="act-btn act-btn-send" title="{{ __('messages.Download') }}">
                                                <svg class="act-btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                                </svg>
                                                {{ __('messages.Download') }}
                                            </a>
                                            @can('Send devis')
                                            <form action="{{ route($devisRoutePrefix . 'devis.sendDevis') }}" method="post" class="inline-block">
                                                @csrf
                                                <input type="hidden" name="devis_id" value="{{ $d->id }}">
                                                <button type="submit" class="act-btn act-btn-send" title="{{ __('messages.Send') }}">
                                                    <svg class="act-btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                                    </svg>
                                                    {{ __('messages.Send') }}
                                                </button>
                                            </form>
                                            @endcan
                                            @can('Delete devis')
                                            <form action="{{route($devisRoutePrefix . 'devis.destroy', $d->id)}}" method="post" onsubmit="return confirm('{{ __('messages.Are you sure?') }}');" class="inline-block">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="act-btn act-btn-delete" title="{{ __('messages.Delete') }}">
                                                    <svg class="act-btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                    {{ __('messages.Delete') }}
                                                </button>
                                            </form>
                                            @endcan
                                            <a href="{{route($devisRoutePrefix . 'devis.editDevis', $d->id)}}" class="act-btn act-btn-edit" title="{{ __('messages.Edit Devis') }}">
                                                <svg class="act-btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                                {{ __('messages.Edit Devis') }}
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="py-8 text-center">
                                        <div class="flex flex-col items-center gap-3">
                                            <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center">
                                                <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                </svg>
                                            </div>
                                            <p class="text-[13px] font-medium text-slate-500">{{ __('messages.No devis found') }}</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    </div>
                </div>
                @endif
                @endcan

                @if(Auth::user()->hasRole('Admin') || Auth::user()->hasRole('Super Admin'))
                @php
                    $fileRoutePrefix = 'admin.';
                @endphp
                <div class="dt-card overflow-hidden">
                    <div class="px-5 py-3.5 border-b border-slate-100 flex items-center justify-between gap-3">
                        <p class="text-[13px] font-semibold text-[#0B2638]">{{ __('messages.Project Files') }}</p>
                        <button type="button" class="section-toggle"
                                data-target="projectFilesBody" aria-expanded="false">
                            <span class="hidden lg:inline">{{ __('messages.Show') ?? 'Show' }}</span>
                            <svg class="section-chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6" />
                            </svg>
                        </button>
                    </div>

                    <div id="projectFilesBody" class="hidden">
                    <div class="p-4 sm:p-5">
                        <form action="{{ route($fileRoutePrefix . 'projects.uploadFile', $project->id) }}" method="POST" enctype="multipart/form-data" class="flex flex-col sm:flex-row sm:items-center gap-3">
                            @csrf
                            <input type="file" name="file" required class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-[#2D7080]/10 file:text-[#2D7080] hover:file:bg-[#2D7080]/20">
                            <button type="submit" class="dt-btn dt-btn-teal w-full sm:w-auto justify-center">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                                {{ __('messages.Upload') }}
                            </button>
                        </form>
                    </div>

                    <div class="px-4 sm:px-5 pb-4 sm:pb-5">
                        <div class="rounded-xl border border-slate-200 overflow-hidden bg-white">
                            <div class="overflow-x-auto" style="-webkit-overflow-scrolling: touch;">
                                <table class="project-files-table w-full table-auto">
                                    <thead>
                                        <tr>
                                            <th class="text-left pl-5">{{ __('messages.File Name') }}</th>
                                            <th class="project-files-uploaded-col text-left">{{ __('messages.Uploaded At') }}</th>
                                            <th class="project-files-actions-col text-center">{{ __('messages.Action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($Project_files as $file)
                                        <tr class="hover:bg-slate-50/70">
                                            <td class="pl-5 text-[12.5px] text-slate-900 whitespace-normal break-words">
                                                <a href="{{ route($fileRoutePrefix . 'projects.downloadfiles', $file->file_name) }}" class="text-[#2D7080] hover:text-[#0B2638] hover:underline">{{ $file->file_name }}</a>
                                            </td>
                                            <td class="project-files-uploaded-col text-[12px] text-slate-500 whitespace-nowrap">
                                                {{ $file->created_at->format('Y-m-d H:i') }}
                                            </td>
                                            <td class="project-files-actions-col text-center">
                                                <div class="flex flex-wrap items-center justify-center gap-2">
                                                    <a href="{{ route($fileRoutePrefix . 'projects.downloadfiles', $file->file_name) }}" class="act-btn act-btn-send" title="{{ __('messages.Download') }}">
                                                        <svg class="act-btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                                        </svg>
                                                        {{ __('messages.Download') }}
                                                    </a>
                                                    <form action="{{ route($fileRoutePrefix . 'projects.deletefiles', $file->file_name) }}" method="POST" onsubmit="return confirm('{{ __('messages.Are you sure?') }}');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="act-btn act-btn-delete" title="{{ __('messages.Delete') }}">
                                                            <svg class="act-btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                            </svg>
                                                            {{ __('messages.Delete') }}
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="3" class="py-8 text-center">
                                                <div class="flex flex-col items-center gap-3">
                                                    <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center">
                                                        <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                                                        </svg>
                                                    </div>
                                                    <p class="text-[13px] font-medium text-slate-500">{{ __('messages.No files uploaded') }}</p>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    </div>
                </div>
                @endif

                @can('view contacts')
                @php
                    $project_contacts=App\Models\client_contacts_projects::where('project_id',$project->id)->get();
                    $contactIds = $project_contacts->pluck('contact_id');
                    $contacts = App\Models\client_contacts::whereIn('id', $contactIds)->get();
                @endphp
                <div class="dt-card overflow-hidden">
                    <div class="px-5 py-3.5 border-b border-slate-100">
                        <p class="text-[13px] font-semibold text-[#0B2638]">{{ __('messages.Contacts Infos') }}</p>
                        <p class="text-[12px] text-slate-500 mt-0.5">{{ __('messages.Mandatory informations') }}</p>
                    </div>
                    <div class="divide-y divide-slate-100">
                        @foreach ($contacts as $contact)
                        <div x-data="{ open: false }">
                            <button @click="open = !open" class="w-full flex justify-between items-center gap-3 px-5 py-3.5 text-left hover:bg-slate-50/70 transition-colors">
                                <div class="flex items-center gap-3 min-w-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#2D7080] flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v11.494m-5.747-8.606l-.001 5.72m11.495-5.72l-.001 5.72M3 10.044v5.72c0 1.25.803 2.335 1.95 2.684A19.46 19.46 0 0012 21c2.148 0 4.217-.36 6.05-1.002 1.147-.35 1.95-1.435 1.95-2.685v-5.72c0-1.25-.803-2.335-1.95-2.684A19.46 19.46 0 0012 6.309a19.463 19.463 0 00-6.05 1.051C4.803 7.71 4 8.794 4 10.044z" /></svg>
                                    <span class="text-[13px] font-semibold text-[#0B2638] truncate">{{ ucfirst($contact->name) }}</span>
                                </div>
                                <svg x-bind:class="{ 'rotate-180': open }" class="h-4 w-4 text-slate-400 transform transition-transform flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                            </button>
                            <div x-show="open" x-transition class="px-5 py-4 bg-slate-50/60 border-t border-slate-100">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-[11px] font-semibold uppercase tracking-[.07em] text-slate-400 mb-1">{{ __('messages.Full Name') }}</label>
                                        <input class="block w-full rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3" type="text" value="{{$contact->name}}" readonly />
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-semibold uppercase tracking-[.07em] text-slate-400 mb-1">{{ __('messages.Functionality') }}</label>
                                        <input class="block w-full rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3" type="text" value="{{$contact->functionality}}" readonly />
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-semibold uppercase tracking-[.07em] text-slate-400 mb-1">{{ __('messages.Mobile Fix') }}</label>
                                        <input class="block w-full rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3" type="text" value="{{$contact->mobile_fix}}" readonly />
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-semibold uppercase tracking-[.07em] text-slate-400 mb-1">{{ __('messages.Email') }}</label>
                                        <input class="block w-full rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3" type="text" value="{{$contact->email}}" readonly />
                                    </div>
                                    <div class="sm:col-span-2">
                                        <label class="block text-[11px] font-semibold uppercase tracking-[.07em] text-slate-400 mb-1">{{ __('messages.Address') }}</label>
                                        <input class="block w-full rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3" type="text" value="{{$contact->address}}" readonly />
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endcan

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($teams as $team)
                    <div class="dt-card overflow-hidden flex flex-col">
                        <div class="px-5 py-3.5 border-b border-slate-100 flex justify-between items-start gap-3">
                            <div class="min-w-0">
                                <h5 class="text-[13px] font-bold text-[#0B2638] capitalize truncate">{{$team->team_name}}</h5>
                                <p class="text-[12px] font-medium text-slate-500">{{ __('messages.Team Members:') }}</p>
                            </div>
                            @php $teamRoute = '#'; @endphp
                            @if(Auth::user()->hasRole('Admin')) @php $teamRoute = route('admin.teams.index'); @endphp @endif
                            @if(Auth::user()->hasRole('Responsable de projet')) @php $teamRoute = route('Responsable_de_projet.teams.index'); @endphp @endif
                            @if(Auth::user()->hasRole('Employee')) @php $teamRoute = route('employee.teams.index'); @endphp @endif
                            @if(Auth::user()->hasRole('Responsable de projet et gestionnaire')) @php $teamRoute = route('Responsable_gestionnaire.teams.index'); @endphp @endif
                            <a href="{{ $teamRoute }}" class="act-btn act-btn-send flex-shrink-0">{{ __('messages.View') }}</a>
                        </div>

                        <div class="px-5 py-4 space-y-3">
                            @foreach(App\Models\team_members::where('team_id', $team->team_id)->get() as $team_member)
                            @php
                                $employee = App\Models\Employees::where('id', $team_member->employee_id)->first();
                                $employee_user = App\Models\User::where('id', $employee->user_id)->first();
                                $colors = ['#2ECC71', '#BDC3C7', '#F39C12', '#E74C3C', '#9B59B6', '#2E86C1','#641E16','#2C3E50','#F4D03F','#F4D03F'];
                                $color = $colors[$loop->index % count($colors)];
                                $initials = strtoupper(substr($team_member->employee_name, 0, 1));
                                $employeeShowRoute = '#';
                                if(Auth::user()->hasRole('Admin')) $employeeShowRoute = route('admin.employees.show', $team_member->employee_id);
                                elseif(Auth::user()->hasRole('Responsable de projet')) $employeeShowRoute = route('Responsable_de_projet.employees.show', $team_member->employee_id);
                                elseif(Auth::user()->hasRole('Employee')) $employeeShowRoute = route('employee.employees.show', $team_member->employee_id);
                                elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire')) $employeeShowRoute = route('Responsable_gestionnaire.employees.show', $team_member->employee_id);
                            @endphp
                            <div class="flex items-center justify-between gap-2">
                                <div class="flex items-center min-w-0">
                                    @if($employee->image)
                                        <img class="h-9 w-9 rounded-full mr-3 object-cover ring-1 ring-slate-200 flex-shrink-0" src="{{ Storage::disk('public')->url('employee_images/' . $employee->image) }}" alt="employee Image">
                                    @else
                                        <div class="h-9 w-9 rounded-full mr-3 flex items-center justify-center text-white font-semibold flex-shrink-0" style="background-color: {{ $color }};">
                                            <span class="text-sm">{{ $initials }}</span>
                                        </div>
                                    @endif
                                    <div class="min-w-0">
                                        <a href="{{ $employeeShowRoute }}" class="text-[13px] font-semibold text-[#0B2638] hover:text-[#2D7080] truncate block">{{$team_member->employee_name}}</a>
                                        @if($employee_user->is_loggedIn == 0)
                                            <span class="dt-badge dt-badge-neutral mt-1"><span class="dt-dot"></span>{{ __('messages.Off Work') }}</span>
                                        @elseif($employee_user->is_loggedIn == 1)
                                            <span class="dt-badge dt-badge-success mt-1"><span class="dt-dot"></span>{{ __('messages.On Work') }}</span>
                                        @else
                                            <span class="dt-badge dt-badge-warning mt-1"><span class="dt-dot"></span>{{ __('messages.On Pause') }}</span>
                                        @endif
                                    </div>
                                </div>
                                @can('View employee')
                                <a href="{{ $employeeShowRoute }}" class="act-btn act-btn-send flex-shrink-0">{{ __('messages.View') }}</a>
                                @endcan
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>

            </div>
        </main>
    </div>

    <div x-data="editDateModal()" x-cloak x-show="show"
         @open-edit-date-modal.window="open($event.detail)"
         class="dt-modal">
        <div @click.outside="show = false" class="dt-modal-content dt-modal-content-custom bg-white rounded-2xl shadow-xl w-full max-w-md mx-4">
            <div class="flex items-center justify-between px-5 sm:px-6 py-4 border-b border-slate-100">
                <h5 class="text-[15px] font-bold text-[#0B2638]">{{ __('messages.Edit Date and Time') }}</h5>
                <button @click="show = false" type="button" class="text-slate-400 hover:text-slate-600 text-2xl leading-none">&times;</button>
            </div>
            <div class="px-5 sm:px-6 py-5 space-y-4">
                <div>
                    <label for="dateInput" class="block text-[13px] font-semibold text-slate-700 mb-1.5">{{ __('messages.Date') }}</label>
                    <input type="date" x-model="date" id="dateInput" class="block w-full rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3">
                </div>
                <div>
                    <label for="timeInput" class="block text-[13px] font-semibold text-slate-700 mb-1.5">{{ __('messages.Time') }} (HH:MM)</label>
                    <input type="time" x-model="time" id="timeInput" class="block w-full rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3">
                </div>
            </div>
            <div class="flex items-center justify-end gap-2 px-5 sm:px-6 py-4 bg-slate-50 border-t border-slate-100 rounded-b-2xl">
                <button @click="show = false" type="button" class="dt-btn dt-btn-ghost">{{ __('messages.Close button') }}</button>
                <button @click="save()" type="button" class="dt-btn dt-btn-teal">{{ __('messages.Save changes button') }}</button>
            </div>
        </div>
    </div>

    <div x-data="moveDurationModal()" x-cloak x-show="show"
         @open-move-modal.window="open($event.detail)"
         class="dt-modal">
        <div @click.outside="show = false" class="dt-modal-content dt-modal-content-custom bg-white rounded-2xl shadow-xl w-full max-w-md mx-4">
            <div class="flex items-center justify-between px-5 sm:px-6 py-4 border-b border-slate-100">
                <h5 class="text-[15px] font-bold text-[#0B2638]">{{ __('messages.Move Duration') }}</h5>
                <button @click="show = false" type="button" class="text-slate-400 hover:text-slate-600 text-2xl leading-none">&times;</button>
            </div>
            <div class="px-5 sm:px-6 py-5 space-y-4">
                <div>
                    <label for="projectSelect" class="block text-[13px] font-semibold text-slate-700 mb-1.5">{{ __('messages.Project') }}</label>
                    <select x-model="projectId" @change="loadTasks()" id="projectSelect" class="dt-filter-select block w-full rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3">
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}">{{ $project->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="taskSelect" class="block text-[13px] font-semibold text-slate-700 mb-1.5">{{ __('messages.Task') }}</label>
                    <select x-model="taskId" id="taskSelect" class="dt-filter-select block w-full rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3">
                    </select>
                </div>
                <div>
                    <label for="employeeSelect" class="block text-[13px] font-semibold text-slate-700 mb-1.5">{{ __('messages.Employee') }}</label>
                    <select x-model="employeeId" id="employeeSelect" class="dt-filter-select block w-full rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3">
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="moveStartDate" class="block text-[13px] font-semibold text-slate-700 mb-1.5">{{ __('messages.Start Date') }}</label>
                    <input type="datetime-local" x-model="startDate" @input="calculateDuration()" id="moveStartDate" class="block w-full rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3">
                </div>
                <div>
                    <label for="moveEndDate" class="block text-[13px] font-semibold text-slate-700 mb-1.5">{{ __('messages.End Date') }}</label>
                    <input type="datetime-local" x-model="endDate" @input="calculateDuration()" id="moveEndDate" class="block w-full rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3">
                </div>
                <div>
                    <label for="moveDuration" class="block text-[13px] font-semibold text-slate-700 mb-1.5">{{ __('messages.Duration (HH:MM)') }}</label>
                    <input type="text" x-model="duration" id="moveDuration" @input="validateDurationFormat()"
                           class="block w-full rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3"
                           placeholder="{{ __('messages.Duration placeholder') }}" pattern="^(\d+)h:(\d{2})m$">
                </div>
            </div>
            <div class="flex items-center justify-end gap-2 px-5 sm:px-6 py-4 bg-slate-50 border-t border-slate-100 rounded-b-2xl">
                <button @click="show = false" type="button" class="dt-btn dt-btn-ghost">{{ __('messages.Close button') }}</button>
                <button @click="saveMove()" type="button" class="dt-btn dt-btn-teal">{{ __('messages.Save changes button') }}</button>
            </div>
        </div>
    </div>

    <div x-data="manualTimerModal()" x-cloak x-show="show"
         @open-manual-timer-modal.window="open($event.detail)"
         class="dt-modal">
        <div @click.outside="show = false" class="dt-modal-content dt-modal-content-custom bg-white rounded-2xl shadow-xl w-full max-w-md mx-4">
            <div class="flex items-center justify-between px-5 sm:px-6 py-4 border-b border-slate-100">
                <h5 class="text-[15px] font-bold text-[#0B2638]">{{ __('messages.Add Manual Time') }}</h5>
                <button @click="show = false" type="button" class="text-slate-400 hover:text-slate-600 text-2xl leading-none">&times;</button>
            </div>
            <div class="px-5 sm:px-6 py-5 space-y-4">
                <div>
                    <label for="manualEmployeeSelect" class="block text-[13px] font-semibold text-slate-700 mb-1.5">{{ __('messages.Employee') }}</label>
                    <select x-model="employeeId" id="manualEmployeeSelect" class="dt-filter-select block w-full rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3">
                        <option value="">{{ __('messages.Select Employee') }}</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="manualStartDate" class="block text-[13px] font-semibold text-slate-700 mb-1.5">{{ __('messages.Start Date and Time') }}</label>
                    <input type="datetime-local" x-model="startDate" id="manualStartDate" class="block w-full rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3">
                </div>
                <div>
                    <label for="manualEndDate" class="block text-[13px] font-semibold text-slate-700 mb-1.5">{{ __('messages.End Date and Time') }}</label>
                    <input type="datetime-local" x-model="endDate" id="manualEndDate" class="block w-full rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3">
                </div>
            </div>
            <div class="flex items-center justify-end gap-2 px-5 sm:px-6 py-4 bg-slate-50 border-t border-slate-100 rounded-b-2xl">
                <button @click="show = false" type="button" class="dt-btn dt-btn-ghost">{{ __('messages.Close button') }}</button>
                <button @click="save()" type="button" class="dt-btn dt-btn-teal">{{ __('messages.Save Timer') }}</button>
            </div>
        </div>
    </div>

    <div x-data="exportReportModal()" x-cloak x-show="show"
         @open-export-modal.window="open($event.detail.isCustomer)"
         class="dt-modal">
        <div @click.outside="show = false" class="dt-modal-content dt-modal-content-custom bg-white rounded-2xl shadow-xl w-full max-w-md mx-4">
            <div class="flex items-center justify-between px-5 sm:px-6 py-4 border-b border-slate-100">
                <h5 class="text-[15px] font-bold text-[#0B2638]" x-text="isCustomerReport ? @js(__('messages.Export for Customer')) : @js(__('messages.Export Report'))"></h5>
                <button @click="show = false" type="button" class="text-slate-400 hover:text-slate-600 text-2xl leading-none">&times;</button>
            </div>
            <div class="px-5 sm:px-6 py-5">
                <p class="text-sm text-slate-700">{{ __('messages.Export report date note') }}</p>
            </div>
            <div class="flex items-center justify-end gap-2 px-5 sm:px-6 py-4 bg-slate-50 border-t border-slate-100 rounded-b-2xl">
                <button @click="show = false" type="button" class="dt-btn dt-btn-ghost">{{ __('messages.Cancel') }}</button>
                <button @click="download()" type="button" class="dt-btn dt-btn-teal">{{ __('messages.Download PDF') }}</button>
            </div>
        </div>
    </div>

    <div x-data="sendInvoiceModal()" x-cloak x-show="show"
         @open-send-invoice-modal.window="open($event.detail.invoiceId)"
         class="dt-modal">
        <div x-show="show" x-transition @click.outside="show = false" class="dt-modal-content dt-modal-content-custom bg-white rounded-2xl shadow-xl w-full max-w-lg mx-4">
            <div class="flex items-center justify-between px-5 sm:px-6 py-4 border-b border-slate-100">
                <h5 class="text-[15px] font-bold text-[#0B2638]">{{ __('messages.Send Invoice') }}</h5>
                <button @click="show = false" type="button" class="text-slate-400 hover:text-slate-600 text-2xl leading-none">&times;</button>
            </div>
            <form id="sendInvoiceForm" @submit.prevent="submitForm">
                @csrf
                <div class="px-5 sm:px-6 py-5 space-y-4">
                    <div>
                        <h6 class="text-[13px] font-semibold text-slate-700 mb-2">{{ __('messages.Select Recipients:') }}</h6>
                        <div id="recipientsList" class="space-y-2 p-3 border border-slate-200 rounded-xl bg-slate-50 h-40 overflow-y-auto"></div>
                    </div>
                    <div>
                        <label for="fileUpload" class="block text-[13px] font-semibold text-slate-700 mb-2">{{ __('messages.Upload Additional Files (Optional):') }}</label>
                        <input type="file" class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-[#2D7080]/10 file:text-[#2D7080] hover:file:bg-[#2D7080]/20" id="fileUpload" name="file[]" multiple>
                    </div>
                </div>
                <div class="flex items-center justify-end gap-2 px-5 sm:px-6 py-4 bg-slate-50 border-t border-slate-100 rounded-b-2xl">
                    <button @click="show = false" type="button" class="dt-btn dt-btn-ghost">{{ __('messages.Close button') }}</button>
                    <button type="submit" class="dt-btn dt-btn-teal">{{ __('messages.Send Invoice') }}</button>
                </div>
            </form>
        </div>
    </div>

    <div x-data="partialCreditNoteModal()" x-cloak x-show="show"
         @open-partial-credit-modal.window="open($event.detail.invoiceId, $event.detail.totalCost)"
         class="dt-modal">
        <div x-show="show" x-transition @click.outside="show = false" class="dt-modal-content dt-modal-content-custom bg-white rounded-2xl shadow-xl w-full max-w-md mx-4">
            <div class="flex items-center justify-between px-5 sm:px-6 py-4 border-b border-slate-100">
                <h5 class="text-[15px] font-bold text-[#0B2638]">{{ __('messages.Partial Credit Note') }}</h5>
                <button @click="show = false" type="button" class="text-slate-400 hover:text-slate-600 text-2xl leading-none">&times;</button>
            </div>
            <form @submit.prevent="submitForm">
                <div class="px-5 sm:px-6 py-5">
                    <label for="partialAmount" class="block text-[13px] font-semibold text-slate-700 mb-2">{{ __('messages.Enter Amount:') }}</label>
                    <input type="number" x-model="amount" class="block w-full rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3" id="partialAmount" step="0.0001" required>
                    <span x-show="error" x-text="errorMessage" class="text-red-600 text-sm mt-1"></span>
                </div>
                <div class="flex items-center justify-end gap-2 px-5 sm:px-6 py-4 bg-slate-50 border-t border-slate-100 rounded-b-2xl">
                    <button @click="show = false" type="button" class="dt-btn dt-btn-ghost">{{ __('messages.Close button') }}</button>
                    <button type="submit" class="dt-btn dt-btn-teal">{{ __('messages.Submit') }}</button>
                </div>
            </form>
        </div>
    </div>

    <div x-data="moveTaskModal()" x-cloak x-show="show"
         @open-move-task-modal.window="open($event.detail.taskId, $event.detail.currentProjectId)"
         class="dt-modal">
        <div @click.outside="show = false" class="dt-modal-content dt-modal-content-custom bg-white rounded-2xl shadow-xl w-full max-w-md mx-4">
            <div class="flex items-center justify-between px-5 sm:px-6 py-4 border-b border-slate-100">
                <h5 class="text-[15px] font-bold text-[#0B2638]">{{ __('messages.Move Task') }}</h5>
                <button @click="show = false" type="button" class="text-slate-400 hover:text-slate-600 text-2xl leading-none">&times;</button>
            </div>
            <div class="px-5 sm:px-6 py-5 space-y-4">
                <div>
                    <label for="moveProjectSelect" class="block text-[13px] font-semibold text-slate-700 mb-1.5">{{ __('messages.Select Project label') }}</label>
                    <select x-model="selectedProjectId" id="moveProjectSelect" class="dt-filter-select block w-full rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3">
                        <option value="">{{ __('messages.Select a project option') }}</option>
                        <template x-for="proj in availableProjects" :key="proj.id">
                            <option :value="proj.id" x-text="proj.name"></option>
                        </template>
                    </select>
                </div>
                <p class="text-sm text-slate-600">{{ __('messages.Move task description') }}</p>
            </div>
            <div class="flex items-center justify-end gap-2 px-5 sm:px-6 py-4 bg-slate-50 border-t border-slate-100 rounded-b-2xl">
                <button @click="show = false" type="button" class="dt-btn dt-btn-ghost">{{ __('messages.Cancel') }}</button>
                <button @click="moveTask()" type="button" class="dt-btn dt-btn-teal">{{ __('messages.Move Task') }}</button>
            </div>
        </div>
    </div>

    <div id="sendDevisModal" class="dt-modal">
        <div class="dt-modal-content dt-modal-content-custom bg-white rounded-2xl shadow-xl w-full max-w-lg mx-4">
            <div class="flex items-center justify-between px-5 sm:px-6 py-4 border-b border-slate-100">
                <h5 class="text-[15px] font-bold text-[#0B2638]" id="sendDevisModalLabel">{{ __('messages.Send Devis') }}</h5>
                <button type="button" id="closeSendDevisModal" class="text-slate-400 hover:text-slate-600 text-2xl leading-none">&times;</button>
            </div>
            <div class="px-5 sm:px-6 py-5">
                <form id="sendDevisForm" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="devis_id" id="devis_id">

                    <div class="mb-4">
                        <label class="block text-[13px] font-semibold text-slate-700 mb-2">{{ __('messages.Select Recipients Devis') }}</label>
                        <div id="devisRecipientsList" class="border border-slate-200 p-3 rounded-xl h-32 overflow-y-auto bg-slate-50 space-y-2">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="attachments" class="block text-[13px] font-semibold text-slate-700 mb-2">{{ __('messages.Additional Attachments (PDF only):') }}</label>
                        <input type="file" class="block w-full text-sm text-slate-700 border border-slate-200 rounded-xl cursor-pointer bg-slate-50 focus:outline-none file:bg-slate-100 file:border-0 file:px-3 file:py-2" id="attachments" name="attachments[]" multiple accept=".pdf">
                        <small class="text-slate-500">{{ __('messages.Select PDF files to be merged with the devis') }}</small>
                    </div>
                </form>
            </div>
            <div class="flex justify-end items-center gap-2 px-5 sm:px-6 py-4 bg-slate-50 border-t border-slate-100 rounded-b-2xl">
                <button type="button" id="cancelSendDevisModal" class="dt-btn dt-btn-ghost">{{ __('messages.Close button') }}</button>
                <button type="button" class="dt-btn dt-btn-teal" id="confirmSend">{{ __('messages.Send Devis') }}</button>
            </div>
        </div>
    </div>

    @include('dashboard_layouts.scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.3.1/jspdf.umd.min.js"></script>
    <script src="https://unpkg.com/jspdf-autotable@3.5.17/dist/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.4/xlsx.full.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        // Alpine components are registered in the head section
        // This section is for other page logic only

        // --- PAGE SCRIPT LOGIC ---
        document.addEventListener('DOMContentLoaded', function() {

            // --- Comment editing (in-place) ---
            document.querySelectorAll('.editable-comment').forEach(function(cell) {
                cell.addEventListener('dblclick', function() {
                    var span = cell.querySelector('.comment-text');
                    if (!span) return;
                    var originalText = span.textContent || '';
                    if (cell.getAttribute('data-editing') === '1') return;
                    cell.setAttribute('data-editing', '1');
                    var input = document.createElement('textarea');
                    input.className = 'block w-full rounded-lg border-gray-300 shadow-sm sm:text-sm py-1 px-2';
                    input.value = originalText;
                    input.rows = 2;
                    span.style.display = 'none';
                    cell.appendChild(input);
                    input.focus();
                    input.select();
                    var save = function(newValue) {
                        var trimmed = (newValue || '').trim();
                        if (trimmed === (originalText || '').trim()) { cancel(); return; }
                        var url = cell.getAttribute('data-update-url');
                        var field = cell.getAttribute('data-field') || 'comment';
                        var token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                        fetch(url, {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': token, 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: `${field}=${encodeURIComponent(trimmed)}`
                        }).then(resp => resp.json())
                        .then(json => {
                            if (json.status === 'success') span.textContent = trimmed;
                            else { span.textContent = originalText; alert(json.message || @js(__('messages.Failed to update.'))); }
                        }).catch(err => { span.textContent = originalText; alert(@js(__('messages.Network error'))); })
                        .finally(() => cleanup());
                    };
                    var cancel = () => cleanup();
                    var cleanup = () => {
                        if (input && input.parentNode) input.parentNode.removeChild(input);
                        span.style.display = '';
                        cell.removeAttribute('data-editing');
                    };
                    input.addEventListener('keydown', (e) => {
                        if (e.key === 'Enter' && (e.ctrlKey || e.metaKey)) { e.preventDefault(); save(input.value); }
                        else if (e.key === 'Escape') { e.preventDefault(); cancel(); }
                    });
                    input.addEventListener('blur', () => save(input.value));
                });
            });

            // Task search filter
            document.getElementById('taskSearchInput').addEventListener('keyup', function() {
                const searchTerm = this.value.toLowerCase();
                const tableRows = document.querySelectorAll('#taskTable tbody tr');

                tableRows.forEach(row => {
                    // [FIX] Changed this condition. The 'data-task-id' attribute didn't exist.
                    // We only need to skip the 'No tasks found' row, which has 1 cell (due to colspan).
                    // All valid task rows have 11 cells.
                    if (row.cells.length < 11) {
                        return; // Skip 'No tasks found' row
                    }
                    const taskTitle = row.cells[0]?.textContent || '';
                    const taskDescription = row.cells[1]?.textContent || '';

                    if (taskTitle.toLowerCase().includes(searchTerm) || taskDescription.toLowerCase().includes(searchTerm)) {
                        row.style.display = ""; // Show row
                    } else {
                        row.style.display = "none"; // Hide row
                    }
                });
            });

            // [FIX] Removed broken Excel export button code

            @can('Update project')
            // In-place editing for project details
            $('#editableTable .editable').dblclick(function(){
                var originalContent = $(this).text().trim();
                var width = $(this).width();
                $(this).html("<input type='text' value='" + originalContent + "' style='width: 100%; min-width: " + width + "px;' class='block rounded-lg border-gray-300 shadow-sm sm:text-sm py-1 px-2' />");
                var input = $(this).children().first();
                input.focus();
                input[0].setSelectionRange(0, 0);

                input.blur(function(){
                    var newContent = $(this).val();
                    var clientId = $(this).parent().data('id');
                    var columnName = $(this).parent().data('column');
                    $(this).parent().text(newContent);

                    var updatedUrl = '';
                    @if(Auth::user()->hasRole('Responsable de projet'))
                        updatedUrl = '/Responsable_de_projet/projects/updateProject';
                    @elseif(Auth::user()->hasRole('Admin'))
                        updatedUrl = '/admin/projects/updateProject';
                    @endif

                    $.ajax({
                        url: updatedUrl,
                        type: 'post',
                        data: {
                            content: newContent,
                            id: clientId,
                            column: columnName,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            location.reload();
                        }
                    });
                });
            });
            @endcan

            // --- Send Devis Modal Logic ---
            const sendDevisModalEl = document.getElementById('sendDevisModal');
            const closeSendDevisModalBtn = document.getElementById('closeSendDevisModal');
            const cancelSendDevisModalBtn = document.getElementById('cancelSendDevisModal');

            if (closeSendDevisModalBtn) {
                closeSendDevisModalBtn.addEventListener('click', () => {
                    if (sendDevisModalEl) sendDevisModalEl.classList.add('hidden');
                });
            }
            if (cancelSendDevisModalBtn) {
                cancelSendDevisModalBtn.addEventListener('click', () => {
                    if (sendDevisModalEl) sendDevisModalEl.classList.add('hidden');
                });
            }

            function handleSendClick(event) {
                event.preventDefault();
                const form = event.target.closest('form');
                const devisId = form.querySelector('input[name="devis_id"]').value;
                const formAction = form.getAttribute('action');

                document.getElementById('devis_id').value = devisId;
                const recipientsList = document.getElementById('devisRecipientsList');
                if (!recipientsList) {
                    console.error('devisRecipientsList not found');
                    return;
                }
                recipientsList.innerHTML = '<div class="text-center">{{ __('messages.Loading...') }}</div>';

                // This URL should be dynamic based on user role
                let contactsUrl = '';
                @if(Auth::user()->hasRole('Admin'))
                contactsUrl = `/admin/devis/get-contacts/${devisId}`;
                @elseif(Auth::user()->hasRole('Responsable de projet'))
                contactsUrl = `/Responsable_de_projet/devis/get-contacts/${devisId}`;
                @elseif(Auth::user()->hasRole('Employee'))
                contactsUrl = `/employee/devis/get-contacts/${devisId}`;
                @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                contactsUrl = `/Responsable_gestionnaire/devis/get-contacts/${devisId}`;
                @endif

                document.getElementById('sendDevisForm').action = formAction;

                fetch(contactsUrl)
                    .then(response => response.json())
                    .then(data => {
                        recipientsList.innerHTML = '';
                        if (data.contacts && data.contacts.length > 0) {
                            data.contacts.forEach(contact => {
                                const div = document.createElement('div');
                                div.className = 'flex items-center';
                                div.innerHTML = `
                                    <input class="h-4 w-4 rounded border-slate-300 text-[#2D7080] focus:ring-[#2D7080]/30" type="checkbox" name="recipients[]" value="${contact.email}" id="recipient_${contact.id}" checked>
                                    <label class="ml-2 block text-sm text-slate-900" for="recipient_${contact.id}">
                                        ${contact.name} (${contact.email})
                                    </label>`;
                                recipientsList.appendChild(div);
                            });
                        } else {
                            recipientsList.innerHTML = '<p class="text-slate-500">{{ __('messages.No contacts found for this client') }}</p>';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        recipientsList.innerHTML = '<p class="text-red-600">{{ __('messages.Error loading contacts') }}</p>';
                    });

                if (sendDevisModalEl) sendDevisModalEl.classList.remove('hidden');
            }

            document.querySelectorAll('form[action*="sendDevis"]').forEach(form => {
                const submitButton = form.querySelector('button[type="submit"]');
                if (submitButton) {
                    submitButton.addEventListener('click', handleSendClick);
                }
            });

            // Handle confirm send button
            const confirmSendBtn = document.getElementById('confirmSend');
            if (confirmSendBtn) {
                confirmSendBtn.addEventListener('click', function() {
                const form = document.getElementById('sendDevisForm');
                const devisId = document.getElementById('devis_id').value;
                const selectedRecipients = Array.from(document.querySelectorAll('input[name="recipients[]"]:checked'))
                    .map(checkbox => checkbox.value);

                if (selectedRecipients.length === 0) {
                    alert(@js(__('messages.Please select at least one recipient')));
                    return;
                }

                const formData = new FormData();
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                formData.append('_token', csrfToken);
                formData.append('devis_id', devisId);
                formData.append('recipients', JSON.stringify(selectedRecipients));

                const attachments = document.getElementById('attachments').files;
                for (let i = 0; i < attachments.length; i++) {
                    formData.append('attachments[]', attachments[i]);
                }

                fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(response => {
                    if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        alert(@js(__('messages.Devis sent successfully!')));
                        if (sendDevisModalEl) sendDevisModalEl.classList.add('hidden');
                        location.reload();
                    } else {
                        alert(@js(__('messages.Error sending devis: ')) + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert(@js(__('messages.An error occurred while sending the devis')));
                });
            });
            }

        });
    </script>

    <script>
        // Section collapse/expand (Tasks / Invoices / Devis / Files)
        document.addEventListener('DOMContentLoaded', function () {
            const isMobile = () => window.matchMedia && window.matchMedia('(max-width: 1024px)').matches;

            function setExpanded(btn, expanded) {
                const targetId = btn.getAttribute('data-target');
                const panel = targetId ? document.getElementById(targetId) : null;
                if (!panel) return;

                btn.setAttribute('aria-expanded', expanded ? 'true' : 'false');
                panel.classList.toggle('hidden', !expanded);

                const chevron = btn.querySelector('.section-chevron');
                if (chevron) chevron.classList.toggle('rotate-180', expanded);
            }

            document.querySelectorAll('button.section-toggle[data-target]').forEach(btn => {
                // Mobile: collapsed by default. Desktop: expanded by default.
                setExpanded(btn, !isMobile());

                btn.addEventListener('click', () => {
                    const isExpanded = btn.getAttribute('aria-expanded') === 'true';
                    setExpanded(btn, !isExpanded);
                });
            });
        });
    </script>

    <div id="stopCommentModal" class="dt-modal">
        <div class="dt-modal-content dt-modal-content-custom">
            <span id="stopCommentClose" class="dt-close-button">&times;</span>
            <div class="flex items-center justify-center mb-3">
                <div class="bg-red-100 rounded-full p-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                    </svg>
                </div>
            </div>
            <h2 class="text-[15px] font-bold text-[#0B2638] mb-2">{{ __('messages.Stop Task') }}</h2>
            <p class="text-sm text-slate-500 mb-4">{{ __('messages.Please leave a comment describing the work done before stopping the task.') }}</p>
            <div class="text-left mb-4">
                <div class="flex justify-between items-center mb-1">
                    <label for="stopCommentText" class="block text-[13px] font-semibold text-slate-700">{{ __('messages.Comment') }} <span class="text-red-500">*</span></label>
                    <div class="flex gap-3 items-center">
                        <button type="button" id="stopCommentAiBtn" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 flex items-center gap-1 transition-colors opacity-50 cursor-not-allowed" title="{{ __('messages.Correct with AI') }}" disabled>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                            </svg>
                            <span id="stopCommentAiBtnText">{{ __('messages.Correct with AI') }}</span>
                        </button>
                        <button type="button" id="stopCommentTranslateBtn" class="text-xs font-semibold text-blue-600 hover:text-blue-800 flex items-center gap-1 transition-colors opacity-50 cursor-not-allowed" title="{{ __('messages.Translate to Dutch') }}" disabled>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5c-.006 3.125-1.026 6.042-2.751 8.498m-1.782-2.5A18.022 18.022 0 014.888 5h7.863z" />
                            </svg>
                            <span id="stopCommentTranslateBtnText">{{ __('messages.Translate to Dutch') }}</span>
                        </button>
                    </div>
                </div>
                <textarea id="stopCommentText" rows="3" maxlength="500" placeholder="{{ __('messages.Describe what you worked on...') }}" spellcheck="true" class="border border-slate-200 rounded-xl px-3 py-2 w-full text-sm focus:outline-none focus:ring-2 focus:ring-red-400 resize-none transition-colors"></textarea>
                <p id="stopCommentError" class="text-xs text-red-500 mt-1 hidden">{{ __('messages.Comment is required to stop the task.') }}</p>
                <p id="stopCommentAiFeedback" class="text-xs mt-1 hidden"></p>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" id="stopCommentCancelBtn" class="dt-btn dt-btn-ghost">{{ __('messages.Cancel') }}</button>
                <button type="button" id="stopCommentConfirmBtn" class="dt-btn dt-btn-primary opacity-50 cursor-not-allowed" disabled>{{ __('messages.Confirm Stop') }}</button>
            </div>
        </div>
    </div>

    <div id="spellCheckModal" class="dt-modal" style="z-index: 60;">
        <div class="dt-modal-content dt-modal-content-custom" style="max-width: 420px;">
            <div class="flex items-center justify-center mb-3">
                <div class="bg-amber-100 rounded-full p-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <h2 class="text-[15px] font-bold text-[#0B2638] mb-2">{{ __('messages.Check Spelling?') }}</h2>
            <p class="text-sm text-slate-500 mb-5">{{ __('messages.Would you like to verify the spelling of your comment before submitting?') }}</p>
            <div class="flex flex-col gap-2">
                <button type="button" id="spellCheckVerifyBtn" class="w-full px-4 py-2.5 text-sm font-medium text-white bg-indigo-500 rounded-lg hover:bg-indigo-600 transition-colors flex items-center justify-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ __('messages.Yes, verify spelling') }}
                </button>
                <button type="button" id="spellCheckSkipBtn" class="w-full px-4 py-2.5 text-sm font-medium text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors">
                    {{ __('messages.No, submit as is') }}
                </button>
            </div>
        </div>
    </div>

    <div id="translateCheckModal" class="dt-modal" style="z-index: 60;">
        <div class="dt-modal-content dt-modal-content-custom" style="max-width: 420px;">
            <div class="flex items-center justify-center mb-3">
                <div class="bg-blue-100 rounded-full p-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5c-.006 3.125-1.026 6.042-2.751 8.498m-1.782-2.5A18.022 18.022 0 014.888 5h7.863z" />
                    </svg>
                </div>
            </div>
            <h2 class="text-[15px] font-bold text-[#0B2638] mb-2">{{ __('messages.Translate to Dutch?') }}</h2>
            <p class="text-sm text-slate-500 mb-5">{{ __('messages.Would you like to translate your comment into Dutch before submitting?') }}</p>
            <div class="flex flex-col gap-2">
                <button type="button" id="translateCheckVerifyBtn" class="w-full px-4 py-2.5 text-sm font-medium text-white bg-blue-500 rounded-lg hover:bg-blue-600 transition-colors flex items-center justify-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ __('messages.Yes, translate to Dutch') }}
                </button>
                <button type="button" id="translateCheckSkipBtn" class="w-full px-4 py-2.5 text-sm font-medium text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors">
                    {{ __('messages.No, submit as is') }}
                </button>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            var activeStopForm = null;
            var stopModal = document.getElementById('stopCommentModal');
            var stopCommentText = document.getElementById('stopCommentText');
            var stopCommentError = document.getElementById('stopCommentError');
            var stopConfirmBtn = document.getElementById('stopCommentConfirmBtn');
            var stopCancelBtn = document.getElementById('stopCommentCancelBtn');
            var stopCloseBtn = document.getElementById('stopCommentClose');
            var stopCommentAiBtn = document.getElementById('stopCommentAiBtn');
            var stopCommentAiBtnText = document.getElementById('stopCommentAiBtnText');
            var stopCommentTranslateBtn = document.getElementById('stopCommentTranslateBtn');
            var stopCommentTranslateBtnText = document.getElementById('stopCommentTranslateBtnText');
            var stopCommentAiFeedback = document.getElementById('stopCommentAiFeedback');
            var spellCheckModal = document.getElementById('spellCheckModal');
            var spellCheckVerifyBtn = document.getElementById('spellCheckVerifyBtn');
            var spellCheckSkipBtn = document.getElementById('spellCheckSkipBtn');
            var translateCheckModal = document.getElementById('translateCheckModal');
            var translateCheckVerifyBtn = document.getElementById('translateCheckVerifyBtn');
            var translateCheckSkipBtn = document.getElementById('translateCheckSkipBtn');

            // State flags to avoid repetitive popups
            var hasSpelledChecked = false;
            var hasTranslated = false;

            // Intercept stop button clicks to show modal
            $(document).on('click', '.stop-button', function(e) {
                e.preventDefault();
                e.stopPropagation();
                activeStopForm = $(this).closest('.stop-form')[0];

                // Reset modal state
                stopCommentText.value = '';
                stopCommentError.classList.add('hidden');
                stopCommentAiFeedback.classList.add('hidden');
                stopCommentAiFeedback.textContent = '';
                stopConfirmBtn.disabled = true;
                stopConfirmBtn.classList.add('opacity-50', 'cursor-not-allowed');
                stopCommentAiBtn.disabled = true;
                stopCommentAiBtn.classList.add('opacity-50', 'cursor-not-allowed');
                stopCommentTranslateBtn.disabled = true;
                stopCommentTranslateBtn.classList.add('opacity-50', 'cursor-not-allowed');
                hasSpelledChecked = false;
                hasTranslated = false;
                stopModal.style.display = 'block';
                stopCommentText.focus();
            });

            // Enable/disable confirm, AI and Translate button based on comment content
            stopCommentText.addEventListener('input', function() {
                var hasContent = this.value.trim().length > 0;
                stopConfirmBtn.disabled = !hasContent;
                stopCommentAiBtn.disabled = !hasContent;
                stopCommentTranslateBtn.disabled = !hasContent;
                if (hasContent) {
                    stopConfirmBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                    stopCommentAiBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                    stopCommentTranslateBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                    stopCommentError.classList.add('hidden');
                } else {
                    stopConfirmBtn.classList.add('opacity-50', 'cursor-not-allowed');
                    stopCommentAiBtn.classList.add('opacity-50', 'cursor-not-allowed');
                    stopCommentTranslateBtn.classList.add('opacity-50', 'cursor-not-allowed');
                }
            });

            // AI correction request
            stopCommentAiBtn.addEventListener('click', function() {
                var comment = stopCommentText.value.trim();
                if (!comment) return;

                stopCommentAiBtn.disabled = true;
                stopCommentAiBtn.classList.add('opacity-50', 'cursor-not-allowed');
                stopCommentText.disabled = true;
                var oldBtnText = stopCommentAiBtnText.textContent;
                stopCommentAiBtnText.textContent = "{{ __('messages.Correcting...') }}";
                stopCommentAiFeedback.classList.add('hidden');

                var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                fetch('/tasks/correct-comment', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ comment: comment })
                })
                .then(function(res) {
                    if (!res.ok) {
                        return res.json().then(function(err) {
                            throw new Error(err.error || 'Failed to correct comment.');
                        });
                    }
                    return res.json();
                })
                .then(function(data) {
                    if (data.corrected) {
                        stopCommentText.value = data.corrected;
                        stopCommentAiFeedback.textContent = "{{ __('messages.Spelling and grammar corrected!') }}";
                        stopCommentAiFeedback.className = "text-xs mt-1 text-emerald-600 font-medium";
                        stopCommentAiFeedback.classList.remove('hidden');
                        hasSpelledChecked = true;
                    }
                })
                .catch(function(err) {
                    console.error(err);
                    stopCommentAiFeedback.textContent = "{{ __('messages.Failed to correct comment. Please check API configuration.') }}";
                    stopCommentAiFeedback.className = "text-xs mt-1 text-red-500 font-medium";
                    stopCommentAiFeedback.classList.remove('hidden');
                })
                .finally(function() {
                    stopCommentAiBtn.disabled = false;
                    stopCommentAiBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                    stopCommentText.disabled = false;
                    stopCommentAiBtnText.textContent = oldBtnText;
                });
            });

            // Translate request
            stopCommentTranslateBtn.addEventListener('click', function() {
                var comment = stopCommentText.value.trim();
                if (!comment) return;

                stopCommentTranslateBtn.disabled = true;
                stopCommentTranslateBtn.classList.add('opacity-50', 'cursor-not-allowed');
                stopCommentAiBtn.disabled = true;
                stopCommentAiBtn.classList.add('opacity-50', 'cursor-not-allowed');
                stopCommentText.disabled = true;
                var oldBtnText = stopCommentTranslateBtnText.textContent;
                stopCommentTranslateBtnText.textContent = "{{ __('messages.Translating...') }}";
                stopCommentAiFeedback.classList.add('hidden');

                var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                fetch('/tasks/translate-comment', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ comment: comment })
                })
                .then(function(res) {
                    if (!res.ok) {
                        return res.json().then(function(err) {
                            throw new Error(err.error || 'Failed to translate comment.');
                        });
                    }
                    return res.json();
                })
                .then(function(data) {
                    if (data.translated) {
                        stopCommentText.value = data.translated;
                        stopCommentAiFeedback.textContent = "{{ __('messages.Translated to Dutch!') }}";
                        stopCommentAiFeedback.className = "text-xs mt-1 text-emerald-600 font-medium";
                        stopCommentAiFeedback.classList.remove('hidden');
                        hasTranslated = true;
                    }
                })
                .catch(function(err) {
                    console.error(err);
                    stopCommentAiFeedback.textContent = "{{ __('messages.Failed to translate comment. Please check API configuration.') }}";
                    stopCommentAiFeedback.className = "text-xs mt-1 text-red-500 font-medium";
                    stopCommentAiFeedback.classList.remove('hidden');
                })
                .finally(function() {
                    stopCommentTranslateBtn.disabled = false;
                    stopCommentTranslateBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                    stopCommentAiBtn.disabled = false;
                    stopCommentAiBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                    stopCommentText.disabled = false;
                    stopCommentTranslateBtnText.textContent = oldBtnText;
                });
            });

            // Function to actually submit the stop task
            function doSubmitStop() {
                var comment = stopCommentText.value.trim();
                if (activeStopForm) {
                    activeStopForm.querySelector('.stop-comment-field').value = comment;
                    stopModal.style.display = 'none';
                    activeStopForm.submit();
                }
            }

            // Confirm stop: show spell-check or translate popups if they haven't been completed yet
            stopConfirmBtn.addEventListener('click', function() {
                var comment = stopCommentText.value.trim();
                if (!comment) {
                    stopCommentError.classList.remove('hidden');
                    stopCommentText.focus();
                    return;
                }

                if (!hasSpelledChecked) {
                    spellCheckModal.style.display = 'block';
                } else if (!hasTranslated) {
                    translateCheckModal.style.display = 'block';
                } else {
                    doSubmitStop();
                }
            });

            // Spell-check popup: verify spelling (trigger AI correction then return to comment modal)
            spellCheckVerifyBtn.addEventListener('click', function() {
                spellCheckModal.style.display = 'none';
                hasSpelledChecked = true;
                if (!stopCommentAiBtn.disabled) {
                    stopCommentAiBtn.click();
                }
            });

            // Spell-check popup: skip and open translate confirmation popup
            spellCheckSkipBtn.addEventListener('click', function() {
                spellCheckModal.style.display = 'none';
                hasSpelledChecked = true;
                if (!hasTranslated) {
                    translateCheckModal.style.display = 'block';
                } else {
                    doSubmitStop();
                }
            });

            // Close spell-check modal on outside click
            spellCheckModal.addEventListener('click', function(e) {
                if (e.target === spellCheckModal) {
                    spellCheckModal.style.display = 'none';
                }
            });

            // Translate popup: verify spelling (trigger translation then return to comment modal)
            translateCheckVerifyBtn.addEventListener('click', function() {
                translateCheckModal.style.display = 'none';
                hasTranslated = true;
                if (!stopCommentTranslateBtn.disabled) {
                    stopCommentTranslateBtn.click();
                }
            });

            // Translate popup: skip and submit directly
            translateCheckSkipBtn.addEventListener('click', function() {
                translateCheckModal.style.display = 'none';
                hasTranslated = true;
                doSubmitStop();
            });

            // Close translate modal on outside click
            translateCheckModal.addEventListener('click', function(e) {
                if (e.target === translateCheckModal) {
                    translateCheckModal.style.display = 'none';
                }
            });

            // Cancel / Close modal
            function closeStopModal() {
                stopModal.style.display = 'none';
                activeStopForm = null;
            }
            stopCancelBtn.addEventListener('click', closeStopModal);
            stopCloseBtn.addEventListener('click', closeStopModal);
            stopModal.addEventListener('click', function(e) {
                if (e.target === stopModal) closeStopModal();
            });

            stopCommentText.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    if (!stopConfirmBtn.disabled) {
                        stopConfirmBtn.click();
                    }
                }
            });
        });
    </script>
    @livewireScripts
</body>
</html>
