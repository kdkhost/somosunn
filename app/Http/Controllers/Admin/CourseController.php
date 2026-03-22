<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Panel\Admin\Concerns\ManagesContentVisibility;
use App\Models\Enrollment;
use App\Models\OrderItem;
use App\Services\WatermarkService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Course;

class CourseController extends Controller
{
    use ManagesContentVisibility;

    protected $mpService;

    public function __construct(\App\Services\Payment\MercadoPagoService $mpService)
    {
        $this->mpService = $mpService;
    }
    public function available(Request $request)
    {
        $q = trim((string) $request->input('q', ''));
        $userId = (int) Auth::id();

        $enrolledCourseIds = Enrollment::query()
            ->where('user_id', $userId)
            ->where('enrollable_type', Course::class)
            ->pluck('enrollable_id');

        $paidCourseIds = OrderItem::query()
            ->where('item_type', 'course')
            ->whereHas('order', function ($query) use ($userId) {
                $query->where('user_id', $userId)->where('status', 'paid');
            })
            ->pluck('item_id');

        $courseIds = $enrolledCourseIds->merge($paidCourseIds)->unique()->values();

        $query = Course::query()->with('creator');

        if ($courseIds->isEmpty()) {
            $query->whereRaw('1=0');
        } else {
            $query->whereIn('id', $courseIds);
        }

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('title', 'like', '%' . $q . '%')
                    ->orWhere('short_description', 'like', '%' . $q . '%')
                    ->orWhere('full_description', 'like', '%' . $q . '%');
            });
        }

        $items = $query->orderByDesc('id')->paginate(12)->withQueryString();
        return view('admin.courses.available', compact('items', 'q'));
    }

    public function index()
    {
        $this->ensurePermission('courses.view');

        $query = Course::query()->latest();

        if (!auth()->user()->isAdmin()) {
            $query->where('user_id', auth()->id());
        }

        $courses = $query->get();
        return view('admin.courses.index', compact('courses'));
    }

    public function create()
    {
        $this->ensurePermission('courses.create');
        $course = new Course();
        return view('admin.courses.form', compact('course'));
    }

    public function show(Course $course)
    {
        $this->ensurePermission('courses.edit');
        $this->ensureCanManage($course);

        return redirect()->route('admin.courses.edit', $course);
    }

    public function store(Request $request)
    {
        $this->ensurePermission('courses.create');

        foreach (['price', 'flash_sale_price'] as $field) {
            if (!$request->has($field)) {
                continue;
            }

            $request->merge([$field => $this->normalizeMoneyInput($request->input($field))]);
        }

        if ($request->has('flash_sale_ends_at') && $request->input('flash_sale_ends_at')) {
            $request->merge([
                'flash_sale_ends_at' => str_replace('T', ' ', (string) $request->input('flash_sale_ends_at')),
            ]);
        }

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'short_description' => 'nullable|string|max:500',
            'full_description' => 'nullable|string',
            'price' => 'nullable|numeric',
            'flash_sale_price' => 'nullable|numeric|min:0',
            'flash_sale_ends_at' => 'nullable|date',
            'author_name' => 'nullable|string|max:255',
            'status' => 'required|in:draft,published,archived,paused',
            'thumbnail' => 'nullable|image|max:10240', // 10MB Max
            'video_floating_width' => 'nullable|integer|min:260|max:960',
            'video_floating_height' => 'nullable|integer|min:160|max:720',
            'is_recurring' => 'nullable|boolean',
            'period' => 'nullable|string|max:50',
            'billing_cycle' => 'nullable|integer|min:1',
            'is_somos_unicas' => 'nullable|boolean',
            'visibility' => 'nullable|string|in:ambos,somos_unn,somos_unicas',
        ]);

        $data['is_featured'] = $request->has('is_featured');
        $data['is_certificate_enabled'] = $request->has('is_certificate_enabled');
        $data['video_block_download'] = $request->has('video_block_download');
        $data['video_floating_enabled'] = $request->has('video_floating_enabled');
        $data['is_recurring'] = $request->boolean('is_recurring');
        $data['period'] = $request->input('period', 'months');
        $data['billing_cycle'] = (int) $request->input('billing_cycle', 1);

        // Handle Certificate Settings
        if ($request->has('certificate_settings')) {
            $data['certificate_settings'] = json_decode($request->certificate_settings, true);
        }

        // Legacy support automation
        $data['published'] = ($data['status'] === 'published');
        $data['price'] = (!isset($data['price']) || $data['price'] === null || $data['price'] === '') ? 0 : $data['price'];
        $data = $this->applyVisibilityData($request, $data, null, false, 'courses');

        // Validation for new fields
        $request->validate([
            'certificate_bg' => 'nullable|image|max:5120', // 5MB
            'certificate_settings' => 'nullable|json',
        ]);

        if ($request->hasFile('certificate_bg')) {
            $file = $request->file('certificate_bg');
            $fileName = 'cert_bg_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/certificates'), $fileName);
            $data['certificate_bg'] = 'uploads/certificates/' . $fileName;
        }

        if ($request->hasFile('thumbnail')) {
            $file = $request->file('thumbnail');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $data['thumbnail'] = app(WatermarkService::class)->processPublicImage(
                $file,
                'uploads/course-thumbs',
                $fileName,
                ['prefix' => 'course-thumb']
            );
        }

        $course = Course::create($data + ['user_id' => auth()->id()]);

        if ($course->is_recurring) {
            $this->syncWithMercadoPago($course);
        }

        return response()->json(['success' => true]);
    }

    public function edit(Course $course)
    {
        $this->ensurePermission('courses.edit');
        $this->ensureCanManage($course);

        return view('admin.courses.form', compact('course'));
    }

    public function update(Request $request, Course $course)
    {
        $this->ensurePermission('courses.edit');
        $this->ensureCanManage($course);

        foreach (['price', 'flash_sale_price'] as $field) {
            if (!$request->has($field)) {
                continue;
            }

            $request->merge([$field => $this->normalizeMoneyInput($request->input($field))]);
        }

        if ($request->has('flash_sale_ends_at') && $request->input('flash_sale_ends_at')) {
            $request->merge([
                'flash_sale_ends_at' => str_replace('T', ' ', (string) $request->input('flash_sale_ends_at')),
            ]);
        }

        $data = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'short_description' => 'nullable|string|max:500',
            'full_description' => 'nullable|string',
            'price' => 'nullable|numeric',
            'flash_sale_price' => 'nullable|numeric|min:0',
            'flash_sale_ends_at' => 'nullable|date',
            'author_name' => 'nullable|string|max:255',
            'status' => 'sometimes|required|in:draft,published,archived,paused',
            'thumbnail' => 'nullable|image|max:10240', // 10MB Max
            'certificate_bg' => 'nullable|image|max:5120',
            'instructor_signature' => 'nullable|image|max:2048|mimes:png,jpg,jpeg',
            'certificate_settings' => 'nullable|json',
            'certificate_title' => 'nullable|string|max:255',
            'presentation_text' => 'nullable|string|max:500',
            'video_floating_width' => 'nullable|integer|min:260|max:960',
            'video_floating_height' => 'nullable|integer|min:160|max:720',
            'is_recurring' => 'nullable|boolean',
            'period' => 'nullable|string|max:50',
            'billing_cycle' => 'nullable|integer|min:1',
            'is_somos_unicas' => 'nullable|boolean',
            'visibility' => 'nullable|string|in:ambos,somos_unn,somos_unicas',
        ]);

        $data['is_featured'] = $request->has('is_featured');
        $data['is_certificate_enabled'] = $request->has('is_certificate_enabled');
        $data['video_block_download'] = $request->has('video_block_download');
        $data['video_floating_enabled'] = $request->has('video_floating_enabled');
        $data['is_recurring'] = $request->boolean('is_recurring');
        $data['period'] = $request->input('period', $course->period ?? 'months');
        $data['billing_cycle'] = (int) $request->input('billing_cycle', $course->billing_cycle ?? 1);

        // Handle Certificate Settings
        if ($request->has('certificate_settings')) {
            // Depending on frontend, it might come as string JSON or array if not processed by JS.
            // Assuming string from hidden input
            $decoded = json_decode($request->certificate_settings, true);
            if ($decoded !== null) {
                $data['certificate_settings'] = $decoded;
            }
        }

        // Legacy support automation - Use null coalescing to prevent undefined key errors
        $data['published'] = (isset($data['status']) && $data['status'] === 'published');
        $data['price'] = (!isset($data['price']) || $data['price'] === null || $data['price'] === '') ? 0 : $data['price'];
        $data = $this->applyVisibilityData(
            $request,
            $data,
            $course->visibility,
            (bool) $course->is_somos_unicas,
            'courses'
        );



        if ($request->hasFile('certificate_bg')) {
            if ($course->certificate_bg && file_exists(public_path($course->certificate_bg))) {
                @unlink(public_path($course->certificate_bg));
            }
            $file = $request->file('certificate_bg');
            $fileName = 'cert_bg_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/certificates'), $fileName);
            $data['certificate_bg'] = 'uploads/certificates/' . $fileName;
        }

        if ($request->hasFile('thumbnail')) {
            $file = $request->file('thumbnail');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $data['thumbnail'] = app(WatermarkService::class)->processPublicImage(
                $file,
                'uploads/course-thumbs',
                $fileName,
                ['prefix' => 'course-thumb']
            );
        }

        // Handle Instructor Signature Upload
        if ($request->hasFile('instructor_signature')) {
            if ($course->instructor_signature && file_exists(public_path($course->instructor_signature))) {
                @unlink(public_path($course->instructor_signature));
            }
            $file = $request->file('instructor_signature');
            $fileName = 'sig_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/signatures'), $fileName);
            $data['instructor_signature'] = 'uploads/signatures/' . $fileName;
        }

        // Merge additional certificate fields into certificate_settings if provided
        if (!isset($data['certificate_settings'])) {
            $data['certificate_settings'] = $course->certificate_settings ?? [];
        }

        // Note: workload_hours is now auto-calculated from lessons, no manual input
        if ($request->filled('certificate_title')) {
            // Only update text, preserve other styling/position if it's an array
            if (isset($data['certificate_settings']['title']) && is_array($data['certificate_settings']['title'])) {
                $data['certificate_settings']['title']['text'] = $request->certificate_title;
                $data['certificate_settings']['custom_title'] = $request->certificate_title;
            } else {
                $data['certificate_settings']['title'] = $request->certificate_title;
            }
        }
        if ($request->filled('presentation_text')) {
            if (isset($data['certificate_settings']['presentation_text']) && is_array($data['certificate_settings']['presentation_text'])) {
                $data['certificate_settings']['custom_presentation_text'] = $request->presentation_text;
            } else {
                $data['certificate_settings']['presentation_text'] = $request->presentation_text;
            }
        }

        $course->update($data);

        if ($course->is_recurring && empty($course->mp_plan_id)) {
            $this->syncWithMercadoPago($course);
        }

        return response()->json(['success' => true]);
    }

    private function syncWithMercadoPago(Course $course): void
    {
        try {
            if ($course->is_recurring && empty($course->mp_plan_id)) {
                $mpPlan = $this->mpService->createPreapprovalPlan([
                    'name' => 'Curso: ' . $course->title,
                    'price' => $course->getEffectivePriceAttribute(),
                    'period' => $course->period ?: 'months',
                    'billing_cycle' => $course->billing_cycle ?: 1,
                ]);

                if (isset($mpPlan['id'])) {
                    Course::where('id', $course->id)->update(['mp_plan_id' => $mpPlan['id']]);
                }
            }
        } catch (\Exception $e) {
            \Log::error('Erro ao sincronizar curso com Mercado Pago: ' . $e->getMessage());
        }
    }

    public function reorderLessons(Request $request, Course $course)
    {
        $this->ensurePermission('courses.edit');
        $this->ensureCanManage($course);

        $dados = $request->validate([
            'lessons' => 'required|array|min:1',
            'lessons.*.id' => 'required|integer|exists:lessons,id',
            'lessons.*.order' => 'required|integer|min:1',
        ]);

        foreach ((array) $dados['lessons'] as $item) {
            $course->lessons()
                ->where('id', (int) $item['id'])
                ->update(['order' => (int) $item['order']]);
        }

        return response()->json(['success' => true]);
    }

    public function destroy(Course $course)
    {
        $this->ensurePermission('courses.delete');
        $this->ensureCanManage($course);

        $course->delete();
        return redirect()->route('admin.courses.index')->with('success', 'Curso removido');
    }

    /**
     * Verifica se o usuário tem permissão ou é admin.
     */
    protected function ensurePermission(string $permission): void
    {
        $user = Auth::user();
        if (!$user || (!$user->isAdmin() && !$user->hasPermission($permission))) {
            abort(403, 'Você não tem permissão para realizar esta ação.');
        }
    }

    protected function normalizeMoneyInput($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        $value = str_replace(['R$', ' ', "\u{00A0}"], '', $value);
        if ($value === '') {
            return null;
        }

        if (str_contains($value, ',')) {
            // Brazilian format: 1.234,56 -> 1234.56
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        } elseif (substr_count($value, '.') > 1) {
            // 1.234.567 -> 1234567
            $value = str_replace('.', '', $value);
        }

        $value = trim($value);
        if ($value === '' || $value === '-') {
            return null;
        }

        return $value;
    }

    /**
     * Verifica se o usuário pode gerenciar o curso (é dono ou admin).
     */
    protected function ensureCanManage(Course $course): void
    {
        if (Auth::user()->isAdmin()) {
            return;
        }

        if (!$course->isOwnedBy(Auth::id())) {
            abort(403, 'Você não tem permissão para gerenciar este curso.');
        }
    }
}
