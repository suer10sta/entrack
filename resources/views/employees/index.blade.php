<!DOCTYPE html>
<html lang="en">

@include('dashboard_layouts.head')
<style>
  a {
    color: blue;
}
</style>
<meta name="csrf-token" content="{{ csrf_token() }}">


<body class="g-sidenav-show bg-gray-100">
    
    <div class="min-height-300 bg-primary position-absolute w-100"></div>

    @include('employees.sidebar')

    <main class="main-content position-relative border-radius-lg">
        <nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl z-index-sticky"
            id="navbarBlur" data-scroll="false">
            <div class="container-fluid py-1 px-3">

                @include('dashboard_layouts.navbar')

            </div>

            <div class="container-fluid py-4">
                @include('employees.navinfos')


                <div class="row">
                    <div class="col-12 col-lg-8">
                        <div class="card">
                            <div class="card-body">
                                <div class="table-responsive" style="overflow: visible;">
                                    <table class="table align-items-center mb-0">
                                        <thead>
                                            <tr>
                                                <th
                                                    class="text-uppercase text-dark text-sm font-weight-bolder opacity-10">
                                                    Project
                                                </th>
                                                <th
                                                    class="text-uppercase text-dark text-sm font-weight-bolder opacity-10 ps-2">
                                                    Budget
                                                </th>
                                                <th
                                                    class="text-uppercase text-dark text-sm font-weight-bolder opacity-10 ps-2">
                                                    Client
                                                </th>
                                                <th
                                                    class="text-uppercase text-dark text-sm font-weight-bolder opacity-10 ps-2">
                                                    Teams
                                                </th>
                                                <th
                                                    class="text-uppercase text-dark text-sm font-weight-bolder opacity-10 ps-2">
                                                    Status
                                                </th>
                                                <th
                                                    class="text-uppercase text-dark text-sm font-weight-bolder opacity-10 ps-2">
                                                    Completion
                                                </th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($projects as $project)
                                            <tr >
                                                <td>
                                                    <div class="d-flex px-2">
                                                        <div class="my-auto">
                                                            <a href="{{route('admin.projects.show',$project->id)}}">
                                                                <h6 class="mb-0 text-sm">{{$project->name}}</h6>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <p class="text-sm font-weight-bold mb-0 text-success" >${{$project->budget}}</p>
                                                </td>
                                                <td>
                                                    <p class="text-sm font-weight-bold mb-0">
                                                        <a href="{{route('admin.clients.show', $project->client_id)}}">
                                                            {{App\Models\Client::where('id', $project->client_id)->first()->name}}
                                                        </a>
                                                    </p>
                                                </td>
                                                <td>
                                                    <p class="text-sm font-weight-bold mb-0">
                                                        @forEach(App\Models\project_teams::where('project_id', $project->id)->get() as $project_team)
                                                        <a href="{{route('admin.teams.index')}}">
                                                            {{$project_team->team_name}}
                                                        </a>
                                                         <br>
                                                        @endforEach
                                                    </p>
                                                </td>
                                                <td>
                                                    <span class="badge badge-dot me-4">
                                                        @if($project->status == 0)
                                                        <i class="bg-danger"></i>
                                                        <span class="text-dark text-sm">inactive</span>
                                                        @elseif($project->status == 1)
                                                        <i class="bg-info"></i>
                                                        <span class="text-dark text-sm">working</span>
                                                        @else
                                                        <i class="bg-success"></i>
                                                        <span class="text-dark text-sm">done</span>
                                                        @endif
                                                    </span>
                                                </td>
                                                <td class="align-middle text-center">
                                                    <div class="d-flex align-items-center">
                                                        @if($project->status == 0)
                                                        <span class="me-2 text-sm">0%</span>
                                                        <div>
                                                            <div class="progress" style="width: 100px;">
                                                                <div class="progress-bar bg-danger" role="progressbar"
                                                                    aria-valuenow="0" aria-valuemin="0"
                                                                    aria-valuemax="100" style="width: 0%"></div>
                                                            </div>
                                                        </div>
                                                        @elseif($project->status == 1)
                                                        <span class="me-2 text-sm">60%</span>
                                                        <div>
                                                            <div class="progress" style="width: 100px;">
                                                                <div class="progress-bar bg-info" role="progressbar"
                                                                    aria-valuenow="60" aria-valuemin="0"
                                                                    aria-valuemax="100" style="width: 60%"></div>
                                                            </div>
                                                        </div>
                                                        @else
                                                        <span class="me-2 text-sm">100%</span>
                                                        <div>
                                                            <div class="progress" style="width: 100px;">
                                                                <div class="progress-bar bg-success" role="progressbar"
                                                                    aria-valuenow="100" aria-valuemin="0"
                                                                    aria-valuemax="100" style="width: 100%"></div>
                                                            </div>
                                                        </div>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td class="align-middle">
                                                    <div class="dropdown">
                                                        <button class="btn btn-link text-dark mb-0 " type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                                        <i class="fas fa-ellipsis-v"></i> 
                                                        </button>
                                                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                                            <li>
                                                                @php
                                                                $status = 1;
                                                                @endphp
                                                                <form
                                                                action="{{ route('admin.projects.status', ['project' => $project->id, 'status' => $status])}}"
                                                                method="post"
                                                                onsubmit="return confirm('Are you sure?');"
                                                                class="d-inline">
                                                                    @csrf
                                                                    <button type="submit"
                                                                        class="dropdown-item border-radius-md">Working</button>
                                                                </form>
                                                            </li>
                                                            <li>
                                                                @php
                                                                $status = 2;
                                                                @endphp
                                                                <form
                                                                action="{{ route('admin.projects.status', ['project' => $project->id, 'status' => $status])}}"
                                                                method="post"
                                                                onsubmit="return confirm('Are you sure?');"
                                                                class="d-inline">
                                                                    @csrf
                                                                    <button type="submit"
                                                                        class="dropdown-item border-radius-md">Done</button>
                                                                </form>
                                                            </li>
                                                            <li>
                                                                <a class="dropdown-item" href="#">Edit</a>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TO DO LIST CODE -->
                    <div class="col-lg-4 col-md-6 mb-4 mb-lg-0">
                        <div class="card h-100 d-flex justify-content-between ">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0 text-capitalize">To do list</h5>
                                <small class="text-muted">{{ date('d - M Y') }}</small>
                            </div>

                            <div class="card-body pt-0">
                                <ul class="list-group list-group-flush" data-toggle="checklist">
                                    @foreach($todoliste as $todo)
                                        <li class="checklist-entry list-group-item px-0">
                                            <div class="checklist-item checklist-item-success checklist-item-checked d-flex">
                                                <div class="checklist-info">
                                                    <h6 class="checklist-title mb-0">{{$todo->title}}</h6>
                                                    <small class="text-sm">From {{$todo->start}} To {{$todo->end}}</small>
                                                </div>
                                                @if($todo->status == 0)
                                                    <div class="form-check my-auto ms-auto">
                                                        <input class="form-check-input" type="checkbox" id="customCheck1" data-id="{{ $todo->id }}" />
                                                    </div>
                                                @else
                                                    <div class="form-check my-auto ms-auto">
                                                        <input class="form-check-input" type="checkbox" id="customCheck1" checked data-id="{{ $todo->id }}" />
                                                    </div>
                                                @endif
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                                
                            </div>
                            <div class="d-flex justify-content-center pb-3">
                                <a href="{{route('admin.todoliste.create')}}" type="button" class="btn btn-outline-primary btn-xs m-2 ">
                                    New
                                </a>
                                <form action="{{route('admin.todoliste.destroy') }}" method="post"
                                    onsubmit="return confirm('Are you sure?');" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-primary btn-xs m-2">Clear</button>
                                </form>
                                
                            </div>
                        </div>
                        
                    </div>
                </div>




                <div class="row mt-3">
                    <!-- TEAM MEMBERS CARD-->
                    <div class="row mt-4">
                        <div class="col-lg-4 col-md-6 mb-4 mb-lg-0">
                            <div class="card h-100">
                                <div class="card-header">
                                    <h5 class="mb-0 text-capitalize">Teams </h5>
                                </div>
                                <div class="card-body pt-0">
                                    <ul class="list-group list-group-flush">
                                        @foreach($teams as $team)
                                        <li class="list-group-item px-0">
                                            <div class="row align-items-center">
                                                <div class="col-auto d-flex align-items-center">
                                                    @php
                                                    $colors = ['#2ECC71', '#BDC3C7', '#F39C12', '#E74C3C', '#9B59B6',
                                                    '#2E86C1','#641E16','#2C3E50','#F4D03F','#F4D03F'];
                                                    @endphp
                                                    <div class="avatar"
                                                        style="background-color: {{ $colors[$loop->index % count($colors)] }};">
                                                        <span class="initial-letter">{{ strtoupper(substr($team->name,
                                                            0, 1)) }}</span>
                                                    </div>
                                                </div>
                                                <div class="col ml-2">
                                                    <h6 class="mb-0">
                                                        <a href="{{route('admin.teams.index')}}">{{$team->name}}</a>
                                                    </h6>
                                                </div>

                                                <div class="col-auto">
                                                    <a href="{{route('admin.teams.index')}}" type="button"
                                                        class="btn btn-outline-primary btn-xs mb-0">
                                                        view
                                                    </a>
                                                </div>
                                            </div>
                                        </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>


                        <!--CALANDER CARD-->
                        <div class="col-12 col-lg-8">



                            <div class="card">
                                <div class="card-body">
                                    <div class="col-md-6">
                                        <div class="input-group mb-3">
                                            <input type="text" id="searchInput" class="form-control"
                                                placeholder="Search events">
                                        </div>
                                    </div>

                                    <!-- Add Event Button -->

                                    <!-- Add Event Form (hidden by default) -->
                                    <div id="addEventForm" style="display : none;">
                                        <form action="" method="POST">
                                            @csrf
                                            <label for='title'>{{ __('title') }}</label>
                                            <input type='text' class='form-control' id='title' name='title'>

                                            <label for="start">{{__('Start')}}</label>
                                            <input type='date' class='form-control' id='start' name='start' required
                                                value='{{ now()->toDateString() }}'>

                                            <label for="end">{{__('End')}}</label>
                                            <input type='date' class='form-control' id='end' name='end' required
                                                value='{{ now()->toDateString() }}'>


                                            <label for="description">{{__('Description')}}</label>
                                            <textarea id="description" name="description"></textarea>

                                            <label for="color">{{__('Color')}}</label>
                                            <input type="color" id="color" name="color" />

                                            <input type="submit" value="Save" class="btn btn-success" />
                                        </form>
                                    </div>

                                    <div id="calendar" style="width: 100%; height: 100vh"></div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                
            </div>






    </main>


    @include('dashboard_layouts.scripts')

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.0/xlsx.full.min.js"></script>
    <script type="text/javascript">
    $(document).ready(function () {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            initialView: 'dayGridMonth',
            selectable: true,
            selectMirror: true,
            dayMaxEvents: true,
            events: '/events',
            eventClick: function (info) {
                if (confirm("Are you sure you want to delete this event?")) {
                    $.ajax({
                        url: '/events/' + info.event.id,
                        type: 'DELETE',
                        success: function () {
                            calendar.refetchEvents();
                        },
                        error: function () {
                            console.log('Error deleting event');
                        }
                    });
                }
            },
            eventContent: function (info) {
                var event = info.event;
                var el = document.createElement('div');
                el.innerHTML = '<span style="color:red;">❌</span> ' + event.title;
                el.addEventListener('click', function () {
                    calendar.getEventById(event.id).remove();
                });
                return el;
            },
            eventDrop: function (info) {
                var eventId = info.event.id;
                var newStartDate = info.event.start.toISOString().slice(0, 10);

                // Update the event's start date in the database
                $.ajax({
                    url: '/events/' + eventId,
                    type: 'PATCH',
                    data: {
                        start_date: newStartDate
                    },
                    success: function () {
                        console.log('Event start date updated successfully.');
                    },
                    error: function () {
                        console.log('Error updating event start date.');
                    }
                });
            },
            eventResize: function (info) {
                var eventId = info.event.id;
                var newEndDate = info.event.end.toISOString().slice(0, 10);

                // Update the event's end date in the database
                $.ajax({
                    url: '/events/' + eventId,
                    type: 'PATCH',
                    data: {
                        end_date: newEndDate
                    },
                    success: function () {
                        console.log('Event end date updated successfully.');
                    },
                    error: function () {
                        console.log('Error updating event end date.');
                    }
                });
            }
        });

        calendar.render();

        document.getElementById('exportButton').addEventListener('click', function () {
            var events = calendar.getEvents().map(function (event) {
                return {
                    title: event.title,
                    start: event.start ? event.start.toISOString().slice(0, 10) : null,
                    end: event.end ? event.end.toISOString().slice(0, 10) : null,
                    backgroundColor: event.backgroundColor
                };
            });

            var ws = XLSX.utils.json_to_sheet(events);
            var wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, 'Events');

            var blob = XLSX.write(wb, {
                bookType: 'xlsx',
                type: 'blob'
            });

            var downloadLink = document.createElement('a');
            var objectURL = URL.createObjectURL(blob);
            downloadLink.href = objectURL;
            downloadLink.download = 'events.xlsx';
            downloadLink.click();
        });

        document.getElementById('addEventButton').addEventListener('click', function () {
            document.getElementById('addEventForm').style.display = 'block';
        });

        document.getElementById('submitEventButton').addEventListener('click', function () {
            var title = document.getElementById('eventTitle').value;
            var date = document.getElementById('eventDate').value;

            calendar.addEvent({
                title: title,
                start: date,
                allDay: true
            });

            document.getElementById('addEventForm').style.display = 'none';
            document.getElementById('eventTitle').value = '';
            document.getElementById('eventDate').value = '';
        });
    });

    //CHECKBOES CHANGE STATUS
    document.querySelectorAll('.form-check-input').forEach(function(element) {
        element.addEventListener('change', function() {
            var id = this.getAttribute('data-id');
            var status = this.checked ? 1 : 0;

            fetch('/todoliste/' + id + '/updatestatus', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({id: id, status: status})
            });
        });
    });

</script>



</body>

</html>