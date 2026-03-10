<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GalleryController extends Controller
{
    /**
     * Display a listing of all gallery media.
     */
    public function index(Request $request)
    {
        $query = EventMedia::with(['event', 'user'])->latest();

        if ($request->event_id) {
            $query->where('event_id', $request->event_id);
        }

        if ($request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        $media = $query->paginate(24);
        $events = Event::orderBy('start_at', 'desc')->get();

        return view('admin.gallery.index', compact('media', 'events'));
    }

    /**
     * Upload new media to the gallery.
     */
    public function upload(Request $request, \App\Services\WatermarkService $watermarkService)
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
                        'watermarked' => true
                    ]);
                    $uploadedCount++;
                } catch (\Exception $e) {
                    \Log::error("Admin gallery upload error: " . $e->getMessage());
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
                        \Log::error("Admin gallery upload fallback error: " . $e2->getMessage());
                    }
                }
            }
        }

        return back()->with('success', "Sucesso! $uploadedCount foto(s) enviadas para a galeria.");
    }

    /**
     * Remove the specified media from storage.
     */
    public function destroy(EventMedia $media)
    {
        // Admin routes are already protected by admin/superadmin middleware
        if ($media->file_path && Storage::disk('public')->exists($media->file_path)) {
            Storage::disk('public')->delete($media->file_path);
        }

        $media->delete();

        return back()->with('success', 'Mídia removida com sucesso!');
    }
}
