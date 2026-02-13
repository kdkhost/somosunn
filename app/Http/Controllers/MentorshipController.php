<?php

namespace App\Http\Controllers;

use App\Models\ItemReview;
use App\Models\MentorshipMaterial;
use App\Models\Mentorship;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MentorshipController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Pega mentorias disponíveis
        $mentorships = Mentorship::latest()->paginate(12);
        return view('mentorships.index', compact('mentorships'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Mentorship $mentorship)
    {
        $mentorship->load(['materials' => function ($query) {
            $query->latest('id');
        }]);

        $approvedReviewsQuery = ItemReview::query()
            ->where('reviewable_type', Mentorship::class)
            ->where('reviewable_id', $mentorship->id)
            ->where('status', 'approved');

        $reviews = (clone $approvedReviewsQuery)
            ->with('user:id,name,photo')
            ->latest('id')
            ->limit(6)
            ->get();

        $reviewsCount = (clone $approvedReviewsQuery)->count();
        $reviewsAvg = (clone $approvedReviewsQuery)->avg('rating');
        $reviewsAvg = $reviewsAvg !== null ? round((float) $reviewsAvg, 1) : null;

        $myReview = null;
        if (Auth::check()) {
            $myReview = ItemReview::query()
                ->where('user_id', Auth::id())
                ->where('reviewable_type', Mentorship::class)
                ->where('reviewable_id', $mentorship->id)
                ->first();
        }

        $canDownloadMaterials = Auth::check() && Auth::user()->hasMentorshipAccess($mentorship);

        return view('mentorships.show', compact(
            'mentorship',
            'reviews',
            'reviewsCount',
            'reviewsAvg',
            'myReview',
            'canDownloadMaterials'
        ));
    }

    public function downloadMaterial(Mentorship $mentorship, MentorshipMaterial $material)
    {
        if ((int) $material->mentorship_id !== (int) $mentorship->id) {
            abort(404);
        }

        if (!Auth::check() || !Auth::user()->hasMentorshipAccess($mentorship)) {
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
