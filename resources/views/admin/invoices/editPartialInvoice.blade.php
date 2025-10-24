<!DOCTYPE html>
<html lang="en">

@include('dashboard_layouts.head')

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

            <div class="container-fluid py-4">
                <div class="row">
                    <div class="col-lg-9 col-12 mx-auto">
                        <div class="card card-body mt-4">
                            <h6 class="mb-0">Edit Partial Invoice {{ $entreprise->invoice_prefix }}-{{ $invoice->number }}</h6>
                            <p class="text-sm mb-0">Update the partial invoice details</p>
                            @if(session('message'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('message') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close">X</button>
                            </div>
                            @endif
                            <hr class="horizontal dark my-3" />
                            @if(Auth::user()->hasRole('Admin'))
                            <form method="POST" action="{{ route('admin.invoices.updatePartialInvoice', $invoice->id) }}">
                            @endif
                            @if(Auth::user()->hasRole('Responsable de projet'))
                            <form method="POST" action="{{ route('Responsable_de_projet.invoices.updatePartialInvoice', $invoice->id) }}">
                            @endif
                            @if(Auth::user()->hasRole('Employee'))
                            <form method="POST" action="{{ route('employee.invoices.updatePartialInvoice', $invoice->id) }}">
                            @endif
                            @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                            <form method="POST" action="{{ route('Responsable_gestionnaire.invoices.updatePartialInvoice', $invoice->id) }}">
                            @endif
                                @csrf
                                @method('PUT')

                                <div class="form-group mb-3">
                                    <label for="project_id">Project</label>
                                    <select name="project_id" id="project_id" class="form-control @error('project_id') is-invalid @enderror" required>
                                        @foreach($projects as $project)
                                            <option value="{{ $project->id }}" 
                                                {{ $project->id == $invoice->project_id ? 'selected' : '' }}
                                                data-tva="{{ $project->tva }}"
                                                data-remaining="{{ $project->budget ? $project->budget : 0 }}">
                                                {{ $project->name }} (Budget: {{ $project->budget ? $project->budget : 0 }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('project_id')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <div class="form-group mb-3">
                                    <label for="amount">Amount</label>
                                    <input type="number" step="0.0001" name="amount" id="amount" 
                                           class="form-control @error('amount') is-invalid @enderror" 
                                           value="{{ old('amount', $invoice->partial) }}" required>
                                    @error('amount')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <div class="form-group mb-3">
                                    <label for="comment">Comment</label>
                                    <textarea name="comment" id="comment" class="form-control @error('comment') is-invalid @enderror">{{ old('comment', $invoice->comment) }}</textarea>
                                    @error('comment')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary">Update Invoice</button>
                                    @if(Auth::user()->hasRole('Admin')) 
                                        <a href="{{ route('admin.invoices.invoicehistory') }}" class="btn btn-secondary">Cancel</a>
                                    @endif
                                    @if(Auth::user()->hasRole('Responsable de projet'))
                                        <a href="{{ route('Responsable_de_projet.invoices.invoicehistory') }}" class="btn btn-secondary">Cancel</a>
                                    @endif
                                    @if(Auth::user()->hasRole('Employee'))
                                        <a href="{{ route('employee.invoices.invoicehistory') }}" class="btn btn-secondary">Cancel</a>
                                    @endif
                                    @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                        <a href="{{ route('Responsable_gestionnaire.invoices.invoicehistory') }}" class="btn btn-secondary">Cancel</a>
                                    @endif
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </nav>
    </main>

    @include('dashboard_layouts.scripts')
</body>
</html>