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
use App\Models\PostHide;
use App\Models\PostMedia;
use App\Models\PostReaction;
use App\Models\PostReport;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

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

        $connectedUserIds = Connection::where('status', 'accepted')
            ->where(function ($q) {
                $q->where('requester_id', Auth::id())->orWhere('requested_id', Auth::id());
            })
            ->get()
            ->map(function ($connection) {
                return $connection->requester_id === Auth::id()
                    ? $connection->requested_id
                    : $connection->requester_id;
            })
            ->unique()
            ->values()
            ->toArray();

        $recommendedUsers = collect();
        if (Auth::check()) {
            $excludedIds = array_unique(array_merge([Auth::id()], $blockedUserIds, $connectedUserIds));
            $recommendedQuery = User::whereNotIn('id', $excludedIds)
                ->where('role', '!=', 'superadmin');

            if (!Auth::user()->isAdmin()) {
                $recommendedQuery->where(function ($q) use ($connectedUserIds) {
                    $q->where('hide_profile', false)
                        ->orWhereIn('id', $connectedUserIds);
                });
            }

            $recommendedUsers = $recommendedQuery
                ->inRandomOrder()
                ->limit(5)
                ->get(['id', 'name']);
        }

        $adsEnabled = (string) Setting::get('ads_enabled', '0') === '1';
        $adsCode = (string) Setting::get('ads_code_html', '');

        $hiddenPostIds = PostHide::where('user_id', Auth::id())
            ->pluck('post_id')
            ->all();

        $posts = Post::with(['user', 'sharedTo', 'comments.user', 'reactions', 'media'])
            ->whereNotIn('user_id', $blockedUserIds)
            ->when(!empty($hiddenPostIds), function ($query) use ($hiddenPostIds) {
                $query->whereNotIn('posts.id', $hiddenPostIds);
            })
            ->where(function ($query) use ($connectedUserIds) {
                $query->where('visibility', 'public')
                    ->orWhere('visibility', 'community')
                    ->orWhere(function ($sub) use ($connectedUserIds) {
                        $sub->where('visibility', 'connections')
                            ->whereIn('user_id', $connectedUserIds);
                    })
                    ->orWhere('user_id', Auth::id());
            })
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

            return view('social.feed', [
                'posts' => $posts,
                'isDemo' => true,
                'shareTargets' => User::whereIn('id', $connectedUserIds)->orderBy('name')->get(['id', 'name']),
                'recommendedUsers' => $recommendedUsers,
                'adsEnabled' => $adsEnabled,
                'adsCode' => $adsCode,
            ]);
        }

        return view('social.feed', [
            'posts' => $posts,
            'shareTargets' => User::whereIn('id', $connectedUserIds)->orderBy('name')->get(['id', 'name']),
            'recommendedUsers' => $recommendedUsers,
            'adsEnabled' => $adsEnabled,
            'adsCode' => $adsCode,
        ]);
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

        $postsQuery = Post::with(['user', 'sharedTo', 'comments.user', 'reactions', 'media'])
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhere('shared_to_user_id', $user->id);
            });

        if (Auth::check()) {
            $hiddenPostIds = PostHide::where('user_id', Auth::id())
                ->pluck('post_id')
                ->all();

            if (!empty($hiddenPostIds)) {
                $postsQuery->whereNotIn('posts.id', $hiddenPostIds);
            }
        }
        $postsQuery->latest();

        if (Auth::check() && Auth::id() === $user->id) {
            $posts = $postsQuery->paginate(10);
        } else {
            $isConnected = Auth::check() ? Auth::user()->isConnectedWith($user->id) : false;

            $posts = $postsQuery
                ->where(function ($query) use ($isConnected) {
                    $query->where('visibility', 'public')
                        ->orWhere('visibility', 'community');

                    if ($isConnected) {
                        $query->orWhere('visibility', 'connections');
                    }
                })
                ->paginate(10);
        }

        $shareTargets = [];
        if (Auth::check()) {
            $shareTargets = Connection::where('status', 'accepted')
                ->where(function ($q) {
                    $q->where('requester_id', Auth::id())->orWhere('requested_id', Auth::id());
                })
                ->get()
                ->map(function ($connection) {
                    return $connection->requester_id === Auth::id()
                        ? $connection->requested_id
                        : $connection->requester_id;
                })
                ->unique()
                ->values()
                ->toArray();
        }

        return view('social.profile', [
            'user' => $user,
            'posts' => $posts,
            'shareTargets' => empty($shareTargets)
                ? collect()
                : User::whereIn('id', $shareTargets)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function publicPost(Post $post)
    {
        $post->load(['user', 'media']);

        $imagePath = $post->media->first()?->path;
        $seoImage = (string) (Setting::get('seo_og_image') ?: '');
        if ($seoImage === '') {
            $seoImage = (string) (Setting::get('logo_front') ?: Setting::get('logo_image'));
        }
        if ($seoImage === '') {
            $seoImage = 'img/logo.svg';
        }
        $shareImage = $imagePath ? asset($imagePath) : asset(ltrim($seoImage, '/'));

        $contentText = trim(strip_tags((string) ($post->content ?? '')));
        $shareDescription = Str::limit($contentText, 160);

        return view('social.post_share', [
            'post' => $post,
            'shareImage' => $shareImage,
            'shareDescription' => $shareDescription,
        ]);
    }

    public function storePost(Request $request)
    {
        $validated = $request->validate([
            'content' => 'required|string|max:1000',
            'visibility' => 'required|string|in:public,connections,community',
            'media' => 'nullable|image|max:2048',
        ]);

        $post = Auth::user()->posts()->create([
            'content' => $validated['content'],
            'visibility' => $validated['visibility'],
        ]);

        if ($request->hasFile('media') && $request->file('media')->isValid()) {
            $file = $request->file('media');
            $filename = 'post_' . $post->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $directory = public_path('uploads/imagens/posts');

            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            $file->move($directory, $filename);

            PostMedia::create([
                'post_id' => $post->id,
                'type' => 'image',
                'path' => 'uploads/imagens/posts/' . $filename,
            ]);
        }

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

    public function destroyComment(PostComment $comment)
    {
        $postOwnerId = $comment->post?->user_id;

        if ($comment->user_id !== Auth::id() && $postOwnerId !== Auth::id() && !Auth::user()->isAdmin()) {
            abort(403);
        }

        PostComment::where('parent_id', $comment->id)->delete();
        $comment->delete();

        return back()->with('success', 'Comentario removido com sucesso.');
    }

    public function sharePost(Post $post)
    {
        $sharedContent = "Compartilhou de {$post->user->name}:\n\n" . (string) $post->content;

        Auth::user()->posts()->create([
            'content' => $sharedContent,
            'visibility' => 'community',
        ]);

        return back()->with('success', 'Post compartilhado!');
    }

    public function sharePostToUser(Request $request, Post $post)
    {
        $validated = $request->validate([
            'target_user_id' => 'required|integer|exists:users,id',
            'message' => 'nullable|string|max:500',
        ]);

        $targetUserId = (int) $validated['target_user_id'];

        if (!Auth::user()->isConnectedWith($targetUserId)) {
            abort(403);
        }

        $message = trim((string) ($validated['message'] ?? ''));
        $sharedContent = "Compartilhou de {$post->user->name}:\n\n" . (string) $post->content;
        $content = $message !== '' ? ($message . "\n\n" . $sharedContent) : $sharedContent;

        Auth::user()->posts()->create([
            'content' => $content,
            'visibility' => 'connections',
            'shared_to_user_id' => $targetUserId,
        ]);

        return back()->with('success', 'Post compartilhado na linha do tempo do membro.');
    }

    public function reportPost(Request $request, Post $post)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        $report = PostReport::create([
            'post_id' => $post->id,
            'user_id' => Auth::id(),
            'reason' => $validated['reason'],
            'status' => 'open',
            'ip_address' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
        ]);

        $adminEmails = User::whereIn('role', ['admin', 'superadmin'])
            ->whereNotNull('email')
            ->pluck('email')
            ->unique()
            ->values()
            ->all();

        if (!empty($adminEmails)) {
            try {
                Mail::raw(
                    "Denuncia de post #{$post->id}\nAutor: {$post->user->name}\nDenunciante: " . Auth::user()->name . "\nMotivo: {$report->reason}",
                    function ($message) use ($adminEmails) {
                        $message->to($adminEmails)->subject('Denuncia de post na comunidade');
                    }
                );
            } catch (\Throwable $e) {
                Log::warning('Falha ao notificar admins sobre denuncia de post: ' . $e->getMessage());
            }
        }

        return back()->with('success', 'Denuncia enviada. Obrigado por ajudar a comunidade.');
    }

    public function hidePost(Post $post)
    {
        $hidden = PostHide::where('user_id', Auth::id())
            ->where('post_id', $post->id)
            ->first();

        if (!$hidden) {
            PostHide::create([
                'post_id' => $post->id,
                'user_id' => Auth::id(),
            ]);
        }

        return back()->with('success', 'Postagem ocultada.');
    }

    public function unpublishPost(Post $post)
    {
        if ($post->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
            abort(403);
        }

        $post->update([
            'visibility' => 'community',
        ]);

        return back()->with('success', 'Postagem despublicada.');
    }

    public function destroyPost(Post $post)
    {
        if ($post->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
            abort(403);
        }

        $post->delete();

        return back()->with('success', 'Publicacao removida com sucesso.');
    }
}
