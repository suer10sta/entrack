<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoices History - BizManager</title>
    @vite('resources/css/app.css') 
    @livewireStyles
    <meta name="csrf-token" content="{{ csrf_token() }}">
    {{-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet"> --}}

    <style>
        /* Text alignment for inputs/textareas */
        .entrepriseInfos {
            text-align: left;
        }
        /* Style readonly inputs */
        input:read-only, textarea:read-only {
            background-color: #f9fafb; /* bg-gray-50 */
            cursor: default;
        }
    </style>
</head>

<body class="bg-gray-100 flex h-screen overflow-hidden font-sans">


<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-NKDMSK6" height="0" width="0"
    style="display: none; visibility: hidden"></iframe></noscript>

    {{-- Sidebar Component --}}
    <x-dashboard.sidebar />

    <div class="flex flex-col flex-1 overflow-y-auto">
        {{-- Navbar Component --}}
        <x-dashboard.navbar />
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-8 space-y-6">

            {{-- 1. Header Section --}}
            <div class="flex flex-wrap justify-between items-center gap-4">
                <div>
                    {{-- Adjusted header style --}}
                    <h1 class="text-xl font-semibold text-gray-900">Devis Parameters</h1>
                    <p class="text-sm text-gray-500">Configure devis settings and templates</p>
                </div>
            </div>

            {{-- 2. Devis Information Card --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-1">Devis Information</h2>
                    <p class="text-sm text-gray-500 mb-6">Mandatory information</p>

                    {{-- Devis Prefix & Start Number --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Prefix</label>
                            <input
                                class="editable entrepriseInfos block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3"
                                type="text" name="devis_prefix"
                                value="{{$entreprise->devis_prefix}}" readonly />
                            {{-- Optional: Add help text if needed --}}
                            {{-- <small class="text-xs text-gray-500 mt-1">Prefix used for devis numbering (e.g., DEV-)</small> --}}
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Start Number</label>
                            <input
                                class="editable entrepriseInfos block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3"
                                type="text" name="devis_start_number"
                                value="{{$entreprise->devis_start_number}}" readonly />
                             {{-- Optional: Add help text if needed --}}
                             {{-- <small class="text-xs text-gray-500 mt-1">The next devis number will start from here.</small> --}}
                        </div>
                    </div>

                    {{-- General Conditions --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">General Condition</label>
                        <textarea rows="6"
                            class="editable entrepriseInfos block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3"
                            name="general_conditions_Devis" readonly>{{ $entreprise->general_conditions_Devis }}</textarea>
                         {{-- Optional: Add help text if needed --}}
                         {{-- <small class="text-xs text-gray-500 mt-1">Default terms and conditions added to the devis PDF.</small> --}}
                    </div>
                </div>
            </div>

             {{-- 3. Devis Body Message Card --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-1">Devis Body Message</h2>
                    <p class="text-sm text-gray-500 mb-6">Message</p>

                    <div>
                        {{-- <label class="block text-sm font-medium text-gray-700 mb-1">Message</label> --}} {{-- Label removed as per screenshot --}}
                        <textarea rows="6"
                            class="editable entrepriseInfos block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3"
                            name="devis_body" readonly>{{ $entreprise->devis_body }}</textarea>
                        {{-- Optional: Add help text if needed --}}
                        {{-- <small class="text-xs text-gray-500 mt-1">Default message body used when sending devis via email.</small> --}}
                    </div>
                </div>
            </div>

            {{-- 4. Action Buttons --}}
            <div class="flex justify-end items-center gap-3 pt-4">
                 @php $backRoute = '#'; // Default fallback @endphp
                 @if(Auth::user()->hasRole('Responsable de projet')) @php $backRoute = route('Responsable_de_projet.index'); @endphp @endif
                 @if(Auth::user()->hasRole('Admin')) @php $backRoute = route('admin.index'); @endphp @endif
                 @if(Auth::user()->hasRole('Employee')) @php $backRoute = route('employee.index'); @endphp @endif
                 @if(Auth::user()->hasRole('Responsable de projet et gestionnaire')) @php $backRoute = route('Responsable_gestionnaire.index'); @endphp @endif

                 {{-- Back Button --}}
                 <a href="{{ $backRoute }}" class="inline-flex items-center bg-white hover:bg-gray-50 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 shadow-sm">
                     Back
                 </a>
                 {{-- Save Changes Button (Note: Saving happens on blur, this button doesn't trigger save but could be used for other actions or feedback) --}}
                 <button type="button" id="saveChangesBtn" class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg shadow-sm">
                     Save Changes
                 </button>
            </div>


        </main>
    </div>

    {{-- SCRIPTS --}}
    @livewireScripts
    {{-- Include Bootstrap JS ONLY if needed for other components on the page --}}
    {{-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script> --}}
    @include('dashboard_layouts.scripts') {{-- Keep if it includes necessary base scripts --}}
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script> {{-- Keep jQuery for your edit script --}}
    <script src="//unpkg.com/alpinejs" defer></script> {{-- Keep if needed elsewhere --}}

    {{-- Your existing jQuery script for inline editing --}}
<script>
    $(document).ready(function() {
        // --- Double-click editing ---
        $(".editable").dblclick(function() {
            // Make editable and visually indicate it
            $(this).prop('readonly', false)
                   .removeClass('bg-gray-50 cursor-default') // Remove readonly styles
                   .addClass('bg-white ring-1 ring-blue-500'); // Add editing styles
            $(this).focus(); // Focus the element
        });

        // --- Save on blur (when clicking away) ---
        $(".editable").blur(function() {
            var inputElement = $(this); // Store reference

            // Make readonly again and change style back
            inputElement.prop('readonly', true)
                        .addClass('bg-gray-50 cursor-default') // Add readonly styles
                        .removeClass('bg-white ring-1 ring-blue-500'); // Remove editing styles

            var newContent = inputElement.val();
            // Prevent saving if content hasn't changed (optional but good practice)
            if (newContent === inputElement.data('originalValue')) {
                 console.log("No changes detected for:", inputElement.attr('name'));
                 // Remove any lingering success/error borders
                 inputElement.removeClass('border-green-500 border-red-500');
                 return; // Exit if no change
            }


            var EntrepriseId = "{{ Auth::user()->entreprise_id }}";
            var columnName = inputElement.attr('name');

            // --- Determine AJAX URL based on role ---
            var updatedUrl = '';
            @if(Auth::user()->hasRole('Responsable de projet')) updatedUrl = '/Responsable_de_projet/Enterprise_infos/updateInfos'; @endif
            @if(Auth::user()->hasRole('Admin')) updatedUrl = '/admin/Enterprise_infos/updateInfos'; @endif
            @if(Auth::user()->hasRole('Employee')) updatedUrl = '/employee/Enterprise_infos/updateInfos'; @endif
            @if(Auth::user()->hasRole('Responsable de projet et gestionnaire')) updatedUrl = '/Responsable_gestionnaire/Enterprise_infos/updateInfos'; @endif


            // --- Perform AJAX call ---
            if (updatedUrl) { // Only send if a URL is defined
                // Optional: Show a subtle saving indicator
                inputElement.addClass('border-yellow-400'); // Yellow border while saving

                $.ajax({
                    url: updatedUrl,
                    type: 'post',
                    data: {
                        content: newContent,
                        id: EntrepriseId,
                        column: columnName,
                        _token: $('meta[name="csrf-token"]').attr('content') // Use meta tag for CSRF
                    },
                    success: function(response) {
                        console.log("Update successful:", response);
                        // Update original value data and show success
                        inputElement.data('originalValue', newContent);
                        inputElement.removeClass('border-yellow-400 border-red-500').addClass('border-green-500'); // Green border for success
                        setTimeout(() => inputElement.removeClass('border-green-500'), 2000); // Remove green border after 2s
                        // Optionally trigger feedback from the 'Save Changes' button
                        $('#saveChangesBtn').removeClass('bg-blue-600 hover:bg-blue-700').addClass('bg-green-600').text('Saved!');
                        setTimeout(() => {
                             $('#saveChangesBtn').removeClass('bg-green-600').addClass('bg-blue-600 hover:bg-blue-700').text('Save Changes');
                        }, 2000);
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.error("Update failed:", textStatus, errorThrown);
                        // Show error
                        inputElement.removeClass('border-yellow-400 border-green-500').addClass('border-red-500'); // Red border for error
                        // Revert to original value
                        inputElement.val(inputElement.data('originalValue'));
                        alert('Failed to save changes for ' + columnName + '. Please check console for details.');
                         $('#saveChangesBtn').removeClass('bg-blue-600 hover:bg-blue-700').addClass('bg-red-600').text('Error!');
                        setTimeout(() => {
                             $('#saveChangesBtn').removeClass('bg-red-600').addClass('bg-blue-600 hover:bg-blue-700').text('Save Changes');
                        }, 2000);
                    }
                });
            } else {
                 console.warn("No update URL defined for this user role.");
                 // Revert styles if no URL
                 inputElement.prop('readonly', true).addClass('bg-gray-50 cursor-default').removeClass('bg-white ring-1 ring-blue-500');
            }
        });

        // Store original value on focus to compare on blur
        $(".editable").focus(function() {
            $(this).data('originalValue', $(this).val());
            // Remove lingering success/error borders when starting to edit again
            $(this).removeClass('border-green-500 border-red-500');
        });

        // --- Save Changes Button Feedback ---
        // The button itself doesn't save (blur does), but we can use it for feedback.
        // Or, you could change the logic to save only when this button is clicked.
        $('#saveChangesBtn').click(function() {
            // Example: Briefly show feedback if needed, although saving happens on blur.
             $(this).text('Saved!');
             setTimeout(() => $(this).text('Save Changes'), 1500);
        });

    });
</script>
</body>