<?php

namespace App\Http\Controllers\Declare;

use App\Http\Controllers\Controller;
use App\Models\GameEvent;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index()
    {
        if (auth()->user()->role !== 'declare') {
            abort(403);
        }

        $openEvent = GameEvent::where('created_by', auth()->id())
            ->where('status', 'open')
            ->latest()
            ->first();

        $events = GameEvent::where('created_by', auth()->id())
            ->withCount('declarations')
            ->latest()
            ->paginate(10);

        return view('declare.events.index', compact('openEvent', 'events'));
    }

    public function store(Request $request)
    {
        if (auth()->user()->role !== 'declare') {
            abort(403);
        }

        $request->validate([
            'event_name' => ['required', 'string', 'max:150'],
            'event_date' => ['required', 'date'],
        ]);

        $hasOpenEvent = GameEvent::where('created_by', auth()->id())
            ->where('status', 'open')
            ->exists();

        if ($hasOpenEvent) {
            return back()->with('error', 'You already have an open event. Close it first before creating a new one.');
        }

        GameEvent::create([
            'created_by' => auth()->id(),
            'event_name' => $request->event_name,
            'event_date' => $request->event_date,
            'status' => 'open',
        ]);

        return back()->with('success', 'Event created successfully.');
    }

    public function show(GameEvent $event)
    {
        if (auth()->user()->role !== 'declare') {
            abort(403);
        }

        if ((int) $event->created_by !== (int) auth()->id()) {
            abort(403);
        }

        $event->load([
            'creator',
            'declarations' => function ($query) {
                $query->with('declarer')->latest();
            },
        ]);

        return view('declare.events.show', compact('event'));
    }

    public function close(GameEvent $event)
    {
        if (auth()->user()->role !== 'declare') {
            abort(403);
        }

        if ((int) $event->created_by !== (int) auth()->id()) {
            abort(403);
        }

        if ($event->status === 'closed') {
            return back()->with('error', 'This event is already closed.');
        }

        $event->update([
            'status' => 'closed',
            'closed_at' => now(),
        ]);

        return back()->with('success', 'Event closed successfully.');
    }
}