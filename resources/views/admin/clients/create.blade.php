<!DOCTYPE html>
<html lang="en">

<head>
    <x-favicon />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.Add New Client') }}</title>
    {{-- This assumes you have Vite and Tailwind CSS set up --}}
    @vite('resources/css/app.css')
</head>

<body class="dt-app-shell bg-[#f8fafc] text-slate-900 antialiased flex h-screen overflow-hidden">
    
    @if(Auth::user()->hasRole('Admin'))
        <x-dashboard.sidebar />
    @else
        <x-employees.sidebar />
    @endif

    <div class="flex flex-col flex-1 overflow-y-auto">
    
        <x-dashboard.navbar />

        {{-- Main Content --}}
        <main class="dt-create-page flex-1 overflow-y-auto bg-[#f8fafc] p-4 sm:p-6 lg:px-20 lg:py-8">
            <div class="dt-create-container mx-auto w-full">

                {{-- Header with back navigation (same design as Admin Dashboard) --}}
                @php
                    $indexRoute = '#'; // Default fallback
                    if (Auth::user()->hasRole('Responsable de projet')) $indexRoute = route('Responsable_de_projet.clients.index');
                    elseif (Auth::user()->hasRole('Responsable de projet et gestionnaire')) $indexRoute = route('Responsable_gestionnaire.clients.index');
                    elseif (Auth::user()->hasRole('Employee')) $indexRoute = route('employee.clients.index');
                    elseif (Auth::user()->hasRole('Admin')) $indexRoute = route('admin.clients.index');
                @endphp
                <div class="flex items-center gap-3 mb-5">
                    <a href="{{ $indexRoute }}" title="{{ __('messages.Clients Table') }}" aria-label="{{ __('messages.Clients Table') }}"
                        class="dt-btn dt-btn-ghost !px-3 flex-shrink-0">
                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
                    </a>
                    <div class="min-w-0">
                        <p class="dt-overline mb-1">{{ __('messages.Clients Table') }}</p>
                        <h1 class="text-[22px] sm:text-[24px] font-bold tracking-tight text-[#0B2638] leading-tight truncate">
                            {{ __('messages.Add New Client') }}
                        </h1>
                    </div>
                </div>

                {{-- Error Messages --}}
                @if ($errors->any())
                        <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl">
                        <div class="text-[13px] font-semibold mb-2">{{ __('messages.Mandatory informations') }}</div>
                        <ul class="list-disc list-inside space-y-1 text-[13px]">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-[13px] font-medium">
                        {{ session('error') }}
                    </div>
                @endif

                {{-- Form --}}
                <form method="POST" action="
                    @if(Auth::user()->hasRole('Responsable de projet')) {{route('Responsable_de_projet.clients.store')}}
                    @elseif(Auth::user()->hasRole('Employee')) {{route('employee.clients.store')}}
                    @elseif(Auth::user()->hasRole('Admin')) {{route('admin.clients.store')}}
                    @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire')) {{route('Responsable_gestionnaire.clients.store')}}
                    @endif
                " enctype="multipart/form-data" autocomplete="off">
                    @csrf
                    <input type="hidden" name="from" value="{{ old('from', request()->query('from')) }}" />

                    <div class="space-y-6"> 

                        <div class="dt-card dt-create-panel p-5 sm:p-6">
                            <h2 class="dt-section-title mb-1">{{ __('messages.Personal Infos') }}</h2>
                            <p class="dt-section-subtitle mb-5">{{ __('messages.Mandatory informations') }}</p>
                            
                            <div class="space-y-4">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('messages.Name') }} <span
                                                class="text-red-500">*</span></label>
                                        <input type="text" name="name" value="{{ old('name') }}" placeholder="{{ __('messages.Full Name') }}"
                                            class="w-full border @error('name') border-red-500 @else border-slate-300 @enderror rounded-lg px-4 py-2 focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none transition" />
                                        @error('name')
                                        <p class="text-red-500 text-xs mt-1.5">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('messages.Client Number') }}</label>
                                        <input type="text" name="client_number" value="{{ old('client_number') }}" placeholder="{{ __('messages.Client Number') }}"
                                            class="w-full border @error('client_number') border-red-500 @else border-slate-300 @enderror rounded-lg px-4 py-2 focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none transition" />
                                        @error('client_number')
                                        <p class="text-red-500 text-xs mt-1.5">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('messages.Email') }}</label>
                                        <input type="text" name="email" value="{{ old('email') }}" placeholder="*****@gmail.com"
                                            class="w-full border @error('email') border-red-500 @else border-slate-300 @enderror rounded-lg px-4 py-2 focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none transition" />
                                        @error('email')
                                        <p class="text-red-500 text-xs mt-1.5">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('messages.Phone') }}</label>
                                        <input type="number" name="phone" value="{{ old('phone') }}" placeholder="{{ __('messages.Phone Number') }}"
                                            class="w-full border @error('phone') border-red-500 @else border-slate-300 @enderror rounded-lg px-4 py-2 focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none transition" />
                                        @error('phone')
                                        <p class="text-red-500 text-xs mt-1.5">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="dt-card dt-create-panel p-5 sm:p-6">
                            <h2 class="dt-section-title mb-1">{{ __('messages.address_workplace_infos') }}</h2>
                            <p class="dt-section-subtitle mb-5">{{ __('messages.Mandatory informations') }}</p>

                            <div class="space-y-4">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('messages.Street') }} <span
                                                class="text-red-500">*</span></label>
                                        <input type="text" name="address" value="{{ old('address') }}" placeholder="{{ __('messages.Address') }}"
                                            class="w-full border @error('address') border-red-500 @else border-slate-300 @enderror rounded-lg px-4 py-2 focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none transition" />
                                        @error('address')
                                        <p class="text-red-500 text-xs mt-1.5">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('messages.City') }} <span
                                                class="text-red-500">*</span></label>
                                        <input type="text" name="city" value="{{ old('city') }}" placeholder="{{ __('messages.City') }}"
                                            class="w-full border @error('city') border-red-500 @else border-slate-300 @enderror rounded-lg px-4 py-2 focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none transition" />
                                        @error('city')
                                        <p class="text-red-500 text-xs mt-1.5">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('messages.Zip Code') }} <span
                                                class="text-red-500">*</span></label>
                                        <input type="number" name="zip" value="{{ old('zip') }}" placeholder="{{ __('messages.Zip Code') }}"
                                            class="w-full border @error('zip') border-red-500 @else border-slate-300 @enderror rounded-lg px-4 py-2 focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none transition" />
                                        @error('zip')
                                        <p class="text-red-500 text-xs mt-1.5">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('messages.Country') }} <span
                                                class="text-red-500">*</span></label>
                                        <input type="text" name="country" value="{{ old('country') }}" placeholder="{{ __('messages.Country') }}"
                                            class="w-full border @error('country') border-red-500 @else border-slate-300 @enderror rounded-lg px-4 py-2 focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none transition" />
                                        @error('country')
                                        <p class="text-red-500 text-xs mt-1.5">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="dt-card dt-create-panel p-5 sm:p-6">
                            <h2 class="dt-section-title mb-1">{{ __('messages.Billing Infos') }}</h2>
                            <p class="dt-section-subtitle mb-5">{{ __('messages.billing_infos_desc') }}</p>

                            <div class="space-y-4">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('messages.TVA number') }}</label>
                                        <input type="text" name="TVA" value="{{ old('TVA', '0') }}" placeholder="{{ __('messages.TVA number') }}"
                                            class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none transition" />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('messages.Due Date (days)') }}
                                            <span class="text-red-500">*</span></label>
                                        <input type="text" name="due_date" value="{{ old('due_date') }}" placeholder="{{ __('messages.Due Date by Days') }}"
                                            class="w-full border @error('due_date') border-red-500 @else border-slate-300 @enderror rounded-lg px-4 py-2 focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none transition" />
                                        @error('due_date')
                                        <p class="text-red-500 text-xs mt-1.5">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                <div class="flex items-center pt-2 pb-2">
                                    <input id="billingSameAsClient" type="checkbox"
                                        class="w-4 h-4 rounded border-slate-300 text-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25">
                                    <label for="billingSameAsClient"
                                        class="ml-2 text-sm font-medium text-slate-700">{{ __('messages.Billing address is the same as client address') }}</label>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label
                                            class="block text-sm font-medium text-slate-700 mb-1">{{ __('messages.Street') }}</label>
                                        <input type="text" name="billing_street" placeholder="{{ __('messages.Billing Street') }}"
                                            class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none transition" />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('messages.City') }}</label>
                                        <input type="text" name="billing_city" placeholder="{{ __('messages.Billing City') }}"
                                            class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none transition" />
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('messages.Zip Code') }}</label>
                                        <input type="text" name="billing_zip" placeholder="{{ __('messages.Billing Zip Code') }}"
                                            class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none transition" />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('messages.Country') }}</label>
                                        <input type="text" name="billing_country" placeholder="{{ __('messages.Billing Country') }}"
                                            class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none transition" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="dt-card dt-create-panel p-5 sm:p-6">
                            <div class="flex justify-between">
                                <div>
                                <h2 class="dt-section-title mb-1">{{ __('messages.Contacts Infos') }}</h2>
                                <p class="dt-section-subtitle mb-5">{{ __('messages.Mandatory informations') }}</p>
                                </div>
                                
                                <button type="button" id="addContact"
                                    class="mb-4 inline-flex items-center gap-1.5 rounded-lg border border-dashed border-slate-300 bg-white px-3 py-2 text-[12.5px] font-semibold text-slate-500 hover:border-[#2D7080] hover:text-[#2D7080] transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                    {{ __('messages.add_contact') }}
                                </button>
                            </div>
                            <div id="contact_infos" class="space-y-6">
                                <div class="contact-entry space-y-4">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('messages.Full Name') }}</label>
                                            <input type="text" name="contact_name[]" placeholder="{{ __('messages.Full Name') }}"
                                                class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none transition" />
                                        </div>
                                        <div>
                                            <label
                                                class="block text-sm font-medium text-slate-700 mb-1">{{ __('messages.Function') }}</label>
                                            <input type="text" name="contact_function[]"
                                                placeholder="{{ __('messages.Functionality') }}"
                                                class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none transition" />
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('messages.Phone Number') }}</label>
                                            <input type="number" name="contact_mobile_fix[]"
                                                placeholder="{{ __('messages.Phone Number') }}"
                                                class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none transition" />
                                        </div>
                                        <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('messages.Email') }}</label>
                                        <input type="text" name="contact_email[]"
                                                placeholder="*****@gmail.com"
                                                class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none transition" />
                                        </div>
                                    </div>
                                    <div>
                                        <label
                                            class="block text-sm font-medium text-slate-700 mb-1">{{ __('messages.Address') }}</label>
                                        <input type="text" name="contact_address[]" placeholder="{{ __('messages.Address') }}"
                                            class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none transition" />
                                    </div>
                                </div>
                                </div>
                        </div>
                        
                    </div>

                    <div class="dt-create-actions flex flex-col-reverse sm:flex-row sm:justify-end mt-6 gap-2 sm:gap-3">
                        {{-- Cancel Button Logic --}}
                            <a href="javascript:history.back()"
                                class="dt-btn dt-btn-ghost w-full sm:w-auto">{{ __('messages.Cancel') }}</a>
                        <button type="submit"
                            class="dt-btn dt-btn-teal w-full sm:w-auto">
                            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                            {{ __('messages.Create') }}
                        </button>
                    </div>
                </form>

            </div>
        </main>
    </div>

    {{-- Include your dashboard scripts --}}
    @include('dashboard_layouts.scripts') 
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

<script>
        // Disable autocomplete script
        window.addEventListener('load', function () {
            var inputs = document.getElementsByTagName('input');
            for (var i = 0; i < inputs.length; i++) {
                inputs[i].setAttribute('autocomplete', 'none');
            }
        });

        // jQuery scripts for form functionality
        $(document).ready(function () {
            
            // Billing address checkbox logic
            $('#billingSameAsClient').change(function () {
                const isDisabled = this.checked;
                const billingStreet = isDisabled ? $('input[name="address"]').val() : '';
                const billingCity = isDisabled ? $('input[name="city"]').val() : '';
                const billingZip = isDisabled ? $('input[name="zip"]').val() : '';
                const billingCountry = isDisabled ? $('input[name="country"]').val() : '';

                $('input[name="billing_street"]').val(billingStreet).prop('disabled', isDisabled);
                $('input[name="billing_city"]').val(billingCity).prop('disabled', isDisabled);
                $('input[name="billing_zip"]').val(billingZip).prop('disabled', isDisabled);
                $('input[name="billing_country"]').val(billingCountry).prop('disabled', isDisabled);
            });

            // Add new contact logic
            $("#addContact").click(function (e) {
                e.preventDefault();
                var container = $("#contact_infos");
                var entryToClone = container.children(".contact-entry").first();
                var newEntry = entryToClone.clone();

                // Clear input values in the new entry
                newEntry.find('input').val('');

                // Create and add a delete button
                var deleteButton = $('<button type="button" class="w-full px-4 py-2 bg-red-50 hover:bg-red-100 text-red-700 border border-red-200 rounded-xl text-[13px] font-semibold mt-4 remove-contact transition-colors">{{ __('messages.Delete Contact') }}</button>');
                
                deleteButton.click(function() {
                    $(this).closest('.contact-entry').remove();
                });
                
                // Add a visual separator (for all new cards)
                newEntry.prepend('<hr class="my-4 border-t-2 border-[#0B2638]/70">');
                
                // Add the delete button
                newEntry.append(deleteButton);

                // Append the new entry to the container
                container.append(newEntry);
            });

            // This delegation is needed to remove newly added contacts
            // (The button is added dynamically, so we listen on the container)
            $('#contact_infos').on('click', '.remove-contact', function() {
                $(this).closest('.contact-entry').remove();
            });

        });
</script>

</body>
</html>
