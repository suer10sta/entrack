<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.Project Details - DalyTrac') }}</title>
    @vite('resources/css/app.css')
    @livewireStyles
    <style>
        .modal { display: none; position: fixed; z-index: 50; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.5); backdrop-filter: blur(4px); }
        .modal-content { animation: slideDown 0.3s ease-out; }
        @keyframes slideDown { from { transform: translateY(-50px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        .modal-content-custom {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 600px;
            text-align: center;
            position: relative;
            border-radius: 0.75rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        .close-button {
            color: #94a3b8;
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .close-button:hover { color: #475569; }
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
                            else alert(response.message || 'Failed to update.');
                        },
                        error: (xhr) => {
                            let msg = 'Failed to update.';
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
                        alert('Please enter duration in format (e.g., 01h:30m or 10h:00m)');
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
                            else alert('Failed: ' + (response.message || 'Unknown error'));
                        },
                        error: (xhr) => alert('Error: ' + xhr.responseText)
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
                        alert('Please select an employee and both start and end dates.');
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
                                alert(response.message || 'Failed to create timer.');
                            }
                        },
                        error: (xhr) => {
                            let msg = 'Failed to create timer.';
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
                    alert('Download logic not implemented in Alpine.js modal yet.');
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
                            if (data.recipients && data.recipients.length > 0) {
                                recipientsList.innerHTML += `
                                    <div class="flex items-center">
                                        <input class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" type="checkbox" id="selectAllRecipients">
                                        <label class="ml-2 text-sm font-bold text-gray-900" for="selectAllRecipients">Select All</label>
                                    </div>`;
                                data.recipients.forEach(recipient => {
                                    recipientsList.innerHTML += `
                                        <div class="flex items-center">
                                            <input class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 recipient-checkbox" type="checkbox" 
                                                   name="selected_recipients[]" value="${recipient.email}" id="recipient-${recipient.email}" 
                                                   ${recipient.is_comptable === 1 ? 'checked' : ''}>
                                            <label class="ml-2 text-sm text-gray-700" for="recipient-${recipient.email}">
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
                                recipientsList.innerHTML = '<p class="text-sm text-gray-500">No recipients found</p>';
                            }
                        });
                },
                submitForm() {
                    if (!this.form) return;
                    const selectedRecipients = Array.from(document.querySelectorAll('input[name="selected_recipients[]"]:checked')).map(cb => cb.value);
                    if (selectedRecipients.length === 0) {
                        alert('Please select at least one recipient'); return;
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
                        else alert(data.message || 'Error sending invoice');
                    }).catch(error => alert('Error sending invoice.'));
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
                        this.error = true; this.errorMessage = 'Please enter a valid amount > 0.'; return;
                    }
                    if (amount > this.totalCost) {
                        this.error = true; this.errorMessage = 'Amount exceeds total cost.'; return;
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
                    .catch(error => alert('An error occurred: ' + error.message));
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
                                alert(response.message || 'Failed to move task.');
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
    <style>
        /* Table Highlight Styles */
        .highlighted-task td { background-color: #eff6ff !important; } /* blue-50 */
        .regie-task td { background-color: #fefce8 !important; } /* yellow-50 */

        /* Clickable Date Styles */
        td[data-field="start_date"],
        td[data-field="end_date"] {
            color: #2563eb !important; /* blue-600 */
            cursor: pointer !important;
            font-weight: 500;
        }
        td[data-field="start_date"]:hover,
        td[data-field="end_date"]:hover {
            color: #1d4ed8 !important; /* blue-800 */
            text-decoration: underline;
        }
        
        /* Table styles */
        .table {
            width: 100%;
        }
        .table td, .table th {
            padding: 0.75rem; /* 12px */
            vertical-align: top; /* Align content to the top */
        }

        /* Mobile: Tasks table becomes stacked cards (no horizontal scroll) */
        @media (max-width: 1024px) {
            #taskTable thead {
                display: none;
            }
            #taskTable tbody,
            #taskTable tr,
            #taskTable td {
                width: 100%;
            }
            #taskTable tbody tr {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 10px;
                margin-bottom: 16px;
                border: 1px solid rgba(148, 163, 184, 0.55); /* slate-400/55 */
                border-radius: 16px;
                background: linear-gradient(180deg, rgba(255,255,255,0.98) 0%, rgba(248,250,252,0.98) 100%);
                overflow: hidden;
                box-shadow:
                    0 18px 45px rgba(15, 23, 42, 0.10),
                    0 3px 10px rgba(15, 23, 42, 0.08);
                transition: transform 160ms ease, box-shadow 160ms ease, border-color 160ms ease;
                padding: 12px;
            }
            #taskTable tbody tr:hover {
                transform: translateY(-1px);
                border-color: rgba(59, 130, 246, 0.45); /* blue-500/45 */
                box-shadow:
                    0 22px 55px rgba(15, 23, 42, 0.12),
                    0 4px 14px rgba(15, 23, 42, 0.10);
            }
            #taskTable tbody td {
                display: block;
                padding: 10px 10px;
                border: 1px solid rgba(226, 232, 240, 0.9); /* slate-200 */
                border-radius: 12px;
                background: rgba(255,255,255,0.75);
                text-align: left !important;
                white-space: normal !important;
            }
            #taskTable tbody td[data-span="2"] {
                grid-column: 1 / -1;
            }
            #taskTable tbody td[data-label]::before {
                content: attr(data-label);
                display: block;
                font-size: 11px;
                font-weight: 700;
                letter-spacing: .04em;
                text-transform: uppercase;
                color: #64748b; /* slate-500 */
                margin-bottom: 4px;
            }
            #taskTable tbody td[data-label] .task-actions {
                justify-content: center !important;
            }
        }

        /* Mobile: Invoices table becomes modern 2-column cards */
        @media (max-width: 1024px) {
            #invoicesTable thead {
                display: none;
            }
            #invoicesTable tbody {
                display: block;
            }
            #invoicesTable tbody tr {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 10px;
                margin-bottom: 16px;
                border: 1px solid rgba(148, 163, 184, 0.55);
                border-radius: 16px;
                background: linear-gradient(180deg, rgba(255,255,255,0.98) 0%, rgba(248,250,252,0.98) 100%);
                overflow: hidden;
                box-shadow: 0 18px 45px rgba(15, 23, 42, 0.10), 0 3px 10px rgba(15, 23, 42, 0.08);
                padding: 12px;
            }
            #invoicesTable tbody td {
                display: block;
                width: 100%;
                padding: 10px 10px;
                border: 1px solid rgba(226, 232, 240, 0.9);
                border-radius: 12px;
                background: rgba(255,255,255,0.75);
                text-align: left !important;
                white-space: normal !important;
            }
            #invoicesTable tbody td[data-span="2"] {
                grid-column: 1 / -1;
            }
            #invoicesTable tbody td[data-label]::before {
                content: attr(data-label);
                display: block;
                font-size: 11px;
                font-weight: 700;
                letter-spacing: .04em;
                text-transform: uppercase;
                color: #64748b;
                margin-bottom: 4px;
            }
            #invoicesTable tbody td[data-label] .invoice-actions {
                justify-content: center !important;
            }
        }

        /* Mobile: Devis table becomes modern 2-column cards */
        @media (max-width: 1024px) {
            #devisTable thead {
                display: none;
            }
            #devisTable tbody {
                display: block;
            }
            #devisTable tbody tr {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 10px;
                margin-bottom: 16px;
                border: 1px solid rgba(148, 163, 184, 0.55);
                border-radius: 16px;
                background: linear-gradient(180deg, rgba(255,255,255,0.98) 0%, rgba(248,250,252,0.98) 100%);
                overflow: hidden;
                box-shadow: 0 18px 45px rgba(15, 23, 42, 0.10), 0 3px 10px rgba(15, 23, 42, 0.08);
                padding: 12px;
            }
            #devisTable tbody td {
                display: block;
                width: 100%;
                padding: 10px 10px;
                border: 1px solid rgba(226, 232, 240, 0.9);
                border-radius: 12px;
                background: rgba(255,255,255,0.75);
                text-align: left !important;
                white-space: normal !important;
            }
            #devisTable tbody td[data-span="2"] {
                grid-column: 1 / -1;
            }
            #devisTable tbody td[data-label]::before {
                content: attr(data-label);
                display: block;
                font-size: 11px;
                font-weight: 700;
                letter-spacing: .04em;
                text-transform: uppercase;
                color: #64748b;
                margin-bottom: 4px;
            }
            #devisTable tbody td[data-label] .devis-actions {
                justify-content: center !important;
            }
        }

        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-50 flex h-screen overflow-hidden font-sans" x-data>

    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-NKDMSK6" height="0" width="0"
            style="display: none; visibility: hidden"></iframe></noscript>

    {{-- Sidebar --}}
    @if(Auth::user()->hasRole('Admin'))
        <x-dashboard.sidebar />
    @else
        <x-employees.sidebar />
    @endif

    {{-- Main Wrapper (Navbar + Content) --}}
    <div class="flex flex-col flex-1 overflow-y-auto">
        {{-- Navbar --}}
        <x-dashboard.navbar />

        {{-- Main Content --}}
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-[#eaebef] p-4 sm:p-6 lg:p-8">
            {{-- [FIX] Using max-w-10xl for "larger" cards and space-y-6 for spacing --}}
            <div class="max-w-10xl mx-auto space-y-6"> 

                {{-- Global alert message (e.g. when trying to edit a running task) --}}
                @if(session('alertmessage'))
                    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                        <strong class="font-bold">{{ __('messages.Error!') }}</strong>
                        <span class="block sm:inline">{{ session('alertmessage') }}</span>
                        <button type="button" onclick="this.parentElement.style.display='none';" class="absolute top-0 bottom-0 right-0 px-4 py-3 font-bold text-xl" aria-label="{{ __('messages.Close') }}">&times;</button>
                    </div>
                @endif
                
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

                    // --- Determine Route Prefixes Once ---
                    $taskRoutePrefix = 'admin.'; // Default
                    if (Auth::user()->hasRole('Responsable de projet')) $taskRoutePrefix = 'Responsable_de_projet.';
                    elseif (Auth::user()->hasRole('Responsable de projet et gestionnaire')) $taskRoutePrefix = 'Responsable_gestionnaire.';
                    elseif (Auth::user()->hasRole('Employee')) $taskRoutePrefix = 'employee.';
                @endphp

                {{-- 1. Project Header Card --}}
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4">
                        <div class="flex items-center gap-2 mb-2 sm:mb-0">
                            <h1 class="text-2xl font-bold text-gray-900">{{ $project->name }}</h1>
                            @if(!empty($project->archived))
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-800">
                                    Archived
                                </span>
                            @endif
                        </div>
                        @can('Add task')
                        <div class="flex flex-wrap items-center gap-2">
                            <a href="{{ route($taskRoutePrefix . 'tasks.create', $project->id) }}"
                                class="inline-flex items-center gap-2 bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors flex-shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                {{ __('messages.Add Task') }}
                            </a>
                            @if(Auth::user()->hasRole('Admin'))
                                <a href="{{ route('admin.projects.time-transfer.export', $project) }}"
                                    class="inline-flex items-center gap-2 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors flex-shrink-0">
                                    {{ __('messages.Export time transfer') }}
                                </a>
                                <a href="{{ route('admin.projects.time-transfer.import', $project) }}"
                                    class="inline-flex items-center gap-2 bg-indigo-500 hover:bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors flex-shrink-0">
                                    {{ __('messages.Import time transfer') }}
                                </a>
                            @endif
                        </div>
                        @endcan
                    </div>

                    <div class="border-t border-gray-200 pt-4">
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 lg:grid-cols-10 gap-3 sm:gap-4">
                            <div class="rounded-xl border border-slate-200 bg-gradient-to-b from-white to-slate-50 px-3 py-3 shadow-sm">
                                <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">{{ __('messages.Budget') }}</p>
                                <p class="mt-1 text-base font-extrabold text-slate-900 editable" data-id="{{$project->id}}" data-column="budget">€{{ number_format($project->budget, 2, ',', ' ') }}</p>
                            </div>
                            <div class="rounded-xl border border-slate-200 bg-gradient-to-b from-white to-slate-50 px-3 py-3 shadow-sm">
                                <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">{{ __('messages.Total invoiced') }}</p>
                                <p class="mt-1 text-base font-extrabold text-emerald-700">€{{ number_format($totalBilled, 2, ',', ' ') }}</p>
                            </div>
                            <div class="rounded-xl border border-slate-200 bg-gradient-to-b from-white to-slate-50 px-3 py-3 shadow-sm">
                                <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">{{ __('messages.Unbilled') }}</p>
                                <p class="mt-1 text-base font-extrabold text-slate-900">€{{ number_format(max($unbilledBudget, 0), 2, ',', ' ') }}</p>
                            </div>
                            <div class="rounded-xl border border-slate-200 bg-gradient-to-b from-white to-slate-50 px-3 py-3 shadow-sm">
                                <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">{{ __('messages.Cost/Hour label') }}</p>
                                <p class="mt-1 text-base font-extrabold text-slate-900 editable" data-id="{{$project->id}}" data-column="cost_per_hour">€{{ number_format($project->cost_per_hour, 2, ',', ' ') }}</p>
                            </div>
                            <div class="rounded-xl border border-slate-200 bg-gradient-to-b from-white to-slate-50 px-3 py-3 shadow-sm">
                                <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">{{ __('messages.Duration') }}</p>
                                <p class="mt-1 text-base font-extrabold text-slate-900">{{ $hours }}h:{{ $minutes }}m</p>
                            </div>
                            <div class="rounded-xl border border-slate-200 bg-gradient-to-b from-white to-slate-50 px-3 py-3 shadow-sm">
                                <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">{{ __('messages.Est. Hours header') }}</p>
                                <p class="mt-1 text-base font-extrabold text-slate-900 editable" data-id="{{$project->id}}" data-column="estimated_hours">{{ $project->estimated_hours }}h</p>
                            </div>
                            <div class="rounded-xl border border-slate-200 bg-gradient-to-b from-white to-slate-50 px-3 py-3 shadow-sm">
                                <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">{{ __('messages.Difference') }}</p>
                                <p class="mt-1 text-base font-extrabold">
                                    @if($difference > 0)
                                        <span class="text-emerald-600">+{{ $diffhours }}h:{{ $diffminutes }}m</span>
                                    @elseif($difference < 0)
                                        <span class="text-red-600">-{{ $diffhours }}h:{{ $diffminutes }}m</span>
                                    @else
                                        <span class="text-slate-600">{{ $diffhours }}h:{{ $diffminutes }}m</span>
                                    @endif
                                </p>
                            </div>
                            <div class="rounded-xl border border-slate-200 bg-gradient-to-b from-white to-slate-50 px-3 py-3 shadow-sm">
                                <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">{{ __('messages.VAT') }}</p>
                                <p class="mt-1 text-base font-extrabold text-slate-900 editable" data-id="{{$project->id}}" data-column="tva">{{ $project->tva }}%</p>
                            </div>
                            <div class="rounded-xl border border-slate-200 bg-gradient-to-b from-white to-slate-50 px-3 py-3 shadow-sm">
                                <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">{{ __('messages.Client') }}</p>
                                <p class="mt-1 truncate text-base font-extrabold text-slate-900">{{ $project->client->name ?? '-' }}</p>
                            </div>
                            <div class="col-span-2 sm:col-span-1 lg:col-span-1 rounded-xl border border-slate-200 bg-gradient-to-b from-white to-slate-50 px-3 py-3 shadow-sm">
                                <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">{{ __('messages.Progress') }}</p>
                                <div class="mt-1 flex items-center justify-between gap-2">
                                    <p class="text-base font-extrabold text-slate-900">{{ $formattedProgressPercentage }}%</p>
                                    <span class="text-[11px] font-semibold text-slate-500">{{ __('messages.Progress') }}</span>
                                </div>
                                <div class="mt-2 w-full h-2.5 bg-slate-200 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full @if($progressPercentage < 50) bg-emerald-500 @elseif($progressPercentage < 75) bg-yellow-500 @else bg-red-500 @endif"
                                         style="width: {{ min($formattedProgressPercentage, 100) }}%">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @if(Auth::user()->hasRole('Admin') || Auth::user()->hasRole('Responsable de projet'))
                        <div class="pt-4 mt-4 border-t border-gray-200">
                            <div class="flex flex-wrap items-center justify-center gap-2 sm:flex-nowrap sm:justify-end">
                            @can('Update project')
                                <a href="{{ route(Auth::user()->hasRole('Admin') ? 'admin.projects.edit' : 'Responsable_de_projet.projects.edit', $project->id) }}" class="inline-flex justify-center bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-lg text-sm shadow-sm">
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
                                    <button type="submit"
                                            class="inline-flex justify-center bg-gray-600 hover:bg-gray-700 text-white font-medium px-4 py-2 rounded-lg text-sm shadow-sm">
                                        {{ __('messages.Archive') }}
                                    </button>
                                </form>

                                <form action="{{ $deleteRoute }}" method="post" onsubmit="return confirm('{{ __('messages.Are you sure you want to delete this project?') }}');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="inline-flex justify-center bg-red-600 hover:bg-red-700 text-white font-medium px-4 py-2 rounded-lg text-sm shadow-sm">
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
                                <a href="{{ route($createRouteName, ['client_id' => $project->client_id, 'parent_id' => $project->id]) }}"
                                   class="inline-flex justify-center bg-emerald-600 hover:bg-emerald-700 text-white font-medium px-4 py-2 rounded-lg text-sm shadow-sm w-full sm:w-auto">
                                    {{ __('messages.Create SubProject') }}
                                </a>
                            @endcan
                        </div>
                    @endif
                </div>
                </div>

                @if(isset($subprojects) && $subprojects->count() > 0)
                    {{-- Subprojects Summary Section --}}
                    <div class="mt-6 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg shadow-sm border border-blue-200 overflow-hidden">
                        <div class="px-6 py-4 border-b border-blue-200">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('messages.Subprojects Summary') }}</h2>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-200">
                                    <p class="text-sm text-gray-500 mb-1">{{ __('messages.Total Subprojects') }}</p>
                                    <p class="text-2xl font-bold text-gray-900">{{ $subprojects->count() }}</p>
                                </div>
                                <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-200">
                                    <p class="text-sm text-gray-500 mb-1">{{ __('messages.Total Tasks header') }}</p>
                                    <p class="text-2xl font-bold text-gray-900">{{ $subprojectsSummary['total_tasks'] ?? 0 }}</p>
                                </div>
                                <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-200">
                                    <p class="text-sm text-gray-500 mb-1">{{ __('messages.Total Hours') }}</p>
                                    <p class="text-2xl font-bold text-gray-900">
                                        {{ $subprojectsSummary['total_hours'] ?? 0 }}h:{{ str_pad($subprojectsSummary['remaining_minutes'] ?? 0, 2, '0', STR_PAD_LEFT) }}m
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    

                    {{-- Subprojects List --}}
                    <div class="mt-6 bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                            <h2 class="text-lg font-semibold text-gray-900">{{ __('messages.Subprojects') }}</h2>
                            <span class="text-sm text-gray-500">{{ $subprojects->count() }} {{ __('messages.Total') ?? 'total' }}</span>
                        </div>
                        <div class="divide-y divide-gray-200">
                            @foreach($subprojects as $sub)
                                <div class="px-6 py-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                                    @php
                                        $subProjectShowRoute = route($taskRoutePrefix . 'projects.show', $sub->id);
                                        $subProjectDeleteRoute = route($taskRoutePrefix . 'projects.destroy', $sub->id);
                                    @endphp
                                    <div>
                                        <a href="{{ $subProjectShowRoute }}" class="text-sm font-medium text-blue-600 hover:underline">
                                            {{ $sub->name }}
                                        </a>
                                        <p class="text-xs text-gray-500 mt-0.5">
                                            Budget: {{ $sub->budget ?? '-' }} | CPH: {{ $sub->cost_per_hour ?? '-' }}
                                        </p>
                                    </div>
                                    <div class="flex items-center gap-3 text-xs text-gray-500">
                                        <span>Created: {{ $sub->created_at?->format('Y-m-d') }}</span>
                                        @can('Delete project')
                                            <form action="{{ $subProjectDeleteRoute }}" method="post" onsubmit="return confirm('{{ __('messages.Are you sure you want to delete this project?') }}');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" title="{{ __('messages.Delete project') }}"
                                                        class="inline-flex items-center justify-center w-7 h-7 rounded-md text-white bg-red-600 hover:bg-red-700">
                                                    <svg class="w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862A2 2 0 015.995 19.142L5 7m5 4v6m4-6v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif


                {{-- 2. Tasks Table --}}
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
                {{-- Card still has overflow-hidden, which is fine as it has no dropdowns --}}
                <div class="mt-6 bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="p-4 sm:p-6 border-b border-gray-200 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
                        <div class="flex items-center justify-between gap-3 w-full">
                            <h2 class="text-lg font-semibold text-gray-900">{{ __('messages.Tasks header') }}</h2>
                            <button type="button" class="section-toggle inline-flex items-center gap-2 text-sm font-semibold text-gray-600 hover:text-gray-900"
                                    data-target="projectTasksBody" aria-expanded="false">
                                <span class="hidden lg:inline">{{ __('messages.Show') ?? 'Show' }}</span>
                                <svg class="section-chevron w-5 h-5 transition-transform" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6" />
                                </svg>
                            </button>
                        </div>
                        <div class="relative">
                            <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-3 top-2.5 h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 1010.5 18.5a7.5 7.5 0 006.15-3.85z"/></svg>
                            <input type="text" id="taskSearchInput" placeholder="{{ __('messages.Search tasks...') }}" class="w-full sm:w-64 pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                        </div>
                    </div>

                    <div id="projectTasksBody" class="hidden">
                    <div class="overflow-x-visible" style="-webkit-overflow-scrolling: touch;">
                        <table class="w-full text-left border-collapse table-auto" id="taskTable">
                            <thead class="border-b border-gray-200 bg-gray-50">
                                <tr>
                                    {{-- Removed all style="width: ...%" attributes --}}
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600 uppercase tracking-wider text-center whitespace-nowrap">{{ __('messages.Task column') }}</th>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600 uppercase tracking-wider text-center whitespace-nowrap">{{ __('messages.Description column') }}</th>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600 uppercase tracking-wider text-center whitespace-nowrap">{{ __('messages.Employees column') }}</th>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600 uppercase tracking-wider text-center whitespace-nowrap">{{ __('messages.Duration') }}</th>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600 uppercase tracking-wider text-center whitespace-nowrap">{{ __('messages.Total column') }}</th>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600 uppercase tracking-wider text-center whitespace-nowrap">{{ __('messages.Estimated column') }}</th>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600 uppercase tracking-wider text-center whitespace-nowrap">{{ __('messages.Difference column') }}</th>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600 uppercase tracking-wider text-center whitespace-nowrap">{{ __('messages.End Date') }}</th>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600 uppercase tracking-wider text-center whitespace-nowrap">{{ __('messages.Progress column') }}</th>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600 uppercase tracking-wider text-center whitespace-nowrap">{{ __('messages.Teams column') }}</th>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600 uppercase tracking-wider text-center whitespace-nowrap">{{ __('messages.Pointage') }}</th>
                                    <th class="px-4 py-3 text-center text-sm font-semibold text-gray-600 uppercase tracking-wider text-center whitespace-nowrap">{{ __('messages.Action column') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
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
                                <tr class="hover:bg-gray-50">
                                    <td data-label="{{ __('messages.Task column') }}" data-span="2" class="px-4 py-3 text-sm text-gray-900 whitespace-normal break-words">
                                        <a href="{{ route('admin.tasks.show', $task->id) }}" class="text-blue-600 hover:underline font-semibold">
                                            {{ $task->title }}
                                        </a>
                                    </td>
                                    <td data-label="{{ __('messages.Description column') }}" data-span="2" class="px-4 py-3 text-sm text-gray-600 max-w-xs truncate whitespace-normal break-words">{{ $task->description }}</td>
                                    <td data-label="{{ __('messages.Employees column') }}" class="px-4 py-3 text-sm text-gray-900 whitespace-normal break-words">
                                        @foreach($taskEmployeeDetails as $employee)
                                            <span class="inline-block bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs mb-1">{{ $employee->name }}</span><br>
                                        @endforeach
                                    </td>
                                    <td data-label="{{ __('messages.Duration') }}" class="px-4 py-3 text-sm text-gray-900 whitespace-normal break-words">
                                        @foreach($taskEmployeeDetails as $employee)
                                            @php
                                                $duration = $allTaskTimers->where('task_id', $task->id)->where('employee_id', $employee->id)->sum('duration');
                                                $h = floor($duration / 60);
                                                $m = $duration % 60;
                                            @endphp
                                            <a href="{{ route($taskRoutePrefix . 'tasks.durationInfos', ['employee' => $employee->id, 'task' => $task->id]) }}" class="text-blue-600 hover:text-blue-800">
                                                {{ $h }}h:{{ $m }}m
                                            </a><br>
                                        @endforeach
                                    </td>
                                    <td data-label="{{ __('messages.Total column') }}" class="px-4 py-3 text-sm text-gray-900 whitespace-nowrap">{{ $taskHours }}h:{{ $taskMinutes }}m</td>
                                    <td data-label="{{ __('messages.Estimated column') }}" class="px-4 py-3 text-sm text-gray-900 whitespace-nowrap">{{ $task->estimated_hours }}h</td>
                                    <td data-label="{{ __('messages.Difference column') }}" class="px-4 py-3 text-sm whitespace-nowrap">
                                        @if($taskDifference > 0)
                                            <span class="text-green-600">+{{ $diffHours }}h:{{ $diffMinutes }}m</span>
                                        @elseif($taskDifference < 0)
                                            <span class="text-red-600">-{{ $diffHours }}h:{{ $diffMinutes }}m</span>
                                        @else
                                            <span class="text-gray-600">0h:0m</span>
                                        @endif
                                    </td>
                                    <td data-label="{{ __('messages.End Date') }}" class="px-4 py-3 text-sm text-gray-900 whitespace-nowrap">
                                        @if($taskEndDate)
                                            {{ \Carbon\Carbon::parse($taskEndDate)->format('d-m-Y H:i') }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td data-label="{{ __('messages.Progress column') }}" class="px-4 py-3">
                                        <div class="w-24 h-2 bg-gray-200 rounded-full overflow-hidden">
                                            <div class="h-full rounded-full @if($taskProgress < 50) bg-green-500 @elseif($taskProgress < 75) bg-yellow-500 @else bg-red-500 @endif" style="width: {{ min($taskProgressFormatted, 100) }}%"></div>
                                        </div>
                                        <span class="text-xs text-gray-700 mt-1">{{ $taskProgressFormatted }}%</span>
                                    </td>
                                    <td data-label="{{ __('messages.Teams column') }}" class="px-4 py-3 text-sm text-gray-900 whitespace-normal break-words">
                                        @foreach($teamTasks as $teamTask)
                                            {{ $allTeamDetails->get($teamTask->team_id)?->name }}<br>
                                        @endforeach
                                    </td>
                                    <td data-label="{{ __('messages.Pointage') }}" data-span="2" class="px-4 py-3 text-sm text-gray-900">
                                        @if($employee_task)
                                            @if(!$task_timer)
                                                <form class="flex flex-col gap-2" action="{{ route($taskRoutePrefix . 'tasks.start', ['tasks' => $employee_task->id, 'employee_id' => $current_employee_id]) }}" method="POST">
                                                    @csrf
                                                    {{-- [FIX] rows="1" and new fixed width --}}
                                                    <textarea name="comment" class="block w-full rounded-lg border-gray-300 shadow-sm sm:text-sm py-1 px-2" placeholder="{{ __('messages.Comment placeholder') }}" rows="1" style="width: 130px;"></textarea>
                                                    <button class="px-3 py-1.5 text-xs font-medium text-white bg-green-600 rounded-lg hover:bg-green-700" type="submit">{{ __('messages.Start') }}</button>
                                                </form>
                                            @else
                                                <form class="mt-2 stop-form" action="{{ route($taskRoutePrefix . 'tasks.stop', ['tasks' => $employee_task->id, 'employee_id' => $current_employee_id]) }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="stop_comment" class="stop-comment-field">
                                                    <button class="stop-button px-3 py-1.5 text-xs font-medium text-white bg-red-600 rounded-lg hover:bg-red-700" type="submit" data-task-id="{{ $task->id }}">{{ __('messages.Stop') }}</button>
                                                </form>
                                            @endif
                                        @endif
                                    </td>
                                    <td data-label="{{ __('messages.Action column') }}" data-span="2" class="px-4 py-3">
                                        <div class="task-actions flex items-center justify-center gap-3">
                                            @can('Update task')
                                                <a href="{{ route($taskRoutePrefix . 'tasks.edit', $task->id) }}" class="p-1.5 rounded-md bg-blue-600 text-white hover:bg-blue-700" title="{{ __('messages.Edit Task') }}">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                                    </svg>
                                                </a>
                                            @endcan

                                            @can('Add manual timer')
                                                <button type="button"
                                                        class="p-1.5 rounded-md bg-emerald-600 text-white hover:bg-emerald-700 flex items-center justify-center"
                                                        title="{{ __('messages.Add Manual Time') }}"
                                                        @click="$dispatch('open-manual-timer-modal', { taskId: {{ $task->id }}, employeeId: {{ $current_employee_id ?? 'null' }} })">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M12 6a6 6 0 016 6 6 6 0 11-6-6z" />
                                                    </svg>
                                                </button>
                                            @endcan

                                            @can('Update task')
                                                <button type="button" class="p-1.5 rounded-md bg-purple-600 text-white hover:bg-purple-700" title="{{ __('messages.Move Task') }}" @click="$dispatch('open-move-task-modal', { taskId: {{ $task->id }}, currentProjectId: {{ $project->id }} })">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                                    </svg>
                                                </button>
                                            @endcan
                                            @can('Delete task')
                                            <form method="post" action="{{ route($taskRoutePrefix . 'tasks.destroy', $task->id) }}" onsubmit="return confirm('{{ __('messages.Are you sure?') }}');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-1.5 rounded-md bg-red-600 text-white hover:bg-red-700" title="{{ __('messages.Delete Task') }}">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                </button>
                                            </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="11" class="py-8 text-center text-gray-500">{{ __('messages.No tasks found') }}</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    </div>
                </div>

                {{-- 3. Invoices Table --}}
                @can('View invoices')
                @if($invoices !== null)
                {{-- [FIX] Removed overflow-hidden from card to prevent dropdown clipping --}}
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="p-4 sm:p-6 border-b border-gray-200 flex flex-col sm:flex-row justify-between items-center gap-3">
                        <div class="flex items-center justify-between gap-3 w-full">
                            <h2 class="text-lg font-semibold text-gray-900">{{ __('messages.Invoices header') }}</h2>
                            <button type="button" class="section-toggle inline-flex items-center gap-2 text-sm font-semibold text-gray-600 hover:text-gray-900"
                                    data-target="projectInvoicesBody" aria-expanded="false">
                                <span class="hidden lg:inline">{{ __('messages.Show') ?? 'Show' }}</span>
                                <svg class="section-chevron w-5 h-5 transition-transform" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
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
                        <div class="flex flex-wrap gap-x-4 gap-y-1 text-sm mt-2 sm:mt-0">
                            <span>{{ __('messages.Total Paid') }}: <strong class="text-green-600">€{{ number_format($totalPaid, 2, ',', ' ') }}</strong></span>
                            <span>{{ __('messages.Total Unpaid') }}: <strong class="text-red-600">€{{ number_format($totalUnpaid, 2, ',', ' ') }}</strong></span>
                            <span>{{ __('messages.Total TVA') }}: <strong class="text-gray-800">€{{ number_format($totaltva, 2, ',', ' ') }}</strong></span>
                        </div>
                    </div>
                    <div id="projectInvoicesBody" class="hidden">
                    <div class="overflow-x-visible" style="-webkit-overflow-scrolling: touch;">
                        <table class="w-full table" id="invoicesTable">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600 uppercase">{{ __('messages.Invoice N') }}</th>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600 uppercase">{{ __('messages.VAT') }}</th>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600 uppercase">{{ __('messages.Client') }}</th>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600 uppercase">{{ __('messages.Type') }}</th>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600 uppercase">{{ __('messages.Created At') }}</th>
                                    <th class="px-4 py-3 text-right text-sm font-semibold text-gray-600 uppercase">{{ __('messages.Cost HT') ?? 'Cost HT' }}</th>
                                    <th class="px-4 py-3 text-right text-sm font-semibold text-gray-600 uppercase">{{ __('messages.Cost TVA') ?? 'Cost TVA' }}</th>
                                    <th class="px-4 py-3 text-right text-sm font-semibold text-gray-600 uppercase">{{ __('messages.Cost TTC') ?? 'Cost TTC' }}</th>
                                    <th class="px-4 py-3 text-center text-sm font-semibold text-gray-600 uppercase">{{ __('messages.Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse($invoices as $invoice)
                                <tr class="hover:bg-gray-50">
                                    <td data-label="{{ __('messages.Invoice N') }}" data-span="2" class="px-4 py-3 text-sm font-medium whitespace-nowrap">
                                        @php $pdfRoute = '#'; @endphp
                                        @if(Auth::user()->hasRole('Responsable de projet')) @php $pdfRoute = route('Responsable_de_projet.invoices.servePdf', ['invoiceName' => $invoice->id]); @endphp @endif
                                        @if(Auth::user()->hasRole('Admin')) @php $pdfRoute = route('admin.invoices.servePdf', ['invoiceName' => $invoice->id]); @endphp @endif
                                        @if(Auth::user()->hasRole('Employee')) @php $pdfRoute = route('employee.invoices.servePdf', ['invoiceName' => $invoice->id]); @endphp @endif
                                        @if(Auth::user()->hasRole('Responsable de projet et gestionnaire')) @php $pdfRoute = route('Responsable_gestionnaire.invoices.servePdf', ['invoiceName' => $invoice->id]); @endphp @endif
                                        
                                        <a href="{{ $pdfRoute }}" target="_blank" class="text-blue-600 hover:text-blue-800">
                                            {{$entreprise_infos->invoice_prefix}}-{{date('ym', strtotime($invoice->created_at))}}{{$invoice->number}}
                                        </a>
                                    </td>
                                    <td data-label="{{ __('messages.VAT') }}" class="px-4 py-3 text-sm text-gray-900 whitespace-nowrap">
                                        @php $project = App\Models\Project::find($invoice->project_id); @endphp
                                        {{$project->tva ?? 'N/A'}}%
                                    </td>
                                    <td data-label="{{ __('messages.Client') }}" class="px-4 py-3 text-sm text-gray-900 whitespace-normal break-words">{{ $invoice->client_name }}</td>
                                    <td data-label="{{ __('messages.Type') }}" class="px-4 py-3 text-sm text-gray-600 whitespace-nowrap">
                                        @if(($invoice->type ?? '') === 'Percentage')
                                            {{ $invoice->type }} ({{ (float)($invoice->percentage ?? 0) }}%)
                                        @else
                                            {{ $invoice->type }}
                                        @endif
                                    </td>
                                    <td data-label="{{ __('messages.Created At') }}" class="px-4 py-3 text-sm text-gray-600 whitespace-nowrap">{{ $invoice->created_at->format('Y-m-d') }}</td>
                                    <td data-label="{{ __('messages.Cost HT') ?? 'Cost HT' }}" class="py-4 px-4 text-sm font-semibold text-gray-900 text-right">
                                        €{{ number_format((float)($invoice->total_htva ?? 0), 2, ',', ' ') }}
                                    </td>
                                    <td data-label="{{ __('messages.Cost TVA') ?? 'Cost TVA' }}" class="py-4 px-4 text-sm font-semibold text-gray-900 text-right">
                                        €{{ number_format((float)($invoice->total_tva ?? 0), 2, ',', ' ') }}
                                    </td>
                                    <td data-label="{{ __('messages.Cost TTC') ?? 'Cost TTC' }}" class="py-4 px-4 text-sm font-semibold text-gray-900 text-right">
                                        €{{ number_format((float)($invoice->total_ttc ?? 0), 2, ',', ' ') }}
                                    </td>
                                    <td data-label="{{ __('messages.Actions') }}" data-span="2" class="px-7 py-3">
                                        <div class="invoice-actions flex flex-row items-center justify-center gap-2">
                                            @php
                                                $isPaid = $invoice->Payment_status == 1;
                                                $toggleRoute = '#';
                                                if(Auth::user()->hasRole('Admin')) $toggleRoute = $isPaid ? route('admin.invoice.deactivate', $invoice->id) : route('admin.invoice.activate', $invoice->id);
                                                elseif(Auth::user()->hasRole('Responsable de projet')) $toggleRoute = $isPaid ? route('Responsable_de_projet.invoice.deactivate', $invoice->id) : route('Responsable_de_projet.invoice.activate', $invoice->id);
                                                elseif(Auth::user()->hasRole('Employee')) $toggleRoute = $isPaid ? route('employee.invoice.deactivate', $invoice->id) : route('employee.invoice.activate', $invoice->id);
                                                elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire')) $toggleRoute = $isPaid ? route('Responsable_gestionnaire.invoice.deactivate', $invoice->id) : route('Responsable_gestionnaire.invoice.activate', $invoice->id);
                                            @endphp
                                            <form action="{{ $toggleRoute }}" method="post" onsubmit="return confirm('{{ __('messages.Change payment status?') }}');" class="w-full">
                                                @csrf
                                                <button type="submit" title="{{ $isPaid ? __('messages.Mark as Unpaid') : __('messages.Mark as Paid') }}"
                                                        class="payment-button w-full px-2 py-1 rounded-md text-xs font-medium {{ $isPaid ? 'bg-green-600 hover:bg-green-700 text-white' : 'bg-red-600 hover:bg-red-700 text-white' }} whitespace-nowrap">
                                                    {{ $isPaid ? __('messages.Paid') : __('messages.Unpaid') }}
                                                </button>
                                            </form>
                                            
                                            <button type="button" title="{{ $invoice->sentInvoice == 0 ? __('messages.Send Invoice') : __('messages.Resend Invoice') }}"
                                                    class="w-full px-2 py-1 rounded-md bg-gray-600 text-white hover:bg-gray-700 text-xs font-medium whitespace-nowrap"
                                                    @click="$dispatch('open-send-invoice-modal', { invoiceId: {{ $invoice->id }} })">
                                                {{ __('messages.Send') }}
                                            </button>
                                            
                                            <div x-data="{ open: false }" class="relative w-full">
                                                <button @click="open = !open" type="button" title="{{ __('messages.Create Credit Note') }}" class="w-full px-2 py-1 rounded-md bg-purple-600 text-white hover:bg-purple-700 text-xs font-medium whitespace-nowrap">
                                                    {{ __('messages.Credit Note') }}
                                                </button>
                                                <ul x-show="open" @click.outside="open = false" x-transition style="display: none;"
                                                    class="absolute right-0 bottom-full mb-2 w-40 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none z-50">
                                                    @php 
                                                    $creditNoteRoute = ''; $detailedCreditNoteRoute = '';
                                                    if(Auth::user()->hasRole('Admin')) { $creditNoteRoute = 'admin.invoice.creditNote'; $detailedCreditNoteRoute = 'admin.invoice.detailedCreditNote'; }
                                                    elseif(Auth::user()->hasRole('Responsable de projet')) { $creditNoteRoute = 'Responsable_de_projet.invoice.creditNote'; $detailedCreditNoteRoute = 'Responsable_de_projet.invoice.detailedCreditNote'; }
                                                    elseif(Auth::user()->hasRole('Employee')) { $creditNoteRoute = 'employee.invoice.creditNote'; $detailedCreditNoteRoute = 'employee.invoice.detailedCreditNote'; }
                                                    elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire')) { $creditNoteRoute = 'Responsable_gestionnaire.invoice.creditNote'; $detailedCreditNoteRoute = 'Responsable_gestionnaire.invoice.detailedCreditNote'; }
                                                    @endphp
                                                    
                                                    @if($invoice->type == 'Detailed' && $detailedCreditNoteRoute)
                                                        <li><a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" @click.prevent="$dispatch('open-partial-credit-modal', { invoiceId: {{ $invoice->id }}, totalCost: {{ $invoice->total_ttc }} }); open = false;">{{ __('messages.Partial') }}</a></li>
                                                        <li><a href="{{ route($detailedCreditNoteRoute, $invoice->id) }}?type=full" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">{{ __('messages.Full') }}</a></li>
                                                    @elseif($creditNoteRoute)
                                                        <li><a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" @click.prevent="$dispatch('open-partial-credit-modal', { invoiceId: {{ $invoice->id }}, totalCost: {{ $invoice->total_ttc }} }); open = false;">{{ __('messages.Partial') }}</a></li>
                                                        <li><a href="{{ route($creditNoteRoute, $invoice->id) }}?type=full" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">{{ __('messages.Full') }}</a></li>
                                                    @endif
                                                </ul>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="py-8 text-center text-gray-500">{{ __('messages.No invoices found') }}</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    </div>
                </div>
                @endif
                @endcan

                
                {{-- 4. Devis Table --}}
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
                {{-- [FIX] Removed overflow-hidden from card to prevent dropdown clipping --}}
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="p-4 sm:p-6 border-b border-gray-200 flex flex-col sm:flex-row justify-between items-center gap-3">
                        <div class="flex items-center justify-between gap-3 w-full">
                            <h2 class="text-lg font-semibold text-gray-900">{{ __('messages.Devis') }}</h2>
                            <button type="button" class="section-toggle inline-flex items-center gap-2 text-sm font-semibold text-gray-600 hover:text-gray-900"
                                    data-target="projectDevisBody" aria-expanded="false">
                                <span class="hidden lg:inline">{{ __('messages.Show') ?? 'Show' }}</span>
                                <svg class="section-chevron w-5 h-5 transition-transform" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6" />
                                </svg>
                            </button>
                        </div>
                        <div class="text-right mt-2 sm:mt-0">
                            <span class="text-sm font-medium text-gray-700">{{ __('messages.TOTAL ACCEPTED') }}: </span>
                            <strong class="text-green-600">€{{ number_format($Total_Accpeted, 2, ',', ' ') }}</strong>
                        </div>
                    </div>
                    <div id="projectDevisBody" class="hidden">
                    <div class="overflow-x-visible" style="-webkit-overflow-scrolling: touch;">
                        <table class="w-full table" id="devisTable">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600 uppercase">{{ __('messages.devis N') }}</th>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600 uppercase">{{ __('messages.Client Name / Emails') }}</th>
                                    <th class="px-4 py-3 text-right text-sm font-semibold text-gray-600 uppercase">{{ __('messages.Budget label devis') }}</th>
                                    <th class="px-4 py-3 text-right text-sm font-semibold text-gray-600 uppercase">{{ __('messages.Billed Budget') ?? 'Budget Facturé' }}</th>
                                    <th class="px-4 py-3 text-right text-sm font-semibold text-gray-600 uppercase">{{ __('messages.Unbilled Budget') }}</th>
                                    <th class="px-4 py-3 text-center text-sm font-semibold text-gray-600 uppercase">{{ __('messages.Created at') }}</th>
                                    <th class="px-4 py-3 text-center text-sm font-semibold text-gray-600 uppercase">{{ __('messages.Acceptance') }}</th>
                                    <th class="px-4 py-3 text-center text-sm font-semibold text-gray-600 uppercase">{{ __('messages.Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse($devis as $d)
                                <tr class="hover:bg-gray-50">
                                    <td data-label="{{ __('messages.devis N') }}" data-span="2" class="px-4 py-3 text-sm font-semibold whitespace-nowrap">
                                        <a href="{{ route($devisRoutePrefix . 'devis.downloadDevis', $d->devis_number) }}" class="text-blue-600 hover:text-blue-800">
                                            {{$entreprise_infos->devis_prefix}}-{{$d->devis_number}}
                                        </a>
                                    </td>
                                    <td data-label="{{ __('messages.Client Name / Emails') }}" data-span="2" class="px-4 py-3 text-sm text-gray-900 whitespace-normal break-words">
                                        @php $client = $devisClients->get($d->client_id); @endphp
                                        @if (!$client)
                                            @foreach (explode(',', $d->emails) as $email)
                                                <span class="block">{{ $email }}</span>
                                            @endforeach
                                        @else
                                            {{$client->name}}
                                        @endif
                                    </td>
                                    <td data-label="{{ __('messages.Budget label devis') }}" class="py-4 px-4 text-sm font-medium text-gray-900 text-right">€{{ number_format($d->budget, 2, ',', ' ') }}</td>
                                    @php
                                        $totalBilledForDevis = $singleDevisOnProject
                                            ? $totalBilled
                                            : (float) ($devisInvoicesTotal->get($d->id)?->total ?? 0);
                                        $unbilledBudget = ($d->budget ?? 0) - $totalBilledForDevis;
                                    @endphp
                                    <td data-label="{{ __('messages.Billed Budget') ?? 'Budget Facturé' }}" class="py-4 px-4 text-sm font-medium text-gray-900 text-right">
                                        €{{ number_format($totalBilledForDevis, 2, ',', ' ') }}
                                    </td>
                                    <td data-label="{{ __('messages.Unbilled Budget') }}" class="py-4 px-4 text-sm font-medium text-gray-900 text-right">
                                        €{{ number_format(max($unbilledBudget, 0), 2, ',', ' ') }}
                                    </td>
                                    <td data-label="{{ __('messages.Created at') }}" class="py-4 px-4 text-sm text-gray-700 text-center whitespace-nowrap">{{$d->created_at->format('Y-m-d')}}</td>
                                    <td data-label="{{ __('messages.Acceptance') }}" class="py-4 px-4 text-center whitespace-nowrap">
                                        @if($d->acceptanceStatus == 1)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">{{ __('messages.Accepted') }}</span>
                                        @elseif($d->acceptanceStatus == 2)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">{{ __('messages.Declined') }}</span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">{{ __('messages.Pending') }}</span>
                                        @endif
                                    </td>
                                    <td data-label="{{ __('messages.Actions') }}" data-span="2" class="py-4 px-4">
                                        <div class="devis-actions flex items-center justify-center gap-3">
                                            <a href="{{ route($devisRoutePrefix . 'devis.downloadDevis', $d->devis_number) }}" class="p-1 text-blue-600 hover:text-blue-800 rounded-md hover:bg-gray-100" title="{{ __('messages.Download') }}">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                                </svg>
                                            </a>
                                            @can('Send devis')
                                            <form action="{{ route($devisRoutePrefix . 'devis.sendDevis') }}" method="post" class="inline-block">
                                                @csrf
                                                <input type="hidden" name="devis_id" value="{{ $d->id }}">
                                                <button type="submit" class="p-1 text-green-600 hover:text-green-800 rounded-md hover:bg-gray-100" title="{{ __('messages.Send') }}">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                                    </svg>
                                                </button>
                                            </form>
                                            @endcan
                                            @can('Delete devis')
                                            <form action="{{route($devisRoutePrefix . 'devis.destroy', $d->id)}}" method="post" onsubmit="return confirm('{{ __('messages.Are you sure?') }}');" class="inline-block">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-1 text-red-600 hover:text-red-800 rounded-md hover:bg-gray-100" title="{{ __('messages.Delete') }}">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </form>
                                            @endcan
                                            <a href="{{route($devisRoutePrefix . 'devis.editDevis', $d->id)}}" class="p-1 text-cyan-600 hover:text-cyan-800 rounded-md hover:bg-gray-100" title="{{ __('messages.Edit Devis') }}">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="py-8 text-center text-gray-500">{{ __('messages.No devis found') }}</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    </div>
                </div>
                @endif
                @endcan
                
                {{-- 5. Project Files Table (Original Logic: Admin/Super Admin only) --}}
                @if(Auth::user()->hasRole('Admin') || Auth::user()->hasRole('Super Admin'))
                @php
                    $fileRoutePrefix = 'admin.';
                @endphp
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="p-6 border-b border-gray-200 flex items-center justify-between gap-3">
                        <h2 class="text-lg font-semibold text-gray-900">{{ __('messages.Project Files') }}</h2>
                        <button type="button" class="section-toggle inline-flex items-center gap-2 text-sm font-semibold text-gray-600 hover:text-gray-900"
                                data-target="projectFilesBody" aria-expanded="false">
                            <span class="hidden lg:inline">{{ __('messages.Show') ?? 'Show' }}</span>
                            <svg class="section-chevron w-5 h-5 transition-transform" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6" />
                            </svg>
                        </button>
                    </div>
                    
                    <div id="projectFilesBody" class="hidden">
                    <style>
                        /* Project Files: keep Actions visible on mobile */
                        @media (max-width: 1024px) {
                            .project-files-table th,
                            .project-files-table td {
                                padding-left: 12px !important;
                                padding-right: 12px !important;
                            }

                            .project-files-uploaded-col { display: none; }

                            .project-files-actions-col {
                                width: 1%;
                                white-space: nowrap;
                            }
                        }
                    </style>
                    <div class="p-4 sm:p-6">
                        <form action="{{ route($fileRoutePrefix . 'projects.uploadFile', $project->id) }}" method="POST" enctype="multipart/form-data" class="flex flex-col sm:flex-row sm:items-center gap-3">
                            @csrf
                            <input type="file" name="file" required class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            <button type="submit" class="inline-flex justify-center bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-lg text-sm shadow-sm w-full sm:w-auto">{{ __('messages.Upload') }}</button>
                        </form>
                    </div>
                
                    <div class="px-4 sm:px-6 pb-4 sm:pb-6">
                        <div class="rounded-xl border border-slate-200 overflow-hidden bg-white">
                            <div class="overflow-x-visible" style="-webkit-overflow-scrolling: touch;">
                                <table class="project-files-table w-full table">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600 uppercase">{{ __('messages.File Name') }}</th>
                                            <th class="project-files-uploaded-col px-4 py-3 text-left text-sm font-semibold text-gray-600 uppercase">{{ __('messages.Uploaded At') }}</th>
                                            <th class="project-files-actions-col px-4 py-3 text-center text-sm font-semibold text-gray-600 uppercase">{{ __('messages.Action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200">
                                        @forelse($Project_files as $file)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-3 text-sm text-gray-900 whitespace-normal break-words">
                                                <a href="{{ route($fileRoutePrefix . 'projects.downloadfiles', $file->file_name) }}" class="text-blue-600 hover:text-blue-800">{{ $file->file_name }}</a>
                                            </td>
                                            <td class="project-files-uploaded-col px-4 py-3 text-sm text-gray-600 whitespace-nowrap">
                                                {{ $file->created_at->format('Y-m-d H:i') }}
                                            </td>
                                            <td class="project-files-actions-col px-4 py-3">
                                                <div class="flex items-center justify-center gap-4">
                                                    <a href="{{ route($fileRoutePrefix . 'projects.downloadfiles', $file->file_name) }}" class="p-1 text-blue-600 hover:text-blue-800 rounded-md hover:bg-gray-100" title="{{ __('messages.Download') }}">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                                        </svg>
                                                    </a>
                                                    <form action="{{ route($fileRoutePrefix . 'projects.deletefiles', $file->file_name) }}" method="POST" onsubmit="return confirm('{{ __('messages.Are you sure?') }}');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="p-1 text-red-600 hover:text-red-800 rounded-md hover:bg-gray-100" title="{{ __('messages.Delete') }}">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                            </svg>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="3" class="py-8 text-center text-gray-500">{{ __('messages.No files uploaded') }}</td>
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

                {{-- 6. Contacts Section (Converted to Alpine.js) --}}
                @can('view contacts')
                @php
                    $project_contacts=App\Models\client_contacts_projects::where('project_id',$project->id)->get();
                    $contactIds = $project_contacts->pluck('contact_id');
                    $contacts = App\Models\client_contacts::whereIn('id', $contactIds)->get();
                @endphp
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">{{ __('messages.Contacts Infos') }}</h2>
                        <p class="text-sm text-gray-600 mt-1">{{ __('messages.Mandatory informations') }}</p>
                    </div>
                    <div class="divide-y divide-gray-200">
                        @foreach ($contacts as $contact)
                        <div x-data="{ open: false }">
                            {{-- Accordion Header --}}
                            <button @click="open = !open" class="w-full flex justify-between items-center p-6 text-left hover:bg-gray-50">
                                <div class="flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-600 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v11.494m-5.747-8.606l-.001 5.72m11.495-5.72l-.001 5.72M3 10.044v5.72c0 1.25.803 2.335 1.95 2.684A19.46 19.46 0 0012 21c2.148 0 4.217-.36 6.05-1.002 1.147-.35 1.95-1.435 1.95-2.685v-5.72c0-1.25-.803-2.335-1.95-2.684A19.46 19.46 0 0012 6.309a19.463 19.463 0 00-6.05 1.051C4.803 7.71 4 8.794 4 10.044z" /></svg>
                                    <span class="text-base font-semibold text-gray-900">{{ ucfirst($contact->name) }}</span>
                                </div>
                                <svg x-bind:class="{ 'rotate-180': open }" class="h-5 w-5 text-gray-500 transform transition-transform" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                            </button>
                            {{-- Accordion Content --}}
                            <div x-show="open" x-transition class="p-6 bg-gray-50 border-t border-gray-200">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Full Name') }}:</label>
                                        <input class="block w-full rounded-lg border-gray-300 shadow-sm sm:text-sm py-2 px-3 bg-gray-100" type="text" value="{{$contact->name}}" readonly />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Functionality') }}:</label>
                                        <input class="block w-full rounded-lg border-gray-300 shadow-sm sm:text-sm py-2 px-3 bg-gray-100" type="text" value="{{$contact->functionality}}" readonly />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Mobile Fix') }}:</label>
                                        <input class="block w-full rounded-lg border-gray-300 shadow-sm sm:text-sm py-2 px-3 bg-gray-100" type="text" value="{{$contact->mobile_fix}}" readonly />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Email') }}:</label>
                                        <input class="block w-full rounded-lg border-gray-300 shadow-sm sm:text-sm py-2 px-3 bg-gray-100" type="text" value="{{$contact->email}}" readonly />
                                    </div>
                                    <div class="sm:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Address') }}:</label>
                                        <input class="block w-full rounded-lg border-gray-300 shadow-sm sm:text-sm py-2 px-3 bg-gray-100" type="text" value="{{$contact->address}}" readonly />
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endcan

                {{-- 7. Teams Section --}}
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($teams as $team)
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 flex flex-col">
                        <div class="p-6 border-b border-gray-200 flex justify-between items-start">
                            <div>
                                <h5 class="text-lg font-semibold text-gray-900 capitalize">{{$team->team_name}}</h5>
                                <p class="text-sm font-medium text-gray-500">Team Members:</p>
                            </div>
                            @php $teamRoute = '#'; @endphp
                            @if(Auth::user()->hasRole('Admin')) @php $teamRoute = route('admin.teams.index'); @endphp @endif
                            @if(Auth::user()->hasRole('Responsable de projet')) @php $teamRoute = route('Responsable_de_projet.teams.index'); @endphp @endif
                            @if(Auth::user()->hasRole('Employee')) @php $teamRoute = route('employee.teams.index'); @endphp @endif
                            @if(Auth::user()->hasRole('Responsable de projet et gestionnaire')) @php $teamRoute = route('Responsable_gestionnaire.teams.index'); @endphp @endif
                            <a href="{{ $teamRoute }}" class="inline-flex justify-center bg-blue-600 hover:bg-blue-700 text-white font-medium px-3 py-1 rounded-lg text-xs shadow-sm">View</a>
                        </div>
                        
                        <div class="p-6 space-y-4">
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
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    @if($employee->image)
                                        <img class="h-10 w-10 rounded-full mr-3 object-cover" src="{{ Storage::disk('public')->url('employee_images/' . $employee->image) }}" alt="employee Image">
                                    @else
                                        <div class="h-10 w-10 rounded-full mr-3 flex items-center justify-center text-white font-semibold" style="background-color: {{ $color }};">
                                            <span>{{ $initials }}</span>
                                        </div>
                                    @endif
                                    <div>
                                        <a href="{{ $employeeShowRoute }}" class="text-sm font-semibold text-gray-900 hover:text-blue-600">{{$team_member->employee_name}}</a>
                                        @if($employee_user->is_loggedIn == 0)
                                            <span class="block text-xs text-red-600">Off Work</span>
                                        @elseif($employee_user->is_loggedIn == 1)
                                            <span class="block text-xs text-green-600">On Work</span>
                                        @else
                                            <span class="block text-xs text-yellow-600">On Pause</span>
                                        @endif
                                    </div>
                                </div>
                                @can('View employee')
                                <a href="{{ $employeeShowRoute }}" class="inline-flex justify-center bg-white hover:bg-gray-100 text-gray-700 font-medium px-3 py-1 rounded-lg text-xs border border-gray-300 shadow-sm">View</a>
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

    {{-- 1. Edit Date/Time Modal --}}
    <div x-data="editDateModal()" x-cloak x-show="show" 
         @open-edit-date-modal.window="open($event.detail)"
         class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50">
        <div @click.outside="show = false" class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
            <div class="flex items-center justify-between p-4 border-b">
                <h5 class="text-lg font-semibold">Edit Date and Time</h5>
                <button @click="show = false" type="button" class="text-gray-400 hover:text-gray-600">&times;</button>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label for="dateInput" class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                    <input type="date" x-model="date" id="dateInput" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3">
                </div>
                <div>
                    <label for="timeInput" class="block text-sm font-medium text-gray-700 mb-1">Time (HH:MM)</label>
                    <input type="time" x-model="time" id="timeInput" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3">
                </div>
            </div>
            <div class="flex items-center justify-end p-4 bg-gray-50 border-t rounded-b-lg space-x-2">
                <button @click="show = false" type="button" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-100">Close</button>
                <button @click="save()" type="button" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">Save changes</button>
            </div>
        </div>
    </div>

    {{-- 2. Move Duration Modal --}}
    <div x-data="moveDurationModal()" x-cloak x-show="show"
         @open-move-modal.window="open($event.detail)"
         class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50">
        <div @click.outside="show = false" class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
            <div class="flex items-center justify-between p-4 border-b">
                <h5 class="text-lg font-semibold">Move Duration</h5>
                <button @click="show = false" type="button" class="text-gray-400 hover:text-gray-600">&times;</button>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label for="projectSelect" class="block text-sm font-medium text-gray-700 mb-1">Project</label>
                    <select x-model="projectId" @change="loadTasks()" id="projectSelect" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3 form-select-custom">
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}">{{ $project->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="taskSelect" class="block text-sm font-medium text-gray-700 mb-1">Task</label>
                    <select x-model="taskId" id="taskSelect" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3 form-select-custom">
                    </select>
                </div>
                <div>
                    <label for="employeeSelect" class="block text-sm font-medium text-gray-700 mb-1">Employee</label>
                    <select x-model="employeeId" id="employeeSelect" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3 form-select-custom">
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="moveStartDate" class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                    <input type="datetime-local" x-model="startDate" @input="calculateDuration()" id="moveStartDate" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3">
                </div>
                <div>
                    <label for="moveEndDate" class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                    <input type="datetime-local" x-model="endDate" @input="calculateDuration()" id="moveEndDate" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3">
                </div>
                <div>
                    <label for="moveDuration" class="block text-sm font-medium text-gray-700 mb-1">Duration (HH:MM)</label>
                    <input type="text" x-model="duration" id="moveDuration" @input="validateDurationFormat"
                           class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3"
                           placeholder="00h:00m" pattern="^(\d+)h:(\d{2})m$">
                </div>
            </div>
            <div class="flex items-center justify-end p-4 bg-gray-50 border-t rounded-b-lg space-x-2">
                <button @click="show = false" type="button" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-100">Close</button>
                <button @click="saveMove()" type="button" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">Save changes</button>
            </div>
        </div>
    </div>

    {{-- 3. Manual Timer Modal --}}
    <div x-data="manualTimerModal()" x-cloak x-show="show"
         @open-manual-timer-modal.window="open($event.detail)"
         class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50">
        <div @click.outside="show = false" class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
            <div class="flex items-center justify-between p-4 border-b">
                <h5 class="text-lg font-semibold">{{ __('messages.Add Manual Time') }}</h5>
                <button @click="show = false" type="button" class="text-gray-400 hover:text-gray-600">&times;</button>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label for="manualEmployeeSelect" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Employee') }}</label>
                    <select x-model="employeeId" id="manualEmployeeSelect" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3 form-select-custom">
                        <option value="">{{ __('messages.Select Employee') }}</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="manualStartDate" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Start Date and Time') }}</label>
                    <input type="datetime-local" x-model="startDate" id="manualStartDate" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3">
                </div>
                <div>
                    <label for="manualEndDate" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.End Date and Time') }}</label>
                    <input type="datetime-local" x-model="endDate" id="manualEndDate" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3">
                </div>
            </div>
            <div class="flex items-center justify-end p-4 bg-gray-50 border-t rounded-b-lg space-x-2">
                <button @click="show = false" type="button" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-100">{{ __('messages.Close button') }}</button>
                <button @click="save()" type="button" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">{{ __('messages.Save Timer') }}</button>
            </div>
        </div>
    </div>
    
    {{-- 4. Export Report Modal --}}
    <div x-data="exportReportModal()" x-cloak x-show="show"
         @open-export-modal.window="open($event.detail.isCustomer)"
         class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50">
        <div @click.outside="show = false" class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
            <div class="flex items-center justify-between p-4 border-b">
                <h5 class="text-lg font-semibold" x-text="isCustomerReport ? 'Export for Customer' : 'Export Report'"></h5>
                <button @click="show = false" type="button" class="text-gray-400 hover:text-gray-600">&times;</button>
            </div>
            <div class="p-6">
                <p class="text-sm text-gray-700">The report will use the Start Date and End Date you selected. If no dates are selected, all records will be downloaded.</p>
            </div>
            <div class="flex items-center justify-end p-4 bg-gray-50 border-t rounded-b-lg space-x-2">
                <button @click="show = false" type="button" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-100">Cancel</button>
                <button @click="download()" type="button" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">Download PDF</button>
            </div>
        </div>
    </div>
    
    {{-- 5. Send Invoice Modal (for Invoices Table) --}}
    <div x-data="sendInvoiceModal()" x-cloak x-show="show" 
         @open-send-invoice-modal.window="open($event.detail.invoiceId)"
         class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50">
        <div x-show="show" x-transition @click.outside="show = false" class="bg-white rounded-lg shadow-xl w-full max-w-lg mx-4">
            <div class="flex items-center justify-between p-4 border-b">
                <h5 class="text-lg font-semibold">{{ __('messages.Send Invoice') }}</h5>
                <button @click="show = false" type="button" class="text-gray-400 hover:text-gray-600">&times;</button>
            </div>
            <form id="sendInvoiceForm" @submit.prevent="submitForm">
                @csrf
                <div class="p-6 space-y-4">
                    <div>
                        <h6 class="text-sm font-medium text-gray-700 mb-2">Select Recipients:</h6>
                        <div id="recipientsList" class="space-y-2 p-3 border border-gray-200 rounded-lg bg-gray-50 h-40 overflow-y-auto"></div>
                    </div>
                    <div>
                        <label for="fileUpload" class="block text-sm font-medium text-gray-700 mb-2">Upload Additional Files (Optional):</label>
                        <input type="file" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" id="fileUpload" name="file[]" multiple>
                    </div>
                </div>
                <div class="flex items-center justify-end p-4 bg-gray-50 border-t rounded-b-lg space-x-2">
                    <button @click="show = false" type="button" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-100">Close</button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">{{ __('messages.Send Invoice') }}</button>
                </div>
            </form>
        </div>
    </div>
    
    {{-- 5. Partial Credit Note Modal (for Invoices Table) --}}
    <div x-data="partialCreditNoteModal()" x-cloak x-show="show" 
         @open-partial-credit-modal.window="open($event.detail.invoiceId, $event.detail.totalCost)"
         class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50">
        <div x-show="show" x-transition @click.outside="show = false" class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
            <div class="flex items-center justify-between p-4 border-b">
                <h5 class="text-lg font-semibold">{{ __('messages.Partial Credit Note') }}</h5>
                <button @click="show = false" type="button" class="text-gray-400 hover:text-gray-600">&times;</button>
            </div>
            <form @submit.prevent="submitForm">
                <div class="p-6">
                    <label for="partialAmount" class="block text-sm font-medium text-gray-700 mb-2">Enter Amount:</label>
                    <input type="number" x-model="amount" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" id="partialAmount" step="0.0001" required>
                    <span x-show="error" x-text="errorMessage" class="text-red-600 text-sm mt-1"></span>
                </div>
                <div class="flex items-center justify-end p-4 bg-gray-50 border-t rounded-b-lg space-x-2">
                    <button @click="show = false" type="button" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-100">Close</button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">Submit</button>
                </div>
            </form>
        </div>
    </div>

    {{-- 6. Move Task Modal --}}
    <div x-data="moveTaskModal()" x-cloak x-show="show" 
         @open-move-task-modal.window="open($event.detail.taskId, $event.detail.currentProjectId)"
         class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50">
        <div @click.outside="show = false" class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
            <div class="flex items-center justify-between p-4 border-b">
                <h5 class="text-lg font-semibold">{{ __('messages.Move Task') }}</h5>
                <button @click="show = false" type="button" class="text-gray-400 hover:text-gray-600">&times;</button>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label for="moveProjectSelect" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Select Project label') }}</label>
                    <select x-model="selectedProjectId" id="moveProjectSelect" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3 form-select-custom">
                        <option value="">{{ __('messages.Select a project option') }}</option>
                        <template x-for="proj in availableProjects" :key="proj.id">
                            <option :value="proj.id" x-text="proj.name"></option>
                        </template>
                    </select>
                </div>
                <p class="text-sm text-gray-600">{{ __('messages.Move task description') }}</p>
            </div>
            <div class="flex items-center justify-end p-4 bg-gray-50 border-t rounded-b-lg space-x-2">
                <button @click="show = false" type="button" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-100">{{ __('messages.Cancel') }}</button>
                <button @click="moveTask()" type="button" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">{{ __('messages.Move Task') }}</button>
            </div>
        </div>
    </div>

    {{-- 7. Send Devis Modal --}}
    <div id="sendDevisModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-lg mx-4">
            <div class="flex justify-between items-center p-4 border-b">
                <h5 class="text-lg font-semibold text-gray-800" id="sendDevisModalLabel">Send Devis</h5>
                <button type="button" id="closeSendDevisModal" class="text-gray-400 hover:text-gray-600">&times;</button>
            </div>
            <div class="p-6">
                <form id="sendDevisForm" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="devis_id" id="devis_id">
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Select Recipients:</label>
                        <div id="devisRecipientsList" class="border p-3 rounded-md h-32 overflow-y-auto bg-gray-50 space-y-2">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="attachments" class="block text-sm font-medium text-gray-700 mb-1">Additional Attachments (PDF only):</label>
                        <input type="file" class="block w-full text-sm text-gray-700 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none file:bg-gray-100 file:border-0 file:px-3 file:py-2" id="attachments" name="attachments[]" multiple accept=".pdf">
                        <small class="text-gray-500">Select PDF files to be merged with the devis</small>
                    </div>
                </form>
            </div>
            <div class="flex justify-end items-center p-4 bg-gray-50 border-t rounded-b-lg space-x-2">
                <button type="button" id="cancelSendDevisModal" class="px-4 py-2 bg-white border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50">Close</button>
                <button type="button" class="px-4 py-2 bg-blue-600 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-blue-700" id="confirmSend">Send Devis</button>
            </div>
        </div>
    </div>

    {{-- SCRIPTS --}}
    @include('dashboard_layouts.scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.3.1/jspdf.umd.min.js"></script>
    <script src="https://unpkg.com/jspdf-autotable@3.5.17/dist/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.4/xlsx.full.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    {{-- Main Page Logic --}}
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
                            else { span.textContent = originalText; alert(json.message || 'Failed to update'); }
                        }).catch(err => { span.textContent = originalText; alert('Network error'); })
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
                recipientsList.innerHTML = '<div class="text-center">Loading...</div>';
                
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
                                    <input class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" type="checkbox" name="recipients[]" value="${contact.email}" id="recipient_${contact.id}" checked>
                                    <label class="ml-2 block text-sm text-gray-900" for="recipient_${contact.id}">
                                        ${contact.name} (${contact.email})
                                    </label>`;
                                recipientsList.appendChild(div);
                            });
                        } else {
                            recipientsList.innerHTML = '<p class="text-gray-500">No contacts found for this client</p>';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        recipientsList.innerHTML = '<p class="text-red-600">Error loading contacts</p>';
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
                    alert('Please select at least one recipient');
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
                        alert('Devis sent successfully!');
                        if (sendDevisModalEl) sendDevisModalEl.classList.add('hidden');
                        location.reload();
                    } else {
                        alert('Error sending devis: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while sending the devis');
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

    {{-- Stop Comment Modal --}}
    <div id="stopCommentModal" class="modal">
        <div class="modal-content-custom">
            <span id="stopCommentClose" class="close-button">&times;</span>
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
                    <label for="stopCommentText" class="block text-sm font-medium text-slate-700">{{ __('messages.Comment') }} <span class="text-red-500">*</span></label>
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
                <textarea id="stopCommentText" rows="3" maxlength="500" placeholder="{{ __('messages.Describe what you worked on...') }}" spellcheck="true" class="border border-slate-300 rounded-lg px-3 py-2 w-full text-sm focus:outline-none focus:ring-2 focus:ring-red-400 resize-none transition-colors"></textarea>
                <p id="stopCommentError" class="text-xs text-red-500 mt-1 hidden">{{ __('messages.Comment is required to stop the task.') }}</p>
                <p id="stopCommentAiFeedback" class="text-xs mt-1 hidden"></p>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" id="stopCommentCancelBtn" class="px-4 py-2 text-sm font-medium text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors">{{ __('messages.Cancel') }}</button>
                <button type="button" id="stopCommentConfirmBtn" class="px-4 py-2 text-sm font-medium text-white bg-red-500 rounded-lg hover:bg-red-600 transition-colors opacity-50 cursor-not-allowed" disabled>{{ __('messages.Confirm Stop') }}</button>
            </div>
        </div>
    </div>

    {{-- Spell-Check Confirmation Modal --}}
    <div id="spellCheckModal" class="modal" style="z-index: 60;">
        <div class="modal-content-custom" style="max-width: 420px;">
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

    {{-- Translate Confirmation Modal --}}
    <div id="translateCheckModal" class="modal" style="z-index: 60;">
        <div class="modal-content-custom" style="max-width: 420px;">
            <div class="flex items-center justify-center mb-3">
                <div class="bg-blue-100 rounded-full p-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5c-.006 3.125-1.026 6.042-2.751 8.498m-1.782-2.5A18.022 18.022 0 014.888 5h7.863z" />
                    </svg>
                </div>
            </div>
            <h2 class="text-lg font-semibold text-slate-800 mb-2">{{ __('messages.Translate to Dutch?') }}</h2>
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
</body>
</html>
