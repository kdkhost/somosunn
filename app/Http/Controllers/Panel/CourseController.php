<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CourseController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $courses = Course::where('user_id', $user->id)->latest()->get();
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
