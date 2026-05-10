<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Magazine;
use App\Support\UploadStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MagazineController extends Controller
{
    public function index(Request $request)
    {
        $query = Magazine::query()->with('creator')->latest();

        if (!Auth::user()->isAdmin()) {
            $query->where('user_id', Auth::id());
        }

        $q = trim((string) $request->input('q', ''));
        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('title', 'like', '%' . $q . '%')
                    ->orWhere('edition', 'like', '%' . $q . '%');
            });
        }

        $magazines = $query->paginate(15)->withQueryString();

        return view('admin.magazines.index', compact('magazines', 'q'));
    }

    public function create()
    {
        $this->ensureCanCreate();
        $magazine = new Magazine();
        return view('admin.magazines.form', compact('magazine'));
    }

    public function store(Request $request)
    {
        $this->ensureCanCreate();

        // Uploads grandes (ate 100 MB) precisam de mais tempo/memoria
        @ini_set('memory_limit', '512M');
        @set_time_limit(300);

        $data = $this->validateData($request);

        $data['user_id'] = Auth::id();

        // Thumbnail
        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = UploadStorage::storeUploadedFile(
                $request->file('thumbnail'),
                'magazines/thumbs',
                null,
                ['prefix' => 'magazine-thumb']
            );
        }

        // PDF (watermark DESLIGADO — PDFs nao sao imagens)
        if ($request->hasFile('pdf_file')) {
            $file = $request->file('pdf_file');
            // IMPORTANTE: pegar o tamanho ANTES do storeUploadedFile, pois ele move o arquivo tmp
            $data['file_size_kb'] = (int) round($file->getSize() / 1024);
            $data['pdf_file']     = UploadStorage::storeUploadedFile(
                $file,
                'magazines/pdfs',
                null,
                ['watermark' => false, 'prefix' => 'magazine-pdf']
            );
        }

        $magazine = Magazine::create($data);

        return redirect()
            ->route('panel.admin.magazines.index')
            ->with('success', 'Revista criada com sucesso.');
    }

    public function edit(Magazine $magazine)
    {
        $this->ensureCanManage($magazine);
        return view('admin.magazines.form', compact('magazine'));
    }

    public function update(Request $request, Magazine $magazine)
    {
        $this->ensureCanManage($magazine);

        @ini_set('memory_limit', '512M');
        @set_time_limit(300);

        $data = $this->validateData($request, $magazine);

        if ($request->hasFile('thumbnail')) {
            $this->deleteFileIfExists($magazine->thumbnail);
            $data['thumbnail'] = UploadStorage::storeUploadedFile(
                $request->file('thumbnail'),
                'magazines/thumbs',
                null,
                ['prefix' => 'magazine-thumb']
            );
        }

        if ($request->hasFile('pdf_file')) {
            $this->deleteFileIfExists($magazine->pdf_file);
            $file = $request->file('pdf_file');
            // IMPORTANTE: pegar o tamanho ANTES do storeUploadedFile, pois ele move o arquivo tmp
            $data['file_size_kb'] = (int) round($file->getSize() / 1024);
            $data['pdf_file']     = UploadStorage::storeUploadedFile(
                $file,
                'magazines/pdfs',
                null,
                ['watermark' => false, 'prefix' => 'magazine-pdf']
            );
        }

        $magazine->update($data);

        return redirect()
            ->route('panel.admin.magazines.index')
            ->with('success', 'Revista atualizada com sucesso.');
    }

    public function destroy(Magazine $magazine)
    {
        $this->ensureCanManage($magazine);

        $magazine->delete(); // soft delete — mantem arquivos

        return redirect()->back()->with('success', 'Revista removida.');
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    protected function validateData(Request $request, ?Magazine $magazine = null): array
    {
        $pdfRule = $magazine ? 'nullable|file|mimes:pdf|max:102400' : 'required|file|mimes:pdf|max:102400';

        return $request->validate([
            'title'             => 'required|string|max:255',
            'category'          => 'nullable|string|max:80',
            'edition'           => 'nullable|string|max:60',
            'published_at'      => 'nullable|date',
            'short_description' => 'nullable|string|max:500',
            'full_description'  => 'nullable|string',
            'thumbnail'         => 'nullable|image|max:10240',
            'pdf_file'          => $pdfRule, // 100 MB
            'pages_count'       => 'nullable|integer|min:1|max:2000',
            'is_featured'       => 'nullable|boolean',
            'allow_download'    => 'nullable|boolean',
            'enable_sound'      => 'nullable|boolean',
            'status'            => 'required|in:draft,published,archived',
            'visibility'        => 'required|in:public,members,interest',
        ]);
    }

    protected function ensureCanCreate(): void
    {
        $user = Auth::user();
        if (!$user) abort(403);

        // Admin, Superadmin, ou usuario com feature "magazines.publish" (Editor de Revistas)
        if ($user->isAdmin()) {
            return;
        }
        if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            return;
        }
        if ($user->canAccessFeature('magazines.publish') || $user->canAccessFeature('magazines_create')) {
            return;
        }

        abort(403, 'Voce precisa ter a permissao "Publicar revistas (Editor)" ativa para publicar revistas.');
    }

    protected function ensureCanManage(Magazine $magazine): void
    {
        $user = Auth::user();
        if (!$user) abort(403);
        if ($user->isAdmin()) return;
        if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) return;
        if ($magazine->isOwnedBy($user->id)) return;
        abort(403);
    }

    protected function deleteFileIfExists(?string $path): void
    {
        if (empty($path)) return;
        try {
            // Tenta disco public; se nao existir, ignora
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
                return;
            }
            $full = public_path($path);
            if (is_file($full)) {
                @unlink($full);
            }
        } catch (\Throwable $e) {
            // silencioso
        }
    }
}
