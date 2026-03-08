<?php

namespace App\Http\Controllers\Panel\Admin;

use App\Http\Controllers\Panel\Admin\Concerns\ManagesContentVisibility;
use App\Http\Controllers\Controller;
use App\Models\Mentorship;
use App\Models\User;
use App\Services\PointsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class MentorshipController extends Controller
{
    use ManagesContentVisibility;

    public function index(Request $request)
    {
        $this->ensurePermission('mentorships.view');

        $query = Mentorship::query()->with('mentor')->latest('id');

        if (!$this->canManageAllMentorships()) {
            $query->where('mentor_id', Auth::id());
        }

        $search = trim((string) $request->query('q', ''));
        if ($search !== '') {
            $query->where(function ($sub) use ($search) {
                $sub->where('title', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        $mentorships = $query->paginate(12);
        $mentorships->appends($request->all());

        return view('panel.admin.mentorships.index', compact('mentorships', 'search'));
    }

    public function create()
    {
        $this->ensurePermission('mentorships.create');

        $mentorship = new Mentorship();
        $mentors = $this->mentorOptions();

        return view('panel.admin.mentorships.form', compact('mentorship', 'mentors'));
    }

    public function store(Request $request)
    {
        $this->ensurePermission('mentorships.create');

        $data = $this->validatedData($request, true);
        $data['mentor_id'] = $this->resolveMentorId($request, $data['mentor_id'] ?? null);
        $data['schedule'] = $this->parseSchedule($request->input('schedule_json'));
        $data['is_certificate_enabled'] = $request->boolean('is_certificate_enabled');
        $data = $this->applyVisibilityData($request, $data);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $fileName = 'mentorship_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/mentorship-images'), $fileName);
            $data['image'] = 'uploads/mentorship-images/' . $fileName;
        }

        if ($request->has('certificate_settings')) {
            $data['certificate_settings'] = is_string($request->certificate_settings) ? json_decode($request->certificate_settings, true) : $request->certificate_settings;
        }

        $mentorship = Mentorship::create($data);

        // Gamificação: premia o mentor por criar a primeira mentoria (não-repetível)
        try {
            $mentorUser = isset($data['mentor_id']) ? User::find($data['mentor_id']) : null;
            if ($mentorUser) {
                (new PointsService())->award($mentorUser, 'mentor', ['mentorship_id' => $mentorship->id]);
            }
        } catch (\Throwable $e) {
            \Log::warning('Falha ao pontuar mentor: ' . $e->getMessage());
        }

        return redirect()->route('panel.admin.mentorships.index')->with('success', 'Mentoria criada com sucesso.');
    }

    public function edit(Mentorship $mentorship)
    {
        $this->ensurePermission('mentorships.edit');
        $this->ensureOwnership($mentorship);

        $mentors = $this->mentorOptions();

        return view('panel.admin.mentorships.form', compact('mentorship', 'mentors'));
    }

    public function update(Request $request, Mentorship $mentorship)
    {
        $this->ensurePermission('mentorships.edit');
        $this->ensureOwnership($mentorship);

        $data = $this->validatedData($request);
        $data['mentor_id'] = $this->resolveMentorId($request, $data['mentor_id'] ?? null);
        $data['schedule'] = $this->parseSchedule($request->input('schedule_json'));
        $data['is_certificate_enabled'] = $request->boolean('is_certificate_enabled');
        $data = $this->applyVisibilityData(
            $request,
            $data,
            $mentorship->visibility,
            (bool) $mentorship->is_somos_unicas
        );

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

        if ($request->has('certificate_settings')) {
            $data['certificate_settings'] = is_string($request->certificate_settings) ? json_decode($request->certificate_settings, true) : $request->certificate_settings;
        }

        $mentorship->update($data);

        return redirect()->route('panel.admin.mentorships.index')->with('success', 'Mentoria atualizada com sucesso.');
    }

    public function destroy(Mentorship $mentorship)
    {
        $this->ensurePermission('mentorships.delete');
        $this->ensureOwnership($mentorship);

        $mentorship->delete();

        return redirect()->route('panel.admin.mentorships.index')->with('success', 'Mentoria removida com sucesso.');
    }

    private function validatedData(Request $request, bool $isCreate = false): array
    {
        return $request->validate([
            'title' => 'required|string|max:255',
            'mentor_id' => 'nullable|exists:users,id',
            'price' => 'nullable|string', // Will normalize in logic if needed
            'flash_sale_price' => 'nullable|string',
            'flash_sale_ends_at' => 'nullable|date',
            'slots' => 'nullable|integer|min:1',
            'description' => 'nullable|string',
            'image' => ($isCreate ? 'required' : 'nullable') . '|image|max:5120',
            'remove_image' => 'nullable|boolean',
            'schedule_json' => 'nullable|string',
            'type' => 'required|in:online,presencial',
            'video_platform' => 'nullable|string|max:255',
            'video_link' => 'nullable|string|max:2000',
            'demo_link' => 'nullable|string|max:2000',
            'visibility' => $this->visibilityRule(),
        ]);
    }

    private function deletePublicImageIfExists(?string $path): void
    {
        if (!$path)
            return;
        $fullPath = public_path($path);
        if (file_exists($fullPath))
            @unlink($fullPath);
    }

    private function parseSchedule(?string $raw): ?array
    {
        if (!$raw)
            return null;
        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : null;
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
            return User::query()->select('id', 'name', 'email')->orderBy('name')->get();
        }
        return User::query()->select('id', 'name', 'email')->where('id', Auth::id())->get();
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
        if ($this->canManageAllMentorships())
            return;
        if ((int) $mentorship->mentor_id !== (int) Auth::id())
            abort(403);
    }
}
