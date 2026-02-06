<?php

namespace App\Http\Controllers;

use App\Models\Testimonial;
use Illuminate\Http\Request;

class TestimonialController extends Controller
{
    public function store(Request $request)
    {
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Faça login para enviar um depoimento.');
        }

        $data = $request->validate([
            'content' => 'required|string|min:20|max:2000',
            'rating' => 'nullable|integer|min:1|max:5',
        ]);

        $user = auth()->user();

        $authorTitleParts = array_filter([
            trim((string) ($user->occupation ?? '')),
            trim((string) ($user->company ?? '')),
        ]);

        Testimonial::create([
            'user_id' => $user->id,
            'author_name' => $user->name,
            'author_title' => $authorTitleParts ? implode(' • ', $authorTitleParts) : null,
            'rating' => $data['rating'] ?? null,
            'content' => trim($data['content']),
            'status' => 'pending',
        ]);

        return back()->with('success', 'Depoimento enviado! Ele será publicado após moderação.');
    }
}

