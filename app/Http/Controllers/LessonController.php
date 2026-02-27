<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonAttachment;
use App\Models\LessonBookmark;
use App\Models\LessonProgress;
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
            'free_preview_mode' => 'exclude_unless:is_free_preview,1|nullable|string|in:full,time',
            'free_preview_seconds' => 'exclude_unless:is_free_preview,1|nullable|integer|min:1|required_if:free_preview_mode,time',
        ]);

        $videoUrl = $validated['video_url'] ?? null;

        if ($request->hasFile('video_file')) {
            $path = $request->file('video_file')->store('course-videos', 'public');
            $videoUrl = Storage::disk('public')->url($path);
        }

        $previewData = $this->buildPreviewData($request, (int) ($validated['duration'] ?? 0));

        $lesson = $course->lessons()->create([
            'title' => $validated['title'],
            'order' => $validated['order'],
            'video_url' => $videoUrl,
            'content' => $validated['content'],
            'duration' => $request->duration ?? 0,
        ] + $previewData);

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
        if ((int) $lesson->course_id !== (int) $course->id) {
            abort(404);
        }

        $hasFullAccess = $this->hasFullCourseAccess($course);

        if (!$this->canViewLesson($course, $lesson)) {
            abort(403, 'Você precisa comprar este curso para ver esta aula.');
        }

        $previewLimitSeconds = 0;
        if (
            !$hasFullAccess
            && (bool) $lesson->is_free_preview
            && (string) ($lesson->free_preview_mode ?? 'full') === 'time'
        ) {
            $previewLimitSeconds = max(0, (int) ($lesson->free_preview_seconds ?? 0));

            $lessonDuration = max(0, (int) ($lesson->duration ?? 0));
            if ($lessonDuration > 0 && $previewLimitSeconds > 0) {
                $previewLimitSeconds = min($previewLimitSeconds, $lessonDuration);
            }
        }

        $previous = $course->lessons()->where('order', '<', $lesson->order)->orderBy('order', 'desc')->first();
        $next = $course->lessons()->where('order', '>', $lesson->order)->orderBy('order', 'asc')->first();

        $resumeAt = 0;
        $bookmarks = collect();

        if (Auth::check()) {
            $progress = LessonProgress::query()
                ->where('user_id', Auth::id())
                ->where('lesson_id', $lesson->id)
                ->first();

            if ($progress && $progress->current_time_seconds !== null) {
                $resumeAt = max(0, (int) $progress->current_time_seconds);
            }

            $bookmarks = LessonBookmark::query()
                ->where('user_id', Auth::id())
                ->where('lesson_id', $lesson->id)
                ->orderBy('position_seconds')
                ->orderByDesc('id')
                ->get();
        }

        if (Auth::check() && $course->user_id != Auth::id()) {
            $enrollment = $course->enrollments()->where('user_id', Auth::id())->first();
            if ($enrollment) {
                $lesson->progress()->firstOrCreate(
                    ['user_id' => Auth::id()],
                    ['completed_at' => null]
                );
            }
        }

        return view(
            'courses.lesson',
            compact('course', 'lesson', 'previous', 'next', 'resumeAt', 'bookmarks', 'hasFullAccess', 'previewLimitSeconds')
        );
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
            'free_preview_mode' => 'exclude_unless:is_free_preview,1|nullable|string|in:full,time',
            'free_preview_seconds' => 'exclude_unless:is_free_preview,1|nullable|integer|min:1|required_if:free_preview_mode,time',
        ]);

        $duration = array_key_exists('duration', $validated)
            ? (int) $validated['duration']
            : (int) ($lesson->duration ?? 0);
        $previewData = $this->buildPreviewData($request, $duration);

        $lesson->update(array_merge($validated, $previewData));

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

        $headers = [
            'Content-Type' => 'application/octet-stream',
            'X-Content-Type-Options' => 'nosniff',
        ];

        $publicDisk = Storage::disk('public');
        if (method_exists($publicDisk, 'path')) {
            $absolutePath = $publicDisk->path($attachment->file_path);
            if (is_file($absolutePath)) {
                return response()->download($absolutePath, $downloadName, $headers);
            }
        }

        $stream = $publicDisk->readStream($attachment->file_path);
        if ($stream === false) {
            abort(404);
        }

        return response()->streamDownload(function () use ($stream) {
            fpassthru($stream);
            if (is_resource($stream)) {
                fclose($stream);
            }
        }, $downloadName, $headers);
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

    public function updatePlaybackProgress(Request $request, Course $course, Lesson $lesson)
    {
        if ((int) $lesson->course_id !== (int) $course->id) {
            abort(404);
        }

        if (!$this->canViewLesson($course, $lesson)) {
            abort(403, 'Você não tem permissão para atualizar o progresso desta aula.');
        }

        if (!Auth::check()) {
            abort(401);
        }

        $validated = $request->validate([
            'current_time_seconds' => ['required', 'numeric', 'min:0'],
        ]);

        $seconds = (int) floor((float) $validated['current_time_seconds']);
        $duration = max(0, (int) ($lesson->duration ?? 0));

        if ($duration > 0) {
            $seconds = min($seconds, $duration);
        }

        $progress = LessonProgress::query()->updateOrCreate(
            [
                'user_id' => Auth::id(),
                'lesson_id' => $lesson->id,
            ],
            [
                'current_time_seconds' => $seconds,
                'last_position_at' => now(),
            ]
        );

        $justCompleted = false;
        if ($duration > 0 && $seconds >= (int) floor($duration * 0.95)) {
            if ($progress->completed_at === null) {
                $progress->completed_at = now();
                $progress->save();
                $justCompleted = true;
            }
        }

        $courseCompleted = false;
        if ($justCompleted) {
            $courseCompleted = $this->checkCourseAndIssueCertificate($course);
        }

        return response()->json([
            'success' => true,
            'resume_at' => (int) ($progress->current_time_seconds ?? 0),
            'completed_at' => $progress->completed_at ? $progress->completed_at->toIso8601String() : null,
            'course_completed' => $courseCompleted,
        ]);
    }

    private function checkCourseAndIssueCertificate(Course $course): bool
    {
        $user = Auth::user();
        $totalLessons = $course->lessons()->count();
        if ($totalLessons === 0)
            return false;

        // Get completed lessons progress
        $completedProgress = LessonProgress::query()
            ->where('user_id', $user->id)
            ->whereIn('lesson_id', $course->lessons()->pluck('id'))
            ->whereNotNull('completed_at')
            ->with('lesson') // Eager load lessons to get duration
            ->get();

        $completedCount = $completedProgress->count();

        // Rule: 89% completion required
        $completionRatio = $completedCount / $totalLessons;

        if ($completionRatio >= 0.89) {
            if ($course->is_certificate_enabled) {
                // Check if already issued
                $existing = $course->certificates()->where('user_id', $user->id)->first();
                if (!$existing) {
                    try {
                        // Calculate actual watched workload (sum of completed lessons duration)
                        $watchedSeconds = $completedProgress->sum(function ($progress) {
                            return $progress->lesson->duration ?? 0;
                        });

                        // Convert to hours (decimal)
                        $workloadHours = $watchedSeconds > 0 ? round($watchedSeconds / 3600, 2) : 0;

                        // Trigger certificate generation
                        $course->certificates()->create([
                            'user_id' => $user->id,
                            'cert_hash' => \Illuminate\Support\Str::random(12),
                            'issued_at' => now(),
                            'workload' => $workloadHours
                        ]);
                    } catch (\Exception $e) {
                        \Log::error("Failed to issue certificate for user {$user->id} in course {$course->id}: " . $e->getMessage());
                    }
                }
            }
            return true;
        }

        return false;
    }

    public function storeBookmark(Request $request, Course $course, Lesson $lesson)
    {
        if ((int) $lesson->course_id !== (int) $course->id) {
            abort(404);
        }

        if (!$this->canViewLesson($course, $lesson)) {
            abort(403, 'Você não tem permissão para adicionar anotações nesta aula.');
        }

        if (!Auth::check()) {
            abort(401);
        }

        $validated = $request->validate([
            'position_seconds' => ['required', 'numeric', 'min:0'],
            'note' => ['required', 'string', 'min:2', 'max:1000'],
        ]);

        $seconds = (int) floor((float) $validated['position_seconds']);
        $duration = max(0, (int) ($lesson->duration ?? 0));

        if ($duration > 0) {
            $seconds = min($seconds, $duration);
        }

        $bookmark = LessonBookmark::query()->create([
            'user_id' => Auth::id(),
            'course_id' => $course->id,
            'lesson_id' => $lesson->id,
            'position_seconds' => $seconds,
            'note' => trim((string) $validated['note']),
        ]);

        return response()->json([
            'success' => true,
            'bookmark' => $bookmark,
        ]);
    }

    public function destroyBookmark(Course $course, Lesson $lesson, LessonBookmark $bookmark)
    {
        if ((int) $lesson->course_id !== (int) $course->id) {
            abort(404);
        }

        if (!$this->canViewLesson($course, $lesson)) {
            abort(403, 'Você não tem permissão para remover anotações nesta aula.');
        }

        if (!Auth::check()) {
            abort(401);
        }

        if (
            (int) $bookmark->user_id !== (int) Auth::id()
            || (int) $bookmark->lesson_id !== (int) $lesson->id
            || (int) $bookmark->course_id !== (int) $course->id
        ) {
            abort(404);
        }

        $bookmark->delete();

        return response()->json([
            'success' => true,
        ]);
    }

    private function canViewLesson(Course $course, Lesson $lesson): bool
    {
        if ($this->hasFullCourseAccess($course)) {
            return true;
        }

        if ($lesson->is_free_preview) {
            return true;
        }

        return false;
    }

    private function hasFullCourseAccess(Course $course): bool
    {
        if (!Auth::check()) {
            return false;
        }

        if ((int) $course->user_id === (int) Auth::id()) {
            return true;
        }

        return $course->enrollments()->where('user_id', Auth::id())->exists();
    }

    private function buildPreviewData(Request $request, int $durationSeconds = 0): array
    {
        $isFreePreview = $request->boolean('is_free_preview');

        if (!$isFreePreview) {
            return [
                'is_free_preview' => false,
                'free_preview_mode' => 'full',
                'free_preview_seconds' => null,
            ];
        }

        $modeInput = (string) $request->input('free_preview_mode', 'full');
        $mode = in_array($modeInput, ['full', 'time'], true) ? $modeInput : 'full';

        if ($mode !== 'time') {
            return [
                'is_free_preview' => true,
                'free_preview_mode' => 'full',
                'free_preview_seconds' => null,
            ];
        }

        $seconds = max(1, (int) $request->input('free_preview_seconds', 0));
        if ($durationSeconds > 0) {
            $seconds = min($seconds, $durationSeconds);
        }

        return [
            'is_free_preview' => true,
            'free_preview_mode' => 'time',
            'free_preview_seconds' => $seconds,
        ];
    }
}
