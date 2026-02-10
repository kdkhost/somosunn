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
                // Update specific text key if your frontend stores it there, 
                // OR if your structure splits text from style. 
                // Based on previous template analysis: $dataMap['title'] is used. 
                // But wait, the Template uses $certTitle = $settings['title'] ?? 'DEFAULT';
                // And checks is_array($settings['title']). 
                // If it IS an array, it renders the dragging element.
                // The dragging element uses {{ $dataMap[$key] }} which comes from... $certTitle!

                // So, we actually DON'T need to store the text inside $settings['title'] array 
                // because the template pulls the text from $dataMap['title'] which comes from $certTitle 
                // which comes from $settings['title'] (if string) or fallback.

                // WAIT! The template logic:
                // $certTitle = $settings['title'] ?? 'DEF'; (This might get the ARRAY if available)
                // $dataMap['title'] = $certTitle;
                // Loop renders $dataMap[$key].

                // If $settings['title'] is an array, $certTitle becomes that array.
                // $dataMap['title'] becomes that array.
                // Blade {{ $array }} throws error!

                // Correct Logic:
                // The frontend 'title' element in certSettings probably DOES NOT contain the text itself,
                // just the style/position. The text is likely expected to be static or passed separately.
                // However, users want to CUSTOMIZE the text.
                // So we should store the text differently or the template handles it.

                // Let's look at template again.
                // $certTitle = $settings['title'] ?? '...'; 
                // If $settings['title'] is array (from Draggable), $certTitle is Array.
                // $dataMap['title'] = $certTitle (Array).
                // <div ...> {{ $dataMap[$key] ?? '' }} </div>
                // {{ Array }} -> Error.

                // THIS explains why it might be failing or defaulting.
                // If the user drags 'title', $settings['title'] is saved as Array.
                // The Template crashes or behaves weirdly.

                // Fix: properties 'title' and 'presentation_text' in settings should ONLY be used for STYLE.
                // The actual TEXT should be stored in specific string keys like 'title_text' or handled via the separate inputs.

                // BUT, the Controller overwrites $settings['title'] with string.
                // This makes it work as text, but breaks positioning.

                // PROPOSED FIX:
                // 1. Save 'certificate_title' input into a NEW key, e.g., $settings['title_text'].
                // 2. Or, if we must use 'title', we ensure the template handles $settings['title'] as style, 
                //    and uses a separate source for the inner text.

                // Let's assume we want to save the text in a separate field in the JSON or use the column.
                // The $request->certificate_title IS the text.
                // We should NOT overwrite $settings['title'] (the style object).
                // We should save the text in $settings['title_text'] or simply rely on the text input being passed to the view?
                // The view sets $certTitle = $settings['title'] ?? ...

                // View Fix is needed too.
                // Controller should NOT touch $settings['title'] if it is an array.
                // Only save text if we have a place for it.
                // Let's save it as $settings['custom_title'] and $settings['custom_presentation_text'].

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
