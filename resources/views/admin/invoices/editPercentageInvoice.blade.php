<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

@include('dashboard_layouts.head')
@include('partials.edit-layout-styles')

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

    <main class="dt-edit-legacy main-content position-relative border-radius-lg">
        <nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl z-index-sticky"
            id="navbarBlur" data-scroll="false">
            <div class="container-fluid py-1 px-3">
                @include('dashboard_layouts.navbar')
            </div>

            <div class="dt-edit-container dt-edit-container--compact container-fluid py-4">
                <div class="row">
                    <div class="dt-edit-column col-lg-9 col-12 mx-auto">
                        <div class="card card-body mt-4">
                            <h6 class="mb-0">{{ __('messages.Edit Percentage Invoice') }} {{ $entreprise->invoice_prefix }}-{{ $invoice->number }}</h6>
                            <p class="text-sm mb-0">{{ __('messages.Update the percentage invoice details') }}</p>
                            @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="{{ __('messages.Close') }}">X</button>
                            </div>
                            @endif
                            @if(session('message'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('message') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="{{ __('messages.Close') }}">X</button>
                            </div>
                            @endif
                            <hr class="horizontal dark my-3" />
                            @if(Auth::user()->hasRole('Admin'))
                            <form method="POST" action="{{ route('admin.invoices.updatePercentageInvoice', $invoice->id) }}">
                            @endif
                            @if(Auth::user()->hasRole('Responsable de projet'))
                            <form method="POST" action="{{ route('Responsable_de_projet.invoices.updatePercentageInvoice', $invoice->id) }}">
                            @endif
                            @if(Auth::user()->hasRole('Employee'))
                            <form method="POST" action="{{ route('employee.invoices.updatePercentageInvoice', $invoice->id) }}">
                            @endif
                            @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                            <form method="POST" action="{{ route('Responsable_gestionnaire.invoices.updatePercentageInvoice', $invoice->id) }}">
                            @endif
                                @csrf
                                @method('PUT')

                                <div class="form-group mb-3">
                                    <label for="project_id">{{ __('messages.Project') }}</label>
                                    <select name="project_id" id="project_id" class="form-control @error('project_id') is-invalid @enderror" required>
                                        @foreach($projects as $project)
                                            <option value="{{ $project->id }}" 
                                                {{ $project->id == $invoice->project_id ? 'selected' : '' }}
                                                data-tva="{{ $project->tva }}"
                                                data-remaining="{{ $project->budget ? $project->budget : 0 }}">
                                                {{ $project->name }} ({{ __('messages.Budget') }}: {{ $project->budget ? $project->budget : 0 }})
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
                                    <label for="percentage">{{ __('messages.Percentage (%)') }}</label>
                                    <input type="number" step="0.0001" min="0" max="100" name="percentage" id="percentage" 
                                           class="form-control @error('percentage') is-invalid @enderror" 
                                           value="{{ old('percentage', $invoice->percentage) }}" required>
                                    @error('percentage')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <div class="form-group mb-3">
                                    <label for="comment">{{ __('messages.Comment') }}</label>
                                    <textarea name="comment" id="comment" class="form-control @error('comment') is-invalid @enderror">{{ old('comment', $invoice->comment) }}</textarea>
                                    @error('comment')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary">{{ __('messages.Update Invoice') }}</button>
                                    @if(Auth::user()->hasRole('Admin')) 
                                        <a href="{{ route('admin.invoices.invoicehistory') }}" class="btn btn-secondary">{{ __('messages.Cancel') }}</a>
                                    @endif
                                    @if(Auth::user()->hasRole('Responsable de projet'))
                                        <a href="{{ route('Responsable_de_projet.invoices.invoicehistory') }}" class="btn btn-secondary">{{ __('messages.Cancel') }}</a>
                                    @endif
                                    @if(Auth::user()->hasRole('Employee'))
                                        <a href="{{ route('employee.invoices.invoicehistory') }}" class="btn btn-secondary">{{ __('messages.Cancel') }}</a>
                                    @endif
                                    @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                        <a href="{{ route('Responsable_gestionnaire.invoices.invoicehistory') }}" class="btn btn-secondary">{{ __('messages.Cancel') }}</a>
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
