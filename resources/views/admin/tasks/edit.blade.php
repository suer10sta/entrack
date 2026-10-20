<!DOCTYPE html>
<html lang="fr">
<head>
    <x-favicon />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.DalyTrac Dashboard - Update Task') }}</title>
    @vite('resources/css/app.css')
</head>
<body class="dt-app-shell bg-[#f8fafc] text-slate-900 antialiased flex h-screen overflow-hidden">
    {{-- Sidebar --}}
    @if(Auth::user()->hasRole('Admin'))
        <x-dashboard.sidebar />
    @else
        <x-employees.sidebar />
    @endif

    {{-- Main content area --}}
    <div class="flex flex-col flex-1 overflow-y-auto">

        {{-- Navbar --}}
        <x-dashboard.navbar />

        {{-- Page Content --}}
        <main class="flex-1 overflow-y-auto bg-[#f8fafc] p-4 sm:p-6 lg:px-20 lg:py-8">

            @php
                $user = Auth::user();
                $actionRoute = '#';
                $cancelRoute = '#';
                $indexRoute = '#';

                if ($user->hasRole('Admin')) {
                    $actionRoute = route('admin.tasks.update', $task);
                    $cancelRoute = route('admin.index');
                    $indexRoute = route('admin.tasks.index');
                } elseif ($user->hasRole('Responsable de projet')) {
                    $actionRoute = route('Responsable_de_projet.tasks.update', $task);
                    $cancelRoute = route('Responsable_de_projet.index');
                    $indexRoute = route('Responsable_de_projet.tasks.index');
                } elseif ($user->hasRole('Employee')) {
                    $actionRoute = route('employee.tasks.update', $task);
                    $cancelRoute = route('employee.index');
                    $indexRoute = route('employee.tasks.index');
                } elseif ($user->hasRole('Responsable de projet et gestionnaire')) {
                    $actionRoute = route('Responsable_gestionnaire.tasks.update', $task);
                    $cancelRoute = route('Responsable_gestionnaire.index');
                    $indexRoute = route('Responsable_gestionnaire.tasks.index');
                }
            @endphp

            <div class="max-w-6xl mx-auto w-full">
                {{-- Header with back navigation --}}
                <div class="flex items-center gap-3 mb-5">

                    <div class="min-w-0 flex-1">
                        <p class="dt-overline mb-1">{{ __('messages.Tasks Table') }}</p>
                        <h1 class="text-[22px] sm:text-[24px] font-bold tracking-tight text-[#0B2638] leading-tight truncate">
                            {{ __('messages.Update Task') }}
                        </h1>
                        <p class="text-[13.5px] text-slate-500 mt-1 truncate">{{ __('messages.Editing task') }}: "{{ $task->title }}"</p>
                    </div>
                    <span class="dt-badge dt-badge-navy hidden md:inline-flex flex-shrink-0 max-w-[260px]">
                        <span class="dt-dot flex-shrink-0"></span><span class="truncate">{{ $task->title }}</span>
                    </span>
                </div>

                {{-- Single form tag using the $actionRoute variable --}}
                <form method="POST" action="{{ $actionRoute }}" autocomplete="off">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 xl:grid-cols-5 gap-4 sm:gap-5 items-start">
                        {{-- Left column: task facts --}}
                        <div class="xl:col-span-3 min-w-0">
                            <section class="dt-card p-5 sm:p-6">
                                <div class="flex items-center gap-3 mb-5">
                                    <span class="flex items-center justify-center w-9 h-9 rounded-xl bg-[#0B2638]/[0.06] text-[#0B2638] ring-1 ring-[#0B2638]/10 flex-shrink-0">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    </span>
                                    <div class="min-w-0">
                                        <h2 class="dt-section-title">{{ __('messages.Task Name') }}</h2>
                                        <p class="dt-section-subtitle mt-0.5">{{ __('messages.Task Description') }}</p>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label for="TaskName" class="block text-[13px] font-semibold text-slate-700 mb-1.5">
                                        {{ __('messages.Task Name') }} <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" id="TaskName" name="title" value="{{ $task->title }}"
                                           class="w-full rounded-xl border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-800 shadow-sm outline-none transition focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25" />
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <label for="estimated_hours" class="block text-[13px] font-semibold text-slate-700 mb-1.5">
                                            {{ __('messages.Estimated Hours') }}
                                        </label>
                                        <div class="relative">
                                            <input type="number" step="0.01" min="0" id="estimated_hours" name="estimated_hours" value="{{ old('estimated_hours', $task->estimated_hours) }}"
                                                   class="w-full rounded-xl border-slate-200 bg-white pl-3.5 pr-9 py-2.5 text-sm text-slate-800 shadow-sm outline-none transition focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25" />
                                            <span class="pointer-events-none absolute inset-y-0 right-3.5 flex items-center text-[13px] font-semibold text-slate-400">h</span>
                                        </div>
                                    </div>

                                    <div>
                                        <label for="consumed_hours" class="block text-[13px] font-semibold text-slate-700 mb-1.5">
                                            {{ __('messages.Consumed Hours') }}
                                        </label>
                                        <div class="relative">
                                            <input type="number" step="0.01" min="0" id="consumed_hours" name="consumed_hours" value="{{ old('consumed_hours', $task->consumed_hours ?? 0) }}"
                                                   class="w-full rounded-xl border-slate-200 bg-white pl-3.5 pr-9 py-2.5 text-sm text-slate-800 shadow-sm outline-none transition focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25" />
                                            <span class="pointer-events-none absolute inset-y-0 right-3.5 flex items-center text-[13px] font-semibold text-slate-400">h</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <label for="start_date" class="block text-[13px] font-semibold text-slate-700 mb-1.5">
                                            {{ __('messages.Start Date') }} <span class="text-red-500">*</span>
                                        </label>
                                        <input type="date" id="start_date" name="start"
                                            value="{{ old('start', $task->start ?? '') }}"
                                            class="w-full rounded-xl border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-800 shadow-sm outline-none transition focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25" />
                                    </div>

                                    <div>
                                        <label for="end_date" class="block text-[13px] font-semibold text-slate-700 mb-1.5">
                                            {{ __('messages.End Date') }}
                                        </label>
                                        <input type="date" id="end_date" name="end"
                                            value="{{ old('end', $task->end ?? '') }}"
                                            class="w-full rounded-xl border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-800 shadow-sm outline-none transition focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25" />
                                    </div>
                                </div>

                                <div>
                                    <label for="TaskDescription" class="block text-[13px] font-semibold text-slate-700 mb-1.5">
                                        {{ __('messages.Task Description') }}
                                    </label>
                                    <textarea id="TaskDescription" name="description" rows="4"
                                              class="w-full rounded-xl border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-800 shadow-sm outline-none transition focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 resize-y">{{ $task->description }}</textarea>
                                </div>
                            </section>
                        </div>

                        {{-- Right column: assignments --}}
                        <div class="xl:col-span-2 space-y-4 sm:space-y-5 min-w-0">
                            {{-- Employees Checkboxes --}}
                            <section class="dt-card p-5 sm:p-6">
                                <div class="flex items-center justify-between gap-3 mb-4">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <span class="flex items-center justify-center w-9 h-9 rounded-xl bg-[#0B2638]/[0.06] text-[#0B2638] ring-1 ring-[#0B2638]/10 flex-shrink-0">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                        </span>
                                        <h2 class="dt-section-title truncate">{{ __('messages.Assign Employees') }} <span class="text-red-500">*</span></h2>
                                    </div>
                                    <span class="dt-badge dt-badge-navy flex-shrink-0"><span class="dt-dot"></span><span data-selected-count="employeesPanel">0</span></span>
                                </div>
                                <div id="employeesPanel" class="max-h-60 space-y-1 overflow-y-auto rounded-xl border border-slate-200 bg-slate-50/50 p-3 custom-scrollbar">

                                    {{-- CORRECTED LOGIC: Loops assigned employees (checked) --}}
                                    @if(isset($old_employee_users))
                                        @foreach($old_employee_users as $employee)
                                            <div class="flex items-center rounded-xl px-3 py-2 hover:bg-white border border-transparent hover:border-slate-200 hover:shadow-sm transition-colors">
                                                <input id="employee{{$employee->id}}" name="employees[]" type="checkbox" value="{{$employee->id}}" checked
                                                       class="h-4 w-4 flex-shrink-0 rounded border-gray-300 text-[#2D7080] focus:ring-[#2D7080]/25">
                                                <label for="employee{{$employee->id}}" class="ml-2 block text-sm font-medium text-slate-700 truncate">
                                                    {{$employee->name}}
                                                </label>
                                            </div>
                                        @endforeach
                                    @endif

                                    {{-- CORRECTED LOGIC: Loops unassigned employees (unchecked) --}}
                                    @if(isset($employee_users))
                                        @foreach($employee_users as $employee)
                                            <div class="flex items-center rounded-xl px-3 py-2 hover:bg-white border border-transparent hover:border-slate-200 hover:shadow-sm transition-colors">
                                                <input id="employee{{$employee->id}}" name="employees[]" type="checkbox" value="{{$employee->id}}"
                                                       class="h-4 w-4 flex-shrink-0 rounded border-gray-300 text-[#2D7080] focus:ring-[#2D7080]/25">
                                                <label for="employee{{$employee->id}}" class="ml-2 block text-sm font-medium text-slate-700 truncate">
                                                    {{$employee->name}}
                                                </label>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            </section>

                            {{-- Teams Checkboxes --}}
                            <section class="dt-card p-5 sm:p-6">
                                <div class="flex items-center justify-between gap-3 mb-4">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <span class="flex items-center justify-center w-9 h-9 rounded-xl bg-[#E7B84B]/20 text-[#0B2638] ring-1 ring-[#E7B84B]/40 flex-shrink-0">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V7z" /></svg>
                                        </span>
                                        <h2 class="dt-section-title truncate">{{ __('messages.Assign Teams') }}</h2>
                                    </div>
                                    <span class="dt-badge dt-badge-navy flex-shrink-0"><span class="dt-dot"></span><span data-selected-count="teamsPanel">0</span></span>
                                </div>
                                <div id="teamsPanel" class="max-h-60 space-y-1 overflow-y-auto rounded-xl border border-slate-200 bg-slate-50/50 p-3 custom-scrollbar">

                                    {{--
                                      FIXED LOGIC: Applying the same logic as employees to teams.
                                      Assumes your controller sends '$old_teams' for assigned teams.
                                      If it's named differently (e.g., '$assigned_teams'), just change the variable name below.
                                    --}}
                                    @if(isset($old_teams))
                                        @foreach($old_teams as $team)
                                            <div class="flex items-center rounded-xl px-3 py-2 hover:bg-white border border-transparent hover:border-slate-200 hover:shadow-sm transition-colors">
                                                <input id="team{{$team->id}}" name="teams[]" type="checkbox" value="{{$team->id}}" checked
                                                       class="h-4 w-4 flex-shrink-0 rounded border-gray-300 text-[#2D7080] focus:ring-[#2D7080]/25">
                                                <label for="team{{$team->id}}" class="ml-2 block text-sm font-medium text-slate-700 truncate">
                                                    {{$team->name}}
                                                </label>
                                            </div>
                                        @endforeach
                                    @endif

                                    {{--
                                      FIXED LOGIC: Assumes '$teams' is for unassigned teams.
                                    --}}
                                    @if(isset($teams))
                                        @foreach($teams as $team)
                                            <div class="flex items-center rounded-xl px-3 py-2 hover:bg-white border border-transparent hover:border-slate-200 hover:shadow-sm transition-colors">
                                                <input id="team{{$team->id}}" name="teams[]" type="checkbox" value="{{$team->id}}"
                                                       class="h-4 w-4 flex-shrink-0 rounded border-gray-300 text-[#2D7080] focus:ring-[#2D7080]/25">
                                                <label for="team{{$team->id}}" class="ml-2 block text-sm font-medium text-slate-700 truncate">
                                                    {{$team->name}}
                                                </label>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            </section>
                        </div>
                    </div>

                    {{-- Sticky action bar --}}
                    <div class="sticky bottom-4 z-20 dt-card mt-5 px-5 py-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between" style="box-shadow: 0 16px 40px -12px rgba(11,38,56,0.25);">
                        <p class="text-[12.5px] text-slate-500"><span class="text-red-500 font-bold">*</span> {{ __('messages.Mandatory informations') }}</p>
                        <div class="flex flex-col-reverse sm:flex-row gap-2 sm:gap-3 w-full sm:w-auto">
                            {{-- Single "Cancel" link --}}
                            <button type="button" id="cancelButton"
                               class="dt-btn dt-btn-ghost w-full sm:w-auto">
                                {{ __('messages.Cancel') }}
                            </button>

                            <button type="submit"
                                    class="dt-btn dt-btn-teal !px-6 w-full sm:w-auto">
                                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                {{ __('messages.Save') }}
                            </button>
                        </div>
                    </div>

                </form>
            </div>
        </main>
    </div>

    <script>
    // This script was in your original file. It seems to be for a dropdown that is not in this form.
    // It might be part of your navbar component. I've added checks to prevent errors.
    document.addEventListener('DOMContentLoaded', function () {
        const dropdownButton = document.getElementById('taskDropdown');
        const selectedProjectInput = document.getElementById('selectedProject');

        if (dropdownButton && selectedProjectInput) {
            document.querySelectorAll('.dropdown-item').forEach(item => {
                item.addEventListener('click', function (event) {
                    event.preventDefault();

                    const selectedProject = event.target.getAttribute('data-value');

                    // Update the button text
                    dropdownButton.textContent = selectedProject;

                    // Update the hidden input value
                    selectedProjectInput.value = selectedProject;
                });
            });
        }

        // Cancel button handler
        const cancelButton = document.getElementById('cancelButton');
        if (cancelButton) {
            cancelButton.addEventListener('click', function() {
                window.history.back();
            });
        }

        // Selected-count badges for assignment panels (display only)
        function updateCount(panelId) {
            var panel = document.getElementById(panelId);
            if (!panel) return;
            var badge = document.querySelector('[data-selected-count="' + panelId + '"]');
            if (!badge) return;
            badge.textContent = panel.querySelectorAll('input[type="checkbox"]:checked').length;
        }
        ['employeesPanel', 'teamsPanel'].forEach(function (id) {
            updateCount(id);
            var panel = document.getElementById(id);
            if (panel) panel.addEventListener('change', function () { updateCount(id); });
        });

        // Prevent accidental form submission - ensure form only submits via Save button
        const form = document.querySelector('form[method="POST"]');
        if (form) {
            // Prevent form submission on Enter key in any input field
            form.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && e.target.tagName !== 'TEXTAREA') {
                    const submitButton = form.querySelector('button[type="submit"]');
                    if (e.target !== submitButton) {
                        e.preventDefault();
                        return false;
                    }
                }
            });

            // Ensure checkboxes don't accidentally trigger form submission
            document.querySelectorAll('input[type="checkbox"][name="employees[]"], input[type="checkbox"][name="teams[]"]').forEach(checkbox => {
                checkbox.addEventListener('click', function(e) {
                    // Allow checkbox to toggle normally, just stop any propagation issues
                    e.stopPropagation();
                });
            });
        }
    });
    </script>

</body>
</html>
