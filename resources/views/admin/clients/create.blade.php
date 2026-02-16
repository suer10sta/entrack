<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Client</title>
    {{-- This assumes you have Vite and Tailwind CSS set up --}}
    @vite('resources/css/app.css')
</head>

<body class="bg-gray-50 flex h-screen overflow-hidden">
    
    @if(Auth::user()->hasRole('Admin'))
        <x-dashboard.sidebar />
    @else
        <x-employees.sidebar />
    @endif

    <div class="flex flex-col flex-1 overflow-y-auto">
    
        <x-dashboard.navbar />

        {{-- Main Content --}}
        <main class="flex-1 p-8 bg-[#eaebef]">
            <div class="max-w-4xl mx-auto">
                
                {{-- Header --}}
                <div class="mb-8 text-center">
                    <h1 class="text-4xl font-bold text-slate-900 mb-2">Add New Client</h1>
                </div>

                {{-- Error Messages --}}
                @if ($errors->any())
                    <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                        <div class="font-semibold mb-2">Please fix the following errors:</div>
                        <ul class="list-disc list-inside space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
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

                    <div class="space-y-6"> 

                        <div class="bg-white rounded-2xl shadow-sm p-6">
                            <h2 class="text-xl font-bold text-slate-900 mb-1">Personal Infos</h2>
                            <p class="text-sm text-slate-500 mb-6">Mandatory informations</p>
                            
                            <div class="space-y-4">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-1">Name <span
                                                class="text-red-500">*</span></label>
                                        @error('name')
                                        <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                        @enderror
                                        <input type="text" name="name" value="{{ old('name') }}" placeholder="Full Name"
                                            class="w-full border @error('name') border-red-500 @else border-slate-300 @enderror rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-1">Client Number <span
                                                class="text-red-500">*</span></label>
                                        @error('client_number')
                                        <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                        @enderror
                                        <input type="text" name="client_number" value="{{ old('client_number') }}" placeholder="Client Number"
                                            class="w-full border @error('client_number') border-red-500 @else border-slate-300 @enderror rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                                        @error('email')
                                        <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                        @enderror
                                        <input type="text" name="email" value="{{ old('email') }}" placeholder="*****@gmail.com"
                                            class="w-full border @error('email') border-red-500 @else border-slate-300 @enderror rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-1">Phone</label>
                                        @error('phone')
                                        <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                        @enderror
                                        <input type="number" name="phone" value="{{ old('phone') }}" placeholder="Phone Number"
                                            class="w-full border @error('phone') border-red-500 @else border-slate-300 @enderror rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-2xl shadow-sm p-6">
                            <h2 class="text-xl font-bold text-slate-900 mb-1">Address Infos</h2>
                            <p class="text-sm text-slate-500 mb-6">Mandatory informations</p>

                            <div class="space-y-4">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-1">Street <span
                                                class="text-red-500">*</span></label>
                                        @error('address')
                                        <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                        @enderror
                                        <input type="text" name="address" value="{{ old('address') }}" placeholder="Your Full Address Here"
                                            class="w-full border @error('address') border-red-500 @else border-slate-300 @enderror rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-1">City <span
                                                class="text-red-500">*</span></label>
                                        @error('city')
                                        <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                        @enderror
                                        <input type="text" name="city" value="{{ old('city') }}" placeholder="City"
                                            class="w-full border @error('city') border-red-500 @else border-slate-300 @enderror rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-1">Zip Code <span
                                                class="text-red-500">*</span></label>
                                        @error('zip')
                                        <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                        @enderror
                                        <input type="number" name="zip" value="{{ old('zip') }}" placeholder="Zip Code"
                                            class="w-full border @error('zip') border-red-500 @else border-slate-300 @enderror rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-1">Country <span
                                                class="text-red-500">*</span></label>
                                        @error('country')
                                        <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                        @enderror
                                        <input type="text" name="country" value="{{ old('country') }}" placeholder="Country"
                                            class="w-full border @error('country') border-red-500 @else border-slate-300 @enderror rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-white rounded-2xl shadow-sm p-6">
                            <h2 class="text-xl font-bold text-slate-900 mb-1">Billing Infos</h2>
                            <p class="text-sm text-slate-500 mb-6">Mandatory informations</p>

                            <div class="space-y-4">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-1">TVA number</label>
                                        <input type="text" name="TVA" placeholder="TVA number"
                                            class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-1">Due Date (days)
                                            <span class="text-red-500">*</span></label>
                                        @error('due_date')
                                        <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                        @enderror
                                        <input type="text" name="due_date" value="{{ old('due_date') }}" placeholder="Due Date by Days"
                                            class="w-full border @error('due_date') border-red-500 @else border-slate-300 @enderror rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                                    </div>
                                </div>

                                <div class="flex items-center pt-2 pb-2">
                                    <input id="billingSameAsClient" type="checkbox"
                                        class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-2 focus:ring-blue-500">
                                    <label for="billingSameAsClient"
                                        class="ml-2 text-sm font-medium text-slate-700">Billing address is the same as
                                        client address</label>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label
                                            class="block text-sm font-medium text-slate-700 mb-1">Street</label>
                                        <input type="text" name="billing_street" placeholder="Billing Street"
                                            class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-1">City</label>
                                        <input type="text" name="billing_city" placeholder="Billing City"
                                            class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-1">Zip Code</label>
                                        <input type="text" name="billing_zip" placeholder="Billing Zip Code"
                                            class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-1">Country</label>
                                        <input type="text" name="billing_country" placeholder="Billing Country"
                                            class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-2xl shadow-sm p-6">
                            <h2 class="text-xl font-bold text-slate-900 mb-1">Accounting Info</h2>
                            <p class="text-sm text-slate-500 mb-6">Mandatory informations</p>
                            
                            <div class="space-y-4">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-1">Account Manager Name <span class="text-red-500">**</span></label>
                                        <input type="text" name="comp_name" placeholder="Account Manager Name"
                                            class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-1">Email <span
                                                class="text-red-500">**</span></label>
                                        <input type="text" name="comp_email" placeholder="*****@gmail.com"
                                            class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-1">Phone
                                            Number</label>
                                        <input type="number" name="comp_phone" placeholder="Phone Number"
                                            class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-1">Address</label>
                                        <input type="text" name="comp_address" placeholder="Address"
                                            class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-2xl shadow-sm p-6">
                            <h2 class="text-xl font-bold text-slate-900 mb-1">Contacts Infos</h2>
                            <p class="text-sm text-slate-500 mb-6">Mandatory informations</p>

                            <div id="contact_infos" class="space-y-6">
                                <div class="contact-entry space-y-4">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700 mb-1">Full Name
                                                <span class="text-red-500">**</span></label>
                                            <input type="text" name="contact_name[]" placeholder="Full Name"
                                                class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                                        </div>
                                        <div>
                                            <label
                                                class="block text-sm font-medium text-slate-700 mb-1">Function</label>
                                            <input type="text" name="contact_function[]"
                                                placeholder="Functionality"
                                                class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700 mb-1">Phone
                                                Number</label>
                                            <input type="number" name="contact_mobile_fix[]"
                                                placeholder="Phone Number"
                                                class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700 mb-1">Email
                                                <span class="text-red-500">**</span></label>
                                            <input type="text" name="contact_email[]"
                                                placeholder="*****@gmail.com"
                                                class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                                        </div>
                                    </div>
                                    <div>
                                        <label
                                            class="block text-sm font-medium text-slate-700 mb-1">Address</label>
                                        <input type="text" name="contact_address[]" placeholder="Address"
                                            class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                                    </div>
                                </div>
                                </div>
                        </div>
                        
                    </div>

                    <div class="flex justify-end mt-8 gap-4">
                        {{-- Cancel Button Logic --}}
                            <a href="javascript:history.back()"
                                class="px-5 py-2 bg-slate-200 hover:bg-slate-300 text-slate-800 rounded-lg font-medium">Cancel</a>
                        <button type="submit"
                            class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold shadow">
                            Create
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
                var deleteButton = $('<button type="button" class="w-full px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg font-medium mt-4 remove-contact">Delete Contact</button>');
                
                deleteButton.click(function() {
                    $(this).closest('.contact-entry').remove();
                });
                
                // Add a visual separator (for all new cards)
                newEntry.prepend('<hr class="my-4 border-t-2 border-slate-200">');
                
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
