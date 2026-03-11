<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventMedia;
use App\Services\WatermarkService;
use App\Support\UploadStorage;
use Illuminate\Http\Request;

class GalleryController extends Controller
{
    /**
     * Display gallery media.
     * Members see only their own uploads. Admins see everything.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $baseQuery = EventMedia::query();

        if (!$user->isAdmin()) {
            $baseQuery->where('user_id', $user->id);
        }

        $query = (clone $baseQuery)->with(['event', 'user'])->latest();

        if ($request->filled('event_id')) {
            $query->where('event_id', $request->event_id);
        }

        $media = $query->paginate(20);

        $events = Event::where('published', true)
            ->orderBy('start_at', 'desc')
            ->get();

        $selectedEvent = $request->filled('event_id')
            ? $events->firstWhere('id', (int) $request->event_id)
            : null;

        $stats = [
            'visible_total' => (clone $baseQuery)->count(),
            'event_coverage' => (clone $baseQuery)->distinct()->count('event_id'),
            'my_uploads' => EventMedia::query()->where('user_id', $user->id)->count(),
        ];

        return view('panel.gallery.index', compact('media', 'events', 'selectedEvent', 'stats'));
    }

    /**
     * Upload new media to the gallery.
     */
    public function upload(Request $request, WatermarkService $watermarkService)
    {
        $request->validate([
            'event_id' => 'required|exists:events,id',
            'files' => 'required|array',
            'files.*' => 'required|image|mimes:jpeg,png,jpg,webp|max:10240',
        ]);

        $event = Event::findOrFail($request->event_id);
        $uploadedCount = 0;

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                try {
                    $path = $watermarkService->processEventImage($file, $event);

                    EventMedia::create([
                        'event_id' => $event->id,
                        'user_id' => auth()->id(),
                        'file_path' => $path,
                        'type' => 'image',
                        'watermarked' => true,
                    ]);

                    $uploadedCount++;
                } catch (\Throwable $exception) {
                    \Log::error('Gallery upload error: ' . $exception->getMessage());

                    try {
                        $path = UploadStorage::storeUploadedFile($file, 'events/' . $event->id . '/gallery');

                        EventMedia::create([
                            'event_id' => $event->id,
                            'user_id' => auth()->id(),
                            'file_path' => $path,
                            'type' => 'image',
                            'watermarked' => false,
                        ]);

                        $uploadedCount++;
                    } catch (\Throwable $fallbackException) {
                        \Log::error('Gallery upload fallback error: ' . $fallbackException->getMessage());
                    }
                }
            }
        }

        if ($uploadedCount === 0) {
            $message = 'Nenhuma foto conseguiu ser enviada para a galeria.';

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                ], 422);
            }

            return back()->with('error', $message);
        }

        $message = "Sucesso! {$uploadedCount} foto(s) foram enviadas para a galeria.";

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'uploaded_count' => $uploadedCount,
            ]);
        }

        return back()->with('success', $message);
    }

    /**
     * Remove the specified media from storage.
     */
    public function destroy(EventMedia $media)
    {
        $user = auth()->user();

        if ($media->user_id !== $user->id && !$user->isAdmin()) {
            return back()->with('error', 'Voce nao tem permissao para excluir esta midia.');
        }

        if ($media->file_path) {
            UploadStorage::delete($media->file_path);
        }

        $media->delete();

        return back()->with('success', 'Midia excluida da galeria com sucesso.');
    }
}
