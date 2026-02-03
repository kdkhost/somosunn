<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LessonController extends Controller
{
    public function store(Request $request, Course $course)
    {
        $this->authorize('update', $course);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'order' => 'required|integer',
            'video_url' => 'nullable|url',
            'content' => 'nullable|string',
            'is_free_preview' => 'nullable|boolean',
        ]);

        $course->lessons()->create([
            'title' => $validated['title'],
            'order' => $validated['order'],
            'video_url' => $validated['video_url'],
            'content' => $validated['content'],
            'is_free_preview' => $request->has('is_free_preview'),
        ]);

        return back()->with('success', 'Aula adicionada.');
    }

    public function show(Course $course, Lesson $lesson)
    {
        // Check access
        $canView = $lesson->is_free_preview || 
                   (Auth::check() && ($course->user_id == Auth::id() || $course->enrollments()->where('user_id', Auth::id())->exists()));
        
        if (!$canView) {
            abort(403, 'Você precisa comprar este curso para ver esta aula.');
        }

        $previous = $course->lessons()->where('order', '<', $lesson->order)->orderBy('order', 'desc')->first();
        $next = $course->lessons()->where('order', '>', $lesson->order)->orderBy('order', 'asc')->first();

        // Mark progress if enrolled
        if(Auth::check() && !$course->user_id == Auth::id()){ // Don't track progress for owner? Or do? Maybe yes.
             // Usually track for students
             $enrollment = $course->enrollments()->where('user_id', Auth::id())->first();
             if($enrollment){
                 $lesson->progress()->firstOrCreate([
                     'user_id' => Auth::id()
                 ], ['completed_at' => now()]);
             }
        }

        return view('courses.lesson', compact('course', 'lesson', 'previous', 'next'));
    }

    public function update(Request $request, Course $course, Lesson $lesson)
    {
        $this->authorize('update', $course);
        
        $validated = $request->validate([
            'title' => 'required|string',
            'order' => 'integer',
            'video_url' => 'nullable|url',
            'content' => 'nullable|string',
        ]);
        
        $lesson->update($validated); // Add is_free_preview check if needed

        return back()->with('success', 'Aula atualizada.');
    }

    public function destroy(Course $course, Lesson $lesson)
    {
        $this->authorize('update', $course);
        $lesson->delete();
        return back()->with('success', 'Aula removida.');
    }
}
