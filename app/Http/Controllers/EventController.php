<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Page;
use App\Support\ContentVisibility;
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

        $events = ContentVisibility::applyPublicFilter(
            Event::query()
                ->where('published', true)
                ->where('type', 'event')
                ->whereNotNull('start_at'),
            'events'
        )
            ->publicUpcoming()
            ->orderBy('start_at')
            ->get();

        $featuredEvent = $events->first();
        $otherEvents = $featuredEvent ? $events->slice(1)->values() : collect();

        $pastEvents = ContentVisibility::applyPublicFilter(
            Event::query()
                ->where('published', true)
                ->where('type', 'event')
                ->whereNotNull('start_at'),
            'events'
        )
            ->publicPast()
            ->orderByDesc('start_at')
            ->limit(6)
            ->get();

        $pageData = Page::dataBySlug('eventos');

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

        $userRegistration = null;
        if (auth()->check()) {
            $userRegistration = \App\Models\EventRegistration::where('event_id', $event->id)
                ->where('user_id', auth()->id())
                ->whereIn('status', \App\Models\EventRegistration::COUNTED_STATUSES)
                ->first();
        }

        return view('events.show', compact('event', 'userRegistration'));
    }
}
