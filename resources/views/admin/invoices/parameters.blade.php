<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <x-favicon />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.Invoice Parameters - DalyTrac') }}</title>
    @vite('resources/css/app.css')
    @livewireStyles
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        /* Styling for inputs/textareas */
        .entrepriseInfos {
            text-align: left;
            background-color: #ffffff;
        }
        /* Visual feedback after saving */
        input.border-green-500, textarea.border-green-500 {
            border-color: #22c55e;
            box-shadow: 0 0 0 1px #22c55e;
        }
        input.border-red-500, textarea.border-red-500 {
            border-color: #ef4444;
            box-shadow: 0 0 0 1px #ef4444;
        }
        /* Focus ring with brand color */
        .entrepriseInfos:focus {
            outline: none;
            border-color: #2D7080;
            box-shadow: 0 0 0 3px rgba(45, 112, 128, 0.1);
        }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-50 to-slate-100 flex h-screen overflow-hidden font-sans">
    @if(Auth::user()->hasRole('Admin'))
        <x-dashboard.sidebar />
    @else
        <x-employees.sidebar />
    @endif
    
    <div class="flex flex-col flex-1 overflow-y-auto">
        <x-dashboard.navbar />
        
        <main class="flex-1 overflow-y-auto p-4 md:p-6 lg:p-8">
            <div class="max-w-5xl mx-auto">
                {{-- Page Header --}}
                <div class="mb-8">
                    <h1 class="text-3xl md:text-4xl font-bold text-[#0B2638] mb-2">{{ __('messages.Invoice Parameters') }}</h1>
                    <p class="text-base text-gray-600">{{ __('messages.Configure invoice settings and templates') }}</p>
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

                <form id="invoiceParamsForm" class="space-y-6">
                    @csrf
                    <input type="hidden" name="entreprise_id" value="{{ Auth::user()->entreprise_id }}">

                    {{-- Invoice Information Card --}}
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-[0_20px_50px_rgba(15,23,42,0.12),0_4px_12px_rgba(15,23,42,0.08)] p-6 md:p-8 space-y-6">
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-[#2D7080]/10 flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#2D7080]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <div>
                                <h5 class="text-xl font-bold text-[#0B2638]">{{ __('messages.Invoice Information') }}</h5>
                                <p class="text-sm text-gray-500 mt-1">{{ __('messages.Mandatory information for all invoices') }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="invoice_prefix" class="block text-sm font-semibold text-gray-700 mb-2">{{ __('messages.Invoice Prefix') }}</label>
                                <input id="invoice_prefix" 
                                       class="entrepriseInfos block w-full rounded-xl border border-slate-300 shadow-sm sm:text-sm py-3 px-4 transition-all duration-200" 
                                       type="text" 
                                       name="invoice_prefix" 
                                       value="{{ $entreprise->invoice_prefix ?? '' }}" />
                                <p class="mt-2 text-xs text-gray-500">{{ __('messages.Used as prefix for all invoice numbers') }}</p>
                            </div>
                            <div>
                                <label for="invoice_start_number" class="block text-sm font-semibold text-gray-700 mb-2">{{ __('messages.Start Number') }}</label>
                                <input id="invoice_start_number"
                                       class="entrepriseInfos block w-full rounded-xl border border-slate-300 shadow-sm sm:text-sm py-3 px-4 transition-all duration-200"
                                       type="text"
                                       name="invoice_start_number"
                                       value="{{ $entreprise->invoice_start_number ?? '' }}" />
                                <p class="mt-2 text-xs text-gray-500">{{ __('messages.Next generated invoice will increment from this value') }}</p>
                            </div>
                        </div>

                        <div>
                            <label for="general_conditions" class="block text-sm font-semibold text-gray-700 mb-2">{{ __('messages.General Conditions') }}</label>
                            <textarea id="general_conditions" 
                                      class="entrepriseInfos block w-full rounded-xl border border-slate-300 shadow-sm sm:text-sm py-3 px-4 transition-all duration-200 resize-y" 
                                      name="general_conditions" 
                                      rows="5">{{ $entreprise->general_conditions ?? '' }}</textarea>
                            <p class="mt-2 text-xs text-gray-500">{{ __('messages.Displayed at the bottom of invoices') }}</p>
                        </div>
                    </div>

                    {{-- Invoice Body Message Card --}}
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-[0_20px_50px_rgba(15,23,42,0.12),0_4px_12px_rgba(15,23,42,0.08)] p-6 md:p-8 space-y-6">
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-[#E7B84B]/10 flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#E7B84B]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div>
                                <h5 class="text-xl font-bold text-[#0B2638]">{{ __('messages.Invoice Body Message') }}</h5>
                                <p class="text-sm text-gray-500 mt-1">{{ __('messages.Message sent with invoice emails') }}</p>
                            </div>
                        </div>
                        <div>
                            <label for="invoice_body" class="block text-sm font-semibold text-gray-700 mb-2">{{ __('messages.Message Content') }}</label>
                            <textarea id="invoice_body" 
                                      class="entrepriseInfos block w-full rounded-xl border border-slate-300 shadow-sm sm:text-sm py-3 px-4 transition-all duration-200 resize-y" 
                                      name="invoice_body" 
                                      rows="4">{{ $entreprise->invoice_body ?? '' }}</textarea>
                            <p class="mt-2 text-xs text-gray-500">{{ __('messages.This message will be included in invoice emails') }}</p>
                        </div>
                    </div>

                    {{-- Reminder Messages Card --}}
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-[0_20px_50px_rgba(15,23,42,0.12),0_4px_12px_rgba(15,23,42,0.08)] p-6 md:p-8 space-y-6">
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-[#E7B84B]/10 flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#E7B84B]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                </svg>
                            </div>
                            <div>
                                <h5 class="text-xl font-bold text-[#0B2638]">{{ __('messages.Reminder Messages') }}</h5>
                                <p class="text-sm text-gray-500 mt-1">{{ __('messages.Customize reminder emails for unpaid invoices') }}</p>
                            </div>
                        </div>

                        <div class="space-y-6">
                            <div>
                                <label for="first_reminder" class="block text-sm font-semibold text-gray-700 mb-2">{{ __('messages.First Reminder') }}</label>
                                <textarea id="first_reminder" 
                                          class="entrepriseInfos block w-full rounded-xl border border-slate-300 shadow-sm sm:text-sm py-3 px-4 transition-all duration-200 resize-y" 
                                          name="first_reminder" 
                                          rows="4">{{ $entreprise->first_reminder ?? '' }}</textarea>
                                <p class="mt-2 text-xs text-gray-500">{{ __('messages.Sent 5 days after invoice due date') }}</p>
                            </div>

                            <div>
                                <label for="second_reminder" class="block text-sm font-semibold text-gray-700 mb-2">{{ __('messages.Second Reminder') }}</label>
                                <textarea id="second_reminder" 
                                          class="entrepriseInfos block w-full rounded-xl border border-slate-300 shadow-sm sm:text-sm py-3 px-4 transition-all duration-200 resize-y" 
                                          name="second_reminder" 
                                          rows="4">{{ $entreprise->second_reminder ?? '' }}</textarea>
                                <p class="mt-2 text-xs text-gray-500">{{ __('messages.Sent 10 days after invoice due date') }}</p>
                            </div>

                            <div>
                                <label for="third_reminder" class="block text-sm font-semibold text-gray-700 mb-2">{{ __('messages.Third Reminder') }}</label>
                                <textarea id="third_reminder" 
                                          class="entrepriseInfos block w-full rounded-xl border border-slate-300 shadow-sm sm:text-sm py-3 px-4 transition-all duration-200 resize-y" 
                                          name="third_reminder" 
                                          rows="4">{{ $entreprise->third_reminder ?? '' }}</textarea>
                                <p class="mt-2 text-xs text-gray-500">{{ __('messages.Sent 15 days after invoice due date') }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="flex flex-col sm:flex-row justify-between items-center gap-4 pt-6 border-t border-slate-200">
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
                           class="inline-flex items-center justify-center w-full sm:w-auto bg-white hover:bg-slate-50 text-gray-700 border border-slate-300 text-sm font-semibold px-6 py-3 rounded-xl shadow-sm transition-all duration-200 hover:shadow-md">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            {{ __('messages.Back') }}
                        </a>

                        <button type="submit"
                                id="saveButton"
                                class="inline-flex items-center justify-center w-full sm:w-auto bg-[#2D7080] hover:bg-[#0B2638] text-white text-sm font-semibold px-6 py-3 rounded-xl shadow-sm transition-all duration-200 hover:shadow-md disabled:opacity-50 disabled:cursor-not-allowed"
                                {{ !$updateUrl ? 'disabled' : '' }}>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                            </svg>
                            {{ __('messages.Save All Changes') }}
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    @livewireScripts
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script>
        $(document).ready(function() {
            // Store the original value on focus to detect changes
            $('.entrepriseInfos').focus(function() {
                 $(this).data('originalValue', $(this).val());
                 // Clear existing border states
                 $(this).removeClass('border-green-500 border-red-500'); 
            });

            $('#invoiceParamsForm').on('submit', function(e) {
                e.preventDefault();
                
                const $button = $('#saveButton');
                const originalText = $button.html();
                
                $button.prop('disabled', true).html('<svg class="animate-spin h-4 w-4 mr-2 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>{{ __('messages.Saving...') }}');

                const entrepriseId = "{{ Auth::user()->entreprise_id }}";
                const updateUrl = "{{ $updateUrl }}";
                const formData = $(this).serializeArray();
                
                const fieldsToUpdate = formData.filter(field => 
                    field.name !== '_token' && field.name !== 'entreprise_id'
                );
                
                let completed = 0;
                let errors = 0;
                const total = fieldsToUpdate.length;
                let hasChanges = false;

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
                        return;
                    }
                    
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
                            $input.data('originalValue', newValue);
                            $input.addClass('border-green-500');
                            checkComplete();
                        },
                        error: function(xhr) {
                            completed++;
                            errors++;
                            $input.addClass('border-red-500');
                            console.error('Error saving ' + field.name + ':', xhr);
                            checkComplete();
                        }
                    });
                });

                function checkComplete() {
                    if (completed === total) {
                        $button.prop('disabled', false);
                        
                        if (!hasChanges) {
                            $button.html(originalText);
                            return;
                        }

                        if (errors === 0) {
                            $button.removeClass('bg-[#2D7080] hover:bg-[#0B2638]').addClass('bg-green-600');
                            $button.html('<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>{{ __('messages.Saved!') }}');
                        } else {
                            $button.removeClass('bg-[#2D7080] hover:bg-[#0B2638]').addClass('bg-red-600');
                            $button.html('<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77-1.333.192 3 1.732 3z" /></svg>{{ __('messages.Error!') }}');
                            
                            if (errors === total) {
                                alert('{{ __('messages.Failed to save changes. Please try again.') }}');
                            } else {
                                alert('{{ __('messages.Some changes were saved, but fields failed to save.') }}'.replace(':count', errors));
                            }
                        }

                        setTimeout(() => {
                            $button.removeClass('bg-green-600 bg-red-600').addClass('bg-[#2D7080] hover:bg-[#0B2638]');
                            $button.html(originalText);
                            $('.entrepriseInfos').removeClass('border-green-500 border-red-500');
                        }, 2000);
                    }
                }
            });
        });
    </script>
</body>
</html>
