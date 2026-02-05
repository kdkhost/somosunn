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

        $now = now();

        // Public listing: upcoming events and events that haven't ended yet (ongoing).
        $events = Event::where('published', true)
            ->whereNotNull('start_at')
            ->where(function ($query) use ($now) {
                $query->where('start_at', '>=', $now)
                    ->orWhere(function ($q) use ($now) {
                        $q->whereNotNull('end_at')->where('end_at', '>=', $now);
                    });
            })
            ->orderBy('start_at')
            ->get();

        $featuredEvent = $events->first();
        $otherEvents = $featuredEvent ? $events->slice(1)->values() : collect();

        return view('events.index', compact('events', 'featuredEvent', 'otherEvents'));
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
