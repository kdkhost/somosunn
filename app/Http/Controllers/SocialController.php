<?php

namespace App\Http\Controllers;

use App\Models\Connection;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SocialController extends Controller
{
    public function feed()
    {
        $demoMode = (bool) config('app.demo_mode');

        // Check if social feature is enabled
        $isEnabled = \App\Models\Setting::get('feature_social', '1') === '1';

        if (!$isEnabled) {
            abort(404, 'Comunidade temporariamente indisponível');
        }

        $blockedUserIds = Connection::where(function ($q) {
            $q->where('requester_id', Auth::id())->orWhere('requested_id', Auth::id());
        })->where('status', 'blocked')->pluck('requester_id', 'requested_id')->flatten()->unique()->toArray();

        $posts = Post::with('user')
            ->whereNotIn('user_id', $blockedUserIds)
            ->latest()
            ->paginate(10);

        // If no posts exist, provide demo data
        if ($demoMode && $posts->isEmpty()) {
            $demoPosts = collect([
                (object) [
                    'id' => 1,
                    'content' => 'Bem-vindos à comunidade UNN! 🚀 Aqui você pode compartilhar suas experiências, aprender com outros empreendedores e fazer conexões valiosas.',
                    'user' => (object) ['id' => 0, 'name' => 'UNN Oficial'],
                    'created_at' => now()->subHours(2),
                    'is_demo' => true,
                ],
                (object) [
                    'id' => 2,
                    'content' => 'Dica do dia: Networking não é sobre coletar contatos, é sobre cultivar relacionamentos. Qualidade sempre supera quantidade! 💡',
                    'user' => (object) ['id' => 0, 'name' => 'UNN Academy'],
                    'created_at' => now()->subHours(5),
                    'is_demo' => true,
                ],
                (object) [
                    'id' => 3,
                    'content' => 'Quem está animado para o próximo evento? Me conta nos comentários qual tema você gostaria de ver abordado! 🎯',
                    'user' => (object) ['id' => 0, 'name' => 'Equipe UNN'],
                    'created_at' => now()->subDay(),
                    'is_demo' => true,
                ],
            ]);

            // Create a fake paginator for demo
            $posts = new \Illuminate\Pagination\LengthAwarePaginator(
                $demoPosts,
                $demoPosts->count(),
                10,
                1
            );

            return view('social.feed', ['posts' => $posts, 'isDemo' => true]);
        }

        return view('social.feed', compact('posts'));
    }

    public function profile($username)
    {
        $user = User::findOrFail($username);

        if ($user->role === 'superadmin') {
            abort(404);
        }

        // Privacy check: Hide profile from non-connected users if configured
        // (Assuming user might have a setting 'hide_profile_public' or similar)
        // Or based on the Connection's hide_profile if we want that specifically per-connection?
        // Usually it's a global user setting.
        if (Auth::check() && Auth::id() !== $user->id) {
            $connection = Auth::user()->hasPendingConnectionWith($user->id);
            $isConnected = Auth::user()->isConnectedWith($user->id);

            // If user has a global setting to hide profile from non-friends
            $hideFromPublic = $user->hide_profile ?? false; // Assuming column in users
            if ($hideFromPublic && !$isConnected && !Auth::user()->isAdmin()) {
                return view('social.profile_private', compact('user'));
            }
        }

        $posts = Post::where('user_id', $user->id)->latest()->paginate(10);

        return view('social.profile', compact('user', 'posts'));
    }

    public function storePost(Request $request)
    {
        $validated = $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        $post = Auth::user()->posts()->create([
            'content' => $validated['content'],
            'visibility' => 'public',
        ]);

        // Gamificação: pontuar publicação (se regra existir)
        try {
            (new \App\Services\PointsService())->award(Auth::user(), 'publish', ['post_id' => $post->id]);
        } catch (\Throwable $e) {
            \Log::warning('Falha ao pontuar publicação: ' . $e->getMessage());
        }

        return back()->with('success', 'Post publicado!');
    }
}
