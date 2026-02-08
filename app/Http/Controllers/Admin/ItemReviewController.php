<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\ItemReview;
use App\Models\Mentorship;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ItemReviewController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            abort(403);
        }

        $status = trim((string) $request->query('status', ''));
        $type = trim((string) $request->query('type', ''));
        $q = trim((string) $request->query('q', ''));

        $items = $this->visibleReviewsQuery($user)
            ->with([
                'user:id,name,email,photo',
                'moderator:id,name',
                'reviewable',
            ])
            ->when($status !== '', function (Builder $query) use ($status) {
                $query->where('status', $status);
            })
            ->when($type === 'course', function (Builder $query) {
                $query->where('reviewable_type', Course::class);
            })
            ->when($type === 'mentorship', function (Builder $query) {
                $query->where('reviewable_type', Mentorship::class);
            })
            ->when($q !== '', function (Builder $query) use ($q) {
                $query->where(function (Builder $sub) use ($q) {
                    $sub->where('comment', 'like', '%' . $q . '%')
                        ->orWhereHas('user', function (Builder $u) use ($q) {
                            $u->where('name', 'like', '%' . $q . '%')
                                ->orWhere('email', 'like', '%' . $q . '%');
                        })
                        ->orWhereHasMorph('reviewable', [Course::class], function (Builder $r) use ($q) {
                            $r->where('title', 'like', '%' . $q . '%');
                        })
                        ->orWhereHasMorph('reviewable', [Mentorship::class], function (Builder $r) use ($q) {
                            $r->where('title', 'like', '%' . $q . '%');
                        });
                });
            })
            ->orderByRaw("FIELD(status, 'pending', 'rejected', 'approved')")
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.reviews.index', compact('items', 'status', 'type', 'q'));
    }

    public function approve(ItemReview $review)
    {
        $review = $this->resolveVisibleReviewOrFail($review->id);

        $review->update([
            'status' => 'approved',
            'moderated_by' => Auth::id(),
            'moderated_at' => now(),
            'moderation_notes' => null,
        ]);

        return back()->with('success', 'Avaliação aprovada com sucesso.');
    }

    public function reject(Request $request, ItemReview $review)
    {
        $review = $this->resolveVisibleReviewOrFail($review->id);

        $data = $request->validate([
            'moderation_notes' => 'nullable|string|max:255',
        ]);

        $review->update([
            'status' => 'rejected',
            'moderated_by' => Auth::id(),
            'moderated_at' => now(),
            'moderation_notes' => $data['moderation_notes'] ?? null,
        ]);

        return back()->with('success', 'Avaliação recusada.');
    }

    public function destroy(ItemReview $review)
    {
        $review = $this->resolveVisibleReviewOrFail($review->id);
        $review->delete();

        return back()->with('success', 'Avaliação removida.');
    }

    private function resolveVisibleReviewOrFail(int $id): ItemReview
    {
        $user = Auth::user();
        if (!$user) {
            abort(403);
        }

        $review = $this->visibleReviewsQuery($user)->where('id', $id)->first();
        if (!$review) {
            abort(403, 'Você não tem permissão para moderar esta avaliação.');
        }

        return $review;
    }

    private function visibleReviewsQuery($user): Builder
    {
        $query = ItemReview::query();
        if ($user->isAdmin()) {
            return $query;
        }

        return $query->where(function (Builder $scoped) use ($user) {
            $scoped
                ->where(function (Builder $courseScope) use ($user) {
                    $courseScope
                        ->where('reviewable_type', Course::class)
                        ->whereHasMorph('reviewable', [Course::class], function (Builder $courseQuery) use ($user) {
                            $courseQuery->where('user_id', $user->id);
                        });
                })
                ->orWhere(function (Builder $mentorshipScope) use ($user) {
                    $mentorshipScope
                        ->where('reviewable_type', Mentorship::class)
                        ->whereHasMorph('reviewable', [Mentorship::class], function (Builder $mentorshipQuery) use ($user) {
                            $mentorshipQuery->where('mentor_id', $user->id);
                        });
                });
        });
    }
}
