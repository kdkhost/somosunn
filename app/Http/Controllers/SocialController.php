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
        $posts = Post::latest()->paginate(10);
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
