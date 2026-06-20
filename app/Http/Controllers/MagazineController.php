<?php

namespace App\Http\Controllers;

use App\Models\Magazine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MagazineController extends Controller
{
    /**
     * Listagem pública de revistas para visitantes e membros.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $q        = trim((string) $request->input('q', ''));
        $category = trim((string) $request->input('category', ''));

        $query = Magazine::query()
            ->visibleTo($user)
            ->with('creator');

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('title', 'like', '%' . $q . '%')
                    ->orWhere('edition', 'like', '%' . $q . '%')
                    ->orWhere('short_description', 'like', '%' . $q . '%');
            });
        }

        if ($category !== '') {
            $query->where('category', $category);
        }

        $perPage = max(4, min(50, (int) \App\Models\Setting::get('magazines_per_page', 10)));

        $magazines = $query
            ->orderByDesc('is_featured')
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        $categories = Magazine::query()
            ->where('status', 'published')
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        $hasNewsInterest = Magazine::userHasNewsInterest($user);

        return view('magazines.index', compact('magazines', 'categories', 'q', 'category', 'hasNewsInterest'));
    }

    /**
     * Visualiza uma revista específica no leitor interativo.
     */
    public function show(Magazine $magazine)
    {
        $user = Auth::user();

        // Visibilidade
        if (!$this->canView($magazine, $user)) {
            abort(403, 'Você precisa marcar "Notícias" como interesse no seu perfil para acessar esta revista.');
        }

        $magazine->increment('views_count');

        $related = Magazine::query()
            ->visibleTo($user)
            ->where('id', '!=', $magazine->id)
            ->when($magazine->category, fn($q) => $q->where('category', $magazine->category))
            ->orderByDesc('published_at')
            ->limit(4)
            ->get();

        // Escolhe o leitor interativo conforme a configuração administrativa.
        $engine = \App\Models\Setting::get('magazine_flipbook_engine', 'dearflip');
        $viewName = $engine === 'stpageflip' ? 'magazines.show-stpageflip' : 'magazines.show';

        return view($viewName, compact('magazine', 'related'));
    }

    protected function canView(Magazine $magazine, $user): bool
    {
        if (!$magazine->isPublished()) {
            if ($user && ($user->isAdmin() || $magazine->isOwnedBy($user->id))) {
                return true;
            }
            return false;
        }

        if ($magazine->visibility === 'public') {
            return true;
        }

        if (!$user) {
            return false;
        }

        if ($user->isAdmin() || $magazine->isOwnedBy($user->id)) {
            return true;
        }

        if ($magazine->visibility === 'members') {
            return true;
        }

        // visibility = interest
        return Magazine::userHasNewsInterest($user);
    }
}
