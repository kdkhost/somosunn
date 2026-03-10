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
