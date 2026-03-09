<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventMedia;
use Illuminate\Http\Request;

class GalleryController extends Controller
{
    /**
     * Display a listing of events that have media.
     */
    public function index()
    {
        // Pega eventos que possuem pelo menos uma mídia, ordenados pelos mais recentes
        $events = Event::has('media')
            ->where('published', true)
            ->with([
                'media' => function ($query) {
                    $query->where('type', 'image')->orderBy('created_at', 'asc');
                }
            ])
            ->orderBy('start_at', 'desc')
            ->paginate(12);

        return view('site.gallery.index', compact('events'));
    }

    /**
     * Display the gallery for a specific event.
     */
    public function show(Event $event)
    {
        if (!$event->published) {
            abort(404);
        }

        $media = $event->media()
            ->orderBy('type', 'asc') // Imagens primeiro, depois vídeos
            ->orderBy('created_at', 'asc')
            ->paginate(24);

        if ($media->isEmpty()) {
            return redirect()->route('gallery.index')->with('info', 'Este evento ainda não possui fotos na galeria.');
        }

        return view('site.gallery.show', compact('event', 'media'));
    }
}
