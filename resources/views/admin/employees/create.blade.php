<!DOCTYPE html>
<html lang="en">
<head>
    <x-favicon />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.Add New Employee') }} - DalyTrac</title>
    @vite('resources/css/app.css')
</head>
<body class="dt-app-shell bg-[#f8fafc] text-slate-900 antialiased flex h-screen overflow-hidden">
    {{-- Sidebar --}}
    @if(Auth::user()->hasRole('Admin'))
        <x-dashboard.sidebar />
    @else
        <x-employees.sidebar />
    @endif
    <div class="flex flex-col flex-1 overflow-y-auto">
        {{-- Navbar --}}
        <x-dashboard.navbar />

        {{-- Main Content --}}

        <main class="flex-1 overflow-y-auto bg-[#f8fafc] p-4 sm:p-6 lg:px-20 lg:py-8">

            <div class="max-w-7xl mx-auto w-full">
                {{-- Header with back navigation (same design as Add Client page) --}}
                @php
                    $indexRoute = '#'; // Default fallback
                    if (Auth::user()->hasRole('Responsable de projet')) $indexRoute = route('Responsable_de_projet.employees.index');
                    elseif (Auth::user()->hasRole('Responsable de projet et gestionnaire')) $indexRoute = route('Responsable_gestionnaire.employees.index');
                    elseif (Auth::user()->hasRole('Employee')) $indexRoute = route('employee.employees.index');
                    elseif (Auth::user()->hasRole('Admin')) $indexRoute = route('admin.employees.index');
                @endphp
                <div class="flex items-center gap-3 mb-5">
                    <a href="{{ $indexRoute }}" title="{{ __('messages.Employees Table') }}" aria-label="{{ __('messages.Employees Table') }}"
                        class="dt-btn dt-btn-ghost !px-3 flex-shrink-0">
                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
                    </a>
                    <div class="min-w-0">
                        <p class="dt-overline mb-1">{{ __('messages.Employees Table') }}</p>
                        <h1 class="text-[22px] sm:text-[24px] font-bold tracking-tight text-[#0B2638] leading-tight truncate">
                            {{ __('messages.Add New Employee') }}
                        </h1>
                        <p class="text-[13.5px] text-slate-500 mt-1">{{ __('messages.Fill the mandatory information below') }}</p>
                    </div>
                </div>

                @if(session('error'))
    <div class="mb-6 rounded-xl bg-red-50 border border-red-200 p-4 text-[13px] font-medium text-red-700">
        <strong>{{ __('messages.Error') }}:</strong> {{ session('error') }}
    </div>
@endif

@if(session('message'))
    <div class="mb-6 rounded-xl bg-emerald-50 border border-emerald-200 p-4 text-[13px] font-medium text-emerald-700">
        <strong>{{ __('messages.Success') }}:</strong> {{ session('message') }}
    </div>
@endif
                {{-- Form --}}
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    {{-- Personal Infos --}}
                    <div class="dt-card p-5 sm:p-6">
                        <div class="flex items-center gap-3 mb-1">
                            <span class="flex items-center justify-center w-9 h-9 rounded-xl bg-[#0B2638]/[0.06] text-[#0B2638] ring-1 ring-[#0B2638]/10 flex-shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M5.121 17.804A10.97 10.97 0 0112 15c2.485 0 4.779.815 6.879 2.191M15 10a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </span>
                            <h2 class="dt-section-title">{{ __('messages.Personal Infos Section') }}</h2>
                        </div>
                        <p class="dt-section-subtitle mb-5">{{ __('messages.Mandatory informations') }}</p>

                        <form method="POST" action="
                            @if(Auth::user()->hasRole('Admin')) {{route('admin.employees.store')}} 
                            @elseif(Auth::user()->hasRole('Responsable de projet')) {{route('Responsable_de_projet.employees.store')}} 
                            @elseif(Auth::user()->hasRole('Employee')) {{route('employee.employees.store')}} 
                            @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire')) {{route('Responsable_gestionnaire.employees.store')}} 
                            @endif
                        " enctype="multipart/form-data" autocomplete="off" class="space-y-4">
                            @csrf
                            <input type="hidden" name="from" value="{{ old('from', request()->query('from')) }}" />

                            {{-- Name & Email --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('messages.Name') }} <span
                                            class="text-red-500">*</span></label>
                                    <input required type="text" name="name" placeholder="{{ __('messages.Full Name') }}"
                                        class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none transition" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('messages.Login Email') }} <span
                                            class="text-red-500">*</span></label>
                                    <input required type="email" name="email" placeholder="*****@gmail.com"
                                        class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none transition" />
                                </div>
                            </div>

                            {{-- Age & Company Phone --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('messages.Age') }}</label>
                                    <input type="number" name="age" placeholder="{{ __('messages.Employee Age') }}"
                                        class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none transition" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('messages.Company Phone Number') }}</label>
                                    <input type="number" name="Employee_number" placeholder="{{ __('messages.Company Phone Number') }}"
                                        class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none transition" />
                                </div>
                            </div>

                            {{-- Personal Phone & Contact Email --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('messages.Personal Phone') }}</label>
                                    <input type="number" name="phoneNumber" placeholder="{{ __('messages.Personal Phone') }}"
                                        class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none transition" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('messages.Contact Email') }} <span
                                            class="text-red-500">*</span></label>
                                    <input required type="email" name="contact_email" placeholder="Contact Email"
                                        class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none transition" />
                                </div>
                            </div>

                            {{-- Password --}}
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('messages.Password') }} <span
                                        class="text-red-500">*</span></label>
                                <input required type="password" name="password" placeholder="******"
                                    class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none transition" />
                            </div>
                    </div>

                    {{-- Professional Infos --}}
                    <div class="dt-card p-5 sm:p-6">
                        <div class="flex items-center gap-3 mb-1">
                            <span class="flex items-center justify-center w-9 h-9 rounded-xl bg-[#0B2638]/[0.06] text-[#0B2638] ring-1 ring-[#0B2638]/10 flex-shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                                </svg>
                            </span>
                            <h2 class="dt-section-title">{{ __('messages.Professional Infos Section') }}</h2>
                        </div>
                        <p class="dt-section-subtitle mb-5">{{ __('messages.Mandatory informations') }}</p>

                        <div class="space-y-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('messages.CNSS') }}</label>
                                    <input type="text" name="CNSS" placeholder="{{ __('messages.CNSS Number') }}"
                                        class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none transition" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('messages.Bank Account Number') }}</label>
                                    <input type="text" name="bankAccountNumber" placeholder="{{ __('messages.Bank Account Number') }}"
                                        class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none transition" />
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('messages.Cost Per Hour') }} <span class="text-red-500">*</span></label>
                                    <input required type="number" name="cost_per_hour" placeholder="{{ __('messages.Cost Per Hour') }}"
                                        class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none transition" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('messages.Salary') }}</label>
                                    <input type="number" name="salary" placeholder="{{ __('messages.Salary') }}"
                                        class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none transition" />
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('messages.CNSS Contribution') }}</label>
                                    <input type="number" step="0.0001" name="Cotisation_CNSS" placeholder="{{ __('messages.CNSS Contribution') }}"
                                        class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none transition" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('messages.AMO Contribution') }}</label>
                                    <input type="number" step="0.0001" name="Cotisation_AMO" placeholder="{{ __('messages.AMO Contribution') }}"
                                        class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none transition" />
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('messages.Work Hours Limit') }} <span class="text-red-500">*</span></label>
                                    <input required type="number" step="0.0001" name="workHours_limit" placeholder="{{ __('messages.Work Hours Limit') }}"
                                        class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none transition" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('messages.Release Date') }}</label>
                                    <input type="date" name="Date_Release"
                                        class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none transition" />
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('messages.Timezone') }}</label>
                                <select name="timezone"
                                    class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none transition">
                                    @foreach (timezone_identifiers_list() as $timezone)
                                        <option value="{{ $timezone }}">{{ $timezone }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Roles --}}
                    <div class="dt-card p-5 sm:p-6">
                        <div class="flex items-center gap-3 mb-1">
                            <span class="flex items-center justify-center w-9 h-9 rounded-xl bg-[#E7B84B]/20 text-[#0B2638] ring-1 ring-[#E7B84B]/40 flex-shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 11c0 1.657-1.343 3-3 3H5v-6h4c1.657 0 3 1.343 3 3zM19 11a3 3 0 00-3-3h-1v6h1a3 3 0 003-3z" />
                                </svg>
                            </span>
                            <h2 class="dt-section-title">{{ __('messages.Assign Roles') }} <span class="text-red-500">*</span></h2>
                        </div>
                        <p class="dt-section-subtitle mb-5">{{ __('messages.Roles informations') }}</p>

                        <div class="space-y-2">
                            @foreach($roles as $role)
                                @if(Auth::user()->hasRole('SuperAdmin') || (!$user->hasRole('SuperAdmin') && $role != 'SuperAdmin'))
                                    @php
                                        $roleLabelKey = 'messages.' . $role->name;
                                        $roleLabel = __($roleLabelKey);
                                    @endphp
                                    <label class="flex items-center gap-3 cursor-pointer p-2.5 rounded-xl border border-transparent hover:bg-slate-50 hover:border-slate-200 transition-colors">
                                        <input   type="checkbox" name="roles[]" value="{{ $role->name }}"
                                            class="w-4 h-4 rounded border-slate-300 text-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25" />
                                        <span class="text-sm font-medium text-slate-700">{{ $roleLabel !== $roleLabelKey ? $roleLabel : $role->name }}</span>
                                    </label>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Buttons --}}
                <div class="flex flex-col-reverse sm:flex-row sm:justify-end mt-8 gap-2 sm:gap-3">
                    <button type="button"  onclick="window.history.back()"
                       class="dt-btn dt-btn-ghost w-full sm:w-auto">
                       {{ __('messages.Cancel') }}
                    </button>

                    <button type="submit"
                        class="dt-btn dt-btn-teal w-full sm:w-auto">
                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                        {{ __('messages.Create') }}
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
                    msg.classList.add('text-red-500', 'text-xs', 'mt-1.5', 'error-message');
                    msg.textContent = '{{ __('messages.This field is required.') }}';
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
            msg.classList.add('text-red-500', 'text-xs', 'mt-1.5', 'error-message');
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
            msg.classList.add('text-red-500', 'text-xs', 'mt-1.5', 'error-message');
            msg.textContent = '{{ __('messages.The number must contain digits only.') }}';
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
