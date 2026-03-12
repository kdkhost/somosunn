<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;

class GalleryController extends Controller
{
    /**
     * Display a listing of events that have media.
     */
    public function index()
    {
        $events = Event::has('media')
            ->where('published', true)
            ->with('galleryCoverMedia')
            ->withCount('media')
            ->orderBy('start_at', 'desc')
            ->paginate(12);

        return view('site.gallery.index', compact('events'));
    }

    /**
     * Display the gallery for a specific event.
     */
    public function show(Request $request, Event $event)
    {
        if (!$event->published) {
            abort(404);
        }

        $event->loadMissing('galleryCoverMedia');

        $photosQuery = $event->media()
            ->with('user')
            ->where('type', 'image')
            ->latest();

        $videosQuery = $event->media()
            ->with('user')
            ->where('type', 'video')
            ->latest();

        $photoCount = (clone $photosQuery)->count();
        $videoCount = (clone $videosQuery)->count();
        $totalMedia = $photoCount + $videoCount;

        if ($totalMedia === 0) {
            return redirect()
                ->route('gallery.index')
                ->with('info', 'Este evento ainda nao possui midias na galeria.');
        }

        $photos = $photosQuery
            ->paginate(18, ['*'], 'fotos')
            ->appends($request->query());

        $videos = $videosQuery
            ->paginate(8, ['*'], 'videos')
            ->appends($request->query());

        $featuredPhoto = $photos->first();

        $relatedEvents = Event::has('media')
            ->where('published', true)
            ->whereKeyNot($event->getKey())
            ->with('galleryCoverMedia')
            ->withCount('media')
            ->orderBy('start_at', 'desc')
            ->take(3)
            ->get();

        return view('site.gallery.show', compact(
            'event',
            'photos',
            'videos',
            'photoCount',
            'videoCount',
            'totalMedia',
            'featuredPhoto',
            'relatedEvents'
        ));
    }
}
