<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CourseController extends Controller
{
    /**
     * Display a listing of the courses.
     */
    public function index()
    {
        $courses = Course::query() // where('status', 'published') -> Temporarily disabled due to missing column on production
            ->with('creator')
            ->latest()
            ->paginate(12);

        return view('courses.index', compact('courses'));
    }

    /**
     * Show the form for creating a new course.
     */
    public function create()
    {
        return view('courses.create');
    }

    /**
     * Store a newly created course in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'duration' => 'nullable|integer', // minutes
            'is_certificate_enabled' => 'nullable|boolean',
            'thumbnail' => 'nullable|image|max:2048',
            'short_description' => 'nullable|string|max:500',
            'full_description' => 'nullable|string',
        ]);

        $slug = Str::slug($validated['title']) . '-' . uniqid();
        
        $path = null;
        if ($request->hasFile('thumbnail')) {
            $path = $request->file('thumbnail')->store('courses', 'public');
        }

        $course = Auth::user()->createdCourses()->create([
            'title' => $validated['title'],
            'slug' => $slug,
            'price' => $validated['price'],
            'duration' => $validated['duration'] ?? 0,
            'is_certificate_enabled' => $request->has('is_certificate_enabled'),
            'thumbnail' => $path,
            'short_description' => $validated['short_description'],
            'full_description' => $validated['full_description'],
            'author_name' => Auth::user()->name,
            'status' => 'draft',
        ]);

        return redirect()->route('courses.show', $course->slug)
            ->with('success', 'Curso criado com sucesso! Adicione aulas agora.');
    }

    /**
     * Display the specified course.
     */
    public function show($slug)
    {
        $course = Course::where('slug', $slug)
            ->with(['lessons' => function($q) {
                // Determine if user can see full content logic here if needed
                $q->orderBy('order');
            }])
            ->firstOrFail();

        // Check enrollment
        $isEnrolled = false; 
        if(Auth::check()){
            $isEnrolled = $course->enrollments()->where('user_id', Auth::id())->exists() || $course->user_id == Auth::id();
        }

        return view('courses.show', compact('course', 'isEnrolled'));
    }

    /**
     * Show the form for editing the specified course.
     */
    public function edit(Course $course)
    {
        $this->authorize('update', $course);
        return view('courses.edit', compact('course'));
    }

    /**
     * Update the specified course in storage.
     */
    public function update(Request $request, Course $course)
    {
        $this->authorize('update', $course);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'duration' => 'nullable|integer',
            'is_certificate_enabled' => 'nullable|boolean',
            'thumbnail' => 'nullable|image|max:2048',
            'short_description' => 'nullable|string|max:500',
            'full_description' => 'nullable|string',
            'status' => 'required|in:draft,published,archived',
        ]);

        if ($request->hasFile('thumbnail')) {
            if ($course->thumbnail) {
                Storage::disk('public')->delete($course->thumbnail);
            }
            $course->thumbnail = $request->file('thumbnail')->store('courses', 'public');
        }

        $course->update([
            'title' => $validated['title'],
            'price' => $validated['price'],
            'duration' => $validated['duration'],
            'is_certificate_enabled' => $request->has('is_certificate_enabled'),
            'short_description' => $validated['short_description'],
            'full_description' => $validated['full_description'],
            'status' => $validated['status'],
        ]);

        return redirect()->route('courses.edit', $course->id)
            ->with('success', 'Curso atualizado com sucesso.');
    }

    /**
     * Remove the specified course from storage.
     */
    public function destroy(Course $course)
    {
        $this->authorize('delete', $course);
        $course->delete();
        return redirect()->route('courses.index')->with('success', 'Curso removido.');
    }
}
