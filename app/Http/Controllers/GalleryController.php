<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventMedia;
use App\Support\UploadStorage;
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
        $allEvents = Event::has('media')
            ->where('published', true)
            ->with(['galleryCoverMedia', 'media'])
            ->withCount('media')
            ->orderBy('start_at', 'desc')
            ->get()
            ->filter(fn (Event $event) => $event->media->contains(
                fn (EventMedia $media) => $media->hasAccessibleFile()
            ))
            ->values();

        $events = $this->paginateCollection($allEvents, 12, 'page');

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

        $registeredPhotoCount = (clone $photosQuery)->count();
        $registeredVideoCount = (clone $videosQuery)->count();
        $registeredTotalMedia = $registeredPhotoCount + $registeredVideoCount;

        $photos = $this->paginateAccessibleMedia($photosQuery, 18, 'fotos', $request);
        $videos = $this->paginateAccessibleMedia($videosQuery, 8, 'videos', $request);

        $photoCount = $photos->total();
        $videoCount = $videos->total();
        $totalMedia = $photoCount + $videoCount;

        if ($totalMedia === 0) {
            if ($registeredTotalMedia > 0) {
                Log::warning('Galeria pública possui registros sem arquivos acessíveis.', [
                    'event_id' => $event->id,
                    'registered_media' => $registeredTotalMedia,
                ]);
            }

            return redirect()
                ->route('gallery.index')
                ->with('info', 'Este evento ainda não possui mídias disponíveis na galeria.');
        }

        $featuredPhoto = $photos->first();

        $relatedEvents = Event::has('media')
            ->where('published', true)
            ->whereKeyNot($event->getKey())
            ->with(['galleryCoverMedia', 'media'])
            ->withCount('media')
            ->orderBy('start_at', 'desc')
            ->get()
            ->filter(fn (Event $relatedEvent) => $relatedEvent->media->contains(
                fn (EventMedia $media) => $media->hasAccessibleFile()
            ))
            ->take(3)
            ->values();

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

    private function paginateAccessibleMedia($query, int $perPage, string $pageName, Request $request): LengthAwarePaginator
    {
        $items = $query
            ->get()
            ->filter(fn (EventMedia $media) => UploadStorage::exists($media->file_path))
            ->values();

        return $this->paginateCollection($items, $perPage, $pageName, $request);
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
