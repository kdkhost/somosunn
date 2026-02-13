<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mentorship;
use App\Models\MentorshipMaterial;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class MentorshipController extends Controller
{
    public function index(Request $request)
    {
        $this->ensurePermission('mentorships.view');

        $user = Auth::user();
        $query = Mentorship::query()->with('mentor')->latest('id');

        if (!$this->canManageAllMentorships()) {
            $query->where('mentor_id', $user->id);
        }

        $search = trim((string) $request->query('q', ''));
        if ($search !== '') {
            $query->where(function ($sub) use ($search) {
                $sub->where('title', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        $items = $query->paginate(20)->withQueryString();

        return view('admin.mentorships.index', compact('items', 'search'));
    }

    public function available(Request $request)
    {
        // Any logged in member can view available mentorships
        $query = Mentorship::query()->with('mentor')->latest('id');

        $search = trim((string) $request->query('q', ''));
        if ($search !== '') {
            $query->where(function ($sub) use ($search) {
                $sub->where('title', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        $items = $query->paginate(12)->withQueryString();

        return view('admin.mentorships.available', compact('items', 'search'));
    }

    public function create()
    {
        $this->ensurePermission('mentorships.create');

        $mentorship = new Mentorship();
        $mentors = $this->mentorOptions();

        return view('admin.mentorships.form', compact('mentorship', 'mentors'));
    }

    public function store(Request $request)
    {
        $this->ensurePermission('mentorships.create');

        if ($request->has('flash_sale_ends_at') && $request->input('flash_sale_ends_at')) {
            $request->merge([
                'flash_sale_ends_at' => str_replace('T', ' ', (string) $request->input('flash_sale_ends_at')),
            ]);
        }

        $data = $this->validatedData($request, true);
        $data['mentor_id'] = $this->resolveMentorId($request, $data['mentor_id'] ?? null);
        $data['schedule'] = $this->parseSchedule($request->input('schedule_json'));
        $data['is_certificate_enabled'] = $request->boolean('is_certificate_enabled');

        if ($request->hasFile('image') && !Schema::hasColumn('mentorships', 'image')) {
            return back()->with('error', 'Seu banco de dados está desatualizado: falta a coluna mentorships.image. Atualize o código e rode: php artisan migrate')->withInput();
        }

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $fileName = 'mentorship_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/mentorship-images'), $fileName);
            $data['image'] = 'uploads/mentorship-images/' . $fileName;
        }

        if ($request->hasFile('certificate_bg')) {
            $file = $request->file('certificate_bg');
            $fileName = 'cert_bg_m_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/certificates'), $fileName);
            $data['certificate_bg'] = 'uploads/certificates/' . $fileName;
        }

        if ($request->hasFile('instructor_signature')) {
            $file = $request->file('instructor_signature');
            $fileName = 'sig_m_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/signatures'), $fileName);
            $data['instructor_signature'] = 'uploads/signatures/' . $fileName;
        }

        if ($request->has('certificate_settings')) {
            $data['certificate_settings'] = is_string($request->certificate_settings) ? json_decode($request->certificate_settings, true) : $request->certificate_settings;
        }

        Mentorship::create($data);

        return redirect()->route('admin.mentorships.index')->with('success', 'Mentoria criada com sucesso.');
    }

    public function edit(Mentorship $mentorship)
    {
        $this->ensurePermission('mentorships.edit');
        $this->ensureOwnership($mentorship);

        $mentors = $this->mentorOptions();

        return view('admin.mentorships.form', compact('mentorship', 'mentors'));
    }

    public function show(Mentorship $mentorship)
    {
        $this->ensurePermission('mentorships.view');
        $this->ensureOwnership($mentorship);

        return redirect()->route('admin.mentorships.edit', $mentorship);
    }

    public function update(Request $request, Mentorship $mentorship)
    {
        $this->ensurePermission('mentorships.edit');
        $this->ensureOwnership($mentorship);

        if ($request->has('flash_sale_ends_at') && $request->input('flash_sale_ends_at')) {
            $request->merge([
                'flash_sale_ends_at' => str_replace('T', ' ', (string) $request->input('flash_sale_ends_at')),
            ]);
        }

        $data = $this->validatedData($request);
        $data['mentor_id'] = $this->resolveMentorId($request, $data['mentor_id'] ?? null);
        $data['schedule'] = $this->parseSchedule($request->input('schedule_json'));
        $data['is_certificate_enabled'] = $request->boolean('is_certificate_enabled');

        if (($request->hasFile('image') || $request->boolean('remove_image')) && !Schema::hasColumn('mentorships', 'image')) {
            $message = 'Seu banco de dados está desatualizado: falta a coluna mentorships.image. Atualize o código e rode: php artisan migrate';

            if ($request->ajax() || $request->wantsJson() || $request->expectsJson()) {
                return response()->json(['status' => 'error', 'message' => $message], 422);
            }

            return back()->with('error', $message)->withInput();
        }

        if ($request->boolean('remove_image')) {
            $this->deletePublicImageIfExists($mentorship->image);
            $data['image'] = null;
        }

        if ($request->hasFile('image')) {
            $this->deletePublicImageIfExists($mentorship->image);

            $file = $request->file('image');
            $fileName = 'mentorship_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/mentorship-images'), $fileName);
            $data['image'] = 'uploads/mentorship-images/' . $fileName;
        }

        if ($request->hasFile('certificate_bg')) {
            $file = $request->file('certificate_bg');
            $fileName = 'cert_bg_m_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/certificates'), $fileName);
            $data['certificate_bg'] = 'uploads/certificates/' . $fileName;
        }

        if ($request->hasFile('instructor_signature')) {
            $file = $request->file('instructor_signature');
            $fileName = 'sig_m_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/signatures'), $fileName);
            $data['instructor_signature'] = 'uploads/signatures/' . $fileName;
        }

        if ($request->has('certificate_settings')) {
            $data['certificate_settings'] = is_string($request->certificate_settings) ? json_decode($request->certificate_settings, true) : $request->certificate_settings;
        }

        $mentorship->update($data);

        if ($request->ajax()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('admin.mentorships.index')->with('success', 'Mentoria atualizada com sucesso.');
    }

    public function destroy(Mentorship $mentorship)
    {
        $this->ensurePermission('mentorships.delete');
        $this->ensureOwnership($mentorship);

        $mentorship->delete();

        return redirect()->route('admin.mentorships.index')->with('success', 'Mentoria removida com sucesso.');
    }

    public function uploadMaterial(Request $request, Mentorship $mentorship)
    {
        $this->ensurePermission('mentorships.edit');
        $this->ensureOwnership($mentorship);

        set_time_limit(0);
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', 0);

        $policy = $this->resolveMaterialUploadPolicy();
        $maxValidationKb = max(1, max($policy['document_max_mb'], $policy['video_max_mb'])) * 1024;
        $mimesRule = !empty($policy['allowed_extensions'])
            ? ('|mimes:' . implode(',', $policy['allowed_extensions']))
            : '';

        $request->validate([
            'file' => 'required|file|max:' . $maxValidationKb . $mimesRule,
            'name' => 'nullable|string|max:255',
        ]);

        $file = $request->file('file');
        $extension = strtolower((string) $file->getClientOriginalExtension());

        if (!empty($policy['allowed_extensions']) && !in_array($extension, $policy['allowed_extensions'], true)) {
            return response()->json([
                'error' => 'Formato de arquivo nao permitido pela politica ativa.',
            ], 422);
        }

        $isVideo = in_array($extension, $policy['video_extensions'], true);
        $maxAllowedBytes = max(1, $isVideo ? $policy['video_max_mb'] : $policy['document_max_mb']) * 1024 * 1024;

        if ((int) $file->getSize() > $maxAllowedBytes) {
            return response()->json([
                'error' => 'Arquivo excede o limite de tamanho permitido.',
            ], 422);
        }

        $path = $file->store('mentorship-materials', 'public');
        $customName = trim((string) $request->input('name', ''));
        $finalName = $customName !== '' ? $customName : $file->getClientOriginalName();

        $material = $mentorship->materials()->create([
            'file_path' => $path,
            'file_name' => $finalName,
            'file_type' => $extension,
            'file_size' => $file->getSize(),
            'created_by' => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'material' => $material,
            'download_url' => route('mentorships.materials.download', [$mentorship, $material]),
        ]);
    }

    public function renameMaterial(Request $request, Mentorship $mentorship, MentorshipMaterial $material)
    {
        $this->ensurePermission('mentorships.edit');
        $this->ensureOwnership($mentorship);

        if ((int) $material->mentorship_id !== (int) $mentorship->id) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $material->update([
            'file_name' => trim((string) $validated['name']),
        ]);

        return response()->json([
            'success' => true,
            'material' => $material->fresh(),
        ]);
    }

    public function deleteMaterial(Mentorship $mentorship, MentorshipMaterial $material)
    {
        $this->ensurePermission('mentorships.edit');
        $this->ensureOwnership($mentorship);

        if ((int) $material->mentorship_id !== (int) $mentorship->id) {
            abort(404);
        }

        if (Storage::disk('public')->exists($material->file_path)) {
            Storage::disk('public')->delete($material->file_path);
        }

        $material->delete();

        return response()->json([
            'success' => true,
        ]);
    }

    private function validatedData(Request $request, bool $isCreate = false): array
    {
        return $request->validate([
            'title' => 'required|string|max:255',
            'mentor_id' => 'nullable|exists:users,id',
            'price' => 'nullable|numeric|min:0',
            'flash_sale_price' => 'nullable|numeric|min:0',
            'flash_sale_ends_at' => 'nullable|date',
            'slots' => 'nullable|integer|min:1|max:100000',
            'description' => 'nullable|string|max:20000',
            'image' => ($isCreate ? 'required' : 'nullable') . '|image|max:5120',
            'remove_image' => 'nullable|boolean',
            'schedule_json' => 'nullable|string|max:20000',
            'type' => 'required|in:online,presencial',
            'video_platform' => 'nullable|string|max:255',
            'video_link' => 'nullable|string|max:2000',
            'demo_link' => 'nullable|string|max:2000',
        ]);
    }

    private function deletePublicImageIfExists(?string $path): void
    {
        $path = trim((string) $path);
        if ($path === '') {
            return;
        }

        $fullPath = public_path($path);
        if (file_exists($fullPath)) {
            @unlink($fullPath);
        }
    }

    private function parseSchedule(?string $raw): ?array
    {
        $raw = trim((string) $raw);
        if ($raw === '') {
            return null;
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return null;
        }

        return $decoded;
    }

    private function resolveMaterialUploadPolicy(): array
    {
        $defaultDoc = implode(',', (array) config('uploads.allowed_document_formats', []));
        $defaultVideo = implode(',', (array) config('uploads.allowed_video_formats', []));

        $docExtensions = $this->parseExtensions((string) Setting::get('allowed_document_formats', $defaultDoc));
        if (empty($docExtensions)) {
            $docExtensions = $this->parseExtensions($defaultDoc);
        }

        $videoExtensions = $this->parseExtensions((string) Setting::get('allowed_video_formats', $defaultVideo));
        if (empty($videoExtensions)) {
            $videoExtensions = $this->parseExtensions($defaultVideo);
        }

        $allExtensions = array_values(array_unique(array_merge($docExtensions, $videoExtensions)));

        $documentMaxMb = (int) Setting::get('document_max_mb', (int) config('uploads.document_max_mb', 50));
        $videoMaxMb = (int) Setting::get('video_max_mb', (int) config('uploads.video_max_mb', 1024));

        return [
            'document_extensions' => $docExtensions,
            'video_extensions' => $videoExtensions,
            'allowed_extensions' => $allExtensions,
            'document_max_mb' => max(1, $documentMaxMb),
            'video_max_mb' => max(1, $videoMaxMb),
        ];
    }

    private function parseExtensions(?string $raw): array
    {
        $parts = preg_split('/[,\s]+/', strtolower((string) $raw)) ?: [];
        $extensions = [];

        foreach ($parts as $part) {
            $ext = trim((string) $part);
            $ext = ltrim($ext, '.');

            if ($ext === '' || !preg_match('/^[a-z0-9]+$/', $ext)) {
                continue;
            }

            $extensions[] = $ext;
        }

        return array_values(array_unique($extensions));
    }

    private function resolveMentorId(Request $request, $requestedMentorId): int
    {
        if ($this->canManageAllMentorships()) {
            return (int) ($requestedMentorId ?: Auth::id());
        }

        return (int) Auth::id();
    }

    private function mentorOptions()
    {
        if ($this->canManageAllMentorships()) {
            return User::query()
                ->select('id', 'name', 'email')
                ->orderBy('name')
                ->limit(300)
                ->get();
        }

        return User::query()
            ->select('id', 'name', 'email')
            ->where('id', Auth::id())
            ->get();
    }

    private function canManageAllMentorships(): bool
    {
        $user = Auth::user();
        return $user && $user->isAdmin();
    }

    private function ensurePermission(string $permission): void
    {
        $user = Auth::user();
        if (!$user || (!$user->isAdmin() && !$user->hasPermission($permission))) {
            abort(403);
        }
    }

    private function ensureOwnership(Mentorship $mentorship): void
    {
        if ($this->canManageAllMentorships()) {
            return;
        }

        if ((int) $mentorship->mentor_id !== (int) Auth::id()) {
            abort(403);
        }
    }
}
