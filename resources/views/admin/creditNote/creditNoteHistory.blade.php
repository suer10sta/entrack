<!DOCTYPE html>
<html lang="en">

@include('dashboard_layouts.head')

<style>
  a {
    color: blue;
}
.font-weight-bolder{
    color : black !important;
}
th {
    font-size: 14px !important; 
}
.text-sm{
    color: #332f2f;
}
</style>
<body class="g-sidenav-show bg-gray-100">
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-NKDMSK6" height="0" width="0"
            style="display: none; visibility: hidden"></iframe></noscript>
    <div class="min-height-300 bg-primary position-absolute w-100"></div>

    @if(Auth::user()->hasRole('Responsable de projet') || Auth::user()->hasRole('Employee') || Auth::user()->hasRole('Responsable de projet et gestionnaire'))
        @include('employees.sidebar')
    @endif
    @if(Auth::user()->hasRole('Admin'))
        @include('admin.sidebar')
    @endif

    <main class="main-content position-relative border-radius-lg">
        <nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl z-index-sticky"
            id="navbarBlur" data-scroll="false">
            <div class="container-fluid py-1 px-3">
                @include('dashboard_layouts.navbar')
            </div>
        </nav>
        <div class="container-fluid py-4">
            <div class="row mt-3">
                <div class="col-12">
                    <div class="card">
                    <div class="card-header p-3 pb-0">
                        <div class="d-flex justify-content-between align-items-start">
                            <h6>Credit Note History Table</h6>
                            <div class="d-flex align-items-start">
                                <div x-data="datePicker()" class="d-flex align-items-center">
                                    <div class="me-2">
                                        <label for="startDate" class="form-label">Start Date:</label>
                                        <input type="date" id="startDate" class="form-control" x-model="startDate">
                                    </div>
                                    <div class="me-2">
                                        <label for="endDate" class="form-label">End Date:</label>
                                        <input type="date" id="endDate" class="form-control" x-model="endDate">
                                    </div>
                                    <div>
                                        <label class="form-label d-block">&nbsp;</label> <!-- Adjusted for alignment -->
                                        <a href="#" @click.prevent="updateHref" class="btn btn-primary mt-2" id="downloadLink">Credit Note Reports PDF</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @php
                            $total = App\Models\credit_note::where('entreprise_id', Auth::user()->entreprise_id)->sum('total_ttc');                          
                        @endphp
                        <div class="d-flex flex-column align-items-end ms-3"> <!-- Adjusted for alignment and spacing -->
                            <div class="d-flex align-items-center">
                                <div>
                                    <label for="totalPaid" class="form-label">Total : -€{{number_format($total, 2, ',', '')}} </label>
                                </div>
                               
                            </div>
                        </div>
                    </div>
                        @if(session('message'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('message') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">X</button>
                    </div>
                  @endif

                        <div class="row mb-3 p-4">
                            <div class="col-md-3">
                                <label for="clientFilter" class="form-label">Filter by Client:</label>
                                <select id="clientFilter" class="form-select">
                                    <option value="">All Clients</option>
                                    <!-- Populate dropdown with unique client names -->
                                    @foreach($clients as $client)
                                    <option value="{{$client->name}}">{{$client->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="projectFilter" class="form-label">Filter by Project:</label>
                                <select id="projectFilter" class="form-select">
                                    <option value="">All Projects</option>
                                    <!-- Populate dropdown with unique project names -->
                                    @foreach($projects as $project)
                                    <option value="{{$project->name}}">{{strtoupper($project->name)}}</option>
                                    @endforeach
                                </select>
                            </div>
  <!--
                            <div class="col-md-3">
                                <label for="paymentFilter" class="form-label">Filter by Payment:</label>
                                <select id="paymentFilter" class="form-select">
                                    <option value="">All Invoices</option>
                                    <option value="Paid">Paid</option>
                                    <option value="Unpaid">Unpaid</option>
                                </select>
                            </div>
-->
                            <div class="col-md-3">
                                <label for="typeFilter" class="form-label">Filter by Type:</label>
                                <select id="typeFilter" class="form-select">
                                    <option value="">All Invoices</option>
                                    <option value="Partial Period">Partial Period</option>
                                    <option value="Full Period">Full Period</option>
                                </select>
                            </div>
                        </div>

                        <table class="table align-items-center mb-0" id="invoicesTable">
                            <thead>
                                <tr>
                                    <th
                                        class="text-center text-uppercase text-secondary text-x font-weight-bolder opacity-10">
                                        Credit Note N
                                    </th>
                                    <th
                                        class="text-center text-uppercase text-secondary text-x font-weight-bolder opacity-10">
                                        Project Name
                                    </th>
                                    <th
                                        class="text-center text-uppercase text-secondary text-x font-weight-bolder opacity-10">
                                        TVA
                                    </th>
                                    <th
                                        class="text-center text-uppercase text-secondary text-x font-weight-bolder opacity-10">
                                        Client Name
                                    </th>
                                    <th
                                        class="text-center text-uppercase text-secondary text-x font-weight-bolder opacity-10 ps-2">
                                        Start Date
                                    </th>
                                    <th
                                        class="text-center text-uppercase text-secondary text-x font-weight-bolder opacity-10">
                                        End Date
                                    </th>
                                    <th
                                        class="text-center text-uppercase text-secondary text-x font-weight-bolder opacity-10">
                                        Invoice Type
                                    </th>
                                    <th
                                        class="text-center text-uppercase text-secondary text-x font-weight-bolder opacity-10">
                                        Created at
                                    </th>
                                    <th
                                        class="text-center text-uppercase text-secondary text-x font-weight-bolder opacity-10">
                                        Cost Total
                                    </th>
                                    <th
                                        class="text-center text-uppercase text-secondary text-x font-weight-bolder opacity-10">
                                        Credit Note Type
                                    </th>
                                    <th
                                        class="text-center text-uppercase text-secondary text-x font-weight-bolder opacity-10">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($credit_notes as $credit_note)
                                <tr>
                                    <td>
                                        <p class="align-middle text-center text-sm font-weight-bold mb-0">
                                            @if(Auth::user()->hasRole('Responsable de projet'))
                                            <a href="{{ route('Responsable_de_projet.invoices.serveCreditNotePdf', $credit_note->id) }}" target="_blank">
                                                NC-{{date('ym')}}{{$credit_note->credit_note_number}}
                                            </a>
                                            @endif
                                            @if(Auth::user()->hasRole('Admin'))
                                            <a href="{{ route('admin.invoices.serveCreditNotePdf', $credit_note->id ) }}" target="_blank">
                                                NC-{{date('ym')}}{{$credit_note->credit_note_number}}
                                            </a>
                                            @endif
                                            @if(Auth::user()->hasRole('Employee'))
                                            <a href="{{ route('employee.invoices.serveCreditNotePdf', $credit_note->id) }}" target="_blank">
                                                NC-{{date('ym')}}{{$credit_note->credit_note_number}}
                                            </a>
                                            @endif
                                            @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                            <a href="{{ route('Responsable_gestionnaire.invoices.serveCreditNotePdf', $credit_note->id) }}" target="_blank">
                                                NC-{{date('ym')}}{{$credit_note->credit_note_number}}
                                            </a>
                                            @endif
                                        </p>
                                    <td>
                                        <p class="align-middle text-center text-sm font-weight-bold mb-0">
                                            {{strtoupper($credit_note->project_name)}}  
                                        </p>
                                    </td>
                                    <td>
                                        <p class="align-middle text-center text-sm font-weight-bold mb-0">
                                            @php 
                                                $project = App\Models\Project::find($credit_note->project_id);
                                            @endphp
                                            {{$project->tva}}%
                                        </p>
                                    </td>
                                    <td>
                                        <p class="align-middle text-center text-sm font-weight-bold mb-0">
                                            @php
                                            $client = \App\Models\Client::where('id', $credit_note->client_id)->first();
                                            @endphp
                                            {{$client->name}}
                                        </p>
                                    </td>
                                    <td>
                                        <p class="text-center text-sm font-weight-bold mb-0">{{$credit_note->start_date}}
                                        </p>
                                    </td>
                                    <td>
                                        <p class="text-center text-sm font-weight-bold mb-0">{{$credit_note->end_date}}</p>
                                    </td>
                                    <td>
                                        <p class="text-center text-sm font-weight-bold mb-0">{{$credit_note->type}}</p>
                                    </td>
                                    <td>
                                        <p class="text-center text-sm font-weight-bold mb-0">{{$credit_note->created_at}}
                                        </p>
                                    </td>
                                    <td>
                                        <p class="text-center text-sm font-weight-bold mb-0">-€{{number_format($credit_note->total_ttc, 2, '.', '')}}
                                        </p>
                                    </td>
                                    <td>
                                        <p class="text-center text-sm font-weight-bold mb-0">{{$credit_note->credit_note_type}}
                                        </p>
                                    </td>
                                    <td>
                                        <div class="card-footer d-flex justify-content-end">
                                            <!--
                                            <a class="btn btn-dark m-2" href="{{ route('admin.invoice.sendInvoice', ['invoice_id' => $credit_note->id]) }}">
                                                Send
                                            </a>
                                            -->

                                            @if(Auth::user()->hasRole('Responsable de projet'))
                                                <a class="btn btn-primary btn-xs p-2 m-2 mb-0"
                                                    href="{{ route('Responsable_de_projet.invoices.serveCreditNotePdf', $credit_note->id) }}">
                                                    View & download
                                                </a>
                                                
                                                <form action="{{route('Responsable_de_projet.credit_note.destroy',$credit_note->id) }}"
                                                    method="post" onsubmit="return confirm('Are you sure?');"
                                                    class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-xs p-2 m-2 mb-0">X</button>
                                                </form>
                                            @endif
                                            @if(Auth::user()->hasRole('Admin'))
                                                <a class="btn btn-primary btn-xs p-2 m-2 mb-0"
                                                    href="{{ route('admin.invoices.serveCreditNotePdf', $credit_note->id) }}">
                                                    View & download
                                                </a>

                                                <button type="button" class="btn btn-dark btn-xs p-2 m-2 mb-0"
                                                    data-bs-toggle="modal" data-bs-target="#sendCreditNoteModal"
                                                    data-credit-note-id="{{ $credit_note->id }}"
                                                    data-invoice-id="{{ $credit_note->invoice_id }}">
                                                    {{ $credit_note->emailsent ? 'Resend' : 'Send' }}
                                                </button>

                                                <form action="{{route('admin.credit_note.destroy',$credit_note->id) }}"
                                                    method="post" onsubmit="return confirm('Are you sure?');"
                                                    class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-xs p-2 m-2 mb-0">X</button>
                                                </form>
                                            @endif
                                            @if(Auth::user()->hasRole('Employee'))
                                                <a class="btn btn-primary btn-xs p-2 m-2 mb-0"
                                                    href="{{ route('employee.invoices.serveCreditNotePdf', $credit_note->id) }}">
                                                    View & download
                                                </a>

                                                <form action="{{route('employee.credit_note.destroy',$credit_note->id) }}"
                                                    method="post" onsubmit="return confirm('Are you sure?');"
                                                    class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-xs p-2 m-2 mb-0">X</button>
                                                </form>
                                            @endif
                                            @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                                <a class="btn btn-primary btn-xs p-2 m-2 mb-0"
                                                    href="{{ route('Responsable_gestionnaire.invoices.serveCreditNotePdf', $credit_note->id) }}">
                                                    View & download
                                                </a>

                                                <form action="{{route('Responsable_gestionnaire.credit_note.destroy',$credit_note->id) }}"
                                                    method="post" onsubmit="return confirm('Are you sure?');"
                                                    class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-xs p-2 m-2 mb-0">X</button>
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
            </div>

        </div>
    </main>

    <!-- Send Credit Note Modal -->
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
                                <!-- Recipients will be populated here -->
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

        sendCreditNoteModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var creditNoteId = button.getAttribute('data-credit-note-id');
            var invoiceId = button.getAttribute('data-invoice-id');
            var form = document.getElementById('sendCreditNoteForm');
            form.action = '{{ route("admin.credit_note.send", ":credit_note_id") }}'.replace(':credit_note_id', creditNoteId);

            // Clear and show loading state
            recipientsContainer.innerHTML = `
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            `;

            // Fetch recipients based on the credit note's invoice
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
    });
    </script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    

    <script>
    // JavaScript for filtering the table
    document.addEventListener("DOMContentLoaded", function() {
        // Get references to the filter dropdowns
        const clientFilter = document.getElementById('clientFilter');
        const projectFilter = document.getElementById('projectFilter');
        const typeFilter = document.getElementById('typeFilter');

        // Add event listeners to filter the table when selection changes
        clientFilter.addEventListener('change', filterTable);
        projectFilter.addEventListener('change', filterTable);
        typeFilter.addEventListener('change', filterTable);

        function filterTable() {
            const clientValue = clientFilter.value.toLowerCase();
            const projectValue = projectFilter.value.toLowerCase();
            const typeValue = typeFilter.value.toLowerCase();;

            // Loop through each row in the table
            const rows = document.querySelectorAll('tbody tr');
            rows.forEach(row => {
                const clientName = row.querySelector('td:nth-child(4)').textContent.trim().toLowerCase();
                const projectName = row.querySelector('td:nth-child(2)').textContent.trim().toLowerCase();
                const invoiceType = row.querySelector('td:nth-child(7)').textContent.trim().toLowerCase();

                // Show or hide the row based on filter selections
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

</body>

</html>