<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.Edit Project') }}</title>
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
            <div class="max-w-6xl mx-auto w-full">

                {{-- Header with back navigation --}}
                @php
                    $indexRoute = '#'; // Default fallback
                    if (Auth::user()->hasRole('Responsable de projet')) $indexRoute = route('Responsable_de_projet.projects.index');
                    elseif (Auth::user()->hasRole('Responsable de projet et gestionnaire')) $indexRoute = route('Responsable_gestionnaire.projects.index');
                    elseif (Auth::user()->hasRole('Employee')) $indexRoute = route('employee.projects.index');
                    elseif (Auth::user()->hasRole('Admin')) $indexRoute = route('admin.projects.index');
                @endphp
                <div class="flex items-center gap-3 mb-5">
                    <a href="{{ $indexRoute }}" title="{{ __('messages.Projects Table') }}" aria-label="{{ __('messages.Projects Table') }}"
                        class="dt-btn dt-btn-ghost !px-3 flex-shrink-0">
                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
                    </a>
                    <div class="min-w-0 flex-1">
                        <p class="dt-overline mb-1">{{ __('messages.Projects Table') }}</p>
                        <h1 class="text-[22px] sm:text-[24px] font-bold tracking-tight text-[#0B2638] leading-tight truncate">
                            {{ __('messages.Edit Project') }}
                        </h1>
                        <p class="text-[13.5px] text-slate-500 mt-1">{{ __('messages.Update project details') }}</p>
                    </div>
                    <span class="dt-badge dt-badge-navy hidden md:inline-flex flex-shrink-0 max-w-[260px]">
                        <span class="dt-dot flex-shrink-0"></span><span class="truncate">{{ $project->name }}</span>
                    </span>
                </div>

                @if(session('message'))
                <div class="mb-5 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl relative flex items-center gap-2" role="alert">
                    <span class="text-[13px] font-medium">{{ session('message') }}</span>
                    <button type="button" class="absolute top-0 bottom-0 right-0 px-4 py-3 text-emerald-600 hover:text-emerald-800 font-bold" data-bs-dismiss="alert" aria-label="Close">&times;</button>
                </div>
                @endif

                @if(Auth::user()->hasRole('Admin'))
                    <form method="POST" action="{{ route('admin.projects.updateInfos', ['id' => $project->id, 'client_id' => $project->client_id ?? '']) }}" autocomplete="off">
                @elseif(Auth::user()->hasRole('Responsable de projet'))
                    <form method="POST" action="{{ route('Responsable_de_projet.projects.updateInfos', ['id' => $project->id, 'client_id' => $project->client_id ?? '']) }}" autocomplete="off">
                @elseif(Auth::user()->hasRole('Employee'))
                    <form method="POST" action="{{ route('employee.projects.updateInfos', ['id' => $project->id, 'client_id' => $project->client_id ?? '']) }}" autocomplete="off">
                @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                    <form method="POST" action="{{ route('Responsable_gestionnaire.projects.updateInfos', ['id' => $project->id, 'client_id' => $project->client_id ?? '']) }}" autocomplete="off">
                @endif
                    @csrf

                    <div class="grid grid-cols-1 xl:grid-cols-5 gap-4 sm:gap-5 items-start">
                        {{-- Left column: project facts + display --}}
                        <div class="xl:col-span-3 space-y-4 sm:space-y-5 min-w-0">
                            {{-- Project details --}}
                            <section class="dt-card p-5 sm:p-6">
                                <div class="flex items-center gap-3 mb-5">
                                    <span class="flex items-center justify-center w-9 h-9 rounded-xl bg-[#0B2638]/[0.06] text-[#0B2638] ring-1 ring-[#0B2638]/10 flex-shrink-0">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V7z" /></svg>
                                    </span>
                                    <div class="min-w-0">
                                        <h2 class="dt-section-title">{{ __('messages.Project Name') }}</h2>
                                        <p class="dt-section-subtitle mt-0.5">{{ __('messages.Update project details') }}</p>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label for="projectName" class="block text-[13px] font-semibold text-slate-700 mb-1.5">{{ __('messages.Project Name') }} <span class="text-red-500">*</span></label>
                                    <input type="text" id="projectName" name='name' value="{{ $project->name }}" class="w-full rounded-xl border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-800 shadow-sm outline-none transition focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25" />
                                </div>

                                <div class="mb-4">
                                    <label for="clientSelect" class="block text-[13px] font-semibold text-slate-700 mb-1.5">{{ __('messages.Client') }}</label>
                                    <select id="clientSelect" name="client_id" class="w-full rounded-xl border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-800 shadow-sm outline-none transition focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25">
                                        <option value="">{{ __('messages.No client') }}</option>
                                        @foreach($clients as $client)
                                        <option value="{{ $client->id }}" {{ ($project->client_id ?? '') == $client->id ? 'selected' : '' }}>{{ $client->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label for="projectBudget" class="block text-[13px] font-semibold text-slate-700 mb-1.5">{{ __('messages.Budget label') }}</label>
                                        <div class="relative">
                                            <input type="number" step="0.0001" id="projectBudget" name='budget' value="{{ $project->budget }}" class="w-full rounded-xl border-slate-200 bg-white pl-3.5 pr-9 py-2.5 text-sm text-slate-800 shadow-sm outline-none transition focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25" />
                                            <span class="pointer-events-none absolute inset-y-0 right-3.5 flex items-center text-[13px] font-semibold text-slate-400">€</span>
                                        </div>
                                    </div>
                                    <div>
                                        <label for="costPerHour" class="block text-[13px] font-semibold text-slate-700 mb-1.5">{{ __('messages.CPH') }} <span class="text-red-500">*</span></label>
                                        <div class="relative">
                                            <input type="number" step="0.0001" id="costPerHour" name='cost_per_hour' value="{{ $project->cost_per_hour }}" class="w-full rounded-xl border-slate-200 bg-white pl-3.5 pr-11 py-2.5 text-sm text-slate-800 shadow-sm outline-none transition focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25" />
                                            <span class="pointer-events-none absolute inset-y-0 right-3.5 flex items-center text-[13px] font-semibold text-slate-400">€/h</span>
                                        </div>
                                    </div>
                                    <div>
                                        <label for="estimatedHours" class="block text-[13px] font-semibold text-slate-700 mb-1.5">{{ __('messages.Est. Hours') }}</label>
                                        <div class="relative">
                                            <input type="number" step="0.0001" id="estimatedHours" name='estimated_hours' value="{{ $project->estimated_hours }}" class="w-full rounded-xl border-slate-200 bg-white pl-3.5 pr-9 py-2.5 text-sm text-slate-800 shadow-sm outline-none transition focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25" />
                                            <span class="pointer-events-none absolute inset-y-0 right-3.5 flex items-center text-[13px] font-semibold text-slate-400">h</span>
                                        </div>
                                    </div>
                                    <div>
                                        <label for="tva" class="block text-[13px] font-semibold text-slate-700 mb-1.5">{{ __('messages.TVA %') }} <span class="text-red-500">*</span></label>
                                        <div class="relative">
                                            <input type="number" step="0.0001" id="tva" name='tva' value="{{ $project->tva }}" class="w-full rounded-xl border-slate-200 bg-white pl-3.5 pr-9 py-2.5 text-sm text-slate-800 shadow-sm outline-none transition focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25" />
                                            <span class="pointer-events-none absolute inset-y-0 right-3.5 flex items-center text-[13px] font-semibold text-slate-400">%</span>
                                        </div>
                                    </div>
                                </div>
                            </section>

                            {{-- Display & content --}}
                            <section class="dt-card p-5 sm:p-6">
                                <div class="flex items-center gap-3 mb-5">
                                    <span class="flex items-center justify-center w-9 h-9 rounded-xl bg-[#E7B84B]/20 text-[#0B2638] ring-1 ring-[#E7B84B]/40 flex-shrink-0">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21a4 4 0 01-.88-7.903A5 5 0 1115.9 6.06A5 5 0 0119 16.9V18a1 1 0 01-1 1H8a3 3 0 01-1-5.83V21zM7 21h10" /></svg>
                                    </span>
                                    <div class="min-w-0">
                                        <h2 class="dt-section-title">{{ __('messages.Tag Color') }}</h2>
                                        <p class="dt-section-subtitle mt-0.5">{{ __('messages.Tag Color helper') }}</p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-stretch">
                                    <div class="rounded-xl border border-slate-200 bg-slate-50/50 p-3 flex items-center gap-3">
                                        <input type="color" id="projectColor" name="color" value="{{ $project->color ?? '#FDE68A' }}" class="h-11 w-16 flex-shrink-0 cursor-pointer rounded-lg border border-slate-200 bg-white p-1 shadow-sm" />
                                        <p class="text-[12.5px] text-slate-500 leading-snug">{{ __('messages.Tag Color helper') }}</p>
                                    </div>
                                    <label for="regie" class="flex cursor-pointer items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 shadow-sm transition hover:border-slate-300">
                                        <span class="text-sm font-semibold text-slate-700">{{ __('messages.Regie') }}</span>
                                        <input id="regie" name="regie" type="checkbox" value="1" {{ $project->regie ? 'checked' : '' }} class="peer sr-only">
                                        <span aria-hidden="true" class="relative h-6 w-11 flex-shrink-0 rounded-full bg-slate-200 transition-colors peer-checked:bg-[#2D7080] after:absolute after:top-0.5 after:left-0.5 after:h-5 after:w-5 after:rounded-full after:bg-white after:shadow after:transition-transform peer-checked:after:translate-x-5"></span>
                                    </label>
                                </div>

                                <div class="mt-4">
                                    <label for="projectDescription" class="block text-[13px] font-semibold text-slate-700 mb-1.5">{{ __('messages.Informations') }}</label>
                                    <textarea id="projectDescription" name="description" rows="4" class="w-full rounded-xl border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-800 shadow-sm outline-none transition focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 resize-y">{{ $project->description }}</textarea>
                                </div>
                            </section>
                        </div>

                        {{-- Right column: teams & contacts --}}
                        <div class="xl:col-span-2 space-y-4 sm:space-y-5 min-w-0">
                            <section class="dt-card p-5 sm:p-6">
                                <div class="flex items-center justify-between gap-3 mb-4">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <span class="flex items-center justify-center w-9 h-9 rounded-xl bg-[#0B2638]/[0.06] text-[#0B2638] ring-1 ring-[#0B2638]/10 flex-shrink-0">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                        </span>
                                        <h2 class="dt-section-title truncate">{{ __('messages.Teams label') }}</h2>
                                    </div>
                                    <span class="dt-badge dt-badge-navy flex-shrink-0"><span class="dt-dot"></span><span data-selected-count="teamsPanel">0</span></span>
                                </div>
                                <div id="teamsPanel" class="max-h-60 space-y-1 overflow-y-auto rounded-xl border border-slate-200 bg-slate-50/50 p-3 custom-scrollbar">
                                    @foreach($teams as $team)
                                    <div class="flex items-center rounded-xl px-3 py-2 hover:bg-white border border-transparent hover:border-slate-200 hover:shadow-sm transition-colors">
                                        @php
                                            $project_teams = App\Models\project_teams::where('project_id', $project->id)
                                                ->where('team_id', $team->id)
                                                ->first();
                                        @endphp
                                        <input id="team{{$team->id}}" name="teams[]" type="checkbox" value="{{$team->id}}"
                                               {{ $project_teams ? 'checked' : '' }}
                                               class="h-4 w-4 flex-shrink-0 text-[#2D7080] border-gray-300 rounded focus:ring-[#2D7080]/25">
                                        <label for="team{{$team->id}}" class="ml-2 block text-sm font-medium text-slate-700 truncate">
                                            {{$team->name}}
                                        </label>
                                    </div>
                                    @endforeach
                                </div>
                            </section>

                            <section class="dt-card p-5 sm:p-6">
                                <div class="flex items-center justify-between gap-3 mb-4">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <span class="flex items-center justify-center w-9 h-9 rounded-xl bg-[#0B2638]/[0.06] text-[#0B2638] ring-1 ring-[#0B2638]/10 flex-shrink-0">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                        </span>
                                        <h2 class="dt-section-title truncate">{{ __('messages.Contacts label') }} <span class="text-red-500">*</span></h2>
                                    </div>
                                    <span class="dt-badge dt-badge-navy flex-shrink-0"><span class="dt-dot"></span><span data-selected-count="contactsDiv">0</span></span>
                                </div>
                                @php
                                $client_contacts = App\Models\client_contacts::where('client_id', $project->client_id ?? null)
                                    ->whereNotNull('name')
                                    ->get();
                                @endphp
                                <div id="contactsDiv" class="max-h-60 space-y-1 overflow-y-auto rounded-xl border border-slate-200 bg-slate-50/50 p-3 custom-scrollbar">
                                    @foreach($client_contacts as $contact)
                                    <div class="flex items-center rounded-xl px-3 py-2 hover:bg-white border border-transparent hover:border-slate-200 hover:shadow-sm transition-colors">
                                        @php
                                            $project_contacts = App\Models\client_contacts_projects::where('project_id', $project->id)
                                                ->where('contact_id', $contact->id)
                                                ->first();
                                        @endphp
                                        <input id="contact{{ $contact->id }}" name="contacts[]" type="checkbox" value="{{ $contact->id }}"
                                               {{ $project_contacts ? 'checked' : '' }}
                                               class="h-4 w-4 flex-shrink-0 text-[#2D7080] border-gray-300 rounded focus:ring-[#2D7080]/25">
                                        <label for="contact{{ $contact->id }}" class="ml-2 block text-sm font-medium text-slate-700 truncate">
                                            {{ $contact->name }}
                                        </label>
                                    </div>
                                    @endforeach
                                </div>
                            </section>
                        </div>
                    </div>

                    {{-- Sticky action bar --}}
                    <div class=" bottom-4 z-20 dt-card mt-5 px-5 py-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between" >
                        <p class="text-[12.5px] text-slate-500"><span class="text-red-500 font-bold">*</span> {{ __('messages.Mandatory informations') }}</p>
                        <div class="flex flex-col-reverse sm:flex-row gap-2 sm:gap-3 w-full sm:w-auto">
                            <a href="javascript:history.back()" class="dt-btn dt-btn-ghost w-full sm:w-auto">
                                {{ __('messages.Cancel') }}
                            </a>
                            <button type="submit" name="button" class="dt-btn dt-btn-teal !px-6 w-full sm:w-auto">
                                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                {{ __('messages.Update Project button') }}
                            </button>
                        </div>
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

        // Selected-count badges for teams & contacts panels (display only)
        document.addEventListener('DOMContentLoaded', function () {
            function updateCount(panelId) {
                var panel = document.getElementById(panelId);
                if (!panel) return;
                var badge = document.querySelector('[data-selected-count="' + panelId + '"]');
                if (!badge) return;
                badge.textContent = panel.querySelectorAll('input[type="checkbox"]:checked').length;
            }
            ['teamsPanel', 'contactsDiv'].forEach(function (id) {
                updateCount(id);
                var panel = document.getElementById(id);
                if (panel) panel.addEventListener('change', function () { updateCount(id); });
            });
        });
    </script>
</body>
</html>
