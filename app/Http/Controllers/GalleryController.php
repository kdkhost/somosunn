<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventMedia;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

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

        $allPhotos = $photosQuery->get();
        $allVideos = $videosQuery->get();

        $registeredTotalMedia = $allPhotos->count() + $allVideos->count();
        $availableTotalMedia = $allPhotos
            ->filter(fn (EventMedia $media) => $media->hasAccessibleFile())
            ->count()
            + $allVideos
                ->filter(fn (EventMedia $media) => $media->hasAccessibleFile())
                ->count();

        if ($registeredTotalMedia > 0 && $availableTotalMedia === 0) {
            Log::warning('Galeria publica possui registros sem arquivos acessiveis.', [
                'event_id' => $event->id,
                'registered_media' => $registeredTotalMedia,
            ]);
        }

        $photos = $this->paginateCollection($allPhotos, 18, 'fotos', $request);
        $videos = $this->paginateCollection($allVideos, 8, 'videos', $request);

        $photoCount = $photos->total();
        $videoCount = $videos->total();
        $totalMedia = $photoCount + $videoCount;

        if ($totalMedia === 0) {
            return redirect()
                ->route('gallery.index')
                ->with('info', 'Este evento ainda nao possui midias disponiveis na galeria.');
        }

        $featuredPhoto = $allPhotos->first(fn (EventMedia $media) => $media->hasAccessibleFile()) ?: $photos->first();

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

    private function paginateCollection($items, int $perPage, string $pageName, ?Request $request = null): LengthAwarePaginator
    {
        $page = LengthAwarePaginator::resolveCurrentPage($pageName);
        $request ??= request();

        return new LengthAwarePaginator(
            $items->forPage($page, $perPage)->values(),
            $items->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'pageName' => $pageName,
                'query' => $request->query(),
            ]
        );
    }
}
