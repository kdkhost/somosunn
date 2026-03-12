<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventMedia;
use App\Services\WatermarkService;
use App\Support\UploadStorage;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class GalleryController extends Controller
{
    /**
     * Display a listing of all gallery media.
     */
    public function index(Request $request)
    {
        $query = EventMedia::with(['event.galleryCoverMedia', 'user'])->latest();

        if ($request->filled('event_id')) {
            $query->where('event_id', $request->event_id);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $media = $query->paginate(24);
        $events = Event::with('galleryCoverMedia')
            ->withCount('media')
            ->orderBy('start_at', 'desc')
            ->get();
        $selectedEvent = $request->filled('event_id')
            ? $events->firstWhere('id', (int) $request->event_id)
            : null;

        return view('admin.gallery.index', compact('media', 'events', 'selectedEvent'));
    }

    /**
     * Upload new media to the gallery.
     */
    public function store(Request $request, WatermarkService $watermarkService)
    {
        return $this->upload($request, $watermarkService);
    }

    public function upload(Request $request, WatermarkService $watermarkService)
    {
        $request->validate([
            'event_id' => 'required|exists:events,id',
        ]);

        $event = Event::findOrFail($request->integer('event_id'));

        return app(EventMediaController::class)->store($request, $event, $watermarkService);
    }

    public function uploadCover(Request $request, Event $event, WatermarkService $watermarkService)
    {
        $request->validate([
            'cover_image' => 'required|image|mimes:jpeg,png,jpg,webp|max:10240',
        ]);

        $oldCustomCover = $event->gallery_cover_image;

        try {
            $path = $watermarkService->processStorageImage(
                $request->file('cover_image'),
                'events/' . $event->id . '/gallery/covers',
                null,
                ['prefix' => 'gallery-cover']
            );
        } catch (\Throwable $exception) {
            \Log::error('Admin gallery custom cover upload error: ' . $exception->getMessage(), [
                'event_id' => $event->id,
            ]);

            $path = UploadStorage::storeUploadedFile(
                $request->file('cover_image'),
                'events/' . $event->id . '/gallery/covers',
                null,
                ['prefix' => 'gallery-cover']
            );
        }

        if (!blank($oldCustomCover) && $oldCustomCover !== $path) {
            UploadStorage::delete($oldCustomCover);
        }

        $event->forceFill([
            'gallery_cover_image' => $path,
            'gallery_cover_media_id' => null,
        ])->save();

        return $this->coverResponse($request, $event, 'Capa personalizada do album atualizada com sucesso.');
    }

    public function setCoverFromMedia(Request $request, EventMedia $media)
    {
        $media->loadMissing('event');
        $event = $media->event;

        abort_if(!$event, 404);

        if ($media->type !== 'image') {
            throw ValidationException::withMessages([
                'media' => 'A capa do album precisa ser uma imagem.',
            ]);
        }

        if (!blank($event->gallery_cover_image)) {
            UploadStorage::delete($event->gallery_cover_image);
        }

        $event->forceFill([
            'gallery_cover_image' => null,
            'gallery_cover_media_id' => $media->id,
        ])->save();

        return $this->coverResponse($request, $event, 'Capa do album definida a partir da foto selecionada.');
    }

    public function clearCover(Request $request, Event $event)
    {
        if (!blank($event->gallery_cover_image)) {
            UploadStorage::delete($event->gallery_cover_image);
        }

        $event->forceFill([
            'gallery_cover_image' => null,
            'gallery_cover_media_id' => null,
        ])->save();

        return $this->coverResponse($request, $event, 'A capa personalizada do album foi removida.');
    }

    /**
     * Remove the specified media from storage.
     */
    public function destroy(Request $request, EventMedia $media)
    {
        $media->loadMissing('event');

        if ($media->event && (int) $media->event->gallery_cover_media_id === (int) $media->id) {
            $media->event->forceFill(['gallery_cover_media_id' => null])->save();
        }

        UploadStorage::delete($media->file_path);
        $media->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Midia removida com sucesso.',
            ]);
        }

        return back()->with('success', 'Midia removida com sucesso!');
    }

    /**
     * @param  array<int, EventMedia>  $mediaItems
     * @return array<int, array<string, mixed>>
     */
    private function serializeMediaCollection(array $mediaItems): array
    {
        return array_map(function (EventMedia $media) {
            $media->loadMissing(['event', 'user']);
            $event = $media->event;

            return [
                'id' => $media->id,
                'event_id' => $media->event_id,
                'type' => $media->type,
                'url' => UploadStorage::url($media->file_path),
                'watermarked' => (bool) $media->watermarked,
                'event_title' => optional($event)->title ?: 'Evento sem titulo',
                'owner_name' => optional($media->user)->name ?: 'Sistema',
                'uploaded_at' => $media->created_at?->format('d/m/Y H:i'),
                'is_cover' => $event
                    ? (blank($event->gallery_cover_image) && (int) $event->gallery_cover_media_id === (int) $media->id)
                    : false,
                'set_cover_url' => route('admin.gallery.cover.media', $media),
                'delete_url' => route('admin.gallery.destroy', $media),
            ];
        }, $mediaItems);
    }

    /**
     * @param  array<int, array<string, mixed>>  $mediaPayload
     * @param  array<int, string>  $failedFiles
     */
    private function galleryResponse(
        Request $request,
        bool $success,
        string $message,
        array $mediaPayload,
        int $status,
        array $failedFiles = []
    ) {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => $success,
                'message' => $message,
                'uploaded_count' => count($mediaPayload),
                'failed_files' => $failedFiles,
                'media' => $mediaPayload,
            ], $status);
        }

        return back()->with($success ? 'success' : 'error', $message);
    }

    private function coverResponse(Request $request, Event $event, string $message)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'cover_url' => $event->fresh()->gallery_cover_url,
                'event_id' => $event->id,
            ]);
        }

        return back()->with('success', $message);
    }
}
