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
                    <h1 class="text-xl font-semibold text-gray-900">Expense Note Parameters</h1>
                    <p class="text-sm text-gray-500">Configure expense note settings</p>
                </div>
            </div>

            {{-- 2. Expense Note Information Card --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-1">Expense Note Information</h2>
                    <p class="text-sm text-gray-500 mb-6">Mandatory information</p>

                    {{-- Expense Note Prefix --}}
                    {{-- Use grid to allow for potential future fields --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Prefix</label>
                            <input
                                class="editable entrepriseInfos block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3 bg-gray-50" {{-- Added bg-gray-50 for readonly state --}}
                                type="text" name="notedefraisPrefix"
                                value="{{$entreprise->notedefraisPrefix}}" readonly />
                            <small class="text-xs text-gray-500 mt-1">Prefix used for expense note numbering (e.g., NF-). Double-click to edit.</small> {{-- Added help text --}}
                        </div>
                         {{-- Placeholder for potential second column if needed later --}}
                         <div></div>
                    </div>

                    {{-- Removed other fields as they are not in the provided code --}}

                </div>
            </div>

            {{-- Removed the second card as there's no 'body message' field in the code --}}

            {{-- 3. Action Buttons --}}
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
                 {{-- Save Changes Button --}}
                 <button type="button" id="saveChangesBtn" class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg shadow-sm">
                     Save Changes
                 </button>
            </div>

        </main>
        
    </div>
    {{-- SCRIPTS --}}
    @livewireScripts
    {{-- Include Bootstrap JS ONLY if needed --}}
    {{-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script> --}}
    @include('dashboard_layouts.scripts')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="//unpkg.com/alpinejs" defer></script>

    {{-- jQuery script for inline editing --}}
<script>
    $(document).ready(function() {
        // --- Double-click editing ---
        $(".editable").dblclick(function() {
            $(this).prop('readonly', false)
                   .removeClass('bg-gray-50 cursor-default')
                   .addClass('bg-white ring-1 ring-blue-500');
            $(this).focus();
        });

        // --- Save on blur (when clicking away) ---
        $(".editable").blur(function() {
            var inputElement = $(this);
            inputElement.prop('readonly', true)
                        .addClass('bg-gray-50 cursor-default')
                        .removeClass('bg-white ring-1 ring-blue-500');

            var newContent = inputElement.val();
            if (newContent === inputElement.data('originalValue')) {
                 console.log("No changes detected for:", inputElement.attr('name'));
                 inputElement.removeClass('border-green-500 border-red-500');
                 return;
            }

            var EntrepriseId = "{{ Auth::user()->entreprise_id }}";
            var columnName = inputElement.attr('name');
            var updatedUrl = '';
            // --- Determine AJAX URL based on role ---
             @if(Auth::user()->hasRole('Responsable de projet')) updatedUrl = '/Responsable_de_projet/Enterprise_infos/updateInfos'; @endif
             @if(Auth::user()->hasRole('Admin')) updatedUrl = '/admin/Enterprise_infos/updateInfos'; @endif
             @if(Auth::user()->hasRole('Employee')) updatedUrl = '/employee/Enterprise_infos/updateInfos'; @endif
             @if(Auth::user()->hasRole('Responsable de projet et gestionnaire')) updatedUrl = '/Responsable_gestionnaire/Enterprise_infos/updateInfos'; @endif

            if (updatedUrl) {
                inputElement.addClass('border-yellow-400'); // Saving indicator

                $.ajax({
                    url: updatedUrl,
                    type: 'post',
                    data: {
                        content: newContent,
                        id: EntrepriseId,
                        column: columnName,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        console.log("Update successful:", response);
                        inputElement.data('originalValue', newContent);
                        inputElement.removeClass('border-yellow-400 border-red-500').addClass('border-green-500');
                        setTimeout(() => inputElement.removeClass('border-green-500'), 2000);
                        // Button feedback
                        $('#saveChangesBtn').removeClass('bg-blue-600 hover:bg-blue-700').addClass('bg-green-600').text('Saved!');
                        setTimeout(() => {
                             $('#saveChangesBtn').removeClass('bg-green-600').addClass('bg-blue-600 hover:bg-blue-700').text('Save Changes');
                        }, 2000);
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.error("Update failed:", textStatus, errorThrown);
                        inputElement.removeClass('border-yellow-400 border-green-500').addClass('border-red-500');
                        inputElement.val(inputElement.data('originalValue')); // Revert
                        alert('Failed to save changes for ' + columnName + '. Please check console.');
                        // Button feedback
                         $('#saveChangesBtn').removeClass('bg-blue-600 hover:bg-blue-700').addClass('bg-red-600').text('Error!');
                        setTimeout(() => {
                             $('#saveChangesBtn').removeClass('bg-red-600').addClass('bg-blue-600 hover:bg-blue-700').text('Save Changes');
                        }, 2000);
                    }
                });
            } else {
                 console.warn("No update URL defined for this user role.");
                 inputElement.prop('readonly', true).addClass('bg-gray-50 cursor-default').removeClass('bg-white ring-1 ring-blue-500');
            }
        });

        // Store original value on focus
        $(".editable").focus(function() {
            $(this).data('originalValue', $(this).val());
            $(this).removeClass('border-green-500 border-red-500');
        });

        // --- Save Changes Button Feedback ---
        $('#saveChangesBtn').click(function() {
             // Example feedback
             $(this).text('Saved!');
             setTimeout(() => $(this).text('Save Changes'), 1500);
             // Optionally trigger blur on active element if you want the button to force save
             // document.activeElement.blur();
        });
    });
</script>
</body>