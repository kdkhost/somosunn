<?php

namespace App\Http\Controllers;

use App\Models\ItemReview;
use Illuminate\Http\Request;
use App\Models\Mentorship;
use Illuminate\Support\Facades\Auth;

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

        return view('mentorships.show', compact('mentorship', 'reviews', 'reviewsCount', 'reviewsAvg', 'myReview'));
    }
}
