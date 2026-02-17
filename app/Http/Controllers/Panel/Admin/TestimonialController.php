<?php

namespace App\Http\Controllers\Panel\Admin;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TestimonialController extends Controller
{
    public function index(Request $request)
    {
        $this->ensurePermission('testimonials.view');

        $status = $request->query('status');
        $q = trim((string) $request->query('q', ''));

        $testimonials = Testimonial::query()
            ->with(['user', 'moderator'])
            ->when($status, fn($query) => $query->where('status', $status))
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
            ->paginate(15);

        $testimonials->appends($request->all());

        return view('panel.admin.testimonials.index', compact('testimonials', 'status', 'q'));
    }

    public function edit(Testimonial $testimonial)
    {
        $this->ensurePermission('testimonials.moderate');
        return view('panel.admin.testimonials.form', compact('testimonial'));
    }

    public function update(Request $request, Testimonial $testimonial)
    {
        $this->ensurePermission('testimonials.moderate');

        $data = $request->validate([
            'author_name' => 'nullable|string|max:120',
            'author_title' => 'nullable|string|max:160',
            'rating' => 'nullable|integer|min:1|max:5',
            'content' => 'required|string|min:10',
            'is_featured' => 'nullable|boolean',
        ]);

        $data['is_featured'] = $request->boolean('is_featured');
        $testimonial->update($data);

        return redirect()->route('panel.admin.testimonials.index')->with('success', 'Depoimento atualizado com sucesso.');
    }

    public function approve(Testimonial $testimonial)
    {
        $this->ensurePermission('testimonials.moderate');

        $testimonial->update([
            'status' => 'approved',
            'moderated_by' => Auth::id(),
            'moderated_at' => now(),
            'moderation_notes' => null,
        ]);

        return back()->with('success', 'Depoimento aprovado com sucesso.');
    }

    public function reject(Request $request, Testimonial $testimonial)
    {
        $this->ensurePermission('testimonials.moderate');

        $data = $request->validate([
            'moderation_notes' => 'nullable|string|max:255',
        ]);

        $testimonial->update([
            'status' => 'rejected',
            'moderated_by' => Auth::id(),
            'moderated_at' => now(),
            'moderation_notes' => $data['moderation_notes'] ?? null,
        ]);

        return back()->with('success', 'Depoimento reprovado.');
    }

    public function destroy(Testimonial $testimonial)
    {
        $this->ensurePermission('testimonials.delete');
        $testimonial->delete();
        return back()->with('success', 'Depoimento excluído.');
    }

    protected function ensurePermission(string $perm)
    {
        if (!Auth::user()->isAdmin() && !Auth::user()->hasPermission($perm)) {
            abort(403);
        }
    }
}
