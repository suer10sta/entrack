<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Parameters - BizManager</title> {{-- Updated title --}}
    @vite('resources/css/app.css')
    @livewireStyles
    <meta name="csrf-token" content="{{ csrf_token() }}">
    {{-- @include('dashboard_layouts.head') --}}
    {{-- Removed custom <style> block --}}
</head>
<body class="bg-gray-50 flex h-screen overflow-hidden">

    {{-- Sidebar Component --}}
    <x-dashboard.sidebar />

    {{-- Main Wrapper (Navbar + Content) --}}
    <div class="flex flex-col flex-1 overflow-y-auto">

        {{-- Navbar Component --}}
        <x-dashboard.navbar />

        {{-- Main Content Area --}}
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-8">

            {{-- Keep Bootstrap row/col structure for centering --}}
            <div class="row">
                <div class="col-12 col-lg-8 mx-auto my-4">
                    {{-- Styled Title Card --}}
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 text-center p-6">
                        <h3 class="text-2xl font-semibold text-gray-900">Invoice Parameters</h3>
                    </div>
                </div>
            </div>

            {{-- Keep Bootstrap row/col structure for form container --}}
            <div class="row">
                <div class="col-12 col-lg-8 m-auto">
                    
                    {{-- Main Form Card --}}
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 space-y-6"> {{-- Added space-y-6 --}}
                        
                        {{-- Invoice Infos Section --}}
                        <div>
                            <h5 class="text-lg font-semibold text-gray-900 mb-1">Invoice Infos</h5>
                            <p class="text-sm text-gray-600">Mandatory information</p>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4"> {{-- Use Tailwind grid --}}
                                <div>
                                    <label for="invoice_prefix" class="block text-sm font-medium text-gray-700 mb-1">Prefix:</label>
                                    <input
                                        id="invoice_prefix"
                                        class="editable entrepriseInfos block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm bg-gray-100/50"
                                        type="text" name="invoice_prefix"
                                        value="{{$entreprise->invoice_prefix}}" readonly />
                                </div>
                            </div>
                        </div>

                        {{-- General Conditions Section --}}
                        <div class="border-t border-gray-200 pt-6">
                            <label for="general_conditions" class="block text-sm font-medium text-gray-700 mb-1">General Conditions:</label>
                            <textarea
                                id="general_conditions"
                                class="editable entrepriseInfos block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm bg-gray-100/50"
                                name="general_conditions" rows="6" readonly>{{$entreprise->general_conditions}}</textarea>
                        </div>
                        
                        {{-- Invoice Body Message Section --}}
                        <div class="border-t border-gray-200 pt-6">
                            <h5 class="text-lg font-semibold text-gray-900 mb-1">Invoice Body Message</h5>
                            <label for="invoice_body" class="block text-sm font-medium text-gray-700 mb-1">Invoice Message:</label>
                            <textarea
                                id="invoice_body"
                                class="editable entrepriseInfos block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm bg-gray-100/50"
                                name="invoice_body" rows="4" readonly>{{$entreprise->invoice_body}}</textarea>
                        </div>
                        
                        {{-- Invoice Reminder Messages Section --}}
                        <div class="border-t border-gray-200 pt-6">
                             <h5 class="text-lg font-semibold text-gray-900 mb-1">Invoice Reminder Body Messages</h5>
                            <div class="grid grid-cols-1 gap-4 mt-4"> {{-- Use grid for layout --}}
                                <div>
                                    <label for="first_reminder" class="block text-sm font-medium text-gray-700 mb-1">First Reminder Message:</label>
                                    <textarea
                                        id="first_reminder"
                                        class="editable entrepriseInfos block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm bg-gray-100/50"
                                        name="first_reminder" rows="4" readonly>{{$entreprise->first_reminder}}</textarea>
                                </div>
                                <div>
                                    <label for="second_reminder" class="block text-sm font-medium text-gray-700 mb-1">Second Reminder Message:</label>
                                    <textarea
                                        id="second_reminder"
                                        class="editable entrepriseInfos block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm bg-gray-100/50"
                                        name="second_reminder" rows="4" readonly>{{$entreprise->second_reminder}}</textarea>
                                </div>
                                 <div>
                                    <label for="third_reminder" class="block text-sm font-medium text-gray-700 mb-1">Third Reminder Message:</label>
                                    <textarea
                                        id="third_reminder"
                                        class="editable entrepriseInfos block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm bg-gray-100/50"
                                        name="third_reminder" rows="4" readonly>{{$entreprise->third_reminder}}</textarea>
                                </div>
                            </div>
                        </div>

                        {{-- Back Button --}}
                        <div class="flex justify-end mt-6 border-t border-gray-200 pt-6">
                            @php
                                $backRoute = route('admin.index'); // Default
                                if (Auth::user()->hasRole('Responsable de projet')) $backRoute = route('Responsable_de_projet.index');
                                elseif (Auth::user()->hasRole('Employee')) $backRoute = route('employee.index');
                                elseif (Auth::user()->hasRole('Responsable de projet et gestionnaire')) $backRoute = route('Responsable_gestionnaire.index');
                            @endphp
                            <a href="{{ $backRoute }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 border border-gray-300 text-sm font-medium px-4 py-2 rounded-lg">
                                Back
                            </a>
                        </div>
                        
                    </div> {{-- End Card --}}
                </div> {{-- End Col --}}
            </div> {{-- End Row --}}
        
        </main>
    </div> {{-- End Main Wrapper --}}

    @livewireScripts
    {{-- @include('dashboard_layouts.scripts') --}}

    {{-- Keep JS libraries --}}
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="//unpkg.com/alpinejs" defer></script> {{-- Keep Alpine for date picker --}}

    {{-- Keep existing scripts, add Tailwind classes to inputs --}}
    <script>
    $(document).ready(function() {
        $(".entrepriseInfos").dblclick(function() {
            $(this).prop('readonly', false);
            // Add focus styles
            $(this).addClass('focus:border-blue-500 focus:ring-blue-500 bg-white').removeClass('bg-gray-100/50');
        });

        $(".entrepriseInfos").blur(function() {
            $(this).prop('readonly', true);
            // Remove focus styles
            $(this).removeClass('focus:border-blue-500 focus:ring-blue-500 bg-white').addClass('bg-gray-100/50');
            
            var newContent = $(this).val();
            var EntrepriseId = "{{ Auth::user()->entreprise_id }}";
            var columnName = $(this).attr('name');
            var updatedUrl = '';

            @if(Auth::user()->hasRole('Responsable de projet'))
                updatedUrl = '/Responsable_de_projet/Enterprise_infos/updateInfos';
            @elseif(Auth::user()->hasRole('Admin') || Auth::user()->hasRole('SuperAdmin')) // Added SuperAdmin
                updatedUrl = '/admin/Enterprise_infos/updateInfos';
            @elseif(Auth::user()->hasRole('Employee'))
                updatedUrl = '/employee/Enterprise_infos/updateInfos';
            @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                updatedUrl = '/Responsable_gestionnaire/Enterprise_infos/updateInfos';
            @endif

            if(updatedUrl) { // Check if url was set
                $.ajax({
                    url: updatedUrl,
                    type: 'post',
                    data: {
                        content: newContent,
                        id: EntrepriseId,
                        column: columnName,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        // console.log('Update successful');
                        // You could add a small success indicator here
                    },
                    error: function(xhr, status, error) {
                        console.error("Error updating content: " + error, xhr.responseText);
                        // Optionally revert the text or show an error
                        // $(this).val(originalContent); // Need to store original content on dblclick
                    }
                });
            } else {
                 console.error("No update route defined for this user role.");
            }
        });
    });
    </script>

</body>
</html>