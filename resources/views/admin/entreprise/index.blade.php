<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <x-favicon />
    @include('partials.password-visibility')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.Company Info - DalyTrac') }}</title>
    @vite('resources/css/app.css')
    <style>
        /* Editable fields: readonly state */
        .entrepriseInfos[readonly] {
            background-color: #f8fafc;
            cursor: pointer;
        }
        .entrepriseInfos[readonly]:hover {
            border-color: #99d6de;
        }
        .entrepriseInfos:not([readonly]) {
            background-color: #ffffff;
            border-color: #2D7080 !important;
            box-shadow: 0 0 0 2px rgba(45, 112, 128, 0.25) !important;
        }
        .entrepriseInfos {
            transition: background-color .15s ease, border-color .15s ease, box-shadow .15s ease;
        }
    </style>
</head>
<body class="dt-app-shell bg-[#f8fafc] text-slate-900 antialiased flex h-screen overflow-hidden font-sans">
    {{-- Sidebar --}}
    @if(Auth::user()->hasRole('Admin'))
        <x-dashboard.sidebar />
    @else
        <x-employees.sidebar />
    @endif
    <div class="flex flex-col flex-1 overflow-y-auto">
        <x-dashboard.navbar />

        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-[#f8fafc] px-3 py-4 sm:px-5 sm:py-5 lg:px-10 lg:py-6 xl:px-16 xl:py-7">

            @php
                $countriesList = json_decode(file_get_contents(base_path('countries.json')), true) ?? [];
            @endphp

            <div class="mx-auto max-w-[1400px] xl:max-w-[1600px] 2xl:max-w-[1800px] space-y-4">

                @php
                    $backRoute = route('admin.index');
                    if (Auth::user()->hasRole('Responsable de projet'))
                        $backRoute = route('Responsable_de_projet.index');
                    elseif (Auth::user()->hasRole('Employee'))
                        $backRoute = route('employee.index');
                    elseif (Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                        $backRoute = route('Responsable_gestionnaire.index');
                @endphp

                <div class="flex items-center gap-2">
                    <div class="min-w-0">
                        <p class="text-[10px] font-semibold uppercase tracking-[.08em] text-slate-400 mb-0.5">{{ __('messages.Settings') }}</p>
                        <h1 class="text-[18px] sm:text-[20px] lg:text-[22px] font-bold tracking-tight text-[#0B2638] leading-tight">
                            {{ __('messages.Enterprise Information') }}
                        </h1>
                        <p class="text-[12px] text-slate-500 mt-0.5">{{ __('messages.Enterprise Information subtitle') }}</p>
                    </div>
                   
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

                    <div class="space-y-4">
                        <div class="dt-card overflow-hidden">
                            <div class="px-5 py-3.5 border-b border-slate-100 flex items-center gap-3">
                                <div class="flex items-center justify-center w-9 h-9 rounded-xl bg-[#0B2638]/[0.06] text-[#0B2638] ring-1 ring-[#0B2638]/10 flex-shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V7z" />
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <h2 class="text-[13px] font-semibold text-[#0B2638]">{{ __('messages.General Information') }}</h2>
                                    <p class="text-[12px] text-slate-500 mt-0.5">{{ __('messages.Mandatory informations') }}</p>
                                </div>
                            </div>
                            <div class="p-5 space-y-4">
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                    <div class="min-w-0">
                                        <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">{{ __('messages.Name') }}</label>
                                        <input class="editable entrepriseInfos block w-full rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3"
                                            type="text" name="name" value="{{$entreprise->name}}" readonly />
                                    </div>
                                    <div class="min-w-0">
                                        <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">{{ __('messages.Phone Number') }}</label>
                                        <input class="editable entrepriseInfos block w-full rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3"
                                            type="text" name="phone" value="{{$entreprise->phone}}" readonly />
                                    </div>
                                    <div class="min-w-0">
                                        <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">{{ __('messages.Contact Email') }}</label>
                                        <input class="editable entrepriseInfos block w-full rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3"
                                            type="text" name="email" value="{{$entreprise->email}}" readonly />
                                    </div>
                                </div>
                                <div class="min-w-0">
                                    <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">{{ __('messages.Address') }}</label>
                                    <input class="editable entrepriseInfos block w-full rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3"
                                        type="text" name="Address" value="{{$entreprise->Address}}" readonly />
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                    <div class="min-w-0">
                                        <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">{{ __('messages.Country') }}</label>
                                        <select class="editable entrepriseInfos block w-full rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3"
                                            name="country">
                                            @foreach($countriesList as $country)
                                                <option value="{{ $country['name'] }}"
                                                    @if($entreprise->country === $country['name']) selected @endif>
                                                    {{ __('countries.' . $country['code']) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="min-w-0">
                                        <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">{{ __('messages.City') }}</label>
                                        <input class="editable entrepriseInfos block w-full rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3"
                                            type="text" name="city" value="{{$entreprise->city}}" readonly />
                                    </div>
                                    <div class="min-w-0">
                                        <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">{{ __('messages.Zip Code') }}</label>
                                        <input class="editable entrepriseInfos block w-full rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3"
                                            type="text" name="zip_code" value="{{$entreprise->zip_code}}" readonly />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="dt-card overflow-hidden">
                            <div class="px-5 py-3.5 border-b border-slate-100 flex items-center gap-3">
                                <div class="flex items-center justify-center w-9 h-9 rounded-xl bg-[#E7B84B]/20 text-[#0B2638] ring-1 ring-[#E7B84B]/40 flex-shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z" />
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <h2 class="text-[13px] font-semibold text-[#0B2638]">{{ __('messages.Legal Information') }}</h2>
                                    <p class="text-[12px] text-slate-500 mt-0.5">{{ __('messages.Mandatory informations') }}</p>
                                </div>
                            </div>
                            <div class="p-5 space-y-4">
                                <div class="min-w-0">
                                    <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">{{ __('messages.TVA') }}</label>
                                    <input class="editable entrepriseInfos block w-full rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3"
                                        type="text" name="tva" value="{{$entreprise->tva}}" readonly />
                                </div>
                            </div>
                        </div>

                        <div class="dt-card overflow-hidden">
                            <div class="px-5 py-3.5 border-b border-slate-100 flex items-center gap-3">
                                <div class="flex items-center justify-center w-9 h-9 rounded-xl bg-[#2D7080]/10 text-[#2D7080] ring-1 ring-[#2D7080]/20 flex-shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V7z" />
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <h2 class="text-[13px] font-semibold text-[#0B2638]">{{ __('messages.Banking Information') }}</h2>
                                    <p class="text-[12px] text-slate-500 mt-0.5">{{ __('messages.Mandatory informations') }}</p>
                                </div>
                            </div>
                            <div class="p-5 space-y-4">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div class="min-w-0">
                                        <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">{{ __('messages.Bank Name') }}</label>
                                        <input class="editable entrepriseInfos block w-full rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3"
                                            type="text" name="bank_name" value="{{$entreprise->bank_name}}" readonly />
                                    </div>
                                    <div class="min-w-0">
                                        <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">{{ __('messages.IBAN') }}</label>
                                        <input class="editable entrepriseInfos block w-full rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3"
                                            type="text" name="iban" value="{{$entreprise->iban}}" readonly />
                                    </div>
                                </div>
                                <div class="min-w-0">
                                    <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">{{ __('messages.Swift/BIC') }}</label>
                                    <input class="editable entrepriseInfos block w-full rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3"
                                        type="text" name="bic" value="{{$entreprise->swift}}/{{$entreprise->bic}}" readonly />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="dt-card overflow-hidden">
                            <div class="px-5 py-3.5 border-b border-slate-100 flex items-center gap-3">
                                <div class="flex items-center justify-center w-9 h-9 rounded-xl bg-[#E7B84B]/20 text-[#0B2638] ring-1 ring-[#E7B84B]/40 flex-shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <rect x="3" y="6" width="18" height="12" rx="2" stroke-linecap="round" stroke-linejoin="round" />
                                        <circle cx="12" cy="12" r="2.5" />
                                        <path stroke-linecap="round" d="M6.5 9.5h.01M17.5 14.5h.01" />
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <h2 class="text-[13px] font-semibold text-[#0B2638]">{{ __('messages.Billing Information') }}</h2>
                                    <p class="text-[12px] text-slate-500 mt-0.5">{{ __('messages.Mandatory informations') }}</p>
                                </div>
                            </div>
                            <div class="p-5 space-y-4">
                                <div class="min-w-0">
                                    <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">{{ __('messages.Billit API key') }}</label>
                                    <p class="text-xs text-slate-500 mb-1.5">{{ __('messages.Billit API key help') }}</p>
                                    @if(filled($entreprise->getRawOriginal('billit_api_key')))
                                        <p class="text-xs text-emerald-600 mb-1.5">{{ __('messages.Billit API key saved hint') }}</p>
                                    @endif
                                    @php
                                        $billitMask = filled($entreprise->getRawOriginal('billit_api_key')) ? str_repeat('*', 12) : '';
                                        $billitReal = $entreprise->getBillitApiKeyDecrypted() ?? '';
                                    @endphp
                                    <div class="relative">
                                        <input
                                            class="editable entrepriseInfos block w-full rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3 pr-20"
                                            type="password"
                                            name="billit_api_key"
                                            value="{{ $billitMask }}"
                                            data-masked-value="{{ $billitMask }}"
                                            data-real-value="{{ $billitReal }}"
                                            data-has-existing="{{ filled($entreprise->getRawOriginal('billit_api_key')) ? '1' : '0' }}"
                                            autocomplete="new-password"
                                            placeholder=""
                                            readonly />
                                        @if(filled($entreprise->getRawOriginal('billit_api_key')))
                                            <button
                                                type="button"
                                                class="absolute inset-y-0 right-10 px-3 flex items-center text-slate-400 hover:text-slate-600"
                                                aria-label="{{ __('messages.Clear Billit API key') }}"
                                                data-clear-billit-key>
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm2.707-10.707a1 1 0 00-1.414-1.414L10 7.586 8.707 6.293a1 1 0 00-1.414 1.414L8.594 9l-1.293 1.293a1 1 0 101.414 1.414L10 10.414l1.293 1.293a1 1 0 001.414-1.414L11.414 9l1.293-1.293z" clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                        @endif
                                        <button
                                            type="button"
                                            class="absolute inset-y-0 right-0 px-3 flex items-center text-slate-400 hover:text-slate-600"
                                            aria-label="{{ __('messages.Toggle Billit API key visibility') }}"
                                            data-toggle-billit-visibility>
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                                <path d="M10 3c-4.5 0-8.3 3.1-9.6 7.4a1 1 0 000 .6C1.7 15.9 5.5 19 10 19s8.3-3.1 9.6-7.4a1 1 0 000-.6C18.3 6.1 14.5 3 10 3zm0 13a6 6 0 01-6-6 6 6 0 0112 0 6 6 0 01-6 6z"/>
                                                <path d="M10 7a3 3 0 100 6 3 3 0 000-6z"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                <div class="min-w-0">
                                    <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">{{ __('messages.Created at') }}</label>
                                    <input class="editable entrepriseInfos block w-full rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3"
                                        type="text" name="chargename"
                                        value="{{ \Carbon\Carbon::parse($entreprise->created_at)->format('d-m-Y') }}"
                                        readonly />
                                </div>
                            </div>
                        </div>

                        <div class="dt-card overflow-hidden">
                            <div class="px-5 py-3.5 border-b border-slate-100 flex items-center gap-3">
                                <div class="flex items-center justify-center w-9 h-9 rounded-xl bg-[#0B2638]/[0.06] text-[#0B2638] ring-1 ring-[#0B2638]/10 flex-shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <h2 class="text-[13px] font-semibold text-[#0B2638]">{{ __('messages.Logo') }}</h2>
                                    <p class="text-[12px] text-slate-500 mt-0.5">{{ __('messages.Mandatory informations') }}</p>
                                </div>
                            </div>
                            <div class="p-5">
                                <form action="{{ route('admin.entreprise.updateImage') }}" method="POST"
                                    enctype="multipart/form-data">
                                    @csrf
                                    <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                                        <div class="flex-shrink-0 w-28 h-28 border-2 border-dashed border-slate-200 rounded-xl flex items-center justify-center overflow-hidden bg-slate-50">
                                            @if($entreprise->image)
                                                <img id="logo-preview" src="{{ Storage::disk('public')->url($entreprise->image) }}" alt="Company Logo" class="w-full h-full object-contain p-2">
                                            @else
                                                <div id="logo-placeholder" class="text-center">
                                                    <svg class="mx-auto h-10 w-10 text-slate-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                                                    </svg>
                                                    <p class="mt-1 text-xs text-slate-400">{{ __('messages.No logo') }}</p>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="flex-grow">
                                            <div class="flex flex-col sm:flex-row sm:items-end gap-3">
                                                <div class="flex-grow">
                                                    <input type="file" name="entreprise_image" id="file-upload" class="hidden" accept="image/*">
                                                    <div class="flex space-x-3">
                                                        <label for="file-upload" class="dt-btn dt-btn-ghost cursor-pointer">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                                                            <span id="file-button-text">{{ __('messages.Choose file') }}</span>
                                                        </label>
                                                        <span id="file-name" class="text-sm text-slate-500 self-center"></span>
                                                    </div>
                                                </div>
                                                <div>
                                                    <button type="submit" class="dt-btn dt-btn-primary w-full sm:w-auto justify-center">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                                                        {{ __('messages.Update Image') }}
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="dt-card overflow-hidden">
                            <div class="px-5 py-3.5 border-b border-slate-100 flex items-center gap-3">
                                <div class="flex items-center justify-center w-9 h-9 rounded-xl bg-[#2D7080]/10 text-[#2D7080] ring-1 ring-[#2D7080]/20 flex-shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <h2 class="text-[13px] font-semibold text-[#0B2638]">{{ __('messages.Emails Backup') }}</h2>
                                    <p class="text-[12px] text-slate-500 mt-0.5">{{ __('messages.Mandatory informations') }}</p>
                                </div>
                            </div>
                            <div class="p-5 space-y-4">
                                <div class="min-w-0">
                                    <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">{{ __('messages.Emails Backup label') }}</label>
                                    <textarea
                                        class="editable entrepriseInfos block w-full rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3"
                                        name="emails_backup" rows="3" style="resize: vertical;"
                                        readonly
                                    >{{ $entreprise->emails_backup ?? __('messages.Double Click to Configure it') }}</textarea>
                                </div>
                                <div class="min-w-0">
                                    <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">{{ __('messages.WorkDays Emails label') }}</label>
                                    <textarea
                                        class="editable entrepriseInfos block w-full rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3"
                                        name="workdays_emails" rows="3" style="resize: vertical;"
                                        readonly
                                    >{{ $entreprise->workdays_emails ?? __('messages.Double Click to Configure it') }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col-reverse sm:flex-row sm:justify-between sm:items-center gap-2 pt-2">
                    <!-- <a href="{{ $backRoute }}" class="dt-btn dt-btn-ghost">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        {{ __('messages.Back') }}
                    </a> -->
                    <button type="button" id="save-changes-btn"
                        class="dt-btn dt-btn-primary w-full sm:w-auto justify-center disabled:opacity-50 disabled:cursor-not-allowed">
                        <span id="save-text">{{ __('messages.Save All Changes enterprise') }}</span>
                        <svg id="save-spinner" class="hidden animate-spin ml-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </button>
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
            document.getElementById('file-button-text').textContent = @json(__('messages.Change file'));
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
                if (!confirm(@json(__('messages.Remove the Billit API key for this enterprise?')))) {
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
                alert(@json(__('messages.No changes to save')));
                return;
            }

            var $btn = $(this);
            var originalHTML = $btn.html();

            // Disable button and show spinner
            $btn.prop('disabled', true);
            $btn.html('<svg class="animate-spin h-4 w-4 mr-2 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>@json(__('messages.Saving...'))');

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
                        $btn.html('<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>@json(__('messages.Saved!'))');
                    } else {
                        // ERROR ANIMATION
                        $btn.removeClass('bg-blue-600 hover:bg-blue-700').addClass('bg-red-600');
                        $btn.html('<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>@json(__('messages.Error!'))');

                        if (errors.length === totalFields) {
                            alert(@json(__('messages.Failed to save changes. Please try again.')));
                        } else {
                            alert(@json(__('messages.Some changes were saved, but ')) + errors.length + @json(__(' field(s) failed to save: ')) + errors.join(', '));
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
