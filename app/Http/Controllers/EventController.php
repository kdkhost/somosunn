<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Page;
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
                    ->orWhere('end_at', '>=', $now)
                    // If end_at is empty, keep today's events visible even after the start time (common in quick-create).
                    ->orWhere(function ($q) use ($now) {
                        $q->whereNull('end_at')->whereDate('start_at', $now->toDateString());
                    });
            })
            ->orderBy('start_at')
            ->get();

        $featuredEvent = $events->first();
        $otherEvents = $featuredEvent ? $events->slice(1)->values() : collect();

        $pastEvents = Event::where('published', true)
            ->whereNotNull('start_at')
            ->where(function ($query) use ($now) {
                $query->where('end_at', '<', $now)
                    ->orWhere(function ($q) use ($now) {
                        $q->whereNull('end_at')->where('start_at', '<', $now);
                    });
            })
            ->orderByDesc('start_at')
            ->limit(6)
            ->get();

        $pageData = Page::where('slug', 'eventos')->first()?->data ?? [];

        return view('events.index', compact('events', 'featuredEvent', 'otherEvents', 'pastEvents', 'pageData'));
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
