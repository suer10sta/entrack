<!DOCTYPE html>
<html lang="en">

@include('dashboard_layouts.head')
<!-- Calendar Specific Styles -->
<link href='https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.css' rel='stylesheet' />
<link href='https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.print.css' rel='stylesheet' media='print' />
<link href="https://cdnjs.cloudflare.com/ajax/libs/spectrum/1.8.1/spectrum.min.css" rel="stylesheet">

<style>
    #calendar {
        background: white;
        padding: 20px;
        border-radius: 10px;
        max-width: 100%;
        margin: 20px auto;
    }
    .fc-event {
        cursor: pointer;
    }
    .event-description {
        margin-top: 10px;
        font-size: 0.9em;
    }
    .fc-time-grid-event {
        padding: 5px;
    }
    .fc-day-grid-event {
        padding: 5px;
    }
    .text-sm{
    color: #332f2f;
}
</style>

<body class="g-sidenav-show bg-gray-100">
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-NKDMSK6" height="0" width="0"
            style="display: none; visibility: hidden"></iframe></noscript>
    <div class="min-height-300 bg-primary position-absolute w-100"></div>

    @if(Auth::user()->hasRole('Responsable de projet'))
        @include('employees.sidebar')
    @endif
    @if(Auth::user()->hasRole('Admin'))
        @include('admin.sidebar')
    @endif

    <main class="main-content position-relative border-radius-lg">
        <nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl z-index-sticky"
            id="navbarBlur" data-scroll="false">
            <div class="container-fluid py-1 px-3">
                @include('dashboard_layouts.navbar')
            </div>
        </nav>

        <div class="container-fluid py-4">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header pb-0">
                            <h6>Calendar</h6>
                        </div>
                        <div class="card-body px-0 pt-0 pb-2">
                            <div id="calendar"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Event Modal -->
            <div class="modal fade" id="eventModal" tabindex="-1" role="dialog">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Add Event</h5>
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                        </div>
                        <div class="modal-body">
                            <form id="eventForm">
                                <div class="form-group mb-3">
                                    <label for="title">Event Title</label>
                                    <input type="text" class="form-control" id="title" required>
                                </div>
                                <div class="form-group mb-3">
                                    <label for="description">Description</label>
                                    <textarea class="form-control" id="description" rows="3"></textarea>
                                </div>
                                <div class="form-group mb-3">
                                    <label for="color">Event Color</label>
                                    <input type="text" class="form-control" id="color">
                                </div>
                                <div class="form-group mb-3">
                                    <label for="start">Start Time</label>
                                    <input type="datetime-local" class="form-control" id="start" required>
                                </div>
                                <div class="form-group mb-3">
                                    <label for="end">End Time</label>
                                    <input type="datetime-local" class="form-control" id="end" required>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary" id="saveEvent">Save Event</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    @include('dashboard_layouts.scripts')
    
    <!-- Calendar Specific Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <script src='https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.js'></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/spectrum/1.8.1/spectrum.min.js"></script>

    <script>
        $(document).ready(function() {
            // Debug information
            console.log('Document ready');
            console.log('jQuery version:', $.fn.jquery);
            console.log('Calendar element exists:', $('#calendar').length > 0);
            console.log('Color input exists:', $('#color').length > 0);

            // Initialize color picker
            $("#color").spectrum({
                color: "#3788d8",
                preferredFormat: "hex",
                showInput: true,
                showPalette: true,
                palette: [
                    ["#3788d8", "#42ca8b", "#e83e8c", "#ffc107", "#dc3545"],
                    ["#007bff", "#28a745", "#17a2b8", "#ffc107", "#dc3545"]
                ]
            });

            var calendar = $('#calendar').fullCalendar({
                header: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'month,agendaWeek,agendaDay'
                },
                defaultView: 'month',
                editable: true,
                selectable: true,
                selectHelper: true,
                
                select: function(start, end) {
                    $('#eventModal').modal('show');
                    $('#start').val(moment(start).format('YYYY-MM-DDTHH:mm'));
                    $('#end').val(moment(end).format('YYYY-MM-DDTHH:mm'));
                },
                
                eventDrop: function(event, delta, revertFunc) {
                    $.ajax({
                        url: `/admin/calendar/events/${event.id}/dates`,
                        type: 'PATCH',
                        data: {
                            start: moment(event.start).format('YYYY-MM-DD HH:mm:ss'),
                            end: moment(event.end).format('YYYY-MM-DD HH:mm:ss'),
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            // Optional: Show success message
                        },
                        error: function() {
                            revertFunc();
                            alert('There was an error updating the event');
                        }
                    });
                },
                
                events: '/admin/calendar/events',
                
                eventRender: function(event, element) {
                    if (event.description) {
                        element.find('.fc-title').after(`<div class="event-description">${event.description}</div>`);
                    }
                },
                
                eventClick: function(event) {
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
                    
                    console.log('Original event start:', event.start);
                    console.log('Formatted start date:', startDate);
                    console.log('Original event end:', event.end);
                    console.log('Formatted end date:', endDate);
                    
                    $('#start').val(startDate);
                    $('#end').val(endDate);
                    
                    $('.modal-title').text('Edit Event');
                    $('#saveEvent').data('eventId', event.id);
                    $('#eventModal').modal('show');
                }
            });

            // Handle event saving
            $('#saveEvent').click(function() {
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
                    success: function(response) {
                        calendar.fullCalendar('refetchEvents');
                        $('#eventModal').modal('hide');
                        $('#eventForm')[0].reset();
                        $('#saveEvent').removeData('eventId');
                        $('.modal-title').text('Add Event');
                        $("#color").spectrum("set", "#3788d8"); // Reset color picker
                    },
                    error: function(error) {
                        console.log('Error:', error);
                        alert('There was an error saving the event');
                    }
                });
            });

            // Close modal handler
            $('.close').click(function() {
                $('#eventModal').modal('hide');
            });
        });
    </script>
</body>

</html>
