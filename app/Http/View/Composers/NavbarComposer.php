<?php

namespace App\Http\View\Composers;

use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use App\Models\Connection;
use App\Models\Message;
use App\Models\ItemReview;
use App\Models\Testimonial;
use App\Models\Course;
use App\Models\Mentorship;

class NavbarComposer
{
    public function compose(View $view)
    {
        if (Auth::check()) {
            $user = Auth::user();
            
            // Pending Connections (excluding blocks if we had them, but for now just pending)
            $pendingConnectionsQ = Connection::with('requester')
                ->where('requested_id', $user->id)
                ->where('status', 'pending');
                
            $pendingConnectionsCount = $pendingConnectionsQ->count();
            $pendingConnections = $pendingConnectionsQ->latest()->take(5)->get();

            // Unread Messages Grouped by User
            $unreadMessagesGroups = collect();
            $unreadMessagesCount = 0;

            try {
                $allUnread = Message::where('user_id', '!=', $user->id)
                    ->whereHas('conversation', function($q) use ($user) {
                        $q->whereHas('users', function($u) use ($user) {
                            $u->where('users.id', $user->id);
                        });
                    })
                    ->whereNull('read_at')
                    ->with('user')
                    ->get();
                
                $unreadMessagesCount = $allUnread->count();
                
                $unreadMessagesGroups = $allUnread->groupBy('user_id')->map(function ($msgs) {
                    return (object) [
                        'user' => $msgs->first()->user,
                        'count' => $msgs->count(),
                        'latest' => $msgs->sortByDesc('created_at')->first()
                    ];
                });

            } catch (\Exception $e) {
                $unreadMessagesCount = 0;
            }

            // Pending Reviews - for content owners
            $pendingReviewsCount = 0;
            $pendingReviews = collect();
            try {
                // Get IDs of courses and mentorships owned by user
                $userCourseIds = Course::where('user_id', $user->id)->pluck('id')->toArray();
                $userMentorshipIds = Mentorship::where('mentor_id', $user->id)->pluck('id')->toArray();

                $reviewsQuery = ItemReview::where('status', 'pending')
                    ->where(function ($q) use ($userCourseIds, $userMentorshipIds, $user) {
                        // Reviews on user's courses
                        $q->where(function ($sub) use ($userCourseIds) {
                            $sub->where('reviewable_type', 'App\\Models\\Course')
                                ->whereIn('reviewable_id', $userCourseIds);
                        });
                        // Reviews on user's mentorships
                        $q->orWhere(function ($sub) use ($userMentorshipIds) {
                            $sub->where('reviewable_type', 'App\\Models\\Mentorship')
                                ->whereIn('reviewable_id', $userMentorshipIds);
                        });
                        // Admins see all pending reviews
                        if ($user->isAdmin()) {
                            $q->orWhereNotNull('id');
                        }
                    })
                    ->with(['user', 'reviewable']);

                $pendingReviewsCount = $reviewsQuery->count();
                $pendingReviews = $reviewsQuery->latest()->take(5)->get();
            } catch (\Exception $e) {
                // Silently fail
            }

            // Pending Testimonials - for admins only
            $pendingTestimonialsCount = 0;
            $pendingTestimonials = collect();
            try {
                if ($user->isAdmin() || $user->hasPermission('testimonials.moderate')) {
                    $testimonialsQuery = Testimonial::where('status', 'pending')
                        ->with('user');
                    $pendingTestimonialsCount = $testimonialsQuery->count();
                    $pendingTestimonials = $testimonialsQuery->latest()->take(5)->get();
                }
            } catch (\Exception $e) {
                // Silently fail
            }

            // Total notification count for bell
            $totalNotificationsCount = $pendingConnectionsCount + $pendingReviewsCount + $pendingTestimonialsCount;

            $view->with(compact(
                'pendingConnectionsCount', 
                'pendingConnections', 
                'unreadMessagesCount', 
                'unreadMessagesGroups',
                'pendingReviewsCount',
                'pendingReviews',
                'pendingTestimonialsCount',
                'pendingTestimonials',
                'totalNotificationsCount'
            ));
        }
    }
}
