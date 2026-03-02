<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\ItemReview;
use App\Models\Mentorship;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ItemReviewController extends Controller
{
    public function storeCourse(Request $request, Course $course)
    {
        if (!Auth::user()->hasCourseAccess($course)) {
            return back()->with('error', 'Você precisa ter acesso ao curso para enviar uma avaliação.');
        }

        return $this->storeForReviewable($request, $course, 'Avaliação do curso enviada para moderação.');
    }

    public function storeMentorship(Request $request, Mentorship $mentorship)
    {
        return $this->storeForReviewable($request, $mentorship, 'Avaliação da mentoria enviada para moderação.');
    }

    private function storeForReviewable(Request $request, Model $reviewable, string $successMessage)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Faça login para enviar sua avaliação.');
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|min:10|max:2000',
        ]);

        $existingReview = ItemReview::query()
            ->where('user_id', Auth::id())
            ->where('reviewable_type', $reviewable->getMorphClass())
            ->where('reviewable_id', $reviewable->getKey())
            ->first();

        ItemReview::query()->updateOrCreate(
            [
                'user_id' => Auth::id(),
                'reviewable_type' => $reviewable->getMorphClass(),
                'reviewable_id' => $reviewable->getKey(),
            ],
            [
                'rating' => (int) $validated['rating'],
                'comment' => trim((string) $validated['comment']),
                'status' => 'pending',
                'moderated_by' => null,
                'moderated_at' => null,
                'moderation_notes' => null,
            ]
        );

        // Gamificação: pontuar nova avaliação (somente na criação, não na atualização)
        if (!$existingReview) {
            try {
                (new \App\Services\PointsService())->award(
                    Auth::user(),
                    'review',
                    ['reviewable_type' => $reviewable->getMorphClass(), 'reviewable_id' => $reviewable->getKey()]
                );
            } catch (\Throwable $e) {
                \Log::warning('Falha ao pontuar review: ' . $e->getMessage());
            }
        }

        // Notificar o criador (instrutor/mentor)
        $creator = null;
        if ($reviewable instanceof Course) {
            $creator = $reviewable->creator;
        } elseif ($reviewable instanceof Mentorship) {
            $creator = $reviewable->user; // Mentorship might use 'user_id' directly or 'user' relation
        }

        if ($creator && $creator->id !== Auth::id()) {
            $typeName = ($reviewable instanceof Course) ? 'curso' : 'mentoria';
            $creator->notify(new \App\Notifications\AppNotification([
                'message' => Auth::user()->name . " enviou uma nova avaliação para o seu {$typeName}: \"{$reviewable->title}\".",
                'type' => 'ReviewSubmitted',
                'action_url' => '#', // Admin ou Painel do Instrutor se houver
                'action_label' => 'Ver feedback'
            ]));
        }

        $message = $existingReview
            ? 'Sua avaliação foi atualizada e voltou para moderação.'
            : $successMessage;

        return back()->with('success', $message);
    }
}
