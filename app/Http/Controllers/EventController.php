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

        $events = Event::query()
            ->where('published', true)
            ->whereNotNull('start_at')
            ->publicUpcoming()
            ->orderBy('start_at')
            ->get();

        $featuredEvent = $events->first();
        $otherEvents = $featuredEvent ? $events->slice(1)->values() : collect();

        $pastEvents = Event::query()
            ->where('published', true)
            ->whereNotNull('start_at')
            ->publicPast()
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
