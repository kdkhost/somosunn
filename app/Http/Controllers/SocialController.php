<?php
/**
 * =============================================================================
 * AVISO LEGAL DE DIREITOS AUTORAIS E PROPRIEDADE INTELECTUAL
 * =============================================================================
 *
 * © 2026 Marcelo Brad - Todos os direitos reservados.
 *
 * AUTOR:
 * marcelo-brad rj
 *
 * CONTATO:
 * Tel: +55 21 98132-5441
 * Email: contato@kdkhost.com.br
 * Telegram: @MARCELO_BRAD
 * Instagram: @marcelobradrj
 * WhatsApp: +55 21 98132-5441
 *
 * -----------------------------------------------------------------------------
 * DIREITOS AUTORAIS:
 * Este software, incluindo seu código-fonte, estrutura, banco de dados,
 * layout, funcionalidades, lógica de programação e documentação associada,
 * é protegido pelas leis brasileiras de direitos autorais (Lei nº 9.610/98)
 * e demais legislações internacionais aplicáveis.
 *
 * -----------------------------------------------------------------------------
 * PROPRIEDADE INTELECTUAL:
 * Todo o conteúdo deste sistema é de propriedade exclusiva do autor,
 * sendo proibida a reprodução total ou parcial, modificação,
 * engenharia reversa, redistribuição, sublicenciamento,
 * comercialização ou qualquer forma de exploração sem autorização
 * expressa e formal do titular dos direitos.
 *
 * -----------------------------------------------------------------------------
 * LICENÇA DE USO:
 * Este sistema é licenciado, não vendido.
 * O uso é restrito ao cliente contratante conforme contrato firmado.
 * É vedado o compartilhamento, revenda ou distribuição a terceiros
 * sem autorização prévia e documentada.
 *
 * -----------------------------------------------------------------------------
 * RESPONSABILIDADE:
 * Alterações realizadas por terceiros não autorizados anulam qualquer
 * responsabilidade do autor sobre falhas, vulnerabilidades ou danos
 * decorrentes do uso indevido do sistema.
 *
 * -----------------------------------------------------------------------------
 * SEGURANÇA E MONITORAMENTO:
 * Este software pode conter mecanismos de identificação,
 * rastreamento de licença e validação de integridade para
 * proteção contra uso não autorizado e pirataria.
 *
 * -----------------------------------------------------------------------------
 * PENALIDADES:
 * O uso indevido ou não autorizado poderá resultar em medidas legais
 * cabíveis nas esferas civil e criminal, incluindo indenizações por
 * perdas e danos.
 *
 * =============================================================================
 */

namespace App\Http\Controllers;

use App\Models\Connection;
use App\Models\Post;
use App\Models\PostComment;
use App\Models\PostReaction;
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

        $posts = Post::with(['user', 'comments.user', 'reactions'])
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
                    'reactions' => collect(),
                    'comments' => collect(),
                ],
                (object) [
                    'id' => 2,
                    'content' => 'Dica do dia: Networking não é sobre coletar contatos, é sobre cultivar relacionamentos. Qualidade sempre supera quantidade! 💡',
                    'user' => (object) ['id' => 0, 'name' => 'UNN Academy'],
                    'created_at' => now()->subHours(5),
                    'is_demo' => true,
                    'reactions' => collect(),
                    'comments' => collect(),
                ],
                (object) [
                    'id' => 3,
                    'content' => 'Quem está animado para o próximo evento? Me conta nos comentários qual tema você gostaria de ver abordado! 🎯',
                    'user' => (object) ['id' => 0, 'name' => 'Equipe UNN'],
                    'created_at' => now()->subDay(),
                    'is_demo' => true,
                    'reactions' => collect(),
                    'comments' => collect(),
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

        $posts = Post::with(['user', 'comments.user', 'reactions'])
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(10);

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

    public function toggleReaction(Request $request, Post $post)
    {
        $validated = $request->validate([
            'type' => 'nullable|string|in:like,love,haha,wow,sad,angry',
        ]);

        $type = $validated['type'] ?? 'like';

        $existing = PostReaction::where('post_id', $post->id)
            ->where('user_id', Auth::id())
            ->first();

        if ($existing) {
            $existing->delete();
        } else {
            PostReaction::create([
                'post_id' => $post->id,
                'user_id' => Auth::id(),
                'type' => $type,
            ]);
        }

        return back();
    }

    public function storeComment(Request $request, Post $post)
    {
        $validated = $request->validate([
            'content' => 'required|string|max:1000',
            'parent_id' => 'nullable|integer|exists:post_comments,id',
        ]);

        PostComment::create([
            'post_id' => $post->id,
            'user_id' => Auth::id(),
            'parent_id' => $validated['parent_id'] ?? null,
            'content' => $validated['content'],
        ]);

        return back();
    }

    public function sharePost(Post $post)
    {
        $sharedContent = "Compartilhou de {$post->user->name}:\n\n" . (string) $post->content;

        Auth::user()->posts()->create([
            'content' => $sharedContent,
            'visibility' => 'public',
        ]);

        return back()->with('success', 'Post compartilhado!');
    }
}
