<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TestimonialController extends Controller
{
    // ─── Listagem ─────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $this->ensureCanView();

        $status = $request->query('status');
        $source = $request->query('source');
        $active = $request->query('active'); // '' | '1' | '0'
        $q = trim((string) $request->query('q', ''));

        $testimonials = Testimonial::query()
            ->with(['user', 'moderator'])
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($source, fn ($query) => $query->where('source', $source))
            ->when($active !== '' && $active !== null, fn ($query) => $query->where('is_active', (bool) $active))
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

        return view('admin.testimonials.index', compact('testimonials', 'status', 'source', 'active', 'q'));
    }

    // ─── Criar (admin cria manualmente) ──────────────────────────────────────

    public function create()
    {
        $this->ensureCanModerate();

        $testimonial = new Testimonial();
        $users = User::select('id', 'name', 'email')->orderBy('name')->get();

        return view('admin.testimonials.form', compact('testimonial', 'users'));
    }

    public function store(Request $request)
    {
        $this->ensureCanModerate();

        $data = $request->validate([
            'user_id'      => 'nullable|exists:users,id',
            'author_name'  => 'nullable|string|max:120',
            'author_title' => 'nullable|string|max:160',
            'rating'       => 'nullable|integer|min:1|max:5',
            'content'      => 'required|string|min:10',
            'is_featured'  => 'nullable|boolean',
            'is_active'    => 'nullable|boolean',
        ], [
            'content.required' => 'O texto do depoimento é obrigatório.',
            'content.min'      => 'O depoimento precisa ter ao menos 10 caracteres.',
        ]);

        // Se vinculado a um usuário, preencher nome/título automaticamente
        if (!empty($data['user_id']) && empty($data['author_name'])) {
            $user = User::find($data['user_id']);
            $data['author_name']  = $user?->name;
            $data['author_title'] = $user?->job_title ?? $user?->bio_short ?? null;
        }

        $data['is_featured'] = $request->boolean('is_featured');
        $data['is_active']   = $request->boolean('is_active', true);
        $data['source']      = 'manual';
        $data['status']      = 'approved'; // admin criando = já aprovado
        $data['moderated_by'] = auth()->id();
        $data['moderated_at'] = now();

        Testimonial::create($data);

        return response()->json(['redirect' => route('admin.testimonials.index'), 'message' => 'Depoimento criado e publicado.']);
    }

    // ─── Editar ───────────────────────────────────────────────────────────────

    public function edit(Testimonial $testimonial)
    {
        $this->ensureCanModerate();

        $users = User::select('id', 'name', 'email')->orderBy('name')->get();

        return view('admin.testimonials.form', compact('testimonial', 'users'));
    }

    public function update(Request $request, Testimonial $testimonial)
    {
        $this->ensureCanModerate();

        $data = $request->validate([
            'user_id'      => 'nullable|exists:users,id',
            'author_name'  => 'nullable|string|max:120',
            'author_title' => 'nullable|string|max:160',
            'rating'       => 'nullable|integer|min:1|max:5',
            'content'      => 'required|string|min:10',
            'is_featured'  => 'nullable|boolean',
            'is_active'    => 'nullable|boolean',
        ]);

        $data['is_featured'] = $request->boolean('is_featured');
        $data['is_active']   = $request->boolean('is_active');

        $testimonial->update($data);

        return response()->json(['redirect' => route('admin.testimonials.index'), 'message' => 'Depoimento atualizado.']);
    }

    // ─── Toggle ativo/inativo (PATCH) ─────────────────────────────────────────

    public function toggle(Testimonial $testimonial)
    {
        $this->ensureCanModerate();

        $testimonial->update(['is_active' => !$testimonial->is_active]);

        $msg = $testimonial->is_active ? 'Depoimento ativado.' : 'Depoimento desativado.';

        return response()->json(['is_active' => $testimonial->is_active, 'message' => $msg]);
    }

    // ─── Moderação ────────────────────────────────────────────────────────────

    public function approve(Testimonial $testimonial)
    {
        $this->ensureCanModerate();

        $testimonial->update([
            'status'           => 'approved',
            'is_active'        => true,
            'moderated_by'     => auth()->id(),
            'moderated_at'     => now(),
            'moderation_notes' => null,
        ]);

        return response()->json(['ok' => true, 'message' => 'Depoimento aprovado.']);
    }

    public function reject(Request $request, Testimonial $testimonial)
    {
        $this->ensureCanModerate();

        $data = $request->validate([
            'moderation_notes' => 'nullable|string|max:255',
        ]);

        $testimonial->update([
            'status'           => 'rejected',
            'is_active'        => false,
            'moderated_by'     => auth()->id(),
            'moderated_at'     => now(),
            'moderation_notes' => $data['moderation_notes'] ?? null,
        ]);

        return response()->json(['ok' => true, 'message' => 'Depoimento recusado.']);
    }

    // ─── Excluir ──────────────────────────────────────────────────────────────

    public function destroy(Testimonial $testimonial)
    {
        $this->ensureCanDelete();

        $testimonial->delete();

        return response()->json(['ok' => true, 'message' => 'Depoimento removido.']);
    }

    // ─── Importar do Google Meu Negócio ───────────────────────────────────────

    /**
     * Importa reviews via Google Places API (Details endpoint).
     * Requer configurações:
     *   - google_places_api_key  (Settings)
     *   - google_business_place_id (Settings)
     *
     * Retorna apenas os 5 mais recentes (limitação da API pública).
     */
    public function importGoogle()
    {
        $this->ensureCanModerate();

        $apiKey  = Setting::get('google_places_api_key');
        $placeId = Setting::get('google_business_place_id');

        if (!$apiKey || !$placeId) {
            return back()->with('error',
                'Configure a Chave da API do Google e o ID do Local (Place ID) em Configurações > Integrações.'
            );
        }

        try {
            $url = 'https://maps.googleapis.com/maps/api/place/details/json';
            $response = Http::timeout(15)->get($url, [
                'place_id' => $placeId,
                'fields'   => 'reviews',
                'key'      => $apiKey,
                'language' => 'pt-BR',
            ]);

            if (!$response->ok()) {
                return back()->with('error', 'Falha ao contatar a API do Google. Código: ' . $response->status());
            }

            $reviews = $response->json('result.reviews', []);

            if (empty($reviews)) {
                return back()->with('info', 'Nenhum review encontrado para este local.');
            }

            $imported = 0;
            $skipped  = 0;

            foreach ($reviews as $review) {
                $externalId = $review['author_url'] ?? null; // URL única do autor como ID externo

                // Evita duplicatas pelo external_id
                if ($externalId && Testimonial::where('external_id', $externalId)->exists()) {
                    $skipped++;
                    continue;
                }

                Testimonial::create([
                    'source'       => 'google',
                    'external_id'  => $externalId,
                    'author_name'  => $review['author_name'] ?? 'Usuário Google',
                    'author_title' => 'Google Meu Negócio',
                    'rating'       => $review['rating'] ?? null,
                    'content'      => $review['text'] ?? '',
                    'avatar_url'   => $review['profile_photo_url'] ?? null,
                    'status'       => 'approved',
                    'is_active'    => true,
                    'is_featured'  => false,
                    'moderated_by' => auth()->id(),
                    'moderated_at' => now(),
                ]);

                $imported++;
            }

            $msg = "Importação concluída: {$imported} novo(s), {$skipped} ignorado(s) (já existentes).";
            return back()->with('success', $msg);
        } catch (\Throwable $e) {
            Log::error('Erro ao importar do Google: ' . $e->getMessage());
            return back()->with('error', 'Erro ao importar: ' . $e->getMessage());
        }
    }

    // ─── Autorizações ─────────────────────────────────────────────────────────

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

