<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.Expense Note History - DalyTrac') }}</title>
    @vite('resources/css/app.css') 
    @livewireStyles
    <meta name="csrf-token" content="{{ csrf_token() }}">
    {{-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet"> --}}

    <style>
        /* Mobile: show expense note rows as modern 2-col cards (no horizontal scroll) */
        @media (max-width: 1024px) {
            .expense-table-wrapper {
                overflow-x: visible !important;
            }

            #expenseTable thead {
                display: none;
            }

            #expenseTable tbody {
                display: block;
                width: 100%;
            }

            #expenseTable tbody tr {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 10px;
                margin-bottom: 16px;
                padding: 12px;
                border: 1px solid rgba(148, 163, 184, 0.55);
                border-radius: 16px;
                background: linear-gradient(180deg, rgba(255,255,255,0.95), rgba(248,250,252,0.92));
                box-shadow: 0 18px 45px rgba(15,23,42,0.10), 0 3px 10px rgba(15,23,42,0.08);
            }

            #expenseTable tbody td {
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

            #expenseTable tbody td[data-span="2"] {
                grid-column: span 2 / span 2;
            }

            #expenseTable tbody td[data-label]::before {
                content: attr(data-label);
                display: block;
                font-size: 11px;
                font-weight: 700;
                letter-spacing: .06em;
                text-transform: uppercase;
                color: #64748b;
                margin-bottom: 6px;
            }

            #expenseTable tbody td[data-label="Actions"] .flex {
                justify-content: center !important;
            }

            /* Mobile accordion: collapsed row shows only Note N */
            #expenseTable tbody tr.expense-row.is-collapsed td:not(.expense-toggle-cell) {
                display: none !important;
            }
            #expenseTable tbody tr.expense-row td.expense-toggle-cell {
                cursor: pointer;
                user-select: none;
            }
            /* We render the label inline in markup for this cell */
            #expenseTable tbody tr.expense-row td.expense-toggle-cell[data-label]::before {
                content: none !important;
            }
            #expenseTable tbody tr.expense-row td.expense-toggle-cell .expense-chevron {
                transition: transform 180ms ease;
            }
            #expenseTable tbody tr.expense-row[aria-expanded="true"] td.expense-toggle-cell .expense-chevron {
                transform: rotate(180deg);
            }
        }
    </style>
</head>

<body class="bg-gray-100 flex h-screen overflow-hidden font-sans">
    {{-- Sidebar Component --}}
    @if(Auth::user()->hasRole('Admin'))
        <x-dashboard.sidebar />
    @else
        <x-employees.sidebar />
    @endif

    <div class="flex flex-col flex-1 overflow-y-auto">
        {{-- Navbar Component --}}
        <x-dashboard.navbar />

        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-4 sm:p-6 lg:p-8 space-y-8">
        {{--  Header Section --}}
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">{{ __('messages.Expense Note History') }}</h1>
                    <p class="text-sm text-gray-500">{{ __('messages.Track and manage all expense notes') }}</p>
                </div>
                <div class="flex items-center gap-3 w-full sm:w-auto">
                <button type="button" id="openDevisReportModal" class="inline-flex items-center justify-center bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg shadow-sm w-full sm:w-auto">
                        {{-- Download Icon --}}
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        {{ __('messages.Expense Note Reports PDF') }} 
                    </button>
                </div>
            </div>
            {{-- Cards Section --}}
             @php
                  $Total_budget = App\Models\note_de_frais::where('entreprise_id', Auth::user()->entreprise_id)->sum('budget');
             @endphp
             {{-- Date Pickers + Total Accepted in one row --}}
             <div class="flex flex-col lg:flex-row justify-between items-center gap-6">
                {{-- Date Pickers --}}
                <div class="grid grid-cols-2 gap-4 w-full sm:flex sm:flex-row sm:flex-wrap sm:items-end sm:w-auto lg:w-auto">
                    <div class="sm:col-auto">
                        <label for="startDate" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Start Date') }}</label>
                        <input type="date" id="startDate" placeholder="jj/mm/aaaa"
                        class="block w-full sm:w-44 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3">
                    </div>
                    
                    <div class="sm:col-auto">
                        <label for="endDate" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.End Date') }}</label>
                        <input type="date" id="endDate" placeholder="jj/mm/aaaa"
                        class="block w-full sm:w-44 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3">
                    </div>
                </div>
                {{-- Total Accepted Card --}}
                <div class="bg-white rounded-lg shadow border border-gray-200 p-5 flex items-center space-x-4 min-w-[280px] w-full lg:w-auto">
                    <div class="flex-shrink-0 bg-green-100 rounded-full p-3">
                        <svg class="h-6 w-6 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase font-medium">{{ __('messages.TOTAL ACCEPTED') }}</p>
                    <p class="text-xl font-semibold text-green-600">€{{ number_format($Total_budget, 2, ',', ' ') }}</p>
                </div>
            </div>
        </div>


            {{-- Table Section --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="expense-table-wrapper overflow-x-auto" style="-webkit-overflow-scrolling: touch;">
                    <table id="expenseTable" class="w-full divide-y divide-gray-200 table-auto" >
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-4 py-3 text-left text-sm font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">{{ __('messages.Note N') }}</th>
                                <th scope="col" class="px-4 py-3 text-left text-sm font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">{{ __('messages.Employee Name') }}</th>
                                <th scope="col" class="px-4 py-3 text-right text-sm font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">{{ __('messages.Budget') }}</th>
                                <th scope="col" class="px-4 py-3 text-center text-sm font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">{{ __('messages.Created at expense') }}</th>
                                <th scope="col" class="px-4 py-3 text-right text-sm font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">{{ __('messages.Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            {{-- Use @forelse for cleaner empty handling --}}
                            @forelse($notedefrais as $note)
                            {{-- Corrected variable from $d to $note --}}
                            {{-- Removed the 'sent' check as 'note_de_frais' might not have a 'sent' status like devis/invoices --}}
                            <tr class="expense-row hover:bg-gray-50" data-expense-row aria-expanded="true">
                                <td data-label="{{ __('messages.Note N') }}" data-span="2" class="px-4 py-3 text-blue-600 whitespace-nowrap text-sm expense-toggle-cell" data-expense-toggle>
                                    {{-- Use devis-link-normal style --}}
                                    @php $noteNumberDisplay = $entreprise->notedefraisPrefix . '-' . date('ymd', strtotime($note->created_at)) . $note->note_number; @endphp {{-- Changed date format --}}
                                    @php $downloadRoute = '#'; @endphp
                                    @if(Auth::user()->hasRole('Responsable de projet')) @php $downloadRoute = route('Responsable_de_projet.notedefrais.downloadNoteDeFrais', $note->note_number); @endphp @endif
                                    @if(Auth::user()->hasRole('Admin')) @php $downloadRoute = route('admin.notedefrais.downloadNoteDeFrais', $note->note_number); @endphp @endif
                                    @if(Auth::user()->hasRole('Employee')) @php $downloadRoute = route('employee.notedefrais.downloadNoteDeFrais', $note->note_number); @endphp @endif
                                    @if(Auth::user()->hasRole('Responsable de projet et gestionnaire')) @php $downloadRoute = route('Responsable_gestionnaire.notedefrais.downloadNoteDeFrais', $note->note_number); @endphp @endif
                                    {{-- Desktop: keep original simple link layout --}}
                                    <a href="{{ $downloadRoute }}" class="note-link-normal hidden lg:inline">{{ $noteNumberDisplay }}</a>

                                    {{-- Mobile accordion header --}}
                                    <div class="flex items-start justify-between gap-3 lg:hidden">
                                        <div class="flex items-baseline gap-2 min-w-0">
                                            <span class="text-[11px] leading-none font-bold tracking-[.06em] uppercase text-slate-500 whitespace-nowrap">
                                                {{ __('messages.Note N') }}
                                            </span>
                                            <a href="{{ $downloadRoute }}" class="note-link-normal truncate leading-none">
                                                {{ $noteNumberDisplay }}
                                            </a>
                                        </div>
                                        <span class="shrink-0 mt-0.5 text-slate-400">
                                            <svg class="expense-chevron w-5 h-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </span>
                                    </div>
                                </td>
                                <td data-label="{{ __('messages.Employee Name') }}" class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 font-medium">
                                    {{$note->employee_name}}
                                </td>
                                <td data-label="{{ __('messages.Budget') }}" class="px-4 py-3 whitespace-nowrap text-sm font-semibold text-gray-900 text-right">
                                    {{number_format($note->budget, 2, ',', '')}}€ 
                                </td>
                                <td data-label="{{ __('messages.Created at expense') }}" class="px-4 py-3 text-sm text-gray-500 text-center">
                                    {{ $note->created_at ? \Carbon\Carbon::parse($note->created_at)->format('Y-m-d H:i:s') : '' }} 
                                </td>
                                <td data-label="{{ __('messages.Actions') }}" data-span="2" class="px-4 py-3 whitespace-nowrap text-sm text-right">
                                    {{-- Action Buttons - styled like screenshot --}}
                                    <div class="flex items-center justify-end gap-2"> 
                                        @php $dlRoute = '#'; $delRoute = '#'; @endphp
                                        @if(Auth::user()->hasRole('Admin'))
                                            @php
                                            $dlRoute = route('admin.notedefrais.downloadNoteDeFrais', $note->note_number);
                                            $delRoute = route('admin.notedefrais.destroy', $note->id);
                                            @endphp
                                        @elseif(Auth::user()->hasRole('Responsable de projet'))
                                            @php
                                            $dlRoute = route('Responsable_de_projet.notedefrais.downloadNoteDeFrais', $note->note_number);
                                            $delRoute = route('Responsable_de_projet.notedefrais.destroy', $note->id);
                                            @endphp
                                        @elseif(Auth::user()->hasRole('Employee'))
                                            @php
                                            $dlRoute = route('employee.notedefrais.downloadNoteDeFrais', $note->note_number);
                                            $delRoute = route('employee.notedefrais.destroy', $note->id);
                                            @endphp
                                        @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                             @php
                                            $dlRoute = route('Responsable_gestionnaire.notedefrais.downloadNoteDeFrais', $note->note_number);
                                            $delRoute = route('Responsable_gestionnaire.notedefrais.destroy', $note->id);
                                            @endphp
                                        @endif

                                        <a class="flex items-center gap-1 text-white bg-blue-600 hover:bg-blue-700 font-medium rounded text-xs px-3 py-1.5" href="{{ $dlRoute }}">
    
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" 
                                        d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3" />
                                    </svg>
                                </a>
                                        @can('Delete expense note') 
                                            @if($delRoute !== '#') 
                                            <form action="{{ $delRoute }}" method="post" onsubmit="return confirm('{{ __('messages.Are you sure you want to delete this expense note?') }}');" class="inline">
                                    @csrf 
                                    @method('DELETE') 
                                    <button type="submit" title="Delete"
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-white bg-red-600 hover:bg-red-700">
                                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862A2 2 0 015.995 19.142L5 7m5 4v6m4-6v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3M4 7h16" />
                                        </svg>
                                    </button> 
                                </form>                                            
                                @endif
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                            @empty 
                                <tr>
                                    <td colspan="5" class="text-center px-4 py-6 text-gray-500">
                                        {{ __('messages.No expense notes found matching your criteria.') }}
                                    </td>
                                </tr>
                            @endforelse 
                        </tbody>
                    </table>
                </div>
            </div>

        </main>
    </div>

    @livewireScripts
    @include('dashboard_layouts.scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.4/xlsx.full.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
         document.addEventListener('DOMContentLoaded', function() {
            const reportModal = document.getElementById('openDevisReportModal'); // Button to open
            // Note: Your modal HTML is missing, so this script assumes it exists with ID 'devisReportModal'
            const devisReportModal = document.getElementById('devisReportModal'); 
            
            if (reportModal && devisReportModal) {
                reportModal.addEventListener('click', () => {
                    devisReportModal.classList.remove('hidden');
                });
                
                // Add logic for your modal's close/cancel buttons
                document.getElementById('closeDevisReportModal')?.addEventListener('click', () => devisReportModal.classList.add('hidden'));
                document.getElementById('cancelDevisReportBtn')?.addEventListener('click', () => devisReportModal.classList.add('hidden'));
                
                // Add logic for confirm button
                document.getElementById('confirmDevisReport')?.addEventListener('click', function() {
                     let baseUrl = '';
                     @if(Auth::user()->hasRole('Responsable de projet')) baseUrl = "{{route('Responsable_de_projet.notedefrais.downloadRepports')}}"; @endif
                     @if(Auth::user()->hasRole('Admin')) baseUrl = "{{route('admin.notedefrais.downloadRepports')}}"; @endif
                     @if(Auth::user()->hasRole('Employee')) baseUrl = "{{route('employee.notedefrais.downloadRepports')}}"; @endif
                     @if(Auth::user()->hasRole('Responsable de projet et gestionnaire')) baseUrl = "{{route('Responsable_gestionnaire.notedefrais.downloadRepports')}}"; @endif

                    const startDate = document.getElementById('startDate').value;
                    const endDate = document.getElementById('endDate').value;
                    let urlWithParams = baseUrl;

                    if (startDate && endDate) {
                        urlWithParams += `?startDate=${startDate}&endDate=${endDate}`;
                    }
                    window.location.href = urlWithParams;
                    devisReportModal.classList.add('hidden');
                });
            }

            // --- FILTER SCRIPT ---
            const startDateFilter = document.getElementById('startDate');
            const endDateFilter = document.getElementById('endDate');
            const tableBody = document.querySelector('#expenseTable tbody');
            const allRows = tableBody.querySelectorAll('tr');

            function filterTable() {
                const startDate = startDateFilter.value;
                const endDate = endDateFilter.value;
                let hasVisibleRows = false;

                allRows.forEach(row => {
                    const noResultsRow = row.querySelector('td[colspan="5"]');
                    if (noResultsRow) return; // Skip the 'no results' row

                    const dateCell = row.querySelector('td:nth-child(4)');
                    if (!dateCell) return;

                    const rowDate = dateCell.textContent.trim().split(' ')[0]; // Get 'YYYY-MM-DD' part

                    let show = true;
                    if (startDate && rowDate < startDate) {
                        show = false;
                    }
                    if (endDate && rowDate > endDate) {
                        show = false;
                    }

                    if (show) {
                        row.style.display = "";
                        hasVisibleRows = true;
                    } else {
                        row.style.display = "none";
                    }
                });
                
                const noResults = tableBody.querySelector('td[colspan="5"]');
                if(noResults){
                    noResults.parentElement.style.display = hasVisibleRows ? 'none' : 'table-row';
                }
            }

            if(startDateFilter) startDateFilter.addEventListener('change', filterTable);
            if(endDateFilter) endDateFilter.addEventListener('change', filterTable);
            filterTable(); // Run on load

            // Mobile accordion per expense note row (show only number when collapsed)
            const mql = window.matchMedia('(max-width: 1024px)');

            function setRowCollapsed(row, collapsed) {
                if (!row) return;
                row.classList.toggle('is-collapsed', collapsed);
                row.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
            }

            function initAccordions() {
                const rows = document.querySelectorAll('#expenseTable tbody tr.expense-row');
                const isMobile = mql.matches;
                rows.forEach(row => setRowCollapsed(row, isMobile));
            }

            document.addEventListener('click', function (e) {
                const toggleCell = e.target.closest('[data-expense-toggle]');
                if (!toggleCell) return;

                // Let normal link clicks open the PDF without toggling.
                if (e.target.closest('a')) return;

                const row = toggleCell.closest('tr.expense-row');
                if (!row) return;

                const collapsed = row.classList.contains('is-collapsed');
                setRowCollapsed(row, !collapsed);
            });

            if (mql.addEventListener) mql.addEventListener('change', initAccordions);
            else if (mql.addListener) mql.addListener(initAccordions);

            initAccordions();
         });
    </script>
    
    <div id="devisReportModal" class="fixed inset-0 z-40 flex items-center justify-center bg-black bg-opacity-50 hidden">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-md mx-4">
            <div class="flex justify-between items-center p-4 border-b">
                <h5 class="text-lg font-semibold text-gray-800">Download Report</h5>
                <button type="button" id="closeDevisReportModal" class="text-gray-400 hover:text-gray-600">&times;</button>
            </div>
            <div class="p-6">
                <p class="text-sm text-gray-700">{{ __('messages.The report will use the Start Date and End Date you selected on the main page. If no dates are selected, all records will be downloaded.') }}</p>
            </div>
            <div class="flex justify-end items-center p-4 bg-gray-50 border-t rounded-b-lg space-x-2">
                <button type="button" id="cancelDevisReportBtn" class="px-4 py-2 bg-white border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50">{{ __('messages.Cancel') }}</button>
                <button type="button" id="confirmDevisReport" class="px-4 py-2 bg-blue-600 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-blue-700">Download PDF</button>
            </div>
        </div>
    </div>

</body>
</html>
