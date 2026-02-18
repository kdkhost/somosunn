<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    public function index()
    {
        $courses = Auth::user()->wishlist()->paginate(12);
        return view('panel.wishlist.index', compact('courses'));
    }

    public function toggle(Course $course)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $user->wishlist()->toggle($course->id);

        $isWishlisted = $user->wishlist()->where('course_id', $course->id)->exists();

        return response()->json([
            'success' => true,
            'is_wishlisted' => $isWishlisted,
            'message' => $isWishlisted ? 'Curso adicionado à sua lista.' : 'Curso removido da sua lista.'
        ]);
    }
}
