<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventMedia;
use App\Services\WatermarkService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GalleryController extends Controller
{
    /**
     * Display gallery media.
     * Members see only their own uploads. Admins see everything.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = EventMedia::with(['event', 'user'])->latest();

        // Permission check: Members only see their own uploads in the management view
        if (!$user->isAdmin()) {
            $query->where('user_id', $user->id);
        }

        if ($request->event_id) {
            $query->where('event_id', $request->event_id);
        }

        $media = $query->paginate(20);

        // Active events for filtering/uploading
        $events = Event::where('published', true)
            ->orderBy('start_at', 'desc')
            ->get();

        return view('panel.gallery.index', compact('media', 'events'));
    }

    /**
     * Upload new media to the gallery.
     */
    public function upload(Request $request, WatermarkService $watermarkService)
    {
        $request->validate([
            'event_id' => 'required|exists:events,id',
            'files' => 'required|array',
            'files.*' => 'required|image|mimes:jpeg,png,jpg,webp|max:10240', // 10MB per file
        ]);

        $event = Event::findOrFail($request->event_id);
        $uploadedCount = 0;

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                try {
                    // Process image with event watermark
                    $path = $watermarkService->processEventImage($file, $event);

                    EventMedia::create([
                        'event_id' => $event->id,
                        'user_id' => auth()->id(),
                        'file_path' => $path,
                        'type' => 'image',
                        'watermarked' => true
                    ]);
                    $uploadedCount++;
                } catch (\Exception $e) {
                    \Log::error("Gallery upload error: " . $e->getMessage());
                    // Fallback to simple store if watermark fails
                    try {
                        $path = $file->store('events/' . $event->id . '/gallery', 'public');
                        EventMedia::create([
                            'event_id' => $event->id,
                            'user_id' => auth()->id(),
                            'file_path' => $path,
                            'type' => 'image',
                            'watermarked' => false
                        ]);
                        $uploadedCount++;
                    } catch (\Exception $e2) {
                        \Log::error("Gallery upload fallback error: " . $e2->getMessage());
                    }
                }
            }
        }

        return back()->with('success', "Sucesso! $uploadedCount foto(s) foram enviadas para a galeria.");
    }

    /**
     * Remove the specified media from storage.
     */
    public function destroy(EventMedia $media)
    {
        $user = auth()->user();

        // Owners can delete their own, Admins can delete anything
        if ($media->user_id !== $user->id && !$user->isAdmin()) {
            return back()->with('error', 'Você não tem permissão para excluir esta mídia.');
        }

        if ($media->file_path && Storage::disk('public')->exists($media->file_path)) {
            Storage::disk('public')->delete($media->file_path);
        }

        $media->delete();

        return back()->with('success', 'Mídia excluída da galeria com sucesso.');
    }
}
