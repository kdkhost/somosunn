<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventMaterial;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

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

        return view('events.index', compact('events', 'featuredEvent', 'otherEvents', 'pastEvents'));
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

        $event->load(['materials' => function ($query) {
            $query->latest('id');
        }]);

        $canDownloadMaterials = Auth::check() && Auth::user()->hasEventAccess($event);

        return view('events.show', compact('event', 'canDownloadMaterials'));
    }

    public function downloadMaterial(Event $event, EventMaterial $material)
    {
        if ((int) $material->event_id !== (int) $event->id) {
            abort(404);
        }

        if (!Auth::check() || !Auth::user()->hasEventAccess($event)) {
            abort(403, 'Voce nao tem permissao para baixar este material.');
        }

        if (!Storage::disk('public')->exists($material->file_path)) {
            abort(404);
        }

        $downloadName = trim((string) $material->file_name) !== ''
            ? $material->file_name
            : basename((string) $material->file_path);

        $headers = [
            'Content-Type' => 'application/octet-stream',
            'X-Content-Type-Options' => 'nosniff',
        ];

        $publicDisk = Storage::disk('public');
        if (method_exists($publicDisk, 'path')) {
            $absolutePath = $publicDisk->path($material->file_path);
            if (is_file($absolutePath)) {
                return response()->download($absolutePath, $downloadName, $headers);
            }
        }

        $stream = $publicDisk->readStream($material->file_path);
        if ($stream === false) {
            abort(404);
        }

        return response()->streamDownload(function () use ($stream) {
            fpassthru($stream);
            if (is_resource($stream)) {
                fclose($stream);
            }
        }, $downloadName, $headers);
    }
}
