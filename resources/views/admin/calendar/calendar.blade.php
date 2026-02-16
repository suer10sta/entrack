<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Planning - DalyTrac</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Calendar Specific Styles -->
    <link href='https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.css' rel='stylesheet' />
    <link href='https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.print.css' rel='stylesheet'
        media='print' />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/spectrum/1.8.1/spectrum.min.css" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

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
        }

        .fc-event .fc-title {
            font-weight: 600;
        }

        .fc-event:hover {
            transform: translateY(-1px);
        }

        .fc-time-grid-event,
        .fc-day-grid-event {
            padding: 2px 4px;
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
    </style>
</head>

<body class="bg-gray-50 flex h-screen overflow-hidden">

    {{-- Sidebar Component --}}
    @if(Auth::user()->hasRole('Admin'))
        @if(Auth::user()->hasRole('Admin'))
        <x-dashboard.sidebar />
    @else
        <x-employees.sidebar />
    @endif
    @else
        <x-employees.sidebar />
    @endif

    {{-- Main Wrapper (Navbar + Content) --}}
    <div class="flex flex-col flex-1 overflow-y-auto">

        {{-- Navbar Component --}}
        <x-dashboard.navbar />

        {{-- Main Content Area --}}
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6 lg:p-8">

            {{-- Container for the calendar --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">

                {{-- Header Section --}}
                <div class="px-6 py-5 border-b border-gray-200 flex flex-col gap-4">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                        <div>
                            <h1 class="text-xl font-bold text-gray-900">Planning Calendar</h1>
                            <p class="text-sm text-gray-500 mt-1">Manage your team's schedule and events efficiently.</p>
                        </div>
                        <div class="flex gap-3">
                            @can('Create planning')
                            <button id="openEventModal" type="button"
                                class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 rounded-lg shadow-sm transition-colors duration-200">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                <span>Add Event</span>
                            </button>
                            @endcan
                            <button id="openSendEventsModal" type="button"
                                class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:ring-4 focus:ring-green-300 rounded-lg shadow-sm transition-colors duration-200">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                <span>Send Events</span>
                            </button>
                        </div>
                    </div>

                    {{-- Filters Row --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="filterClient" class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                                Filter by Client
                            </label>
                            <select id="filterClient"
                                class="block w-full rounded-md border border-gray-300 bg-white py-2 px-3 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">All clients</option>
                                @foreach($clients as $client)
                                    <option value="{{ $client->id }}">{{ $client->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="filterEmployee" class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                                Filter by Employee
                            </label>
                            <select id="filterEmployee"
                                class="block w-full rounded-md border border-gray-300 bg-white py-2 px-3 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">All employees</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Calendar Body --}}
                <div class="p-6">
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
                class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full border border-gray-100">
                <!-- Header -->
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 border-b border-gray-100">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div
                                class="mx-auto flex-shrink-0 flex items-center justify-center h-10 w-10 rounded-full bg-blue-100 sm:mx-0">
                                <svg class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <h3 class="text-lg leading-6 font-semibold text-gray-900 modal-title" id="modal-title">
                                Add Event
                            </h3>
                        </div>
                        <button type="button"
                            class="bg-white rounded-md text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                            id="closeEventModal">
                            <span class="sr-only">Close</span>
                            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Body (Form) -->
                <div class="bg-gray-50 px-4 py-5 sm:p-6">
                    <form id="eventForm" class="space-y-4">
                        <!-- Title -->
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700">Event Title <span
                                    class="text-red-500">*</span></label>
                            <div class="mt-1">
                                <input type="text" name="title" id="title"
                                    class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md placeholder-gray-400"
                                    placeholder="e.g., Project Meeting" required>
                            </div>
                        </div>

                        <!-- Description -->
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                            <div class="mt-1">
                                <textarea id="description" name="description" rows="3"
                                    class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md placeholder-gray-400"
                                    placeholder="Add details about the event..."></textarea>
                            </div>
                        </div>

                        <!-- Color Picker -->
                        <div>
                            <label for="color" class="block text-sm font-medium text-gray-700">Tag Color</label>
                            <div class="mt-1">
                                <input type="text" id="color" name="color"
                                    class="block w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            </div>
                        </div>

                        <!-- Assign to Employees -->
                        <div>
                            <span class="block text-sm font-medium text-gray-700">Assign to Employees</span>
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
                            <p class="mt-1 text-xs text-gray-500">Select one or more employees to assign this event.</p>
                        </div>

                        <!-- Client Selection -->
                        <div>
                            <label for="client_id" class="block text-sm font-medium text-gray-700">Client</label>
                            <div class="mt-1">
                                <select id="client_id" name="client_id"
                                        class="block w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                    <option value="">No client</option>
                                    @foreach($clients as $client)
                                        <option value="{{ $client->id }}">{{ $client->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Optionally link this event to a specific client.</p>
                        </div>

                        <!-- Project Selection -->
                        <div>
                            <label for="project_id" class="block text-sm font-medium text-gray-700">Project</label>
                            <div class="mt-1">
                                <select id="project_id" name="project_id"
                                        class="block w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                    <option value="">No project</option>
                                    @foreach($projects as $project)
                                        <option value="{{ $project->id }}">{{ $project->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Link this event to a project to track budget and time.</p>
                        </div>

                        <!-- Task Selection -->
                        <div>
                            <label for="task_id" class="block text-sm font-medium text-gray-700">Task</label>
                            <div class="mt-1">
                                <select id="task_id" name="task_id"
                                        class="block w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                    <option value="">No task</option>
                                </select>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Tasks are filtered by the selected project.</p>
                        </div>

                        <!-- Finished Event Checkbox -->
                        <div class="flex items-center">
                            <input type="checkbox" id="is_finished" name="is_finished" value="1"
                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="is_finished" class="ml-2 block text-sm font-medium text-gray-700">
                                Mark as Finished
                            </label>
                        </div>

                        <!-- Time Range -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="start" class="block text-sm font-medium text-gray-700">Start Time <span
                                        class="text-red-500">*</span></label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <input type="datetime-local" name="start" id="start"
                                        class="focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                        required>
                                </div>
                            </div>

                            <div>
                                <label for="end" class="block text-sm font-medium text-gray-700">End Time <span
                                        class="text-red-500">*</span></label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <input type="datetime-local" name="end" id="end"
                                        class="focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                        required>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Footer -->
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-gray-200">
                    <button type="button" id="saveEvent"
                        class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm transition-colors duration-200">
                        Save Event
                    </button>
                    <button type="button" id="deleteEvent" style="display: none;"
                        class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm transition-colors duration-200">
                        Delete Event
                    </button>
                    <button type="button" id="cancelEvent" onclick="$('#closeEventModal').click()"
                        class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-colors duration-200">
                        Cancel
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
                class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl w-full border border-gray-100">
                <!-- Header -->
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 border-b border-gray-100">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div
                                class="mx-auto flex-shrink-0 flex items-center justify-center h-10 w-10 rounded-full bg-green-100 sm:mx-0">
                                <svg class="h-5 w-5 text-green-600" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <h3 class="text-lg leading-6 font-semibold text-gray-900" id="send-events-modal-title">
                                Select Events to Send
                            </h3>
                        </div>
                        <button type="button"
                            class="bg-white rounded-md text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                            id="closeSendEventsModal">
                            <span class="sr-only">Close</span>
                            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Body -->
                <div class="bg-gray-50 px-4 py-5 sm:p-6">
                    <div class="mb-4">
                        <p class="text-sm text-gray-600 mb-3">Select the events you want to include in the email:</p>
                        <div id="eventsList" class="space-y-2 max-h-96 overflow-y-auto">
                            <!-- Events will be loaded here -->
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-gray-200">
                    <button type="button" id="proceedToPreview"
                        class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm transition-colors duration-200">
                        Continue to Preview
                    </button>
                    <button type="button" id="cancelSendEvents" onclick="$('#closeSendEventsModal').click()"
                        class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-colors duration-200">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Calendar Specific Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
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
            @endphp
            var calendarRoutePrefix = '{{ $calendarRoutePrefix }}';
            var canCreatePlanning = {{ $canCreatePlanning ? 'true' : 'false' }};

            // Currently selected filters
            var selectedClientId = '';
            var selectedEmployeeId = '';
            var allProjects = @json($projects);
            var allTasks = @json($tasks);

            // In month view we render events as all-day, where FullCalendar uses an exclusive end date.
            // Convert back to an inclusive end date whenever we rebuild exact datetime values.
            function getMonthViewEndDateStr(event) {
                if (event.end) {
                    return moment(event.end).clone().subtract(1, 'day').format('YYYY-MM-DD');
                }

                return moment(event.start).format('YYYY-MM-DD');
            }

            // Initialize color picker with improved config
            $("#color").spectrum({
                color: "#2563eb", /* Default blue-600 */
                preferredFormat: "hex",
                showInput: true,
                showPalette: true,
                palette: [
                    ["#2563eb", "#10b981", "#f59e0b", "#ef4444", "#8b5cf6"], /* Tailwind colors */
                    ["#3b82f6", "#059669", "#d97706", "#dc2626", "#7c3aed"]
                ],
                className: "full-spectrum",
                showInitial: true,
                showSelectionPalette: true,
                maxSelectionSize: 10,
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
                $projectSelect.append('<option value="">No project</option>');

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
                $taskSelect.append('<option value="">No task</option>');

                tasksForProject.forEach(function (task) {
                    var selected = selectedTaskId && String(task.id) === String(selectedTaskId) ? 'selected' : '';
                    $taskSelect.append('<option value="' + task.id + '" ' + selected + '>' + task.title + '</option>');
                });

                if (!selectedTaskId) {
                    $taskSelect.val('');
                }
            }

            $('#project_id').on('change', function () {
                populateTaskOptions($(this).val(), null);
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
                    if (!originalColor || originalColor === '#6b7280') {
                        originalColor = currentColor !== '#6b7280' ? currentColor : '#2563eb';
                    }
                    // Set color to gray when finished is checked
                    $("#color").spectrum("set", "#6b7280");
                } else {
                    // If unchecked, restore the original color if we have it, otherwise default to blue
                    if (originalColor && originalColor !== '#6b7280') {
                        $("#color").spectrum("set", originalColor);
                    } else {
                        // Default to blue when unchecked
                        $("#color").spectrum("set", "#2563eb");
                        originalColor = null; // Reset so it defaults to blue next time
                    }
                }
            });

            const $modal = $('#eventModal');

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
                originalColor = null; // Reset original color
            };

            var calendar = $('#calendar').fullCalendar({
                header: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'month,agendaWeek,agendaDay'
                },
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
                        alert('You do not have permission to create planning events.');
                        return;
                    }
                    
                    // Handle single day click - open modal with clicked day
                    // Prevent default selection behavior
                    jsEvent.preventDefault();
                    
                    $('#eventForm')[0].reset();
                    $('input[name="employee_ids[]"]').prop('checked', false);
                    $("#color").spectrum("set", "#2563eb");
                    $('#is_finished').prop('checked', false);
                    populateProjectOptions('', null);
                    populateTaskOptions('', null);
                    originalColor = null;
                    $('#saveEvent').removeData('eventId');
                    $('#deleteEvent').removeData('eventId');
                    $('#deleteEvent').hide();
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
                        alert('You do not have permission to create planning events.');
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
                            $("#color").spectrum("set", "#2563eb");
                            $('#is_finished').prop('checked', false);
                            populateProjectOptions('', null);
                            populateTaskOptions('', null);
                            originalColor = null;
                            $('#saveEvent').removeData('eventId');
                            $('#deleteEvent').removeData('eventId');
                            $('#deleteEvent').hide();
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
                    $("#color").spectrum("set", "#2563eb");
                    $('#is_finished').prop('checked', false);
                    populateProjectOptions('', null);
                    populateTaskOptions('', null);
                    originalColor = null;
                    $('#saveEvent').removeData('eventId');
                    $('#deleteEvent').removeData('eventId');
                    $('#deleteEvent').hide();
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
                            alert('Transaction failed');
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
                            alert('Transaction failed');
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
                            alert('Error loading events.');
                        }
                    });
                },

                eventRender: function (event, element, view) {
                    var isLimitExceeded = !!event.is_limit_exceeded;
                    if (isLimitExceeded) {
                        element.css({
                            'background-color': '#dc2626',
                            'border-color': '#dc2626',
                            'color': '#ffffff'
                        });
                    }

                    // Adjust text color based on event background color
                    var bgColor = event.color || element.css('background-color');
                    if (isLimitExceeded) {
                        bgColor = '#dc2626';
                    }
                    if (bgColor && typeof bgColor === 'string' && bgColor[0] === '#') {
                        if (isLightColor(bgColor)) {
                            element.css('color', '#000000'); // pure black for light backgrounds
                        } else {
                            element.css('color', '#000000'); // white for dark backgrounds
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
                        element.find('.fc-title').after('<div class="event-project text-xs opacity-90 mt-0.5">Project: ' + event.project_name + '</div>');
                    }

                    if (event.task_name) {
                        element.find('.fc-title').after('<div class="event-task text-xs opacity-90 mt-0.5">Task: ' + event.task_name + '</div>');
                    }

                    var budgetAndTimeParts = [];
                    var projectBudget = formatCurrency(event.project_budget);
                    var projectSpent = formatCurrency(event.project_budget_spent);
                    var projectEstimated = formatHours(event.project_estimated_hours);
                    var projectTotal = formatHours(event.project_total_hours);
                    var taskEstimated = formatHours(event.task_estimated_hours);
                    var taskTotal = formatHours(event.task_total_hours);
                    var budgetConsumedHours = formatHours(event.budget_consumed_hours);
                    var eventPlannedHours = formatHours(event.duration_hours);
                    var timerDifferenceHours = formatHours(event.timer_difference_hours);

                    if (budgetConsumedHours !== null && eventPlannedHours !== null) {
                        budgetAndTimeParts.push('Budget consumed: ' + budgetConsumedHours + 'h / ' + eventPlannedHours + 'h');
                    } else if (budgetConsumedHours !== null) {
                        budgetAndTimeParts.push('Budget consumed: ' + budgetConsumedHours + 'h');
                    }

                    if (projectBudget !== null && projectSpent !== null) {
                        budgetAndTimeParts.push('Project budget: ' + projectSpent + ' / ' + projectBudget);
                    }

                    if (projectEstimated !== null && projectTotal !== null) {
                        budgetAndTimeParts.push('Project time: ' + projectTotal + 'h / ' + projectEstimated + 'h');
                    }

                    if (budgetAndTimeParts.length > 0) {
                        var infoClass = isLimitExceeded ? 'font-semibold' : 'opacity-90';
                        element.find('.fc-title').after('<div class="event-budget text-xs mt-0.5 ' + infoClass + '">' + budgetAndTimeParts.join(' | ') + '</div>');
                    }

                    if (event.is_timer_time_exceeded && timerDifferenceHours !== null) {
                        var exceededClass = isLimitExceeded ? 'font-semibold' : 'opacity-90';
                        element.find('.fc-title').after('<div class="event-exceeded text-xs mt-0.5 ' + exceededClass + '">Exceeded by: ' + timerDifferenceHours + 'h</div>');
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
                        element.find('.fc-title').after(`<div class="event-employees text-xs opacity-80 mt-0.5">Employees: ${employeesLabel}</div>`);
                    }
                },

                eventClick: function (event) {
                    $('#title').val(event.title);
                    $('#description').val(event.description);
                    
                    // Store the original color from the event (use originalColor if available, otherwise color)
                    originalColor = event.originalColor || event.color;
                    
                    // If event is finished, show gray color in picker, otherwise show original
                    var displayColor = event.is_finished ? '#6b7280' : originalColor;
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

                    $('.modal-title').text('Edit Event');
                    $('#saveEvent').data('eventId', event.id);
                    $('#deleteEvent').data('eventId', event.id);
                    $('#deleteEvent').show();
                    showModal();
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
                    alert('You do not have permission to create planning events.');
                    return;
                }
                
                $('.modal-title').text('Add Event');
                $('#eventForm')[0].reset();
                $('input[name="employee_ids[]"]').prop('checked', false);
                $("#color").spectrum("set", "#2563eb");
                $('#is_finished').prop('checked', false);
                populateProjectOptions('', null);
                populateTaskOptions('', null);
                originalColor = null; // Reset original color
                $('#saveEvent').removeData('eventId');
                $('#deleteEvent').removeData('eventId');
                $('#deleteEvent').hide();
                showModal();
            });

            $('#saveEvent').click(function () {
                // simple validation
                if (!$('#title').val() || !$('#start').val() || !$('#end').val()) {
                    alert('Please fill in all required fields.');
                    return;
                }

                // Get the color - if finished is checked, save gray; otherwise save the selected color
                var selectedColor = $("#color").spectrum("get").toHexString();
                var isFinished = $('#is_finished').is(':checked');
                // If finished, always save gray; if not finished, save the selected color
                // But if selected color is gray and not finished, default to blue
                var colorToSave = isFinished ? '#6b7280' : (selectedColor !== '#6b7280' ? selectedColor : '#2563eb');

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
                        $("#color").spectrum("set", "#2563eb"); // Reset color picker
                        $('#is_finished').prop('checked', false);
                    },
                    error: function (error) {
                        console.log('Error:', error);
                        alert('Error saving the event.');
                    }
                });
            });

            // Handle event deletion
            $('#deleteEvent').click(function () {
                var eventId = $(this).data('eventId');
                
                if (!eventId) {
                    alert('No event selected for deletion.');
                    return;
                }

                if (!confirm('Are you sure you want to delete this event? This action cannot be undone.')) {
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
                        $("#color").spectrum("set", "#2563eb");
                        $('#is_finished').prop('checked', false);
                    },
                    error: function (error) {
                        console.log('Error:', error);
                        alert('Error deleting the event.');
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
                        alert('Error loading events.');
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
                    const colorStyle = `background-color: ${event.color || '#2563eb'};`;
                    
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
                    alert('Please select at least one event to send.');
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
</body>

</html>