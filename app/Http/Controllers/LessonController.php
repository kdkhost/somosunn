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
        set_time_limit(0);
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', 0);

        $videoMaxMb = (int) config('uploads.video_max_mb', 1024);
        $videoMaxKb = max(1, $videoMaxMb) * 1024;
        $allowedVideoExt = array_map('strtolower', array_map('trim', (array) config('uploads.allowed_video_formats', [])));
        $allowedVideoRule = !empty($allowedVideoExt) ? ('|mimes:' . implode(',', $allowedVideoExt)) : '';

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'order' => 'required|integer',
            'video_url' => 'nullable|url',
            'video_file' => 'nullable|file|max:' . $videoMaxKb . $allowedVideoRule,
            'content' => 'nullable|string',
            'is_free_preview' => 'nullable|boolean',
            'duration' => 'nullable|integer',
        ]);

        $videoUrl = $validated['video_url'] ?? null;

        if ($request->hasFile('video_file')) {
            $path = $request->file('video_file')->store('course-videos', 'public');
            $videoUrl = 'storage/' . $path;
        }

        $lesson = $course->lessons()->create([
            'title' => $validated['title'],
            'order' => $validated['order'],
            'video_url' => $videoUrl,
            'content' => $validated['content'],
            'is_free_preview' => $request->has('is_free_preview'),
            'duration' => $request->duration ?? 0,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Aula criada com sucesso',
                'lesson' => $lesson
            ]);
        }

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

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Aula atualizada com sucesso',
                'lesson' => $lesson
            ]);
        }

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
        set_time_limit(0);
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', 0);

        $docMaxMb = (int) config('uploads.document_max_mb', 50);
        $docMaxKb = max(1, $docMaxMb) * 1024;
        $allowedDocExt = array_map('strtolower', array_map('trim', (array) config('uploads.allowed_document_formats', [])));
        $allowedDocRule = !empty($allowedDocExt) ? ('|mimes:' . implode(',', $allowedDocExt)) : '';

        $request->validate([
            'file' => 'required|file|max:' . $docMaxKb . $allowedDocRule,
        ]);

        $file = $request->file('file');
        $path = $file->store('course-materials', 'public');
        
        $attachment = $lesson->attachments()->create([
            'file_path' => $path,
            'file_name' => $request->input('name', $file->getClientOriginalName()),
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
