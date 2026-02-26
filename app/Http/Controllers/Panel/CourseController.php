<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class CourseController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $courses = Course::query()
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id);

                if (Schema::hasColumn('courses', 'created_by')) {
                    $query->orWhere('created_by', $user->id);
                }

                if (trim((string) ($user->name ?? '')) !== '') {
                    $query->orWhere(function ($fallbackQuery) use ($user) {
                        $fallbackQuery->where(function ($courseOwnerQuery) {
                            $courseOwnerQuery->whereNull('user_id')
                                ->orWhere('user_id', 1);
                        })
                            ->where('author_name', trim((string) ($user->name ?? '')));
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
            'title' => 'Título',
            'short_description' => 'Descrição curta',
            'full_description' => 'Descrição completa',
            'price' => 'Preço',
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
            'title' => 'Título',
            'short_description' => 'Descrição curta',
            'full_description' => 'Descrição completa',
            'price' => 'Preço',
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
        return redirect()->route('panel.courses.index')->with('success', 'Curso excluído com sucesso!');
    }
}
