<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

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
            $videoUrl = Storage::disk('public')->url($path);
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
                'lesson' => $lesson,
            ]);
        }

        return back()->with('success', 'Aula adicionada.');
    }

    public function show(Course $course, Lesson $lesson)
    {
        if (!$this->canViewLesson($course, $lesson)) {
            abort(403, 'Voce precisa comprar este curso para ver esta aula.');
        }

        $previous = $course->lessons()->where('order', '<', $lesson->order)->orderBy('order', 'desc')->first();
        $next = $course->lessons()->where('order', '>', $lesson->order)->orderBy('order', 'asc')->first();

        if (Auth::check() && $course->user_id != Auth::id()) {
            $enrollment = $course->enrollments()->where('user_id', Auth::id())->first();
            if ($enrollment) {
                $lesson->progress()->firstOrCreate(
                    ['user_id' => Auth::id()],
                    ['completed_at' => now()]
                );
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
                'lesson' => $lesson,
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
            'file_size' => $file->getSize(),
        ]);

        return response()->json([
            'success' => true,
            'attachment' => $attachment,
        ]);
    }

    public function downloadAttachment(Course $course, Lesson $lesson, LessonAttachment $attachment)
    {
        if ((int) $attachment->lesson_id !== (int) $lesson->id || (int) $lesson->course_id !== (int) $course->id) {
            abort(404);
        }

        if (!$this->canViewLesson($course, $lesson)) {
            abort(403, 'Voce nao tem permissao para baixar este material.');
        }

        if (!Storage::disk('public')->exists($attachment->file_path)) {
            abort(404);
        }

        $downloadName = trim((string) $attachment->file_name) !== ''
            ? $attachment->file_name
            : basename((string) $attachment->file_path);

        return Storage::disk('public')->download($attachment->file_path, $downloadName);
    }

    public function deleteAttachment(Course $course, Lesson $lesson, LessonAttachment $attachment)
    {
        $this->authorize('update', $course);

        if (Storage::disk('public')->exists($attachment->file_path)) {
            Storage::disk('public')->delete($attachment->file_path);
        }

        $attachment->delete();

        return response()->json(['success' => true]);
    }

    public function renameAttachment(Request $request, Course $course, Lesson $lesson, LessonAttachment $attachment)
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

    private function canViewLesson(Course $course, Lesson $lesson): bool
    {
        if ($lesson->is_free_preview) {
            return true;
        }

        if (!Auth::check()) {
            return false;
        }

        if ((int) $course->user_id === (int) Auth::id()) {
            return true;
        }

        return $course->enrollments()->where('user_id', Auth::id())->exists();
    }
}
