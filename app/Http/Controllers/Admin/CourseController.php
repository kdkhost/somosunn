<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Course;

class CourseController extends Controller
{
    public function index()
    {
        $courses = Course::latest()->get();
        return view('admin.courses.index', compact('courses'));
    }

    public function create()
    {
        return view('admin.courses.form', ['course' => new Course]);
    }

    public function store(Request $request)
    {
        if ($request->has('price')) {
            $price = $request->price;
            // Remove R$, whitespace, and dots (thousand separators)
            $price = str_replace(['R$', ' ', '.'], '', $price);
            // Replace comma with dot
            $price = str_replace(',', '.', $price);
            $request->merge(['price' => $price]);
        }

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric',
            'author_name' => 'nullable|string|max:255',
            'status' => 'required|in:draft,published,archived,paused',
            'thumbnail' => 'nullable|image|max:10240', // 10MB Max
        ]);
        
        $data['is_featured'] = $request->has('is_featured');
        $data['published'] = $request->has('published'); // Legacy support
        $data['price'] = (!isset($data['price']) || $data['price'] === null || $data['price'] === '') ? 0 : $data['price'];

        if ($request->hasFile('thumbnail')) {
            $path = $request->file('thumbnail')->store('course-thumbs', 'public');
            $data['thumbnail'] = 'storage/' . $path;
        }
        
        Course::create($data + ['user_id' => auth()->id()]);
        
        return redirect()->route('admin.courses.index')->with('success','Curso criado com sucesso');
    }

    public function edit(Course $course)
    {
        return view('admin.courses.form', compact('course'));
    }

    public function update(Request $request, Course $course)
    {
        if ($request->has('price')) {
            $price = $request->price;
            $price = str_replace(['R$', ' ', '.'], '', $price);
            $price = str_replace(',', '.', $price);
            $request->merge(['price' => $price]);
        }

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric',
            'author_name' => 'nullable|string|max:255',
            'status' => 'required|in:draft,published,archived,paused',
            'thumbnail' => 'nullable|image|max:10240', // 10MB Max
        ]);

        $data['is_featured'] = $request->has('is_featured');
        $data['published'] = $request->has('published');
        $data['price'] = (!isset($data['price']) || $data['price'] === null || $data['price'] === '') ? 0 : $data['price'];

        if ($request->hasFile('thumbnail')) {
            $path = $request->file('thumbnail')->store('course-thumbs', 'public');
            $data['thumbnail'] = 'storage/' . $path;
        }

        $course->update($data);
        
        return redirect()->route('admin.courses.index')->with('success','Curso atualizado com sucesso');
    }

    public function destroy(Course $course)
    {
        $course->delete();
        return redirect()->route('admin.courses.index')->with('success','Curso removido');
    }
}