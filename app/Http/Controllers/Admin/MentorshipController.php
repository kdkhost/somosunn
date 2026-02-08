<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mentorship;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        $data = $this->validatedData($request);
        $data['mentor_id'] = $this->resolveMentorId($request, $data['mentor_id'] ?? null);
        $data['schedule'] = $this->parseSchedule($request->input('schedule_json'));

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

        $data = $this->validatedData($request);
        $data['mentor_id'] = $this->resolveMentorId($request, $data['mentor_id'] ?? null);
        $data['schedule'] = $this->parseSchedule($request->input('schedule_json'));

        $mentorship->update($data);

        return redirect()->route('admin.mentorships.index')->with('success', 'Mentoria atualizada com sucesso.');
    }

    public function destroy(Mentorship $mentorship)
    {
        $this->ensurePermission('mentorships.delete');
        $this->ensureOwnership($mentorship);

        $mentorship->delete();

        return redirect()->route('admin.mentorships.index')->with('success', 'Mentoria removida com sucesso.');
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'title' => 'required|string|max:255',
            'mentor_id' => 'nullable|exists:users,id',
            'price' => 'nullable|numeric|min:0',
            'slots' => 'nullable|integer|min:1|max:100000',
            'description' => 'nullable|string|max:20000',
            'schedule_json' => 'nullable|string|max:20000',
            'type' => 'required|in:online,presencial',
            'video_platform' => 'nullable|string|max:255',
            'video_link' => 'nullable|string|max:2000',
            'demo_link' => 'nullable|string|max:2000',
        ]);
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
