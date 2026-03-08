<?php
/**
 * Controller for managing courses in the Admin Panel.
 * Migrated to Tailwind CSS and new Panel structure.
 */

namespace App\Http\Controllers\Panel\Admin;

use App\Http\Controllers\Panel\Admin\Concerns\ManagesContentVisibility;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class CourseController extends Controller
{
    use ManagesContentVisibility;

    /**
     * Display a listing of courses.
     */
    public function index(Request $request)
    {
        $this->ensurePermission('courses.view');
        $user = Auth::user();

        if (!$user->isAdmin()) {
            $this->repairLegacyOwnershipForCurrentUser();
        }

        $query = Course::query()->with(['creator'])->withCount(['lessons', 'enrollments'])->latest();

        // If not superadmin, only show courses created by the user
        if (!$user->isAdmin()) {
            $this->applyOwnerFilter($query);
        }

        // Search
        $search = trim((string) $request->query('q', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                    ->orWhere('author_name', 'like', '%' . $search . '%');
            });
        }

        $courses = $query->paginate(6);
        $courses->appends($request->all());

        return view('panel.admin.courses.index', compact('courses', 'search'));
    }

    /**
     * Show the form for creating a new course.
     */
    public function create()
    {
        $this->ensurePermission('courses.create');

        $course = new Course();
        $course->status = 'draft';

        return view('panel.admin.courses.form', compact('course'));
    }

    /**
     * Store a newly created course in storage.
     */
    public function store(Request $request)
    {
        $this->ensurePermission('courses.create');

        $this->normalizeMoneyFields($request);
        $this->sanitizeDates($request);

        $data = $this->validateCourse($request);

        // Booleans
        $data['is_featured'] = $request->boolean('is_featured');
        $data['is_certificate_enabled'] = $request->boolean('is_certificate_enabled');
        $data['video_block_download'] = $request->boolean('video_block_download');
        $data['video_floating_enabled'] = $request->boolean('video_floating_enabled');

        // Legacy support
        $data['published'] = ($data['status'] === 'published');
        $data['user_id'] = Auth::id();
        $data = $this->applyVisibilityData($request, $data);
        if (Schema::hasColumn('courses', 'created_by')) {
            $data['created_by'] = Auth::id();
        }

        // Handle Thumbnail
        if ($request->hasFile('thumbnail')) {
            $file = $request->file('thumbnail');
            $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/course-thumbs'), $fileName);
            $data['thumbnail'] = 'uploads/course-thumbs/' . $fileName;
        }

        $course = Course::create($data);

        return redirect()->route('panel.admin.courses.edit', $course)->with('success', 'Curso criado com sucesso. Agora você pode adicionar aulas.');
    }

    /**
     * Show the form for editing the specified course.
     */
    public function edit(Course $course)
    {
        $this->ensurePermission('courses.edit');
        $this->ensureCanManage($course);

        $course->load([
            'lessons' => function ($q) {
                $q->orderBy('order', 'asc');
            }
        ]);

        return view('panel.admin.courses.form', compact('course'));
    }

    /**
     * Update the specified course in storage.
     */
    public function update(Request $request, Course $course)
    {
        $this->ensurePermission('courses.edit');
        $this->ensureCanManage($course);

        $this->normalizeMoneyFields($request);
        $this->sanitizeDates($request);

        $data = $this->validateCourse($request, $course->id);

        $data['is_featured'] = $request->boolean('is_featured');
        $data['is_certificate_enabled'] = $request->boolean('is_certificate_enabled');
        $data['video_block_download'] = $request->boolean('video_block_download');
        $data['video_floating_enabled'] = $request->boolean('video_floating_enabled');

        // Legacy support
        $data['published'] = ($data['status'] === 'published');
        $data = $this->applyVisibilityData(
            $request,
            $data,
            $course->visibility,
            (bool) $course->is_somos_unicas
        );
        if (Schema::hasColumn('courses', 'created_by') && empty($course->created_by)) {
            $data['created_by'] = Auth::id();
        }

        // Handle Thumbnail
        if ($request->hasFile('thumbnail')) {
            $this->deleteFileIfExists($course->thumbnail);
            $file = $request->file('thumbnail');
            $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/course-thumbs'), $fileName);
            $data['thumbnail'] = 'uploads/course-thumbs/' . $fileName;
        }

        // Handle Certificate BG
        if ($request->hasFile('certificate_bg')) {
            $this->deleteFileIfExists($course->certificate_bg);
            $file = $request->file('certificate_bg');
            $fileName = 'cert_bg_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/certificates'), $fileName);
            $data['certificate_bg'] = 'uploads/certificates/' . $fileName;
        }

        // Handle Instructor Signature
        if ($request->hasFile('instructor_signature')) {
            $this->deleteFileIfExists($course->instructor_signature);
            $file = $request->file('instructor_signature');
            $fileName = 'sig_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/signatures'), $fileName);
            $data['instructor_signature'] = 'uploads/signatures/' . $fileName;
        }

        $course->update($data);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Curso atualizado com sucesso.']);
        }

        return redirect()->route('panel.admin.courses.index')->with('success', 'Curso atualizado com sucesso.');
    }

    /**
     * Remove the specified course from storage.
     */
    public function destroy(Course $course)
    {
        $this->ensurePermission('courses.delete');
        $this->ensureCanManage($course);

        $this->deleteFileIfExists($course->thumbnail);
        $this->deleteFileIfExists($course->certificate_bg);
        $this->deleteFileIfExists($course->instructor_signature);

        $course->delete();

        return redirect()->route('panel.admin.courses.index')->with('success', 'Curso removido com sucesso.');
    }

    /**
     * Validation rules for Course.
     */
    protected function validateCourse(Request $request, $id = null)
    {
        return $request->validate([
            'title' => 'required|string|max:255',
            'short_description' => 'nullable|string|max:500',
            'full_description' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'flash_sale_price' => 'nullable|numeric|min:0',
            'flash_sale_ends_at' => 'nullable|date',
            'author_name' => 'nullable|string|max:255',
            'status' => 'required|in:draft,published,archived,paused',
            'visibility' => $this->visibilityRule(),
            'video_floating_width' => 'nullable|integer|min:260|max:960',
            'video_floating_height' => 'nullable|integer|min:160|max:720',
            'certificate_settings' => 'nullable|json',
            'thumbnail' => ($id ? 'nullable' : 'required') . '|image|max:5120',
            'certificate_bg' => 'nullable|image|max:5120',
            'instructor_signature' => 'nullable|image|max:2048|mimes:png,jpg,jpeg',
        ]);
    }

    /**
     * Normalize money input (replace comma with dot, remove R$).
     */
    protected function normalizeMoneyFields(Request $request)
    {
        foreach (['price', 'flash_sale_price'] as $field) {
            if ($request->has($field)) {
                $value = $request->input($field);
                if ($value) {
                    $value = str_replace(['R$', ' ', "\u{00A0}"], '', (string) $value);
                    if (str_contains($value, ',')) {
                        $value = str_replace('.', '', $value);
                        $value = str_replace(',', '.', $value);
                    }
                    $request->merge([$field => $value]);
                }
            }
        }
    }

    /**
     * Sanitize dates (remove T from datetime-local).
     */
    protected function sanitizeDates(Request $request)
    {
        if ($request->has('flash_sale_ends_at') && $request->input('flash_sale_ends_at')) {
            $request->merge([
                'flash_sale_ends_at' => str_replace('T', ' ', (string) $request->input('flash_sale_ends_at')),
            ]);
        }
    }

    /**
     * Delete file from public path if it exists.
     */
    protected function deleteFileIfExists(?string $path)
    {
        if ($path && file_exists(public_path($path))) {
            @unlink(public_path($path));
        }
    }

    /**
     * Ensure user has permission.
     */
    protected function ensurePermission(string $permission)
    {
        $user = Auth::user();
        if (!$user || (!$user->isAdmin() && !$user->hasPermission($permission))) {
            abort(403, 'Acesso negado.');
        }
    }

    /**
     * Ensure user can manage the course.
     */
    protected function ensureCanManage(Course $course)
    {
        if (Auth::user()->isAdmin()) {
            return;
        }

        $authUserId = (int) Auth::id();
        $authUserName = trim((string) (Auth::user()->name ?? ''));
        $normalizedAuthUserName = $this->normalizeOwnerName($authUserName);

        $ownsByUserId = (int) $course->user_id === $authUserId;
        $ownsByLegacyCreatedBy = false;
        $ownsByAuthorFallback = false;

        if (Schema::hasColumn('courses', 'created_by')) {
            $ownsByLegacyCreatedBy = (int) ($course->created_by ?? 0) === $authUserId;
        }

        if ((empty($course->user_id) || in_array((int) $course->user_id, [0, 1], true)) && $normalizedAuthUserName !== '') {
            $ownsByAuthorFallback = $this->normalizeOwnerName((string) ($course->author_name ?? '')) === $normalizedAuthUserName;
        }

        if (!$ownsByUserId && !$ownsByLegacyCreatedBy && !$ownsByAuthorFallback) {
            abort(403, 'Você não tem permissão para gerenciar este curso.');
        }
    }

    protected function applyOwnerFilter($query): void
    {
        $authUserId = (int) Auth::id();
        $authUserName = trim((string) (Auth::user()->name ?? ''));
        $normalizedAuthUserName = $this->normalizeOwnerName($authUserName);

        $query->where(function ($ownerQuery) use ($authUserId, $normalizedAuthUserName) {
            $ownerQuery->where('user_id', $authUserId);

            if (Schema::hasColumn('courses', 'created_by')) {
                $ownerQuery->orWhere('created_by', $authUserId);
            }

            if ($normalizedAuthUserName !== '') {
                $ownerQuery->orWhere(function ($fallbackQuery) use ($normalizedAuthUserName) {
                    $fallbackQuery->where(function ($courseOwnerQuery) {
                        $courseOwnerQuery->whereNull('user_id')
                            ->orWhereIn('user_id', [0, 1]);
                    })
                        ->whereRaw('LOWER(TRIM(author_name)) = ?', [$normalizedAuthUserName]);
                });
            }
        });
    }

    protected function repairLegacyOwnershipForCurrentUser(): void
    {
        $user = Auth::user();
        if (!$user || !Schema::hasColumn('courses', 'user_id')) {
            return;
        }

        $authUserId = (int) $user->id;
        if ($authUserId <= 0) {
            return;
        }

        if (Schema::hasColumn('courses', 'created_by')) {
            Course::query()
                ->where(function ($query) {
                    $query->whereNull('user_id')
                        ->orWhereIn('user_id', [0, 1]);
                })
                ->where('created_by', $authUserId)
                ->update(['user_id' => $authUserId]);
        }

        $normalizedAuthUserName = $this->normalizeOwnerName((string) ($user->name ?? ''));
        if ($normalizedAuthUserName === '') {
            return;
        }

        Course::query()
            ->select(['id', 'author_name'])
            ->where(function ($query) {
                $query->whereNull('user_id')
                    ->orWhereIn('user_id', [0, 1]);
            })
            ->whereNotNull('author_name')
            ->get()
            ->each(function (Course $course) use ($authUserId, $normalizedAuthUserName) {
                if ($this->normalizeOwnerName((string) ($course->author_name ?? '')) !== $normalizedAuthUserName) {
                    return;
                }

                Course::query()
                    ->whereKey($course->id)
                    ->update(['user_id' => $authUserId]);
            });
    }

    protected function normalizeOwnerName(string $value): string
    {
        $value = str_replace("\u{00A0}", ' ', $value);
        $value = preg_replace('/\s+/u', ' ', trim($value)) ?? '';
        if ($value === '') {
            return '';
        }

        return mb_strtolower(Str::ascii($value));
    }

    public function reorderLessons(Request $request, Course $course)
    {
        $this->ensurePermission('courses.edit');

        $request->validate([
            'lessons' => 'required|array',
            'lessons.*.id' => 'required|integer|exists:lessons,id',
            'lessons.*.order' => 'required|integer',
        ]);

        foreach ($request->lessons as $item) {
            $course->lessons()->where('id', $item['id'])->update(['order' => $item['order']]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Preview certificate with current form data (without saving).
     */
    public function certificatePreview(Request $request, Course $course)
    {
        $this->ensurePermission('courses.view');
        $this->ensureCanManage($course);

        // We use the same validation but relaxed for preview
        $request->validate([
            'certificate_settings' => 'nullable|json',
            'author_name' => 'nullable|string|max:255',
        ]);

        // Temporarily override course data for rendering
        if ($request->filled('certificate_settings')) {
            $course->certificate_settings = $request->input('certificate_settings');
        }
        if ($request->filled('author_name')) {
            $course->author_name = $request->input('author_name');
        }

        $user = Auth::user();
        $type = 'course';
        $certHash = 'PREVIEW-' . strtoupper(Str::random(8));
        $workload = $course->total_hours ?? 0;

        $settings = $course->certificate_settings;
        if (is_string($settings)) {
            $settings = json_decode($settings, true) ?: [];
        }

        $fontCss = app(\App\Services\Certificate\CertificateFontCssGenerator::class)
            ->buildFontCss($settings ?: [], true);

        return view('admin.certificates.template', [
            'user' => $user,
            'course' => $course,
            'certHash' => $certHash,
            'authorName' => $course->author_name ?: 'Instrutor',
            'workload' => $workload,
            'type' => $type,
            'fontCss' => $fontCss,
            'isPreview' => true
        ]);
    }
}
