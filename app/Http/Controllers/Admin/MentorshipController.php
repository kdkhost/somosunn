<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mentorship;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

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
            'is_somos_unicas' => 'nullable|boolean',
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
