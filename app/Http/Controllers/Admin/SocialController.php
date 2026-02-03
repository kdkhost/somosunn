<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;

class SocialController extends Controller
{
    public function index()
    {
        $posts = Post::with('user')->latest()->paginate(20);
        return view('admin.social.index', compact('posts'));
    }

    public function destroy(Post $post)
    {
        $post->delete();
        return back()->with('success', 'Post removido com sucesso.');
    }
}
