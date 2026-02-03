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
            'duration' => 'nullable|integer',
        ]);

        $course->lessons()->create([
            'title' => $validated['title'],
            'order' => $validated['order'],
            'video_url' => $validated['video_url'],
            'content' => $validated['content'],
            'is_free_preview' => $request->has('is_free_preview'),
            'duration' => $request->duration ?? 0,
        ]);

        return back()->with('success', 'Aula adicionada.');
    }

    public function show(Course $course, Lesson $lesson)
    {
        // Public/Student view - existing logic...
        $canView = $lesson->is_free_preview || 
                   (Auth::check() && ($course->user_id == Auth::id() || $course->enrollments()->where('user_id', Auth::id())->exists()));
        
        if (!$canView) {
            abort(403, 'Você precisa comprar este curso para ver esta aula.');
        }

        $previous = $course->lessons()->where('order', '<', $lesson->order)->orderBy('order', 'desc')->first();
        $next = $course->lessons()->where('order', '>', $lesson->order)->orderBy('order', 'asc')->first();

        // Mark progress if enrolled
        if(Auth::check() && $course->user_id != Auth::id()){
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
            'duration' => 'nullable|integer',
            'is_free_preview' => 'nullable|boolean',
        ]);
        
        $lesson->update($validated + ['is_free_preview' => $request->has('is_free_preview')]);

        return back()->with('success', 'Aula atualizada.');
    }

    public function destroy(Course $course, Lesson $lesson)
    {
        $this->authorize('update', $course);
        $lesson->delete();
        return back()->with('success', 'Aula removida.');
    }

    // Attachment Methods

    public function uploadAttachment(Request $request, Course $course, Lesson $lesson)
    {
        $this->authorize('update', $course);
        
        $request->validate([
            'file' => 'required|file|max:51200' // 50MB max
        ]);

        $file = $request->file('file');
        $path = $file->store('course-materials', 'public');
        
        $attachment = $lesson->attachments()->create([
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(), // Default name
            'file_type' => $file->getClientOriginalExtension(),
            'file_size' => $file->getSize()
        ]);

        return response()->json([
            'success' => true,
            'attachment' => $attachment
        ]);
    }

    public function deleteAttachment(Course $course, Lesson $lesson, \App\Models\LessonAttachment $attachment)
    {
        $this->authorize('update', $course);
        
        if(\Illuminate\Support\Facades\Storage::disk('public')->exists($attachment->file_path)){
            \Illuminate\Support\Facades\Storage::disk('public')->delete($attachment->file_path);
        }
        
        $attachment->delete();
        
        return response()->json(['success' => true]);
    }

    public function renameAttachment(Request $request, Course $course, Lesson $lesson, \App\Models\LessonAttachment $attachment)
    {
        $this->authorize('update', $course);
        $request->validate(['name' => 'required|string|max:255']);
        
        $attachment->update(['file_name' => $request->name]);
        
        return response()->json(['success' => true]);
    }

    public function getDetails(Course $course, Lesson $lesson)
    {
        $this->authorize('update', $course);
        return response()->json($lesson->load('attachments'));
    }
}
