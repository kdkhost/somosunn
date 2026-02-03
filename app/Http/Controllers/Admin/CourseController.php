<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Course;

class CourseController extends Controller
{
    public function index()
    {
        $courses = Course::paginate(20);
        return view('admin.courses.index', compact('courses'));
    }

    public function create()
    {
        return view('admin.courses.form', ['course' => new Course]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric',
            'author_name' => 'nullable|string|max:255',
            'status' => 'required|in:draft,published,archived,paused',
        ]);
        
        $data['is_featured'] = $request->has('is_featured');
        $data['published'] = $request->has('published'); // Legacy support
        $data['price'] = $data['price'] ?? 0;
        
        Course::create($data + ['user_id' => auth()->id()]);
        
        return redirect()->route('admin.courses.index')->with('success','Curso criado com sucesso');
    }

    public function edit(Course $course)
    {
        return view('admin.courses.form', compact('course'));
    }

    public function update(Request $request, Course $course)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric',
            'author_name' => 'nullable|string|max:255',
            'status' => 'required|in:draft,published,archived,paused',
        ]);

        $data['is_featured'] = $request->has('is_featured');
        $data['published'] = $request->has('published');
        $data['price'] = $data['price'] ?? 0;

        $course->update($data);
        
        return redirect()->route('admin.courses.index')->with('success','Curso atualizado com sucesso');
    }

    public function destroy(Course $course)
    {
        $course->delete();
        return redirect()->route('admin.courses.index')->with('success','Curso removido');
    }
}