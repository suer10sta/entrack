<!DOCTYPE html>
<html lang="en">

<head>
    <x-favicon />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.Create New Project') }}</title>
    @vite('resources/css/app.css')
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
    <style>
        /* Searchable client dropdown (Tom Select, dashboard look) */
        .ts-wrapper.single .ts-control {
            border: 1px solid #e2e8f0 !important;
            border-radius: 0.75rem !important;
            padding: 0.625rem 2.25rem 0.625rem 2.5rem !important;
            font-size: 0.875rem !important;
            background-color: #fff !important;
            min-height: 46px !important;
            color: #1e293b !important;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05) !important;
        }
        .ts-wrapper.single.focus .ts-control {
            border-color: #2D7080 !important;
            box-shadow: 0 0 0 2px rgba(45, 112, 128, 0.25) !important;
        }
        .ts-dropdown {
            border: 1px solid #e2e8f0 !important;
            border-radius: 0.75rem !important;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,.1), 0 2px 4px -2px rgba(0,0,0,.1) !important;
        }
        .ts-dropdown .option {
            padding: 0.5rem 0.75rem !important;
            font-size: 0.875rem !important;
        }
        .ts-dropdown .option.active {
            background-color: rgba(45, 112, 128, 0.10) !important;
            color: #0B2638 !important;
        }
        /* Native color swatch */
        input[type="color"].dt-swatch {
            -webkit-appearance: none;
            appearance: none;
            padding: 0;
            background: none;
            cursor: pointer;
        }
        input[type="color"].dt-swatch::-webkit-color-swatch-wrapper { padding: 4px; }
        input[type="color"].dt-swatch::-webkit-color-swatch { border: 1px solid #e2e8f0; border-radius: 8px; }
        input[type="color"].dt-swatch::-moz-color-swatch { border: 1px solid #e2e8f0; border-radius: 8px; }
    </style>
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
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-[#f8fafc] p-4 sm:p-6 lg:px-20 lg:py-8">
            <div class="max-w-5xl mx-auto w-full">

                <div class="mb-5">
                    <p class="dt-overline mb-1">{{ __('messages.projects_page_title') }}</p>
                    <h1 class="text-[22px] sm:text-[24px] font-bold tracking-tight text-[#0B2638] leading-tight">{{ __('messages.New project') }}</h1>
                    <p class="text-[13.5px] text-slate-500 mt-1">{{ __('messages.Create new project for your client') }}</p>
                </div>

                @if(session('message'))
                <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl relative flex items-center gap-2" role="alert">
                    <span class="block sm:inline text-[13px] font-medium">{{ session('message') }}</span>
                    <button type="button" class="absolute top-0 bottom-0 right-0 px-4 py-3 text-emerald-600 hover:text-emerald-800 font-bold text-xl" data-bs-dismiss="alert" aria-label="Close">&times;</button>
                </div>
                @endif

                @if(Auth::user()->hasRole('Admin'))
                <form method="POST" action="{{route('admin.projects.store', $client_id ?? '')}}" autocomplete="off" class="space-y-4 sm:space-y-5 pb-2">
                @elseif(Auth::user()->hasRole('Responsable de projet'))
                <form method="POST" action="{{route('Responsable_de_projet.projects.store', $client_id ?? '')}}" autocomplete="off" class="space-y-4 sm:space-y-5 pb-2">
                @elseif(Auth::user()->hasRole('Employee'))
                <form method="POST" action="{{route('employee.projects.store', $client_id ?? '')}}" autocomplete="off" class="space-y-4 sm:space-y-5 pb-2">
                @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                <form method="POST" action="{{route('Responsable_gestionnaire.projects.store', $client_id ?? '')}}" autocomplete="off" class="space-y-4 sm:space-y-5 pb-2">
                @endif
                    @csrf
                    <input type="hidden" name="from" value="{{ old('from', request()->query('from')) }}" />
                    @if(!empty($parent_id))
                        <input type="hidden" name="parent_id" value="{{ $parent_id }}">
                    @endif

                    {{-- Section 01 · General information --}}
                    <section class="dt-card p-5 sm:p-6">
                        <div class="flex items-center gap-3 mb-5">
                            <!-- <span class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-xl bg-[#0B2638] text-[13px] font-extrabold text-[#E7B84B]">01</span> -->
                            <h2 class="text-[15px] font-bold tracking-tight text-[#0B2638]">{{ __('messages.Project section general') }}</h2>
                            <span class="hidden sm:block h-px flex-1 bg-slate-100"></span>
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 sm:gap-5">
                            <div class="lg:col-span-7">
                                <label for="projectName" class="block text-[13px] font-medium text-slate-700 mb-1.5">{{ __('messages.Project Name') }} <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <span class="pointer-events-none absolute inset-y-0 left-3.5 flex items-center text-slate-400">
                                        <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"/></svg>
                                    </span>
                                    <input type="text" id="projectName" name='name' placeholder="{{ __('messages.Project Name') }}" class="w-full rounded-xl border-slate-200 bg-white pl-10 pr-3.5 py-3 text-sm text-slate-800 shadow-sm outline-none transition focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 placeholder:text-slate-400" />
                                </div>
                            </div>
                            <div class="lg:col-span-5">
                                <label for="clientSelect" class="block text-[13px] font-medium text-slate-700 mb-1.5">{{ __('messages.Client') }}</label>
                                <div class="relative">
                                    <span class="pointer-events-none absolute inset-y-0 left-3.5 z-10 flex items-center text-slate-400">
                                        <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><rect x="4" y="3" width="16" height="18" rx="2"/><path stroke-linecap="round" d="M9 21v-4h6v4M8 7h2M8 11h2M14 7h2M14 11h2"/></svg>
                                    </span>
                                    <select id="clientSelect" name="client_id" class="w-full">
                                        <option value="">{{ __('messages.No client') }}</option>
                                        @foreach($clients as $client)
                                        <option value="{{ $client->id }}" {{ ($client_id ?? '') == $client->id ? 'selected' : '' }}>{{ $client->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="lg:col-span-12">
                                <label for="projectDescription" class="block text-[13px] font-medium text-slate-700 mb-1.5">{{ __('messages.Informations') }}</label>
                                <textarea id="projectDescription" name="description" rows="4" class="w-full rounded-xl border-slate-200 bg-white px-3.5 py-3 text-sm text-slate-800 shadow-sm outline-none transition focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 placeholder:text-slate-400"></textarea>
                            </div>
                        </div>
                    </section>

                    {{-- Section 02 · Budget & billing --}}
                    <section class="dt-card p-5 sm:p-6">
                        <div class="flex items-center gap-3 mb-5">
                            <!-- <span class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-xl bg-[#0B2638] text-[13px] font-extrabold text-[#E7B84B]">02</span> -->
                            <h2 class="text-[15px] font-bold tracking-tight text-[#0B2638]">{{ __('messages.Project section budget') }}</h2>
                            <span class="hidden sm:block h-px flex-1 bg-slate-100"></span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
                            <div>
                                <label for="projectBudget" class="block text-[13px] font-medium text-slate-700 mb-1.5">{{ __('messages.Budget label') }}</label>
                                <div class="relative">
                                    <span class="pointer-events-none absolute inset-y-0 left-3.5 flex items-center text-slate-400 text-sm font-semibold">€</span>
                                    <input type="number" step="0.0001" id="projectBudget" name='budget' class="w-full rounded-xl border-slate-200 bg-white pl-8 pr-3.5 py-3 text-sm text-slate-800 shadow-sm outline-none transition focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25" />
                                </div>
                            </div>
                            <div>
                                <label for="costPerHour" class="block text-[13px] font-medium text-slate-700 mb-1.5">{{ __('messages.CPH') }} <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <span class="pointer-events-none absolute inset-y-0 left-3.5 flex items-center text-slate-400">
                                        <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                    </span>
                                    <input type="number" step="0.0001" id="costPerHour" name='cost_per_hour' class="w-full rounded-xl border-slate-200 bg-white pl-10 pr-3.5 py-3 text-sm text-slate-800 shadow-sm outline-none transition focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25" />
                                </div>
                            </div>
                            <div>
                                <label for="estimatedHours" class="block text-[13px] font-medium text-slate-700 mb-1.5">{{ __('messages.Est. Hours') }}</label>
                                <div class="relative">
                                    <span class="pointer-events-none absolute inset-y-0 left-3.5 flex items-center text-slate-400">
                                        <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                    </span>
                                    <input type="number" step="0.0001" id="estimatedHours" name='estimated_hours' class="w-full rounded-xl border-slate-200 bg-white pl-10 pr-3.5 py-3 text-sm text-slate-800 shadow-sm outline-none transition focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25" />
                                </div>
                            </div>
                            <div>
                                <label for="tva" class="block text-[13px] font-medium text-slate-700 mb-1.5">{{ __('messages.TVA %') }} <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <span class="pointer-events-none absolute inset-y-0 left-3.5 flex items-center text-slate-400 text-sm font-semibold">%</span>
                                    <input type="number" step="0.0001" id="tva" name='tva' class="w-full rounded-xl border-slate-200 bg-white pl-8 pr-3.5 py-3 text-sm text-slate-800 shadow-sm outline-none transition focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25" />
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5 mt-4 sm:mt-5">
                            <div>
                                <label for="projectColor" class="block text-[13px] font-medium text-slate-700 mb-1.5">{{ __('messages.Tag Color') }}</label>
                                <div class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 shadow-sm transition focus-within:border-[#2D7080] focus-within:ring-2 focus-within:ring-[#2D7080]/25">
                                    <input type="color" id="projectColor" name="color" value="#FDE68A" class="dt-swatch h-9 w-14 flex-shrink-0 rounded-lg" />
                                    <p class="text-xs text-slate-500 leading-snug">
                                        {{ __('messages.Tag Color helper') }}
                                    </p>
                                </div>
                            </div>
                            <div>
                                <span class="block text-[13px] font-medium text-slate-700 mb-1.5">{{ __('messages.Regie') }}</span>
                                <label for="regie" class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 bg-slate-50/50 px-3.5 py-3 transition-colors hover:border-slate-300 hover:bg-slate-50">
                                    <input id="regie" name="regie" type="checkbox" value="1" class="peer sr-only">
                                    <span aria-hidden="true" class="relative h-6 w-11 flex-shrink-0 rounded-full bg-slate-300 transition-colors after:absolute after:left-0.5 after:top-0.5 after:h-5 after:w-5 after:rounded-full after:bg-white after:shadow after:transition-transform peer-checked:bg-[#2D7080] peer-checked:after:translate-x-5 peer-focus-visible:outline peer-focus-visible:outline-2 peer-focus-visible:outline-[#2D7080]"></span>
                                    <span class="text-sm text-slate-700">{{ __('messages.Regie') }}</span>
                                </label>
                            </div>
                        </div>
                    </section>

                    {{-- Section 03 · Team & contacts --}}
                    <section class="dt-card p-5 sm:p-6">
                        <div class="flex items-center gap-3 mb-5">
                            <!-- <span class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-xl bg-[#0B2638] text-[13px] font-extrabold text-[#E7B84B]">03</span> -->
                            <h2 class="text-[15px] font-bold tracking-tight text-[#0B2638]">{{ __('messages.Project section team') }}</h2>
                            <span class="hidden sm:block h-px flex-1 bg-slate-100"></span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-5">
                            <div>
                                <div class="mb-2 flex items-center justify-between gap-2">
                                    <label class="block text-[13px] font-medium text-slate-700">{{ __('messages.Teams label') }}</label>
                                    <span class="dt-badge dt-badge-neutral"><span class="dt-dot"></span>{{ $teams->count() }}</span>
                                </div>
                                <div class="h-48 space-y-1 overflow-y-auto rounded-xl border border-slate-200 bg-slate-50/50 p-2.5 custom-scrollbar">
                                    @foreach($teams as $team)
                                    <div class="flex items-center rounded-xl px-3 py-2 hover:bg-white border border-transparent hover:border-slate-200 hover:shadow-sm transition-colors">
                                        <input id="team{{$team->id}}" name="teams[]" type="checkbox" value="{{$team->id}}" class="h-4 w-4 rounded border-slate-300 text-[#2D7080] focus:ring-[#2D7080]/25">
                                        <label for="team{{$team->id}}" class="ml-2.5 block min-w-0 flex-1 truncate text-sm text-slate-700">
                                            {{$team->name}}
                                        </label>
                                    </div>
                                    @endforeach
                                </div>
                            </div>

                            <div>
                                <div class="mb-2 flex items-center justify-between gap-2">
                                    <label class="block text-[13px] font-medium text-slate-700">{{ __('messages.Contacts label') }} <span class="text-red-500">*</span></label>
                                    @php
                                    $client_contacts = App\Models\client_contacts::where('client_id', $client_id ?? null)
                                        ->whereNotNull('name')
                                        ->get();
                                    @endphp
                                    <span class="dt-badge dt-badge-neutral"><span class="dt-dot"></span>{{ $client_contacts->count() }}</span>
                                </div>
                                <div id="contactsDiv" class="h-48 space-y-1 overflow-y-auto rounded-xl border border-slate-200 bg-slate-50/50 p-2.5 custom-scrollbar">
                                    @foreach($client_contacts as $contact)
                                    <div class="flex items-center rounded-xl px-3 py-2 hover:bg-white border border-transparent hover:border-slate-200 hover:shadow-sm transition-colors">
                                        <input id="contact{{ $contact->id }}" name="contacts[]" type="checkbox" value="{{ $contact->id }}" class="h-4 w-4 rounded border-slate-300 text-[#2D7080] focus:ring-[#2D7080]/25">
                                        <label for="contact{{ $contact->id }}" class="ml-2.5 block min-w-0 flex-1 truncate text-sm text-slate-700">
                                            {{ $contact->name }}
                                        </label>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </section>

                    {{-- Sticky actions --}}
                    <div class=" bottom-4 z-10 flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-2 rounded-2xl px-4 py-3 ">
                        <a href="javascript:history.back()" class="dt-btn dt-btn-ghost w-full sm:w-auto">
                            {{ __('messages.Cancel') }}
                        </a>
                        <button type="submit" name="button" class="dt-btn dt-btn-primary w-full sm:w-auto">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                            {{ __('messages.Create project') }}
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    @include('dashboard_layouts.scripts')

    <script>
        // Disable autocomplete
        window.addEventListener('load', function() {
            var inputs = document.getElementsByTagName('input');
            for (var i = 0; i < inputs.length; i++) {
                inputs[i].setAttribute('autocomplete', 'none');
            }
        });

        // Handle regie checkbox and project name
        document.getElementById('regie').addEventListener('change', function() {
            const projectNameInput = document.getElementById('projectName');
            const currentName = projectNameInput.value;

            if (this.checked) {
                // Add - REGIE if not already present
                if (!currentName.includes(' - REGIE')) {
                    projectNameInput.value = currentName + ' - REGIE';
                }
            } else {
                // Remove - REGIE if present
                projectNameInput.value = currentName.replace(' - REGIE', '');
            }
        });
    </script>
    {{-- Searchable client dropdown (Tom Select, same as filters).
         Syncs the original select, so the submitted value is unchanged. --}}
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var el = document.getElementById('clientSelect');
            if (el && window.TomSelect && !el.tomselect) {
                new TomSelect(el, {
                    create: false,
                    sortField: null,
                    maxOptions: null,
                    allowEmptyOption: true,
                    placeholder: el.options[0] ? el.options[0].text : ''
                });
            }
        });
    </script>
</body>
</html>
