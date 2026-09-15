<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.Company Info - DalyTrac') }}</title>
    @vite('resources/css/app.css')
</head>
<body class="bg-gray-50 flex h-screen overflow-hidden">
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
        <main class="flex-1 overflow-y-auto">

                {{-- Main content container --}}
                <div class="w-full px-4 md:px-6 py-4 sm:py-6">
                    <div class="flex flex-wrap -mx-3 mb-5">
                        <div class="w-full">
                            <div class="w-full lg:w-10/12 xl:w-8/12 mx-auto my-4">

                                {{-- Page Header --}}
                                <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">{{ __('messages.Enterprise Information') }}</h1>
                                <p class="text-base text-gray-500 mb-8">{{ __('messages.Enterprise Information subtitle') }}</p>

                                <div class="bg-white rounded-2xl border border-slate-200 shadow-[0_20px_50px_rgba(15,23,42,0.12),0_4px_12px_rgba(15,23,42,0.08)] px-5 sm:px-8 md:px-10 py-4 sm:py-6 md:py-8 mb-6">
                                    {{-- Card Header --}}
                                    <div class="mb-6">
                                        <h5 class="text-xl font-bold text-gray-900">{{ __('messages.Enterprise Infos') }}</h5>
                                        <p class="text-sm text-gray-500">{{ __('messages.Mandatory informations') }}</p>
                                    </div>

                                    {{-- Form Grid (3 columns) --}}
                                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                                        @php
                                            $inputClasses = "editable entrepriseInfos mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm bg-gray-50";
                                            $labelClasses = "block text-sm font-medium text-gray-700";
                                            $countriesList = json_decode(file_get_contents(base_path('countries.json')), true) ?? [];
                                        @endphp

                                        <div>
                                            <label class="{{ $labelClasses }}">{{ __('messages.Name') }}:</label>
                                            <input
                                                class="{{ $inputClasses }}"
                                                type="text" name="name" value="{{$entreprise->name}}"
                                                readonly />
                                        </div>

                                        <div>
                                            <label class="{{ $labelClasses }}">{{ __('messages.Phone Number') }}:</label>
                                            <input
                                                class="{{ $inputClasses }}"
                                                type="text" name="phone" value="{{$entreprise->phone}}"
                                                readonly />
                                        </div>

                                        <div>
                                            <label class="{{ $labelClasses }}">{{ __('messages.Contact Email') }}:</label>
                                            <input
                                                class="{{ $inputClasses }}"
                                                type="text" name="email" value="{{$entreprise->email}}"
                                                readonly />
                                        </div>

                                        <div>
                                            <label class="{{ $labelClasses }}">{{ __('messages.Address') }}:</label>
                                            <input
                                                class="{{ $inputClasses }}"
                                                type="text" name="Address" value="{{$entreprise->Address}}"
                                                readonly />
                                        </div>

                                        <div>
                                            <label class="{{ $labelClasses }}">{{ __('messages.Country') }}:</label>
                                            <select
                                                class="{{ $inputClasses }} h-9"
                                                name="country">
                                                @foreach($countriesList as $country)
                                                    <option value="{{ $country['name'] }}"
                                                        @if($entreprise->country === $country['name']) selected @endif>
                                                        {{ $country['name'] }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div>
                                            <label class="{{ $labelClasses }}">{{ __('messages.City') }}:</label>
                                            <input
                                                class="{{ $inputClasses }}"
                                                type="text" name="city" value="{{$entreprise->city}}"
                                                readonly />
                                        </div>

                                        <div>
                                            <label class="{{ $labelClasses }}">{{ __('messages.Zip Code') }}:</label>
                                            <input
                                                class="{{ $inputClasses }}"
                                                type="text" name="zip_code" value="{{$entreprise->zip_code}}"
                                                readonly />
                                        </div>

                                        <div>
                                            <label class="{{ $labelClasses }}">TVA:</label>
                                            <input
                                                class="{{ $inputClasses }}"
                                                type="text" name="tva" value="{{$entreprise->tva}}"
                                                readonly />
                                        </div>

                                        <div>
                                            <label class="{{ $labelClasses }}">{{ __('messages.Bank Name') }}:</label>
                                            <input
                                                class="{{ $inputClasses }}"
                                                type="text" name="bank_name"
                                                value="{{$entreprise->bank_name}}" readonly />
                                        </div>

                                        <div>
                                            <label class="{{ $labelClasses }}">IBAN:</label>
                                            <input
                                                class="{{ $inputClasses }}"
                                                type="text" name="iban" value="{{$entreprise->iban}}"
                                                readonly />
                                        </div>

                                        <div>
                                            <label class="{{ $labelClasses }}">Swift/BIC:</label>
                                            <input
                                                class="{{ $inputClasses }}"
                                                type="text" name="bic"
                                                value="{{$entreprise->swift}}/{{$entreprise->bic}}"
                                                readonly />
                                        </div>

                                        <div class="sm:col-span-2 lg:col-span-3">
                                            <label class="{{ $labelClasses }}">{{ __('messages.Billit API key') }}</label>
                                            <p class="text-xs text-gray-500 mb-1">{{ __('messages.Billit API key help') }}</p>
                                            @if(filled($entreprise->billit_api_key))
                                                <p class="text-xs text-green-700 mb-1">{{ __('messages.Billit API key saved hint') }}</p>
                                            @endif
                                            @php
                                                $billitMask = filled($entreprise->billit_api_key) ? str_repeat('*', 12) : '';
                                            @endphp
                                            <div class="relative">
                                                <input
                                                    class="{{ $inputClasses }} pr-20"
                                                    type="password"
                                                    name="billit_api_key"
                                                    value="{{ $billitMask }}"
                                                    data-masked-value="{{ $billitMask }}"
                                                    data-real-value="{{ (string) ($entreprise->billit_api_key ?? '') }}"
                                                    data-has-existing="{{ filled($entreprise->billit_api_key) ? '1' : '0' }}"
                                                    autocomplete="new-password"
                                                    placeholder=""
                                                    readonly />
                                                @if(filled($entreprise->billit_api_key))
                                                    <button
                                                        type="button"
                                                        class="absolute inset-y-0 right-10 px-3 flex items-center text-gray-500 hover:text-gray-700"
                                                        aria-label="Clear Billit API key"
                                                        data-clear-billit-key>
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm2.707-10.707a1 1 0 00-1.414-1.414L10 7.586 8.707 6.293a1 1 0 00-1.414 1.414L8.586 9l-1.293 1.293a1 1 0 101.414 1.414L10 10.414l1.293 1.293a1 1 0 001.414-1.414L11.414 9l1.293-1.293z" clip-rule="evenodd" />
                                                        </svg>
                                                    </button>
                                                @endif
                                                <button
                                                    type="button"
                                                    class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-500 hover:text-gray-700"
                                                    aria-label="Toggle Billit API key visibility"
                                                    data-toggle-billit-visibility>
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                                        <path d="M10 3c-4.5 0-8.3 3.1-9.6 7.4a1 1 0 000 .6C1.7 15.9 5.5 19 10 19s8.3-3.1 9.6-7.4a1 1 0 000-.6C18.3 6.1 14.5 3 10 3zm0 13a6 6 0 01-6-6 6 6 0 0112 0 6 6 0 01-6 6z"/>
                                                        <path d="M10 7a3 3 0 100 6 3 3 0 000-6z"/>
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>

                                        <div>
                                            <label class="{{ $labelClasses }}">{{ __('messages.Created at') }}:</label>
                                            <input
                                                class="{{ $inputClasses }}"
                                                type="text" name="chargename"
                                                value="{{ \Carbon\Carbon::parse($entreprise->created_at)->format('d-m-Y') }}"
                                                readonly />
                                        </div>
                                    </div>

                                    {{-- Logo Section --}}
                                    <form action="{{ route('admin.entreprise.updateImage') }}" method="POST"
                                        enctype="multipart/form-data" class="mt-8">
                                        @csrf
                                        <label class="{{ $labelClasses }}">{{ __('messages.Logo') }}</label>
                                        <div class="mt-2 flex flex-col sm:flex-row sm:items-center sm:space-x-6">
                                            {{-- Logo Preview --}}
                                            <div class="flex-shrink-0 w-32 h-32 border-2 border-gray-300 border-dashed rounded-lg flex items-center justify-center mb-4 sm:mb-0 overflow-hidden bg-white">
                                                @if($entreprise->image)
                                                    <img id="logo-preview" src="{{ Storage::disk('public')->url($entreprise->image) }}" alt="Company Logo" class="w-full h-full object-contain p-2">
                                                @else
                                                    <div id="logo-placeholder" class="text-center">
                                                        <svg class="mx-auto h-12 w-12 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                           <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                                                        </svg>
                                                        <p class="mt-1 text-xs text-gray-500">{{ __('messages.No logo') }}</p>
                                                    </div>
                                                @endif
                                            </div>
                                            
                                            <div class="flex-grow">
                                                <div class="flex flex-col sm:flex-row sm:items-end space-y-3 sm:space-y-0 sm:space-x-4">
                                                    <div class="flex-grow">
                                                        {{-- Hidden file input --}}
                                                        <input type="file" name="entreprise_image" id="file-upload" class="hidden" accept="image/*">
                                                        <div class="flex space-x-3">
                                                            {{-- Button for choosing file --}}
                                                            <label for="file-upload" class="cursor-pointer bg-white rounded-md border border-gray-300 py-2 px-3 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">
                                                                <span id="file-button-text">{{ __('messages.Choose file') }}</span>
                                                            </label>
                                                            <span id="file-name" class="text-sm text-gray-500 self-center"></span>
                                                        </div>
                                                    </div>
                                                    {{-- Update Image Button --}}
                                                    <div>
                                                        <button type="submit"
                                                            class="inline-flex justify-center w-full sm:w-auto py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                                            {{ __('messages.Update Image') }}
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>

                                <div class="bg-white rounded-2xl border border-slate-200 shadow-[0_20px_50px_rgba(15,23,42,0.12),0_4px_12px_rgba(15,23,42,0.08)] px-5 sm:px-8 md:px-10 py-4 sm:py-6 md:py-8 mb-6">
                                    {{-- Card Header --}}
                                    <div class="mb-6">
                                        <h5 class="text-xl font-bold text-gray-900">{{ __('messages.Emails Backup') }}</h5>
                                        <p class="text-sm text-gray-500">{{ __('messages.Mandatory informations') }}</p>
                                    </div>

                                    {{-- Form Fields --}}
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                        @php
                                            $textareaClasses = "editable entrepriseInfos mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm bg-gray-50";
                                        @endphp
                                        <div>
                                            <label class="{{ $labelClasses }}">{{ __('messages.Emails Backup label') }}</label>
                                            <textarea
                                                class="{{ $textareaClasses }}"
                                                name="emails_backup" rows="3" style="resize: vertical;"
                                                readonly
                                            >{{ $entreprise->emails_backup ?? __('messages.Double Click to Configure it') }}</textarea>
                                        </div>

                                        <div>
                                            <label class="{{ $labelClasses }}">{{ __('messages.WorkDays Emails label') }}</label>
                                            <textarea
                                                class="{{ $textareaClasses }}"
                                                name="workdays_emails" rows="3" style="resize: vertical;"
                                                readonly
                                            >{{ $entreprise->workdays_emails ?? __('messages.Double Click to Configure it') }}</textarea>
                                        </div>
                                    </div>
                                </div>

                                

                                {{-- Footer Buttons --}}
                                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mt-8 mb-8">
                                    {{-- Back Button --}}
                                    <div>
                                        @php
                                            $backLinkClass = "inline-flex items-center text-sm font-medium text-gray-600 hover:text-gray-900";
                                            $backArrow = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 mr-1"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" /></svg>';
                                        @endphp

                                        @if(Auth::user()->hasRole('Admin'))
                                            <a class='{{ $backLinkClass }}' href="{{route('admin.index')}}">
                                                {!! $backArrow !!} {{ __('messages.Back') }}
                                            </a>
                                        @endif
                                        @if(Auth::user()->hasRole('Responsable de projet'))
                                            <a class='{{ $backLinkClass }}' href="{{route('Responsable_de_projet.index')}}">
                                                {!! $backArrow !!} {{ __('messages.Back') }}
                                            </a>
                                        @endif
                                        @if(Auth::user()->hasRole('Employee'))
                                            <a class='{{ $backLinkClass }}' href="{{route('employee.index')}}">
                                                {!! $backArrow !!} {{ __('messages.Back') }}
                                            </a>
                                        @endif
                                        @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                            <a class='{{ $backLinkClass }}' href="{{route('Responsable_gestionnaire.index')}}">
                                                {!! $backArrow !!} {{ __('messages.Back') }}
                                            </a>
                                        @endif
                                    </div>

                                    {{-- Save Changes Button --}}
                                    <div class="w-full sm:w-auto">
                                        <button type="button" id="save-changes-btn"
                                            class="inline-flex justify-center w-full sm:w-auto py-2 px-6 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed">
                                            <span id="save-text">{{ __('messages.Save All Changes enterprise') }}</span>
                                            <svg id="save-spinner" class="hidden animate-spin ml-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </main>
    </div>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script>
    // File upload preview
    document.getElementById('file-upload').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            document.getElementById('file-button-text').textContent = 'Change file';
            document.getElementById('file-name').textContent = file.name;
            
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('logo-preview');
                const placeholder = document.getElementById('logo-placeholder');
                
                if (preview) {
                    preview.src = e.target.result;
                } else {
                    const container = placeholder.parentElement;
                    placeholder.remove();
                    const img = document.createElement('img');
                    img.id = 'logo-preview';
                    img.src = e.target.result;
                    img.alt = 'Company Logo';
                    img.className = 'w-full h-full object-contain p-2';
                    container.appendChild(img);
                }
            }
            reader.readAsDataURL(file);
        }
    });

    // Track modified fields
    let modifiedFields = {};

    $(document).ready(function() {
        // Double click to edit - Enterprise Info
        $(".entrepriseInfos").dblclick(function() {
            var $field = $(this);

            // Keep <select> always editable; no need to toggle disabled/readonly
            if ($field.is('select')) {
                return;
            }

            // If this is the Billit API key and it currently displays the mask,
            // clear it so the user can type a replacement key.
            if ($field.attr('name') === 'billit_api_key') {
                const masked = $field.attr('data-masked-value') || '';
                if ($field.val() === masked) {
                    $field.val('');
                }
            }

            $field.prop('readonly', false);
            $field.removeClass('bg-gray-50').addClass('bg-white border-blue-300');
        });

        // On blur / change - save individual field
        $(".entrepriseInfos").on('blur change', function() {
            var $field = $(this);

            if (!$field.is('select')) {
                $field.prop('readonly', true);
                $field.removeClass('bg-white border-blue-300').addClass('bg-gray-50');
            }
            
            var newContent = $field.val();
            var columnName = $field.attr('name');

            // Never queue the masked stars value
            if (columnName === 'billit_api_key') {
                const masked = $field.attr('data-masked-value') || '';
                if (masked !== '' && newContent === masked) {
                    return;
                }
                // If the user only revealed the existing key (no changes), don't queue it.
                const hasExisting = ($field.attr('data-has-existing') || '0') === '1';
                const real = $field.attr('data-real-value') || '';
                if (hasExisting && real !== '' && newContent === real) {
                    return;
                }
                // Allow empty only when explicitly cleared
                const clearing = ($field.attr('data-clearing') || '0') === '1';
                if ((newContent === '' || newContent === undefined) && !clearing) {
                    return;
                }
            }

            // Store modified field
            modifiedFields[columnName] = newContent;
        });

        // Capture live typing for sensitive fields like Billit API key, so clicking
        // "Save" without blurring still persists the value.
        $(".entrepriseInfos").on('input', function() {
            var $field = $(this);
            var columnName = $field.attr('name');
            if (columnName !== 'billit_api_key') {
                return;
            }

            var newContent = $field.val();
            const masked = $field.attr('data-masked-value') || '';
            if (masked !== '' && newContent === masked) {
                return;
            }
            const hasExisting = ($field.attr('data-has-existing') || '0') === '1';
            const real = $field.attr('data-real-value') || '';
            if (hasExisting && real !== '' && newContent === real) {
                return;
            }
            const clearing = ($field.attr('data-clearing') || '0') === '1';
            if ((newContent === '' || newContent === undefined) && !clearing) {
                return;
            }
            modifiedFields[columnName] = newContent;
        });

        // Eye toggle for Billit API key visibility
        const billitInput = document.querySelector('input[name="billit_api_key"]');
        const billitToggle = document.querySelector('[data-toggle-billit-visibility]');
        if (billitInput && billitToggle) {
            billitToggle.addEventListener('click', function() {
                const masked = billitInput.getAttribute('data-masked-value') || '';
                const real = billitInput.getAttribute('data-real-value') || '';
                const hasExisting = (billitInput.getAttribute('data-has-existing') || '0') === '1';

                const isPassword = billitInput.getAttribute('type') === 'password';
                const willShow = isPassword;

                billitInput.setAttribute('type', willShow ? 'text' : 'password');

                // Only swap values when the field is not being edited.
                if (billitInput.hasAttribute('readonly') && hasExisting) {
                    billitInput.value = willShow ? real : masked;
                }
            });
        }

        // Clear Billit key (explicit removal)
        const clearBillitBtn = document.querySelector('[data-clear-billit-key]');
        if (billitInput && clearBillitBtn) {
            clearBillitBtn.addEventListener('click', function() {
                if (!confirm('Remove the Billit API key for this enterprise?')) {
                    return;
                }
                billitInput.removeAttribute('readonly');
                billitInput.value = '';
                billitInput.setAttribute('data-clearing', '1');
                modifiedFields['billit_api_key'] = '';
            });
        }

        // Double click to edit - SMTP Info
        $(".editableSmtp").dblclick(function() {
            $(this).prop('readonly', false);
            $(this).removeClass('bg-gray-50').addClass('bg-white border-blue-300');
        });

        // On blur - save SMTP field
        $(".editableSmtp").blur(function() {
            $(this).prop('readonly', true);
            $(this).removeClass('bg-white border-blue-300').addClass('bg-gray-50');
            
            var $field = $(this);
            var newContent = $field.val();
            var columnName = $field.attr('name');
            
            // Store modified SMTP field
            modifiedFields['smtp_' + columnName] = newContent;
        });

        // Save Changes button click handler
        $('#save-changes-btn').click(function() {
            // Fallback: ensure Billit API key is captured even if the field never blurred.
            const billitInput = document.querySelector('input[name="billit_api_key"]');
            if (billitInput) {
                const masked = billitInput.getAttribute('data-masked-value') || '';
                const hasExisting = (billitInput.getAttribute('data-has-existing') || '0') === '1';
                const real = billitInput.getAttribute('data-real-value') || '';
                const isJustViewingExisting = hasExisting && real !== '' && billitInput.value === real;
                const clearing = (billitInput.getAttribute('data-clearing') || '0') === '1';

                if (clearing) {
                    modifiedFields['billit_api_key'] = '';
                } else if (billitInput.value && !isJustViewingExisting && (masked === '' || billitInput.value !== masked)) {
                    modifiedFields['billit_api_key'] = billitInput.value;
                }
            }

            if (Object.keys(modifiedFields).length === 0) {
                alert('No changes to save');
                return;
            }

            var $btn = $(this);
            var originalHTML = $btn.html();
            
            // Disable button and show spinner
            $btn.prop('disabled', true);
            $btn.html('<svg class="animate-spin h-4 w-4 mr-2 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Saving...');

            var EntrepriseId = "{{ Auth::user()->entreprise_id }}";
            var savedCount = 0;
            var totalFields = Object.keys(modifiedFields).length;
            var errors = [];

            // Save each modified field
            Object.keys(modifiedFields).forEach(function(columnName) {
                var content = modifiedFields[columnName];
                var isSmtp = columnName.startsWith('smtp_');
                var actualColumnName = isSmtp ? columnName.replace('smtp_', '') : columnName;
                
                let updateUrl = '';
                @if(Auth::user()->hasRole('Admin'))
                    updateUrl = isSmtp ? '/admin/Enterprise_SMTPinfos/updateSMTPInfos' : '/admin/Enterprise_infos/updateInfos';
                @elseif(Auth::user()->hasRole('Responsable de projet'))
                    updateUrl = isSmtp ? '/Responsable_de_projet/Enterprise_SMTPinfos/updateSMTPInfos' : '/Responsable_de_projet/Enterprise_infos/updateInfos';
                @elseif(Auth::user()->hasRole('Employee'))
                    updateUrl = isSmtp ? '/employee/Enterprise_SMTPinfos/updateSMTPInfos' : '/employee/Enterprise_infos/updateInfos';
                @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                    updateUrl = isSmtp ? '/Responsable_gestionnaire/Enterprise_SMTPinfos/updateSMTPInfos' : '/Responsable_gestionnaire/Enterprise_infos/updateInfos';
                @endif

                $.ajax({
                    url: updateUrl,
                    type: 'post',
                    data: {
                        content: content,
                        id: EntrepriseId,
                        column: actualColumnName,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        savedCount++;
                        checkComplete();
                    },
                    error: function(response) {
                        errors.push(actualColumnName);
                        savedCount++;
                        checkComplete();
                    }
                });
            });

            function checkComplete() {
                if (savedCount === totalFields) {
                    $btn.prop('disabled', false);

                    if (errors.length === 0) {
                        // SUCCESS ANIMATION
                        $btn.removeClass('bg-blue-600 hover:bg-blue-700').addClass('bg-green-600');
                        $btn.html('<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>Saved!');
                    } else {
                        // ERROR ANIMATION
                        $btn.removeClass('bg-blue-600 hover:bg-blue-700').addClass('bg-red-600');
                        $btn.html('<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>Error!');
                        
                        if (errors.length === totalFields) {
                            alert('Failed to save changes. Please try again.');
                        } else {
                            alert('Some changes were saved, but ' + errors.length + ' field(s) failed to save: ' + errors.join(', '));
                        }
                    }

                    // RESET BUTTON after 2 seconds
                    setTimeout(function() {
                        $btn.removeClass('bg-green-600 bg-red-600').addClass('bg-blue-600 hover:bg-blue-700');
                        $btn.html(originalHTML);
                    }, 2000);

                    // Clear modified fields
                    modifiedFields = {};
                }
            }
        });
    });
</script>
</body>
</html>
