<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    /**
     * Display a listing of events.
     */
    public function index()
    {
        // Check if events feature is enabled
        $isEnabled = \App\Models\Setting::get('feature_events', '1') === '1';

        if (!$isEnabled) {
            abort(404, 'Eventos temporariamente indisponível');
        }

        $events = Event::where('published', true)
            ->where('start_at', '>=', now())
            ->orderBy('start_at')
            ->get();

        if ($events->isEmpty()) {
            return view('events.index', ['events' => collect(), 'isDemo' => false]);
        }

        return view('events.index', compact('events'));
    }

    /**
     * Display a single event.
     */
    public function show(Event $event)
    {
        // Check if events feature is enabled
        $isEnabled = \App\Models\Setting::get('feature_events', '1') === '1';

        if (!$isEnabled) {
            abort(404);
        }

        if (!$event->published) {
            abort(404);
        }

        return view('events.show', compact('event'));
    }
}
