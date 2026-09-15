<!DOCTYPE html>
<html lang="en"> 
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.Credit Note History - DalyTrac') }}</title> 
    
    @vite('resources/css/app.css')
    @livewireStyles
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <script src="//unpkg.com/alpinejs" defer></script>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <style>
        /* Vos styles personnalisés pour les selecteurs et le tableau sont conservés */
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
        .credit-note-link:hover { text-decoration: underline; }
        .amount-negative {
            color: #DC2626;
            font-weight: 600;
        }
        
        /* [MIS À JOUR] Les styles de boutons sont maintenant gérés par les classes Tailwind, 
           mais vous pouvez garder ceci si vous le préférez à @apply */
        .btn-view {
            background-color: #2563EB; color: white; padding: 8px 16px;
            border-radius: 6px; font-size: 14px; font-weight: 500;
            border: none; cursor: pointer; display: inline-flex;
            align-items: center; gap: 6px; transition: all 0.2s;
        }
        .btn-view:hover { background-color: #1D4ED8; }
        
        .btn-resend {
            background-color: white; color: #374151; padding: 8px 16px;
            border-radius: 6px; font-size: 14px; font-weight: 500;
            border: 1px solid #D1D5DB; cursor: pointer; display: inline-flex;
            align-items: center; gap: 6px; transition: all 0.2s;
        }
        .btn-resend:hover { background-color: #F9FAFB; }
        
        .btn-delete {
            background-color: #DC2626; color: white; padding: 8px 12px;
            border-radius: 6px; font-size: 14px; border: none;
            cursor: pointer; transition: all 0.2s;
        }
        .btn-delete:hover { background-color: #B91C1C; }

        /* Mobile: show credit-note rows as modern 2-col cards (no horizontal scroll) */
        @media (max-width: 1024px) {
            .credit-note-table-wrapper {
                overflow-x: visible !important;
            }

            .credit-note-table thead {
                display: none;
            }

            .credit-note-table tbody {
                display: block;
                width: 100%;
            }

            .credit-note-table tbody tr {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 10px;
                margin-bottom: 16px;
                padding: 12px;
                border: 1px solid rgba(148, 163, 184, 0.55);
                border-radius: 16px;
                background: linear-gradient(180deg, rgba(255,255,255,0.95), rgba(248,250,252,0.92));
                box-shadow: 0 18px 45px rgba(15,23,42,0.10), 0 3px 10px rgba(15,23,42,0.08);
                transition: transform 180ms ease, box-shadow 180ms ease;
            }

            .credit-note-table tbody td {
                display: block;
                width: 100%;
                padding: 10px 12px !important;
                border: 1px solid rgba(226, 232, 240, 0.9);
                border-radius: 12px;
                background: rgba(255,255,255,0.78);
                text-align: left !important;
                white-space: normal !important;
                word-break: break-word;
            }

            .credit-note-table tbody td[data-span="2"] {
                grid-column: span 2 / span 2;
            }

            .credit-note-table tbody td[data-label]::before {
                content: attr(data-label);
                display: block;
                font-size: 11px;
                font-weight: 700;
                letter-spacing: .06em;
                text-transform: uppercase;
                color: #64748b;
                margin-bottom: 6px;
            }

            /* Keep action buttons readable on mobile */
            .credit-note-table tbody td[data-label="Actions"] .flex {
                justify-content: center;
                flex-wrap: wrap;
            }

            /* Mobile accordion: collapsed row shows only credit note number */
            .credit-note-table tbody tr.credit-note-item-row.is-collapsed td:not(.credit-note-toggle-cell) {
                display: none !important;
            }
            .credit-note-table tbody tr.credit-note-item-row td.credit-note-toggle-cell {
                cursor: pointer;
                user-select: none;
            }
            /* We render the label inline in markup for this cell */
            .credit-note-table tbody tr.credit-note-item-row td.credit-note-toggle-cell[data-label]::before {
                content: none !important;
            }
            .credit-note-table tbody tr.credit-note-item-row td.credit-note-toggle-cell .credit-note-chevron {
                transition: transform 180ms ease;
            }
            .credit-note-table tbody tr.credit-note-item-row[aria-expanded="true"] td.credit-note-toggle-cell .credit-note-chevron {
                transform: rotate(180deg);
            }
        }
    </style>
</head>
<body class="bg-gray-50">

    <div class="flex h-screen overflow-hidden" x-data> 
        {{-- Sidebar Component --}}
        @if(Auth::user()->hasRole('Admin'))
        <x-dashboard.sidebar />
    @else
        <x-employees.sidebar />
    @endif

        {{-- Main Wrapper (Navbar + Content) --}}
        <div class="flex flex-col flex-1 overflow-y-auto">

            {{-- Navbar Component --}}
            <x-dashboard.navbar />

            {{-- Main Content Area --}}
            <main class="flex-1 p-4 sm:p-6 lg:p-8 bg-gray-50">
                <div class="mb-6">
                    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-4 mb-4">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900 mb-1">{{ __('messages.Credit Note History') }}</h1>
                            <p class="text-sm text-gray-500">{{ __('messages.Manage and track all credit notes') }}</p>
                        </div>
                        
                        <div class="flex items-end gap-3 w-full sm:w-auto">
                            <div x-data="datePicker()" class="flex flex-col sm:flex-row sm:items-end gap-3 w-full sm:w-auto">
                                <div>
                                    <label for="startDate" class="block text-xs font-medium text-gray-700 mb-1">{{ __('messages.Start Date') }}:</label>
                                    <input type="date" id="startDate" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" x-model="startDate">
                                </div>
                                <div>
                                    <label for="endDate" class="block text-xs font-medium text-gray-700 mb-1">{{ __('messages.End Date') }}:</label>
                                    <input type="date" id="endDate" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" x-model="endDate">
                                </div>
                                <button @click.prevent="updateHref" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 flex items-center justify-center gap-2 h-[42px] w-full sm:w-auto">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"/>
                                    </svg>
                                    {{ __('messages.Credit Note Reports PDF') }}
                                </button>
                            </div>
                        </div>
                    </div>

                    @php
                        $total = App\Models\credit_note::where('entreprise_id', Auth::user()->entreprise_id)->sum('total_ttc');                 
                    @endphp
                    <div class="bg-white border border-gray-200 rounded-lg p-4 flex justify-end mb-6">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-medium text-gray-600 uppercase">{{ __('messages.Total Credit Notes:') }}</span>
                            <span class="text-xl font-bold text-red-600">-€{{number_format($total, 2, '.', '')}}</span>
                        </div>
                    </div>

                    @if(session('message'))
                        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-6 flex justify-between items-center">
                            <span>{{ session('message') }}</span>
                            <button type="button" class="text-green-600 hover:text-green-800 font-bold" onclick="this.parentElement.style.display='none'">×</button>
                        </div>
                    @endif

                    <div class="bg-white border border-gray-200 rounded-lg p-4 sm:p-5 mb-6">
                        <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4">{{ __('messages.Credit Note Filters') }}</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                            <div>
                                <label for="clientFilter" class="block text-xs font-medium text-gray-700 uppercase tracking-wide mb-2">{{ __('messages.Credit Note Client') }}</label>
                                <select id="clientFilter" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 form-select-custom">
                                    <option value="">{{ __('messages.All Clients') }}</option>
                                    @foreach($clients as $client)
                                    <option value="{{$client->name}}">{{$client->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="projectFilter" class="block text-xs font-medium text-gray-700 uppercase tracking-wide mb-2">{{ __('messages.Credit Note Project') }}</label>
                                <select id="projectFilter" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 form-select-custom">
                                    <option value="">{{ __('messages.All Projects') }}</option>
                                    @foreach($projects as $project)
                                    <option value="{{$project->name}}">{{strtoupper($project->name)}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="typeFilter" class="block text-xs font-medium text-gray-700 uppercase tracking-wide mb-2">{{ __('messages.Credit Note Type') }}</label>
                                <select id="typeFilter" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 form-select-custom">
                                    <option value="">{{ __('messages.All Type') }}</option>
                                    <option value="Detailed">{{ __('messages.Detailed Invoice Generator') }}</option>
                                    <option value="Partial">{{ __('messages.Partial') }}</option>
                                    <option value="Full Period">{{ __('messages.Period Invoice') }}</option>
                                    <option value="Partial Period">{{ __('messages.Partial') }} {{ __('messages.Period Invoice') }}</option>
                                    <option value="Partial Full">{{ __('messages.Partial') }} {{ __('messages.Full Invoice') }}</option>
                                    <option value="Full">{{ __('messages.Full') }}</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white border border-gray-200 rounded-lg overflow-hidden shadow-sm">
                    <div class="credit-note-table-wrapper overflow-x-auto" style="-webkit-overflow-scrolling: touch;">
                        <table class="w-full credit-note-table table-auto">
                            <thead>
                                <tr>
                                    <th class="whitespace-nowrap">{{ __('messages.Credit Note N') }}</th>
                                    <th class="whitespace-nowrap">{{ __('messages.Project') }}</th>
                                    <th class="text-center">{{ __('messages.VAT') }}</th>
                                    <th class="whitespace-nowrap">{{ __('messages.Client') }}</th>
                                    <th class="text-center whitespace-nowrap">{{ __('messages.Start Date column') }}</th>
                                    <th class="text-center whitespace-nowrap">{{ __('messages.End Date column') }}</th>
                                    <th class="text-center whitespace-nowrap">{{ __('messages.Type') }}</th>
                                    <th class="text-center whitespace-nowrap">{{ __('messages.Created At') }}</th>
                                    <th class="text-right whitespace-nowrap">{{ __('messages.Amount') }}</th>
                                    <th class="text-center whitespace-nowrap">{{ __('messages.Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($credit_notes as $credit_note)
                                @php
                                    // Normalize a readable type label for filtering
                                    $typeLabel = $credit_note->type;
                                    $creditNoteKind = $credit_note->credit_note_type ?? '';
                                    if (strtolower($credit_note->type) === 'detailed') {
                                        $typeLabel = 'Detailed';
                                    } elseif (strtolower($credit_note->type) === 'period') {
                                        $typeLabel = ($creditNoteKind === 'Partial') ? 'Partial Period' : 'Full Period';
                                    } elseif (strtolower($credit_note->type) === 'full') {
                                        $typeLabel = ($creditNoteKind === 'Partial') ? 'Partial Full' : 'Full';
                                    }
                                @endphp
                                <tr data-type="{{ strtolower($typeLabel) }}" class="credit-note-item-row" data-credit-note-row aria-expanded="true">
                                    <td data-label="{{ __('messages.Credit Note N') }}" data-span="2" class="credit-note-toggle-cell" data-credit-note-toggle>
                                        @if(Auth::user()->hasRole('Responsable de projet'))
                                        <a href="{{ route('Responsable_de_projet.invoices.serveCreditNotePdf', $credit_note->id) }}" target="_blank" class="credit-note-link hidden lg:inline">
                                            NC-{{ $credit_note->created_at->format('ym') }}{{ $credit_note->credit_note_number }}
                                        </a>
                                        @endif
                                        @if(Auth::user()->hasRole('Admin'))
                                        <a href="{{ route('admin.invoices.serveCreditNotePdf', $credit_note->id ) }}" target="_blank" class="credit-note-link hidden lg:inline">
                                            NC-{{ $credit_note->created_at->format('ym') }}{{ $credit_note->credit_note_number }}
                                        </a>
                                        @endif
                                        @if(Auth::user()->hasRole('Employee'))
                                        <a href="{{ route('employee.invoices.serveCreditNotePdf', $credit_note->id) }}" target="_blank" class="credit-note-link hidden lg:inline">
                                            NC-{{ $credit_note->created_at->format('ym') }}{{ $credit_note->credit_note_number }}
                                        </a>
                                        @endif
                                        @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                        <a href="{{ route('Responsable_gestionnaire.invoices.serveCreditNotePdf', $credit_note->id) }}" target="_blank" class="credit-note-link hidden lg:inline">
                                            NC-{{ $credit_note->created_at->format('ym') }}{{ $credit_note->credit_note_number }}
                                        </a>
                                        @endif

                                        {{-- Mobile accordion header --}}
                                        <div class="flex items-start justify-between gap-3 lg:hidden">
                                            <div class="flex items-baseline gap-2 min-w-0">
                                                <span class="text-[11px] leading-none font-bold tracking-[.06em] uppercase text-slate-500 whitespace-nowrap">
                                                    {{ __('messages.Credit Note N') }}
                                                </span>
                                                @php
                                                    $creditNotePdfRoute = '';
                                                    if(Auth::user()->hasRole('Responsable de projet')) $creditNotePdfRoute = route('Responsable_de_projet.invoices.serveCreditNotePdf', $credit_note->id);
                                                    elseif(Auth::user()->hasRole('Admin')) $creditNotePdfRoute = route('admin.invoices.serveCreditNotePdf', $credit_note->id);
                                                    elseif(Auth::user()->hasRole('Employee')) $creditNotePdfRoute = route('employee.invoices.serveCreditNotePdf', $credit_note->id);
                                                    elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire')) $creditNotePdfRoute = route('Responsable_gestionnaire.invoices.serveCreditNotePdf', $credit_note->id);
                                                @endphp
                                                <a href="{{ $creditNotePdfRoute ?: '#' }}" target="_blank" class="credit-note-link truncate leading-none">
                                                    NC-{{ $credit_note->created_at->format('ym') }}{{ $credit_note->credit_note_number }}
                                                </a>
                                            </div>
                                            <span class="shrink-0 mt-0.5 text-slate-400">
                                                <svg class="credit-note-chevron w-5 h-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                    <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                            </span>
                                        </div>
                                    </td>
                                    <td data-label="{{ __('messages.Project') }}" class="font-medium">{{strtoupper($credit_note->project_name)}}</td>
                                    <td data-label="{{ __('messages.VAT') }}" class="text-center">
                                            @php 
                                            $project = App\Models\Project::find($credit_note->project_id);
                                        @endphp
                                        {{$project->tva}}%
                                    </td>
                                    <td data-label="{{ __('messages.Client') }}">
                                        @php
                                        $client = \App\Models\Client::where('id', $credit_note->client_id)->first();
                                        @endphp
                                        {{$client->name}}
                                    </td>
                                    <td data-label="{{ __('messages.Start Date column') }}" class="text-center">{{$credit_note->start_date}}</td>
                                    <td data-label="{{ __('messages.End Date column') }}" class="text-center">{{$credit_note->end_date}}</td>
                                    <td data-label="{{ __('messages.Type') }}" class="text-center">{{$typeLabel}}</td>
                                    <td data-label="{{ __('messages.Created At') }}" class="text-center">{{$credit_note->created_at}}</td>
                                    <td data-label="{{ __('messages.Amount') }}" data-span="2" class="text-right amount-negative">-€{{number_format($credit_note->total_ttc, 2, '.', '')}}</td>
                                    <td data-label="{{ __('messages.Actions') }}" data-span="2">
                                        <div class="flex items-center justify-center gap-2">
                                            @if(Auth::user()->hasRole('Responsable de projet'))
                                                <a href="{{ route('Responsable_de_projet.invoices.serveCreditNotePdf', $credit_note->id) }}" class="btn-view">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                    {{ __('messages.View') }}
                                                </a>
                                                <form action="{{route('Responsable_de_projet.credit_note.destroy',$credit_note->id) }}"
                                                    method="post" onsubmit="return confirm('{{ __('messages.Are you sure?') }}');" class="inline-block">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn-delete">🗑</button>
                                                </form>
                                            @endif
                                            @if(Auth::user()->hasRole('Admin'))
                                                <a href="{{ route('admin.invoices.serveCreditNotePdf', $credit_note->id) }}" class="btn-view">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                    {{ __('messages.View & Download') }} 
                                                </a>
                                                {{-- [CORRIGÉ] Bouton "Resend" utilise Alpine.js --}}
                                                <button type="button" class="btn-resend"
                                                    @click="$dispatch('open-modal', { id: 'sendCreditNoteModal', creditNoteId: {{ $credit_note->id }}, invoiceId: {{ $credit_note->invoice_id }} })">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                                                    {{ __('messages.Resend') }}
                                                </button>
                <form action="{{route('admin.credit_note.destroy',$credit_note->id) }}"
                                                    method="post" onsubmit="return confirm('{{ __('messages.Are you sure?') }}');" class="inline-block">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-white bg-red-600 hover:bg-red-700">
                                                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862A2 2 0 015.995 19.142L5 7m5 4v6m4-6v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3M4 7h16" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            @endif
                                            @if(Auth::user()->hasRole('Employee'))
                                                <a href="{{ route('employee.invoices.serveCreditNotePdf', $credit_note->id) }}" class="btn-view">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                    {{ __('messages.View') }}
                                                </a>
                                                <form action="{{route('employee.credit_note.destroy',$credit_note->id) }}"
                                                    method="post" onsubmit="return confirm('{{ __('messages.Are you sure?') }}');" class="inline-block">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn-delete">🗑</button>
                                                </form>
                                            @endif
                                            @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                                <a href="{{ route('Responsable_gestionnaire.invoices.serveCreditNotePdf', $credit_note->id) }}" class="btn-view">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                    {{ __('messages.View') }}
                                                </a>
                                                <form action="{{route('Responsable_gestionnaire.credit_note.destroy',$credit_note->id) }}"
                                                    method="post" onsubmit="return confirm('{{ __('messages.Are you sure?') }}');" class="inline-block">
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

    {{-- [CORRIGÉ] Modal "Send Credit Note" (Conversion en Tailwind/Alpine) --}}
    <div x-data="sendCreditNoteModal()" 
         x-show="show" 
         @open-modal.window="if ($event.detail.id === 'sendCreditNoteModal') open($event.detail.creditNoteId, $event.detail.invoiceId)"
         x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50" style="display: none;">
        
        <div x-show="show" x-transition @click.outside="show = false" class="bg-white rounded-lg shadow-xl w-full max-w-lg mx-4">
            
            <div class="flex items-center justify-between p-4 border-b">
                <h5 class="text-lg font-semibold">{{ __('messages.Send Credit Note') }}</h5>
                <button @click="show = false" type="button" class="text-gray-400 hover:text-gray-600">&times;</button>
            </div>
            
            <form id="sendCreditNoteForm" @submit.prevent="submitForm">
                @csrf
                <div class="p-6 space-y-4">
                    <div class="mb-3">
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('messages.Select Recipients:') }}</label>
                        <div id="recipientsContainer" class="border rounded p-3 h-40 overflow-y-auto bg-gray-50">
                            <div class="text-center">
                                {{-- Spinner Tailwind --}}
                                <svg class="animate-spin h-5 w-5 text-blue-600 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="fileUpload" class="block text-sm font-medium text-gray-700 mb-2">Upload Additional Files (Optional):</label>
                        <input type="file" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" id="fileUpload" name="file[]" multiple>
                    </div>
                </div>
                <div class="flex items-center justify-end p-4 bg-gray-50 border-t rounded-b-lg space-x-2">
                    <button @click="show = false" type="button" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-100">Close</button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">Send Credit Note</button>
                </div>
            </form>
        </div>
    </div>


    {{-- [CORRIGÉ] Suppression de @include('dashboard_layouts.scripts') --}}
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.4/xlsx.full.min.js"></script>

    {{-- [CORRIGÉ] Tout le JS est combiné et mis à jour pour Alpine.js --}}
    <script>
        
        // Fonction Alpine pour le modal "Send Credit Note"
        function sendCreditNoteModal() {
            return {
                show: false,
                creditNoteId: null,
                invoiceId: null,
                form: document.getElementById('sendCreditNoteForm'),
                recipientsContainer: document.getElementById('recipientsContainer'),

                open(creditNoteId, invoiceId) {
                    this.creditNoteId = creditNoteId;
                    this.invoiceId = invoiceId;
                    this.form.action = '{{ route("admin.credit_note.send", ":credit_note_id") }}'.replace(':credit_note_id', this.creditNoteId);
                    
                    this.recipientsContainer.innerHTML = `<div class="text-center"><svg class="animate-spin h-5 w-5 text-blue-600 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg></div>`;
                    
                    fetch(`/admin/invoice/${this.invoiceId}/recipients`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.recipients && data.recipients.length > 0) {
                                this.recipientsContainer.innerHTML = data.recipients.map(recipient => `
                                    <div class="flex items-center mb-2">
                                        <input class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" type="checkbox" 
                                            name="recipient_emails[]" 
                                            value="${recipient.email}" 
                                            id="recipient_${recipient.id}"
                                            ${recipient.is_comptable ? 'checked' : ''}>
                                        <label class="ml-2 text-sm text-gray-700" for="recipient_${recipient.id}">
                                            ${recipient.name ? recipient.name + ' (' + recipient.email + ')' : recipient.email}
                                        </label>
                                    </div>
                                `).join('');
                            } else {
                                this.recipientsContainer.innerHTML = '<p class="text-sm text-gray-500">' + @json(__('messages.No recipients found')) + '</p>';
                            }
                        })
                        .catch(error => {
                            console.error('Error fetching recipients:', error);
                            this.recipientsContainer.innerHTML = '<p class="text-sm text-red-600">' + @json(__('messages.Error loading recipients')) + '</p>';
                        });
                    
                    this.show = true;
                },

                submitForm() {
                    const formData = new FormData(this.form);
                    
                    fetch(this.form.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        this.show = false;
                        if (data.success) {
                            // Vous n'avez pas de fonction showMessage ici, donc nous utilisons une alerte
                            alert(data.message || @json(__('messages.Credit note sent successfully!')));
                            window.location.reload();
                        } else {
                            alert('Error: ' + (data.message || 'Unknown error'));
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert(@json(__('messages.An error occurred while sending the credit note.')));
                    });
                }
            }
        }
        
        // Fonction Alpine pour le DatePicker
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

        // Script de filtre (ne dépend pas de Bootstrap)
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

                    const rows = document.querySelectorAll('.credit-note-table tbody tr');
                rows.forEach(row => {
                    const clientName = row.querySelector('td:nth-child(4)').textContent.trim().toLowerCase();
                    const projectName = row.querySelector('td:nth-child(2)').textContent.trim().toLowerCase();
                        const invoiceType = (row.dataset.type || row.querySelector('td:nth-child(7)').textContent).trim().toLowerCase();

                    const typeMatches = typeValue === '' ||
                        (typeValue === 'partial' ? invoiceType.includes('partial') : invoiceType === typeValue);

                    if ((clientValue === '' || clientName.includes(clientValue)) &&
                        (projectValue === '' || projectName.includes(projectValue)) &&
                        typeMatches) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            }
        });

        // Mobile accordion per credit note row (show only number when collapsed)
        document.addEventListener('DOMContentLoaded', function () {
            const mql = window.matchMedia('(max-width: 1024px)');

            function setRowCollapsed(row, collapsed) {
                if (!row) return;
                row.classList.toggle('is-collapsed', collapsed);
                row.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
            }

            function initAccordions() {
                const rows = document.querySelectorAll('.credit-note-table tbody tr.credit-note-item-row');
                const isMobile = mql.matches;
                rows.forEach(row => setRowCollapsed(row, isMobile));
            }

            document.addEventListener('click', function (e) {
                const toggleCell = e.target.closest('[data-credit-note-toggle]');
                if (!toggleCell) return;

                // Let normal link clicks open the PDF without toggling.
                if (e.target.closest('a')) return;

                const row = toggleCell.closest('tr.credit-note-item-row');
                if (!row) return;

                const collapsed = row.classList.contains('is-collapsed');
                setRowCollapsed(row, !collapsed);
            });

            if (mql.addEventListener) mql.addEventListener('change', initAccordions);
            else if (mql.addListener) mql.addListener(initAccordions);

            initAccordions();
        });
    </script>
    
    @livewireScripts
</body>
</html>
