<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\ShareRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShareRequestController extends Controller
{
    /**
     * Lista as solicitações de compartilhamento pendentes do usuário logado.
     */
    public function index()
    {
        $requests = ShareRequest::pending()
            ->where('to_user_id', Auth::id())
            ->with(['fromUser', 'post.user', 'post.media'])
            ->latest()
            ->paginate(20);

        return view('social.share_requests', compact('requests'));
    }

    /**
     * Aprova a solicitação: cria o post na timeline do destinatário.
     */
    public function approve(ShareRequest $shareRequest)
    {
        abort_unless($shareRequest->to_user_id === Auth::id(), 403);
        abort_unless($shareRequest->isPending(), 422, 'Esta solicitação não está mais pendente.');

        $post = $shareRequest->post;
        $fromUser = $shareRequest->fromUser;

        $sharedContent = "Compartilhou de {$post->user->name}:\n\n" . (string) $post->content;
        $content = $shareRequest->message
            ? $shareRequest->message . "\n\n" . $sharedContent
            : $sharedContent;

        Auth::user()->posts()->create([
            'content'            => $content,
            'visibility'         => 'connections',
            'shared_to_user_id'  => Auth::id(),
        ]);

        $shareRequest->update(['status' => 'approved']);

        // Notificar quem enviou o compartilhamento
        $fromUser?->notify(new \App\Notifications\AppNotification([
            'message'      => Auth::user()->name . ' aprovou seu compartilhamento.',
            'type'         => 'ShareApproved',
            'action_url'   => route('social.profile', Auth::user()->username ?? Auth::id()),
            'action_label' => 'Ver timeline',
        ]));

        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Compartilhamento aprovado!']);
        }

        return back()->with('success', 'Compartilhamento aprovado e publicado na sua timeline!');
    }

    /**
     * Rejeita a solicitação.
     */
    public function reject(ShareRequest $shareRequest)
    {
        abort_unless($shareRequest->to_user_id === Auth::id(), 403);
        abort_unless($shareRequest->isPending(), 422, 'Esta solicitação não está mais pendente.');

        $shareRequest->update(['status' => 'rejected']);

        // Notificar quem enviou
        $shareRequest->fromUser?->notify(new \App\Notifications\AppNotification([
            'message'      => Auth::user()->name . ' recusou seu compartilhamento.',
            'type'         => 'ShareRejected',
            'action_url'   => route('social.feed'),
            'action_label' => 'Ir para o feed',
        ]));

        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Solicitação recusada.']);
        }

        return back()->with('success', 'Solicitação de compartilhamento recusada.');
    }

    /**
     * Retorna a contagem de solicitações pendentes (polling).
     */
    public function pendingCount()
    {
        $count = ShareRequest::pending()
            ->where('to_user_id', Auth::id())
            ->count();

        return response()->json(['count' => $count]);
    }
}
