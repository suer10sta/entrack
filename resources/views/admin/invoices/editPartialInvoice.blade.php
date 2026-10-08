<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DalyTrac Dashboard</title>
    @vite('resources/css/app.css')
    @livewireStyles
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Styles personnalisés pour l'icône de flèche du 'select' --}}
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
    </style>
</head>
<body class="bg-gray-50 flex h-screen overflow-hidden">
    {{-- Sidebar --}}
    @if(Auth::user()->hasRole('Admin'))
        <x-dashboard.sidebar />
    @else
        <x-employees.sidebar />
    @endif
    <div class="flex flex-col flex-1 overflow-y-auto">
        {{-- Navbar --}}
        <x-dashboard.navbar />

        {{-- Main Content --}}
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-4 sm:p-6 lg:p-8">
            <div class="max-w-3xl mx-auto">
                
                {{-- Session Error --}}
                @if(session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <strong class="font-bold">{{ __('messages.Error!') }}</strong>
                    <span class="block sm:inline">{{ session('error') }}</span>
                    <button type="button" onclick="this.parentElement.style.display='none';" class="absolute top-0 bottom-0 right-0 px-4 py-3 font-bold text-xl" aria-label="{{ __('messages.Close') }}">&times;</button>
                </div>
                @endif

                {{-- Session Message --}}
                @if(session('message'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <strong class="font-bold">{{ __('messages.Success') }}!</strong>
                    <span class="block sm:inline">{{ session('message') }}</span>
                    <button type="button" onclick="this.parentElement.style.display='none';" class="absolute top-0 bottom-0 right-0 px-4 py-3 font-bold text-xl" aria-label="{{ __('messages.Close') }}">&times;</button>
                </div>
                @endif

                {{-- Determine Routes --}}
                @php
                    $updateRoute = '';
                    $cancelRoute = '';
                    if (Auth::user()->hasRole('Admin')) {
                        $updateRoute = route('admin.invoices.updatePartialInvoice', $invoice->id);
                        $cancelRoute = route('admin.invoices.invoicehistory');
                    } elseif (Auth::user()->hasRole('Responsable de projet')) {
                        $updateRoute = route('Responsable_de_projet.invoices.updatePartialInvoice', $invoice->id);
                        $cancelRoute = route('Responsable_de_projet.invoices.invoicehistory');
                    } elseif (Auth::user()->hasRole('Employee')) {
                        $updateRoute = route('employee.invoices.updatePartialInvoice', $invoice->id);
                        $cancelRoute = route('employee.invoices.invoicehistory');
                    } elseif (Auth::user()->hasRole('Responsable de projet et gestionnaire')) {
                        $updateRoute = route('Responsable_gestionnaire.invoices.updatePartialInvoice', $invoice->id);
                        $cancelRoute = route('Responsable_gestionnaire.invoices.invoicehistory');
                    }
                @endphp

                {{-- Form Card --}}
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <form method="POST" action="{{ $updateRoute }}">
                        @csrf
                        @method('PUT')

                        {{-- Card Header --}}
                        <div class="p-6 border-b border-gray-200">
                            <h2 class="text-lg font-semibold text-gray-900">
                                {{ __('messages.Edit Partial Invoice') }} {{ $entreprise->invoice_prefix }}-{{ $invoice->number }}
                            </h2>
                            <p class="text-sm text-gray-600 mt-1">{{ __('messages.Update the partial invoice details') }}</p>
                        </div>

                        {{-- Card Body (Form Fields) --}}
                        <div class="p-6 space-y-5">
                            
                            {{-- Project --}}
                            <div>
                                <label for="project_id" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Project') }}</label>
                                <select name="project_id" id="project_id" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3 form-select-custom @error('project_id') border-red-500 @enderror" required>
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
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Amount --}}
                            <div>
                                <label for="amount" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Amount') }}</label>
                                <input type="number" step="0.0001" name="amount" id="amount" 
                                       class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('amount') border-red-500 @enderror" 
                                       value="{{ old('amount', $invoice->partial) }}" required>
                                @error('amount')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Comment --}}
                            <div>
                                <label for="comment" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Comment') }}</label>
                                <textarea name="comment" id="comment" rows="4" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('comment') border-red-500 @enderror">{{ old('comment', $invoice->comment) }}</textarea>
                                @error('comment')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                        </div>

                        {{-- Card Footer (Buttons) --}}
                        <div class="mt-6 p-6 bg-gray-50 border-t border-gray-200 flex items-center gap-3">
                            <button type="submit" class="inline-flex justify-center bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-lg text-sm shadow-sm">
                                {{ __('messages.Update Invoice') }}
                            </button>
                            <a href="{{ $cancelRoute }}" class="inline-flex justify-center bg-white hover:bg-gray-50 text-gray-700 font-medium px-4 py-2 rounded-lg text-sm border border-gray-300 shadow-sm">
                                {{ __('messages.Cancel') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
    @livewireScripts
</body>
</html>
