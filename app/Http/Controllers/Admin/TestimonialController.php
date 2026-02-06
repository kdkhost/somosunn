<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use Illuminate\Http\Request;

class TestimonialController extends Controller
{
    public function index(Request $request)
    {
        $this->ensureCanView();

        $status = $request->query('status');
        $q = trim((string) $request->query('q', ''));

        $testimonials = Testimonial::query()
            ->with(['user', 'moderator'])
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('author_name', 'like', '%' . $q . '%')
                        ->orWhere('author_title', 'like', '%' . $q . '%')
                        ->orWhere('content', 'like', '%' . $q . '%');
                });
            })
            ->orderByRaw("FIELD(status, 'pending', 'approved', 'rejected')")
            ->orderByDesc('is_featured')
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.testimonials.index', compact('testimonials', 'status', 'q'));
    }

    public function edit(Testimonial $testimonial)
    {
        $this->ensureCanModerate();

        return view('admin.testimonials.form', compact('testimonial'));
    }

    public function update(Request $request, Testimonial $testimonial)
    {
        $this->ensureCanModerate();

        $data = $request->validate([
            'author_name' => 'nullable|string|max:120',
            'author_title' => 'nullable|string|max:160',
            'rating' => 'nullable|integer|min:1|max:5',
            'content' => 'required|string|min:10',
            'is_featured' => 'nullable|boolean',
        ]);

        $data['is_featured'] = $request->boolean('is_featured');

        $testimonial->update($data);

        return redirect()->route('admin.testimonials.index')->with('success', 'Depoimento atualizado');
    }

    public function approve(Testimonial $testimonial)
    {
        $this->ensureCanModerate();

        $testimonial->update([
            'status' => 'approved',
            'moderated_by' => auth()->id(),
            'moderated_at' => now(),
            'moderation_notes' => null,
        ]);

        return back()->with('success', 'Depoimento aprovado');
    }

    public function reject(Request $request, Testimonial $testimonial)
    {
        $this->ensureCanModerate();

        $data = $request->validate([
            'moderation_notes' => 'nullable|string|max:255',
        ]);

        $testimonial->update([
            'status' => 'rejected',
            'moderated_by' => auth()->id(),
            'moderated_at' => now(),
            'moderation_notes' => $data['moderation_notes'] ?? null,
        ]);

        return back()->with('success', 'Depoimento recusado');
    }

    public function destroy(Testimonial $testimonial)
    {
        $this->ensureCanDelete();

        $testimonial->delete();

        return back()->with('success', 'Depoimento removido');
    }

    protected function ensureCanView(): void
    {
        $user = auth()->user();
        if (!$user) {
            abort(403);
        }

        if (!$user->hasPermission('testimonials.view') && !$user->hasPermission('testimonials.moderate')) {
            abort(403, 'Permissão negada.');
        }
    }

    protected function ensureCanModerate(): void
    {
        $user = auth()->user();
        if (!$user || !$user->hasPermission('testimonials.moderate')) {
            abort(403, 'Permissão negada.');
        }
    }

    protected function ensureCanDelete(): void
    {
        $user = auth()->user();
        if (!$user || !$user->hasPermission('testimonials.delete')) {
            abort(403, 'Permissão negada.');
        }
    }
}

