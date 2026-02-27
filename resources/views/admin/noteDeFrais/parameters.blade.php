<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.Expense Note Parameters - DalyTrac') }}</title>
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
    @if(Auth::user()->hasRole('Admin'))
        <x-dashboard.sidebar />
    @else
        <x-employees.sidebar />
    @endif

    <div class="flex flex-col flex-1 overflow-y-auto">
        {{-- Navbar Component --}}
        <x-dashboard.navbar />
        
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-8 space-y-6">

            {{-- 1. Header Section --}}
            <div class="flex flex-wrap justify-between items-center gap-4">
                <div>
                    {{-- Adjusted header style --}}
                    <h1 class="text-xl font-semibold text-gray-900">{{ __('messages.Expense Note Parameters') }}</h1>
                    <p class="text-sm text-gray-500">{{ __('messages.Configure expense note settings') }}</p>
                </div>
            </div>

            {{-- 2. Expense Note Information Card --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-1">{{ __('messages.Expense Note Information') }}</h2>
                    <p class="text-sm text-gray-500 mb-6">{{ __('messages.Mandatory information expense') }}</p>

                    {{-- Expense Note Prefix --}}
                    {{-- Use grid to allow for potential future fields --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Prefix') }}</label>
                            <input
                                class="editable entrepriseInfos block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3 bg-gray-50" {{-- Added bg-gray-50 for readonly state --}}
                                type="text" name="notedefraisPrefix"
                                value="{{$entreprise->notedefraisPrefix}}" readonly />
                            <small class="text-xs text-gray-500 mt-1">{{ __('messages.Prefix help expense') }}</small> {{-- Added help text --}}
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
                 <a href="{{ url()->previous() ?: $backRoute }}" class="inline-flex items-center bg-white hover:bg-gray-50 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 shadow-sm">
                     {{ __('messages.Back') }}
                 </a>
                 {{-- Save Changes Button --}}
                 <button type="button" id="saveChangesBtn" class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg shadow-sm">
                     {{ __('messages.Save Changes Expense') }}
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

        // --- 1. Double-click to make a field editable ---
        // (This behavior is unchanged)
        $(".editable").dblclick(function() {
            $(this).prop('readonly', false)
                   .removeClass('bg-gray-50 cursor-default')
                   .addClass('bg-white ring-1 ring-blue-500');
            $(this).focus();
        });

        // --- 2. Store original value on focus ---
        // (This behavior is unchanged)
        $(".editable").focus(function() {
            $(this).data('originalValue', $(this).val());
            $(this).removeClass('border-green-500 border-red-500'); // Clear old status borders
        });

        // --- 3. Blur (Clicking away) ---
        // (MODIFIED: All saving logic has been REMOVED from here)
        $(".editable").blur(function() {
            var inputElement = $(this);
            // This now *only* makes the field read-only again. It does NOT save.
            inputElement.prop('readonly', true)
                        .addClass('bg-gray-50 cursor-default')
                        .removeClass('bg-white ring-1 ring-blue-500');
        });

        // --- 4. Save Changes Button Click ---
        // (MODIFIED: All saving logic is MOVED here)
        $('#saveChangesBtn').click(function() {
            var saveButton = $(this); // Get the button
            
            // Get shared info
            var EntrepriseId = "{{ Auth::user()->entreprise_id }}";
            var updatedUrl = '';
            @if(Auth::user()->hasRole('Responsable de projet')) updatedUrl = '/Responsable_de_projet/Enterprise_infos/updateInfos'; @endif
            @if(Auth::user()->hasRole('Admin')) updatedUrl = '/admin/Enterprise_infos/updateInfos'; @endif
            @if(Auth::user()->hasRole('Employee')) updatedUrl = '/employee/Enterprise_infos/updateInfos'; @endif
            @if(Auth::user()->hasRole('Responsable de projet et gestionnaire')) updatedUrl = '/Responsable_gestionnaire/Enterprise_infos/updateInfos'; @endif

            if (!updatedUrl) {
                alert(@json(__('messages.Cannot save. No permissions expense')));
                return; 
            }
            
            // Give user feedback on the button
            saveButton.prop('disabled', true).removeClass('bg-blue-600 hover:bg-blue-700').addClass('bg-yellow-500').text(@json(__('messages.Saving Expense...')));

            var hasErrors = false;

            // Loop through EACH editable field on the page
            $(".editable").each(function() {
                var inputElement = $(this);
                var newContent = inputElement.val();
                var originalValue = inputElement.data('originalValue');

                // Make it read-only (in case it's still in focus)
                inputElement.prop('readonly', true)
                            .addClass('bg-gray-50 cursor-default')
                            .removeClass('bg-white ring-1 ring-blue-500');

                // Check if value actually changed. If not, skip it.
                if (newContent === originalValue || originalValue === undefined) {
                    return; // 'continue' in a .each() loop
                }

                // --- This is the saving logic ---
                var columnName = inputElement.attr('name');
                inputElement.addClass('border-yellow-400'); // Saving indicator

                $.ajax({
                    url: updatedUrl,
                    type: 'post',
                    async: false, // Make calls sequential to avoid race conditions (safer)
                    data: {
                        content: newContent,
                        id: EntrepriseId,
                        column: columnName,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        console.log("Update successful:", columnName);
                        inputElement.data('originalValue', newContent); // Update original value
                        inputElement.removeClass('border-yellow-400 border-red-500').addClass('border-green-500');
                        setTimeout(() => inputElement.removeClass('border-green-500'), 2000);
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.error("Update failed:", columnName);
                        inputElement.removeClass('border-yellow-400 border-green-500').addClass('border-red-500');
                        inputElement.val(originalValue); // Revert on error
                        hasErrors = true; // Mark that an error occurred
                    }
                });
            });

            // --- All fields have been processed, update button feedback ---
            saveButton.prop('disabled', false);
            if (hasErrors) {
                saveButton.removeClass('bg-yellow-500').addClass('bg-red-600').text(@json(__('messages.Error Expense!')));
                alert(@json(__('messages.Some changes failed to save. Please check fields marked in red.')));
            } else {
                saveButton.removeClass('bg-yellow-500').addClass('bg-green-600').text(@json(__('messages.Saved Expense!')));
            }
            
            // Reset button text after 2 seconds
            setTimeout(() => {
                saveButton.removeClass('bg-green-600 bg-red-600').addClass('bg-blue-600 hover:bg-blue-700').text(@json(__('messages.Save Changes Expense')));
            }, 2000);
        });
    });
</script>
</body>
