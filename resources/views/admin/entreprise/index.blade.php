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
        <main class="flex-1 overflow-y-auto ">

                {{-- Main content container --}}
                <div class="w-full px-4 md:px-6 py-4">
                    <div class="flex flex-wrap -mx-3 mb-5">
                        <div class="w-full">
                            <div class="w-full lg:w-10/12 xl:w-8/12 mx-auto my-4">

                                {{-- Page Header --}}
                                <h1 class="text-3xl font-bold text-gray-800">{{ __('messages.Enterprise Information') }}</h1>
                                <p class="text-base text-gray-500 mb-8">{{ __('messages.Enterprise Information subtitle') }}</p>

                                <div class="bg-white rounded-xl shadow-lg p-6 md:p-8 mb-6">
                                    {{-- Card Header --}}
                                    <div class="mb-6">
                                        <h5 class="text-xl font-bold text-gray-900">{{ __('messages.Enterprise Infos') }}</h5>
                                        <p class="text-sm text-gray-500">{{ __('messages.Mandatory informations') }}</p>
                                    </div>

                                    {{-- Form Grid (3 columns) --}}
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                        @php
                                            $inputClasses = "editable entrepriseInfos mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm bg-gray-50";
                                            $labelClasses = "block text-sm font-medium text-gray-700";
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
                                            <input
                                                class="{{ $inputClasses }}"
                                                type="text" name="country" value="{{$entreprise->country}}"
                                                readonly />
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

                                <div class="bg-white rounded-xl shadow-lg p-6 md:p-8 mb-6">
                                    {{-- Card Header --}}
                                    <div class="mb-6">
                                        <h5 class="text-xl font-bold text-gray-900">{{ __('messages.Emails Backup') }}</h5>
                                        <p class="text-sm text-gray-500">{{ __('messages.Mandatory informations') }}</p>
                                    </div>

                                    {{-- Form Fields --}}
                                    <div class="space-y-6">
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
                                <div class="flex justify-between items-center mt-8 mb-8">
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
                                    <div>
                                        <button type="button" id="save-changes-btn"
                                            class="inline-flex justify-center py-2 px-6 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed">
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
            $(this).prop('readonly', false);
            $(this).removeClass('bg-gray-50').addClass('bg-white border-blue-300');
        });

        // On blur - save individual field
        $(".entrepriseInfos").blur(function() {
            $(this).prop('readonly', true);
            $(this).removeClass('bg-white border-blue-300').addClass('bg-gray-50');
            
            var $field = $(this);
            var newContent = $field.val();
            var columnName = $field.attr('name');
            
            // Store modified field
            modifiedFields[columnName] = newContent;
        });

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
