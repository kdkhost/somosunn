<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventResource;
use App\Models\Event;
use Illuminate\Http\Request;

class EventApiController extends Controller
{
    public function index(Request $request)
    {
        $status = (string) $request->query('status', 'upcoming');
        $perPage = (int) $request->query('per_page', 20);
        $perPage = max(1, min($perPage, 100));

        $query = Event::query()->where('published', true);

        if ($status === 'past') {
            $query->where('start_at', '<', now())->orderBy('start_at', 'desc');
        } elseif ($status === 'all') {
            $query->orderBy('start_at', 'asc');
        } else {
            $query->where('start_at', '>=', now())->orderBy('start_at', 'asc');
        }

        $events = $query->paginate($perPage);

        return EventResource::collection($events);
    }

    public function show(Event $event)
    {
        if (!$event->published) {
            abort(404);
        }

        return new EventResource($event);
    }
}
