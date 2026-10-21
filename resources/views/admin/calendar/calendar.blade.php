<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <x-favicon />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('messages.Planning - DalyTrac') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
    <style>
        /* Searchable filter selects (Tom Select, dashboard look) */
        .ts-wrapper.single .ts-control {
            border: 1px solid #e2e8f0 !important;
            border-radius: 0.5rem !important;
            padding: 0.5rem 0.75rem !important;
            font-size: 13px !important;
            background-color: #f8fafc !important;
            min-height: 38px !important;
            color: #475569 !important;
            box-shadow: none !important;
        }
        .ts-wrapper.single.focus .ts-control {
            border-color: #2D7080 !important;
            background-color: #fff !important;
            box-shadow: 0 0 0 2px rgba(45, 112, 128, 0.20) !important;
        }
        .ts-dropdown {
            border: 1px solid #e2e8f0 !important;
            border-radius: 0.5rem !important;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,.1), 0 2px 4px -2px rgba(0,0,0,.1) !important;
            z-index: 60 !important;
        }
        .ts-dropdown .option {
            padding: 0.5rem 0.75rem !important;
            font-size: 13px !important;
        }
        .ts-dropdown .option.active {
            background-color: rgba(45, 112, 128, 0.10) !important;
            color: #0B2638 !important;
        }
    </style>

    <!-- Calendar Specific Styles -->
    <link href='https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.css' rel='stylesheet' />
    <link href='https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.print.css' rel='stylesheet'
        media='print' />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/spectrum/1.8.1/spectrum.min.css" rel="stylesheet">

    <style>
        #calendar {
            background: white;
            padding: 20px;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            max-width: 100%;
        }

        .fc-toolbar {
            margin-bottom: 1.5rem;
        }

        .fc-toolbar h2 {
            font-size: 1.25rem;
            font-weight: 600;
            color: #111827; 
        }

        .fc-button {
            border-radius: 0.5rem !important;
            border: 1px solid #e5e7eb !important;
            background: #ffffff !important;
            color: #374151 !important;
            font-weight: 500 !important;
            padding: 0.5rem 1rem !important;
            height: auto !important;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            transition: all 0.2s;
        }

        .fc-button:hover {
            background-color: #f9fafb !important;
            border-color: #d1d5db !important;
        }

        .fc-button.fc-state-active {
            background: #2563eb !important;
            color: #ffffff !important;
            border-color: #2563eb !important;
        }

        .fc-event {
            cursor: pointer;
            border-radius: 6px;
            padding: 2px 4px;
            border: none;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            transition: transform 0.1s;
            margin-bottom: 6px;
            font-family: "Arial", sans-serif;
            font-size: 0.85rem;
        }

        .fc-event .fc-title {
            font-weight: 600;
            font-size: 0.9rem;
        }

        .fc-event:hover {
            transform: translateY(-1px);
        }

        .fc-time-grid-event,
        .fc-day-grid-event {
            padding: 2px 4px;
            margin-bottom: 6px;
        }

        /* Make calendar separation lines bold */
        .fc-widget-header,
        .fc-widget-content {
            border-width: 2px !important;
            border-color: #d1d5db !important; /* Tailwind gray-300 */
        }

        /* Add bottom spacing between the last event bar and the day below in month view */
        .fc-day-grid .fc-row .fc-content-skeleton tbody tr:last-child td {
            padding-bottom: 25px;
        }

        .event-description {
            margin-top: 4px;
            font-size: 0.75rem;
            opacity: 0.9;
        }

        /* Custom scrollbar for calendar if needed */
        .fc-scroller::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        .fc-scroller::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .fc-scroller::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }

        /* Responsive tweaks for FullCalendar v3 toolbar on mobile */
        @media (max-width: 1024px) {
            #calendar {
                padding: 12px;
            }

            .fc-toolbar {
                margin-bottom: 1rem;
            }

            .fc-toolbar .fc-left,
            .fc-toolbar .fc-center,
            .fc-toolbar .fc-right {
                float: none !important;
                width: 100%;
                display: flex;
                flex-wrap: wrap;
                justify-content: center;
                gap: 0.5rem;
                margin-bottom: 0.5rem;
                text-align: center;
            }

            .fc-toolbar h2 {
                width: 100%;
                font-size: 1.05rem;
                line-height: 1.25rem;
            }

            .fc-button {
                padding: 0.4rem 0.7rem !important;
                font-size: 0.85rem !important;
            }
        }

        #calendar {
            padding: 0;
            border: 0;
            border-radius: 0;
            box-shadow: none;
        }

        #calendar .fc-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 8px;
            margin: 0 0 16px;
        }

        #calendar .fc-toolbar h2 {
            margin: 0;
            color: #0B2638;
            font-size: 18px;
            font-weight: 700;
            line-height: 1.25;
        }

        #calendar .fc-button {
            border: 1px solid #e2e8f0 !important;
            border-radius: 8px !important;
            background: #fff !important;
            color: #475569 !important;
            padding: 6px 10px !important;
            font-size: 12px !important;
            font-weight: 600 !important;
            box-shadow: none !important;
            text-shadow: none !important;
        }

        #calendar .fc-button:hover {
            border-color: #cbd5e1 !important;
            background: #f8fafc !important;
        }

        #calendar .fc-button.fc-state-active,
        #calendar .fc-button.fc-state-down {
            border-color: #2D7080 !important;
            background: #2D7080 !important;
            color: #fff !important;
        }

        #calendar .fc-widget-header,
        #calendar .fc-widget-content {
            border-width: 1px !important;
            border-color: #e2e8f0 !important;
        }

        #calendar .fc-day-header {
            padding: 9px 4px;
            background: #f8fafc;
            color: #64748b;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .06em;
            text-transform: uppercase;
        }

        #calendar .fc-day-number {
            padding: 7px 8px;
            color: #64748b;
            font-size: 11px;
        }

        #calendar .fc-today {
            background: rgba(45, 112, 128, .06) !important;
        }

        #calendar .fc-event {
            margin: 2px 3px 5px;
            border: 0;
            border-radius: 6px;
            padding: 3px 5px;
            box-shadow: 0 1px 2px rgba(11, 38, 56, .10);
            font-size: 11px;
            line-height: 1.3;
            white-space: normal;
            overflow: hidden;
        }

        #calendar .fc-event .fc-title,
        #calendar .fc-event .fc-content {
            white-space: normal;
            overflow-wrap: anywhere;
            word-break: break-word;
        }

        #calendar .fc-event .event-project,
        #calendar .fc-event .event-task,
        #calendar .fc-event .event-budget,
        #calendar .fc-event .event-employees,
        #calendar .fc-event .event-description {
            max-width: 100%;
            overflow: hidden;
            overflow-wrap: anywhere;
            text-overflow: ellipsis;
        }

        .planning-modal-panel {
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            box-shadow: 0 20px 50px rgba(11, 38, 56, .18);
        }

        #eventForm :is(input:not([type="checkbox"]), select, textarea) {
            width: 100%;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background: #f8fafc;
            color: #1e293b;
            font-size: 13px;
            padding: 8px 12px;
            outline: none;
        }

        #eventForm :is(input:not([type="checkbox"]), select, textarea):focus {
            border-color: #2D7080;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(45, 112, 128, .12);
        }

        #eventForm input[type="checkbox"] { accent-color: #2D7080; }
        #eventForm label,
        #eventForm > div > span { color: #475569; font-size: 12px; font-weight: 600; }
        #eventForm p { color: #94a3b8; }
        #eventModal .modal-footer,
        #sendEventsModal .modal-footer { background: #f8fafc; border-color: #e2e8f0; }

        #calendar .fc-button-group {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            vertical-align: middle;
        }

        #calendar .fc-button-group > .fc-button {
            margin: 0 !important;
            border-radius: 8px !important;
        }

        #calendar .fc-left > .fc-button-group + .fc-button {
            margin-left: 8px;
        }

        @media (max-width: 640px) {
            #calendar .fc-toolbar {
                justify-content: center;
                gap: 6px;
            }

            #calendar .fc-toolbar .fc-left,
            #calendar .fc-toolbar .fc-center,
            #calendar .fc-toolbar .fc-right {
                display: flex;
                width: 100%;
                align-items: center;
                justify-content: center;
                flex-wrap: wrap;
                float: none !important;
                gap: 6px;
                margin: 0;
                text-align: center;
            }

            #calendar .fc-toolbar h2 {
                width: 100%;
                font-size: 16px;
            }

            #calendar .fc-button {
                padding: 5px 8px !important;
                font-size: 11px !important;
            }

            #calendar .fc-day-header {
                padding: 7px 1px;
                font-size: 9px;
            }

            #calendar .fc-day-number {
                padding: 5px 4px;
                font-size: 10px;
            }

            #calendar .fc-event {
                margin: 1px 1px 3px;
                padding: 2px 3px;
                font-size: 10px;
            }

            #calendar .fc-button-group {
                gap: 5px;
            }

            #calendar .fc-left > .fc-button-group + .fc-button {
                margin-left: 5px;
            }
        }
    </style>
</head>

<body class="dt-app-shell bg-[#f8fafc] text-slate-900 antialiased flex h-screen overflow-hidden">

    {{-- Sidebar Component --}}
    @if(Auth::user()->hasRole('Admin'))
        <x-dashboard.sidebar />
    @else
        <x-employees.sidebar />
    @endif

    {{-- Main Wrapper (Navbar + Content) --}}
    <div class="flex flex-col flex-1 overflow-y-auto">

        {{-- Navbar Component --}}
        <x-dashboard.navbar />

        {{-- Main Content Area --}}
        <main class="flex-1 min-w-0 overflow-x-hidden overflow-y-auto bg-[#f8fafc] px-3 py-4 sm:px-5 sm:py-5 lg:px-16 lg:py-7">
            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3 mb-4">
                <div class="min-w-0">
                    <p class="dt-overline mb-0.5">{{ __('messages.Planning') }}</p>
                    <h1 class="text-[18px] sm:text-[20px] lg:text-[22px] font-bold tracking-tight text-[#0B2638] leading-tight">{{ __('messages.Planning Calendar') }}</h1>
                    <p class="text-[12px] text-slate-500 mt-0.5 hidden sm:block">{{ __('messages.Planning Calendar subtitle') }}</p>
                </div>
                <div class="flex flex-col sm:flex-row sm:flex-wrap gap-2 w-full sm:w-auto">
                    @can('Create planning')
                    <button id="openEventModal" type="button" class="dt-btn dt-btn-primary justify-center w-full sm:w-auto">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                        <span>{{ __('messages.Add Event') }}</span>
                    </button>
                    @endcan
                    <button id="openSendEventsModal" type="button" class="dt-btn dt-btn-teal justify-center w-full sm:w-auto">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                        <span>{{ __('messages.Send Events') }}</span>
                    </button>
                </div>
            </div>

            <div class="dt-card p-3 sm:p-4 mb-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 sm:gap-3">
                    <div>
                        <label for="filterClient" class="block text-[11px] font-semibold uppercase tracking-[.07em] text-slate-400 mb-1.5">{{ __('messages.Filter by Client') }}</label>
                        <select id="filterClient" class="dt-filter-select block w-full rounded-lg border border-slate-200 bg-slate-50 text-[13px] text-slate-700 focus:border-[#2D7080] focus:ring-1 focus:ring-[#2D7080] py-2 px-3 transition-all">
                            <option value="">{{ __('messages.All clients') }}</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}">{{ $client->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="filterEmployee" class="block text-[11px] font-semibold uppercase tracking-[.07em] text-slate-400 mb-1.5">{{ __('messages.Filter by Employee') }}</label>
                        <select id="filterEmployee" class="dt-filter-select block w-full rounded-lg border border-slate-200 bg-slate-50 text-[13px] text-slate-700 focus:border-[#2D7080] focus:ring-1 focus:ring-[#2D7080] py-2 px-3 transition-all">
                            <option value="">{{ __('messages.All employees') }}</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="dt-card overflow-hidden">
                <div class="p-3 sm:p-4 lg:p-5">
                    <div id="calendar"></div>
                </div>
            </div>

        </main>
    </div>

    <!-- Event Modal (Tailwind CSS) -->
    <div id="eventModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog"
        aria-modal="true">
        <!-- Backdrop -->
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity backdrop-blur-sm" aria-hidden="true"
                id="modalBackdrop"></div>

            <!-- This element is to trick the browser into centering the modal contents. -->
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <!-- Modal Panel -->
            <div
                class="planning-modal-panel inline-block align-bottom bg-white text-left overflow-hidden transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                <!-- Header -->
                <div class="bg-white px-4 py-4 sm:px-5 border-b border-slate-100">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div
                                class="mx-auto flex-shrink-0 flex items-center justify-center h-9 w-9 rounded-lg bg-[#2D7080]/10 sm:mx-0">
                                <svg class="h-4 w-4 text-[#2D7080]" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <h3 class="text-[15px] leading-6 font-bold text-[#0B2638] modal-title" id="modal-title">
                                {{ __('messages.Add Event') }}
                            </h3>
                        </div>
                        <button type="button"
                            class="bg-white rounded-lg p-1 text-slate-400 hover:text-slate-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#2D7080]"
                            id="closeEventModal">
                            <span class="sr-only">{{ __('messages.Close') }}</span>
                            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Body (Form) -->
                <div class="bg-white px-4 py-4 sm:px-5 sm:py-5 max-h-[70vh] overflow-y-auto">
                    <form id="eventForm" class="space-y-4">
                        <!-- Title -->
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700">{{ __('messages.Event Title') }} <span
                                    class="text-red-500">*</span></label>
                            <div class="mt-1">
                                <input type="text" name="title" id="title"
                                    class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md placeholder-gray-400"
                                    placeholder="{{ __('messages.Event Title placeholder') }}" required>
                            </div>
                        </div>

                        <!-- Description -->
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700">{{ __('messages.Description') }}</label>
                            <div class="mt-1">
                                <textarea id="description" name="description" rows="3"
                                    class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md placeholder-gray-400"
                                    placeholder="{{ __('messages.Event Description placeholder') }}"></textarea>
                            </div>
                        </div>

                        <!-- Color Picker -->
                        <div>
                            <label for="color" class="block text-sm font-medium text-gray-700">{{ __('messages.Tag Color') }}</label>
                            <div class="mt-1">
                                <input type="text" id="color" name="color"
                                    class="block w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            </div>
                        </div>

                        <!-- Assign to Employees -->
                        <div>
                            <span class="block text-sm font-medium text-gray-700">{{ __('messages.Assign to Employees label') }}</span>
                            <div class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-48 overflow-y-auto border border-gray-200 rounded-md p-2 bg-white">
                                @foreach($employees as $employee)
                                    <label class="inline-flex items-center space-x-2 text-sm text-gray-700">
                                        <input type="checkbox"
                                               class="employee-checkbox h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                               name="employee_ids[]"
                                               value="{{ $employee->id }}">
                                        <span>{{ $employee->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                            <p class="mt-1 text-xs text-gray-500">{{ __('messages.Assign to Employees helper') }}</p>
                        </div>

                        <!-- Client Selection -->
                        <div>
                            <label for="client_id" class="block text-sm font-medium text-gray-700">{{ __('messages.Client') }}</label>
                            <div class="mt-1">
                                <select id="client_id" name="client_id"
                                        class="block w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                    <option value="">{{ __('messages.No client') }}</option>
                                    @foreach($clients as $client)
                                        <option value="{{ $client->id }}">{{ $client->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">{{ __('messages.Client helper') }}</p>
                        </div>

                        <!-- Project Selection -->
                        <div>
                            <label for="project_id" class="block text-sm font-medium text-gray-700">{{ __('messages.Project') }}</label>
                            <div class="mt-1">
                                <select id="project_id" name="project_id"
                                        class="block w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                    <option value="">{{ __('messages.No project') }}</option>
                                    @foreach($projects as $project)
                                        <option value="{{ $project->id }}">{{ $project->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">{{ __('messages.Project helper') }}</p>
                        </div>

                        <!-- Task Selection -->
                        <div>
                            <label for="task_id" class="block text-sm font-medium text-gray-700">{{ __('messages.Task') }}</label>
                            <div class="mt-1">
                                <select id="task_id" name="task_id"
                                        class="block w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                    <option value="">{{ __('messages.No task') }}</option>
                                </select>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">{{ __('messages.Task helper') }}</p>
                        </div>

                        <!-- Finished Event Checkbox -->
                        <div class="flex items-center">
                            <input type="checkbox" id="is_finished" name="is_finished" value="1"
                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="is_finished" class="ml-2 block text-sm font-medium text-gray-700">
                                {{ __('messages.Mark as Finished') }}
                            </label>
                        </div>

                        <!-- Time Range -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="start" class="block text-sm font-medium text-gray-700">{{ __('messages.Start Time') }} <span
                                        class="text-red-500">*</span></label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <input type="datetime-local" name="start" id="start"
                                        class="focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                </div>
                            </div>

                            <div>
                                <label for="end" class="block text-sm font-medium text-gray-700">{{ __('messages.End Time') }} <span
                                        class="text-red-500">*</span></label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <input type="datetime-local" name="end" id="end"
                                        class="focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Footer -->
                <div class="modal-footer px-4 py-3 sm:px-5 flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-2 border-t">
                    
                    <button type="button" id="deleteEvent" style="display: none;"
                        class="dt-btn w-full sm:w-auto bg-red-600 text-white hover:bg-red-700 justify-center"
                        title="{{ __('messages.Delete Event') }}">
                        <span class="sr-only">{{ __('messages.Delete Event') }}</span>
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path d="M7 2a1 1 0 0 0-1 1v1H4a1 1 0 1 0 0 2h.293l.853 10.24A2 2 0 0 0 7.14 18h5.72a2 2 0 0 0 1.994-1.76L15.707 6H16a1 1 0 1 0 0-2h-2V3a1 1 0 0 0-1-1H7zm1 3a1 1 0 0 1 2 0v10a1 1 0 1 1-2 0V5zm4 0a1 1 0 0 1 2 0v10a1 1 0 1 1-2 0V5z"/>
                        </svg>
                    </button>
                    <button type="button" id="saveEvent"
                        class="dt-btn dt-btn-primary w-full sm:w-auto justify-center"
                        title="{{ __('messages.Save Event') }}">
                        {{ __('messages.Save Event') }}
                    </button>
                    <!-- View Task button (shown only when event has a related project & task) -->
                    <button type="button" id="viewTask"
                        class="dt-btn dt-btn-ghost w-full sm:w-auto justify-center hidden">
                        {{ __('messages.View Task') }}
                    </button>
                    <!-- View Project button (shown only when event has a selected project) -->
                    <button type="button" id="viewProject"
                        class="dt-btn dt-btn-ghost w-full sm:w-auto justify-center hidden">
                        {{ __('messages.View Project') }}
                    </button>
                    <button type="button" id="cancelEvent" onclick="$('#closeEventModal').click()"
                        class="dt-btn dt-btn-ghost w-full sm:w-auto justify-center">
                        {{ __('messages.Cancel') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Send Events Modal -->
    <div id="sendEventsModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="send-events-modal-title" role="dialog"
        aria-modal="true">
        <!-- Backdrop -->
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity backdrop-blur-sm" aria-hidden="true"
                id="sendEventsModalBackdrop"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <!-- Modal Panel -->
            <div
                class="planning-modal-panel inline-block align-bottom bg-white text-left overflow-hidden transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl w-full">
                <!-- Header -->
                <div class="bg-white px-4 py-4 sm:px-5 border-b border-slate-100">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div
                                class="mx-auto flex-shrink-0 flex items-center justify-center h-9 w-9 rounded-lg bg-[#2D7080]/10 sm:mx-0">
                                <svg class="h-4 w-4 text-[#2D7080]" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <h3 class="text-[15px] leading-6 font-bold text-[#0B2638]" id="send-events-modal-title">
                                {{ __('messages.Select Events to Send') }}
                            </h3>
                        </div>
                        <button type="button"
                            class="bg-white rounded-lg p-1 text-slate-400 hover:text-slate-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#2D7080]"
                            id="closeSendEventsModal">
                            <span class="sr-only">{{ __('messages.Close') }}</span>
                            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Body -->
                <div class="bg-white px-4 py-4 sm:px-5 sm:py-5">
                    <div class="mb-4">
                        <p class="text-sm text-gray-600 mb-3">{{ __('messages.Select events helper') }}</p>
                        <div id="eventsList" class="space-y-2 max-h-96 overflow-y-auto">
                            <!-- Events will be loaded here -->
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="modal-footer px-4 py-3 sm:px-5 flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-2 border-t">
                    <button type="button" id="proceedToPreview"
                        class="dt-btn dt-btn-primary w-full sm:w-auto justify-center">
                        {{ __('messages.Continue to Preview') }}
                    </button>
                    <button type="button" id="cancelSendEvents" onclick="$('#closeSendEventsModal').click()"
                        class="dt-btn dt-btn-ghost w-full sm:w-auto justify-center">
                        {{ __('messages.Cancel') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Calendar Specific Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    @if(app()->getLocale() === 'fr')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/locale/fr.min.js"></script>
    @elseif(app()->getLocale() === 'es')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/locale/es.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/locale/nl.min.js"></script>
    @endif
    <script src='https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.js'></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/spectrum/1.8.1/spectrum.min.js"></script>
    {{-- Removed Bootstrap Bundle as we are using Tailwind modal --}}

    <script>
        $(document).ready(function () {
            // Debug information
            console.log('Document ready');
            console.log('Calendar element exists:', $('#calendar').length > 0);
            
            // Determine calendar route prefix based on user role
            @php
                $calendarRoutePrefix = '/admin/calendar';
                if (Auth::user()->hasRole('Employee')) {
                    $calendarRoutePrefix = '/employee/calendar';
                } elseif (Auth::user()->hasRole('Responsable de projet')) {
                    $calendarRoutePrefix = '/Responsable_de_projet/calendar';
                } elseif (Auth::user()->hasRole('Responsable de projet et gestionnaire')) {
                    $calendarRoutePrefix = '/Responsable_gestionnaire/calendar';
                }
                
                // Check if user has permission to create planning
                $canCreatePlanning = Auth::user()->can('Create planning');

                // Task show route template for the current user role
                $taskShowRouteTemplate = null;
                if (Auth::user()->hasRole('Admin')) {
                    $taskShowRouteTemplate = route('admin.tasks.show', ['task_id' => '__TASK_ID__']);
                } elseif (Auth::user()->hasRole('Responsable de projet')) {
                    $taskShowRouteTemplate = route('Responsable_de_projet.tasks.show', ['task' => '__TASK_ID__']);
                } elseif (Auth::user()->hasRole('Employee')) {
                    $taskShowRouteTemplate = route('employee.tasks.show', ['task' => '__TASK_ID__']);
                } elseif (Auth::user()->hasRole('Responsable de projet et gestionnaire')) {
                    $taskShowRouteTemplate = route('Responsable_gestionnaire.tasks.show', ['task' => '__TASK_ID__']);
                }

                // Project show route template (only for roles that have it)
                $projectShowRouteTemplate = null;
                if (Auth::user()->hasRole('Admin')) {
                    $projectShowRouteTemplate = route('admin.projects.show', '__PROJECT_ID__');
                }
            @endphp
            var calendarRoutePrefix = '{{ $calendarRoutePrefix }}';
            var canCreatePlanning = {{ $canCreatePlanning ? 'true' : 'false' }};
            var taskShowRouteTemplate = @json($taskShowRouteTemplate);
            var projectShowRouteTemplate = @json($projectShowRouteTemplate);

            // Currently selected filters
            var selectedClientId = '';
            var selectedEmployeeId = '';
            var allProjects = @json($projects);
            // Set moment.js locale for month/day names translation
            var currentLocale = @js(app()->getLocale());
            moment.locale(currentLocale);
            
            // Ensure moment locale is set before FullCalendar initialization
            // This will translate month names and day names automatically

            var allTasks = @json($tasks);
            // Default pastel color for new events
            var defaultEventColor = '#FDE68A';

            // In month view we render events as all-day, where FullCalendar uses an exclusive end date.
            // Convert back to an inclusive end date whenever we rebuild exact datetime values.
            function getMonthViewEndDateStr(event) {
                if (event.end) {
                    return moment(event.end).clone().subtract(1, 'day').format('YYYY-MM-DD');
                }

                return moment(event.start).format('YYYY-MM-DD');
            }

            // Initialize color picker with improved config (pastel-only palette)
            $("#color").spectrum({
                color: defaultEventColor, /* Default pastel color */
                preferredFormat: "hex",
                showInput: false,
                showPalette: true,
                showPaletteOnly: true,
                palette: [
                    ["#FDE68A", "#BFDBFE", "#A5B4FC", "#FBCFE8", "#BBF7D0"], /* Pastel row 1 */
                    ["#FEF3C7", "#E0F2FE", "#E9D5FF", "#FCE7F3", "#DCFCE7"]  /* Pastel row 2 */
                ],
                className: "full-spectrum",
                showInitial: true,
                showSelectionPalette: false,
            });

            // Store original color when editing an event
            var originalColor = null;

            // Helper: determine if a hex color is light
            function isLightColor(hexColor) {
                if (!hexColor) return false;
                // Remove leading '#'
                hexColor = hexColor.replace('#', '');
                if (hexColor.length === 3) {
                    // Expand shorthand form (#abc -> #aabbcc)
                    hexColor = hexColor.split('').map(function (c) { return c + c; }).join('');
                }
                var r = parseInt(hexColor.substr(0, 2), 16);
                var g = parseInt(hexColor.substr(2, 2), 16);
                var b = parseInt(hexColor.substr(4, 2), 16);
                // Perceived luminance formula
                var luminance = 0.299 * r + 0.587 * g + 0.114 * b;
                return luminance > 186; // threshold: higher = more likely to be considered light
            }

            function safeNumber(value) {
                var parsed = parseFloat(value);
                return isNaN(parsed) ? null : parsed;
            }

            function formatHours(value) {
                var parsed = safeNumber(value);
                return parsed === null ? null : parsed.toFixed(2);
            }

            function formatCurrency(value) {
                var parsed = safeNumber(value);
                if (parsed === null) return null;
                return parsed.toFixed(2) + ' EUR';
            }

            function populateProjectOptions(clientId, selectedProjectId) {
                var clientIdInt = parseInt(clientId, 10);
                var projectsForClient = allProjects.filter(function (project) {
                    if (!clientId) {
                        return true;
                    }
                    return parseInt(project.client_id, 10) === clientIdInt;
                });

                var $projectSelect = $('#project_id');
                $projectSelect.empty();
                $projectSelect.append(('<option value="">' + @js(__('messages.No project')) + '</option>'));

                projectsForClient.forEach(function (project) {
                    var selected = selectedProjectId && String(project.id) === String(selectedProjectId) ? 'selected' : '';
                    $projectSelect.append('<option value="' + project.id + '" ' + selected + '>' + project.name + '</option>');
                });

                if (!selectedProjectId) {
                    $projectSelect.val('');
                }
            }

            function populateTaskOptions(projectId, selectedTaskId) {
                var projectIdInt = parseInt(projectId, 10);
                var tasksForProject = allTasks.filter(function (task) {
                    return parseInt(task.project_id, 10) === projectIdInt;
                });

                var $taskSelect = $('#task_id');
                $taskSelect.empty();
                $taskSelect.append(('<option value="">' + @js(__('messages.No task')) + '</option>'));

                tasksForProject.forEach(function (task) {
                    var selected = selectedTaskId && String(task.id) === String(selectedTaskId) ? 'selected' : '';
                    $taskSelect.append('<option value="' + task.id + '" ' + selected + '>' + task.title + '</option>');
                });

                if (!selectedTaskId) {
                    $taskSelect.val('');
                }
            }

            $('#project_id').on('change', function () {
                var projectId = $(this).val();
                populateTaskOptions(projectId, null);

                // When a project is selected, automatically use its color for the event (if defined)
                if (projectId) {
                    var selectedProject = allProjects.find(function (project) {
                        return String(project.id) === String(projectId);
                    });

                    if (selectedProject && selectedProject.color) {
                        $("#color").spectrum("set", selectedProject.color);
                        originalColor = selectedProject.color;
                    } else {
                        // Fall back to default color if project has no color and event is not marked finished
                        if (!$('#is_finished').is(':checked')) {
                            $("#color").spectrum("set", defaultEventColor);
                            originalColor = null;
                        }
                    }
                }
            });

            $('#client_id').on('change', function () {
                populateProjectOptions($(this).val(), null);
                populateTaskOptions('', null);
            });

            populateProjectOptions('', null);
            populateTaskOptions('', null);

            // Handle finished checkbox change
            $('#is_finished').on('change', function() {
                if ($(this).is(':checked')) {
                    // Store current color before changing to gray (only if not already stored)
                    var currentColor = $("#color").spectrum("get").toHexString();
                    if (!originalColor || originalColor === '#e5e7eb') {
                        originalColor = currentColor !== '#e5e7eb' ? currentColor : defaultEventColor;
                    }
                    // Set color to a light gray when finished is checked
                    $("#color").spectrum("set", "#e5e7eb");
                } else {
                    // If unchecked, restore the original color if we have it, otherwise default to the default event color
                    if (originalColor && originalColor !== '#e5e7eb') {
                        $("#color").spectrum("set", originalColor);
                    } else {
                        // Default to pastel color when unchecked
                        $("#color").spectrum("set", defaultEventColor);
                        originalColor = null; // Reset so it defaults to the pastel next time
                    }
                }
            });

            const $modal = $('#eventModal');
            const $viewTaskBtn = $('#viewTask');
            const $viewProjectBtn = $('#viewProject');

            // Function to show modal using Tailwind classes
            const showModal = () => {
                $modal.removeClass('hidden');
                // Small delay to allow display change to register before opacity transition (if we added one)
                // For now just removing hidden is enough for the structure
                $('body').addClass('overflow-hidden');
            };

            // Function to hide modal
            const hideModal = () => {
                $modal.addClass('hidden');
                $('body').removeClass('overflow-hidden');
                $('#saveEvent').removeData('eventId');
                $('#deleteEvent').removeData('eventId');
                $('#deleteEvent').hide();
                // Reset "View Task" button state
                $viewTaskBtn.data('taskUrl', null).addClass('hidden');
                // Reset "View Project" button state
                $viewProjectBtn.data('projectUrl', null).addClass('hidden');
                originalColor = null; // Reset original color
            };

            var calendar = $('#calendar').fullCalendar({
                header: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'month,agendaWeek,agendaDay'
                },
                buttonText: {
                    today: {!! json_encode(__('messages.Today')) !!},
                    month: {!! json_encode(__('messages.Month')) !!},
                    week: {!! json_encode(__('messages.Week')) !!},
                    day: {!! json_encode(__('messages.Day')) !!},
                    prev: {!! json_encode(__('messages.Prev')) !!},
                    next: {!! json_encode(__('messages.Next')) !!}
                },
                lang: '{{ app()->getLocale() }}',
                // FullCalendar will use moment.js locale for month/day names
                // Ensure moment.locale() is set before this initialization
                monthNames: moment.localeData().months(),
                monthNamesShort: moment.localeData().monthsShort(),
                dayNames: moment.localeData().weekdays(),
                dayNamesShort: moment.localeData().weekdaysShort(),
                defaultView: 'month',
                weekends: false, // Hide Saturday and Sunday columns (no tasks displayed on weekends)
                timeFormat: 'HH:mm', // Use 24-hour time (e.g., 14:00 instead of 2:00 PM)
                slotLabelFormat: 'HH:mm', // 24-hour time for the time axis as well
                // Allow drag & drop / resize only if the user has planning permission
                editable: canCreatePlanning,
                eventStartEditable: canCreatePlanning,      // drag start of event (changes start date/time)
                eventDurationEditable: canCreatePlanning,   // drag end of event (changes duration)
                eventResizableFromStart: canCreatePlanning, // allow resizing from the start edge as well
                selectable: canCreatePlanning, // Only allow selection if user has permission
                selectHelper: canCreatePlanning,
                height: 'auto', // Better height handling
                contentHeight: 'auto',
                themeSystem: 'standard',

                dayClick: function (date, jsEvent, view) {
                    // Check permission before allowing event creation
                    if (!canCreatePlanning) {
                        alert(@js(__('messages.You do not have permission to create planning events.')));
                        return;
                    }
                    
                    // Handle single day click - open modal with clicked day
                    // Prevent default selection behavior
                    jsEvent.preventDefault();
                    
                    $('#eventForm')[0].reset();
                    $('input[name="employee_ids[]"]').prop('checked', false);
                    $("#color").spectrum("set", defaultEventColor);
                    $('#is_finished').prop('checked', false);
                    populateProjectOptions('', null);
                    populateTaskOptions('', null);
                    originalColor = null;
                    $('#saveEvent').removeData('eventId');
                    $('#deleteEvent').removeData('eventId');
                    $('#deleteEvent').hide();
                    $viewTaskBtn.data('taskUrl', null).addClass('hidden');
                    $viewProjectBtn.data('projectUrl', null).addClass('hidden');
                    $('.modal-title').text('Add Event');
                    
                    // Set start to clicked day at 09:00 and end at 10:00
                    var clickedDay = moment(date).startOf('day').hour(9).minute(0);
                    var endTime = clickedDay.clone().add(1, 'hour');
                    // Use square brackets to escape the 'T' literal in moment.js format
                    $('#start').val(clickedDay.format('YYYY-MM-DD[T]HH:mm'));
                    $('#end').val(endTime.format('YYYY-MM-DD[T]HH:mm'));
                    
                    showModal();
                },

                select: function (start, end, jsEvent, view) {
                    // Check permission before allowing event creation
                    if (!canCreatePlanning) {
                        alert(@js(__('messages.You do not have permission to create planning events.')));
                        return;
                    }
                    
                    // Only handle select for week and day views (where dragging makes sense)
                    // In month view, dayClick handles single day clicks
                    if (view.name === 'month') {
                        // In month view, if it's a single day selection, let dayClick handle it
                        // This handles drag selections in month view
                        var startMoment = moment(start);
                        var endMoment = moment(end);
                        var diffDays = endMoment.diff(startMoment, 'days');
                        
                        // If it's essentially a single day (less than 1.5 days), treat as day click
                        if (diffDays < 1.5) {
                            $('#eventForm')[0].reset();
                            $('input[name="employee_ids[]"]').prop('checked', false);
                            $("#color").spectrum("set", defaultEventColor);
                            $('#is_finished').prop('checked', false);
                            populateProjectOptions('', null);
                            populateTaskOptions('', null);
                            originalColor = null;
                            $('#saveEvent').removeData('eventId');
                            $('#deleteEvent').removeData('eventId');
                            $('#deleteEvent').hide();
                            $viewTaskBtn.data('taskUrl', null).addClass('hidden');
                            $viewProjectBtn.data('projectUrl', null).addClass('hidden');
                            $('.modal-title').text('Add Event');
                            
                            var clickedDay = startMoment.startOf('day').hour(9).minute(0);
                            var endTime = clickedDay.clone().add(1, 'hour');
                            // Use square brackets to escape the 'T' literal in moment.js format
                            $('#start').val(clickedDay.format('YYYY-MM-DD[T]HH:mm'));
                            $('#end').val(endTime.format('YYYY-MM-DD[T]HH:mm'));
                            showModal();
                            return;
                        }
                    }
                    
                    // Handle range selection (drag to select) for week/day views or multi-day selections
                    $('#eventForm')[0].reset();
                    $('input[name="employee_ids[]"]').prop('checked', false);
                    $("#color").spectrum("set", defaultEventColor);
                    $('#is_finished').prop('checked', false);
                    populateProjectOptions('', null);
                    populateTaskOptions('', null);
                    originalColor = null;
                    $('#saveEvent').removeData('eventId');
                    $('#deleteEvent').removeData('eventId');
                    $('#deleteEvent').hide();
                    $viewTaskBtn.data('taskUrl', null).addClass('hidden');
                    $viewProjectBtn.data('projectUrl', null).addClass('hidden');
                    $('.modal-title').text('Add Event');
                    
                    // Use the selected times for range selection
                    var startMoment = moment(start);
                    var endMoment = moment(end);
                    // Use square brackets to escape the 'T' literal in moment.js format
                    $('#start').val(startMoment.format('YYYY-MM-DD[T]HH:mm'));
                    $('#end').val(endMoment.format('YYYY-MM-DD[T]HH:mm'));
                    
                    showModal();
                },

                eventDrop: function (event, delta, revertFunc) {
                    // Preserve original times when moving events, and make sure we always send a valid end > start
                    var view = $('#calendar').fullCalendar('getView');

                    var startMoment, endMoment;
                    if (view.name === 'month') {
                        // In month view FullCalendar often sets all-day events with end = null or midnight.
                        // Rebuild datetime from the new date + the original stored time, falling back safely if needed.
                        var startDateStr = moment(event.start).format('YYYY-MM-DD');
                        var endDateStr = getMonthViewEndDateStr(event);

                        var startTime = event._originalStartTime || moment(event.start).format('HH:mm:ss');
                        var endTime = event._originalEndTime || moment(event.end || event.start).format('HH:mm:ss');

                        startMoment = moment(startDateStr + ' ' + startTime, 'YYYY-MM-DD HH:mm:ss');
                        endMoment = moment(endDateStr + ' ' + endTime, 'YYYY-MM-DD HH:mm:ss');
                    } else {
                        startMoment = moment(event.start);
                        var rawEnd = event.end || startMoment.clone().add(1, 'hour');
                        endMoment = moment(rawEnd);
                    }

                    // Final safety: if either moment is invalid, fall back to using the raw event times
                    if (!startMoment.isValid()) {
                        startMoment = moment(event.start || new Date());
                    }
                    if (!endMoment.isValid() || !endMoment.isAfter(startMoment)) {
                        endMoment = startMoment.clone().add(1, 'hour');
                    }

                    var payload = {
                        start: startMoment.format('YYYY-MM-DD HH:mm:ss'),
                        end: endMoment.format('YYYY-MM-DD HH:mm:ss'),
                        _token: '{{ csrf_token() }}'
                    };

                    $.ajax({
                        url: calendarRoutePrefix + '/events/' + event.id + '/dates',
                        type: 'PATCH',
                        data: payload,
                        success: function (response) {
                            // Success
                        },
                        error: function (xhr) {
                            console.log('updateDates error (eventDrop):', {
                                status: xhr.status,
                                response: xhr.responseJSON || xhr.responseText,
                                payload: payload
                            });
                            revertFunc();
                            alert(@js(__('messages.Transaction failed')));
                        }
                    });
                },

                // Update start/end when the user extends or shortens an event
                eventResize: function (event, delta, revertFunc) {
                    // Preserve original times when resizing events, and make sure we always send a valid end > start
                    var view = $('#calendar').fullCalendar('getView');

                    var startMoment, endMoment;
                    if (view.name === 'month') {
                        var startDateStr = moment(event.start).format('YYYY-MM-DD');
                        var endDateStr = getMonthViewEndDateStr(event);

                        var startTime = event._originalStartTime || moment(event.start).format('HH:mm:ss');
                        var endTime = event._originalEndTime || moment(event.end || event.start).format('HH:mm:ss');

                        startMoment = moment(startDateStr + ' ' + startTime, 'YYYY-MM-DD HH:mm:ss');
                        endMoment = moment(endDateStr + ' ' + endTime, 'YYYY-MM-DD HH:mm:ss');
                    } else {
                        startMoment = moment(event.start);
                        var rawEnd = event.end || startMoment.clone().add(1, 'hour');
                        endMoment = moment(rawEnd);
                    }

                    if (!startMoment.isValid()) {
                        startMoment = moment(event.start || new Date());
                    }
                    if (!endMoment.isValid() || !endMoment.isAfter(startMoment)) {
                        endMoment = startMoment.clone().add(1, 'hour');
                    }

                    var resizePayload = {
                        start: startMoment.format('YYYY-MM-DD HH:mm:ss'),
                        end: endMoment.format('YYYY-MM-DD HH:mm:ss'),
                        _token: '{{ csrf_token() }}'
                    };

                    $.ajax({
                        url: calendarRoutePrefix + '/events/' + event.id + '/dates',
                        type: 'PATCH',
                        data: resizePayload,
                        success: function (response) {
                            // Success
                        },
                        error: function (xhr) {
                            console.log('updateDates error (eventResize):', {
                                status: xhr.status,
                                response: xhr.responseJSON || xhr.responseText,
                                payload: resizePayload
                            });
                            revertFunc();
                            alert(@js(__('messages.Transaction failed')));
                        }
                    });
                },

                // Load events and make them all-day in month view so they can be resized across days,
                // while preserving their exact times internally via _originalStartTime/_originalEndTime.
                events: function (start, end, timezone, callback) {
                    $.ajax({
                        url: calendarRoutePrefix + '/events',
                        type: 'GET',
                        data: {
                            client_id: selectedClientId || '',
                            employee_id: selectedEmployeeId || ''
                        },
                        success: function (events) {
                            var view = $('#calendar').fullCalendar('getView');

                            var transformed = $.map(events, function (ev) {
                                var e = $.extend({}, ev);

                                // Store original time-of-day so we can preserve it when editing in month view
                                e._originalStartTime = ev.start_time || null;
                                e._originalEndTime = ev.end_time || null;

                                // In month view, treat events as all-day so they can be resized across days.
                                // In agenda views, keep them timed.
                                if (view.name === 'month') {
                                    var startDay = moment(ev.start).startOf('day');
                                    var endActualDay = moment(ev.end || ev.start).startOf('day');

                                    // FullCalendar all-day events use an exclusive end date.
                                    e.start = startDay.format('YYYY-MM-DD');
                                    e.end = endActualDay.clone().add(1, 'day').format('YYYY-MM-DD');
                                    e.allDay = true;
                                } else {
                                    e.allDay = false;
                                }

                                return e;
                            });

                            callback(transformed);
                        },
                        error: function () {
                            alert(@js(__('messages.Error loading events.')));
                        }
                    });
                },

                eventRender: function (event, element, view) {
                    // Limit exceeded is determined on the backend: time consumed (ended timers) vs task estimate (or 0h)
                    var isLimitExceeded = !!event.is_limit_exceeded;
                    var isFinished = !!event.is_finished;
                    if (isLimitExceeded) {
                        // Use a lighter red for over-estimated events to keep them readable
                        element.css({
                            'background-color': '#fecaca', // light red
                            'border-color': '#fecaca',
                            'color': '#b91c1c' // dark red text
                        });
                    } else if (isFinished) {
                        // Use a light gray for finished tasks
                        element.css({
                            'background-color': '#B5B5B5', // light gray
                            'border-color': '#e5e7eb',
                            'color': '#000000' // dark gray text
                        });
                    } else {
                        // Adjust text color based on event background color (non exceeded, non finished events)
                        var bgColor = event.color || element.css('background-color');
                        if (bgColor && typeof bgColor === 'string' && bgColor[0] === '#') {
                            if (isLightColor(bgColor)) {
                                element.css('color', '#000000'); // pure black for light backgrounds
                            } else {
                                element.css('color', '#000000'); // black for dark backgrounds
                            }
                        }
                    }

                    // Build event bar title: time (HH:mm) - Client (bold) - Original title (uppercased)
                    var $titleEl = element.find('.fc-title');
                    var originalTitle = $titleEl.text();
                    var upperTitle = originalTitle ? originalTitle.toUpperCase() : '';
                    var titleParts = [];

                    // Use the original stored start time in month view (FullCalendar normalizes all-day events to 00:00)
                    var displayTime = null;
                    if (view && view.name === 'month' && event._originalStartTime) {
                        // event._originalStartTime is "HH:mm:ss"
                        displayTime = moment(event._originalStartTime, 'HH:mm:ss').format('HH:mm');
                    } else if (event.start) {
                        displayTime = moment(event.start).format('HH:mm');
                    }

                    if (displayTime) {
                        titleParts.push(displayTime);
                    }

                    if (event.client_name) {
                        titleParts.push('<span class="font-bold">' + event.client_name + '</span>');
                    }

                    if (upperTitle) {
                        titleParts.push(upperTitle);
                    }

                    if (titleParts.length > 0) {
                        $titleEl.html(titleParts.join(' - '));
                    }

                    // Remove default time element so we don't show time twice
                    element.find('.fc-time').remove();

                    if (event.project_name) {
                        element.find('.fc-title').after('<div class="event-project text-xs opacity-90 mt-0.5"><span class="font-bold">{{ __('messages.Project label') }}</span> ' + event.project_name + '</div>');
                    }

                    if (event.task_name) {
                        element.find('.fc-title').after('<div class="event-task text-xs opacity-90 mt-0.5"><span class="font-bold">{{ __('messages.Task label calendar') }}</span> ' + event.task_name + '</div>');
                    }

                    var budgetAndTimeParts = [];
                    var projectBudget = formatCurrency(event.project_budget);
                    var projectSpent = formatCurrency(event.project_budget_spent);
                    var taskTotal = formatHours(event.task_total_hours);
                    var timeConsumedHours = formatHours(event.budget_consumed_hours);
                    var timerDifferenceHours = formatHours(event.timer_difference_hours);
                    var hasTaskEstimate = event.task_estimated_hours !== null && event.task_estimated_hours !== undefined;
                    var taskEstimatedDisplay = hasTaskEstimate ? formatHours(event.task_estimated_hours) : '0';

                    // Time consumed vs selected task estimated hours
                    if (timeConsumedHours !== null) {
                        budgetAndTimeParts.push('<span class="font-bold">{{ __('messages.Time consumed label') }}</span> ' + timeConsumedHours + 'h / ' + taskEstimatedDisplay + 'h');
                    }

                    if (projectBudget !== null && projectSpent !== null) {
                        budgetAndTimeParts.push('<span class="font-bold">{{ __('messages.Project budget label') }}</span> ' + projectSpent + ' / ' + projectBudget);
                    }

                    if (budgetAndTimeParts.length > 0) {
                        var infoClass = isLimitExceeded ? 'font-semibold' : 'opacity-90';
                        element.find('.fc-title').after('<div class="event-budget text-xs mt-0.5 ' + infoClass + '">' + budgetAndTimeParts.join(' | ') + '</div>');
                    }

                    if (event.description) {
                        // Add description with Tailwind styling
                        element.find('.fc-title').after(`<div class="event-description truncate text-xs opacity-90">${event.description}</div>`);
                    }
                    // Add creator name below the event title
                    //if (event.creator_name) {
                    //    element.find('.fc-title').after(`<div class="event-creator text-xs opacity-75 mt-0.5" style="font-style: italic;">Created by: ${event.creator_name}</div>`);
                    //}
                    // Add assigned employees below the event (if any)
                    if (event.employee_names && event.employee_names.length > 0) {
                        var employeesLabel = event.employee_names.join(', ');
                        element.find('.fc-title(').after(`<div class="event-employees text-xs opacity-80 mt-0.5"><span class="font-bold">' + @js(__('messages.Employees label')) + '</span> ${employeesLabel}</div>`);
                    }
                },

                eventClick: function (event) {
                    $(')#title').val(event.title);
                    $('#description').val(event.description);
                    
                    // Store the original color from the event (use originalColor if available, otherwise color)
                    originalColor = event.originalColor || event.color;
                    
                    // If event is finished, show light gray color in picker, otherwise show original
                    var displayColor = event.is_finished ? '#e5e7eb' : originalColor;
                    $("#color").spectrum("set", displayColor);
                    
                    // Set finished checkbox
                    $('#is_finished').prop('checked', event.is_finished || false);

                    // Rebuild start/end using original time parts in month view so we don't lose the time (00:00) or end date
                    var view = $('#calendar').fullCalendar('getView');
                    var start, end;

                    if (view.name === 'month' && event._originalStartTime && event._originalEndTime) {
                        // Combine the displayed date with the original stored time
                        var startDateStr = moment(event.start).format('YYYY-MM-DD');
                        var endDateStr = getMonthViewEndDateStr(event);

                        start = moment(startDateStr + ' ' + event._originalStartTime, 'YYYY-MM-DD HH:mm:ss');
                        end = moment(endDateStr + ' ' + event._originalEndTime, 'YYYY-MM-DD HH:mm:ss');
                    } else {
                        // In agenda views, the event already has the correct time information
                        start = moment(event.start);
                        end = moment(event.end || event.start);
                    }

                    // Manually construct the datetime strings for the datetime-local inputs
                    var startDate = start.year() + '-' +
                        String(start.month() + 1).padStart(2, '0') + '-' +
                        String(start.date()).padStart(2, '0') + 'T' +
                        String(start.hours()).padStart(2, '0') + ':' +
                        String(start.minutes()).padStart(2, '0');

                    var endDate = end.year() + '-' +
                        String(end.month() + 1).padStart(2, '0') + '-' +
                        String(end.date()).padStart(2, '0') + 'T' +
                        String(end.hours()).padStart(2, '0') + ':' +
                        String(end.minutes()).padStart(2, '0');

                    $('#start').val(startDate);
                    $('#end').val(endDate);

                    // Set assigned employees (if any)
                    if (event.employee_ids) {
                        var employeeIds = event.employee_ids.map(function (id) { return id.toString(); });
                        $('input[name="employee_ids[]"]').prop('checked', false);
                        employeeIds.forEach(function (id) {
                            $('input[name="employee_ids[]"][value="' + id + '"]').prop('checked', true);
                        });
                    } else {
                        $('input[name="employee_ids[]"]').prop('checked', false);
                    }

                    // Set client selection (if any)
                    if (event.client_id) {
                        $('#client_id').val(event.client_id.toString());
                    } else {
                        $('#client_id').val('');
                    }

                    if (event.project_id) {
                        populateProjectOptions($('#client_id').val(), event.project_id);
                        populateTaskOptions(event.project_id, event.task_id);
                    } else {
                        populateProjectOptions($('#client_id').val(), null);
                        populateTaskOptions('', null);
                    }

                    // Configure "View Task" button: only if the event has both project & task and we know the route template
                    if (event.project_id && event.task_id && taskShowRouteTemplate) {
                        var taskUrl = taskShowRouteTemplate.replace('__TASK_ID__', event.task_id);
                        $viewTaskBtn
                            .data('taskUrl', taskUrl)
                            .removeClass('hidden');
                    } else {
                        $viewTaskBtn
                            .data('taskUrl', null)
                            .addClass('hidden');
                    }

                    // Configure "View Project" button: only if the event has a project and we know the route template
                    if (event.project_id && projectShowRouteTemplate) {
                        var projectUrl = projectShowRouteTemplate.replace('__PROJECT_ID__', event.project_id);
                        $viewProjectBtn
                            .data('projectUrl', projectUrl)
                            .removeClass('hidden');
                    } else {
                        $viewProjectBtn
                            .data('projectUrl', null)
                            .addClass('hidden');
                    }

                    $('.modal-title').text('Edit Event');
                    $('#saveEvent').data('eventId', event.id);
                    $('#deleteEvent').data('eventId', event.id);
                    $('#deleteEvent').show();
                    showModal();
                }
            });

            // Navigate to the related task when "View Task" is clicked
            $viewTaskBtn.on('click', function () {
                var taskUrl = $(this).data('taskUrl');
                if (taskUrl) {
                    window.location.href = taskUrl;
                }
            });

            // Navigate to the related project when "View Project" is clicked
            $viewProjectBtn.on('click', function () {
                var projectUrl = $(this).data('projectUrl');
                if (projectUrl) {
                    window.location.href = projectUrl;
                }
            });

            // Keep "View Project" button in sync with the project selector (also applies to Add Event)
            $('#project_id').on('change', function () {
                var projectId = $(this).val();
                if (projectId && projectShowRouteTemplate) {
                    var projectUrl = projectShowRouteTemplate.replace('__PROJECT_ID__', projectId);
                    $viewProjectBtn.data('projectUrl', projectUrl).removeClass('hidden');
                } else {
                    $viewProjectBtn.data('projectUrl', null).addClass('hidden');
                }
            });

            // Filter handlers
            $('#filterClient').on('change', function () {
                selectedClientId = $(this).val();
                calendar.fullCalendar('refetchEvents');
            });

            $('#filterEmployee').on('change', function () {
                selectedEmployeeId = $(this).val();
                calendar.fullCalendar('refetchEvents');
            });

            // Handle event saving
            $('#openEventModal').on('click', function () {
                // Check permission before allowing event creation
                if (!canCreatePlanning) {
                    alert(@js(__('messages.You do not have permission to create planning events.')));
                    return;
                }
                
                $('.modal-title').text('Add Event');
                $('#eventForm')[0].reset();
                $('input[name="employee_ids[]"]').prop('checked', false);
                $("#color").spectrum("set", defaultEventColor);
                $('#is_finished').prop('checked', false);
                populateProjectOptions('', null);
                populateTaskOptions('', null);
                originalColor = null; // Reset original color
                $('#saveEvent').removeData('eventId');
                $('#deleteEvent').removeData('eventId');
                $('#deleteEvent').hide();
                $viewTaskBtn.data('taskUrl', null).addClass('hidden');
                $viewProjectBtn.data('projectUrl', null).addClass('hidden');
                showModal();
            });

            $('#saveEvent').click(function () {
                // simple validation (only title required on frontend; dates handled in controller)
                if (!$('#title').val()) {
                    alert(@js(__('messages.Please enter a title.')));
                    return;
                }

                // Get the color - if finished is checked, save gray; otherwise save the selected color
                var selectedColor = $("#color").spectrum("get").toHexString();
                var isFinished = $('#is_finished').is(':checked');
                // If finished, always save light gray; if not finished, save the selected color
                // But if selected color is that gray and not finished, default to the pastel color
                var colorToSave = isFinished ? '#e5e7eb' : (selectedColor !== '#e5e7eb' ? selectedColor : defaultEventColor);

                var employeeIds = $('input[name="employee_ids[]"]:checked').map(function () {
                    return $(this).val();
                }).get();

                var eventData = {
                    title: $('#title').val(),
                    description: $('#description').val(),
                    color: colorToSave,
                    is_finished: isFinished ? 1 : 0,
                    start: moment($('#start').val()).format('YYYY-MM-DD HH:mm:ss'),
                    end: moment($('#end').val()).format('YYYY-MM-DD HH:mm:ss'),
                    'employee_ids': employeeIds,
                    client_id: $('#client_id').val() || null,
                    project_id: $('#project_id').val() || null,
                    task_id: $('#task_id').val() || null,
                    _token: '{{ csrf_token() }}'
                };

                var eventId = $(this).data('eventId');
                var url = calendarRoutePrefix + '/events';
                var method = 'POST';

                if (eventId) {
                    url += `/${eventId}`;
                    method = 'PUT';
                }

                $.ajax({
                    url: url,
                    type: method,
                    data: eventData,
                    success: function (response) {
                        calendar.fullCalendar('refetchEvents');
                        hideModal();
                        $('#eventForm')[0].reset();
                        populateProjectOptions('', null);
                        populateTaskOptions('', null);
                        $('.modal-title').text('Add Event');
                        $("#color").spectrum("set", defaultEventColor); // Reset color picker
                        $('#is_finished').prop('checked', false);
                    },
                    error: function (error) {
                        console.log('Error:', error);
                        alert(@js(__('messages.Error saving the event.')));
                    }
                });
            });

            // Handle event deletion
            $('#deleteEvent').click(function () {
                var eventId = $(this).data('eventId');
                
                if (!eventId) {
                    alert(@js(__('messages.No event selected for deletion.')));
                    return;
                }

                if (!confirm(@js(__('messages.Are you sure you want to delete this event? This action cannot be undone.')))) {
                    return;
                }

                $.ajax({
                    url: calendarRoutePrefix + '/events/' + eventId,
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function (response) {
                        calendar.fullCalendar('refetchEvents');
                        hideModal();
                        $('#eventForm')[0].reset();
                        populateProjectOptions('', null);
                        populateTaskOptions('', null);
                        $('.modal-title').text('Add Event');
                        $("#color").spectrum("set", defaultEventColor);
                        $('#is_finished').prop('checked', false);
                    },
                    error: function (error) {
                        console.log('Error:', error);
                        alert(@js(__('messages.Error deleting the event.')));
                    }
                });
            });

            // Close modal handler
            $('#closeEventModal').click(hideModal);

            // Close on backdrop click
            $('#eventModal').click(function (e) {
                if (e.target.id === 'modalBackdrop' || e.target.id === 'eventModal') {
                    hideModal();
                }
            });

            // Send Events Modal functionality
            const $sendEventsModal = $('#sendEventsModal');
            let currentMonthEvents = [];
            let currentMonth = null;
            let currentYear = null;

            const showSendEventsModal = () => {
                $sendEventsModal.removeClass('hidden');
                $('body').addClass('overflow-hidden');
                loadCurrentMonthEvents();
            };

            const hideSendEventsModal = () => {
                $sendEventsModal.addClass('hidden');
                $('body').removeClass('overflow-hidden');
                $('#eventsList').empty();
            };

            const loadCurrentMonthEvents = () => {
                // Get current view date from calendar
                const view = calendar.fullCalendar('getView');
                
                // Get the actual month being displayed from the view's title
                // FullCalendar title format is "January 2026" or similar
                const title = view.title;
                const titleMatch = title.match(/(\w+)\s+(\d{4})/);
                
                if (titleMatch) {
                    // Parse month name to number
                    const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
                                      'July', 'August', 'September', 'October', 'November', 'December'];
                    const monthName = titleMatch[1];
                    const monthIndex = monthNames.indexOf(monthName);
                    if (monthIndex !== -1) {
                        currentMonth = monthIndex + 1;
                        currentYear = parseInt(titleMatch[2]);
                    } else {
                        // Fallback: use the current date
                        const now = moment();
                        currentMonth = now.month() + 1;
                        currentYear = now.year();
                    }
                } else {
                    // Fallback: use the current date
                    const now = moment();
                    currentMonth = now.month() + 1;
                    currentYear = now.year();
                }

                console.log('Loading events for:', currentMonth, currentYear, '(from title:', title + ')');

                // Fetch events for current month
                $.ajax({
                    url: calendarRoutePrefix + '/events/current-month',
                    type: 'GET',
                    data: {
                        month: currentMonth,
                        year: currentYear
                    },
                    success: function (response) {
                        console.log('Events response:', response);
                        currentMonthEvents = response.events || [];
                        renderEventsList();
                    },
                    error: function (error) {
                        console.log('Error:', error);
                        console.log('Error response:', error.responseJSON);
                        alert(@js(__('messages.Error loading events.')));
                    }
                });
            };

            const renderEventsList = () => {
                const $eventsList = $('#eventsList');
                $eventsList.empty();

                if (currentMonthEvents.length === 0) {
                    $eventsList.html('<p class="text-sm text-gray-500 text-center py-4">No events found for this month.</p>');
                    return;
                }

                currentMonthEvents.forEach(function (event) {
                    const eventDate = moment(event.start).format('MMM DD, YYYY');
                    const eventTime = moment(event.start).format('HH:mm') + ' - ' + moment(event.end).format('HH:mm');
                    const colorStyle = `background-color: ${event.color || defaultEventColor};`;
                    
                    const eventHtml = `
                        <div class="flex items-start p-3 bg-white rounded-lg border border-gray-200 hover:border-gray-300 transition-colors">
                            <input type="checkbox" 
                                   class="mt-1 h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded event-checkbox" 
                                   value="${event.id}" 
                                   data-event-id="${event.id}">
                            <div class="ml-3 flex-1">
                                <div class="flex items-center gap-2">
                                    <div class="w-3 h-3 rounded-full" style="${colorStyle}"></div>
                                    <h4 class="text-sm font-medium text-gray-900">${event.title || 'Untitled Event'}</h4>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">${eventDate} • ${eventTime}</p>
                                ${event.description ? `<p class="text-xs text-gray-600 mt-1">${event.description}</p>` : ''}
                            </div>
                        </div>
                    `;
                    $eventsList.append(eventHtml);
                });
            };

            // Open send events modal
            $('#openSendEventsModal').on('click', function () {
                showSendEventsModal();
            });

            // Close send events modal
            $('#closeSendEventsModal').click(hideSendEventsModal);

            // Close on backdrop click
            $('#sendEventsModal').click(function (e) {
                if (e.target.id === 'sendEventsModalBackdrop' || e.target.id === 'sendEventsModal') {
                    hideSendEventsModal();
                }
            });

            // Proceed to preview
            $('#proceedToPreview').on('click', function () {
                const selectedEventIds = [];
                $('.event-checkbox:checked').each(function () {
                    selectedEventIds.push($(this).data('event-id'));
                });

                if (selectedEventIds.length === 0) {
                    alert(@js(__('messages.Please select at least one event to send.')));
                    return;
                }

                // Redirect to preview page with selected event IDs
                const params = new URLSearchParams({
                    events: selectedEventIds.join(','),
                    month: currentMonth,
                    year: currentYear
                });
                window.location.href = calendarRoutePrefix + '/send-events/preview?' + params.toString();
            });
        });
    </script>
    {{-- Searchable filter selects (Tom Select). Syncs the original selects and
         fires their change events, so existing filter logic keeps working. --}}
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            ['filterClient', 'filterEmployee'].forEach(function (id) {
                var el = document.getElementById(id);
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
        });
    </script>
</body>

</html>
