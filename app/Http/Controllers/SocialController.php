<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SocialController extends Controller
{
    public function feed()
    {
        // Check if social feature is enabled
        $isEnabled = \App\Models\Setting::get('feature_social', '1') === '1';
        
        if (!$isEnabled) {
            abort(404, 'Comunidade temporariamente indisponível');
        }

        $posts = Post::with('user')->latest()->paginate(10);
        
        // If no posts exist, provide demo data
        if ($posts->isEmpty()) {
            $demoPosts = collect([
                (object)[
                    'id' => 1,
                    'content' => 'Bem-vindos à comunidade UNN! 🚀 Aqui você pode compartilhar suas experiências, aprender com outros empreendedores e fazer conexões valiosas.',
                    'user' => (object)['id' => 0, 'name' => 'UNN Oficial'],
                    'created_at' => now()->subHours(2),
                    'is_demo' => true,
                ],
                (object)[
                    'id' => 2,
                    'content' => 'Dica do dia: Networking não é sobre coletar contatos, é sobre cultivar relacionamentos. Qualidade sempre supera quantidade! 💡',
                    'user' => (object)['id' => 0, 'name' => 'UNN Academy'],
                    'created_at' => now()->subHours(5),
                    'is_demo' => true,
                ],
                (object)[
                    'id' => 3,
                    'content' => 'Quem está animado para o próximo evento? Me conta nos comentários qual tema você gostaria de ver abordado! 🎯',
                    'user' => (object)['id' => 0, 'name' => 'Equipe UNN'],
                    'created_at' => now()->subDay(),
                    'is_demo' => true,
                ],
            ]);
            
            // Create a fake paginator for demo
            $posts = new \Illuminate\Pagination\LengthAwarePaginator(
                $demoPosts, $demoPosts->count(), 10, 1
            );
            
            return view('social.feed', ['posts' => $posts, 'isDemo' => true]);
        }

        return view('social.feed', compact('posts'));
    }

    public function profile($username)
    {
        // Assuming username is id for simplicity or adding username column
        // Standard user model usually has id, name, email. 
        // We'll use ID for now or check if username exists.
        
        $user = User::findOrFail($username); // Using ID for scaffold.
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

        return back()->with('success', 'Post publicado!');
    }
}
