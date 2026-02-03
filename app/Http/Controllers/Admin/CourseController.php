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
        $data = $request->validate([ 'title'=>'required','description'=>'nullable','price'=>'nullable|numeric','level'=>'nullable','cert_required'=>'nullable|boolean','published'=>'nullable|boolean' ]);
        Course::create($data + ['created_by' => auth()->id()]);
        return redirect()->route('admin.courses.index')->with('success','Curso criado');
    }

    public function edit(Course $course)
    {
        return view('admin.courses.form', compact('course'));
    }

    public function update(Request $request, Course $course)
    {
        $data = $request->validate([ 'title'=>'required','description'=>'nullable','price'=>'nullable|numeric','level'=>'nullable','cert_required'=>'nullable|boolean','published'=>'nullable|boolean' ]);
        $course->update($data);
        return redirect()->route('admin.courses.index')->with('success','Curso atualizado');
    }

    public function destroy(Course $course)
    {
        $course->delete();
        return redirect()->route('admin.courses.index')->with('success','Curso removido');
    }
}