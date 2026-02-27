<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class CourseController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $this->repairLegacyOwnershipForCurrentUser($user);
        $normalizedUserName = $this->normalizeOwnerName((string) ($user->name ?? ''));

        $courses = Course::query()
            ->where(function ($query) use ($user, $normalizedUserName) {
                $query->where('user_id', $user->id);

                if (Schema::hasColumn('courses', 'created_by')) {
                    $query->orWhere('created_by', $user->id);
                }

                if ($normalizedUserName !== '') {
                    $query->orWhere(function ($fallbackQuery) use ($normalizedUserName) {
                        $fallbackQuery->where(function ($courseOwnerQuery) {
                            $courseOwnerQuery->whereNull('user_id')
                                ->orWhereIn('user_id', [0, 1]);
                        })
                            ->whereRaw('LOWER(TRIM(author_name)) = ?', [$normalizedUserName]);
                    });
                }
            })
            ->latest()
            ->get();

        return view('panel.courses.index', compact('courses'));
    }

    public function create()
    {
        $course = new Course();
        return view('panel.courses.form', compact('course'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'short_description' => 'nullable|string|max:500',
            'full_description' => 'nullable|string',
            'price' => 'nullable|numeric',
            'status' => 'required|in:draft,published,archived,paused',
            'thumbnail' => 'nullable|image|max:10240',
        ], [], [
            'title' => 'Titulo',
            'short_description' => 'Descricao curta',
            'full_description' => 'Descricao completa',
            'price' => 'Preco',
            'status' => 'Status',
            'thumbnail' => 'Thumbnail',
        ]);

        if ($request->hasFile('thumbnail')) {
            $file = $request->file('thumbnail');
            $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/course-thumbs'), $fileName);
            $data['thumbnail'] = 'uploads/course-thumbs/' . $fileName;
        }

        $data['user_id'] = Auth::id();
        if (Schema::hasColumn('courses', 'created_by')) {
            $data['created_by'] = Auth::id();
        }
        Course::create($data);

        return redirect()->route('panel.courses.index')->with('success', 'Curso criado com sucesso!');
    }

    public function edit(Course $course)
    {
        $this->authorize('update', $course);
        return view('panel.courses.form', compact('course'));
    }

    public function update(Request $request, Course $course)
    {
        $this->authorize('update', $course);
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'short_description' => 'nullable|string|max:500',
            'full_description' => 'nullable|string',
            'price' => 'nullable|numeric',
            'status' => 'required|in:draft,published,archived,paused',
            'thumbnail' => 'nullable|image|max:10240',
        ], [], [
            'title' => 'Titulo',
            'short_description' => 'Descricao curta',
            'full_description' => 'Descricao completa',
            'price' => 'Preco',
            'status' => 'Status',
            'thumbnail' => 'Thumbnail',
        ]);

        if ($request->hasFile('thumbnail')) {
            $file = $request->file('thumbnail');
            $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/course-thumbs'), $fileName);
            $data['thumbnail'] = 'uploads/course-thumbs/' . $fileName;
        }

        if (Schema::hasColumn('courses', 'created_by') && empty($course->created_by)) {
            $data['created_by'] = Auth::id();
        }

        $course->update($data);
        return redirect()->route('panel.courses.index')->with('success', 'Curso atualizado com sucesso!');
    }

    public function destroy(Course $course)
    {
        $this->authorize('delete', $course);
        $course->delete();
        return redirect()->route('panel.courses.index')->with('success', 'Curso excluido com sucesso!');
    }

    protected function repairLegacyOwnershipForCurrentUser($user): void
    {
        if (!$user || !Schema::hasColumn('courses', 'user_id')) {
            return;
        }

        $authUserId = (int) $user->id;
        if ($authUserId <= 0) {
            return;
        }

        if (Schema::hasColumn('courses', 'created_by')) {
            Course::query()
                ->where(function ($query) {
                    $query->whereNull('user_id')
                        ->orWhereIn('user_id', [0, 1]);
                })
                ->where('created_by', $authUserId)
                ->update(['user_id' => $authUserId]);
        }

        $normalizedUserName = $this->normalizeOwnerName((string) ($user->name ?? ''));
        if ($normalizedUserName === '') {
            return;
        }

        Course::query()
            ->select(['id', 'author_name'])
            ->where(function ($query) {
                $query->whereNull('user_id')
                    ->orWhereIn('user_id', [0, 1]);
            })
            ->whereNotNull('author_name')
            ->get()
            ->each(function (Course $course) use ($authUserId, $normalizedUserName) {
                if ($this->normalizeOwnerName((string) ($course->author_name ?? '')) !== $normalizedUserName) {
                    return;
                }

                Course::query()
                    ->whereKey($course->id)
                    ->update(['user_id' => $authUserId]);
            });
    }

    protected function normalizeOwnerName(string $value): string
    {
        $value = str_replace("\u{00A0}", ' ', $value);
        $value = preg_replace('/\s+/u', ' ', trim($value)) ?? '';
        if ($value === '') {
            return '';
        }

        return mb_strtolower(Str::ascii($value));
    }
}
