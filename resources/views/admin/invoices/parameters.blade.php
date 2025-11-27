<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Parameters - DailyTrac</title>
    @vite('resources/css/app.css')
    @livewireStyles
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-gray-100 flex h-screen overflow-hidden font-sans">
    <x-dashboard.sidebar />
    
    <div class="flex flex-col flex-1 overflow-y-auto">
        <x-dashboard.navbar />
        
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-8 space-y-8">
            <div class="flex flex-wrap justify-between items-center gap-4">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">Invoice Parameters</h1>
                    <p class="text-sm text-gray-600">Configure invoice settings and templates</p>
                </div>
            </div>

            @php
            $updateUrl = '';
            if(Auth::user()->hasRole('Responsable de projet'))
                $updateUrl = '/Responsable_de_projet/Enterprise_infos/updateInfos';
            elseif(Auth::user()->hasRole('Admin') || Auth::user()->hasRole('SuperAdmin'))
                $updateUrl = '/admin/Enterprise_infos/updateInfos';
            elseif(Auth::user()->hasRole('Employee'))
                $updateUrl = '/employee/Enterprise_infos/updateInfos';
            elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                $updateUrl = '/Responsable_gestionnaire/Enterprise_infos/updateInfos';
            @endphp

            <form id="invoiceParamsForm" class="space-y-8">
                @csrf
                <input type="hidden" name="entreprise_id" value="{{ Auth::user()->entreprise_id }}">

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 space-y-5">
                    <div>
                        <h5 class="text-lg font-semibold text-gray-900">Invoice Information</h5>
                        <p class="text-sm text-gray-500 mt-1">Mandatory information for all invoices</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="invoice_prefix" class="block text-sm font-medium text-gray-700 mb-1">Invoice Prefix</label>
                            <input id="invoice_prefix" 
                                   class="entrepriseInfos block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3" 
                                   type="text" 
                                   name="invoice_prefix" 
                                   value="{{ $entreprise->invoice_prefix ?? '' }}" />
                            <p class="mt-1 text-xs text-gray-500">Used as prefix for all invoice numbers</p>
                        </div>
                    </div>

                    <div>
                        <label for="general_conditions" class="block text-sm font-medium text-gray-700 mb-1">General Conditions</label>
                        <textarea id="general_conditions" 
                                  class="entrepriseInfos block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3" 
                                  name="general_conditions" 
                                  rows="5">{{ $entreprise->general_conditions ?? '' }}</textarea>
                        <p class="mt-1 text-xs text-gray-500">Displayed at the bottom of invoices</p>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 space-y-5">
                    <div>
                        <h5 class="text-lg font-semibold text-gray-900">Invoice Body Message</h5>
                        <p class="text-sm text-gray-500 mt-1">Message sent with invoice emails</p>
                    </div>
                    <div>
                        <label for="invoice_body" class="block text-sm font-medium text-gray-700 mb-1">Message Content</label>
                        <textarea id="invoice_body" 
                                  class="entrepriseInfos block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3" 
                                  name="invoice_body" 
                                  rows="4">{{ $entreprise->invoice_body ?? '' }}</textarea>
                        <p class="mt-1 text-xs text-gray-500">This message will be included in invoice emails</p>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 space-y-5">
                    <div>
                        <h5 class="text-lg font-semibold text-gray-900">Reminder Messages</h5>
                        <p class="text-sm text-gray-500 mt-1">Customize reminder emails for unpaid invoices</p>
                    </div>

                    <div>
                        <label for="first_reminder" class="block text-sm font-medium text-gray-700 mb-1">First Reminder</label>
                        <textarea id="first_reminder" 
                                  class="entrepriseInfos block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3" 
                                  name="first_reminder" 
                                  rows="4">{{ $entreprise->first_reminder ?? '' }}</textarea>
                        <p class="mt-1 text-xs text-gray-500">Sent 5 days after invoice due date</p>
                    </div>

                    <div>
                        <label for="second_reminder" class="block text-sm font-medium text-gray-700 mb-1">Second Reminder</label>
                        <textarea id="second_reminder" 
                                  class="entrepriseInfos block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3" 
                                  name="second_reminder" 
                                  rows="4">{{ $entreprise->second_reminder ?? '' }}</textarea>
                        <p class="mt-1 text-xs text-gray-500">Sent 10 days after invoice due date</p>
                    </div>

                    <div>
                        <label for="third_reminder" class="block text-sm font-medium text-gray-700 mb-1">Third Reminder</label>
                        <textarea id="third_reminder" 
                                  class="entrepriseInfos block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3" 
                                  name="third_reminder" 
                                  rows="4">{{ $entreprise->third_reminder ?? '' }}</textarea>
                        <p class="mt-1 text-xs text-gray-500">Sent 15 days after invoice due date</p>
                    </div>
                </div>

                <div class="flex justify-between items-center mt-8 pt-6 border-t border-gray-200">
                    @php
                    $backRoute = route('admin.index');
                    if (Auth::user()->hasRole('Responsable de projet'))
                        $backRoute = route('Responsable_de_projet.index');
                    elseif (Auth::user()->hasRole('Employee'))
                        $backRoute = route('employee.index');
                    elseif (Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                        $backRoute = route('Responsable_gestionnaire.index');
                    @endphp

                    <a href="{{ $backRoute }}" 
                       class="inline-flex items-center bg-white hover:bg-gray-50 text-gray-700 border border-gray-300 text-sm font-medium px-4 py-2 rounded-lg shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back
                    </a>

                    <button type="submit" 
                            id="saveButton"
                            class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg shadow-sm disabled:opacity-50"
                            {{ !$updateUrl ? 'disabled' : '' }}>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                        </svg>
                        Save All Changes
                    </button>
                </div>
            </form>
        </main>
    </div>

    @livewireScripts
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#invoiceParamsForm').on('submit', function(e) {
                e.preventDefault();
                
                const $button = $('#saveButton');
                const originalText = $button.html(); // This saves the original "Save All Changes" text with its icon
                
                // This is your existing "Saving..." state, which is great
                $button.prop('disabled', true).html('<svg class="animate-spin h-4 w-4 mr-2 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Saving...');

                const entrepriseId = "{{ Auth::user()->entreprise_id }}";
                const updateUrl = "{{ $updateUrl }}";
                const formData = $(this).serializeArray();
                
                const fieldsToUpdate = formData.filter(field => 
                    field.name !== '_token' && field.name !== 'entreprise_id'
                );
                
                let completed = 0;
                let errors = 0;
                const total = fieldsToUpdate.length;

                if (total === 0) {
                    $button.prop('disabled', false).html(originalText);
                    alert('No changes to save!'); // Keep this, as no action was taken
                    return;
                }

                fieldsToUpdate.forEach(function(field) {
                    $.ajax({
                        url: updateUrl,
                        type: 'POST',
                        data: {
                            content: field.value,
                            id: entrepriseId,
                            column: field.name,
                            _token: $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            completed++;
                            checkComplete();
                        },
                        error: function(xhr) {
                            completed++;
                            errors++;
                            console.error('Error saving ' + field.name + ':', xhr);
                            checkComplete();
                        }
                    });
                });

                // --- MODIFIED FUNCTION ---
                // This function now handles the button animation
                function checkComplete() {
                    if (completed === total) {
                        $button.prop('disabled', false); // Re-enable the button

                        if (errors === 0) {
                            // --- SUCCESS ANIMATION ---
                            // 1. Change button to green and show "Saved!"
                            $button.removeClass('bg-blue-600 hover:bg-blue-700').addClass('bg-green-600');
                            $button.html('<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>Saved!');
                        
                        } else {
                            // --- ERROR ANIMATION ---
                            // 1. Change button to red and show "Error!"
                            $button.removeClass('bg-blue-600 hover:bg-blue-700').addClass('bg-red-600');
                            $button.html('<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>Error!');
                            
                            // 2. Still show an alert so the user knows what failed
                            if (errors === total) {
                                alert('Failed to save changes. Please try again.');
                            } else {
                                alert('Some changes were saved, but ' + errors + ' field(s) failed to save.');
                            }
                        }

                        // --- 2. RESET BUTTON (for both success and error) ---
                        // After 2 seconds, change it back to the original blue button
                        setTimeout(() => {
                            $button.removeClass('bg-green-600 bg-red-600').addClass('bg-blue-600 hover:bg-blue-700');
                            $button.html(originalText); // Restore original icon and text
                        }, 2000); // 2-second delay
                    }
                }
            });
        });
    </script>
</body> 
</html>