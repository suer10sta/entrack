<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.Devis Parameters - DalyTrac') }}</title> {{-- Updated title --}}
    @vite('resources/css/app.css') 
    @livewireStyles
    @include('admin.devis.partials.dashboardStyles')
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        /* Styling for inputs/textareas (non-readonly by default) */
        .entrepriseInfos {
            text-align: left;
            background-color: #ffffff; /* Fond blanc normal */
        }
        /* Styling for visual feedback after saving */
        input.border-green-500, textarea.border-green-500 {
            border-color: #22c55e;
            box-shadow: 0 0 0 1px #22c55e;
        }
        input.border-red-500, textarea.border-red-500 {
            border-color: #ef4444;
            box-shadow: 0 0 0 1px #ef4444;
        }
    </style>
</head>

<body class="devis-dashboard-page dt-app-shell bg-[#f8fafc] text-slate-900 antialiased flex h-screen overflow-hidden">

    {{-- Sidebar Component --}}
    @if(Auth::user()->hasRole('Admin'))
        <x-dashboard.sidebar />
    @else
        <x-employees.sidebar />
    @endif

    <div class="flex flex-col flex-1 overflow-y-auto">
        {{-- Navbar Component --}}
        <x-dashboard.navbar />
        
        <main class="flex-1 min-w-0 overflow-x-hidden overflow-y-auto bg-[#f8fafc] px-3 py-4 sm:px-5 sm:py-5 lg:px-16 lg:py-7 space-y-5">
            {{-- 1. Header Section --}}
            <div class="flex flex-wrap justify-between items-end gap-3">
                <div>
                    <p class="dt-overline mb-0.5">{{ __('messages.New Devis') }}</p>
                    <h1 class="text-[18px] sm:text-[20px] lg:text-[22px] font-bold text-[#0B2638] leading-tight">{{ __('messages.Devis Parameters') }}</h1>
                    <p class="text-[12px] text-slate-500 mt-0.5">{{ __('messages.Configure devis settings and templates') }}</p>
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

            {{-- Added "invoiceParamsForm" ID so the JS can target it --}}
            <form id="invoiceParamsForm" class="devis-form space-y-5">
                @csrf
                <input type="hidden" name="entreprise_id" value="{{ Auth::user()->entreprise_id }}">

                {{-- 2. Devis Information Card --}}
                <div class="dt-card overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-100 flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-[#2D7080]/10 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-[#2D7080]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317a1.724 1.724 0 012.35 0l.9 1.2a1.724 1.724 0 002.1.55l1.4-.5a1.724 1.724 0 012.05 1.18l.3 1.45a1.724 1.724 0 001.45 1.45l1.45.3a1.724 1.724 0 011.18 2.05l-.5 1.4a1.724 1.724 0 00.55 2.1l1.2.9a1.724 1.724 0 010 2.35l-1.2.9a1.724 1.724 0 00-.55 2.1l.5 1.4a1.724 1.724 0 01-1.18 2.05l-1.45.3a1.724 1.724 0 00-1.45 1.45l-.3 1.45a1.724 1.724 0 01-2.05 1.18l-1.4-.5a1.724 1.724 0 00-2.1.55l-.9 1.2a1.724 1.724 0 01-2.35 0l-.9-1.2a1.724 1.724 0 00-2.1-.55l-1.4.5a1.724 1.724 0 01-2.05-1.18l-.3-1.45a1.724 1.724 0 00-1.45-1.45l-1.45-.3a1.724 1.724 0 01-1.18-2.05l.5-1.4a1.724 1.724 0 00-.55-2.1l-1.2-.9a1.724 1.724 0 010-2.35l1.2-.9a1.724 1.724 0 00.55-2.1l-.5-1.4a1.724 1.724 0 011.18-2.05l1.45-.3a1.724 1.724 0 001.45-1.45l.3-1.45a1.724 1.724 0 012.05-1.18l1.4.5a1.724 1.724 0 002.1-.55z"/><circle cx="11.5" cy="15.5" r="3"/></svg>
                        </div>
                        <div>
                            <p class="text-[13px] font-semibold text-[#0B2638]">{{ __('messages.Devis Information') }}</p>
                            <p class="text-[11.5px] text-slate-400">{{ __('messages.Mandatory information devis') }}</p>
                        </div>
                    </div>

                    <div class="p-5 sm:p-6 space-y-5">
                    <div>
                        <h5 class="sr-only">{{ __('messages.Devis Information') }}</h5>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="devis_prefix" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Prefix') }}</label>
                            <input id="devis_prefix" 
                                   {{-- Removed "editable" class --}}
                                   class="entrepriseInfos block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3" 
                                   type="text" 
                                   name="devis_prefix" 
                                   value="{{ $entreprise->devis_prefix ?? '' }}" />
                        </div>
                        <div>
                            <label for="devis_start_number" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Start Number') }}</label>
                            <input id="devis_start_number" 
                                   {{-- Removed "editable" class --}}
                                   class="entrepriseInfos block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3"
                                   type="text" 
                                   name="devis_start_number"
                                   value="{{$entreprise->devis_start_number}}" />
                        </div>
                    </div>
                    <div>
                        <label for="general_conditions_Devis" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.General Condition') }}</label>
                        <textarea id="general_conditions_Devis" rows="6"
                            {{-- Removed "editable" class --}}
                            class="entrepriseInfos block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3"
                            name="general_conditions_Devis" >{{ $entreprise->general_conditions_Devis }}</textarea>
                    </div>
                    </div>
                </div>

                {{-- 3. Devis Body Message Card --}}
                <div class="dt-card overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-100 flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-[#2D7080]/10 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-[#2D7080]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M7 8h10M7 12h10M7 16h6M5 3h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z"/></svg>
                        </div>
                        <div>
                            <p class="text-[13px] font-semibold text-[#0B2638]">{{ __('messages.Devis Body Message') }}</p>
                            <p class="text-[11.5px] text-slate-400">{{ __('messages.Message devis') }}</p>
                        </div>
                    </div>
                    <div class="p-5 sm:p-6 space-y-5">
                    <div>
                        <h5 class="sr-only">{{ __('messages.Devis Body Message') }}</h5>
                    </div>
                    <div>
                        <textarea id="devis_body" rows="6" {{-- Added ID for clarity --}}
                            {{-- Removed "editable" class --}}
                            class="entrepriseInfos block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3"
                            name="devis_body" >{{ $entreprise->devis_body }}</textarea>
                    </div>
                    </div>
                </div>

                {{-- 4. Action buttons --}}
                <div class="flex flex-col-reverse sm:flex-row sm:justify-between sm:items-center gap-2 mt-5 pt-5 border-t border-slate-200">
                    @php
                        $backRoute = route('admin.index'); // Fallback
                        if(Auth::user()->hasRole('Responsable de projet')) $backRoute = route('Responsable_de_projet.index');
                        elseif(Auth::user()->hasRole('Employee')) $backRoute = route('employee.index');
                        elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire')) $backRoute = route('Responsable_gestionnaire.index');
                    @endphp

                    <a href="{{ url()->previous() ?: $backRoute }}" 
                       class="dt-btn dt-btn-ghost justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        {{ __('messages.Back') }}
                    </a>
                    
                    {{-- Updated ID to match the JS selector --}}
                    <button type="submit" 
                            id="saveButton" 
                            class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg shadow-sm disabled:opacity-50"
                            {{ !$updateUrl ? 'disabled' : '' }}>
                        {{-- Icon and label now handled in JS; fallback included --}}
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                        </svg>
                        {{ __('messages.Save All Changes devis') }}
                    </button>
                </div>
            </form>
        </main>
    </div>

    {{-- Scripts --}}
    @livewireScripts
    {{-- @include('dashboard_layouts.scripts') --}} {{-- Ensure this does not include another jQuery instance --}}
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="//unpkg.com/alpinejs" defer></script>

    {{-- Save logic (invoice.parameters) --}}
    <script>
        $(document).ready(function() {
            
            // Store the original value on focus to detect changes
            $(".entrepriseInfos").focus(function() {
                 $(this).data('originalValue', $(this).val());
                 // Clear existing border states
                 $(this).removeClass('border-green-500 border-red-500'); 
            });

            $('#invoiceParamsForm').on('submit', function(e) {
                e.preventDefault();
                
                // Button pulled from the new file
                const $button = $('#saveButton'); 
                const originalText = $button.html();
                
                $button.prop('disabled', true).html('<svg class="animate-spin h-4 w-4 mr-2 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>{{ __('messages.Saving...') }}');

                const entrepriseId = "{{ Auth::user()->entreprise_id }}";
                const updateUrl = "{{ $updateUrl }}";
                const formData = $(this).serializeArray();
                
                // Filter out fields that shouldn't be saved
                const fieldsToUpdate = formData.filter(field => 
                    field.name !== '_token' && field.name !== 'entreprise_id'
                );
                
                let completed = 0;
                let errors = 0;
                const total = fieldsToUpdate.length;
                let hasChanges = false; // Track whether any field actually changed

                if (total === 0) {
                    $button.prop('disabled', false).html(originalText);
                    return;
                }

                fieldsToUpdate.forEach(function(field) {
                    const $input = $(`[name="${field.name}"]`);
                    const originalValue = $input.data('originalValue');
                    const newValue = field.value;

                    // Skip if the value did not change
                    if (newValue === originalValue || typeof originalValue === 'undefined') {
                        completed++;
                        checkComplete();
                        return; // continue
                    }
                    
                    // Reaching this point means we detected a change
                    hasChanges = true;

                    $.ajax({
                        url: updateUrl,
                        type: 'POST',
                        data: {
                            content: newValue,
                            id: entrepriseId,
                            column: field.name,
                            _token: $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            completed++;
                            $input.data('originalValue', newValue); // Update stored value
                            $input.addClass('border-green-500'); // Success feedback
                            checkComplete();
                        },
                        error: function(xhr) {
                            completed++;
                            errors++;
                            $input.addClass('border-red-500'); // Error feedback
                            console.error('Error saving ' + field.name + ':', xhr);
                            checkComplete();
                        }
                    });
                });
 
                // Handle button animation/resets
                function checkComplete() {
                    if (completed === total) {
                        $button.prop('disabled', false); 
                        
                        // If nothing needed to be saved
                        if (!hasChanges) {
                            $button.html(originalText); // Restore default appearance
                             // No alert; user simply clicked save without changes
                             return;
                        }

                        if (errors === 0) {
                            // --- Success animation ---
                            $button.removeClass('bg-blue-600 hover:bg-blue-700').addClass('bg-green-600');
                            $button.html('<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>{{ __('messages.Saved!') }}');
                        
                        } else {
                            // --- Error animation ---
                            $button.removeClass('bg-blue-600 hover:bg-blue-700').addClass('bg-red-600');
                            $button.html('<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>{{ __('messages.Error!') }}');
                            alert('{{ __('messages.Failed to save changes. Please try again.') }}');
                        }

                        // --- Reset button state ---
                        setTimeout(() => {
                            $button.removeClass('bg-green-600 bg-red-600').addClass('bg-blue-600 hover:bg-blue-700');
                            $button.html(originalText); // Restore original icon/text
                            $('.entrepriseInfos').removeClass('border-green-500 border-red-500'); // Clear feedback states
                        }, 2000); // 2-second delay
                    }
                }
            });
        });
    </script> 
</body> 
</html>
