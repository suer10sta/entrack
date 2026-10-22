<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <x-favicon />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('messages.Edit Period Invoice - DalyTrac') }}</title>
    @vite('resources/css/app.css')
    @livewireStyles
    <link rel="stylesheet" href="{{ asset('css/dalytrac-period-invoice-edit.css') }}">
</head>
<body class="bg-gray-50 flex h-screen overflow-hidden font-sans">
    @if(Auth::user()->hasRole('Admin'))
        <x-dashboard.sidebar />
    @else
        <x-employees.sidebar />
    @endif
    <div class="flex flex-col flex-1 min-w-0 overflow-y-auto">
        <x-dashboard.navbar />
        @php
            $prefix = 'admin';
            if (!Auth::user()->hasRole('Admin')) {
                if (Auth::user()->hasRole('Responsable de projet')) $prefix = 'Responsable_de_projet';
                elseif (Auth::user()->hasRole('Employee')) $prefix = 'employee';
                elseif (Auth::user()->hasRole('Responsable de projet et gestionnaire')) $prefix = 'Responsable_gestionnaire';
            }
            $updateRoute = route($prefix.'.invoices.updatePeriodInvoice', $invoice->id);
            $cancelRoute = route($prefix.'.invoices.invoicehistory');
            $reference = \App\Support\InvoiceNumbering::reference($entreprise, $invoice, true);
            $currentProject = $projects->firstWhere('id', $invoice->project_id);
            $formatAmount = fn ($amount) => number_format((float) $amount, 2, app()->getLocale() === 'en' ? '.' : ',', app()->getLocale() === 'en' ? ',' : ' ').' €';
        @endphp
        <main class="period-invoice-edit">
            <div class="pie-container">
                <nav class="pie-breadcrumb" aria-label="{{ __('messages.Invoices') }}"><a href="{{ $cancelRoute }}">{{ __('messages.Invoices') }}</a><span aria-hidden="true">/</span><span>{{ $reference }}</span></nav>
                <header class="pie-page-header">
                    <div><span class="pie-eyebrow">{{ __('messages.Period Invoice') }}</span><h1>{{ __('messages.Edit Period Invoice') }}</h1><p>{{ __('messages.period_edit_intro') }}</p></div>
                    <span class="pie-reference">{{ $reference }}</span>
                </header>
                @if(session('error'))<div class="pie-alert pie-alert-error" role="alert">{{ session('error') }}</div>@endif
                @if(session('message'))<div class="pie-alert pie-alert-success" role="status">{{ session('message') }}</div>@endif
                @if($errors->any())
                    <div class="pie-alert pie-alert-error" role="alert"><strong>{{ __('messages.period_edit_validation') }}</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                @endif
                <div class="pie-layout">
                    <form id="periodInvoiceForm" method="POST" action="{{ $updateRoute }}" class="pie-form">
                        @csrf
                        @method('PUT')
                        <section class="pie-card" aria-labelledby="pie-project-title">
                            <div class="pie-section-heading"><span class="pie-step" aria-hidden="true">01</span><div><h2 id="pie-project-title">{{ __('messages.Project') }}</h2><p>{{ __('messages.period_edit_project_help') }}</p></div></div>
                            <div class="pie-field">
                                <label for="project_id">{{ __('messages.Project') }} <span aria-hidden="true">*</span></label>
                                <select name="project_id" id="project_id" required @error('project_id') aria-invalid="true" aria-describedby="project_id-error" @enderror>
                                    @foreach($projects as $project)
                                        <option value="{{ $project->id }}" @selected((string) old('project_id', $invoice->project_id) === (string) $project->id) data-tva="{{ $project->tva }}" data-remaining="{{ $project->budget ?: 0 }}">{{ $project->name }} ({{ __('messages.Budget') }}: {{ $formatAmount($project->budget ?: 0) }})</option>
                                    @endforeach
                                </select>
                                @error('project_id')<p id="project_id-error" class="pie-field-error">{{ $message }}</p>@enderror
                            </div>
                        </section>
                        <section class="pie-card" aria-labelledby="pie-period-title">
                            <div class="pie-section-heading"><span class="pie-step" aria-hidden="true">02</span><div><h2 id="pie-period-title">{{ __('messages.period_edit_billing_period') }}</h2><p>{{ __('messages.period_edit_period_help') }}</p></div></div>
                            <div class="pie-date-grid">
                                <div class="pie-field">
                                    <label for="start_date">{{ __('messages.Start Date') }} <span aria-hidden="true">*</span></label>
                                    <input type="date" name="start_date" id="start_date" value="{{ old('start_date', $invoice->start_date ? \Carbon\Carbon::parse($invoice->start_date)->format('Y-m-d') : '') }}" required @error('start_date') aria-invalid="true" aria-describedby="start_date-error" @enderror>
                                    @error('start_date')<p id="start_date-error" class="pie-field-error">{{ $message }}</p>@enderror
                                </div>
                                <div class="pie-field">
                                    <label for="end_date">{{ __('messages.End Date') }} <span aria-hidden="true">*</span></label>
                                    <input type="date" name="end_date" id="end_date" value="{{ old('end_date', $invoice->end_date ? \Carbon\Carbon::parse($invoice->end_date)->format('Y-m-d') : '') }}" required @error('end_date') aria-invalid="true" aria-describedby="end_date-error" @enderror>
                                    @error('end_date')<p id="end_date-error" class="pie-field-error">{{ $message }}</p>@enderror
                                </div>
                            </div>
                        </section>
                        <section class="pie-card" aria-labelledby="pie-comment-title">
                            <div class="pie-section-heading"><span class="pie-step" aria-hidden="true">03</span><div><h2 id="pie-comment-title">{{ __('messages.Comment') }} <span class="pie-optional">{{ __('messages.period_edit_optional') }}</span></h2><p>{{ __('messages.period_edit_comment_help') }}</p></div></div>
                            <div class="pie-field">
                                <label for="comment" class="sr-only">{{ __('messages.Comment') }}</label>
                                <textarea name="comment" id="comment" rows="5" placeholder="{{ __('messages.Optional comment for the period invoice...') }}" @error('comment') aria-invalid="true" aria-describedby="comment-error" @enderror>{{ old('comment', $invoice->comment) }}</textarea>
                                @error('comment')<p id="comment-error" class="pie-field-error">{{ $message }}</p>@enderror
                            </div>
                        </section>
                        <div class="pie-actions">
                            <a href="{{ $cancelRoute }}" class="dt-btn dt-btn-ghost">{{ __('messages.Cancel') }}</a>
                            <button type="submit" class="dt-btn dt-btn-primary"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6"/></svg>{{ __('messages.Update Invoice') }}</button>
                        </div>
                    </form>
                    <aside class="pie-summary" aria-labelledby="pie-summary-title">
                        <div class="pie-card pie-summary-card">
                            <div class="pie-summary-icon" aria-hidden="true"><svg width="23" height="23" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M8 3h7l4 4v14H5V3h3Zm6 0v5h5M8 12h8m-8 4h5"/></svg></div>
                            <h2 id="pie-summary-title">{{ __('messages.period_edit_summary') }}</h2>
                            <p class="pie-summary-reference">{{ $reference }}</p>
                            <div class="pie-summary-project"><span>{{ __('messages.Project') }}</span><strong>{{ $currentProject?->name ?? $invoice->project_name }}</strong></div>
                            <dl class="pie-totals">
                                <div><dt>{{ __('messages.Total HT') }}</dt><dd>{{ $formatAmount($invoice->total_htva) }}</dd></div>
                                <div><dt>{{ __('messages.VAT') }}</dt><dd>{{ $formatAmount($invoice->total_tva) }}</dd></div>
                                <div class="pie-total"><dt>{{ __('messages.period_edit_total_incl_tax') }}</dt><dd>{{ $formatAmount($invoice->total_ttc) }}</dd></div>
                            </dl>
                            <p class="pie-summary-note"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 11v6m0-10v1"/></svg><span>{{ __('messages.period_edit_recalculation') }}</span></p>
                        </div>
                    </aside>
                </div>
            </div>
        </main>
    </div>
    @livewireScripts
</body>
</html>
