<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Planning - DailyTrac</title>
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
    <x-dashboard.sidebar />

    {{-- Main Wrapper (Navbar + Content) --}}
    <div class="flex flex-col flex-1 overflow-y-auto">

        {{-- Navbar Component --}}
        <x-dashboard.navbar />

        {{-- Main Content Area --}}
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6 lg:p-8">

            {{-- Container for the calendar --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">

                {{-- Header Section --}}
                <div
                    class="px-6 py-5 border-b border-gray-200 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div>
                        <h1 class="text-xl font-bold text-gray-900">Planning Calendar</h1>
                        <p class="text-sm text-gray-500 mt-1">Manage your team's schedule and events efficiently.</p>
                    </div>
                    <button id="openEventModal" type="button"
                        class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 rounded-lg shadow-sm transition-colors duration-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>Add Event</span>
                    </button>
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
                    <button type="button" id="cancelEvent" onclick="$('#closeEventModal').click()"
                        class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-colors duration-200">
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
            };

            var calendar = $('#calendar').fullCalendar({
                header: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'month,agendaWeek,agendaDay'
                },
                defaultView: 'month',
                editable: false,
                selectable: true,
                selectHelper: true,
                height: 'auto', // Better height handling
                contentHeight: 'auto',
                themeSystem: 'standard',

                select: function (start, end) {
                    showModal();
                    $('#start').val(moment(start).format('YYYY-MM-DDTHH:mm'));
                    $('#end').val(moment(end).format('YYYY-MM-DDTHH:mm'));
                },

                eventDrop: function (event, delta, revertFunc) {
                    // Keeping Drop logic same but simplified verification
                    $.ajax({
                        url: `/admin/calendar/events/${event.id}/dates`,
                        type: 'PATCH',
                        data: {
                            start: moment(event.start).format('YYYY-MM-DD HH:mm:ss'),
                            end: moment(event.end).format('YYYY-MM-DD HH:mm:ss'),
                            _token: '{{ csrf_token() }}'
                        },
                        success: function (response) {
                            // Success
                        },
                        error: function () {
                            revertFunc();
                            alert('Transaction failed');
                        }
                    });
                },

                events: '/admin/calendar/events',

                eventRender: function (event, element) {
                    if (event.description) {
                        // Add description with Tailwind styling
                        element.find('.fc-title').after(`<div class="event-description truncate text-xs opacity-90">${event.description}</div>`);
                    }
                },

                eventClick: function (event) {
                    $('#title').val(event.title);
                    $('#description').val(event.description);
                    $("#color").spectrum("set", event.color);

                    // Use the original event time without UTC conversion
                    var start = moment(event.start);
                    var end = moment(event.end);

                    // Manually construct the datetime string
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

                    $('.modal-title').text('Edit Event');
                    $('#saveEvent').data('eventId', event.id);
                    showModal();
                }
            });

            // Handle event saving
            $('#openEventModal').on('click', function () {
                $('.modal-title').text('Add Event');
                $('#eventForm')[0].reset();
                $("#color").spectrum("set", "#2563eb");
                $('#saveEvent').removeData('eventId');
                showModal();
            });

            $('#saveEvent').click(function () {
                // simple validation
                if (!$('#title').val() || !$('#start').val() || !$('#end').val()) {
                    alert('Please fill in all required fields.');
                    return;
                }

                var eventData = {
                    title: $('#title').val(),
                    description: $('#description').val(),
                    color: $("#color").spectrum("get").toHexString(),
                    start: moment($('#start').val()).format('YYYY-MM-DD HH:mm:ss'),
                    end: moment($('#end').val()).format('YYYY-MM-DD HH:mm:ss'),
                    _token: '{{ csrf_token() }}'
                };

                var eventId = $(this).data('eventId');
                var url = '/admin/calendar/events';
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
                        $('.modal-title').text('Add Event');
                        $("#color").spectrum("set", "#2563eb"); // Reset color picker
                    },
                    error: function (error) {
                        console.log('Error:', error);
                        alert('Error saving the event.');
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
        });
    </script>
</body>

</html>