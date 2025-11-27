<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoices History - DailyTrac</title>
    @vite('resources/css/app.css') 
    @livewireStyles
    <meta name="csrf-token" content="{{ csrf_token() }}">
    {{-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet"> --}}

</head>

<body class="bg-gray-100 flex h-screen overflow-hidden font-sans">
    {{-- Sidebar Component --}}
    <x-dashboard.sidebar />

    <div class="flex flex-col flex-1 overflow-y-auto">
        {{-- Navbar Component --}}
        <x-dashboard.navbar />

        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-8 space-y-8">
        {{--  Header Section --}}
            <div class="flex flex-wrap justify-between items-center gap-4">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">Expense Note History</h1>
                    <p class="text-sm text-gray-500">Track and manage all expense notes</p>
                </div>
                <div class="flex items-center gap-3">
                <button type="button" id="openDevisReportModal" class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg shadow-sm">
                        {{-- Download Icon --}}
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Expense Note Reports PDF 
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
                <div class="flex flex-wrap items-end gap-4">
                    <div>
                        <label for="startDate" class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                        <input type="date" id="startDate" placeholder="jj/mm/aaaa"
                        class="block w-44 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3">
                    </div>
                    
                    <div>
                        <label for="endDate" class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                        <input type="date" id="endDate" placeholder="jj/mm/aaaa"
                        class="block w-44 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3">
                    </div>
                </div>
                {{-- Total Accepted Card --}}
                <div class="bg-white rounded-lg shadow border border-gray-200 p-5 flex items-center space-x-4 min-w-[280px]">
                    <div class="flex-shrink-0 bg-green-100 rounded-full p-3">
                        <svg class="h-6 w-6 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase font-medium">TOTAL ACCEPTED</p>
                    <p class="text-xl font-semibold text-green-600">€{{ number_format($Total_budget, 2, ',', ' ') }}</p>
                </div>
            </div>
        </div>


            {{-- Table Section --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-full divide-y divide-gray-200" >
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-4 py-3 text-left text-sm font-semibold text-gray-600 uppercase tracking-wider">Note N</th>
                                <th scope="col" class="px-4 py-3 text-left text-sm font-semibold text-gray-600 uppercase tracking-wider">Employee Name</th>
                                <th scope="col" class="px-4 py-3 text-right text-sm font-semibold text-gray-600 uppercase tracking-wider">Budget</th>
                                <th scope="col" class="px-4 py-3 text-center text-sm font-semibold text-gray-600 uppercase tracking-wider">Created at</th>
                                <th scope="col" class="px-4 py-3 text-right text-sm font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            {{-- Use @forelse for cleaner empty handling --}}
                            @forelse($notedefrais as $note)
                            {{-- Corrected variable from $d to $note --}}
                            {{-- Removed the 'sent' check as 'note_de_frais' might not have a 'sent' status like devis/invoices --}}
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-blue-600 whitespace-nowrap text-sm">
                                    {{-- Use devis-link-normal style --}}
                                    @php $noteNumberDisplay = $entreprise->notedefraisPrefix . '-' . date('ymd', strtotime($note->created_at)) . $note->note_number; @endphp {{-- Changed date format --}}
                                    @php $downloadRoute = '#'; @endphp
                                    @if(Auth::user()->hasRole('Responsable de projet')) @php $downloadRoute = route('Responsable_de_projet.notedefrais.downloadNoteDeFrais', $note->note_number); @endphp @endif
                                    @if(Auth::user()->hasRole('Admin')) @php $downloadRoute = route('admin.notedefrais.downloadNoteDeFrais', $note->note_number); @endphp @endif
                                    @if(Auth::user()->hasRole('Employee')) @php $downloadRoute = route('employee.notedefrais.downloadNoteDeFrais', $note->note_number); @endphp @endif
                                    @if(Auth::user()->hasRole('Responsable de projet et gestionnaire')) @php $downloadRoute = route('Responsable_gestionnaire.notedefrais.downloadNoteDeFrais', $note->note_number); @endphp @endif
                                    <a href="{{ $downloadRoute }}" class="note-link-normal">{{ $noteNumberDisplay }}</a>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 font-medium">
                                    {{$note->employee_name}}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm font-semibold text-gray-900 text-right">
                                    {{number_format($note->budget, 2, ',', '')}}€ 
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500 text-center">
                                    {{ $note->created_at ? \Carbon\Carbon::parse($note->created_at)->format('Y-m-d H:i:s') : '' }} 
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-right">
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

                                        <a class="text-white bg-blue-600 hover:bg-blue-700 font-medium rounded text-xs px-3 py-1.5 text-center" href="{{ $dlRoute }}">Download</a>
                                        @can('Delete expense note') {{-- Keep permission check --}}
                                            @if($delRoute !== '#') {{-- Ensure delete route exists --}}
                                            <form action="{{ $delRoute }}" method="post" onsubmit="return confirm('Are you sure?');" class="inline-block"> @csrf @method('DELETE') <button type="submit" class="text-white bg-red-600 hover:bg-red-700 font-medium rounded text-xs px-3 py-1.5 text-center">Delete</button> </form>
                                            @endif
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                            @empty 
                                <tr>
                                    <td colspan="5" class="text-center px-4 py-6 text-gray-500">
                                        No expense notes found matching your criteria.
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
         });
    </script>
    
    <div id="devisReportModal" class="fixed inset-0 z-40 flex items-center justify-center bg-black bg-opacity-50 hidden">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-md mx-4">
            <div class="flex justify-between items-center p-4 border-b">
                <h5 class="text-lg font-semibold text-gray-800">Download Report</h5>
                <button type="button" id="closeDevisReportModal" class="text-gray-400 hover:text-gray-600">&times;</button>
            </div>
            <div class="p-6">
                <p class="text-sm text-gray-700">The report will use the Start Date and End Date you selected on the main page. If no dates are selected, all records will be downloaded.</p>
            </div>
            <div class="flex justify-end items-center p-4 bg-gray-50 border-t rounded-b-lg space-x-2">
                <button type="button" id="cancelDevisReportBtn" class="px-4 py-2 bg-white border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
                <button type="button" id="confirmDevisReport" class="px-4 py-2 bg-blue-600 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-blue-700">Download PDF</button>
            </div>
        </div>
    </div>

</body>
</html>