<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\ItemReview;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class CourseController extends Controller
{
    /**
     * Display a listing of the courses.
     */
    public function index()
    {
        $demoMode = (bool) config('app.demo_mode');

        // Check if courses feature is enabled in settings
        $isEnabled = \App\Models\Setting::get('feature_courses', '1') === '1';

        if (!$isEnabled) {
            abort(404, 'Cursos temporariamente indisponível');
        }

        $publicStatuses = ['published', 'paused'];

        $featuredCourse = Course::with('creator')
            ->withCount([
                'reviews as approved_reviews_count' => function ($query) {
                    $query->where('status', 'approved');
                }
            ])
            ->whereIn('status', $publicStatuses)
            ->orderByDesc('is_featured')
            ->orderByDesc('id')
            ->first();

        $courses = Course::with('creator')
            ->withCount([
                'reviews as approved_reviews_count' => function ($query) {
                    $query->where('status', 'approved');
                }
            ])
            ->whereIn('status', $publicStatuses)
            ->orderByDesc('is_featured')
            ->orderByDesc('id')
            ->paginate(12);

        // If no courses exist, provide demo data
        if ($demoMode && $courses->isEmpty()) {
            $demoCourses = collect([
                (object) [
                    'id' => 1,
                    'title' => 'Networking Estratégico',
                    'slug' => 'networking-estrategico-demo',
                    'short_description' => 'Aprenda a construir conexões que geram resultados reais para seu negócio.',
                    'price' => 297.00,
                    'duration' => 480,
                    'thumbnail' => null,
                    'creator' => (object) ['name' => 'UNN Academy'],
                    'is_demo' => true,
                ],
                (object) [
                    'id' => 2,
                    'title' => 'Vendas de Alto Impacto',
                    'slug' => 'vendas-alto-impacto-demo',
                    'short_description' => 'Técnicas avançadas para fechar negócios e aumentar seu faturamento.',
                    'price' => 497.00,
                    'duration' => 600,
                    'thumbnail' => null,
                    'creator' => (object) ['name' => 'UNN Academy'],
                    'is_demo' => true,
                ],
                (object) [
                    'id' => 3,
                    'title' => 'Liderança e Gestão de Equipes',
                    'slug' => 'lideranca-gestao-demo',
                    'short_description' => 'Desenvolva habilidades de liderança para conduzir equipes de alta performance.',
                    'price' => 397.00,
                    'duration' => 540,
                    'thumbnail' => null,
                    'creator' => (object) ['name' => 'UNN Academy'],
                    'is_demo' => true,
                ],
            ]);

            return view('courses.index', [
                'courses' => $demoCourses,
                'featuredCourse' => $demoCourses->first(),
                'isDemo' => true,
            ]);
        }

        return view('courses.index', compact('courses', 'featuredCourse'));
    }

    /**
     * Show the form for creating a new course.
     */
    public function create()
    {
        if (!Auth::user() || !Auth::user()->canAccessFeature('vendor')) {
            abort(403, 'Você não possui permissão para criar cursos.');
        }
        return view('courses.create');
    }

    /**
     * Store a newly created course in storage.
     */
    public function store(Request $request)
    {
        if (!Auth::user() || !Auth::user()->canAccessFeature('vendor')) {
            abort(403, 'Você não possui permissão para criar cursos.');
        }
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'duration' => 'nullable|integer', // minutes
            'is_certificate_enabled' => 'nullable|boolean',
            'thumbnail' => 'nullable|image|max:2048',
            'short_description' => 'nullable|string|max:500',
            'full_description' => 'nullable|string',
        ]);

        $slug = null;
        if (Schema::hasColumn('courses', 'slug')) {
            $slug = Str::slug($validated['title']) . '-' . uniqid();
        }

        $path = null;
        if ($request->hasFile('thumbnail')) {
            $path = $request->file('thumbnail')->store('courses', 'public');
        }

        $courseData = [
            'title' => $validated['title'],
            'price' => $validated['price'],
            'duration' => $validated['duration'] ?? 0,
            'is_certificate_enabled' => $request->has('is_certificate_enabled'),
            'thumbnail' => $path,
            'short_description' => $validated['short_description'],
            'full_description' => $validated['full_description'],
            'author_name' => Auth::user()->name,
            'status' => 'draft',
        ];

        if ($slug !== null) {
            $courseData['slug'] = $slug;
        }

        $course = Auth::user()->createdCourses()->create($courseData);

        $routeParam = $course->slug ?: $course->id;

        return redirect()->route('courses.show', $routeParam)
            ->with('success', 'Curso criado com sucesso! Adicione aulas agora.');
    }

    /**
     * Display the specified course.
     */
    public function show($courseParam)
    {
        $courseParam = (string) $courseParam;

        $query = Course::query()->with([
            'lessons' => function ($q) {
                $q->orderBy('order');
            },
        ]);

        if (Schema::hasColumn('courses', 'slug')) {
            $query->where(function ($q) use ($courseParam) {
                $q->where('slug', $courseParam);

                if (ctype_digit($courseParam)) {
                    $q->orWhere('id', (int) $courseParam);
                }
            });
        } else {
            // Banco legado sem coluna slug: evita 500 e permite acesso por ID.
            if (!ctype_digit($courseParam)) {
                abort(404);
            }

            $query->where('id', (int) $courseParam);
        }

        $course = $query->firstOrFail();

        $isEnabled = \App\Models\Setting::get('feature_courses', '1') === '1';
        if (!$isEnabled) {
            abort(404);
        }

        // Canonical: if accessed by ID but the course has a slug, redirect to slug URL.
        if (ctype_digit($courseParam) && !empty($course->slug) && $courseParam !== (string) $course->slug) {
            return redirect()->route('courses.show', $course->slug, 301);
        }

        $isPublic = in_array((string) ($course->status ?? ''), ['published', 'paused'], true);
        $canManage = Auth::check() && (Auth::user()->isAdmin() || Auth::id() === $course->user_id);

        if (!$isPublic && !$canManage) {
            abort(404);
        }

        $isEnrolled = Auth::check() && Auth::user()->hasCourseAccess($course);
        $approvedReviewsQuery = ItemReview::query()
            ->where('reviewable_type', Course::class)
            ->where('reviewable_id', $course->id)
            ->where('status', 'approved');

        $reviews = (clone $approvedReviewsQuery)
            ->with('user:id,name,photo')
            ->latest('id')
            ->limit(6)
            ->get();

        $reviewsCount = (clone $approvedReviewsQuery)->count();
        $reviewsAvg = (clone $approvedReviewsQuery)->avg('rating');
        $reviewsAvg = $reviewsAvg !== null ? round((float) $reviewsAvg, 1) : null;

        $myReview = null;
        if (Auth::check()) {
            $myReview = ItemReview::query()
                ->where('user_id', Auth::id())
                ->where('reviewable_type', Course::class)
                ->where('reviewable_id', $course->id)
                ->first();
        }

        return view('courses.show', compact('course', 'isEnrolled', 'reviews', 'reviewsCount', 'reviewsAvg', 'myReview'));
    }

    /**
     * Show the form for editing the specified course.
     */
    public function edit(Course $course)
    {
        if (!Auth::user() || !Auth::user()->canAccessFeature('vendor')) {
            abort(403, 'Você não possui permissão para editar cursos.');
        }
        $this->authorize('update', $course);
        return view('courses.edit', compact('course'));
    }

    /**
     * Update the specified course in storage.
     */
    public function update(Request $request, Course $course)
    {
        if (!Auth::user() || !Auth::user()->canAccessFeature('vendor')) {
            abort(403, 'Você não possui permissão para editar cursos.');
        }
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
        if (!Auth::user() || !Auth::user()->canAccessFeature('vendor')) {
            abort(403, 'Você não possui permissão para remover cursos.');
        }
        $this->authorize('delete', $course);
        $course->delete();
        return redirect()->route('courses.index')->with('success', 'Curso removido.');
    }

    /**
     * Mark the course as complete manually by the user.
     */
    public function complete(Request $request, Course $course)
    {
        if (!Auth::check()) {
            abort(401);
        }

        $user = Auth::user();
        if (!$user->hasCourseAccess($course)) {
            abort(403, 'Você não tem acesso a este curso.');
        }

        // Calculate progress
        $lessons = $course->lessons()->orderBy('order')->get();
        $totalLessons = $lessons->count();

        if ($totalLessons === 0) {
            return redirect()->route('panel.dashboard')
                ->with('error', 'Este curso não possui aulas para concluir.');
        }

        $completedLessonsCount = \App\Models\LessonProgress::where('user_id', $user->id)
            ->whereIn('lesson_id', $lessons->pluck('id'))
            ->whereNotNull('completed_at')
            ->count();

        $percentage = ($completedLessonsCount / $totalLessons);

        // 89% Threshold Check
        if ($percentage < 0.89) {
            return redirect()->back()
                ->with('error', 'Você precisa concluir pelo menos 89% do curso para finalizar.');
        }

        // Trigger Certificate / Completion Logic
        // We reuse the logic from LessonController that handles this check
        $lessonController = new LessonController();
        $dummyLesson = $lessons->last(); // Just to pass a lesson, though logic might not strictly need it if called differently

        // However, LessonController::checkCourseAndIssueCertificate is protected/private or designed to be called internally.
        // Let's replicate the essential part or trigger it via a lesson update if needed.
        // Actually, looking at previous context, `checkCourseAndIssueCertificate` is called by `updatePlaybackProgress`.

        // Let's try to find if we can re-use or if we should implement the specific completion logic here.
        // Since `checkCourseAndIssueCertificate` logic is complex (calculates workload etc), 
        // and we want to ensure consistent behavior, we can instantiate LessonController and call it if it was public,
        // OR we just duplicate the critical call or move logic to a Service.

        // For now, let's look at `LessonController` again to see if we can make it public or static, 
        // OR if we can just trigger it. 
        // Actually, the MOST ROBUST way is to ensure all lessons are marked? No, user might skip some.
        // We just need to trigger the "Issue Certificate" logic.

        // Let's invoke the logic directly here for simplicity and safety,
        // leveraging the code we just modified in LessonController.

        // Since we can't easily call that private/protected method from here without refactoring,
        // I will copy the essential "Issue Certificate" logic which allows us to be explicit.

        // 1. Calculate Watched Time (Workload) - Same logic as LessonController
        $lessonProgresses = \App\Models\LessonProgress::where('user_id', $user->id)
            ->whereIn('lesson_id', $lessons->pluck('id'))
            ->with('lesson') // optimize
            ->get();
        $watchedSeconds = $lessonProgresses->sum(function ($progress) {
            return $progress->lesson->duration ?? 0;
        });

        // Let's simplify: If we are here, we are > 89%.
        // Check if certificate exists.
        $existingCert = $course->certificates()->where('user_id', $user->id)->first();

        if (!$existingCert && $course->is_certificate_enabled) {
            // Generate certificate with PDF using the proper method
            $certificateController = new \App\Http\Controllers\Admin\CertificateController();
            $existingCert = $certificateController->issueCertificate($user->id, 'course', $course->id);
        }

        // Mark enrollment as completed (using polymorphic relationship)
        \App\Models\Enrollment::updateOrCreate(
            [
                'user_id' => $user->id,
                'enrollable_id' => $course->id,
                'enrollable_type' => 'App\\Models\\Course'
            ],
            [
                'completed_at' => now(),
                'progress' => round($percentage * 100),
                'status' => 'completed'
            ]
        );

        // Redirect to Student Dashboard (Meus Cursos)
        return redirect()->route('panel.dashboard')
            ->with('success', 'Parabéns! Curso concluído com sucesso. Seu certificado já está disponível (se aplicável).');
    }
}
