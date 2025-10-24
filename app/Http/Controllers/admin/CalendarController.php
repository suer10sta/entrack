<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\calendars;
use Carbon\Carbon;

class CalendarController extends Controller
{
    public function index()
    {
        return view('admin.calendar.calendar');
    }

    public function getEvents()
    {
        // Get the current user's entreprise_id
        $entreprise_id = auth()->user()->entreprise_id;

        $events = calendars::where('entreprise_id', $entreprise_id)
            ->get()
            ->map(function ($event) {
                return [
                    'id' => $event->id,
                    'title' => $event->title,
                    'description' => $event->description,
                    'start' => $event->start_date ? $event->start_date->format('Y-m-d H:i:s') : null,
                    'end' => $event->end_date ? $event->end_date->format('Y-m-d H:i:s') : null,
                    'color' => $event->color,
                ];
            });

        return response()->json($events);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'required|string|size:7|starts_with:#',
            'start' => 'required|date_format:Y-m-d H:i:s',
            'end' => 'required|date_format:Y-m-d H:i:s|after:start',
        ]);

        $event = calendars::create([
            'title' => $request->title,
            'description' => $request->description,
            'color' => $request->color,
            'start_date' => Carbon::parse($request->start),
            'end_date' => Carbon::parse($request->end),
            'entreprise_id' => auth()->user()->entreprise_id,
        ]);

        return response()->json([
            'id' => $event->id,
            'message' => 'Event created successfully'
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $event = calendars::findOrFail($id);

        $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'color' => 'sometimes|string|size:7|starts_with:#',
            'start' => 'sometimes|date_format:Y-m-d H:i:s',
            'end' => 'sometimes|date_format:Y-m-d H:i:s|after:start',
        ]);

        $event->update([
            'title' => $request->title ?? $event->title,
            'description' => $request->description ?? $event->description,
            'color' => $request->color ?? $event->color,
            'start_date' => $request->start ? Carbon::parse($request->start) : $event->start_date,
            'end_date' => $request->end ? Carbon::parse($request->end) : $event->end_date,
        ]);

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
            'end' => 'required|date_format:Y-m-d H:i:s|after:start',
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
            ->whereDate('start_date', $date)
            ->orderBy('start_date')
            ->get();

        return response()->json($events);
    }
}