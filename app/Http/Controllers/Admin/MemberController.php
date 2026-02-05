<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Post;

class MemberController extends Controller
{
    public function socialFeed()
    {
        $posts = Post::with('user')->latest()->paginate(10);
        return view('social.feed', [
            'posts' => $posts,
            'extends' => 'admin.layouts.app'
        ]);
    }

    public function portal()
    {
        return view('site.portal', [
            'extends' => 'admin.layouts.app'
        ]);
    }
}
