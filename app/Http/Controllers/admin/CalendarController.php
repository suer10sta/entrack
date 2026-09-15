<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\calendars;
use App\Models\Client;
use App\Models\User;
use App\Models\Employees;
use App\Models\Project;
use App\Models\Tasks;
use App\Models\tasks_timer;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Illuminate\Support\Facades\Mail;
use App\Mail\CalendarEventsEmail;
use Illuminate\Validation\ValidationException;

class CalendarController extends Controller
{
    public function index()
    {
        $employees = Employees::where('entreprise_id', auth()->user()->entreprise_id)
            ->where('archived', 0)
            ->orderBy('name', 'asc')
            ->get();

        $clients = Client::where('entreprise_id', auth()->user()->entreprise_id)
            ->orderBy('name', 'asc')
            ->get();

        $projects = Project::where('entreprise_id', auth()->user()->entreprise_id)
            ->where(function ($query) {
                $query->whereNull('archived')
                    ->orWhere('archived', 0);
            })
            ->orderBy('name', 'asc')
            ->get(['id', 'client_id', 'name', 'budget', 'cost_per_hour', 'estimated_hours', 'color']);

        $tasks = Tasks::where('entreprise_id', auth()->user()->entreprise_id)
            ->orderBy('title', 'asc')
            ->get(['id', 'project_id', 'title', 'estimated_hours']);

        return view('admin.calendar.calendar', compact('employees', 'clients', 'projects', 'tasks'));
    }

    public function getEvents(Request $request)
    {
        // Get the current user's entreprise_id
        $entreprise_id = auth()->user()->entreprise_id;

        $query = calendars::where('entreprise_id', $entreprise_id)
            ->with(['creator.employee', 'employees', 'client', 'project', 'task']);

        // Optional filter by client
        if ($request->filled('client_id')) {
            $query->where('client_id', $request->input('client_id'));
        }

        // Optional filter by employee (single employee id)
        if ($request->filled('employee_id')) {
            $employeeId = $request->input('employee_id');
            $query->whereHas('employees', function ($q) use ($employeeId) {
                $q->where('employees.id', $employeeId);
            });
        }

        // Show all events in the same enterprise (with optional filters)
        $eventsCollection = $query->get();

        $taskIds = $eventsCollection->pluck('task_id')
            ->filter()
            ->unique()
            ->values();

        $taskTimerHours = collect();
        if ($taskIds->isNotEmpty()) {
            // Only consider ended timers (end_date not null)
            $taskTimerHours = tasks_timer::where('entreprise_id', $entreprise_id)
                ->whereIn('task_id', $taskIds)
                ->whereNotNull('end_date')
                ->selectRaw('task_id, COALESCE(SUM(duration), 0) as total_duration_minutes')
                ->groupBy('task_id')
                ->get()
                ->mapWithKeys(function ($timerRow) {
                    return [
                        (int) $timerRow->task_id => round(((float) $timerRow->total_duration_minutes) / 60, 2),
                    ];
                });
        }

        $projectHours = $eventsCollection
            ->filter(fn($event) => !is_null($event->project_id))
            ->groupBy('project_id')
            ->map(function ($projectEvents) {
                return round($projectEvents->sum(function ($projectEvent) {
                    return $this->calculateDurationHours($projectEvent->start_date, $projectEvent->end_date);
                }), 2);
            });

        $taskHours = $eventsCollection
            ->filter(fn($event) => !is_null($event->task_id))
            ->groupBy('task_id')
            ->map(function ($taskEvents) {
                return round($taskEvents->sum(function ($taskEvent) {
                    return $this->calculateDurationHours($taskEvent->start_date, $taskEvent->end_date);
                }), 2);
            });

        $events = $eventsCollection
            ->map(function ($event) use ($projectHours, $taskHours, $taskTimerHours) {
                // Return the actual stored color, but if finished, display as gray in calendar
                // The frontend will handle the gray display based on is_finished
                $displayColor = $event->is_finished ? '#6b7280' : $event->color;
                
                // Get creator name - prefer employee name, fallback to user name
                $creatorName = 'Unknown';
                if ($event->creator) {
                    $employee = $event->creator->employee;
                    $creatorName = $employee ? $employee->name : $event->creator->name;
                }
                
                $durationHours = round($this->calculateDurationHours($event->start_date, $event->end_date), 2);
                $projectTotalHours = $event->project_id ? ($projectHours[$event->project_id] ?? 0.0) : null;
                $taskTotalHours = $event->task_id ? ($taskHours[$event->task_id] ?? 0.0) : null;
                $projectBudget = $event->project?->budget;
                $projectCostPerHour = $event->project?->cost_per_hour;
                $projectEstimatedHours = $event->project?->estimated_hours;
                $taskEstimatedHours = $event->task?->estimated_hours;
                $projectSpent = (!is_null($projectTotalHours) && !is_null($projectCostPerHour))
                    ? round($projectTotalHours * (float) $projectCostPerHour, 2)
                    : null;
                $taskTimerTotalHours = $event->task_id ? ($taskTimerHours[(int) $event->task_id] ?? 0.0) : null;
                // Use task estimated hours if present, otherwise compare against 0h
                $effectiveEstimatedHours = !is_null($taskEstimatedHours) ? (float) $taskEstimatedHours : 0.0;
                $timerDifferenceHours = !is_null($taskTimerTotalHours)
                    ? round($taskTimerTotalHours - $effectiveEstimatedHours, 2)
                    : null;

                $isBudgetExceeded = !is_null($projectBudget) && !is_null($projectSpent) && $projectSpent > (float) $projectBudget;
                $isProjectTimeExceeded = !is_null($projectEstimatedHours) && !is_null($projectTotalHours) && $projectTotalHours > (float) $projectEstimatedHours;
                $isTaskTimeExceeded = !is_null($event->task?->estimated_hours) && !is_null($taskTotalHours) && $taskTotalHours > (float) $event->task->estimated_hours;
                // Time consumed (ended timers) vs selected task estimated hours (or 0h if none)
                $isTimerTimeExceeded = !is_null($taskTimerTotalHours) && $taskTimerTotalHours > $effectiveEstimatedHours;

                return [
                    'id' => $event->id,
                    'title' => $event->title,
                    'description' => $event->description,
                    'start' => $event->start_date ? $event->start_date->format('Y-m-d H:i:s') : null,
                    'end' => $event->end_date ? $event->end_date->format('Y-m-d H:i:s') : null,
                    // Also send the pure time parts so the frontend can preserve times when resizing in month view
                    'start_time' => $event->start_date ? $event->start_date->format('H:i:s') : null,
                    'end_time' => $event->end_date ? $event->end_date->format('H:i:s') : null,
                    'color' => $displayColor, // Display color (gray if finished)
                    'originalColor' => $event->color, // Store original color for editing
                    'is_finished' => $event->is_finished,
                    'creator_name' => $creatorName,
                    'client_id' => $event->client_id,
                    'client_name' => $event->client ? $event->client->name : null,
                    'project_id' => $event->project_id,
                    'project_name' => $event->project ? $event->project->name : null,
                    'project_budget' => $projectBudget,
                    'project_cost_per_hour' => $projectCostPerHour,
                    'project_estimated_hours' => $projectEstimatedHours,
                    'project_total_hours' => $projectTotalHours,
                    'project_budget_spent' => $projectSpent,
                    'budget_consumed_hours' => $taskTimerTotalHours,
                    'task_id' => $event->task_id,
                    'task_name' => $event->task ? $event->task->title : null,
                    'task_estimated_hours' => $taskEstimatedHours,
                    'task_total_hours' => $taskTotalHours,
                    'task_timer_total_hours' => $taskTimerTotalHours,
                    'timer_difference_hours' => $timerDifferenceHours,
                    'duration_hours' => $durationHours,
                    'is_budget_exceeded' => $isBudgetExceeded,
                    'is_project_time_exceeded' => $isProjectTimeExceeded,
                    'is_task_time_exceeded' => $isTaskTimeExceeded,
                    'is_timer_time_exceeded' => $isTimerTimeExceeded,
                    'is_limit_exceeded' => $isTimerTimeExceeded,
                    'employee_ids' => $event->employees->pluck('id'),
                    'employee_names' => $event->employees->pluck('name'),
                ];
            });

        return response()->json($events);
    }

    public function store(Request $request)
    {
        // Normalize start/end coming from the frontend.
        // The JS sometimes sends an empty string or "Invalid date" when a field is left blank.
        $start = $request->input('start');
        $end = $request->input('end');
        $start = ($start && $start !== 'Invalid date') ? $start : null;
        $end = ($end && $end !== 'Invalid date') ? $end : null;

        // If only one of start/end is provided, copy it to the other
        if ($start && !$end) {
            $end = $start;
        } elseif ($end && !$start) {
            $start = $end;
        }

        $request->merge(['start' => $start, 'end' => $end]);

        // If a project is selected and it has a color, force the event color to match the project color.
        // This guarantees that all new project-related events inherit the project's color.
        if ($request->filled('project_id')) {
            $project = Project::where('id', $request->project_id)
                ->where('entreprise_id', auth()->user()->entreprise_id)
                ->first();

            if ($project && $project->color) {
                $request->merge(['color' => $project->color]);
            }
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'required|string|size:7|starts_with:#',
            'start' => 'nullable|date_format:Y-m-d H:i:s',
            // allow end to be equal to start so single-instant events are valid
            'end' => 'nullable|date_format:Y-m-d H:i:s|after_or_equal:start',
            'client_id' => 'nullable|integer|exists:clients,id',
            'project_id' => 'nullable|integer|exists:projects,id',
            'task_id' => 'nullable|integer|exists:tasks,id',
            'employee_ids' => 'nullable|array',
            'employee_ids.*' => 'integer|exists:employees,id',
        ]);

        $this->validateProjectAndTaskOwnership(
            auth()->user()->entreprise_id,
            $request->project_id,
            $request->task_id
        );

        // Ensure at least one of start/end is present after normalization
        if (!$start && !$end) {
            throw ValidationException::withMessages([
                'start' => 'Either start or end time is required.',
            ]);
        }

        $event = calendars::create([
            'title' => $request->title,
            'description' => $request->description,
            'color' => $request->color,
            'is_finished' => $request->has('is_finished') ? (bool)$request->is_finished : false,
            'start_date' => Carbon::parse($request->start),
            'end_date' => Carbon::parse($request->end),
            'entreprise_id' => auth()->user()->entreprise_id,
            'user_id' => auth()->user()->id,
            'client_id' => $request->client_id,
            'project_id' => $request->project_id,
            'task_id' => $request->task_id,
        ]);

        // When an event is created with a selected task, align the task estimated hours
        // with the planned event duration.
        if ($event->task_id) {
            $eventDurationHours = round($this->calculateDurationHours($event->start_date, $event->end_date), 2);
            Tasks::where('id', $event->task_id)
                ->where('entreprise_id', auth()->user()->entreprise_id)
                ->update(['estimated_hours' => $eventDurationHours]);
        }

        // Attach selected employees to the event (if any)
        if ($request->filled('employee_ids')) {
            $event->employees()->sync($request->employee_ids);
        }

        return response()->json([
            'id' => $event->id,
            'message' => 'Event created successfully'
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $event = calendars::findOrFail($id);

        // Normalize start/end coming from the frontend.
        $start = $request->input('start');
        $end = $request->input('end');
        $start = ($start && $start !== 'Invalid date') ? $start : null;
        $end = ($end && $end !== 'Invalid date') ? $end : null;

        // If only one of start/end is provided, copy it to the other
        if ($start && !$end) {
            $end = $start;
        } elseif ($end && !$start) {
            $start = $end;
        }

        $request->merge(['start' => $start, 'end' => $end]);

        // If a project is (re)selected and it has a color, force the event color to match the project color.
        if ($request->filled('project_id')) {
            $project = Project::where('id', $request->project_id)
                ->where('entreprise_id', auth()->user()->entreprise_id)
                ->first();

            if ($project && $project->color) {
                $request->merge(['color' => $project->color]);
            }
        }

        $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'color' => 'sometimes|string|size:7|starts_with:#',
            'start' => 'nullable|date_format:Y-m-d H:i:s',
            // allow end to be equal to start so single-instant events are valid
            'end' => 'nullable|date_format:Y-m-d H:i:s|after_or_equal:start',
            'client_id' => 'nullable|integer|exists:clients,id',
            'project_id' => 'nullable|integer|exists:projects,id',
            'task_id' => 'nullable|integer|exists:tasks,id',
            'employee_ids' => 'nullable|array',
            'employee_ids.*' => 'integer|exists:employees,id',
        ]);

        $this->validateProjectAndTaskOwnership(
            auth()->user()->entreprise_id,
            $request->project_id,
            $request->task_id
        );

        $event->update([
            'title' => $request->title ?? $event->title,
            'description' => $request->description ?? $event->description,
            'color' => $request->color ?? $event->color,
            'is_finished' => $request->has('is_finished') ? (bool)$request->is_finished : $event->is_finished,
            'start_date' => $request->start ? Carbon::parse($request->start) : $event->start_date,
            'end_date' => $request->end ? Carbon::parse($request->end) : $event->end_date,
            'client_id' => $request->has('client_id') ? $request->client_id : $event->client_id,
            'project_id' => $request->has('project_id') ? $request->project_id : $event->project_id,
            'task_id' => $request->has('task_id') ? $request->task_id : $event->task_id,
        ]);

        // Sync selected employees (if provided)
        if ($request->has('employee_ids')) {
            $event->employees()->sync($request->employee_ids ?? []);
        }

        return response()->json([
            'message' => 'Event updated successfully'
        ]);
    }

    public function destroy($id)
    {
        $event = calendars::findOrFail($id);
        $event->delete();

        return response()->json([
            'message' => 'Event deleted successfully'
        ]);
    }

    public function updateDates(Request $request, $id)
    {
        $event = calendars::findOrFail($id);

        $request->validate([
            'start' => 'required|date_format:Y-m-d H:i:s',
            // Allow end to be equal to start to avoid over-strict failures on drag/drop
            'end' => 'required|date_format:Y-m-d H:i:s|after_or_equal:start',
        ]);

        $event->update([
            'start_date' => Carbon::parse($request->start),
            'end_date' => Carbon::parse($request->end),
        ]);

        return response()->json([
            'message' => 'Event dates updated successfully'
        ]);
    }

    public function getDailyEvents(Request $request)
    {
        $date = $request->get('date', now()->format('Y-m-d'));
        $entreprise_id = auth()->user()->entreprise_id;

        $events = calendars::where('entreprise_id', $entreprise_id)
            // Include all events where the selected date falls between
            // the event start and end dates (inclusive)
            ->whereDate('start_date', '<=', $date)
            ->whereDate('end_date', '>=', $date)
            ->with('creator.employee')
            ->orderBy('start_date')
            ->get()
            ->map(function ($event) {
                // Get creator name - prefer employee name, fallback to user name
                $creatorName = 'Unknown';
                if ($event->creator) {
                    $employee = $event->creator->employee;
                    $creatorName = $employee ? $employee->name : $event->creator->name;
                }
                
                return [
                    'id' => $event->id,
                    'title' => $event->title,
                    'description' => $event->description,
                    'start' => $event->start_date,
                    'end' => $event->end_date,
                    'color' => $event->color,
                    'is_finished' => $event->is_finished,
                    'creator_name' => $creatorName,
                ];
            });

        return response()->json($events);
    }

    public function getCurrentMonthEvents(Request $request)
    {
        $month = (int) $request->get('month', now()->month);
        $year = (int) $request->get('year', now()->year);
        $entreprise_id = auth()->user()->entreprise_id;

        // Get events where start_date falls in the specified month and year
        // Using whereYear and whereMonth is more reliable than whereBetween for datetime fields
        $events = calendars::where('entreprise_id', $entreprise_id)
            ->whereYear('start_date', $year)
            ->whereMonth('start_date', $month)
            ->with('creator.employee')
            ->orderBy('start_date')
            ->get()
            ->map(function ($event) {
                $displayColor = $event->is_finished ? '#6b7280' : $event->color;
                
                // Get creator name - prefer employee name, fallback to user name
                $creatorName = 'Unknown';
                if ($event->creator) {
                    $employee = $event->creator->employee;
                    $creatorName = $employee ? $employee->name : $event->creator->name;
                }
                
                return [
                    'id' => $event->id,
                    'title' => $event->title,
                    'description' => $event->description,
                    'start' => $event->start_date ? $event->start_date->format('Y-m-d H:i:s') : null,
                    'end' => $event->end_date ? $event->end_date->format('Y-m-d H:i:s') : null,
                    'color' => $displayColor,
                    'originalColor' => $event->color,
                    'is_finished' => $event->is_finished,
                    'creator_name' => $creatorName,
                ];
            });

        return response()->json([
            'events' => $events,
            'month' => $month,
            'year' => $year,
            'count' => $events->count()
        ]);
    }

    public function previewSendEvents(Request $request)
    {
        $eventIds = $request->get('events');
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);

        if (!$eventIds) {
            return redirect()->route('calendar.index')->with('error', 'No events selected.');
        }

        $eventIdsArray = explode(',', $eventIds);
        $entreprise_id = auth()->user()->entreprise_id;

        $events = calendars::where('entreprise_id', $entreprise_id)
            ->whereIn('id', $eventIdsArray)
            ->orderBy('start_date')
            ->get()
            ->map(function ($event) {
                $displayColor = $event->is_finished ? '#6b7280' : $event->color;
                return [
                    'id' => $event->id,
                    'title' => $event->title,
                    'description' => $event->description,
                    'start' => $event->start_date,
                    'end' => $event->end_date,
                    'color' => $displayColor,
                    'is_finished' => $event->is_finished,
                ];
            });

        $monthName = Carbon::create($year, $month, 1)->format('F Y');

        return view('admin.calendar.send-events-preview', [
            'events' => $events,
            'eventIds' => $eventIds,
            'month' => $month,
            'year' => $year,
            'monthName' => $monthName
        ]);
    }

    public function generateEventsPDF(Request $request)
    {
        $eventIds = $request->get('events');
        if (!$eventIds) {
            return response()->json(['error' => 'No events specified'], 400);
        }

        $eventIdsArray = explode(',', $eventIds);
        $entreprise_id = auth()->user()->entreprise_id;

        $events = calendars::where('entreprise_id', $entreprise_id)
            ->whereIn('id', $eventIdsArray)
            ->orderBy('start_date')
            ->get()
            ->map(function ($event) {
                $displayColor = $event->is_finished ? '#6b7280' : $event->color;
                return [
                    'id' => $event->id,
                    'title' => $event->title,
                    'description' => $event->description,
                    'start' => $event->start_date,
                    'end' => $event->end_date,
                    'color' => $displayColor,
                    'is_finished' => $event->is_finished,
                ];
            });

        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);
        $monthName = Carbon::create($year, $month, 1)->format('F Y');

        $pdf = PDF::loadView('admin.calendar.events-pdf', [
            'events' => $events,
            'monthName' => $monthName
        ]);

        return $pdf->stream('calendar-events-' . $monthName . '.pdf');
    }

    public function sendEventsEmail(Request $request)
    {
        $request->validate([
            'recipients' => 'required|string',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'events' => 'required|string',
            'attachments.*' => 'nullable|file|max:10240', // Max 10MB per file
        ]);

        $eventIds = explode(',', $request->events);
        $entreprise_id = auth()->user()->entreprise_id;

        $events = calendars::where('entreprise_id', $entreprise_id)
            ->whereIn('id', $eventIds)
            ->orderBy('start_date')
            ->get()
            ->map(function ($event) {
                $displayColor = $event->is_finished ? '#6b7280' : $event->color;
                return [
                    'id' => $event->id,
                    'title' => $event->title,
                    'description' => $event->description,
                    'start' => $event->start_date,
                    'end' => $event->end_date,
                    'color' => $displayColor,
                    'is_finished' => $event->is_finished,
                ];
            });

        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);
        $monthName = Carbon::create($year, $month, 1)->format('F Y');

        // Generate PDF
        $pdf = PDF::loadView('admin.calendar.events-pdf', [
            'events' => $events,
            'monthName' => $monthName
        ]);

        // Parse recipients (comma-separated emails)
        $recipients = array_map('trim', explode(',', $request->recipients));
        $recipients = array_filter($recipients, function($email) {
            return filter_var($email, FILTER_VALIDATE_EMAIL);
        });

        if (empty($recipients)) {
            return response()->json(['error' => 'No valid email addresses provided'], 400);
        }

        // Handle attachments
        $attachments = [];
        if ($request->hasFile('attachments')) {
            $attachments = $request->file('attachments');
            if (!is_array($attachments)) {
                $attachments = [$attachments];
            }
        }

        try {
            // Send email to all recipients
            foreach ($recipients as $recipient) {
                Mail::to($recipient)->send(new CalendarEventsEmail(
                    $request->subject,
                    $request->body,
                    $pdf,
                    $attachments,
                    $monthName
                ));
            }

            return response()->json(['message' => 'Email sent successfully to ' . count($recipients) . ' recipient(s)']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to send email: ' . $e->getMessage()], 500);
        }
    }

    private function calculateDurationHours($startDate, $endDate): float
    {
        if (!$startDate || !$endDate) {
            return 0.0;
        }

        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        if ($end->lessThanOrEqualTo($start)) {
            return 0.0;
        }

        return $start->diffInMinutes($end) / 60;
    }

    private function validateProjectAndTaskOwnership(int $entrepriseId, $projectId, $taskId): void
    {
        if ($projectId) {
            $projectExists = Project::where('id', $projectId)
                ->where('entreprise_id', $entrepriseId)
                ->exists();

            if (!$projectExists) {
                throw ValidationException::withMessages([
                    'project_id' => 'The selected project is invalid for your entreprise.',
                ]);
            }
        }

        if ($taskId) {
            $taskQuery = Tasks::where('id', $taskId)
                ->where('entreprise_id', $entrepriseId);

            if ($projectId) {
                $taskQuery->where('project_id', $projectId);
            }

            if (!$taskQuery->exists()) {
                throw ValidationException::withMessages([
                    'task_id' => 'The selected task is invalid for the selected project.',
                ]);
            }
        }
    }
}
