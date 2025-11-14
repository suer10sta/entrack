<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BizManager Dashboard</title>
    @vite('resources/css/app.css')
</head>
<body class="bg-gray-50 flex h-screen overflow-hidden">
    {{-- Sidebar --}}
    <x-dashboard.sidebar />
    <div class="flex flex-col flex-1 overflow-y-auto">
        {{-- Navbar --}}
        <x-dashboard.navbar />

        {{-- Main Content --}}

        <main class="flex-1 p-8">

            <div class="max-w-7xl mx-auto">
                {{-- Header --}}
                <div class="mb-8 text-center">
                    <h1 class="text-4xl font-bold text-slate-900 mb-2">Add New Employee</h1>
                    <p class="text-slate-600">Fill the mandatory information below</p>
                </div>

                @if(session('error'))
    <div class="mb-4 rounded-lg bg-red-100 border border-red-400 p-4 text-sm text-red-700">
        <strong>Error:</strong> {{ session('error') }}
    </div>
@endif

@if(session('message'))
    <div class="mb-4 rounded-lg bg-green-100 border border-green-400 p-4 text-sm text-green-700">
        <strong>Success:</strong> {{ session('message') }}
    </div>
@endif
                {{-- Form --}}
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    {{-- Personal Infos --}}
                    <div class="bg-white rounded-2xl shadow-sm p-6 border-l-4 border-blue-500">
                        <h2 class="text-xl font-bold text-slate-900 mb-1 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-500" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5.121 17.804A10.97 10.97 0 0112 15c2.485 0 4.779.815 6.879 2.191M15 10a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Personal Infos
                        </h2>
                        <p class="text-sm text-slate-500 mb-4">Mandatory informations</p>

                        <form method="POST" action="
                            @if(Auth::user()->hasRole('Admin')) {{route('admin.employees.store')}} 
                            @elseif(Auth::user()->hasRole('Responsable de projet')) {{route('Responsable_de_projet.employees.store')}} 
                            @elseif(Auth::user()->hasRole('Employee')) {{route('employee.employees.store')}} 
                            @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire')) {{route('Responsable_gestionnaire.employees.store')}} 
                            @endif
                        " enctype="multipart/form-data" autocomplete="off" class="space-y-4">
                            @csrf

                            {{-- Name & Email --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Name <span
                                            class="text-red-500">*</span></label>
                                    <input required type="text" name="name" placeholder="Full Name"
                                        class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Login Email <span
                                            class="text-red-500">*</span></label>
                                    <input required type="email" name="email" placeholder="*****@gmail.com"
                                        class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                                </div>
                            </div>

                            {{-- Age & Company Phone --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Age</label>
                                    <input type="number" name="age" placeholder="Employee Age"
                                        class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Company Phone Number</label>
                                    <input type="number" name="Employee_number" placeholder="Company Phone Number"
                                        class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                                </div>
                            </div>

                            {{-- Personal Phone & Contact Email --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Personal Phone</label>
                                    <input type="number" name="phoneNumber" placeholder="Personal Phone Number"
                                        class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Contact Email <span
                                            class="text-red-500">*</span></label>
                                    <input required type="email" name="contact_email" placeholder="Contact Email"
                                        class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                                </div>
                            </div>

                            {{-- Password --}}
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Password <span
                                        class="text-red-500">*</span></label>
                                <input required type="password" name="password" placeholder="******"
                                    class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                            </div>
                    </div>

                    {{-- Professional Infos --}}
                    <div class="bg-white rounded-2xl shadow-sm p-6 border-l-4 border-amber-500">
                        <h2 class="text-xl font-bold text-slate-900 mb-1 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-amber-500" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                            </svg>
                            Professional Infos
                        </h2>
                        <p class="text-sm text-slate-500 mb-4">Mandatory informations</p>

                        <div class="space-y-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">CNSS</label>
                                    <input type="text" name="CNSS" placeholder="CNSS Number"
                                        class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-amber-500 focus:border-transparent" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Bank Account Number</label>
                                    <input type="text" name="bankAccountNumber" placeholder="Bank Account Number"
                                        class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-amber-500 focus:border-transparent" />
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Cost Per Hour <span class="text-red-500">*</span></label>
                                    <input required type="number" name="cost_per_hour" placeholder="Cost Per Hour"
                                        class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-amber-500 focus:border-transparent" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Salary</label>
                                    <input type="number" name="salary" placeholder="Salary"
                                        class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-amber-500 focus:border-transparent" />
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">CNSS Contribution</label>
                                    <input type="number" step="0.0001" name="Cotisation_CNSS" placeholder="CNSS Contribution"
                                        class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-amber-500 focus:border-transparent" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">AMO Contribution</label>
                                    <input type="number" step="0.0001" name="Cotisation_AMO" placeholder="AMO Contribution"
                                        class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-amber-500 focus:border-transparent" />
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Work Hours Limit <span class="text-red-500">*</span></label>
                                    <input required type="number" step="0.0001" name="workHours_limit" placeholder="Work Hours Limit"
                                        class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-amber-500 focus:border-transparent" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Release Date</label>
                                    <input type="date" name="Date_Release"
                                        class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-amber-500 focus:border-transparent" />
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Timezone</label>
                                <select name="timezone"
                                    class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                                    @foreach (timezone_identifiers_list() as $timezone)
                                        <option value="{{ $timezone }}">{{ $timezone }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Roles --}}
                    <div class="bg-white rounded-2xl shadow-sm p-6 border-l-4 border-green-500">
                        <h2 class="text-xl font-bold text-slate-900 mb-1 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-500" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 11c0 1.657-1.343 3-3 3H5v-6h4c1.657 0 3 1.343 3 3zM19 11a3 3 0 00-3-3h-1v6h1a3 3 0 003-3z" />
                            </svg>
                            Assign Roles <span class="text-red-500">*</span>
                        </h2>
                        <p class="text-sm text-slate-500 mb-4">Roles informations</p>

                        <div class="space-y-2">
                            @foreach($roles as $role)
                                @if(Auth::user()->hasRole('SuperAdmin') || (!$user->hasRole('SuperAdmin') && $role != 'SuperAdmin'))
                                    <label class="flex items-center gap-3 cursor-pointer p-2 rounded-lg hover:bg-slate-50">
                                        <input   type="checkbox" name="roles[]" value="{{ $role->name }}"
                                            class="w-4 h-4 rounded border-slate-300 text-green-600 focus:ring-2 focus:ring-green-500" />
                                        <span class="text-sm font-medium text-slate-700">{{ $role->name }}</span>
                                    </label>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Buttons --}}
                <div class="flex justify-end mt-8 gap-4">
                    <button type="button"  onclick="window.history.back()" 
                       class="px-5 py-2 bg-slate-200 hover:bg-slate-300 text-slate-800 rounded-lg font-medium">
                       Cancel
                    </button>

                    <button type="submit"
                        class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold shadow">
                        Create
                    </button>
                </div>
            </form>
        </main>
    </div>
    <script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('form');

    form.addEventListener('submit', (e) => {
        let valid = true;

        // Validate every required field
        form.querySelectorAll('input[required], select[required], textarea[required]').forEach(input => {
            if (!input.value.trim()) {
                valid = false;
                input.classList.add('border-red-500', 'focus:ring-red-500');
                
                // Add an error message if one does not already exist
                if (!input.nextElementSibling || !input.nextElementSibling.classList.contains('error-message')) {
                    const msg = document.createElement('p');
                    msg.classList.add('text-red-500', 'text-sm', 'mt-1', 'error-message');
                    msg.textContent = 'This field is required.';
                    input.insertAdjacentElement('afterend', msg);
                }
            } else {
                // Remove errors once the field is filled
                input.classList.remove('border-red-500', 'focus:ring-red-500');
                if (input.nextElementSibling && input.nextElementSibling.classList.contains('error-message')) {
                    input.nextElementSibling.remove();
                }
            }
        });

        // Validate email format
        const emailInput = form.querySelector('input[type="email"]');
        if (emailInput && emailInput.value.trim() && !emailInput.validity.valid) {
            valid = false;
            emailInput.classList.add('border-red-500', 'focus:ring-red-500');
            const msg = document.createElement('p');
            msg.classList.add('text-red-500', 'text-sm', 'mt-1', 'error-message');
            msg.textContent = 'Please enter a valid email.';
            if (!emailInput.nextElementSibling || !emailInput.nextElementSibling.classList.contains('error-message')) {
                emailInput.insertAdjacentElement('afterend', msg);
            }
        }

        // Validate phone number (digits only)
        const phoneInput = form.querySelector('input[name="phone_number"]');
        if (phoneInput && phoneInput.value.trim() && !/^[0-9]+$/.test(phoneInput.value)) {
            valid = false;
            phoneInput.classList.add('border-red-500', 'focus:ring-red-500');
            const msg = document.createElement('p');
            msg.classList.add('text-red-500', 'text-sm', 'mt-1', 'error-message');
            msg.textContent = 'The number must contain digits only.';
            if (!phoneInput.nextElementSibling || !phoneInput.nextElementSibling.classList.contains('error-message')) {
                phoneInput.insertAdjacentElement('afterend', msg);
            }
        }

        if (!valid) {
            e.preventDefault(); // Block form submission
        }
    });
});
</script>

</body>
</html>
