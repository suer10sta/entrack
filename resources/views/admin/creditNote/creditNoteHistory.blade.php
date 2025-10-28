<!DOCTYPE html>
<html lang="en"> 
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Credit Note History - BizManager</title> 
    @vite('resources/css/app.css')
    @livewireStyles
    
    <style>
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
        select:invalid {
            color: #9ca3af;
        }
        option {
            color: #111827;
        }
        
        /* Custom table styling */
        .credit-note-table th {
            font-size: 10px;
            font-weight: 600;
            letter-spacing: 0.05em;
            color: #9CA3AF;
            padding: 12px 16px;
            text-align: left;
            text-transform: uppercase;
            background-color: #F9FAFB;
        }
        
        .credit-note-table td {
            padding: 16px;
            font-size: 14px;
            color: #374151;
        }
        
        .credit-note-table tbody tr {
            border-bottom: 1px solid #E5E7EB;
            background-color: white;
        }
        
        .credit-note-table tbody tr:hover {
            background-color: #F9FAFB;
        }
        
        .credit-note-link {
            color: #2563EB;
            text-decoration: none;
            font-weight: 500;
        }
        
        .credit-note-link:hover {
            text-decoration: underline;
        }
        
        .amount-negative {
            color: #DC2626;
            font-weight: 600;
        }
        
        .btn-view {
            background-color: #2563EB;
            color: white;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
        }
        
        .btn-view:hover {
            background-color: #1D4ED8;
        }
        
        .btn-resend {
            background-color: white;
            color: #374151;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            border: 1px solid #D1D5DB;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
        }
        
        .btn-resend:hover {
            background-color: #F9FAFB;
        }
        
        .btn-delete {
            background-color: #DC2626;
            color: white;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 14px;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .btn-delete:hover {
            background-color: #B91C1C;
        }

        /* Remove any modals or overlays from view */
        .modal {
            display: none;
        }
        
        .modal.show {
            display: block;
        }
    </style>
</head>
<body class="bg-gray-50">

    <div class="flex h-screen overflow-hidden">
        {{-- Sidebar Component --}}
        <x-dashboard.sidebar />

        {{-- Main Wrapper (Navbar + Content) --}}
        <div class="flex flex-col flex-1 overflow-y-auto">

            {{-- Navbar Component --}}
            <x-dashboard.navbar />

            {{-- Main Content Area --}}
            <main class="flex-1 p-6 bg-gray-50">
                <!-- Header Section -->
                <div class="mb-6">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900 mb-1">Credit Note History</h1>
                            <p class="text-sm text-gray-500">Manage and track all credit notes</p>
                        </div>
                        
                        <div class="flex items-end gap-3">
                            <div x-data="datePicker()" class="flex items-end gap-3">
                                <div>
                                    <label for="startDate" class="block text-xs font-medium text-gray-700 mb-1">Start Date:</label>
                                    <input type="date" id="startDate" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" x-model="startDate">
                                </div>
                                <div>
                                    <label for="endDate" class="block text-xs font-medium text-gray-700 mb-1">End Date:</label>
                                    <input type="date" id="endDate" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" x-model="endDate">
                                </div>
                                <button @click.prevent="updateHref" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 flex items-center gap-2 h-[42px]">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"/>
                                    </svg>
                                    Credit Note Reports PDF
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Total Section -->
                    @php
                        $total = App\Models\credit_note::where('entreprise_id', Auth::user()->entreprise_id)->sum('total_ttc');                          
                    @endphp
                    <div class="bg-white border border-gray-200 rounded-lg p-4 flex justify-end mb-6">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-medium text-gray-600 uppercase">Total Credit Notes:</span>
                            <span class="text-xl font-bold text-red-600">-€{{number_format($total, 2, '.', '')}}</span>
                        </div>
                    </div>

                    @if(session('message'))
                        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-6 flex justify-between items-center">
                            <span>{{ session('message') }}</span>
                            <button type="button" class="text-green-600 hover:text-green-800 font-bold" onclick="this.parentElement.style.display='none'">×</button>
                        </div>
                    @endif

                    <!-- Filters Section -->
                    <div class="bg-white border border-gray-200 rounded-lg p-5 mb-6">
                        <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4">Filters</h3>
                        <div class="grid grid-cols-3 gap-6">
                            <div>
                                <label for="clientFilter" class="block text-xs font-medium text-gray-700 uppercase tracking-wide mb-2">Client</label>
                                <select id="clientFilter" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 form-select-custom">
                                    <option value="">All Clients</option>
                                    @foreach($clients as $client)
                                    <option value="{{$client->name}}">{{$client->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="projectFilter" class="block text-xs font-medium text-gray-700 uppercase tracking-wide mb-2">Project</label>
                                <select id="projectFilter" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 form-select-custom">
                                    <option value="">All Projects</option>
                                    @foreach($projects as $project)
                                    <option value="{{$project->name}}">{{strtoupper($project->name)}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="typeFilter" class="block text-xs font-medium text-gray-700 uppercase tracking-wide mb-2">Type</label>
                                <select id="typeFilter" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 form-select-custom">
                                    <option value="">All Invoices</option>
                                    <option value="Partial Period">Partial Period</option>
                                    <option value="Full Period">Full Period</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Table Section -->
                <div class="bg-white border border-gray-200 rounded-lg overflow-hidden shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="w-full credit-note-table">
                            <thead>
                                <tr>
                                    <th>Credit Note N</th>
                                    <th>Project</th>
                                    <th class="text-center">TVA</th>
                                    <th>Client</th>
                                    <th class="text-center">Start Date</th>
                                    <th class="text-center">End Date</th>
                                    <th class="text-center">Type</th>
                                    <th class="text-center">Created At</th>
                                    <th class="text-right">Amount</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($credit_notes as $credit_note)
                                <tr>
                                    <td>
                                        @if(Auth::user()->hasRole('Responsable de projet'))
                                        <a href="{{ route('Responsable_de_projet.invoices.serveCreditNotePdf', $credit_note->id) }}" target="_blank" class="credit-note-link">
                                            NC-{{date('ym')}}{{$credit_note->credit_note_number}}
                                        </a>
                                        @endif
                                        @if(Auth::user()->hasRole('Admin'))
                                        <a href="{{ route('admin.invoices.serveCreditNotePdf', $credit_note->id ) }}" target="_blank" class="credit-note-link">
                                            NC-{{date('ym')}}{{$credit_note->credit_note_number}}
                                        </a>
                                        @endif
                                        @if(Auth::user()->hasRole('Employee'))
                                        <a href="{{ route('employee.invoices.serveCreditNotePdf', $credit_note->id) }}" target="_blank" class="credit-note-link">
                                            NC-{{date('ym')}}{{$credit_note->credit_note_number}}
                                        </a>
                                        @endif
                                        @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                        <a href="{{ route('Responsable_gestionnaire.invoices.serveCreditNotePdf', $credit_note->id) }}" target="_blank" class="credit-note-link">
                                            NC-{{date('ym')}}{{$credit_note->credit_note_number}}
                                        </a>
                                        @endif
                                    </td>
                                    <td class="font-medium">{{strtoupper($credit_note->project_name)}}</td>
                                    <td class="text-center">
                                        @php 
                                            $project = App\Models\Project::find($credit_note->project_id);
                                        @endphp
                                        {{$project->tva}}%
                                    </td>
                                    <td>
                                        @php
                                        $client = \App\Models\Client::where('id', $credit_note->client_id)->first();
                                        @endphp
                                        {{$client->name}}
                                    </td>
                                    <td class="text-center">{{$credit_note->start_date}}</td>
                                    <td class="text-center">{{$credit_note->end_date}}</td>
                                    <td class="text-center">{{$credit_note->type}}</td>
                                    <td class="text-center">{{$credit_note->created_at}}</td>
                                    <td class="text-right amount-negative">-€{{number_format($credit_note->total_ttc, 2, '.', '')}}</td>
                                    <td>
                                        <div class="flex items-center justify-center gap-2">
                                            @if(Auth::user()->hasRole('Responsable de projet'))
                                                <a href="{{ route('Responsable_de_projet.invoices.serveCreditNotePdf', $credit_note->id) }}" class="btn-view">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                    </svg>
                                                    View
                                                </a>
                                                <form action="{{route('Responsable_de_projet.credit_note.destroy',$credit_note->id) }}"
                                                    method="post" onsubmit="return confirm('Are you sure?');"
                                                    class="inline-block">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn-delete">🗑</button>
                                                </form>
                                            @endif
                                            @if(Auth::user()->hasRole('Admin'))
                                                <a href="{{ route('admin.invoices.serveCreditNotePdf', $credit_note->id) }}" class="btn-view">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                    </svg>
                                                    View
                                                </a>
                                                <button type="button" class="btn-resend"
                                                    data-bs-toggle="modal" data-bs-target="#sendCreditNoteModal"
                                                    data-credit-note-id="{{ $credit_note->id }}"
                                                    data-invoice-id="{{ $credit_note->invoice_id }}">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                                    </svg>
                                                    Resend
                                                </button>
                                                <form action="{{route('admin.credit_note.destroy',$credit_note->id) }}"
                                                    method="post" onsubmit="return confirm('Are you sure?');"
                                                    class="inline-block">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn-delete">🗑</button>
                                                </form>
                                            @endif
                                            @if(Auth::user()->hasRole('Employee'))
                                                <a href="{{ route('employee.invoices.serveCreditNotePdf', $credit_note->id) }}" class="btn-view">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                    </svg>
                                                    View
                                                </a>
                                                <form action="{{route('employee.credit_note.destroy',$credit_note->id) }}"
                                                    method="post" onsubmit="return confirm('Are you sure?');"
                                                    class="inline-block">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn-delete">🗑</button>
                                                </form>
                                            @endif
                                            @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                                <a href="{{ route('Responsable_gestionnaire.invoices.serveCreditNotePdf', $credit_note->id) }}" class="btn-view">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                    </svg>
                                                    View
                                                </a>
                                                <form action="{{route('Responsable_gestionnaire.credit_note.destroy',$credit_note->id) }}"
                                                    method="post" onsubmit="return confirm('Are you sure?');"
                                                    class="inline-block">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn-delete">🗑</button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Send Credit Note Modal (Hidden by default) -->
    <div class="modal fade" id="sendCreditNoteModal" tabindex="-1"
        aria-labelledby="sendCreditNoteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="sendCreditNoteModalLabel">Send Credit Note</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <form id="sendCreditNoteForm" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Select Recipients:</label>
                            <div id="recipientsContainer" class="border rounded p-3">
                                <div class="text-center">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="fileUpload" class="form-label">Upload Additional Files (Optional):</label>
                            <input type="file" class="form-control" id="fileUpload" name="file[]" multiple>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Send Credit Note</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @include('dashboard_layouts.scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.4/xlsx.full.min.js"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        var sendCreditNoteModal = document.getElementById('sendCreditNoteModal');
        const recipientsContainer = document.getElementById('recipientsContainer');

        if (sendCreditNoteModal) {
            sendCreditNoteModal.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                var creditNoteId = button.getAttribute('data-credit-note-id');
                var invoiceId = button.getAttribute('data-invoice-id');
                var form = document.getElementById('sendCreditNoteForm');
                form.action = '{{ route("admin.credit_note.send", ":credit_note_id") }}'.replace(':credit_note_id', creditNoteId);

                recipientsContainer.innerHTML = `
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                `;

                fetch(`/admin/invoice/${invoiceId}/recipients`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.recipients && data.recipients.length > 0) {
                            recipientsContainer.innerHTML = data.recipients.map(recipient => `
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" 
                                        name="recipient_emails[]" 
                                        value="${recipient.email}" 
                                        id="recipient_${recipient.id}"
                                        ${recipient.is_comptable ? 'checked' : ''}>
                                    <label class="form-check-label" for="recipient_${recipient.id}">
                                        ${recipient.name ? recipient.name + ' (' + recipient.email + ')' : recipient.email}
                                    </label>
                                </div>
                            `).join('');
                        } else {
                            recipientsContainer.innerHTML = '<div class="alert alert-info">No recipients found</div>';
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching recipients:', error);
                        recipientsContainer.innerHTML = '<div class="alert alert-danger">Error loading recipients</div>';
                    });
            });
        }
    });
    </script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const clientFilter = document.getElementById('clientFilter');
        const projectFilter = document.getElementById('projectFilter');
        const typeFilter = document.getElementById('typeFilter');

        if (clientFilter) clientFilter.addEventListener('change', filterTable);
        if (projectFilter) projectFilter.addEventListener('change', filterTable);
        if (typeFilter) typeFilter.addEventListener('change', filterTable);

        function filterTable() {
            const clientValue = clientFilter.value.toLowerCase();
            const projectValue = projectFilter.value.toLowerCase();
            const typeValue = typeFilter.value.toLowerCase();

            const rows = document.querySelectorAll('tbody tr');
            rows.forEach(row => {
                const clientName = row.querySelector('td:nth-child(4)').textContent.trim().toLowerCase();
                const projectName = row.querySelector('td:nth-child(2)').textContent.trim().toLowerCase();
                const invoiceType = row.querySelector('td:nth-child(7)').textContent.trim().toLowerCase();

                if ((clientValue === '' || clientName.includes(clientValue)) &&
                    (projectValue === '' || projectName.includes(projectValue)) &&
                    (typeValue === '' || invoiceType === typeValue)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }
    });
    </script>

    <script src="//unpkg.com/alpinejs" defer></script>
    <script>
    function datePicker() {          
        return {
            startDate: '',
            endDate: '',
            updateHref() {
                let baseUrl = '';
                @if(Auth::user()->hasRole('Responsable de projet'))
                baseUrl = "{{ route('Responsable_de_projet.creditNote.downloadRepports') }}";
                @endif
                @if(Auth::user()->hasRole('Admin'))
                baseUrl = "{{ route('admin.creditNote.downloadRepports') }}";
                @endif
                @if(Auth::user()->hasRole('Employee'))
                baseUrl = "{{ route('employee.creditNote.downloadRepports') }}";
                @endif
                @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                baseUrl = "{{ route('Responsable_gestionnaire.creditNote.downloadRepports') }}";
                @endif
                
                let urlWithParams = baseUrl;
                if (this.startDate && this.endDate) {
                    urlWithParams += `?startDate=${this.startDate}&endDate=${this.endDate}`;
                }
                window.location.href = urlWithParams;
            }
        }
    }
    </script>

    @livewireScripts
</body>
</html>