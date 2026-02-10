<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Course;

class CourseController extends Controller
{
    public function available(Request $request)
    {
        $q = $request->input('q');
        $query = Course::query()->where('status', 'published');

        if ($q) {
            $query->where('title', 'like', "%{$q}%");
        }

        $items = $query->latest()->paginate(12);
        return view('admin.courses.available', compact('items', 'q'));
    }

    public function index()
    {
        $this->ensurePermission('courses.view');

        $query = Course::latest();

        // Se não for admin, mostra apenas cursos do próprio usuário
        if (!Auth::user()->isAdmin()) {
            $query->where('user_id', Auth::id());
        }

        $courses = $query->get();
        return view('admin.courses.index', compact('courses'));
    }

    public function create()
    {
        $this->ensurePermission('courses.create');

        return view('admin.courses.form', ['course' => new Course]);
    }

    public function store(Request $request)
    {
        $this->ensurePermission('courses.create');

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
            'short_description' => 'nullable|string|max:500',
            'full_description' => 'nullable|string',
            'price' => 'nullable|numeric',
            'author_name' => 'nullable|string|max:255',
            'status' => 'required|in:draft,published,archived,paused',
            'thumbnail' => 'nullable|image|max:10240', // 10MB Max
            'video_floating_width' => 'nullable|integer|min:260|max:960',
            'video_floating_height' => 'nullable|integer|min:160|max:720',
        ]);

        $data['is_featured'] = $request->has('is_featured');
        $data['is_certificate_enabled'] = $request->has('is_certificate_enabled');
        $data['video_block_download'] = $request->has('video_block_download');
        $data['video_floating_enabled'] = $request->has('video_floating_enabled');

        // Handle Certificate Settings
        if ($request->has('certificate_settings')) {
            $data['certificate_settings'] = json_decode($request->certificate_settings, true);
        }

        // Legacy support automation
        $data['published'] = ($data['status'] === 'published');
        $data['price'] = (!isset($data['price']) || $data['price'] === null || $data['price'] === '') ? 0 : $data['price'];

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
            $file->move(public_path('uploads/course-thumbs'), $fileName);
            $data['thumbnail'] = 'uploads/course-thumbs/' . $fileName;
        }

        Course::create($data + ['user_id' => auth()->id()]);

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

        if ($request->has('price')) {
            $price = $request->price;
            $price = str_replace(['R$', ' ', '.'], '', $price);
            $price = str_replace(',', '.', $price);
            $request->merge(['price' => $price]);
        }

        $data = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'short_description' => 'nullable|string|max:500',
            'full_description' => 'nullable|string',
            'price' => 'nullable|numeric',
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
        ]);

        $data['is_featured'] = $request->has('is_featured');
        $data['is_certificate_enabled'] = $request->has('is_certificate_enabled');
        $data['video_block_download'] = $request->has('video_block_download');
        $data['video_floating_enabled'] = $request->has('video_floating_enabled');

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
            $file->move(public_path('uploads/course-thumbs'), $fileName);
            $data['thumbnail'] = 'uploads/course-thumbs/' . $fileName;
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
